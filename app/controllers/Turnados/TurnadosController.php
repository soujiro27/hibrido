<?php 
namespace App\Controllers\Turnados;

use App\Controllers\Template;
use Sirius\Validation\Validator;
use Carbon\Carbon;
use App\Controllers\ApiController;


use App\Models\Volantes\Volantes;
use App\Models\Catalogos\PuestosJuridico;
use App\Models\Documentos\TurnadosJuridico;
use App\Models\Documentos\AnexosJuridico;


class TurnadosController extends Template {

	private $modulo = 'Documentos Turnados';

	public function index() {
        
		$idUsuario = $_SESSION['idUsuario'];
       

        $turnados_propios = TurnadosJuridico::select('idVolante')
        ->where('idUsrReceptor',"$idUsuario")
        ->get();
        

        
        $volantes_repetidos = $this->array_turnados($turnados_propios);
        $volantes = array_unique($volantes_repetidos);

        

        $turnos = Volantes::select('sia_Volantes.idVolante','sia_Volantes.folio',
            'sia_Volantes.numDocumento','sia_Volantes.idRemitente','sia_Volantes.fRecepcion','sia_Volantes.asunto'
        ,'c.nombre as caracter','a.nombre as accion','audi.clave','sia_Volantes.extemporaneo','t.estadoProceso')
            ->join('sia_catCaracteres as c','c.idCaracter','=','sia_Volantes.idCaracter')
            ->join('sia_CatAcciones as a','a.idAccion','=','sia_Volantes.idAccion')
            ->join('sia_VolantesDocumentos as vd','vd.idVolante','=','sia_Volantes.idVolante')
            ->join('sia_auditorias as audi','audi.idAuditoria','=','vd.cveAuditoria')
            ->join( 'sia_catSubTiposDocumentos as sub','sub.idSubTipoDocumento','=','vd.idSubTipoDocumento')
            ->join('sia_turnosJuridico as t','t.idVolante','=','sia_Volantes.idVolante')
            ->whereIn('sia_volantes.idVolante',$volantes)
            ->get();


        	echo $this->render('/documentos/turnos/index.twig',[
            'iracs' => $turnos,
            'sesiones'=> $_SESSION,
            'modulo' => $this->modulo,
            ]);

            //var_dump($turnos);

        }

	public function create($id,$message, $errors) {
        
        $personas = $this->load_personal($id);
        echo $this->render('documentos/turnos/create.twig',[
            'sesiones' => $_SESSION,
            'modulo' => $this->modulo,
            'mensaje' => $message,
            'errors' => $errors,
            'id' => $id,
            'personas' => $personas
        ]);

    }

    public function save_turnado(array $data,$files, $app) {

            $nombre_file = $files['archivo']['name'];
            $size_file = $files['archivo']['size'];
            $id = $data['idVolante'];
            $area = $this->Area($id); 
            $idPuesto = $data['idUsrReceptor'];

            $puestos = PuestosJuridico::select('u.idUsuario')
                    ->join('sia_usuarios as u','u.idEmpleado','=','sia_PuestosJuridico.rpe')
                    ->where('sia_PuestosJuridico.idPuestoJuridico',"$idPuesto")
                    ->get();
            $idPuesto = $puestos[0]['idUsuario'];


            $errors = ApiController::validate_file($nombre_file,$size_file);
            if(empty($errors)){
                if(empty($this->validate($data))){
                    $turno = new TurnadosJuridico([
                        'idVolante' => $data['idVolante'],
                        'idAreaRemitente' => $area,
                        'idAreaRecepcion' => $area,
                        'idUsrReceptor' => $idPuesto,
                        'idEstadoTurnado' => 'En Atencion',
                        'idTipoTurnado' => $data['idTipoTurnado'],
                        'idTipoPrioridad' =>$data['idTipoPrioridad'],
                        'comentario' => $data['comentario'],
                        'usrAlta' => $_SESSION['idUsuario'],
                        'estatus' => 'ACTIVO',
                        'fAlta' => Carbon::now('America/Mexico_City')->format('Y-d-m H:i:s')
                    ]);

                    $turno->save();
                    $max = TurnadosJuridico::all()->max('idTurnadoJuridico');

                    if(ApiController::upload_files($id,$max,$files)){
                         ApiController::notificaciones($idPuesto,$data['idVolante']);
                        $app->redirect('/SIA/juridico/turnos/'.$id);        
                    } else { 
                        $this->create($id,'Hubo un Error Intente de Nuevo',false);        
                    }

                } else {
                    $this->create($id,$message = false,$errors);    
                }   

            }else{
                $this->create($id,$message = false,$errors);
            }
    }



    public function createDocumentos($id,$message, $errors) {
        $turnados = $this->load_personal($id);


         echo $this->render('documentos/turnos/documentos.twig',[
            'sesiones' => $_SESSION,
            'modulo' => $this->modulo,
            'mensaje' => $message,
            'errors' => $errors,
            'id' => $id,
            'turnados' => $turnados,
        ]);
    }


    public function validate($data){
        $errors = [];
        $validator = new \Sirius\Validation\Validator;
        $validator->add(
            array(
            'idVolante' => 'required | Number | MaxLength(4)',
            'idUsrReceptor' => 'required | Number | MaxLength(3)',
            'comentario' => 'MaxLength(350)',
            'idTipoPrioridad' => 'required | Alpha | MaxLength(15)'
        ));

        if(!$validator->validate($data)){
            $errors = $validator->getMessages();
            return $errors;
        }else{

            return $errors;
        }   
    }

    public function Area($id){
        
        $volante = Volantes::where('idVolante',"$id")->get();

        return $volante[0]['idTurnado'];

    }

    public function load_personal($id){

        $turnado_volantes = Volantes::select('idTurnado')->where('idVolante',"$id")->get();
        $idTurnado = $turnado_volantes[0]['idTurnado'];

        $puestos = PuestosJuridico::where('idArea',"$idTurnado")->get();
        return $puestos;

    }


    public function array_turnados($data) {
		$id = [];
		foreach ($data as $key => $value) {
			array_push($id,$data[$key]['idVolante']);
		}
		return $id;
	}

}