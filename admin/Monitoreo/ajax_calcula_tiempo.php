<?php
if (!defined('_IN_ADMIN_MAIN_INDEX'))
{
    die ("No puedes acceder directamente a este archivo...");
}
//Procedimiento que calcula las hora de retraso en atencion con STORE PROCEDURE
/*
global $db, $gid,$_dbhost,$_dbuname,$_dbpass,$_dbname;
$gid=$_GET['gid'];
$xh_tpss=0;
$xp_tpss=0;
$xm_tpss=0;
$xh_tpes=0;
$xp_tpes=0;
$xm_tpes=0;
$linkTodb = mysqli_connect($_dbhost,$_dbuname,$_dbpass) or die ("Error de Conexion");
$conn = mysqli_select_db ($linkTodb,$_dbname) or die("Error de Base de Datos");
$result = mysqli_query($linkTodb,"CALL StoreSeg('$gid')") or die ("Error en el Store Procedure StoreSeg");
if(mysqli_num_rows($result) > 0)
{
    list($total_atencion,$hrs_atencion,$max_atencion,$prom_atencion) = mysqli_fetch_array($result,MYSQLI_NUM);
    $hrs_atencion_fd=brigeFormatDayToDayHour($hrs_atencion);
    $pro_atencion_fd=brigeFormatDayToDayHour($prom_atencion);
    $max_atencion_fd=brigeFormatDayToDayHour($max_atencion);

    $hrs_compromiso_fd=brigeFormatDayToDayHour($hrs_compromiso);
    $pro_compromiso_fd=brigeFormatDayToDayHour($prom_compromiso);
    $max_compromiso_fd=brigeFormatDayToDayHour($max_compromiso);

    $return = array('gid' => $gid,
                'd_xh_tpss' => $hrs_atencion,
                'd_xp_tpss' => $prom_atencion,
                'd_xm_tpss' => $max_atencion,
                't_xh_tpss' => $hrs_atencion_fd,
                't_xp_tpss' => $prom_atencion_fd,
                't_xm_tpss' => $max_atencion_fd);
}
header('Content-Type: ' . ($xhr ? 'application/json' : 'text/plain'));
die(json_encode($return));
*/

//Procedimiento que calcula las hora de retraso en atencion con php

global $db, $gid,$_dbhost,$_dbuname,$_dbpass,$_dbname;
$gid=$_GET['gid'];
$xh_tpss=0;
$xh_tpes=0;
$hrs_atencion=0;
$hrs_compromiso=0;

$pro_atencion=0;
$pro_compromiso=0;

$max_atencion=0;
$max_compromiso=0;

$total_atencion=0;
$total_compromiso=0;

$db->sql_query("DROP TABLE IF EXISTS tmp;");
$sql_create="CREATE TEMPORARY TABLE tmp AS SELECT DISTINCT b.contacto_id,a.gid,a.uid,b.id,b.user_id,b.inicio,b.fin,b.fecha_cita,b.timestamp,1 as status FROM crm_contactos a LEFT JOIN crm_campanas_llamadas b on a.contacto_id=b.contacto_id WHERE a.gid=".$gid.";";
if($db->sql_query($sql_create))
{
    $db->sql_query("UPDATE tmp as c SET c.status=0 WHERE c.id NOT IN (select b.llamada_id from crm_campanas_llamadas_eventos b where b.uid=c.uid and c.gid=".$gid.");");
    $db->sql_query("UPDATE tmp as c SET c.status=2 WHERE c.uid=0;");

    $array=array();
    $sql_asi="select SUM(TIMESTAMPDIFF(HOUR,b.timestamp,now())) as total,
                     MAX(TIMESTAMPDIFF(HOUR,b.timestamp,now())) as maximo,
                     AVG(TIMESTAMPDIFF(HOUR,b.timestamp,now())) as promedio
              FROM tmp a,unicos_log b
              WHERE a.gid=".$gid." and a.status=0 and a.contacto_id=b.contacto_id
              GROUP BY a.gid;";
    $res_asi=$db->sql_query($sql_asi);
    if($db->sql_numrows($res_asi)>0)
    {
        $array=$db->sql_fetchrow($res_asi);
    }
    $hrs_atencion=$array['total'];
    $max_atencion=$array['maximo'];
    $pro_atencion=$array['promedio'];
    
    $hrs_atencion_fd   = $hrs_atencion;
    $pro_atencion_fd   = $pro_atencion;
    $max_atencion_fd   = $max_atencion;

    $hrs_atencion    = brigeFormatDayToDayHour($hrs_atencion);
    $pro_atencion    = brigeFormatDayToDayHour($pro_atencion);
    $max_atencion    = brigeFormatDayToDayHour($max_atencion);

    $return = array('gid' => $gid,
                'd_xh_tpss' => $hrs_atencion,
                'd_xp_tpss' => $pro_atencion,
                'd_xm_tpss' => $max_atencion,
                't_xh_tpss' => $hrs_atencion_fd,
                't_xp_tpss' => $pro_atencion_fd,
                't_xm_tpss' => $max_atencion_fd);
    header('Content-Type: ' . ($xhr ? 'application/json' : 'text/plain'));
    die(json_encode($return));
}
function brigeFormatDayToDayHour($totalHours)
{

    $hoursForDay = 24;
    $days = "" +  ($totalHours/$hoursForDay);
    list($day,$decimalDay) = explode(".",$days);
    $decimalDay =  ((float)(".".$decimalDay))*$hoursForDay;
    return "$day d ".(int)$decimalDay." h";
}

?>