<?
  if (!defined('_IN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}

global $db, $uid, $orderby, $rsort;
include_once("class_autorizado.php");


  $select_modelo=Regresa_modelos($db);


$sql  = "SELECT gid, super FROM users WHERE uid='$uid'";
$result = $db->sql_query($sql) or die("Error");
list($gid, $super) = $db->sql_fetchrow($result);
if ($super > 6)
{
  $_html = "<h1>Usted no es un Gerente</h1>";
} else {

  global $asignar_a, $submit, $submit_seleccionar, $buscar_asignado;
  $array_para_ordenar = array();
  $ordered_contacto_ids = array();
  //crear arrays
  $prioridades = array();
  $sql = "SELECT prioridad_id, prioridad, color FROM crm_prioridades_contactos";
  $r = $db->sql_query($sql) or die($sql);
  while(list($prioridad_id, $prioridad, $prioridad_color) = $db->sql_fetchrow($r)){
	$prioridades[$prioridad_id] = $prioridad;
	$prioridades_color[$prioridad_id] = $prioridad_color;
  }
  //obtenemos todos los users posibles para evitar consultas posteriores
  $users = array();
  $sql = "SELECT uid, user FROM users WHERE gid = '$gid'";
  $r = $db->sql_query($sql) or die($sql);
  while(list($uid, $user) = $db->sql_fetchrow($r)){
	$users[$uid] = $user;
  }

  $r3 = $db->sql_query("SELECT nombre FROM crm_fuentes WHERE fuente_id='$origen_id' LIMIT 1");
  //obtenemos todas las campañas posibles para evitar consultas posteriores
  $origenes = array();
  $sql = "SELECT fuente_id, nombre FROM crm_fuentes ";
  $r = $db->sql_query($sql) or die($sql);
  while(list($c_id, $c_nombre) = $db->sql_fetchrow($r)){
	$origenes[$c_id] = $c_nombre;
  }
  
  if ($asignar_a && $submit_seleccionar) //si se van a reasignar 
  {
    //buscar a que campaï¿½a lo meteremos
    $sql = "SELECT c.campana_id FROM crm_campanas_groups AS g, crm_campanas AS c WHERE c.campana_id=g.campana_id AND g.gid='$gid' ORDER BY c.campana_id  LIMIT 1"; //la primera que sea parte de un ciclo
    $result = $db->sql_query($sql) or die("Error al leer".print_r($db->sql_error()));
    list($campana_id) = $db->sql_fetchrow($result);
    $sql = "SELECT c.contacto_id" //buscar todos los que pudieran ser posibles
        ." FROM crm_contactos AS c  WHERE (gid='$gid' )";//OR gid='0'
    $result = $db->sql_query($sql) or die("Error al leer".print_r($db->sql_error()));
    
    if ($db->sql_numrows($result) > 0)
      while (list($contacto_id) = $db->sql_fetchrow($result)) //revisar si lo mandaron en el post ( => on)
      {
        $tmp = "chbx_$contacto_id";
        if (array_key_exists("$tmp", $_POST))
        { 
          //buscar quien lo tiene
          $sql = "SELECT uid FROM crm_contactos WHERE contacto_id='$contacto_id'";//OR gid='0'
          $db->sql_query($sql) or die("Error al asignar".print_r($db->sql_error()));
          list($from_uid) = $db->sql_fetchrow($result2);
          if ($from_uid == $asignar_a) //reasignarselo a sï¿½ mismo no se puede
          {
            $no_asignados++;
            continue;
          }
          //cambiar al asignado
          $sql = "UPDATE crm_contactos SET uid='$asignar_a' WHERE contacto_id='$contacto_id' AND (gid='$gid' ) ";//OR gid='0'
          $db->sql_query($sql) or die("Error al asignar".print_r($db->sql_error()));
          //ahora mandarlo a la primer campaï¿½a
          //checar primero si no estï¿½ en alguna ya
          $sql = "SELECT id FROM crm_campanas_llamadas WHERE contacto_id='$contacto_id' LIMIT 1";
          $result2 = $db->sql_query($sql) or die("Error al leer".print_r($db->sql_error()));
          if (list($llamada_id) = $db->sql_fetchrow($result2))
            $sql = "UPDATE crm_campanas_llamadas SET campana_id='$campana_id' WHERE id='$llamada_id'";
          else 
            $sql = "INSERT INTO crm_campanas_llamadas (campana_id, contacto_id,status_id,fecha_cita)VALUES('$campana_id','$contacto_id','-2','0000-00-00 00:00:00')";
          $db->sql_query($sql) or die("Error al leer".print_r($db->sql_error()));
		  
		      //meter la asignaciï¿½n al log
		      $sql = "INSERT INTO crm_contactos_asignacion_log (contacto_id,uid,from_uid,to_uid)VALUES('$contacto_id','$uid','$from_uid','$asignar_a')";
          $db->sql_query($sql) or die("Error al leer".print_r($db->sql_error()));
        }
      }
      if ($no_asignados) $extra_js .= "alert('$no_asignados prospectos no fueron reasignados debido a que ya estaban asignados al mismo vendedor.');";
  }
  
  global $submit, $nombre, $apellido_paterno, $apellido_materno, $telefono, $contacto_id, $no_asignados, $order, $vehiculo;
  $nombre_bk = $nombre;
  $apellido_paterno_bk = $apellido_paterno;
  $apellido_materno_bk = $apellido_materno;
  $vehiculo_bk = $vehiculo;

  if (!$order) $order = "contacto_id";
//   if ($no_asignados) {$no_asignados_checked = "CHECKED"; $where .= "AND uid=0 ";}
  if ($contacto_id)      $where .= "AND c.contacto_id LIKE '%$contacto_id%' ";
  if ($nombre)           $where .= "AND c.nombre LIKE '%$nombre%' ";
  if ($apellido_paterno) $where .= "AND c.apellido_paterno LIKE '%$apellido_paterno%'";
  if ($apellido_materno) $where .= "AND c.apellido_materno LIKE '%$apellido_materno%'";
  if ($telefono)         $where .= "AND (c.tel_casa LIKE '%$telefono%' OR c.tel_oficina LIKE '%$telefono%' "
                                  ."OR c.tel_movil LIKE '%$telefono%' OR c.tel_otro LIKE '%$telefono%')";
  if ($buscar_asignado)       $where .= " AND uid='$buscar_asignado'";
  if ($where == "")  $where .= "AND uid=0 ";//los no asignados


  
  //ahora si hacemos el query limitado
  $sql = "SELECT c.contacto_id, c.origen_id, c.nombre, c.apellido_paterno, c.apellido_materno, c.tel_casa, c.tel_oficina, c.tel_movil, c.tel_otro, c.uid, c.prioridad, DATE_FORMAT(c.fecha_importado,'%d-%m-%Y %h:%m:%s'), l.intentos,c.fecha_autorizado,c.fecha_firmado"
        ." FROM crm_contactos AS c, crm_campanas_llamadas as l WHERE (gid='$gid' ) AND c.contacto_id = l.contacto_id $where ORDER BY c.prioridad DESC";//OR gid='0'

  $result = $db->sql_query($sql) or die("Error al leer".print_r($db->sql_error()));
  if ($db->sql_numrows($result) > 0)
  {

    while (list($c, $origen_id, $nombre, $apellido_paterno, $apellido_materno, $t1, $t2, $t3, $t4, $c_uid, $prioridad, $fecha_importado, $intento,$fecha_autorizado,$fecha_firmado) = htmlize($db->sql_fetchrow($result)))
    {
    	if ($t4) $t = $t4;
	    if ($t3) $t = $t3;
	    if ($t2) $t = $t2;
	    if ($t1) $t = $t1;
	    $telefono_ = $t;

	    //ponerle nombre al origen del array
		$origen = $origenes[$origen_id];
		//el vehiculo que quieren
		//hay un vehículo por cada contacto, si no no funciona la db
		$r3 = $db->sql_query("SELECT modelo FROM crm_prospectos_unidades WHERE contacto_id='$c' LIMIT 1");
		list($vehiculo) = $db->sql_fetchrow($r3);
    	if ($vehiculo_bk) 
    		if (strpos(strtoupper($vehiculo), strtoupper($vehiculo_bk)) === FALSE) 
    			continue;
    	//el usuario al que está asignado se saca del array
		if ($c_uid)
        {
			$asignado_a = $users[$c_uid];
	    }
		else
            $asignado_a = "";

        $objeto= new Fecha_autorizado ($db,$fecha_autorizado,$fecha_firmado);
        $color_semaforo=$objeto->Obten_Semaforo();

		$contactos_id[] = $c;
        $contactos_id_para_sortear[$c] = $c;  
		$asignados_a[$c] = $asignado_a;
		$last_asignados_a[$c] = $ultimo_uid;
		$nombres[$c] = "$nombre<br>$apellido_paterno $apellido_materno";
        $autorizados[$c]=$color_semaforo;
		$origenes[$c] = $origen;
		$origenes_id[$c] = $origen_id;
		$vehiculos[$c] = $vehiculo;
		$esperas[$c] = $ultimo_contacto_timestamp;
		$ultimo_contactos_ts[$c] = $ultimo_contacto_timestamp_bk;
		$prioridad_arr[$c] = $prioridades[$prioridad];
		$prioridades_arr[$c] = $prioridad;
		$color_prioridad[$c] = $prioridades_color[$prioridad];
		$fechas_importado[$c] = $fecha_importado; 
		$intentos[$c] = $intento; 
		$counter++; //para saber cuantos estamos mostrando	  
    }
	if (count($contactos_id) > 0)
    {
		    //ordenar la tabla por los datos que solicitan
	    switch($orderby)
	    {
		    case "origen_id": $array_para_ordenar = &$origenes_id; 
    // 		                  $rsort = 0;
						      break;
		    case "prioridad": $array_para_ordenar = &$prioridades_arr; 
  		                  $rsort = 1;
						      break;
		    case "nombre": $array_para_ordenar = &$nombres;
    // 		                  $rsort = 0;
		                      break; //por referencia para evitar que copie
		    case "fecha_importado": $array_para_ordenar = &$fechas_importado;
    // 		                  $rsort = 1;
						      break;
		    case "ultimo_contacto": $array_para_ordenar = &$ultimo_contactos_ts;
    // 		                  $rsort = 1;
						      break;
		    case "intentos": $array_para_ordenar = &$intentos; 
    // 		                  $rsort = 0;
						      break;	
		    case "asignado_a": $array_para_ordenar = &$asignados_a; 
    // 		                  $rsort = 0;
						      break;	
        case "vehiculo": $array_para_ordenar = &$vehiculos; 
    //                      $rsort = 0;
                  break;
		    default: $array_para_ordenar = &$prioridades_arr;
     		                  $rsort = 1;
	    }
	    if (!$rsort)
		    asort($array_para_ordenar); //ordenar por valor y conservar asociaciï¿½n de keys
	    else
		    arsort($array_para_ordenar); //ordenar por valor  en orden inverso y conservar asociaciï¿½n de keys
	    foreach ($array_para_ordenar AS $key=>$value)
	    {
		    $ordered_contacto_ids[] = $key;//echo $key."->$value<br>";
	    }

      	$lista_contactos .= "<center><div id=\"loading\"><img src=\"img/loading.gif\"></div></center>"; 
        $lista_contactos .= "<table  id=\"tabla_contactos\"  class=\"tablesorter\">";//style=\"width:100%;\"

            $lista_contactos .= "<thead><tr>
    <th width='7%'>Campa&ntilde;a</th>
    <th width='6%'>Prioridad</th>
    <th>Nombre</th>
    <th>Fecha de registro</th>
    <th width='6%'>Intentos</th>
    <th>Tiempo de espera (hrs)</font></th>
    <th width='8%'>Producto</th>
    <th>Asignado anteriormente a</th>
    <th width='8%'>Asignado a</th>
    <th width='8%'>Seleccionar</th>
    <th width='7%'>Cancelar</th>
    </tr></thead>";//
    	$lista_contactos .= "<tbody>";
	    foreach ($ordered_contacto_ids AS $c)
	    {
	      
          $lista_contactos .= "<tr style=\"height:35px;\" id=\"$c\">\n"
						      ."<td>{$origenes[$c]}</td>\n"
                              ."<td style=\"background-image:none;background-color: {$color_prioridad[$c]}\" style=\"background-image:none\">$prioridad_arr[$c]</td>\n"
                              ."<td  style=\"cursor:pointer;\" \n"
                              ."onclick=\"location.href='index.php?_module=Directorio&_op=contacto&contacto_id=$c&last_module=$_module&last_op=$_op';\" NOWRAP>{$nombres[$c]}&nbsp;&nbsp;<span style='background-color:{$autorizados[$c]}'>&nbsp;&nbsp;&nbsp;</span></td>\n"
                              ."<td nowrap=\"nowrap\">{$fechas_importado[$c]}</td>\n"
                              ."<td>{$intentos[$c]}</td>\n"
                              ."<td id=\"espera_$c\" class=\"espera\"></td>\n"  //{$esperas[$c]}
						      ."<td>{$vehiculos[$c]}</td>\n"
						      ."<td id=\"ultimo_vendedor_$c\" class=\"ultimo_vendedor\"></td>\n"
						      ."<td>{$asignados_a[$c]}</td>\n"
                              ."<td><input type=\"checkbox\" name=\"chbx_$c\" style=\"height:12;width:16;\" ></td>\n"
                              ."<td><img src=\"img/del.png\" style=\"cursor:pointer;\" onclick=\"window.open('index.php?_module=$_module&_op=contacto_cancelar&contacto_id=$c', 'Cancelación','location=no,resizable=yes,scrollbars=yes,navigation=no,titlebar=no,directories=no,width=400,height=175,left=0,top=0,alwaysraised=yes');\"></td>\n"
                            ."</tr>\n";
	    }
  	  $lista_contactos .= "</tbody>";
  	  $lista_contactos .= "</table>";
  	  
  	  

  	  
  	  
      $select_users = "<select name=\"asignar_a\">";
      $result2 = $db->sql_query("SELECT uid, user FROM users WHERE gid='$gid' AND super='8'") or die("Error");
      while(list($a_uid, $a_user) = htmlize($db->sql_fetchrow($result2)))
      {
        $select_users .= "<option value=\"$a_uid\">$a_user</option>";
      }
      $select_users .= "</select>";
      
      
      
      $lista_contactos .= "<br><br><table style=\"width:100%\">";
      $lista_contactos .= "<tr class=\"row".(++$row_class%2+1)."\" style=\"text-align:center;\">"
                          ."<td colspan=11>"
                          ."<input name=\"all\" type=\"button\" onclick=\"allon();\" value=\"Todos\">&nbsp;"
                          ."<input name=\"none\" type=\"button\" onclick=\"alloff();\" value=\"Ninguno\">"
                          ."</td></tr>"
                          ."<tr class=\"row".(++$row_class%2+1)."\" style=\"text-align:center;\">"
                          ."<td colspan=11>"
                          ."Asignar a $select_users"
                          ."<input type=\"submit\" name=\"submit_seleccionar\" value=\"Seleccionar\">"
                          ."</td></tr>";
        //                  ."<tr class=\"row".(++$row_class%2+1)."\" style=\"text-align:center;\">";
    	$lista_contactos .= "</table>";

    	
    }//si hay algo que mostrar
  }//sql numrows
  else $lista_contactos .= "<br><center>No se encontraron contactos con esos datos, por favor intente de nuevo.</center>";

	$nombre = $nombre_bk;
	$apellido_paterno = $apellido_paterno_bk;
	$apellido_materno = $apellido_materno_bk;
    $vehiculo = $vehiculo_bk; 
	$select_users2 = "<select name=\"buscar_asignado\">";
	$select_users2 .= "<option value=\"\">No asignados</option>";//esto era antes Todos
	$result2 = $db->sql_query("SELECT uid, user FROM users WHERE gid='$gid' AND super='8' ") or die("Error");
	while(list($a_uid, $a_user) = htmlize($db->sql_fetchrow($result2)))
	{
	  $select_users2 .= "<option value=\"$a_uid\"".($a_uid==$buscar_asignado?" SELECTED":"").">$a_user</option>";
	}
	$select_users2 .= "</select>";

}
global $jquery;
//es rsort por que los pops van al revés
$ordered_contacto_ids = array();
asort($array_para_ordenar);
foreach ($array_para_ordenar AS $key=>$value)
{
	    $ordered_contacto_ids[] = $key;//echo $key."->$value<br>";
}
if (isset($ordered_contacto_ids))
{

	$jsarray = "var array_contacto_ids = [];\n";
	$jsarray_index = 0;
	foreach($ordered_contacto_ids as $c)
	{
			$jsarray .= "array_contacto_ids[$jsarray_index] = $c;\n";
			$jsarray_index++;
	}	
}
else $jsarray = "var array_contacto_ids = Array();\n";


function Regresa_modelos($db)
{
    $buffer='';
    $sql_uni="SELECT unidad_id,nombre FROM crm_unidades ORDER BY nombre;";
    $res_uni=$db->sql_query($sql_uni);
    if($db->sql_numrows($res_uni)>0)
    {
        $buffer.='<select name="vehiculo">
                    <option value="">Todos</option>';
        while($fila = $db->sql_fetchrow($res_uni))
        {
            $buffer.="<option value='".$fila['nombre']."'>".$fila['nombre']."</option>";
        }
        $buffer.='</select>';
    }
    return $buffer;
}
 ?>