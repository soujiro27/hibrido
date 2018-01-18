<?php 
namespace App\Models\Catalogos;
use Illuminate\Database\Eloquent\Model;
use App\Models\Catalogos\SubTiposDocumentos;

class TiposDocumentos extends Model {
    protected $primaryKey = 'idTipoDocto';
    protected $table = 'sia_tiposdocumentos';
    public $timestamps = false;
  	public $incrementing = false;
 

 }
