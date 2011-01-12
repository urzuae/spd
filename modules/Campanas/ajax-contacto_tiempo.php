<?
if(!defined('_IN_MAIN_INDEX'))
{
    die("No puedes acceder directamente a este archivo...");
}
global $db, $contacto_id, $_uid;

//checamos el uso horario
$result = $db->sql_query("SELECT gid FROM users WHERE uid='_$uid' LIMIT 1") or die("Error en grupo " . print_r($db->sql_error()));
list($gid) = $db->sql_fetchrow($result);
$sql = "SELECT horas FROM groups_horas WHERE gid = '$gid'";
$result = $db->sql_query($sql) or die("Error en grupo " . print_r($db->sql_error()));
list($horas_diferencia) = $db->sql_fetchrow($result);

$_theme = "";

$sql = "SELECT DATE_FORMAT(l.fecha_cita,'%d-%m-%Y %H:%i')
, UNIX_TIMESTAMP(l.fecha_cita), l.status_id FROM crm_campanas_llamadas AS l WHERE contacto_id = '$contacto_id'";
$result = $db->sql_query($sql) or die("Error en grupo " . print_r($db->sql_error()));
list($fecha_cita, $fecha_cita_timestamp, $status_id) = $db->sql_fetchrow($result);

//buscar la fecha de los contactos en el log (cuando cambio de ciclo de venta)
$sql = "SELECT DATE_FORMAT(timestamp,'%d-%m-%Y'), UNIX_TIMESTAMP(timestamp) FROM crm_campanas_llamadas_log WHERE contacto_id='$contacto_id' ORDER BY timestamp ASC LIMIT 1";
//echo "<br>".$sql."<br>";
$r3 = $db->sql_query($sql) or die($sql);
list($primer_contacto, $primer_contacto_timestamp) = $db->sql_fetchrow($r3);
$sql = "SELECT DATE_FORMAT(timestamp,'%d-%m-%Y'), UNIX_TIMESTAMP(timestamp) FROM crm_campanas_llamadas_log WHERE contacto_id='$contacto_id' ORDER BY timestamp DESC LIMIT 1";
//echo $sql."<br>";
$r3 = $db->sql_query($sql) or die($sql);
list($ultimo_contacto, $ultimo_contacto_timestamp) = $db->sql_fetchrow($r3);

//formatear el tiempo que lleva de retraso la cita
if($fecha_cita_timestamp && $status_id == -2)
{
    
    $retraso = (time() + (60 * (60 * $horas_diferencia))) - $fecha_cita_timestamp;
    
    if($retraso > 0)
    {
        $horas = floor($retraso / 60 / 60);
        $mins = round($retraso / 60 - $horas * 60);
        $retraso = "$horas hr $mins m";
    } else
        $retraso = "";
        
    //ahora checar el tipo de evento que es la cita
    $sql = "SELECT e.evento_id, e.tipo_id FROM crm_campanas_llamadas_eventos AS e, crm_campanas_llamadas AS l WHERE l.id=e.llamada_id AND l.contacto_id='$contacto_id' ORDER BY evento_id DESC"; //buscar el más viejo
    $result = $db->sql_query($sql) or die("Error al buscar tipo" . print_r($db->sql_error()));
    list($evento_id, $evento_tipo_id) = htmlize($db->sql_fetchrow($result));
    //ver si no está cerrado
    $sql = "SELECT cierre_id  FROM crm_campanas_llamadas_eventos_cierres WHERE evento_id='$evento_id'";
    $result = $db->sql_query($sql) or die("Error al buscar tipo" . print_r($db->sql_error()));
    list($cierre_id) = htmlize($db->sql_fetchrow($result));
    if($cierre_id) //ya fue cerrado, quitar el evento
        $tipo = "";
    else //si no fue cerrado, guardar el evento_id
    {
        //obtener eventos_id de la db 
        $sql = "SELECT nombre FROM `crm_campanas_llamadas_eventos_tipos` WHERE tipo_id = '$evento_tipo_id'";
        $result = $db->sql_query($sql) or die("Error al buscar tipo<br>$sql" . print_r($db->sql_error()));
        list($evento_tipo) = htmlize($db->sql_fetchrow($result));
    
    }
} else
    $retraso = "";
    
//darle formato en horas al timestamp para mostrar el tiempo que lleva sin que le den seguimiento
if($ultimo_contacto_timestamp)
{
    $espera_ts = (time() + (60 * (60 * $horas_diferencia))) - $ultimo_contacto_timestamp;
    if($espera_ts > 0)
    {
        $horas = floor($espera_ts / 60 / 60);
        $mins = round($espera_ts / 60 - $horas * 60);
        $espera = "$horas hr $mins m";
    } else
        $espera = "";
    //$espera = time() . " * $horas_diferencia - $ultimo_contacto_timestamp "; 


} else
{
    $ultimo_contacto = "";
}

$return = array(
    'primer_contacto'=>$primer_contacto, 
    'ultimo_contacto'=>$ultimo_contacto, 
    'retraso'=>$retraso, 
    'espera'=>$espera,
	'evento_tipo'=>$evento_tipo
	);
// output correct header
$xhr = isset($_SERVER['HTTP_X_REQUESTED_WITH']) and (strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
header('Content-Type: ' . ($xhr ? 'application/json' : 'text/plain'));

die(json_encode($return));

?>