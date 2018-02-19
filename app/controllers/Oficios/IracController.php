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

class IracController extends Template {

	private $modulo = 'Irac';
    private $filejs = 'Irac';
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
            ->where('sub.nombre','=','IRAC')
            ->where('t.idAreaRecepcion','=',"$area")
            ->where('t.idTipoTurnado','E')
            ->whereYear('sia_Volantes.fRecepcion','=',"$now")
            ->orderBy("$campo","$tipo")
            ->get();

        	echo $this->render('/Oficios/index.twig',[
            'iracs' => $iracs,
            'sesiones'=> $_SESSION,
            'modulo' => $this->modulo,
            'ruta' => $this->modulo,
            'filejs' => $this->filejs
            ]);

	}

	public function create($id) {
		
        $base = new BaseController();
        $cedula = $base->rol_cedulas('IRAC');

        $personas = $this->load_personal($id);
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

        $personas = $this->load_personal($id);
        $cedula = $base->rol_cedulas('IRAC');

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




    public function load_personal($id){

        $turnado_volantes = TurnadosJuridico::select('idAreaRecepcion')->where('idVolante',"$id")->get();
        $idTurnado = $turnado_volantes[0]['idAreaRecepcion'];

        $rpe = $_SESSION['idEmpleado'];

        $puestos = PuestosJuridico::where('idArea',"$idTurnado")
                                    ->where('rpe','<>',"$rpe")
                                    ->get();
        return $puestos;

    }

    public function observaciones($id){

        
        $base = new BaseController();
        $cedula = $base->rol_cedulas('IRAC');
        $observaciones = Observaciones::where('idVolante',"$id")->orderBy('idObservacionDoctoJuridico','DESC')->get();

         echo $this->render('Oficios/Irac/Observaciones.twig',[
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

         echo $this->render('Oficios/Irac/create-observaciones.twig',[
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
                'estatus' => $data['estatus'],
                'fAlta' => Carbon::now('America/Mexico_City')->format('Y-m-d H:i:s')
            ]);

            $observacion->save();
            $success = $base->success();
            echo json_encode($success);


        } else {

            echo json_encode($validate);
        }

    }

    public function create_Update_Observaciones($id){

        $base = new BaseController();
        $cedula = $base->rol_cedulas('IRAC');
        $observacion = Observaciones::find("$id");
        
        echo $this->render('Oficios/Irac/update-observaciones.twig',[
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

        $validate = $this->validate_observaciones($data,false);
        $id = $data['idObservacionDoctoJuridico'];
        $base = new BaseController();

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
        $res[2] = $validate->alphaNumeric($data['observacion'],'observacion',350);
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

    public function createCedula($id){

        $base = new BaseController();
        $cedula = $base->rol_cedulas('IRAC');
        $documentos = DocumentosSiglas::where('idVolante',"$id")->get();
        $espacios = Espacios::where('idVolante',"$id")->get();
        
        if($documentos->isEmpty()){

            echo $this->render('Oficios/Irac/insert-cedula.twig',[
                'sesiones' => $_SESSION,
                'modulo' => $this->modulo,
                'filejs' => $this->filejs,
                'ruta' => $this->modulo,
                'cedula' => $cedula,        
                'idVolante' => $id 
            ]);

        } else {

            echo $this->render('Oficios/Irac/update-cedula.twig',[
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
                'fOficio' => $data['fOficio'],
                'siglas' => $data['siglas'],
                'numFolio' => $data['numFolio'],
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
                'fOficio' => $data['fOficio'],
                'siglas' => $data['siglas'],
                'numFolio' => $data['numFolio'],
                'usrModificacion' => $_SESSION['idUsuario'],
                'fModificacion' => Carbon::now('America/Mexico_City')->format('Y-m-d H:i:s')
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
        $res[3] = $validate->alphaNumeric($data['numFolio'],'numFolio',15);
        $res[4] = $validate->string($data['estatus'],'estatus',10);
        $res[5] = $validate->number($data['encabezado'],'encabezado',false);
        $res[6] = $validate->number($data['cuerpo'],'cuerpo',false);
        $res[7] = $validate->number($data['pie'],'pie',false);


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