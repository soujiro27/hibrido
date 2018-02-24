<?php 
namespace App\Models\Oficios;
use Illuminate\Database\Eloquent\Model;


class Confrontas extends Model {
     protected $primaryKey = 'idConfrontaJuridico';
     protected $table = 'sia_ConfrontasJuridico';
     protected $fillable = [  
     	'idVolante',
		'notaInformativa',
		'nombreResponsable',
		'cargoResponsable',
		'siglas',
		'hConfronta',
		'fConfronta',
		'fOficio',
		'numFolio',
		'usrAlta',
		'usrModificacion',
		'fModificacion'
	];
     public $timestamps = false;

 }
