<?
if (!defined('_IN_ADMIN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}
require("$_includesdir/fpdf.php");
define('FPDF_FONTPATH',"$_includesdir/fonts/");
global $db, $fecha_ini, $fecha_fin, $motivo,$_site_name;

if($motivo!="")
{
	$and_motivo=" AND v.motivo_id=$motivo";
	$sql="select motivo from crm_prospectos_cancelaciones_motivos where motivo_id=$motivo";
	$result = $db->sql_query($sql) or die("Error");
	if ($db->sql_numrows($result) > 0)
	{
	  while(list($motivo) = $db->sql_fetchrow($result)){
		  $tit_motivo=" Con motivo \"$motivo\".";
		  }
	}
}

if ($fecha_ini)
{
  $titulo .= " desde $fecha_ini";
  $fecha_ini = date_reverse($fecha_ini);
  $and_fecha .= " AND timestamp>'$fecha_ini 00:00:00'";
}
if ($fecha_fin)
{
  $titulo .= " hasta $fecha_fin";
  $fecha_fin = date_reverse($fecha_fin);
  $and_fecha .= " AND timestamp<'$fecha_fin 23:59:59'";
}

class PDF extends FPDF
{

    //CABECERA DE PAGINA
    function Header()
    {
	$this->SetFont('Arial','B',15);
	$this->Cell(25);
	$this->Cell(0,17, "Motivos de Cancelaciones de Ventas", 0, 1, 'C');
	$this->Cell(25);
	$this->Ln(8);
    }

    //Pie de página
    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
	$this->Cell(0,10,'Página '.$this->PageNo().'/{nb}',0,0,'C');
    }
}
$pdf=new PDF();

//FORMATO DEL DOCUMENTO PDF
$pdf->AliasNbPages(); //para el {nb} del footer
$border = 1;
$font_type_tit='Arial';
$font_type_cont='Arial';
$height_tit = 6;
$height_cont = 5;
$font_size_tit = 9;
$font_size_cont = 8;
$width=10;
$width1=15;
$width2=62;
$width3=100;

//SUBTITULO
$pdf->AddPage();
$pdf->SetFont('Arial','B',10);
$pdf->Cell($width);
$pdf->MultiCell(180,6,"Motivos de cancelaciones de ventas por vendedor $titulo.$tit_motivo",0,'L');
if($_site_name != '')
{
    $logo = "../img/logo/".$_site_name.".png";
    $pdf->Image($logo, 20, 10, 18);
}
//TITULO DE LAS TABLAS
$pdf->SetFont($font_type_tit,'B', $font_size_tit);
$pdf->SetFillColor(150,150,150);
$pdf->Cell($width);
$pdf->Cell($width1,$height_tit,'Id dis.',$border,0,'L',1);
$pdf->Cell($width2,$height_tit,'Nombre vendedor',$border,0,'L',1);
$pdf->Cell($width3,$height_tit,'Motivos de cancelaciones',$border,1,'L',1);


///Cuantas ventas hay concretadas por vendedor/////////////////////////////////////////////////

$sql = "SELECT u.gid, u.name, v.motivo FROM `crm_prospectos_cancelaciones` AS v, users AS u where v.uid=u.uid 
 $and_fecha $and_motivo ";
//die($sql);

$result = $db->sql_query($sql) or die("Error");
if ($db->sql_numrows($result) > 0)
{
  while(list($id_concesionaria, $nombre_vendedor, $motivo_ventas_canceladas) = $db->sql_fetchrow($result)){
	  $pdf->SetFont($font_type_cont,'', $font_size_cont);
	  $pdf->Cell($width);
	  $pdf->Cell($width1, $height_cont,$id_concesionaria, $border, 0, 'R');
	  $pdf->Cell($width2, $height_cont,$nombre_vendedor, $border, 0, 'L');
	  $pdf->MultiCell($width3, $height_cont,$motivo_ventas_canceladas, $border, 'L');
	  }
}
$pdf->Output();
die();
?>