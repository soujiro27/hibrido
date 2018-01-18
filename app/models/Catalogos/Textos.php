<?php
namespace App\Models\Catalogos;
use Illuminate\Database\Eloquent\Model;
 
class Textos extends Model {
	 protected $primaryKey = 'idDocumentoTexto';
     public $timestamps = false;
     protected $table = 'sia_CatDoctosTextos';
     protected $fillable = ['idTipoDocto', 'tipo', 'idSubTipoDocumento','nombre','texto', 'usrAlta','fAlta'];


}