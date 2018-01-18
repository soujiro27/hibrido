<?php 
namespace App\Models\Documentos;
use Illuminate\Database\Eloquent\Model;


class TurnadosJuridico extends Model {
     protected $primaryKey = 'idTurnadoJuridico';
     protected $table = 'sia_TurnadosJuridico';
     protected $fillable = [
     'idVolante',
     'idAreaRemitente',
     'idAreaRecepcion',
     'idUsrReceptor',
     'idEstadoTurnado',
     'idTipoTurnado',
     'idTipoPrioridad',
     'comentario',
     'usrAlta',
     'fAlta',
     'estatus'
     ];
     
     public $timestamps = false;

 }
