<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\External\Icontador;
use Illuminate\Support\Facades\Log;

class IcontadorController extends Controller
{
    public function login()
    {
        $cliente = new Icontador();
        $token = $cliente->getToken();
        $codigosBruto = $cliente->getCod();
        //Log::info('Respuesta completa de códigos: ' . json_encode($codigosBruto));

        if ($token) {
            return response()->json(['token' => $token]);
        }

        return response()->json(['error' => 'No se pudo obtener el token del proveedor'], 500);
    }
}
