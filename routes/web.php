<?php

use App\Http\Controllers\EmployeesController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UsersController;
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
    Route::get("/users", [UsersController::class, "index"])->name("users.index");
    Route::post("/users", [UsersController::class, "create"])->name("users.post");
    Route::post("/users/{id}/delete", [UsersController::class, "delete"])->name("users.delete");
    Route::get("/users/{id}/update", [UsersController::class, "updateShow"])->name("users.update.index");
    Route::post("/users/{id}/update", [UsersController::class, "update"])->name("users.update.post");

    Route::get("/employees", [EmployeesController::class, "index"])->name("employees.index");
    Route::post("/employees", [EmployeesController::class, "create"])->name("employees.post");
    Route::post("/employees/{id}/delete", [EmployeesController::class, "delete"])->name("employees.delete");
    Route::get("/employees/{id}/update", [EmployeesController::class, "updateShow"])->name("employees.update.index");
    Route::post("/employees/{id}/update", [EmployeesController::class, "update"])->name("employees.update.post");
});