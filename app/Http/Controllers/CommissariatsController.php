<?php

namespace App\Http\Controllers;

use App\Models\ChiefType;
use App\Models\Commissariat;
use App\Models\CommissariatPosition;
use App\Models\Employee;
use App\Models\EmployeePosition;
use App\Models\EmployeePositionStatus;
use App\Models\Person;
use App\Models\Position;
use Illuminate\Http\Request;

class CommissariatsController extends Controller
{
    public function index()
    {
        $commissariats = Commissariat::with('chiefEmployee')->paginate(50);

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
            'chief_employee_id' => 'required|integer|min:1|exists:employees,id',
            'rate_total' => 'required',
            'longitude' => 'required|integer',
            'latitude' => 'required|integer',
        ], [
            'name.required' => 'Название комиссариата обязательно для заполнения.',
            'name.string' => 'Название комиссариата должно быть строкой.',
            'name.min' => 'Название комиссариата должно содержать минимум 2 символа.',
            'name.max' => 'Название комиссариата не должно превышать 255 символов.',
            'chief_employee_id.exists' => 'Несуществующий сотрудник',
        ]);

        // 1) создаем комиссариат
        $commissariat = Commissariat::create($data);
        $commissariat->refresh();

        $chiefTypeId = ChiefType::where('name', 'начальник комиссариата')->value('id');
        $positionId = Position::where('chief_type_id', $chiefTypeId)->value('id');

        $commissariatPosition = CommissariatPosition::create([
            'commissariat_id' => $commissariat->id,
            'position_id' => $positionId,
            'department_id' => null,
            'division_id' => null,
            'rate_total' => $data['rate_total'],
        ]);

        // 3) назначаем эту должность сотруднику (employee_positions)
        EmployeePosition::updateOrCreate(
            [
                'employee_id' => $data['chief_employee_id'],
                'commissariat_position_id' => $commissariatPosition->id,
            ],
            [
                'employee_position_status_id' => EmployeePositionStatus::where('name', 'занят')->value('id'),
                'rate' => 1,
                'is_independent' => 0,
            ]
        );

        $backUrl = $request->get('backUrl', route('commissariats.index'));

        return redirect()->to($backUrl)->with('success', 'Комиссариат успешно создан.');
    }

    public function edit(Request $request, $id)
    {
        $commissariat = Commissariat::with('chiefEmployee.person')->findOrFail($id);
        $employees = Employee::all();


        $employeePositions = EmployeePosition::whereHas('commissariatPosition', function ($q) use ($commissariat) {
            $q->where('commissariat_id', $commissariat->id);
        })->with('employee')->get();

        // это для дивизий и отделов, если будет нужно
        // Подгружаем только позиции, у которых chiefType.name = 'Начальник комиссариата'
        // $positions = Position::whereHas('chiefType', function ($q) {
        //     $q->where('name', 'Начальник комиссариата');
        // })->get();

        $backUrl = $request->input('back_url');

        return view('admin.org.commissariats.edit', compact('commissariat', 'employees', 'backUrl'));
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'name' => 'required|string|min:2|max:255',
            'chief_employee_id' => 'nullable|integer|min:1|exists:employees,id',
            'rate_total' => 'required',
            'longitude' => 'nullable|sometimes|integer',
            'latitude' => 'nullable|sometimes|integer',
        ], [
            'name.required' => 'Название комиссариата обязательно для заполнения.',
            'name.string' => 'Название комиссариата должно быть строкой.',
            'name.min' => 'Название комиссариата должно содержать минимум 2 символа.',
            'name.max' => 'Название комиссариата не должно превышать 255 символов.',
            'chief_employee_id.exists' => 'Несуществующий сотрудник',
        ]);

        $commissariat = Commissariat::findOrFail($id);


        // Обновляем комиссариат
        $commissariat->update([
            'name' => $data['name'],
            'chief_employee_id' => $data['chief_employee_id'], 
            'longitude' => $data['longitude'],
            'latitude' => $data['latitude'],
        ]);


        $backUrl = $request->get('backUrl', route('commissariats.index'));

        return redirect()->to($backUrl)
            ->with('success', 'Комиссариат успешно обновлен.');

    }

    public function delete($id)
    {
        $commissariat = Commissariat::findOrFail($id);
        $commissariat->delete();

        return redirect()->route('commissariats.index')->with('success', 'Комиссариат успешно удален.');
    }
}
