<?php 
namespace App\Models\Volantes;
use Illuminate\Database\Eloquent\Model;
use App\Models\Volantes\VolantesDocumentos;

class Volantes extends Model {
     protected $primaryKey = 'idVolante';
     protected $table = 'sia_volantes';
     public $timestamps = false;
     protected $fillable = ['idTipoDocto',
        'subFolio',
        'extemporaneo',
        'folio',
        'numDocumento',
        'anexos',
        'fDocumento',
        'fRecepcion',
        'idRemitenteJuridico',
        'hRecepcion',
        'idRemitente',
        'destinatario',
        'asunto',
        'idCaracter',
        'idTurnado',
        'idAccion',
        'usrAlta',
        'fAlta',
        'estatus'
    ];



 }
