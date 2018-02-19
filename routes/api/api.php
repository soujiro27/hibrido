<?php 
namespace Routes\Api;
use App\Controllers\ApiController;
$controller = new ApiController;

$auth = function(){
	//echo "yes";
};



$app->group('/juridico',$auth,function() use($app,$controller){

	$app->get('/api/subDocumentos',function() use ($controller,$app){
		$controller->load_subDocumentos_volantes($app->request->get());
	});

	$app->get('/api/auditoria',function() use($controller,$app){
		$controller->load_datos_auditoria($app->request->get());
	});

	$app->get('/api/auditoria/turnos',function() use($controller,$app){
		$controller->load_turnado_auditoria($app->request->get());
	});

	$app->get('/api/remitentes',function() use($controller,$app){
		$controller->load_turnado_volantes($app->request->get());
	});
	
	$app->get('/api/puestos',function() use ($controller,$app){
		$controller->load_puestos_juridico($app->request->get());
	});

	$app->get('/api/documentosTurnados',function() use ($controller,$app){
		$controller->load_documentos_turnados($app->request->get());
	});


	$app->get('/api/areas',function() use ($controller,$app){
		$controller->load_areas();
	});

	$app->get('/api/areas/update',function() use ($controller,$app){
		$controller->load_areas_update($app->request->get());
	});

	$app->post('/api/upload',function() use ($controller,$app){
		$controller->upload_files($app->request->post(),$_FILES,$app);
	});


	$app->get('/api/puestosJuridico',function() use ($controller,$app){
		$controller->load_puestos_juridicos_area();
	});

	$app->get('/api/promocionAcciones',function() use ($controller,$app){
		$controller->load_textos_promocion_acciones();
	});

	

	$app->get('/datos/Sessions',function() use ($controller,$app){
		 $datos = array(
        "idUsuario"         => $_SESSION['idUsuario'],
        "idCuentaActual"    => $_SESSION['idCuentaActual'],
        "userName"          => $_SESSION['sUsuario'],
        "cuenta"            => $_SESSION['sCuentaActual'],
    );

    echo json_encode($datos);

	});


});



?>