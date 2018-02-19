<?php

session_start();

$idVolante = $_GET['param1'];

function conecta(){
    try{
      require './../../../../src/conexion.php';
      $db = new PDO("sqlsrv:Server={$hostname}; Database={$database}", $username, $password );
      return $db;
    }catch (PDOException $e) {
      print "ERROR: " . $e->getMessage();
      die();
    }
  }

function consultaRetorno($sql,$db){
		$query=$db->prepare($sql);
		$query->execute();
    return $query->fetchAll(PDO::FETCH_ASSOC);
}

$cuenta=$_SESSION["idCuentaActual"];

$sql="SELECT a.idAuditoria auditoria,ta.nombre tipo, COALESCE(convert(varchar(20),a.clave),convert(varchar(20),a.idAuditoria)) claveAuditoria,
 dbo.lstSujetosByAuditoria(a.idAuditoria) sujeto, dbo.lstObjetosByAuditoria(a.idAuditoria) objeto, a.idArea,
 ar.nombre,a.rubros
 FROM sia_programas p
 INNER JOIN sia_auditorias a on p.idCuenta=a.idCuenta and p.idPrograma=a.idPrograma
 INNER JOIN sia_areas ar on a.idArea=ar.idArea
 LEFT JOIN sia_tiposauditoria ta on a.tipoAuditoria= ta.idTipoAuditoria
 WHERE a.idCuenta='$cuenta' and a.idAuditoria=(select cveAuditoria from sia_VolantesDocumentos where idVolante='$idVolante')
 GROUP BY a.idAuditoria, a.clave,ta.nombre,a.idProceso,a.idEtapa,ar.nombre, a.idArea,ar.nombre, a.rubros";

$db=conecta();
$datos=consultaRetorno($sql, $db);






$sql="select pagina,parrafo,observacion from sia_ObservacionesDoctosJuridico  where idVolante='$idVolante' and estatus='ACTIVO'";

$tabla=consultaRetorno($sql,$db);

$sql="select ds.siglas, ds.fOficio, ds.idPuestosJuridico,dt.texto from sia_DocumentosSiglas ds
left join sia_CatDoctosTextos dt on ds.idDocumentoTexto=dt.idDocumentoTexto
where ds.idVolante='$idVolante'";

$promo=consultaRetorno($sql,$db);



function convierte($cadena){
  $str = utf8_decode($cadena);
  return $str;
}


//$ente=str_replace('/',"\n", $datos[0]['objeto']);

$ente=$datos[0]['rubros'];
$sujeto=str_replace('/',"\n", $datos[0]['sujeto']);

$audit=convierte('AUDITORÍA SUPERIOR DE LA CIUDAD DE MÉXICO');
$dir=convierte('DIRECCIÓN GENERAL DE ASUNTOS JURÍDICOS');
$dijpa=convierte('DIRECCIÓN DE INTERPRETACIÓN JURÍDICA Y DE PROMOCIÓN DE ACCIONES');
$hoja=convierte('HOJÁ DE EVALUACIÓN DE INFORMES FINALES DE AUDÍTORIA ');
$num=convierte('NÚM DE DOCUMENTO');
$cuenta=convierte('CUENTA PÚBLICA 2016');
$fechaTxt=convierte('Ciudad de México, ');
$destTxt=convierte('DR. IVÁN DE JESÚS OLMOS CANSINO');
//$ente=convierte($ente);
//$sujeto=convierte($sujeto);

















// Include the main TCPDF library (search for installation path).
require_once('tcpdf_include.php');
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Nicola Asuni');
$pdf->SetTitle('Observaciones Ifa');


// remove default header/footer
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);

// set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// set some language-dependent strings (optional)
if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
    require_once(dirname(__FILE__).'/lang/eng.php');
    $pdf->setLanguageArray($l);
}

// ---------------------------------------------------------

// set font
$pdf->SetFont('times', '', 9);

// add a page
$pdf->AddPage();

// set some text to print
$txt = <<<EOD
AUDITORÍA SUPERIOR DE LA CIUDAD DE MÉXICO
DIRECCIÓN GENERAL DE ASUNTOS JURÍDICOS
DIRECCIÓN DE INTERPRETACIÓN JURÍDICA Y PROMOCIÓN DE ACCIONES
HOJA DE EVALUACIÓN DE INFORMES FINALES DE AUDITORÍA
CUENTA PÚBLICA 2016
EOD;

