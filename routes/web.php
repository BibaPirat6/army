<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get("/", [UserController::class, "index"])->name("user.index");
Route::get("/login", function () {
    return view("login");
})->name("login");