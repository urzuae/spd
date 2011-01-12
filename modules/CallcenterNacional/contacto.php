<?
  if (!defined('_IN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}

global $db, $submit, $last_module, $last_op, $close_after, $uid,
       $contacto_id,
       $nombre, $apellido_paterno, $apellido_materno,
       $sexo,
       $compania, $cargo,
       $tel_casa, $tel_oficina,
       $tel_movil, $tel_otro,
       $email,
       $domicilio, $colonia,
       $cp, $poblacion,
       $entidad_id,
       $rfc, $persona_moral,
       /*$fecha_de_nacimiento, */$dia, $mes, $ano,
       $ocupacion,
       $edo_civil, $titulo, $sector, $pais, $ciudad, $primer_cont, $origen, $modelo, $ano_auto, 
       $nota, $version, $color_int, $color_ext, $tipo_pint,
       $no_contactar,$a,$motivo_baja;
       $_site_title;
       
if($a == 'd'){
	$res = array();
	$sql = "SELECT * FROM crm_contactos WHERE contacto_id='$contacto_id' LIMIT 1";
	$result = $db->sql_query($sql) or die("Error al obtener datos del usuario");
	$res = htmlize($db->sql_fetchrow($result));

	$sql = sprintf("INSERT INTO crm_contactos_finalizados
	        (`contacto_id`, `contrato_id`,`uid`,`gid`,`origen_id`,
	         `origen2_id`,`origen_contacto_id`,`origen_extra`,`nombre`,`apellido_paterno`,
	         `apellido_materno`,`sexo`,`compania`,`cargo`,`tel_casa`,
	         `tel_oficina`,`tel_movil`,`tel_otro`,`email`,`domicilio`,
	         `colonia`,`cp`,`poblacion`,`entidad_id`,`rfc`,
	         `curp`,`persona_moral`,`fecha_de_nacimiento`,`ocupacion`,`edo_civil`,
	         `nombre_conyugue`,`nota`,`no_contactar`,`prospecto`,`fecha_importado`,
	         `titulo`,`sector`,`pais`,`ciudad`,`primer_contacto`,
	         `prioridad`,`motivo_fin`)
	         VALUES(
	         '%s','%s','%s','%s','%s',
	         '%s','%s','%s','%s','%s',
	         '%s','%s','%s','%s','%s',
	         '%s','%s','%s','%s','%s',
	         '%s','%s','%s','%s','%s',
	         '%s','%s','%s','%s','%s',
	         '%s','%s','%s','%s','%s',
	         '%s','%s','%s','%s','%s',
	         '%s','%s')",
	         $res["contacto_id"],$res["contrato_id"],$res["uid"],$res["gid"],$res["origen_id"],
	         $res["origen2_id"],$res["origen_contacto_id"],$res["origen_extra"],$res["nombre"],$res["apellido_paterno"],
	         $res["apellido_materno"],$res["sexo"],$res["compania"],$res["cargo"],$res["tel_casa"],
	         $res["tel_oficina"],$res["tel_movil"],$res["tel_otro"],$res["email"],$res["domicilio"],
	         $res["colonia"],$res["cp"],$res["poblacion"],$res["entidad_id"],$res["rfc"],
	         $res["curp"],$res["persona_moral"],$res["fecha_de_nacimiento"],$res["ocupacion"],$res["estado_civil"],
	         $res["nombre_conyugue"],$res["nota"],$res["no_contactar"],$res["prospecto"],$res["fecha_importado"],
	         $res["titulo"],$res["sector"],$res["pais"],$res["ciudad"],$res["primer_contacto"],
	         $res["prioridad"],$motivo_baja);
	$result = $db->sql_query($sql) or die("Error al insertar datos del contacto ".$sql);
	$sql = "DELETE FROM crm_contactos WHERE contacto_id='$contacto_id'";
	$result = $db->sql_query($sql) or die("Error al eliminar datos del contacto");
	header("Location: index.php?_module=$last_module&_op=$last_op");
}       

//inicializar cosas
$sql = "SELECT gid FROM users WHERE uid='$uid' LIMIT 1";
$result = $db->sql_query($sql) or die("Error al obtener datos del usuario");
list($gid) = $db->sql_fetchrow($result);
$gid_del_usuario = $gid;
if ($gid == "1") //si estamos entrando desde VWM tenemos permisos
{
	if (!$contacto_id) //solo podemos entrar aquí si estamos editando uno ya creado desde seleccionar concesionaria
		header("location: index.php?_module=$_module&_op=seleccionar_concesionaria");
	//obtenemos el gid correcto del contacto ya guardado
	$sql = "SELECT gid FROM crm_contactos WHERE contacto_id='$contacto_id' LIMIT 1";
	$result = $db->sql_query($sql) or die("Error al obtener datos del usuario");
	list($gid) = $db->sql_fetchrow($result);	
}
if(!$ano) $ano = "0000";
if(!$mes) $mes = "00";
if(!$dia) $dia = "00";
$fecha_de_nacimiento = "$ano-$mes-$dia";

if (!$contacto_id) //nuevo
{
    $_title = "Contacto nuevo";
    if ($submit)
    {
    	
      $_uid = $uid;
      
      //checar si es callcenter, si lo es no asignarle el contacto
      $sql = "select user from users where uid = '$uid' and (user like '%CALLCENTER%' || user like '%HOSTESS%')";
      $r = $db->sql_query($sql) or die($sql);
      
      if ($db->sql_numrows($r) > 0)
      {
      	list($_user) = $db->sql_fetchrow($r);
        $_uid = 0;
      }

		$primer_cont = date_reverse($primer_cont);
        $sql = "INSERT INTO crm_contactos (
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
                    no_contactar, uid, gid, titulo, sector, pais, ciudad, primer_contacto, origen_id, fecha_importado
                ) VALUES (
                    '$nombre', '$apellido_paterno', '$apellido_materno',
                    '$sexo',
                    '$compania', '$cargo',
                    '$tel_casa', '$tel_oficina',
                    '$tel_movil', '$tel_otro',
                    '$email',
                    '$domicilio', '$colonia',
                    '$cp', '$poblacion',
                    '$entidad_id',
                    '$rfc', '$persona_moral',
                    '$fecha_de_nacimiento',
                    '$ocupacion',
                    '$edo_civil',
                    '$nota',
                    '$no_contactar', '$_uid', '$gid', '$titulo', '$sector', '$pais', '$ciudad', '$primer_cont', '$origen', NOW()
                )";
        $db->sql_query($sql) or die("$sql<br>Error al insertar contacto".print_r($db->sql_error()));
		$contacto_id = $db->sql_nextid();

		$sql = "INSERT INTO crm_prospectos_unidades (
					contacto_id,
					modelo, version, ano, 
					tipo_pintura, color_exterior, color_interior
				) VALUES (
					'$contacto_id', 
					'$modelo', '$version', '$ano_auto',
					'$tipo_pint', '$color_ext', '$color_int'
				)";
                $db->sql_query($sql) or die("$sql<br>Error al insertar contacto".print_r($db->sql_error()));

        if ($_uid == 0)
        {
            //buscar a que campaña lo meteremos
            $sql = "SELECT c.campana_id FROM crm_campanas_groups AS g, crm_campanas AS c WHERE c.campana_id=g.campana_id AND g.gid='$gid' ORDER BY c.campana_id  LIMIT 1"; //la primera que sea parte de un ciclo
            $result = $db->sql_query($sql) or die("Error al leer".print_r($db->sql_error()));
            list($campana_id) = $db->sql_fetchrow($result);
            //para agregarlo a crm_campanas_llamadas
            $sql = "insert into crm_campanas_llamadas  (campana_id, contacto_id) values ('$campana_id', '$contacto_id')";
            $db->sql_query($sql) or die("Error al insertar a la campaña".print_r($db->sql_error()));
            //guardar el log de asignacion 
            $sql = "INSERT INTO crm_contactos_asignacion_log (contacto_id, uid, from_uid, to_uid, from_gid, to_gid)VALUES('$contacto_id','0','0','$_uid','0','$gid')";
            $db->sql_query($sql) or die("Error");
            //checamos si el user de callcenter o hostess tienen permisos  para asignar a un vendedor
            $sql = "INSERT INTO crm_contactos_callcenter (contacto_id, uid, gid)VALUES('$contacto_id', '$uid', '$gid_del_usuario')";
			$db->sql_query($sql) or die("Error de callcenter. $sql");
			//checar si este usuario puede asignar a vendedor directamente
			$sql = "SELECT uid FROM users_asigna_vendedor WHERE uid='$uid'  LIMIT 1"; //la primera que sea parte de un ciclo
            $result = $db->sql_query($sql) or die("Error al leer".print_r($db->sql_error()));
            //ahora redirigir al módulo de seleccionar vendedor
            if ($db->sql_numrows($result) > 0)
				$redirect = "index.php?_module=$_module&_op=asignar_vendedor&contacto_id=$contacto_id";
			else
				$redirect = "index.php?_module=$_module&_op=$_op";	
			
        }
        else
        {
            //buscar a que campaña lo meteremos
            $sql = "SELECT c.campana_id FROM crm_campanas_groups AS g, crm_campanas AS c WHERE c.campana_id=g.campana_id AND g.gid='$gid' ORDER BY c.campana_id  LIMIT 1"; //la primera que sea parte de un ciclo
            $result = $db->sql_query($sql) or die("Error al leer".print_r($db->sql_error()));
            list($campana_id) = $db->sql_fetchrow($result);
            //para agregarlo a crm_campanas_llamadas
            $sql = "insert into crm_campanas_llamadas  (campana_id, contacto_id) values ('$campana_id', '$contacto_id')";
            $db->sql_query($sql) or die("Error al insertar a la campaña".print_r($db->sql_error()));
            //guardar el log de asignacion 
            $sql = "INSERT INTO crm_contactos_asignacion_log (contacto_id, uid, from_uid, to_uid, from_gid, to_gid)VALUES('$contacto_id','0','0','0','0','$gid')";
            $db->sql_query($sql) or die("Error al insertar al log");
            $redirect = "index.php?_module=$_module&_op=$_op";
        }

	  print ("<script language=\"javascript\" type=\"text/javascript\">alert(\"Se ha creado el nuevo contacto\"); location.href = \"$redirect\";</script>");
		  
          //header("location:index.php?_module=$_module&_op=$_op&contacto_id=$contacto_id");
    }
}
else //editar
{
    $readonly = "READONLY";
    if (!$submit) //mostrar el actual para editarlo
    {
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
                    timestamp, titulo, sector, pais, ciudad, primer_contacto, origen_id
                FROM crm_contactos WHERE contacto_id='$contacto_id' LIMIT 1";
        $result = $db->sql_query($sql) or die("Error al consultar datos del contacto");
        list(
                $nombre, $apellido_paterno, $apellido_materno,
                $sexo,
                $compania, $cargo,
                $tel_casa, $tel_oficina,
                $tel_movil, $tel_otro,
                $email,
                $domicilio, $colonia,
                $cp, $poblacion,
                $entidad_id,
                $rfc, $persona_moral,
                $fecha_de_nacimiento,
                $ocupacion,
                $edo_civil,
                $nota,
                $no_contactar,
                $timestamp, $titulo, $sector, $pais, $ciudad, $primer_cont, $origen
            ) = htmlize($db->sql_fetchrow($result));
			
		$sql = "SELECT 
                    modelo, version, ano, tipo_pintura, color_exterior, color_interior
                FROM crm_prospectos_unidades WHERE contacto_id='$contacto_id' LIMIT 1";
        $result = $db->sql_query($sql) or die("Error al consultar datos del contacto");
        list(
                $modelo, $version, $ano_auto, $tipo_pint, $color_ext, $color_int
            ) = htmlize($db->sql_fetchrow($result));		
        list($ano, $mes, $dia) = explode("-", $fecha_de_nacimiento);
		$primer_cont = date_reverse($primer_cont);
    }
    else
    {
		$primer_cont = date_reverse($primer_cont);
        $sql = "UPDATE crm_contactos SET
                    nombre='$nombre', apellido_paterno='$apellido_paterno', apellido_materno='$apellido_materno',
                    sexo='$sexo',
                    compania='$compania', cargo='$cargo',
                    tel_casa='$tel_casa', tel_oficina='$tel_oficina',
                    tel_movil='$tel_movil', tel_otro='$tel_otro',
                    email='$email',
                    domicilio='$domicilio', colonia='$colonia',
                    cp='$cp', poblacion='$poblacion',
                    entidad_id='$entidad_id',
                    rfc='$rfc', persona_moral='$persona_moral',
                    `fecha_de_nacimiento`='$fecha_de_nacimiento',
                    `ocupacion`='$ocupacion',
                    edo_civil='$edo_civil', 
                    nota='$nota',
                    no_contactar='$no_contactar', 
					titulo = '$titulo',
					sector = '$sector',
					pais = '$pais',
					ciudad = '$ciudad',
					primer_contacto = '$primer_cont', 
					origen_id = '$origen'
                WHERE contacto_id='$contacto_id' LIMIT 1";
        $result = $db->sql_query($sql) or die("Error al actualizar datos del contacto".print_r($db->sql_error()));
		$sql = "UPDATE crm_prospectos_unidades SET
                    modelo = '$modelo', 
					version = '$version', 
					ano = '$ano_auto', 
					tipo_pintura = '$tipo_pint', 
					color_interior = '$color_int',
					color_exterior = '$color_ext'
                WHERE contacto_id='$contacto_id' LIMIT 1";
        $result = $db->sql_query($sql) or die("Error al actualizar datos del contacto".print_r($db->sql_error()));
		if ($close_after)
          die("<html><head><title>Cerrar</title><script>window.close();</script></head></html>");
        else
        {
          header("location:index.php?_module=$last_module&_op=$last_op");
        }
    }
    list($f, $h) = explode(" ", $timestamp);
    $timestamp2 = date_reverse($f) ." $h";
    $_title = "Editando contacto";
    $_subtitle = "$nombre $apellido_paterno $apellido_materno - Última modificación: $timestamp2";
}
//los selects del form en html
require_once("$_includesdir/select.php");
$select_entidades = select_entidades_federativas($entidad_id);
$select_dia = select_dia($dia);
$select_mes = select_mes($mes);
$select_ano = select_ano($ano);
$select_sexo = select_sexo($sexo);
$select_edo_civil = select_edo_civil($edo_civil);
$select_persona_moral = select_sino("persona_moral", $persona_moral);
$select_no_contactar = select_sino("no_contactar", $no_contactar);
//Motivos de baja
//Entidad federativa de la concesionaria
$select_entidad_federativa_id = "<select name=\"entidad_federativa_id\" id=\"entidad_federativa_id\" onChange=\"obtenerMunicipios();\">";
$select_entidad_federativa_id .= " <option value=\"\" selected>Seleccione uno</option>\n";
$i = 0;
foreach ($_entidades_federativas AS $entidad_federativa){
  $i++;
  if($entidad_id == $i)
     $select_entidad_federativa_id .= "<option value=\"$i\" selected>$entidad_federativa</option>";
  else
     $select_entidad_federativa_id .= "<option value=\"$i\">$entidad_federativa</option>";
}
$select_entidad_federativa_id .= "</select>";
$sql = "SELECT motivo_id, motivo from crm_prospectos_cancelaciones_motivos order by motivo_id asc";
$r = $db->sql_query($sql) or die($sql);
$motivo_baja = "<select name=\"motivo_baja\" id=\"motivo_baja\">
                 <option value=\"0\">Seleccione uno</option>\n";
