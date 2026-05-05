<?php

use App\Http\Controllers\AssignEmployeeController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\CalendarFileController;
use App\Http\Controllers\CommissariatPositionsController;
use App\Http\Controllers\CommissariatsController;
use App\Http\Controllers\DepartmentsController;
use App\Http\Controllers\DivisionsController;
use App\Http\Controllers\EmployeePositionsController;
use App\Http\Controllers\EmployeesController;
use App\Http\Controllers\ExcelExportController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\MatrixController;
use App\Http\Controllers\PersonsColumnsController;
use App\Http\Controllers\PositionsController;
use App\Http\Controllers\PositionTypesController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StructureController;
use App\Http\Controllers\SubtaskController;
use Illuminate\Support\Facades\Route;

// форма лоигна
Route::get('/login', [LoginController::class, 'index'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');

// пользователь
Route::middleware(['auth'])->group(function () {
    Route::get('/', [StructureController::class, 'index'])->name('structure.index');

    // профиль
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');

    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});

// админ
Route::middleware(['auth', 'admin'])->group(function () {
    // employess
    Route::get('/employees', [EmployeesController::class, 'index'])->name('employees.index');
    Route::get('/employees/create', [EmployeesController::class, 'create'])->name('employees.create');
    Route::get('/employees/{id}', [EmployeesController::class, 'show'])->name('employees.show');
    Route::post('/employees', [EmployeesController::class, 'store'])->name('employees.store');
    Route::get('/employees/{id}/edit', [EmployeesController::class, 'edit'])->name('employees.edit');
    Route::put('/employees/{id}', [EmployeesController::class, 'update'])->name('employees.update');
    Route::delete('/employees/{id}', [EmployeesController::class, 'delete'])->name('employees.delete');

    // persons columns
    Route::get('/persons-columns', [PersonsColumnsController::class, 'index'])->name('persons-columns.index');
    Route::get('/persons-columns/create', [PersonsColumnsController::class, 'create'])->name('persons-columns.create');
    Route::post('/persons-columns', [PersonsColumnsController::class, 'store'])->name('persons-columns.store');
    Route::get('/persons-columns/{id}/edit', [PersonsColumnsController::class, 'edit'])->name('persons-columns.edit');
    Route::put('/persons-columns/{id}', [PersonsColumnsController::class, 'update'])->name('persons-columns.update');
    Route::delete('/persons-columns/{id}', [PersonsColumnsController::class, 'delete'])->name('persons-columns.delete');

    // типы должностей
    Route::get('/position-types', [PositionTypesController::class, 'index'])->name('position-types.index');
    Route::get('/position-types/create', [PositionTypesController::class, 'create'])->name('position-types.create');
    Route::get('/position-types/{id}', [PositionTypesController::class, 'show'])->name('position-types.show');
    Route::post('/position-types', [PositionTypesController::class, 'store'])->name('position-types.store');
    Route::get('/position-types/{id}/edit', [PositionTypesController::class, 'edit'])->name('position-types.edit');
    Route::put('/position-types/{id}', [PositionTypesController::class, 'update'])->name('position-types.update');
    Route::delete('/position-types/{id}', [PositionTypesController::class, 'delete'])->name('position-types.delete');

    // должности
    Route::get('/positions', [PositionsController::class, 'index'])->name('positions.index');
    Route::get('/positions/create', [PositionsController::class, 'create'])->name('positions.create');
    Route::get('/positions/{id}', [PositionsController::class, 'show'])->name('positions.show');
    Route::post('/positions', [PositionsController::class, 'store'])->name('positions.store');
    Route::get('/positions/{id}/edit', [PositionsController::class, 'edit'])->name('positions.edit');
    Route::put('/positions/{id}', [PositionsController::class, 'update'])->name('positions.update');
    Route::delete('/positions/{id}', [PositionsController::class, 'delete'])->name('positions.delete');

    // комиссариаты
    Route::get('/commissariats', [CommissariatsController::class, 'index'])->name('commissariats.index');
    Route::get('/commissariats/create', [CommissariatsController::class, 'create'])->name('commissariats.create');
    Route::get('/commissariats/{id}', [CommissariatsController::class, 'show'])->name('commissariats.show');
    Route::post('/commissariats', [CommissariatsController::class, 'store'])->name('commissariats.store');
    Route::get('/commissariats/{id}/edit', [CommissariatsController::class, 'edit'])->name('commissariats.edit');
    Route::put('/commissariats/{id}', [CommissariatsController::class, 'update'])->name('commissariats.update');
    Route::delete('/commissariats/{id}', [CommissariatsController::class, 'delete'])->name('commissariats.delete');

    // отделы
    Route::get('/departments', [DepartmentsController::class, 'index'])->name('departments.index');
    Route::get('/departments/create', [DepartmentsController::class, 'create'])->name('departments.create');
    Route::get('/departments/{id}', [DepartmentsController::class, 'show'])->name('departments.show');
    Route::post('/departments', [DepartmentsController::class, 'store'])->name('departments.store');
    Route::get('/departments/{id}/edit', [DepartmentsController::class, 'edit'])->name('departments.edit');
    Route::put('/departments/{id}', [DepartmentsController::class, 'update'])->name('departments.update');
    Route::delete('/departments/{id}', [DepartmentsController::class, 'delete'])->name('departments.delete');

    // отделения
    Route::get('/divisions', [DivisionsController::class, 'index'])->name('divisions.index');
    Route::get('/divisions/create', [DivisionsController::class, 'create'])->name('divisions.create');
    Route::get('/divisions/{id}', [DivisionsController::class, 'show'])->name('divisions.show');
    Route::post('/divisions', [DivisionsController::class, 'store'])->name('divisions.store');
    Route::get('/divisions/{id}/edit', [DivisionsController::class, 'edit'])->name('divisions.edit');
    Route::put('/divisions/{id}', [DivisionsController::class, 'update'])->name('divisions.update');
    Route::delete('/divisions/{id}', [DivisionsController::class, 'delete'])->name('divisions.delete');

    // добавить некое назначение какомуто сотруднику
    Route::get('/employee-positions/{id}/create', [EmployeePositionsController::class, 'create'])->name('employee-positions.create');

    // штатные должности в комиссариате
    Route::prefix('commissariat-positions')->name('commissariat-positions.')->group(function () {
        Route::get('/', [CommissariatPositionsController::class, 'index'])->name('index');
        Route::get('/create', [CommissariatPositionsController::class, 'create'])->name('create');
        Route::get('/{id}', [CommissariatPositionsController::class, 'show'])->name('show');
        Route::post('/', [CommissariatPositionsController::class, 'store'])->name('store');
        Route::delete('/{id}', [CommissariatPositionsController::class, 'delete'])->name('delete');

        // добавление сотрудников (более семантичное название)
        Route::get('/{id}/assign-employee/create', [AssignEmployeeController::class, 'create'])->name('assign.create');
        Route::post('/{id}/assign-employee', [AssignEmployeeController::class, 'store'])->name('assign.store');
        Route::get('/{id}/assign-employee/{employeePositionId}/edit', [AssignEmployeeController::class, 'edit'])->name('assign.edit');
        Route::put('/{id}/assign-employee/{employeePositionId}', [AssignEmployeeController::class, 'update'])->name('assign.update');
        Route::delete('/{id}/assign-employee/{employeePositionId}', [AssignEmployeeController::class, 'delete'])->name('assign.delete');
    });

    // структуры
    Route::get('/structure/{id}/commissariat', [StructureController::class, 'show'])->name('structure.show');
    Route::get('/structure/{id}/obsidian', [StructureController::class, 'obsidian'])->name('structure.obsidian');

    // excel
    Route::prefix('excel-export')->name('excel-export.')->group(function () {
        Route::get('/', [ExcelExportController::class, 'index'])->name('index');
        Route::get('/employee', [ExcelExportController::class, 'employee'])->name('employee');
        Route::post('/structure', [ExcelExportController::class, 'structure'])->name('structure');
    });

    // графики
    Route::prefix('calendar')->name('calendar.')->group(function () {
        Route::get('/', [CalendarController::class, 'index'])->name('index');
        Route::get('/tasks/{task}', [CalendarController::class, 'show'])->name('show')
            ->where('task', '[0-9]+');
        Route::get('/events', [CalendarController::class, 'events'])->name('events');
        Route::post('/tasks', [CalendarController::class, 'store'])->name('tasks.store');
        Route::put('/tasks/{task}', [CalendarController::class, 'update'])->name('tasks.update');
        Route::get('/tasks/{task}/files', [CalendarController::class, 'getFiles'])->name('tasks.files');
        // destroy еще не сделан, можно еще сделать удаление и отметка что задача выполнена, также архив
        Route::delete('/tasks/{task}', [CalendarController::class, 'destroy'])->name('tasks.destroy');

        Route::delete('/files/{file}', [CalendarFileController::class, 'destroy'])->name('files.destroy');

        // Подзадачи (CRUD)
        Route::prefix('tasks/{task}/subtasks')->name('subtasks.')->group(function () {
            Route::get('/', [SubtaskController::class, 'index'])->name('index');
            Route::get('/{subtask}', [SubtaskController::class, 'show'])->name('show');
            Route::post('/', [SubtaskController::class, 'store'])->name('store');
            Route::put('/{subtask}', [SubtaskController::class, 'update'])->name('update')
                ->where('subtask', '[0-9]+');
            Route::delete('/{subtask}', [SubtaskController::class, 'destroy'])->name('destroy')
                ->where('subtask', '[0-9]+');
        });

        // Матрица сотрудников
        Route::prefix('matrix')->name('matrix.')->group(function () {
            Route::get('/{commissariat}/{department?}/{division?}', [MatrixController::class, 'index'])
                ->name('index');
        });
    });

});
