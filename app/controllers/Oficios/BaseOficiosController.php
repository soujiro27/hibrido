<?php  

namespace App\Controllers\Oficios;


use Carbon\Carbon;

use App\Controllers\Template;
use App\Controllers\ValidateController;
use App\Controllers\BaseController;


use App\Models\Volantes\Volantes;
use App\Models\Volantes\VolantesDocumentos;
use App\Models\Catalogos\PuestosJuridico;
use App\Models\Documentos\TurnadosJuridico;
use App\Controllers\ApiController;
use App\Models\Oficios\Observaciones;

class BaseOficiosController {

	public function save_turnado($data,$file, $app,$modulo){

		$base = new BaseController();

		$data['estatus'] =  'ACTIVO';
        $validate = $this->validate_turnado($data,$file);
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

	public function validate_turnado($data, $file){
        

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

    public function load_personal($id){

        $turnado_volantes = TurnadosJuridico::select('idAreaRecepcion')->where('idVolante',"$id")->get();
        $idTurnado = $turnado_volantes[0]['idAreaRecepcion'];

        $rpe = $_SESSION['idEmpleado'];

        $puestos = PuestosJuridico::where('idArea',"$idTurnado")
                                    ->where('rpe','<>',"$rpe")
                                    ->get();
        return $puestos;

    }

    public function save_observaciones(array $data, $app){

        $data['estatus'] = 'ACTIVO';
        $id = $data['idVolante'];

        $base = new BaseController();
        $validate = $this->validate_observaciones($data,true);

        $vd = VolantesDocumentos::where('idVolante',"$id")->get();
        $subTipo = $vd[0]['idSubTipoDocumento'];
        $cveAuditoria = $vd[0]['cveAuditoria'];

        if(empty($validate)){

            $observacion = new Observaciones([
                'idVolante' => $id,
                'idSubTipoDocumento' => $subTipo,
                'cveAuditoria' => $cveAuditoria,
                'pagina' => $data['pagina'],
                'parrafo' => $data['parrafo'],
                'observacion' => $data['observacion'],
                'usrAlta' => $_SESSION['idUsuario'],
                'estatus' => $data['estatus']
            ]);

            $observacion->save();
            $success = $base->success();
            echo json_encode($success);


        } else {

            echo json_encode($validate);
        }
    }


    public function update_observaciones(array $data, $app) {

        $base = new BaseController();
        
        $validate = $this->validate_observaciones($data,false);
        $id = $data['idObservacionDoctoJuridico'];

        if(empty($validate)){

            Observaciones::find($id)->update([
                'pagina' => $data['pagina'],
                'parrafo' => $data['parrafo'],
                'observacion' => $data['observacion'],
                'usrModificacion' => $_SESSION['idUsuario'],
                'estatus' => $data['estatus'],
                'fModificacion' => Carbon::now('America/Mexico_City')->format('Y-m-d H:i:s')

            ]);

            $success = $base->success();
            echo json_encode($success);


        } else {

            echo json_encode($validate);
        }

    }


    public function validate_observaciones(array $data, $tipo){

        $validate = new ValidateController();

        $res = [];
        $final = [];



        
        $res[0] = $validate->alphaNumeric($data['pagina'],'pagina',50);
        $res[1] = $validate->alphaNumeric($data['parrafo'],'parrafo',50);
        $res[2] = $validate->alphaNumeric($data['observacion'],'observacion',10000);
        $res[3] = $validate->string($data['estatus'],'estatus',10);

        if($tipo){
            
            $res[4] = $validate->number($data['idVolante'],'idVolante',true);
        
        } else {

            $res[4] = $validate->number($data['idObservacionDoctoJuridico'],'idObservacionDoctoJuridico',true);    
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