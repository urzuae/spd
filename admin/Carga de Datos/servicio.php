<?
  if (!defined('_IN_ADMIN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}

global $db, $submit, $del;
if ($submit)
{
  $filename = $_FILES['f']['tmp_name'];
  $fh = fopen($filename, "r");
  $fh2 = fopen("$_module/files/$_op.csv", "w");
  
  while($data = fgetcsv($fh, 1000, ","))
  {
    if (!$data[0]) continue; //quitar lineas en blanco
    if ($data[0] == "#") continue; //quitar header
    $asesor = $data[19];//el uid del asesor
    $result = $db->sql_query("SELECT uid FROM users WHERE name='$asesor'") or die("Error");
    list($uid) = $db->sql_fetchrow($result);
    //fecha
    $fecha = $data[9];
    list($d, $m, $y) = explode("-", $fecha);
    switch($m){
      case "Ene": $m = 1; break;
      case "Feb": $m = 2; break;
      case "Mar": $m = 3; break;
      case "Abr": $m = 4; break;
      case "May": $m = 5; break;
      case "Jun": $m = 6; break;
      case "Jul": $m = 7; break;
      case "Ago": $m = 8; break;
      case "Sep": $m = 9; break;
      case "Oct": $m = 10; break;
      case "Nov": $m = 11; break;
      case "Dic": $m = 12; break;
    }
    $fecha = "$y-$m-$d";
    $data2 = array($data[0],$data[8],$fecha,$uid,$data[21],$data[22], $data[23]);
    list($contacto_id, $orden, $fecha, $uid, $tipo, $chasis, $modelo) = $data2;
                  //id      orden    fecha asesor   tipo    chasis,     modelo
    $sql = "INSERT INTO crm_clientes_servicios (contacto_id, orden, fecha, uid, tipo, chasis, modelo, fecha_importado)
            VALUES('$contacto_id', '$orden', '$fecha', '$uid', '$tipo', '$chasis', '$modelo', NOW())";
    $db->sql_query($sql) or die("Error".print_r($db->sql_error()));
    $cliente_venta_id = $db->sql_nextid();
    
//     if ($contacto_id == "9087" || $contacto_id == "28580") {$interno++;continue;}
//     if ($contacto_id == "1726") {$planta++;continue;}
    //ahora agregarle una cita
    $fecha_cita = date("Y-m-d 0:00:00", mktime(0,0,0,$m,$d,$y) + 3 * 60 * 60 * 24);
    $sql = "INSERT INTO crm_campanas_llamadas (campana_id, contacto_id, fecha_cita,user_id,status_id,aux_id)VALUES('-1', '$contacto_id', '$fecha_cita','0','-2', $cliente_venta_id)";
    $db->sql_query($sql) or die("Error".print_r($db->sql_error()));
    $total++;
    fputcsv($fh2, $data2, ",");
    
  }
  $msg  = "Se agregaron $total registros<br><br>";
//   header("location: $_module/files/$_op.csv1");
}

 ?>