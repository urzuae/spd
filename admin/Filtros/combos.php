<?
if (!defined('_IN_ADMIN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}

global $db, $id, $id_zona, $combo;


$idcombo = $_GET["id"];
$id_zona = $_GET['id_zona'];
$action =$_GET["combo"];

switch($action){
    case "grupo_empresarial_id":{
		$res = $db->sql_query("SELECT region_id,nombre FROM crm_regiones order by nombre ASC");
		echo"<option value='0'>Selecciona</option>";
        while($rs = $db->sql_fetchrow($res))
			echo '<option value="'.$rs["region_id"].'">'.htmlentities(strtoupper(($rs["nombre"]))).'</option>';
	break;
    }

	case "region_id":{
		$res = $db->sql_query("SELECT zona_id,nombre FROM crm_zonas WHERE region_id = $idcombo order by nombre ASC");
		echo"<option value='0'>Selecciona</option>";	
		while($rs = $db->sql_fetchrow($res))
			echo '<option value="'.$rs["zona_id"].'">'.htmlentities(strtoupper(($rs["nombre"]))).'</option>';
	break;
	}
	
	case "zona_id":{
        $res = $db->sql_query("SELECT DISTINCT a.entidad_id as entidad_id,b.nombre as nombre FROM crm_zonas_entidad a, crm_entidades b WHERE a.zona_id=$idcombo AND a.entidad_id=b.id_entidad  ORDER BY b.nombre;");
		echo"<option value='0'>Selecciona</option>";
		while($rs = $db->sql_fetchrow($res))
        {
			echo '<option value="'.$rs["entidad_id"].'">'.htmlentities(strtoupper(($rs["nombre"]))).'</option>';
        }
    break;
	}
    case "entidad_id":{
		$res = $db->sql_query("SELECT plaza_id,nombre FROM crm_plazas WHERE entidad_id= ".$idcombo." order by nombre;");
		echo"<option value='0'>Selecciona</option>";
		while($rs = $db->sql_fetchrow($res))
			echo '<option value="'.$rs["plaza_id"].'">'.htmlentities(strtoupper(($rs["nombre"]))).'</option>';
	break;

        }
    case "plaza_id":{
		$res = $db->sql_query("SELECT a.gid,b.name FROM crm_plazas_concesionarias a,groups_ubications b WHERE a.plaza_id= ".$idcombo."  AND a.gid=b.gid  AND b.zona_id=".$id_zona." AND b.active=true order by b.name;");
		echo"<option value='0'>Selecciona</option>";
		while($rs = $db->sql_fetchrow($res))
			echo '<option value="'.$rs["gid"].'">'.htmlentities(strtoupper(($rs["nombre"]))).'</option>';
        break;
        }
	
	case "origenPadre":{				
		$res = $db->sql_query("SELECT *
		FROM crm_contactos_origenes
		INNER JOIN crm_contactos_origenes_padre ON crm_contactos_origenes.origen_padre_id = crm_contactos_origenes_padre.origen_padre_id
		WHERE crm_contactos_origenes.origen_padre_id =".$idcombo." order by crm_contactos_origenes.nombre");
		echo"<option value='0'>Selecciona</option>";		
		while($rs = $db->sql_fetchrow($res))
			echo '<option value="'.$rs["origen_id"].'">'.htmlentities(strtoupper(($rs["nombre"]))).'</option>';
	break;
	}
	
}
die();
?>
