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
            'chief_employee_id' => 'required|integer|exists:employees,id',
            'longitude' => 'required|integer',
            'latitude' => 'required|integer',
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
                EmployeePosition::create([
                    'employee_id' => $chiefEmployeeId,
                    'commissariat_position_id' => $commissariatPosition->id,
                    'rate' => 1.00,
                    'employee_position_status_id' => 1, // работает
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
            'chief_employee_id' => 'required|integer|exists:employees,id',
            'old_chief_employee_id' => 'nullable|integer|exists:employees,id',
            'old_chief_employee_position_status_id' => 'nullable|integer|exists:employee_position_statuses,id',
            'longitude' => 'required|integer',
            'latitude' => 'required|integer',
        ]);

        DB::beginTransaction();

        try {
            $commissariat = Commissariat::findOrFail($id);

            // 1. Обновление комиссариата
            $commissariat->update([
                'name' => $data['name'],
                'longitude' => $data['longitude'],
                'latitude' => $data['latitude'],
            ]);

            // 2. Получаем слот начальника
            $chiefPositionRef = Position::whereHas('chiefType', function ($q) {
                $q->where('name', 'начальник комиссариата');
            })->firstOrFail();

            $chiefSlot = CommissariatPosition::where([
                'commissariat_id' => $commissariat->id,
                'position_id' => $chiefPositionRef->id,
                'department_id' => null,
                'division_id' => null,
            ])->first();

            $oldId = (int) $data['old_chief_employee_id'];
            $newId = (int) $data['chief_employee_id'];

            // =========================
            // СЦЕНАРИЙ 1: статус старого
            // =========================
            if ($oldId && $oldId === $newId) {
                EmployeePosition::where('commissariat_position_id', $chiefSlot->id)
                    ->where('employee_id', $oldId)
                    ->update([
                        'employee_position_status_id' => $data['employee_position_status_id'] ?? 1,
                    ]);

                DB::commit();

                $backUrl = $request->get('backUrl', route('commissariats.index'));

                return redirect()->to($backUrl)->with('success', 'Комиссариат успешно обновлен.');
            }

            // =========================
            // СЦЕНАРИЙ 2: замена начальника
            // =========================
            if ($oldId && $oldId !== $newId) {

                // старому ставим статус увольнения/перевода/и т.д.
                if (! empty($data['old_chief_employee_position_status_id'])) {
                    EmployeePosition::where('commissariat_position_id', $chiefSlot->id)
                        ->where('employee_id', $oldId)
                        ->update([
                            'employee_position_status_id' => $data['old_chief_employee_position_status_id'],
                        ]);
                }

                // новому назначение
                EmployeePosition::updateOrCreate(
                    [
                        'commissariat_position_id' => $chiefSlot->id,
                        'employee_id' => $newId,
                    ],
                    [
                        'rate' => 1.00,
                        'employee_position_status_id' => $data['employee_position_status_id'] ?? 1,
                    ]
                );
            }

            DB::commit();

            $backUrl = $request->get('backUrl', route('commissariats.index'));

            return redirect()->to($backUrl)->with('success', 'Комиссариат успешно обновлен.');

        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withErrors([
                'error' => 'Ошибка обновления: '.$e->getMessage(),
            ])->withInput();
        }
    }

    public function delete($id)
    {
        $commissariat = Commissariat::findOrFail($id);
        $commissariat->delete();

        return redirect()->route('commissariats.index')->with('success', 'Комиссариат успешно удален.');
    }
}
