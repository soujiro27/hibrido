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
use App\Models\Oficios\Confrontas;

use App\Controllers\ApiController;

class ConfrontasController extends Template {

	private $modulo = 'confrontasJuridico';
    private $filejs = 'Confronta';

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
            ->where('sub.nombre','=','CONFRONTA')
            ->where('t.idAreaRecepcion','=',"$area")
            ->where('t.idTipoTurnado','E')
            ->whereYear('sia_Volantes.fRecepcion','=',"$now")
            ->orderBy("$campo","$tipo")
            ->get();

        	echo $this->render('/oficios/index.twig',[
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
        $cedula = $base->rol_cedulas('NOTA');

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


    public function createCedula($id){

        $base = new BaseController();
        $cedula = $base->rol_cedulas('NOTA');
        $documentos = Confrontas::where('idVolante',"$id")->get();
        $espacios = Espacios::where('idVolante',"$id")->get();
        $nota = VolantesDocumentos::where('idVolante',"$id")->get();
        
        if($documentos->isEmpty()){

            echo $this->render('Oficios/Confronta/insert.twig',[
                'sesiones' => $_SESSION,
                'modulo' => $this->modulo,
                'filejs' => $this->filejs,
                'ruta' => $this->modulo,
                'cedula' => $cedula,        
                'idVolante' => $id,
                'nota' => $nota
            ]);

        } else {

            echo $this->render('Oficios/Confronta/update.twig',[
                'sesiones' => $_SESSION,
                'modulo' => $this->modulo,
                'filejs' => $this->filejs,
                'ruta' => $this->modulo,
                'cedula' => $cedula,        
                'documentos' => $documentos,
                'idVolante' => $id,
                'espacios' => $espacios,
                'nota' => $nota
            ]);

        }

    }

    public function save_cedula(array $data){

        $data['estatus'] = 'ACTIVO';
        $validate = $this->validate_cedula($data,true);
        $base = new BaseController();
        

        if(empty($validate)){

            $datos = $this->ceate_array_cedula_confronta($data);

            $datos['usrAlta'] = $_SESSION['idUsuario'];
            $datos['fAlta'] = Carbon::now('America/Mexico_City')->format('Y-d-m H:i:s');
            
            $confronta = new Confrontas($datos);
            $confronta->save();

            echo json_encode($base->success());            

        } else {

            echo json_encode($validate);
        }

    }

    public function update_cedula($data,$app){

        $data['estatus'] = 'ACTIVO';
        $validate = $this->validate_cedula($data,false);
        $id = $data['idConfrontaJuridico']; 


        if(empty($validate)){


            $datos = $this->ceate_array_cedula_confronta($data);
            $datos['usrModificacion'] = $_SESSION['idUsuario'];
            $datos['fModificacion'] = Carbon::now('America/Mexico_City')->format('Y-m-d H:i:s');
            
            Confrontas::find($id)->update($datos);

            $base = new BaseController();
            echo json_encode($base->success());

        } else {

            echo json_encode($validate);
        }
    }

    public function ceate_array_cedula_confronta(array $data){

        $idVolante = $data['idVolante'];
        $volantes = VolantesDocumentos::where('idVolante',"$idVolante")->get();
        $nota = $volantes[0]['notaConfronta'];

        $insert = array(
            'idVolante' => $data['idVolante'],
            'nombreResponsable' => $data['nombreResponsable'],
            'cargoResponsable' => $data['cargoResponsable'],
            'siglas' => $data['siglas'],
            'hConfronta' => $data['hConfronta'],
            'fConfronta' => $data['fConfronta'],
            'fOficio' => $data['fOficio'],
            'numFolio' => $data['numFolio'],
        );

        if($nota == 'SI'){

            $insert['notaInformativa'] = $data['notaInformativa'];
        }

        return $insert;

    }

    public function validate_cedula(array $data,$tipo){

        $idVolante = $data['idVolante'];
        $volantes = VolantesDocumentos::where('idVolante',"$idVolante")->get();
        $nota = $volantes[0]['notaConfronta'];

        $validate = new ValidateController();


        $res = [];
        $final = [];

        $res[0] = $validate->string($data['nombreResponsable'],'nombreResponsable',30);
        $res[1] = $validate->string($data['cargoResponsable'],'cargoResponsable',30);
        $res[2] = $validate->alphaNumeric($data['fConfronta'],'fConfronta',10);
        $res[3] = $validate->alphaNumeric($data['hConfronta'],'hConfronta',5);
        $res[4] = $validate->alphaNumeric($data['fOficio'],'fOficio',10);
        $res[5] = $validate->alphaNumeric($data['siglas'],'siglas',50);
        $res[6] = $validate->alphaNumeric($data['numFolio'],'numFolio',50);
        $res[7] = $validate->string($data['estatus'],'estatus',8);




        if($tipo){
            
            $res[8] = $validate->number($data['idVolante'],'idVolante',true);
        
        } else {

            $res[8] = $validate->number($data['idConfrontaJuridico'],'idConfrontaJuridico',true);    
        }

        if($nota == 'SI'){

            $res[9] = $validate->alphaNumeric($data['notaInformativa'],'notaInformativa',20);
        }


        foreach ($res as $key => $value) {
            if(!empty($value)){
                array_push($final,$value);
            }
        }


        return $final;


    }

}