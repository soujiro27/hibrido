<?php 
namespace Routes\Oficios;
use App\Controllers\Oficios\IfaController;

$controller = new IfaController;

$auth = function(){
	//echo "yes";
};



$app->group('/juridico',$auth,function() use($app,$controller){

	$app->get('/Ifa',function() use ($controller,$app){
		$controller->index($app->request->get());
	});

	$app->get('/Ifa/:id',function($id) use ($controller,$app){
		$controller->create($id);
	})->conditions(array('id' => '[0-9]{1,4}'));


	$app->get('/Ifa/historial/:id',function($id) use ($controller,$app){
		$controller->createDocumentos($id);
	})->conditions(array('id' => '[0-9]{1,4}'));

	
	$app->get('/Ifa/observaciones/:id',function($id) use ($controller,$app){
		$controller->observaciones($id);
	});


	$app->get('/Ifa/add/observaciones/:id',function($id) use ($controller,$app){
		$controller->createObservaciones($id);
	});

	$app->get('/Ifa/update/observaciones/:id',function($id) use ($controller,$app){
		$controller->create_Update_Observaciones($id);
	});

	$app->get('/Ifa/cedula/create/:id',function($id) use ($controller,$app){
		$controller->createCedula($id);
	});


	/*----------------post----------------*/
	$app->post('/Ifa/create',function() use ($controller,$app){
		$controller->save_turnado($app->request->post(),$_FILES,$app);
	});


	$app->post('/Ifa/observaciones/create',function() use ($controller,$app){
		$controller->save_observaciones($app->request->post(),$app);
	});


	$app->post('/Ifa/observaciones/update',function() use ($controller,$app){
		$controller->update_observaciones($app->request->post(),$app);
	});

	$app->post('/Ifa/cedula/create',function() use ($controller,$app){
		$controller->save_cedula($app->request->post(),$app);
	});	

	$app->post('/Ifa/cedula/update',function() use ($controller,$app){
		$controller->update_cedula($app->request->post(),$app);
	});	
	
});





?>