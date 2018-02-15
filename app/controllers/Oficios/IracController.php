<?php 
namespace App\Controllers\Oficios;

use Carbon\Carbon;

use App\Controllers\Template;
use App\Controllers\ValidateController;
use App\Controllers\BaseController;
use App\Controllers\Oficios\BaseOficiosController;

use App\Models\Volantes\Volantes;
use App\Models\Catalogos\PuestosJuridico;
use App\Models\Documentos\TurnadosJuridico;
use App\Models\Oficios\Observaciones;

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

         echo $this->render('Oficios/Irac/Observaciones.twig',[
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

        $validate = $this->validate_observaciones(array $data);
        
        if(empty($validate)){

            $observacion = new Observaciones([

            ]);


        } else {

            echo json_encode($validate);
        }


    }


    public function validate_observaciones(array $data){

        $validate = new ValidateController();

        $res = [];
        $final = [];

        
        $res[0] = $validate->alphaNumeric($data['pagina'],'pagina',50);
        $res[1] = $validate->alphaNumeric($data['parrafo'],'parrafo',50);
        $res[2] = $validate->alphaNumeric($data['observacion'],'observacion',350);
        $res[3] = $validate->string($data['estatus'],'estatus',10);

        $res[4] = $validate->number($data['idVolante'],'idVolante',true);

        foreach ($res as $key => $value) {
            if(!empty($value)){
                array_push($final,$value);
            }
        }


        return $final;
    }


}