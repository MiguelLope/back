<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Especialista extends Model
{
    use HasFactory;

    protected $table = 'especialistas'; // Nombre de la tabla (opcional, Laravel lo infiere)
    protected $primaryKey = 'id_especialista'; // Clave primaria (necesario si no es 'id')
    protected $fillable = [ // Campos que se pueden asignar masivamente
        'id_usuario',
        'especialidad',
        'id_consultorio',
        'horario_inicio',
        'horario_fin',
        'dias_trabajo',
    ];
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }

    public function consultorio()
    {
        return $this->belongsTo(Consultorio::class, 'id_consultorio');
    }

    public function citas()
    {
        return $this->hasMany(Cita::class, 'id_especialista');
    }
}
