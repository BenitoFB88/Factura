<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DteEmitido extends Model
{
    protected $table = 'dte_emitidos';

     // estos son los campos que se pueden editar 
     //hay que ver si son mas pero creo que solo estos
     protected $fillable = [
        'iecuenta',
        'iecodanalisis',
        'updated_at',
    ];

    //Cast formatea los datos 
    protected $casts = [
        'fecha' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'anulado' => 'boolean',
        'iecuenta' => 'integer',
        'iecodanalisis' => 'integer',
    ];

//TODO ver esto
    // Relación con el usuario que emite
    // public function emisorUser()
    // {
    //     return $this->belongsTo(User::class, 'emite_id');
    // }
}
