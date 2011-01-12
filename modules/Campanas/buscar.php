<?
  if (!defined('_IN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}
global $db, $how_many, $from, $campana_id, $nombre, $apellido_paterno, $apellido_materno, 
        $submit, $status_id, $uid;


include_once("modules/Gerente/class_autorizado.php");
$window_opc = "'llamada','menubar=no,location=no,resizable=yes,scrollbars=yes,status=no,navigation=no,titlebar=no,directories=no,width=800,height=750,left=200,top=0,alwaysraised=yes'";
$how_many = 25;
if ($from < 1 || !$from) 
    $from = 0;

$result = $db->sql_query("SELECT gid FROM users WHERE uid='$uid' LIMIT 1") or die("Error en grupo ".print_r($db->sql_error()));
list($gid) = $db->sql_fetchrow($result);

$nombre_bk=$nombre;
$apellido_paterno_bk=$apellido_paterno;
$apellido_materno_bk=$apellido_materno;
$ciclo_de_venta_id_bk=$ciclo_de_venta_id;
$status_id_bk=$status_id;

if ($submit)
{
  if ($campana_id)		 $where .= "AND c.campana_id='$campana_id' ";
  if ($nombre)           $where .= "AND d.nombre LIKE '%$nombre%' ";
  if ($apellido_paterno) $where .= "AND d.apellido_paterno LIKE '%$apellido_paterno%' ";
  if ($apellido_materno) $where .= "AND d.apellido_materno LIKE '%$apellido_materno%' ";
  if ($status_id != "")        $where .= "AND c.status_id='$status_id' ";	
  $sql_all = "SELECT c.id, c.contacto_id, d.nombre, d.apellido_paterno, d.apellido_materno, c.status_id, c.campana_id,d.fecha_autorizado,d.fecha_firmado
  FROM crm_campanas_llamadas AS c, crm_contactos AS d
  WHERE  1 AND d.contacto_id=c.contacto_id AND d.uid='$uid'
  $where 
  ORDER BY c.status_id";
  $sql = "$sql_all LIMIT $from, $how_many";

  $result = $db->sql_query($sql) or die("Error al consultar campañas".print_r($db->sql_error()));
  if ($db->sql_numrows($result))
  {
    $tabla_campanas .= "<table width=\"100%\" border=0>\n";
    $tabla_campanas .= "<thead><tr>"
                        ."<td width='1%'>&nbsp;</td>"
                        ."<td>Nombre</td>"
                        ."<td>Apellido Paterno</td>"
                        ."<td>Apellido Materno</td>"
                        ."<td>Status</td>"
                        ."<td>Ciclo</td>"
                        ."<td>Acción</td>"
                        ."</tr></thead>\n";
    $status_ids = array();
    $sql = "SELECT c.campana_id, c.nombre FROM crm_campanas AS c, crm_campanas_groups AS g WHERE c.campana_id=g.campana_id AND g.gid='$gid' ORDER BY c.nombre";
    $result2 = $db->sql_query($sql) or die("Error en selects<br>$sql");
    while(list($id, $n) = $db->sql_fetchrow($result2)) $campanas_ids[$id] = $n;
	
    $sql = "SELECT status_id, nombre FROM crm_campanas_llamadas_status WHERE 1 ORDER BY orden";
    $result2 = $db->sql_query($sql) or die("Error en selects");
    while(list($id, $n) = $db->sql_fetchrow($result2)) $status_ids[$id] = $n;

    while (list($llamada_id, $contacto_id, $nombre, $apellido_paterno, $apellido_materno, $status_id, $campana_id,$fecha_autorizado,$fecha_firmado) =
            htmlize($db->sql_fetchrow($result)))
    {
        $objeto= new Fecha_autorizado ($db,$fecha_autorizado,$fecha_firmado);
        $color_semaforo=$objeto->Obten_Semaforo();
        $sql = "SELECT nombre, apellido_paterno";
        if ($lock) $status = "Activo";
        $tabla_campanas .= "<tr class=\"row".(($c++%2)+1)."\">"
                          ."<td><span style='background-color:{$color_semaforo}'>&nbsp;&nbsp;&nbsp;</span></td>"
                          ."<td>$nombre</td>"
                          ."<td>$apellido_paterno</td><td>$apellido_materno</td>"
                          ."<td>{$status_ids[$status_id]}</td>"
                          ."<td>{$campanas_ids[$campana_id]}</td>"
                          ."<td align=\"center\"><a href=\"#\" onclick=\"window.open('index.php?_module=$_module&_op=llamada&campana_id=$campana_id&llamada_id=$llamada_id&contacto_id=$contacto_id&nopendientes=1',$window_opc);\"><img src=\"img/phone.gif\" border=></a></td>"
    //                         ."<td><a href=\"javascript:void(0);\" onclick=\"del('$campana_id','$name');\"><img src=\"img/del.gif\" border=></a></td>"
                            ."</tr>\n";
    }
    $tabla_campanas .= "</table>\n";

    $result = $db->sql_query($sql_all) or die("Error al contar");
    $num_news = $db->sql_numrows($result);
    if ($num_news > $how_many)
    {
        if ($from > 0) 
            $paginacion_campanas .= "<a href=\"index.php?_module=$_module&_op=$_op&campana_id=$campana_id&orderby=$orderby&uid=$uid&submit=submit&from=".($from - $how_many)."\">&lt;</a>&nbsp; ";
        for ($i = 0; $i < $num_news; $i += $how_many)
        {
            $j++;
            if (!($i >= $from && $i <= ($from + $how_many - 1)))
            {
                $link1 = "<a href=\"index.php?_module=$_module&_op=$_op&campana_id=$campana_id&orderby=$orderby&uid=$uid&submit=submit&from=".($i)."\">";
                $link2 = "</a> ";
            }
            else { $link1 = "<b>"; $link2 = "</b>";}
            $paginacion_campanas .= "&nbsp;$link1$j$link2";
        }
        if (($from + $how_many) < $num_news) 
            $paginacion_campanas .= "&nbsp;<a href=\"index.php?_module=$_module&_op=$_op&campana_id=$campana_id&orderby=$orderby&uid=$uid&submit=submit&from=".($from + $how_many)."\">&gt;</a>";
    }
  }
}

$nombre=$nombre_bk;
$apellido_paterno=$apellido_paterno_bk;
$apellido_materno=$apellido_materno_bk;
$ciclo_de_venta_id=$ciclo_de_venta_id_bk;
$status_id=$status_id_bk;

$select_ciclo_de_venta = "<select name=\"campana_id\">";
$sql = "SELECT c.campana_id, c.nombre FROM crm_campanas AS c, crm_campanas_groups AS g WHERE c.campana_id=g.campana_id AND g.gid='$gid' ORDER BY c.nombre";
$result = $db->sql_query($sql) or die("Error en selects");
$select_ciclo_de_venta .= "<option value=\"\">Todos</option>";
while (list($id, $n) = $db->sql_fetchrow($result))
{
  if ($id == $campana_id) $selected=" SELECTED";
  else $selected="";
  $select_ciclo_de_venta .= "<option value=\"$id\"$selected>$n</option>";
}
$select_ciclo_de_venta .= "</select>";

$select_status = "<select name=\"status_id\">";
$sql = "SELECT status_id, nombre FROM crm_campanas_llamadas_status WHERE campana_id='0' OR campana_id='$campana_id' ORDER BY orden";
$result = $db->sql_query($sql) or die("Error en selects");
$select_status .= "<option value=\"\">Todos</option>";
while (list($id, $n) = $db->sql_fetchrow($result))
{
  if ($id == $status_id) $selected=" SELECTED";
  else $selected="";
  $select_status .= "<option value=\"$id\"$selected>$n</option>";
}
$select_status .= "</select>";
?>
