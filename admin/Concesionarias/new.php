<?
if (!defined('_IN_ADMIN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}
global $db,$_licenses,$_includesdir,$group,$name,$email,$_module,$_op,$submit,/*$_licenses_used,$_licenses_not_used,*/$msg_ciclo;
global $region_id,$zona_id,$entidad_id,$plaza_id,$id_nivel,$_site_title;
$_site_title = "Crear nueva distribuidora";
$listRegiones=Regiones($db,$region_id);
$listZonas   =Zonas($db, $region_id,$zona_id);
$listEntidades=Entidades($db,$region_id,$entidad_id);
$listPlazas=Municipios($db,$entidad_id,$plaza_id);
$categoria=Categorias($id_nivel);
if ($submit)
{
    /*if($_licenses_not_used > 0)
    {*/
        $n =$db->sql_numrows($db->sql_query("SELECT name FROM groups WHERE name='".$group."';"));
        if ($n != 0)
            $error = "<b>No se pudo crear la distribuidora por que ya existe otra con el nombre \"$new\"</b><br>\n";
        else
        {
            $modules = array("Bienvenida", "Gerente", "Noticias", "Directorio", "Campanas","Estadisticas");
            // Se añade a groups
            $db->sql_query("INSERT INTO groups (gid, name,active)VALUES('','$group','1')") or die("No se pudo crear");
            $gid_sig=$db->sql_nextid();
            $db->sql_query("INSERT INTO groups_ubications (gid, name,nivel_id,nombre_nivel)VALUES('$gid_sig','$group','1','Básico')") or die("No se pudo crear la concesionaria");
            $db->sql_query("INSERT INTO crm_niveles_concesionarias (gid, nombre,nivel_id)VALUES('$gid_sig','Básico','1')") or die("No se pudo crear la concesionaria");
    
            // Creamos los modulos para la concesionaria
            $gid=$gid_sig;
            foreach($modules AS $module)
            {
                $sql = "SELECT gid FROM groups_accesses WHERE gid='$gid' AND module='$module' LIMIT 1";
                $result2 = $db->sql_query($sql) or die("Error<br>$sql<br>".print_r($db->sql_error()));
                if ($db->sql_numrows($result2) < 1 )
                    $db->sql_query("INSERT INTO groups_accesses (gid,module)VALUES('$gid','$module')") or die("Error<br>".print_r($db->sql_error()));
            }
    
            // Creamos sus ciclo de venta de la concesionaria
            $sql="SELECT campana_id,etapa_ciclo_id,nombre,next_campana_id FROM crm_campanas WHERE campana_id<=10 order by etapa_ciclo_id;";
            $res=$db->sql_query($sql);
            $contador=0;
            $num = $db->sql_numrows($res);
            if($num > 0)
            {
                while(list($campana_id,$etapa_ciclo,$nombre,$next_campana_id) = $db->sql_fetchrow($res))
                {
                    $contador++;
                    $campana_id=str_pad($gid,4,'0',STR_PAD_LEFT).str_pad($etapa_ciclo,2,'0',STR_PAD_LEFT);
                    $nombre=str_pad($gid,4,'0',STR_PAD_LEFT).'-'.$etapa_ciclo.' '.substr($nombre,6,strlen($nombre));
                    $next_campana_id=str_pad($gid,4,'0',STR_PAD_LEFT).str_pad($next_campana_id,2,'0',STR_PAD_LEFT);
                    if($contador == $num) $next_campana_id=0;
                    $ins="INSERT INTO crm_campanas (campana_id,etapa_ciclo_id,nombre,next_campana_id)
                          VALUES ('$campana_id','$etapa_ciclo','$nombre','$next_campana_id');";
                    if($db->sql_query($ins) or die ("Error en el insert del ciclo de venta ".$ins))
                    {
                        $ins2="INSERT INTO crm_campanas_groups (campana_id,gid) values ('".$campana_id."','".str_pad($gid,4,'0',STR_PAD_LEFT)."');";
                        $db->sql_query($ins2);
                    }
                }
            }
            // Creamos el usuario de gerente de vtas
            $user = str_pad($gid,4,'0',STR_PAD_LEFT)."GTEVTAS";
            $sql = "INSERT INTO users (gid,super,user,password,name,email) VALUES ('$gid','6','$user',PASSWORD('$user'),'GERENTE CRM DE LA DISTRIBUIDORA $gid','$email')";
            $db->sql_query($sql) or die("Error<br>$sql<br>".print_r($db->sql_error()));
    
            $region_id = $region_id+0;
            $zona_id   = $zona_id+0;
            $entidad_id = $entidad_id +0;
            $plaza_id = $plaza_id + 0;
            $id_nivel= $id_nivel + 0;
            $tit='Básico';
            /*if($id_nivel < 2) $tit='Básico';
            if($id_nivel == 2) $tit='Medio';
            if($id_nivel == 3) $tit='Avanzado';*/
            $updateGroupsUbications="UPDATE groups_ubications SET region_id=".$region_id.",zona_id=".$zona_id.", entidad_id=".$entidad_id.", plaza_id=".$plaza_id.",nivel_id=".$id_nivel.",nombre_nivel='".$tit."' WHERE gid=".$gid.";";
            $db->sql_query($updateGroupsUbications) or die("Error al actualizar la tabla de ubicaciones de concesionarias".$updateGroupsUbications);
            
$msg =<<<EOBODY
    <html>
        <head>
            <title>Claves de acceso para el gerente</title>
        </head>
        <body>
            <img style="margin-left:5px;" src="http://www.pcsmexico.com/salesfunnel/activation/images/sf_small.png" /><br/><br/>
            </br>
            <b>Se ha dado de alta la Distribuidora {$group}, las claves de acceso para entrar a su sesi&oacute;n de Gerente son las siguientes:</b>
            <p>Usuario: <b>{$user}</b> </p>
            <p>Contrase&ntilde;a: <b>{$user}</b> </p>
            <p>Al ingresar por primera con su nueva cuenta, se le solicitar&aacute; que actualize sus datos.</p>
        </body>
        </html>
EOBODY;
        
            $eol="\n";
            $headers = 'From: salesfunnel@pcsmexico.com' . $eol;
            $headers .= 'Reply-To: <salesfunnel@pcsmexico.com>' . $eol;
            $headers .= 'Return-Path: <salesfunnel@pcsmexico.com>' . $eol;
            $headers .= 'Content-Type: text/html; charset=iso-8859-1';
            
            mail( $email, "Alta de distribuidora", $msg, $headers);
            header("location: index.php?_module=Concesionarias");    
        }
    //}
    /*else
    {
        $_html= "<script language='JavaScript'>
                    alert('No se puede crear la distribuidora, se tiene".$_licenses." licencias compradas, por favor comunicate con el personal de ventas de PCS Mexico');
                        location.href='index.php?_module=Concesionarias';
                </script>";
    }*/
}

function Regiones($db,$valor_id)
{
    $combo='';
    $sql="SELECT region_id,nombre FROM crm_regiones ORDER BY region_id;";
    $res=$db->sql_query($sql) or die("Error en la consulta:  ".$sql);
    if($db->sql_numrows($res) > 0)
    {        
        $combo.="<select name='region_id' id='region_id' style='width:200px;'><option value=''></option>";
        while(list($id,$name) = $db->sql_fetchrow($res))
        {
            $tmp='';
            if($id == $valor_id)
                $tmp=' SELECTED ';
            $combo.= "<option value='".$id."' ".$tmp.">".$name."</option>";
        }
        $combo.="</select>";
    }
    return $combo;
}

function Zonas($db,$region_id,$valor_id)
{
    $combo="<select name='zona_id' id='zona_id' style='width:200px;'>";
    if($valor_id > 0)
    {
        $sql="SELECT zona_id,nombre,region_id FROM crm_zonas WHERE region_id=".$region_id." ORDER BY zona_id;";
        $res=$db->sql_query($sql) or die("Error en la consulta:  ".$sql);
        if($db->sql_numrows($res) > 0)
        {
            $combo.="<select name='zona_id' id='zona_id' style='width:150px;'><option value=''></option>";
            while(list($id,$name) = $db->sql_fetchrow($res))
            {
                $tmp='';
                if($id == $valor_id)
                    $tmp=' SELECTED ';
                $combo.= "<option value='".$id."' ".$tmp.">".$name."</option>";
            }
            $combo.="</select>";
        }
    }
    return $combo;
}

function Entidades($db,$region_id,$valor_id)
{
    $combo="<select name='entidad_id' id='entidad_id' style='width:200px;'>";
    if($valor_id > 0)
    {
        $sql="SELECT id_entidad,nombre,id_region FROM crm_entidades WHERE id_region=".$region_id." ORDER BY id_entidad;";
        $res=$db->sql_query($sql) or die("Error en la consulta:  ".$sql);
        if($db->sql_numrows($res) > 0)
        {
            $combo.="<select name='entidad_id' id='entidad_id' style='width:150px;'><option value=''></option>";
            while(list($id,$name) = $db->sql_fetchrow($res))
            {
                $tmp='';
                if($id == $valor_id)
                    $tmp=' SELECTED ';
                $combo.= "<option value='".$id."' ".$tmp.">".$name."</option>";
            }
            $combo.="</select>";
        }
    }
    return $combo;
}

function Municipios($db,$entidad_id,$valor_id)
{
    $combo="<select name='plaza_id' id='plaza_id' style='width:200px;'>";
    if($valor_id > 0)
    {
        $sql="SELECT plaza_id,nombre FROM crm_plazas WHERE entidad_id = ".$entidad_id ." ORDER BY plaza_id;";
        $res=$db->sql_query($sql) or die("Error en la consulta:  ".$sql);
        if($db->sql_numrows($res) > 0)
        {
            $combo.="<select name='plaza_id' id='plaza_id' style='width:150px;'><option value=''></option>";
            while(list($id,$name) = $db->sql_fetchrow($res))
            {
                $tmp='';
                if($id == $valor_id)
                    $tmp=' SELECTED ';
                $combo.= "<option value='".$id."' ".$tmp.">".$name."</option>";
            }
            $combo.="</select>";
        }
    }
    return $combo;
}

function Categorias($id_nivel)
{
    $id_nivel=$id_nivel + 0;
    $tmp_basico="";
    $tmp_medio="";
    $tmp_avanzado="";

    if($id_nivel<2)
        $tmp_basico=" selected ";
    if($id_nivel==2)
        $tmp_medio=" selected ";
    if($id_nivel==3)
        $tmp_avanzado=" selected ";

    $buffer='<select name="id_nivel" id="id_nivel" style="width:250px;">
            <option value="1" '.$tmp_basico.'>B&aacute;sico</option>
            <option value="2" '.$tmp_medio.'>Medio</option>
            <option value="3" '.$tmp_avanzado.'>Avanzado</option>
            </select>';
    return $buffer;
}
?>