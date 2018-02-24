<?php 
namespace App\Controllers\Turnados;


use Carbon\Carbon;

use App\Controllers\Template;
use App\Controllers\ValidateController;
use App\Controllers\BaseController;
use App\Controllers\Oficios\BaseOficiosController;

use App\Models\Volantes\Volantes;
use App\Models\Catalogos\PuestosJuridico;
use App\Models\Documentos\TurnadosJuridico;

use App\Controllers\ApiController;


class TurnadosController extends Template {

	private $modulo = 'Documentos Turnados';
    private $filejs = 'Confronta';
    private $ruta = 'turnos';

	public function index() {
        
		$idUsuario = $_SESSION['idUsuario'];
       
        $api = new ApiController();

        $turnados_propios = TurnadosJuridico::where('idUsrReceptor',"$idUsuario")
        ->get();
        

        $volantes_repetidos = [];
        foreach ($turnados_propios as $key => $value) {
            array_push($volantes_repetidos,$turnados_propios[$key]['idVolante']);
        }
        
        $volantes = array_unique($volantes_repetidos);

        $turnos = Volantes::select('sia_Volantes.*','sub.nombre','c.nombre as caracter','a.nombre as accion','audi.clave','t.idEstadoTurnado')
            ->join('sia_catCaracteres as c','c.idCaracter','=','sia_Volantes.idCaracter')
            ->join('sia_CatAcciones as a','a.idAccion','=','sia_Volantes.idAccion')
            ->join('sia_VolantesDocumentos as vd','vd.idVolante','=','sia_Volantes.idVolante')
            ->join('sia_auditorias as audi','audi.idAuditoria','=','vd.cveAuditoria')
            ->join( 'sia_catSubTiposDocumentos as sub','sub.idSubTipoDocumento','=','vd.idSubTipoDocumento')
            ->join('sia_TurnadosJuridico as t','t.idVolante','=','sia_Volantes.idVolante')
            ->where('t.idTipoTurnado','=','I')
            ->where('t.idUsrReceptor',"$idUsuario")
            ->whereIn('sia_volantes.idVolante',$volantes)
            ->get();


        	echo $this->render('/Oficios/turnos/index.twig',[
                'iracs' => $turnos,
                'sesiones'=> $_SESSION,
                'modulo' => $this->modulo,
                'filejs' => $this->filejs,
                'ruta'=> $this->ruta
            ]);
          

        }

	public function create($id) {
        
        $base = new BaseController();
        $baseOficios = new BaseOficiosController();
        $cedula = $base->rol_cedulas('NOTA');

        $personas = $baseOficios->load_personal($id);
        echo $this->render('Oficios/create.twig',[
            'sesiones' => $_SESSION,
            'modulo' => $this->modulo,
            'id' => $id,
            'personas' => $personas,
            'filejs' => $this->filejs,
            'ruta' => $this->ruta,
            'cedula' => $cedula
        ]);

    }

    public function save_turnado(array $data,$file, $app) {

        $save = new BaseOficiosController();
        $save->save_turnado($data,$file, $app, $this->modulo);
       
    }



    public function createDocumentos($id){


        $base = new BaseController();
        $baseOficios = new BaseOficiosController();

        $personas = $baseOficios->load_personal($id);
        $cedula = $base->rol_cedulas('NOTA');

         echo $this->render('Oficios/documentos.twig',[
            'sesiones' => $_SESSION,
            'modulo' => $this->modulo,
            'id' => $id,
            'turnados' => $personas,
            'filejs' => $this->filejs,
            'ruta' => $this->ruta,
            'cedula' => $cedula
        ]);
    }
    


}