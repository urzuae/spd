<?php

if(!defined('_IN_MAIN_INDEX'))
  die("No puedes accesar directamente a este archivo");

global $db, $_theme, $data;


$_theme = "";

//Parsear apellido
//Escapar el sql

if (isset($_POST['data'])) {
  //$data = json_decode($_POST['data']);
  $data = unserialize($data);
  //print_r($data);
  //$data = json_encode($data);
  //print_r($data);
  //$data = json_decode($data);
  //$data = $_POST['data'];
  //print_r($data);
  $params = array();
  
  foreach ($data as $row) {
    //print_r($row);
    $temp = explode(",", $row);
    $params[] = mysql_escape_string($temp[4]);
  }
  
  //print_r($params);
  
  $sql = "SELECT gid FROM groups WHERE name='SALES FUNNEL'";
  $res = $db->sql_query($sql) or die($sql);
  list($gid) = $db->sql_fetchrow($res);
  
  $sql = "SELECT count(c.uid),c.uid,u.gid from crm_contactos as c, users as u where c.uid=u.uid and u.super='8' group by c.uid";
  $result = $db->sql_query($sql);
  $flag = false;
  while(list($current_count, $current_uid, $current_gid) = $db->sql_fetchrow($result)) {
    if(!$flag)
    {
      $count = $current_count;
      $uid = $current_uid;
      $gid = $current_gid;
      $flag = true;
    }
    else
    {
      if ($current_count < $count)
      {
        $count = $current_count;
        $uid = $current_uid;
        $gid = $current_gid;
      }
    }
  }
  
  $sql = "INSERT INTO crm_contactos ( nombre, apellido_paterno, tel_casa, email,nota,gid,uid)
    VALUES ('".$params[0]."','".$params[1]."','".$params[2]."','".$params[3]."','".$params[4]."','".$gid."','".$uid."')";
  
  $result = $db->sql_query($sql) or die($sql);
  $contacto_id = $db->sql_nextid();
  
  //buscar a que campaña lo meteremos
  $sql = "SELECT c.campana_id FROM crm_campanas_groups AS g, crm_campanas AS c WHERE c.campana_id=g.campana_id AND g.gid='$gid' ORDER BY c.campana_id  LIMIT 1"; //la primera que sea parte de un ciclo
  $result = $db->sql_query($sql) or die("Error al leer".print_r($db->sql_error()));
  list($campana_id) = $db->sql_fetchrow($result);
  //para agregarlo a crm_campanas_llamadas
  $sql = "insert into crm_campanas_llamadas  (campana_id, contacto_id) values ('$campana_id', '$contacto_id')";
  $db->sql_query($sql) or die("Error al insertar a la campaña".print_r($db->sql_error()));
  //guardar el log de asignacion
  $sql = "INSERT INTO crm_contactos_asignacion_log (contacto_id, uid, from_uid, to_uid, from_gid, to_gid)VALUES('$contacto_id','$uid','0','$uid','0','$gid')";
  $db->sql_query($sql) or die("Error al insertar al log");
  return true;
}
else
{
  echo false;
}
?>