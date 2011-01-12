<? 
if (!defined('_IN_ADMIN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}

global $db, $del;

$array_graficas = array("Servicio Hojalatería y Pintura"=>"reporte_encuesta_servicios", "Ventas"=>"reporte_encuesta_ventas");
$select_graficas = "<select name=\"_op\">";
while ($g = current($array_graficas))
{
  if ($g == $grafica) $selected = " SELECTED";
  else $selected = "";
  $select_graficas .= "<option value=\"".($g)."\"$selected>".(key($array_graficas))."</option>\n";
  next($array_graficas);
}
$select_graficas .= "</select>";
?>