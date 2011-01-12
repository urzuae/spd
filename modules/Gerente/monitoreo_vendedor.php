<?
  if (!defined('_IN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}
global $db, $how_many, $from, $campana_id, $nombre, $apellido_paterno, $apellido_materno, 
        $submit, $status_id, $ciclo_de_venta_id, $uid, $orderby;
include_once("class_autorizado.php");

$sql  = "SELECT gid, super FROM users WHERE uid='".$_COOKIE['_uid']."'";
$result = $db->sql_query($sql) or die("Error");
list($gid, $super) = $db->sql_fetchrow($result);
if ($super > 6)
{
  die("<h1>Usted no es un Gerente</h1>");
}

$uid = $_GET['uid'];
$sql  = "SELECT name FROM users WHERE uid='$uid'";
$result = $db->sql_query($sql) or die("Error");
list($uname) = $db->sql_fetchrow($result);

$orderby = ""; //para que no ordene
$window_opc = "'llamada','menubar=no,location=no,resizable=yes,scrollbars=yes,status=no,navigation=no,titlebar=no,directories=no,width=800,height=750,left=200,top=0,alwaysraised=yes'";
$how_many = 25;
if ($from < 1 || !$from) $from = 0;
$where_gid = "AND g.gid='$gid'";
$sql = "SELECT c.campana_id, nombre, fecha_ini, fecha_fin FROM crm_campanas AS c, crm_campanas_groups  AS g WHERE c.campana_id=g.campana_id $where_gid ORDER BY campana_id LIMIT $from, $how_many";

$result = $db->sql_query($sql) or die("Error al consultar campa�as ".print_r($db->sql_error()));
$contador_tablas=0;
while (list($campana_id, $name) = htmlize($db->sql_fetchrow($result)))
{
   ////hacer lo mismo que campana->actividades pero para todas las campanas
  //checar si hay en este ciclo
  $contador_tablas++;
  $sql = "SELECT c.id, c.campana_id, d.origen_id, c.contacto_id, d.nombre, d.apellido_paterno, d.apellido_materno,
          DATE_FORMAT(c.fecha_cita,'%Y-%m-%d %H:%i'), UNIX_TIMESTAMP(c.fecha_cita),d.tel_casa, d.tel_oficina, d.tel_movil, d.tel_otro, c.`timestamp`, c.status_id,d.fecha_importado,d.fecha_autorizado,d.fecha_firmado
		  FROM crm_campanas_llamadas AS c, crm_contactos AS d 
          WHERE c.campana_id='$campana_id' AND d.contacto_id=c.contacto_id AND d.uid='$uid' ";
  $r2 = $db->sql_query($sql) or die($sql.(print_r($db->sql_error())));
  
  if ($db->sql_numrows($r2)) //ver si hay, si no dejar "colapsado"
  {
	//esto es un headercito para el t�tulo de la campana y colapsar
	
	$tabla_campanas .=
"<table style=\"text-align: left; width: 100%;\" border=\"0\" cellpadding=\"2\" cellspacing=\"2\"> <tbody> 
	<tr style=\"cursor:pointer\" onclick=\"var v=document.getElementById('bloque_$campana_id');	var i=document.getElementById('img_$campana_id');	if(v.style.display=='none'){v.style.display='block';i.src='img/less.gif'}else{ v.style.display='none';i.src='img/more.gif'}\">
	 <th><img src=\"img/less.gif\" id=\"img_$campana_id\"> $name</th>
	</tr>
</tbody>
</table>
<div id=\"bloque_$campana_id\" style=\"display:block;\">";
	
	$contacto_ids = array();
	$origenes = array();
	$origenes_id = array();
	$nombres = array();
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
    $autorizados=array();
	$counter = 0;
    while (list($llamada_id, $campana_id, $origen_id, $contacto_id, $nombre, $apellido_paterno, $apellido_materno, $fecha_cita, $fecha_cita_timestamp, $tel_casa, $tel_oficina, $tel_movil, $tel_otro, $llamda_timestamp, $status_id,$fecha_importado,$fecha_autorizado,$fecha_firmado) = $db->sql_fetchrow($r2))
    {
		if ($tel_otro) $tel=$tel_otro;
		if ($tel_movil) $tel=$tel_movil;
		if ($tel_oficina) $tel=$tel_oficina;
		if ($tel_casa) $tel=$tel_casa;
		//ponerle nombre al origen
		$r3 = $db->sql_query("SELECT nombre FROM crm_fuentes WHERE fuente_id='$origen_id' LIMIT 1");
		list($origen) = $db->sql_fetchrow($r3);
      //buscar la fecha de los contactos en el log (cuando cambio de ciclo de venta)
      $sql = "SELECT DATE_FORMAT(timestamp,'%Y-%m-%d'), UNIX_TIMESTAMP(timestamp) FROM crm_campanas_llamadas_log WHERE contacto_id='$contacto_id' ORDER BY timestamp ASC LIMIT 1";
      $r3 = $db->sql_query($sql) or die($sql);
      list($primer_contacto,$primer_contacto_timestamp) = $db->sql_fetchrow($r3);

      $sql = "SELECT DATE_FORMAT(timestamp,'%Y-%m-%d'), UNIX_TIMESTAMP(timestamp) FROM crm_campanas_llamadas_log WHERE contacto_id='$contacto_id' ORDER BY timestamp DESC LIMIT 1";
      $r3 = $db->sql_query($sql) or die($sql);
      list($ultimo_contacto, $ultimo_contacto_timestamp) = $db->sql_fetchrow($r3);
	  
	  //formatear el tiempo que lleva de retraso la cita
	  if ($fecha_cita_timestamp && $status_id == -2) 
	  {
		$retraso = time() - $fecha_cita_timestamp;
        // aqui saco la diferencia en horas
        $sql="  SELECT TIMESTAMPDIFF(hour,'".$fecha_cita."',NOW()) as retraso;";
        $res= $db->sql_query($sql);
        list($retraso)=$db->sql_fetchrow($res);

		if ($retraso <= 0)
		{
            $retraso = "";
			/*$horas = floor($retraso / 60 / 60);
			$mins = round($retraso/60 - $horas*60);
			//$retraso = "$horas hr $mins m";
            $retraso = $horas;*/
		}
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
      $objeto= new Fecha_autorizado ($db,$fecha_autorizado,$fecha_firmado);
      $color_semaforo=$objeto->Obten_Semaforo();
      
	  $contacto_ids[] = $contacto_id;
	  $llamada_ids[$contacto_id] = $llamada_id;
      $autorizados[$contacto_id]=$color_semaforo;
	  $campana_ids[$contacto_id] = $campana_id;
	  $origenes[$contacto_id] = $origen;
	  $origenes_id[$contacto_id] = $origen_id;
	  $nombres[$contacto_id] = "$nombre $apellido_paterno $apellido_materno";
	  $tels[$contacto_id] = $tel;
	  #$esperas[$contacto_id] = $ultimo_contacto_timestamp;
      $esperas[$contacto_id] = $fecha_importado;
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
		                  $rsort = 0;
						  break;
		case "nombre": $array_para_ordenar = &$nombres;
		                  $rsort = 0;
		                  break; //por referencia para evitar que copie
		case "tel": $array_para_ordenar = &$tels;
		                  $rsort = 0;
						  break;
		case "ultimo_contacto": $array_para_ordenar = &$ultimo_contactos_ts;
		                  $rsort = 1;
						  break;
		case "primer_contacto": $array_para_ordenar = &$primer_contactos_ts;
		                  $rsort = 0;
		                  break;
		case "fecha_cita": $array_para_ordenar = &$fecha_citas_ts;
		                  $rsort = 0;
						  break;
		default: $array_para_ordenar = &$llamada_ts;
		                  $rsort = 0;
	}
	if (!$rsort)
		asort($array_para_ordenar); //ordenar por valor y conservar asociaci�n de keys
	else
		arsort($array_para_ordenar); //ordenar por valor  en orden inverso y conservar asociaci�n de keys
	foreach ($array_para_ordenar AS $key=>$value)
	{
		$ordered_contacto_ids[] = $key;//echo $key."->$value<br>";
	}
	//hasta el final crear la tabla
    $tabla_campanas .= "<table id=\"tabla_contactosven$contador_tablas\" class=\"tablesorter\" >"
                    ."<thead>"
                    ."<tr>"
                    ."<th style=\"width:180px;\">Campa�a</th>"
                    ."<th style=\"width:330px;\">Nombre</th>"
                    ."<th style=\"width:140px;\">Fecha Registro</th>"
                    ."<th style=\"width:170px;\">Primer contacto</th>"
                    ."<th style=\"width:170px;\">�ltimo contacto</th>"
                    ."<th style=\"width:170px;\">Compromiso</th>"
                    ."<th style=\"width:150px;\">Retraso (hrs)</th>"
                    ."<th style=\"width:32px;\">Sel.</th></tr>"
                    ."</thead>"
                    ."<tbody>";
    $tmp_contador_retraso=0;
	foreach ($ordered_contacto_ids AS $contacto_id)
	{
	  $origen = $origenes[$contacto_id];
	  $nombre = $nombres[$contacto_id];
	  $tel = $tels[$contacto_id];
	  $espera = $esperas[$contacto_id];
	  $primer_contacto = $primer_contactos[$contacto_id] ;
	  $ultimo_contacto = $ultimo_contactos[$contacto_id] ;
	  $fecha_cita = $fecha_citas[$contacto_id];
	  $retraso = $retrasos[$contacto_id];
	  $llamada_id = $llamada_ids[$contacto_id];
      $semaforo=$autorizados[$contacto_id];
      $tmp_contador_retraso=$tmp_contador_retraso + $retraso;
      $tabla_campanas .= "<tr class=\"row".(($c++%2)+1)."\">"
						 ."<td>$origen</td>"
                         ."<td><a href=\"index.php?_module=Campanas&_op=llamada_ro&llamada_id={$llamada_ids[$contacto_id]}&contacto_id=$contacto_id&campana_id={$campana_ids[$contacto_id]}\" target=\"llamada\">
						 $nombre $apellido_paterno $apellido_materno&nbsp;&nbsp;<span style='background-color:$semaforo'>&nbsp;&nbsp;&nbsp;</span></a></td>"
//                          ."<td>$tel</td>"
						 ."<td>$espera</td>"
						 ."<td>$primer_contacto</td>"
                         ."<td>$ultimo_contacto</td>"
                         ."<td>$fecha_cita</td>"
						 ."<td>$retraso</td>"
                         ."<td><input type=\"checkbox\" name=\"chbx_$contacto_id\" style=\"height:12;width:16;\"></td>"
                         ."</tr>";
	}
	$tabla_campanas .= "</tbody><thead><tr class=\"row".(($c++%2)+1)."\"><td align=\"right\"><b>Total</b></td><td><b> $counter</b></td><td></td><td></td><td></td><td></td><td></td><td></td></tr></thead>";
	$tabla_campanas .= "</table>";
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


    $select_users .= "</select>";
	$tabla_campanas .= "<table class=\"width100\"><tr class=\"row".(++$row_class%2+1)."\" style=\"text-align:center;\">"
					."<td colspan=7>"
					."<input name=\"all\" type=\"button\" onclick=\"allon();\" value=\"Todos\">&nbsp;"
					."<input name=\"none\" type=\"button\" onclick=\"alloff();\" value=\"Ninguno\"></td></tr>"
					."<tr class=\"row".(++$row_class%2+1)."\" style=\"text-align:center;\">"
					."<td colspan=7>"
					."<input type=\"submit\" name=\"seleccionar\" value=\"Reasignar\"></td></tr>"
					."<tr class=\"row".(++$row_class%2+1)."\" style=\"text-align:center;\"></table>";
	
}
else
	$tabla_campanas = "<center>No hay prospectos asignados</center>";
?>
