<?
  if (!defined('_IN_ADMIN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}

global $db, $submit, $del;
if ($submit)
{
  $filename = $_FILES['f']['tmp_name'];
  $fh = fopen($filename, "r");
  if (!$fh) die("Error, no se puede leer el archivo (tal vez sea demasiado grande)".$filename);
  include("$_includesdir/select.php");
  /*//vamos a agregar a los gerentes a VWM
  //A que modulos dar acceso
  $modules = array("Bienvenida", "Noticias", "Directorio", "Zona","Estadisticas");
  //crear grupo
  $sql = "INSERT INTO groups (name) VALUES ('GERENTES DE ZONA')";
  $r = $db->sql_query($sql) or die("Error<br>$sql<br>".print_r($db->sql_error()));
  $gid = $db->sql_nextid($r);
  foreach($modules AS $module)
  {
    $sql = "SELECT gid FROM groups_accesses WHERE gid='$gid' AND module='$module' LIMIT 1";
    $result2 = $db->sql_query($sql) or die("Error<br>$sql<br>".print_r($db->sql_error()));
    if ($db->sql_numrows($result2) < 1)
      $db->sql_query("INSERT INTO groups_accesses (gid,module)VALUES('$gid','$module')") or die("Error<br>".print_r($db->sql_error()));
  }
  */
  $gid = 1;
  while($data = fgetcsv($fh, 1000, ","))
  {
    if (!($ii++)) continue; //se salta el primer campo
    $data2 = array();
    foreach ($data as $undato)
    {
      $data2[] = addslashes($undato);
    }
    list(
    $zona_id,
    $nombre,
    $super,
    $email
    ) = $data2;
    if (!$zona_id) continue;
    $nombre = strtoupper($nombre);
    $zona_id = sprintf("%02u", $zona_id);
    $user = "GTEZONA".$zona_id;
    $sql = "INSERT INTO users (gid,super,user,name,password,email)VALUES('$gid','$super','$user','$nombre',PASSWORD('$user'),'$email')";

    $r = $db->sql_query($sql) or die("Error<br>".print_r($db->sql_error()));
    $insertado++;
    $uid = $db->sql_nextid($r);
    $sql = "INSERT INTO crm_zonas_gerentes (zona_id,uid)VALUES('$zona_id', '$uid')";
    $r = $db->sql_query($sql) or die("Error<br>".print_r($db->sql_error()));
  }
  $msg = "$insertado gerentes agregados.";
}

 ?>