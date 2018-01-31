<?php 
namespace Routes\Documentos;
use App\Controllers\Turnados\TurnadosController;

$controller = new TurnadosController;

$auth = function(){
	//echo "yes";
};



$app->group('/juridico',$auth,function() use($app,$controller){

	$app->get('/turnos',function() use ($controller){
		$controller->index();
	});

	$app->get('/turnos/:id',function($id) use ($controller,$app){
		$message = false;
		$errors = false;
		$controller->create($id,$message, $errors);
	})->conditions(array('id' => '[0-9]{1,4}'));

	$app->post('/turnos/:id',function() use($app,$controller) {
		$controller->save_turnado($app->request->post(),$_FILES,$app);
	});

	$app->get('/turnos/historial/:id',function($id) use ($controller,$app){
		$message = false;
		$errors = false;
		$controller->createDocumentos($id,$message, $errors);
	})->conditions(array('id' => '[0-9]{1,4}'));

/*
	$app->get('/Irac/documentos/:id',function($id) use ($controller,$app){
		$message = false;
		$errors = false;
		$controller->createDocumentos($id,$message, $errors);
	})->conditions(array('id' => '[0-9]{1,4}'));

	


	$app->post('/Irac/turno',function() use($app,$controller) {
		$controller->saveTurno($app->request->post(),$app);
	});

*/
});





?>