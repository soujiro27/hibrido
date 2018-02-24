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

	
	$app->get('/Irac/observaciones/:id',function($id) use ($controller,$app){
		$controller->observaciones($id);
	});


	$app->get('/Irac/add/observaciones/:id',function($id) use ($controller,$app){
		$controller->createObservaciones($id);
	});

	$app->get('/Irac/update/observaciones/:id',function($id) use ($controller,$app){
		$controller->create_Update_Observaciones($id);
	});

	$app->get('/Irac/cedula/create/:id',function($id) use ($controller,$app){
		$controller->createCedula($id);
	});


	/*----------------post----------------*/
	$app->post('/Irac/create',function() use ($controller,$app){
		$controller->save_turnado($app->request->post(),$_FILES,$app);
	});


	$app->post('/Irac/observaciones/create',function() use ($controller,$app){
		$controller->save_observaciones($app->request->post(),$app);
	});


	$app->post('/Irac/observaciones/update',function() use ($controller,$app){
		$controller->update_observaciones($app->request->post(),$app);
	});

	$app->post('/Irac/cedula/create',function() use ($controller,$app){
		$controller->save_cedula($app->request->post(),$app);
	});	

	$app->post('/Irac/cedula/update',function() use ($controller,$app){
		$controller->update_cedula($app->request->post(),$app);
	});	



	
});





?>