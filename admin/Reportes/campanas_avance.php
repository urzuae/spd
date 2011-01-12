<? 
if (!defined('_IN_ADMIN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}

global $db, $encuesta_id, $descripcion, $nombre, $submit, $gid,$fecha_ini,$fecha_fin,$_site_name;

if($submit){
  $titulo = array();
  $filename = "../files/".$_site_name."-reporte_de_avance-".date("Y-m-d-H-i-s").".csv";
  $fp = fopen("$filename", 'w');
  $titulo[] = "Reporte de avance";
  if ($fecha_ini) {
	$titulo[] = " desde $fecha_ini";
	$fecha_ini_o = $fecha_ini;
	$fecha_ini = date_reverse ( $fecha_ini );
	$where_fecha .= " AND timestamp>'$fecha_ini 00:00:00'";
  }
  if ($fecha_fin) {
	$titulo[] = " hasta $fecha_fin";
	$fecha_fin_o = $fecha_fin;
	$fecha_fin = date_reverse ( $fecha_fin );
	$where_fecha .= " AND timestamp<'$fecha_fin 23:59:59'";
  }
  fputcsv($fp,$titulo);
  $row = array("Campaña", "Distribuidores");
  $sql = "SELECT nombre FROM crm_campanas_llamadas_status WHERE 1 ORDER BY status_id";
  //queremos todas por que eventualmente se llenará la tabla
  $result2 = $db->sql_query($sql) or die("Error al consultar<br>$sql");
  while (list($status) = $db->sql_fetchrow($result2))
  {
    $row[] = $status;
  }
  fputcsv($fp, $row);
  if($gid){
    $sql = "SELECT campana_id, nombre FROM crm_campanas WHERE LEFT(nombre,4) = '$gid' AND campana_id>10 ORDER BY campana_id";
  }
  else{
    $sql = "SELECT campana_id, nombre FROM crm_campanas WHERE campana_id>10  ORDER BY campana_id";
  }
  $result = $db->sql_query($sql) or die("Error al consultar renglon<br>$sql");
  while(list($campana_id, $nombre) = $db->sql_fetchrow($result))
  { 
  	$consultas = array();
  	//los grupos que pueden accederla
    $sql = "SELECT g.name 
            FROM crm_campanas_groups AS c, 
                 groups AS g 
            WHERE c.gid=g.gid AND c.campana_id='$campana_id'";
    $result2 = $db->sql_query($sql) or die("Error al consultar renglon<br>$sql");
    $groups = "";
    while(list($g_name) = $db->sql_fetchrow($result2))
    {
      if (strlen($groups) != 0) $groups .= ", ";
      $groups .= "$g_name";
    }
    $row = array($nombre, $groups);
    //ahora buscar los status y organizar las llamadas por eso
    $sql = "SELECT status_id FROM crm_campanas_llamadas_status WHERE 1 ORDER BY status_id";
    //queremos todas por que eventualmente se llenará la tabla
    $result2 = $db->sql_query($sql) or die("Error al consultar<br>$sql");
    while (list($status_id) = $db->sql_fetchrow($result2))
    {
      $sql = "SELECT id 
              FROM crm_campanas_llamadas 
              WHERE campana_id='$campana_id' AND 
                    status_id='$status_id'
                    $where_fecha";
      $result3 = $db->sql_query($sql) or die("Error al consultar<br>$sql");
      $row[] = $db->sql_numrows($result3);
      $consultas[] = $sql; 
    }
    fputcsv($fp, $row);
   // fputcsv($fp, $consultas);
  }
  fclose($fp);
//   chmod($filename, 0666);
  header("location:$filename");
}
  $select_groups = "<select name=\"gid\">";
  $result = $db->sql_query("SELECT gid,name FROM groups WHERE 1 ORDER BY gid") or die("Error al cargar grupos");
  $select_groups .= "<option value=\"\">Selecciona una distribuidora</option>\n";
  while(list($_gid,$name) = $db->sql_fetchrow($result)){
  	if ($_gid == $gid)
  	  $selected = " SELECTED";
  	else
  	  $selected = "";
  	$select_groups .= "<option value=\"$_gid\"$selected>$_gid - $name</option>";
  }
  $select_groups .= "</select>";  
  $titulo = "Reporte de avance";
?>