<?php

use App\Http\Controllers\CommissariatsController;
use App\Http\Controllers\DepartmentsController;
use App\Http\Controllers\DivisionsController;
use App\Http\Controllers\EmployeePositionsController;
use App\Http\Controllers\EmployeesController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PersonsController;
use App\Http\Controllers\PositionsController;
use App\Http\Controllers\PositionTypesController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StructureController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\WorkStatusesController;
use Illuminate\Support\Facades\Route;

// форма лоигна
Route::get("/login", [LoginController::class, "index"])->name("login");
Route::post("/login", [LoginController::class, "login"])->name("login.post");


// пользователь
Route::middleware(['auth'])->group(function () {
    Route::get("/", [StructureController::class, "index"])->name("structure.index");

    // профиль
    Route::get("/profile", [ProfileController::class, "index"])->name("profile.index");

    Route::post("/logout", [LoginController::class, "logout"])->name("logout");
});

// админ
Route::middleware(['auth', 'admin'])->group(function () {
    // users
    Route::get("/users", [UsersController::class, "index"])->name("users.index");
    Route::get("/users/create", [UsersController::class, "create"])->name("users.create");
    Route::get("/users/{id}", [UsersController::class, "show"])->name("users.show");
    Route::post("/users", [UsersController::class, "store"])->name("users.store");
    Route::get("/users/{id}/edit", [UsersController::class, "edit"])->name("users.edit");
    Route::put("/users/{id}", [UsersController::class, "update"])->name("users.update");
    Route::delete("/users/{id}", [UsersController::class, "delete"])->name("users.delete");

    // employess
    Route::get("/employees", [EmployeesController::class, "index"])->name("employees.index");
    Route::get("/employees/create", [EmployeesController::class, "create"])->name("employees.create");
    Route::post("/employees", [EmployeesController::class, "store"])->name("employees.store");
    Route::get("/employees/{id}/edit", [EmployeesController::class, "edit"])->name("employees.edit");
    Route::put("/employees/{id}", [EmployeesController::class, "update"])->name("employees.update");
    Route::delete("/employees/{id}", [EmployeesController::class, "delete"])->name("employees.delete");

    Route::get('/employees/live-search', [EmployeesController::class, 'liveSearch'])
        ->name('employees.live-search');


    // persons
    Route::get("/persons", [PersonsController::class, "index"])->name("persons.index");
    Route::get("/persons/create", [PersonsController::class, "create"])->name("persons.create");
    Route::get("/persons/{id}", [PersonsController::class, "show"])->name("persons.show");
    Route::post("/persons", [PersonsController::class, "store"])->name("persons.store");
    Route::get("/persons/{id}/edit", [PersonsController::class, "edit"])->name("persons.edit");
    Route::put("/persons/{id}", [PersonsController::class, "update"])->name("persons.update");
    Route::delete("/persons/{id}", [PersonsController::class, "delete"])->name("persons.delete");

    // work statuses
    Route::get("/work-statuses", [WorkStatusesController::class, "index"])->name("work-statuses.index");
    Route::get("/work-statuses/create", [WorkStatusesController::class, "create"])->name("work-statuses.create");
    Route::post("/work-statuses", [WorkStatusesController::class, "store"])->name("work-statuses.store");
    Route::get("/work-statuses/{id}/edit", [WorkStatusesController::class, "edit"])->name("work-statuses.edit");
    Route::put("/work-statuses/{id}", [WorkStatusesController::class, "update"])->name("work-statuses.update");
    Route::delete("/work-statuses/{id}", [WorkStatusesController::class, "delete"])->name("work-statuses.delete");

    // типы должностей
    Route::get("/position-types", [PositionTypesController::class, "index"])->name("position-types.index");
    Route::get("/position-types/create", [PositionTypesController::class, "create"])->name("position-types.create");
    Route::get("/position-types/{id}", [PositionTypesController::class, "show"])->name("position-types.show");
    Route::post("/position-types", [PositionTypesController::class, "store"])->name("position-types.store");
    Route::get("/position-types/{id}/edit", [PositionTypesController::class, "edit"])->name("position-types.edit");
    Route::put("/position-types/{id}", [PositionTypesController::class, "update"])->name("position-types.update");
    Route::delete("/position-types/{id}", [PositionTypesController::class, "delete"])->name("position-types.delete");

    // должности
    Route::get("/positions", [PositionsController::class, "index"])->name("positions.index");
    Route::get("/positions/create", [PositionsController::class, "create"])->name("positions.create");
    Route::get("/positions/{id}", [PositionsController::class, "show"])->name("positions.show");
    Route::post("/positions", [PositionsController::class, "store"])->name("positions.store");
    Route::get("/positions/{id}/edit", [PositionsController::class, "edit"])->name("positions.edit");
    Route::put("/positions/{id}", [PositionsController::class, "update"])->name("positions.update");
    Route::delete("/positions/{id}", [PositionsController::class, "delete"])->name("positions.delete");

    // комиссариаты
    Route::get("/commissariats", [CommissariatsController::class, "index"])->name("commissariats.index");
    Route::get("/commissariats/create", [CommissariatsController::class, "create"])->name("commissariats.create");
    Route::get("/commissariats/{id}", [CommissariatsController::class, "show"])->name("commissariats.show");
    Route::post("/commissariats", [CommissariatsController::class, "store"])->name("commissariats.store");
    Route::get("/commissariats/{id}/edit", [CommissariatsController::class, "edit"])->name("commissariats.edit");
    Route::put("/commissariats/{id}", [CommissariatsController::class, "update"])->name("commissariats.update");
    Route::delete("/commissariats/{id}", [CommissariatsController::class, "delete"])->name("commissariats.delete");

    // отделы
    Route::get("/departments", [DepartmentsController::class, "index"])->name("departments.index");
    Route::get("/departments/create", [DepartmentsController::class, "create"])->name("departments.create");
    Route::get("/departments/{id}", [DepartmentsController::class, "show"])->name("departments.show");
    Route::post("/departments", [DepartmentsController::class, "store"])->name("departments.store");
    Route::get("/departments/{id}/edit", [DepartmentsController::class, "edit"])->name("departments.edit");
    Route::put("/departments/{id}", [DepartmentsController::class, "update"])->name("departments.update");
    Route::delete("/departments/{id}", [DepartmentsController::class, "delete"])->name("departments.delete");

    // отделения
    Route::get("/divisions", [DivisionsController::class, "index"])->name("divisions.index");
    Route::get("/divisions/create", [DivisionsController::class, "create"])->name("divisions.create");
    Route::get("/divisions/{id}", [DivisionsController::class, "show"])->name("divisions.show");
    Route::post("/divisions", [DivisionsController::class, "store"])->name("divisions.store");
    Route::get("/divisions/{id}/edit", [DivisionsController::class, "edit"])->name("divisions.edit");
    Route::put("/divisions/{id}", [DivisionsController::class, "update"])->name("divisions.update");
    Route::delete("/divisions/{id}", [DivisionsController::class, "delete"])->name("divisions.delete");

    // назначение сотруднику должности
    Route::get("/employee-positions", [EmployeePositionsController::class, "index"])->name("employee-positions.index");
    Route::get("/employee-positions/{id}/create", [EmployeePositionsController::class, "create"])->name("employee-positions.create");
    Route::get("/employee-positions/{id}", [EmployeePositionsController::class, "show"])->name("employee-positions.show");
    Route::post("/employee-positions/{id}", [EmployeePositionsController::class, "store"])->name("employee-positions.store");
    Route::get("/employee-positions/{id}/edit", [EmployeePositionsController::class, "edit"])->name("employee-positions.edit");
    Route::put("/employee-positions/{id}", [EmployeePositionsController::class, "update"])->name("employee-positions.update");
    Route::delete("/employee-positions/{id}", [EmployeePositionsController::class, "delete"])->name("employee-positions.delete");
    Route::delete("/employee-positions/{id}/deleteAll", [EmployeePositionsController::class, "destroy"])->name("employee-positions.destroy");

    // структуры
    Route::get("/structure/{id}/commissariat", [StructureController::class, "show"])->name("structure.show");
});