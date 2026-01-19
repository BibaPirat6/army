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
});

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get("/users", [UsersController::class, "index"])->name("users.index");
    Route::get("/employees", [EmployeesController::class, "index"])->name("employees.index");
});