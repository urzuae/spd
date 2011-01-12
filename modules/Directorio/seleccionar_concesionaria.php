<?
if (!defined('_IN_MAIN_INDEX'))
{

    die ("No puedes acceder directamente a este archivo...");
}
global $db,$submit,$last_module,$close_after,$uid,$contacto_id,$campana_id,$nombre,$apellido_paterno,
       $apellido_materno,$lada1,$lada2,$lada3,$lada4,$tel_casa, $tel_oficina,$tel_movil, $tel_otro,$email,
       $origen_id, $origen2_id, $origen_contacto_id,$modelo,$poblacion, $entidad_federativa_id,$nota, $gid,
       $guarda,$lada_casa_2, $lada_movil_2, $lada_oficina_2, $tel_casa_2,$tel_oficina_2, $tel_movil_2,
       $horario_casa,$horario_casa_2,$horario_oficina,$horario_oficina_2,$horario_celular,$horario_celular_2,
       $medio_contacto;

$gid_contacto = $gid;
$result = $db->sql_query("SELECT gid FROM users WHERE uid='$uid' LIMIT 1") or die("Error en grupo ".print_r($db->sql_error()));
list($gid) = $db->sql_fetchrow($result);
if ($gid != "1")
{
    die("No tiene acceso a este módulo");
}
if(!$contacto_id)
{
    $valida_existe = "valida_existente();";
}
else
{
    $valida_existe= "document.contacto.action = \"index.php\";
            document.contacto.guarda.value = \"1\";
            document.contacto.method = \"POST\";
            document.contacto.submit();";
}
if (!$contacto_id) //nuevo
{
    $_title = "Contacto nuevo";
    if ($guarda)
    {
        if ($lada1)
            $tel_casa = "($lada1) $tel_casa";
        if ($lada2)
            $tel_oficina = "($lada2) $tel_oficina";
        if ($lada3)
            $tel_movil = "($lada3) $tel_movil";
        if ($lada4)
            $tel_otro = "($lada4) $tel_otro";
        if($lada_casa_2)
            $tel_casa_2 = "(" . $lada_casa_2 . ") " . $tel_casa_2;
        if($lada_oficina_2)
            $tel_oficina_2 = "(" . $lada_oficina_2 . ") " . $tel_oficina_2;
        if($lada_movil_2)
            $tel_movil_2 = "(" . $lada_movil_2 . ") " . $tel_movil_2;

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

        /**  lo pasamos por no_asignados, para enviarlo a finalizados y al historial**/
        $sql = "INSERT INTO crm_contactos_no_asignados (nombre, apellido_paterno, apellido_materno,tel_casa, tel_oficina,
                tel_movil, tel_otro,email,origen_id,origen2_id,origen_contacto_id,gid,uid,nota,poblacion,
                entidad_id, medio_contacto, fecha_importado,tel_casa_2,tel_oficina_2,tel_movil_2,
                horario_preferido_casa,horario_preferido_oficina,horario_preferido_movil,horario_preferido_casa_2,
                horario_preferido_oficina_2,horario_preferido_movil_2)
                VALUES ('$nombre', '$apellido_paterno', '$apellido_materno','$tel_casa', '$tel_oficina',
                '$tel_movil', '$tel_otro','$email','-8','$origen2_id','$origen_contacto_id','$gid_contacto',
                '0','$nota','$poblacion','$entidad_federativa_id', '$medio_contacto',NOW(),'$tel_casa_2',
                '$tel_oficina_2','$tel_movil_2','$horario_preferido_casa','$horario_preferido_oficina',
                '$horario_preferido_movil','$horario_preferido_casa_2','$horario_preferido_oficina_2',
                '$horario_preferido_movil_2')";
        $db->sql_query($sql) or die("$sql<br>Error al insertar contacto".print_r($db->sql_error()));
        $contacto_id_no_asig = $db->sql_nextid();

        $sql = "SELECT * FROM crm_contactos_no_asignados WHERE contacto_id='$contacto_id_no_asig' LIMIT 1";
        $result = $db->sql_query($sql);
        $res = $db->sql_fetchrow($result);
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

            $sql = "DELETE FROM crm_contactos_no_asignados WHERE contacto_id='$contacto_id_no_asig'";
            $result = $db->sql_query($sql);

            $sql="INSERT INTO crm_historial_contactos (contacto_id,nombre,primer_apellido,segundo_apellido,
            fecha_alta,primer_contacto,reagenda,elimina,envia_concesionaria,motivo_reagenda,motivo_fin,
            prioridad,timestamp,uid,tipo_envio) VALUES ('$contacto_id_no_asig','$nombre', '$apellido_paterno', '$apellido_materno',
            NOW(),NOW(),'0','0','1','0','0','0',NOW(),'$uid','InBound');";
            $db->sql_query($sql) or die("Error al intoducir datos en la tabla de historial ".$sql);

        // Insertamos en la tabla de crm_contactos
        $sql = "INSERT INTO crm_contactos (nombre, apellido_paterno, apellido_materno,tel_casa, tel_oficina,
                tel_movil, tel_otro,email,origen_id,origen2_id,origen_contacto_id,gid,uid,nota,poblacion,
                entidad_id, medio_contacto, fecha_importado,tel_casa_2,tel_oficina_2,tel_movil_2,
                horario_preferido_casa,horario_preferido_oficina,horario_preferido_movil,horario_preferido_casa_2,
                horario_preferido_oficina_2,horario_preferido_movil_2)
                VALUES ('$nombre', '$apellido_paterno', '$apellido_materno','$tel_casa', '$tel_oficina',
                '$tel_movil', '$tel_otro','$email','-8','$origen2_id','$origen_contacto_id','$gid_contacto',
                '0','$nota','$poblacion','$entidad_federativa_id', '$medio_contacto',NOW(),'$tel_casa_2',
                '$tel_oficina_2','$tel_movil_2','$horario_preferido_casa','$horario_preferido_oficina',
                '$horario_preferido_movil','$horario_preferido_casa_2','$horario_preferido_oficina_2',
                '$horario_preferido_movil_2')";
        ////origen_id 4 por que es de piso
        $db->sql_query($sql) or die("$sql<br>Error al insertar contacto".print_r($db->sql_error()));
        $contacto_id = $db->sql_nextid();


        $modelo_nombre='';
        $sql_modelo="select nombre from crm_unidades where unidad_id=".$modelo.";";
        $res_modelo=$db->sql_query($sql_modelo) or die("Error en el modelo: ".$sql_modelo);
        if($db->sql_numrows($res_modelo)>0)
        {
            $modelo_nombre=$db->sql_fetchfield(0,0,$res_modelo);
        }

        // Insertamos en crm_prospectos_unidades
        $sql = "INSERT INTO crm_prospectos_unidades(contacto_id,modelo, version, ano,tipo_pintura,
                color_exterior, color_interior, modelo_id) VALUES ('$contacto_id','$modelo_nombre', '', '',
                  '', '', '', $modelo)";
        $db->sql_query($sql) or die("$sql<br>Error al insertar contacto".print_r($db->sql_error()));

        //buscar a que campaña lo meteremos
        $sql = "SELECT c.campana_id FROM crm_campanas_groups AS g, crm_campanas AS c WHERE c.campana_id=g.campana_id AND g.gid='$gid_contacto' ORDER BY c.campana_id  LIMIT 1"; //la primera que sea parte de un ciclo
        $result = $db->sql_query($sql) or die("Error al leer".print_r($db->sql_error()));
        list($campana_id) = $db->sql_fetchrow($result);
        //para agregarlo a crm_campanas_llamadas
        $sql = "insert into crm_campanas_llamadas  (campana_id, contacto_id) values ('$campana_id', '$contacto_id')";
        $db->sql_query($sql) or die("Error al insertar a la campaña".print_r($db->sql_error()));

        //guardar el log de asignacion
        $sql = "INSERT INTO crm_contactos_asignacion_log (contacto_id, uid, from_uid, to_uid, from_gid, to_gid)VALUES('$contacto_id','0','0','0','0','$gid_contacto')";
        $db->sql_query($sql) or die("Error");


        if ($close_after == "") //la página para agregar más info
        {
            header("location:index.php?_module=Directorio&_op=contacto&contacto_id=$contacto_id&last_module=$_module&last_op=$_op&listVehicle=$modelo");

        }
        else //este es un parche para agregarlo a una campaña inmediatamente
        {
            //           header("location:index.php?_module=$_module&_op=agregar_a_campana&close_after=1&contacto_id=".$db->sql_nextid());
            die("<html><head><title>CRM - Contacto agregado</title><script>alert('Datos guardados');window.close();</script></head></html>");
        }
    }
}
else //editar
{
    $readonly = "READONLY";
    if (!$guarda) //mostrar el actual para editarlo
    {
        $sql = "SELECT nombre, apellido_paterno, apellido_materno,tel_casa, tel_oficina,tel_movil, tel_otro,
                email,nota FROM crm_contactos WHERE contacto_id='$contacto_id' LIMIT 1";
        $result = $db->sql_query($sql) or die("Error al consultar datos del contacto");
        list($nombre, $apellido_paterno, $apellido_materno,$tel_casa, $tel_oficina,$tel_movil, $tel_otro,
            $email,$nota,) = htmlize($db->sql_fetchrow($result));
        list($ano, $mes, $dia) = explode("-", $fecha_de_nacimiento);
    }
    else
    {
        $sql = "UPDATE crm_contactos SET
                    nombre='$nombre', apellido_paterno='$apellido_paterno', apellido_materno='$apellido_materno',
                    tel_casa='$tel_casa', tel_oficina='$tel_oficina',tel_movil='$tel_movil', tel_otro='$tel_otro',
                    email='$email',nota='$nota',entidad_id='$entidad_federativa_id',poblacion='$poblacion'
                WHERE contacto_id='$contacto_id' LIMIT 1";
        $result = $db->sql_query($sql) or die("Error al actualizar datos del contacto".print_r($db->sql_error()));
        if ($close_after)
        die("<html><head><title>Cerrar</title><script>window.close();</script></head></html>");
        else
        header("location:index.php?_module=$_module");
    }
    list($f, $h) = explode(" ", $timestamp);
    $timestamp2 = date_reverse($f) ." $h";
    $_title = "Editando contacto - $nombre $apellido_paterno $apellido_materno - Última modificación: $timestamp2";
}
$_site_title .= " - $_title";

