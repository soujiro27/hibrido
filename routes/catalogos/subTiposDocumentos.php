<?php 
namespace Routes\Catalogos;
use App\Controllers\Catalogos\SubTiposDocumentosController;

$controller = new SubTiposDocumentosController();

$auth = function(){
	//echo "yes";
};



$app->group('/juridico',$auth,function() use($app,$controller){

	$app->get('/SubTiposDocumentos',function() use ($controller){
		$controller->index();
	});

	$app->get('/SubTiposDocumentos/create',function() use ($controller){
		$errors = false;
		$message = false;
		$controller->create($message, $errors);
	});

	$app->get('/SubTiposDocumentos/:id',function($id) use ($controller,$app){
		$errors = false;
		$message = false;
		$controller->createUpdate($id, $app, $message, $errors);
	})->conditions(array('id' => '[0-9]{1,2}'));

	$app->post('/SubTiposDocumentos/create',function() use ($app,$controller){
		$controller->save($app->request->post(),$app);
	});

	$app->post('/SubTiposDocumentos/update',function() use($app,$controller) {
		$controller->update($app->request->post(),$app);
	});

});



?>