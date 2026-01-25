<?php

namespace App\Http\Controllers;

use App\Models\WorkStatus;

use Illuminate\Http\Request;

class WorkStatusesController extends Controller
{
    public function index()
    {
        $statuses = WorkStatus::paginate(10);
        return view('admin.work-statuses.index')->with('statuses', $statuses);
    }

    public function create()
    {
        return view('admin.work-statuses.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            "name" => "required|string|min:2|unique:work_statuses,name",
            "description" => "required|string|min:2",
        ], [
            "name.required" => "Поле Название обязательно для заполнения",
            "name.min" => "Поле Название минимум 2 символа",
            "name.unique" => "Такой рабочий статус уже существует",
            "description.required" => "Поле Описание обязательно для заполнения",
            "description.min" => "Поле Описание минимум 2 символа",
        ]);

        $status = WorkStatus::create([
            'name' => $request->input('name'),
            "description" => $request->input('description'),
        ]);

        return redirect()->route("work-statuses.index")->with("success", "Рабочий статус " . $status->name . " успешно создан!");
    }

    public function edit($id)
    {
        $status = WorkStatus::findOrFail($id);
        return view('admin.work-statuses.edit')->with('status', $status);
    }
    public function update(Request $request, $id)
    {
        $status = WorkStatus::findOrFail($id);

        $request->validate([
            "name" => "required|string|min:2|unique:work_statuses,name",
            "description" => "required|string|min:2",
        ], [
            "name.required" => "Поле Название обязательно для заполнения",
            "name.min" => "Поле Название минимум 2 символа",
            "name.unique" => "Такой рабочий статус уже существует",
            "description.required" => "Поле Описание обязательно для заполнения",
            "description.min" => "Поле Описание минимум 2 символа",
        ]);

        $status->update([
            'name' => $request->input('name'),
            "description" => $request->input('description'),
        ]);


        return redirect()->route("work-statuses.index")->with("success", "Рабочий статус успешно обновлен!");
    }

    public function delete($id)
    {
        $status = WorkStatus::findOrFail($id);
        $status->delete();

        return redirect()->route("work-statuses.index")->with("success", "Рабочий статус " . $status->name . " успешно удален!");
    }
}
