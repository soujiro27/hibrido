<?php  

namespace App\Controllers;

use Carbon\Carbon;

use App\Models\Catalogos\SubTiposDocumentos; 
use App\Models\Volantes\Notificaciones;
use App\Models\Volantes\Usuarios;
use App\Models\Catalogos\PuestosJuridico;
 

class BaseController {

	public function notificaciones($id,$mensaje){

		$notifica = new Notificaciones([
				'idNotificacion' => '1',
				'idUsuario' => $id,
				'mensaje' => $mensaje,
				'idPrioridad' => 'ALTA',
				'idImpacto' => 'MEDIO',
				'fLectura' => Carbon::now('America/Mexico_City')->format('Y-d-m H:i:s'),
				'usrAlta' => $_SESSION['idUsuario'],
				'fAlta' => Carbon::now('America/Mexico_City')->format('Y-d-m H:i:s'),
				'estatus' => 'ACTIVO',
				'situacion' => 'NUEVO',
				'identificador' => '1',
				'idCuenta' => $_SESSION['idCuentaActual'],
				'idAuditoria' => '1',
				'idModulo' => 'Volantes',
				'referencia' => 'idVolante'
	 
			]);
			$notifica->save();

	}

	public function notificaciones_varios($id,$mensaje){

	}


	public function get_users_notifica($rpe){
		
        $usuarios = []; 
		$usuarios_notifica = PuestosJuridico::select('rpe')
            				->where('usrAsisteA',"$rpe")
            				->get();

        if(!$usuarios_notifica->isEmpty()){

            foreach ($usuarios_notifica as $key => $value) {
                
            	$usr_id = BaseController::get_usrId($usuarios_notifica[$key]['rpe']);
                array_push($usuarios, $usr_id);
            }
        }
        return $usuarios;
	}

	public function get_usrId($rpe){

		$idUsuario = Usuarios::select('idUsuario','saludo','nombre','paterno','materno')
					->where('idEmpleado',"$rpe")
					->get();

		$res = array(
			'idUsuario' => $idUsuario[0]['idUsuario'],
			'nombre' => $idUsuario[0]['saludo'].' '.$idUsuario[0]['nombre'].' '.$idUsuario[0]['paterno'].' '.$idUsuario[0]['materno'],
			'rpe' => $rpe
			 );

		return $res;

	}

	public function success(){
		$sucess = [];
		$sucess['campo'] = 'success';
		$sucess['message'] = 'Registro Exitoso';

		$res[0] = $sucess;
		return $res;
	}

	public function get_nombre_subDocumento($id){

		$sub = SubTiposDocumentos::select('nombre')->where('idSubTipoDocumento',"$id")->get();
		$nombre = $sub[0]['nombre'];
		return $nombre;

	}
	
}

?>