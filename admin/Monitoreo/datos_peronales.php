<?
if (!defined('_IN_ADMIN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}
global $db, $uid, $how_many, $from, $campana_id, $contacto_id, $llamada_id, $submit, $status, $resultado, 
		$nota, $fecha_cita, $personal, $delalert, $ciclo_de_venta_id,
		$next_campana_id;
function html_entity_decode2( $given_html, $quote_style = ENT_QUOTES ) {
       $trans_table = array_flip(get_html_translation_table( HTML_SPECIALCHARS, $quote_style ));
       $trans_table['&#39;'] = "'";
       return ( strtr( $given_html, $trans_table ) );
}

$_css = $_themedir."/style.css";
$_theme = "";
$contacto_id=$_GET['contacto_id'];

if($contacto_id>0)
{
	$sql="SELECT crm_contactos.contacto_id,crm_contactos.uid,crm_contactos.gid,crm_contactos.origen_id,crm_contactos.origen2_id,concat(crm_contactos.nombre,' ',crm_contactos.apellido_paterno,' ',crm_contactos.apellido_materno) as Prospecto,crm_contactos.entidad_id,crm_contactos.fecha_importado,crm_prospectos_unidades.modelo,groups.name,users.name,'' as vendedor,0 as to_uid,0 as to_gid,'' as date_ult,'' as date_pen  FROM ((crm_contactos LEFT JOIN crm_prospectos_unidades  ON crm_contactos.contacto_id = crm_prospectos_unidades.contacto_id AND crm_contactos.uid>0) LEFT JOIN groups ON crm_contactos.gid=groups.gid) LEFT JOIN users ON crm_contactos.uid=users.uid AND users.super=8  limit 10;
	";
	
	
}
//datos de la persona
$sql = "SELECT nombre, apellido_paterno, apellido_materno,tel_casa, tel_oficina,tel_movil, tel_otro,nota, email, origen_id
        FROM crm_contactos WHERE contacto_id='$contacto_id' LIMIT 1";
$result = $db->sql_query($sql) or die("Error al consultar datos del contacto");
global $nombre, $apellido_paterno, $apellido_materno;
list($nombre, $apellido_paterno, $apellido_materno,
     $tel_casa, $tel_oficina,
     $tel_movil, $tel_otro,
     $nota, $email,
	$origen_id
    ) = htmlize($db->sql_fetchrow($result));

	
	//datos del vehículo
    $a="crm_contactos";
    $b="crm_prospectos_unidades";
    $c="users";
    $d="groups";
    $sql="SELECT {$a}.contacto_id, concat({$a}.nombre,' ',{$a}.apellido_paterno,' ',{$a}.apellido_materno) as contacto,
            {$a}.sexo, {$a}.compania, {$a}.cargo, {$a}.tel_casa, {$a}.tel_oficina, {$a}.tel_movil, {$a}.tel_otro, {$a}.email, {$a}.domicilio,
            {$a}.colonia, {$a}.cp, {$a}.poblacion, {$a}.edo_civil, {$a}.persona_moral, {$a}.rfc, {$a}.curp, {$b}.modelo, {$b}.version, {$b}.ano,
            {$b}.paquete, {$b}.tipo_pintura, {$b}.accesorios, {$b}.color_exterior, {$b}.color_interior, {$c}.name as vendedor, 
            {$d}.name as concesonaria
            FROM ((({$a}
              LEFT JOIN {$b} ON {$a}.contacto_id = {$b}.contacto_id) LEFT JOIN {$c} ON {$a}.uid={$c}.uid) LEFT JOIN {$d} ON {$a}.gid={$d}.gid)
              WHERE {$a}.contacto_id ={$contacto_id}
              LIMIT 1";
     $resul=$db->sql_query($sql);
     if($db->sql_numrows($resul) > 0)
     {
     	$array_datos=$db->sql_fetchrow($resul);
        echo"<br>".$sql;
        for($i=0;$i < $db->sql_numfields($resul);$i++)
        {
        	
        	for($j=0;$j< $db->sql_numrows($resul);$j++)
        	{
        		echo"<br><font color='#3e4f88'>".$db->sql_fieldname($i,$resul).":</font>&nbsp;&nbsp;".$db->sql_fetchfield($i,$j,$resul);
        	}	
        }
     }
     
?>
