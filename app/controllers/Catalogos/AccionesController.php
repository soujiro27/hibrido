<?php 
namespace App\Controllers\Catalogos;

use Sirius\Validation\Validator;
use Carbon\Carbon;
use Sirius\Validation\ErrorMessage;
use App\Models\Catalogos\Acciones;
use App\Controllers\Template;

class AccionesController extends Template{
	
	private $modulo = 'Acciones';
	private $filejs = 'Catalogos';

	#crea la tabla con los registros
	public function index(){
		$acciones = Acciones::all();
		echo $this->render('Catalogos/Acciones/index.twig',[
			'sesiones'   => $_SESSION,
			'acciones' => $acciones,
			'modulo'	 => $this->modulo,
			'filejs' => $this->filejs
		]);
	}

	#manda a traer el formulario de insercion
	public function create($message,$errors){
		echo $this->render('Catalogos/Acciones/form.twig',[
			'sesiones' => $_SESSION,
			'modulo' => $this->modulo,
			'filejs' => $this->filejs,
			'mensaje' => $message,
			'errors' => $errors
		]);
	}

	#guarda un nuevo registro
	public function save(array $data, $app){
		$data['estatus'] =  'ACTIVO';
		$errors = [];
		if($this->duplicate($data)){
			if(empty($this->validate($data))){
				$acciones = new Acciones([
					'nombre' => $data['nombre'],
					'usrAlta' => $_SESSION['idUsuario'],
					'estatus' => 'ACTIVO',
	            	'fAlta' => Carbon::now('America/Mexico_City')->format('Y-d-m H:i:s')
				]);

				$acciones->save();
				$app->redirect('/SIA/juridico/Acciones');
			}else{
				
				$test =$this->validate($data);
				var_dump($test);
				/*foreach ($test as $key => $value) {
					foreach ($test[$key] as $k => $v) {
						var_dump($v);
					}
				}*/
			}
		}else{
			//$error[0] = ('' => , );	
		}
	}

	#crea el formulario del update
	public function createUpdate($id,$app,$message,$errors){
		$accion = Acciones::find($id);
		if(empty($accion)){
			$app->render('/jur/public/404.html');
		}else{
			echo $this->render('Catalogos/Acciones/update.twig',[
			'sesiones'   => $_SESSION,
			'accion' => $accion,
			'modulo' => $this->modulo,
			'mensaje' => $message,
			'errors' => $errors
		]);
		}
	}

	#hace el update del registro
	public function update(array $data, $app){
		$id = $data['idAccion'];
		if($this->duplicate($data)){
			if(empty($this->validate($data))){
				Acciones::find($id)->update([
            		'nombre' => $data['nombre'],
              		'usrModificacion' => $_SESSION['idUsuario'],
              		'fModificacion' => Carbon::now('America/Mexico_City')->format('Y-d-m H:i:s'),
              		'estatus' => $data['estatus']
				]);
				$app->redirect('/SIA/juridico/Acciones');
			}else{
				$this->createUpdate($id,$app,false,$this->validate($data));
			}
		}else{
			$this->createUpdate($id, $app, 'Registro Duplicado', $errors = false);
		}
	}

	
	#valida que no haya registros duplicados
	public function duplicate(array $data){
		$nombre = $data['nombre'];
		$estatus = $data['estatus'];
		$caracter = Acciones::where('nombre',"$nombre")
		->where('estatus',"$estatus")
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
				'nombre' => 'required | Alpha | MaxLength(3)(Excede los caracteres permitidos)'
			)
		);

		if(!$validator->validate($data)){
			$errors = $validator->getMessages('nombre');
			return $errors;
		}else{

			return $errors;
		}
	}

}