<?
  if (!defined('_IN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}
global $db,$_site_title;

$_site_title = "Compromisos";
$style1 = "row1";
$style2 = "row2";

if(isset($_REQUEST))
{
    $fecha_ini = $_REQUEST["fecha_ini"];
    $fecha_fin = $_REQUEST["fecha_fin"];
    if($fecha_ini != "" and $fecha_fin != "")
    {
        $add = " and date_format(fecha_cita,'%Y-%m-%d') between '$fecha_ini' and '$fecha_fin'";
    }
    elseif($fecha_ini != "")
    {
        $add = " and date_format(fecha_cita, '%Y-%m-%d') >= '$fecha_ini'";
    }
    elseif($fecha_fin != "")
    {
        $add = " and date_format(fecha_cita, '%Y-%m-%d') <= '$fecha_fin'";
    }

    $tabla_campanas = '<table width="600">';
    $tabla_campanas .= '<thead><tr><td>Nombres</td><td>Apellido Paterno</td><td>Apellido Materno</td><td>Fecha cita</td><td></td></tr></thead><tbody>';

    $sql = "select nombre,apellido_paterno,apellido_materno,date_format(fecha_cita,'%d/%m/%Y %H:%i:%s'),a.contacto_id
    from crm_campanas_llamadas_no_asignados a inner join crm_contactos_no_asignados b
    on a.contacto_id = b.contacto_id where a.contacto_id > 0 $add order by fecha_cita";//die($sql);
    $cs = $db->sql_query($sql);
    while(list($nombre,$apellido_paterno,$apellido_materno,$fecha_cita,$contacto_id) = $db->sql_fetchrow($cs))
    {
        if($style == $style1)
            $style = $style2;
        else
            $style = $style1;
            
        $tabla_campanas .= "<tr><td class=\"$style\">$nombre</td><td class=\"$style\">$apellido_paterno</td><td class=\"$style\">$apellido_materno</td><td class=\"$style\">$fecha_cita</td><td><a href=\"index.php?_module=CallcenterNacional&_op=filtrar&contacto_id=$contacto_id&nopendientes=1\"><img src=\"img/phone.gif\" border=\"0\"></a></td></tr>";
        $_csv .= "\"$nombre\",\"$apellido_paterno\",\"$apellido_materno\",\"$fecha_cita\"\n";
    }

    $tabla_campanas .= '</tbody></table>';


    if($_REQUEST["excel"]){
        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename="'.$_op.'_'.date("d-m-Y").'.csv"');
        die($_csv);
    }
}
?>
