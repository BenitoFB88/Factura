<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\External\Icontador;
use Illuminate\Support\Facades\Log;

class IcontadorController extends Controller
{
    public function actualizarcodigos()
    {
       try{

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
        
        log::info('conexion exitosa '.$hrsExito);
    
            return response()->json([ 'status' => 200,
                                    'mensaje'=>'Actualizacion Exitosa',
                                    'fecha_actualizacion'=> $hrsExito,
                                    'Nuevos codigos '=>$actualizarCodigo]);
       }catch(\Exception $e){
            return response()->json([ 'estatus' => 400,
                                    'mensaje'=> 'Problemas al actualizar codigos de analisis.',
                                    'error'=>$e->getMessage()]);
       }
    }
    public function login()
    {
        $cliente = new Icontador();
        $token = $cliente->getToken();
    
        if($token){
            return response()->json(['token'=>$token]);
        }
            return response()->json(['error'=>'No se pudo obtener el token'],500);
    }
    
}

