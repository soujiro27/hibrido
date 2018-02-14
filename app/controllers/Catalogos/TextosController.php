<?php 
namespace App\Controllers\Catalogos;

use Carbon\Carbon;

use App\Controllers\Template;
use App\Controllers\ValidateController;

use App\Models\Catalogos\Textos;
use App\Models\Catalogos\TiposDocumentos;
use App\Models\Catalogos\SubTiposDocumentos;

class TextosController extends Template {

	private $modulo = 'Textos Juridico';
	private $filejs = 'Textos';
	private $ckeditor = true;
	
	#Crea la tabla principal 
	public function index(){
		$textos = Textos::all();
		echo $this->render('Catalogos/Textos/index.twig',[
			'sesiones' => $_SESSION,
			'modulo' => $this->modulo,
			'doctosTextos' => $textos,
			'filejs' => $this->filejs
		]);
	}

	#crea el formulario para un nuevo registro
	public function create(){

		$tiposDocumento = TiposDocumentos::where('tipo','=','JURIDICO')->where('estatus','=','ACTIVO')->get();
		

		echo $this->render('Catalogos/Textos/create.twig',[
			'sesiones' => $_SESSION,
			'modulo' => $this->modulo,
			'tiposDocumentos' => $tiposDocumento,
			'filejs' => $this->filejs,
			'ckeditor' => $this->ckeditor
		]);
	}

	#hace insercion de un nuevo registro
	public function save(array $data, $app){
		
		$valida = $this->validate($data);
		if(empty($valida)){ 
			$texto = new Textos([
				'idTipoDocto' => $data['documento'],
				'tipo' => 'JURIDICO',
				'idSubTipoDocumento' => $data['subDocumento'],
				'nombre' => 'TEXTO-JURIDICO',
				'texto' => $data['texto'],
				'fAlta' => Carbon::now('America/Mexico_City')->format('Y-d-m H:i:s'),
				'usrAlta' => $_SESSION['idUsuario']
			]);
			$texto->save();
			$sucess = $this->success();
			echo json_encode($sucess);

		} else{
			echo json_encode($valida);
		}
		
	
	}

	#crea el formulario para la actualizacion de un registro
	public function createUpdate($id, $app){
		$texto = Textos::find($id);
		
		$tiposDocumento = TiposDocumentos::where('tipo','=','JURIDICO')->where('estatus','=','ACTIVO')->get();
		
		$subtipos = SubTiposDocumentos::where('tipo','=','JURIDICO')
					->where('estatus','=','ACTIVO')
					->where('auditoria','SI')
					->get();
		if(empty($texto)){
			$app->render('/jur/public/404.html');
		}else{
			echo $this->render('Catalogos/Textos/update.twig',[
			'sesiones'   => $_SESSION,
			'doctoTexto' => $texto,
			'modulo' => $this->modulo,
			'documentos' => $tiposDocumento,
			'subtipos' => $subtipos,
			'filejs' => $this->filejs,
			'ckeditor' => $this->ckeditor
		]);
		}
	}

	#hace el update de un registro
	public function update(array $data, $app){
		$id = $data['idDocumentoTexto'];
		$valida = $this->validate($data);
		if(empty($valida)){ 
			Textos::find($id)->update([
				'idTipoDocto' => $data['documento'],
				'idSubTipoDocumento' => $data['subDocumento'],
				'texto' => $data['texto'],
				'usrModificacion' => $_SESSION['idUsuario'],
              	'fModificacion' => Carbon::now('America/Mexico_City')->format('Y-d-m H:i:s'),
              	'estatus' => $data['estatus']
			]);
			$sucess = $this->success();
			echo json_encode($sucess);

		} else{
			echo json_encode($valida);
		}
		
	}

#valida el formulario 
	public function validate(array $data){
		$final = [];
		$res[0] = ValidateController::string($data['documento'],'documento',20);
		$res[1] = ValidateController::number($data['subDocumento'],'subDocumento',true);
		$res[2] = ValidateController::alphaNumeric($data['texto'],'texto',350);

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

