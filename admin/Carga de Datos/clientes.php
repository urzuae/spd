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
//   $_edo_civil = array( 0=>"Otro", 1=>"Soltero", 2=>"Casado", 3=>"Divorciado", 4=>"Union Libre", 5=>"Viudo" );
  while($data = fgetcsv($fh, 1000, ","))
  {
    if (!($iii++)) continue; //se salta el primer campo
    $data2 = array();
    foreach ($data as $undato)
    {
      $data2[] = addslashes($undato);
    }
    list(
         $contacto_id,
         $sexo, $saludo, $nombre, $apellido_paterno, $apellido_materno,
         $fecha_de_nacimiento, $estado_civil, $nivel_de_estudio, $lada, $telefono, $extension, $celular,
         $email, $direccion, $concesionaria_seleccionada, $concesionaria_alterna,
         $marca, 
         $modelo, $version, $ano, $paquete, $tipo_pintura, $accesorios, $color_exterior, $color_interior)
         = $data2;
    list($estado_civil) = explode(" ", ucwords(strtolower($estado_civil)));
    $edo_civil = array_search(strtr(ucwords(strtolower($estado_civil)), " ",""), $_edo_civil);

    if ($sexo == "1") $sexo = "0"; //están al revés en el layout
    else $sexo = "1";
    
    list($fecha_de_nacimiento) = explode(" ", $fecha_de_nacimiento);//viene tambien la hora
    
    $fecha_de_nacimiento = date_reverse(strtr($fecha_de_nacimiento, "/", "-"));
    if($nombre2) $nombre .= " $nombre2";
    $tel_casa = "";
    if ($lada) $tel_casa .= "($lada)";
    $tel_casa .= " $telefono";
    if ($extension) $tel_casa .= " ext. $extension";
    $tel_movil = $celular;
    $domicilio = $direccion;
    $persona_moral = 0;
    
	$nombre = strtoupper($nombre);
	$apellido_paterno = strtoupper($apellido_paterno);
	$apellido_materno = strtoupper($apellido_materno);
    //ESTO ES TEMPORAL
    $gid='2';
    
    $sql = "SELECT contacto_id FROM crm_contactos WHERE contacto_id='$contacto_id' LIMIT 1";
    $result2 = $db->sql_query($sql) or die("Error");
    if ($db->sql_numrows($result2) > 0)
    {
      $sql = "UPDATE crm_contactos SET
                                          apellido_paterno='$apellido_paterno',
                                          apellido_materno='$apellido_materno',
                                          nombre='$nombre',
                                          edo_civil='$edo_civil',
                                          fecha_de_nacimiento='$fecha_de_nacimiento',
                                          sexo='$sexo',
                                          domicilio='$domicilio',
                                          email='$email',
                                          persona_moral='$persona_moral',
										  gid='$gid'
                                     WHERE contacto_id='$contacto_id'";
		$updated++;
		$r2 = $db->sql_query($sql) or die("Error<br>$sql<br>".print_r($db->sql_error()));
    }
    else
    {//siempre es insert por que no tenemos un id en el layout
      $sql = "INSERT INTO crm_contactos (
                                          apellido_paterno,
                                          apellido_materno,
                                          nombre,
                                          edo_civil,
                                          fecha_de_nacimiento,
                                          tel_casa,
                                          tel_otro,
                                          tel_oficina,
                                          tel_movil,
                                          sexo,
                                          domicilio,
                                          email,
                                          persona_moral,
                                          fecha_importado,
                                          gid
                                        ) VALUES (

                                          '$apellido_paterno',
                                          '$apellido_materno',
                                          '$nombre',
                                          '$edo_civil',
                                          '$fecha_de_nacimiento',
                                          '$tel_casa',
                                          '$tel_otro',
                                          '$tel_oficina',
                                          '$tel_movil',
                                          '$sexo',
                                          '$domicilio',
                                          '$email',
                                          '$persona_moral',
                                          NOW(),
                                          '$gid'
                                        )";
		$inserted++;
		$r2 = $db->sql_query($sql) or die("Error<br>$sql<br>".print_r($db->sql_error()));
		$contacto_id = $db->sql_nextid($r2);
	    //meterlo en la campaña correspondiente
	    ///TEMPORAL, mete todo a la primer campaña
	    $sql = "INSERT INTO crm_campanas_llamadas (contacto_id, campana_id)VALUES('$contacto_id', '1')";
	    echo $sql."<br>";
	    $db->sql_query($sql); 

    }
    echo $sql."<br>";
	//checar si ya está la info de la unidad que quiere en la db
	$r2 = $db->sql_query("SELECT contacto_id FROM crm_prospectos_unidades WHERE contacto_id='$contacto_id'");
	if ($db->sql_numrows($r2) < 1)
	{
		$sql = "insert into crm_prospectos_unidades (contacto_id)VALUES('$contacto_id')";
		$r2 = $db->sql_query($sql) or die("Error<br>$sql<br>".print_r($db->sql_error()));
	}
	$sql = "UPDATe crm_prospectos_unidades SET 
         modelo='$modelo', version='$version', ano='$ano', paquete='$paquete', tipo_pintura='$tipo_pintura', 
		 accesorios='$accesorios', color_exterior='$color_exterior', color_interior='$color_interior'
		 WHERE contacto_id='$contacto_id'";
	
	$r2 = $db->sql_query($sql) or die("Error<br>$sql<br>".print_r($db->sql_error()));
    $counter++;
    
  }
  $msg = "$counter registros procesados.<br>$inserted nuevos.<br>$updated actualizados.";
}

 ?>