<?php
if(!defined('_IN_MAIN_INDEX'))
{
    die("No puedes acceder directamente a este archivo...");
}
include_once ("./includes/select.php");
global $db, $contacto_id, $_entidades_federativas, $_module, $_op, $_action, $nombre, $apellido_paterno, $apellido_materno, 
$lada1, $lada2, $lada3, $lada4, $tel_casa, $tel_oficina, $tel_movil, $tel_otro, $email, $entidad_federativa_id, $poblacion, $gid, 
$nota, $medio_contacto, $motivo_baja, $fecha_cita, $hora_cita, $minuto_cita, $evento_comentario, $nopendientes, $llamada_id, $uid, 
$siguiente, $prioridad_id, $canal_recepcion_id, $lada_casa_2, $lada_movil_2, $lada_oficina_2, $tel_casa_2, $tel_oficina_2, $tel_movil_2,
$horario_casa, $horario_casa_2, $horario_oficina, $horario_oficina_2, $horario_celular, $horario_celular_2,$_site_title;
$_css = $_themedir . "/style.css";
$yesterday = date('Y-m-d h:i:s', mktime(date("h"), date("i"), date("s"), date("m"), date("d") - 1, date("Y")));
$sql = "UPDATE crm_campanas_llamadas_no_asignados SET `lock` = '0'
        WHERE (timestamp < '$yesterday' OR user_id ='$uid') AND `lock` = '1'";
$result = $db->sql_query($sql) or die($sql);
$sql = "SELECT COUNT(c.contacto_id)
        FROM crm_contactos_no_asignados AS c, crm_campanas_llamadas_no_asignados AS l 
        WHERE c.contacto_id = l.contacto_id AND l.`lock` = 0 AND NOT (l.fecha_cita > NOW())";
$result = $db->sql_query($sql) or die($sql);
list($num_contactos) = $db->sql_fetchrow($result);
if($num_contactos == 0)
{
    $_html = "<html><head>
             <link type=\"text/css\" href=\"$_css\" rel=\"stylesheet\"></head><body>
             <center><h1>No se encontraron contactos </h1><br>
             <input type=button value=\"Regresar\" onclick=\"location.href='index.php'\">
             </center></body>";
    die($_html);
}
if(!$nopendientes)
{
    $sql = "SELECT l.fecha_cita , l.contacto_id, c.nombre, c.apellido_paterno, c.apellido_materno
        FROM crm_campanas_llamadas_no_asignados AS l, crm_contactos_no_asignados as c
        WHERE l.fecha_cita < NOW() AND l.fecha_cita != '00-00-00 00:00:00' 
        AND l.`lock` = '0' AND c.contacto_id = l.contacto_id ORDER BY l.fecha_cita";

    $result = $db->sql_query($sql) or die($sql);
    $cuantas_llamadas_pendientes = 0;
    if($db->sql_numrows($result_cid) != 0)
    {
        while(list($fecha_cita, $contacto_id, $nombre, $ap_pat, $ap_mat) = $db->sql_fetchrow($result))
        {
            $lista .= "<tr class=\"row1\" style=\"cursor:pointer;\" onclick=\"location.href='index.php?_module=$_module&_op=$_op&nopendientes=1&contacto_id=$contacto_id'\">
    	          <td>$nombre</td><td>$ap_pat</td><td>$ap_mat</td>
                  <td>$fecha_cita</td>
                  <td><a href=\"index.php?_module=$_module&_op=$_op&contacto_id=$contacto_id&nopendientes=1\"><img src=\"img/phone.gif\" border=0></a></td>
                  </tr>\n";
            $cuantas_llamadas_pendientes++;
        }
        $_html = "<html><head>
            <link type=\"text/css\" href=\"$_css\" rel=\"stylesheet\"></head><body>
            <center><h1>Hay compromisos pendientes ($cuantas_llamadas_pendientes):</h1><br>
            <table>\n<thead><td>Nombre</td><td>Paterno</td><td>Materno</td><td>Cita</td><td></td></thead>" . $lista . "</table>\n
            <input type=button value=\"Ignorar\" onclick=\"location.href='index.php?_module=$_module&_op=$_op&nopendientes=1'\">
            </center></body>";
        //no continuar hasta que se conteste
        die($_html);
    }
}

