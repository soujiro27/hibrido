<?php 
namespace App\Controllers\Volantes;
use App\Models\Volantes\VolantesDocumentos;
use App\Models\Volantes\Volantes;
use App\Models\Catalogos\TiposDocumentos;
use App\Controllers\Template;
use Sirius\Validation\Validator;
use Carbon\Carbon;
use App\Models\Catalogos\Caracteres;
use App\Models\Volantes\Areas;
use App\Models\Catalogos\Acciones;
use App\Models\Catalogos\PuestosJuridico;
use App\Models\Volantes\Usuarios;
use App\Models\Volantes\Notificaciones;

class volantesController extends Template{
	
	private $modulo = 'Volantes';

	#crea la tabla con los registros
	public function index(){

		$volantes = Volantes::select('sia_Volantes.*','vd.cveAuditoria','a.clave','sub.nombre','t.estadoProceso')
		->join('sia_VolantesDocumentos as vd','vd.idVolante','=','sia_volantes.idVolante')
		->join('sia_turnosJuridico as t','t.idVolante','=','sia_Volantes.idVolante'  )
		->join('sia_auditorias as a','a.idAuditoria','=','vd.cveAuditoria')
		->join('sia_catSubTiposDocumentos as sub','sub.idSubTipoDocumento','=','vd.idSubTipoDocumento')
		->where('sub.auditoria','SI')
		->orderBy('fRecepcion', 'desc')
		->get();
		
		echo $this->render('Volantes/volantes/index.twig',[
			'sesiones'   => $_SESSION,
			'modulo'	 => $this->modulo,
			'volantes' => $volantes,
		]);
	}

