<?php

use Illuminate\Http\Request;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\InvoiceManagerController;
use App\External\Icontador;
use App\Http\Controllers\Api\IcontadorController;

use  App\Http\Controllers\DteEmitidoController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Aquí puedes registrar las rutas de tu API.
| Estas rutas están protegidas con el middleware 'api' por defecto.
*/

Route::post('traerfolio','DteEmitidoController@listarDTEDesdeFolioAmas');

Route::options('{any}', function () {
    return response()->json([], 204);
})->where('any', '.*');


// Ruta pública para login
Route::post('/login', 'Auth\LoginController@login');

Route::get('/prueba', 'ConsumoBBDDController@prueba');
//TODO no protegidas sacar de acá
//probar login icontador:

Route::post('/icontadortoken', 'Api\IcontadorController@login');
// probar listar factura 
Route::get('/buscar-dte', 'DteEmitidoController@listarDTE');
//editar facturas
Route::post('/actualizar-dtes', 'DteEmitidoController@actualizarDTEs');
Route::get('/obtenerCodigos', [IcontadorController::class, 'codigosAnalisisTodos']);



Route::get('/hola', function () {
    return response()->json(['mensaje' => '¡Bienvenido a iHosting!']);
});
Route::post('/registrar', [RegisterController::class, 'registroUsuario']);




//rutas protegeidas
Route::middleware(['auth:api'])->group(function () {
    Route::post('/actualizarcodigos', 'Api\IcontadorController@actualizarcodigos');

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

    // Crear una nueva factura
    Route::post('invoices', [InvoiceManagerController::class, 'store']);
    Route::get('/buscar-dte', 'DteEmitidoController@listarDTE');
    Route::post('/actualizar-dtes', 'DteEmitidoController@actualizarDTEs');


    //  Route::post('/registrar', [RegisterController::class, 'registroUsuario']);
//
});


