<?php

use Illuminate\Http\Request;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\InvoiceManagerController;
use App\External\Icontador;
use App\Http\Controllers\Api\IcontadorController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Aquí puedes registrar las rutas de tu API.
| Estas rutas están protegidas con el middleware 'api' por defecto.
*/
Route::post('/registrar', [RegisterController::class, 'registroUsuario']);

Route::options('{any}', function () {
    return response()->json([], 204);
})->where('any', '.*');

// Ruta pública para login
Route::post('/login', 'Auth\LoginController@login');

Route::get('/prueba', 'ConsumoBBDDController@prueba');


//rutas protegeidas
Route::middleware(['auth:api'])->group(function () {
    Route::post('/actualizarcodigos', 'Api\IcontadorController@actualizarcodigos');

    Route::post('/actualizar-dtes', 'DteEmitidoController@actualizarDTEs');
    Route::get('/obtenerCodigos', [IcontadorController::class, 'codigosAnalisisTodos']);

    //GRUPO DE RUTAS PROTEGIDAS
// Ver todas las facturas
    Route::get('invoices', [InvoiceManagerController::class, 'index']);

    // Obtener datos del usuario autenticado
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    // Buscar facturas
    Route::get('invoices/search', [InvoiceManagerController::class, 'search']);

    // Ver una factura específica
    Route::get('invoices/{id}', [InvoiceManagerController::class, 'show']);

    Route::post('/actualizar-dtes', 'DteEmitidoController@actualizarDTEs');
    Route::post('/icontadortoken', 'Api\IcontadorController@login');
    Route::get('/buscar-dte', 'DteEmitidoController@listarDTE');
    //editar facturas


    //  Route::post('/registrar', [RegisterController::class, 'registroUsuario']);
//
});


