<?php
namespace App\Models\Volantes;
use Illuminate\Database\Eloquent\Model;

class TurnosJuridico extends Model {
    public $timestamps = false;
    protected $primaryKey = 'idTurnoJuridico';
    protected $table = 'sia_turnosJuridico';
    protected $fillable = [
        'idVolante',
        'emisor',
        'receptor',
        'estadoProceso',
        'usrAlta',
        'usrModificacion',
        'fModificacion',
        'fAlta',
        'estatus'];

}