<?
if (!defined('_IN_MAIN_INDEX'))
{
    die ("No puedes acceder directamente a este archivo...");
}
require_once("$_includesdir/select.php");
global $db, $submit, $uid, $_edo_civil;
if ($submit)
{
    $entidades = Genera_Entidades($db);
    $unidades  = Genera_Unidades($db);

    $rechazados = array();
    $alertas = array();
    $nuevo_csv = array();
    $array_crm_contactos_logs = array();
    $array_crm_contactos_unidades = array();
    $array_crm_contactos = array();
    $array_vendedor_uid = array();

    $no_errores=0;
    $insertados = 0;
    // recupero el archivo
    $filename = $_FILES['f']['tmp_name'];
    $fh = fopen($filename, "r");
    if (!$fh)
        die("<html><head><script>alert('No se puede leer el archivo (tal vez sea demasiado grande o esté vacio)');location.href='index.php?_module=$_module&_op=$_op';</script></head></html>");

    $sql = "SELECT gid FROM users WHERE uid='$uid' LIMIT 1";
    $result = $db->sql_query($sql) or die("Error al obtener datos del usuario");
    list($gid) = $db->sql_fetchrow($result);
    $_edo_civil_upper = array_flip(array_change_key_case(array_flip($_edo_civil),CASE_UPPER));
    while($data = fgetcsv($fh, 1000, ","))
    {
        $linea++;
        if (!($iii++))
            continue;
        $procesados++;
        $data2 = array();
        foreach ($data as $undato)
        {
            $data2[] = addslashes($undato);
        }
        list(
            $nombre, $ap_pat, $ap_mat,
            $titulo, $sexo,
            $compania, $sector, $cargo,
            $tel_casa, $tel_ofi, $tel_mov, $tel_otro,
            $email,
            $domicilio, $colonia, $delegacion, $cp, $estado, $ciudad, $pais,
            $rfc, $moral, $fecha_nac, $ocupacion, $edo_civil,
            $nota,
            $no_contactar, $primer_cont, $origen,
            $modelo, $version, $ano, $tipo_pint, $color_ext, $color_int, $vendedor,$codigo)
        = $data2;

        if ((!$nombre))
        {
            $rechazados[] = $linea;//linea en la que lo botamos
            $rechazados_motivo[$linea] = "SE REQUIERE EL NOMBRE DEL PROSPECTO";
            $nuevo_csv[] = $data;
            $no_errores++;
            continue; //linea vacia
        }
        if ( (!$ap_pat))
        {
            $rechazados[] = $linea;//linea en la que lo botamos
            $rechazados_motivo[$linea] = "SE REQUIERE EL APELLIDO PATERNO DEL PROSPECTO";
            $nuevo_csv[] = $data;
            $no_errores++;
            continue; //linea vacia
        }

        if (!$tel_casa && !$tel_ofi && !$tel_mov && !$tel_otro)
        {
            $rechazados[] = $linea;//linea en la que lo botamos
            $rechazados_motivo[$linea] = "SE REQUIERE AL MENOS UN TELEFONO";
            $nuevo_csv[] = $data;
            $no_errores++;
            continue;
        }

        $primer_cont = str_replace("/", "-", $primer_cont);
        $primer_cont = date_reverse($primer_cont);
        list($anio, $mes, $dia) = explode ("-", $primer_cont);
        if ( $primer_cont != "" && ( strlen($anio) < 4  || $mes > 12 || $dia > 31 || $anio < 1900))
        {
            $rechazados[] = $linea;//linea en la que lo botamos
            $rechazados_motivo[$linea] = "LA FECHA DE PRIMER CONTACTO DEBE ESTAR EN FORMATO dd-mm-aaaa";
            $nuevo_csv[] = $data;
            $no_errores++;
            continue;
        }

        if( $primer_cont == "")
        {
            $primer_cont = date("Y-m-d");
            $alertas_motivo[$linea_alertas] = "$linea:LA FECHA DEL PRIMER CONTACTO ESTA VACIA.";
            $linea_alertas++;
        }

        $fecha_nac = str_replace("/", "-", $fecha_nac);
        $fecha_nac = date_reverse($fecha_nac);
        list($anio, $mes, $dia) = explode ("-", $fecha_nac);
        if ( $fecha_nac != "" &&(  strlen($anio) < 4  || $mes > 12 || $dia > 31 || $anio < 1900))
        {
            $rechazados[] = $linea;//linea en la que lo botamos
            $rechazados_motivo[$linea] = "LA FECHA DE NACIMIENTO DEBE ESTAR EN FORMATO dd-mm-aaaa";
            $nuevo_csv[] = $data;
            $no_errores++;
            continue;
        }
        $entidad_id=Revisa_Entidad($db,strtoupper(trim($estado)));
        if($entidad_id > 0)
            $estado=$entidades[$entidad_id];
        /*if($entidad_id == 0)
        {
            $rechazados[] = $linea;//linea en la que lo botamos
            $rechazados_motivo[$linea] = "EL ESTADO NO CONCUERDA, POR FAVOR REVISE LA ORTOGRAFIA";
            $nuevo_csv[] = $data;
            $no_errores++;
            continue;
        }*/
        if (strtoupper($sexo) == "MASCULINO")
            $sexo = "0";
        elseif  (strtoupper($sexo) == "FEMENINO")
            $sexo = "1";
        else
        {
            $rechazados[] = $linea;//linea en la que lo botamos
            $rechazados_motivo[$linea] = "EL SEXO DEBE SER MASCULINO O FEMENINO";
            $nuevo_csv[] = $data;
            $no_errores++;
            continue;
        }

        if (strtoupper($moral) == "SI")
            $moral = "0";
        elseif  (strtoupper($moral) == "NO")
            $moral = "1";
        else
        {
            $rechazados[] = $linea;//linea en la que lo botamos
            $rechazados_motivo[$linea] = "PERSONA MORAL SOLO PUEDE SER SI O NO";
            $no_errores++;
            $nuevo_csv[] = $data;
            continue;
        }
        if (!($edo_civil = array_search(strtoupper($edo_civil), $_edo_civil_upper)))
        {
            $rechazados[] = $linea;//linea en la que lo botamos
            $rechazados_motivo[$linea] = "EL ESTADO CIVIL NO CONCUERDA, POR FAVOR REVISE LA ORTOGRAFIA";
            $no_errores++;
            $nuevo_csv[] = $data;
            continue;
        }

        $sqlori = "SELECT nombre, fuente_id from crm_fuentes where active=1;";
        $rori = $db->sql_query($sqlori) or die($sqlori);
        while(list($org, $oid) = $db->sql_fetchrow($rori))
        {
            $origenes[$oid] = strtoupper($org);
        }
        if (!($origen = array_search(strtoupper($origen), $origenes)))
        {
            $rechazados[] = $linea;//linea en la que lo botamos
            $rechazados_motivo[$linea] = "EL ORIGEN O FUENTE NO CONCUERDA, POR FAVOR REVISE LA ORTOGRAFIA";
            $no_errores++;
            $nuevo_csv[] = $data;
            continue;
        }

        $modelo_id=Revisa_modelo($db,strtoupper(trim($modelo)));
        $modelo=$unidades[$modelo_id];
        if ($modelo_id == 0)
        {
            $rechazados[] = $linea;//linea en la que lo botamos
            $rechazados_motivo[$linea] = "EL MODELO ES INCORRECTO, POR FAVOR REVISE LA SINTAXIS";
            $no_errores++;
            $nuevo_csv[] = $data;
            continue;
        }
        $nombre = trim(strtoupper($nombre));
        $ap_pat = trim(strtoupper($ap_pat));
        $ap_mat = trim(strtoupper($ap_mat));

        $tmp=array();
        if($nombre != '')
            $tmp[]="nombre = '".$nombre."'";

        if($ap_pat != '')
            $tmp[]="apellido_paterno = '".$ap_pat."'";

        if($ap_mat != '')
            $tmp[]="apellido_materno = '".$ap_mat."'";

        if(count($tmp)>0)
        {
            $filtro=implode (" AND ",$tmp);
            $sql_nom="SELECT * FROM crm_contactos WHERE ".$filtro.";";
            $res_nom=$db->sql_query($sql_nom);
            $num_nom=$db->sql_numrows($res_nom);
            if($db->sql_numrows($res_nom)>0)
            {
                $rechazados[] = $linea;//linea en la que lo botamos
                $rechazados_motivo[$linea] = "EL PROSPECTO YA ESTA REGISTRADO, POR FAVOR REVISE EL NOMBRE ";
                $no_errores++;
                $nuevo_csv[] = $data;
                continue;
            }
        }
        $vendedor_uid=0;
        if($vendedor=="")
        {
            $alertas[] = $linea_alertas;//linea en la que lo botamos
            $alertas_motivo[$linea_alertas] = "$linea:NO SE ASIGN&Oacute; NINGUN VENDEDOR";
            $linea_alertas++;
        }
        else
        {
            $sql = "SELECT uid FROM users WHERE user='$vendedor' and gid='$gid'";//OR gid='0'
            $result_v = $db->sql_query($sql) or die("Error al asignar".print_r($db->sql_error()));
            if($db->sql_numrows($result_v) > 0)
            {
                list($vendedor_uid) = $db->sql_fetchrow($result_v);
            }
            else
            {
                $alertas[] = $linea_alertas;//linea en la que lo botamos
                $alertas_motivo[$linea_alertas] = "$linea:NO SE ASIGN&Oacute; NINGUN VENDEDOR";
                $linea_alertas++;
            }
        }
        // REVISAMOS EL CODIGO DE CAMPANA
        if($codigo!='')
        {
            if(strlen($codigo)>10)
            {
                $rechazados[] = $linea;//linea en la que lo botamos
                $rechazados_motivo[$linea] = "EL CODIGO DE CAMPA&Ntilde;A NO PUEDE TENER MAS DE 10 CARACTERES DE LONGITUD, POR FAVOR REVISE EL CODIGO";
                $no_errores++;
                $nuevo_csv[] = $data;
                continue;
            }
        }
        $date=date("Y-m-d H:i:s");
        $array_crm_contactos[]="INSERT INTO crm_contactos (
                    nombre, apellido_paterno, apellido_materno,
                    sexo,compania, cargo,tel_casa, tel_oficina,
                    tel_movil, tel_otro,email,domicilio, colonia,
                    cp, poblacion,entidad_id,rfc, persona_moral,
                    fecha_de_nacimiento,ocupacion,edo_civil,
                    nota,no_contactar, gid, uid, titulo, sector, pais,
                    ciudad, primer_contacto, origen_id,codigo_campana,fecha_importado
                ) VALUES ('$nombre','$ap_pat','$ap_mat',
                          '$sexo','$compania','$cargo','$tel_casa','$tel_ofi',
                          '$tel_mov','$tel_otro','$email','$domicilio','$colonia',
                          '$cp','$delegacion','$entidad_id','$rfc','$moral',
                          '$fecha_nac','$ocupacion','$edo_civil','$nota','$no_contactar',
                          '$gid','$vendedor_uid','$titulo','$sector','$pais',
                          '$ciudad','$primer_cont','$origen','$codigo','$date');";

            $array_crm_contactos_logs[]="'".$uid."','".$vendedor_uid."'";
            $array_crm_contactos_unidades[]="'".$modelo."','".$version."','".$ano."','".$tipo_pint."','".$color_ext."','".$color_int."','".$modelo_id."'";
            $array_vendedor_uid[]=$vendedor_uid;
    }
    fclose($fh);

    if($no_errores == 0)
    {

        for($i=0; $i <count($array_crm_contactos); $i++)
        {
            $vendedor_uid=( $array_vendedor_uid[$i] + 0);
            $sql=$array_crm_contactos[$i];
            $res = $db->sql_query($sql) or die("Error 1 <br>$sql<br>");
            $contacto_id = mysql_insert_id();

            $sql_log="INSERT INTO crm_contactos_asignacion_log (contacto_id,uid,to_uid)
                VALUES('".$contacto_id."',".$array_crm_contactos_logs[$i].");";
            $res_log = $db->sql_query($sql_log) or die("Error 2 <br>$sql_log<br>");
            $sql_uni="INSERT INTO crm_prospectos_unidades (
                    contacto_id,
                    modelo, version, ano,
                    tipo_pintura, color_exterior, color_interior,modelo_id
                ) VALUES ('".$contacto_id."',".$array_crm_contactos_unidades[$i].");";
            $res_uni = $db->sql_query($sql_uni) or die("Error 3 <br>$sql_uni<br>");

            $sql_cam = "SELECT c.campana_id FROM crm_campanas_groups AS g, crm_campanas AS c WHERE c.campana_id=g.campana_id AND g.gid='$gid' ORDER BY c.campana_id  LIMIT 1"; //la primera que sea parte de un ciclo
            $result = $db->sql_query($sql_cam) or die("Error 4 <br>$sql_cam<br>");
            list($campana_id) = $db->sql_fetchrow($result);

            $sql_ins = "INSERT INTO crm_campanas_llamadas (campana_id,contacto_id,user_id,status_id,fecha_cita)VALUES('$campana_id','$contacto_id','$vendedor_uid','-2','0000-00-00 00:00:00')";
            $db->sql_query($sql_ins) or die("Error 5<br>$sql_ins<br>");
            $insertados++;
        }
    }
    $file_tmp = fopen("./files/".$system_name."-".date("Y-m-d").".csv" , "w" );
    if(!$file_tmp){
        die("Error al crear el archivo de salida.");
    }
    fputcsv($file_tmp, split(',', "Nombre,Apellido Paterno,Apellido Materno,
                          Titulo,Sexo,Compañía,Sector,Cargo,Telefono Casa,Telefono Oficina,
                          Telefono Movil,Telefono Otro,E-mail,Domicilio,Colonia,Delegacion,
                          Codigo Postal,Estado,Ciudad,Pais,RFC,Persona Moral,Fecha de Nacimiento,
                          Ocupacion,Estado Civil,Notas,No contactar,Primer Contacto,Origen,
                          Modelo,Version,Año,Tipo de Pintura,Color Exterior,Color Interior,Vendedor,Codigo Campana"));
    foreach ($nuevo_csv as $lineas_csv)
    {
        fputcsv($file_tmp, $lineas_csv);
    }
    fclose($file_tmp);

    $mensaje_contactos = "$procesados registros procesados. $insertados agregados. $no_errores errores.";
    if($rechazados)
    {
        foreach ($rechazados as $linea)
        {
            $motivo = $rechazados_motivo[$linea];
            //$msg .= "$linea,$motivo\n\r";

            $contenido_errores .= "<tr class=\"row".(++$row_class%2+1)."\">
    <td>$linea</td>
    <td>$motivo</td>
    </tr>";
        }

        $tabla_errores .= "<table style=\"width:100%;\">
    <thead><tr>
    <td><font color=\"white\">Linea</font></td>
    <td><font color=\"white\">Error</font></td>
    </tr></thead>
        $contenido_errores
    </table>
    ";//
    }
    else{
        $tabla_errores = "Ninguno";
    }
//    if($no_errores == 0)
//    {
        if($alertas_motivo)
        {
            foreach ($alertas_motivo as $linea)
            {
                list($lineas,$motivo) = explode(":",$linea);
                $contenido_alertas .= "<tr class=\"row".(++$row_class%2+1)."\">
                <td>$lineas</td>
                <td>$motivo</td>
                </tr>";
            }
            $tabla_alertas .= "<table style=\"width:100%;\">
                               <thead><tr>
                                    <td><font color=\"white\">Linea</font></td>
                                    <td><font color=\"white\">Aviso</font></td>
                                </tr></thead>
                                $contenido_alertas
                                </table>";
        }
        else
        {
            $tabla_alertas = "Ninguno";
        }
  //  }

    $tabla_contenido_contactos = "<table style=\"text-align: left; width: 100%;\" border=\"0\" cellpadding=\"2\" cellspacing=\"2\">
  <tbody>
    <tr>
      <td colspan=\"2\" rowspan=\"1\">
      <h1>Carga de contactos</h1>
      </td>
    </tr>
    <tr>
      <td colspan=\"2\"><strong>$mensaje_contactos</strong></td>
    </tr>
    <tr class=\"\" >
      <td><strong>Errores</strong></td>
    </tr>
    <tr class=\"\" >
      <td style=\"width:15px;\"></td>
      <td style=\"width:100%;\">
    $tabla_errores
      </td>
    </tr>
    <tr class=\"\" >
      <td><strong>Avisos</strong></td>
    </tr>
    <tr class=\"\" >
      <td style=\"width:15px;\"></td>
      <td style=\"width:100%;\">
    $tabla_alertas
      </td>
    </tr>
  </tbody>
  </table>";

    //header("Refresh: 5; Content-Disposition: attachment; filename=../tmp_".date("Y-m-d").".csv");
    if($contenido_errores != "")
    header('Refresh: 1; url=index.php?_module=Gerente&_op=csv_errores');
}
function Revisa_modelo($db,$modelo)
{
    $id=0;
    $sql="SELECT unidad_id FROM crm_unidades where upper(nombre)='".$modelo."';";
    $res=$db->sql_query($sql) or die("Error en el query:  ".$sql);
    if($db->sql_numrows($res)>0)
    $id=$db->sql_fetchfield(0,0,$res);
    return $id;
}
function Genera_Unidades($db)
{
    $sql = "SELECT unidad_id, nombre FROM crm_unidades";
    $r = $db->sql_query($sql) or die($sql);
    $unidades = array();
    while (list($id, $n) = $db->sql_fetchrow($r))
    $unidades[$id] = $n;
    return $unidades;
}
function Genera_Entidades($db)
{
    $sql = "SELECT id_entidad, nombre FROM crm_entidades ORDER BY id_entidad";
    $r = $db->sql_query($sql) or die($sql);
    $entidades = array();
    while (list($id, $n) = $db->sql_fetchrow($r))
        $entidades[$id] = $n;
    return $entidades;
}
function Revisa_Entidad($db,$estado)
{
    $id=0;
    $sql="SELECT id_entidad FROM crm_entidades where upper(nombre)='".$estado."';";
    $res=$db->sql_query($sql) or die("Error en el query:  ".$sql);
    if($db->sql_numrows($res)>0)
        $id=$db->sql_fetchfield(0,0,$res);
    return $id;
}
?>