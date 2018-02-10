<?php 
namespace Routes\Oficios;
use App\Controllers\Oficios\DiversosController;

$controller = new DiversosController;

$auth = function(){
	//echo "yes";
};



$app->group('/juridico',$auth,function() use($app,$controller){

	$app->get('/DocumentosDiversos',function() use ($controller){
		$controller->index();
	});

	$app->get('/DocumentosDiversos/:id',function($id) use ($controller,$app){
		$message = false;
		$errors = false;
		$controller->create($id,$message, $errors);
	})->conditions(array('id' => '[0-9]{1,4}'));


	$app->get('/DocumentosDiversos/historial/:id',function($id) use ($controller,$app){
		$message = false;
		$errors = false;
		$controller->createDocumentos($id,$message, $errors);
	})->conditions(array('id' => '[0-9]{1,4}'));

	
	$app->post('/DocumentosDiversos/create',function() use ($controller,$app){
		$controller->save_turnado($app->request->post(),$_FILES,$app);
	});

	
});





?>