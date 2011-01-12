<?
  if (!defined('_IN_ADMIN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}
global $db,$gid,$_licenses,$submit,$guardar_ciclo_venta,$msg_ciclo,$_site_title;
$_site_title = "Ciclo de ventas";
$ayuda = "<p>¿Necesita ayuda? De un clic en el ícono.</p>
          <a href=\"../admin/Ayuda/ayuda.php\" onClick=\"return popup(this, 'notes')\"><img src=\"../img/ayuda.gif\" alt=\"Ayuda\" /></a>";
if($submit)
{
    $total=$_POST['total_ciclo'];
    $sql="select count(*) as total FROM crm_campanas;";
    $res=$db->sql_query($sql);
    list($no_etapas_ciclo)= $db->sql_fetchrow($res);
    if($no_etapas_ciclo > 0)
    {
        $db->sql_query("TRUNCATE TABLE `crm_campanas`;");
    }
    for($i=1;$i <= $total; $i++)
    {
        $campo="ciclo-".$i;
        $name="0001-".$i." ".$_POST[$campo];
        $sig=$i + 1;
        if($i == $total) $sig=0;
        $ins="INSERT INTO crm_campanas (etapa_ciclo_id,nombre,next_campana_id)
              VALUES ('$i','$name','$sig');";
        $db->sql_query($ins) or die ("Error en el insert del ciclo de venta ".$ins);
        #$guardar_ciclo_venta="<span class='arbol_bloqueado'>Se ha guardado el ciclo de venta</span>";
        header('location: index.php?_module=Modelos');
    }
}

//checamos si ya tenemos ciclo de venta
$sql="SELECT COUNT(*) AS no_distribuidoras FROM groups;";
$res=$db->sql_query($sql);
list($no_etapas) = $db->sql_fetchrow($res);
$sql_index ="SELECT id,nombre,orden FROM crm_ciclo_venta ORDER BY orden;";
$result = $db->sql_query($sql_index) or die("Error al consultar campañas");
if ($db->sql_numrows($result)>0)
{
    if($no_etapas < 1)
    {
        $tabla_campanas .="
            <input type='hidden' name='total_ciclo' id='total_ciclo' value='".$db->sql_numrows($result)."'>
            <table width='70%' border='0'>
            <tr><td>
            <p align='justify'><span class='parrafo'>
                El ciclo de venta sugerido consta de 5 etapas, el limite es de 10 etapas.
                Por favor revise su ciclo de venta, ya que al momento de crear una distribuidora, usted ya no podra modificar el ciclo. </span></p>
            <p align='justify'><span class='parrafo'>
                Una vez haya concluído, de un clic en el botón \"Aceptar ciclo de ventas\".
            </span></p>
            </td></tr></table><br>
                <table width=\"70%\" border=\"0\" align=\"center\">
                <thead>
                    <tr>
                        <td align='center' width='40%'>Nombre</a></td>
                        <td width='15%' align='center'>Cambiar Nombre</td>
                        <td width='15%' align='center'>Subir</td>
                        <td width='15%' align='center'>Bajar</td>
                        <td width='15%' align='center'>Eliminar</td>
                    </tr>
                </thead><tbody>";
                $consec=1;
                while (list($id,$name,$orden) = htmlize($db->sql_fetchrow($result)))
                {
                    $tabla_campanas .= "
                    <tr class=\"row".(($c++%2)+1)."\">
                        <td align='left'> ".$consec."&nbsp;&nbsp;<input value='".$name."' name='ciclo-".$consec."' id='ciclo' type='hidden' >".$name."</td>
                        <td align='center'>
                            <a href='#' onclick=\"actualiza('".$id."','".$name."')\">
                            <img src='../img/edit.gif' border='0'>
                        </td>
                        <td align='center'>
                            <a href='#' onclick=\"asciende('".$id."','".$consec."')\">
                            <img src='../img/desc.1.gif' border='0'>
                        </td>
                        <td align='center'>
                            <a href='#' onclick=\"desciende('".$id."','".$consec."')\">
                            <img src='../img/asc.1.gif' border='0'>
                        </td>
                            <td align='center'>
                            <a href='#' onclick=\"elimina_etapa('".$id."','".$db->sql_numrows($result)."');\">
                            <img src='../img/del.gif' border='0' width='16' height='16'>
                        </td>
                    </tr>";
                    $consec++;
                }
                $tabla_campanas .= "</tbody>
                                    <tr height='30'><td colspan='5'>&nbsp;</td></tr>
                                    <tr>
                                    <td align='center' colspan='5'>
                                        <input type='button' name='incluir' id='incluir' value='Incluir Etapa'>
                                        &nbsp;&nbsp;&nbsp;&nbsp;
                                        <input type='submit' name='submit' id='submit' value='Aceptar Ciclo de Venta' >
                                    </td></tr></table><br>
                                    <div id='nva_etapa'>
                                        Favor de Teclear el nombre de la etapa:&nbsp;
                                        <input type='text' name='nm_ciclo_venta' id='nm_ciclo_venta' value='' onBlur='caps1(this);'>
                                        &nbsp;&nbsp;<input type='button' name='guardar_etapa' id='guardar_etapa' value='Guardar Etapa'>
                                        &nbsp;&nbsp;<input type='button' name='actualiza_etapa' id='actualiza_etapa' value='Actualizar Etapa'>
                                        &nbsp;&nbsp;<input type='button' name='cancelar_etapa' id='cancelar_etapa' value='Cancelar'></div><br>
                                    <div id='respuesta'></div><br>";
    }
    else
    {
        $tabla_campanas .= "<h2>Listado de Ciclo de venta </h2><br>
            <table width=\"50%\" border=\"0\" align=\"center\">
            <thead>
                <tr>
                <td align='center' width='40%'>Nombre</a></td>
                </tr>
            </thead><tbody>";
        $consec=1;
        while (list($id,$name,$orden) = htmlize($db->sql_fetchrow($result)))
        {
            $tabla_campanas .= "
            <tr class=\"row".(($c++%2)+1)."\">
                <td align='left'>$consec  $name</td>
            </tr>";
            $consec++;
        }
        $tabla_campanas .= "</table><br><p align='center'>Para poder actualizar su ciclo de venta, favor de comunicarse a las Oficinas de PCS México.</p><br>";
    }
}
else
{
    $db->sql_query("TRUNCATE TABLE `crm_ciclo_venta`;");
    $db->sql_query("INSERT INTO crm_ciclo_venta (nombre,orden) values ('PROSPECCION',1);");
    header('location: index.php?_module=Campanas');
}

?>