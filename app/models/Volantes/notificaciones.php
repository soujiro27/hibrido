<?php
namespace App\Models\Volantes;
use Illuminate\Database\Eloquent\Model;

class Notificaciones extends Model {
    public $timestamps = false;
    protected $table = 'sia_notificacionesmensajes';
    protected $fillable = ['idNotificacion',
        'idUsuario',
        'mensaje',
        'idPrioridad',
        'idImpacto',
        'fLectura',
        'usrAlta',
        'fAlta',
        'estatus',
        'situacion',
        'identificador'
        ,'idCuenta'
        ,'idAuditoria',
        'idModulo'
        ,'referencia'];

}