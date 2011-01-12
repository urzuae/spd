<?
  if (!defined('_IN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}
$_theme = "";

$_css = $_themedir."/style.css";
global $_site_title, $db, $submit, $how_many, $from, $campana_id, $contacto_id, $campana_id, $uid,
                    $nombre, $apellido_paterno, $apellido_materno,
                    $sexo, $nacionalidad,
                    $rfc,
                    $dia, $mes, $ano, //$fecha_de_nacimiento,
                    $edo_de_nac,
                    $domicilio,$domicilio_ext, $domicilio_int, $colonia,
                    $cp, $poblacion,
                    $entidad_id,
                    $tel_casa, $tel_oficina,
                    $tel_movil, $tel_otro,
                    $email, $nombre_ref,

                    $nombre_empresa,

                    $domicilio_recoleccion,$domicilio_recoleccion_ext, $domicilio_recoleccion_int,
                    $colonia_recoleccion,
                    $poblacion_recoleccion,
                    $ciudad_recoleccion,
                    $cp_recoleccion,
                    $entidad_id_recoleccion,
                    $referencia_recoleccion,
                    $cita,
                    $lugar_cita,
                    $afore,
                    $afore_tiempo
                    ;

list($fecha_cita, $hora_cita) = explode(" ", $cita);
list($dd, $mm, $aaaa) = explode("-", $fecha_cita);
$cita = "$aaaa-$mm-$dd $hora_cita";
if (!$submit)
{
    //OBTENEMOS DATOS DE DB
    //datos de la persona
    $sql = "SELECT 
                    nombre, apellido_paterno, apellido_materno,
                    sexo,
                    rfc,
                    fecha_de_nacimiento,
                    domicilio, colonia,
                    cp, poblacion,
                    entidad_id,
                    tel_casa, tel_oficina,
                    tel_movil, tel_otro,
                    email
                FROM crm_contactos WHERE contacto_id='$contacto_id' LIMIT 1";
    $result = $db->sql_query($sql) or die("Error al consultar datos del contacto".print_r($db->sql_error()));
    list(
                    $nombre, $apellido_paterno, $apellido_materno,
                    $sexo,
                    $rfc,
                    $fecha_de_nacimiento,
                    $domicilio, $colonia,
                    $cp, $poblacion,
                    $entidad_id,
                    $tel_casa, $tel_oficina, $tel_oficina_ext,
                    $tel_movil, $tel_otro,
                    $email
    ) = htmlize($db->sql_fetchrow($result));
    list($ano, $mes, $dia) = explode("-", $fecha_de_nacimiento);
}
else //guardar
{
    list($fecha_cita, $hora_cita) = explode(" ", $cita);
    $sql = "SELECT uid FROM crm_citas WHERE cita LIKE '$fecha_cita %'";
    $result = $db->sql_query($sql) or die("Error".print_r($db->sql_error()));
    if ($db->sql_numrows($result) >= 150) die("<html><body onload=\"alert('No se puede citar ese día. Porfavor presione ALT + <-');history.go(-1);\"></body></html>");
    $sql = "INSERT INTO crm_citas
    (
                    campana_id, contacto_id, uid,
                    nombre, apellido_paterno, apellido_materno,
                    sexo, nacionalidad,
                    rfc,
                    fecha_de_nacimiento,
                    domicilio, domicilio_ext, domicilio_int, colonia,
                    cp, poblacion,
                    entidad_id,
                    tel_casa, tel_movil,
                    tel_oficina, tel_oficina_ext, tel_otro,
                    email, nombre_ref,
                    
                    nombre_empresa,
                    
                    domicilio_recoleccion, domicilio_recoleccion_ext, domicilio_recoleccion_int,
                    colonia_recoleccion,
                    poblacion_recoleccion,
                    ciudad_recoleccion,
                    cp_recoleccion,
                    entidad_id_recoleccion,
                    referencia_recoleccion,
                    cita,
                    lugar_cita,
                    afore,
                    afore_tiempo
                    
    ) VALUES (".strtoupper("
                    '$campana_id', '$contacto_id', '$uid',
                    '$nombre', '$apellido_paterno', '$apellido_materno',
                    '$sexo', '$nacionalidad',
                    '$rfc',
                    '$ano-$mes-$dia', 
                    '$domicilio', '$domicilio_ext', '$domicilio_int', '$colonia',
                    '$cp', '$poblacion',
                    '$entidad_id',
                    '$tel_casa', '$tel_movil',
                    '$tel_oficina', '$tel_oficina_ext', '$tel_otro',
                    '$email', '$nombre_ref',
                    
                    '$nombre_empresa',
                    
                    '$domicilio_recoleccion', '$domicilio_recoleccion_ext', '$domicilio_recoleccion_int',
                    '$colonia_recoleccion',
                    '$poblacion_recoleccion',
                    '$ciudad_recoleccion',
                    '$cp_recoleccion',
                    '$entidad_id_recoleccion',
                    '$referencia_recoleccion',
                    '$cita',
                    '$lugar_cita',
                    '$afore',
                    '$afore_tiempo'
    )
    ");
    
    $db->sql_query($sql) or die("Error al guardar contrato.<br>$sql<br>".print_r($db->sql_error()));
    die("<html><body onload=\"window.close();\"></body></html>");
// die("$sql<br><br>".$db->sql_nextid());
}
include("$_includesdir/select.php");

$select_entidades = select_entidades_federativas($entidad_id, 29);
$select_dia = select_dia($dia);
$select_mes = select_mes($mes);
$select_ano = select_ano($ano);
global $_entidades_federativas;

$select_sexo = select_sexo($sexo);
global $_dias, $_meses, $_anos;
$select_dia_adicional1 = select_dia_extra("dia_adicional1");
$select_mes_adicional1 = select_mes_extra("mes_adicional1");
$select_ano_adicional1 = select_array("ano_adicional1", $_anos);

$select_entidades_recoleccion = select_array("entidad_id_recoleccion", $_entidades_federativas);
$select_lugar_cita = select_array("lugar_cita", array("Trabajo", "Casa", "Otro"));
$select_afore_tiempo = select_array("afore_tiempo", array("0 a 1 mes","1 a 6 meses","6 a 12 meses","mas de 12 meses"));

$_site_title = "Cita";

?>