// print a block of text using Write()
$pdf->Write(0, $txt, '', 0, 'C', true, 0, false, false, 0);

// ---------------------------------------------------------

$pdf->Ln(5);



$html = <<<EOD
<table cellspacing="0" cellpadding="3" border="1">
    <tr>
        <td>UNIDAD ADMINISTRATIVA AUDITORA</td>
        <td>{$datos[0]['nombre']}</td>
    </tr>
    <tr>
        <td>CLAVE</td>
        <td>{$datos[0]['claveAuditoria']}</td>
    </tr>
    <tr>
        <td>RUBRO AUDITADO</td>
        <td>{$ente}</td>
    </tr>
    <tr>
        <td>TIPO DE AUDITORIA</td>
        <td>{$datos[0]['tipo']}</td>
    </tr>
    <tr>
        <td>ENTE AUDITADO</td>
        <td>{$sujeto}</td>
    </tr>
</table>
EOD;

$pdf->writeHTML($html, true, false, false, false, '');


$txt = <<<EOD
OBSERVACIONES
EOD;


// print a block of text using Write()
$pdf->Write(0, $txt, '', 0, 'C', true, 0, false, false, 0);
$pdf->Ln(3);
$txt='';
$cont=1;
foreach($tabla as $llave => $valor){
    $txt=$txt.'<tr><td align="center" colspan="1" width="40">'.$cont.'</td>';
    foreach($tabla[$llave] as $key=>$value){
        $txt=$txt.'<td>'.$value.'</td>';
    }
    $txt=$txt.'</tr>';
    $cont++;
}
$html = <<<EOD
<table cellspacing="0" cellpadding="3" border="1" >
   <tr>
       <td align="center" colspan="1" width="40" >No.</td>
       <td align="center" colspan="1" width="40" >Hoja</td>
       <td align="center" colspan="1" width="40" >Parrafo</td>
       <td align="left" colspan="1" width="545"></td>
   </tr>
   <tbody>
   $txt</tbody>
</table>
EOD;

//echo $html;
$pdf->writeHTML($html, true, false, false, false, '');

$texto=$promo[0]['texto'];
$html = <<<EOD
<table cellspacing="0" cellpadding="1" border="1" >
<tr><td align="center" >POTENCIALES PROMOCIONES DE ACCIONES:</td></tr>
<tr><td align="left" > $texto</td></tr>
</table>
EOD;



$pdf->writeHTML($html, true, false, false, false, '');


