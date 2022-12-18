<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PasswordResetController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post("login",[AuthController::class,"login"]);
Route::post("register",[AuthController::class,"register"]);
Route::post("logout",[AuthController::class,"logout"]);

Route::post("generate-code",[PasswordResetController::class,"generateCode"]);
Route::post("code-check",[PasswordResetController::class,"codeCheck"]);
Route::post("reset-password",[PasswordResetController::class,"resetPassword"]);

Route::get("category/manage", [CategoryController::class,"index"]);
Route::get("category/add_edit/{id}", [CategoryController::class,"add_edit"]);
Route::post("category/save", [CategoryController::class,"save"]);
Route::get("category/delete/{id}", [CategoryController::class,"delete"]);