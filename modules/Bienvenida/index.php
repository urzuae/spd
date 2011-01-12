<?

if (!defined('_IN_MAIN_INDEX')) {
    die("No puedes acceder directamente a este archivo...");
}

global $db, $uid, $title, $_site_title, $super, $_modulesdir, $tipo_usuario,$name_usuario,$usuario_franja;
$_site_title = "Bienvenida";

if (!$uid)
{
    $no_registrado = " Para operarlo ingrese su <b>usuario</b> y <b>clave</b> (este es sensible a mayúsculas y minúsculas).";
}
else
{
    $sql = "SELECT super FROM users WHERE uid='$uid'";
    $_result = $db->sql_query($sql) or die("Error al consultar user y password en db");
    $super = $db->sql_fetchfield(0, 0, $_result);
    #include_once($_modulesdir . "/Gerente/ciclo_venta.php");
    if ($super == "4" || $super == "6") {
        $instrucciones = '<br>Para poder usar el sistema, el gerente debe: <br><br> <a href="index.php?_module=Gerente&_op=usuario"><u>Dar de alta</u></a> a la fuerza de venta<br><!-- <a href= "index.php?_module=Gerente&_op=administracion_vendedores"><u>Configurar</u></a> cuotas de venta por vendedores--><br> <a href="index.php?_module=Gerente&_op=monitoreo_vendedores"><u>Configurar tiempo de cumplimiento</u></a><br><br>El vendedor debe de administrar los prospectos que le asigno el gerente y/o los que ingrese al sistema';
        $imagen_gerente = '<img src="img/Pv01_r2_c3.jpg" name="imagen1" alt="Sales Funnel">';
        
    } elseif ($super == "8") {
        $instrucciones = "<br>Instrucciones para utilizar el sistema:<br>El vendedor debe de administrar los prospectos que le asigno el gerente y/o los que ingrese al sistema";
        $imagen_gerente = '<img src="img/Pv01_r2_c2.jpg" name="imagen1" alt="Sales Funnel">';
    
    }
    
    else
        $instrucciones = "";
    
    $instrucciones.="<br>".$buffer;
}

?>