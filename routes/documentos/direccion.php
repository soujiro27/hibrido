<?php
namespace Routes\Documentos;
use App\Controllers\Documentos\DireccionController;

$controller = new DireccionController;

$auth = function(){
	//echo "yes";
};



$app->group('/juridico',$auth,function() use($app,$controller){


	$app->get('/DocumentosGral',function() use($app,$controller){
		$controller->index($app->request->get());
	});

	$app->get('/DocumentosGral/:id',function($id) use($app,$controller) {
	 	$controller->create($id,$app);
	})->conditions(array('id' => '[0-9]{1,4}'));

	$app->post('/DocumentosGral/:id',function($id) use ($controller,$app){
		$controller->save_and_udpate($app->request->post(),$_FILES,$app);
	});


	$app->get('/DocumentosGral/update/:id',function($id) use ($app){
	$get = new DocumentosUploadController();
	echo $get->getCreate(false,$id);
	});

});


?>




