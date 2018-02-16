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

$sql="select v.idTipoDocto, v.numDocumento,v.fDocumento,
 v.fRecepcion, v.hRecepcion, v.destinatario,v.folio, v.anexos,v.idTurnado,v.idRemitente, v.asunto,
cr.nombre as caracter,
a.nombre as accion,
ar.nombre as area,
CONVERT (date, SYSDATETIMEOFFSET()) as fActual,
CONCAT(e.nombre,' ',e.paterno,' ',e.materno) as titular
from sia_Volantes v
inner join sia_CatCaracteres cr on v.idCaracter=cr.idCaracter
inner join sia_CatAcciones a on v.idAccion=a.idAccion
inner join sia_areas ar on v.idRemitente=ar.idArea
inner join sia_empleados e on ar.idEmpleadoTitular=e.idEmpleado
where v.idVolante='$idVolante'";


$db=conecta();
$datos=consultaRetorno($sql, $db);
//echo json_encode($datos);

function convierte($cadena){
  $str = utf8_decode($cadena);
  return $str;
}

$audit='AUDITORÍA SUPERIOR DE LA CIUDAD DE MÉXICO';
$dir='DIRECCIÓN GENERAL DE ASUNTOS JURÍDICOS';
$num='NÚM DE DOCUMENTO';
$titular=$datos[0]['titular'];
$area=$datos[0]['area'];
$dest=$datos[0]['destinatario'];
$asunto=$datos[0]['asunto'];
$document =$datos[0]['numDocumento'];
$tipo= $datos[0]['idTipoDocto'];


class MYPDF extends TCPDF {
        // Page footer
    public function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        // Set font
        $this->SetFont('helvetica' , 'I', 10);
        // Page number
       // $this->Cell(0, 0,$this->getAliasNumPage(),0,0,'C');
    }
}


// create new PDF document
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Auditoria Superior de la Ciudad de México');
$pdf->SetTitle($document);


