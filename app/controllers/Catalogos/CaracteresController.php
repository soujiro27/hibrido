<?php 
namespace App\Controllers\Catalogos;

use Carbon\Carbon;

use App\Models\Catalogos\Caracteres;

use App\Controllers\Template;
use App\Controllers\ValidateController;

class CaracteresController extends Template {

	private $modulo = 'Caracteres';
	private $filejs = 'Caracteres';

	#obtiene los registros para la tabla principal
	public function index(){
		$caracteres = Caracteres::all();
		echo $this->render('Catalogos/Caracteres/index.twig',[
			'sesiones'   => $_SESSION,
			'caracteres' => $caracteres,
			'modulo'	 => $this->modulo,
			'filejs' => $this->filejs

		]);
	}

	#crea el formulario de insercion de un nuevo registro
	public function create(){
		echo $this->render('Catalogos/Caracteres/create.twig',[
			'sesiones'   => $_SESSION,
			'modulo' => $this->modulo,
			'filejs' => $this->filejs
		]);
	}

	#guarda un registro en la base de datos
	public function save(array $data, $app){
	
		$data['estatus'] =  'ACTIVO';
		$valida = $this->validate($data);
		$duplicado = $this->duplicate($data);

		if(empty($valida[0])){ 

			if(empty($duplicado[0])){
				
				$caracter = new Caracteres([
		            'siglas' =>$data['siglas'],
		            'nombre' => $data['nombre'],
		            'usrAlta' => $_SESSION['idUsuario']
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

	#crea el formulario de actualizacion
	public function createUpdate($id,$app){
		$caracter = Caracteres::find($id);
		if(empty($caracter)){
			$app->render('/jur/public/404.html');
		}else{
			echo $this->render('Catalogos/Caracteres/update.twig',[
			'sesiones'   => $_SESSION,
			'caracter' => $caracter,
			'modulo' => $this->modulo,
			'filejs' => $this->filejs
		]);
		}
		
	}

	#hace la actualizacion del registro
	public function update(array $data, $app){

		$id = $data['idCaracter'];
		$valida = $this->validate($data);
		$duplicado = $this->duplicate($data);

		if(empty($valida[0])){ 

			if(empty($duplicado[0])){
				
				Caracteres::find($id)->update([
					'siglas' => $data['siglas'],
            		'nombre' => $data['nombre'],
              		'usrModificacion' => $_SESSION['idUsuario'],
              		'fModificacion' => Carbon::now('America/Mexico_City')->format('Y-d-m H:i:s'),
              		'estatus' => $data['estatus']
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


	public function validate(array $data){
		$res= [];
		$nombre = ValidateController::string($data['nombre'],'nombre',10);
		$siglas = ValidateController::string($data['siglas'],'siglas',2);
		$res[0] = $nombre;
		$res[1] = $siglas;
		return $res;
	}

	public function success(){
		$sucess = [];
		$sucess['campo'] = 'success';
		$sucess['message'] = 'Registro Exitoso';

		$res[0] = $sucess;
		return $res;
	}

	#valida que no haya registros duplicados
	public function duplicate(array $data){
		$errors  = array();
		$res = [];

		$siglas = $data['siglas'];
		$nombre = $data['nombre'];
		$estatus = $data['estatus'];
		
		$caracter = Caracteres::where('nombre',"$nombre")
					->where('siglas',"$siglas")
					->where('estatus',"$estatus")
					->count();

		if($caracter > 0){
			$errors['campo'] = 'Duplicado';
			$errors['message'] = 'No puede haber registros Duplicados';
		}

		$res[0] = $errors;
		
		return $res;
	}
	
}

