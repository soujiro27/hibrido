<?php  
namespace App\Controllers;
use App\Models\Base\UsuariosRoles;

class AuthController {

	#valida que el usuario haya iniciado sesion
	public function sesion($app){
		if(empty($_SESSION['idUsuario'])){
			 $app->redirect('./hibrido/public/404.html');
		}
	}

	#valida que el usuario este en el rol que le corresponde
	public function rol($modulo,$app){
		$idUsuario = $_SESSION['idUsuario'];
		$roles = UsuariosRoles::select('rm.idModulo')
				->join('sia_rolesmodulos as rm','rm.idRol','=','sia_usuariosroles.idRol')
				->where('sia_usuariosroles.idUsuario',"$idUsuario")
				->where('rm.idModulo',"$modulo")
				->get();
		if($roles->isEmpty()){
			 $app->redirect('./hibrido/public/404.html');
		}

	}
}