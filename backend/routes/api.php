<?php

use Illuminate\Http\Request;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Aquí puedes registrar las rutas de tu API.
| Estas rutas están protegidas con el middleware 'api' por defecto.
*/

// Ruta pública para login
Route::post('/login', 'Auth\LoginController@login');
Route::get('/prueba', 'ConsumoBBDDController@prueba');
Route::post('/registrar', [RegisterController::class, 'registroUsuario']);


Route::get('/hola', function () {
    return response()->json(['mensaje' => '¡Bienvenido a iHosting!']);
});


//rutas protegeidas
Route::middleware(['auth:api'])->group(function () {
//GRUPO DE RUTAS PROTEGIDAS
    // Obtener datos del usuario autenticado
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Route::post('/registrar', [RegisterController::class, 'registroUsuario']);
//
});


