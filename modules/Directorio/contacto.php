<?
  if (!defined('_IN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}
global $db, $guarda, $last_module, $last_op, $close_after, $uid,$_site_title,
       $contacto_id,
       $nombre, $apellido_paterno, $apellido_materno,
       $sexo,
       $compania, $cargo,
       $tel_casa, $tel_oficina,
       $tel_movil, $tel_otro,
       $email,
       $domicilio, $colonia,
       $cp, $poblacion,
       $entidad_id, $idVehiculo, $idVersion, $idTransmision,
       $rfc, $persona_moral,
       /*$fecha_de_nacimiento, */$dia, $mes, $ano,
       $ocupacion,
       $edo_civil, $titulo, $sector, $pais, $ciudad, $primer_cont, $origen, $codigo_campana,$modelo, $ano_auto,
       $nota, $version, $color_int, $color_ext, $tipo_pint,
       $no_contactar,$a,$motivo_baja, $razon_social, $nombre_contacto,$prioridad_contacto,
       $lada_casa_2, $lada_movil_2, $lada_oficina_2, $tel_casa_2, $tel_oficina_2, $tel_movil_2,
       $horario_casa, $horario_casa_2, $horario_oficina, $horario_oficina_2, $horario_celular, 
       $horario_celular_2, $lada1, $lada2, $lada3,$color_semaforo;

$_site_title = "Contacto nuevo";
include_once("modules/Gerente/class_autorizado.php");

//VERIFICA SI EL VENDEDOR TIENE PERMISOS PARA EDITAR
$sql = "SELECT edit_contact FROM users WHERE uid = '$uid' and super = '8'";
$result = $db->sql_query($sql);
list($edit_contact) = $db->sql_fetchrow($result);

$idVehiculo = 0 + $_REQUEST["listVehicle"];
$idVersion =  0; //+ $_REQUEST["listVersion"];
$idTransmision = 0; //+ $_REQUEST["listTransmision"];

if($edit_contact == ""){
	$edit_contact = 1;
}

if($contacto_id == ""){
	$edit_contact = 1;
}
if(!$contacto_id){
	$valida_existe = "valida_existente();";
}
else
{
	$valida_existe= "document.contacto.action = \"index.php\";
		    document.contacto.guarda.value = \"1\";
		    document.contacto.method = \"POST\";
		    document.contacto.submit();";
}
if($a == 'd')
{
	$sql = "INSERT INTO crm_contactos_finalizados SELECT * FROM crm_contactos WHERE contacto_id='$contacto_id' LIMIT1 )";
	$result = $db->sql_query($sql) or die("Error al insertar datos del contacto ".$sql);
	$sql = "DELETE FROM crm_contactos WHERE contacto_id='$contacto_id'";
	$result = $db->sql_query($sql) or die("Error al eliminar datos del contacto");
	$sql = sprintf("INSERT INTO crm_prospectos_cancelaciones
	        (contacto_id, uid, motivo_id, motivo)
	        VALUES
	        ('%s','%s','%s','%s')",$res["contacto_id"],$uid, $motivo_baja,"");
	$result = $db->sql_query($sql) or die("Error al eliminar datos del contacto: motivos");
	header("Location: index.php?_module=$last_module&_op=$last_op");
}

//inicializar cosas
$sql = "SELECT gid FROM users WHERE uid='$uid' LIMIT 1";
$result = $db->sql_query($sql) or die("Error al obtener datos del usuario");
list($gid) = $db->sql_fetchrow($result);
$gid_del_usuario = $gid;
/*if ($gid == "1") //si estamos entrando desde VWM tenemos permisos
{
	if (!$contacto_id) //solo podemos entrar aquí si estamos editando uno ya creado desde seleccionar concesionaria
		header("location: index.php?_module=$_module&_op=seleccionar_concesionaria");
	//obtenemos el gid correcto del contacto ya guardado
	$sql = "SELECT gid FROM crm_contactos WHERE contacto_id='$contacto_id' LIMIT 1";
	$result = $db->sql_query($sql) or die("Error al obtener datos del usuario");
	list($gid) = $db->sql_fetchrow($result);
}*/

if(!$ano) $ano = "0000";
if(!$mes) $mes = "00";
if(!$dia) $dia = "00";
$fecha_de_nacimiento = "$ano-$mes-$dia";

if (!$contacto_id) //nuevo
{
    $_title = "Contacto nuevo";
    if ($guarda)
    {
        $_uid = $uid;
        //checar si es callcenter, si lo es no asignarle el contacto
        $sql = "select user from users where uid = '$uid' and (super = '10' OR super = '12')";
        $r = $db->sql_query($sql) or die($sql);
        if ($db->sql_numrows($r) > 0)
        {
            list($_user) = $db->sql_fetchrow($r);
            $_uid = 0;
        }
        if($lada1) $tel_casa = "(" . $lada1 . ") " . $tel_casa;
        if($lada2) $tel_oficina = "(" . $lada2 . ") " . $tel_oficina;
        if($lada3) $tel_movil = "(" . $lada3 . ") " . $tel_movil;
        if($lada_casa_2) $tel_casa_2 = "(" . $lada_casa_2 . ") " . $tel_casa_2;
        if($lada_oficina_2) $tel_oficina_2 = "(" . $lada_oficina_2 . ") " . $tel_oficina_2;
        if($lada_movil_2) $tel_movil_2 = "(" . $lada_movil_2 . ") " . $tel_movil_2;

        $horario_casa = (is_array($horario_casa)) ? $horario_casa : array();
        $horario_casa_2 = (is_array($horario_casa_2)) ? $horario_casa_2 : array();
        $horario_oficina = (is_array($horario_oficina)) ? $horario_oficina : array();
        $horario_oficina_2 = (is_array($horario_oficina_2)) ? $horario_oficina_2 : array();
        $horario_celular = (is_array($horario_celular)) ? $horario_celular : array();
        $horario_celular_2 = (is_array($horario_celular_2)) ? $horario_celular_2 : array();
        $horario_preferido_casa = implode('',$horario_casa);
        $horario_preferido_casa_2 = implode('',$horario_casa_2);
        $horario_preferido_oficina = implode('',$horario_oficina);
        $horario_preferido_oficina_2 = implode('',$horario_oficina_2);
        $horario_preferido_movil = implode('',$horario_celular);
        $horario_preferido_movil_2 = implode('',$horario_celular_2);
        $sql_modelo="SELECT unidad_id,nombre FROM crm_unidades where unidad_id=".$idVehiculo.";";
        $res_modelo=$db->sql_query($sql_modelo);
        if($db->sql_numrows($res_modelo)>0)
        {
            $modelo=$db->sql_fetchfield(1, 0, $res_modelo);
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
                    no_contactar, uid, gid, titulo, sector, pais, ciudad,
                    primer_contacto, origen_id, fecha_importado,
                    razon_social, nombre_contacto,tel_casa_2,tel_oficina_2,tel_movil_2,
                    horario_preferido_casa,horario_preferido_oficina,horario_preferido_movil,
                    horario_preferido_casa_2,horario_preferido_oficina_2,horario_preferido_movil_2,codigo_campana,prioridad)
                    VALUES (
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
                    '$no_contactar', '$_uid', '$gid', '$titulo', '$sector', '$pais', '$ciudad',
                    '$primer_cont', '$origen', NOW(),
                    '$razon_social','$nombre_contacto',
                    '$tel_casa_2','$tel_oficina_2','$tel_movil_2',
                    '$horario_preferido_casa','$horario_preferido_oficina','$horario_preferido_movil',
                    '$horario_preferido_casa_2','$horario_preferido_oficina_2','$horario_preferido_movil_2','$codigo_campana','$prioridad_contacto')";
        $db->sql_query($sql) or die("$sql<br>Error al insertar contacto".print_r($db->sql_error()));
		$contacto_id = $db->sql_nextid();
		$sql = "INSERT INTO crm_prospectos_unidades (
					contacto_id,modelo, version, ano,tipo_pintura, color_exterior, color_interior,
                    modelo_id, version_id, transmision_id
				) VALUES (
					'$contacto_id','$modelo', '$version', '$ano_auto','$tipo_pint', '$color_ext', '$color_int',
                    '$idVehiculo', '$idVersion', '$idTransmision'
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
            //primero se lo asignamos al callcenter/hostess
            $sql = "INSERT INTO crm_contactos_asignacion_log (contacto_id, uid, from_uid, to_uid, from_gid, to_gid)VALUES('$contacto_id','$uid','0','$uid','0','$gid')";
            $db->sql_query($sql) or die("Error");

            //luego se lo desasignamos y se le asigna al $_uid (a nadie)
			$sql = "INSERT INTO crm_contactos_asignacion_log (contacto_id, uid, from_uid, to_uid, from_gid, to_gid)VALUES('$contacto_id','0','$uid','$_uid','0','0')";
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
            $sql = "INSERT INTO crm_contactos_asignacion_log (contacto_id, uid, from_uid, to_uid, from_gid, to_gid)VALUES('$contacto_id','$uid','0','$uid','0','$gid')";
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
    if (!$guarda) //mostrar el actual para editarlo
    {    
        $sql = "SELECT nombre, apellido_paterno, apellido_materno,sexo,compania, cargo,
                tel_casa, tel_oficina,tel_movil, tel_otro,email,domicilio, colonia,cp, poblacion,
                entidad_id,rfc, persona_moral,fecha_de_nacimiento,ocupacion,edo_civil,nota,no_contactar,
                timestamp, titulo, sector, pais, ciudad, primer_contacto, origen_id,razon_social, nombre_contacto,
                tel_casa_2,tel_oficina_2,tel_movil_2,
                horario_preferido_casa,horario_preferido_oficina,horario_preferido_movil,
                horario_preferido_casa_2,horario_preferido_oficina_2,horario_preferido_movil_2,fecha_autorizado,fecha_firmado,codigo_campana,prioridad
                FROM crm_contactos WHERE contacto_id='$contacto_id' LIMIT 1";
        $result = $db->sql_query($sql) or die("Error al consultar datos del contacto");
        list($nombre, $apellido_paterno, $apellido_materno,$sexo,$compania, $cargo,
             $tel_casa, $tel_oficina,$tel_movil, $tel_otro,$email,$domicilio, $colonia,$cp, $poblacion,
             $entidad_id,$rfc, $persona_moral,$fecha_de_nacimiento,$ocupacion,$edo_civil,$nota,$no_contactar,
             $timestamp, $titulo, $sector, $pais, $ciudad, $primer_cont, $origen,$razon_social, $nombre_contacto,
             $tel_casa_2, $tel_oficina_2, $tel_movil_2,
             $horario_preferido_casa, $horario_preferido_oficina, $horario_preferido_movil,
             $horario_preferido_casa_2, $horario_preferido_oficina_2, $horario_preferido_movil_2,$fecha_autorizado,$fecha_firmado,$codigo_campana
            ) = htmlize($db->sql_fetchrow($result));


        $par = array('(',')');
        list($lada1, $tel_casa, $ext_casa) = explode(" ", str_replace($par, "", $tel_casa));
        list($lada3, $tel_movil, $ext_movil) = explode(" ", str_replace($par, "", $tel_movil));
        list($lada2, $tel_oficina, $ext_oficina) = explode(" ", str_replace($par, "", $tel_oficina));
        list($lada4, $tel_otro, $ext_otro) = explode(" ", str_replace($par, "", $tel_otro));
        list($lada_casa_2, $tel_casa_2, $ext_casa_2) = explode(" ", str_replace($par, "", $tel_casa_2));
        list($lada_oficina_2, $tel_oficina_2, $ext_oficina_2) = explode(" ", str_replace($par, "", $tel_oficina_2));
        list($lada_movil_2, $tel_movil_2, $ext_movil_2) = explode(" ", str_replace($par, "", $tel_movil_2));
        list($horario_casa_manana_checked,$horario_casa_tarde_checked,$horario_casa_noche_checked) = get_horario($horario_preferido_casa);
        list($horario_casa_manana_checked_2,$horario_casa_tarde_checked_2,$horario_casa_noche_checked_2) = get_horario($horario_preferido_casa_2);
        list($horario_oficina_manana_checked,$horario_oficina_tarde_checked,$horario_oficina_noche_checked) = get_horario($horario_preferido_oficina);
        list($horario_oficina_manana_checked_2,$horario_oficina_tarde_checked_2,$horario_oficina_noche_checked_2) = get_horario($horario_preferido_oficina_2);
        list($horario_celular_manana_checked,$horario_celular_tarde_checked,$horario_celular_noche_checked) = get_horario($horario_preferido_movil);
        list($horario_celular_manana_checked_2,$horario_celular_tarde_checked_2,$horario_celular_noche_checked_2) = get_horario($horario_preferido_movil_2);
		$sql = "SELECT  modelo, version, ano, tipo_pintura, color_exterior, color_interior, modelo_id, version_id, transmision_id
                FROM crm_prospectos_unidades WHERE contacto_id='$contacto_id' LIMIT 1";
        $result = $db->sql_query($sql) or die("Error al consultar datos del contacto");
        list($modelo, $version, $ano_auto, $tipo_pint, $color_ext, $color_int, $idVehiculo, $idVersion, $idTransmision) = htmlize($db->sql_fetchrow($result));
        list($ano, $mes, $dia) = explode("-", $fecha_de_nacimiento);
		$primer_cont = date_reverse($primer_cont);
        include_once("modules/Directorio/visualiza_modelos.php");
        $otro_auto='';
        if($edit_contact == 1)
            $otro_auto='<input type="button" name="actualiza_auto"  id="actualiza_auto" value="Actualizar Inf Producto">&nbsp;&nbsp;<input type="button" name="otro_auto"  id="otro_auto" value="Agrega Producto">';
        $objeto= new Fecha_autorizado ($db,$fecha_autorizado,$fecha_firmado);
        $color_semaforo=$objeto->Obten_Semaforo();
    }
    else
    {

        if($lada1)  $tel_casa = "(" . $lada1 . ") " . $tel_casa;
        if($lada2)  $tel_oficina = "(" . $lada2 . ") " . $tel_oficina;
        if($lada3)  $tel_movil = "(" . $lada3 . ") " . $tel_movil;
        if($lada4)  $tel_otro = "(" . $lada4 . ") " . $tel_otro;

        if($lada_casa_2) $tel_casa_2 = "(" . $lada_casa_2 . ") " . $tel_casa_2;
        if($lada_oficina_2) $tel_oficina_2 = "(" . $lada_oficina_2 . ") " . $tel_oficina_2;
        if($lada_movil_2) $tel_movil_2 = "(" . $lada_movil_2 . ") " . $tel_movil_2;

        if ($ext_casa) $tel_casa .= " $ext_casa";
        if ($ext_casa_2) $tel_casa_2 .= " $ext_casa_2";
        if ($ext_movil) $tel_movil .= " $ext_movil";
        if ($ext_movil_2) $tel_movil_2 .= " $ext_movil_2";
        if ($ext_oficina) $tel_oficina .= " $ext_oficina";
        if ($ext_oficina_2) $tel_oficina_2 .= " $ext_oficina_2";

        $horario_casa = (is_array($horario_casa)) ? $horario_casa : array();
        $horario_casa_2 = (is_array($horario_casa_2)) ? $horario_casa_2 : array();
        $horario_oficina = (is_array($horario_oficina)) ? $horario_oficina : array();
        $horario_oficina_2 = (is_array($horario_oficina_2)) ? $horario_oficina_2 : array();
        $horario_celular = (is_array($horario_celular)) ? $horario_celular : array();
        $horario_celular_2 = (is_array($horario_celular_2)) ? $horario_celular_2 : array();
        $horario_preferido_casa = implode('',$horario_casa);
        $horario_preferido_casa_2 = implode('',$horario_casa_2);
        $horario_preferido_oficina = implode('',$horario_oficina);
        $horario_preferido_oficina_2 = implode('',$horario_oficina_2);
        $horario_preferido_movil = implode('',$horario_celular);
        $horario_preferido_movil_2 = implode('',$horario_celular_2);
		$primer_cont = date_reverse($primer_cont);
        $sql = "UPDATE crm_contactos SET
                nombre='$nombre', apellido_paterno='$apellido_paterno', apellido_materno='$apellido_materno',
                sexo='$sexo',compania='$compania', cargo='$cargo',
                tel_casa='$tel_casa', tel_oficina='$tel_oficina',tel_movil='$tel_movil', tel_otro='$tel_otro',
                email='$email',domicilio='$domicilio', colonia='$colonia',cp='$cp', poblacion='$poblacion',
                entidad_id='$entidad_id',rfc='$rfc', persona_moral='$persona_moral',`fecha_de_nacimiento`='$fecha_de_nacimiento',
                `ocupacion`='$ocupacion',edo_civil='$edo_civil',nota='$nota',no_contactar='$no_contactar',
                titulo = '$titulo',sector = '$sector',pais = '$pais',ciudad = '$ciudad',primer_contacto = '$primer_cont',
                origen_id = '$origen',razon_social = '$razon_social',nombre_contacto = '$nombre_contacto',codigo_campana='$codigo_campana',
                tel_casa_2 = '$tel_casa_2', tel_oficina_2 = '$tel_oficina_2', tel_movil_2 = '$tel_movil_2',
                horario_preferido_casa = '$horario_preferido_casa', horario_preferido_oficina = '$horario_preferido_oficina', horario_preferido_movil = '$horario_preferido_movil',
                horario_preferido_casa_2 = '$horario_preferido_casa_2', horario_preferido_oficina_2 = '$horario_preferido_oficina_2', horario_preferido_movil_2 = '$horario_preferido_movil_2',prioridad='$prioridad_contacto'
                WHERE contacto_id='$contacto_id' LIMIT 1";
        $result = $db->sql_query( $sql ) or die("\n$sql\n1-Error al actualizar datos del contacto".print_r($db->sql_error(), true));
        if ($close_after)
        {
                    die("<html><head><title>Cerrar</title><script>window.close();</script></head></html>");
        }
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
if($cp)
{
    $filtro_colonia=Recupera_colonia($db,$cp,$colonia);
}
function Recupera_colonia($db,$cp,$colonia)
{
    $filtro='';
    $sql = "SELECT d_codigo,d_asenta FROM cps WHERE d_codigo='".$cp."';";
        $res =$db->sql_query($sql);
    if($db->sql_numrows($res)>0)
    {
        while(list($d_codigo,$d_asenta) = $db->sql_fetchrow($res))
        {
            $nombre=str_replace('á','&aacute;',$d_asenta);
            $nombre=str_replace('é','&eacute;',$nombre);
            $nombre=str_replace('í','&iacute;',$nombre);
            $nombre=str_replace('ó','&oacute;',$nombre);
            $nombre=str_replace('ú','&uacute;',$nombre);
            $nombre=str_replace('ñ','&ntilde;',$nombre);
            $tmp="";
             if(trim($nombre)==trim($colonia))
                $tmp=" SELECTED ";
            $select.='<option value="'.$nombre.'" '.$tmp.'>'.$nombre.'</option>';
        }
    }
    return $select;
}


$select_entidades = select_entidades_federativas($entidad_id);
$select_dia = select_dia($dia);
$select_mes = select_mes($mes);
$select_ano = select_ano($ano);
$select_sexo = select_sexo($sexo);
$select_edo_civil = select_edo_civil($edo_civil);
$select_persona_moral = select_sino("persona_moral", $persona_moral);
$select_no_contactar = select_sino("no_contactar", $no_contactar);

//Motivos de baja
$sql = "SELECT motivo_id, motivo from crm_prospectos_cancelaciones_motivos order by motivo_id asc";
$r = $db->sql_query($sql) or die($sql);
$motivo_baja = "<select name=\"motivo_baja\" id=\"motivo_baja\">
                 <option value=\"0\">Seleccione uno</option>\n";
while(list($id_mot,$mot) = $db->sql_fetchrow($r)){
  	$motivo_baja .= "<option value=\"$id_mot\">$mot</option>\n";
}
$motivo_baja .= "</select>";

//Prioridad
$sql="SELECT prioridad_id,prioridad,valor,color FROM crm_prioridades_contactos ORDER BY prioridad_id DESC;";
$res=$db->sql_query($sql) or die("Error en la consulta:  ".$sql);
if($db->sql_numrows($res)>0)
{
    $prioridad= "<select name='prioridad_contacto' id='prioridad_contacto'>
            <option value=''></option>";
    while(list($prioridad_id,$prioridad_nm,$valor,$color_prioridad) = $db->sql_fetchrow($res))
    {
        $tmp='';
        if($prioridad_id == $prioridad_contacto)
            $tmp=' selected ';
        $prioridad.="<option value='".$prioridad_id."' ".$tmp."  style='background-color:".$color_prioridad."'>".$prioridad_nm."</option>";
    }
    $prioridad.="</select>";
}
// fecha de primer contacto
 if($primer_cont == '') $primer_cont=date('d-m-Y');
//Nombre de la concesionaria
$sql = "SELECT name FROM `groups` WHERE gid = '$gid'";
$r = $db->sql_query($sql) or die($sql);
list($gid_name) = $db->sql_fetchrow($r);

//select origen y tipo de usuario
$sql = "select super from users where uid = '$uid'";
$cs = $db->sql_query($sql);
list($super) = $db->sql_fetchrow($cs);

$origen_readonly='';
if($super == 8 and $contacto_id != "")
{
  $origen_readonly = 'disabled="disabled"';
}
if( (!empty($origen)) && ($origen != 0))
{
  $tmp_nom_origen=recupera_nombre($db,$origen);
}


$ret_prod = $db->sql_query("SELECT * FROM `crm_unidades`");
while(list($id_prod, $name_prod) = $db->sql_fetchrow($ret_prod))
{
  $selected_prod = $name_prod == $modelo ? "selected" : "";
  $select_product .= "<option value=$id_prod $selected>$name_prod</option>";
}


$sql_padres="SELECT a.padre_id,b.nombre,b.fuente_id FROM crm_fuentes_arbol a,crm_fuentes b WHERE a.padre_id=1 and a.hijo_id=b.fuente_id AND b.active=1 ORDER BY b.nombre;";
$res_padres=$db->sql_query($sql_padres);
if( $db->sql_numrows($res_padres) > 0)
{
    $select_origen_padre.="<input type='hidden' id='gid' name='gid' value=".$gid.">
                            <input type='hidden' id='origen' name='origen' value=".$origen.">";
    $select_origen_padre.="<select name=\"padre_id\" id=\"padre_id\" class=\"nodo\" $origen_readonly>
                               <option value='0'>Seleccione</option>";
    if( (!empty($origen)) && ($origen != 0))
    {
        $select_origen_padre.="<option value='".$origen."' selected>".$tmp_nom_origen."</option>";
    }
    while($fila = $db->sql_fetchrow($res_padres))
    {
        $select_origen_padre.= "<option value=\"".$fila['fuente_id']."\">".$fila['nombre']."</option>";
    }
    $select_origen_padre.="</select>";
}
/** cambios de luis hdez **/
$select_origen_padre.="<br><select name='hijo_id_1' id='hijo_id_1' class='nodo'><option value='0'>Seleccionar</option></select>
                        <br><select name='hijo_id_2' id='hijo_id_2' class='nodo'><option value='0'>Seleccionar</option></select>
                        <br><select name='hijo_id_3' id='hijo_id_3' class='nodo'><option value='0'>Seleccionar</option></select>
                        <br><select name='hijo_id_4' id='hijo_id_4' class='nodo'><option value='0'>Seleccionar</option></select>";
include_once("admin/Catalogos/mostrarArbol.php");
$_site_title = "Nuevo prospecto";
if ($close_after)
{
  $cancelar_button = "<input id=\"cerrar\" name=\"cerrar\" value=\"Cerrar\" onclick=\"self.close();\" type=\"button\">";
  global $_no_boxes;
  $_no_boxes = 1;
}
else
{
  $cancelar_button = "<input value=\"Cancelar\" name=\"buttoncancelar\" id=\"buttoncancelar\" onclick=\"location.href='index.php?_module=$last_module&_op=$last_op'\" type=\"button\">";
}
if($edit_contact == 0)
        $guardar_button = '<input value="Guardar" name="buttonguardarcerrar" id="buttonguardarcerrar" type="button" onclick="self.close();">';
else
    $guardar_button = '<input value="Guardar" name="buttonguardar" id="buttonguardar" type="button" onclick="return validate();">';



/*$r = $db->sql_query("SELECT YEAR(NOW())");
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
*/





global $_admin_menu2;
/*$_admin_menu2 = "<table style=\"text-align: left; width: 100%;\" border=\"0\" cellpadding=\"2\" cellspacing=\"2\">
                   <tbody>
                     <tr><td style=\"text-align: left; vertical-align: top;\">&nbsp;</td></tr>
                   </tbody>
                 </table>
                       ";*/
$sql  = "SELECT gid, super, user FROM users WHERE uid='$uid'";
$result = $db->sql_query($sql) or die("Error");
list($gid, $super) = $db->sql_fetchrow($result);
if ($super == 10 && $gid != 1){
	$baja = "";
}
else{
	$form_baja = "<form onchange=\"capsall(this);\" method=\"post\" action=\"index.php?_module=$_module&_op=$_op&a=d\" onSubmit=\"return validate_baja(this);\">
  <input name=\"contacto_id\" id=\"contacto_id\" value=\"$contacto_id\" type=\"hidden\">
  <input name=\"llamada_id\" value=\"$llamada_id\" type=\"hidden\">
  <input name=\"last_module\" value=\"$last_module\" type=\"hidden\">
  <input name=\"last_op\" value=\"$last_op\" type=\"hidden\">
  <table class=\"width100\" border=\"0\" cellpadding=\"2\" cellspacing=\"2\">
    <thead><tr>
      <td colspan=\"4\"><img alt=\"\" src=\"img/personal.gif\">Baja</td>
    </tr>
    </thead>
    <tbody>
      $baja
    </tbody>
  </table>
</form>";
	$baja = "<tr class=\"row1\">
        <td style=\"text-align: right;\">Motivos de Baja</td>
        <td colspan=\"3\">
           <table>
             <tr>
               <td>
               $motivo_baja
               </td>
               <td>
                 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input value=\"Baja\" name=\"btnBaja\" type=\"submit\">
               </td>
             </tr>
           </table>
        </td>
      </tr>";
}


function get_horario($string){
    $return = array(
    (eregi('M',$string) ? 'checked="checked"' : ''),
    (eregi('T',$string) ? 'checked="checked"' : ''),
    (eregi('N',$string) ? 'checked="checked"' : '')
    );
    return $return;
}

function recupera_padres($db,$origen,$cadena_origenes)
{
    $sql_tmp="SELECT * FROM `crm_fuentes_arbol`WHERE hijo_id ='".$origen."';";
    $res_tmp=$db->sql_query($sql_tmp);
    $pos=0;
    if($db->sql_numrows($res_tmp) > 0)
    {
        $tmp_padre=$db->sql_fetchfield(0, 0, $res_tmp);
        $tmp_hijo=$db->sql_fetchfield(1, 0, $res_tmp);
        $tmp_name_padre = recupera_nombre($db,$tmp_padre);
        $tmp_name_hijo  = recupera_nombre($db,$tmp_hijo);
        $cadena_origenes.=$tmp_name_hijo."|";
        $conta=0;
        if($tmp_padre != 0)
        {

            $cadena_origenes=recupera_padres($db,$tmp_padre,$cadena_origenes);
            //echo"<br>".$cadena_origenes."   entra";
            $conta++;
        }
    }
    return $cadena_origenes;
}
function recupera_nombre($db,$origen)
{
    $tmp_nom_origen='';
    $sql_origen="SELECT nombre from crm_fuentes where fuente_id=".$origen." and active=1;";
    $res_origen=$db->sql_query($sql_origen);
    $tmp_nom_origen=$db->sql_fetchfield(0,0,$res_origen);
    return $tmp_nom_origen;

}
?>