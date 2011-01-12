<?
if (!defined('_IN_ADMIN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}
require("$_includesdir/fpdf.php");
define('FPDF_FONTPATH',"$_includesdir/fonts/");
global $db, $fecha_ini, $fecha_fin,$_site_name;

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
	$this->Cell(0,17, "Cantidad de Ventas Concretadas por Vendedor", 0, 1, 'C');
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
$width=20;
$width1=35;
$width2=80;
$width3=35;
//SUBTITULO
$pdf->AddPage();
$pdf->SetFont('Arial','B',10);
$pdf->Cell($width);
$pdf->Cell(190,17,"Cantidad de de ventas concretadas por vendedor $titulo.",0,10);
if($_site_name != '')
{
    $logo="../img/logo/". $_site_name.".png";
    $pdf->Image($logo, 20, 10, 18);
}
//TITULO DE LAS TABLAS
$pdf->SetFont($font_type_tit,'B', $font_size_tit);
$pdf->SetFillColor(150,150,150);
$pdf->Cell($width1,$height_tit,'Id distribuidora',$border,0,'L',1);
$pdf->Cell($width2,$height_tit,'Nombre vendedor',$border,0,'L',1);
$pdf->Cell($width3,$height_tit,'Ventas concretadas',$border,1,'L',1);


///Cuantas ventas hay concretadas por vendedor/////////////////////////////////////////////////

$sql = "SELECT u.gid, u.name, count(v.uid) FROM `crm_prospectos_ventas` AS v, users AS u where v.uid=u.uid 
$and_fecha group by (v.uid)";

$result = $db->sql_query($sql) or die("Error");
if ($db->sql_numrows($result) > 0)
{
  while(list($id_concesionaria, $nombre_vendedor, $ventas_concretadas) = $db->sql_fetchrow($result)){
	  $pdf->SetFont($font_type_cont,'', $font_size_cont);
	  $pdf->Cell($width);
	  $pdf->Cell($width1, $height_cont,$id_concesionaria, $border, 0, 'R');
	  $pdf->Cell($width2, $height_cont,$nombre_vendedor, $border, 0, 'L');
	  $pdf->Cell($width3, $height_cont,$ventas_concretadas, $border, 1, 'R');
	  }
}
$pdf->Output();
die();
?>