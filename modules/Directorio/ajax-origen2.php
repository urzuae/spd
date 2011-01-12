<?
if (!defined('_IN_MAIN_INDEX')) {
  die ("No puedes acceder directamente a este archivo...");
} 

global $db, $origen_id,$nav;
       
//inicializar cosas
$sql = "select origen_id, nombre from crm_contactos_origenes where origen_padre_id='$origen_id' and activo = 1 order by nombre asc";
$cs = $db->sql_query($sql);

if($nav == "ie")
	$select = "<select id=\"origen\" name=\"origen\">";
$select .= '<option value="">--- Seleccione ---</option>';
while(list($origen_id,$nombre) = $db->sql_fetchrow($cs)){
    $select .= "<option value=\"$origen_id\">$nombre</option>\n";
}
if($nav == "ie")
	$select .= "</select>";

header("Content-Type: text/html; charset=\"iso-8859-1\"',true");
die($select);
?>
