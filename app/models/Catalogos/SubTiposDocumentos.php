<?php 
namespace App\Models\Catalogos;
use Illuminate\Database\Eloquent\Model;
use App\Models\Catalogos\TiposDocumentos;

class SubTiposDocumentos extends Model {
     protected $primaryKey = 'idSubTipoDocumento';
     protected $table = 'sia_catSubTiposDocumentos';
     protected $fillable = ['idTipoDocto','nombre','auditoria', 'usrAlta','fAlta','estatus'];
     public $timestamps = false;


     public function TiposDocumentos(){
     	return $this->hasOne('App\Models\Catalogos\TiposDocumentos');
     }

 }
