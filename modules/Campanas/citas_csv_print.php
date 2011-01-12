<?
  if (!defined('_IN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}
$_theme = "";
$_css = $_themedir."/style.css";
global $db, $sep;
if (!$sep) $sep = ";";
$fp = fopen('citas_print.csv','w');
fputcsv($fp, array('id', 'año','mes','dia','hora','tel casa','tel oficina', 'ext', 'celular', 'referencia','nombre de referencia', 'nombre', 'sexo','año de nacimiento','mes','dia','compañia','afore','tiempo con afore','edad','rfc','lugar de cita','dia de cita','mes','año','hora','domicilio','colonia','cp','poblacion','ciudad','estado','referencia'), "$sep", "\"");
$result = $db->sql_query("SELECT * FROM crm_citas");
while ($row = $db->sql_fetchrow($result))
{
    $line = array();
    list(
         $contrato_id,
         $campana_id,
         $contacto_id,
         $uid,
         $nombre,
         $apellido_paterno,
         $apellido_materno,
         $sexo,
         $nacionalidad,
         $compania,
         $cargo,
         $tel_casa,
         $tel_oficina,
         $tel_oficina_ext,
         $tel_movil,
         $tel_otro,
         $email,
         $nombre_ref,
         $domicilio,
         $domicilio_ext,
         $domicilio_int,
         $colonia,
         $cp,
         $poblacion,
         $entidad_id,
         $rfc,
         $fecha_de_nacimiento,
         $nombre_empresa,
         $domicilio_recoleccion,
         $domicilio_recoleccion_ext,
         $domicilio_recoleccion_int,
         $colonia_recoleccion,
         $poblacion_recoleccion,
         $ciudad_recoleccion,
         $cp_recoleccion,
         $entidad_id_recoleccion,
         $referencia_recoleccion,
         $cita,
         $lugar_cita,
         $afore,
         $afore_tiempo,
         $timestamp
        ) = $row;
    array_push($line, $contrato_id);

    list($fecha_ts, $hora) = explode(" ", $timestamp); //timestamp
    list($y, $m, $d) = explode("-", $fecha_ts);
    list($hh, $mm, $ss) = explode(":", $hora);
    array_push($line, $y);
    array_push($line, $m);
    array_push($line, $d);
    array_push($line, "$hh:$mm");
//     array_push($line, $mm);
    array_push($line, $tel_casa);
    array_push($line, $tel_oficina);
    array_push($line, $tel_oficina_ext);
    array_push($line, $tel_movil);
    array_push($line, $tel_otro);
    array_push($line, $nombre_ref);
    array_push($line, "$nombre $apellido_paterno $apellido_materno");
    array_push($line, $sexo?"F":"M");
    list($y, $m, $d) = explode("-", $fecha_de_nacimiento);
    array_push($line, $y);
    array_push($line, $m);
    array_push($line, $d);
    array_push($line, $compania);
    array_push($line, $afore);
    array_push($line, $afore_tiempo);
    array_push($line, date("Y") - $y);//edad
    array_push($line, $rfc);
    switch($lugar_cita)
    {
        case "TR": $lugar_cita2 = "01";break;
        case "CA": $lugar_cita2 = "02";break;
        default: $lugar_cita2 = "03";
    }
    array_push($line, $lugar_cita);
    list($fecha_ts, $hora) = explode(" ", $cita); //timestamp
    list($y, $m, $d) = explode("-", $fecha_ts);
    list($hh, $mm, $ss) = explode(":", $hora);
    array_push($line, $d);
    array_push($line, $m);
    array_push($line, $y);
    array_push($line, "$hh:$mm");
//     array_push($line, $mm);
    array_push($line, "$domicilio_recoleccion $domicilio_recoleccion_ext $domicilio_recoleccion_int");
    array_push($line, $colonia_recoleccion);
    array_push($line, $cp_recoleccion);
    array_push($line, $poblacion_recoleccion);
    array_push($line, $ciudad_recoleccion);

//     include("$_includesdir/select.php");
//     global $_entidades_federativas;
    $_entidades_federativas = array("Aguascalientes",
                                "Baja California Norte",
                                "Baja California Sur",
                                "Campeche",
                                "Chiapas",
                                "Chihuahua",
                                "Coahuila",
                                "Colima",
                                "Distrito Federal",
                                "Durango",
                                "Guanajuato",
                                "Guerrero",
                                "Hidalgo",
                                "Jalisco",
                                "Estado de México",
                                "Michoacán de Ocampo",
                                "Morelos",
                                "Nayarit",
                                "Nuevo León",
                                "Oaxaca",
                                "Puebla",
                                "Querétaro",
                                "Quintana Roo",
                                "San Luis Potosí",
                                "Sinaloa",
                                "Sonora",
                                "Tabasco",
                                "Tamaulipas",
                                "Tlaxcala",
                                "Veracruz",
                                "Yucatán",
                                "Zacatecas");
    $ef = array();
    foreach ($_entidades_federativas AS $e) array_push($ef, strtoupper($e));
    $entidad_id_recoleccion2 = array_search($entidad_id_recoleccion, $ef) + 1;
    array_push($line,$entidad_id_recoleccion2);
    array_push($line,$referencia_recoleccion);
    fputcsv($fp, $line, "$sep", "\"");
}
fclose($fp);
chmod('citas_print.csv', 0666);
header("location: citas_print.csv");
?>