function mes($num){
  $meses= ['Enero','Febrero','Marzo','Abril','Mayo','Junio', 'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
  return $meses[$num-1];
}

$fecha=explode('-',$promo[0]['fOficio']);
//var_dump($fecha);
$mes=mes(intval($fecha[1]));


$html = <<<EOD
<p align="right">Ciudad de México, $fecha[2] de $mes de $fecha[0]</p>
EOD;


$pdf->writeHTML($html, true, false, false, false, '');




$pdf->Ln(7);

$html = <<<EOD
<table cellspacing="0" cellpadding="1" border="0" >
<tr><td align="center" >AUTORIZÓ</td><td align="center">REVISÓ</td></tr>

</table>
EOD;
$pdf->writeHTML($html, true, false, false, false, '');

$pdf->Ln(15);


$usr=$_SESSION["idUsuario"];

$sql="SELECT ar.idArea,pj.puesto juridico,
CONCAT(pj.saludo,' ',pj.nombre,' ',pj.paterno,' ',pj.materno) 
nombre, ds.siglas,ds.fOficio 
FROM sia_Volantes vo 
LEFT JOIN sia_TurnadosJuridico tj on tj.idVolante = vo.idVolante
LEFT JOIN sia_areas ar on tj.idAreaRecepcion = ar.idArea 
LEFT JOIN sia_usuarios us on ar.idEmpleadoTitular=us.idEmpleado 
LEFT JOIN sia_PuestosJuridico pj on us.idEmpleado=pj.rpe 
LEFT JOIN sia_DocumentosSiglas ds on vo.idVolante = ds.idVolante 
WHERE vo.idVolante='$idVolante' and tj.idTipoTurnado='E'";

$jefe=consultaRetorno($sql,$db);
$titular=$jefe[0]['nombre'];
$area=$jefe[0]['juridico'];
$html = <<<EOD
<table cellspacing="0" cellpadding="1" border="0">
<tr>
<td colspan="1" width="70"></td>
<td align="center" colspan="1" width="200" style="border-top:1px solid black" >DR. IVÁN DE JESÚS OLMOS CANSINO</td>
<td colspan="1" width="120"></td>
<td align="center" colspan="1" width="230" style="border-top:1px solid black" >$titular</td>
</tr>
<tr>
<td colspan="1" width="70"></td>
<td align="center" colspan="1" width="200"  >DIRECTOR GENERAL DE ASUNTOS JURÍDICOS</td>
<td colspan="1" width="120"></td>
<td align="center" colspan="1" width="230"  >$area</td>
</tr>

</table>
EOD;

$pdf->writeHTML($html, true, false, false, false, '');


$ef=explode(",",$promo[0]['idPuestosJuridico']);
//var_dump($ef);
$nombres=array();
$puestos=array();
$saludo=array();
for($i=0;$i<count($ef)-1;$i++){
    if(!empty($ef[$i])){
      $usrf=$ef[$i];
      //var_dump($ef[$i]);
    }
    $sql="select concat(saludo,' ',nombre,' ',paterno,' ',materno) as nombre,puesto  
from sia_PuestosJuridico
where idPuestoJuridico='$usrf'";
    $nombre=consultaRetorno($sql,$db);
    array_push($nombres,$nombre[0]['nombre']);
    array_push($puestos,$nombre[0]['puesto']);
   //  array_push($saludo,$nombre[0]['saludo']);
}
//var_dump($nombres);
//var_dump($puestos);
$linea='';
$elaboro='';

$firmaSecond='';
$elementos=count($nombres);
//var_dump($elementos);

  if($elementos == 1){
     $elaboro=$elaboro.'<br><tr><td align="center"><p>ELABORÓ</p><br><br>'. $nombres[$elementos-1].'<br>'.$puestos[$elementos-1].'</td><td></td></tr>';
     
  }elseif ($elementos==2) {
    $elaboro='<tr>';
    foreach ($nombres as $llave => $valor) {
      
        $elaboro=$elaboro.'<td align="center"><p>ELABORÓ</p><br><br><br><br>'.$nombres[$llave].'<br>'.$puestos[$llave].'</td>';
      } 
    $elaboro=$elaboro.'</tr>';
  }elseif ($elementos==3) {
    $cont=1;
    $elaboro='<tr>';
    foreach ($nombres as $llave => $valor) {
        if($cont>2){
          $elaboro=$elaboro.'<br><br><br><br><tr><td align="center"><p>ELABORÓ</p><br><br><br><br>'.$nombres[$llave].' '. $valor.'<br>'.$puestos[$llave].'</td></tr>';
        }elseif($cont>1){

        $elaboro=$elaboro.'<td align="center"><p>ELABORÓ</p><br><br><br><br>'.$nombres[$llave].' '.$valor.'<br><br><br>'.$puestos[$llave].'</td></tr>';

        }else{
           $elaboro=$elaboro.'<td align="center"><p>ELABORÓ</p><br><br><br><br>'.$nombres[$llave].' '.$valor.'<br>'.$puestos[$llave].'</td>';
        }
        $cont++;
      } 
   
  }






$lineaPuestos='';

foreach ($puestos as $key => $value) {
  $lineaPuestos=$lineaPuestos.'<td align="center" colspan="1"  >'.$value.'</td>';
}
//echo $linea;
$pdf->Ln(20);

$html = <<<EOD
<table cellspacing="0" cellpadding="1" border="0" >
$elaboro
</table>
EOD;
$pdf->writeHTML($html, true, false, false, false, '');
$pdf->Ln(5);

if($cont>2){
  
$html = <<<EOD
<table cellspacing="0" cellpadding="1" border="0" >
$firmaSecond
</table>
EOD;
$pdf->writeHTML($html, true, false, false, false, '');
}

$pdf->Ln(10);

$siglas=$promo[0]['siglas'];

$html = <<<EOD
<p>$siglas</p>
EOD;

$pdf->writeHTML($html, true, false, false, false, '');



//Close and output PDF document
$pdf->Output('example_002.pdf', 'I');

//============================================================+
// END OF FILE
//============================================================+
