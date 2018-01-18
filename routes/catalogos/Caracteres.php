<?php 
namespace Routes\Catalogos;
use App\Controllers\Catalogos\CaracteresController;

$controller = new CaracteresController();

$auth = function(){
	//echo "yes";
};



$app->group('/juridico',$auth,function() use($app,$controller){

	$app->get('/Caracteres',function() use ($controller){
		$controller->index();
	});

	$app->get('/Caracteres/create',function() use ($controller){
		$errors = false;
		$message = false;
		$controller->create($errors,$message);
	});

	$app->get('/Caracteres/:id',function($id) use ($controller,$app){
		$errors = false;
		$message = false;
		$controller->createUpdate($id, $app, $errors, $message);
	})->conditions(array('id' => '[0-9]{1,2}'));

	$app->post('/Caracteres/create',function() use ($app,$controller){
		$controller->save($app->request->post(),$app);
	});

	$app->post('/Caracteres/update',function() use($app,$controller) {
		$controller->update($app->request->post(),$app);
	});

});



?>