<?
if (!defined('_IN_MAIN_INDEX')) {
  die ("No puedes acceder directamente a este archivo...");
}

global $db, $uid;

if($_REQUEST["fecha_ini"] != "" and $_REQUEST["fecha_fin"] != "")
    $rango_fechas = " and date_format(a.timestamp,'%Y-%m-%d') between '".$_REQUEST["fecha_ini"]."' and '".$_REQUEST["fecha_fin"]."'";
elseif($_REQUEST["fecha_ini"] != "")
    $rango_fechas = " and date_format(a.timestamp,'%Y-%m-%d') >= '".$_REQUEST["fecha_ini"]."'";
elseif($_REQUEST["fecha_fin"] != "")
    $rango_fechas = " and date_format(a.timestamp,'%Y-%m-%d') <= '".$_REQUEST["fecha_fin"]."'";

$sql  = "SELECT gid, super FROM users WHERE uid='$uid'";
$result = $db->sql_query($sql) or die("Error");
list($gid, $super) = $db->sql_fetchrow($result);
$gid = sprintf("%04d", $gid);
if ($super > 6){
  	$_html = "<h1>Usted no es un Gerente</h1>";
} 
else {
	$sql  = "SELECT uid, user FROM users WHERE gid='$gid' AND super = '8'";
	$result = $db->sql_query($sql) or die("Error2");
	while (list($uid, $user) = $db->sql_fetchrow($result)){	
		if($class == "row1")
			$class = "row2";
		else
			$class = "row1";		
		$sql2 = "SELECT COUNT(log_id) FROM crm_contactos_asignacion_log a WHERE uid = '$uid' $rango_fechas";
		$result2 = $db->sql_query($sql2) or die("Error2");
		list($cant_prospectos) = $db->sql_fetchrow($result2);
		$_html .= "<tr class=\"$class\">";
		$_html .= "<td>$uid</td>";
		$_html .= "<td>$user</td>";
		$_html .= "<td align=\"center\">$cant_prospectos</td>";
		$_html .= "</tr>";
	}
}

?>
