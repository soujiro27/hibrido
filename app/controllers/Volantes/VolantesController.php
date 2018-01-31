<?php 
namespace App\Controllers\Volantes;
use Carbon\Carbon;

use App\Models\Volantes\VolantesDocumentos;
use App\Models\Volantes\Volantes;
use App\Models\Catalogos\TiposDocumentos;

use App\Controllers\Template;
use App\Controllers\ValidateController;
use App\Controllers\BaseController;

use App\Models\Volantes\Areas;
use App\Models\Volantes\Usuarios;
use App\Models\Volantes\Notificaciones;
use App\Models\Documentos\TurnadosJuridico; 


use App\Models\Catalogos\Caracteres;
use App\Models\Catalogos\Acciones;
use App\Models\Catalogos\PuestosJuridico;

class volantesController extends Template{
	
	private $modulo = 'Volantes';
	private $filejs = 'Volantes	';

	#crea la tabla con los registros
	public function index(){

		$volantes = Volantes::select('sia_Volantes.*','vd.cveAuditoria','a.clave','sub.nombre','t.idEstadoTurnado','t.idAreaRecepcion')
		->join('sia_VolantesDocumentos as vd','vd.idVolante','=','sia_volantes.idVolante')
		->join('sia_TurnadosJuridico as t','t.idVolante','=','sia_Volantes.idVolante'  )
		->join('sia_auditorias as a','a.idAuditoria','=','vd.cveAuditoria')
		->join('sia_catSubTiposDocumentos as sub','sub.idSubTipoDocumento','=','vd.idSubTipoDocumento')
		->where('sub.auditoria','SI')
		->orderBy('fRecepcion', 'desc')
		->get();
		
		echo $this->render('Volantes/volantes/index.twig',[
			'sesiones'   => $_SESSION,
			'modulo'	 => $this->modulo,
			'volantes' => $volantes,
			'filejs' => $this->filejs
		]);
	}

	#manda a traer el formulario de insercion
	public function create(){
		
		$documentos = TiposDocumentos::where('estatus','ACTIVO')->where('tipo','JURIDICO')->get();
		$caracteres = Caracteres::where('estatus','ACTIVO')->get();
		$turnados  = Areas::where('idAreaSuperior','DGAJ')->where('estatus','ACTIVO')->get();
		$turnadoDireccion = array ('idArea'=>'DGAJ','nombre' => 'DIRECCIÓN GENERAL DE ASUNTOS JURIDICOS');
		$acciones = Acciones::where('estatus','ACTIVO')->get();
		
		echo $this->render('Volantes/volantes/create.twig',[
			'sesiones' => $_SESSION,
			'modulo' => $this->modulo,
			'documentos' => $documentos,
			'cuenta' =>  $_SESSION['idCuentaActual'],
			'caracteres' => $caracteres,
			'turnados' => $turnados,
            'direccionGral' => $turnadoDireccion,
            'acciones' => $acciones,
			'filejs' => $this->filejs
		]);
	}


	#guarda un nuevo registro
	public function save(array $data, $app) {
		
		$data['estatus'] =  'ACTIVO';
		
		$valida = $this->validate_process($data);
		
		
		$datos_director_area = BaseController::get_data_area($data['idTurnado']);

		if(empty($valida)){
			$volantes = new Volantes([
				'idTipoDocto' =>$data['documento'],
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
				'destinatario' => 'DR. IVAN OLMOS CANSIANO',
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
				'promocion' => $data['promocion'],
				'cveAuditoria' => $data['cveAuditoria'],
				'idSubTipoDocumento' => $data['subDocumento'],
				'notaConfronta' => $data['notaConfronta'],
				'usrAlta' => $_SESSION['idUsuario'],
				'fAlta' => Carbon::now('America/Mexico_City')->format('Y-d-m H:i:s')
			]);

			$volantesDocumentos->save();

			$turno = new TurnadosJuridico([
	            'idVolante' => $max,
	            'idAreaRemitente' => 'DGAJ',
	            'idAreaRecepcion' => $data['idTurnado'],
	            'idUsrReceptor' => $datos_director_area[0]['idUsuario'],
	            'idEstadoTurnado' => 'EN ATENCION',
	            'idTipoTurnado' => 'NUEVO',
	            'idTipoPrioridad' => $data['idCaracter'],
	            'comentario' => 'SIN COMENTARIOS',
	            'usrAlta' => $_SESSION['idUsuario'],
	            'estatus' => 'ACTIVO',
	            'fAlta' => Carbon::now('America/Mexico_City')->format('Y-d-m H:i:s')
        	]);

        	$turno->save();

			$this->send_notificaciones($data);
			$this->send_notificaciones_varios($data);
			$success = BaseController::success();
			echo json_encode($success);
		} else {
			echo json_encode($valida);
		}
	}

	public function createUpdate($id, $app){
		
		$volantes = Volantes::find($id);
		$turnados  = Areas::where('idAreaSuperior','DGAJ')->where('estatus','ACTIVO')->get();
		$acciones = Acciones::where('estatus','ACTIVO')->get();
		$caracteres = Caracteres::where('estatus','ACTIVO')->get();
		$turnadosJuridico = TurnadosJuridico::where('idVolante',"$id")->get();
		$opcion = $turnadosJuridico[0]['idAreaRecepcion'];

		echo $this->render('Volantes/volantes/update.twig',[
            'sesiones'=> $_SESSION,
            'volantes'=> $volantes,
            'caracteres' => $caracteres,
            'acciones' => $acciones,
            'turnados' => $turnados,
            'chose' => $opcion,
			'close' => true,
            'modulo' => 'Volantes',
            'filejs' => $this->filejs
        ]);
		
	}