/*_action = g, significa que se va a continuar para editar a dicho contacto
*/
if($_action == 'g')
{
   $viejoContactoId = $contacto_id;
    if($lada1)
        $tel_casa = "($lada1) $tel_casa";
    if($lada2)
        $tel_oficina = "($lada2) $tel_oficina";
    if($lada3)
        $tel_movil = "($lada3) $tel_movil";
    if($lada4)
        $tel_otro = "($lada4) $tel_otro";
        
    if($lada_casa_2) $tel_casa_2 = "(" . $lada_casa_2 . ") " . $tel_casa_2;
    if($lada_oficina_2) $tel_oficina_2 = "(" . $lada_oficina_2 . ") " . $tel_oficina_2;
    if($lada_movil_2) $tel_movil_2 = "(" . $lada_movil_2 . ") " . $tel_movil_2;

    $horario_casa = (is_array($horario_casa)) ? $horario_casa : array();
    $horario_casa_2 = (is_array($horario_casa_2)) ? $horario_casa_2 : array();
    $horario_oficina = (is_array($horario_oficina)) ? $horario_oficina : array();
    $horario_oficina_2 = (is_array($horario_oficina_2)) ? $horario_oficina_2 : array();
    $horario_celular = (is_array($horario_celular)) ? $horario_celular : array();
    $horario_celular_2 = (is_array($horario_celular_2)) ? $horario_celular_2 : array();    
    $horario_preferido_casa = implode('',$horario_casa);
    $horario_preferido_casa_2 = implode('',$horario_casa_2);        
    $horario_preferido_oficina = implode('',$horario_oficina);
    $horario_preferido_oficina_2 = implode('',$horario_oficina_2);    
    $horario_preferido_movil = implode('',$horario_celular);
    $horario_preferido_movil_2 = implode('',$horario_celular_2);
        
    $sql = sprintf("update crm_contactos_no_asignados set
	        nombre = '%s', apellido_paterno = '%s', apellido_materno = '%s',
	        tel_casa = '%s', tel_oficina = '%s', tel_movil = '%s',
	        tel_otro = '%s', email = '%s',
	        poblacion = '%s', entidad_id = '%s', gid = '%s',
	        nota = '%s', medio_contacto = '%s', prioridad = '%s',
	        tel_casa_2 = '%s', tel_oficina_2 = '%s', tel_movil_2 = '%s',
            horario_preferido_casa = '%s', horario_preferido_oficina = '%s', horario_preferido_movil = '%s',
            horario_preferido_casa_2 = '%s', horario_preferido_oficina_2 = '%s', horario_preferido_movil_2 = '%s'
	        where contacto_id = '%s'",
            $nombre, $apellido_paterno, $apellido_materno, $tel_casa, $tel_oficina, $tel_movil, $tel_otro, $email,
            $poblacion, $entidad_federativa_id, $gid, $nota, $medio_contacto,  $prioridad_id ,
            $tel_casa_2, $tel_oficina_2, $tel_movil_2,
            $horario_preferido_casa, $horario_preferido_oficina, $horario_preferido_movil,
            $horario_preferido_casa_2, $horario_preferido_oficina_2, $horario_preferido_movil_2,
            $contacto_id);

    $r1 = $db->sql_query($sql) or die($sql);
    $contacto_id_eliminar = $contacto_id;
    $res = array();
    $sql = "SELECT * FROM crm_contactos_no_asignados WHERE contacto_id='$contacto_id' LIMIT 1";
    $result = $db->sql_query($sql);
    $res = $db->sql_fetchrow($result);
    $sql = "SELECT modelo FROM crm_prospectos_unidades_no_asignados WHERE contacto_id='$contacto_id' LIMIT 1";
    $result = $db->sql_query($sql);
    list($modelo) = $db->sql_fetchrow($result);
    
    $sql = sprintf("SELECT c.campana_id FROM crm_campanas_groups AS g, crm_campanas AS c 
	        WHERE c.campana_id=g.campana_id AND g.gid='%s' ORDER BY c.campana_id  LIMIT 1", $res['gid']);
    $result = $db->sql_query($sql) or die("Error al leer" . print_r($db->sql_error()));
    list($campana_id) = $db->sql_fetchrow($result);
    
    $sql = sprintf("INSERT INTO crm_contactos (
                nombre, apellido_paterno, apellido_materno,
                tel_casa, tel_oficina,tel_movil, tel_otro,email,origen_id,gid,nota,
                poblacion, entidad_id,persona_moral, fecha_de_nacimiento,
                fecha_importado, primer_contacto, prioridad,fecha_alta,                
                tel_casa_2,tel_oficina_2,tel_movil_2,horario_preferido_casa,horario_preferido_oficina,
                horario_preferido_movil,horario_preferido_casa_2,horario_preferido_oficina_2,horario_preferido_movil_2)
                VALUES ('%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s',
                '%s','%s','%s','%s','%s','%s','%s','%s','%s')", 
    $res['nombre'], $res['apellido_paterno'], $res['apellido_materno'], $res['tel_casa'], $res['tel_oficina'], $res['tel_movil'], $res['tel_otro'], 
    $res['email'], $res['origen_id'], $res['gid'], $res['nota'], $res['poblacion'], $res['entidad_id'], $res['persona_moral'], $res['fecha_de_nacimiento'], 
    $res['fecha_importado'], $res['primer_contacto'], $res['prioridad'],$res["fecha_alta"],
    
    $res['tel_casa_2'],  $res['tel_oficina_2'], $res['tel_movil_2'], $res['horario_preferido_casa'],
    $res['horario_preferido_oficina'], $res['horario_preferido_movil'],  $res['horario_preferido_casa_2'], $res['horario_preferido_oficina_2'],
    $res['horario_preferido_movil_2']
    ); //origen_id 4 por que es de piso
    $db->sql_query($sql) or die("$sql<br>Error al insertar contacto 1 " . print_r($db->sql_error()));
    $contacto_id = $db->sql_nextid();
    /*
     * Se asigna a la tabla temporal para enviar el correo de que
     * se ha asignado a su concesionaria
    */
    $sql_temp = sprintf("INSERT INTO crm_contactos_asignados_tmp (contacto_id, gid)
                         VALUES('%s','%s')", $contacto_id, $res['gid']);
    $db->sql_query($sql_temp) or die("$sql_temp<br>Error al insertar contacto tmp " . print_r($db->sql_error()));
    /*
     * Se inserta en la tabla de usuarios unidades para saber que unidad le corresponde
    */
    $modelo_id=Revisa_modelo($db,$modelo);

    $sql = sprintf("INSERT INTO crm_prospectos_unidades (contacto_id,modelo, version, ano, tipo_pintura, color_exterior, color_interior,modelo_id)
                    VALUES ('%s','%s', '', '','', '', '','%d')", $contacto_id, $modelo,$modelo_id);
    $db->sql_query($sql) or die("$sql<br>Error al insertar contacto 2 " . print_r($db->sql_error()));
    
    $sql = "select intentos from crm_campanas_llamadas_no_asignados where contacto_id = '$viejoContactoId'";
    $result = $db->sql_query($sql) or die($sql);
    list($intentos) = $db->sql_fetchrow($result);
    
    //para agregarlo a crm_campanas_llamadas
    $sql = "insert into crm_campanas_llamadas  (campana_id, contacto_id,intentos) values ('$campana_id', '$contacto_id','$intentos')";
    $db->sql_query($sql) or die("Error al insertar a la campaña 1 " . print_r($db->sql_error()));
    //guardar el log de asignacion 
    $sql = sprintf("INSERT INTO crm_contactos_asignacion_log (contacto_id, uid, from_uid, to_uid, from_gid, to_gid)
            VALUES('%s','0','0','0','0','%s')", $contacto_id, $res['gid']);
    $db->sql_query($sql) or die("Error");
    
    //VERIFICAR SI HA SIDO EL PRIMER CONTACTO, SINO, INSERTARLO
    if($res["primer_contacto"] == '0000-00-00 00:00:00')
    $res["primer_contacto"] = date("Y-m-d H:i:s");
        
    $sql = sprintf("INSERT INTO crm_contactos_no_asignados_finalizados
	        (`contacto_id`, `uid`,`gid`,`origen_id`,`origen_contacto_id`,
	        `nombre`,`apellido_paterno`,`apellido_materno`,`sexo`,`compania`,
	        `cargo`,`tel_casa`,`tel_oficina`,`tel_movil`,`tel_otro`,
	        `email`,`domicilio`,`colonia`,`cp`,`poblacion`,
	        `entidad_id`,`rfc`,`curp`,`persona_moral`,`fecha_de_nacimiento`,
	        `ocupacion`,`edo_civil`,`nombre_conyugue`,`nota`,`no_contactar`,
	        `prospecto`,`fecha_importado`,`titulo`, `sector`,
	        `pais`,`ciudad`,`proximo_contacto`,`primer_contacto`,`medio_contacto`,
	        `motivo_fin`, fecha_alta, prioridad,tel_casa_2,tel_oficina_2,tel_movil_2,
            horario_preferido_casa,horario_preferido_oficina,horario_preferido_movil,horario_preferido_casa_2,
            horario_preferido_oficina_2,horario_preferido_movil_2) VALUES(
	        '%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s',
	        '%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s',
	        '%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s',
            '%s','%s','%s','%s','%s','%s')", $res["contacto_id"], $uid, $res["gid"], $res["origen_id"], $res["contacto_id"], $res["nombre"], 
    		$res["apellido_paterno"], $res["apellido_materno"], $res["sexo"], $res["compania"], $res["cargo"], $res["tel_casa"], 
    		$res["tel_oficina"], $res["tel_movil"], $res["tel_otro"], $res["email"], $res["domicilio"], $res["colonia"], $res["cp"], 
    		$res["poblacion"], $res["entidad_id"], $res["rfc"], $res["curp"], $res["persona_moral"], $res["fecha_de_nacimiento"], 
    		$res["ocupacion"], $res["estado_civil"], $res["nombre_conyugue"], $res["nota"], $res["no_contactar"], $res["prospecto"], 
    		$res["fecha_importado"], $res["titulo"], $res["sector"], $res["pais"], $res["ciudad"], $res["proximo_contacto"], 
    		$res["primer_contacto"], $res["medio_contacto"], "Se asigno", $res["fecha_alta"], $res["prioridad"],
    		$res['tel_casa_2'],  $res['tel_oficina_2'], $res['tel_movil_2'], $res['horario_preferido_casa'],
            $res['horario_preferido_oficina'], $res['horario_preferido_movil'],  $res['horario_preferido_casa_2'], $res['horario_preferido_oficina_2'],
            $res['horario_preferido_movil_2']);
    $result = $db->sql_query($sql) or die("Error al consultar datos del contacto ".$sql);
    
    $sql = "DELETE FROM crm_contactos_no_asignados WHERE contacto_id='$contacto_id_eliminar'";
    $result = $db->sql_query($sql);
    
    $sql = "DELETE FROM crm_prospectos_unidades_no_asignados WHERE contacto_id='$contacto_id_eliminar'";
    $result = $db->sql_query($sql);
    
    //se guarda en crm_contactos_no_asignados_log el evento de asignar a un concesionarios
    $sql = "INSERT INTO crm_contactos_no_asignados_log (contacto_id, contacto_id_eliminado, uid, prioridad_id, canal_recepcion_id) VALUES ('$contacto_id', '$contacto_id_eliminar', '$uid', '$prioridad_id', '$canal_recepcion_id')";
    $result = $db->sql_query($sql);
    
    //-----------------------------------------------------------------------------------------
    // HISTORIAL DE CONTACTOS
    $sql = "INSERT INTO crm_historial_contactos(
    	contacto_id,
    	nombre,
    	primer_apellido,
    	segundo_apellido,
    	fecha_alta,
    	primer_contacto,
    	envia_concesionaria,
    	prioridad,
    	uid	
    )
    values(
    	'$contacto_id_eliminar',
    	'$res[nombre]',
    	'$res[apellido_paterno]',
    	'$res[apellido_materno]',
    	'$res[fecha_importado]',
    	'$res[primer_contacto]',
    	1,
    	'$prioridad_id',
    	'$uid'
    )
    ";
    $db->sql_query($sql) or die("Error:<br>$sql");
    header("location:index.php?_module=Directorio&_op=contacto&contacto_id=$contacto_id&last_module=$_module&last_op=$_op");
    exit();
}
/*
  * Si action es igual a b, significa que acaba de eliminar a el registro,
  * pero este registro se pasa a la tabla de registros finalizados no asignados
*/
if($_action == 'b')
{
    $res = array();
    $sql = "SELECT * FROM crm_contactos_no_asignados WHERE contacto_id='$contacto_id' LIMIT 1";
    $result = $db->sql_query($sql) or die("Error al consultar datos del contacto ".$sql);
    $res = htmlize($db->sql_fetchrow($result));
    
    //VERIFICAR SI HA SIDO EL PRIMER CONTACTO, SINO, INSERTARLO
    if($res["primer_contacto"] == '0000-00-00 00:00:00')
        $res["primer_contacto"] = date("Y-m-d H:i:s");

    $sql = sprintf("INSERT INTO crm_contactos_no_asignados_finalizados
	       (contacto_id,uid,gid,origen_id,origen_contacto_id,nombre,apellido_paterno,apellido_materno,sexo,
            compania,cargo,tel_casa,tel_oficina,tel_movil,tel_otro,email,domicilio,colonia,cp,poblacion,entidad_id,
            rfc,curp,persona_moral,fecha_de_nacimiento,ocupacion,edo_civil,nombre_conyugue,nota,no_contactar,
	        prospecto,fecha_importado,titulo,sector,pais,ciudad,proximo_contacto,primer_contacto,medio_contacto,
	        prioridad,motivo_fin,fecha_alta,tel_casa_2,tel_oficina_2,tel_movil_2,horario_preferido_casa,
            horario_preferido_oficina,horario_preferido_movil,horario_preferido_casa_2,horario_preferido_oficina_2,
            horario_preferido_movil_2) VALUES('%s','%s','%s','%s','%s','%s','%s','%s','%s','%s',
	        '%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s',
	        '%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s',
            '%s')", $res["contacto_id"], $uid, $res["gid"], $res["origen_id"], $res["contacto_id"], $res["nombre"], 
    		$res["apellido_paterno"], $res["apellido_materno"], $res["sexo"], $res["compania"], $res["cargo"], $res["tel_casa"], 
    		$res["tel_oficina"], $res["tel_movil"], $res["tel_otro"], $res["email"], $res["domicilio"], $res["colonia"], $res["cp"], 
    		$res["poblacion"], $res["entidad_id"], $res["rfc"], $res["curp"], $res["persona_moral"], $res["fecha_de_nacimiento"], 
    		$res["ocupacion"], $res["estado_civil"], $res["nombre_conyugue"], $res["nota"], $res["no_contactar"], $res["prospecto"], 
    		$res["fecha_importado"], $res["titulo"], $res["sector"], $res["pais"], $res["ciudad"], $res["proximo_contacto"], 
    		$res["primer_contacto"], $res["medio_contacto"], $res["prioridad"], $motivo_baja,$res["fecha_alta"],    		
    		$res['tel_casa_2'],  $res['tel_oficina_2'], $res['tel_movil_2'], $res['horario_preferido_casa'],
            $res['horario_preferido_oficina'], $res['horario_preferido_movil'],  $res['horario_preferido_casa_2'], $res['horario_preferido_oficina_2'],
            $res['horario_preferido_movil_2']
    		
    		);
    $result = $db->sql_query($sql) or die("Error al consultar datos del contacto  ".$sql);
    if(!$result)
    {
        echo "<script language=\"javascript\" type=\"text/javascript\">
		        alert('El registro no se pudo elminar');
		      </script>";
    } else
    {
        $sql = "DELETE FROM crm_contactos_no_asignados WHERE contacto_id='$contacto_id'";
        $result = $db->sql_query($sql) or die("Error al consultar datos del contacto  ".$sql);
        if(!$result)
            $jscript .= "
		        alert('El registro no se pudo elminar');";
        else
            $jscript .= "alert('Registro dado de baja');";
    }
    
    //-----------------------------------------------------------------------------------------
    // HISTORIAL DE CONTACTOS
    $sql = "INSERT INTO crm_historial_contactos(
    	contacto_id,
    	nombre,
    	primer_apellido,
    	segundo_apellido,
    	fecha_alta,
    	primer_contacto,
    	elimina,
    	motivo_fin,
    	uid	
    )
    values(
    	'$res[contacto_id]',
    	'$res[nombre]', 
    	'$res[apellido_paterno]', 
    	'$res[apellido_materno]',
    	'$res[fecha_importado]',
    	'$res[primer_contacto]',
    	1,
    	'$motivo_baja',
    	'$uid'
    )
    ";
    $db->sql_query($sql) or die("Error:<br>$sql");
    $contacto_id = "";
}// fin del modulo de eliminar
else if($_action == 'e') // SE REAGENDA
{
	$sql = "select intentos from crm_campanas_llamadas_no_asignados where contacto_id = '$contacto_id'";
	$result = $db->sql_query($sql) or die($sql);
	list($intentos) = $db->sql_fetchrow($result);
	$intentos++;
    $proximo_contacto = sprintf("%s %s:%s:00", date_reverse($fecha_cita), $hora_cita, $minuto_cita);
    $sql = sprintf("UPDATE crm_campanas_llamadas_no_asignados SET `lock` = 0, comentario = '%s',
	                fecha_cita = '%s', intentos='%s', user_id = '%s' 
	                WHERE contacto_id = %s", $evento_comentario, $proximo_contacto, $intentos, $uid, $contacto_id);
    $result = $db->sql_query($sql) or die($sql);    
    $sql = sprintf("INSERT INTO crm_campanas_llamadas_no_asignados_log
	                 (contacto_id, user_id , status ) VALUES ('%s','%s','%s')
	                ", $contacto_id, $uid, '0');
    $result = $db->sql_query($sql) or die($sql);
    
    $sql = sprintf("UPDATE crm_contactos_no_asignados SET motivo_reagenda_id = '%s'
	                WHERE contacto_id = %s",$_REQUEST["razon_reagenda"], $contacto_id);
    $result = $db->sql_query($sql) or die($sql);

    $sql = "select primer_contacto from crm_contactos_no_asignados where contacto_id = '$contacto_id'";
    $cs = $db->sql_query($sql) or die($sql);
    list($primer_contacto) = $db->sql_fetchrow($cs);
    if($primer_contacto == '0000-00-00 00:00:00')
        $fecha_actual=date("Y-m-d H:i:s");
    $db->sql_query("update crm_contactos_no_asignados set primer_contacto ='".$fecha_actual."' where contacto_id = '$contacto_id'");

    $sql = "select nombre, apellido_paterno, apellido_materno, fecha_importado, primer_contacto from crm_contactos_no_asignados where contacto_id = '$contacto_id'";
    $cs = $db->sql_query($sql) or die($sql);
    list($nombre, $primer_apellido, $segundo_apellido, $fecha_importado, $primer_contacto) = $db->sql_fetchrow($cs);

    // HISTORIAL DE CONTACTOS
    $sql = "INSERT INTO crm_historial_contactos(contacto_id,nombre,primer_apellido,segundo_apellido,fecha_alta,
            primer_contacto,reagenda,motivo_reagenda,uid) values ('$contacto_id','$nombre','$primer_apellido',
            '$segundo_apellido','$fecha_importado','$primer_contacto',1,'$_REQUEST[razon_reagenda]','$uid')";
    $db->sql_query($sql) or die("Error:<br>$sql");

    
    header("Location: index.php?_module=$_module&_op=$_op&nopendientes=1");
    $contacto_id = "";
}
 /*
  * else if($_action == 'nc')
{
    $sql = "SELECT HOUR(NOW())";
    $result = $db->sql_query($sql) or die($sql);
    list($hora_actual) = $db->sql_fetchrow($result);
    if($hora_actual < 10)
        $hora_actual = "0" . $hora_actual;
    $sql = "SELECT NOW()";
    $result = $db->sql_query($sql) or die($sql);
    list($proximo_contacto) = $db->sql_fetchrow($result);
    $hora_mas = $hora_actual + 3;
    $proximo_contacto = str_replace(sprintf(" %s:", $hora_actual), sprintf(" %s:", $hora_mas), $proximo_contacto);
    $sql = sprintf("UPDATE crm_campanas_llamadas_no_asignados SET `lock` = 0, comentario = '%s',
	                fecha_cita = '%s', intentos=intentos+1, user_id = '%s' 
	                WHERE llamada_id = %s", $evento_comentario, $proximo_contacto, $uid, $llamada_id);die($sql);
    $result = $db->sql_query($sql) or die($sql);
    
    $sql = sprintf("INSERT INTO crm_campanas_llamadas_no_asignados_log
	                 (contacto_id, user_id , status ) VALUES ('%s','%s','%s')
	                ", $contacto_id, $uid, '0');
    $result = $db->sql_query($sql) or die($sql);
    
    $sql = sprintf("UPDATE crm_contactos_no_asignados SET fecha_importado = NOW() 
	                WHERE contacto_id = %s", $contacto_id);
    $result = $db->sql_query($sql) or die($sql);
    $contacto_id = "";
}*
 */
/*********************LOCK********************
$sql = "UPDATE `crm_campanas_llamadas_no_asignados`
        SET `lock` = 0
        WHERE llamada_id = '".$llamada_id."'";
$result = $db->sql_query($sql) or die($sql);
 *********************************************/

$_title = "Contacto no asignado";
$unidades = array();
$par = array("(", ")");

if($contacto_id != "")
{
    $sql = "SELECT campana_id, comentario, fecha_cita, llamada_id, intentos
        FROM  crm_campanas_llamadas_no_asignados 
        WHERE contacto_id = $contacto_id";
    $result_cid = $db->sql_query($sql) or die($sql);
    list($campana_id, $comentario, $fecha_cita, $llamada_id, $num_intentos) = $db->sql_fetchrow($result_cid);
}
else
{
    $sql = "SELECT c.contacto_id, l.campana_id, l.comentario, l.fecha_cita, l.llamada_id, l.intentos
        FROM crm_contactos_no_asignados AS c, crm_campanas_llamadas_no_asignados AS l 
        WHERE c.contacto_id = l.contacto_id AND l.lock = 0 AND NOT(l.fecha_cita > NOW()) ORDER BY l.intentos, c.timestamp ASC";
    $result_cid = $db->sql_query($sql) or die($sql);
    list($contacto_id, $campana_id, $comentario, $fecha_cita, $llamada_id, $num_intentos) = $db->sql_fetchrow($result_cid);
}
if($llamada_id)
    $llamada_id_o = $llamada_id;
    
    
//////////////////// LOCK /////////////////////
//chekar si está lockeado
$sql = "SELECT `lock`, timestamp, user_id FROM crm_campanas_llamadas_no_asignados WHERE llamada_id='$llamada_id'";
$result = $db->sql_query($sql) or die("Error en lock" . print_r($db->sql_error())."<br>".$sql);
list($lock, $timestamp, $uid_lock) = htmlize($db->sql_fetchrow($result));
list($dia_ts, $hora_ts) = explode(" ", $timestamp);

$hoy = date("Y-m-d");
if($lock == 1 && $uid_lock != $uid && $dia_ts == $hoy)
    die("<script>
        setTimeout('location.href=\"index.php?_module=$_module&_op=$_op&nopendientes=1\"',3000);
        </script><center>Error: este registro está siendo usado.<br><a href=\"index.php?_module=$_module&_op=$_op&nopendientes=1\">Continuar</a></center>");

//lockearla para ke nadie más la llame, guardar kien lockeo el registro para ke kuando esta misma persona 
//entre a otro registro se deslockee este.

$sql = "UPDATE crm_campanas_llamadas_no_asignados SET `lock`='0' WHERE llamada_id='$llamada_id_o' AND `lock`='1'";
$db->sql_query($sql) or die("Error en lock" . print_r($db->sql_error()));
if($_action == 'n')
{
    $contacto_id = "";
}
else if($_action == 'u')
{
    if($lada1)
        $tel_casa = "(" . $lada1 . ") " . $tel_casa;
    if($lada2)
        $tel_oficina = "(" . $lada2 . ") " . $tel_oficina;
    if($lada3)
        $tel_movil = "(" . $lada3 . ") " . $tel_movil;
    if($lada4)
        $tel_otro = "(" . $lada4 . ") " . $tel_otro;
        
    if($lada_casa_2) $tel_casa_2 = "(" . $lada_casa_2 . ") " . $tel_casa_2;
    if($lada_oficina_2) $tel_oficina_2 = "(" . $lada_oficina_2 . ") " . $tel_oficina_2;
    if($lada_movil_2) $tel_movil_2 = "(" . $lada_movil_2 . ") " . $tel_movil_2;
   
    $horario_casa = (is_array($horario_casa)) ? $horario_casa : array();
    $horario_casa_2 = (is_array($horario_casa_2)) ? $horario_casa_2 : array();
    $horario_oficina = (is_array($horario_oficina)) ? $horario_oficina : array();
    $horario_oficina_2 = (is_array($horario_oficina_2)) ? $horario_oficina_2 : array();
    $horario_celular = (is_array($horario_celular)) ? $horario_celular : array();
    $horario_celular_2 = (is_array($horario_celular_2)) ? $horario_celular_2 : array();
    
    $horario_preferido_casa = implode('',$horario_casa);
    $horario_preferido_casa_2 = implode('',$horario_casa_2);    
    
    $horario_preferido_oficina = implode('',$horario_oficina);
    $horario_preferido_oficina_2 = implode('',$horario_oficina_2);
    
    $horario_preferido_movil = implode('',$horario_celular);
    $horario_preferido_movil_2 = implode('',$horario_celular_2);
    
    $sql = sprintf("update crm_contactos_no_asignados set
	        nombre = '%s', apellido_paterno = '%s', apellido_materno = '%s',
	        tel_casa = '%s', tel_oficina = '%s', tel_movil = '%s',
	        tel_otro = '%s', email = '%s',
	        poblacion = '%s', entidad_id = '%s', gid = '%s',
	        nota = '%s', medio_contacto = '%s', prioridad = '%s',	        
	        tel_casa_2 = '%s', tel_oficina_2 = '%s', tel_movil_2 = '%s',
	        horario_preferido_casa = '%s', horario_preferido_oficina = '%s', horario_preferido_movil = '%s',
	        horario_preferido_casa_2 = '%s', horario_preferido_oficina_2 = '%s', horario_preferido_movil_2 = '%s'
	        where contacto_id = '%s'", $nombre, $apellido_paterno, $apellido_materno, 
            $tel_casa, $tel_oficina, $tel_movil, $tel_otro,
            $email, $poblacion, $entidad_federativa_id,
            $gid, $nota, $medio_contacto, $prioridad_id,
            $tel_casa_2, $tel_oficina_2, $tel_movil_2,
            $horario_preferido_casa, $horario_preferido_oficina, $horario_preferido_movil,
            $horario_preferido_casa_2, $horario_preferido_oficina_2, $horario_preferido_movil_2,
            $contacto_id);
    
    $r1 = $db->sql_query($sql) or die($sql);
    $sql = sprintf("update crm_campanas_llamadas_no_asignados set
	        `lock` = 0, user_id = '%s' where contacto_id = %s", $uid, $contacto_id);

    $r2 = $db->sql_query($sql) or die($sql);
    if($r2 && $r1)
    {
        $jscript .= "alert('El registro se modificó correctamente');";
    }
    $sql = "update crm_prospectos_unidades_no_asignados set modelo='$_REQUEST[modelo]' where contacto_id='$contacto_id'";
    $db->sql_query($sql);
}

$sql = "SELECT nombre, apellido_paterno,apellido_materno, tel_casa, 
            tel_movil, tel_oficina,tel_otro, email, gid, entidad_id,poblacion,
            nota, medio_contacto, fecha_importado, fecha_alta, primer_contacto, prioridad,
            tel_casa_2, tel_oficina_2, tel_movil_2, 
            horario_preferido_casa, horario_preferido_oficina, horario_preferido_movil, 
            horario_preferido_casa_2, horario_preferido_oficina_2, horario_preferido_movil_2
	        FROM crm_contactos_no_asignados WHERE contacto_id='$contacto_id' LIMIT 1";

$result = $db->sql_query($sql) or die("Error al consultar datos del contacto  ".$sql);
    list($nombre, $apellido_paterno, $apellido_materno, $tel_casa, $tel_movil, $tel_oficina, $tel_otro, $email, $gid, $entidad_id, $municipio_origen, $nota, $medio_contacto, $fecha_importado, $fecha_alta, $primer_contacto, $prioridad_id,
    $tel_casa_2, $tel_oficina_2, $tel_movil_2,
    $horario_preferido_casa, $horario_preferido_oficina, $horario_preferido_movil,
    $horario_preferido_casa_2, $horario_preferido_oficina_2, $horario_preferido_movil_2) = htmlize($db->sql_fetchrow($result));

    list($lada1, $tel_casa) = explode(" ", str_replace($par, "", $tel_casa));
    list($lada3, $tel_movil) = explode(" ", str_replace($par, "", $tel_movil));
    list($lada2, $tel_oficina) = explode(" ", str_replace($par, "", $tel_oficina));
    list($lada4, $tel_otro) = explode(" ", str_replace($par, "", $tel_otro));
    list($lada_casa_2, $tel_casa_2) = explode(" ", str_replace($par, "", $tel_casa_2));
    list($lada_oficina_2, $tel_oficina_2) = explode(" ", str_replace($par, "", $tel_oficina_2));
    list($lada_movil_2, $tel_movil_2) = explode(" ", str_replace($par, "", $tel_movil_2));

    list($horario_casa_manana_checked,$horario_casa_tarde_checked,$horario_casa_noche_checked) = get_horario($horario_preferido_casa);
    list($horario_casa_manana_checked_2,$horario_casa_tarde_checked_2,$horario_casa_noche_checked_2) = get_horario($horario_preferido_casa_2);
    list($horario_oficina_manana_checked,$horario_oficina_tarde_checked,$horario_oficina_noche_checked) = get_horario($horario_preferido_oficina);
    list($horario_oficina_manana_checked_2,$horario_oficina_tarde_checked_2,$horario_oficina_noche_checked_2) = get_horario($horario_preferido_oficina_2);
    list($horario_celular_manana_checked,$horario_celular_tarde_checked,$horario_celular_noche_checked) = get_horario($horario_preferido_movil);
    list($horario_celular_manana_checked_2,$horario_celular_tarde_checked_2,$horario_celular_noche_checked_2) = get_horario($horario_preferido_movil_2);

/*    if($nombre == '' && $apellido_materno == '' && $apellido_paterno == '')
    {
        #die('Error al consultar los datos del contacto, estan vacios');
        echo 'Error al consultar los datos del contacto, estan vacios';
    }*/

    $select_entidad_federativa_id = "<select name=\"entidad_federativa_id\" id=\"entidad_federativa_id\" onChange=\"obtenerMunicipios();\">";
    $select_entidad_federativa_id .= " <option value=\"\" selected>Seleccione uno</option>\n";
    $i = 0;
    foreach($_entidades_federativas as $entidad_federativa)
    {
        $i++;
        if($entidad_id == $i)
            $select_entidad_federativa_id .= "<option value=\"$i\" selected>$entidad_federativa</option>";
        else
            $select_entidad_federativa_id .= "<option value=\"$i\">$entidad_federativa</option>";
    }
    $select_entidad_federativa_id .= "</select>";

    $select_medio_contacto = select_medio_contacto($medio_contacto);
    $sql = "SELECT modelo,version,paquete ,tipo_pintura,color_exterior,color_interior
                FROM crm_prospectos_unidades_no_asignados WHERE contacto_id='$contacto_id' LIMIT 1";
    $result = $db->sql_query($sql) or die("Error al consultar datos del contacto  ".$sql);
    list($modelo, $version, $paquete, $tipo_pintura, $color_exterior, $color_interior) = htmlize($db->sql_fetchrow($result));

    //select de modelo
    $sql = "SELECT nombre from crm_unidades order by nombre asc";
    $r = $db->sql_query($sql) or die($sql);
    while(list($mod) = $db->sql_fetchrow($r))
    {
        $modelos[] = $mod;
    }
    $select_modelo = select_array("modelo", $modelos, $modelo);
    $sql = "SELECT motivo_id, motivo from crm_prospectos_cancelaciones_motivos order by motivo_id asc";
    $r = $db->sql_query($sql) or die($sql);
    $motivo_baja = "<select name=\"motivo_baja\" id=\"motivo_baja\">
                     <option value=\"0\">Seleccione uno</option>\n";
    while(list($id_mot, $mot) = $db->sql_fetchrow($r))
    {
        $motivo_baja .= "<option value=\"$id_mot\">$mot</option>\n";
    }
    $motivo_baja .= "</select>";

    if($fecha_cita == "0000-00-00 00:00:00")
        $fecha_cita = "";
    list($fecha_cita, $hora) = explode(" ", $fecha_cita);
    list($hora_cita, $minuto_cita) = explode(":", $hora);
    $hidden_hora_cita = "<input type='hidden' id='hidden_hora_cita' value='$hora_cita'>";
    $hidden_hora_cita .= "<input type='hidden' id='hidden_minuto_cita' value='$minuto_cita'>";
    $select_hora = "<select name=\"hora_cita\" id=\"hora_cita\">";
    for($h = 0; $h < 24; $h++)
    {
        $h2 = sprintf("%02d", $h);
        if($h == $hora_cita)
            $sel = " SELECTED";
        else
            $sel = "";
        $select_hora .= "<option$sel>$h2</option>";
    }
    $select_hora .= "</select>";
    $select_minuto = "<select name=\"minuto_cita\" id=\"minuto_cita\">";
    for($m = 0; $m < 60; $m += 5)
    {
        $m2 = sprintf("%02d", $m);
        if($m == $minuto_cita)
            $sel = " SELECTED";
        else
            $sel = "";
        $select_minuto .= "<option$sel>$m2</option>";
    }
    $select_minuto .= "</select>";

    $fecha_cita = date_reverse($fecha_cita);

    $sql = "SELECT gid, name FROM groups";
    $r = $db->sql_query($sql) or die($sql);
    $groups = array();
    while(list($id, $n) = $db->sql_fetchrow($r))
    {
        if($gid == $id)
        {
            $concesionaria_origen = $n;
            break;
        }
    }
    $option_concesionaria = "<option value=\"$gid\">$concesionaria_origen</option>";
    $option_municipio = "<option value=\"$municipio_origen\">$municipio_origen</option>";

    $sql = "UPDATE crm_campanas_llamadas_no_asignados SET `lock` = '1', user_id = '$uid' WHERE `contacto_id` = '$contacto_id'";
    $db->sql_query($sql) or die($sql);

    if($siguiente)
    {
        $sql = "UPDATE crm_campanas_llamadas_no_asignados SET `lock` = 0 WHERE contacto_id = '$siguiente'";
        $result = $db->sql_query($sql) or die($sql);
    }

    $sql = "Select prioridad_id, valor, prioridad from crm_prioridades_contactos";
    $result = $db->sql_query($sql) or die($sql);
    $array_prioridades = array();
    while (list($id_prioridad, $valor, $nombre_prioridad) = $db->sql_fetchrow($result))
        $array_prioridades[$id_prioridad] = $nombre_prioridad . " (" . str_replace("_", " ", $valor) . ")";
    $select_prioridad = select_array_assoc("prioridad_id", $array_prioridades, $prioridad_id);

    $sql = "Select canal_recepcion_id, nombre from crm_contactos_no_asignados_canales_recepcion";
    $result = $db->sql_query($sql) or die($sql);
    $array_canales = array();
    while (list($id_canal_recepcion, $nombre_canal) = $db->sql_fetchrow($result))
        $array_canales[$id_canal_recepcion] = $nombre_canal;
    $select_canal_recepcion = select_array_assoc("canal_recepcion_id", $array_canales, $canal_recepcion_id);

    list($dia_imp, $hr_imp) = explode(" ", $fecha_importado);
    $fecha_importado = date_reverse($dia_imp)." ".$hr_imp;
    list($dia_alta, $hr_alta) = explode(" ",$fecha_alta);
    $fecha_asignado = date_reverse($dia_alta)." ".$hr_alta;

//RAZONES DE REAGENDAR
    $sql = "select motivo_reagenda_id from crm_contactos_no_asignados where contacto_id = '$contacto_id'";
    list($motivo_reagenda_id) = $db->sql_fetchrow($db->sql_query($sql));
    $sql = "select motivo_id, motivo from crm_motivos_reagenda";
    $cs = $db->sql_query($sql);
    while(list($motivo_id,$motivo) = $db->sql_fetchrow($cs))
    {
        if($motivo_reagenda_id == $motivo_id)
            $select = " selected";
        else
            unset($select);
        $razon_reagenda .= "<option value=\"$motivo_id\" $select>$motivo</option>";
    }

    $sql = "delete from crm_historial_contactos where contacto_id = 0";
    $db->sql_query($sql);
    unset($select);

function Revisa_modelo($db,$modelo)
{
    $id=0;
    $sql="SELECT unidad_id FROM crm_unidades where upper(nombre)='".$modelo."';";
    $res=$db->sql_query($sql) or die("Error en el query:  ".$sql);
    if($db->sql_numrows($res)>0)
        $id=$db->sql_fetchfield(0,0,$res);
    return $id;
}
function get_horario($string){
    $return = array(
    (eregi('M',$string) ? 'checked="checked"' : ''),
    (eregi('T',$string) ? 'checked="checked"' : ''),
    (eregi('N',$string) ? 'checked="checked"' : '')
    );
    return $return;
}

$_site_title = "Filtrar";

?>