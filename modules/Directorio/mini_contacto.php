<?
  if (!defined('_IN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}

global $db, $submit, $last_module, $close_after, $uid,
       $contacto_id, $campana_id,
       $nombre, $apellido_paterno, $apellido_materno,
       $lada1,$lada2,$lada3,$lada4,
       $tel_casa, $tel_oficina,
       $tel_movil, $tel_otro,
       $email,
	   $origen_id, $origen2_id, $origen_contacto_id,
    $modelo, 
       $nota;
$_css = $_themedir."/style.css";
$_theme = "";
$result = $db->sql_query("SELECT gid FROM users WHERE uid='$uid' LIMIT 1") or die("Error en grupo ".print_r($db->sql_error()));
list($gid) = $db->sql_fetchrow($result);

if (!$contacto_id) //nuevo
{
    $_title = "Contacto nuevo";
    if ($submit)
    {
        if ($lada1)
          $tel_casa = "($lada1) $tel_casa";
        if ($lada2)
          $tel_oficina = "($lada2) $tel_oficina";
        if ($lada3)
          $tel_movil = "($lada3) $tel_movil";
        if ($lada4)
          $tel_otro = "($lada4) $tel_otro";
        $sql = "INSERT INTO crm_contactos (
                    nombre, apellido_paterno, apellido_materno,
                    tel_casa, tel_oficina,
                    tel_movil, tel_otro,
                    email,
					origen_id,
					origen2_id,
					origen_contacto_id,
					gid,uid,
                    nota

                ) VALUES (
                    '$nombre', '$apellido_paterno', '$apellido_materno',
                    '$tel_casa', '$tel_oficina',
                    '$tel_movil', '$tel_otro',
                    '$email',
					'-4',
					'$origen2_id',
					'$origen_contacto_id',
					'$gid','$uid',
                    '$nota'
                )";//origen_id 4 por que es de piso
        $db->sql_query($sql) or die("$sql<br>Error al insertar contacto".print_r($db->sql_error()));
        $contacto_id = $db->sql_nextid();
        $sql = "INSERT INTO crm_prospectos_unidades (
                  contacto_id,
                  modelo, version, ano, 
                  tipo_pintura, color_exterior, color_interior
                ) VALUES (
                  '$contacto_id', 
                  '$modelo', '', '',
                  '', '', ''
                )";
        $db->sql_query($sql) or die("$sql<br>Error al insertar contacto".print_r($db->sql_error()));

        //buscar a que campaña lo meteremos
        $sql = "SELECT c.campana_id FROM crm_campanas_groups AS g, crm_campanas AS c WHERE c.campana_id=g.campana_id AND g.gid='$gid' ORDER BY c.campana_id  LIMIT 1"; //la primera que sea parte de un ciclo
        $result = $db->sql_query($sql) or die("Error al leer".print_r($db->sql_error()));
        list($campana_id) = $db->sql_fetchrow($result);
        //para agregarlo a crm_campanas_llamadas
        $sql = "insert into crm_campanas_llamadas  (campana_id, contacto_id) values ('$campana_id', '$contacto_id')";
        $db->sql_query($sql) or die("Error al insertar a la campaña".print_r($db->sql_error()));
        
        //guardar el log de asignacion 
        $sql = "INSERT INTO crm_contactos_asignacion_log (contacto_id, uid, from_uid, to_uid, from_gid, to_gid)VALUES('$contacto_id','0','0','$uid','0','$gid')";
        $db->sql_query($sql) or die("Error");

        if ($last_module == "Prospectos") //regresar a prospectos
        {
          $contacto_id = $db->sql_nextid();
          header("location:index.php?_module=Prospectos&_op=prospecto&contacto_id=$contacto_id");
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
    if (!$submit) //mostrar el actual para editarlo
    {
        $sql = "SELECT 
                    nombre, apellido_paterno, apellido_materno,
                    tel_casa, tel_oficina,
                    tel_movil, tel_otro,
                    email,
                    nota
                FROM crm_contactos WHERE contacto_id='$contacto_id' LIMIT 1";
        $result = $db->sql_query($sql) or die("Error al consultar datos del contacto");
        list(
                $nombre, $apellido_paterno, $apellido_materno,
                $tel_casa, $tel_oficina,
                $tel_movil, $tel_otro,
                $email,
                $nota,
            ) = htmlize($db->sql_fetchrow($result));
        list($ano, $mes, $dia) = explode("-", $fecha_de_nacimiento);
    }
    else
    {
        $sql = "UPDATE crm_contactos SET
                    nombre='$nombre', apellido_paterno='$apellido_paterno', apellido_materno='$apellido_materno',
                    tel_casa='$tel_casa', tel_oficina='$tel_oficina',
                    tel_movil='$tel_movil', tel_otro='$tel_otro',
                    email='$email',
                    nota='$nota'
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
if ($close_after)
{
  $cancelar_button = "<input value=\"Cancelar\" onclick=\"window.close();\" type=\"button\">";
  global $_no_boxes;
  $_no_boxes = 1;
}
else
  $cancelar_button = "<input value=\"Cancelar\" onclick=\"location.href='index.php?_module=$_module'\" type=\"button\">";



//los selects del form en html
require_once("$_includesdir/select.php");
//select de modelo
$sql = "SELECT nombre from crm_unidades order by nombre asc";
$r = $db->sql_query($sql) or die($sql);
while(list($mod) = $db->sql_fetchrow($r))
{
  $modelos[] = $mod;
}
$select_modelo = select_array("modelo", $modelos, $modelo);
?>