// set default header data
$pdf->setPrintHeader(false);
// set header and footer fonts
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// set margins
$pdf->SetMargins(PDF_MARGIN_LEFT,8, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(2);
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

// ---------------------------------------------------------

$pdf->SetFont('helvetica', '', 8);
$pdf->AddPage();

$text1 = '
<table cellspacing="0" cellpadding="0" border="0">
    <tr>
        <td colspan="2"><img src="./img/logo-top.png"/></td>
        <td colspan="1"></td>
        <td colspan="7"><p><font size="10"><b>'.$audit.'<br>'.$dir.'</b></p></font></td>
    </tr>
</table>';

$pdf->writeHTML($text1);

//----------------------------------------------

$pdf->SetFont('helvetica','B');
$pdf->SetXY(165,30);
$pdf->Cell(15, 5, 'Folio', 1, 1, 'C', 0, '', 1);
$pdf->SetXY(180,30);
$pdf->Cell(18, 5, $datos[0]['folio'], 1, 1, 'C', 0, '', 1);
$pdf->SetXY(15,35);
$pdf->Cell(45, 5, 'DATOS DEL DOCUMENTO', 1, 1, 'C', 0, '', 1);
$pdf->SetXY(165,35);
$pdf->Cell(15, 5, 'Fecha', 1, 1, 'C', 0, '', 1);
$pdf->SetXY(180,35);
$pdf->Cell(18, 5, $datos[0]['fActual'], 1, 1, 'C', 0, '', 1);

$pdf->SetXY(15,40);
$pdf->Cell(30, 5,'TIPO', 1, 1, 'C', 0, '', 1);
$pdf->SetXY(45,40);
$pdf->Cell(48, 5,'NÚM DE DOCUMENTO', 1, 1, 'C', 0, '', 1);
$pdf->SetXY(93,40);
$pdf->Cell(40, 5,'FECHA DOCUMENTO', 1, 1, 'C', 0, '', 1);
$pdf->SetXY(133,40);
$pdf->Cell(25, 5,'ANEXOS', 1, 1, 'C', 0, '', 1);
$pdf->SetXY(158,40);
$pdf->Cell(40, 5,'FECHA RECEPCION', 1, 1, 'C', 0, '', 1);

$pdf->SetXY(15,45);
$pdf->Cell(30, 5,$datos[0]['idTipoDocto'], 1, 1, 'C', 0, '', 1);
$pdf->SetXY(45,45);
$pdf->Cell(48, 5,$datos[0]['numDocumento'], 1, 1, 'C', 0, '', 1);
$pdf->SetXY(93,45);
$pdf->Cell(40, 5,$datos[0]['fDocumento'], 1, 1, 'C', 0, '', 1);
$pdf->SetXY(133,45);
$pdf->Cell(25, 5,$datos[0]['anexos'], 1, 1, 'C', 0, '', 1);
$pdf->SetXY(158,45);
$pdf->Cell(40, 5,$datos[0]['fRecepcion'], 1, 1, 'C', 0, '', 1);

$pdf->Ln(15);
$pdf->Cell(45, 5,'REMITENTE', 1, 1, 'C', 0, '', 1);
$pdf->SetXY(15,70);
$pdf->Cell(77, 5,'NOMBRE', 1, 1, 'J', 0, '', 1);
$pdf->SetXY(92,70);
$pdf->Cell(105, 5,'CARGO', 1, 1, 'J', 0, '', 1);

$pdf->SetXY(15,75);
$pdf->Cell(15, 5,$datos[0]['idRemitente'], 1, 1, 'J', 0, '', 1);
$pdf->SetXY(30,75);
$pdf->Cell(62, 5,$titular, 1, 1, 'L', 0, '', 1);
$pdf->SetXY(92,75);
$pdf->Cell(105, 5,$area, 1, 1, 'L', 0, '', 1);

$pdf->Ln(13);
$pdf->Cell(91, 5,'DEPENDENCIA', 1, 1, 'C', 0, '', 1);
$pdf->SetXY(106,93);
$pdf->Cell(91, 5,'DESTINATARIO', 1, 1, 'C', 0, '', 1);


$pdf->SetXY(15,98);
$pdf->Cell(91, 5,$audit, 1, 1, 'C', 0, '', 1);
$pdf->SetXY(106,98);
$pdf->Cell(91, 5,$dest, 1, 1, 'C', 0, '', 1);


$pdf->Ln(10);
$pdf->Cell(45, 5,'ASUNTO', 1, 1, 'L', 0, '', 1);
$pdf->MultiCell(183, 30,$asunto,1, 'L', 0, 1, '', '', true, 0, false, true, 40, 'M');

$pdf->Ln(5);
$pdf->Cell(61, 5,'CARACTER', 1, 1, 'C', 0, '', 1);
$pdf->SetXY(76,153);
$pdf->Cell(61, 5,'ACUSE DE RECIBO:', 1, 1, 'C', 0, '', 1);
$pdf->SetXY(137,153);
$pdf->Cell(61, 5,'NOMBRE Y FIRMA: ', 1, 1, 'C', 0, '', 1);

$pdf->SetXY(15,158);
$pdf->Cell(61, 5,$datos[0]['caracter'], 1, 1, 'C', 0, '', 1);
$pdf->SetXY(76,158);
$pdf->Cell(61, 5,$datos[0]['idTurnado'], 1, 1, 'C', 0, '', 1);
$pdf->SetXY(137,158);
$pdf->Cell(61, 5,$datos[0]['accion'], 1, 1, 'C', 0, '', 1);


$pdf->Ln(10);
$pdf->Cell(45, 5,'CONTROL DE GESTION', 1, 1, 'L', 0, '', 1);
$pdf->SetXY(15,178);
$pdf->Cell(183, 10,'ACUSE DE RECIBO: ', 1, 1, 'L', 0, '', 1);
$pdf->SetXY(15,188);
$pdf->Cell(183, 10,'NOMBRE Y FIRMA: ', 1, 1, 'L', 0, '', 1);

$pdf->Ln(5);
$pdf->Cell(45,5,'ACCIONES IMPLEMENTADAS', 1, 1, 'L', 0, '', 1);
$pdf->SetXY(15,208);
$pdf->Cell(183, 5,'', 1, 1, 'L', 0, '', 1);

$pdf->Ln(5);
$pdf->Cell(45,5,'EXPEDIENTE', 1, 1, 'L', 0, '', 1);
$pdf->SetXY(15,223);
$pdf->Cell(183, 5,'', 1, 1, 'L', 0, '', 1);

$pdf->Ln(20);
$pdf->Cell(90,5,'',0,0,'C');
$pdf->Cell(45,5,'PENDIENTE',0,0,'C');
$pdf->Cell(45,5,'DESAHOGO','T',0,'C');




// ---------------------------------------------------------

//Close and output PDF document
$pdf->Output($tipo.' ' .$document.'.pdf', 'I');

//============================================================+
// END OF FILE
//============================================================+
