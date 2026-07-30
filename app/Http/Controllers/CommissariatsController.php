<?php

namespace App\Http\Controllers;

use App\DTO\CommissariatFiltersData;
use App\Filters\CommissariatFilter;
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
    public function index(Request $request)
    {
        $filters = CommissariatFiltersData::fromRequest($request);

        $commissariats = Commissariat::query()
            ->with([
                'chiefCommissariatPosition' => function ($query) {
                    $query->with([
                        'activeAssignment' => function ($query) {
                            $query->with('employee.person');
                        },
                    ]);
                },
                'commissariatPositions' => function ($query) {
                    $query->withCount('activeAssignment');
                },
            ])
            ->filter(new CommissariatFilter($filters))
            ->paginate(15)
            ->withQueryString();

        return view(
            'admin.org.commissariats.index',
            compact('commissariats', 'filters')
        );
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
            'chief_employee_id' => 'nullable|integer|exists:employees,id',
            'longitude' => 'required|integer',
            'latitude' => 'required|integer',
        ]);

        DB::beginTransaction();
        try {
            $commissariat = Commissariat::create([
                'name' => $data['name'],
                'longitude' => $data['longitude'],
                'latitude' => $data['latitude'],
            ]);

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

            if (! empty($data['chief_employee_id'])) {
                EmployeePosition::create([
                    'employee_id' => $data['chief_employee_id'],
                    'commissariat_position_id' => $commissariatPosition->id,
                    'rate' => 1.00,
                    'employee_position_status_id' => 1,
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
            'chief_employee_id' => 'nullable|integer|exists:employees,id',
            'old_chief_employee_id' => 'nullable|integer|exists:employees,id',
            'old_chief_employee_position_status_id' => 'nullable|integer|exists:employee_position_statuses,id',
            'longitude' => 'required|integer',
            'latitude' => 'required|integer',
        ]);

        DB::beginTransaction();

        try {
            $commissariat = Commissariat::findOrFail($id);

            // 1. Обновление основных данных комиссариата
            $commissariat->update([
                'name' => $data['name'],
                'longitude' => $data['longitude'],
                'latitude' => $data['latitude'],
            ]);

            // 2. Находим или создаем должность "начальник комиссариата"
            $chiefPositionRef = Position::whereHas('chiefType', function ($q) {
                $q->where('name', 'начальник комиссариата');
            })->firstOrFail();

            // Ищем существующий слот должности начальника
            $chiefSlot = CommissariatPosition::where([
                'commissariat_id' => $commissariat->id,
                'position_id' => $chiefPositionRef->id,
                'department_id' => null,
                'division_id' => null,
            ])->first();

            // Если слота нет — создаем его (штатная должность всегда должна существовать)
            if (! $chiefSlot) {
                $chiefSlot = CommissariatPosition::create([
                    'commissariat_id' => $commissariat->id,
                    'position_id' => $chiefPositionRef->id,
                    'rate_total' => 1.00,
                    'is_independent' => false,
                ]);
            }

            $oldId = $data['old_chief_employee_id'] ? (int) $data['old_chief_employee_id'] : null;
            $newId = $data['chief_employee_id'] ? (int) $data['chief_employee_id'] : null;

            // СЦЕНАРИЙ 1: Ничего не изменилось (был тот же или оба пустые)
            if ($oldId === $newId) {
                // Если начальник есть — просто обновляем статус если передан
                if ($newId && isset($data['employee_position_status_id'])) {
                    EmployeePosition::where('commissariat_position_id', $chiefSlot->id)
                        ->where('employee_id', $newId)
                        ->update([
                            'employee_position_status_id' => $data['employee_position_status_id'],
                        ]);
                }

                DB::commit();

                return redirect()->to($request->get('backUrl', route('commissariats.index')))
                    ->with('success', 'Комиссариат успешно обновлен.');
            }

            // СЦЕНАРИЙ 2: Снятие начальника (был -> стал пусто)
            if ($oldId && ! $newId) {
                // Меняем статус старому начальнику
                if (! empty($data['old_chief_employee_position_status_id'])) {
                    EmployeePosition::where('commissariat_position_id', $chiefSlot->id)
                        ->where('employee_id', $oldId)
                        ->update([
                            'employee_position_status_id' => $data['old_chief_employee_position_status_id'],
                        ]);
                }

                DB::commit();

                return redirect()->to($request->get('backUrl', route('commissariats.index')))
                    ->with('success', 'Комиссариат успешно обновлен.');
            }

            // СЦЕНАРИЙ 3: Назначение нового начальника (пусто -> стал)
            if (! $oldId && $newId) {
                // Деактивируем все предыдущие назначения на эту должность (на всякий случай)
                EmployeePosition::where('commissariat_position_id', $chiefSlot->id)
                    ->update([
                        'employee_position_status_id' => 2, // или другой статус "не работает"
                    ]);

                // Создаем новое назначение
                EmployeePosition::create([
                    'employee_id' => $newId,
                    'commissariat_position_id' => $chiefSlot->id,
                    'rate' => 1.00,
                    'employee_position_status_id' => 1, // работает
                ]);

                DB::commit();

                return redirect()->to($request->get('backUrl', route('commissariats.index')))
                    ->with('success', 'Комиссариат успешно обновлен.');
            }

            // СЦЕНАРИЙ 4: Замена начальника (был один -> стал другой)
            if ($oldId && $newId && $oldId !== $newId) {
                // Меняем статус старому начальнику
                if (! empty($data['old_chief_employee_position_status_id'])) {
                    EmployeePosition::where('commissariat_position_id', $chiefSlot->id)
                        ->where('employee_id', $oldId)
                        ->update([
                            'employee_position_status_id' => $data['old_chief_employee_position_status_id'],
                        ]);
                }

                // Создаем новое назначение
                EmployeePosition::create([
                    'employee_id' => $newId,
                    'commissariat_position_id' => $chiefSlot->id,
                    'rate' => 1.00,
                    'employee_position_status_id' => 1, // работает
                ]);

                DB::commit();

                return redirect()->to($request->get('backUrl', route('commissariats.index')))
                    ->with('success', 'Комиссариат успешно обновлен.');
            }

            // На всякий случай (если ни одно условие не сработало)
            DB::commit();

            return redirect()->to($request->get('backUrl', route('commissariats.index')))
                ->with('success', 'Комиссариат успешно обновлен.');

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
