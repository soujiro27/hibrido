<?php  namespace App\Controllers\Volantes;

use App\Models\Volantes\VolantesDocumentos;
use App\Models\Volantes\Volantes;
use App\Models\Volantes\Areas;
use App\Models\Volantes\Usuarios;
use App\Models\Volantes\Notificaciones;
use App\Models\Volantes\TurnosJuridico;

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
    private $filejs = 'Volantes';


	#crea la tabla con los registros
	public function index() {
        $volantes = VolantesDocumentos::select('v.idVolante','v.folio','v.subfolio','v.numDocumento','v.idRemitente'
        ,'t.receptor','v.fRecepcion','v.extemporaneo','sub.nombre','t.estadoProceso','v.estatus')
        ->join('sia_Volantes as v','v.idVolante','=','sia_volantesDocumentos.idVolante')
        ->join('sia_turnosJuridico as t','t.idVolante','=','v.idVolante'  )
        ->join('sia_catSubTiposDocumentos as sub','sub.idSubTipoDocumento','=','sia_volantesDocumentos.idSubTipoDocumento')
        ->where('sub.auditoria','NO')
        ->orderBy('fRecepcion', 'desc')
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
		$turnados  = Areas::where('idAreaSuperior','DGAJ')->where('estatus','ACTIVO')->get();
		$turnadoDireccion = array ('idArea'=>'DGAJ','nombre' => 'DIRECCIÓN GENERAL DE ASUNTOS JURIDICOS');
		$acciones = Acciones::where('estatus','ACTIVO')->get();
        
        echo $this->render('Volantes/volantesDiversos/create.twig',[
            'sesiones' => $_SESSION,
            'documentos' => $documentos,
            'caracteres' => $caracteres,
            'acciones' => $acciones,
            'turnados' => $turnados,
            'direccionGral' => $turnadoDireccion,
            'modulo' => $this->modulo,
            'filejs' => $this->filejs
        ]);
    }

    public function save(array $data, $app){
        
        $data['estatus'] =  'ACTIVO';
        $valida = $this->validate($data);
        $duplicate = $this->duplicate($data);

        if(empty($valida[0])){

            if(empty($duplicate[0])){

                $areas = $this->create_turnados($data['idTurnado']);
                
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
                    'idTurnado' => $areas[0],
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
            
                $this->save_turnos_juridico($max,$areas);
                $this->notificaciones($areas,$data);
                $this->notificaciones_varios($areas,$data);
                $success = BaseController::success();
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

            $areas = TurnosJuridico::select('receptor')->where('idVolante',"$id")->get();
            $res = [];
            foreach ($areas as $key => $value) {
                array_push($res,$areas[$key]['receptor']);
            }

            $this->notificaciones($res,$data);
            //$this->notificaciones_varios($areas,$data);
            $success = BaseController::success();
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
        $res[6] = ValidateController::string($data['idRemitente'],'idRemitente',20);
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

        $res[1] = ValidateController::alphaNumeric($data['numDocumento'],'numDocumento',20);
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

    public function save_turnos_juridico($idVolante,$areas) {

        $elementos = count($areas);
        if($elementos > 1){
            for ($i=1; $i < $elementos  ; $i++) { 
                
                $turno = new TurnosJuridico([
                    'idVolante' => $idVolante,
                    'emisor' => 'DGAJ',
                    'receptor' => $areas[$i],
                    'estadoProceso' => 'PENDIENTE',
                    'usrAlta' => $_SESSION['idUsuario'],
                    'fAlta' => Carbon::now('America/Mexico_City')->format('Y-d-m H:i:s')
                ]);

                $turno->save();
            }
        }
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

        $nombre = BaseController::get_nombre_subDocumento($data['idSubTipoDocumento']);

        foreach ($areas as $key => $value) {
        
            $rpe = $this->get_usrid_boss_area($areas[$key]);

            if(!empty($rpe)){

                $datos_boss = BaseController::get_usrId($rpe);
            
                $mensaje = 'Mensaje enviado a: '.$datos_boss['nombre'].
                    "\nHas recibido un ".$nombre.
                    "\nCon el folio: ".$data['folio'];
                    
                BaseController::notificaciones($datos_boss['idUsuario'],$mensaje);
            }
        }
    }

    public function notificaciones_varios($areas,$data){

            $nombre = BaseController::get_nombre_subDocumento($data['idSubTipoDocumento']);
            
            foreach ($areas as $key => $value) {
            
            $rpe = $this->get_usrid_boss_area($areas[$key]);

            $datos_boss = BaseController::get_usrId($rpe);

            $users = BaseController::get_users_notifica($rpe);
            
            $mensaje = 'Mensaje enviado a: '.$datos_boss['nombre'].
                    "\nHas recibido un un ".$nombre.
                    "\nCon el folio: ".$data['folio'];

            foreach ($users as $index => $el) {
                BaseController::notificaciones($users[$index]['idUsuario'],$mensaje);
            }
        }
    }

   
}
