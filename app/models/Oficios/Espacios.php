<?php 
namespace App\Models\Oficios;
use Illuminate\Database\Eloquent\Model;


class Espacios extends Model {
     protected $primaryKey = 'idEspacioJuridico ';
     protected $table = 'EspaciosJuridco';
     protected $fillable = [  
     	'idVolante',
		'encabezado',
		'cuerpo',
		'pie',
		'usrAlta',
		'usrModificacion',
		'fModificacion'
	];
     public $timestamps = false;

 }
