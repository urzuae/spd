<?
if(!defined('_IN_MAIN_INDEX'))
{
    die("No puedes acceder directamente a este archivo...");
}

global $db, $contacto_id;
$_theme = "";
$c = $contacto_id;

/*
$sql  = "SELECT gid, super FROM users WHERE contacto_id='$contacto_id'";
$result = $db->sql_query($sql) or die("Error");
list($gid) = $db->sql_fetchrow($result);		///LASIGUIENTE PARTE ES LA QUE ALENTA
*/

$sql = "SELECT DATE_FORMAT(timestamp,'%d-%m-%Y'), UNIX_TIMESTAMP(timestamp) FROM crm_campanas_llamadas_log WHERE contacto_id='$c' ORDER BY timestamp DESC LIMIT 1";
$r3 = $db->sql_query($sql) or die($sql);
list($ultimo_contacto, $ultimo_contacto_timestamp) = $db->sql_fetchrow($r3);

//$sql = "SELECT from_uid FROM crm_contactos_asignacion_log WHERE contacto_id = $c ORDER BY timestamp DESC LIMIT 1";
$sql = "SELECT u.user FROM crm_contactos_asignacion_log AS c, users AS u WHERE c.contacto_id = '$c' and u.uid = c.from_uid ORDER BY c.timestamp DESC LIMIT 1";
$r4 = $db->sql_query($sql) or die($sql);
list($ultimo_uid) = $db->sql_fetchrow($r4);
if ($ultimo_uid)
{
	$sql = "SELECT user FROM users WHERE uid = '$ultimo_uid'";
	$r = $db->sql_query($sql) or die($sql);
	list($ultimo_user) = $db->sql_fetchrow($r);
}
//darle formato en horas al timestamp
if($ultimo_contacto_timestamp)
{
    $ultimo_contacto_timestamp = time() - $ultimo_contacto_timestamp;
    $ultimo_contacto_timestamp_bk = $ultimo_contacto_timestamp;
    if($ultimo_contacto_timestamp > 0)
    {
        $ultimo_contacto_timestamp = $ultimo_contacto_timestamp / 60 / 60; //entre 60 segs, entre 60 mins
        $ultimo_contacto_timestamp = sprintf("%u", $ultimo_contacto_timestamp); //entero
        

        $ultimo_contacto_timestamp .= " hr"; //($ultimo_contacto_timestamp!=1?"s":"")
    }

} else
{
    $ultimo_contacto_timestamp = "";
    $ultimo_contacto_timestamp_bk = "";
}



$return = array(
    'espera'=>$ultimo_contacto_timestamp, 
	'ultimo_vendedor'=>$ultimo_uid
	);
// output correct header
$xhr = isset($_SERVER['HTTP_X_REQUESTED_WITH']) and (strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
header('Content-Type: ' . ($xhr ? 'application/json' : 'text/plain'));

die(json_encode($return));

?>