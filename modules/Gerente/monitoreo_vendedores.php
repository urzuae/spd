<?
  if (!defined('_IN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}
global $db, $uid, $how_many, $from, $orderby, $del, $rsort;
$kpi_contactos = array();
$window_opc = "'llamada','location=no,resizable=yes,scrollbars=yes,navigation=no,titlebar=no,directories=no,width=800,height=750,left=0,top=0,alwaysraised=yes'";
$how_many = 25;
if ($from < 1 || !$from) $from = 0;
if (!$orderby) $orderby = "nombre";

$result = $db->sql_query("SELECT gid FROM users WHERE uid='$uid' LIMIT 1") or die("Error en grupo ".print_r($db->sql_error()));
list($gid) = $db->sql_fetchrow($result);
$campanas = array();
//buscar las campanas a las que tengo acceso
$sql = "SELECT c.campana_id, nombre FROM crm_campanas AS c, crm_campanas_groups  AS g WHERE c.campana_id=g.campana_id AND gid='$gid' ORDER BY c.campana_id LIMIT $from, $how_many";
$result = $db->sql_query($sql) or die("Error al consultar campañas ".print_r($db->sql_error()));
$columnas_tabla='';
$letrero='<p>';
while (list($campana_id, $name) = htmlize($db->sql_fetchrow($result)))
{
	$campanas[$campana_id] = $name;
        $columnas_tabla.="<td>".strtoupper(substr($name,8,3))."</td>";
        $letrero.=strtoupper(substr($name,8,3))."&nbsp;&nbsp;&nbsp;".$name."<br>";
}
$letrero.='</p>';
$users = array();	
//obtenemos a los usuarios del grupo
$sql = "SELECT uid, name FROM users WHERE gid='$gid' AND super='8'";
$result = $db->sql_query($sql) or die("Error al consultar campañas ".print_r($db->sql_error()));
while (list($c_uid, $c_name) = htmlize($db->sql_fetchrow($result)))
	$users[$c_uid] = $c_name;

foreach ($users AS $c_uid=>$name)
{	
	$total = 0;
	//calcular contactos en ciclo
	$contactos[$c_uid] = array();
	foreach ($campanas AS $campana_id=>$name)
	{
		//a cada una calcularle sus contactos
		$sql = "SELECT count(id) FROM crm_campanas_llamadas AS l, crm_contactos AS c WHERE c.contacto_id=l.contacto_id AND c.uid='$c_uid' AND campana_id='$campana_id'"; 
		$r2 = $db->sql_query($sql) or die($sql);
		list($cuantos) = $db->sql_fetchrow($r2);
		$contactos[$c_uid][$campana_id] = $cuantos;
		$total += $cuantos;
	}
	$sql = sprintf("SELECT COUNT(contacto_id) FROM crm_contactos WHERE uid = '%s' AND fecha_importado > '%s 00:00:00'",$c_uid,date("Y-m-d"));
	$r_p2 = $db->sql_query($sql) or die($sql);
	//echo $sql."<br>";
	while(list($cuenta_hoy)=$db->sql_fetchrow($r_p2)){
		$kpi_contactos[$c_uid] = $cuenta_hoy;
	}
  $campanas_total[$c_uid] = $total;
}
if ($rsort)
  $nrsort = 0;
else
  $nrsort = 1;
$tabla_campanas .= "
                   ".$letrero."
                   <table border=\"0\" style=\"width:100%\"   id=\"tabla_contactos\"  class=\"tablesorter\">\n
                   <thead><tr>"
                    ."<th rowspan=\"2\">Nombre del vendedor</th>".$columnas_tabla.""
                    ."<th rowspan=\"2\">Total</th>"
/*                    ."<th rowspan=\"2\">Prom t asign - ini</th>"
                    ."<th rowspan=\"2\">MAXt Asig-Ini</th>"
                    ."<th rowspan=\"2\">Prom t ini - cierre</th>"
                    ."<th rowspan=\"2\">MAXt Ini-Cierre</th>"*/
                    ."<th rowspan=\"2\">Retrasos acumulados</th>"
                    ."<th rowspan=\"2\">Núm. Retrasos </th>"
                    ."<th rowspan=\"2\">Núm. Ventas</th>"
                    ."<th rowspan=\"2\">Prospectos capturados al día</th>"
                    ."</tr>"
                    ."</thead>\n";

//ordenar la tabla por los datos que solicitan
switch($orderby)
{
  case "total": $array_para_ordenar = &$campanas_total; 
            break;
  case "nombre": $array_para_ordenar = &$users; 
            break;
  case "kpi_t_asign_ini_s": $array_para_ordenar = &$kpi_t_asign_ini_s;
                    break; //por referencia para evitar que copie
  case "kpi_max_t_asign_ini": $array_para_ordenar = &$kpi_max_t_asign_ini;
                    break; //por referencia para evitar que copie   
  case "kpi_t_ini_cierre_s": $array_para_ordenar = &$kpi_t_ini_cierre_s;
            break;
  case "kpi_max_t_ini_cierre": $array_para_ordenar = &$kpi_max_t_ini_cierre;
            break;
  case "kpi_retrasos": $array_para_ordenar = &$kpi_retrasos;
                    break;
  case "kpi_contactos": $array_para_ordenar = &$kpi_contactos;
                    break;
  default: $array_para_ordenar = &$users;
}
if (!$rsort)
  asort($array_para_ordenar); //ordenar por valor y conservar asociación de keys
else
  arsort($array_para_ordenar); //ordenar por valor  en orden inverso y conservar asociación de keys
foreach ($array_para_ordenar AS $key=>$value)
{
  $ordered_users[] = $key;//echo $key."->$value<br>";
}

  
  
//foreach ($users AS $c_uid=>$uname)
foreach ($array_para_ordenar AS $c_uid=>$x)
{
  $uname = $users[$c_uid];
	$tabla_campanas .= "<tr class=\"row".(($c++%2)+1)."\">";
	$tabla_campanas .= "<td><a href=\"index.php?_module=$_module&_op=monitoreo_vendedor&uid=$c_uid\">$uname</a></td>";
	$total_contactos = 0;
	foreach ($campanas AS $campana_id=>$name)
	{
		$tabla_campanas .= "<td>".($contactos[$c_uid][$campana_id])."</td>";
		$total_contactos += $contactos[$c_uid][$campana_id];
		$total_campanas[$campana_id] += $contactos[$c_uid][$campana_id];
	}
	//consultar que está en la db de los ultimos kpis
	$sql = "SELECT prom_t_asig_ini,max_t_asig_ini,prom_t_ini_cierre,max_t_ini_cierre,retrasos_acumulados, retrasos, ventas FROM crm_monitoreo_vendedores WHERE uid='$c_uid'";
	$r2 = $db->sql_query($sql) or die($sql);
	list($prom_t_asig_ini,$max_t_asig_ini,$prom_t_ini_cierre,$max_t_ini_cierre,$retrasos_acumulados, $retrasos, $ventas) = $db->sql_fetchrow($r2);
	$tabla_campanas .= "<td>$total_contactos</td>";
	/*$tabla_campanas .= "<td id=\"kpi_t_asign_ini_s_$c_uid\">$prom_t_asig_ini hr</td>";
	$tabla_campanas .= "<td id=\"kpi_max_t_asign_ini_$c_uid\">$max_t_asig_ini hr</td>";
        $tabla_campanas .= "<td id=\"kpi_t_ini_cierre_s_$c_uid\">$prom_t_ini_cierre hr</td>";
	$tabla_campanas .= "<td id=\"kpi_max_t_ini_cierre_$c_uid\">$max_t_ini_cierre hr</td>";*/
	$tabla_campanas .= "<td id=\"kpi_retrasos_$c_uid\">$retrasos_acumulados hr</td>";
	$tabla_campanas .= "<td id=\"kpi_retrasos_cuantos_$c_uid\">$retrasos</td>";
	$tabla_campanas .= "<td id=\"kpi_ventas_$c_uid\">$ventas</td>";
	$tabla_campanas .= "<td id=\"kpi_contactos_$c_uid\">{$kpi_contactos[$c_uid]}</td>";
	$tabla_campanas .= "</tr>";
	$ventas_total += $ventas;
	$retrasos_total += $retrasos;
	//aumentar los totales
	$prom_t_asig_ini_total += $prom_t_asig_ini;
	$max_t_asig_ini_total += $max_t_asig_ini;
	$prom_t_ini_cierre_total += $prom_t_ini_cierre;
	$max_t_ini_cierre_total += $max_t_ini_cierre;
	$retrasos_acumulados_total += $retrasos_acumulados;
	$kpi_contactos_total += $kpi_contactos[$c_uid];
}

$total_users = count($users);
if ($total_users)
{
	$prom_t_asig_ini_total = round($prom_t_asig_ini_total / $total_users);
	$prom_t_ini_cierre_total = round($prom_t_ini_cierre_total / $total_users);
}

if ($total_users)
{
	$tabla_campanas .= "</tbody><tfoot>";
	$tabla_campanas .= "<tr class=\"row".(($c++%2)+1)."\" style=\"font-weight:bold;\"><td  align=\"right\"><b>Total</b></td>";
	foreach ($campanas AS $campana_id=>$name)
	{
		$tabla_campanas .= "<td>".$total_campanas[$campana_id]."</td>";
		$total_total += $total_campanas[$campana_id];
	}
	
	$tabla_campanas .= "<td>$total_total</td>";
	/*$tabla_campanas .= "<td id=\"total_kpi_t_asign_ini_s\">$prom_t_asig_ini_total hr</td>";
	$tabla_campanas .= "<td id=\"total_kpi_max_t_asig_ini\">$max_t_asig_ini_total hr</td>";
	$tabla_campanas .= "<td id=\"total_kpi_t_ini_cierre_s\">$prom_t_ini_cierre_total hr</td>";
	$tabla_campanas .= "<td id=\"total_kpi_max_t_ini_cierre\">$max_t_ini_cierre_total hr</td>";*/
	$tabla_campanas .= "<td id=\"total_kpi_retrasos\">$retrasos_acumulados_total hr</td>";
	$tabla_campanas .= "<td id=\"total_retrasos_cuantos\">$retrasos_total</td>";
	$tabla_campanas .= "<td id=\"total_ventas\">$ventas_total</td>";
	$tabla_campanas .= "<td id=\"total_kpi_contactos\">$kpi_contactos_total</td>";
	$tabla_campanas .= "</tr>\n";
}
$tabla_campanas .= "</tfoot></table>\n";

?>