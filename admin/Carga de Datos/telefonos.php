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
  global $_edo_civil;
  while($data = fgetcsv($fh, 1000, ","))
  {
    if (!($i++)) continue;
    $data2 = array();
    foreach ($data as $undato)
    {
      $data2[] = addslashes($undato);
    }
    list(
    $contacto_id,
    $tel_casa,
    $tel_otro,
    $tel_vacio,
    $tel_oficina,
    $tel_vacio2,
    $tel_movil,
    ) = $data2;
    if ($tel_casa)
    {
      $sql = "UPDATE crm_contactos SET tel_casa='$tel_casa' WHERE contacto_id='$contacto_id'";
      $db->sql_query($sql) or die("Error<br>$sql<br>".print_r($db->sql_error()));
    }
    elseif ($tel_oficina)
    {
      $sql = "UPDATE crm_contactos SET tel_oficina='$tel_oficina' WHERE contacto_id='$contacto_id'";
      $db->sql_query($sql) or die("Error<br>$sql<br>".print_r($db->sql_error()));
    }
    elseif ($tel_movil)
    {
      $sql = "UPDATE crm_contactos SET tel_movil='$tel_movil' WHERE contacto_id='$contacto_id'";
      $db->sql_query($sql) or die("Error<br>$sql<br>".print_r($db->sql_error()));
    }
    elseif ($tel_otro)
    {
      $sql = "UPDATE crm_contactos SET tel_otro='$tel_otro' WHERE contacto_id='$contacto_id'";
      $db->sql_query($sql) or die("Error<br>$sql<br>".print_r($db->sql_error()));
    }
    $counter++;
  }
  $msg = "$counter registros procesados y actualizados.";
}

 ?>