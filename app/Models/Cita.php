<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cita extends Model
{
    use HasFactory;

    protected $table = 'citas';
    protected $primaryKey = 'id_cita'; // Definir la clave primaria

    protected $fillable = [
        'id_usuario', 'id_especialista', 'id_consultorio', 'fecha_hora', 'estado', 'motivo'
    ];

    public function paciente()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }

    public function especialista()
    {
        return $this->belongsTo(Especialista::class, 'id_especialista');
    }

    public function consultorio()
    {
        return $this->belongsTo(Consultorio::class, 'id_consultorio');
    }

    public function historialMedico()
    {
        return $this->hasOne(HistorialMedico::class, 'id_cita');
    }
}

