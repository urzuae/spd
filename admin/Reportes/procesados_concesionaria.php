<?
if (!defined('_IN_ADMIN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}

global $db, $campana_id, $fuente_id, $uid, $fecha_ini, $fecha_fin, $origen_id, $gid ;
global $_admin_menu2, $_admin_menu,$_excel;

$origen_id = $fuente_id;

if ($fecha_fin || $fecha_ini) {    
    if ($fecha_ini) {
        $fecha_ini_o = $fecha_ini;
        $fecha_ini = date_reverse ( $fecha_ini );
        $where_fecha .= " AND c.timestamp>'$fecha_ini 00:00:00'";
        $where_fecha_evento .= " AND e.timestamp>'$fecha_ini 00:00:00'";
    }
    if ($fecha_fin) {
        $fecha_fin_o = $fecha_fin;
        $fecha_fin = date_reverse ( $fecha_fin );
        $where_fecha .= " AND c.timestamp<'$fecha_fin 23:59:59'";
        $where_fecha_evento .= " AND e.timestamp<'$fecha_fin 23:59:59'";
    }
}


if($origen_id){
    $where_origen = " AND c.origen_id = '$origen_id'";
}

$fp = fopen('procesados_concesionaria.csv', 'w');

fputcsv($fp, split(',', "GID, TOTAL CONTACTOS, CUENTAS, PROMEDIO"));

$sql = "SELECT gid FROM groups";
$r = $db->sql_query($sql) or die($sql);
$groups = array();
while (list($gid) = $db->sql_fetchrow($r))
{
    $where_gid = " AND c.gid = '$gid'";
    $sql_total = "SELECT COUNT( c.contacto_id )
              FROM crm_contactos AS c
              WHERE 1
    $where_origen
    $where_gid";
    $result_t = $db->sql_query($sql_total) or die($sql_total);
    list ( $total_contactos ) = $db->sql_fetchrow ( $result_t );
    $sql_con = "SELECT COUNT(DISTINCT(e.llamada_id))
            FROM crm_contactos AS c,
                 crm_campanas_llamadas AS l,
                 crm_campanas_llamadas_eventos AS e
            WHERE c.contacto_id = l.contacto_id AND
                  e.llamada_id = l.id
    $where_fecha
    $where_origen
    $where_gid";
    $result = $db->sql_query($sql_con) or die($sql_con);
    list ( $cuenta ) = $db->sql_fetchrow ( $result );

    if($cuenta != 0)
    $promedio = number_format(100*($cuenta/$total_contactos),3,".","");
    else
    $promedio = 0;

    $tabla_contenido_autos .= "<tr class=\"row".(++$rowclass%2?"2":"1")."\" >
                 <td>$gid</td>
                      <td>$total_contactos</td>
                      <td>$cuenta</td>
                      <td>$promedio%</td>
                    </tr>";
    fputcsv($fp, split(',', "$gid, $total_contactos, $cuenta, $promedio"));
}

fclose($fp);

$titulo = "Reporte de prospectos procesados";
$link_excel = "procesados_concesionaria_excel".$l_excel;
$contenido_autos = "<table>
         <thead>
           <tr>
             <td rowspan=\"1\">Distribuidora</td>
             <td rowspan=\"1\">Total de prospectos</td>
             <td rowspan=\"1\">Usuarios con seguimiento</td>
             <td rowspan=\"1\">Porcentaje</td>
          </tr>
         </thead>
$tabla_contenido_autos
       </table>";
$fecha_fin = $fecha_fin_o;
$fecha_ini = $fecha_ini_o;
   /*
  $sql = "SELECT gid, name FROM groups";
  $r = $db->sql_query($sql) or die($sql);
  $groups = array();
  while (list($id, $n) = $db->sql_fetchrow($r)){
    if($gid == $id){
        $concesionaria_origen = $n;
        break;
    }
}
  $select_groups = "<select name=\"gid\">";
  $result = $db->sql_query("SELECT gid,name FROM groups WHERE 1 ORDER BY gid") or die("Error al cargar grupos");
  $select_groups .= "<option value=\"\">Selecciona una concesionaria</option>\n";
  while(list($_gid,$name) = $db->sql_fetchrow($result)){
    if ($_gid == $gid)
      $selected = " SELECTED";
    else
      $selected = "";
    $select_groups .= "<option value=\"$_gid\"$selected>$_gid - $name</option>";
  }
  $select_groups .= "</select>";
  */
$select_fuentes = "<select name=\"fuente_id\">
                     <option value=\"0\">Todos</option>";

$sql = "SELECT origen_id, nombre FROM crm_contactos_origenes";
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

echo "<a href=\"procesados_concesionaria.csv\">Descargar Reporte</a>";

?>