while(list($id_mot,$mot) = $db->sql_fetchrow($r)){
  	$motivo_baja .= "<option value=\"$id_mot\">$mot</option>\n";
}
$motivo_baja .= "</select>";
//Prioridad
$sql = "SELECT p.prioridad, p.color FROM crm_prioridades_contactos AS p, crm_contactos AS c WHERE c.contacto_id = '$contacto_id' AND c.prioridad = p.prioridad_id";
$r = $db->sql_query($sql) or die($sql);
list($prioridad,$color_prioridad) = $db->sql_fetchrow($r);
//Nombre de la concesionaria
$sql = "SELECT name FROM `groups` WHERE gid = '$gid'";
$r = $db->sql_query($sql) or die($sql);
list($gid_name) = $db->sql_fetchrow($r);
//select de modelo
$sql = "SELECT nombre from crm_unidades order by nombre asc";
$r = $db->sql_query($sql) or die($sql);
while(list($mod) = $db->sql_fetchrow($r))
{
	$modelos[] = $mod;
}
$select_modelo = select_array("modelo", $modelos, $modelo);

//select origen
$sql = "SELECT nombre, fuente_id from crm_fuentes order by fuente_id desc";
$r = $db->sql_query($sql) or die($sql);
while(list($org, $oid) = $db->sql_fetchrow($r))
{
	$origenes[] = array($oid, $org);
}
$select_origen = select_array_index("origen", $origenes, $origen);


