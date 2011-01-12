<?
    if (!defined('_IN_ADMIN_MAIN_INDEX'))
    {
        die ("No puedes acceder directamente a este archivo...");
    }
    global $db, $submit, $del;
    $errores='';
    $no_errores=0;
    $counter=0;
    $inserted=0;
    $updated=0;
    $groups_accesses=0;
    $campanas_creadas=0;
    $almacenado=false;    
    if ($submit)
    {
        $filename = $_FILES['f']['tmp_name'];
        $file= $_FILES['f']['name'];
        if(empty($filename))
        {
            $msg="<font color='red' size='2'>Favor de seleccionar un archivo</font><br>";
        }
        else
        {
            
        	if(substr($file,(strlen($file)-4),strlen($file)) != ".csv")
        	{
        	   $msg="<font color='red' size='2'>El archivo debe estar en formato CSV y de limitados por comas(,).</font><br>";
        	}
        	else 
        	{
                $fh = fopen($filename, "r");
                if (!$fh)
                { 
                    $msg="<font color='red' size='2'>Error, no se puede leer el archivo (tal vez sea demasiado grande o no este delimitado por comas (,)) ".$filename."</font><br>";
                }
                else
                {
                    include("$_includesdir/select.php");
                    $modules = array("Bienvenida", "Gerente", "Noticias", "Directorio", "Campanas","Estadisticas");
                    while($data = fgetcsv($fh, 1000, ","))
                    {
                        if (!($ii++)) 
                            continue; //se salta el primer campo
                        $data2 = array();
                        foreach ($data as $undato)
                        {
                            $data2[] = addslashes($undato);
                        }
                        list($gid,$nombre_grupo,$direccion,$colonia,$ciudad,$estado) = $data2;
                        $estado=strtr($estado, "_", " ");
                        $entidad_federativa_id = array_search(strtoupper($estado),$_entidades_federativas);
                        $entidad_federativa_id++;
                        if (!$gid) 
                            continue;
                        $counter++;
                        // zero fill a gid
                        $gid = sprintf("%04u", $gid);
                        // checamos que no existan usuarios, en la tabla de users antes de cargar las concesionarias
                        $res=$db->sql_query("select uid FROM users WHERE gid=".$gid.";");
                        if($db->sql_numrows($res) == 0)
                        {
                            // No existe usuario con ese uid
                            $sql = "SELECT gid FROM groups WHERE gid='$gid' LIMIT 1";
                            $result2 = $db->sql_query($sql) or die("Error");
                            if ($db->sql_numrows($result2) > 0)
                            {
                                $sql = "UPDATE groups SET name='".strtoupper($nombre_grupo)."',active=true WHERE gid='$gid'";
                                $sql_u="UPDATE groups_ubications SET name='".strtoupper($nombre_grupo)."' WHERE gid='$gid'";
                                $updated++;
                                $almacenado=true;
                            }
                            else
                            {
                                $sql = "INSERT INTO groups (gid,name,active) VALUES ('$gid','".strtoupper($nombre_grupo)."','1')";
                                $sql_u="INSERT INTO groups_ubications (gid,name,nivel_id,nombre_nivel) VALUES ('$gid','".strtoupper($nombre_grupo)."','1','Básico')";
                                $inserted++;
                                $almacenado=true;
                            }
                            $db->sql_query($sql) or die("Error<br>$sql<br>".print_r($db->sql_error()));
                            $db->sql_query($sql_u) or die("Error<br>$sql_u<br>".print_r($db->sql_error()));
                            

                            // REviso en tabla de niveles
                            $sql= "SELECT gid FROM crm_niveles_concesionarias WHERE gid='$gid' LIMIT 1;";
                            $result2 = $db->sql_query($sql) or die("Error");
                            if ($db->sql_numrows($result2) == 0)
                            {
                                $sql = "INSERT INTO crm_niveles_concesionarias (gid,nombre,nivel_id) VALUES
                                ('$gid','Básico','1')";
                                $resul3=$db->sql_query($sql) or die("Error");
                            }
                            $sql = "SELECT gid FROM groups_info WHERE gid='$gid' LIMIT 1";
                            $result2 = $db->sql_query($sql) or die("Error");
                            if ($db->sql_numrows($result2) > 0)
                            {
                                $sql = "UPDATE groups_info SET direccion='$direccion',colonia='$colonia',ciudad='$ciudad',entidad_federativa_id='$entidad_federativa_id' WHERE gid='$gid'";
                                $updated2++;
                            }
                            else
                            {
                                $sql = "INSERT INTO groups_info (gid,direccion,colonia,ciudad,entidad_federativa_id) VALUES
                                ('$gid','$direccion','$colonia','$ciudad','$entidad_federativa_id')";
                                $inserted2++;
                            }
                            $db->sql_query($sql) or die("Error<br>$sql<br>".print_r($db->sql_error()));
                            $counter2++;

                            //ahora darles permisos para entrar al sistema
                            foreach($modules AS $module)
                            {
                                $sql = "SELECT gid FROM groups_accesses WHERE gid='$gid' AND module='$module' LIMIT 1";
                                $result2 = $db->sql_query($sql) or die("Error<br>$sql<br>".print_r($db->sql_error()));
                                if ($db->sql_numrows($result2) < 1)
                                    $db->sql_query("INSERT INTO groups_accesses (gid,module)VALUES('$gid','$module')") or die("Error<br>".print_r($db->sql_error()));
                            }
                            $groups_accesses++;

                            // doy de alta los usuarios
                            $user = $gid."GTECRM";
                            $email = $gid."rcvwy@vw-concesionarios.com.mx";
                            $sql = "INSERT INTO users (gid,super,user,password,name,email) VALUES ('$gid','4','$user',PASSWORD('$user'),'GERENTE CRM DE LA CONCESIONARIA $gid','$email')";
                            $db->sql_query($sql) or die("Error<br>$sql<br>".print_r($db->sql_error()));


                            //crear un usuario al gerente de ventas (puede que nunca lo use)
                            $user = $gid."GTEVTAS";
                            $email = $gid."rcvwv@vw-concesionarios.com.mx";
                            $sql = "INSERT INTO users (gid,super,user,password,name,email)VALUES('$gid','6','$user',PASSWORD('$user'),'GERENTE DE VENTAS DE LA CONCESIONARIA $gid','$email')";
                            $db->sql_query($sql) or die("Error<br>$sql<br>".print_r($db->sql_error()));

                            //crear un usuario de call center
                            $user = $gid."CALLCENTER";
                            $email = "";
                            $sql = "INSERT INTO users (gid,super,user,password,name,email)VALUES('$gid','10','$user',PASSWORD('$user'),'CALLCENTER DE LA CONCESIONARIA $gid','$email')";
                            $db->sql_query($sql) or die("Error<br>$sql<br>".print_r($db->sql_error()));

                            //crear un usuario de hostess
                            $user = $gid."HOSTESS";
                            $email = "";
                            $sql = "INSERT INTO users (gid,super,user,password,name,email)VALUES('$gid','12','$user',PASSWORD('$user'),'HOSTESS DE LA CONCESIONARIA $gid','$email')";
                            $db->sql_query($sql) or die("Error<br>$sql<br>".print_r($db->sql_error()));

                            //Crearles sus campa??as iniciales
                            // las campanas que son menores de 100 las consideraremos las que se van a replicar, despu?“s usaremos un identificador mas complejo
                            $sql = "SELECT campana_id, nombre, saludo, etapa_ciclo_id "
                                ." FROM crm_campanas WHERE campana_id < 100 AND campana_id > 0";
                            $result2 = $db->sql_query($sql) or die("Error<br>$sql<br>".print_r($db->sql_error()));
                            $cuantas_campanas = $db->sql_numrows($result2);
                            $num_campana = 0;
                            while (list($campana_id_orig, $nombre, $saludo, $etapa_ciclo_id) = $db->sql_fetchrow($result2))
                            {
                                $num_campana++;
                                //compondremos el nuevo id en base al gid y 2 d?????“gitos
                                $campana_id = $gid . ($campana_id_orig < 10?"0":"") . $campana_id_orig; //0 precede si es un solo d?????“gito
                                $sql = "SELECT campana_id FROM crm_campanas WHERE campana_id='$campana_id'";
                                $result3 = $db->sql_query($sql) or die("Error<br>$sql<br>".print_r($db->sql_error()));
                                if ($db->sql_numrows($result3) < 1)
                                {
                                    if ($num_campana < $cuantas_campanas)
                                        $next_campana_id = $campana_id+1; //solo si no es la ultima
                                    else
                                        $next_campana_id = '0';
                                    $sql = "INSERT INTO crm_campanas (campana_id, nombre, saludo, next_campana_id, etapa_ciclo_id)VALUES('$campana_id','$gid-$nombre','$saludo','$next_campana_id', '$etapa_ciclo_id')";
                                    $db->sql_query($sql) or die("Error<br>$sql<br>".print_r($db->sql_error()));
                                    $campanas_creadas++;

                                    //asignarles que el grupo nuevo es responsable de esta campana
                                    $sql = "SELECT gid FROM crm_campanas_groups WHERE campana_id='$campana_id'";
                                    $result4 = $db->sql_query($sql) or die("Error<br>$sql<br>".print_r($db->sql_error()));
                                    if ($db->sql_numrows($result4) < 1)
                                    {
                                        $sql = "INSERT INTO crm_campanas_groups (campana_id, gid)VALUES('$campana_id','$gid')";
                                        $db->sql_query($sql) or die("Error<br>$sql<br>".print_r($db->sql_error()));
                                    }
                                    //copiar las objeciones de la campana, se necesita una funcion recursiva para copiar con padres_id
                                }
                            }
                        }
                        else
                        {
                            $errores.=" Existen usuarios asignados a la concesionaria: ".$gid." favor de eliminarlos";
                            $no_errores++;
                        }
                    }
                }
                $msg ="<table width='60%' align='center' style='border:1px solid #cdcdcd;'><tr bgcolor='#CDCDCD'><td>Resultados de la carga de concesionarias</td></tr>
                        <tr><td>".$counter."  registros procesados.</td></tr>
                        <tr><td>".$inserted." concesionarias Creadas.</td></tr>
                        <tr><td>".$updated."  concesionarias actualizados.</td></tr>
                        <tr><td>Se configuro los niveles de acceso para $groups_accesses grupos.</td></tr>
                        <tr><td>".$campanas_creadas." campa&ntilde;as creadas.</td></tr>
                        <tr><td>".$no_errores." concesionarias con Errores.</td></tr>";
                if($no_errores > 0)
                    $msg .="<tr><td><font color='red'>".$errores."</font></td></tr>";
                $msg .="</table>";
            }
        }
    }
 ?>