<?
  if (!defined('_IN_ADMIN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}


  global $db, $asignar_a, $submit, $seleccionar, $buscar_gid, $order,$_site_title;

  $_site_title = "Cancelar contactos";

  global $submit, $nombre, $apellido_paterno, $apellido_materno, $telefono, $contacto_id, $no_asignados, $order;

  $contacto_id_bk = $contacto_id;
  $nombre_bk = $nombre;
  $apellido_paterno_bk = $apellido_paterno;
  $apellido_materno_bk = $apellido_materno;
  if (!$order) $order = "contacto_id";
  if ($no_asignados) {$no_asignados_checked = "CHECKED"; $where .= "AND uid=0 ";}
  if ($contacto_id)         $where .= "AND c.contacto_id LIKE '%$contacto_id%' ";
  if ($nombre)           $where .= "AND c.nombre LIKE '%$nombre%' ";
  if ($apellido_paterno) $where .= "AND c.apellido_paterno LIKE '%$apellido_paterno%'";
  if ($apellido_materno) $where .= "AND c.apellido_materno LIKE '%$apellido_materno%'";
  if ($telefono)         $where .= "AND (c.tel_casa LIKE '%$telefono%' OR c.tel_oficina LIKE '%$telefono%' "
                                  ."OR c.tel_movil LIKE '%$telefono%' OR c.tel_otro LIKE '%$telefono%') ";
  if ($buscar_gid > 0)              $where .= "AND gid='$buscar_gid' ";
  if ($buscar_gid == "0")              $where .= "AND gid='0' ";
  if (!$where) $where = "AND 0";
  $sql = "SELECT c.contacto_id, c.contrato_id, c.nombre, c.apellido_paterno, c.apellido_materno, c.tel_casa, c.tel_oficina, c.tel_movil, c.tel_otro, c.gid"
        ." FROM crm_contactos AS c  WHERE 1 $where ORDER BY c.`$order`";//OR gid='0'
  $result = $db->sql_query($sql) or die("Error al leer".print_r($db->sql_error()));
  if ($db->sql_numrows($result) > 0)
  {
    $lista_contactos .= "<table style=\"width:100%;\">";
    $lista_contactos .= "<thead><tr><td><a  href=\"index.php?_module=$_module&_op=$_op&order=contacto_id&buscar_gid=$buscar_gid\" ><font color=\"white\">ID</font></a></td><td><a  href=\"index.php?_module=$_module&_op=$_op&order=nombre&buscar_gid=$buscar_gid\" ><font color=\"white\">Nombre</font></a></td><td><font color=\"white\">Último contacto</font></a><td><font color=\"white\">Asignado a</font></a></td></td><td>Cancelar</td></tr></thead>";//
    while (list($contacto_id, $contrato_id, $nombre, $apellido_paterno, $apellido_materno, $t1, $t2, $t3, $t4, $gid) = htmlize($db->sql_fetchrow($result)))
    {
      //obtener el último contacto
      $sql = "SELECT timestamp FROM crm_campanas_llamadas_log WHERE contacto_id='$contacto_id' ORDER BY timestamp DESC LIMIT 1";
      $result2 = $db->sql_query($sql) or die("Error al leer".print_r($db->sql_error()));
      list($timestamp) = htmlize($db->sql_fetchrow($result2));
      if ($timestamp)
      {
//         $timestamp = date("d-m-Y",strtotime($timestamp));
        $timestamp = time() - strtotime($timestamp);
        if ($timestamp > 0)
        {
          $timestamp = $timestamp / 60 / 60; //entre 60 segs, entre 60 mins
          $timestamp = sprintf("%u",$timestamp);//entero
          
          $timestamp .= " hora".($timestamp!=1?"s":"");
        }
      }
      if ($t4) $t = $t4;
      if ($t3) $t = $t3;
      if ($t2) $t = $t2;
      if ($t1) $t = $t1;
      $telefono_ = $t;
      if ($gid)
      {
        $sql = "SELECT name FROM groups WHERE gid='$gid'";
        $result2 = $db->sql_query($sql) or die("Error");
        list($asignado_a) = htmlize($db->sql_fetchrow($result2));
      }
      else $asignado_a = "";
      $lista_contactos .= "<tr class=\"row".(++$row_class%2+1)."\">"
                          ."<td>$contacto_id</td>"
                          ."<td>$nombre $apellido_paterno $apellido_materno</td>"
                          ."<td>$timestamp</td>"
                          ."<td>$asignado_a</td>"
                          ."<td><img src=\"../img/del.png\" style=\"cursor:pointer;\" onclick=\"window.open('index.php?_module=$_module&_op=contacto_cancelar&contacto_id=$contacto_id&gid=$buscar_gid', 'Cancelación','location=no,resizable=yes,scrollbars=yes,navigation=no,titlebar=no,directories=no,width=400,height=175,left=0,top=0,alwaysraised=yes');\"></td>"
                        ."</tr>";
    }
    $select_users = "<select name=\"asignar_a\">";
    $result2 = $db->sql_query("SELECT uid, user FROM users WHERE gid='$gid'") or die("Error");
    while(list($a_uid, $a_user) = htmlize($db->sql_fetchrow($result2)))
    {
      $select_users .= "<option value=\"$a_uid\">$a_user</option>";
    }
    $select_users .= "</select>";
    $select_groups = "<select name=\"cambiar_a\">";
    $result2 = $db->sql_query("SELECT gid, name FROM groups WHERE gid!='$gid' ORDER BY gid") or die("Error");
    while(list($a_gid, $a_group) = htmlize($db->sql_fetchrow($result2)))
    {
      $select_groups .= "<option value=\"$a_gid\">$a_gid - $a_group</option>";
    }
    $select_groups .= "</select>";


    $lista_contactos .= "<tr class=\"row".(++$row_class%2+1)."\" style=\"text-align:center;\">"
                        ."<td colspan=6>"
                        ."<input name=\"all\" type=\"button\" onclick=\"allon();\" value=\"Todos\">&nbsp;"
                        ."<input name=\"none\" type=\"button\" onclick=\"alloff();\" value=\"Ninguno\"></td></tr>";

    $lista_contactos .= "</table>";
    
  }
  else $lista_contactos .= "<br><center>No se encontraron contactos con esos datos, por favor intente de nuevo.</center>";

  $nombre = $nombre_bk;
  $apellido_paterno = $apellido_paterno_bk;
  $apellido_materno = $apellido_materno_bk;
  $contacto_id = $contacto_id_bk;

$select_groups2 = "<select name=\"buscar_gid\">";
$select_groups2 .= "<option value=\"\">Todas</a>";
$select_groups2 .= "<option value=\"0\"".("0"===$buscar_gid?" SELECTED":"").">Ninguna</a>";
$result2 = $db->sql_query("SELECT gid, name FROM groups WHERE 1 ORDER BY gid") or die("Error");
while(list($a_gid, $a_group) = htmlize($db->sql_fetchrow($result2)))
{
  $select_groups2 .= "<option value=\"$a_gid\"".($a_gid==$buscar_gid?" SELECTED":"").">$a_gid - $a_group</option>";
}
$select_groups2 .= "</select>";
 ?>