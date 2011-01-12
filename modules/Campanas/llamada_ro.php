<?
if (!defined('_IN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}
global $_site_title, $db, $uid, $how_many, $from, $campana_id, $contacto_id, $llamada_id, $submit, $status, $resultado, 
		$nota, $fecha_cita, $personal, $delalert, $ciclo_de_venta_id,
		$next_campana_id;
include_once("modules/Gerente/class_autorizado.php");

function html_entity_decode2( $given_html, $quote_style = ENT_QUOTES ) {
       $trans_table = array_flip(get_html_translation_table( HTML_SPECIALCHARS, $quote_style ));
       $trans_table['&#39;'] = "'";
       return ( strtr( $given_html, $trans_table ) );
}
if (!$campana_id) die("Por favor seleccione nuevamente una campaña.");
$_css = $_themedir."css/".$_theme."/style.css";
$_theme = "";
$_site_title = "Llamada";

//cambiar el formato de la fecha
if ($fecha_cita)
{
    list($ff, $hh) = explode("     ", $fecha_cita);
    $ff = date_reverse($ff);
    $fecha_cita = "$ff $hh";
}

//AKI VA OPERACIONES ESPECIALES DEPENDIENDO KONSULTA


//INICIALIZAR EL CONTACTO
if (!$contacto_id)//chekamos si tenemos un usuario o tenemos ke eskogerlo de forma inteligente
{
    //buskamos primero si no hay ke hacer una llamada en los próximos minutos
    global $nopendientes; //si esta seteado esto no buscar pendientes
    if (!$nopendientes) //solo entrar aki si no esta seteado, osea, entra a buscar siempre que se pueda
    {
        $en10mins = time() + (10 * 60);
        $sql = "SELECT l.id, c.contacto_id, c.nombre, c.apellido_paterno, c.apellido_materno, l.fecha_cita, l.campana_id FROM crm_campanas_llamadas AS l, crm_contactos AS c WHERE l.contacto_id=c.contacto_id AND c.uid='$uid' AND l.status_id='-2' "
        ."AND l.fecha_cita <= '".date("Y-m-d G:i:s", $en10mins)."' "//dentro de 10 minutos o antes
        ."AND campana_id='$campana_id' ORDER BY l.fecha_cita, c.nombre ASC ";
        $result = $db->sql_query($sql)
            or die("Error al buscar contacto");
        $cuantas_llamadas_pendientes = $db->sql_numrows($result);
        if ($cuantas_llamadas_pendientes > 0) //si encontramos algun pendiente preguntar si keremos llamarlo
        {
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
                    ."<center><h1>Hay llamadas pendientes ($cuantas_llamadas_pendientes):</h1><br>"
                    .$lista
                    ."<input type=button value=\"No llamar\" onclick=\"location.href='index.php?_module=$_module&_op=$_op&campana_id=$campana_id&nopendientes=1'\">"
                    ."</center></body>";
            //no continuar hasta que se conteste
            die($_html);
        }
    }
    //si no pues entonces lo normal
    //buskar la primera llamada ke haya ke realizar
    //>=0 son por realizar
    //si esta lockeado hay ke chekar si no se dejo lockeado de un dia anterior y usar de todas formas
    $sql = "SELECT l.id, c.contacto_id FROM crm_campanas_llamadas AS l, crm_contactos AS c WHERE l.contacto_id=c.contacto_id AND c.uid='$uid'  AND l.campana_id='$campana_id' ORDER BY l.timestamp LIMIT 1";//quitar lock, solo una persona puede usarlo a la vez AND (l.`lock`=0 OR (l.`lock`!=0 AND l.timestamp NOT LIKE '".date("Y-m-d")."%'))
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
list($status_ini, $resultado_ini, $fecha_cita, $intentos) = htmlize($db->sql_fetchrow($db->sql_query("SELECT status_id, resultado_id, fecha_cita, intentos FROM crm_campanas_llamadas WHERE id='$llamada_id'")));

if ($fecha_cita == "0000-00-00 00:00:00")
    $fecha_cita = "";
//OBTENEMOS DATOS KE MOSTRAMOS
//datos de la persona
$sql = "SELECT 
                    nombre, apellido_paterno, apellido_materno,
                    tel_casa, tel_oficina,
                    tel_movil, tel_otro,
                    nota, email, origen_id,fecha_autorizado,fecha_firmado
                FROM crm_contactos WHERE contacto_id='$contacto_id' LIMIT 1";
$result = $db->sql_query($sql) or die("Error al consultar datos del contacto");
global $nombre, $apellido_paterno, $apellido_materno;
list(
                $nombre, $apellido_paterno, $apellido_materno,
                $tel_casa, $tel_oficina,
                $tel_movil, $tel_otro,
                $nota, $email,
				$origen_id,$fecha_autorizado,$fecha_firmado
    ) = htmlize($db->sql_fetchrow($result));

	
	//datos del vehículo
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
        $array_titulos=array(0 => 'Producto',1 => 'Categoria',2 => 'Sub Categoria',3 => 'No de Serie');
        $buffer_modelo.="<table width='100%' align='center' border='0'>";
        for ($i=0;$i < count($array_titulos); $i++)
        {
            switch($i)
            {
                case 0:
                    $tmp_heigh=12;
                    $array_tmp=$tmp_m;
                    break;
                case 1:
                    $tmp_heigh=28;
                    $array_tmp=$tmp_v;
                    break;
                case 2:
                    $tmp_heigh=29;
                    $array_tmp=$tmp_t;
                    break;
                case 3:
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
  

$objeto= new Fecha_autorizado ($db,$fecha_autorizado,$fecha_firmado);
$color_semaforo=$objeto->Obten_Semaforo();

//cambiamos el saludo para mostrar variables
//SALUDO Y OBJECIONES
//dependiendo de la actividad de venta
global $campana_id_objeciones;
if ($ciclo_de_venta_id && $ciclo_de_venta!=1) //si envian una opción de ciclo de ventas, es que están cambiando en la etapa del ciclo de ventas, lo cual
{//como no se ha consultado la DB y llenado este campo, si existe es que lo mandaron por POST
  $campana_id_objeciones = -$ciclo_de_venta_id; //las campañas coinciden
}
else $campana_id_objeciones = $campana_id;

//SALUDO
$sql = "SELECT saludo FROM crm_campanas WHERE campana_id='$campana_id_objeciones'";
list($saludo) = htmlize($db->sql_fetchrow($db->sql_query($sql)));
global $username;
list($username) = htmlize($db->sql_fetchrow($db->sql_query("SELECT name FROM users WHERE uid='$uid' LIMIT 1")));;
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
    list($ff, $hh) = explode(" ", $fecha_cita);
    $ff = date_reverse($ff);
    $fecha_cita = "$ff     $hh";
}

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
//STATUS Y RESULTADOS
//select del status (para guardar)
$sql = "SELECT status_id, nombre FROM crm_campanas_llamadas_status WHERE campana_id='0' OR campana_id='$campana_id' ORDER BY orden";
$result = $db->sql_query($sql) or die("Error al buscar status".print_r($db->sql_error()));
$select_status = "<select name=\"status\">\n";
while (list($status_id, $status) = htmlize($db->sql_fetchrow($result)))
{
    //las ke no se kieran mostrar
//     if ($status_id == 0 || $status_id == -1) continue;
    if ($status_id == $status_ini) {$selected = " SELECTED";$select_status .= "<option value=\"$status_id\"$selected>$status</option>\n";}
    else $selected = "";
    
}
$select_status .= "</select>";
//select del resultado (para guardar)
$sql = "SELECT resultado_id, nombre FROM crm_campanas_llamadas_resultados WHERE campana_id='$campana_id' ORDER BY resultado_id";
$result = $db->sql_query($sql) or die("Error al buscar resultados".print_r($db->sql_error()));

while (list($resultado_id, $resultado) = htmlize($db->sql_fetchrow($result)))
{
    if ($resultado_id == $resultado_ini) $selected = " CHECKED";
    else $selected = "";
    $select_resultado .= "<input type=\"radio\" name=\"resultado\" value=\"$resultado_id\"$selected>$resultado&nbsp;&nbsp;";
}


//parche para el catálogo
$cat_productos = array("Smart Access" => "#","E - Budget" => "#","Symphony" => "#","Web Project" => "#");
$catalogo = "\n<ul id=nav>\n";
foreach($cat_productos AS $p => $url)
{
  $catalogo .= "<li><a href=\"$url\" target=\"vw_com\">$p</a></li>";
}
$catalogo .= "</ul>\n";
$saludo = strtr($saludo, "\n", " ");
$saludo = strtr($saludo, "\r", " ");

//extra objeciones
$sql = "SELECT objecion_id, titulo, objecion FROM crm_campanas_objeciones WHERE campana_id='$campana_id_objeciones' AND objecion_padre_id='-1';";
$result = $db->sql_query($sql) or die("Error en extra objeciones");
while (list($objecion_id, $titulo, $objecion) = $db->sql_fetchrow($result))
{
    $extra_objeciones .= "<input type=\"hidden\" name=\"obj_extra_$objecion_id\" id=\"obj_extra_$objecion_id\" value=\"$objecion\">\n";
    $extra_objeciones .= "<input class=boton type=button style=\"width:120px;\" value=\"$titulo\" onclick=\"show_popup(document.getElementById('obj_extra_$objecion_id').value)\"><br>\n";
}

$extra_objeciones .= "<br><input class=boton type=button style=\"width:120px;\" value=\"Despedida\" onclick=\"show_popup('Muchas gracias por su tiempo, mi nombre es $username, hasta pronto.')\"><br>"
                   ."<br><input class=boton type=button style=\"width:120px;\" value='Regresar al Inicio' onclick=\"show_obj_bt(0);show_obj('$saludo')\"><br>";

if ($url_contacto)
$contacto_button = "<input value=\"Abrir $titulo_contacto\" onclick=\"window.open('$url_contacto&contacto_id=$contacto_id&campana_id=$campana_id&llamada_id=$llamada_id','contrato','location=no,resizable=yes,scrollbars=yes,navigation=no,titlebar=no,directories=no,width=800,height=650,left=0,top=0,alwaysraised=yes');\" type=\"button\">";

//La parte variable de la ventana (depende de la campana)
//$variable = "<br>Módulo variable<br><br>";

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
	$select_ciclo_de_venta_id .= "$semaforo<input readonly type=\"radio\" name=\"next_campana_id\" group=\"next_campana_id\" value=\"$campana_id_\"$disabled>$n<br>";
	
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
}
else //hay un evento, cerrar o cancelar o posponer
{
  $titulo_planear = "Compromisoz";
  $cerrar_evento = "<input type=\"hidden\" name=\"evento_id\" value=\"$evento_id\" >";
  $cerrar_evento .= "";
  $botones_submit .= "<input value=\"Cancelar\" name=\"submit_cancelar_evento\" type=\"submit\" style=\"width:180px;\" ><br>";
  $botones_submit .= "<input value=\"Posponer\" name=\"submit_posponer\" type=\"submit\" style=\"width:180px;\" ><br>";
  $botones_submit .= "<input value=\"Registrar\" name=\"submit_registrar\" type=\"submit\" style=\"width:180px;\" >";
  
  //estos 2 siguientes no se pueden modificar
  $input_evento_comentario = "<b>$evento_comentario</b>"; //este no se puede modificar
  $sql = "SELECT nombre FROM crm_campanas_llamadas_eventos_tipos WHERE tipo_id='$evento_tipo_id'";
  $result = $db->sql_query($sql) or die("Error al buscar tipo".print_r($db->sql_error()));
  list($tipo) = htmlize($db->sql_fetchrow($result));
  $select_tipo = "<select name=\"evento_tipo_id\" style=\"width:180px;\" ><option>$tipo</option></select></b>";
  //fin de lo no modificable
}
?>