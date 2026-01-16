<?php

use App\Http\Controllers\BasicController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Маршруты авторизации
Route::get("/", [BasicController::class, "index"])->name("login");
Route::post("/login", [BasicController::class, "login"])->name("login.post");
Route::post("/logout", [BasicController::class, "logout"])->name("logout");

// Защищенные маршруты (требуют авторизации)
Route::middleware('auth')->group(function () {
    Route::get("/structure", [UserController::class, "index"])->name("structure");
    Route::get("/profile", [UserController::class, "profile"])->name("profile");
});