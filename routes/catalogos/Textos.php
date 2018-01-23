<?php 
namespace Routes\Catalogos;

use \App\Controllers\Catalogos\TextosController;
$controller = new TextosController();


$auth = function(){
	//echo "yes";
};



$app->group('/juridico',$auth,function() use($app,$controller){

	$app->get('/DoctosTextos',function() use ($controller){
		$controller->index();
	});

	$app->get('/DoctosTextos/create',function() use ($controller){
		$controller->create();
	});

	$app->get('/DoctosTextos/:id',function($id) use ($controller,$app){
		$controller->createUpdate($id, $app);
	})->conditions(array('id' => '[0-9]{1,4}'));

	$app->post('/DoctosTextos/create',function() use ($app,$controller){
		$controller->save($app->request->post(), $app);
	});

	$app->post('/DoctosTextos/update',function() use($app,$controller) {
		$controller->update($app->request->post(),$app);
	});

});



?>