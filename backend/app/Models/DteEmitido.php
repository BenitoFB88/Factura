<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DteEmitido extends Model
{

    protected $table = 'dte_emitidos';  // Nombre de la tabla

    protected $fillable = [
        'tipo_dte', 'emisor', 'folio', 'fecha', 'receptor', 'mail_receptor', 
        'mail_sii', 'neto', 'iva', 'total', 'xml', 'track_id', 'revision_estado', 
        'revision_detalle', 'anulado', 'id_user', 'fma_pago', 'estado_pago', 
        'notificacion', 'emite_id'
    ];

    // Definir relaciones, si es necesario
}
