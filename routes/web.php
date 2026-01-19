<?php

use App\Http\Controllers\BasicController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get("/login", [UserController::class, "index"])->name("login");
Route::post("/login", [UserController::class, "login"])->name("login.post");

Route::middleware(['auth'])->group(function () {
    Route::get("/", [BasicController::class, "index"])->name("home.index");
});