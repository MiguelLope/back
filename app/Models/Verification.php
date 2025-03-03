<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Verification extends Model {
    use HasFactory;

    protected $fillable = ['id_usuario', 'codigo', 'expires_at'];

    public static function generateCode($id_usuario) {
        $codigo = Str::random(6); // Código de 6 caracteres alfanuméricos

        return self::create([
            'id_usuario' => $id_usuario,
            'codigo' => $codigo,
            'expires_at' => now()->addMinutes(10),
        ]);
    }

    public function usuario() {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }
}
