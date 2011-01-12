<?
if (!defined('_IN_MAIN_INDEX'))
{
    die ("No puedes acceder directamente a este archivo...");
}

global $db, $how_many, $from, $campana_id, $nombre, $apellido_paterno, $apellido_materno,
       $submit, $status_id, $ciclo_de_venta_id, $uid, $orderby, $rsort, $open,$_dbhost,$_site_name;

include_once($_includesdir."/Genera_Excel.php");
$sql  = "SELECT gid, super FROM users WHERE uid='".$_COOKIE['_uid']."'";
$result = $db->sql_query($sql) or die("Error");
list($gid, $super) = $db->sql_fetchrow($result);
if ($super > 6)
{
    die("<h1>Usted no es un Gerente</h1>");
}
$sql = "SELECT c.campana_id, nombre FROM crm_campanas AS c, crm_campanas_groups  AS g WHERE c.campana_id=g.campana_id AND g.gid='$gid' ORDER BY campana_id LIMIT 0,25";
$result = $db->sql_query($sql) or die("Error al consultar campañas ".print_r($db->sql_error()));
if($db->sql_numrows($result)> 0)
{
    while (list($campana_id, $name) = htmlize($db->sql_fetchrow($result)))
    {
        $campanasNombre[]=array('campana' => $name,'campanaId'	=> $campana_id);
    }
}
$array_vendedores=Regresa_Vendedores($db,$gid);
$array_fuentes=Regresa_Fuentes($db);

$sql="SELECT b.campana_id,b.status_id,a.contacto_id, a.uid, a.origen_id, concat( a.nombre, ' ', a.apellido_paterno, ' ', a.apellido_materno ) AS nombre,
      a.fecha_importado, b.fecha_cita, TIMESTAMPDIFF( HOUR , b.fecha_cita, now( ) ) AS retrasos,
      concat(a.tel_casa,' ',a.tel_oficina,' ',a.tel_movil,' ',a.tel_otro) as tels,a.email,a.domicilio,a.colonia,a.cp,a.poblacion,
      a.codigo_campana,b.timestamp,b.id
      FROM crm_contactos AS a
      INNER JOIN crm_campanas_llamadas AS b ON a.contacto_id = b.contacto_id
      WHERE a.gid=".$gid." 
      ORDER BY b.campana_id, b.contacto_id;";
$res=$db->sql_query($sql) or die("Error en la consulta de fuentes  ".$sql);
if($db->sql_numrows($res)>0)
{
    $counter=$db->sql_numrows($res);
    while($row = $db->sql_fetchrow($res))
    {
        $registros[]=array('idcampana' => $row[0],'idstatus' => $row[1],'idcontacto' => $row[2],'uid'=> $row[3],
                           'idorigen' => $row[4],'nombre' => $row[5],'fecha_importado'=> $row[6],'compromiso' => $row[7],
                           'retraso'	=> $row[8],'tels'=> $row[9],'email'=> $row[10],'domicilio'=> $row[11],
                           'colonia'=> $row[12],'cp'=> $row[13],'poblacion'=> $row[14],'codigo_campana'=> $row[15],
                           'ulti_contacto' => $row[16],'idllamada' => $row[17]);
    }
}

