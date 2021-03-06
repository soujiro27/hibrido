<?php  namespace App\Controllers\Volantes;

use App\Models\Volantes\VolantesDocumentos;
use App\Models\Volantes\Volantes;
use App\Models\Volantes\Areas;
use App\Models\Volantes\Usuarios;
use App\Models\Volantes\Notificaciones;
use App\Models\Documentos\TurnadosJuridico; 

use App\Models\Catalogos\TiposDocumentos;
use App\Models\Catalogos\Caracteres;
use App\Models\Catalogos\Acciones;
use App\Models\Catalogos\PuestosJuridico;

use App\Controllers\Template;
use App\Controllers\ValidateController;
use App\Controllers\BaseController;


use Carbon\Carbon;


class volantesDiversosController extends Template { 

    private $modulo = 'Volantes-Diversos';
    private $filejs = 'Diversos';


	#crea la tabla con los registros
	public function index(array $data) {

        $now = Carbon::now('America/Mexico_City')->format('Y');
        $campo = 'Folio';
        $tipo = 'desc';

        if(!empty($data)){
            $now = $data['year'];
            $campo = $data['campo'];
            $tipo = $data['tipo'];
        }


        $volantes = VolantesDocumentos::select('v.idVolante','v.folio','v.subfolio','v.numDocumento',
            'v.idRemitente','t.idAreaRecepcion','v.fRecepcion','v.extemporaneo','sub.nombre',
            't.idEstadoTurnado','t.idAreaRecepcion','v.estatus')
        ->join('sia_Volantes as v','v.idVolante','=','sia_volantesDocumentos.idVolante')
        ->join('sia_TurnadosJuridico as t','t.idVolante','=','v.idVolante'  )
        ->join('sia_catSubTiposDocumentos as sub','sub.idSubTipoDocumento','=','sia_volantesDocumentos.idSubTipoDocumento')
        ->where('sub.auditoria','NO')
        ->whereYear('v.fRecepcion','=',"$now")
        ->orderBy("$campo","$tipo")
        ->get();

      
		echo $this->render('Volantes/volantesDiversos/index.twig',[
			'sesiones'   => $_SESSION,
			'modulo'	 => $this->modulo,
			'volantes' => $volantes,
            'filejs' => $this->filejs

		]);
    }

    public function create() {
        $documentos = TiposDocumentos::where('estatus','ACTIVO')->where('tipo','JURIDICO')->get();
		$caracteres = Caracteres::where('estatus','ACTIVO')->get();
		$acciones = Acciones::where('estatus','ACTIVO')->get();
        
        echo $this->render('Volantes/volantesDiversos/create.twig',[
            'sesiones' => $_SESSION,
            'documentos' => $documentos,
            'caracteres' => $caracteres,
            'acciones' => $acciones,
            'modulo' => $this->modulo,
            'filejs' => $this->filejs
        ]);
    }

    public function save(array $data,$file,$app){
        

        $base = new BaseController();

        $data['estatus'] =  'ACTIVO';
        $valida = $this->validate($data);
        $duplicate = $this->duplicate($data);
        $nombre_file = $file['file']['name'];

        if(empty($valida[0])){

            if(empty($duplicate[0])){

                
                $volantes = new Volantes([
                    'idTipoDocto' =>$data['idTipoDocto'],
                    'subFolio' => $data['subFolio'],
                    'extemporaneo' => $data['extemporaneo'],
                    'folio' => $data['folio'],
                    'numDocumento' => $data['numDocumento'],
                    'anexos' => $data['anexos'],
                    'fDocumento' => $data['fDocumento'],
                    'fRecepcion' => $data['fRecepcion'],
                    'hRecepcion' => $data['hRecepcion'],
                    'hRecepcion' => $data['hRecepcion'],
                    'idRemitente' => $data['idRemitente'],
                    'idRemitenteJuridico' => $data['idRemitenteJuridico'],
                    'destinatario' => 'DR. IVÁN DE JESÚS OLMOS CANSINO',
                    'asunto' => $data['asunto'],
                    'idCaracter' => $data['idCaracter'],
                    'idAccion' => $data['idAccion'],
                    'usrAlta' => $_SESSION['idUsuario'],
                    'fAlta' => Carbon::now('America/Mexico_City')->format('Y-d-m H:i:s')
                ]);

                $volantes->save();
                $max = Volantes::all()->max('idVolante');
                
                $volantesDocumentos = new VolantesDocumentos([
                    'idVolante' => $max,
                    'promocion' => 'NO',
                    'idSubTipoDocumento' => $data['idSubTipoDocumento'],
                    'notaConfronta' => 'NO',
                    'usrAlta' => $_SESSION['idUsuario'],
                    'fAlta' => Carbon::now('America/Mexico_City')->format('Y-d-m H:i:s')
                ]);

                $volantesDocumentos->save();
            
                $areas = $this->create_turnados($data['idTurnado']);

               

                foreach ($areas as $key => $value) {
                    
                    $datos_director_area = $base->get_data_area($value);

                    $idUsuario = $datos_director_area[0]['idUsuario'];
                
                    $turno = new TurnadosJuridico([
                        'idVolante' => $max,
                        'idAreaRemitente' => 'DGAJ',
                        'idAreaRecepcion' => $value,
                        'idUsrReceptor' => $idUsuario ,
                        'idEstadoTurnado' => 'EN ATENCION',
                        'idTipoTurnado' => 'E',
                        'idTipoPrioridad' => $data['idCaracter'],
                        'comentario' => 'SIN COMENTARIOS',
                        'usrAlta' => $_SESSION['idUsuario'],
                        'estatus' => 'ACTIVO',
                        'fAlta' => Carbon::now('America/Mexico_City')->format('Y-d-m H:i:s')
                    ]);

                    $turno->save();
                }

                if(!empty($nombre_file)){
    
                $nombre_final = $base->upload_file_areas($file,$max);

                    Volantes::find($max)->update([
                        'anexoDoc' => $nombre_final,
                        'usrModificacion' => $_SESSION['idUsuario'],
                        'fModificacion' => Carbon::now('America/Mexico_City')->format('Y-d-m H:i:s') 
                    ]);

                }


                $this->notificaciones($areas,$data);
                $this->notificaciones_varios($areas,$data);
                $success = $base->success();
                echo json_encode($success);


            } else {

                echo json_encode($duplicate);
            }

        } else {

            echo json_encode($valida);
        }
        
       
    }


