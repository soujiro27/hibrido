<?php 
namespace App\Controllers\Catalogos;

use App\Models\Catalogos\Textos;
use App\Models\Catalogos\TiposDocumentos;
use App\Controllers\Template;
use Sirius\Validation\Validator;
use Carbon\Carbon;

class TextosController extends Template {

	private $modulo = 'Textos Juridico';

	#Crea la tabla principal 
	public function index(){
		$textos = Textos::all();
		echo $this->render('Catalogos/Textos/index.twig',[
			'sesiones' => $_SESSION,
			'modulo' => $this->modulo,
			'doctosTextos' => $textos
		]);
	}

	#crea el formulario para un nuevo registro
	public function create($message,$errors){
		$tiposDocumento = TiposDocumentos::where('tipo','=','JURIDICO')->where('estatus','=','ACTIVO')->get();

		echo $this->render('Catalogos/Textos/create.twig',[
			'sesiones' => $_SESSION,
			'modulo' => $this->modulo,
			'mensaje' => $message,
			'errors' => $errors,
			'tiposDocumentos' => $tiposDocumento
		]);
	}

	#hace insercion de un nuevo registro
	public function save(array $data, $app){
		$errors = $this->validate($data);
		if(empty($errors)){
			$texto = new Textos([
				'idTipoDocto' => $data['idTipoDocto'],
				'tipo' => 'JURIDICO',
				'idSubTipoDocumento' => $data['idSubTipoDocumento'],
				'nombre' => 'TEXTO-JURIDICO',
				'texto' => $data['texto'],
				'fAlta' => Carbon::now('America/Mexico_City')->format('Y-d-m H:i:s'),
				'usrAlta' => $_SESSION['idUsuario']
			]);
				$acciones->save();
				$app->redirect('/SIA/juridico/DoctosTextos');
			}else{
				$this->create(false,$errors);
		}
	}

	#crea el formulario para la actualizacion de un registro
	public function createUpdate($id, $app, $message, $errors){
		$texto = Textos::find($id);
		$tiposDocumento = TiposDocumentos::where('tipo','=','JURIDICO')->where('estatus','=','ACTIVO')->get();

		if(empty($texto)){
			$app->render('/jur/public/404.html');
		}else{
			echo $this->render('Catalogos/Textos/update.twig',[
			'sesiones'   => $_SESSION,
			'doctoTexto' => $texto,
			'modulo' => $this->modulo,
			'mensaje' => $message,
			'errors' => $errors,
			'documentos' => $tiposDocumento
		]);
		}
	}

	#hace el update de un registro
	public function update(array $data, $app){
		$id = $data['idDocumentoTexto'];
		$errors = $this->validate($data);
		if(empty($errors)){
			Textos::find($id)->update([
				'idTipoDocto' => $data['idTipoDocto'],
				'idSubTipoDocumento' => $data['idSubTipoDocumento'],
				'texto' => $data['texto'],
				'usrModificacion' => $_SESSION['idUsuario'],
              	'fModificacion' => Carbon::now('America/Mexico_City')->format('Y-d-m H:i:s'),
              	'estatus' => $data['estatus']
			]);
			$app->redirect('/SIA/juridico/DoctosTextos');
		}else{
			$this->createUpdate($id,$app,false,$errors);
		}
	}

	#valida los campos para insercion y actualizacion
	public function validate(array $data){
		$errors = [];
		$validator = new \Sirius\Validation\Validator;
		
		$validator->add(
			array(
				'idTipoDocto' => 'required | Alpha | MaxLength(30)(Excede los caracteres permitidos)',
				'idSubTipoDocumento' => 'required | number ',
				'texto' => 'required'
			)
		);

		if(!$validator->validate($data)){
			$errors = $validator->getMessages();
			return $errors;
		}else{

			return $errors;
		}

	}
}

