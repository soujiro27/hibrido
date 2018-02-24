<?php 
namespace Routes\Oficios;
use App\Controllers\Oficios\DiversosController;

$controller = new DiversosController;

$auth = function(){
	//echo "yes";
};



$app->group('/juridico',$auth,function() use($app,$controller){

	$app->get('/DocumentosDiversos',function() use ($controller,$app){
		$controller->index($app->request->get());
	});

	$app->get('/DocumentosDiversos/:id',function($id) use ($controller,$app){
		$controller->create($id);
	})->conditions(array('id' => '[0-9]{1,4}'));


	$app->get('/DocumentosDiversos/historial/:id',function($id) use ($controller,$app){
		$controller->createDocumentos($id);
	})->conditions(array('id' => '[0-9]{1,4}'));

	
	$app->get('/DocumentosDiversos/cedula/create/:id',function($id) use ($controller,$app){
		$controller->createCedula($id);
	});


	/*----------------post----------------*/
	$app->post('/DocumentosDiversos/create',function() use ($controller,$app){
		$controller->save_turnado($app->request->post(),$_FILES,$app);
	});

	$app->post('/DocumentosDiversos/cedula/create',function() use ($controller,$app){
		$controller->save_cedula($app->request->post());
	});	

	$app->post('/DocumentosDiversos/cedula/update',function() use ($controller,$app){
		$controller->update_cedula($app->request->post(),$app);
	});	

	
});





?>