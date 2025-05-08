<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DteEmitido;
use Illuminate\Support\Facades\Log;


class DteEmitidoController extends Controller
{
    public function listarDTE(Request $request)
    {
        // Iniciar log para depuración
        Log::info('0 Iniciar buscar DTE');

        $query = DteEmitido::query();

        // Buscar por ID
        if ($request->filled('id')) {
            $query->where('id', $request->input('id'));
        }

        // Buscar por folio
        if ($request->filled('folio')) {
            $query->where('folio', $request->input('folio'));
        }

        // Buscar por emisor
        if ($request->filled('emisor')) {
            $query->where('emisor', $request->input('emisor'));
        }

        // Buscar por fecha exacta
        if ($request->filled('fecha')) {
            $query->where('fecha', $request->input('fecha'));
        }

        // Buscar por fecha de inicio (mayor o igual)
        if ($request->filled('fecha_inicio')) {
            $query->where('fecha', '>=', $request->input('fecha_inicio'));
        }

        // Buscar por fecha de fin (menor o igual)
        if ($request->filled('fecha_fin')) {
            $query->where('fecha', '<=', $request->input('fecha_fin'));
        }

        // Si se especifican ambas fechas (inicio y fin), se buscan facturas dentro del rango
        if ($request->filled('fecha_inicio') && $request->filled('fecha_fin')) {
            $query->whereBetween('fecha', [
                $request->input('fecha_inicio'),
                $request->input('fecha_fin')
            ]);
        }

        // Ordenar los resultados por fecha descendente
        $query->orderBy('fecha', 'desc');

        // Ejecutar la consulta y devolver el resultado en formato JSON
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
