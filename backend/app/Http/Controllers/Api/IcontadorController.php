<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\External\Icontador;

class IcontadorController extends Controller
{
    public function login()
    {
        $cliente = new Icontador();
        $token = $cliente->getToken();

        if ($token) {
            return response()->json(['token' => $token]);
        }

        return response()->json(['error' => 'No se pudo obtener el token del proveedor'], 500);
    }
}
