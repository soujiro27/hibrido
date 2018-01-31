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
            
        }

        $app->redirect('/SIA/juridico/DocumentosGral');
    }


    


}