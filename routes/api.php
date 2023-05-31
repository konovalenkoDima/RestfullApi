<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::controller(\App\Http\Controllers\UserController::class)->group(function () {
    Route::get("/token", "getToken")->name("get.token");
    Route::get("/users", "getUsers")->name("get.users");
    Route::post("/users", "createUser")->name("create.user");
    Route::get("/users/{id}", "searchUser")->name("search_user");
});

Route::get("/positions", [\App\Http\Controllers\PositionController::class, "getPositions"])->name("get.positions");
