<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DteEmitido;
use Illuminate\Support\Facades\Log;


class DteEmitidoController extends Controller
{
    public function listarDTE(Request $request)
    {   //consumo el models
        Log::info('0 Iniciar buscar DTE');
        $query = DteEmitido::query();
        //Si tiene id, va a mostrar el dte del id
        if ($request->has('id')) {
            $query->where('id', $request->input('id'));
        }

        if ($request->has('folio')) {//muestra por folio
            $query->where('folio', $request->input('folio'));
        }

        if ($request->has('emisor')) {//muestra por emosion
            $query->where('emisor', $request->input('emisor'));
        }

        if ($request->has('fecha')) {//muestra por la fecha
            $query->where('fecha', $request->input('fecha'));
        }

        if ($request->has('fecha_inicio') && $request->has('fecha_fin')) {
            $query->whereBetween('fecha', [
                $request->input('fecha_inicio'),//rango de fecha
                $request->input('fecha_fin')
            ]);
        }

        //genera la consulta y la devuelve en json
        return response()->json($query->get());
    }

    //la función de actualizar, solo actualiza lo que el model permite en fill
    public function actualizarDTEs(Request $request)
    {
        $datos = $request->input('datos');
        //recibe una array para que reciba multiples 
        if (!is_array($datos)) {
            return response()->json(['error' => 'Formato de datos inválido'], 400);
        }

        $resultados = [];

        foreach ($datos as $item) {
            if (!isset($item['emisor'], $item['folio'])) {
                $resultados[] = ['estado' => 'faltan emisor o folio', 'datos' => $item];
                continue;
            }

            $dte = DteEmitido::where('emisor', $item['emisor'])
                            ->where('folio', $item['folio'])
                            ->first();

            if ($dte) {
                $dte->update([
                    'iecuenta' => $item['iecuenta'] ?? $dte->iecuenta,
                    'iecodanalisis' => $item['iecodanalisis'] ?? $dte->iecodanalisis,
                    'updated_at' => now(),
                ]);
                $resultados[] = ['emisor' => $item['emisor'], 'folio' => $item['folio'], 'estado' => 'actualizado'];
            } else {
                $resultados[] = ['emisor' => $item['emisor'], 'folio' => $item['folio'], 'estado' => 'no encontrado'];
            }
        }

        return response()->json($resultados);
    }

}
