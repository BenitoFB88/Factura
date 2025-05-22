<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\External\Icontador;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;


class IcontadorController extends Controller
{
    public function actualizarcodigos()
    {
        try {
            $cliente = new Icontador();
            $cuentasBrutas = $cliente->getCuenta();
            $estructuraCuentas = $cuentasBrutas['data_cuenta']['respuesta']['data']['mi_plan_de_cuentas'] ?? [];

            $cuentas = $cliente->extraerCuentas($estructuraCuentas);
            $cliente->actualizarCuentas($cuentas);

            $codigosBruto = $cliente->getCod();
            $cuentaYcodigo = $cliente->separadorCuentaCodigo($codigosBruto);
            $actualizarCodigo = $cliente->actualizararCOD($cuentaYcodigo);
            $hrsExito = $codigosBruto['fecha_epoch'];

            \Log::info('conexion exitosa ' . $hrsExito);

            // Obtener todos los códigos después de actualizar
            $todosLosCodigos = $this->obtenerTodosCodigosAnalisis();

            return response()->json([
                'status' => 200,
                'mensaje' => 'Actualización Exitosa',
                'fecha_actualizacion' => $hrsExito,
                'nuevos_codigos' => $actualizarCodigo,
                'codigos_actualizados' => $todosLosCodigos,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'estatus' => 400,
                'mensaje' => 'Problemas al actualizar códigos de análisis.',
                'error' => $e->getMessage()
            ]);
        }
    }

    // Función privada para obtener todos los códigos de análisis
    private function obtenerTodosCodigosAnalisis()
    {
        return DB::table('iecodanalises')->get();
    }


    public function login()
    {
        $cliente = new Icontador();
        $token = $cliente->getToken();

        if ($token) {
            return response()->json(['token' => $token]);
        }

        return response()->json(['error' => 'No se pudo obtener el token del proveedor'], 500);
    }


    public function codigosAnalisisTodos()
    {
        try {
            // Obtener todos los códigos de análisis con detalles
            $todosLosCodigos = DB::table('iecodanalises')->get();

            return response()->json([
                'status' => 200,
                'mensaje' => 'Todos los códigos de análisis obtenidos',
                'cantidad' => count($todosLosCodigos),
                'codigos' => $todosLosCodigos,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'mensaje' => 'Error al obtener los códigos de análisis',
                'error' => $e->getMessage()
            ]);
        }
    }

}

