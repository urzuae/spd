<? 
if (!defined('_IN_ADMIN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}

global $db, $encuesta_id, $descripcion, $nombre, $submit,$gid,$fecha_ini,$fecha_fin,$_site_name;

if($submit){
  if ($fecha_ini) {
	$fecha_ini_o = $fecha_ini;
	$titulo .= " desde $fecha_ini";
	$fecha_ini = date_reverse ( $fecha_ini );
	$where_fecha .= " AND p.timestamp>'$fecha_ini 00:00:00'";
  }
  if ($fecha_fin) {
	$l_excel .= "&fecha_fin=$fecha_fin";
	$fecha_fin_o = $fecha_fin;
	$titulo .= " hasta $fecha_fin";
	$fecha_fin = date_reverse ( $fecha_fin );
	$where_fecha .= " AND p.timestamp<'$fecha_fin 23:59:59'";
  }
  $titulo = array();
  $filename = "../files/".$_site_name."-reporte-penalizacion_usuarios-".date("Y-m-d-H-i-s").".csv";
  $fp = fopen("$filename", 'w');
  $titulo[] = "Reporte de penalizacion de usuarios";
  fputcsv($fp,$titulo);
  fputcsv($fp, array("Usuario","Score","Distribuidora"));
  if($gid){
  	$gid = (integer)$gid;
    $sql_gid = "SELECT DISTINCT(p.uid), u.name, u.score, u.gid 
                FROM users AS u,
                     users_penalties AS p 
                WHERE u.gid = '$gid' AND
                      p.uid = u.uid
                      $where_fecha 
                ORDER BY gid";
  }
  else{
  	$sql_gid = "SELECT DISTINCT(p.uid), u.name, u.score, u.gid 
  	            FROM users AS u,
  	                 users_penalties AS p
  	            WHERE p.uid = u.uid
  	                  $where_fecha
  	            ORDER BY gid";
  }
  $result = $db->sql_query($sql_gid) or die("Error al consultar renglon<br>$sql_gid");
  while(list($uid_up,$uname, $score, $gid) = $db->sql_fetchrow($result))
  { 
    //los grupos que pueden accederla
    $sql = "SELECT g.name FROM groups AS g WHERE g.gid='$gid'";
    $result2 = $db->sql_query($sql) or die("Error al consultar renglon<br>$sql");
    list($gname) = $db->sql_fetchrow($result2);
    fputcsv($fp, array($uname,$score,$gname));
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
  
  $titulo = "Reporte. Penalización de usuarios";

?>