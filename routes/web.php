<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// home
Route::get("/", [HomeController::class, "index"])->name("home.index");


// login
Route::get("/login", [UserController::class, "index"])->name("user.index");
Route::post("/login", [UserController::class, "login"])->name("user.login");