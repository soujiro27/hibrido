<?php  

namespace App\Controllers\Oficios;


use Carbon\Carbon;

use App\Controllers\Template;
use App\Controllers\ValidateController;
use App\Controllers\BaseController;

use App\Models\Volantes\Volantes;
use App\Models\Catalogos\PuestosJuridico;
use App\Models\Documentos\TurnadosJuridico;

use App\Controllers\ApiController;

class BaseOficiosController {

	public function save_turnado($data,$file, $app,$modulo){

		$base = new BaseController();

		$data['estatus'] =  'ACTIVO';
        $validate = $this->validate($data,$file);
        $nombre_file = $file['file']['name'];

        if(empty($validate)){

            $datos = $base->datos_insert_turnados($data);
            $max = $base->insert_turnado_interno($data,$datos);
            
            if(!empty($nombre_file)){

                $base->insert_anexos_interno($max,$file);
            }

            $base->send_notificaciones($data,$datos,$modulo);
            $base->send_notificaciones_varios($data,$datos,$modulo);

            $success = $base->success();
            echo json_encode($success);


        } else {

            echo json_encode($valida);
        }

	}

	public function validate($data, $file){
        

		$validate = new ValidateController();

        $res = [];
        $final = [];

        $nombre_file = $file['file']['name'];

        $res[0] = $validate->string($data['idTipoPrioridad'],'idTipoPrioridad',10);
        $res[1] = $validate->alphaNumeric($data['comentario'],'comentario',350);
        $res[2] = $validate->string($data['estatus'],'estatus',10);

        $res[3] = $validate->number($data['idUsrReceptor'],'idUsrReceptor',true);
        $res[4] = $validate->number($data['idVolante'],'idVolante',true);

        if(!empty($nombre_file)){

            $res[5] = $validate->alphaNumeric($nombre_file,'Archivo',50);
            
        }

        foreach ($res as $key => $value) {
            if(!empty($value)){
                array_push($final,$value);
            }
        }


        return $final;
        
    }

}

?>