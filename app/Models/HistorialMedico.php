<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistorialMedico extends Model
{
    use HasFactory;

    protected $table = 'historial_medico';
    protected $primaryKey = 'id_historial';

    protected $fillable = [
        'id_usuario', 'id_especialista', 'id_cita', 'diagnostico', 'tratamiento', 'fecha'
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }

    public function especialista()
    {
        return $this->belongsTo(Especialista::class, 'id_especialista');
    }

    public function cita()
    {
        return $this->belongsTo(Cita::class, 'id_cita');
    }
}
