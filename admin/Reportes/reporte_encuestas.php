<? 
if (!defined('_IN_ADMIN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}

global $db, $encuesta_id, $descripcion, $nombre, $submit;

//servicio
if ($encuesta_id)
{
  $filename = "$_module/files/reporte_de_avance.csv";
  $fp = fopen("$filename", 'w');
//   fputscv($fp, array());
  /*
      $sql = "SELECT status_id, nombre FROM crm_campanas_llamadas WHERE campana_id='$campana_id' OR campana_id='0'";
    $result2 = $db->sql_query($sql) or die("Error al consultar<br>$sql")
    while (list($status_id, $s_nombre) = $db->sql_fetchrow($result2))
  */
  $sql = "SELECT campana_id, nombre, gid FROM crm_campanas WHERE 1 ORDER BY gid";
  $result = $db->sql_query($sql) or die("Error al consultar renglon<br>$sql");
  while(list($campana_id, $nombre, $gid) = $db->sql_fetchrow($result))
  { 
    $sql = "SELECT nombre FROM groups WHERE gid='$gid' limit 1";
    $result2 = $db->sql_query($sql) or die("Error al consultar<br>$sql");
    list($g_nombre) = $db->sql_fetchrow($result2);
    $row = array($nombre, $gid, $g_nombre);
    //ahora buscar los status y organizar las llamadas por eso
    $sql = "SELECT status_id FROM crm_campanas_llamadas WHERE 1 ORDER BY status_id";
    //queremos todas por que eventualmente se llenará la tabla
    $result2 = $db->sql_query($sql) or die("Error al consultar<br>$sql");
    while (list($status_id) = $db->sql_fetchrow($result2))
    {
      $sql = "SELECT id FROM crm_campanas_llamadas WHERE campana_id='$campana_id' AND status_id='$status_id'";
      $result3 = $db->sql_query($sql) or die("Error al consultar<br>$sql");
      $row[] = $db->sql_numrows($result3);
    }
    fputcsv($fp, $row);
  }
  fclose($fp);
  chmode($filename, 0666);
  header("location:$filename");
}
else
{

}

?> 
