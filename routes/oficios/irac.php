<?php 
namespace Routes\Oficios;
use App\Controllers\Oficios\IracController;

$controller = new IracController;

$auth = function(){
	//echo "yes";
};



$app->group('/juridico',$auth,function() use($app,$controller){

	$app->get('/Irac',function() use ($controller){
		$controller->index();
	});

	$app->get('/Irac/:id',function($id) use ($controller,$app){
		$message = false;
		$errors = false;
		$controller->create($id,$message, $errors);
	})->conditions(array('id' => '[0-9]{1,4}'));


	$app->get('/Irac/historial/:id',function($id) use ($controller,$app){
		$message = false;
		$errors = false;
		$controller->createDocumentos($id,$message, $errors);
	})->conditions(array('id' => '[0-9]{1,4}'));

	
	$app->post('/Irac/create',function() use ($controller,$app){
		$controller->save_turnado($app->request->post(),$_FILES,$app);
	});

	
});





?>