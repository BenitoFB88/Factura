<?php

namespace App\Http\Controllers;

use App\Models\DteEmitido;  // Modelo para la tabla dte_emitidos
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InvoiceManagerController extends Controller
{
    // Constructor para aplicar middleware de autenticación
    public function __construct()
    {
        $this->middleware('auth:api');  // Validación de token
    }

    // Mostrar todas las facturas
    public function index()
    {
        $invoices = DteEmitido::orderBy('fecha', 'desc')->get();
        return response()->json($invoices);
    }

    // Buscar facturas por fecha, cliente y número de factura
    public function search(Request $request)
    {
        // Validación de los parámetros
        $validator = Validator::make($request->all(), [
            'fecha' => 'nullable|date',
            'cliente' => 'nullable|string',
            'numero_factura' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Datos inválidos'], 400);
        }

        // Inicialización de la consulta
        $query = DteEmitido::query();

        // Agregar la fecha solo si está presente
        if ($request->filled('fecha')) {
            $query->whereDate('fecha', $request->fecha);
        }

        // Agregar el filtro por cliente si está presente
        if ($request->filled('cliente')) {
            $query->where('receptor', 'like', '%' . $request->cliente . '%');
        }

        // Agregar el filtro por número de factura si está presente
        if ($request->filled('numero_factura')) {
            // Verifica que se esté pasando el valor de numero_factura correctamente
            \Log::debug('Número de factura recibido:', [$request->numero_factura]);

            $query->where('folio', $request->numero_factura);
        }

        // Obtener todos los resultados sin paginación
        $invoices = $query->orderBy('fecha', 'desc')->get();

        // Retorna todas las facturas sin paginación
        return response()->json($invoices);
    }

    // Mostrar una factura específica por su ID
    public function show($id)
    {
        $invoice = DteEmitido::find($id);

        if (!$invoice) {
            return response()->json(['error' => 'Factura no encontrada'], 404);
        }

        return response()->json($invoice);
    }

    // Crear una nueva factura (este es un ejemplo básico)
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tipo_dte' => 'required|integer',
            'emisor' => 'required|string|max:255',
            'folio' => 'required|integer|unique:dte_emitidos',
            'fecha' => 'required|date',
            'receptor' => 'required|string|max:255',
            'neto' => 'required|integer',
            'iva' => 'required|integer',
            'total' => 'required|integer',
            'xml' => 'required|string',
            'track_id' => 'required|integer',
            'revision_estado' => 'required|string|max:255',
            'anulado' => 'required|boolean',
            'id_user' => 'required|integer',
            'fma_pago' => 'required|integer',
            'estado_pago' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $invoice = DteEmitido::create($request->all());

        return response()->json($invoice, 201);
    }
}
