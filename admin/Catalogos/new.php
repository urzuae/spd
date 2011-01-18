<?
if (!defined('_IN_ADMIN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}
global $db,$_licenses,$_includesdir,$producto,$url,$_module,$_op,$submit,$msg_ciclo,$_site_title;
$_site_title = "Nueva fuente";
$select_origenPadre=genera_origen($db,$origen_padre_id);

function genera_origen($db,$origen_padre_id)
{
    $sql_padres="SELECT a.padre_id,b.nombre,b.fuente_id FROM crm_fuentes_arbol a,crm_fuentes b WHERE a.padre_id>0 and a.hijo_id=b.fuente_id and b.active=1 ORDER BY b.nombre;";
    $res_padres=$db->sql_query($sql_padres);
    if( $db->sql_numrows($res_padres) >= 0)
    {
        $select_origenPadre="<select name=\"padre_id\" id=\"padre_id\" class=\"nodo\">
                          <option value='1'>Raiz</option>";
        while($fila = $db->sql_fetchrow($res_padres))
        {
            $select_origenPadre.= "<option value=\"".$fila['fuente_id']."\">".$fila['nombre']."</otpion>";
        }
        $select_origenPadre.="</select>";
    }
    $select_origenPadre.="&nbsp;&nbsp;
                        <input type='hidden' id='origen' name='origen'>
                        <br><select name='hijo_id_1' id='hijo_id_1' class='nodo'><option value='0'>Seleccionar</option></select>
                        <br><select name='hijo_id_2' id='hijo_id_2' class='nodo'><option value='0'>Seleccionar</option></select>
                        <br><select name='hijo_id_3' id='hijo_id_3' class='nodo'><option value='0'>Seleccionar</option></select>
                        <br><select name='hijo_id_4' id='hijo_id_4' class='nodo'><option value='0'>Seleccionar</option></select>";
    return $select_origenPadre;
}
?>