<?php
if (!defined('_IN_ADMIN_MAIN_INDEX'))
{
    die ("No puedes acceder directamente a este archivo...");
}
global $db,$opc,$fuente_id,$nm_fuente;

$date=date("Y-m-d H:i:s");
switch($opc)
{
    case 1:
    {
        $ins="INSERT INTO crm_fuentes (nombre,timestamp,active) VALUES ('".$nm_fuente."','".$date."','1');";
        $res=$db->sql_query($ins) or die("Error al insertar el origen:  ".$ins);
        if ($res)
        {
            $maximo=$db->sql_nextid();
            $ins="INSERT INTO crm_fuentes_arbol (padre_id,hijo_id ) VALUES ('".$fuente_id."','".$maximo."');";
            $res=$db->sql_query($ins) or die("Error al insertar el origen en el arbol de origenes:  ".$ins);
            echo"Origen registrado";
        }
        break;
    }
    case 2:
    {
        $upd="UPDATE crm_fuentes SET active=0 WHERE fuente_id='".$fuente_id."';";
        $res=$db->sql_query($upd) or die("Error al bloquear el origen:  ".$upd);
        break;
    }
    case 3:
    {
        $upd="UPDATE crm_fuentes SET active=1 WHERE fuente_id='".$fuente_id."';";
        $res=$db->sql_query($upd) or die("Error al desbloquear el origen:  ".$upd);
        break;
    }
    case 4:
    {
        $upd="UPDATE crm_fuentes SET active=2 WHERE fuente_id='".$fuente_id."';";
        $res=$db->sql_query($upd) or die("Error al Eliminar el origen:  ".$upd);
        break;
    }
    case 5:
    {
        $ins="UPDATE crm_fuentes SET nombre='".$nm_fuente."' WHERE fuente_id=".$fuente_id.";";
        $res=$db->sql_query($ins) or die("Error al insertar el origen:  ".$ins);
        echo"Origen Actualizado";
        break;
    }
}
die();
?>
