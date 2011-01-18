<?
if (!defined('_IN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}
global $db, $uid, $how_many, $from, $campana_id, $contacto_id, $llamada_id, $submit, $status, $compania, 
		$nota, $fecha_cita, $hora_cita, $minuto_cita, $personal, $delalert, $ciclo_de_venta_id,
    $evento_id, $evento_tipo_id, $evento_comentario, 
		$next_campana_id,$finalizar;
	
header("Cache-Control: no-cache");
include_once("modules/Gerente/class_autorizado.php");
function html_entity_decode2( $given_html, $quote_style = ENT_QUOTES )
{
       $trans_table = array_flip(get_html_translation_table( HTML_SPECIALCHARS, $quote_style ));
       $trans_table['&#39;'] = "'";
       return ( strtr( $given_html, $trans_table ) );
}
if (!$campana_id)
    die("Por favor seleccione nuevamente una campaña.");

if($finalizar)
{
	$sql = "insert into crm_contactos_finalizados select * from crm_contactos where contacto_id = '$contacto_id'";
	$db->sql_query($sql) or die($sql);	
	$sql = "delete from crm_campanas_llamadas WHERE contacto_id='$contacto_id'";
	$db->sql_query($sql) or die($sql);
	$sql = "delete from crm_contactos WHERE contacto_id='$contacto_id'";
	$db->sql_query($sql) or die($sql);
	header("Location: index.php?_module=$_module&_op=$_op&campana_id=$campana_id");
}

//chekar si estamos autorizados
$result = $db->sql_query("SELECT gid FROM users WHERE uid='$uid' LIMIT 1") or die("Error en grupo ".print_r($db->sql_error()));
list($gid) = $db->sql_fetchrow($result);

$result = $db->sql_query("SELECT name FROM groups WHERE gid='$gid' LIMIT 1") or die("Error en grupo ".print_r($db->sql_error()));
list($grupo) = $db->sql_fetchrow($result);

if ($gid != 1)
    $where_gid = " AND gid='$gid'";

$sql = "SELECT campana_id FROM crm_campanas_groups  WHERE campana_id='$campana_id' $where_gid LIMIT 1";
$r = $db->sql_query($sql) or die("Error al obtener el ciclo de venta de esta distribuidora.<br>$sql");
$numrows = $db->sql_numrows($r);
if ($numrows < 1) 
{
	die("<html><head><script>alert('Está intentando dar seguimiento a un prospecto que no está asignado a esta distribuidora. Campaña: $campana_id. Distribuidora: $gid. $numrows');window.close();</script></head></html>");
}

$sql = "SELECT horas FROM groups_horas WHERE gid = '$gid'";
$result = $db->sql_query($sql) or die("Error en grupo ".print_r($db->sql_error()));
list($horas_diferencia) = $db->sql_fetchrow($result);

$_css = $_themedir."/css/".$_theme."/style.css";
$_theme = "";

//cambiar el formato de la fecha
if ($fecha_cita)
{
    $ff = date_reverse($fecha_cita);
    if (!$hora_cita) $hora_cita = "00";
    if (!$minuto_cita) $minuto_cita = "00";
    $fecha_cita = "$ff $hora_cita:$minuto_cita";
}

//AKI VA OPERACIONES ESPECIALES DEPENDIENDO KONSULTA
if ($next_campana_id) //se está actualizando el ciclo de prospección, solo guardar esto y no hacer buscar otro contacto
{
	//checar si la llamada está en status = 0, si es así, hay que cambiarla a en proceso, si no hay que mantener el status que tenía
  $sql = "SELECT status_id FROM crm_campanas_llamadas WHERE id='$llamada_id' LIMIT 1";
  $r2 = $db->sql_query($sql) or die("Error".print_r($db->sql_error()));
  list($status_id) = $db->sql_fetchrow($r2);
  if ($status_id == 0) $status_id = 1;
	$sql = "UPDATE crm_campanas_llamadas SET campana_id='$next_campana_id', status_id='$status_id' WHERE id='$llamada_id'";
	$db->sql_query($sql) or die("Error".print_r($db->sql_error()));

    $sql = "SELECT nombre FROM crm_campanas WHERE campana_id='$next_campana_id' LIMIT 1";
	$r2 = $db->sql_query($sql) or die("Error".print_r($db->sql_error()));
	list($next_campana) = $db->sql_fetchrow($r2);

//logear la aktividad
	$db->sql_query("INSERT INTO crm_campanas_llamadas_log (llamada_id, contacto_id, uid,status_id, campana_id)VALUES('$llamada_id', '$contacto_id', '$uid','$status_id','$next_campana_id')") or die("Error".print_r($db->sql_error()));
	$campana_id = $next_campana_id;
	//TODO: checar si tenemos permisos para la siguiente campana, si no regresar
}

//si posponemos, abrir un evento, si le quitamos terminamos el evento marcarlo como status=1
if ($submit && $status == -2) //queremos posponer
{
  //con los datos que tenemos crear un evento
  $comentario = addslashes($comentario);
  $sql = "INSERT INTO crm_campanas_llamadas_eventos (llamada_id,tipo_id,comentario,uid,fecha_cita) VALUES('$llamada_id','$evento_tipo_id','$evento_comentario','$uid','$fecha_cita')";
  $db->sql_query($sql) or die("Error".print_r($db->sql_error()));

  //status_id='-2' pospuesta
  $sql = "UPDATE crm_campanas_llamadas SET fecha_cita='$fecha_cita', intentos=intentos+1, status_id='-2', user_id='$uid' WHERE id='$llamada_id'";
  $db->sql_query($sql) or die("Error".print_r($db->sql_error()));

  //loggear
  $db->sql_query("INSERT INTO crm_campanas_llamadas_log (llamada_id, contacto_id, uid,status_id, campana_id)VALUES('$llamada_id', '$contacto_id', '$uid','-2','$campana_id')") or die("Error".print_r($db->sql_error()));
}
if ($evento_id) //ya está abierto un evento, checar si queremos cancelarlo, posponerlo y cerrarlo
{
  global $submit_cancelar_evento, $submit_posponer, $submit_registrar; //los botones de submit
  $msg_prosponer='';
  //borrar el evento, elimina también la cita y regresa status a status_id=0
  if ($submit_cancelar_evento)
  {
    global $evento_cierre_comentario;    
    //buscar los datos actuales para agregar a la nota
    $sql = "SELECT tipo_id, comentario FROM crm_campanas_llamadas_eventos WHERE llamada_id='$llamada_id' ORDER BY evento_id DESC"; //buscar el más viejo
    $result = $db->sql_query($sql) or die("Error al buscar tipo".print_r($db->sql_error()));
    list($evento_tipo_id, $evento_comentario)  = htmlize($db->sql_fetchrow($result));
    $sql = "SELECT nombre FROM crm_campanas_llamadas_eventos_tipos WHERE tipo_id='$evento_tipo_id'"; //buscar el más viejo
    $result = $db->sql_query($sql) or die("Error al buscar tipo".print_r($db->sql_error()));
    list($evento_tipo)  = htmlize($db->sql_fetchrow($result));
    if ($evento_cierre_status_id) $cierre_status = "Exitoso";
    else  $cierre_status = "No exitoso";
    $nota = html_entity_decode("$evento_tipo: $evento_comentario\nCancelado: $evento_cierre_comentario");
    //agregar a la nota, copiado de guardar_nota.php
    $nota2 = date("d-m-Y");
    $sql = "SELECT name FROM users WHERE uid='$uid' LIMIT 1";
    $r2 = $db->sql_query($sql) or die("Error".print_r($db->sql_error()));
    list($nombre_u) = $db->sql_fetchrow($r2);
    $nota2 .= ": $nombre_u";
    if ($campana_id)
    {
              $sql = "SELECT nombre FROM crm_campanas WHERE campana_id='$campana_id' LIMIT 1";
              $r2 = $db->sql_query($sql) or die("Error".print_r($db->sql_error()));
              list($campana) = $db->sql_fetchrow($r2);
        $nota2 .= ": $campana";
    }
    $nota = $nota2."\n".$nota."\n";
    $notanl = str_replace("\n","\\n", $nota);
    $sql = "UPDATE crm_contactos SET nota = concat(nota,'$nota') WHERE contacto_id='$contacto_id' AND uid='$uid'";
    $db->sql_query($sql) or die("Error".print_r($db->sql_error()));
    //fin de nota
    
  
    $sql = "DELETE FROM crm_campanas_llamadas_eventos WHERE evento_id='$evento_id'";
    $db->sql_query($sql) or die("Error".print_r($db->sql_error()));
    $sql = "UPDATE crm_campanas_llamadas SET fecha_cita='', intentos=intentos+1, status_id='1', user_id='$uid' WHERE id='$llamada_id'";
  //status_id='-2' pospuesta  
    $db->sql_query($sql) or die("Error".print_r($db->sql_error()));
    //loggear
    $db->sql_query("INSERT INTO crm_campanas_llamadas_log (llamada_id, contacto_id, uid,status_id, campana_id)VALUES('$llamada_id', '$contacto_id', '$uid','1','$campana_id')") or die("Error".print_r($db->sql_error()));  
    //unset fecha_cita
    unset($fecha_cita);
    unset($hora_cita);
    unset($minuto_cita);
    
  }
  //posponer la cita
  if ($submit_posponer)
  {
    $status = -2;
    //el evento se queda igual, solo cambiar las fechas
    $sql = "UPDATE crm_campanas_llamadas SET fecha_cita='$fecha_cita', intentos=intentos+1, status_id='-2', user_id='$uid' WHERE id='$llamada_id'";
    $db->sql_query($sql) or die("Error".print_r($db->sql_error()));
    $db->sql_query("INSERT INTO crm_campanas_llamadas_log (llamada_id, contacto_id, uid,status_id, campana_id)VALUES('$llamada_id', '$contacto_id', '$uid','$status','$campana_id')") or die("Error".print_r($db->sql_error()));
    $msg_prosponer="<center><div style='padding-right: 30px';><br><div style='background: #ffff99';><span style='font-size:12px;color:#FF0000;background-color:#ffff99;width:120px;'>Cita Pospuesta para la fecha indicada en el selector.</span></div><br><br></div></center>";
  }
  //cerrar el evento, elimina la cita y pone el status a status_id=1
  if ($submit_registrar)
  {
    global $evento_cierre_status_id, $evento_cierre_comentario;
    $status = 1;
    $evento_cierre_comentario = addslashes($evento_cierre_comentario);
    $sql = "INSERT INTO crm_campanas_llamadas_eventos_cierres (evento_id, status_id, comentario, uid) 
            VALUES('$evento_id', '$evento_cierre_status_id','$evento_cierre_comentario','$uid')";
    $db->sql_query($sql) or die("Error".print_r($db->sql_error()));
    $sql = "UPDATE crm_campanas_llamadas SET fecha_cita='', status_id='1', user_id='$uid' WHERE id='$llamada_id'";
  //status_id='-2' pospuesta  
    $db->sql_query($sql) or die("Error".print_r($db->sql_error()));
    //loggear
    $db->sql_query("INSERT INTO crm_campanas_llamadas_log (llamada_id, contacto_id, uid,status_id, campana_id)VALUES('$llamada_id', '$contacto_id', '$uid','$status','$campana_id')") or die("Error".print_r($db->sql_error()));  
    
    //buscar los datos actuales para agregar a la nota
    $sql = "SELECT tipo_id, comentario FROM crm_campanas_llamadas_eventos WHERE llamada_id='$llamada_id' ORDER BY evento_id DESC"; //buscar el más viejo
    $result = $db->sql_query($sql) or die("Error al buscar tipo".print_r($db->sql_error()));
    list($evento_tipo_id, $evento_comentario)  = htmlize($db->sql_fetchrow($result));

    $sql = "SELECT nombre FROM crm_campanas_llamadas_eventos_tipos WHERE tipo_id='$evento_tipo_id'"; //buscar el más viejo
    $result = $db->sql_query($sql) or die("Error al buscar tipo".print_r($db->sql_error()));
    list($evento_tipo)  = htmlize($db->sql_fetchrow($result));
    
    if ($evento_cierre_status_id)
        $cierre_status = "Exitoso";
    else
        $cierre_status = "No exitoso";

    $nota = html_entity_decode("$evento_tipo: $evento_comentario\n$cierre_status: $evento_cierre_comentario");
    //agregar a la nota, copiado de guardar_nota.php
    $nota2 = date("d-m-Y");
    $sql = "SELECT name FROM users WHERE uid='$uid' LIMIT 1";
    $r2 = $db->sql_query($sql) or die("Error".print_r($db->sql_error()));
    list($nombre_u) = $db->sql_fetchrow($r2);
    $nota2 .= ": $nombre_u";
    if ($campana_id) 
    {
        $sql = "SELECT nombre FROM crm_campanas WHERE campana_id='$campana_id' LIMIT 1";
        $r2 = $db->sql_query($sql) or die("Error".print_r($db->sql_error()));
        list($campana) = $db->sql_fetchrow($r2);
        $nota2 .= ": $campana";
    }
    $nota = $nota2."\n".$nota."\n";
    $notanl = str_replace("\n","\\n", $nota);
    $sql = "UPDATE crm_contactos SET nota = concat(nota,'$nota') WHERE contacto_id='$contacto_id' AND uid='$uid'";
    $db->sql_query($sql) or die("Error".print_r($db->sql_error()));
  }
}

//INICIALIZAR EL CONTACTO
if (!$contacto_id)//chekamos si tenemos un usuario o tenemos ke eskogerlo de forma inteligente
{
    //buskamos primero si no hay ke hacer una llamada en los próximos minutos
    global $nopendientes; //si esta seteado esto no buscar pendientes
    if (!$nopendientes) //solo entrar aki si no esta seteado, osea, entra a buscar siempre que se pueda
    {
    	//$horas_diferencia = -1;
        $en10mins = time() + (10 * 60) + (60*(60*$horas_diferencia));

        $sql = "SELECT l.id, c.contacto_id, c.nombre, c.apellido_paterno, c.apellido_materno, l.fecha_cita, l.campana_id FROM crm_campanas_llamadas AS l, crm_contactos AS c WHERE l.contacto_id=c.contacto_id AND c.uid='$uid' AND l.status_id='-2' "
        ."AND l.fecha_cita <= '".date("Y-m-d G:i:s", $en10mins)."' "//dentro de 10 minutos o antes
        ."AND campana_id='$campana_id' ORDER BY l.fecha_cita, c.nombre ASC ";
        $result = $db->sql_query($sql) or die("Error al buscar contacto");
        $cuantas_llamadas_pendientes = $db->sql_numrows($result);
        if ($cuantas_llamadas_pendientes > 0)
        {
            ////si encontramos algun pendiente preguntar si keremos llamarlo
            $lista = "<table>\n<thead><td>Nombre</td><td>Paterno</td><td>Materno</td><td>Cita</td><td></td></thead>";
            while (list($llamada_id, $contacto_id, $nombre, $ap_pat, $ap_mat, $cita, $campana_id_tmp) = $db->sql_fetchrow($result))
            {
                if ($cuantas_llamadas_limit++ > 20) break; //salir si se pasa del limite
                //cambiar el formato que se va a mostrar de la cita
                list($fecha, $hora) = explode (" ", $cita);
                if ($fecha == date("Y-m-d"))
                    $fecha = "Hoy";
                else
                {
                    list($ano, $mes, $dia) = explode ("-", $fecha);
                    $fecha = "$dia-$mes-$ano";
                }
                list($hh, $mm, $ss) = explode (":", $hora);
                $cita = "$fecha a las $hh:$mm";
                $lista .= "<tr class=\"row1\" style=\"cursor:pointer;\" onclick=\"window.open('index.php?_module=$_module&_op=$_op&campana_id=$campana_id_tmp&contacto_id=$contacto_id&llamada_id=$llamada_id','_self')\"><td>$nombre</td><td>$ap_pat</td><td>$ap_mat</td>"
                    ."<td>$cita</td>"
                    ."<td><a href=\"index.php?_module=$_module&_op=$_op&campana_id=$campana_id_tmp&contacto_id=$contacto_id&llamada_id=$llamada_id\"><img src=\"img/phone.gif\" border=0></a></td></tr>\n";
            }
            $lista .= "</table>\n";
            $_html = "<html><head><link type=\"text/css\" href=\"$_css\" rel=\"stylesheet\"></head><body>"
                    ."<center><h1>Hay compromisos pendientes ($cuantas_llamadas_pendientes):</h1><br>"
                    .$lista
                    ."<input type=button value=\"Ignorar\" onclick=\"location.href='index.php?_module=$_module&_op=$_op&campana_id=$campana_id&nopendientes=1'\">"
                    ."</center></body>";
            //no continuar hasta que se conteste
            die($_html);
        }
    }
    //si no pues entonces lo normal
    //buskar la primera llamada ke haya ke realizar
    //>=0 son por realizar
    //si esta lockeado hay ke chekar si no se dejo lockeado de un dia anterior y usar de todas formas
    $sql = "SELECT l.id, c.contacto_id FROM crm_campanas_llamadas AS l, crm_contactos AS c WHERE l.contacto_id=c.contacto_id AND c.uid='$uid'  AND l.campana_id='$campana_id' AND l.status_id!=-1 ORDER BY l.timestamp LIMIT 1";//quitar lock, solo una persona puede usarlo a la vez AND (l.`lock`=0 OR (l.`lock`!=0 AND l.timestamp NOT LIKE '".date("Y-m-d")."%'))
    $result = $db->sql_query($sql) 
        or die("<br>$sql<br>Error al buscar contacto<br>".print_r($db->sql_error()));
    list($llamada_id, $contacto_id) = htmlize($db->sql_fetchrow($result));
    //chekar ke hacer si no enkontramos ninguna persona a kien llamar
    if (!$contacto_id)
    {
      //checar el motivo, puede ser por que no tenga a nadie asignado o por que ya se haya acabado la db
     $sql = "SELECT l.id FROM crm_campanas_llamadas AS l, crm_contactos AS c WHERE l.contacto_id=c.contacto_id AND c.uid='$uid' ORDER BY l.status_id, l.timestamp";//no mostrar los finalizados
     $result2 = $db->sql_query($sql) 
        or die("<br>$sql<br>Error al buscar contacto<br>".print_r($db->sql_error()));
     if ($db->sql_numrows($result2)) //hay en campaña asignado
       die("<html><head><script>$js_extra alert('No hay más personas a quien usted pueda llamar en esta campaña');window.close();</script></head></html>");
     else //no tiene asignado
      die("<html><head><script>$js_extra alert('No hay personas asignadas a usted en esta campaña');window.close();</script></head></html>");
    }
}
$sql = "SELECT l.id FROM crm_campanas_llamadas AS l, crm_contactos AS c WHERE l.contacto_id=c.contacto_id AND c.uid='$uid' AND campana_id='$campana_id'";//cuantos contactos
$result2 = $db->sql_query($sql) or die("<br>$sql<br>Error al buscar contacto<br>".print_r($db->sql_error()));
$cuantos_prospectos = $db->sql_numrows($result2);

//////////////////// LOCK /////////////////////
//chekar si está lockeado
$sql = "SELECT `lock`, timestamp, id, user_id FROM crm_campanas_llamadas  WHERE id='$llamada_id'";
$result = $db->sql_query($sql) or die("Error en lock".print_r($db->sql_error()));
if ($db->sql_numrows($result) < 1) die("Error: no existe esta llamada en la DB.<br>$sql");
list($lock, $timestamp, $llamada_id, $uid_lock) = htmlize($db->sql_fetchrow($result));
list($dia_ts, $hora_ts) = explode(" ", $timestamp);
$hoy = date("Y-m-d");
if (!(($lock == 0) || ($lock && ($dia_ts != $hoy)) || ($lock && ($uid_lock=$uid)))) die("<script>setTimeout('location.href=\"index.php?_module=$_module&_op=llamada&campana_id=$campana_id&nopendientes=1\"',3000);</script><center>Error: este registro está siendo usado.<br><a href=\"index.php?_module=$_module&_op=llamada&campana_id=$campana_id&nopendientes=1\">Continuar</a></center>");
//lockearla para ke nadie más la llame, guardar kien lockeo el registro para ke kuando esta misma persona 
//entre a otro registro se deslockee este.
//antes de lockear deslockear a todos los de este usuario
$db->sql_query("UPDATE crm_campanas_llamadas SET `lock`='0' WHERE user_id='$uid' AND `lock`='1'") or die("Error en lock".print_r($db->sql_error()));
//ahora sí, continuar a lockear este
$db->sql_query("UPDATE crm_campanas_llamadas SET `lock`='1', user_id='$uid', inicio=NOW() WHERE id='$llamada_id'") or die("Error en lock".print_r($db->sql_error()));


//INICIALIZACION
//obtenemos los datos de la kampaña
$fecha = date("r");
$sql = "SELECT nombre, titulo_contacto, url_contacto FROM crm_campanas WHERE campana_id='$campana_id'";
list($campana, $titulo_contacto, $url_contacto) = htmlize($db->sql_fetchrow($db->sql_query($sql)));

//obtenemos el status anterior por si abortamos
list($status_ini, $fecha_cita, $intentos) = htmlize($db->sql_fetchrow($db->sql_query("SELECT status_id, fecha_cita, intentos FROM crm_campanas_llamadas WHERE id='$llamada_id'")));

if ($fecha_cita == "0000-00-00 00:00:00") $fecha_cita = "";
//OBTENEMOS DATOS KE MOSTRAMOS
//datos de la persona
$sql = "SELECT nombre, apellido_paterno, apellido_materno,tel_casa, tel_oficina,tel_movil, tel_otro,nota,
        email, origen_id,tel_casa_2,tel_oficina_2,tel_movil_2,horario_preferido_casa,horario_preferido_oficina,
        horario_preferido_movil,horario_preferido_casa_2,horario_preferido_oficina_2,horario_preferido_movil_2,
        codigo_campana,fecha_autorizado,fecha_firmado
        FROM crm_contactos WHERE contacto_id='$contacto_id' LIMIT 1";

$result = $db->sql_query($sql) or die("Error al consultar datos del contacto");
global $nombre, $apellido_paterno, $apellido_materno;
list($nombre, $apellido_paterno, $apellido_materno,$tel_casa, $tel_oficina,$tel_movil, $tel_otro,$nota, $email,
     $origen_id,$tel_casa_2, $tel_oficina_2, $tel_movil_2,$horario_preferido_casa, $horario_preferido_oficina,
     $horario_preferido_movil,$horario_preferido_casa_2, $horario_preferido_oficina_2, $horario_preferido_movil_2,
     $codigo_campana,$fecha_autorizado,$fecha_firmado) = htmlize($db->sql_fetchrow($result));

    $objeto= new Fecha_autorizado ($db,$fecha_autorizado,$fecha_firmado);
    $color_semaforo=$objeto->Obten_Semaforo();
  
    list($horario_casa_manana_checked,$horario_casa_tarde_checked,$horario_casa_noche_checked) = get_horario($horario_preferido_casa);
    list($horario_casa_manana_checked_2,$horario_casa_tarde_checked_2,$horario_casa_noche_checked_2) = get_horario($horario_preferido_casa_2);
        
        
    list($horario_oficina_manana_checked,$horario_oficina_tarde_checked,$horario_oficina_noche_checked) = get_horario($horario_preferido_oficina);
    list($horario_oficina_manana_checked_2,$horario_oficina_tarde_checked_2,$horario_oficina_noche_checked_2) = get_horario($horario_preferido_oficina_2);
        
    list($horario_celular_manana_checked,$horario_celular_tarde_checked,$horario_celular_noche_checked) = get_horario($horario_preferido_movil);
    list($horario_celular_manana_checked_2,$horario_celular_tarde_checked_2,$horario_celular_noche_checked_2) = get_horario($horario_preferido_movil_2);

    $horario_preferido_casa = "
        <table>
            <tr>
                <td><input type=\"checkbox\" disabled=\"disabled\" $horario_casa_manana_checked></td>
                <td><input type=\"checkbox\"  disabled=\"disabled\" $horario_casa_tarde_checked></td>
                <td><input type=\"checkbox\" disabled=\"disabled\"  $horario_casa_noche_checked></td>
            </tr>
        </table>";

    $horario_preferido_casa_2 = "<table><tr>
                <td><input type=\"checkbox\"  disabled=\"disabled\" $horario_casa_manana_checked_2></td>
                <td><input type=\"checkbox\"  disabled=\"disabled\" $horario_casa_tarde_checked_2></td>
                <td><input type=\"checkbox\"  disabled=\"disabled\" $horario_casa_noche_checked_2></td>
                </table>";

    $horario_preferido_oficina = "<table><tr>
                <td><input type=\"checkbox\"  disabled=\"disabled\" $horario_oficina_manana_checked></td>
                <td><input type=\"checkbox\"  disabled=\"disabled\" $horario_oficina_tarde_checked></td>
                <td><input type=\"checkbox\"  disabled=\"disabled\" $horario_oficina_noche_checked></td>
                </table>";

    $horario_preferido_oficina_2 = "<table><tr>
                <td><input type=\"checkbox\"  disabled=\"disabled\" $horario_oficina_manana_checked_2></td>
                <td><input type=\"checkbox\"  disabled=\"disabled\" $horario_oficina_tarde_checked_2></td>
                <td><input type=\"checkbox\"  disabled=\"disabled\" $horario_oficina_noche_checked_2></td>
                </table>";

    $horario_preferido_movil = "<table><tr>
                <td><input type=\"checkbox\"  disabled=\"disabled\" $horario_celular_manana_checked></td>
                <td><input type=\"checkbox\"  disabled=\"disabled\" $horario_celular_tarde_checked></td>
                <td><input type=\"checkbox\"  disabled=\"disabled\" $horario_celular_noche_checked></td>
                </table>";

    $horario_preferido_movil_2 = "<table><tr>
                <td><input type=\"checkbox\"  disabled=\"disabled\" $horario_celular_manana_checked_2></td>
                <td><input type=\"checkbox\"  disabled=\"disabled\" $horario_celular_tarde_checked_2></td>
                <td><input type=\"checkbox\"  disabled=\"disabled\" $horario_celular_noche_checked_2></td>
                </table>";                        

	
    $buffer_modelo='';
    $sql = "SELECT modelo,modelo_id,version_id,transmision_id,timestamp
                FROM crm_prospectos_unidades WHERE contacto_id='$contacto_id'";
    $result = $db->sql_query($sql) or die("Error al consultar datos del contacto");
    if($db->sql_numrows($result) > 0)
    {
        $tmp_m=array();
        $tmp_v=array();
        $tmp_t=array();
        $tmp_ch=array();
        while ($fila = $db->sql_fetchrow($result))
        {
            $tmp_m[]=$fila['modelo']."";
            $sql_version="SELECT nombre FROM crm_versiones WHERE version_id=".$fila['version_id'].";";
            $res_version=$db->sql_query($sql_version);
            if($db->sql_numrows($res_version) > 0)
                $tmp_v[]=$db->sql_fetchfield(0,0,$res_version);

            $sql_version="SELECT nombre FROM crm_transmisiones WHERE transmision_id=".$fila['transmision_id'].";";
            $res_version=$db->sql_query($sql_version);
            if($db->sql_numrows($res_version) > 0)
                $tmp_t[]=$db->sql_fetchfield(0,0,$res_version);

            $sql_chasis="SELECT chasis FROM crm_prospectos_ventas WHERE contacto_id=".$contacto_id." AND modelo_id=".$fila['modelo_id']." AND version_id=".$fila['version_id']." AND transmision_id=".$fila['transmision_id']." AND timestamp_unidades='".$fila['timestamp']."' LIMIT 1";
            $res_chasis=$db->sql_query($sql_chasis);
            if($db->sql_numrows($res_chasis) > 0)
                $tmp_ch[]=$db->sql_fetchfield(0,0,$res_chasis);
        }
        if(count($tmp_m) > 0)
        {
            $tmp_width=round(100/(count($tmp_m)+1));
            $array_titulos=array(0 => 'Producto',
				 //1 => 'Categoria',2 => 'Sub Categoria',
				 1 => 'No de Serie');
            $buffer_modelo.="<table width='100%' align='center' border='0'>";
            for ($i=0;$i < count($array_titulos); $i++)
            {
                switch($i)
                {
                    case 0:
                        $tmp_heigh=12;
                        $array_tmp=$tmp_m;
                        break;
                    /*case 1:
                        $tmp_heigh=28;
                        $array_tmp=$tmp_v;
                        break;
                    case 2:
                        $tmp_heigh=29;
                        $array_tmp=$tmp_t;
                        break;*/
                    case 1:
                        $tmp_heigh=22;
                        $array_tmp=$tmp_ch;
                        break;
                }
                $buffer_modelo.="<tr height='$tmp_heigh' class=\"row".($class_row++%2?"2":"1")."\"><td width='$tmp_width%'><b>".$array_titulos[$i]."</b></td>";
                for($j=0; $j<count($array_tmp);$j++)
                {
                    $buffer_modelo.="<td width='$tmp_width%' align='left'>&nbsp;".$array_tmp[$j]."</td>";
                }
                $buffer_modelo.="</tr>";
            }
            $buffer_modelo.="</table>";
        }
    }

//cambiamos el saludo para mostrar variables
//SALUDO Y OBJECIONES
//dependiendo de la actividad de venta
global $campana_id_objeciones;
if ($ciclo_de_venta_id && $ciclo_de_venta!=1) //si envian una opción de ciclo de ventas, es que están cambiando en la etapa del ciclo de ventas, lo cual
{//como no se ha consultado la DB y llenado este campo, si existe es que lo mandaron por POST
  $campana_id_objeciones = -$ciclo_de_venta_id; //las campañas coinciden
}
else
{
    $campana_id_objeciones = $campana_id;
}

//SALUDO
$sql = "SELECT saludo FROM crm_campanas WHERE campana_id='$campana_id_objeciones'";
list($saludo) = htmlize($db->sql_fetchrow($db->sql_query($sql)));
global $username;
list($username) = htmlize($db->sql_fetchrow($db->sql_query("SELECT name FROM users WHERE uid='$uid' LIMIT 1")));;
if ($campana_id == -1)
{
 list($compania) = htmlize($db->sql_fetchrow($db->sql_query("SELECT tipo FROM crm_clientes_servicios WHERE contacto_id='$contacto_id' LIMIT 1 order by clientes_servicio_id DESC")));;
}
$saludo = str_replace("[USUARIO]", "$username", $saludo);
$saludo = str_replace("[NOMBRE]", "$nombre $apellido_paterno $apellido_materno", $saludo);
$hora = date("G");
if ($hora >= 19) $diastardes = "BUENAS NOCHES";
else if ($hora >= 12) $diastardes = "BUENAS TARDES";
else $diastardes = "BUENOS DÍAS";
$saludo = str_replace("[SALUDO]", "$diastardes", $saludo);

$saludo = str_replace("[GRUPO]", "$grupo", $saludo);

if ($nota) $note_style = "background-color:#E6E6EB;color:black;";

//cambiar el formato de la fecha
if ($fecha_cita)
{
	list($ff, $hm) = explode(" ", $fecha_cita);
    list($hh, $mm) = explode(":", $hm);
    $ff = date_reverse($ff);
    $fecha_cita = "$ff";
    $hora_cita = "$hh";
    $minuto_cita = "$mm";
}
    $select_hora = "<select name=\"hora_cita\" id=\"hora_cita\" value=\"$hora_cita\" style='width:40px'>";
    for ($h = 0; $h < 24; $h++)
    {
    	$h2 = sprintf("%02d", $h);
    	if ($h == $hora_cita)
    		$sel = " SELECTED";
    	else 
    		$sel = "";
    	$select_hora .= "<option$sel>$h2</option>";
    }
    $select_hora .= "</select>";
    $select_minuto = "<select name=\"minuto_cita\" id=\"minuto_cita\" value=\"$minuto_cita\" style='width:40px'>";
    for ($m = 0; $m < 60; $m += 5)
    {
    	$m2 = sprintf("%02d", $m);
    	if ($m == $minuto_cita)
    		$sel = " SELECTED";
    	else 
    		$sel = "";
    	$select_minuto .= "<option$sel>$m2</option>";
    }
    $select_minuto .= "</select>";




//OBJECIONES


//vamos a inicializar las objeciones
function objeciones_js_array($objecion_padre_id)
{
    global $db, $campana_id_objeciones, $js_objeciones, $js_titulos, $js_padres, $nombre, $apellido_paterno, $apellido_materno, $uid, $username;
    list($username) = htmlize($db->sql_fetchrow($db->sql_query("SELECT name FROM users WHERE uid='$uid' LIMIT 1")));
    $sql = "SELECT objecion_id, titulo, objecion FROM crm_campanas_objeciones WHERE campana_id='$campana_id_objeciones' AND objecion_padre_id='$objecion_padre_id' ORDER BY objecion_id";
    $result = $db->sql_query($sql) or die("Error al buscar objeciones".print_r($db->sql_error()));
    if ($db->sql_numrows($result) < 1) return;
    $i = 0;
    while (list($objecion_id, $titulo, $objecion) = htmlize($db->sql_fetchrow($result)))
    {
        //mismos kambios ke en el saludo
        $objecion = str_replace("[USUARIO]", "$username", $objecion);
        $objecion = str_replace("[NOMBRE]", "$nombre $apellido_paterno $apellido_materno", $objecion);
        $hora = date("G");
        if ($hora >= 19) $diastardes = "BUENAS NOCHES";
        else if ($hora >= 12) $diastardes = "BUENAS TARDES";
        else $diastardes = "BUENOS DÍAS";
        $objecion = str_replace("[SALUDO]", "$diastardes", $objecion);
        $objecion = str_replace("[GRUPO]", "$grupo", $objecion);
        $objecion = str_replace("\r", "", $objecion);
        $objecion = str_replace("\n", "\\\\n", $objecion);
        //ahora inicializar el javascript de kada objecion
        $js_objeciones .= "array_objeciones[$objecion_id] = \"".html_entity_decode2($objecion)."\";\n";
        $js_titulos .= "array_titulos[$objecion_id] = \"".html_entity_decode2($titulo)."\";\n";
        $js_padres .= "array_padres['$objecion_padre_id-$i'] = $objecion_id;\n";
        $i++;
        objeciones_js_array($objecion_id);
    }
}
global $js_objeciones, $js_titulos, $js_padres;
objeciones_js_array(0);

$objecion = $saludo;

//parche para el catálogo
$sql="SELECT unidad_id,url,nombre FROM crm_unidades WHERE active=1 ORDER BY nombre;";
$res=$db->sql_query($sql) or die ("Error:  ".$sql);
if($db->sql_numrows($res) > 0)
{
    $catalogo .= "<table width=\"100%\" height=\"25px\"><th><b>Catálogo</b></th></table>";
    $catalogo .= "\n<ul id=nav>\n";
    while(list($unidad_id,$url,$nombre) = $db->sql_fetchrow($res))
    {
        if($url!='#')
            $catalogo .= "<li><a href='".$url."' target='vw_com'>".$nombre."</a></li>";
        else
            $catalogo .= "<li>".$nombre."</li>";
    }
    $catalogo .= "</ul>\n<br>\n";
}


//TODO: arreglar despues para que haya un catálogo por campaña
// if ($campana_id == 5) $catalogo = "";
$saludo = strtr($saludo, "\n", " ");
$saludo = strtr($saludo, "\r", " ");

/*//extra objeciones
$extra_objeciones = "<table width=\"100%\"><th>Accesos rápidos</th></table>";
$sql = "SELECT objecion_id, titulo, objecion FROM crm_campanas_objeciones WHERE campana_id='$campana_id_objeciones' AND objecion_padre_id='-1';";
$result = $db->sql_query($sql) or die("Error en extra objeciones");
while (list($objecion_id, $titulo, $objecion) = $db->sql_fetchrow($result))
{
    $extra_objeciones .= "<input type=\"hidden\" name=\"obj_extra_$objecion_id\" id=\"obj_extra_$objecion_id\" value=\"$objecion\">\n";
    $extra_objeciones .= "<input class=boton type=button style=\"width:120px;\" value=\"$titulo\" onclick=\"show_popup(document.getElementById('obj_extra_$objecion_id').value)\"><br>\n";
}

$extra_objeciones .= "<input class=boton type=button style=\"width:120px;\" value=\"Despedida\" onclick=\"show_popup('Muchas gracias por su tiempo, mi nombre es $username, hasta pronto.')\"><br>"
                   ."<br><input class=boton type=button style=\"width:120px;\" value='Regresar al inicio\nde dialogo' onclick=\"show_obj_bt(0);show_obj('$saludo')\"><br>";
    */
//el contacto_button es para crear contratos, llenar encuestas, y cualquier cosa que sigue despues de un contacto exitoso.
if ($url_contacto)
$contacto_button = "<input value=\"Abrir $titulo_contacto\" onclick=\"window.open('$url_contacto&contacto_id=$contacto_id&campana_id=$campana_id&llamada_id=$llamada_id','contrato','location=no,resizable=yes,scrollbars=yes,navigation=no,titlebar=no,directories=no,width=800,height=650,left=0,top=0,alwaysraised=yes');\" type=\"button\">";

//La parte variable de la ventana (depende de la campana)
//$variable = "<br><center>Módulo variable</center><br>";

//la parte nueva de prospección
//buscar el ciclo , hacia adelante y hacia atrás
$ciclo_campanas_id = array();
//los del ciclo que siguen
$campana_id_ = $campana_id;
do
{
	if ($campana_id_) array_push($ciclo_campanas_id, $campana_id_);//no agregar la campana 0 que es donde acaba
	$r = $db->sql_query("SELECT next_campana_id FROM crm_campanas WHERE campana_id='$campana_id_'") or die("Error en ciclo");        
}
while (list($campana_id_) = $db->sql_fetchrow($r));
//los de antes 
$campana_id_ = $campana_id;
do
{
	if ($campana_id_ != $campana_id)
		array_unshift($ciclo_campanas_id, $campana_id_);
	$r = $db->sql_query("SELECT campana_id FROM crm_campanas WHERE next_campana_id='$campana_id_'") or die("Error en ciclo");
}
while (list($campana_id_) = $db->sql_fetchrow($r));
//el select de ciclo
$enable = false;
foreach($ciclo_campanas_id AS $campana_id_)
{
	if ($enable)
	{
		$semaforo = "<img src=\"img/pixel.gif\" style=\"height:15px;width:15px;\">";	   //poner vacio a las qu eno
		$disabled = "";
	}
	else 
	{
		$semaforo = "<img src=\"img/ok.gif\" style=\"height:15px;width:15px;\">"; //poner una imagen a los anteriores
		$disabled = " DISABLED";
	}
	$r = $db->sql_query("SELECT nombre FROM crm_campanas WHERE campana_id='$campana_id_'") or die("Error en ciclo");
	list($n) = $db->sql_fetchrow($r);
	$select_ciclo_de_venta_id .= "$semaforo<input type=\"radio\" name=\"next_campana_id\" group=\"next_campana_id\" value=\"$campana_id_\"$disabled>$n<br>";
	
	if ($campana_id_ == $campana_id) //desactivar los que están antes de esta campana
		$enable = true;
}

//buscar la fecha de los contactos en el log (cuando cambio de ciclo de venta)
$sql = "SELECT DATE_FORMAT(timestamp,'%d-%m-%Y') FROM crm_campanas_llamadas_log WHERE contacto_id='$contacto_id' ORDER BY timestamp ASC LIMIT 1";
$r = $db->sql_query($sql) or die($sql);
list($primer_contacto) = $db->sql_fetchrow($r);
if ($primer_contacto) $primer_contacto = "&nbsp;&nbsp;&nbsp;Primer contacto: <b>$primer_contacto</b>";
$sql = "SELECT DATE_FORMAT(timestamp,'%d-%m-%Y') FROM crm_campanas_llamadas_log WHERE contacto_id='$contacto_id' ORDER BY timestamp DESC LIMIT 1";
$r = $db->sql_query($sql) or die($sql);
list($ultimo_contacto) = $db->sql_fetchrow($r);
if ($ultimo_contacto) $ultimo_contacto = "&nbsp;&nbsp;&nbsp;Último contacto:&nbsp;<b> $ultimo_contacto</b>";

//este es un parche de actividades
$fase_id = $campana_id[strlen($campana_id)-1];
/*switch($fase_id)
{
case '8': $script_a = "Programación del seguimiento inmediato, mediato y posterior<br> Llamada posterior a la entrega<br> Descubrir comportamiento del auto<br> Descubrir nivel de satisfacción del cliente<br> Detección de problemas específicos <br> Canalización del problema<br> Contacto de seguimiento de la solución del problema<br> Solicitud de referidos<br> Programación de llamadas posteriores<br> Descubrimiento de nuevas necesidades de transporte<br> Ofrecimiento de productos y servicios.<br>";
    $select_actividades = "&nbsp;&nbsp;&nbsp;        Disponibilidad al cambio / segunda compra</a><BR>" . $select_actividades;
    $select_actividades = "&nbsp;&nbsp;&nbsp;        Uso prolongado de la unidad<BR>" . $select_actividades;
    $select_actividades = "&nbsp;&nbsp;&nbsp;        Mantenimiento de la condición del vehículo<BR>" . $select_actividades;
    $select_actividades = "&nbsp;&nbsp;&nbsp;        Solución de problemas específicos del auto<BR>" . $select_actividades;
    $select_actividades = "&nbsp;&nbsp;&nbsp;        Comentarios a terceros (+ o - )<BR>" . $select_actividades;
    $select_actividades = "&nbsp;&nbsp;&nbsp;        Comprobación de la decisión personal de compra<BR>" . $select_actividades;
    $select_actividades = "<a href=\"javascript:popup_actividades('$script_a');\">&nbsp;&nbsp;&nbsp;        Comprobación de la calidad del vehículo<BR>" . $select_actividades;

case '7': $script_a = "Acordar y respetar fecha, tiempo y lugar de la entrega<br> Verificar la condición de entrega de la unidad<br> Solución de problemas previos a la entrega<br> Recopilación de la documentación pertinente<br> Puntualidad para la recepción del cliente<br> Explicación de documentos por firmar<br> Explicación de documentos a entregar<br> Explicación de operación de caracterísiticas especiales del auto<br> Seguimiento de un recorrido de entrega<br> Presentación del personal de Postventa<br> Entrega de documentación de la unidad<br> Reporte de entrega<br> Solicitud de referidos<br>";
    $select_actividades = "&nbsp;&nbsp;&nbsp;        Disfrute / uso del vehículo </a><BR>" . $select_actividades;
    $select_actividades = "&nbsp;&nbsp;&nbsp;        Felicitación por la compra <BR>" . $select_actividades;
    $select_actividades = "&nbsp;&nbsp;&nbsp;        Expectativas de entrega <BR>" . $select_actividades;
    $select_actividades = "<a href=\"javascript:popup_actividades('$script_a');\">&nbsp;&nbsp;&nbsp;        Acordar fecha, tiempo y lugar de entrega <BR>" . $select_actividades;
    $select_actividades = "<b>FASE COMPROBACIÓN</b><BR>" . $select_actividades;

case '6': $script_a = "Atención a indicios de cierre<br> Aplicación de técnicas de cierre orales<br> Aplicación de técnicas de cierre visuales<br> Elaboración de resúmenes<br> Presentación de opciones finales<br> Alerta ante objeciones dilatorias<br> Solicitud del cierre<br> Llenado del pedido<br> Felicitación por la compra<br> Programación de la entrega<br> Trámite del pago / depósito<br> Lllenado y entrega de documentacion pertinente<br> Reporte de la venta<br>";
    $select_actividades = "&nbsp;&nbsp;&nbsp;        Toma de decisión de compra </a><BR>" . $select_actividades;
    $select_actividades = "&nbsp;&nbsp;&nbsp;        Comparación de alternativas <BR>" . $select_actividades;
    $select_actividades = "&nbsp;&nbsp;&nbsp;        Búsqueda de mayores beneficios <BR>" . $select_actividades;
    $select_actividades = "<a href=\"javascript:popup_actividades('$script_a');\">&nbsp;&nbsp;&nbsp;        Negociación de detalles específicos <BR>" . $select_actividades;

case '5': $script_a = "Entrega de información específica<br> Oferta de beneficios adicionales<br> Ofrecimiento de apoyo en trámites crediticios<br> Ofrecimiento de realización de trámites propios<br> Quitar obstáculos<br> Presentaciones adicionales a terceros<br> Adaptación de planes financieros<br> Defensa de no-negociables<br> Manejo de objeciones financieras<br>";
    $select_actividades = "&nbsp;&nbsp;&nbsp;        Consulta con terceros </a><BR>" . $select_actividades;
    $select_actividades = "&nbsp;&nbsp;&nbsp;        Análisis de la situación económica personal  <BR>" . $select_actividades;
    $select_actividades = "&nbsp;&nbsp;&nbsp;        Análisis de la información financiera <BR>" . $select_actividades;
    $select_actividades = "<a href=\"javascript:popup_actividades('$script_a');\">&nbsp;&nbsp;&nbsp;        Análisis de la información automovilística<BR>" . $select_actividades;
    $select_actividades = "<b>FASE DECISIVA</b><BR>" . $select_actividades;    

case '4': $script_a = "Ofrecimiento de prueba de manejo<br> Cita de prueba de manejo<br> Preparación de la unidad y la ruta<br> Adaptar la prueba a sus necesidades específicas<br> Aplicar técnica Anticipación-Aprobación<br> Introducción de garantías y respaldos de la marca y agencia<br> Aplicación de técnicas de cierre<br> Ofrecimiento de beneficios adicionales<br> Manejo de objeciones técnológicas<br>";
    $select_actividades = "&nbsp;&nbsp;&nbsp;        Análisis del comportamiento de la unidad </a><BR>" . $select_actividades;
    $select_actividades = "&nbsp;&nbsp;&nbsp;        Comprobación de la información recibida <BR>" . $select_actividades;
    $select_actividades = "<a href=\"javascript:popup_actividades('$script_a');\">&nbsp;&nbsp;&nbsp;        Deseo de efectuar prueba de manejo<BR>" . $select_actividades;

case '3': $script_a = "Presentación del vehículo directamente<br> Presentación del vehículo por folletería<br> Envío de información del vehículo<br> Investigación sobre auto competidor<br> Impulso a ventajas y beneficios del vehículo propio<br> Presentación de planes de compra<br> Presentación de alternativas de compra<br>";
    $select_actividades = "&nbsp;&nbsp;&nbsp;        Solicitud de información sobre cómo adquirir el auto </a><BR>" . $select_actividades;
    $select_actividades = "&nbsp;&nbsp;&nbsp;        Solicitud de información específica sobre el auto  <BR>" . $select_actividades;
    $select_actividades = "&nbsp;&nbsp;&nbsp;        Visitas a concesionarias de la competencia <BR>" . $select_actividades;
    $select_actividades = "&nbsp;&nbsp;&nbsp;        Recopilación de experiencias con el producto  <BR>" . $select_actividades;
    $select_actividades = "<a href=\"javascript:popup_actividades('$script_a');\">&nbsp;&nbsp;&nbsp;        Solicitud de información oficial y no oficial  <BR>" . $select_actividades;
    $select_actividades = "<b>FASE FORMATIVA</B><BR>" . $select_actividades;

case '2': $script_a = "Determinar sus necesidades y deseos<br> Investigar sus posibilidades económicas<br> Investigar posibles obstáculos económicos, tiempo, etc<br> Determinar canal de ventas<br>";
    $select_actividades = "<a href=\"javascript:popup_actividades('$script_a');\">&nbsp;&nbsp;&nbsp;        Disponibilidad para proporcionar información</a><BR>" . $select_actividades;

case '1': $script_a = "Presentación personal y de su empresa<br> Ofrecimiento de productos y servicios<br> Determinar vehículo de su interés<br> Investigar cuánto conoce del producto<br>";
    $select_actividades = "&nbsp;&nbsp;&nbsp;        Búsqueda de información</a><BR>" . $select_actividades;
    $select_actividades = "&nbsp;&nbsp;&nbsp;        Conciencia de alternativas<BR>" . $select_actividades;
    $select_actividades = "<a href=\"javascript:popup_actividades('$script_a');\">&nbsp;&nbsp;&nbsp;        Identificación de sus necesidades / deseos<BR>" . $select_actividades;
    $select_actividades = "<b>FASE ESTIMULANTE</b><BR>" . $select_actividades;
    

}
*/
$script_act = "
<script>
function popup_actividades(txt)
{
var generator=window.open('','ciclo','height=200,width=360,location=no,resizable=yes,scrollbars=yes,navigation=no,titlebar=no,directories=no');
generator.document.write('<html><head><title>Actividades del ciclo de venta - $campana</title>');
generator.document.write('<link type=\"text/css\" href=\"$_css\" rel=\"stylesheet\">');
generator.document.write('</head><body onclick=\"self.close()\">');
generator.document.write(txt);
generator.document.write('</body></html>');
generator.document.close();
}
</script>";

$select_actividades .= $script_act;

//busquemos si hay un evento relacionado con esta llamada, y si no ha sido cerrado
$sql = "SELECT evento_id, tipo_id, comentario FROM crm_campanas_llamadas_eventos WHERE llamada_id='$llamada_id' ORDER BY evento_id DESC"; //buscar el más viejo
$result = $db->sql_query($sql) or die("Error al buscar tipo".print_r($db->sql_error()));
list($evento_id, $evento_tipo_id, $evento_comentario)  = htmlize($db->sql_fetchrow($result));
//ver si no está cerrado
$sql = "SELECT cierre_id  FROM crm_campanas_llamadas_eventos_cierres WHERE evento_id='$evento_id'"; 
$result = $db->sql_query($sql) or die("Error al buscar tipo".print_r($db->sql_error()));
list($cierre_id) = htmlize($db->sql_fetchrow($result));
if ($cierre_id) //ya fue cerrado, quitar el evento
{
  $evento_id = $evento_tipo_id = $evento_comentario = "";
} //si no fue cerrado, guardar el evento_id


$evento_usuario = "";

if (!$evento_id)
{
  $titulo_planear = "Planear";
  $status = -2; //para marcar el input hidden y que se haga posponer
  //revisar si hay un evento abierto
  $botones_submit = "<input value=\"Planear\" name=\"submit\" type=\"submit\" style=\"width:180px;\" >";
  //el comentario y el tipo son editables
  $input_evento_comentario = "<input name=\"evento_comentario\" value=\"\" style=\"width:180px;\" onblur=\"caps1(this);\">";
  //select del tipo del evento
  $sql = "SELECT tipo_id, nombre FROM crm_campanas_llamadas_eventos_tipos WHERE 1 ORDER BY tipo_id";
  $result = $db->sql_query($sql) or die("Error al buscar tipo".print_r($db->sql_error()));
  $select_tipo = "<select name=\"evento_tipo_id\" style=\"width:180px;\" >\n";
  $select_tipo .= "<option value=\"\" SELECTED>Seleccione un tipo</option>\n";
  while (list($tipo_id, $tipo) = htmlize($db->sql_fetchrow($result)))
  {
      $select_tipo .= "<option value=\"$tipo_id\">$tipo</option>\n";
  }
  $select_tipo .= "</select>";
  $boton_eliminar_prospecto="onclick=\"window.open('index.php?_module=$_module&_op=llamada_cancelar&contacto_id=$contacto_id&campana_id=$campana_id', 'venta','location=no,resizable=yes,scrollbars=yes,navigation=no,titlebar=no,directories=no,width=400,height=175,left=0,top=0,alwaysraised=yes');\"";
  //fin de select y de cosas editables
}
else //hay un evento, cerrar o cancelar o posponer
{
  $boton_eliminar_prospecto="onclick=\"alert('Favor de cerrar el compromiso antes de eliminar al prospecto')\"";
  $fecha_cita_ro = "<b>$fecha_cita</b><br>";
  $fecha_cita_bk = $fecha_cita;
  $hora_cita_bk = $hora_cita;
  $titulo_planear = "Planear compromiso";
  $cerrar_evento = "<input type=\"hidden\" name=\"evento_id\" value=\"$evento_id\" >";
  $cerrar_evento .= "<input type=\"hidden\" name=\"fecha_cita_bk\" id=\"fecha_cita_bk\" value=\"$fecha_cita_bk\" >";
  $cerrar_evento .= "<input type=\"hidden\" name=\"hora_cita_bk\" id=\"hora_cita_bk\" value=\"$hora_cita_bk\" >";
  $cerrar_evento .= "Status<br>";
  $cerrar_evento .= "<select name=\"evento_cierre_status_id\" style=\"width:180px;\" >";
  $cerrar_evento .= "<option value=\"0\">No exitoso</option>";
  $cerrar_evento .= "<option value=\"1\">Exitoso</option>";
  $cerrar_evento .= "</select><br><br>";
  $cerrar_evento .= "Comentario<br>";
  $cerrar_evento .= "<input name=\"evento_cierre_comentario\" id=\"evento_cierre_comentario\"  style=\"width:180px;\" onblur=\"caps1(this);\"><br><br>";
  $cerrar_evento .= "";
  $botones_submit .= "<input value=\"Guardar\" name=\"submit_registrar\" type=\"submit\" style=\"width:180px;\" onclick=\"if (document.getElementById('evento_cierre_comentario').value == ''){alert('Ingrese una comentario para guardar'); return false;}return true;\">";
  $botones_submit .= "<input value=\"Posponer\" name=\"submit_posponer\" type=\"submit\" style=\"width:180px;\" ><br>".$msg_prosponer;
  $botones_submit .= "<input value=\"Cancelar\" name=\"submit_cancelar_evento\" type=\"submit\" style=\"width:180px;\"><br>";
  
  //estos 2 siguientes no se pueden modificar
  $input_evento_comentario = "<b>$evento_comentario</b>"; //este no se puede modificar
  $sql = "SELECT nombre FROM crm_campanas_llamadas_eventos_tipos WHERE tipo_id='$evento_tipo_id'";
  $result = $db->sql_query($sql) or die("Error al buscar tipo".print_r($db->sql_error()));
  list($tipo) = htmlize($db->sql_fetchrow($result));
  $select_tipo = "<select name=\"evento_tipo_id\" style=\"width:180px;\" ><option>$tipo</option></select></b>";
  //fin de lo no modificable
}

$sql_uni="SELECT contacto_id,modelo_id,version_id,transmision_id,timestamp FROM crm_prospectos_unidades WHERE contacto_id = '$contacto_id' ORDER BY timestamp";
$res_uni=$db->sql_query($sql_uni) or die($sql_uni);
$num_unidades=$db->sql_numrows($res_uni);
if($num_unidades == 0)
{
    $opciones_botones_venta = "El prospecto no tiene asociado ningun producto;";
}
else
{
    $num_ventas=0;
    $num_conf=0;
    if($num_unidades > 1)
    {
        while(list($id_con,$modelo_id,$timestamp)=$db->sql_fetchrow($res_uni))
        {
            $sql_vta="SELECT * FROM crm_prospectos_ventas
            WHERE contacto_id='$id_con' AND modelo_id='$modelo_id' AND timestamp_unidades='$timestamp';";
	    $sql_conf="SELECT * FROM crm_prospectos_ventas
	    WHERE contacto_id='$id_con' AND modelo_id='$modelo_id' AND timestamp_unidades='$timestamp' AND venta_confirmada='1'";
            $res_vta=$db->sql_query($sql_vta) or die($sql_vta);
	    $res_conf = $db->sql_query($sql_conf) or die($sql_conf);
            if($db->sql_numrows($res_vta)>0)
                $num_ventas++;
	    if($db->sql_numrows($res_conf)>0)
		$num_conf++;
        }
    }
    else
    {
	$sql_vta="SELECT * FROM crm_prospectos_ventas
	WHERE contacto_id='$contacto_id';";
	$sql_conf = "SELECT * FROM crm_prospectos_ventas WHERE contacto_id='$contacto_id' AND venta_confirmada='1'";
	$res_vta=$db->sql_query($sql_vta) or die($sql_vta);
	$res_conf=$db->sql_query($sql_conf) or die($sql_conf);
	if($db->sql_numrows($res_vta)>0)
	    $num_ventas++;
	if($db->sql_numrows($res_conf)>0)
	    $num_conf++;
    }
}
$s='';$ss='';$sss='';
if($num_unidades > 1) $s='s';
if($num_ventas > 1) $ss='s';
if($num_conf > 1) $sss='s';
$opciones_botones_venta = "<input type=\"button\" style=\"width:180px;\" value=\"Reportar venta\" onclick=\"window.open('index.php?_module=$_module&_op=llamada_venta&contacto_id=$contacto_id&campana_id=$campana_id&llamada_id=$llamada_id&nopendientes=1', 'venta','location=no,resizable=yes,scrollbars=yes,navigation=no,titlebar=no,directories=no,width=600,height=250,left=0,top=0,alwaysraised=yes');\"><br>";
$opciones_botones_venta.= "<b>$num_unidades producto$s asignado$s<br>$num_ventas venta$ss registrada$ss<b><br>";
$opciones_botones_venta.= "<b>$num_conf venta$sss confirmada$sss</b><br/>";
$opciones_botones_venta .=Asigna_semaforo($color_semaforo,$fecha_firmado,$contacto_id);
//if(!($id_llamada > 0))
if($num_ventas < $num_unidades)
{
  $opciones_botones_venta .= "<input type=\"button\" style=\"width:180px;\" ".$boton_eliminar_prospecto." value=\"Eliminar prospecto\" ><br>
		Esta opción sirve para explicar los motivos del vendedor para no cerrar una venta.
		<br>
      $contacto_button";
}
else
{
    $opciones_botones_venta.= "<b>Este contacto ya tiene una venta registrada<b>";
}
    if(eregi('8$',$campana_id))
    {
        $forma_finalizar_contacto = "<form action=\"index.php\" name=\"finalizar_venta\" method=\"post\" onsubmit=\"return confirm('¿Desea terminar el seguimiento del cliente?');\">
		  <br>
		    <input name=\"_module\" value=\"$_module\" type=\"hidden\">
			<input name=\"_op\" value=\"$_op\" type=\"hidden\">
			<input name=\"contacto_id\" value=\"$contacto_id\" type=\"hidden\">
			<input name=\"campana_id\" value=\"$campana_id\" type=\"hidden\">
			<input name=\"llamada_id\" value=\"$llamada_id\" type=\"hidden\">
		  <input name=\"finalizar\" value=\"Terminar seguimiento\" type=\"submit\" style=\"width:180px;\">
		</form>";
    }
//}

function get_horario($string){
    $return = array(
    (eregi('M',$string) ? 'checked="checked"' : ''),
    (eregi('T',$string) ? 'checked="checked"' : ''),
    (eregi('N',$string) ? 'checked="checked"' : '')
    );
    return $return;
}

function Asigna_semaforo($color_semaforo,$fecha_firmado,$contacto_id)
{
    $buf='';
    if($color_semaforo!='')
    {
        if($fecha_firmado == '0000-00-00 00:00:00')
        {
            $buf.= "<input type='hidden' name='contacto_id' id='contacto_id' value='$contacto_id'>";
            $buf.= "<input type='button'  style='width:180px;' value='Contrato VWL firmado' id='autorizado' name='autorizado'><div id='firmado'><span style='background-color:$color_semaforo'></span></div><br>";
        }
        else
        {
            $style='display:table;border-collapse:separate;border-spacing:5px;background-color:'.$color_semaforo.';width:180px;';
            $buf="<span style='$style'>&quot;El contrato VWL ya ha sido firmado por el cliente &quot;</span><br><br>";
        }
    }
    return $buf;
}
?>