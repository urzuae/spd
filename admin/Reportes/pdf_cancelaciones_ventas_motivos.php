<?
global $db;
$sql = "select motivo, motivo_id from `crm_prospectos_cancelaciones_motivos` order by motivo_id asc";
$result = $db->sql_query($sql) or die("Error");
$select_motivos .= "<select name='motivo'>\n";
$select_motivos .= "<option value=\"\">Todas</option>";
while (list($nombre, $origen_id2) = $db->sql_fetchrow($result))
{
        if ($origen_id2 == $origen_id) $sel = " SELECTED";
        else $sel = "";
        $select_motivos .= "<option value=\"$origen_id2\" $sel>$nombre</option>";
}
$select_motivos .= "</select>";
?>