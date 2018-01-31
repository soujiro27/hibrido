<?php
namespace App\Controllers\Documentos;

use App\Controllers\BaseController;
use App\Controllers\Template;
use App\Controllers\ValidateController;
use Carbon\Carbon;

use App\Models\Volantes\Volantes;
use App\Models\Catalogos\PuestosJuridico;
use App\Models\Documentos\TurnadosJuridico;
use App\Models\Documentos\AnexosJuridico;


class DireccionController extends Template {

    private $modulo = 'Documentos';
    private $filejs = 'Documentos';

    public function index($app) {

        $documentos = Volantes::select('sia_volantes.*','sub.nombre','t.idEstadoTurnado','t.idTurnadoJuridico','t.idAreaRecepcion')
            ->join('sia_VolantesDocumentos as vd','vd.idVolante','=','sia_volantes.idVolante')
            ->join('sia_catSubTiposDocumentos as sub','sub.idSubTipoDocumento','=','vd.idSubTipoDocumento')
            ->join('sia_TurnadosJuridico as t','t.idVolante','=','sia_volantes.idVolante')
            ->get();

        echo $this->render('/documentos/Direccion/index.twig',[
            'documentos' => $documentos,
            'sesiones'=> $_SESSION,
            'modulo' => $this->modulo,
            'filejs' => $this->filejs
            ]);
    }

    public function create($id,$app) {

        $volantes = Volantes::find($id);

        echo $this->render('/documentos/Direccion/create.twig',[
            'sesiones'=> $_SESSION,
            'volantes' => $volantes,
            'id' => $id,
            'modulo' => $this->modulo,
            'filejs' => $this->filejs
        ]);
    }

    public function save_and_udpate(array $data,$files, $app){
        
        $id = $data['idVolante'];
        $volantes = Volantes::where('idVolante',"$id")->get();
        $nombre_file = $files['archivo']['name'];
        
        if(!($volantes->isEmpty()) && !(empty($nombre_file))){
            
            $directory ='hibrido/files/'.$id;
    
            $extension = explode('.',$nombre_file);

            if(!file_exists($directory)){
                    
                mkdir($directory,0777,true);
            } 

            $nombre_final = $id.'.'.$extension[1];

            Volantes::find($id)->update([
                'anexoDoc' => $nombre_final,
                'usrModificacion' => $_SESSION['idUsuario'],
				'fModificacion' => Carbon::now('America/Mexico_City')->format('Y-d-m H:i:s') 
            ]);

            move_uploaded_file($files['archivo']['tmp_name'],$directory.'/'.$nombre_final);

            $turnos = TurnadosJuridico::select('idAreaRecepcion')->where('idVolante',"$id")->get();
            
            $areas = [];

            foreach ($turnos as $key => $value) {
                array_push($areas,$turnos[$key]['idAreaRecepcion']);
            }

            $datos = Volantes::select('sia_volantes.*','sub.*')
                    ->join('sia_VolantesDocumentos as sub','sub.idVolante','=','sia_volantes.idVolante')
                    ->get();

            $data['folio'] = $datos[0]['folio'];
            $data['idSubTipoDocumento'] = $datos[0]['idSubTipoDocumento'];

            $this->notificaciones($areas,$data);
            $this->notificaciones_varios($areas,$data);
            
        }

        $app->redirect('/SIA/juridico/DocumentosGral');
    }

    public function notificaciones($areas, $data) {
        var_dump($areas);
        var_dump($data);
        
        $nombre = BaseController::get_nombre_subDocumento($data['idSubTipoDocumento']);

        foreach ($areas as $key => $value) {
        
            $rpe = $this->get_usrid_boss_area($areas[$key]);

            if(!empty($rpe)){

                $datos_boss = BaseController::get_usrId($rpe);
            
                $mensaje = 'Mensaje enviado a: '.$datos_boss['nombre'].
                    "\nHas recibido un Documento Digitalizado correspondiente a un: ".$nombre.
                    "\nCon el folio: ".$data['folio'];
                    
                BaseController::notificaciones($datos_boss['idUsuario'],$mensaje);
            }
        }
    }

     public function get_usrid_boss_area($turnado){
        
        $puestos = PuestosJuridico::select('rpe')
            ->where('idArea',"$turnado")
            ->where('titular','SI')
            ->get();
        
        if($puestos->isEmpty()){
            $jefe_area_rpe =     [];
        } else{

            $jefe_area_rpe = $puestos[0]['rpe'];
        }
        
        return $jefe_area_rpe;


    }

     public function notificaciones_varios($areas,$data){

            $nombre = BaseController::get_nombre_subDocumento($data['idSubTipoDocumento']);
            
        foreach ($areas as $key => $value) {
            
            $rpe = $this->get_usrid_boss_area($areas[$key]);

            $datos_boss = BaseController::get_usrId($rpe);

            $users = BaseController::get_users_notifica($rpe);
            
            $mensaje = 'Mensaje enviado a: '.$datos_boss['nombre'].
                    "\nHas recibido un Documento Digitalizado correspondiente a un: ".$nombre.
                    "\nCon el folio: ".$data['folio'];

            foreach ($users as $index => $el) {
                BaseController::notificaciones($users[$index]['idUsuario'],$mensaje);
            }
        }
         
    }
    


}