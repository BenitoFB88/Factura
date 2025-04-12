<?php

use Illuminate\Http\Request;
use App\Http\Controllers\Auth\ApiLoginController;


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

//Ruta Desprotegida para crear usuarios
Route::post('/registrar',[RegisterController::class, 'registrar']);
Route::post('/login', [ApiLoginController::class, 'login']);


Route::get('/prueba', 'ConsumoBBDDController@prueba');

Route::get('/hola', function () {
    return response()->json(['mensaje' => 'Hola desde Laravel 5.7 🐘']);
});

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
