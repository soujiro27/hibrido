<?php 
namespace App\Models\Oficios;
use Illuminate\Database\Eloquent\Model;


class Plantillas extends Model {
     protected $primaryKey = 'idPlantillaJuridico';
     protected $table = 'sia_plantillasJuridico';
     protected $fillable = [
     	'idVolante',
     	'numFolio',
     	'asunto',
     	'fOficio',
     	'idRemitente',
     	'texto',
     	'siglas',
     	'copias',
     	'espacios',	
     	'idPuestosJuridico',
     	'usrAlta',
     	'fAlta',
     	'usrModificacion',
     	'fModificacion'
     ];
     public $timestamps = false;

 }
