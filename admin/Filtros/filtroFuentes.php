<?php
if (!defined('_IN_ADMIN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}
global $db, $idcombo;
$idcombo = $_GET["id"];
if($idcombo != 0)
{
    $res =$db->sql_query("SELECT a.padre_id,b.nombre,b.fuente_id FROM crm_fuentes_arbol a,crm_fuentes b WHERE a.padre_id=".$idcombo." and a.hijo_id=b.fuente_id ORDER BY b.nombre ASC",$conection);
    if($db->sql_numrows($res) > 0)
    {
        echo"<option value='0'>Selecciona</option>";
        while($rs = $db->sql_fetchrow($res))
            echo '<option value="'.$rs["fuente_id"].'">'.htmlentities($rs["nombre"]).'</option>';
    }
}
die();
?>
