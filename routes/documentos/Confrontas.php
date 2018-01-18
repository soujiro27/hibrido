<?php 
namespace Routes\Documentos;
use App\Controllers\Documentos\ConfrontasController;

$controller = new ConfrontasController;

$auth = function(){
	//echo "yes";
};



$app->group('/juridico',$auth,function() use($app,$controller){

	$app->get('/confrontasJuridico',function() use ($controller){
		$controller->index();
	});

	$app->get('/confrontasJuridico/:id',function($id) use ($controller,$app){
		$message = false;
		$errors = false;
		$controller->create($id,$message, $errors);
	})->conditions(array('id' => '[0-9]{1,4}'));


	$app->get('/confrontasJuridico/historial/:id',function($id) use ($controller,$app){
		$message = false;
		$errors = false;
		$controller->createDocumentos($id,$message, $errors);
	})->conditions(array('id' => '[0-9]{1,4}'));

	
	$app->post('/confrontasJuridico/:id',function($id) use ($controller,$app){
		$controller->save_turnado($app->request->post(),$_FILES,$app);
	});

	
});





?>