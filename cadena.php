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

if (isset($_POST['data']))
{
  echo 1;
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