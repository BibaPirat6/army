<?php

use App\Http\Controllers\BasicController;
use App\Http\Controllers\EmployeesController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UsersController;
use Illuminate\Support\Facades\Route;

Route::get("/login", [UserController::class, "index"])->name("login");
Route::post("/login", [UserController::class, "login"])->name("login.post");

Route::middleware(['auth'])->group(function () {
    Route::get("/", [BasicController::class, "index"])->name("home.index");

    Route::post("/logout", [UserController::class, "logout"])->name("logout");
});

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get("/users", [UsersController::class, "index"])->name("users.index");
    Route::post("/users", [UsersController::class, "create"])->name("users.post");
    Route::post("/users/{id}/delete", [UsersController::class, "delete"])->name("users.delete");
    Route::get("/users/{id}/update", [UsersController::class, "updateShow"])->name("users.update.index");
    Route::post("/users/{id}/update", [UsersController::class, "update"])->name("users.update.post");

    Route::get("/employees", [EmployeesController::class, "index"])->name("employees.index");
});