//los selects del form en html
require_once("$_includesdir/select.php");
//$sql = "SELECT nombre from crm_unidades order by nombre asc";
$sql = "SELECT unidad_id, nombre from crm_unidades order by nombre asc";
$r = $db->sql_query($sql) or die($sql);
$select_modelo = "<select name='modelo' id='modelo'>\n<option value='0'>Seleccione uno</option>\n";
while(list($modeloId,$mod) = $db->sql_fetchrow($r))
{
    $modelos[] = $mod;
    $select_modelo .= "<option value='$modeloId' >$mod</option>\n";
}
$select_modelo .= "</select>\n";
include_once("$_includesdir/select.php");
global $_entidades_federativas;
$select_entidad_federativa_id = "<select name=\"entidad_federativa_id\" id=\"entidad_federativa_id\" onChange=\"obtenerMunicipios();\">";
$select_entidad_federativa_id .= " <option value=\"\" selected=\"selected\">Seleccione uno</option>\n";
$i = 0;
foreach ($_entidades_federativas AS $entidad_federativa)
{
    $i++;
    $select_entidad_federativa_id .= "<option value=\"$i\">$entidad_federativa</option>";
}
$select_entidad_federativa_id .= "</select>";
$select_medio_contacto = select_medio_contacto("");
?>