    public function createUpdate($id, $app){
        $volantes = Volantes::find($id);
        $turnados  = Areas::where('idAreaSuperior','DGAJ')->where('estatus','ACTIVO')->get();
        $turnadoDireccion = array ('idArea'=>'DGAJ','nombre' => 'DIRECCIÓN GENERAL DE ASUNTOS JURIDICOS');
        $acciones = Acciones::where('estatus','ACTIVO')->get();
        $caracteres = Caracteres::where('estatus','ACTIVO')->get();
      

        echo $this->render('Volantes/volantesDiversos/update.twig',[
            'sesiones'=> $_SESSION,
            'volantes'=> $volantes,
            'caracteres' => $caracteres,
            'acciones' => $acciones,
            'turnados' => $turnados,
            'direccionGral' => $turnadoDireccion,
            'close' => true,
            'modulo' => $this->modulo,
            'filejs' => $this->filejs

        ]);
    }

    public function update(array $data, $app) {

        $base = new BaseController();

        $id = $data['idVolante'];
        
        $valida = $this->validate_update($data);
        $areas = $this->create_turnados($data['idTurnado']);
        
        $subDocumento = VolantesDocumentos::select('idSubTipoDocumento')->where('idVolante',"$id")->get();
        $data['idSubTipoDocumento'] = $subDocumento[0]['idSubTipoDocumento'];

        $folio = Volantes::find($id);
        $data['folio'] = $folio[0]['folio'];
       
        if(empty($valida[0])){

             Volantes::find($id)->update([
                'numDocumento' => $data['numDocumento'],
                'anexos' => $data['anexos'],
                'fDocumento' => $data['fDocumento'],
                'fRecepcion' => $data['fRecepcion'],
                'hRecepcion' => $data['hRecepcion'],
                'asunto' => $data['asunto'],
                'idCaracter' => $data['idCaracter'],
                'idAccion' => $data['idAccion'],
                'usrModificacion' => $_SESSION['idUsuario'],
                'fModificacion' => Carbon::now('America/Mexico_City')->format('Y-d-m H:i:s'),
                'estatus' => $data['estatus']
            ]);

            
            if(!empty($data['idTurnado'])){

                foreach ($areas as $key => $value) {
                    
                    $turno = new TurnosJuridico([
                        'idVolante' => $id,
                        'emisor' => 'DGAJ',
                        'receptor' => $areas[$key],
                        'estadoProceso' => 'PENDIENTE',
                        'usrAlta' => $_SESSION['idUsuario'],
                        'fAlta' => Carbon::now('America/Mexico_City')->format('Y-d-m H:i:s')
                    ]);

                    $turno->save();

                }
            }

            $areas = TurnadosJuridico::select('idAreaRecepcion')->where('idVolante',"$id")->get();
            $res = [];
            foreach ($areas as $key => $value) {
                array_push($res,$areas[$key]['idAreaRecepcion']);
            }



            $this->notificaciones($res,$data);
            $this->notificaciones_varios($res,$data);
            $success = $base->success();
            echo json_encode($success);
        

        } else {

            echo json_encode($valida);
        }

    }

