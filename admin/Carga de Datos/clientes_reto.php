<?
  if (!defined('_IN_ADMIN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}

global $db, $submit, $del;
$origen_id = -2;//reto
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
         $nombre, $apellido_paterno, $apellido_materno,
         $estado_civil, $nivel_de_estudio, $direccion, $lada, $telefono, $celular,
         $email, $concesionaria_alterna,
         $fecha_de_registro,$contacto,
         $marca, 
         $modelo)
         = $data2;
    if (!$gid && !$nombre) continue; //linea vacia
    //rechazar si no tienen teléfono
    if (!$telefono)
    {
      $rechazados[] = $linea;//linea en la que lo botamos
      $rechazados_motivo[$linea] = "TELEFONO VACIO";
      continue;
    }
    if ($modelo == "undefined" || $modelo == "New" || $modelo == "Nuevo" || $modelo == "VW") //checar que el vehículo esté en la lista
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
    
    list($estado_civil) = explode(" ", ucwords(strtolower($estado_civil)));
    $edo_civil = array_search(strtr(ucwords(strtolower($estado_civil)), " ",""), $_edo_civil);
    $tel_movil = $celular;
    $tel_casa = "";
    if ($lada) $tel_casa .= "($lada)";
    $tel_casa .= " $telefono";
    $domicilio = $direccion;
    $persona_moral = 0;
    
	  $nombre = strtoupper($nombre);
    $apellido_paterno = strtoupper($apellido_paterno);
    $apellido_materno = strtoupper($apellido_materno);
    //siempre es insert por que no tenemos un id en el layout
    $sql = "INSERT INTO crm_contactos (
                                          apellido_paterno,
                                          apellido_materno,
                                          nombre,
                                          tel_casa,
                                          tel_movil,
                                          email,
                                          persona_moral,
                                          domicilio,
                                          poblacion,
                                          fecha_importado,
                                          gid,
                                          origen_id
                                        ) VALUES (

                                          '$apellido_paterno',
                                          '$apellido_materno',
                                          '$nombre',
                                          '$tel_casa',
                                          '$tel_movil',
                                          '$email',
                                          '$persona_moral',
                                          '$domicilio',
                                          '$ciudad',
                                          NOW(),
                                          '$gid',
                                          '$origen_id'
                                        )";
		$insertados++;
		$r2 = $db->sql_query($sql) or die("Error<br>$sql<br>".print_r($db->sql_error()));
//     echo $sql."<br>";
		$contacto_id = $db->sql_nextid($r2);
	  //meterlo en la campaña correspondiente
    $sql = "SELECT c.campana_id FROM crm_campanas AS c, crm_campanas_groups AS g WHERE c.campana_id=g.campana_id AND g.gid='$gid' ORDER BY campana_id ASC LIMIT 1";//la primer campaña de la concesionaria
    $r2 = $db->sql_query($sql) or die("Error<br>$sql<br>".print_r($db->sql_error()));
    list($campana_id) = $db->sql_fetchrow($r2);
	  $sql = "INSERT INTO crm_campanas_llamadas (contacto_id, campana_id)VALUES('$contacto_id', '$campana_id')";
// 	  echo $sql."<br>";
	  $db->sql_query($sql); 
    //guardar el log de asignacion 
    $sql = "INSERT INTO crm_contactos_asignacion_log (contacto_id, uid, from_uid, to_uid, from_gid, to_gid)VALUES('$contacto_id','0','0','0','0','$gid')";
    $db->sql_query($sql) or die("Error");

	  //checar si ya está la info de la unidad que quiere en la db
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
	  
	  $r2 = $db->sql_query($sql) or die("Error<br>$sql<br>".print_r($db->sql_error()));
  }
  $msg = "$procesados registros procesados.\n$insertados agregados.\n";
  $msg .= "\n";
  $msg .= "Lista de lineas rechazadas:\n";
  
  if ($rechazados)
    foreach ($rechazados AS $linea)
    {
      $motivo = $rechazados_motivo[$linea];
      $msg .= "$linea,$motivo\n";
    }
  $msg .= "\n";
  $msg .= "Cantidad asignada por concesionaria:\n";
  if ($asignados_por_gid) 
    foreach ($asignados_por_gid AS $gid=>$cuantos)
    {
      $msg .= "Concesionaria $gid: $cuantos\n";
    }
    
  header('Content-Type: text/txt');
  die($msg);
}

 ?>