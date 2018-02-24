<?php 
namespace App\Models\Oficios;
use Illuminate\Database\Eloquent\Model;


class DocumentosSiglas extends Model {
     protected $primaryKey = 'idDocumentoSiglas';
     protected $table = 'sia_DocumentosSiglas';
     protected $fillable = ['idVolante','idSubTipoDocumento','idDocumentoTexto','idPuestosJuridico','fOficio','siglas','numFolio','usrAlta','fAlta','estatus','usrModificacion','fModificacion'];
     public $timestamps = false;

 }
