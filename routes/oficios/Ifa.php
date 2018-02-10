<?php 
namespace Routes\Oficios;
use App\Controllers\Oficios\IfaController;

$controller = new IfaController;

$auth = function(){
	//echo "yes";
};



$app->group('/juridico',$auth,function() use($app,$controller){

	$app->get('/Ifa',function() use ($controller){
		$controller->index();
	});

	$app->get('/Ifa/:id',function($id) use ($controller,$app){
		$message = false;
		$errors = false;
		$controller->create($id,$message, $errors);
	})->conditions(array('id' => '[0-9]{1,4}'));


	$app->get('/Ifa/historial/:id',function($id) use ($controller,$app){
		$message = false;
		$errors = false;
		$controller->createDocumentos($id,$message, $errors);
	})->conditions(array('id' => '[0-9]{1,4}'));

	
	$app->post('/Ifa/:id',function($id) use ($controller,$app){
		$controller->save_turnado($app->request->post(),$_FILES,$app);
	});

	
});





?>