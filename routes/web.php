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
use App\Http\Controllers\UsersController;
use App\Http\Controllers\WorkStatuses;
use Illuminate\Support\Facades\Route;

// форма лоигна
Route::get("/login", [LoginController::class, "index"])->name("login");
Route::post("/login", [LoginController::class, "login"])->name("login.post");


// пользователь
Route::middleware(['auth'])->group(function () {
    Route::get("/", [HomeController::class, "index"])->name("home.index");

    // профиль
    Route::get("/profile", [ProfileController::class, "index"])->name("profile.index");
    Route::get("/profile/update", [ProfileController::class, "updateShow"])->name("profile.update.index");
    Route::put("/profile/update", [ProfileController::class, "update"])->name("profile.update.post");


    Route::post("/logout", [LoginController::class, "logout"])->name("logout");
});

// админ
Route::middleware(['auth', 'admin'])->group(function () {
    // users
    Route::get("/users", [UsersController::class, "index"])->name("users.index");
    Route::post("/users", [UsersController::class, "create"])->name("users.post");
    Route::delete("/users/{id}/delete", [UsersController::class, "delete"])->name("users.delete");
    Route::get("/users/{id}/update", [UsersController::class, "updateShow"])->name("users.update.index");
    Route::put("/users/{id}/update", [UsersController::class, "update"])->name("users.update.post");

    // employess
    Route::get("/employees", [EmployeesController::class, "index"])->name("employees.index");
    Route::post("/employees", [EmployeesController::class, "create"])->name("employees.post");
    Route::delete("/employees/{id}/delete", [EmployeesController::class, "delete"])->name("employees.delete");
    Route::get("/employees/{id}/update", [EmployeesController::class, "updateShow"])->name("employees.update.index");
    Route::put("/employees/{id}/update", [EmployeesController::class, "update"])->name("employees.update.post");

    // persons
    Route::get("/persons", [PersonsController::class, "index"])->name("persons.index");
    Route::post("/persons", [PersonsController::class, "create"])->name("persons.post");
    Route::delete("/persons/{id}/delete", [PersonsController::class, "delete"])->name("persons.delete");
    Route::get("/persons/{id}/update", [PersonsController::class, "updateShow"])->name("persons.update.index");
    Route::put("/persons/{id}/update", [PersonsController::class, "update"])->name("persons.update.post");

    // work statuses
    Route::get("/work-statuses", [WorkStatuses::class, "index"])->name("work-statuses.index");
    Route::post("/work-statuses", [WorkStatuses::class, "create"])->name("work-statuses.post");
    Route::delete("/work-statuses/{id}/delete", [WorkStatuses::class, "delete"])->name("work-statuses.delete");

    // типы должностей
    Route::get("/position-types", [PositionTypesController::class, "index"])->name("position-types.index");
    Route::get("/position-types/create", [PositionTypesController::class, "create"])->name("position-types.create");
    Route::post("/position-types", [PositionTypesController::class, "store"])->name("position-types.store");
    Route::get("/position-types/{id}/edit", [PositionTypesController::class, "edit"])->name("position-types.edit");
    Route::put("/position-types/{id}", [PositionTypesController::class, "update"])->name("position-types.update");
    Route::delete("/position-types/{id}", [PositionTypesController::class, "delete"])->name("position-types.delete");

    // должности
    Route::get("/positions", [PositionsController::class, "index"])->name("positions.index");
    Route::get("/positions/create", [PositionsController::class, "create"])->name("positions.create");
    Route::post("/positions", [PositionsController::class, "store"])->name("positions.store");
    Route::get("/positions/{id}/edit", [PositionsController::class, "edit"])->name("positions.edit");
    Route::put("/positions/{id}", [PositionsController::class, "update"])->name("positions.update");
    Route::delete("/positions/{id}", [PositionsController::class, "delete"])->name("positions.delete");

    // комиссариаты
    Route::get("/commissariats", [CommissariatsController::class, "index"])->name("commissariats.index");
    Route::get("/commissariats/create", [CommissariatsController::class, "create"])->name("commissariats.create");
    Route::post("/commissariats", [CommissariatsController::class, "store"])->name("commissariats.store");
    Route::get("/commissariats/{id}/edit", [CommissariatsController::class, "edit"])->name("commissariats.edit");
    Route::put("/commissariats/{id}", [CommissariatsController::class, "update"])->name("commissariats.update");
    Route::delete("/commissariats/{id}", [CommissariatsController::class, "delete"])->name("commissariats.delete");

    // отделы
    Route::get("/departments", [DepartmentsController::class, "index"])->name("departments.index");
    Route::get("/departments/create", [DepartmentsController::class, "create"])->name("departments.create");
    Route::post("/departments", [DepartmentsController::class, "store"])->name("departments.store");
    Route::get("/departments/{id}/edit", [DepartmentsController::class, "edit"])->name("departments.edit");
    Route::put("/departments/{id}", [DepartmentsController::class, "update"])->name("departments.update");
    Route::delete("/departments/{id}", [DepartmentsController::class, "delete"])->name("departments.delete");

    // подразделения
    Route::get("/divisions", [DivisionsController::class, "index"])->name("divisions.index");
    Route::get("/divisions/create", [DivisionsController::class, "create"])->name("divisions.create");
    Route::post("/divisions", [DivisionsController::class, "store"])->name("divisions.store");
    Route::get("/divisions/{id}/edit", [DivisionsController::class, "edit"])->name("divisions.edit");
    Route::put("/divisions/{id}", [DivisionsController::class, "update"])->name("divisions.update");
    Route::delete("/divisions/{id}", [DivisionsController::class, "delete"])->name("divisions.delete");

    // назначение сотруднику должности
    Route::get("/employee-positions", [EmployeePositionsController::class, "index"])->name("employee-positions.index");
    Route::get("/employee-positions/{id}/create", [EmployeePositionsController::class, "create"])->name("employee-positions.create");
    Route::post("/employee-positions/{id}", [EmployeePositionsController::class, "store"])->name("employee-positions.store");
});