if ( (count($campanasNombre) > 0) )
{
    foreach($campanasNombre as $valor)
    {
        $total_bloque=0;
        $display_bloque = "none";
        $icono_bloque = "more";
        $contador_tablas++;
        $tabla_campanas .=
        "<table style=\"text-align: left; width: 100%;\" border=\"0\" cellpadding=\"2\" cellspacing=\"2\"> <tbody>
        <tr style=\"cursor:pointer\" onclick=\"var v=document.getElementById('bloque_$uid_$valor[campanaId]');	var i=document.getElementById('img_$uid_$valor[campanaId]'); var o=document.getElementById('open');	if(v.style.display=='none'){v.style.display='block';i.src='img/less.gif';o.value = o.value+'$valor[campanaId]'+'-';}else{ v.style.display='none';i.src='img/more.gif';o.value = o.value.replace('$valor[campanaId] ','')}\">
        <th><img src=\"img/pixel.gif\" width=\"15px\"><img src=\"img/$icono_bloque.gif\" id=\"img_$uid_$valor[campanaId]\"> $valor[campana]</th>
        </tr>
        </table>
        <div id=\"bloque_$uid_$valor[campanaId]\" style=\"display:$display_bloque;\">
        <table id=\"tabla_contactos$contador_tablas\" class=\"tablesorter\" >
            <thead>
              <tr>
              <th style=\"width:150px; cursor:pointer;\">Origen</td>
              <th style=\"width:360px; cursor:pointer;\">Nombre</th>
              <th style=\"width:340px; cursor:pointer;\">Vendedor</th>
              <th style=\"width:150px; cursor:pointer;\">Registro</th>
              <th style=\"width:170px; cursor:pointer;\">Tel o Email</th>
              <th style=\"width:170px; cursor:pointer;\">Producto</th>
              <th style=\"width:170px; cursor:pointer;\">Compromiso</th>
              <th style=\"width:150px; cursor:pointer;\">Retraso (hrs)</th>
              <th style=\"width:32px; cursor:pointer;\">Sel.</th></tr>
                      </thead>
                      <tbody>";

        if(count($registros)> 0)
        {
            $modelos_seleccionados='';
            foreach($registros as $valores)
            {
                $campanaOriginal=$valor['campanaId'];
                $campanaDeRegistro=$valores['idcampana'];
                $i=1;
                if($campanaOriginal == $campanaDeRegistro)
                {
                    if($valores[retraso]<= 0)
                        $valores[retraso]=0;

                    $modelos_seleccionados=Regresa_Modelos($db,$valores['idcontacto']);
                    $total_bloque++;
                    $conunt=$i+$i;
                    $idfuente=$valores[idorigen];
                    $id_ven=$valores[uid];
                    $tabla_campanas .= "
                        <tr class=\"row".(($c++%2)+1)."\">
                        <td>".$array_fuentes[$idfuente]."</td>
                        <td>
                            <a class='background-color:#fff;' target=\"llamada\" href=\"index.php?_module=Campanas&_op=llamada_ro&llamada_id=$valores[idllamada]&contacto_id=$valores[idcontacto]&campana_id={$valores[idcampana]}\">
                             ".$valores['nombre']."</a>
                        </td>
                        <td>".$array_vendedores[$id_ven]."</td>
                        <td>".$valores['fecha_importado']."</td>
                        <td>".$valores['tels']."<br>".$valores['email']."</td>
                        <td>".$modelos_seleccionados."</td>";
                        $compromiso='';
                        $ret='';
                        if($valores['idstatus'] == -2)
                        {
                            if($valores['compromiso']!='0000-00-00 00:00:00')
                            {
                                $compromiso=$valores['compromiso'];
                                $ret=$valores['retraso'];
                            }

                        }
                    $tabla_campanas .= "<td>".$compromiso."</td>
                        <td>".$ret."</td>
                        <td><input type=\"checkbox\" name=\"chbx_".$valores[idcontacto]."\" style=\"height:12;width:16;\"></td></tr>";
                    $total_campana=$valores['total_campana'];
                }
            }
        }
        $tabla_campanas .= "</tbody>";
        $tabla_campanas .= "<tr class=\"row".(($c++%2)+1)."\"><td align=\"right\"><b>Total</b></td><td><b>$total_bloque</b></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>";
        $tabla_campanas .= "</table>";
        $tabla_campanas .= "</div>";
    }
    $tabla_campanas .= "<table class=\"width100\">";
    $tabla_campanas .= "<thead>";
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

}
$archivo='Monitoreo-de-Prospectos-'.$gid;
if(file_exists($archivo.".xls"))
    unlink($archivo.".xls");
$objeto = new Genera_Excel($tabla_campanas,$archivo,$_site_name,1);
$boton_excel=$objeto->Obten_href();


function Regresa_Modelos($db,$idcontacto)
{
    $lista_modelos='';
    $sql="SELECT modelo FROM crm_prospectos_unidades WHERE contacto_id=".$idcontacto.";";
    $res=$db->sql_query($sql) or die("Error en la consulta de vehiculos");
    if($db->sql_numrows($res)>0)
    {
        while(list($modelo) = $db->sql_fetchrow($res))
        {
            $lista_modelos.=$modelo.", ";
        }
        $lista_modelos=substr($lista_modelos,0,(strlen($lista_modelos) - 2));
    }
    return $lista_modelos;
}
function Regresa_Vendedores($db,$gid)
{
    $array=array();
    $sql="SELECT uid,name FROM users WHERE gid='".$gid."' ORDER BY gid,uid;";
    $res=$db->sql_query($sql) or die("Error en la consulta de vendeores ".$sql);
    if($db->sql_numrows($res)>0)
    {
        while(list($id,$name) = $db->sql_fetchrow($res))
        {
            $array[$id]=$name;
        }
    }
    return $array;
}
function Regresa_Fuentes($db)
{
    $array=array();
    $sql="SELECT fuente_id,nombre FROM crm_fuentes WHERE fuente_id!=0 ORDER BY fuente_id;";
    $res=$db->sql_query($sql) or die("Error en la consulta de fuentes  ".$sql);
    if($db->sql_numrows($res)>0)
    {
        while(list($id,$name) = $db->sql_fetchrow($res))
        {
            $array[$id]=$name;
        }
    }
    return $array;
}


/*        $tabla_excel.=
        "<table style=\"text-align: left; width: 100%;\" border=\"0\" cellpadding=\"2\" cellspacing=\"2\"> <tbody>
        <tr style=\"cursor:pointer\" >
        <th><img src=\"img/$icono_bloque.gif\" id=\"img_$uid_$valor[campanaId]\">$valor[campana]</th>
        </tr>
        </table>
        <div id=\"bloque_$uid_$valor[campanaId]\" style=\"display:$display_bloque;\">
           <table>
             <thead>
              <tr>
              <th>Campaña</td>
              <th>Nombre</th>
              <th>Vendedor</th>
              <th>Registro</th>
              <th>Primer contacto</th>
              <th>Último contacto</th>
              <th>Compromiso</th>
              <th>Retraso (hrs)</th>
              <th>Tel Casa</th>
              <th>Tel Oficina</th>
              <th>Tel Movil</th>
              <th>Email</th>
              <th>Domicilio</th>
              <th>Colonia</th>
              <th>Cp</th>
              <th>Poblacion</th>
              <th>Productos de Interes</th>
            </thead>
            <tbody>";*/

?>