<?
  if (!defined('_IN_ADMIN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}


  global $db, $asignar_a, $submit, $seleccionar, $buscar_gid;

  global $cambiar_a, $seleccionar2;
  if ($cambiar_a && $seleccionar2) //si se van a reasignar 
  {
    //buscar a que campaña lo meteremos
    $sql = "SELECT c.campana_id FROM crm_campanas_groups AS g, crm_campanas AS c WHERE c.campana_id=g.campana_id AND g.gid='$cambiar_a' ORDER BY c.campana_id  LIMIT 1"; //la primera que sea
    $result = $db->sql_query($sql) or die("Error al leer".print_r($db->sql_error()));
    list($campana_id) = $db->sql_fetchrow($result);
    
    $sql = "SELECT c.contacto_id" //buscar todos los que pudieran ser posibles
        ." FROM crm_contactos AS c  WHERE 1";//OR gid='0'
    $result = $db->sql_query($sql) or die("Error al leer".print_r($db->sql_error()));
    
    if ($db->sql_numrows($result) > 0)
    {
      while (list($contacto_id) = $db->sql_fetchrow($result)) //revisar si lo mandaron en el post ( => on)
      {
        $tmp = "chbx_$contacto_id";
        if (array_key_exists("$tmp", $_POST))
        { //cambiar al asignado
          //obtener desde a donde se lo quitamos
          $sql = "SELECT uid, gid FROM crm_contactos WHERE contacto_id='$contacto_id'";
          $r2 = $db->sql_query($sql) or die("Error al asignar".print_r($db->sql_error()));
          list($from_uid, $from_gid) = $db->sql_fetchrow($r2);
          $sql = "UPDATE crm_contactos SET gid='$cambiar_a', uid='0' WHERE contacto_id='$contacto_id'  ";//OR gid='0'
          $db->sql_query($sql) or die("Error al asignar".print_r($db->sql_error()));
          //guardar el cambio
          $sql = "INSERT INTO `crm_contactos_asignacion_log` (contacto_id,uid,from_uid,to_uid, from_gid, to_gid)VALUES('$contacto_id','0','$from_uid','0','$from_gid','$cambiar_a')";
          $db->sql_query($sql) or die($sql);
          $cambiados++;

          //ahora mandarlo a la primer campaña
          //checar primero si no está en alguna ya
          $sql = "SELECT id FROM crm_campanas_llamadas WHERE contacto_id='$contacto_id' LIMIT 1";
          $result2 = $db->sql_query($sql) or die("Error al leer".print_r($db->sql_error()));
          if (list($llamada_id) = $db->sql_fetchrow($result2))
            $sql = "UPDATE crm_campanas_llamadas SET campana_id='$campana_id' WHERE id='$llamada_id'";
          else 
            $sql = "INSERT INTO crm_campanas_llamadas (campana_id,status_id,fecha_cita)VALUES('$campana_id','-2','0000-00-00 00:00:00')";
          $db->sql_query($sql) or die("Error al leer".print_r($db->sql_error()));

        } 
      }
      $sql = "SELECT name FROM groups WHERE gid='$cambiar_a'";
      $result = $db->sql_query($sql) or die("Error al leer".print_r($db->sql_error()));
      list($nuevo_grupo) = $db->sql_fetchrow($result);
      if ($cambiados) $msg = "$cambiados contactos fueron cambiados a $nuevo_grupo.";
    } 
  }

  
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
    $lista_contactos .= "<thead><tr><td><a  href=\"index.php?_module=$_module&_op=$_op&order=contacto_id\" ><font color=\"white\">ID</font></a></td><td><a  href=\"index.php?_module=$_module&_op=$_op&order=nombre\" ><font color=\"white\">Nombre</font></a></td><td><font color=\"white\">Último contacto</font></a><td><font color=\"white\">Asignado a</font></a></td></td><td>Seleccionar</td></tr></thead>";//
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
                          ."<td style=\"cursor:pointer;\" "
                            ."onclick=\"location.href='../index.php?_module=Directorio&_op=contacto&contacto_id=$contacto_id';\">$contacto_id</td>"
                          ."<td  style=\"cursor:pointer;\" "
                            ."onclick=\"location.href='../index.php?_module=Directorio&_op=contacto&contacto_id=$contacto_id';\">$nombre $apellido_paterno $apellido_materno</td>"
                          ."<td>$timestamp</td>"
                          ."<td>$asignado_a</td>"
                          ."<td><input type=\"checkbox\" name=\"chbx_$contacto_id\" style=\"height:12;width:16;\"></td>"
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
                        ."<input name=\"none\" type=\"button\" onclick=\"alloff();\" value=\"Ninguno\"></td></tr>"
                        ."<tr class=\"row".(++$row_class%2+1)."\" style=\"text-align:center;\">"
                        ."<td colspan=6>"
                        ."Cambiar a agencia $select_groups"
                        ."<input type=\"submit\" name=\"seleccionar2\" value=\"Seleccionar\"></td></tr>";
    $lista_contactos .= "</table>";
    
  }
  else $lista_contactos .= "<br><center>No se encontraron contactos con esos datos, por favor intente de nuevo.</center>";

  $nombre = $nombre_bk;
  $apellido_paterno = $apellido_paterno_bk;
  $apellido_materno = $apellido_materno_bk;
  $contacto_id = $contacto_id_bk;

$select_groups = "<select name=\"cambiar_a\">";
$select_groups2 = "<select name=\"buscar_gid\">";
$select_groups2 .= "<option value=\"\">Todas</a>";
$select_groups2 .= "<option value=\"0\"".("0"===$buscar_gid?" SELECTED":"").">Ninguna</a>";
$result2 = $db->sql_query("SELECT gid, name FROM groups WHERE 1 ORDER BY gid") or die("Error");
while(list($a_gid, $a_group) = htmlize($db->sql_fetchrow($result2)))
{
  $select_groups .= "<option value=\"$a_gid\">$a_group</option>";
  $select_groups2 .= "<option value=\"$a_gid\"".($a_gid==$buscar_gid?" SELECTED":"").">$a_gid - $a_group</option>";
}
$select_groups .= "</select>";
$select_groups2 .= "</select>";
 ?>