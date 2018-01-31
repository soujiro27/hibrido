<?php 
include '/../vendor/autoload.php';

/*--------------Catalogos-------------------*/
include_once '/../routes/catalogos/Caracteres.php';
include_once '/../routes/catalogos/Acciones.php';
include_once '/../routes/catalogos/SubTiposDocumentos.php';
include_once '/../routes/catalogos/Textos.php';


/*-------------Volantes----------------------*/
include_once '/../routes/volantes/volantes.php';
include_once '/../routes/volantes/volantesDiversos.php';


/*----------oficios------------------------*/
include_once '/../routes/oficios/irac.php';
//include_once '/../routes/oficios/Confrontas.php';
//include_once '/../routes/oficios/ifa.php';

/*----------Documentos---------------------*/

include_once '/../routes/documentos/direccion.php';







/*-------Turnados----------------------------*/
include_once '/../routes/oficios/Turnados.php';

/*------------ Api --------------------------*/
include_once '/../routes/api/api.php';

/*----------------Datos DB ------------------*/
include_once '/../../src/conexion.php';
use Illuminate\Database\Capsule\Manager as Capsule;
$capsule = new Capsule;
$capsule->addConnection([
    'driver'    => 'sqlsrv',
    'host'      => $hostname,
    'database'  => $database,
    'username'  => $username,
    'password'  => $password,
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
]);
$capsule->setAsGlobal();
$capsule->bootEloquent();



/*------------------- 404 ---------------------*/
$app->notFound(function () use ($app) {
   $app->render('/hibrido/public/404.html');
});