	#manda a traer el formulario de insercion
	public function create($message,$errors){
		
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
			'mensaje' => $message,
			'errors' => $errors
		]);
	}


	#guarda un nuevo registro
	public function save(array $data, $app) {
		
		$data['estatus'] =  'ACTIVO';
		if ($this->duplicate($data)) {
			if(empty($this->validate($data))) {

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
					'destinatario' => $data['destinatario'],
					'asunto' => $data['asunto'],
					'idCaracter' => $data['idCaracter'],
					'idTurnado' => $data['idTurnado'],
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
					'idSubTipoDocumento' => $data['idSubTipoDocumento'],
					'notaConfronta' => $data['notaConfronta'],
					'usrAlta' => $_SESSION['idUsuario'],
					'fAlta' => Carbon::now('America/Mexico_City')->format('Y-d-m H:i:s')
				]);

				$volantesDocumentos->save();
				$this->notificaciones($data['idTurnado']);
				$app->redirect('/SIA/juridico/Volantes');
			} else {
				$this->create($message = false,$this->validate($data));
			}

		} else {
			$this->create('El numero de Folio Y/O SubFolio ya fue Asignado',$errors = false);
		}
	
	}

	public function createUpdate($id, $app, $message, $errors){
		$volantes = Volantes::find($id);
		$turnados  = Areas::where('idAreaSuperior','DGAJ')->where('estatus','ACTIVO')->get();
		$turnadoDireccion = array ('idArea'=>'DGAJ','nombre' => 'DIRECCIÓN GENERAL DE ASUNTOS JURIDICOS');
		$acciones = Acciones::where('estatus','ACTIVO')->get();
		$caracteres = Caracteres::where('estatus','ACTIVO')->get();

		$err = false;

		echo $this->render('Volantes/volantes/update.twig',[
            'sesiones'=> $_SESSION,
            'volantes'=> $volantes,
            'caracteres' => $caracteres,
            'acciones' => $acciones,
            'turnados' => $turnados,
            'direccionGral' => $turnadoDireccion,
			'errors' => $errors,
			'close' => true,
			'mensaje' => $message,
            'modulo' => 'Volantes',
        ]);
		
	}

	public function update(array $data, $app) {
		$id = $data['idVolante'];

		Volantes::find($id)->update([
			'numDocumento' => $data['numDocumento'],
			'anexos' => $data['anexos'],
			'fDocumento' => $data['fDocumento'],
			'fRecepcion' => $data['fRecepcion'],
			'hRecepcion' => $data['hRecepcion'],
			'asunto' => $data['asunto'],
			'idCaracter' => $data['idCaracter'],
			'idTurnado' => $data['idTurnado'],
			'idAccion' => $data['idAccion'],
			'usrModificacion' => $_SESSION['idUsuario'],
			'fModificacion' => Carbon::now('America/Mexico_City')->format('Y-d-m H:i:s'),
			'estatus' => $data['estatus']
		]);
		$this->notificaciones( $data['idTurnado']);
		$app->redirect('/SIA/juridico/Volantes');
	}

	public function duplicate(array $data) {
		$folio = $data['folio'];
		$subFolio = $data['subFolio'];
		$fecha = date("Y",strtotime($data['fRecepcion']));
		$res = Volantes::where('folio',"$folio")
						->where('subFolio',"$subFolio")
						->whereYear('fRecepcion',"$fecha")
						->count();

		if($res == 0) {
			return true;
		} else {
			return false;
		}
	}

	public function validate (array $data){
		$errors = [];
		$validator = new \Sirius\Validation\Validator;
		
		$validator->add(
			array(
				'idTipoDocto' => 'required | Alpha | MaxLength(10)(Excede los caracteres permitidos)',
				'idSubTipoDocumento' => 'required | MaxLength(2)(Excede los caracteres permitidos)',
				'notaConfronta' => 'required | MaxLength(2)(Excede los caracteres permitidos)',
				'numDocumento' => 'required | MaxLength(50)(Excede los caracteres permitidos)',
				'promocion' => 'required | MaxLength(2)(Excede los caracteres permitidos)',
				'cveAuditoria' => 'required | MaxLength(4)(Excede los caracteres permitidos)',
				'fDocumento' => 'required',
				'anexos' => 'required | Number',
				'fRecepcion' => 'required ',
				'hRecepcion' => 'required',
				'idRemitente' => 'required | MaxLength(25)(Excede los caracteres permitidos)',
				'destinatario' => 'required | MaxLength(50)(Excede los caracteres permitidos)',
				'asunto' => 'required',
				'idCaracter' => 'required | Number',
				'idTurnado' => 'required | MaxLength(25)(Excede los caracteres permitidos)',
				'idAccion' => 'required | Number',
				'folio' => 'required | Number',
				'subFolio' => 'required | Number',
				'extemporaneo' => 'required | MaxLength(25)(Excede los caracteres permitidos)'
			)
		);

		if(!$validator->validate($data)){
			$errors = $validator->getMessages();
			return $errors;
		}else{

			return $errors;
		}	
	}


	public function notificaciones($turnado) {

		$rpe_boss = $this->get_rpe_boss($turnado);
		$jefe_Area_idUsuario = $this->Usuario($rpe_boss);
		$users = $this->get_users_notifica($rpe_boss);
		$notifica = $this->create_array($jefe_Area_idUsuario,$users);

		foreach ($notifica as $key => $value) {
			$notifica = new Notificaciones([
				'idNotificacion' => '1',
				'idUsuario' => $value,
				'mensaje' => 'mensaje',
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

	public function get_rpe_boss($turnado){
		$puestos = PuestosJuridico::select('rpe')
			->where('idArea',"$turnado")
			->where('titular','SI')
			->get();
		$jefe_area_rpe = $puestos[0]['rpe'];
		return $jefe_area_rpe;
	}

	public function get_users_notifica($rpe){
		 $usuarios_notifica = PuestosJuridico::select('rpe')
            ->where('usrAsisteA',"$rpe")
            ->get();

        if($usuarios_notifica->isEmpty()){
           $usuarios = array(); 
        } else {
 
            $cont = 0;
            foreach ($usuarios_notifica as $key => $value) {
                $usuarios[$cont] = $this->Usuario($usuarios_notifica[$key]['rpe']);
                $cont++;
            }
        }
        return $usuarios;


	}

	public function create_array($boss,$users){
		$jefe[0] = $boss;
		$res =  array_merge($jefe,$users);
		return $res;
	}

	public function Usuario($rpe) {
		$idUsuario = Usuarios::select('idUsuario')
					->where('idEmpleado',"$rpe")
					->get();
		return $idUsuario[0]['idUsuario'];
	}
}