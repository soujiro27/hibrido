<?php 
namespace App\Controllers;
use App\Models\Api\Auditorias;
use App\Models\Api\AuditoriasUnidades;
use App\Models\Api\Unidades;

use App\Models\Catalogos\PuestosJuridico;
use App\Models\Catalogos\SubTiposDocumentos;
use App\Models\Catalogos\Textos;

use App\Models\Documentos\AnexosJuridico;
use App\Models\Documentos\TurnadosJuridico;

use App\Models\Volantes\Remitentes;
use App\Models\Volantes\Volantes;
use App\Models\Volantes\VolantesDocumentos;
use App\Models\Volantes\Usuarios;
use App\Models\Volantes\Notificaciones;
use App\Models\Volantes\Areas;

use Sirius\Validation\Validator;
use Carbon\Carbon;

class ApiController {

	#trae los subdocumentos que concuerden con el documento (idTipoDocto) de la tabla de sia_catSubTiposDocumentos
	public function load_subDocumentos_volantes(array $dato){
		$tipo = $dato['tipo'];
		$auditoria = $dato['audi'];
		$res = SubTiposDocumentos::select('idSubTipoDocumento as valor','nombre')
				->where('idTipoDocto',"$tipo")
				->where('auditoria',"$auditoria")
				->where('estatus','ACTIVO')
				->get();
		if($res->isEmpty()){
			$res = array('error' => 'No hay ningun Sub-Documento Asignado', );
		}
		echo json_encode($res);
	}


	#trae los datos de la auditoria mediante el numero 
	public function load_datos_auditoria(array $dato){
				
		$cuenta = substr($dato['cuenta'], -2);

		if(empty($dato['clave'])){
			$datosAuditoria = array('error' => 'La Auditoria NO existe', );
		}else{
			$cveAuditoria = 'ASCM/'.$dato['clave'].'/'.$cuenta;
			
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


		$cuenta = substr($dato['cuenta'], -2);

		if(empty($dato['clave']))
		{
			$turnos  = array('error' => 'No Hay Datos', );
		}else{

			$clave = 'ASCM/'.$dato['clave'].'/'.$cuenta;

			$datos = Auditorias::select('idAuditoria', 'tipoAuditoria','rubros')
			->where('clave',"$clave")
			->get();
			
			$idAuditoria = $datos[0]['idAuditoria'];		

			$turnos = VolantesDocumentos::select('sub.nombre','t.idAreaRecepcion')
			->join('sia_volantes as v','v.idVolante','sia_volantesDocumentos.idVolante')
			->join('sia_catSubTiposDocumentos as sub','sub.idSubTipoDocumento','sia_volantesDocumentos.idSubTipoDocumento')
			->join('sia_TurnadosJuridico as t','t.idVolante','sia_volantesDocumentos.idVolante')
			->where('sia_volantesDocumentos.cveAuditoria',"$idAuditoria")
			->where('t.idTipoTurnado','E')
			->get();
		}
		echo json_encode($turnos);
	
	}

	public function load_turnado_volantes(array $data) {
		$tipo = $data['tipo'];
		$sigla = $data['siglas'];
         $remitentes  = Remitentes::where('estatus','=','ACTIVO')
        ->where('tipoRemitente','=',"$tipo")
        ->where('siglasArea','like',"%".$sigla."%")
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

	public function load_areas() {

		$res = [];
		$turnados  = Areas::where('idAreaSuperior','DGAJ')->where('estatus','ACTIVO')->get();
		$turnadoDireccion = array ('idArea'=>'DGAJ','nombre' => 'DIRECCIÓN GENERAL DE ASUNTOS JURIDICOS');
		
		foreach ($turnados as $key => $value) {
			$res[$key] = $turnados[$key];
		}
		array_push($res,$turnadoDireccion);

		echo json_encode($res);
	}


	public function load_areas_update($data) {
		
		$idVolante = $data['idVolante']; 
		$datos = TurnadosJuridico::where('idVolante',"$idVolante")->get();
		echo json_encode($datos);

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
							->where('idTipoTurnado','I')
							->get();
	
		$turnados_recibidos = TurnadosJuridico::select('idTurnadoJuridico')
							->where('idVolante',"$idVolante")
							->where('usrAlta',"$idUsuario_envio")
							->where('idUsrReceptor',"$idUsuario")
							->where('idTipoTurnado','I')
							->get();

		$propios = $this->array_turnados($turnados_propios);
		$recibidos = $this->array_turnados($turnados_recibidos);

		$res = array_merge($propios,$recibidos);


		$turnados = TurnadosJuridico::select('sia_TurnadosJuridico.*','a.archivoFinal','u.saludo','u.nombre','u.paterno','u.materno')
					->leftJoin('sia_AnexosJuridico as a ','a.idTurnadoJuridico','=','sia_TurnadosJuridico.idTurnadoJuridico')
					->join('sia_usuarios as u','u.idUsuario','=','sia_TurnadosJuridico.usrAlta')
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

	public function load_puestos_juridicos_area(){

		$idArea = $_SESSION['idArea'];
		$personal = PuestosJuridico::where('idArea',"$idArea")
									->where('titular','No')
									->where('estatus','ACTIVO')
									->get();
		echo json_encode($personal);

	}

	public function load_textos_promocion_acciones(){
		$textos = Textos::where('idTipoDocto','OFICIO')->where('estatus','ACTIVO')->get();
		echo json_encode($textos);

	}


}