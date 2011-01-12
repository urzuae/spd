<?
if (! defined ( '_IN_MAIN_INDEX' )) {
	die ( "No puedes acceder directamente a este archivo..." );
}

global $db, $submit, $last_module, $last_op, $close_after, $uid, $contacto_id, $nombre, $apellido_paterno, $apellido_materno, $sexo, $compania, $cargo, $tel_casa, $tel_oficina, $tel_movil, $tel_otro, $email, $domicilio, $colonia, $cp, $poblacion, $entidad_id, $rfc, $persona_moral,
       $dia, $mes, $ano, $ocupacion, $edo_civil, $titulo, $sector, $pais, $ciudad, $primer_cont, $origen, $modelo, $ano_auto, $nota, $version, $color_int, $color_ext, $tipo_pint, $no_contactar;

$enabled = "DISABLED";

//inicializar cosas
$sql = "SELECT gid FROM users WHERE uid='$uid' LIMIT 1";
$result = $db->sql_query ( $sql ) or die ( "Error al obtener datos del usuario" );
list ( $gid ) = $db->sql_fetchrow ( $result );
$gid_del_usuario = $gid;
if ($gid == "1") //si estamos entrando desde VWM tenemos permisos
{
	if (! $contacto_id) //solo podemos entrar aquí si estamos editando uno ya creado desde seleccionar concesionaria
		header ( "location: index.php?_module=$_module&_op=seleccionar_concesionaria" );
		//obtenemos el gid correcto del contacto ya guardado
	$sql = "SELECT gid FROM crm_contactos WHERE contacto_id='$contacto_id' LIMIT 1";
	$result = $db->sql_query ( $sql ) or die ( "Error al obtener datos del usuario" );
	list ( $gid ) = $db->sql_fetchrow ( $result );
}
if (! $ano)
	$ano = "0000";
if (! $mes)
	$mes = "00";
if (! $dia)
	$dia = "00";
$fecha_de_nacimiento = "$ano-$mes-$dia";
$tabla=' crm_contactos ';
$sql_busca="SELECT contacto_id FROM crm_contactos_finalizados WHERE contacto_id='$contacto_id' LIMIT 1;";
$res_busca=$db->sql_query($sql_busca);
if($db->sql_numrows($res_busca)>0)    $tabla=' crm_contactos_finalizados ';
    $sql = "SELECT
        nombre, apellido_paterno, apellido_materno,
        sexo,
        compania, cargo,
        tel_casa, tel_oficina,
        tel_movil, tel_otro,
        email,
        domicilio, colonia,
        cp, poblacion,
        entidad_id,
        rfc, persona_moral,
        fecha_de_nacimiento,
        ocupacion,
        edo_civil, 
        nota,
        no_contactar,
        timestamp, titulo, sector, pais, ciudad, primer_contacto, origen_id,codigo_campana
        FROM ".$tabla." WHERE contacto_id='$contacto_id' LIMIT 1";
	$result = $db->sql_query ( $sql ) or die ( "Error al consultar datos del contacto" );
    list ( $nombre, $apellido_paterno, $apellido_materno, $sexo, $compania, $cargo, $tel_casa, $tel_oficina, $tel_movil, $tel_otro, $email, $domicilio, $colonia, $cp, $poblacion, $entidad_id, $rfc, $persona_moral, $fecha_de_nacimiento, $ocupacion, $edo_civil, $nota, $no_contactar, $timestamp, $titulo, $sector, $pais, $ciudad, $primer_cont, $origen, $codigo_campana ) = htmlize ( $db->sql_fetchrow ( $result ) );
		
	$sql = "SELECT 
            modelo, version_id, ano, tipo_pintura, color_exterior, color_interior
            FROM crm_prospectos_unidades WHERE contacto_id='$contacto_id' LIMIT 1";
	$result = $db->sql_query ( $sql ) or die ( "Error al consultar datos del contacto" );
	list ( $modelo, $version_id, $ano_auto, $tipo_pint, $color_ext, $color_int ) = htmlize ( $db->sql_fetchrow ( $result ) );
	list ( $ano, $mes, $dia ) = explode ( "-", $fecha_de_nacimiento );

    $sql_version="SELECT nombre FROM crm_versiones WHERE version_id=".$version_id.";";
    $res_version=$db->sql_query($sql_version);
    if($db->sql_numrows($res_version) > 0)
        $version=$db->sql_fetchfield(0,0,$res_version);


require_once ("$_includesdir/select.php");
$select_entidades = select_entidades_federativas ( $entidad_id );
$select_dia = select_dia ( $dia );
$select_mes = select_mes ( $mes );
$select_ano = select_ano ( $ano );
$select_sexo = select_sexo ( $sexo );
$select_edo_civil = select_edo_civil ( $edo_civil );
$select_persona_moral = select_sino ( "persona_moral", $persona_moral );
$select_no_contactar = select_sino ( "no_contactar", $no_contactar );
//select de modelo
$sql = "SELECT nombre from crm_unidades order by nombre asc";
$r = $db->sql_query ( $sql ) or die ( $sql );
while ( list ( $mod ) = $db->sql_fetchrow ( $r ) ) {
	$modelos [] = $mod;
}
$select_modelo = select_array ( "modelo", $modelos, $modelo );

//select origen

$sql = "SELECT nombre, fuente_id from crm_fuentes order by fuente_id desc";
$r = $db->sql_query ( $sql ) or die ( $sql );
if($db->sql_numrows($r)>0)
{
    $select_origen="<select name='origen'><option value='0'>Seleccione</option>";
    while ( list ( $org, $oid ) = $db->sql_fetchrow ( $r ) )
    {
        $tmp='';
        if($oid == $origen)
        {
            $tmp=' selected ';
        }
        $select_origen.="<option value='".$oid."' ".$tmp.">".$org."</option>";
    }
    $select_origen.="</select>";
}
$_title = "Contacto Finalizado";

$_site_title .= " - $_title";
if ($close_after) {
	$cancelar_button = "<input value=\"Regresar\" onclick=\"window.close();\" type=\"button\">";
	global $_no_boxes;
	$_no_boxes = 1;
} else
	$cancelar_button = "<input value=\"Regresar\" onclick=\"location.href='index.php?_module=$last_module&_op=$last_op'\" type=\"button\">";
?>
