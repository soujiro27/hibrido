<?php 
namespace App\Controllers\Catalogos;

use Carbon\Carbon;

use App\Models\Catalogos\Acciones;
use App\Controllers\Template;
use App\Controllers\ValidateController;



class AccionesController extends Template{
	
	private $modulo = 'Acciones';
	private $filejs = 'Acciones';

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
	public function create(){
		echo $this->render('Catalogos/Acciones/form.twig',[
			'sesiones' => $_SESSION,
			'modulo' => $this->modulo,
			'filejs' => $this->filejs,
		]);
	}

	#guarda un nuevo registro
	public function save(array $data, $app){
		
		$data['estatus'] =  'ACTIVO';
		$valida = $this->validate($data);
		$duplicado = $this->duplicate($data);

		if(empty($valida[0])){ 

			if(empty($duplicado[0])){
				$acciones = new Acciones([
					'nombre' => $data['nombre'],
					'usrAlta' => $_SESSION['idUsuario'],
					'estatus' => 'ACTIVO',
	            	'fAlta' => Carbon::now('America/Mexico_City')->format('Y-d-m H:i:s')
				]);

				$acciones->save();
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
		$accion = Acciones::find($id);
		if(empty($accion)){
			$app->render('/jur/public/404.html');
		}else{
			echo $this->render('Catalogos/Acciones/update.twig',[
			'sesiones'   => $_SESSION,
			'accion' => $accion,
			'modulo' => $this->modulo,
			'filejs' => $this->filejs
		]);
		}
	}

	#hace el update del registro
	public function update(array $data, $app){
		$id = $data['idAccion'];
		$valida = $this->validate($data);
		$duplicado = $this->duplicate($data);

		if(empty($valida[0])){ 

			if(empty($duplicado[0])){
				Acciones::find($id)->update([
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

	
	#valida que no haya registros duplicados
	public function duplicate(array $data){
		$errors  = array();
		$res = [];
		$nombre = $data['nombre'];
		$estatus = $data['estatus'];
		$caracter = Acciones::where('nombre',"$nombre")
		->where('estatus',"$estatus")
		->count();
		if($caracter > 0){
			$errors['campo'] = 'Duplicado';
			$errors['message'] = 'No puede haber registros Duplicados';
		}

		$res[0] = $errors;
		
		return $res;
	}

	#valida el formulario 
	public function validate(array $data){
		$res= [];
		$nombre = ValidateController::string($data['nombre'],'nombre',50);
		$res[0] = $nombre;
		return $res;
	}

	public function success(){
		$sucess = [];
		$sucess['campo'] = 'success';
		$sucess['message'] = 'Registro Exitoso';

		$res[0] = $sucess;
		return $res;
	}

}