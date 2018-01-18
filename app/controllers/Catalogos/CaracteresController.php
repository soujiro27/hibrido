<?php 
namespace App\Controllers\Catalogos;

use App\Models\Catalogos\Caracteres;
use App\Controllers\Template;
use Sirius\Validation\Validator;
use Carbon\Carbon;

class CaracteresController extends Template {

	private $modulo = 'Caracteres';

	#obtiene los registros para la tabla principal
	public function index(){
		$caracteres = Caracteres::all();
		echo $this->render('Catalogos/Caracteres/index.twig',[
			'sesiones'   => $_SESSION,
			'caracteres' => $caracteres,
			'modulo'	 => $this->modulo

		]);
	}

	#crea el formulario de insercion de un nuevo registro
	public function create($errors, $message){
		echo $this->render('Catalogos/Caracteres/create.twig',[
			'sesiones'   => $_SESSION,
			'modulo' => $this->modulo,
			'errors' => $errors,
			'mensaje' => $message
		]);
	}

	#guarda un registro en la base de datos
	public function save(array $data, $app){
		$data['estatus'] = 'ACTIVO';
		$errors = $this->validate($data);
		if($this->duplicate($data)){			
			if(empty($errors)){
				$caracter = new Caracteres([
	            'siglas' =>$data['siglas'],
	            'nombre' => $data['nombre'],
	            'usrAlta' => $_SESSION['idUsuario'],
	            'fAlta' => Carbon::now('America/Mexico_City')->format('Y-d-m H:i:s')
	            ]);
	            $caracter->save();
	            $app->redirect('/SIA/juridico/Caracteres');
			}else{
				$this->create($errors,false);
			}
		}else{
			$this->create(false,'Registro Duplicado');
		}
		
	}

	#crea el formulario de actualizacion
	public function createUpdate($id,$app,$errors, $message){
		$caracter = Caracteres::find($id);
		if(empty($caracter)){
			$app->render('/jur/public/404.html');
		}else{
			echo $this->render('Catalogos/Caracteres/update.twig',[
			'sesiones'   => $_SESSION,
			'caracter' => $caracter,
			'modulo' => $this->modulo,
			'mensaje' => $message,
			'errors' => $errors
		]);
		}
		
	}

	#hace la actualizacion del registro
	public function update(array $data, $app){
		$id = $data['idCaracter'];
		$errors = $this->validate($data);
		if($this->duplicate($data)){
			if(empty($errors)){
				Caracteres::find($id)->update([
					'siglas' => $data['siglas'],
            		'nombre' => $data['nombre'],
              		'usrModificacion' => $_SESSION['idUsuario'],
              		'fModificacion' => Carbon::now('America/Mexico_City')->format('Y-d-m H:i:s'),
              		'estatus' => $data['estatus']
				]);
				$app->redirect('/SIA/juridico/Caracteres');
			}else{
				$this->createUpdate($id,$app,$errors,false);
			}
		}else{
			$this->createUpdate($id,$app,false,'Registro Duplicado');
		}
	}


	#valida el formulario 
	public function validate(array $data){
		$errors = [];
		$validator = new \Sirius\Validation\Validator;
		$validator->add(
			array(
				'siglas' => 'required | Alpha | MaxLength(2)(Excede los caracteres permitidos)',
				'nombre' => 'required | Alpha | MaxLength(10)(Excede los caracteres permitidos)'
			)
		);
		$validator->validate($data);
		$errors = $validator->getMessages();
		return $errors;
	}

	#valida que no haya registros duplicados
	public function duplicate(array $data){
		$siglas = $data['siglas'];
		$nombre = $data['nombre'];
		$estatus = $data['estatus'];
		$caracter = Caracteres::where('siglas',"$siglas")
		->where('nombre',"$nombre")
		->where('estatus',"$estatus")
		->count();
		if($caracter == 0){
			return true;
		}else{
			return false;
		}
	}
	
}

