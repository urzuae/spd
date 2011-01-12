<?
  if (!defined('_IN_ADMIN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}

global $db, $campana_id, $gid, $uid, $fecha_ini, $fecha_fin ;
global $_admin_menu2, $_admin_menu,$_excel;

    
if ($fecha_ini) {
	$fecha_ini_o = $fecha_ini;
	$fecha_ini = date_reverse ( $fecha_ini );
	$where_fecha .= " AND fecha_importado>'$fecha_ini 00:00:00'";
}
if ($fecha_fin) {
	$fecha_fin_o = $fecha_fin;
	$fecha_fin = date_reverse ( $fecha_fin );
	$where_fecha .= " AND fecha_importado<'$fecha_fin 23:59:59'";
}

if($gid){
	$where_concesionaria = " AND gid = '$gid'";                       
}
$sql_noasignados = "SELECT COUNT(contacto_id) 
        FROM crm_contactos_no_asignados
        WHERE 1
        $where_fecha 
        $where_concesionaria";
$result = $db->sql_query($sql_noasignados) or die($sql_noasignados);
list($cuenta_no_asignados) = $db->sql_fetchrow( $result );

$sql_asignados = "SELECT COUNT(contacto_id) 
        FROM crm_contactos_no_asignados_finalizados
        WHERE motivo_fin = 'Se asigno'
        $where_fecha 
        $where_concesionaria";
$result = $db->sql_query($sql_asignados) or die($sql_asignados);
list($cuenta_asignados) = $db->sql_fetchrow( $result );

$sql_asignados_t = "SELECT COUNT(contacto_id) 
        FROM crm_contactos_no_asignados_finalizados
        WHERE motivo_fin != 'Se asigno'
        $where_fecha 
        $where_concesionaria";
$result = $db->sql_query($sql_asignados_t) or die($sql_asignados_t);
list($cuenta_asignados_t) = $db->sql_fetchrow( $result );


$tabla_contenido_prospectos .= "<tr class=\"row".(++$rowclass%2?"2":"1")."\" >
                      <td>$cuenta_no_asignados</td>
                      <td>$cuenta_asignados</td>
                      <td>$cuenta_asignados_t</td>
                    </tr>";

$titulo = "Reporte de prospectos no asignados a distribuidoras";
 $contenido_prospectos = "<table>
         <thead>
           <tr>
             <td rowspan=\"1\">Prospectos no asignados</td>
	         <td rowspan=\"1\">Prospectos asignados</td>
	         <td rowspan=\"1\">Prospectos cancelados</td>
	      </tr>
	     </thead>
	     $tabla_contenido_prospectos
	   </table>";
  $fecha_fin = $fecha_fin_o;
  $fecha_ini = $fecha_ini_o; 
  
  $select_groups = "<select name=\"gid\">";
  $result = $db->sql_query("SELECT gid,name FROM groups WHERE 1 ORDER BY gid") or die("Error al cargar grupos");
  $select_groups .= "<option value=\"\">Selecciona una distribuidora</option>\n";
  while(list($_gid,$name) = $db->sql_fetchrow($result)){
  	if ($_gid == $gid)
  	  $selected = " SELECTED";
  	else
  	  $selected = "";
  	$select_groups .= "<option value=\"$_gid\"$selected>$_gid - $name</option>";
  }
  $select_groups .= "</select>";
  
  $select_fuentes = "<select name=\"fuente_id\">
                     <option value=\"0\">Todos</option>";
 ?>