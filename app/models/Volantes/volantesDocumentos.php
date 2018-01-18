<?php
namespace App\Models\Volantes;
use Illuminate\Database\Eloquent\Model;
use App\Models\Volantes\Volantes;

class VolantesDocumentos extends Model {
    public $timestamps = false;
    protected $table = 'sia_VolantesDocumentos';
    protected $primaryKey = 'idVolanteDocumento';
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

    public function Volantes(){
        return $this->hasOne('App\Models\Volantes\Volantes','idVolante');
    }

}