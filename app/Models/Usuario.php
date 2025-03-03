<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Usuario extends Model
{
    use HasFactory;

    protected $table = 'usuarios';

    protected $primaryKey = 'id_usuario'; // Definir la clave primaria

    protected $fillable = [
        'nombre', 'curp', 'email', 'contraseña', 'tipo_usuario', 'telefono'
    ];

    protected $hidden = [
        'contraseña',
    ];

    public function especialista()
    {
        return $this->hasOne(Especialista::class, 'id_usuario');
    }

    public function historialMedico()
    {
        return $this->hasMany(HistorialMedico::class, 'id_usuario');
    }

    public function citas()
    {
        return $this->hasMany(Cita::class, 'id_paciente');
    }
}
