<?php 
namespace App\Controllers;
use App\Models\Catalogos\SubTiposDocumentos;
use App\Models\Api\Auditorias;
use App\Models\Api\AuditoriasUnidades;
use App\Models\Api\Unidades;
use App\Models\Catalogos\PuestosJuridico;
use App\Models\Documentos\AnexosJuridico;
use App\Models\Documentos\TurnadosJuridico;
use App\Models\Volantes\Remitentes;
use App\Models\Volantes\Volantes;
use App\Models\Volantes\VolantesDocumentos;
use App\Models\Volantes\Usuarios;
use App\Models\Volantes\Notificaciones;

use Sirius\Validation\Validator;
use Carbon\Carbon;

class ApiController {

	#trae los subdocumentos que concuerden con el documento (idTipoDocto) de la tabla de sia_catSubTiposDocumentos
	public function load_subDocumentos_volantes(array $dato){
		$tipo = $dato['dato'];
		$res = SubTiposDocumentos::select('idSubTipoDocumento as valor','nombre')
				->where('idTipoDocto',"$tipo")
				->where('auditoria','SI')
				->where('estatus','ACTIVO')
				->get();
		if($res->isEmpty()){
			$res = array('error' => 'No hay ningun Sub-Documento Asignado', );
		}
		echo json_encode($res);
	}

	public function load_subDocumentos_volantesDiversos(array $dato){
		$tipo = $dato['dato'];
		$res = SubTiposDocumentos::select('idSubTipoDocumento as valor','nombre')
				->where('idTipoDocto',"$tipo")
				->where('auditoria','NO')
				->where('estatus','ACTIVO')
				->get();
		if($res->isEmpty()){
			$res = array('error' => 'No hay ningun Sub-Documento Asignado', );
		}
		echo json_encode($res);
	}

	#trae los datos de la auditoria mediante el numero 
	public function load_datos_auditoria(array $dato){

		if(empty($dato['clave'])){
			$datosAuditoria = array('error' => 'La Auditoria NO existe', );
		}else{
			$cveAuditoria = 'ASCM/'.$dato['clave'].'/'.$dato['cuenta'];
			
			$datos = Auditorias::select('idAuditoria', 'tipoAuditoria','rubros','idArea')
			->where('clave',"$cveAuditoria")
			->get();

			if($datos->isEmpty()){
				$datosAuditoria = array('error' => 'La Auditoria NO existe', );
			}else{
				$idAuditoria = $datos[0]['idAuditoria'];

				$unidades = AuditoriasUnidades::select('idCuenta','idSector','idSubsector','idUnidad')
				->where('idAuditoria',"$idAuditoria")
				->get();

				$sector = $unidades[0]['idSector'];
				$subSector = $unidades[0]['idSubsector'];
				$unidad = $unidades[0]['idUnidad'];
				$cuenta = $unidades[0]['idCuenta'];

				$unidades = Unidades::select('nombre')
				->where('idSector',"$sector")
				->where('idSubsector',"$subSector")
				->where('idUnidad',"$unidad")
				->where('idCuenta',"$cuenta")
				->get();

				
				$datosAuditoria = array(
					'sujeto' => $unidades[0]['nombre'],
					'tipo' => $datos[0]['tipoAuditoria'],
					'rubro' => $datos[0]['rubros'],
					'id' => $datos[0]['idAuditoria'],
					'idArea' => $datos[0]['idArea']
				);		
			}
		}

		
		echo json_encode($datosAuditoria);
	} 

