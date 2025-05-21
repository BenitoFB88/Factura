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
            //$cuentas = $cliente->extraerCuentas($estructuraCuentas);
            $cliente->actualizarCuentas($cuentas);

            $codigosBruto = $cliente->getCod();
            $cuentaYcodigo = $cliente->separadorCuentaCodigo($codigosBruto);
            $actualizarCodigo = $cliente->actualizararCOD($cuentaYcodigo);
            $hrsExito = $codigosBruto['fecha_epoch'];

            log::info('conexion exitosa ' . $hrsExito);

            return response()->json([
                'status' => 200,
                'mensaje' => 'Actualizacion Exitosa',
                'fecha_actualizacion' => $hrsExito,
                'Nuevos codigos ' => $actualizarCodigo
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'estatus' => 400,
                'mensaje' => 'Problemas al actualizar codigos de analisis.',
                'error' => $e->getMessage()
            ]);
        }
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


    public function codigosAnalisisNoUsados()
    {
        try {
            // Paso 1: IDs de todos los códigos de análisis registrados
            $todosLosCodigos = DB::table('iecodanalises')->pluck('id')->toArray();

            // Paso 2: IDs de los códigos usados en facturas (distintos y no nulos)
            $codigosUsados = DB::table('dte_emitidos')
                ->whereNotNull('iecodanalisis')
                ->distinct()
                ->pluck('iecodanalisis')
                ->toArray();

            // Paso 3: Calcular los códigos que no se usan
            $codigosNoUsados = array_diff($todosLosCodigos, $codigosUsados);

            // Paso 4: Obtener detalles de esos códigos
            $detallesNoUsados = DB::table('iecodanalises')
                ->whereIn('id', $codigosNoUsados)
                ->get();

            return response()->json([
                'status' => 200,
                'mensaje' => 'Códigos de análisis no utilizados encontrados',
                'cantidad_no_usados' => count($detallesNoUsados),
                'codigos_no_usados' => $detallesNoUsados,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'mensaje' => 'Error al obtener los códigos de análisis no utilizados',
                'error' => $e->getMessage()
            ]);
        }
    }
}

