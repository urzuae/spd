<?
if (!defined('_IN_ADMIN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}

global $db, $del, $new,$_module,$_id,$desbloquea,$msg_ciclo,$_site_title,$_licenses,$_licenses_not_used,$_licenses_used;
$_site_title = "Distribuidoras";
if($desbloquea)
{
   	$db->sql_query("UPDATE groups SET active=true WHERE gid=".$desbloquea);
    $db->sql_query("UPDATE users  SET active=1 WHERE gid=".$desbloquea);
    $db->sql_query("UPDATE groups_ubications SET active=true WHERE gid=".$desbloquea);
    $error="Se ha desbloqueado la distribuidora con id:  ".$desbloquea;

}
if($del)
{
    $sql="SELECT gid,name FROM delete_groups WHERE gid=".$del.";";
    $res=$db->sql_query($sql);
    if($db->sql_numrows($res) == 0)
        $db->sql_query("INSERT INTO delete_groups ( SELECT gid,name FROM groups WHERE gid=".$del.");");
    else
        $db->sql_query("UPDATE delete_groups SET name='".$db->sql_fetchfield(1,0,$res)."' WHERE gid='".$del."';");

    $db->sql_query("UPDATE crm_contactos SET gid='0' WHERE gid='$del'") or die("No se pudo actualizar los registros");
    $db->sql_query("DELETE FROM groups_accesses WHERE gid='$del'") or die("Error al borrar permisos");
    $db->sql_query("DELETE FROM groups_ubications WHERE gid='$del' LIMIT 1") or die("No se pudo borrar");
    $db->sql_query("DELETE FROM crm_plazas_concesionarias WHERE gid='$del' LIMIT 1") or die("No se pudo borrar en la tabla de plazas");
    $db->sql_query("DELETE FROM crm_niveles_concesionarias WHERE gid='$del' LIMIT 1") or die("No se pudo borrar en niveles");
    $db->sql_query("DELETE FROM crm_grupos_concesionarias WHERE gid='$del' LIMIT 1") or die("No se pudo borrar en grupos empresariales");
    $db->sql_query("DELETE FROM groups_zonas WHERE gid='$del' LIMIT 1") or die("No se pudo borrar en zonas");
    $db->sql_query("DELETE FROM groups WHERE gid='$del' LIMIT 1") or die("No se pudo borrar groups");

    // Eliminamos vendedores de la concesionaria eliminada
    $db->sql_query("INSERT INTO delete_users ( SELECT uid,gid,super,name FROM users WHERE gid=".$del.");");
    $db->sql_query("DELETE FROM users WHERE gid=".$del.";");
}

// Inicio del modulo
// Verificamos que ya haya un ciclo de venta establecido
$sql="SELECT COUNT(*) AS total_etapas FROM crm_campanas;";
$res=$db->sql_query($sql);
list($no_etapas_ciclo)= $db->sql_fetchrow($res);
if($no_etapas_ciclo <1)
{
    $_html .= "<p><br><br>Para poder crear una Distribuidora, usted necesita tener configurado su <b>ciclo de venta</b>, tenga en cuenta que al momento de crear una distribuidora, ya no podrá modificar el ciclo de venta.</p>";
}
else
{
    //lista de usuarios
    $array_gids=array();
    $sql_count="select gid,count(gid) as total FROM crm_contactos WHERE gid>0 GROUP BY gid ORDER BY gid;";
    $res_count=$db->sql_query($sql_count);
    if($db->sql_numrows($res_count) > 0)
    {
        while(list($gid,$total)=$db->sql_fetchrow($res_count))
        {
            $gid=str_pad($gid,4,"0",STR_PAD_LEFT);
            $array_gids[$gid]=$total;
        }
    }

    $_html = "<script type='text/javascript' src='".$_themedir."/jquery/crm_groups_fuentes.js'></script>
         <script>
            function del(id,name,no_prospectos)
            {
                if(no_prospectos > 0)
                {
                    alert('No se puede eliminar la Distribuidora, por que tiene prospectos asignados');
                }
                else
                {
                    if (confirm('Esta seguro que desea eliminar a la Distribuidora '+name))
                        location.href=('index.php?_module=$_module&del='+id);
                }
            }
          </script>";
    $_html .= "<script>function desbloquea(id,name){if (confirm('Esta seguro que desea desbloquear la distribuidora '+name)) location.href=('index.php?_module=$_module&desbloquea='+id);}</script>";
    $_html .= "<script>function newgroup(){var conc = prompt('Ingrese el nombre de la nueva distribuidora');if (conc) location.href=('index.php?_module=$_module&new='+conc);}</script>\n";
    $_html .= "<div class=\"title\">Lista de Distribuidoras</div><br>\n";
    $_html .= "Aquí se muestra la lista de los grupos de usuarios.<br>\n";
   
    // $_html .= "Para ver la lista de miembros del grupo dé click al nombre.<br>\n";
    $_html .= $error;
    
    /*$_html .= "<br>
        <table width='40%' align='center' border='0'>
            <thead>
                <tr>
                    <td align='left' colspan='2' height='30'>Derechos de uso</td>
                </tr>
            </thead>
            <tbody>
                <tr class='row2' height='30'>
                    <td>Derechos de uso</td>
                    <td>$_licenses</td>
                </tr>
		<tr class='row1' height='30'>
		    <td>Derechos de uso asignados</td>
		    <td>$_licenses_used</td>
		</tr>
		<tr class='row2' height='30'>
		    <td>Derechos de uso libres</td>
		    <td>$_licenses_not_used</td>
		</tr>
            </tbody>
        </table>";*/
    
    $_html .= "<table width='80%' align='center' border='0'><tr><td>
               <table class='tablesorter' width='80%' cellspacing='3'  cellpadding='3'>
               <thead><tr style=\"font-weight:bold;\"><th>Gid</th><th>Nombre</th>
                      <th colspan=\"4\" align=\"center\">Acci&oacute;n</th>
                      <th>No de prospectos</th></tr></thead><tbody>";
    $result = $db->sql_query("SELECT g.gid, g.name,g.active FROM groups AS g WHERE 1 ORDER BY g.gid;") OR die("Error al consultar db: ".print_r($db->sql_error()));
    while (list($id, $name, $active) = htmlize($db->sql_fetchrow($result)))
    {
        $id=str_pad($id,4,"0",STR_PAD_LEFT);
        $gid_total=( $array_gids[$id] + 0);
	$_html .=  "<tr class=\"row".(($c++%2)+1)."\">"
              ."<td>$id</td>"
              ."<td>$name</td>"
              ."<td><a href=\"index.php?_module=$_module&_op=bloquea&gid=$id\"><img src=\"../img/cross.gif\" onmouseover=\"return escape('Bloquear')\"  border=0></a></td>"
              ."<td><a href=\"index.php?_module=$_module&_op=reubicar&gid=$id\"><img src=\"../img/mexico.gif\" onmouseover=\"return escape('Reubicar')\"  border=0></a></td>"
              /*."<td><a href=\"index.php?_module=$_module&_op=categoria&gid=$id\"><img src=\"../img/categorias.png\" onmouseover=\"return escape('Categoria')\"  border=0></a></td>"*/
              ."<td><a href=\"#\" onclick=\"del('$id','$id','$gid_total')\"><img src=\"../img/del.gif\" onmouseover=\"return escape('Borrar')\"  border=\"0\"></a></td>
                <td>";
                if($active == false)
                {
                    $_html .="<a href=\"#\" onclick=\"desbloquea('$id','$id')\"><img src=\"../img/lock.gif\" width='18' height='18' onmouseover=\"return escape('Desbloquear Concesionaria')\"  border=0></a>";
                }
              $_html .="</td><td align='center'>$gid_total</td></tr>";
    }
    $_html .=  '<center></tbody><thead><tr class="row".(($c++%2)+1)."\">
                <td colspan="8" align="right">
		</td></tr></thead></table></td></tr></table><br>';
    if($_licenses_not_used > 0)
        $_html .=  '<INPUT TYPE="submit" VALUE="Crear una distribuidora" onclick="window.location=\'index.php?_module=Concesionarias&_op=new\'" >';
    
    $_html .=  '</center>';

/*Texto anterior en la variable _html
<td colspan='8' align='right'><a href='index.php?_module=Concesionarias&_op=new'><span class='parrafo'><center>Crear una Distribuidora</center></span></a></td></tr></thead></table></td></tr></table><br>"*/

    $_html .= "<p>¿Necesita ayuda? De un clic en el ícono.</p>
          <a href=\"../admin/Ayuda/ayuda_distribuidoras.php\" onClick=\"return popup(this, 'notes')\"><img src=\"../img/ayuda.gif\" alt=\"Ayuda\" /></a>";
	  
 
    #global $_admin_menu2;
    #$_admin_menu2 .= "<table><tr><td></td><td><a href=\"index.php?_module=$_module&_op=new\" > Crear Distribuidor</a></td></tr></table>";
}
?>