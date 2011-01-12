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
  $chasises = array();
  while($data = fgetcsv($fh, 1000, ","))
  {
    if (!$data[0]) continue; //quitar lineas en blanco
    if ($data[0] == "#") continue; //quitar header
    $asesor = $data[19];//el uid del asesor
    $result = $db->sql_query("SELECT uid FROM users WHERE name='$asesor'") or die("Error");
    list($uid) = $db->sql_fetchrow($result);
    //fecha
    $fecha = $data[4];
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
    
    
    $data2 = array($data[0],$data[2],$data[3],$fecha,$uid,$data[9],$data[5]);
    list($contacto_id, $operacion, $factura, $fecha, $uid, $tipo, $chasis) = $data2;
                  //id      orden    fecha asesor   tipo    chasis,     modelo
    $sql = "INSERT INTO crm_clientes_ventas (contacto_id, operacion, factura, fecha, uid, tipo, chasis, fecha_importado)
            VALUES('$contacto_id', '$operacion', '$factura', '$fecha', '$uid', '$tipo', '$chasis', NOW())";
    $db->sql_query($sql) or die("Error".print_r($db->sql_error()));
    $cliente_servicio_id = $db->sql_nextid();
    $total++;
    //ahora agregarle una cita
    if ($operacion == "CANC" || ( ereg("CANC", $chasis) == TRUE))
    {
      $canceladas++;
      continue; //se cancelo, no llamar
    }
    if (in_array($chasis, $chasises))
    {
       $repetidas++;
       continue; //ya lo dimos de alta, no agregar otra llamada
    }
    $chasises[] = $chasis;
    
    $fecha_cita = date("Y-m-d 0:00:00", mktime(0,0,0,$m,$d,$y) + (3 * 60 * 60 * 24));
    $sql = "INSERT INTO crm_campanas_llamadas (campana_id, contacto_id, fecha_cita,user_id,status_id,aux_id)VALUES('-3', '$contacto_id', '$fecha_cita','0','-2', $cliente_servicio_id)";
    $db->sql_query($sql) or die("Error".print_r($db->sql_error()));
    $call_center++;
    fputcsv($fh2, $data2, ",");
  }
  $msg = "Total procesadas: $total<br>
          Canceladas: $canceladas<br>
          Repetidas: $repetidas<br>
          Agregadas a Call Center: $call_center<br><br>";
//   header("location: $_module/files/$_op.csv");
}

 ?>