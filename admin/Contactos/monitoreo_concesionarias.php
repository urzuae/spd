<?
  if (!defined('_IN_ADMIN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}
global $db, $uid, $how_many, $from, $orderby, $del;
$window_opc = "'llamada','location=no,resizable=yes,scrollbars=yes,navigation=no,titlebar=no,directories=no,width=800,height=750,left=0,top=0,alwaysraised=yes'";
$how_many = 25;
if ($from < 1 || !$from) $from = 0;
if (!$orderby) $orderby = "nombre";

// $result = $db->sql_query("SELECT gid FROM users WHERE uid='$uid' LIMIT 1") or die("Error en grupo ".print_r($db->sql_error()));
// list($gid) = $db->sql_fetchrow($result);

	
//obtenemos a los usuarios del grupo
$sql = "SELECT gid, gid FROM groups WHERE 1 ORDER BY gid";
$result = $db->sql_query($sql) or die("Error al consultar campañas ".print_r($db->sql_error()));
while (list($c_uid, $c_name) = htmlize($db->sql_fetchrow($result)))
	$groups[$c_uid] = $c_name;

foreach ($groups AS $c_uid=>$name)
{
	$total = 0;
	//calcular contactos en ciclo
	$contactos[$c_uid] = array();
  //las campañas 
  $campanas[$c_uid] = array();
  //buscar las campanas a las que tengo acceso
  $sql = "SELECT c.campana_id, c.nombre FROM crm_campanas AS c, crm_campanas_groups  AS g WHERE c.campana_id=g.campana_id AND gid='$c_uid' ORDER BY c.nombre";
  $result2 = $db->sql_query($sql) or die("Error al consultar campañas ".print_r($db->sql_error()));
  while (list($campana_id, $name) = htmlize($db->sql_fetchrow($result2)))
  {
    $campanas[$c_uid][$campana_id] = $name;
  }
	foreach ($campanas[$c_uid] AS $campana_id=>$name)
	{
		//a cada una calcularle sus contactos
		$sql = "SELECT (id) FROM crm_campanas_llamadas AS l, crm_contactos AS c WHERE c.contacto_id=l.contacto_id AND c.gid='$c_uid' AND l.campana_id='$campana_id'";
		$r2 = $db->sql_query($sql) or die($sql);
		$cuantos = $db->sql_numrows($r2);
//     echo $sql." ----- $cuantos<br>";    
		$contactos[$c_uid][$campana_id] = $cuantos;
		$total += $cuantos;
	}
	//quiero los kpis de todos los contactos de esta persona
	$retrasos = 0;
	$cuantos_retrasos = 0;
	$max_t_asign_ini = 0;
	$max_t_ini_cierre = 0;
	$t_asign_ini_s = 0;
	$t_ini_cierre_s = 0;
	$cuantos_t_asign_ini_s = 0;
	$cuantos_t_ini_cierre_s = 0;
	$sql = "SELECT (c.contacto_id), UNIX_TIMESTAMP(l.fecha_cita), l.status_id FROM crm_campanas_llamadas AS l, crm_contactos AS c WHERE c.contacto_id=l.contacto_id AND c.gid='$c_uid'";
	$result = $db->sql_query($sql) or die("Error al consultar campañas ".print_r($db->sql_error()));
//     $cuantos = $db->sql_numrows($result);
//     echo $sql." ----- $cuantos<br>";     
	while (list($c, $fecha_cita, $status_id) = htmlize($db->sql_fetchrow($result)))
	{
		$a[$c_uid] .= $c." ";
		//se necesitan datos para el promedio desde la asignación al inicio, desde el inicio al cierre
		//máximos de asg al inicio, y del inicio al cierre
		//los retrasos acumulados
		//obtener el primer y último contacto
		//si tiene algún contacto, no calcula el tiempo desde la asignación
		$sql = "SELECT UNIX_TIMESTAMP(fecha_importado) FROM crm_contactos WHERE contacto_id='$c'  LIMIT 1";
		$r3 = $db->sql_query($sql) or die($sql);
		list($ts_importado) = $db->sql_fetchrow($r3);//todos tienen esto
		$sql = "SELECT UNIX_TIMESTAMP(timestamp) FROM crm_contactos_asignacion_log WHERE contacto_id='$c' ORDER BY timestamp DESC LIMIT 1";
		$r3 = $db->sql_query($sql) or die($sql);
		list($ts_asignado) = $db->sql_fetchrow($r3);
		$sql = "SELECT UNIX_TIMESTAMP(timestamp) FROM crm_campanas_llamadas_log WHERE contacto_id='$c' ORDER BY timestamp DESC LIMIT 1";
		$r3 = $db->sql_query($sql) or die($sql);
		list($ts_ultimo_contacto) = $db->sql_fetchrow($r3);
		//max tiempos desde la asign al inicio
		if ($ts_asignado && $ts_importado)
		{
			$ts_asign_ini = $ts_asignado - $ts_importado; // 1000 - 800
			$t_asign_ini_s += $ts_asign_ini;
			$cuantos_t_asign_ini_s ++;
			if ($ts_asign_ini > $max_t_asign_ini) $max_t_asign_ini = $ts_asign_ini;
		}
		if ($ts_ultimo_contacto && $ts_asignado) //aqui marcamos que el último contacto es el cierre, pero probablemente sea otro 
		{
			$ts_ini_cierre = $ts_ultimo_contacto - $ts_asignado; // 1200 - 200
			$t_ini_cierre_s += $ts_ini_cierre;
			$cuantos_t_ini_cierre_s ++;
			if ($ts_ini_cierre > $max_t_ini_cierre) $max_t_ini_cierre = $ts_ini_cierre;//mayor
		}
		//retrasados acumulados
		$retraso =  time() - $fecha_cita;
		if ($status_id == -2 && $retraso > 0 )
		{
			$retrasos += $retraso;
			$cuantos_retrasos++;
		}
	}
	$kpi_retrasos[$c_uid] = round($retrasos/60/60); //sumatoria de horas de retraso
	$kpi_max_t_asign_ini[$c_uid] = round($max_t_asign_ini/60/60); //sumatoria de horas de retraso
	$kpi_max_t_ini_cierre[$c_uid] = round($max_t_ini_cierre/60/60); //sumatoria de horas de retraso
	if ($cuantos_t_asign_ini_s) $kpi_t_asign_ini_s[$c_uid] = round($t_asign_ini_s/$cuantos_t_asign_ini_s/60/60); ////promedio
	else $kpi_t_asign_ini_s[$c_uid] = "0";
	if ($cuantos_t_ini_cierre_s)$kpi_t_ini_cierre_s[$c_uid] = round($t_ini_cierre_s/$cuantos_t_ini_cierre_s/60/60); //promedio
	else $kpi_t_ini_cierre_s[$c_uid] = "0";

}

$tabla_campanas .= "<table border=\"0\" style=\"width:100%\">\n";
$tabla_campanas .= "<thead><tr>"
                    ."<td rowspan=\"2\"><a href=\"index.php?_module=$_module&_op=$_op&orderby=nombre\" style=\"color:#ffffff\">Concesionaria</a></td>"
                    ."<td colspan=\"8\">Ciclo</td>"
          ."<td rowspan=\"2\">Total</td>"
          ."<td rowspan=\"2\">Prom t asign - ini</td>"
          ."<td rowspan=\"2\">MAXt Asig-Ini</td>"
          ."<td rowspan=\"2\">Prom t ini - cierre</td>"
          ."<td rowspan=\"2\">MAXt Ini-Cierre</td>"
          ."<td rowspan=\"2\">Retrasos acumulados</td>"
                    ."</tr>"
                    ."<tr>"
                    ."<td>P</td>"
                    ."<td>C</td>"
                    ."<td>P</td>"
                    ."<td>D</td>"
                    ."<td>N</td>"
                    ."<td>C</td>"
                    ."<td>E</td>"
                    ."<td>S</td>"
                    ."</tr>"
                    ."</thead>\n";
foreach ($groups AS $c_uid=>$uname)
{
  //si no hay campañas, saltarnoslos
  if (count($campanas[$c_uid]) < 1) continue;
	$tabla_campanas .= "<tr class=\"row".(($c++%2)+1)."\">";
	$tabla_campanas .= "<td><a href=\"index.php?_module=$_module&_op=monitoreo_concesionaria&gid=$c_uid\">$uname</a></td>";
	$total_contactos = 0;
  $cc_id = 1;//esto es un override para las campañas como cada quient eien las propias, queremos que agarre 8 de cada una
	foreach ($campanas[$c_uid] AS $campana_id=>$name)
	{
		$tabla_campanas .= "<td>".($contactos[$c_uid][$campana_id])."</td>";
		$total_contactos += $contactos[$c_uid][$campana_id];
		$total_campanas[$cc_id++] += $contactos[$c_uid][$campana_id];
	}
	$tabla_campanas .= "<td>$total_contactos</td>";
	$tabla_campanas .= "<td>{$kpi_t_asign_ini_s[$c_uid]} hr</td>";
	$tabla_campanas .= "<td>{$kpi_max_t_asign_ini[$c_uid]} hr</td>";
  $tabla_campanas .= "<td>{$kpi_t_ini_cierre_s[$c_uid]} hr</td>"; 
	$tabla_campanas .= "<td>{$kpi_max_t_ini_cierre[$c_uid]} hr</td>";
	$tabla_campanas .= "<td>{$kpi_retrasos[$c_uid]} hr</td>";
	
	$tabla_campanas .= "</tr>";
}
$tabla_campanas .= "<tr class=\"row".(($c++%2)+1)."\" style=\"font-weight:bold;\"><td  align=\"right\"><b>Total</b></td>";
//foreach ($campanas[$c_uid] AS $campana_id=>$name)
for ($i = 1; $i <= 8 ; $i++)//esto es un override para las campañas como cada quient eien las propias, queremos que agarre 8 de cada una
{
// 	$tabla_campanas .= "<td>".$total_campanas[$campana_id]."</td>";
// 	$total_total += $total_campanas[$campana_id];
  $tabla_campanas .= "<td>".$total_campanas[$i]."</td>";
  $total_total += $total_campanas[$i];
}

$tabla_campanas .= "<td>$total_total</td>";
$tabla_campanas .= "<td>".array_sum($kpi_t_asign_ini_s)." hr</td>";
$tabla_campanas .= "<td>".array_sum($kpi_max_t_asign_ini)." hr</td>";
$tabla_campanas .= "<td>".array_sum($kpi_t_ini_cierre_s)." hr</td>";
$tabla_campanas .= "<td>".array_sum($kpi_max_t_ini_cierre)." hr</td>";
$tabla_campanas .= "<td>".array_sum($kpi_retrasos)."</td>";
$tabla_campanas .= "</tr>\n";
$tabla_campanas .= "</table>\n";

?>