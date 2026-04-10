<?php

namespace App\Http\Controllers;

use App\Models\Commissariat;
use App\Models\CommissariatPosition;
use App\Models\Employee;
use App\Models\EmployeePosition;
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

        $backUrl = $request->input('back_url');
        $x = $request->input('x');
        $y = $request->input('y');

        return view('admin.org.commissariats.create', compact('employees', 'backUrl', 'x', 'y'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|min:2|max:255',
            'chief_employee_id' => 'sometimes|nullable|integer|exists:employees,id',
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
            $commissariatPosition = CommissariatPosition::create([
                'commissariat_id' => $commissariat->id,
                'position_id' => $chiefPositionRef->id,
            ]);
            $commissariatPosition->refresh();

            // 4) Если выбран начальник — создаём назначение
            $chiefEmployeeId = $data['chief_employee_id'] ?? null;
            if (!empty($chiefEmployeeId)) {
                EmployeePosition::create([
                    'employee_id' => $chiefEmployeeId,
                    'commissariat_position_id' => $commissariatPosition->id,
                    'rate' => 1.00,
                    'employee_position_status_id' => 1, // ID статуса "работает" — проверьте в вашей БД!
                    'started_at' => now()->toDateString(),
                    'is_active' => true,
                    'ended_at' => null,
                    'expected_return_at' => null,
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

        $backUrl = $request->input('back_url');

        return view('admin.org.commissariats.edit', compact('commissariat', 'employees', 'backUrl'));
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'name' => 'required|string|min:2|max:255',
            'chief_employee_id' => 'required|integer|exists:employees,id',
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
            $currentAssignment = EmployeePosition::where('commissariat_position_id', $chiefSlot->id)
                ->where('is_active', true)
                ->whereNull('ended_at')
                ->first();

            $newEmployeeId = $data['chief_employee_id'];
            // 5) 🔄 Логика смены начальника
            if ($currentAssignment) {
                if ($currentAssignment->employee_id != $newEmployeeId) {
                    // ❌ Сотрудник изменился — удаляем старое назначение полностью
                    $currentAssignment->delete();

                    // ✅ Создаём новое назначение
                    $this->createChiefAssignment($chiefSlot->id, $newEmployeeId);
                }
                // Если сотрудник тот же — ничего не делаем
            } else {
                // ✅ Назначения не было — создаём новое
                $this->createChiefAssignment($chiefSlot->id, $newEmployeeId);
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
    private function createChiefAssignment(int $commissariatPositionId, int $employeeId): void
    {
        EmployeePosition::create([
            'commissariat_position_id' => $commissariatPositionId, // ✅ Ключевое поле!
            'employee_id' => $employeeId,
            'rate' => 1.00,
            'employee_position_status_id' => 1, // ID статуса "работает" — проверьте в вашей БД!
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
