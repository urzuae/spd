<?
  if (!defined('_IN_ADMIN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}

global $db, $queja_id;
$_theme = "";
if (!isset($queja_id)) header("location: index.php?_module=$_module");

require("$_includesdir/fpdf.php");
define('FPDF_FONTPATH',"$_includesdir/fonts/");

class PDF extends FPDF
{
//Cabecera de página
function Header()
{
    global $nombre_campana;
    global $title;
    //Logo
    $this->Image("../img/logo.png", 28, 10, 15, 15);
    //Arial bold 15
    $this->SetFont('Arial','B',8);
    //recuadro de vw
    $this->Cell(50, 11, "", "LTR");
    $this->Cell(100, 11, "Nivel del Documento: Formato", 1);
    $this->Cell(40, 11, "Código MOS: GS800", 1, 1);
    $this->SetFont('Arial','B',6);
    $this->Cell(50, 11, "Automotriz Universidad Copilco S.A. de C.V.", "LBR", 0, "C");
    $this->SetFont('Arial','B',8);
    $this->Cell(100, 11, "Titulo: Registro de quejas de clientes y seguimiento", 1);
    $this->Cell(40, 11, "Gerencia: SERVICIO", 1, 1);

    //Salto de línea
//     $this->Ln(5);
}

//Pie de página
function Footer()
{
    $margin = 10;
    //Posición: a 1,5 cm del final
    $this->SetY($margin - 8);
    //Arial italic 8
    $this->SetFont('Arial','I',8);
    //Número de página
//     $this->Cell(0,10,'Página '.$this->PageNo().'/{nb}',0,0,'C');

    $this->SetY(-$margin); //a 1 mm del final
    $yf = $this->GetY();
    $this->SetX(-$margin);
    $xf = $this->GetX();
    $this->Line($margin, $margin, $margin, $yf);
    $this->Line($xf,$margin, $xf, $yf);
    $this->Line($margin, $yf, $xf, $yf);
}

}




$sql = "SELECT fecha, contacto_id, status_id, uid, medio_id, motivo, tipo_de_reclamacion, orden_de_servicio, comentario FROM crm_quejas WHERE queja_id='$queja_id'";
$result = $db->sql_query($sql) or die("Error");
list($fecha, $contacto_id, $status_id, $uid, $medio_id, $motivo, $tipo_de_reclamacion, $orden_de_servicio, $comentario) = $db->sql_fetchrow($result);
if ($comentario) $comentario = "COMENTARIO: ";
else $comentario = "";
$sql = "SELECT nombre FROM crm_quejas_medio WHERE medio_id='$medio_id'";
$result2 = $db->sql_query($sql) or die("Error consultando 2");
list($medio) = ($db->sql_fetchrow($result2));
$sql = "SELECT nombre FROM crm_quejas_status WHERE status_id='$status_id'";
$result2 = $db->sql_query($sql) or die("Error consultando 2");
list($status) = ($db->sql_fetchrow($result2));
$sql = "SELECT nombre, apellido_paterno, apellido_materno,
               domicilio, colonia,
               tel_casa, tel_oficina, tel_movil, tel_otro
        FROM crm_contactos WHERE contacto_id='$contacto_id'";
$result2 = $db->sql_query($sql) or die("Error consultando 2");
list($nombre, $apellido_paterno, $apellido_materno,
     $domicilio, $colonia,
     $tel_casa, $tel_oficina, $tel_movil, $tel_otro
     ) = ($db->sql_fetchrow($result2));
$sql = "SELECT name FROM users WHERE uid='$uid'";
$result2 = $db->sql_query($sql) or die("Error consultando 3");
list($nombre_evalua) = ($db->sql_fetchrow($result2));

//obtener los datos de la orden de servicio
$sql = "SELECT fecha, uid, tipo, chasis, modelo FROM crm_clientes_servicios WHERE orden='$orden_de_servicio'";
$result2 = $db->sql_query($sql) or die("Error consultando 4");
if ($db->sql_numrows($result2) > 0)
{
  list($fecha_servicio, $uid_asesor, $tipo, $chasis, $modelo) = ($db->sql_fetchrow($result2));
  $sql = "SELECT name FROM users WHERE uid='$uid_asesor'";
  $result2 = $db->sql_query($sql) or die("Error consultando 5");
  list($nombre_asesor) = ($db->sql_fetchrow($result2));
  
}

list($fecha, $hora) = explode(" ", $fecha);
$fecha = date_reverse($fecha);

$font_size = 8;
$m = 10;//margen
$h = 6; //altura

$pdf=new PDF();
$pdf->AliasNbPages(); //para el {nb} del footer

$pdf->AddPage();
$pdf->SetFont('Arial','', $font_size);
$pdf->SetFillColor(192, 192, 192);

///DATOS CLIENTE y QUEJA/////////////////////////////////
$pdf->ln(2);
$pdf->Cell(10, $h, "Folio:");
$pdf->Cell(15, $h, "$queja_id", 1, 0, "C");
$pdf->Cell(50, $h, "Fecha de la queja y hora / del repaso:");
$pdf->Cell(20, $h, "$hora", 1, 0, "C");
$pdf->Cell(20, $h, "", 1);
$pdf->Cell(20, $h, "$fecha", 1, 0, "C");
$pdf->Cell(10, $h, "Evalua:");
$pdf->Cell(0, $h, "$nombre_evalua", 0, 1);

$fecha = date_reverse($fecha);
$sql = "SELECT resultado_id, fecha FROM crm_encuestas_resultados WHERE fecha LIKE '$fecha %'";
$result2 = $db->sql_query($sql) or die("Error consultando 3");
list($resultado_id, $fecha_encuesta) = ($db->sql_fetchrow($result2));
list($fecha_encuesta, $hora_encuesta) = explode(" ", $fecha_encuesta);
$fecha_encuesta = date_reverse($fecha_encuesta);

$pdf->Cell(20, $h, "Encuesta:");
$pdf->Cell(15, $h, "$resultado_id", 1, 0, "C");
$pdf->Cell(40, $h, "Hora y fecha de la encuesta:");
$pdf->Cell(20, $h, "$hora_encuesta", 1, 0, "C");
$pdf->Cell(20, $h, "", 1);
$pdf->Cell(20, $h, "$fecha_encuesta", 1, 1, "C");

//siguiente linea
$pdf->Cell(15, $h, "CLIENTE:");
$pdf->SetFont('','B');
$pdf->Cell(70, $h, "$nombre $apellido_paterno $apellido_materno", 0, 0);
$pdf->SetFont('','');
$pdf->Cell(15, $h, "Dirección:");
$pdf->Cell(0, $h, "$domicilio, $colonia", 0, 1);
//ln
$pdf->Cell(20, $h, "Tel. Particular:");
$pdf->Cell(30, $h, "$tel_casa", 0, 0, "");
$pdf->Cell(20, $h, " Tel. Trabajo:");
$pdf->Cell(30, $h, "$tel_oficina", 0, 0, "");
$pdf->Cell(20, $h, " Tel. Celular:");
$pdf->Cell(30, $h, "$tel_movil", 0, 0, "");
$pdf->Cell(15, $h, " Tel. Otro:");
$pdf->Cell(0, $h, "$tel_otro", 0, 1, "");
//ln
$pdf->Cell(25, $h, "Forma de la queja:");
$pdf->Cell(20, $h, "$medio", 0, 1);
//ln
///MOTIVO/////////////////////////////////////
// $pdf->Multicell(0, $h, "Motivo de la queja:     $motivo", 1, "", 1);
// $pdf->Cell(0,0,"","B",1);//linea

///tipo de reclamacion///////////////////////////
$pdf->Multicell(0, $h, "Tipo de reclamación: $comentario    $tipo_de_reclamacion", 1, "", 1);

///Datos de la unidad////////////////////////////
$pdf->Cell(25, $h, "Tipo / modelo:");
$pdf->Cell(90, $h, "$tipo / $modelo", "B", 0, "");
$pdf->Cell(15, $h, "Chasis:");
$pdf->Cell(0, $h, "$chasis", "B", 1, "");

//ln
$pdf->Cell(30, $h, "Fecha de Servicio:");
$pdf->Cell(20, $h, date_reverse($fecha_servicio), "B", 0, "");
$pdf->Cell(27, $h, "Asesor de Servicios:");
$pdf->Cell(0, $h, "$nombre_asesor", "B", 1, "");
//ln
$pdf->ln(2);
$pdf->Cell(0,0,"","",1);//linea
//ln
///Servicio//////////////////////

$sql = "SELECT o.servicio_opcion_id, o.nombre FROM crm_quejas_servicio_opciones AS o WHERE 1 ORDER BY  o.servicio_opcion_id";
$result1 = $db->sql_query($sql) or die("Error 1 $sql");
// $pdf->Cell(20, $h, "SERVICIO:", "", 1, "L");

$pdf->Cell(0, $h, "", "B", 1, "L");
while (list($id_s, $nombre) = $db->sql_fetchrow($result1))
{
  $sql = "SELECT s.servicio_opcion_id FROM crm_quejas_servicio_opciones_seleccionadas AS s WHERE  queja_id='$queja_id' AND servicio_opcion_id='$id_s'";
  $result2 = $db->sql_query($sql) or die("Error 2");
  if ($db->sql_numrows($result2)) $tache = "X";
  else $tache = "";
  $pdf->Cell(5, $h, "$tache", 1, 0, "");
  $pdf->Cell(26, $h, "$nombre", "", 0, "L");

}
$pdf->SetFont('','b', "12");

$pdf->Cell(20, $h, "Orden: $orden_de_servicio", "L", 1, "L");
$pdf->SetFont('','',"$font_size");
// $pdf->Cell(0, $h, "", 0, 1, "");

//ln
$pdf->Cell(0, 0, "", 1, 1, "");
///LLENADO A MANO/////////////////////////////////////////////
//cuadro de 4 de alto
$pdf->Cell(0, $h, "Eliminación del defecto (tiempo de reacción máximo tres días): ", "LTRB", 1);
$pdf->Cell(0, $h, "", "LRT", 1);
$pdf->Cell(0, $h, "", "LRT", 1);
$pdf->Cell(0, $h, "", "LRT", 1);
$pdf->Cell(0, $h, "", "LBRT", 1);

$pdf->Cell(5, $h, "", 1, 0, "");
$pdf->Cell(25, $h, "Repaso", "", 0, "L");
$pdf->Cell(5, $h, "", 0, 0, "");
$pdf->Cell(5, $h, "$opcion1", 1, 0, "");

$pdf->Cell(25, $h, "Cortesía", "", 0, "L");
$pdf->Cell(5, $h, "", 0, 0, "");
$pdf->Cell(5, $h, "$opcion2", 1, 0, "");

$pdf->Cell(25, $h, "Abono", "", 0, "L");
$pdf->Cell(5, $h, "", 0, 0, "");
$pdf->Cell(5, $h, "$opcion3", 1, 0, "");

$pdf->Cell(25, $h, "Atención General", "", 0, "L");
$pdf->Cell(5, $h, "", 0, 0, "");
$pdf->Cell(5, $h, "$opcion4", 1, 0, "");

$pdf->Cell(25, $h, "Otros", "", 0, "L");
$pdf->Cell(5, $h, "", 0, 0, "");
$pdf->Cell(0, $h, "$opcion5", 0, 1, "");

$pdf->Cell(125, $h, "Control de calidad: ", "LTR", 0);
$pdf->Cell(10, $h, "Fecha: ", "T", 0);
$pdf->Cell(2, $h, " ", "T", 0);
$pdf->Cell(10, $h, " ", "1", 0);
$pdf->Cell(10, $h, " ", "1", 0);
$pdf->Cell(10, $h, " ", "1", 0);
$pdf->Cell(0, $h, " ", "T", 1);
$pdf->Cell(125, $h, "", "1", 0);

$pdf->Cell(0, $h, " ", "LR", 1);
$pdf->Cell(125, $h, "", "1", 0);
$pdf->Cell(0, $h, " ", "LR", 1);
$pdf->Cell(125, $h, "", "1", 0);
$pdf->Cell(0, $h, " ", "LR", 1);
$pdf->Cell(125, $h, "", "1", 0);
if ($orden_de_servicio) $gerente = "DE COLSA LLANTADA JOSE ANTONIO";
$pdf->Cell(0, $h, "$gerente", "LBRT", 1, 'C');

$pdf->Cell(90, $h, "Medidas para un aseguramiento de la calidad a largo plazo: ", "LTRB", 0);
$pdf->Cell(50, $h, "Imputable y firma de enterado: ", "1", 0);
$pdf->Cell(12, $h, "UT: ", "1", 0);
$pdf->Cell(0, $h, " ", "1", 1);

$pdf->Cell(90, $h, "", "LRB", 0);
$pdf->Cell(50, $h, "", "1", 0);
$pdf->Cell(12, $h, "REF: ", "1", 0);
$pdf->Cell(0, $h, " ", "1", 1);

$pdf->Cell(90, $h, "", "LRB", 0);
$pdf->Cell(50, $h, "", "1", 0);
$pdf->Cell(12, $h, "M.O.: ", "1", 0);
$pdf->Cell(0, $h, " ", "1", 1);

$pdf->Cell(90, $h, "", "LBR", 0);
$pdf->Cell(50, $h, "", "1", 0);
$pdf->Cell(12, $h, "TOTAL: ", "1", 0);
$pdf->Cell(0, $h, " ", "1", 1);

$pdf->Cell(90, $h, "", "LBR", 0);
$pdf->Cell(50, $h, "", "1", 0);
$pdf->Cell(12, $h, "Refa", "1", 0);
$pdf->Cell(0, $h, " ", "1", 1);


$pdf->Cell(0, $h, "Informe telefónico del Gerente de Servicio/Jefe de Taller/Asesor de Servicio: ", "LTR", 1, "", 1);
$pdf->Cell(40, $h, "de fecha: ", "L", 0, "", 1);
$pdf->Cell(18, $h, "realizado por: ", "", 0, "", 1);
$pdf->Cell(50, $h, "$nombre_evalua", "", 0, "", 1);
$pdf->Cell(25, $h, "cliente satisfecho: ", "", 0, "", 1);
$pdf->Cell(5, $h, "", 1, 0, "", 1);
$pdf->Cell(10, $h, "sí", "", 0, "", 1);
$pdf->Cell(5, $h, "", 1, 0, "", 1);
$pdf->Cell(10, $h, "no", "", 0, "", 1);
$pdf->Cell(0, $h, "", "", 1, "", 1);
$pdf->Cell(0, $h, "", "LBR", 1, "", 1);

$pdf->Cell(0, $h, "Información a la Dirección General: ", "", 1);
$pdf->Cell(0, $h, "", "", 1);
$pdf->Cell(60, $h, "", "", 0);
$pdf->Cell(80, $h, "Vo. Bo. Call Center", "T", 0, "C");
$pdf->Cell(0, $h, "", "", 1);

$pdf->Output();
?>