	public function update(array $data, $app) {


		$id = $data['idVolante'];
		$subDocumento = VolantesDocumentos::select('idSubTipoDocumento')->where('idVolante',"$id")->get();
		$data['subDocumento'] = $subDocumento[0]['idSubTipoDocumento'];
		$valida = $this->validate_update($data);


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

			TurnadosJuridico::where('idVolante',"$id")->update([
				'idAreaRecepcion' => $data['idTurnado'],
				'idTipoPrioridad' => $data['idCaracter']


			]);

			$this->send_notificaciones($data);
			$this->send_notificaciones_varios($data);
			$success = BaseController::success();
			echo json_encode($success);

		} else {
			echo json_encode($valida);
		}
		
	}

	public function duplicate(array $data) {

		$folio = $data['folio'];
		$subFolio = $data['subFolio'];
		$final = [];
		$errors  = array();


		$fecha = date("Y",strtotime($data['fRecepcion']));
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

	public function validate (array $data){
	
		$res = [];
		$final = [];

		$res[0] = ValidateController::string($data['documento'],'documento',20);
		$res[1] = ValidateController::string($data['notaConfronta'],'notaConfronta',2);
		$res[2] = ValidateController::string($data['promocion'],'promocion',2);
		$res[3] = ValidateController::string($data['extemporaneo'],'extemporaneo',2);
		$res[4] = ValidateController::alphaNumeric($data['numDocumento'],'numDocumento',20);
		$res[5] = ValidateController::alphaNumeric($data['fDocumento'],'fDocumento',10);
		$res[6] = ValidateController::alphaNumeric($data['fRecepcion'],'fRecepcion',10);
		$res[7] = ValidateController::alphaNumeric($data['hRecepcion'],'hRecepcion',5);
		$res[8] = ValidateController::string($data['idRemitente'],'idRemitente',20);
		$res[9] = ValidateController::string($data['idTurnado'],'idTurnado',10);
		$res[10] = ValidateController::string($data['estatus'],'estatus',10);


		$res[11] = ValidateController::number($data['subDocumento'],'subDocumento',true);
		$res[12] = ValidateController::number($data['cveAuditoria'],'cveAuditoria',true);
		$res[13] = ValidateController::number($data['folio'],'folio',true);
		$res[14] = ValidateController::number($data['subFolio'],'subFolio',false);
		$res[15] = ValidateController::number($data['anexos'],'anexos',false);
		$res[16] = ValidateController::number($data['idCaracter'],'idCaracter',true);
		$res[17] = ValidateController::number($data['idAccion'],'idAccion',true);

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


		$res[0] = ValidateController::alphaNumeric($data['numDocumento'],'numDocumento',20);
		$res[1] = ValidateController::number($data['anexos'],'anexos',false);
		$res[2] = ValidateController::alphaNumeric($data['fDocumento'],'fDocumento',10);
		$res[3] = ValidateController::alphaNumeric($data['fRecepcion'],'fRecepcion',10);
		$res[4] = ValidateController::alphaNumeric($data['hRecepcion'],'hRecepcion',5);
		$res[5] = ValidateController::number($data['idCaracter'],'idCaracter',true);
		$res[6] = ValidateController::number($data['idAccion'],'idAccion',true);
		$res[7] = ValidateController::string($data['idTurnado'],'idTurnado',10);
		$res[8] = ValidateController::string($data['estatus'],'estatus',10);
		$res[9] = ValidateController::number($data['idVolante'],'idVolante',true);
		$res[10] = ValidateController::number($data['subDocumento'],'subDocumento',true);


		foreach ($res as $key => $value) {
			if(!empty($value)){
				array_push($final,$value);
			}
		}


		return $final;

	}
	

	public function validate_process($data){

		$valida = $this->validate($data);
		$duplicate = $this->duplicate($data);
		$datos_director_area = BaseController::get_data_area($data['idTurnado']);

		$final = [];

		if(!empty($valida[0])){
			return $valida;
		
		} elseif (!empty($duplicate[0])) {
			return $duplicate;
		
		} elseif($datos_director_area->isEmpty()){

			$errors['campo'] = 'Puestos Juridico';
			$errors['message'] = 'EL Director de Area No Ha sido dado de alta en el Sistema';
		
			$final[0] = $errors;
			return $final;
		} else {

			return $final;
		}
		
	}

	public function get_usrid_boss_area($turnado){
		
		$puestos = PuestosJuridico::select('rpe')
			->where('idArea',"$turnado")
			->where('titular','SI')
			->get();
		
		$jefe_area_rpe = $puestos[0]['rpe'];

		
		return $jefe_area_rpe;


	}

	public function send_notificaciones(array $data) {

		$nombre = BaseController::get_nombre_subDocumento($data['subDocumento']);
		
		$rpe = $this->get_usrid_boss_area($data['idTurnado']);
		
		$datos_boss = BaseController::get_usrId($rpe);

		$mensaje = 'Mensaje enviado a: '.$datos_boss['nombre'].
				"\nHas recibido un ".$nombre.
				"\nCon el folio: ".$data['folio'];
				
		BaseController::notificaciones($datos_boss['idUsuario'],$mensaje);
	}

	public function send_notificaciones_varios(array $data){

		$nombre = BaseController::get_nombre_subDocumento($data['subDocumento']);

		$rpe = $this->get_usrid_boss_area($data['idTurnado']);

		$datos_boss = BaseController::get_usrId($rpe);

		$users = BaseController::get_users_notifica($rpe);
		
		$mensaje = 'Mensaje enviado a: '.$datos_boss['nombre'].
				"\nHas recibido un ".$nombre.
				"\nCon el folio: ".$data['folio'];

		foreach ($users as $key => $value) {
			BaseController::notificaciones($users[$key]['idUsuario'],$mensaje);
		}
	}
	
	
}