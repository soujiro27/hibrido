<?php 
namespace App\Controllers\Oficios;

use App\Controllers\Template;
use Sirius\Validation\Validator;
use Carbon\Carbon;

use App\Models\Volantes\Volantes;
use App\Models\Catalogos\PuestosJuridico;
use App\Models\Documentos\TurnadosJuridico;

use App\Controllers\ApiController;

class ConfrontasController extends Template {

	private $modulo = 'Confronta';
    private $filejs = 'Oficios';

	public function index() {
		$id = $_SESSION['idEmpleado'];
        $areas = PuestosJuridico::where('rpe','=',"$id")->get();
        $area = $areas[0]['idArea'];

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
            ->get();

        	echo $this->render('/oficios/Confronta/index.twig',[
            'iracs' => $iracs,
            'sesiones'=> $_SESSION,
            'modulo' => $this->modulo,
            'filejs' => $this->filejs
            ]);

	}

	public function create($id,$message, $errors) {
        
        $personas = $this->load_personal($id);
        echo $this->render('Oficios/Confronta/create.twig',[
            'sesiones' => $_SESSION,
            'modulo' => $this->modulo,
            'mensaje' => $message,
            'errors' => $errors,
            'id' => $id,
            'personas' => $personas,
            'filejs' => $this->filejs
        ]);

    }

    public function save_turnado(array $data,$file, $app) {

        $data['estatus'] =  'ACTIVO';
        $validate = $this->validate($data,$file);
        $nombre_file = $file['file']['name'];

        if(empty($validate)){

            $datos = BaseController::datos_insert_turnados($data);
            $max = BaseController::insert_turnado_interno($data,$datos);
            
            if(!empty($nombre_file)){

                BaseController::insert_anexos_interno($max,$file);
            }

            BaseController::send_notificaciones($data,$datos,'Confronta');
            BaseController::send_notificaciones_varios($data,$datos,'Confronta');

            $success = BaseController::success();
            echo json_encode($success);


        } else {

            echo json_encode($valida);
        }

    }



    public function createDocumentos($id,$message, $errors) {
        $turnados = $this->load_personal($id);


         echo $this->render('Oficios/Confronta/documentos.twig',[
            'sesiones' => $_SESSION,
            'modulo' => $this->modulo,
            'mensaje' => $message,
            'errors' => $errors,
            'id' => $id,
            'turnados' => $turnados,
            'filejs' => $this->filejs
        ]);
    }


    public function validate($data, $file){
        
        $res = [];
        $final = [];

        $nombre_file = $file['file']['name'];

        $res[0] = ValidateController::string($data['idTipoPrioridad'],'idTipoPrioridad',10);
        $res[1] = ValidateController::alphaNumeric($data['comentario'],'comentario',350);
        $res[2] = ValidateController::string($data['estatus'],'estatus',10);

        $res[3] = ValidateController::number($data['idUsrReceptor'],'idUsrReceptor',true);
        $res[4] = ValidateController::number($data['idVolante'],'idVolante',true);

        if(!empty($nombre_file)){

            $res[5] = ValidateController::alphaNumeric($nombre_file,'Archivo',50);
            
        }

        foreach ($res as $key => $value) {
            if(!empty($value)){
                array_push($final,$value);
            }
        }


        return $final;
        
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

}