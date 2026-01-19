<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get("/", [UserController::class, "index"])->name("auth.index");
Route::post("/auth", [UserController::class, "auth"])->name("auth.post");