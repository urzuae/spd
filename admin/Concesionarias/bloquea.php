<? 
if (!defined('_IN_ADMIN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}
global $db, $new;

$id_bloqueado=0;
$name_bloqueado="";
$titulo_reasignado="";


function insert_logs($db,$tmp_contacto,$tmp_uid,$tmp_gid,$tmp_rgid)
{
	$db->sql_query("INSERT INTO crm_contactos_asignacion_log (contacto_id,uid,from_uid,to_uid,from_gid,to_gid,timestamp) VALUES ('".$tmp_contacto."','".$tmp_uid."','".$tmp_uid."','0','".$tmp_gid."','".$tmp_rgid."','".date("Y-m-d H:i:s")."');");
}
/*
 * Si existe la concesionaria bloqueada y la concesionaria a reasignar hago los cambios en la BD
 */ 

if( ($_GET['gid']) && ($_GET['rgid']) )
{
    $result_asignados=$db->sql_query("SELECT contacto_id,uid,gid FROM crm_contactos WHERE gid=".$_GET['gid']);
/**
 * llamo a la funcion insert_logs donde se insertan los logs
 */
    
    while (list($tmp_contacto,$tmp_uid,$tmp_gid) = htmlize($db->sql_fetchrow($result_asignados)))
    {
    	insert_logs($db,$tmp_contacto,$tmp_uid,$_GET['gid'],$_GET['rgid']);
    }
	$db->sql_query("UPDATE groups SET active=false WHERE gid=".$_GET['gid']);
    $db->sql_query("UPDATE users  SET active=false WHERE gid=".$_GET['gid']);
    //$db->sql_query("UPDATE groups_ubications SET active=false WHERE gid=".$_GET['gid']);
    $db->sql_query("UPDATE crm_contactos SET gid=".$_GET['rgid'].",uid=0 WHERE gid=".$_GET['gid']);

    $gid_current= 0 + $_GET['gid'];
    $sql="SELECT a.id,a.campana_id,a.contacto_id FROM crm_campanas_llamadas a
          WHERE substr( a.campana_id, 1, (length( a.campana_id ) -2 )) = '".$gid_current."';";
    
    $res=$db->sql_query($sql) or die("Error en la consulta:  ".$sql);
    if($db->sql_numrows($res) > 0)
    {
        $contador=1;
        while($fila = $db->sql_fetchrow($res))
        {
            $tmp_etapa=substr($fila['campana_id'],( strlen($fila['campana_id'])-2 ),strlen($fila['campana_id']));
            $tmp_gid  =substr($fila['campana_id'],0,( strlen($fila['campana_id'])-2 ));
            $upd="UPDATE crm_campanas_llamadas  set campana_id='".$_GET['rgid'].$tmp_etapa."' WHERE id=".$fila['id'].";";
            $db->sql_query($upd);
            $contador++;
        }
    }
    
    $titulo_reasignado="Se ha bloqueado la distribuidora con id: ".$_GET['gid']." y se ha reasignado a la distribuidora con id: ".$_GET['rgid'];
    $_GET['gid']=$_GET['rgid'];
    header ( "location: index.php?_module=Concesionarias" );
}
    

$result_count=$db->sql_query("SELECT COUNT(*) FROM crm_contactos WHERE gid=".$_GET['gid']);
$total_prospectos=$db->sql_fetchrow($result_count);

$result = $db->sql_query("SELECT g.gid, g.name FROM groups AS g  WHERE g.active=true ORDER BY g.gid") OR die("Error al consultar db: ".print_r($db->sql_error()));
if($db->sql_numrows($result) > 0)
{
    $_cmp_select_reasignado="<select name=\"gid_reasignado\">";
    while (list($id, $name) = htmlize($db->sql_fetchrow($result)))
    {
    	$tmp_select="";
    	if($id==$_GET['gid'])
    	{
            $id_bloqueado=$id;
            $name_bloqueado=$name;
    	}
    	if($id == $_GET['rgid'])
    	   $tmp_select="selected";
        $_cmp_select_reasignado.="<option value=".$id." ".$tmp_select.">".$id."    ".$name."</option>";
    }
    $_cmp_select_reasignado.="</select>";
    //lista de usuarios
    $_html="<script>
                function concesionaria_bloqueada(id_bloqueado,name_bloqueado)
                {
                    if(confirm('Esta seguro de bloquear la distribuidora: '+name_bloqueado+' y reasignar sus prospectos a la distribuidora con id '+window.document.form.gid_reasignado.value))
                    {
                        location.href=('index.php?_module=$_module&_op=bloquea&gid='+id_bloqueado+'&rgid='+window.document.form.gid_reasignado.value);
                    }
                }
            </script>";
    
    $_html .= "<script>function newgroup(){var conc = prompt('Ingrese el nombre de la nueva distribuidora');if (conc) location.href=('index.php?_module=$_module&new='+conc);}</script>\n";
    $_html .= "<form name='form'>";
    $_html .= "<div class=title>Bloqueo de Distribuidora con reasignaci&oacute;n de prospectos</div><br>\n";
    $_html .= "<br>\n";
    $_html .= "<table style=\"border-spacing:0\" cellspacing=1  cellpadding=1>\n";
    $_html .= "<thead><tr style=\"font-weight:bold;\"><td>&nbsp;</td></tr></thead>\n";

    $_html .=  "<tbody>
                <tr class='row'><td><br></td></tr>
                <tr class='row'><td>Distribuidora a bloquear:&nbsp;&nbsp;<font color='#3e4f88'>".$name_bloqueado."</font></td></tr>
                <tr class='row'><td>No de prospectos asignados:&nbsp;&nbsp;<font color='#3e4f88'>".$total_prospectos[0]."</font></td></tr>
                <tr class='row'><td><br>Favor de seleccionar la distribuidora donde se reasignar&aacute;n los prospectos;.\n</td></tr>
                <tr class='row'><td>".$_cmp_select_reasignado."</td></tr>";    
    $_html .= " <tr class='row'><td align='center'><br><input type='button' name='boton1' value='Bloquear y Reasignar' onClick=\"concesionaria_bloqueada('$id_bloqueado','$name_bloqueado');\">&nbsp;&nbsp;<input type='button' name='btn2' value='Regresar' onClick=\"location.href=('index.php?_module=$_module');\"></td></tr>";
    $_html .= "</tbody></table>";
    $_html .= "<br><br><p>".$titulo_reasignado."</p>";
    $_html .= "</form>";
    global $_admin_menu2;//<img src=\"../img/new.gif\" border=0>
    $_admin_menu2 .= "<table><tr><td></td><td><a href=\"#\" onclick=\"newgroup()\"> Crear una nueva Distribuidora</a></td></tr></table>";
}
else
{
    $_html .= "<br>\n";
    $_html .= "<br>No existen distribuidoras registradas en el Sistema.<br>\n";
}
?>