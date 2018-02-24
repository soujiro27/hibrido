<?php 
namespace App\Controllers\Oficios;

use Carbon\Carbon;

use App\Controllers\Template;
use App\Controllers\ValidateController;
use App\Controllers\BaseController;
use App\Controllers\Oficios\BaseOficiosController;

use App\Models\Volantes\Volantes;
use App\Models\Volantes\VolantesDocumentos;
use App\Models\Catalogos\PuestosJuridico;
use App\Models\Documentos\TurnadosJuridico;
use App\Models\Oficios\Observaciones;
use App\Models\Oficios\DocumentosSiglas;
use App\Models\Oficios\Espacios;

use App\Controllers\ApiController;

class IfaController extends Template {

	private $modulo = 'Ifa';
    private $filejs = 'Ifa';
    private $ckeditor = true;


	public function index($data) {
		$id = $_SESSION['idEmpleado'];
        $areas = PuestosJuridico::where('rpe','=',"$id")->get();
        $area = $areas[0]['idArea'];

        $now = Carbon::now('America/Mexico_City')->format('Y');
        $campo = 'Folio';
        $tipo = 'desc';

        if(!empty($data)){
            $now = $data['year'];
            $campo = $data['campo'];
            $tipo = $data['tipo'];
        }

         $iracs = Volantes::select('sia_Volantes.idVolante','sia_Volantes.folio',
            'sia_Volantes.numDocumento','sia_Volantes.idRemitente','sia_Volantes.fRecepcion','sia_Volantes.asunto'
        ,'c.nombre as caracter','a.nombre as accion','audi.clave','sia_Volantes.extemporaneo','t.idEstadoTurnado')
            ->join('sia_catCaracteres as c','c.idCaracter','=','sia_Volantes.idCaracter')
            ->join('sia_CatAcciones as a','a.idAccion','=','sia_Volantes.idAccion')
            ->join('sia_VolantesDocumentos as vd','vd.idVolante','=','sia_Volantes.idVolante')
            ->join('sia_auditorias as audi','audi.idAuditoria','=','vd.cveAuditoria')
            ->join( 'sia_catSubTiposDocumentos as sub','sub.idSubTipoDocumento','=','vd.idSubTipoDocumento')
            ->join('sia_TurnadosJuridico as t','t.idVolante','=','sia_Volantes.idVolante')
            ->where('sub.nombre','=','IFA')
            ->where('t.idAreaRecepcion','=',"$area")
            ->where('t.idTipoTurnado','E')
            ->whereYear('sia_Volantes.fRecepcion','=',"$now")
            ->orderBy("$campo","$tipo")
            ->get();

        	echo $this->render('/Oficios/index.twig',[
            'iracs' => $iracs,
            'sesiones'=> $_SESSION,
            'modulo' => $this->modulo,
            'filejs' => $this->filejs,
            'ruta' => $this->modulo
            ]);

	}

	
    public function create($id) {
        
        $base = new BaseController();
        $baseOficios = new BaseOficiosController();
        $cedula = $base->rol_cedulas('IFA');

        $personas = $baseOficios->load_personal($id);
        echo $this->render('Oficios/create.twig',[
            'sesiones' => $_SESSION,
            'modulo' => $this->modulo,
            'id' => $id,
            'personas' => $personas,
            'filejs' => $this->filejs,
            'ruta' => $this->modulo,
            'cedula' => $cedula
        ]);

    }

    public function save_turnado(array $data,$file, $app) {

        $save = new BaseOficiosController();
        $save->save_turnado($data,$file, $app, $this->modulo);
       
    }



    public function createDocumentos($id){

        $base = new BaseController();
        $baseOficios = new BaseOficiosController();

        $personas = $baseOficios->load_personal($id);
        $cedula = $base->rol_cedulas('IFA');

         echo $this->render('Oficios/documentos.twig',[
            'sesiones' => $_SESSION,
            'modulo' => $this->modulo,
            'id' => $id,
            'turnados' => $personas,
            'filejs' => $this->filejs,
            'ruta' => $this->modulo,
            'cedula' => $cedula
        ]);
    }

   
    public function observaciones($id){

        
        $base = new BaseController();
        $cedula = $base->rol_cedulas('IFA');
        $observaciones = Observaciones::where('idVolante',"$id")->orderBy('idObservacionDoctoJuridico','DESC')->get();

         echo $this->render('Oficios/Observaciones.twig',[
            'sesiones' => $_SESSION,
            'modulo' => $this->modulo,
            'id' => $id,
            'filejs' => $this->filejs,
            'ruta' => $this->modulo,
            'cedula' => $cedula,
            'observaciones' => $observaciones
        ]);
    }


    public function createObservaciones($id){

        $base = new BaseController();
        $cedula = $base->rol_cedulas('IRAC');

         echo $this->render('Oficios/create-observaciones.twig',[
            'sesiones' => $_SESSION,
            'modulo' => $this->modulo,
            'id' => $id,
            'filejs' => $this->filejs,
            'ruta' => $this->modulo,
            'cedula' => $cedula,
            'ckeditor' => $this->ckeditor        
        ]);
    }

    public function save_observaciones(array $data, $app){

       $baseOficios = new BaseOficiosController();
       $baseOficios->save_observaciones($data,$app);
    }

