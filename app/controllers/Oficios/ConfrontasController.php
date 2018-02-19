<?php 
namespace App\Controllers\Oficios;

use Carbon\Carbon;

use App\Controllers\Template;
use App\Controllers\ValidateController;
use App\Controllers\BaseController;
use App\Controllers\Oficios\BaseOficiosController;

use App\Models\Volantes\Volantes;
use App\Models\Volantes\VolantesDocumentos;
use App\Models\Catalogos\PuestosJuridico;
use App\Models\Documentos\TurnadosJuridico;
use App\Models\Oficios\Observaciones;
use App\Models\Oficios\DocumentosSiglas;
use App\Models\Oficios\Espacios;

use App\Controllers\ApiController;

class ConfrontasController extends Template {

	private $modulo = 'confrontasJuridico';
    private $filejs = 'Confronta';

	public function index() {

		$id = $_SESSION['idEmpleado'];
        $areas = PuestosJuridico::where('rpe','=',"$id")->get();
        $area = $areas[0]['idArea'];

        $now = Carbon::now('America/Mexico_City')->format('Y');
        $campo = 'Folio';
        $tipo = 'desc';

        if(!empty($data)){
            $now = $data['year'];
            $campo = $data['campo'];
            $tipo = $data['tipo'];
        }

        $iracs = Volantes::select('sia_Volantes.idVolante','sia_Volantes.folio',
            'sia_Volantes.numDocumento','sia_Volantes.idRemitente','sia_Volantes.fRecepcion','sia_Volantes.asunto'
        ,'c.nombre as caracter','a.nombre as accion','audi.clave','sia_Volantes.extemporaneo','t.idEstadoTurnado')
            ->join('sia_catCaracteres as c','c.idCaracter','=','sia_Volantes.idCaracter')
            ->join('sia_CatAcciones as a','a.idAccion','=','sia_Volantes.idAccion')
            ->join('sia_VolantesDocumentos as vd','vd.idVolante','=','sia_Volantes.idVolante')
            ->join('sia_auditorias as audi','audi.idAuditoria','=','vd.cveAuditoria')
            ->join( 'sia_catSubTiposDocumentos as sub','sub.idSubTipoDocumento','=','vd.idSubTipoDocumento')
            ->join('sia_TurnadosJuridico as t','t.idVolante','=','sia_Volantes.idVolante')
            ->where('sub.nombre','=','CONFRONTA')
            ->where('t.idAreaRecepcion','=',"$area")
            ->where('t.idTipoTurnado','E')
            ->whereYear('sia_Volantes.fRecepcion','=',"$now")
            ->orderBy("$campo","$tipo")
            ->get();

        	echo $this->render('/oficios/index.twig',[
            'iracs' => $iracs,
            'sesiones'=> $_SESSION,
            'modulo' => $this->modulo,
            'filejs' => $this->filejs,
            'ruta' => $this->modulo
            ]);

	}

	public function create($id) {
        
        $base = new BaseController();
        $cedula = $base->rol_cedulas('CONFRONTA');

        $personas = $this->load_personal($id);
        echo $this->render('Oficios/create.twig',[
            'sesiones' => $_SESSION,
            'modulo' => $this->modulo,
            'id' => $id,
            'personas' => $personas,
            'filejs' => $this->filejs,
            'ruta' => $this->modulo,
            'cedula' => $cedula
        ]);

    }

    public function save_turnado(array $data,$file, $app) {

        $save = new BaseOficiosController();
        $save->save_turnado($data,$file, $app, $this->modulo);
       
    }



    public function createDocumentos($id){

        $base = new BaseController();

        $personas = $this->load_personal($id);
        $cedula = $base->rol_cedulas('CONFRONTAS');

         echo $this->render('Oficios/documentos.twig',[
            'sesiones' => $_SESSION,
            'modulo' => $this->modulo,
            'id' => $id,
            'turnados' => $personas,
            'filejs' => $this->filejs,
            'ruta' => $this->modulo,
            'cedula' => $cedula
        ]);
    }




    public function load_personal($id){

        $turnado_volantes = TurnadosJuridico::select('idAreaRecepcion')->where('idVolante',"$id")->get();
        $idTurnado = $turnado_volantes[0]['idAreaRecepcion'];

        $rpe = $_SESSION['idEmpleado'];

        $puestos = PuestosJuridico::where('idArea',"$idTurnado")
                                    ->where('rpe','<>',"$rpe")
                                    ->get();
        return $puestos;

    }

    public function createCedula($id){

        $base = new BaseController();
        $cedula = $base->rol_cedulas('CONFRONTAS');
        $documentos = DocumentosSiglas::where('idVolante',"$id")->get();
        $espacios = Espacios::where('idVolante',"$id")->get();
        
        if($documentos->isEmpty()){

            echo $this->render('Oficios/Confronta/insert.twig',[
                'sesiones' => $_SESSION,
                'modulo' => $this->modulo,
                'filejs' => $this->filejs,
                'ruta' => $this->modulo,
                'cedula' => $cedula,        
                'idVolante' => $id 
            ]);

        } else {

            echo $this->render('Oficios/Confronta/update.twig',[
                'sesiones' => $_SESSION,
                'modulo' => $this->modulo,
                'filejs' => $this->filejs,
                'ruta' => $this->modulo,
                'cedula' => $cedula,        
                'documentos' => $documentos,
                'idVolante' => $id,
                'espacios' => $espacios
            ]);

        }

    }

}