<?php 
namespace Routes\Oficios;
use App\Controllers\Oficios\ConfrontasController;

$controller = new ConfrontasController;

$auth = function(){
	//echo "yes";
};



$app->group('/juridico',$auth,function() use($app,$controller){

	$app->get('/confrontasJuridico',function() use ($controller,$app){
		$controller->index($app->request->get());
	});

	$app->get('/confrontasJuridico/:id',function($id) use ($controller,$app){
		$controller->create($id);
	})->conditions(array('id' => '[0-9]{1,4}'));


	$app->get('/confrontasJuridico/historial/:id',function($id) use ($controller,$app){
		$controller->createDocumentos($id);
	})->conditions(array('id' => '[0-9]{1,4}'));

	
	$app->get('/confrontasJuridico/cedula/create/:id',function($id) use ($controller,$app){
		$controller->createCedula($id);
	});


	/*----------------post----------------*/
	$app->post('/confrontasJuridico/create',function() use ($controller,$app){
		$controller->save_turnado($app->request->post(),$_FILES,$app);
	});

	$app->post('/confrontasJuridico/cedula/create',function() use ($controller,$app){
		$controller->save_cedula($app->request->post());
	});	

	$app->post('/confrontasJuridico/cedula/update',function() use ($controller,$app){
		$controller->update_cedula($app->request->post(),$app);
	});	
	
});





?>