	#trae los datos de aquien fue turnado el ifa, el irac y la confronta por numero de auditoria 
	public function load_turnado_auditoria(array $dato) {

		if(empty($dato['clave']))
		{
			$turnos  = array('error' => 'No Hay Datos', );
		}else{

			$clave = 'ASCM/'.$dato['clave'].'/'.$dato['cuenta'];

			$datos = Auditorias::select('idAuditoria', 'tipoAuditoria','rubros')
			->where('clave',"$clave")
			->get();
			
			$idAuditoria = $datos[0]['idAuditoria'];		

			$turnos = VolantesDocumentos::select('sub.nombre','v.idTurnado')
			->join('sia_volantes as v','v.idVolante','sia_volantesDocumentos.idVolante')
			->join('sia_catSubTiposDocumentos as sub','sub.idSubTipoDocumento','sia_volantesDocumentos.idSubTipoDocumento')
			->where('sia_volantesDocumentos.cveAuditoria',"$idAuditoria")
			->get();
		}
		echo json_encode($turnos);
	
	}

	public function load_turnado_volantes(array $data) {
		$tipo = $data['tipo'];
		$sigla = $data['siglas'];
         $remitentes  = Remitentes::where('estatus','=','ACTIVO')
        ->where('tipoRemitente','=',"$tipo")
        ->where('siglasArea','like',"%".$sigla)
        ->get();
        echo json_encode($remitentes);
	}

	public function load_puestos_juridico(array $data) {
		$idVolante = $data['idVolante'];
		$volantes = Volantes::select('idTurnado')->where('idVolante',"$idVolante")->get();
		$area = $volantes[0]['idTurnado'];

		$personal = PuestosJuridico::where('idArea',"$area")
									->where('titular','No')
									->where('estatus','ACTIVO')
									->get();
		echo json_encode($personal);
	}

	public function upload_files($idVolante,$idTurnadoJuridico,$file) {
		$directory ='jur/files/documentos/'.$idVolante;
		
		if(!empty($file['archivo']['name'])){

			$nombre = $file['archivo']['name'];
			$extension = explode('.',$nombre);
			$final = ApiController::create_name_file($extension[1]);


			if(!file_exists($directory)){
				mkdir($directory,0777,true);
			} 


			$anexo = new AnexosJuridico([
				'idTurnadoJuridico' => $idTurnadoJuridico,
				'archivoOriginal' => $nombre,
				'archivoFinal' => $final,
				'idTipoArchivo' => $extension[1],
	            'usrAlta' => $_SESSION['idUsuario'],
	            'estatus' => 'ACTIVO',
	            'fAlta' => Carbon::now('America/Mexico_City')->format('Y-d-m H:i:s')
			]);

			if($anexo->save()){
					move_uploaded_file($file['archivo']['tmp_name'],$directory.'/'.$final);
					return true;
				
			}else{
				return false;
			}
		} else {
			return true;
		}
			
	
	}

	
	public function validate_file($name,$size) {
		$errors = [];

		if(strlen($name) > 50) {
			$errors['Anexar']['message'] = 'El nombre es demasiado Grande';
		}

		if($size > 8388608) {
			$errors['Anexar']['message'] = 'El tamaño del documento no debe de ser mayor a 10MB';
		}

		return $errors;

	}

	public function create_name_file($extension){
		$hora = Carbon::now('America/Mexico_City')->format('H:i:s');
		$fecha = Carbon::now('America/Mexico_City')->format('Y-d-m');
		$array_hora  = explode(':',$hora);
		$array_fecha = explode('-',$fecha);

		$final ='';
		foreach($array_fecha as $valor) { 
			$final = $final . $valor . '_';
		}

	foreach($array_hora as $valor) { 
			$final = $final . $valor . '_';
		}		

		$final = $final . '.'.$extension;
		
		return $final;
		
	}