$_site_title .= " - $_title - $_subtitle";
if ($close_after)
{
  $cancelar_button = "<input value=\"Regresar\" onclick=\"window.close();\" type=\"button\">";
  global $_no_boxes;
  $_no_boxes = 1;
}
else
  $cancelar_button = "<input value=\"Regresar\" onclick=\"location.href='index.php?_module=$last_module&_op=$last_op'\" type=\"button\">";

$r = $db->sql_query("SELECT YEAR(NOW())");
list($anos_vehiculo) = $db->sql_fetchrow($r);
$r = $db->sql_query("SELECT ano FROM `crm_prospectos_unidades` WHERE `contacto_id` = '$contacto_id'");
list($ano_vehiculo) = $db->sql_fetchrow($r);
$select_ano_vehiculo = "<select name=\"ano_vehiculo\" id=\"ano_vehiculo\">";
if(($ano_vehiculo < $anos_vehiculo) && ($ano_vehiculo != 0) )
  $select_ano_vehiculo .= "<option value=\"".($ano_vehiculo)."\">".($ano_vehiculo)."</option>";
$select_ano_vehiculo .= "<option value=\"".($anos_vehiculo-1)."\">".($anos_vehiculo-1)."</option>
      <option value=\"".($anos_vehiculo)."\" SELECTED>".($anos_vehiculo)."</option>
      <option value=\"".($anos_vehiculo+1)."\">".($anos_vehiculo+1)."</option>
   </select>";
  
  
