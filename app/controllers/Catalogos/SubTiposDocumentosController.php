<?php 
namespace App\Controllers\Catalogos;

use Carbon\Carbon;

use App\Models\Catalogos\SubTiposDocumentos;
use App\Models\Catalogos\TiposDocumentos;

use App\Controllers\Template;
use App\Controllers\ValidateController;

class SubTiposDocumentosController extends template {
	
	private $modulo = 'SubTipos-Documentos';
	private $filejs = 'Catalogos';

	#crea la tabla principal
	public function index()
	{
		$subTipos = SubTiposDocumentos::all();
		echo $this->render('catalogos/subTiposDocumentos/index.twig',[
			'sesiones'   => $_SESSION,
			'subTipos' => $subTipos,
			'modulo'	 => $this->modulo,
			'filejs' => $this->filejs
		]);
	}

	#crea el formulario de insercion
	public function create(){
		$tipos  = TiposDocumentos::where('tipo','JURIDICO')->where('estatus','ACTIVO')->get();
		echo $this->render('Catalogos/subTiposDocumentos/create.twig',[
			'sesiones'   => $_SESSION,
			'modulo' => $this->modulo,
			'tiposDocumentos' => $tipos,
			'filejs' => $this->filejs
		]);
	}

	#inserta un nuevo registro
	public function save(array $data, $app){
		
		$valida = $this->validate($data);
		$data['estatus'] = 'ACTIVO';
		$duplicado = $this->duplicate($data);
		
		if(empty($valida)){ 

			if(empty($duplicado[0])){
				$caracter = new SubTiposDocumentos([
		            'idTipoDocto' =>$data['documento'],
		            'nombre' => $data['nombre'],
		            'auditoria' => $data['auditoria'],
		            'usrAlta' => $_SESSION['idUsuario'],
		            'fAlta' => Carbon::now('America/Mexico_City')->format('Y-d-m H:i:s')
		        ]);
		        $caracter->save();
				$sucess = $this->success();
				echo json_encode($sucess);
			} else {
				echo json_encode($duplicado);
			}

		} else{
			echo json_encode($valida);
		}
		
		

	}

	#crea el formulario del update
	public function createUpdate($id,$app){
		
		$tipos  = TiposDocumentos::where('tipo','JURIDICO')->where('estatus','ACTIVO')->get();
		$subTipo = SubTiposDocumentos::find($id);
		
		if(empty($subTipo)){
			
			$app->render('/jur/public/404.html');

		}else{

			echo $this->render('Catalogos/subTiposDocumentos/update.twig',[
				'sesiones'   => $_SESSION,
				'subtipos' => $subTipo,
				'modulo' => $this->modulo,
				'documentos' => $tipos,
				'filejs' => $this->filejs
			]);
		}
		
	}

	#hace el update del registro
	public function update(array $data, $app){
		
		$id = $data['idSubTipoDocumento'];
		$valida = $this->validate($data);
		$duplicado = $this->duplicate($data);
		
		if(empty($valida)){ 

			if(empty($duplicado[0])){
				
				SubTiposDocumentos::find($id)->update([
					'idTipoDocto' =>$data['documento'],
        			'nombre' => $data['nombre'],
                	'auditoria' => $data['auditoria'],
                	'estatus' => $data['estatus'],
                	'usrModificacion' => $_SESSION['idUsuario'],
                	'fModificacion' => Carbon::now('America/Mexico_City')->format('Y-d-m H:i:s')
				]);
				$sucess = $this->success();
				echo json_encode($sucess);

			} else {
				echo json_encode($duplicado);
			}

		} else{
			echo json_encode($valida);
		}
	}

	public function duplicate(array $data){
		$errors  = array();
		$res = [];
		
		$tipo = $data['documento'];
		$nombre = $data['nombre'];
		$estatus = $data['estatus'];
		$auditoria = $data['auditoria'];
		
		$caracter = SubTiposDocumentos::where('idTipoDocto',"$tipo")
		->where('nombre',"$nombre")
		->where('estatus',"$estatus")
		->where('auditoria',"$auditoria")
		->count();
		
		if($caracter > 0){
			$errors['campo'] = 'Duplicado';
			$errors['message'] = 'No puede haber registros Duplicados';
		}
		
		$res[0] = $errors;
		
		return $res;
	}

	public function validate(array $data){
		$final = [];
		$res[0] = ValidateController::string($data['documento'],'documento',20);
		$res[1] = ValidateController::string($data['nombre'],'subDocumento',50);
		$res[2] = ValidateController::string($data['auditoria'],'auditoria',2);

		foreach ($res as $key => $value) {
			if(!empty($value)){
				array_push($final,$value);
			}
		}
		
		return $final;

	}

	public function success(){
		$sucess = [];
		$sucess['campo'] = 'success';
		$sucess['message'] = 'Registro Exitoso';

		$res[0] = $sucess;
		return $res;
	}
}
