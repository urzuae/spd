<?
  if (!defined('_IN_ADMIN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}

global $db, $submit, $del;
$origen_id = -3;//momento
if ($submit)
{
  global $_theme;
  $_theme = "";
  $filename = $_FILES['f']['tmp_name'];
  $fh = fopen($filename, "r");
  if (!$fh) die("Error, no se puede leer el archivo (tal vez sea demasiado grande)".$filename);
  include("$_includesdir/select.php");
  global $_edo_civil;
//   $_edo_civil = array( 0=>"Otro", 1=>"Soltero", 2=>"Casado", 3=>"Divorciado", 4=>"Union Libre", 5=>"Viudo" );
  while($data = fgetcsv($fh, 1000, ","))
  {
    $linea++;
    if (!($iii++)) continue; //se salta el primer campo
    $procesados++;
    $data2 = array();
    foreach ($data as $undato)
    {
      $data2[] = addslashes($undato);
    }
    list(
         $gid, 
         $nombre, $apellidos,
         $lada, $telefono,
         $email,
         $ciudad,
         $modelo)
         = $data2;
    if (!$gid && !$nombre) continue; //linea vacia
    //rechazar si no tienen tel�fono
    if (!$telefono)
    {
      $rechazados[] = $linea;//linea en la que lo botamos
      $rechazados_motivo[$linea] = "TELEFONO VACIO";
      continue;
    }
    if ($modelo == "undefined" || $modelo == "New" || $modelo == "Nuevo" || $modelo == "VW") //checar que el veh�culo est� en la lista
    {
      $rechazados[] = $linea;//linea en la que lo botamos
      $rechazados_motivo[$linea] = "MODELO INVALIDO: $modelo";
      continue;
    }
    if (strlen($gid) == 6) list($gid) = explode(",", $gid);//quitar el ",1" de la concesionaria
    if (strlen($gid) > 4 || strlen($gid) == 0)
    {
      $rechazados[] = $linea;//linea en la que lo botamos
      $rechazados_motivo[$linea] = "CONCESIONARIA: $gid";
      continue;
    }
    $asignados_por_gid[$gid]++;
    
    $tel_casa = "";
    if ($lada) $tel_casa .= "($lada)";
    $tel_casa .= " $telefono";
    
    $persona_moral = 0;
    
	  $nombre = strtoupper($nombre);
    $apellidos = strtoupper($apellidos); 
    $espacio = strpos($apellidos, " ");
    if ($espacio)
    {
      $apellido_paterno = substr($apellidos, 0, $espacio);
      $apellido_materno = substr($apellidos, $espacio + 1); 
    }
    else
      $apellido_paterno = $apellidos;
    //siempre es insert por que no tenemos un id en el layout
    $sql = "INSERT INTO crm_contactos (
                                          apellido_paterno,
                                          apellido_materno,
                                          nombre,
                                          tel_casa,
                                          email,
                                          persona_moral,
                                          poblacion,
                                          fecha_importado,
                                          gid,
                                          origen_id
                                        ) VALUES (

                                          '$apellido_paterno',
                                          '$apellido_materno',
                                          '$nombre',
                                          '$tel_casa',
                                          '$email',
                                          '$persona_moral',
                                          '$ciudad',
                                          NOW(),
                                          '$gid',
                                          '$origen_id'
                                        )";
		$insertados++;
		$r2 = $db->sql_query($sql) or die("Error<br>$sql<br>".print_r($db->sql_error()));
//     echo $sql."<br>";
		$contacto_id = $db->sql_nextid($r2);
	  //meterlo en la campa�a correspondiente
    $sql = "SELECT c.campana_id FROM crm_campanas AS c, crm_campanas_groups AS g WHERE c.campana_id=g.campana_id AND g.gid='$gid' ORDER BY campana_id ASC LIMIT 1";//la primer campa�a de la concesionaria
    $r2 = $db->sql_query($sql) or die("Error<br>$sql<br>".print_r($db->sql_error()));
    list($campana_id) = $db->sql_fetchrow($r2);
	  $sql = "INSERT INTO crm_campanas_llamadas (contacto_id, campana_id)VALUES('$contacto_id', '$campana_id')";
// 	  echo $sql."<br>";
	  $db->sql_query($sql); 
    //guardar el log de asignacion 
    $sql = "INSERT INTO crm_contactos_asignacion_log (contacto_id, uid, from_uid, to_uid, from_gid, to_gid)VALUES('$contacto_id','0','0','0','0','$gid')";
    $db->sql_query($sql) or die("Error");

	  //checar si ya est� la info de la unidad que quiere en la db
	  $r2 = $db->sql_query("SELECT contacto_id FROM crm_prospectos_unidades WHERE contacto_id='$contacto_id'");
	  if ($db->sql_numrows($r2) < 1)
	  {
		  $sql = "insert into crm_prospectos_unidades (contacto_id)VALUES('$contacto_id')";
		  $r2 = $db->sql_query($sql) or die("Error<br>$sql<br>".print_r($db->sql_error()));
	  }
	  $sql = "UPDATE crm_prospectos_unidades SET 
          modelo='$modelo', version='$version', ano='$ano', paquete='$paquete', tipo_pintura='$tipo_pintura', 
		  accesorios='$accesorios', color_exterior='$color_exterior', color_interior='$color_interior'
		  WHERE contacto_id='$contacto_id'";
	  
	  $r2 = $db->sql_query($sql) or die("Error$sql".print_r($db->sql_error()));
  }
  $msg = "$procesados registros procesados.\n$insertados agregados.\n";
  $msg .= "\n";
  $msg .= "Lista de lineas rechazadas:\n";
  
  foreach ($rechazados AS $linea)
  {
    $motivo = $rechazados_motivo[$linea];
    $msg .= "$linea,$motivo\n";
  }
  $msg .= "\n";
  $msg .= "Cantidad asignada por concesionaria:\n";
  foreach ($asignados_por_gid AS $gid=>$cuantos)
  {
    $msg .= "Concesionaria $gid: $cuantos\n";
  }
  
  header('Content-Type: text/txt');
  die($msg);
}

 ?>