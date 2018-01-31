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
        
        echo $this->render('/documentos/Direccion/create.twig',[
            'sesiones'=> $_SESSION,
            //'nombre' => $nombre[0]['anexoDoc'],
            'id' => $id,
            'modulo' => $this->modulo,
            'filejs' => $this->filejs
        ]);
    }

    public function save_and_udpate(array $data,$files, $app){
        
        $id = $data['idTurnadoJuridico'];
        $volantes = TurnadosJuridico::select('idVolante')->where('idTurnadoJuridico',"$id")->get();
        $nombre_file = $files['archivo']['name'];
        
        if(!($volantes->isEmpty()) && !(empty($nombre_file))){
            
            $idVolante = $volantes[0]['idVolante'];
            $directory ='hibrido/files/'.$idVolante;
    
            $extension = explode('.',$nombre_file);

            if(!file_exists($directory)){
                    
                mkdir($directory,0777,true);
            } 

            $nombre_final = $id.'.'.$extension[1];

            $anexo = new AnexosJuridico([
                'idTurnadoJuridico' => $id,
                'archivoOriginal' => $nombre_file,
                'archivoFinal' => $nombre_final,
                'idTipoArchivo' => $extension[1],
                'usrAlta' => $_SESSION['idUsuario'],
                'comentario' => $data['comentario'],
                'estatus' => 'ACTIVO',
                'fAlta' => Carbon::now('America/Mexico_City')->format('Y-d-m H:i:s')
            ]);

            if($anexo->save()){
                
                move_uploaded_file($files['archivo']['tmp_name'],$directory.'/'.$nombre_final);
            }
        
        }

        //$app->redirect('/SIA/hibrido/DocumentosGral');
    }


    public function update($post,$file,$app) {

        $id = $post['idVolante'];
        if($this->verificaVolante($id)){
            $nombre=$file['anexoDoc']['name'];
            $extension=explode('.',$nombre);
            if(count($extension)>1){
                if(move_uploaded_file($file['anexoDoc']['tmp_name'],"juridico/public/files/".$post['idVolante'].'.'.$extension[1]))
                {
                    $fecha=strftime( "%Y-%d-%m", time() );
                    Volantes::where('idVolante',$post['idVolante'])->update([
                        'anexoDoc' => $post['idVolante'].'.'.$extension[1],
                        'usrModificacion' => $_SESSION['idUsuario'],
                        'fModificacion' => $fecha
                    ]);
                    $app->redirect('/SIA/juridico/DocumentosGral');
                }
            }else{
                echo $this->getCreate('El Archivo Contiene un Formato Incorrecto');
            }
        }else{
            echo $this->getCreate('El Volante ha sido CERRADO no puede agregar documentos');
        }


    }


    public function duplicate($post) {
        $duplicate = DocumentosUploadController::where('idVolante' ,$post['idVolante'])
            ->first();
        return $duplicate;
    }

    public function verificaVolante($id){

        $datos = Turnos::where('idVolante','=',$id)->get();
        $turno =  $datos[0]['estadoProceso'];
        if($turno == 'CERRADO' ){
            return false;
        }else{
            return true;
        }
    }


}