<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Consultorio extends Model
{
    use HasFactory;

    protected $table = 'consultorios';
    protected $primaryKey = 'id_consultorio'; // Definir la clave primaria

    protected $fillable = [
        'nombre', 'ubicacion'
    ];

    public function especialistas()
    {
        return $this->hasMany(Especialista::class, 'id_consultorio');
    }

    public function citas()
    {
        return $this->hasMany(Cita::class, 'id_consultorio');
    }
}
