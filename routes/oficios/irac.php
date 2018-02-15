<?php 
namespace Routes\Oficios;
use App\Controllers\Oficios\IracController;

$controller = new IracController;

$auth = function(){
	//echo "yes";
};



$app->group('/juridico',$auth,function() use($app,$controller){

	$app->get('/Irac',function() use ($controller,$app){
		$controller->index($app->request->get());
	});

	$app->get('/Irac/:id',function($id) use ($controller,$app){
		$controller->create($id);
	})->conditions(array('id' => '[0-9]{1,4}'));


	$app->get('/Irac/historial/:id',function($id) use ($controller,$app){
		$controller->createDocumentos($id);
	})->conditions(array('id' => '[0-9]{1,4}'));

	
	$app->get('/Irac/Observaciones/:id',function($id) use ($controller,$app){
		$controller->observaciones($id);
	});


	$app->post('/Irac/create',function() use ($controller,$app){
		$controller->save_turnado($app->request->post(),$_FILES,$app);
	});


	$app->post('/Observaciones/create',function() use ($controller,$app){
		$controller->save_observaciones($app->request->post(),$app);
	});



	
});





?>