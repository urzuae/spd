<?
  if (!defined('_IN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}
global $db, $how_many, $from, $campana_id, $nombre, $apellido_paterno, $apellido_materno, 
        $submit, $status_id, $ciclo_de_venta_id, $uid, $orderby, $rsort, $open,$_dbhost,$_dbuname,$_dbpass,$_dbname;


$sql  = "SELECT gid, super FROM users WHERE uid='".$_COOKIE['_uid']."'";
$result = $db->sql_query($sql) or die("Error");
list($gid, $super) = $db->sql_fetchrow($result);
if ($super > 6)
{
  die("<h1>Usted no es un Gerente</h1>");
}



	$sql = "SELECT c.campana_id, nombre FROM crm_campanas AS c, crm_campanas_groups  AS g WHERE c.campana_id=g.campana_id AND g.gid='$gid' ORDER BY campana_id LIMIT 0,25";
	$result = $db->sql_query($sql) or die("Error al consultar campañas ".print_r($db->sql_error()));
	while (list($campana_id, $name) = htmlize($db->sql_fetchrow($result)))
	{
		$campanasNombre[]=array(
		'campana' 	=> $name,
		'campanaId'	=> $campana_id
		);
	}


$hoy=date('Y-m-d H:i:s');
$linkTodb = mysqli_connect($_dbhost,$_dbuname,$_dbpass);
if (mysqli_connect_errno())
{
echo "Error de Conexion";
exit();
}
$conn = mysqli_select_db ($linkTodb,$_dbname);
if (! $conn)
{
echo "Error de Base de Datos";

}
$result = mysqli_query($linkTodb,'CALL reporte_prospectos('.$gid.');');

if (! $result)
{
echo "error de procedure";
exit;
}
$i=1;
while ($row = mysqli_fetch_array($result,MYSQLI_NUM))
{
$counter=$i++;
if($row[9] < $hoy )
$retraso='';
else
$retraso=$row[10];

	$campanas[]=array( 
	'campaña' =>$row[2]
	);


	$registros[]=array(
	'campana'	=> $row[2],
	'idllamada'	=> $row[12],
	'idcampana'	=> $row[0],
	'idcontacto'	=> $row[11],
	'origen'	=> $row[3],
	'nombre'	=> $row[4],
	'vendedor'	=> $row[5],
	'espera'	=> $row[6],
	'prim_contacto'	=> $row[7],
	'ulti_contacto'	=> $row[8],
	'compromiso'	=> $row[9],
	'retraso'	=> $retraso,
	'total_campana'	=> $row[13]	
	);

}

foreach($campanasNombre as $valor)
{
        $display_bloque = "none";
        $icono_bloque = "more";
	$tabla_campanas .=
	"<table style=\"text-align: left; width: 100%;\" border=\"0\" cellpadding=\"2\" cellspacing=\"2\"> <tbody> 
	<tr style=\"cursor:pointer\" onclick=\"var v=document.getElementById('bloque_$uid_$valor[campanaId]');	var i=document.getElementById('img_$uid_$valor[campanaId]'); var o=document.getElementById('open');	if(v.style.display=='none'){v.style.display='block';i.src='img/less.gif';o.value = o.value+'$valor[campanaId]'+'-';}else{ v.style.display='none';i.src='img/more.gif';o.value = o.value.replace('$valor[campanaId] ','')}\">
	<th><img src=\"img/pixel.gif\" width=\"15px\"><img src=\"img/$icono_bloque.gif\" id=\"img_$uid_$valor[campanaId]\"> $valor[campana]</th>
	</tr>
	</table>
	<div id=\"bloque_$uid_$valor[campanaId]\" style=\"display:$display_bloque;\">
	<table class=\"width100\">
	                  <thead>
			  <tr>
	                  <td style=\"width:180px;\"><a href=\"#\" onclick=\"location.href='index.php?_module=$_module&_op=$_op&uid=$uid&campana_id=$campana_id&orderby=origen_id&rsort=$nrsort&open='+document.getElementById('open').value;\" style=\"color:white;\">Campaña</a></td>
			  <td style=\"width:360px;\"><a href=\"#\" onclick=\"location.href='index.php?_module=$_module&_op=$_op&uid=$uid&campana_id=$campana_id&orderby=nombre&rsort=$nrsort&open='+document.getElementById('open').value;\" style=\"color:white;\">Nombre</a></td>
			  <td style=\"width:360px;\"><a href=\"#\" onclick=\"location.href='index.php?_module=$_module&_op=$_op&uid=$uid&campana_id=$campana_id&orderby=nombre2&rsort=$nrsort&open='+document.getElementById('open').value;\" style=\"color:white;\">Vendedor</a></td>
			  <td style=\"width:50px;\"><a href=\"#\" onclick=\"location.href='index.php?_module=$_module&_op=$_op&uid=$uid&campana_id=$campana_id&orderby=ultimo_contacto&rsort=$nrsort&open='+document.getElementById('open').value;\" style=\"color:white;\">Espera</a></td>
			  <td style=\"width:180px;\"><a href=\"#\" onclick=\"location.href='index.php?_module=$_module&_op=$_op&uid=$uid&campana_id=$campana_id&orderby=primer_contacto&rsort=$nrsort&open='+document.getElementById('open').value;\" style=\"color:white;\">Primer contacto</a></td>
			  <td style=\"width:180px;\"><a href=\"#\" onclick=\"location.href='index.php?_module=$_module&_op=$_op&uid=$uid&campana_id=$campana_id&orderby=ultimo_contacto&rsort=$nrsort&open='+document.getElementById('open').value;\" style=\"color:white;\">Último contacto</a></td>
			  <td style=\"width:180px;\"><a href=\"#\" onclick=\"location.href='index.php?_module=$_module&_op=$_op&uid=$uid&campana_id=$campana_id&orderby=fecha_cita&rsort=$nrsort&open='+document.getElementById('open').value;\" style=\"color:white;\">Compromiso</a></td>
			  <td style=\"width:180px;\"><a href=\"#\" onclick=\"location.href='index.php?_module=$_module&_op=$_op&uid=$uid&campana_id=$campana_id&orderby=fecha_cita&rsort=$nrsort&open='+document.getElementById('open').value;\" style=\"color:white;\">Retraso</a></td>
			  <td style=\"width:32px;\">Sel.</td></tr>
	                  </thead>
	                  <tbody>";

		foreach($registros as $valores)
		{
			$campanaOriginal=$valor['campanaId'];
			$campanaDeRegistro=$valores['idcampana'];
			$i=1;
			if($campanaOriginal == $campanaDeRegistro){
			$conunt=$i+$i;
			$tabla_campanas .= "<tr class=\"row".(($c++%2)+1)."\">"
						 ."<td>$valores[origen]</td>"
						 ."<td><a target=\"llamada\" href=\"index.php?_module=Campanas&_op=llamada_ro&llamada_id=$row[12]&contacto_id=$row[11]&campana_id={$row[0]}\">
						 $valores[nombre]</a></td>"
					  	 ."<td>$valores[vendedor]</td>"
						 ."<td>$valores[espera]</td>"
						 ."<td>$valores[prim_contacto]</td>"
						 ."<td>$valores[ulti_contacto]</td>"
						 ."<td>$valores[compromiso]</td>"
						 ."<td>$valores[retraso]</td>"
						 ."<td><input type=\"checkbox\" name=\"chbx_$valores[idcontacto]\" style=\"height:12;width:16;\"></td>"
						 ."</tr>";
			$total_campana=$valores['total_campana'];
			}
		}

		$tabla_campanas .= "<tr class=\"row".(($c++%2)+1)."\"><td align=\"right\"><b>Total</b></td><td><b> $total_campana</b></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>";
		$tabla_campanas .= "</tbody></table>";
		$tabla_campanas .= "</div>";

}


		$tabla_campanas .= "<table class=\"width100\">";
	        $tabla_campanas .= "<thead>";
		$tabla_campanas .= "<tr>";
		$tabla_campanas .= "<tr class=\"row".(($c++%2)+1)."\"><th></th><th align=\"left\" colspan=\"6\"><b>Total</b><b> $counter</b></th></tr>";
		$tabla_campanas .= "</thead></table>";


		$tabla_campanas .= "<table class=\"width100\"><tr class=\"row".(++$row_class%2+1)."\" style=\"text-align:center;\">"
		."<td colspan=7>"
		."<input name=\"all\" type=\"button\" onclick=\"allon();\" value=\"Todos\">&nbsp;"
		."<input name=\"none\" type=\"button\" onclick=\"alloff();\" value=\"Ninguno\"></td></tr>"
		."<tr class=\"row".(++$row_class%2+1)."\" style=\"text-align:center;\">"
		."<td colspan=7>"
		."<input type=\"submit\" name=\"seleccionar\" value=\"Reasignar\"></td></tr>"
		."<tr class=\"row".(++$row_class%2+1)."\" style=\"text-align:center;\"></table>";

?>