    public function validate(array $data){
       
        $res = [];
        $final = [];

        $res[0] = ValidateController::string($data['idTipoDocto'],'idTipoDocto',20);
        $res[1] = ValidateController::string($data['extemporaneo'],'extemporaneo',2);
        $res[2] = ValidateController::alphaNumeric($data['numDocumento'],'numDocumento',20);
        $res[3] = ValidateController::alphaNumeric($data['fDocumento'],'fDocumento',10);
        $res[4] = ValidateController::alphaNumeric($data['fRecepcion'],'fRecepcion',10);
        $res[5] = ValidateController::alphaNumeric($data['hRecepcion'],'hRecepcion',5);
        $res[6] = ValidateController::alphaNumeric($data['idRemitente'],'idRemitente',20);
        $res[7] = ValidateController::alphaNumeric($data['idTurnado'],'idTurnado',50);
        $res[8] = ValidateController::string($data['estatus'],'estatus',10);


        $res[9] = ValidateController::number($data['idSubTipoDocumento'],'idSubTipoDocumento',true);
        $res[10] = ValidateController::number($data['folio'],'folio',true);
        $res[11] = ValidateController::number($data['subFolio'],'subFolio',false);
        $res[12] = ValidateController::number($data['anexos'],'anexos',false);
        $res[13] = ValidateController::number($data['idCaracter'],'idCaracter',true);
        $res[14] = ValidateController::number($data['idAccion'],'idAccion',true);
        $res[15] = ValidateController::number($data['idRemitenteJuridico'],'iidRemitenteJuridicodAccion',true);

        foreach ($res as $key => $value) {
            if(!empty($value)){
                array_push($final,$value);
            }
        }


        return $final;
        
    }

    public function validate_update(array $data){

        $res = [];
        $final = [];

        $res[1] = ValidateController::alphaNumeric($data['numDocumento'],'numDocumento',30);
        $res[2] = ValidateController::alphaNumeric($data['fDocumento'],'fDocumento',10);
        $res[3] = ValidateController::alphaNumeric($data['fRecepcion'],'fRecepcion',10);
        $res[4] = ValidateController::alphaNumeric($data['hRecepcion'],'hRecepcion',5);
        $res[5] = ValidateController::string($data['estatus'],'estatus',10);

        $res[6] = ValidateController::number($data['anexos'],'anexos',false);
        $res[7] = ValidateController::number($data['idCaracter'],'idCaracter',true);
        $res[8] = ValidateController::number($data['idAccion'],'idAccion',true);

        foreach ($res as $key => $value) {
            if(!empty($value)){
                array_push($final,$value);
            }
        }


        return $final;
    }

    public function duplicate(array $data) {
        
        $folio = $data['folio'];
        $subFolio = $data['subFolio'];
        $fecha = date("Y",strtotime($data['fRecepcion']));
        $final = [];
        $errors  = array();
        
        $res = Volantes::where('folio',"$folio")
                        ->where('subFolio',"$subFolio")
                        ->whereYear('fRecepcion',"$fecha")
                        ->count();

        if($res > 0) {
            
            $errors['campo'] = 'Duplicado';
            $errors['message'] = 'No puede haber registros Duplicados';
        } 
        $final[0] = $errors;

        return $final;
    }

    public function create_turnados($turnado) {

        $res = explode(',',$turnado);
        return $res;
    }

   


    public function get_usrid_boss_area($turnado){
        
        $puestos = PuestosJuridico::select('rpe')
            ->where('idArea',"$turnado")
            ->where('titular','SI')
            ->get();
        
        if($puestos->isEmpty()){
            $jefe_area_rpe =     [];
        } else{

            $jefe_area_rpe = $puestos[0]['rpe'];
        }
        
        return $jefe_area_rpe;


    }


    public function notificaciones($areas, $data) {

        $base = new BaseController();
        
        $nombre = $base->get_nombre_subDocumento($data['idSubTipoDocumento']);

        foreach ($areas as $key => $value) {
        
            $rpe = $this->get_usrid_boss_area($areas[$key]);

            if(!empty($rpe)){

                $datos_boss = $base->get_usrId($rpe);
            
                $mensaje = 'Mensaje enviado a: '.$datos_boss['nombre'].
                    "\nHas recibido un ".$nombre.
                    "\nCon el folio: ".$data['folio'];
                    
                $base->notificaciones($datos_boss['idUsuario'],$mensaje);
            }
        }
    }

    public function notificaciones_varios($areas,$data){

        $base = new BaseController();

        $nombre = $base->get_nombre_subDocumento($data['idSubTipoDocumento']);
            
        foreach ($areas as $key => $value) {
            
            $rpe = $this->get_usrid_boss_area($areas[$key]);

            $datos_boss = $base->get_usrId($rpe);

            $users = $base->get_users_notifica($rpe);
            
            $mensaje = 'Mensaje enviado a: '.$datos_boss['nombre'].
                    "\nHas recibido un un ".$nombre.
                    "\nCon el folio: ".$data['folio'];

            foreach ($users as $index => $el) {
                $base->notificaciones($users[$index]['idUsuario'],$mensaje);
            }
        }
         
    }

   
}