    public function create_Update_Observaciones($id){

        $base = new BaseController();
        $cedula = $base->rol_cedulas('IFA');
        $observacion = Observaciones::find("$id");
        
        echo $this->render('Oficios/update-observaciones.twig',[
            'sesiones' => $_SESSION,
            'modulo' => $this->modulo,
            'filejs' => $this->filejs,
            'ruta' => $this->modulo,
            'cedula' => $cedula,
            'ckeditor' => $this->ckeditor,
            'obsv' => $observacion
        ]);

    }

    public function update_observaciones(array $data, $app) {

        $baseOficios = new BaseOficiosController();
        $baseOficios->update_observaciones($data,$app);

    }


   
    public function createCedula($id){

        $base = new BaseController();
        $cedula = $base->rol_cedulas('IFA');
        $documentos = DocumentosSiglas::where('idVolante',"$id")->get();
        $espacios = Espacios::where('idVolante',"$id")->get();
        
        if($documentos->isEmpty()){

            echo $this->render('Oficios/Ifa/insert-cedula.twig',[
                'sesiones' => $_SESSION,
                'modulo' => $this->modulo,
                'filejs' => $this->filejs,
                'ruta' => $this->modulo,
                'cedula' => $cedula,        
                'idVolante' => $id 
            ]);

        } else {

            echo $this->render('Oficios/Ifa/update-cedula.twig',[
                'sesiones' => $_SESSION,
                'modulo' => $this->modulo,
                'filejs' => $this->filejs,
                'ruta' => $this->modulo,
                'cedula' => $cedula,        
                'documentos' => $documentos,
                'idVolante' => $id,
                'espacios' => $espacios
            ]);

        }

    }

    public function save_cedula(array $data,$app){
        
        $data['estatus'] = 'ACTIVO';
        $idVolante = $data['idVolante'];
        
        $volantesDocumentos = VolantesDocumentos::where('idVolante',"$idVolante")->get();
        $base = new BaseController();

        $subTipoDoc = $volantesDocumentos[0]['idSubTipoDocumento'];
        $validate = $this->validate_cedula($data,true);

        if(empty($validate)){

            $documento = new DocumentosSiglas([
                'idVolante' => $idVolante,
                'idSubTipoDocumento' => $subTipoDoc,
                'idPuestosJuridico' => $data['idPuestosJuridico'],
                'idDocumentoTexto' => $data['idDocumentoTexto'],
                'fOficio' => $data['fOficio'],
                'siglas' => $data['siglas'],
                'usrAlta' => $_SESSION['idUsuario'],
                'estatus' => $data['estatus'],
                'fAlta' => Carbon::now('America/Mexico_City')->format('Y-d-m H:i:s')

            ]);

            $documento->save();

            $espacios = new Espacios([
                'idVolante' => $idVolante,
                'encabezado' => $data['encabezado'],
                'cuerpo' => $data['cuerpo'],
                'pie' => $data['pie'],
                'usrAlta' => $_SESSION['idUsuario']
            ]);

            $espacios->save();

            $sucess = $base->success();
            echo json_encode($sucess);

        } else {

            echo json_encode($validate);
        }
    }


    public function update_cedula(array $data){

        $data['estatus'] = 'ACTIVO';
        $validate = $this->validate_cedula($data,false);
        $idDocumentoSiglas = $data['idDocumentoSiglas']; 
        $idVolante = $data['idVolante'];
        $base = new BaseController();

        if(empty($validate)){

            DocumentosSiglas::find($idDocumentoSiglas)->update([
                'idPuestosJuridico' => $data['idPuestosJuridico'],
                'idDocumentoTexto' => $data['idDocumentoTexto'],
                'fOficio' => $data['fOficio'],
                'siglas' => $data['siglas'],
                'usrModificacion' => $_SESSION['idUsuario'],
                'fModificacion' => Carbon::now('America/Mexico_City')->format('Y-d-m H:i:s')
            ]);

            Espacios::where('idVolante',"$idVolante")->update([
                'encabezado' => $data['encabezado'],
                'cuerpo' => $data['cuerpo'],
                'pie' => $data['pie']
            ]);

            echo  json_encode($base->success());

        } else {

            echo json_encode($validate);

        }

    }

    public function validate_cedula(array $data,$tipo){

        $validate = new ValidateController();

        $res = [];
        $final = [];

        $res[0] = $validate->alphaNumeric($data['siglas'],'siglas',50);
        $res[1] = $validate->alphaNumeric($data['fOficio'],'fOficio',10);
        $res[2] = $validate->alphaNumeric($data['idPuestosJuridico'],'idPuestosJuridico',50);
        $res[3] = $validate->string($data['estatus'],'estatus',10);
        $res[4] = $validate->number($data['encabezado'],'encabezado',false);
        $res[5] = $validate->number($data['cuerpo'],'cuerpo',false);
        $res[6] = $validate->number($data['pie'],'pie',false);
        $res[7] = $validate->number($data['idDocumentoTexto'],'idDocumentoTexto',true);


        if($tipo){
            
            $res[8] = $validate->number($data['idVolante'],'idVolante',true);
        
        } else {

            $res[8] = $validate->number($data['idDocumentoSiglas'],'idDocumentoSiglas',true);    
        }


        foreach ($res as $key => $value) {
            if(!empty($value)){
                array_push($final,$value);
            }
        }


        return $final;

    }


}