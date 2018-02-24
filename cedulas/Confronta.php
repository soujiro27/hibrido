<?php
//require './fpdf.php';

require('mc_table.php');

$idVolante = $_GET['param1'];

function conecta(){
    try{
      require './../../src/conexion.php';
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


$sql="select v.idRemitente,v.numDocumento,
a.idEmpleadoTitular,a.nombre as area,
CONCAT(u.saludo,' ',u.nombre,' ',u.paterno,' ',u.materno) as titular,
audi.clave,
dbo.lstSujetosByAuditoria(audi.idAuditoria) as ente,
con.notaInformativa, con.nombreResponsable, con.cargoResponsable, con.siglas, con.siglas,con.fOficio,con.hConfronta, con.fConfronta, con.numFolio, catsub.idTipoDocto as tipo
from sia_Volantes v
left join sia_VolantesDocumentos vd on v.idVolante=vd.idVolante
left join sia_areas a on v.idRemitente=a.idArea
left join sia_empleados e on a.idEmpleadoTitular=e.idEmpleado
left join sia_usuarios u on e.idEmpleado=u.idEmpleado
left join sia_auditorias audi on vd.cveAuditoria=audi.idAuditoria
left join sia_confrontasJuridico con on v.idVolante=con.idVolante
left join sia_catSubTiposDocumentos catsub on vd.idSubTipoDocumento=catsub.idSubTipoDocumento
where v.idVolante='$idVolante'";

$db=conecta();
$datos=consultaRetorno($sql, $db);




//var_dump($datos);

function convierte($cadena){
  $str = utf8_decode($cadena);
  return $str;
}

function mes($num){
  $meses= ['Enero','Febrero','Marzo','Abril','Mayo','Junio', 'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
  return $meses[$num-1];
}


//$ente=str_replace('/',',', $datos[0]['ente']);
$ente=$datos[0]['ente'];
$fecha=explode('-',$datos[0]['fOficio']);
$mes=mes($fecha[1]);

$textoNota='Hago referencia a su Nota Informativa '.$datos[0]['numDocumento'];
$texto='Hago referencia a su oficio '.$datos[0]['numDocumento'];
$textoDos=' y Nota Informativa '.$datos[0]['notaInformativa'];
$textoTres=' mediante los cuales solicita se proporcione el nombre del servidor público que asistira a la reunión de Confronta, correspondiente a la Cuenta Pública 2016, sobre el particular, se informa el nombre del representante:';
$textoTres=convierte($textoTres);


if($datos[0]['tipo']=='NOTA'){
  $textoFinal=$textoNota.$textoTres;
}else{
//echo empty($datos[0]['notaInformativa']);
  if(empty($datos[0]['notaInformativa'])){
  $textoFinal=$texto.$textoTres;
  }else{
    $textoFinal=$texto.$textoDos.$textoTres;
  }

}

$audit=convierte('AUDITORÍA SUPERIOR DE LA CIUDAD DE MÉXICO');
$dir=convierte('DIRECCIÓN GENERAL DE ASUNTOS JURÍDICOS');
$num=convierte('NÚM DE DOCUMENTO');
$fechaTxt=convierte('Ciudad de México, ');
$titular=convierte($datos[0]['titular']);
$areaTxt=convierte($datos[0]['area']);
$destTxt=convierte('DR. IVÁN DE JESÚS OLMOS CANSINO');
$ente=convierte($ente);
$footer=convierte('Sin otro particular, hago propicia la ocasión para enviarle un coordial saludo.');
$hora=substr($datos[0]['hConfronta'],0,-11);
$tiempo=$datos[0]['fConfronta']."\n".$hora.' Horas.';
$director=convierte('DIRECTOR GENERAL DE ASUNTOS JURÍDICOS');




$pdf = new PDF_MC_Table();
$pdf->AddPage();
$pdf->SetFont('Arial','B',10);
$pdf->Image('./img/logo-top.png',10,8,33);
$pdf->Cell(80,1,'',0);
$pdf->Cell(50,1,$dir,0,0,'L');
$pdf->Ln(5);
$pdf->Cell(80,1,'',0);
$pdf->Cell(50,5,'NOTA INFORMATIVA',0,0,'L');

$pdf->SetFont('Arial','',10);
$pdf->Ln(10);
$pdf->Cell(180,5,$fechaTxt.$fecha[2].' de '.$mes.' de '.$fecha[0],0,0,'R');
$pdf->Ln(5);
$pdf->SetFont('Arial','I',10);
$pdf->Cell(180,5,'"Fiscalizar con Integridad para Prevenir y Mejorar".',0,0,'R');
$pdf->Ln(10);
$pdf->SetFont('Arial','B',10);
$pdf->Cell(20,5,'PARA: ',0,0,'L');
$pdf->Cell(90,5,$titular,0,0,'L');
$pdf->Ln(10);
$pdf->Cell(20,5,'',0,0,'L');
$pdf->MultiCell(60,5,$areaTxt,0,'L');

$pdf->Ln(2);
$pdf->Cell(20,5,'DE: ',0,0,'L');
$pdf->Cell(90,5,$destTxt,0,0,'L');
$pdf->Ln(5);
$pdf->Cell(20,5,'',0,0,'L');
$pdf->Cell(90,5,$director,0,0,'L');
$pdf->Ln(15);

$pdf->SetFont('Arial','',10);
$pdf->MultiCell(180,5,$textoFinal,0,'L');
$pdf->Ln(10);
$pdf->Cell(15,5,'',0,0,'L');
$pdf->SetFillColor(198,200,204);
$pdf->Cell(50,5,'ENTIDAD',1,0,'C',true);
$pdf->Cell(20,5,'CLAVE',1,0,'C',true);
$pdf->Cell(20,5,'DIA/HORA',1,0,'C',true);
$pdf->Cell(30,5,'ASISTE',1,0,'C',true);
$pdf->Cell(30,5,'CARGO',1,0,'C',true);

$pdf->Ln(5);
$pdf->SetFont('Arial','B',6);
$pdf->Cell(15,5,'',0,0,'L');
$pdf->SetWidths(array(50,20,20,30,30));
$pdf->Row(array($ente,$datos[0]['clave'],$tiempo,$datos[0]['nombreResponsable'],$datos[0]['cargoResponsable']));


$pdf->SetFont('Arial','',10);
$pdf->Ln(10);
$pdf->Cell(120,5,$footer,0,0,'L');
$pdf->Ln(10);
$pdf->SetFont('Arial','B',10);
$pdf->Cell(120,5,'ATENTAMENTE',0,0,'L');
$pdf->Ln(50);
$pdf->SetFont('Arial','',10);
$pdf->Cell(170,5,$datos[0]['siglas'],0,0,'L');
$pdf->Cell(120,5,$datos[0]['numFolio'],0,0,'L');


$pdf->Output();

?>
