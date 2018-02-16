<?php
session_start();
// Include the main TCPDF library (search for installation path).
require_once('./tcpdf/tcpdf.php');

$idVolante = $_GET['param'];


function conecta(){
  try{
    require './../../src/conexion.php';
    $db = new \PDO("sqlsrv:Server={$hostname}; Database={$database}", $username, $password );
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

$sql = "select * from sia_plantillasJuridico where idVolante ='$idVolante'";
$db=conecta();
$datos=consultaRetorno($sql, $db);
$id = $datos[0]['idRemitente'];
$puestojuri = $datos[0]['idPuestoJuridico'];



$sql = "select * from sia_RemitentesJuridico where idRemitenteJuridico='$id'";
$db=conecta();
$datosNombre=consultaRetorno($sql, $db);



$sql = "SELECT * FROM sia_PuestosJuridico  where idPuestoJuridico='$puestojuri'";
$db=conecta();
$datosJuri=consultaRetorno($sql, $db);

$saludo  = $datosNombre[0]['saludo'];
$nombrel = mb_strtoupper($datosNombre[0]['nombre'],'utf-8');
$name    = $saludo.' '.$nombrel;
$puesto  = mb_strtoupper($datosNombre[0]['puesto'],'utf-8');

$texto   = $datos[0]['texto'];
$siglas  = $datos[0]['siglas'];
$asunto  = $datos[0]['asunto'];
$folio   = $datos[0]['numFolio'];

$nomde   = $datosJuri[0]['saludo'] . ' ' .$datosJuri[0]['nombre'] . ' ' .$datosJuri[0]['paterno'] . ' ' .$datosJuri[0]['materno'];
$puesde  = $datosJuri[0]['puesto'];



//var_dump($datos);

function convierte($cadena){
  $str = utf8_decode($cadena);
  return $str;
}

function mes($num){
  $meses= ['Enero','Febrero','Marzo','Abril','Mayo','Junio', 'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
  return $meses[$num-1];
}


$feoficio=explode('-',$datos[0]['fOficio']);
$mes2=mes(intval($feoficio[1]));

class MYPDF extends TCPDF {
        // Page footer
    public function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        // Set font
        $this->SetFont('helvetica' , 'I', 10);
        // Page number
        $this->Cell(0, 0,$this->getAliasNumPage(),0,0,'C');
    }
}

// create new PDF document
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Auditoria Superior de la Ciudad de México');
$pdf->SetTitle('Nota Informativa - '.$folio);
 
 $pdf->setPrintHeader(false);
//$pdf->setPrintFooter(false);

// set header and footer fonts
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
//$pdf->SetHeaderMargin(3);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// set some language-dependent strings (optional)
if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
    require_once(dirname(__FILE__).'/lang/eng.php');
    $pdf->setLanguageArray($l);
}

// -------------------------------------------------------------------


// add a page
$pdf->SetFont('helvetica', '', 8);
$pdf->AddPage();

$text1 = '
<table cellspacing="0" cellpadding="0" border="0">
    <tr>
        <td colspan="4"><img src="img/logo-top.png"/></td>
        <td colspan="4"><p><font size="10"><b>DIRECCIÓN GENERAL DE ASUNTOS JURÍDICOS<br>NOTA INFORMATIVA</b><br><br>Ciudad de México, a '. $feoficio[2] . ' de ' .$mes2 . ' de ' . $feoficio[0].'.'.'<br><br><i>"Fiscalizar con Integridad para Prevenir y Mejorar".</i></p></font></td>
    </tr>
</table>';

$pdf->writeHTML($text1);


// -------------------------------------------------------------------
$pdf->SetFont('helvetica', '', 10);
$tbl = <<<EOD
<table cellspacing="0" cellpadding="0" border="0">
    
    <tr>
        <td rowspan="1"><b>PARA: </b></td>
        <td rowspan="4"></td>
        <td colspan="8"><b>$name<br>$puesto</b></td>
        <td colspan="6"></td>
    </tr>
    <tr>
        <td rowspan="2"><b><br>DE: </b></td>
        <td colspan="8"><b><br>$nomde <br>$puesde</b></td>
        <td colspan="6"></td>
    </tr>

</table>
EOD;

$pdf->writeHTML($tbl, true, false, false, false, '');



// -------------------------------------------------------------------
$tbl = <<<EOD
<table cellspacing="0" cellpadding="0" border="0">
    
    <tr>
        <td align="justify">$texto</td>
    </tr>

</table>
EOD;

$pdf->writeHTML($tbl, true, false, false, false, '');

// -----------------------------------------------------------------------------
$pdf->SetFont('helvetica', '', 9);
$tbl = <<<EOD
<table cellspacing="0" cellpadding="0" border="0">
    
    <tr>
        <td>Sin otro particular por el momento, hago propicia la ocasión para enviarle un cordial saludo.<br><br></td>
    </tr>
    <tr>
        <td><b>ATENTAMENTE<br><br></b></td>
    </tr>
    
</table>
EOD;

$pdf->writeHTML($tbl, true, false, false, false, '');
// -----------------------------------------------------------------------------
//saltos de linea
    $sql = "select * from sia_plantillasJuridico where idVolante ='$idVolante'";
    $db=conecta();
    $datos=consultaRetorno($sql, $db);
    $arreglo = $datos[0]['espacios'];
    $total = $arreglo;

   // $total = '0';
    $to = '';

for($i=0;$i<$total;$i++){
    $to .= '<br>';
}

$tbl = <<<EOD
    <table cellspacing="0" cellpadding="0" border="0">
        <tr>
            <td> $to </td>
        </tr>
    </table>
EOD;

$pdf -> writeHTML($tbl,true,false,false,false,'');


//---------------------------------------------------



$pdf->SetFont('helvetica', '', 6);
$sql = "select copias from sia_plantillasJuridico where idVolante ='$idVolante'";
$db=conecta();
$datos=consultaRetorno($sql, $db);
$arreglo = explode(",",$datos[0]['copias']);
//var_dump($arreglo);
$tr = '';
foreach ($arreglo  as $valor){
    $sql = "select * from sia_RemitentesJuridico where idRemitenteJuridico ='$valor'";
    $db=conecta();
    $datos=consultaRetorno($sql, $db);
    $puesto = $datos[0]['puesto'];
    $nombre = mb_strtoupper($datos[0]['nombre'],'utf-8');
    $saludo = $datos[0]['saludo'];

    $tr .=  '<b>' .$saludo .' '. $nombre.', '.'</b>' . $puesto .'.- Presente.- Para su conocimiento.-<br>';
}



$tbl = <<<EOD
<table cellspacing="0" cellpadding="0" border="0">
    <tr>
      <td>c.c.p.</td> 
      <td colspan="6">$tr </td>
    </tr>
</table>
EOD;

$pdf->writeHTML($tbl, true, false, false, false, '');

// -----------------------------------------------------------------------------

$tbl = <<<EOD
  <table cellspacing="0" cellpadding="0" border="0">
    <tr>
        <td colspan="6" align="left">$siglas<br><br></td>
        <td colspan="6" align="right">$folio</td>
    </tr>
  </table>
EOD;

$pdf->writeHTML($tbl, true, false, false, false, '');
// -----------------------------------------------------------------------------

//Close and output PDF document

$pdf->Output('Folio ' .$folio.'.pdf', 'I');

//============================================================+
// END OF FILE
//============================================================+