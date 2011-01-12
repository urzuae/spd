<?
  if (!defined('_IN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}
global $db, $how_many, $from, $campana_id, $nombre, $apellido_paterno, $apellido_materno, 
        $submit, $status_id, $ciclo_de_venta_id, $uid, $orderby,$_site_title;
$window_opc = "'llamada','menubar=no,location=no,resizable=yes,scrollbars=yes,status=no,navigation=no,titlebar=no,directories=no,width=800,height=750,left=200,top=0,alwaysraised=yes'";
$_site_title = "Actividades";
$how_many = 15;
include_once("modules/Gerente/class_autorizado.php");

$result = $db->sql_query("SELECT gid FROM users WHERE uid='$uid' LIMIT 1") or die("Error en grupo ".print_r($db->sql_error()));
list($gid) = $db->sql_fetchrow($result);
$sql = "SELECT horas FROM groups_horas WHERE gid = '$gid'";
$result = $db->sql_query($sql) or die("Error en grupo ".print_r($db->sql_error()));
list($horas_diferencia) = $db->sql_fetchrow($result);

$prioridades = array();
$sql = "SELECT prioridad_id, prioridad, color FROM crm_prioridades_contactos";
$r = $db->sql_query($sql) or die($sql);
while(list($prioridad_id, $prioridad, $prioridad_color) = $db->sql_fetchrow($r)){
	$prioridades[$prioridad_id] = $prioridad;
	$prioridades_color[$prioridad_id] = $prioridad_color;
}

$prioridad_arr = array();
$array_para_ordenar = array();
$ordered_contacto_ids = array();
if ($from < 1 || !$from) $from = 0;
$sql = "SELECT nombre FROM crm_campanas WHERE campana_id='$campana_id' ";
$r2 = $db->sql_query($sql) or die($sql.(print_r($db->sql_error())));
list($campana) = $db->sql_fetchrow($r2);

$r3 = $db->sql_query("SELECT fuente_id, nombre FROM crm_fuentes");
while(list($origen_id_1,$origen) = $db->sql_fetchrow($r3)){
	$arr_origenes[$origen_id_1] = $origen;	
}
    
    
  //checar si hay en este ciclo
  $sql = "SELECT c.id, d.origen_id, c.contacto_id, d.nombre, d.apellido_paterno, d.apellido_materno, DATE_FORMAT(c.fecha_cita,'%d-%m-%Y %H:%i')
, UNIX_TIMESTAMP(c.fecha_cita),
          d.tel_casa, d.tel_oficina, d.tel_movil, d.tel_otro, c.`timestamp`, c.status_id, d.prioridad,d.fecha_importado,d.fecha_autorizado,d.fecha_firmado
		  FROM crm_campanas_llamadas AS c, crm_contactos AS d 
          WHERE c.campana_id='$campana_id' AND d.contacto_id=c.contacto_id AND d.uid='$uid'
          ORDER BY d.prioridad DESC ";
  $r2 = $db->sql_query($sql) or die($sql.(print_r($db->sql_error())));
  
  if ($db->sql_numrows($r2)) //ver si hay, si no dejar "colapsado"
  {
    while (list($llamada_id, $origen_id, $contacto_id, $nombre, $apellido_paterno, $apellido_materno, $fecha_cita, $fecha_cita_timestamp, $tel_casa, $tel_oficina, $tel_movil, $tel_otro, $llamda_timestamp, $status_id,$prioridad,$fecha_importado,$fecha_autorizado,$fecha_firmado) = $db->sql_fetchrow($r2))
    {
		if ($tel_otro) $tel=$tel_otro;
		if ($tel_movil) $tel=$tel_movil;
		if ($tel_oficina) $tel=$tel_oficina;
		if ($tel_casa) $tel=$tel_casa;
    $objeto= new Fecha_autorizado ($db,$fecha_autorizado,$fecha_firmado);
    $color_semaforo=$objeto->Obten_Semaforo();

	  $contacto_ids[] = $contacto_id;
	  $origenes[$contacto_id] = $arr_origenes[$origen_id];
      $origenes_id[$contacto_id] = $origen_id;
	  $nombres[$contacto_id] = "$nombre $apellido_paterno $apellido_materno";
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
      $tipos[$contacto_id] = $tipo;
      $prioridad_arr[$contacto_id] = $prioridades[$prioridad];
      $prioridades_arr[$contacto_id] = $prioridad;
      $color_prioridad[$contacto_id] = $prioridades_color[$prioridad]; 
      $fechas_importacion[$contacto_id]=$fecha_importado;
      $autorizacion[$contacto_id]=$color_semaforo;
      $counter++;//queremos el total de contactos

    }
	//ordenar la tabla por los datos que solicitan
	switch($orderby)
	{
		case "origen_id": $array_para_ordenar = &$origenes_id; 
//		                  $rsort = 0;
						  break;
		case "nombre": $array_para_ordenar = &$nombres;
//		                  $rsort = 0;
		                  break; //por referencia para evitar que copie
		case "tel": $array_para_ordenar = &$tels;
//		                  $rsort = 0;
						  break;
		case "ultimo_contacto": $array_para_ordenar = &$ultimo_contactos_ts;
//		                  $rsort = 1;
						  break;
		case "primer_contacto": $array_para_ordenar = &$primer_contactos_ts;
//		                  $rsort = 0;
		                  break;
		case "prioridad": $array_para_ordenar = &$prioridades_arr;
		                  $rsort = 1;
		                  break;		                  
		case "fecha_cita": $array_para_ordenar = &$fecha_citas_ts;
//		                  $rsort = 0;
						  break;
        case "tipo": $array_para_ordenar = &$tipos;
//                      $rsort = 1;
              break;

		default: $array_para_ordenar = &$prioridades_arr;//default ordenar por retraso
		                  $rsort = 1;
	}
	if (!$rsort)
		asort($array_para_ordenar); //ordenar por valor y conservar asociación de keys
	else
		arsort($array_para_ordenar); //ordenar por valor  en orden inverso y conservar asociación de keys
	foreach ($array_para_ordenar AS $key=>$value)
	{
		$ordered_contacto_ids[] = $key;//echo $key."->$value<br>";
	}
	//hasta el final crear la tabla
	
	$tabla_campanas  = "<center><div id=\"loading\"><img src=\"img/loading.gif\"></div></center>";
	$tabla_campanas .= "<table id=\"tabla_contactos\" class=\"tablesorter\" width=\"100%\">
	              <thead>
				  <tr>
                  <th>Campaña</th>
				  <th>Nombre</th>
				  <th>Prioridad</th>
				  <th>Teléfono</th>
				  <th>Registro</th>
				  <th>Primer contacto</th>
				  <th>Último contacto</th>
				  <th>Compromiso</th>
				  <th>Retraso</th>
                  <th>Tipo</th>
				  <th>Acción</th></tr>
                  </thead>
                  <tbody>";
	foreach ($ordered_contacto_ids AS $contacto_id)
	{
	  $c = $contacto_id;
	  $origen = $origenes[$contacto_id];
	  $nombre = $nombres[$contacto_id];
	  $tel = $tels[$contacto_id];
	  $espera = $esperas[$contacto_id];
	  $primer_contacto = $primer_contactos[$contacto_id] ;
	  $ultimo_contacto = $ultimo_contactos[$contacto_id] ;
	  $fecha_cita = $fecha_citas[$contacto_id];
	  $retraso = $retrasos[$contacto_id];
      $tipo = $tipos[$contacto_id]; 
	  $llamada_id = $llamada_ids[$contacto_id];
	  $prioridad = $prioridad_arr[$contacto_id];
	  $color_p = $color_prioridad[$contacto_id]; 
      $fecha_i=$fechas_importacion[$contacto_id];
      //<td id=\"espera_$c\" >$espera</td>
      $tabla_campanas .= "<tr>
						 <td>$origen</td>
                         <td>$nombre $apellido_paterno $apellido_materno&nbsp;&nbsp;<span style='background-color:{$autorizacion[$contacto_id]}'>&nbsp;&nbsp;&nbsp;</span></td>
                         <td style=\"background-image:none; background-color: $color_p\" >$prioridad</td>
                         <td>$tel</td>
						 <td>$fecha_i</td>
                         <td id=\"primer_contacto_$c\" >$primer_contacto</td>
                         <td id=\"ultimo_contacto_$c\" >$ultimo_contacto</td>
                         <td>$fecha_cita</td>
						 <td id=\"retraso_$c\" >$retraso</td>
                         <td id=\"evento_tipo_$c\" >$tipo</td>
                         <td align=\"center\"><a href=\"#\" onclick=\"window.open('index.php?_module=$_module&_op=llamada&campana_id=$campana_id&llamada_id=$llamada_id&contacto_id=$contacto_id&nopendientes=1',$window_opc);\"><img src=\"img/phone.gif\" border=></a></td>
                         </tr>";
	}
  }
  

$tabla_campanas .= "</tbody></table>";




//es rsort por que los pops van al revés
asort($array_para_ordenar);
foreach ($array_para_ordenar AS $key=>$value)
{
	    $ordered_contacto_ids[] = $key;//echo $key."->$value<br>";
}

$jsarray = "var array_contacto_ids = Array(";
foreach($ordered_contacto_ids as $c)
{
		if ($jsarray_index++)
			$jsarray .= ",";
		$jsarray .= "$c";
}
$jsarray .= ");\n";

?>
