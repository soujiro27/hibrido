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
use App\Models\Oficios\Plantillas;

use App\Controllers\ApiController;

class DiversosController extends Template {

	private $modulo = 'DocumentosDiversos';
    private $filejs = 'DocumentosDiversos';
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



         $iracs = Volantes::select('sia_Volantes.*','c.nombre as caracter','a.nombre as accion','sia_Volantes.extemporaneo','t.idEstadoTurnado')
            ->join('sia_catCaracteres as c','c.idCaracter','=','sia_Volantes.idCaracter')
            ->join('sia_CatAcciones as a','a.idAccion','=','sia_Volantes.idAccion')
            ->join('sia_VolantesDocumentos as vd','vd.idVolante','=','sia_Volantes.idVolante')
            ->join( 'sia_catSubTiposDocumentos as sub','sub.idSubTipoDocumento','=','vd.idSubTipoDocumento')
            ->join('sia_TurnadosJuridico as t','t.idVolante','=','sia_Volantes.idVolante')
            ->where('sub.auditoria','NO')
            ->where('t.idAreaRecepcion','=',"$area")
            ->where('t.idTipoTurnado','E')
            ->whereYear('sia_Volantes.fRecepcion','=',"$now")
            ->orderBy("$campo","$tipo")
            ->get();

    	echo $this->render('/Oficios/Diversos/index.twig',[
            'iracs' => $iracs,
            'sesiones'=> $_SESSION,
            'modulo' => $this->modulo,
            'filejs' => $this->filejs
            ]);


	}

    public function create($id) {
		
        $base = new BaseController();
        $baseOficios = new BaseOficiosController();
        $cedula = $base->rol_cedulas('NOTA');

        $personas = $this->load_personal();

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

        $personas = $this->load_personal();
        $cedula = $base->rol_cedulas('NOTA');

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

    public function tipo_cedula($id){

        $tipoQuery = VolantesDocumentos::select('sub.idTipoDocto')
                    ->join('sia_catSubTiposDocumentos as sub','sub.idSubtipoDocumento','=','sia_VolantesDocumentos.idSubtipoDocumento')
                    ->where('sia_VolantesDocumentos.idVolante',"$id")
                    ->get();
        $tipo = $tipoQuery[0]['idTipoDocto'];

        return $tipo;

    }

    public function createCedula($id){

        $base = new BaseController();
        $cedula = $base->rol_cedulas('NOTA');
        
        $tipo = $this->tipo_cedula($id);

        $plantilla = Plantillas::where('idVolante',"$id")->get();

        if($plantilla->isEmpty()){

            echo $this->render('Oficios/Diversos/insert-cedula.twig',[
                'sesiones' => $_SESSION,
                'modulo' => $this->modulo,
                'id' => $id,
                'filejs' => $this->filejs,
                'ruta' => $this->modulo,
                'cedula' => $cedula,
                'tipo' => $tipo,
                'ckeditor' => $this->ckeditor
            ]);

        } else {

            echo $this->render('Oficios/Diversos/update-cedula.twig',[
                'sesiones' => $_SESSION,
                'modulo' => $this->modulo,
                'id' => $id,
                'filejs' => $this->filejs,
                'ruta' => $this->modulo,
                'cedula' => $cedula,
                'tipo' => $tipo,
                'plantillas' => $plantilla,
                'ckeditor' => $this->ckeditor
            ]);

        }
    }

    public function create_array_cedula($data){

        $id = $data['idVolante'];
        $tipo = $this->tipo_cedula($id);
        $remitente = Volantes::find($id);

        
        $copias = $data['internos'].','.$data['externos'];
        $last = substr($copias,-1);
        $first = substr($copias,0,1);

        

        if($last == ','){

            $copias = substr($copias,0,-1);

        } elseif ( $first == ','){

            $copias = substr($copias,1);
        }



        $datos  = array(
            'idVolante' => $id,
            'numFolio' => $data['numFolio'],
            'fOficio' => $data['fOficio'],
            'idRemitente' => $remitente['idRemitenteJuridico'],
            'texto' => $data['texto'],
            'siglas' => $data['siglas'],
            'copias' => $copias,
            'espacios' => $data['espacios']
        );

        if($tipo == 'OFICIO' || $tipo == 'CIRCULAR'){

            $datos['asunto'] = $data['asunto'];

        } else {

            $datos['idPuestoJuridico'] = $data['idPuestoJuridico'];

        }

        return $datos;
    }

    public function save_cedula(array $data){
        
        $validate = $this->validate_cedula($data,true);

        $base = new BaseController();

        if(empty($validate)){

            $datos = $this->create_array_cedula($data);
            $datos['usrAlta'] = $_SESSION['idUsuario'];
            $datos['fAlta'] = Carbon::now('America/Mexico_City')->format('Y-m-d H:i:s');
            $plantilla =  new Plantillas($datos);
            $plantilla->save();

           echo json_encode($base->success());


        } else {
           echo json_encode($validate);
        }

    }

    public function validate_cedula($data,$tipo){


        $id = $data['idVolante'];
        $tipoGral = $this->tipo_cedula($id);

       
        $validate = new ValidateController();

        $res = [];
        $final = [];

        $res[0] = $validate->alphaNumeric($data['numFolio'],'numFolio',20);
        $res[1] = $validate->alphaNumeric($data['fOficio'],'fOficio',10);
        $res[3] = $validate->alphaNumeric($data['siglas'],'siglas',50);
        $res[4] = $validate->alphaNumeric($data['texto'],'texto',10000);
        $res[5] = $validate->number($data['espacios'],'espacios',false);
        

        if($tipoGral == 'OFICIO' || $tipoGral=='CIRCULAR'){
            
            $res[6] = $validate->string($data['asunto'],'asunto',30);

        } else{

            $res[6] = $validate->alphaNumeric($data['idPuestoJuridico'],'idPuestoJuridico',50);
        }


        if($tipo){
            
            $res[7] = $validate->number($data['idVolante'],'idVolante',true);
        
        } else {

            $res[7] = $validate->number($data['idPlantillaJuridico'],'idPlantillaJuridico',true);    
        }


        foreach ($res as $key => $value) {
            if(!empty($value)){
                array_push($final,$value);
            }
        }


        return $final;
    }

    public function update_cedula(array $data){

        $validate = $this->validate_cedula($data,false);
        $idPlantillaJuridico = $data['idPlantillaJuridico'];

        $base = new BaseController();

        if(empty($validate)){

            $datos = $this->create_array_cedula($data);
            $datos['usrModificacion'] = $_SESSION['idUsuario'];
            $datos['fModificacion'] = Carbon::now('America/Mexico_City')->format('Y-m-d H:i:s');

            Plantillas::find($idPlantillaJuridico)->update($datos);
            echo json_encode($base->success());


        } else {

            echo json_encode($validate);

        }

    }

   
    
    public function load_personal(){

        $idTurnado = $_SESSION['idArea'];


        $rpe = $_SESSION['idEmpleado'];

        $puestos = PuestosJuridico::where('idArea',"$idTurnado")
                                    ->where('rpe','<>',"$rpe")
                                    ->get();
        return $puestos;

    }



}