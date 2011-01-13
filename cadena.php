<?php
$_dbtype = 'MySQL';
$_dbhost = 'localhost';
$_dbuname = 'root';
$_dbpass = 'redsox';
$_dbname = 'spd';
include("includes/db/mysql.php");
$db = new sql_db($_dbhost, $_dbuname, $_dbpass, $_dbname, false);

# cadena.php en servidor remoto
/*$data = array(0 => "47,78,1,0,asdas",
      1 => "47,78,2,0,asdasd",
      2=>"47,78,3,0,454545",
      3 => "47,78,4,0,asda@adsd.com",
      4=>"47,78,5,0,asdasd");*/

if (isset($_GET['data'])) {
  $data = json_decode($_GET['data']);
  //print_r($data);
  //$data = json_encode($data);
  //print_r($data);
  //$data = json_decode($data);
  $params = array();
  
  foreach ($data as $row) {
    //print_r($row);
    $temp = explode(",", $row);
    $params[] = $temp[4];
  }
  
  print_r($params);
  
  $sql = "INSERT INTO crm_contactos (
                    nombre, apellido_paterno,
                    tel_casa,
                    email,nota)
                    VALUES (
                    '".$params[0]."',
                    '".$params[1]."',
                    '".$params[2]."',
                    '".$params[3]."',
                    '".$params[4]."')";
  
  $result = $db->sql_query($sql) or die($sql);
  echo 1;
} else {
  echo 0;
}
?>