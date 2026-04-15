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

            // 🔹 Начальник
            'chief_employee_id.integer' => 'Некорректный ID сотрудника.',
            'chief_employee_id.exists' => 'Выбранный сотрудник не найден в системе.',

            // 🔹 Статус назначения
            'employee_position_status_id.integer' => 'Некорректный формат статуса.',
            'employee_position_status_id.exists' => 'Выбранный статус назначения не найден.',
            'employee_position_status_id.required' => 'Статус назначения обязателен при назначении начальника.',
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

            $commissariatPosition = CommissariatPosition::create([
                'commissariat_id' => $commissariat->id,
                'position_id' => $chiefPositionRef->id,
                'rate_total' => 1.00,
                'is_independent' => false,
            ]);
            $commissariatPosition->refresh();

            $chiefEmployeeId = $data['chief_employee_id'] ?? null;
            if (! empty($chiefEmployeeId)) {
                $statusId = $data['employee_position_status_id'] ?? 1; // fallback to 1

                EmployeePosition::create([
                    'employee_id' => $chiefEmployeeId,
                    'commissariat_position_id' => $commissariatPosition->id,
                    'rate' => 1.00,
                    'employee_position_status_id' => $statusId,
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
            'employee_position_status_id' => 'sometimes|nullable|integer|exists:employee_position_statuses,id',
            'old_chief_employee_id' => 'sometimes|nullable|integer|exists:employees,id',
            'old_chief_employee_position_status_id' => 'sometimes|nullable|integer|exists:employee_position_statuses,id',
            'longitude' => 'required|integer',
            'latitude' => 'required|integer',
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

            // 🔹 Начальник
            'chief_employee_id.integer' => 'Некорректный ID сотрудника.',
            'chief_employee_id.exists' => 'Выбранный сотрудник не найден в системе.',

            // 🔹 Статус назначения
            'employee_position_status_id.integer' => 'Некорректный формат статуса.',
            'employee_position_status_id.exists' => 'Выбранный статус назначения не найден.',
            'employee_position_status_id.required' => 'Статус назначения обязателен при назначении начальника.',
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
                DB::rollBack();
                throw new \Exception('Не найдена должность "начальник комиссариата" в справочнике');
            }

            // 3) Ищем штатную
            $chiefSlot = CommissariatPosition::where('commissariat_id', $commissariat->id)
                ->where('position_id', $chiefPositionRef->id)
                ->whereNull('department_id')
                ->whereNull('division_id')
                ->first();

            if (! $chiefSlot) {
                $chiefSlot = CommissariatPosition::create([
                    'commissariat_id' => $commissariat->id,
                    'position_id' => $chiefPositionRef->id,
                    'rate_total' => 1.00,
                    'is_independent' => false,
                ]);
            }
            $chiefSlot->refresh();

            // Управление назначением/сменой начальника.
            // В запросе приходят employee IDs (не IDs employee_position). Нужно искать записи
            // EmployeePosition по паре (commissariat_position_id, employee_id).
            $oldChiefEmployeeId = !empty($data['old_chief_employee_id']) ? (int)$data['old_chief_employee_id'] : null;
            $newChiefEmployeeId = !empty($data['chief_employee_id']) ? (int)$data['chief_employee_id'] : null;

            // Пытаемся найти существующие записи назначений (если они есть)
            $oldAssignment = null;
            $newAssignment = null;

            if ($oldChiefEmployeeId) {
                $oldAssignment = EmployeePosition::where('commissariat_position_id', $chiefSlot->id)
                    ->where('employee_id', $oldChiefEmployeeId)
                    ->first();
            }

            if ($newChiefEmployeeId) {
                $newAssignment = EmployeePosition::where('commissariat_position_id', $chiefSlot->id)
                    ->where('employee_id', $newChiefEmployeeId)
                    ->first();
            }

            // Сценарии:
            // 1) Не было начальника -> не назначили: ничего не делаем
            // 2) Не было начальника -> назначили: создаём запись для нового
            // 3) Был начальник -> поменяли ему статус: обновляем существующую запись
            // 4) Был начальник -> назначили нового: обновляем старую запись (статус) и создаём/обновляем новую

            if (! $oldChiefEmployeeId && ! $newChiefEmployeeId) {
                // ничего
            } elseif (! $oldChiefEmployeeId && $newChiefEmployeeId) {
                // Ранее не было начальника, назначаем нового
                if ($newAssignment) {
                    $newAssignment->update([
                        'employee_position_status_id' => $data['employee_position_status_id'] ?? $newAssignment->employee_position_status_id,
                    ]);
                } else {
                    EmployeePosition::create([
                        'employee_id' => $newChiefEmployeeId,
                        'commissariat_position_id' => $chiefSlot->id,
                        'rate' => 1.00,
                        'employee_position_status_id' => $data['employee_position_status_id'] ?? 1,
                    ]);
                }
            } elseif ($oldChiefEmployeeId && ! $newChiefEmployeeId) {
                // Был начальник, теперь не назначают — обновляем его статус, если передан
                if ($oldAssignment) {
                    if (isset($data['old_chief_employee_position_status_id'])) {
                        $oldAssignment->update([
                            'employee_position_status_id' => $data['old_chief_employee_position_status_id'],
                        ]);
                    }
                }
            } else {
                // Оба заданы — возможна замена или смена статуса у того же сотрудника
                if ($oldChiefEmployeeId === $newChiefEmployeeId) {
                    // Тот же сотрудник — просто обновляем его статус (новый)
                    if ($newAssignment) {
                        $newAssignment->update([
                            'employee_position_status_id' => $data['employee_position_status_id'] ?? $newAssignment->employee_position_status_id,
                        ]);
                    }
                } else {
                    // Замена: обновляем старого (если есть) и создаём/обновляем нового
                    if ($oldAssignment && isset($data['old_chief_employee_position_status_id'])) {
                        $oldAssignment->update([
                            'employee_position_status_id' => $data['old_chief_employee_position_status_id'],
                        ]);
                    }

                    if ($newAssignment) {
                        $newAssignment->update([
                            'employee_position_status_id' => $data['employee_position_status_id'] ?? $newAssignment->employee_position_status_id,
                        ]);
                    } else {
                        EmployeePosition::create([
                            'employee_id' => $newChiefEmployeeId,
                            'commissariat_position_id' => $chiefSlot->id,
                            'rate' => 1.00,
                            'employee_position_status_id' => $data['employee_position_status_id'] ?? 1,
                        ]);
                    }
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

    public function delete($id)
    {
        $commissariat = Commissariat::findOrFail($id);
        $commissariat->delete();

        return redirect()->route('commissariats.index')->with('success', 'Комиссариат успешно удален.');
    }
}