global $_admin_menu2;
$_admin_menu2 = "<table style=\"text-align: left; width: 100%;\" border=\"0\" cellpadding=\"2\" cellspacing=\"2\">
                   <tbody>
                     <tr>
                       <td style=\"text-align: left; vertical-align: top;\">
                         <table width=\"100%\"><th>Catálogo</th></table>
                         <ul id=nav>
                           <li><a href=\"http://www.volkswagen.com/vwcms_publish/vwcms/master_public/virtualmaster/es_mx/models/POinter2.html\" target=\"vw_com\">Pointer</a></li>
                           <li><a href=\"http://www.volkswagen.com/vwcms_publish/vwcms/master_public/virtualmaster/es_mx/models/fox.html\" target=\"vw_com\">Lupo</a></li>
                           <li><a href=\"http://www.volkswagen.com/vwcms_publish/vwcms/master_public/virtualmaster/es_mx/models/derby.html\" target=\"vw_com\">Derby</a></li>
                           <li><a href=\"http://www.volkswagen.com/vwcms_publish/vwcms/master_public/virtualmaster/es_mx/models/polo.html\" target=\"vw_com\">Polo</a></li>
                           <li><a href=\"http://www.volkswagen.com/vwcms_publish/vwcms/master_public/virtualmaster/es_mx/models/multivan.html\" target=\"vw_com\">SportsVan</a></li>
                           <li><a href=\"http://www.volkswagen.com/vwcms_publish/vwcms/master_public/virtualmaster/es_mx/models/Fox.html\" target=\"vw_com\">CrossFox</a></li>
                           <li><a href=\"http://www.volkswagen.com/vwcms_publish/vwcms/master_public/virtualmaster/es_mx/models/jetta.html\" target=\"vw_com\">Jetta</a></li>
                           <li><a href=\"http://www.volkswagen.com/vwcms_publish/vwcms/master_public/virtualmaster/es_mx/models/golfmx.html\" target=\"vw_com\">Golf</a></li>
                           <li><a href=\"http://www.volkswagen.com/vwcms_publish/vwcms/master_public/virtualmaster/es_mx/models/beetle_mx.html\" target=\"vw_com\">Beetle</a></li>
                           <li><a href=\"http://www.volkswagen.com/vwcms_publish/vwcms/master_public/virtualmaster/es_mx/models/touran.html\" target=\"vw_com\">Bora</a></li>
                           <li><a href=\"http://www.volkswagen.com/vwcms_publish/vwcms/master_public/virtualmaster/es_mx/models/gli_fah.html\" target=\"vw_com\">GLI</a></li>
                           <li><a href=\"http://www.volkswagen.com/vwcms_publish/vwcms/master_public/virtualmaster/es_mx/models/gti_mx.html\" target=\"vw_com\">GTI</a></li>
                           <li><a href=\"http://www.volkswagen.com/vwcms_publish/vwcms/master_public/virtualmaster/es_mx/models/passat_mx.html\" target=\"vw_com\">Passat</a></li>
                           <li><a href=\"http://www.volkswagen.com/vwcms_publish/vwcms/master_public/virtualmaster/es_mx/models/sharan.html\" target=\"vw_com\">Sharan</a></li>
                           <li><a href=\"http://www.volkswagen.com/vwcms_publish/vwcms/master_public/virtualmaster/es_mx/models/touareg.html\" target=\"vw_com\">Touareg</a></li>
                         </ul>
                         <ul>
                           <li><A href=\"http://www.inhand.carspecs.jato.com/clientsites/inhand/mx.vwshowroom/vw_test5.html\">Virtual Show Room</A></li>
                         </ul>
                         <ul id=\"nav\">
                           <li><a href=\"http://www.volkswagen.com/vwcms_publish/vwcms/master_public/virtualmaster/es_mx/Arg/Promociones.html\" target=\"vw_com\">Promociones</a></li>
                           <li><a href=\"http://mx.volkswagen.clientsites.carspecs.jato.com/mx.volkswagen/browser.asp?screen=select&category=select_model&make=Volkswagen&translated_make=Volkswagen\" target=\"vw_com\">Compara</a></li>
                           <li><a href=\"http://www.volkswagen.com/vwcms_publish/vwcms/master_public/virtualmaster/es_mx/Arg/used_cars.html\" target=\"vw_com\">Configura y cotiza</a></li>
                           <li><a href=\"javascript:no_existe();\">Catálogo Eléctronico</a></li>
                           <li><a href=\"javascript:no_existe();\">Car Locator</a></li>
                           <li><a href=\"javascript:no_existe();\">Calculadora Financiera</a></li>
                           <li><a href=\"javascript:no_existe();\">Dealer Locator</a></li>
                         </ul>
                       </td>
                     </tr>
                   </tbody>
                 </table>";

$_site_title = "Contacto";

?>
