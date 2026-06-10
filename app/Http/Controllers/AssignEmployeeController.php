<?php

namespace App\Http\Controllers;

use App\Models\CommissariatPosition;
use App\Models\Employee;
use App\Models\EmployeePosition;
use App\Models\EmployeePositionStatus;
use Illuminate\Http\Request;

class AssignEmployeeController extends Controller
{
    public function create(Request $request, $id)
    {
        $commissariatPosition = CommissariatPosition::findOrFail($id);
        $backUrl = $request->get('back_url');
        $employees = Employee::all();
        $employeePositionStatuses = EmployeePositionStatus::all();

        $employeeId = $request->input('employeeId');
        $employee = null;

        // Проверяем, передан ли employeeId, и находим сотрудника только если он передан
        if ($employeeId) {
            try {
                $employee = Employee::findOrFail($employeeId);
            } catch (\Exception $e) {
                $employee = null;
            }
        }

        return view('admin.org.commissariat-positions.assign-employee.create', compact(
            'commissariatPosition',
            'backUrl',
            'employees',
            'employeePositionStatuses',
            'employee'
        ));
    }

    /**
     * Store a newly created assignment
     */
    public function store(Request $request, $id)
    {
        // Находим штатную должность
        $commissariatPosition = CommissariatPosition::findOrFail($id);

        // Валидация входных данных
        $validated = $request->validate([
            'chief_employee_id' => 'required|exists:employees,id',
            'rate' => 'required|numeric|min:0.25|max:2.00',
            'employee_position_status_id' => 'required|exists:employee_position_statuses,id',
            'back_url' => 'nullable|url',
        ]);

        // Получаем статус
        $status = EmployeePositionStatus::findOrFail($validated['employee_position_status_id']);

        // Получаем сумму занятых ставок (только те, которые занимают ставку)
        $occupiedRate = $commissariatPosition->employeePositions()
            ->whereHas('employeePositionStatus', function ($query) {
                $query->where('occupies_rate', true);
            })
            ->sum('rate');

        // Вычисляем доступные ставки
        $availableRate = $commissariatPosition->rate_total - $occupiedRate;

        // Проверка: если статус занимает ставку, проверяем наличие свободных ставок
        if ($status->occupies_rate) {
            // Проверяем, что ставка не превышает доступную
            if ($validated['rate'] > $availableRate) {
                return back()
                    ->withErrors([
                        'rate' => sprintf(
                            'Недостаточно свободных ставок. Доступно: %.2f, требуется: %.2f. Всего ставок: %.2f, занято: %.2f',
                            $availableRate,
                            $validated['rate'],
                            $commissariatPosition->rate_total,
                            $occupiedRate
                        ),
                    ])
                    ->withInput();
            }

            // Дополнительная проверка: ставка не может быть больше общей
            if ($validated['rate'] > $commissariatPosition->rate_total) {
                return back()
                    ->withErrors([
                        'rate' => sprintf(
                            'Ставка не может быть больше общей ставки должности (%.2f)',
                            $commissariatPosition->rate_total
                        ),
                    ])
                    ->withInput();
            }
        }

        // Проверка: не назначен ли уже этот сотрудник на эту должность
        $existingAssignment = $commissariatPosition->employeePositions()
            ->where('employee_id', $validated['chief_employee_id'])
            ->first();

        if ($existingAssignment) {
            $employee = Employee::find($validated['chief_employee_id']);

            return back()
                ->withErrors([
                    'chief_employee_id' => sprintf(
                        'Сотрудник "%s" уже назначен на эту должность. ID назначения: %d',
                        $employee->getFullNameAttribute(),
                        $existingAssignment->id
                    ),
                ])
                ->withInput();
        }

        // Создаем новое назначение
        try {
            $employeePosition = new EmployeePosition;
            $employeePosition->employee_id = $validated['chief_employee_id'];
            $employeePosition->commissariat_position_id = $commissariatPosition->id;
            $employeePosition->rate = $validated['rate'];
            $employeePosition->employee_position_status_id = $validated['employee_position_status_id'];
            $employeePosition->save();

            // Получаем информацию о сотруднике для сообщения
            $employee = Employee::find($validated['chief_employee_id']);
            $employeeName = $employee->getFullNameAttribute();
            $positionName = $commissariatPosition->position->name;

            // Формируем сообщение об успехе
            $message = sprintf(
                'Сотрудник "%s" успешно назначен на должность "%s" со ставкой %.2f. Статус: %s',
                $employeeName,
                $positionName,
                $validated['rate'],
                $status->name
            );

            // Если статус не занимает ставку, добавляем пояснение
            if (! $status->occupies_rate) {
                $message .= ' (статус не занимает ставку)';
            } else {
                // Пересчитываем свободные ставки после назначения
                $newOccupiedRate = $occupiedRate + $validated['rate'];
                $newAvailableRate = $commissariatPosition->rate_total - $newOccupiedRate;
                $message .= sprintf(' Осталось свободно: %.2f', $newAvailableRate);
            }

            // Определяем URL для редиректа
            $backUrl = $request->input('back_url');
            if (! $backUrl) {
                $backUrl = route('commissariat-positions.show', [
                    'id' => $commissariatPosition->id,
                    'commissariat_id' => $commissariatPosition->commissariat_id,
                ]);
            }

            return redirect()->to($backUrl)->with('success', $message);

        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => 'Ошибка при сохранении: '.$e->getMessage()])
                ->withInput();
        }
    }

    public function edit(Request $request, $id, $employeePositionId)
    {
        // Находим штатную должность
        $commissariatPosition = CommissariatPosition::findOrFail($id);

        // Находим назначение сотрудника
        $employeePosition = EmployeePosition::with(['employee', 'employeePositionStatus'])
            ->where('commissariat_position_id', $id)
            ->where('id', $employeePositionId)
            ->firstOrFail();

        $backUrl = $request->get('back_url');
        $employees = Employee::all();
        $employeePositionStatuses = EmployeePositionStatus::all();

        // Получаем сумму занятых ставок (исключая ТЕКУЩЕЕ назначение)
        // Считаем только те назначения, у которых статус занимает ставку
        $occupiedRateByOthers = $commissariatPosition->employeePositions()
            ->whereHas('employeePositionStatus', function ($query) {
                $query->where('occupies_rate', true);
            })
            ->where('id', '!=', $employeePositionId)
            ->sum('rate');

        // Если текущий сотрудник занимает ставку, то занято = ставки других
        // Если текущий сотрудник НЕ занимает ставку, то занято = ставки других
        $occupiedRate = $occupiedRateByOthers;

        // Добавляем ставку текущего сотрудника только если он занимает ставку
        if ($employeePosition->employeePositionStatus->occupies_rate) {
            $occupiedRate += $employeePosition->rate;
        }

        // Доступные ставки = всего ставок - занято (включая текущего сотрудника)
        $availableRate = $commissariatPosition->rate_total - $occupiedRate;

        // Для поля max в инпуте:
        // Если текущий статус занимает ставку, то максимальная ставка = доступные + текущая ставка
        // Потому что мы можем освободить ставку текущего сотрудника и назначить новую
        $maxRateForInput = $availableRate;
        if ($employeePosition->employeePositionStatus->occupies_rate) {
            $maxRateForInput = $availableRate + $employeePosition->rate;
        }

        // Минимальная ставка
        $minRateForInput = $employeePosition->employeePositionStatus->occupies_rate ? 0.25 : 0;

        return view('admin.org.commissariat-positions.assign-employee.edit', compact(
            'commissariatPosition',
            'employeePosition',
            'backUrl',
            'employees',
            'employeePositionStatuses',
            'occupiedRate',
            'availableRate',
            'maxRateForInput',
            'minRateForInput'
        ));
    }

    public function update(Request $request, $id, $employeePositionId)
    {
        // Находим штатную должность
        $commissariatPosition = CommissariatPosition::findOrFail($id);

        // Находим назначение
        $employeePosition = EmployeePosition::where('commissariat_position_id', $id)
            ->where('id', $employeePositionId)
            ->with(['employeePositionStatus'])
            ->firstOrFail();

        // Валидация
        $validated = $request->validate([
            'chief_employee_id' => 'required|exists:employees,id',
            'rate' => 'required|numeric|min:0', // Изменили на required, так как теперь всегда отправляется
            'employee_position_status_id' => 'required|exists:employee_position_statuses,id',
            'back_url' => 'nullable|url',
        ]);

        // Получаем новый статус
        $newStatus = EmployeePositionStatus::findOrFail($validated['employee_position_status_id']);

        // Определяем итоговую ставку
        $finalRate = $validated['rate'];

        // Если ставка 0, но статус не занимает ставку - сохраняем предыдущую ставку
        if ($finalRate == 0 && ! $newStatus->occupies_rate) {
            $finalRate = $employeePosition->rate;
        }

        // Если статус занимает ставку, проверяем что ставка > 0
        if ($newStatus->occupies_rate && $finalRate <= 0) {
            return back()
                ->withErrors([
                    'rate' => 'Для статуса, который занимает ставку, необходимо указать ставку больше 0',
                ])
                ->withInput();
        }

        // Рассчитываем занятые ставки ДРУГИМИ сотрудниками (исключая текущее назначение)
        $occupiedRateByOthers = $commissariatPosition->employeePositions()
            ->whereHas('employeePositionStatus', function ($query) {
                $query->where('occupies_rate', true);
            })
            ->where('id', '!=', $employeePositionId)
            ->sum('rate');

        // Доступные ставки для нового назначения
        $availableRate = $commissariatPosition->rate_total - $occupiedRateByOthers;

        // Проверка доступности ставок для нового статуса
        if ($newStatus->occupies_rate) {
            if ($finalRate > $availableRate) {
                return back()
                    ->withErrors([
                        'rate' => sprintf(
                            'Недостаточно свободных ставок. Доступно: %.2f (всего: %.2f, занято другими: %.2f), требуется: %.2f',
                            $availableRate,
                            $commissariatPosition->rate_total,
                            $occupiedRateByOthers,
                            $finalRate
                        ),
                    ])
                    ->withInput();
            }
        }

        // Проверка на дублирование (если меняем сотрудника)
        if ($employeePosition->employee_id != $validated['chief_employee_id']) {
            $exists = $commissariatPosition->employeePositions()
                ->where('employee_id', $validated['chief_employee_id'])
                ->where('id', '!=', $employeePositionId)
                ->exists();

            if ($exists) {
                return back()
                    ->withErrors([
                        'chief_employee_id' => 'Этот сотрудник уже назначен на данную должность',
                    ])
                    ->withInput();
            }
        }

        // Обновляем назначение
        $employeePosition->employee_id = $validated['chief_employee_id'];
        $employeePosition->rate = $finalRate; // Используем итоговую ставку
        $employeePosition->employee_position_status_id = $validated['employee_position_status_id'];
        $employeePosition->save();

        // Формируем сообщение
        $employee = Employee::find($validated['chief_employee_id']);

        if ($newStatus->occupies_rate) {
            $rateText = "со ставкой {$finalRate}";
        } else {
            $rateText = "(статус не занимает ставку, сохраненная ставка: {$finalRate})";
        }

        $message = sprintf(
            'Назначение обновлено: сотрудник "%s", %s, статус: %s',
            $employee->getFullNameAttribute(),
            $rateText,
            $newStatus->name
        );

        $backUrl = $request->input('back_url', route('commissariat-positions.show', [
            'id' => $commissariatPosition->id,
            'commissariat_id' => $commissariatPosition->commissariat_id,
        ]));

        return redirect()->to($backUrl)->with('success', $message);
    }

    /**
     * Удалить назначение сотрудника на штатную должность
     *
     * @param  int  $id  - ID штатной должности (commissariat_position_id)
     * @param  int  $employeePositionId  - ID назначения (employee_position_id)
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete(Request $request, $id, $employeePositionId)
    {
        try {
            // Находим штатную должность
            $commissariatPosition = CommissariatPosition::findOrFail($id);

            // Находим назначение и проверяем, что оно принадлежит этой должности
            $employeePosition = EmployeePosition::where('id', $employeePositionId)
                ->where('commissariat_position_id', $id)
                ->firstOrFail();

            // Сохраняем информацию для сообщения
            $employeeName = $employeePosition->employee->getFullNameAttribute() ?? 'Сотрудник';
            $positionName = $commissariatPosition->position->name;
            $rate = $employeePosition->rate;

            // Удаляем назначение
            $employeePosition->delete();

            // Получаем URL для возврата
            $backUrl = $request->input('back_url', route('commissariat-positions.index', [
                'commissariat_id' => $commissariatPosition->commissariat_id,
            ]));

            return redirect()->to($backUrl)->with('success',
                sprintf('Сотрудник "%s" успешно удален с должности "%s". Ставка %.2f .',
                    $employeeName,
                    $positionName,
                    $rate
                )
            );

        } catch (\Exception $e) {
            $backUrl = $request->input('back_url', route('commissariat-positions.index', [
                'commissariat_id' => $commissariatPosition->commissariat_id ?? null,
            ]));

            return redirect()->to($backUrl)->with('error',
                'Ошибка при удалении назначения: '.$e->getMessage()
            );
        }
    }
}
