<?php  

namespace App\Controllers;

use Carbon\Carbon;

use App\Models\Catalogos\SubTiposDocumentos; 
use App\Models\Volantes\Notificaciones;
use App\Models\Volantes\Usuarios;
use App\Models\Catalogos\PuestosJuridico;
use App\Models\Documentos\TurnadosJuridico;
use App\Models\Documentos\AnexosJuridico;
use App\Models\Volantes\Volantes;
use App\Models\Volantes\Areas;
use App\Models\Base\UsuariosRoles;

 

class BaseController {

    public function load_areas(){
        $res = [];
        $turnados  = Areas::where('idAreaSuperior','DGAJ')->where('estatus','ACTIVO')->get();
        $turnadoDireccion = array ('idArea'=>'DGAJ','nombre' => 'DIRECCIÓN GENERAL DE ASUNTOS JURIDICOS');
        
        foreach ($turnados as $key => $value) {
            $res[$key] = $turnados[$key];
        }
        array_push($res,$turnadoDireccion);

        return $res;
    }

	public function upload_file_areas($file,$idVolante){

		$nombre_file = $file['file']['name'];

		$directory ='hibrido/files/'.$idVolante.'/Areas';
    
        $extension = explode('.',$nombre_file);

        if(!file_exists($directory)){
                    
            mkdir($directory,0777,true);
        } 

        $nombre_final = $idVolante.'.'.$extension[1];

        move_uploaded_file($file['file']['tmp_name'],$directory.'/'.$nombre_final);

        return $nombre_final;

	}

	public function upload_file_interno($file,$idVolante,$nombre_final){

		$directory ='hibrido/files/'.$idVolante.'/Internos';
    

        if(!file_exists($directory)){
                    
            mkdir($directory,0777,true);
        } 

        move_uploaded_file($file['file']['tmp_name'],$directory.'/'.$nombre_final);


	}

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

	public function get_data_area($area){

		$datos = PuestosJuridico::select('sia_PuestosJuridico.*','u.idUsuario')
				->join('sia_usuarios as u','u.idEmpleado','=','sia_PuestosJuridico.rpe')
				->where('sia_PuestosJuridico.titular','SI')
				->where('sia_PuestosJuridico.idArea',"$area")
				->get();

		return $datos;
	}


	public function datos_insert_turnados($data){

        $res = [];
        $id = $data['idVolante'];
        $idPuesto = $data['idUsrReceptor'];

        
        $volante = TurnadosJuridico::where('idVolante',"$id")->where('idTipoTurnado','E')->get();

        $puestos = PuestosJuridico::select('u.idUsuario','sia_PuestosJuridico.*')
                    ->join('sia_usuarios as u','u.idEmpleado','=','sia_PuestosJuridico.rpe')
                    ->where('sia_PuestosJuridico.idPuestoJuridico',"$idPuesto")
                    ->get();
        
        $res['idUsuario'] = $puestos[0]['idUsuario'];
        $res['area'] = $volante[0]['idAreaRecepcion'];
        $res['nombre'] = $puestos[0]['saludo'].' '.$puestos[0]['nombre'].' '.$puestos[0]['paterno'].' '.$puestos[0]['materno'];
        $res['rpe'] = $puestos[0]['rpe'];

        return $res;
    }

    public function insert_turnado_interno(array $data, array $datos){

    	$turno = new TurnadosJuridico([
                'idVolante' => $data['idVolante'],
                'idAreaRemitente' => $datos['area'],
                'idAreaRecepcion' => $datos['area'],
                'idUsrReceptor' => $datos['idUsuario'],
                'idEstadoTurnado' => $data['idEstadoTurnado'],
                'idTipoTurnado' => 'I',
                'idTipoPrioridad' =>$data['idTipoPrioridad'],
                'comentario' => $data['comentario'],
                'usrAlta' => $_SESSION['idUsuario'],
                'estatus' => 'ACTIVO',
                'fAlta' => Carbon::now('America/Mexico_City')->format('Y-d-m H:i:s')
            ]);

            $turno->save();
            $max = TurnadosJuridico::all()->max('idTurnadoJuridico');

            return $max;
    }


    public function insert_anexos_interno($idTurnadoJuridico,$file){

    	$nombre_file = $file['file']['name'];
    	$extension = explode('.',$nombre_file);

    	$turnos = TurnadosJuridico::select('idVolante')->where('idTurnadoJuridico',"$idTurnadoJuridico")->get();
    	$idVolante = $turnos[0]['idVolante'];

    	$nombre_final = $idTurnadoJuridico.'.'.$extension[1];

    	$anexo = new AnexosJuridico([
    		'idTurnadoJuridico' => $idTurnadoJuridico,
    		'archivoOriginal' => $nombre_file,
    		'archivoFinal' => $nombre_final,
    		'idTipoArchivo' => $extension[1],
    		'usrAlta' => $_SESSION['idUsuario'],
            'estatus' => 'ACTIVO',
            'fAlta' => Carbon::now('America/Mexico_City')->format('Y-d-m H:i:s')
            ]);

    	$anexo->save();

    	BaseController::upload_file_interno($file,$idVolante,$nombre_final);

    }


    public function send_notificaciones(array $data, array $datos, $tipo) {

        $idVolante = $data['idVolante'];
        $volante = Volantes::select('folio')->where('idVolante',"$idVolante")->get();
        $folio = $volante[0]['folio'];

        $mensaje = 'Mensaje enviado a: '.$datos['nombre'].
                "\nHas recibido una Instruccion ".
                "\nCorrespondiente a un ".$tipo.
                "\nCon el folio: ".$folio;
                
        BaseController::notificaciones($datos['idUsuario'],$mensaje);
    }

    public function send_notificaciones_varios(array $data, array $datos,$tipo){

        $idVolante = $data['idVolante'];
        $volante = Volantes::select('folio')->where('idVolante',"$idVolante")->get();
        $folio = $volante[0]['folio'];

            
        $users = BaseController::get_users_notifica($datos['rpe']);
        
        $mensaje = 'Mensaje enviado a: '.$datos['nombre'].
                "\nHas recibido una Instruccion ".
                "\nCorrespondiente a un ".$tipo.
                "\nCon el folio: ".$folio;

        foreach ($users as $key => $value) {
            BaseController::notificaciones($users[$key]['idUsuario'],$mensaje);
        }
    }

    public function rol_cedulas($modulo){

        $res = true;

        $idUsuario = $_SESSION['idUsuario'];
        $roles = UsuariosRoles::select('rm.idModulo')
                ->join('sia_rolesmodulos as rm','rm.idRol','=','sia_usuariosroles.idRol')
                ->where('sia_usuariosroles.idUsuario',"$idUsuario")
                ->where('rm.idModulo',"$modulo")
                ->get();
        if($roles->isEmpty()){
             $res = false;
        }

        return $res;

    }

	
}

?>