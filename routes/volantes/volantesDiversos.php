<?php 
namespace Routes\Catalogos;
use App\Controllers\Volantes\VolantesDiversosController;

$controller = new volantesDiversosController();

$auth = function(){
	//echo "yes";
};



$app->group('/juridico',$auth,function() use($app,$controller){

	$app->get('/VolantesDiversos',function() use ($controller){
		$controller->index();
	});

	$app->get('/VolantesDiversos/create',function() use ($controller){
		$controller->create();
	});

	$app->get('/VolantesDiversos/:id',function($id) use ($controller,$app){
		$controller->createUpdate($id, $app);
	})->conditions(array('id' => '[0-9]{1,4}'));

	$app->post('/VolantesDiversos/create',function() use ($app,$controller){
		$controller->save($app->request->post(), $app);
	});

	$app->post('/VolantesDiversos/update',function() use($app,$controller) {
		$controller->update($app->request->post(),$app);
	});

});



?>