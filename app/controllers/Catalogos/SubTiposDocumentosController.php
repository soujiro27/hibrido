<?php 
namespace App\Controllers\Catalogos;

use App\Models\Catalogos\SubTiposDocumentos;
use App\Models\Catalogos\TiposDocumentos;
use App\Controllers\Template;
use Sirius\Validation\Validator;
use Carbon\Carbon;

class SubTiposDocumentosController extends template {
	
	private $modulo = 'SubTipos-Documentos';

	#crea la tabla principal
	public function index()
	{
		$subTipos = SubTiposDocumentos::all();
		echo $this->render('catalogos/subTiposDocumentos/index.twig',[
			'sesiones'   => $_SESSION,
			'subTipos' => $subTipos,
			'modulo'	 => $this->modulo
		]);
	}

	#crea el formulario de insercion
	public function create($message, $errors){
		$tipos  = TiposDocumentos::where('tipo','JURIDICO')->where('estatus','ACTIVO')->get();
		echo $this->render('Catalogos/subTiposDocumentos/create.twig',[
			'sesiones'   => $_SESSION,
			'modulo' => $this->modulo,
			'tiposDocumentos' => $tipos,
			'mensaje' => $message,
			'errors' => $errors
		]);
	}

	#inserta un nuevo registro
	public function save(array $data, $app){
		$data['estatus'] = 'ACTIVO';
		$errors = $this->validate($data);
		if($this->duplicate($data)){			
			if(empty($errors)){
				$caracter = new SubTiposDocumentos([
	            'idTipoDocto' =>$data['idTipoDocto'],
	            'nombre' => $data['nombre'],
	            'auditoria' => $data['auditoria'],
	            'usrAlta' => $_SESSION['idUsuario'],
	            'fAlta' => Carbon::now('America/Mexico_City')->format('Y-d-m H:i:s')
	            ]);
	            $caracter->save();
	            $app->redirect('/SIA/juridico/subTiposDocumentos');
			}else{
				$this->create(false,$errors);
			}
		}else{
			$this->create('Registro Duplicado',false);
		}

	}

	#crea el formulario del update
	public function createUpdate($id,$app,$message,$errors){
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
			'mensaje' => $message,
			'errors' => $errors
		]);
		}
		
	}

	#hace el update del registro
	public function update(array $data, $app){
		$id = $data['idSubTipoDocumento'];
		$errors = $this->validate($data);
		if($this->duplicate($data)){
			if(empty($errors)){
				SubTiposDocumentos::find($id)->update([
					'idTipoDocto' =>$data['idTipoDocto'],
        			'nombre' => $data['nombre'],
                	'auditoria' => $data['auditoria'],
                	'estatus' => $data['estatus'],
                	'usrModificacion' => $_SESSION['idUsuario'],
                	'fModificacion' => Carbon::now('America/Mexico_City')->format('Y-d-m H:i:s')
				]);
				 $app->redirect('/SIA/juridico/SubTiposDocumentos');
			}else{
				$this->createUpdate($id,$app,false,$errors);
			}
		}else{
				$this->createUpdate($id,$app,'Registro Duplicado',false);
		}
	}

	public function duplicate(array $data){
		$tipo = $data['idTipoDocto'];
		$nombre = $data['nombre'];
		$estatus = $data['estatus'];
		$auditoria = $data['auditoria'];
		$caracter = SubTiposDocumentos::where('idTipoDocto',"$tipo")
		->where('nombre',"$nombre")
		->where('estatus',"$estatus")
		->where('auditoria',"$auditoria")
		->count();
		if($caracter == 0){
			return true;
		}else{
			return false;
		}
	}


	#valida el formulario 
	public function validate(array $data){
		$errors = [];
		$validator = new \Sirius\Validation\Validator;
		$validator->add(
			array(
				'idTipoDocto' => 'required',
				'nombre' => 'required | Alpha | MaxLength(50)(Excede los caracteres permitidos)',
				'auditoria' => 'required'
			)
		);
		$validator->validate($data);
		$errors = $validator->getMessages();
		return $errors;
	}
}
