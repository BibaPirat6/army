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
        dd($request->all());
        $data = $request->validate([
            'name' => 'required|string|min:2|max:255',
            'chief_employee_id' => 'sometimes|nullable|integer|exists:employees,id',
            'rate_total' => 'sometimes|numeric|min:0.25|max:2',
            'rate' => 'sometimes|numeric|min:0.25|max:2',
            'employee_position_status_id' => 'sometimes|nullable|integer|exists:employee_position_statuses,id',
            'started_at' => 'sometimes|nullable|date',
            'expected_return_at' => 'sometimes|nullable|date',
            'ended_at' => 'sometimes|nullable|date',
            'longitude' => 'required|integer',
            'latitude' => 'required|integer',
        ], [
            'name.required' => 'Название комиссариата обязательно для заполнения.',
            'name.string' => 'Название комиссариата должно быть строкой.',
            'name.min' => 'Название комиссариата должно содержать минимум 2 символа.',
            'name.max' => 'Название комиссариата не должно превышать 255 символов.',
            'chief_employee_id.exists' => 'Несуществующий сотрудник',
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
            $rateTotal = isset($data['rate_total']) ? (float)$data['rate_total'] : 1.00;
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
                $rate = isset($data['rate']) ? (float)$data['rate'] : 1.00;
                $statusId = $data['employee_position_status_id'] ?? 1; // fallback to 1
                $startedAt = $data['started_at'] ?? now()->toDateString();
                $expectedReturnAt = $data['expected_return_at'] ?? null;
                $endedAt = $data['ended_at'] ?? null;

                EmployeePosition::create([
                    'employee_id' => $chiefEmployeeId,
                    'commissariat_position_id' => $commissariatPosition->id,
                    'rate' => $rate,
                    'employee_position_status_id' => $statusId,
                    'started_at' => $startedAt,
                    'is_active' => true,
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
                    // сотрудник изменился — удаляем старое назначение и создаём новое (с выбранным статусом если есть)
                    $currentAssignment->delete();
                    $this->createChiefAssignment($chiefSlot->id, $newEmployeeId, $newStatusId);
                }
                // если новый id пустой или равен текущему — ничего не делаем
            } else {
                // назначение не было — создаём только если передан новый сотрудник
                if (! empty($newEmployeeId)) {
                    $this->createChiefAssignment($chiefSlot->id, $newEmployeeId, $newStatusId);
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
