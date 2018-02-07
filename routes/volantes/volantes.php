<?php 
namespace Routes\Catalogos;
use App\Controllers\Volantes\VolantesController;

$controller = new volantesController();

$auth = function(){
	//echo "yes";
};



$app->group('/juridico',$auth,function() use($app,$controller){

	$app->get('/Volantes',function() use ($app,$controller){
		$controller->index($app->request->get());
	});

	$app->get('/Volantes/create',function() use ($controller){
		
		$controller->create();
	});

	$app->get('/Volantes/:id',function($id) use ($controller,$app){

		$controller->createUpdate($id, $app);
	})->conditions(array('id' => '[0-9]{1,4}'));

	$app->post('/Volantes/create',function() use ($app,$controller){
		$controller->save($app->request->post(),$_FILES,$app);
	});

	$app->post('/Volantes/update',function() use($app,$controller) {
		$controller->update($app->request->post(),$app);
	});

});



?>