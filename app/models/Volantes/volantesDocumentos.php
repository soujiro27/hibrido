<?php
namespace App\Models\Volantes;
use Illuminate\Database\Eloquent\Model;

class VolantesDocumentos extends Model {
    public $timestamps = false;
     protected $primaryKey = 'idVolanteDocumento';
    protected $table = 'sia_VolantesDocumentos';
    protected $fillable = [
        'idVolante',
        'promocion',
        'cveAuditoria',
        'idSubTipoDocumento',
        'notaConfronta',
        'usrAlta',
        'fAlta',
        'estatus'
    ];

}