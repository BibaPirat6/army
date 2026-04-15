<?php

namespace App\Http\Controllers;

use App\Models\Commissariat;
use App\Models\CommissariatPosition;
use App\Models\Employee;
use App\Models\EmployeePosition;
use App\Models\EmployeePositionStatus;
use App\Models\Person;
use App\Models\Position;
use DB;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CommissariatsController extends Controller
{
    public function index()
    {
        $commissariats = Commissariat::all();

        return view('admin.org.commissariats.index', compact('commissariats'));
    }

    public function show(Request $request, $id)
    {
        $commissariat = Commissariat::findOrFail($id);
        $columns = Person::getTableColumns();

        $backUrl = $request->input('back_url');

        return view('admin.org.commissariats.show', compact('commissariat', 'backUrl', 'columns'));
    }

    public function create(Request $request)
    {
        $employees = Employee::all();
        $employeePositionStatuses = EmployeePositionStatus::all();

        $backUrl = $request->input('back_url');
        $x = $request->input('x');
        $y = $request->input('y');

        return view('admin.org.commissariats.create', compact('employees', 'backUrl', 'x', 'y', 'employeePositionStatuses'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|min:2|max:255',
            'chief_employee_id' => 'sometimes|nullable|integer|exists:employees,id',
            'longitude' => 'required|integer',
            'latitude' => 'required|integer',
            'rate_total' => 'required|numeric|min:0.25|max:2',
            'is_independent' => 'sometimes|boolean',

            'rate' => [
                'sometimes', 'numeric', 'min:0.25', 'max:2',
                'lte:rate_total',
                Rule::requiredIf(fn () => filled($request->chief_employee_id)),
            ],
            'employee_position_status_id' => [
                'sometimes', 'integer', 'exists:employee_position_statuses,id',
                Rule::requiredIf(fn () => filled($request->chief_employee_id)),
            ],
            'started_at' => [
                'sometimes', 'date',
                Rule::requiredIf(fn () => filled($request->chief_employee_id)),
            ],

            'expected_return_at' => [
                'sometimes', 'nullable', 'date', 'after:started_at',
                Rule::requiredIf(fn () => filled($request->chief_employee_id) && in_array($request->input('employee_position_status_id'), [2, 3])),
            ],

            'ended_at' => [
                'sometimes', 'nullable', 'date', 'after:started_at',
                Rule::requiredIf(fn () => filled($request->chief_employee_id) && $request->input('employee_position_status_id') == 4),
            ],
        ], [
            // 🔹 Название комиссариата
            'name.required' => 'Название комиссариата обязательно для заполнения.',
            'name.string' => 'Название комиссариата должно быть текстом.',
            'name.min' => 'Название комиссариата должно содержать не менее 2 символов.',
            'name.max' => 'Название комиссариата не может превышать 255 символов.',

            // 🔹 Координаты
            'longitude.required' => 'Координата X (горизонталь) обязательна.',
            'longitude.integer' => 'Координата X должна быть целым числом.',
            'latitude.required' => 'Координата Y (вертикаль) обязательна.',
            'latitude.integer' => 'Координата Y должна быть целым числом.',

            // 🔹 Ставка комиссариата
            'rate_total.required' => 'Ставка комиссариата обязательна.',
            'rate_total.numeric' => 'Ставка должна быть числом.',
            'rate_total.min' => 'Ставка не может быть меньше 0.25.',
            'rate_total.max' => 'Ставка не может превышать 2.00.',

            // 🔹 Начальник
            'chief_employee_id.integer' => 'Некорректный ID сотрудника.',
            'chief_employee_id.exists' => 'Выбранный сотрудник не найден в системе.',

            // 🔹 Чекбокс
            'is_independent.boolean' => 'Некорректное значение поля «Самостоятельная должность».',

            // 🔹 Ставка сотрудника (rate)
            'rate.numeric' => 'Ставка сотрудника должна быть числом.',
            'rate.min' => 'Ставка сотрудника не может быть меньше 0.25.',
            'rate.max' => 'Ставка сотрудника не может превышать 2.00.',
            'rate.lte' => 'Ставка сотрудника не может превышать общую ставку комиссариата (rate_total).',
            'rate.required' => 'Ставка сотрудника обязательна при назначении начальника.',

            // 🔹 Статус назначения
            'employee_position_status_id.integer' => 'Некорректный формат статуса.',
            'employee_position_status_id.exists' => 'Выбранный статус назначения не найден.',
            'employee_position_status_id.required' => 'Статус назначения обязателен при назначении начальника.',

            // 🔹 Дата начала
            'started_at.date' => 'Некорректный формат даты начала.',
            'started_at.required' => 'Дата начала обязательна при назначении начальника.',

            // 🔹 Ожидаемое возвращение
            'expected_return_at.date' => 'Некорректный формат даты.',
            'expected_return_at.after' => 'Ожидаемое возвращение должно быть позже даты начала.',
            'expected_return_at.required' => 'Ожидаемое возвращение обязательно для статусов «Отпуск» и «Декрет».',

            // 🔹 Дата увольнения
            'ended_at.date' => 'Некорректный формат даты.',
            'ended_at.after' => 'Дата увольнения должна быть позже даты начала.',
            'ended_at.required' => 'Дата увольнения обязательна для статуса «Уволен».',
        ]);

        DB::beginTransaction();
        try {
            // 1) Создаем комиссариат
            $commissariat = Commissariat::create([
                'name' => $data['name'],
                'longitude' => $data['longitude'],
                'latitude' => $data['latitude'],
            ]);

            // 2) Находим справочную должность "начальник комиссариата"
            $chiefPositionRef = Position::whereHas('chiefType', function ($q) {
                $q->where('name', 'начальник комиссариата');
            })->first();

            if (! $chiefPositionRef) {
                // откат и ошибка: справочник должностей не содержит нужную запись
                DB::rollBack();

                return back()->withErrors(['error' => 'Не найдена должность "начальник комиссариата" в справочнике'])->withInput();
            }

            // 3) создаем штатную должность (слот) для начальника комиссариата
            $rateTotal = isset($data['rate_total']) ? (float) $data['rate_total'] : 1.00;
            // checkbox may come as 'on' or be absent — используем has()
            $isIndependent = $request->has('is_independent');

            $commissariatPosition = CommissariatPosition::create([
                'commissariat_id' => $commissariat->id,
                'position_id' => $chiefPositionRef->id,
                'rate_total' => $rateTotal,
                'is_independent' => $isIndependent,
            ]);
            $commissariatPosition->refresh();

            // 4) Если выбран начальник — создаём назначение
            $chiefEmployeeId = $data['chief_employee_id'] ?? null;
            if (! empty($chiefEmployeeId)) {
                $rate = isset($data['rate']) ? (float) $data['rate'] : 1.00;
                $statusId = $data['employee_position_status_id'] ?? 1; // fallback to 1
                $startedAt = $data['started_at'] ?? now()->toDateString();
                $expectedReturnAt = $data['expected_return_at'] ?? null;
                $endedAt = $data['ended_at'] ?? null;
                $isActive = $data['employee_position_status_id'] == 4 ? false : true;

                EmployeePosition::create([
                    'employee_id' => $chiefEmployeeId,
                    'commissariat_position_id' => $commissariatPosition->id,
                    'rate' => $rate,
                    'employee_position_status_id' => $statusId,
                    'started_at' => $startedAt,
                    'is_active' => $isActive,
                    'ended_at' => $endedAt,
                    'expected_return_at' => $expectedReturnAt,
                ]);
            }

            DB::commit();

            $backUrl = $request->get('backUrl', route('commissariats.index'));

            return redirect()->to($backUrl)->with('success', 'Комиссариат успешно создан.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors(['error' => 'Ошибка создания: '.$e->getMessage()])->withInput();
        }
    }

    public function edit(Request $request, $id)
    {
        $commissariat = Commissariat::findOrFail($id);
        $employees = Employee::all();
        $employeePositionStatuses = EmployeePositionStatus::all();

        $backUrl = $request->input('back_url');

        return view('admin.org.commissariats.edit', compact('commissariat', 'employees', 'backUrl', 'employeePositionStatuses'));
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'name' => 'required|string|min:2|max:255',
            'chief_employee_id' => 'sometimes|nullable|integer|exists:employees,id',
            'chief_employee_position_status_id' => 'sometimes|nullable|integer|exists:employee_position_statuses,id',
            'longitude' => 'sometimes|nullable|integer',
            'latitude' => 'sometimes|nullable|integer',

            // Штатная должность
            'rate_total' => 'required|numeric|min:0.25|max:2',
            'is_independent' => 'sometimes|boolean',

            // Новое назначение (если выбран начальник)
            'rate' => [
                'sometimes', 'numeric', 'min:0.25', 'max:2',
                'lte:rate_total',
                \Illuminate\Validation\Rule::requiredIf(fn () => filled($request->chief_employee_id)),
            ],
            'employee_position_status_id' => [
                'sometimes', 'integer', 'exists:employee_position_statuses,id',
                \Illuminate\Validation\Rule::requiredIf(fn () => filled($request->chief_employee_id)),
            ],
            'started_at' => [
                'sometimes', 'date',
                \Illuminate\Validation\Rule::requiredIf(fn () => filled($request->chief_employee_id)),
            ],
            'expected_return_at' => ['sometimes', 'nullable', 'date', 'after:started_at'],
            'ended_at' => ['sometimes', 'nullable', 'date', 'after:started_at'],

            // Старое назначение (при смене) — необязательные поля, используются если переданы
            'old_rate' => ['sometimes', 'nullable', 'numeric', 'min:0.25', 'max:2'],
            'old_started_at' => ['sometimes', 'nullable', 'date'],
            'old_expected_return_at' => ['sometimes', 'nullable', 'date', 'after:old_started_at'],
            'old_ended_at' => ['sometimes', 'nullable', 'date', 'after:old_started_at'],
        ]);

        $commissariat = Commissariat::findOrFail($id);

        DB::beginTransaction();
        try {
            // 1) Обновляем данные комиссариата
            $commissariat->update([
                'name' => $data['name'],
                'longitude' => $data['longitude'] ?? $commissariat->longitude,
                'latitude' => $data['latitude'] ?? $commissariat->latitude,
            ]);

            // 2) Находим должность "Начальник комиссариата" в справочнике
            $chiefPositionRef = Position::whereHas('chiefType', fn ($q) => $q->where('name', 'начальник комиссариата')
            )->first();

            if (! $chiefPositionRef) {
                throw new \Exception('Не найдена должность "начальник комиссариата" в справочнике');
            }

            // 3) Ищем штатную
            $chiefSlot = CommissariatPosition::where('commissariat_id', $commissariat->id)
                ->where('position_id', $chiefPositionRef->id)
                ->whereNull('department_id')      // уровень комиссариата
                ->whereNull('division_id')
                ->first();

            // обновляем или создаём слот если нужно
            $rateTotal = isset($data['rate_total']) ? (float)$data['rate_total'] : 1.00;
            $isIndependent = $request->has('is_independent');

            if ($chiefSlot) {
                $chiefSlot->update(['rate_total' => $rateTotal, 'is_independent' => $isIndependent]);
            } else {
                $chiefSlot = CommissariatPosition::create([
                    'commissariat_id' => $commissariat->id,
                    'position_id' => $chiefPositionRef->id,
                    'rate_total' => $rateTotal,
                    'is_independent' => $isIndependent,
                ]);
            }

            // 4) Ищем текущее активное назначение на эту должность
            $currentAssignment = $chiefSlot ? EmployeePosition::where('commissariat_position_id', $chiefSlot->id)
                ->where('is_active', true)
                ->whereNull('ended_at')
                ->first() : null;

            $newEmployeeId = $data['chief_employee_id'] ?? null;
            $newStatusId = $data['chief_employee_position_status_id'] ?? null;

            // 5) Логика смены начальника: учитываем nullable новый id
            if ($currentAssignment) {
                if (! empty($newEmployeeId) && $currentAssignment->employee_id != $newEmployeeId) {
                    // сотрудник изменился — обновляем старое назначение (если переданы поля)
                    $oldStatusId = $data['chief_employee_position_status_id'] ?? null;
                    $oldRate = $request->input('old_rate');
                    $oldStartedAt = $request->input('old_started_at');
                    $oldExpectedReturnAt = $request->input('old_expected_return_at');
                    $oldEndedAt = $request->input('old_ended_at');

                    if ($oldStatusId !== null) {
                        $currentAssignment->employee_position_status_id = $oldStatusId;
                        // если уволен (предположим id 4) — помечаем неактивным
                        if ((int)$oldStatusId === 4) {
                            $currentAssignment->is_active = false;
                        }
                    }
                    if ($oldRate !== null) $currentAssignment->rate = (float)$oldRate;
                    if ($oldStartedAt) $currentAssignment->started_at = $oldStartedAt;
                    if ($oldExpectedReturnAt) $currentAssignment->expected_return_at = $oldExpectedReturnAt;
                    if ($oldEndedAt) $currentAssignment->ended_at = $oldEndedAt;

                    $currentAssignment->save();

                    // затем создаём новое назначение для нового сотрудника (если передан)
                    $newRate = isset($data['rate']) ? (float)$data['rate'] : 1.00;
                    $newStatus = $data['employee_position_status_id'] ?? 1;
                    $newStartedAt = $data['started_at'] ?? now()->toDateString();
                    $newExpectedReturnAt = $data['expected_return_at'] ?? null;
                    $newEndedAt = $data['ended_at'] ?? null;

                    if (! empty($newEmployeeId)) {
                        EmployeePosition::create([
                            'employee_id' => $newEmployeeId,
                            'commissariat_position_id' => $chiefSlot->id,
                            'rate' => $newRate,
                            'employee_position_status_id' => $newStatus,
                            'started_at' => $newStartedAt,
                            'is_active' => true,
                            'ended_at' => $newEndedAt,
                            'expected_return_at' => $newExpectedReturnAt,
                        ]);
                    }
                }
                // если новый id пустой или равен текущему — ничего не делаем
            } else {
                // назначение не было — создаём только если передан новый сотрудник
                if (! empty($newEmployeeId)) {
                    $newRate = isset($data['rate']) ? (float)$data['rate'] : 1.00;
                    $newStatus = $data['employee_position_status_id'] ?? 1;
                    $newStartedAt = $data['started_at'] ?? now()->toDateString();
                    $newExpectedReturnAt = $data['expected_return_at'] ?? null;
                    $newEndedAt = $data['ended_at'] ?? null;

                    EmployeePosition::create([
                        'employee_id' => $newEmployeeId,
                        'commissariat_position_id' => $chiefSlot->id,
                        'rate' => $newRate,
                        'employee_position_status_id' => $newStatus,
                        'started_at' => $newStartedAt,
                        'is_active' => true,
                        'ended_at' => $newEndedAt,
                        'expected_return_at' => $newExpectedReturnAt,
                    ]);
                }
            }

            DB::commit();

            $backUrl = $request->get('backUrl', route('commissariats.index'));

            return redirect()->to($backUrl)->with('success', '✅ Комиссариат обновлён');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors(['error' => 'Ошибка обновления: '.$e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Вспомогательный метод: создать назначение начальника
     */
    private function createChiefAssignment(int $commissariatPositionId, int $employeeId, ?int $statusId = null): void
    {
        EmployeePosition::create([
            'commissariat_position_id' => $commissariatPositionId,
            'employee_id' => $employeeId,
            'rate' => 1.00,
            'employee_position_status_id' => $statusId ?? 1, // если статус передан — используем его, иначе дефолт 1
            'started_at' => now()->toDateString(),
            'is_active' => true,
            'ended_at' => null,
            'expected_return_at' => null,
        ]);
    }

    public function delete($id)
    {
        $commissariat = Commissariat::findOrFail($id);
        $commissariat->delete();

        return redirect()->route('commissariats.index')->with('success', 'Комиссариат успешно удален.');
    }
}