	public function load_documentos_turnados(array $data) {
		$idUsuario = $_SESSION['idUsuario'];
		$idVolante = $data['idVolante'];
		$idPuesto = $data['idPuesto'];

		$puestos = PuestosJuridico::select('u.idUsuario')
					->join('sia_usuarios as u','u.idEmpleado','=','sia_PuestosJuridico.rpe')
					->where('sia_PuestosJuridico.idPuestoJuridico',"$idPuesto")
					->get();
		$idUsuario_envio = $puestos[0]['idUsuario'];
		

		$turnados_propios = TurnadosJuridico::select('idTurnadoJuridico')
							->where('idVolante',"$idVolante")
							->where('usrAlta',"$idUsuario")
							->where('idUsrReceptor',"$idUsuario_envio")
							->get();
	
		$turnados_recibidos = TurnadosJuridico::select('idTurnadoJuridico')
							->where('idVolante',"$idVolante")
							->where('usrAlta',"$idUsuario_envio")
							->where('idUsrReceptor',"$idUsuario")
							->get();

		$propios = $this->array_turnados($turnados_propios);
		$recibidos = $this->array_turnados($turnados_recibidos);

		$res = array_merge($propios,$recibidos);


		$turnados = TurnadosJuridico::select('sia_TurnadosJuridico.*','a.archivoFinal')
					->leftJoin('sia_AnexosJuridico as a ','a.idTurnadoJuridico','=','sia_TurnadosJuridico.idTurnadoJuridico')
					->whereIn('sia_TurnadosJuridico.idTurnadoJuridico',$res)
					->orderBy('sia_TurnadosJuridico.fAlta','DESC')
					->get();

		echo json_encode($turnados);
	}

	public function array_turnados($data) {
		$id = [];
		foreach ($data as $key => $value) {
			array_push($id,$data[$key]['idTurnadoJuridico']);
		}
		return $id;
	}

	public function notificaciones($idUsuario,$idVolante) {
		$usuario[0] = $idUsuario;
		$rpe = ApiController::get_rpe($idUsuario);
		$reciben = ApiController::get_users_notificaciones($rpe);
		
		$all = array_merge($usuario,$reciben);

		$mensaje = ApiController::mensaje($idVolante);
		
		foreach ($all as $key => $value) {
			$notifica = new Notificaciones([
				'idNotificacion' => '1',
				'idUsuario' => $value,
				'mensaje' => $mensaje,
				'idPrioridad' => 'ALTA',
				'idImpacto' => 'MEDIO',
				'fLectura' => Carbon::now('America/Mexico_City')->format('Y-d-m H:i:s'),
				'usrAlta' => $_SESSION['idUsuario'],
				'fAlta' => Carbon::now('America/Mexico_City')->format('Y-d-m H:i:s'),
				'estatus' => 'ACTIVO',
				'situacion' => 'NUEVO',
				'identificador' => '1',
				'idCuenta' => 'CTA-2016',
				'idAuditoria' => '1',
				'idModulo' => 'Volantes',
				'referencia' => 'idVolante'
	 
			]);
			$notifica->save();
		}	
	}

	public function get_users_notificaciones($rpe){
		 $usuarios = PuestosJuridico::select('u.idUsuario')
		 			->join('sia_usuarios as u','u.idEmpleado','=','sia_PuestosJuridico.rpe')
            		->where('usrAsisteA',"$rpe")
            		->get();
        if($usuarios->isEmpty()){
        	$res = [];
        }else{

        	$cont = 0;
            foreach ($usuarios as $key => $value) {
                $res[$cont] = $usuarios[$key]['idUsuario'];
                $cont++;
            }

        }
        return $res;
	}

	public function get_rpe($idUsuario) {
		$usuarios = Usuarios::select('idEmpleado')->where('idUsuario',"$idUsuario")->get();
		$rpe = $usuarios[0]['idEmpleado'];
		return $rpe;
	}

	public function mensaje($idVolante) {
		$datos = Volantes::select('sia_Volantes.folio','sub.nombre')
				->join('sia_volantesDocumentos as vd','vd.idVolante','=','sia_Volantes.idVolante')
				->join('sia_catSubTiposDocumentos as sub','sub.idSubTipoDocumento','=','vd.idSubTipoDocumento')
				->where('sia_Volantes.idVolante',"$idVolante")
				->get();

		$mensaje = 'Tienes un nuevo turnado de un: '.$datos[0]['nombre'].' con el folio: '.$datos[0]['folio'];
		return $mensaje;
	}
	
}