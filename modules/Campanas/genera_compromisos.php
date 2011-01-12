<?php
if (!defined('_IN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}
global $db, $uid,$gid,$_site_title;

$_site_title = "Generar compromisos";
$sql="SELECT a.evento_id,a.llamada_id,a.tipo_id,a.comentario,a.uid,a.fecha_cita,a.timestamp,b.id,b.campana_id,b.contacto_id
      FROM crm_campanas_llamadas_eventos AS a LEFT JOIN crm_campanas_llamadas AS b ON a.llamada_id =b.id
      WHERE substr(a.timestamp,1,10)='".$date("Y-m-d")."' ORDER BY a.timestamp;";
$res=$db->sql_query($sql) or die ("Error en la consulta:  ".$sql);
if($db->sql_numrows($res)>0)
{
    $buffer='evento_id,llamada_id,tipo_id,comentario,uid,fecha_cita,timestamp,id,campana,contacto_id';
    while($row=$db->sql_fetchrow($res))
    {
        if(count($row)>0)
        {
            $array=array();
            $contacto_id=$row['contacto_id '];
            $array=array_merge($row,Regresa_Datos($db,$contacto_id));
            $buffer.=implode(',',$array)."\n\t";
        }
    }
    $file="salida.csv".date("Y-m-d");
    $f1=fopen("../".$file,"w+");
    fwrite($f1,$buffer);
}

/**
 * Funcion que regresa los datos del contacto
 * @param <object> $db Conexion a la Base de Datos
 * @param <int>  $contacto_id  id del contacto
 * @return <array> $array con los datos del contacto
 */
function Regresa_Datos($db,$contacto_id)
{
    $row=array();
    $sql="SELECT nombre,apellido_paterno,apellido_materno,tel_casa,tel_oficina,tel_movil,tel_otro,email
          FROM crm_contactos WHERE contacto_id=".$contacto_id." LIMIT 1;";
    $res=$db->sql_query($sql);
    if($db->sql_num_rows($res)>0)
    {
        $row=$db->sql_fetchrow($res);
    }
    return $row;
}
?>
