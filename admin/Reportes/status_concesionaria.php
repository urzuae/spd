<?
  if (!defined('_IN_ADMIN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}

global $db, $campana_id, $gid, $uid, $fecha_ini, $fecha_fin, $fuente_id ;
global $_admin_menu2, $_admin_menu,$_excel;

$origen_id = $fuente_id;

if ($fecha_fin || $fecha_ini)
{    
    if ($fecha_ini)
    {
    	$l_excel .= "&fecha_ini=$fecha_ini";
		$fecha_ini_o = $fecha_ini;
		$fecha_ini = date_reverse ( $fecha_ini );
		$where_fecha .= " AND e.timestamp>'$fecha_ini 00:00:00'";
		$where_fecha_total .= " AND fecha_importado>'$fecha_ini 00:00:00'";
	}
	if ($fecha_fin)
    {
		$l_excel .= "&fecha_fin=$fecha_fin";
		$fecha_fin_o = $fecha_fin;
		$fecha_fin = date_reverse ( $fecha_fin );
		$where_fecha .= " AND e.timestamp<'$fecha_fin 23:59:59'";
		$where_fecha_total .= " AND fecha_importado<'$fecha_fin 23:59:59'";
	}
}

if($origen_id)
{
    $where_origen = " AND c.origen_id = '$origen_id'";
}
$total_contactos=0;
$total_contactos_finalizados=0;
$cuenta=0;
$cuenta_finalizadas=0;
if($gid)
{
    // Contamos por crm_contactos
    $sql_total = "SELECT COUNT(DISTINCT(c.contacto_id)) FROM crm_contactos AS c
                WHERE c.gid = '$gid' $where_fecha_total $where_origen";
    $total_contactos=ejecuta_query($db,$sql_total);


    // Contamos por crm_contactos_finalizados
    $sql_total_f = "SELECT COUNT(DISTINCT(c.contacto_id)) FROM crm_contactos_finalizados AS c
                WHERE c.gid = '$gid' $where_fecha_total $where_origen";
    $total_contactos_finalizados=ejecuta_query($db,$sql_total_f);

    // Contamos por llamadas
    $sql_con = "SELECT COUNT( DISTINCT (e.llamada_id) ) FROM crm_campanas_llamadas AS l, crm_contactos AS c,
                crm_campanas_llamadas_eventos AS e
                WHERE l.contacto_id = c.contacto_id AND c.gid = '$gid' AND e.llamada_id = l.id
                $where_fecha $where_fecha_total $where_origen";
    $cuenta=ejecuta_query($db,$sql_con);

    // Contamos por llamadas finalizadas
    $sql_con = "SELECT COUNT( DISTINCT (e.llamada_id) ) FROM crm_campanas_llamadas_finalizadas AS l, crm_contactos_finalizados AS c,
                crm_campanas_llamadas_eventos AS e
                WHERE l.contacto_id = c.contacto_id AND c.gid = '$gid' AND e.llamada_id = l.id
                $where_fecha $where_fecha_total $where_origen";
    $cuenta_finalizadas=ejecuta_query($db,$sql_con);
}
else if($where_fecha_total != "")
{
    $sql_total = "SELECT COUNT(DISTINCT(c.contacto_id)) FROM crm_contactos AS c WHERE 1 $where_fecha_total $where_origen";
    $total_contactos=ejecuta_query($db,$sql_total);

    $sql_total_f = "SELECT COUNT(DISTINCT(c.contacto_id)) FROM crm_contactos_finalizados AS c WHERE 1 $where_fecha_total $where_origen";
    $total_contactos_finalizados=ejecuta_query($db,$sql_total_f);

    $sql_con = "SELECT COUNT( DISTINCT (e.llamada_id)) FROM crm_campanas_llamadas AS l, crm_contactos AS c,
                crm_campanas_llamadas_eventos AS e
                WHERE l.contacto_id = c.contacto_id AND e.llamada_id = l.id $where_fecha $where_fecha_total $where_origen";
    $cuenta=ejecuta_query($db,$sql_con);
    // Contamos por llamadas finalizadas

    $sql_con = "SELECT COUNT( DISTINCT (e.llamada_id)) FROM crm_campanas_llamadas_finalizadas AS l, crm_contactos_finalizados AS c,
                crm_campanas_llamadas_eventos AS e
                WHERE l.contacto_id = c.contacto_id AND e.llamada_id = l.id $where_fecha $where_fecha_total $where_origen";
    $cuenta_finalizadas=ejecuta_query($db,$sql_con);

}
else
{
    $sql_total = "SELECT COUNT(DISTINCT(c.contacto_id)) FROM crm_contactos AS c WHERE 1 $where_origen";
    $total_contactos=ejecuta_query($db,$sql_total);

    $sql_total_f = "SELECT COUNT(DISTINCT(c.contacto_id)) FROM crm_contactos_finalizados AS c WHERE 1 $where_origen";
    $total_contactos_finalizados=ejecuta_query($db,$sql_total_f);
   
    $sql_con = "SELECT COUNT(DISTINCT(e.llamada_id)) FROM crm_contactos AS c,
                crm_campanas_llamadas AS l, crm_campanas_llamadas_eventos AS e
                WHERE c.contacto_id = l.contacto_id AND e.llamada_id = l.id
                $where_origen";   
    $cuenta=ejecuta_query($db,$sql_con);
    $sql_con = "SELECT COUNT(DISTINCT(e.llamada_id)) FROM crm_contactos_finalizados AS c,
                crm_campanas_llamadas_finalizadas AS l, crm_campanas_llamadas_eventos AS e
                WHERE c.contacto_id = l.contacto_id AND e.llamada_id = l.id
                $where_origen";
    $cuenta_finalizadas=ejecuta_query($db,$sql_con);
}

$promedio = 0;
if($cuenta != 0)
  $promedio = number_format(100*($cuenta/$total_contactos),3,".","");

$promedio_fin=0;
if($cuenta_finalizadas != 0)
    $promedio_fin =  number_format(100*($cuenta_finalizadas/$total_contactos_finalizados),3,".","");


$tabla_contenido_autos .= "<tr height='25'>
                      <td>$total_contactos</td>
                      <td>$cuenta</td>
                      <td>$promedio%</td>
                    </tr>";
$tabla_contactos_finalizados="<tr height='25'>
                      <td>$total_contactos_finalizados</td>
                      <td>$cuenta_finalizadas</td>
                      <td>$promedio_fin%</td>
                    </tr>";

$titulo = "Reporte de prospectos atendidos";
$link_excel = "status_concesionaria_excel".$l_excel;
$contenido_autos = "<table width='60%' align='center'>
         <thead>
           <tr height='25'>
             <td rowspan=\"1\" align=\"center\">Prospectos recibidos</td>
	         <td rowspan=\"1\" align=\"center\">Prospectos con seguimiento</td>
	         <td rowspan=\"1\" align=\"center\">Porcentaje</td>
	      </tr>
	     </thead>
	     $tabla_contenido_autos
         <thead>
           <tr height='25'>
             <td rowspan=\"1\" align=\"center\">Prospectos finalizados</td>
	         <td rowspan=\"1\" align=\"center\">Prospectos finalizados con seguimiento</td>
	         <td rowspan=\"1\" align=\"center\">Porcentaje</td>
	      </tr>
          </thead>
            $tabla_contactos_finalizados
          <thead>
           <tr height='25'>
             <td rowspan=\"1\" align=\"center\">Total de Prospectos</td>
             <td rowspan=\"1\" align=\"center\">Total de Prospectos en seguimiento</td>
             <td rowspan=\"1\" align=\"center\">% Prospectos / P. Seguimiento</td>

	      </tr>
          </thead>
            <tr height='25' >
                      <td>".(($total_contactos + $total_contactos_finalizados) + 0)."</td>
                      <td>".(($cuenta + $cuenta_finalizadas) + 0)."</td>";
$promedio_total=0;
if((($total_contactos + $total_contactos_finalizados) + 0) != 0)
    $promedio_total = number_format(100*((($cuenta + $cuenta_finalizadas) + 0)/(($total_contactos + $total_contactos_finalizados) + 0)),3,".","");

        $contenido_autos .= "<td>".$promedio_total."%</td>
            </tr>
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

$sql = "SELECT fuente_id, nombre FROM crm_fuentes WHERE fuente_id > 1 ORDER BY fuente_id ASC";
$result = $db->sql_query($sql) or die("Error");
while(list($origen_id, $nombre_origen) = $db->sql_fetchrow($result)){
	if($fuente_id == $origen_id){
	  $nombre_fuente = $nombre_origen;
	  $selected = " SELECTED";
	}
	else
	  $selected = "";
	$select_fuentes .= "<option value=\"$origen_id\" $selected>$nombre_origen</option>";
}
$select_fuentes .= "</select>";

/** funcion que realiza el query**/
function ejecuta_query($db,$sql)
{
    $total=0;
    $result = $db->sql_query($sql) or die($sql);
    if($db->sql_numrows($result) > 0)
    {
        list($cuenta)=$db->sql_fetchrow( $result );
        $total=$cuenta;
    }
    return $total;
}
 ?>