<?php

$_dbhost = 'localhost';
$_dbuname = 'root';
$_dbpass = 'redsox';
$_dbname = 'spd';
$db = connect($_dbhost, $_dbuname, $_dbpass, $_dbname);

# cadena.php en servidor remoto
/*$data = array(0 => "47,78,1,0,asdas",
      1 => "47,78,2,0,asdasd",
      2=>"47,78,3,0,454545",
      3 => "47,78,4,0,asda@adsd.com",
      4=>"47,78,5,0,asdasd");*/

if (isset($_POST['data'])) {
  //$data = json_decode($_POST['data']);
  $data = unserialize($_POST['data']);;
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
    $params[] = $temp[4];
  }
  
  //print_r($params);
  
  $sql = "SELECT gid FROM groups WHERE name='SALES FUNNEL'";
  $res = $db->sql_query($sql) or die($sql);
  list($gid) = $db->sql_fetchrow($res);
  
  $sql = "SELECT count(c.uid),c.uid from crm_contactos as c, users as u where c.uid=u.uid and u.super='8' group by c.uid";
  $result = $db->sql_query($sql);
  $flag = false;
  while(list($current_count, $current_uid) = $db->sql_fetchrow($result)) {
    if(!$flag)
    {
      $count = $current_count;
      $uid = $current_uid;
      $flag = true;
    }
    else
    {
      if ($current_count < $count)
      {
        $count = $current_count;
        $uid = $current_uid;
      }
    }
  }
  
  $sql = "INSERT INTO crm_contactos (
                    nombre, apellido_paterno,
                    tel_casa,
                    email,nota,gid,uid)
                    VALUES (
                    '".$params[0]."',
                    '".$params[1]."',
                    '".$params[2]."',
                    '".$params[3]."',
                    '".$params[4]."',
                    '".$gid."',
                    '".$uid."')";
  
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
}
else
{
  echo 0;
}

function connect($_dbhost, $_dbuname, $_dbpass, $_dbname)
{
  include("includes/db/mysql.php");
  $_dbtype = 'MySQL';
  $db = new sql_db($_dbhost, $_dbuname, $_dbpass, $_dbname, false);
  return $db;
}
?>