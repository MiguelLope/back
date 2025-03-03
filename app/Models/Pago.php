<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    use HasFactory;

    protected $table = 'pagos'; // Nombre de la tabla

    protected $primaryKey = 'id_pago'; // Clave primaria

    protected $fillable = [
        'id_cita',
        'monto',
        'metodo_pago',
        'estado'
    ];

    /**
     * Relación con la cita.
     */
    public function cita()
    {
        return $this->belongsTo(Cita::class, 'id_cita');
    }
}
