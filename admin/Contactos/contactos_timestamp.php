<?
  if (!defined('_IN_ADMIN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}


global $db, $horas, $buscar_gid, $no_asignados, $cambiar_a;

  global $cambiar_a, $seleccionar2;
  if ($cambiar_a && $seleccionar2) //si se van a reasignar 
  {
    //buscar a que campaña lo meteremos
    $sql = "SELECT c.campana_id FROM crm_campanas_groups AS g, crm_campanas AS c WHERE c.campana_id=g.campana_id AND g.gid='$cambiar_a' ORDER BY c.campana_id  LIMIT 1"; //la primera
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
          $sql = "UPDATE crm_contactos SET gid='$cambiar_a', uid='0' WHERE contacto_id='$contacto_id'  ";//OR gid='0'
          $db->sql_query($sql) or die("Error al asignar".print_r($db->sql_error()));
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
		  		  //meter la asignación al log
		  $sql = "INSERT INTO crm_contactos_asignacion_log (contacto_id,uid)VALUES('$contacto_id','$uid')";
          $db->sql_query($sql) or die("Error al leer".print_r($db->sql_error()));
        } 
      }
      $sql = "SELECT name FROM groups WHERE gid='$cambiar_a'";
      $result = $db->sql_query($sql) or die("Error al leer".print_r($db->sql_error()));
      list($nuevo_grupo) = $db->sql_fetchrow($result);
      if ($cambiados) $msg = "$cambiados contactos fueron cambiados a $nuevo_grupo.";
    } 
  }
  
if ($horas)
{
  $sql = "SELECT llamada_id, contacto_id, uid, status_id, timestamp FROM crm_campanas_llamadas_log WHERE ADDTIME(timestamp,'$horas:00:00')<NOW() order by timestamp DESC ";
  $result = $db->sql_query($sql) or die("Error al leer".print_r($db->sql_error()));
  if ($db->sql_numrows($result) > 0)
  {
    $lista_contactos .= "<table style=\"width:100%;\">";
    $lista_contactos .= "<thead><tr><td>ID</td><td>Nombre</td><td>Último contacto</td><td>Asignado a</td></td><td>Seleccionar</td></tr></thead>";
    $contactos_id[] = array();
    while (list($llamada_id, $contacto_id, $uid, $status_id, $timestamp) = htmlize($db->sql_fetchrow($result)))
    {
      //quitar a los que ya están repetidos
      if (in_array($contacto_id,$contactos_id))
        continue;
      //meterlo para evitar repetirlo
      $contactos_id[] = $contacto_id;
      
      //buscar datos como a quien está asignado
      $sql = "SELECT nombre, apellido_paterno, apellido_materno, uid, gid FROM crm_contactos WHERE contacto_id='$contacto_id'";
      $result2 = $db->sql_query($sql) or die("Error al leer".print_r($db->sql_error()));
      list($nombre, $apellido_paterno, $apellido_aterno, $uid, $gid)  = htmlize($db->sql_fetchrow($result2));
      //ver si descartamos algunos de los encontrados
      if ($no_asignados && $gid)
        continue;
      if ($buscar_gid && $buscar_gid != $gid)
        continue;
      //buscar el nombre de la concesionaria
      if ($gid)
      {
        $sql = "SELECT name FROM groups WHERE gid='$gid'";
        $result2 = $db->sql_query($sql) or die("Error");
        list($asignado_a) = htmlize($db->sql_fetchrow($result2));
      }
      else $asignado_a = "";
      //darle formato en horas al timestamp
      if ($timestamp)
      {
        $timestamp = time() - strtotime($timestamp);
        if ($timestamp > 0)
        {
          $timestamp = $timestamp / 60 / 60; //entre 60 segs, entre 60 mins
          $timestamp = sprintf("%u",$timestamp);//entero
  
          $timestamp .= " hr";//.($timestamp!=1?"s":"")
        }
      }
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

    $select_groups = "<select name=\"cambiar_a\">";
    $result2 = $db->sql_query("SELECT gid, name FROM groups WHERE gid!='$gid' ORDER BY gid") or die("Error");
    while(list($a_gid, $a_group) = htmlize($db->sql_fetchrow($result2)))
    {
      $select_groups .= "<option value=\"$a_gid\">$a_group</option>";
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
}
//selects para buscar contactos


$select_groups2 = "<select name=\"buscar_gid\">";
$select_groups2 .= "<option value=\"\">Todas</a>";
$result2 = $db->sql_query("SELECT gid, name FROM groups WHERE 1 ORDER BY gid") or die("Error");
while(list($a_gid, $a_group) = htmlize($db->sql_fetchrow($result2)))
{

  $select_groups2 .= "<option value=\"$a_gid\"".($a_gid==$buscar_gid?" SELECTED":"").">$a_group</option>";
}
$select_groups2 .= "</select>";
if ($no_asignados) 
{
  $no_asignados_checked = "CHECKED"; 
  
}
 ?>