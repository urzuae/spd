<?
  if (!defined('_IN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}
global $db, $how_many, $from, $campana_id, $nombre, $apellido_paterno, $apellido_materno, 
        $submit, $status_id, $ciclo_de_venta_id, $uid, $orderby, $rsort, $open;
/*
$sql  = "SELECT gid, super FROM users WHERE uid='".$_COOKIE['_uid']."'";
$result = $db->sql_query($sql) or die("Error");
list($gid, $super) = $db->sql_fetchrow($result);
if (!$super)
{
  die("<h1>Usted no es un Gerente</h1>");
}*/
global $gid;
if (!$gid) header("location:index.php?_module=$_module");

  $sql = "SELECT name FROM groups WHERE gid='$gid'";
  $r2 = $db->sql_query($sql) or die("<br>Error".print_r($db->sql_error()));
  list($nombre) = $db->sql_fetchrow($r2);
//ESTO ES CASI UNA COPIA DE monitoreo_vendedor pero en un ciclo UID

$window_opc = "'llamada','menubar=no,location=no,resizable=yes,scrollbars=yes,status=no,navigation=no,titlebar=no,directories=no,width=800,height=750,left=200,top=0,alwaysraised=yes'";
$how_many = 25;
if ($from < 1 || !$from) $from = 0;
if ($open) $array_tabs_abiertos = explode("-",$open);
/*
$sql = "SELECT uid, name FROM users WHERE gid='$gid'";
$result_ = $db->sql_query($sql) or die("Error al consultar usuarios ".print_r($db->sql_error()));
while (list($uid, $uname) = htmlize($db->sql_fetchrow($result_)))
{ //while de uid

		//esto es un headercito para el título de la campana y colapsar
		
		$tabla_campanas .=
	"<table style=\"text-align: left; width: 100%;\" border=\"0\" cellpadding=\"2\" cellspacing=\"2\"> <tbody> 
		<tr style=\"cursor:pointer\" onclick=\"var v=document.getElementById('bloque_uid_$uid');	var i=document.getElementById('img_uid_$uid');	if(v.style.display=='none'){v.style.display='block';i.src='img/less.gif'}else{ v.style.display='none';i.src='img/more.gif'}\">
		 <th style=\"background:#000;\"><img src=\"img/less.gif\" id=\"img_uid_$uid\"> $uname</th>
		</tr>
	</tbody>
	</table>
	<div id=\"bloque_uid_$uid\" style=\"display:block;\">";
*/

	$counter_total = 0;
 
  $where_gid = "AND g.gid='$gid'"; 
	$sql = "SELECT c.campana_id, nombre, fecha_ini, fecha_fin FROM crm_campanas AS c, crm_campanas_groups  AS g WHERE c.campana_id=g.campana_id $where_gid ORDER BY campana_id LIMIT $from, $how_many";
	$result = $db->sql_query($sql) or die("Error al consultar campañas ".print_r($db->sql_error()));
	while (list($campana_id, $name) = htmlize($db->sql_fetchrow($result)))
	{//hacer lo mismo que campana->actividades pero para todas las campanas

	  //checar si hay en este ciclo
	  $sql = "SELECT c.id, d.origen_id, c.contacto_id, d.nombre, d.apellido_paterno, d.apellido_materno, DATE_FORMAT(c.fecha_cita,'%d-%m-%Y %H:%i')
	, UNIX_TIMESTAMP(c.fecha_cita),
	          d.tel_casa, d.tel_oficina, d.tel_movil, d.tel_otro, c.`timestamp`, c.status_id, d.uid
			  FROM crm_campanas_llamadas AS c, crm_contactos AS d 
	          WHERE c.campana_id='$campana_id' AND d.contacto_id=c.contacto_id AND d.gid='$gid' AND d.uid!='' ";
	  $r2 = $db->sql_query($sql) or die($sql.(print_r($db->sql_error())));
	  
	  if ($db->sql_numrows($r2)) //ver si hay, si no dejar "colapsado"
	  {
		//esto es un headercito para el título de la campana y colapsar
		
      if (is_array($array_tabs_abiertos))
      {
        if (in_array($campana_id, $array_tabs_abiertos))
        {
          $display_bloque = "block";
          $icono_bloque = "less";
        }
        else
        {
          $display_bloque = "none";
          $icono_bloque = "more";
        }
      }
      else
      {
        $display_bloque = "none";
        $icono_bloque = "more";
      }
		$tabla_campanas .=
	"<table style=\"text-align: left; width: 100%;\" border=\"0\" cellpadding=\"2\" cellspacing=\"2\"> <tbody> 
		<tr style=\"cursor:pointer\" onclick=\"var v=document.getElementById('bloque_$uid_$campana_id');	var i=document.getElementById('img_$uid_$campana_id'); var o=document.getElementById('open');	if(v.style.display=='none'){v.style.display='block';i.src='img/less.gif';o.value = o.value+'$campana_id'+'-';}else{ v.style.display='none';i.src='img/more.gif';o.value = o.value.replace('$campana_id ','')}\">
		 <th><img src=\"img/pixel.gif\" width=\"15px\"><img src=\"img/$icono_bloque.gif\" id=\"img_$uid_$campana_id\"> $name</th>
		</tr>
	</tbody>
	</table>
	<div id=\"bloque_$uid_$campana_id\" style=\"display:$display_bloque;\">";
		
		
		
		$contacto_ids = array();
		$llamada_ids[] = array();
		$campana_ids[] = array();
		$origenes = array();
		$origenes_id = array();
		$nombres = array();
    $nombres2 = array();  
		$tels = array();
		$esperas = array();
		$primer_contactos = array();
		$ultimo_contactos = array();
		$primer_contactos_ts = array();
		$ultimo_contactos_ts = array();
		$fecha_citas = array();
		$fecha_citas_ts = array();
		$retrasos = array();
		$llamada_ts = array();
		$llamada_ids = array();
		$ordered_contacto_ids = array();
		$counter = 0;
	    while (list($llamada_id, $origen_id, $contacto_id, $nombre, $apellido_paterno, $apellido_materno, $fecha_cita, $fecha_cita_timestamp, $tel_casa, $tel_oficina, $tel_movil, $tel_otro, $llamda_timestamp, $status_id, $c_uid) = $db->sql_fetchrow($r2))
	    {
			if ($tel_otro) $tel=$tel_otro;
			if ($tel_movil) $tel=$tel_movil;
			if ($tel_oficina) $tel=$tel_oficina;
			if ($tel_casa) $tel=$tel_casa;
      
      //nombre al usuario
      $sql = "SELECT name FROM users WHERE uid='$c_uid'";
      $result_ = $db->sql_query($sql) or die("Error al consultar usuarios ".print_r($db->sql_error()));
      list($uname) = htmlize($db->sql_fetchrow($result_));
			//ponerle nombre al origen
			$r3 = $db->sql_query("SELECT nombre FROM crm_contactos_origenes WHERE origen_id='$origen_id' LIMIT 1");
			list($origen) = $db->sql_fetchrow($r3);
	      //buscar la fecha de los contactos en el log (cuando cambio de ciclo de venta)
	      $sql = "SELECT DATE_FORMAT(timestamp,'%d-%m-%Y'), UNIX_TIMESTAMP(timestamp) FROM crm_campanas_llamadas_log WHERE contacto_id='$contacto_id' ORDER BY timestamp ASC LIMIT 1";
	      $r3 = $db->sql_query($sql) or die($sql);
	      list($primer_contacto,$primer_contacto_timestamp) = $db->sql_fetchrow($r3);
	      $sql = "SELECT DATE_FORMAT(timestamp,'%d-%m-%Y'), UNIX_TIMESTAMP(timestamp) FROM crm_campanas_llamadas_log WHERE contacto_id='$contacto_id' ORDER BY timestamp DESC LIMIT 1";
	      $r3 = $db->sql_query($sql) or die($sql);
	      list($ultimo_contacto, $ultimo_contacto_timestamp) = $db->sql_fetchrow($r3);
		  
		  //formatear el tiempo que lleva de retraso la cita
		  if ($fecha_cita_timestamp && $status_id == -2) 
		  {
			$retraso = time() - $fecha_cita_timestamp;
			if ($retraso > 0)
			{
				$horas = floor($retraso / 60 / 60);
				$mins = round($retraso/60 - $horas*60);
				$retraso = "$horas hr $mins m";
			}
			else $retraso = "";
		  }
		  else $retraso = "";
		  
		  //darle formato en horas al timestamp
	      if ($ultimo_contacto_timestamp)
	      {
	       $ultimo_contacto_timestamp = time() - $ultimo_contacto_timestamp;
		   $ultimo_contacto_timestamp_bk = $ultimo_contacto_timestamp;
	        if ($ultimo_contacto_timestamp > 0)
	        {
	          $ultimo_contacto_timestamp = $ultimo_contacto_timestamp / 60 / 60; //entre 60 segs, entre 60 mins
	          $ultimo_contacto_timestamp = sprintf("%u",$ultimo_contacto_timestamp);//entero
	  
	          $ultimo_contacto_timestamp .= " hr";//($ultimo_contacto_timestamp!=1?"s":"")
	        }
	      }
		  else
		  {
			$ultimo_contacto_timestamp = "";
			$ultimo_contacto_timestamp_bk = "";
	      }		
		  $contacto_ids[] = $contacto_id;
		  $llamada_ids[$contacto_id] = $llamada_id;
		  $campana_ids[$contacto_id] = $campana_id;
		  $origenes[$contacto_id] = $origen;
		  $origenes_id[$contacto_id] = $origen_id;
		  $nombres[$contacto_id] = "$nombre $apellido_paterno $apellido_materno";
      $nombres2[$contacto_id] = "$uname";  
		  $tels[$contacto_id] = $tel;
      $tels[$contacto_id] = $tel;  
		  $esperas[$contacto_id] = $ultimo_contacto_timestamp;
		  $primer_contactos[$contacto_id] = $primer_contacto;
		  $ultimo_contactos[$contacto_id] = $ultimo_contacto;
		  $primer_contactos_ts[$contacto_id] = $primer_contacto_timestamp?$primer_contacto_timestamp:'9999999999';//para el sort
		  $ultimo_contactos_ts[$contacto_id] = $ultimo_contacto_timestamp_bk;
		  $fecha_citas[$contacto_id] = $fecha_cita;
		  $fecha_citas_ts[$contacto_id] = $fecha_cita_timestamp?$fecha_cita_timestamp:'9999999999';//para mandarla hasta abajo
		  $retrasos[$contacto_id] = $retraso;
		  $llamada_ts[$contacto_id] = $llamada_timestamp;
		  $llamada_ids[$contacto_id] = $llamada_id;
		  
	      $counter++;//queremos el total de contactos

	    }
		//ordenar la tabla por los datos que solicitan
		switch($orderby)
		{
			case "origen_id": $array_para_ordenar = &$origenes_id; 
							  break;
			case "nombre": $array_para_ordenar = &$nombres;
			                  break; //por referencia para evitar que copie
      case "nombre2": $array_para_ordenar = &$nombres2;
                        break; //por referencia para evitar que copie   
			case "tel": $array_para_ordenar = &$tels;
							  break;
			case "ultimo_contacto": $array_para_ordenar = &$ultimo_contactos_ts;
							  break;
			case "primer_contacto": $array_para_ordenar = &$primer_contactos_ts;
			                  break;
			case "fecha_cita": $array_para_ordenar = &$fecha_citas_ts;
							  break;
			default: $array_para_ordenar = &$fecha_citas_ts;
		}
    
		if (!$rsort)
			asort($array_para_ordenar); //ordenar por valor y conservar asociación de keys
		else
			arsort($array_para_ordenar); //ordenar por valor  en orden inverso y conservar asociación de keys
		foreach ($array_para_ordenar AS $key=>$value)
		{
			$ordered_contacto_ids[] = $key;//echo $key."->$value<br>";
		}
    if ($rsort)
      $nrsort = 0;
    else
      $nrsort = 1;  
		//hasta el final crear la tabla
		$tabla_campanas .= "<table class=\"width100\">"
	                  ."<thead>"
					  ."<tr>"
	                  ."<td style=\"width:180px;\"><a href=\"#\" onclick=\"location.href='index.php?_module=$_module&_op=$_op&uid=$uid&campana_id=$campana_id&gid=$gid&orderby=origen_id&rsort=$nrsort&open='+document.getElementById('open').value;\" style=\"color:white;\">Campaña</a></td>"
					  ."<td style=\"width:360px;\"><a href=\"#\" onclick=\"location.href='index.php?_module=$_module&_op=$_op&uid=$uid&campana_id=$campana_id&gid=$gid&orderby=nombre&rsort=$nrsort&open='+document.getElementById('open').value;\" style=\"color:white;\">Nombre</a></td>"
// 					  ."<td><a href=\"index.php?_module=$_module&_op=$_op&uid=$uid&campana_id=$campana_id&gid=$gid&orderby=tel\" style=\"color:white;\">Teléfono</a></td>"
           ."<td style=\"width:360px;\"><a href=\"#\" onclick=\"location.href='index.php?_module=$_module&_op=$_op&uid=$uid&campana_id=$campana_id&gid=$gid&orderby=nombre2&rsort=$nrsort&open='+document.getElementById('open').value;\" style=\"color:white;\">Vendedor</a></td>"
					  ."<td style=\"width:50px;\"><a href=\"#\" onclick=\"location.href='index.php?_module=$_module&_op=$_op&uid=$uid&campana_id=$campana_id&gid=$gid&orderby=ultimo_contacto&rsort=$nrsort&open='+document.getElementById('open').value;\" style=\"color:white;\">Espera</a></td>"
					  ."<td style=\"width:180px;\"><a href=\"#\" onclick=\"location.href='index.php?_module=$_module&_op=$_op&uid=$uid&campana_id=$campana_id&gid=$gid&orderby=primer_contacto&rsort=$nrsort&open='+document.getElementById('open').value;\" style=\"color:white;\">Primer contacto</a></td>"
					  ."<td style=\"width:180px;\"><a href=\"#\" onclick=\"location.href='index.php?_module=$_module&_op=$_op&uid=$uid&campana_id=$campana_id&gid=$gid&orderby=ultimo_contacto&rsort=$nrsort&open='+document.getElementById('open').value;\" style=\"color:white;\">Último contacto</a></td>"
					  ."<td style=\"width:180px;\"><a href=\"#\" onclick=\"location.href='index.php?_module=$_module&_op=$_op&uid=$uid&campana_id=$campana_id&gid=$gid&orderby=fecha_cita&rsort=$nrsort&open='+document.getElementById('open').value;\" style=\"color:white;\">Compromiso</a></td>"
					  ."<td style=\"width:180px;\"><a href=\"#\" onclick=\"location.href='index.php?_module=$_module&_op=$_op&uid=$uid&campana_id=$campana_id&gid=$gid&orderby=fecha_cita&rsort=$nrsort&open='+document.getElementById('open').value;\" style=\"color:white;\">Retraso</a></td>"
// 					  ."<td style=\"width:32px;\">Sel.</td></tr>"
	                  ."</thead>"
	                  ."<tbody>";
		foreach ($ordered_contacto_ids AS $contacto_id)
		{
		  $origen = $origenes[$contacto_id];
		  $nombre = $nombres[$contacto_id];
      $nombre2 = $nombres2[$contacto_id];  
		  $tel = $tels[$contacto_id];
		  $espera = $esperas[$contacto_id];
		  $primer_contacto = $primer_contactos[$contacto_id] ;
		  $ultimo_contacto = $ultimo_contactos[$contacto_id] ;
		  $fecha_cita = $fecha_citas[$contacto_id];
		  $retraso = $retrasos[$contacto_id];
		  $llamada_id = $llamada_ids[$contacto_id];
	      $tabla_campanas .= "<tr class=\"row".(($c++%2)+1)."\">"
							 ."<td>$origen</td>"
	                         ."<td><az target=\"llamada\" href=\"index.php?_module=Campanas&_op=llamada_ro&llamada_id={$llamada_ids[$contacto_id]}&contacto_id=$contacto_id&campana_id={$campana_ids[$contacto_id]}\">
						 $nombre $apellido_paterno $apellido_materno</az></td>"
// 	                         ."<td>$tel</td>"
                          ."<td>$nombre2</td>"
							 ."<td>$espera</td>"
							 ."<td>$primer_contacto</td>"
	                         ."<td>$ultimo_contacto</td>"
	                         ."<td>$fecha_cita</td>"
							 ."<td>$retraso</td>"
// 	                         ."<td><input type=\"checkbox\" name=\"chbx_$contacto_id\" style=\"height:12;width:16;\"></td>"
	                         ."</tr>";
		}
		$tabla_campanas .= "<tr class=\"row".(($c++%2)+1)."\"><td align=\"right\"><b>Total</b></td><td><b> $counter</b></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>";
		$tabla_campanas .= "</tbody></table>";
		$tabla_campanas .= "</div>";
		$counter_total += $counter;
	  }//fin de si hay algo

	}//fin de esta campana
	if ($counter_total)
	{
		$tabla_campanas .=
	"<table style=\"text-align: left; width: 100%;\" border=\"0\" cellpadding=\"2\" cellspacing=\"2\"> <tbody> 
		<tr>
		 <th>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Total $counter_total</th>
		</tr>
	</tbody>
	</table>";
		
	}
	else
	{
		$tabla_campanas .= "<center>No hay prospectos asignados</center>";
	}

/*
	$tabla_campanas .= "</div>"; //cerrar el div que se colapsa de uid
}//fin de ciclo con uid
*/
/*
$tabla_campanas .= "<table class=\"width100\"><tr class=\"row".(++$row_class%2+1)."\" style=\"text-align:center;\">"
				."<td colspan=7>"
				."<input name=\"all\" type=\"button\" onclick=\"allon();\" value=\"Todos\">&nbsp;"
				."<input name=\"none\" type=\"button\" onclick=\"alloff();\" value=\"Ninguno\"></td></tr>"
				."<tr class=\"row".(++$row_class%2+1)."\" style=\"text-align:center;\">"
				."<td colspan=7>"
				."<input type=\"submit\" name=\"seleccionar\" value=\"Reasignar\"></td></tr>"
				."<tr class=\"row".(++$row_class%2+1)."\" style=\"text-align:center;\"></table>";
// */
?>
