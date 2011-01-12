<?
if(!defined('_IN_MAIN_INDEX'))
{
    die("No puedes acceder directamente a este archivo...");
}
global $db, $contacto_id,$tipo,$gid;
$_theme = "";
$c = $contacto_id;
$tipo=$tipo;
$gid=$gid;
$array = array();
$array_motivos=regresa_motivos_cancelacion($db);
switch($tipo)
{
    case 'Venta':
    {
        $tmp_array=Revisa_en_ventas($db,$gid,$c);
        if(count($tmp_array)>0)
        {
            $fecha_cv=$tmp_array['fecha_cv'];
            $timestamp_cv=$tmp_array['timestamp_cv'];
            if ( (!empty($fecha_cv)) && (!empty($timestamp_cv)))
            {
                $motivo = "Venta";
            }
        }
        break;
    }
    case 'Cancelación':
    {
        $tmp_array=Revisa_en_cancelados($db,$gid,$c);
        if(count($tmp_array)>0)
        {
            $motivo_id =$tmp_array['motivo_id'];
            if($motivo_id == 0)
                $motivo =$tmp_array['motivo'];
            else
                $motivo=$array_motivos[$motivo_id];
            $fecha_cv    =$tmp_array['fecha_cv'];
            $timestamp_cv=$tmp_array['timestamp_cv'];
        }
        break;
    }
    default:
    {
        $tmp_array=Revisa_en_cancelados($db,$gid,$c);
        if(count($tmp_array)>0)
        {
            $motivo_id =$tmp_array['motivo_id'];
            if($motivo_id == 0)
                $motivo =$tmp_array['motivo'];
            else
                $motivo=$array_motivos[$motivo_id];
            $fecha_cv    =$tmp_array['fecha_cv'];
            $timestamp_cv=$tmp_array['timestamp_cv'];
        }
        else
        {
            $tmp_array=Revisa_en_ventas($db,$gid,$c);
            $fecha_cv=$tmp_array['fecha_cv'];
            $timestamp_cv=$tmp_array['timestamp_cv'];
            if ( (!empty($fecha_cv)) && (!empty($timestamp_cv)))
            {
                $motivo = "Venta";
            }
        }
        break;
    }
}
if( ($motivo=='') && ( ($tipo=='Venta') || ($tipo=='Cancelación')))
{
    $motivo="Desconocido";
}

$return = array('fecha'=>$fecha_cv,'motivo'=>$motivo);
// output correct header
$xhr = isset($_SERVER['HTTP_X_REQUESTED_WITH']) and (strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
header('Content-Type: ' . ($xhr ? 'application/json' : 'text/plain'));
die(json_encode($return));


function Revisa_en_ventas($db,$gid,$c)
{
    $sql="SELECT DISTINCT a.contacto_id,  DATE_FORMAT(b.timestamp,'%d-%m-%Y')as fecha_cv, UNIX_TIMESTAMP(b.timestamp)as timestamp_cv
                  FROM (crm_contactos_finalizados a LEFT JOIN crm_prospectos_ventas b ON a.contacto_id = b.contacto_id)
                  WHERE a.gid=".$gid." AND a.contacto_id =".$c." AND b.uid IS NOT NULL ORDER BY `b`.`uid` DESC";
    $res=$db->sql_query($sql);
    if($db->sql_numrows($res) > 0) {
        while($fila = $db->sql_fetchrow($res)) {

            $array['fecha_cv']=$fila['fecha_cv'];
            $array['timestamp_cv']=$fila['timestamp_cv'];
        }
    }
    return $array;

}

function Revisa_en_cancelados($db,$gid,$c)
{
    $array=array();
    $sql="SELECT DISTINCT a.contacto_id, b.motivo_id, b.motivo, DATE_FORMAT(b.timestamp,'%d-%m-%Y') as fecha_cv, UNIX_TIMESTAMP(b.timestamp) as timestamp_cv
                  FROM (crm_contactos_finalizados a LEFT JOIN crm_prospectos_cancelaciones b ON a.contacto_id = b.contacto_id)
                  WHERE a.gid=".$gid." AND a.contacto_id =".$c." AND b.uid IS NOT NULL ORDER BY b.timestamp ASC";
    $res=$db->sql_query($sql);
    if($db->sql_numrows($res) > 0) {
        while($fila = $db->sql_fetchrow($res)) {
            $array['motivo_id']=$fila['motivo_id'];
            $array['motivo']=$fila['motivo'];
            $array['fecha_cv']=$fila['fecha_cv'];
            $array['timestamp_cv']=$fila['timestamp_cv'];
        }
    }
    return $array;
}
function regresa_motivos_cancelacion($db) {
    $array=array();
    $sql="SELECT  motivo_id,motivo FROM crm_prospectos_cancelaciones_motivos ORDER BY motivo_id;";
    $res=$db->sql_query($sql);
    if($db->sql_numrows($res) > 0) {
        while($fila = $db->sql_fetchrow($res)) {
            $array[$fila['motivo_id']]=$fila['motivo'];
        }
    }
    return $array;
}
?>