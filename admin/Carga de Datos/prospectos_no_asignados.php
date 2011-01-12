<?
  if (!defined('_IN_ADMIN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}
function horario_preferido($horario){
    $return = array();
    $return['manana'] = eregi('M',$horario) ? true : false;
    $return['tarde'] = eregi('T',$horario) ? true : false;
    $return['noche'] = eregi('N',$horario) ? true : false;
    return serialize($return);
}

function genera_telefono($lada,$telefono){
    $nuevo_telefono = '';
    if($lada != '') $nuevo_telefono .= "({$lada}) ";
    $nuevo_telefono .= $telefono;
    return $nuevo_telefono;
}


require_once("$_includesdir/mail.php");

global $db, $submit, $del;
if ($submit)
{
    include("$_includesdir/select.php");
    global $_entidades_federativas;
    global $db;
    $filename = $_FILES['f']['tmp_name'];
    if($_FILES['f']['type']!='text/plain'){
		$error++;
		$msge="Tipo de archivo inv?lido ";
	}
	if(!$filename){
		$error++;
		$msge="No hay archivo, favor de cargarlo ";
	}
    if($error==0)
    {
		$msge="";
        $fh = fopen($filename, "r");
        if (!$fh)
        {
            die("<br><h1>El archivo no se puede cargar. Tal vez es demasiado grande.</h1>".$filename);
            return;
        }
        $prioridades = array();
        $sql = "SELECT prioridad_id, valor FROM crm_prioridades_contactos";
        $r = $db->sql_query($sql) or die($sql);
        while(list($prioridad_id, $prioridad_val) = $db->sql_fetchrow($r))
        {
            $prioridades[$prioridad_val] = $prioridad_id;
        }
  
        //obtener la lista de vehí­culos
        $sql = "SELECT unidad_id, nombre FROM crm_unidades";
        $r = $db->sql_query($sql) or die($sql);
        $unidades = array();
        while (list($id, $n) = $db->sql_fetchrow($r))
            $unidades[$id] = $n;

        //obtener la lista de concesionarias
        $sql = "SELECT gid, name FROM groups";
        $r = $db->sql_query($sql) or die($sql);
        $groups = array();
        while (list($id, $n) = $db->sql_fetchrow($r))
            $groups[$id] = $n;

        //obtener la lista de satelites y el gid que les corresponde
        $sql = "SELECT gid, name FROM groups_satelites";
        $r = $db->sql_query($sql) or die($sql);
        $satelites = array();
        while (list($id, $n) = $db->sql_fetchrow($r))
        {
            if (!is_array($satelites[$id]))
                $satelites[$id] = array();
            if (!in_array($n, $satelites[$id])) //checar que no está ya
                $satelites[$id][] = $n;//metemos en el array dentro de este id el nombre
        }
  
        //los gids que se encuentran
        $gid_founds = array();
        //iniciar la lista para checar telefonos repetidos
        $telefonos = array();
        $alerta = $motivo_de_rechazo_1 = $motivo_de_rechazo_2 = $motivo_de_rechazo_3 = $motivo_de_rechazo_4 = $motivo_de_rechazo_5 =$motivo_de_rechazo_6 = 0;
        //   $_edo_civil = array( 0=>"Otro", 1=>"Soltero", 2=>"Casado", 3=>"Divorciado", 4=>"Union Libre", 5=>"Viudo" );
        $linea = 0;
        $total_de_registros_esperados = 0;
        $procesados = 0;
        $insertados = 0;
        $origen_momento = $origen_portal = $origen_news = $origen_GTIA6 = 0;
        $rechazados_concesionaria = array();
        $concesionarias = array();
        $alertas_motivo = array();
        $alertas = array();
        while(1)
        {
            $linea++;
            //las primeras 4 lineas son de control, la 1, 3 y 4 están vacias
            if ($linea == 1 || $linea == 3 || $linea == 4)
            {
                $data = fgets($fh, 1000);//quitar la linea
                continue;
            }
            elseif ($linea == 2) //la tercer linea indica el número de registros
            {
                $data = fgets($fh, 1000);
                if (!$data)
                    break;
                list($texto, $total_de_registros_esperados) = explode(": ", $data);
                //quitarle el último caracter (salto de linea)
                $total_de_registros_esperados = intval($total_de_registros_esperados);
                continue;
            }
            elseif ($linea > 4)
            {
                //leer normalmente
                $data = fgetcsv($fh, 1000, "|");
                if (!$data)
                    break;
                $procesados++;
                $data2 = array();
                foreach ($data as $undato)
                {
                    $data2[] = addslashes($undato);
                }
                list($nombre, $apellidos,$email,$lada, $telefono,$modelo,$medio_contacto,$estado,$ciudad,
                     $concesionaria,$origen,$prioridad,$fecha_de_registro,$horario_preferido_casa,$lada_casa_2,
                     $telefono_casa_2,$horario_preferido_casa_2,$lada_oficina,$telefono_oficina,$horario_preferido_oficina,
                     $lada_oficina_2,$telefono_oficina_2,$horario_preferido_oficina_2,$lada_movil,$telefono_movil,
                     $horario_preferido_movil,$lada_movil_2,$telefono_movil_2,$horario_preferido_movil_2)= $data2;
                //concesionaria nunca la checamos con sql, así­ que le podemos quitar las slashes
                $concesionaria = stripslashes($concesionaria);
                $concesionaria = htmlentities($concesionaria, ENT_QUOTES, "ISO-8859-1");
                list($fecha,$hora,$ampm) = explode(" ",$fecha_de_registro);
                list($mes,$dia,$ano) = explode("/",$fecha);
                list($hora_1, $minuto, $segundo) = explode(":", $hora);
                if($ampm == "p.m." && $hora_1 < 12)
                    $hora_1 = $hora_1+12;
                $hora = sprintf("%s:%s:%s",$hora_1,$minuto, $segundo);
                $fecha_de_registro = sprintf("%s-%s-%s %s",$ano,$mes,$dia,$hora);

                //rechazar si no tienen teléfono no email (puede tener nada mas uno)
                if (!$telefono && !$email)
                {
                    $rechazados[] = $linea;//linea en la que lo botamos
                    $rechazados_motivo[$linea] = "TELEFONO VACIO";
                    $motivo_de_rechazo_1++;
                    continue;
                }
                if ($telefono) //si el teléfono no está vacio, correr filtros
                {
                    //rechazar si está repetido el teléfono
                    if (in_array($telefono, $telefonos) )
                    {
                        //buscamos el teléfono y vemos que modelo tiene dado de alta
                        //si es el mismo modelo que tenemos en la db no agregarlo
                        $sql = "SELECT contacto_id FROM crm_contactos WHERE tel_casa='$telefono'";//solo checo tel_casa por que es el único que agrego en este script
                        $r = $db->sql_query($sql) or die($sql);
                        list($c_id) = $db->sql_fetchrow($r);
                        $sql = "SELECT contacto_id FROM crm_contactos_no_asignados WHERE tel_casa='$telefono'";//solo checo tel_casa por que es el único que agrego en este script
                        $r = $db->sql_query($sql) or die($sql);
                        list($c_id_na) = $db->sql_fetchrow($r);
                        if ($c_id || $c_id_na)
                        {
                            $sql = "SELECT modelo FROM crm_prospectos_unidades WHERE contacto_id='$c_id'";//solo checo tel_casa por que es el único que agrego en este script
                            $r = $db->sql_query($sql) or die($sql);
                            list($modelo_) = $db->sql_fetchrow($r);
                            $sql = "SELECT modelo FROM crm_prospectos_unidades_no_asignados WHERE contacto_id='$c_id_na'";//solo checo tel_casa por que es el único que agrego en este script
                            $r = $db->sql_query($sql) or die($sql);
                            list($modelo_na) = $db->sql_fetchrow($r);
                            if ($modelo_ == $modelo) //si es diferente el modelo asignarlo
                            {
                                $rechazados[] = $linea;//linea en la que lo botamos
                                $rechazados_motivo[$linea] = "CONTACTO REPETIDO EN LOS PROSPECTOS QUE YA TIENEN ASIGNACION (TELEFONO EN ESTA CARGA: $telefono)";
                                $motivo_de_rechazo_2++;
                                continue;
                            }
                            elseif ($modelo == $modelo_na) //si es diferente el modelo asignarlo
                            {
                                $rechazados[] = $linea;//linea en la que lo botamos
                                $rechazados_motivo[$linea] = "CONTACTO REPETIDO EN LOS PROSPECTOS NO ASIGNADOS (TELEFONO EN ESTA CARGA: $telefono)";
                                $motivo_de_rechazo_2++;
                                continue;
                            }
                        }
                    }
                    $telefonos[] = $telefono;//guardar para comparar
                    //rechazar si el teléfono está repetido, para eso checamos en la db ahora
                    $sql = "SELECT contacto_id FROM crm_contactos WHERE tel_casa='$telefono'";//solo checo tel_casa por que es el único que agrego en este script
                    $r = $db->sql_query($sql) or die($sql);
                    $num_rows_c = $db->sql_numrows($r);

                    //rechazar si el teléfono está repetido, para eso checamos en la db de los no asignados ahora
                    $sql = "SELECT contacto_id FROM crm_contactos_no_asignados WHERE tel_casa='$telefono'";//solo checo tel_casa por que es el único que agrego en este script
                    $r_na = $db->sql_query($sql) or die($sql);
                    $num_rows_c_na = $db->sql_numrows($r_na);
                    if (($num_rows_c_na > 0) || ($num_rows_c > 0)) //encontró algo, rechazar
                    {
                        //buscamos el teléfono y vemos que modelo tiene dado de alta
                        //si es el mismo modelo que tenemos en la db no agregarlo
                        list($c_id) = $db->sql_fetchrow($r);
                        list($c_id_na) = $db->sql_fetchrow($r_na);
                        if ($c_id || $c_id_na)
                        {
                            $sql = "SELECT modelo FROM crm_prospectos_unidades WHERE contacto_id='$contacto_id'";//solo checo tel_casa por que es el único que agrego en este script
                            $r = $db->sql_query($sql) or die($sql);
                            list($modelo_) = $db->sql_fetchrow($r);
                            $sql = "SELECT modelo FROM crm_prospectos_unidades_no_asignados WHERE contacto_id='$contacto_id'";//solo checo tel_casa por que es el único que agrego en este script
                            $r = $db->sql_query($sql) or die($sql);
                            list($modelo_na) = $db->sql_fetchrow($r);
                            if ($modelo_ == $modelo) //si es diferente el modelo asignarlo
                            {
                                $rechazados[] = $linea;//linea en la que lo botamos
                                $rechazados_motivo[$linea] = "CONTACTO REPETIDO (TELEFONO EN LA BD DE LOS CONTACTOS ASIGNADOS: $telefono)";
                                $motivo_de_rechazo_2++;
                                continue;
                            }
                            elseif ($modelo_na == $modelo) //si es diferente el modelo asignarlo
                            {
                                $rechazados[] = $linea;//linea en la que lo botamos
                                $rechazados_motivo[$linea] = "CONTACTO REPETIDO (TELEFONO EN LA BD DE LOS CONTACTOS NO ASIGNADOS: $telefono)";
                                $motivo_de_rechazo_2++;
                                continue;
                            }
                        }
                    }
                    //rechazar si el teléfono está repetido, para eso checamos en la db ahora
                    $sql = "SELECT contacto_id FROM crm_contactos_finalizados WHERE tel_casa='$telefono'";//solo checo tel_casa por que es el único que agrego en este script
                    $r = $db->sql_query($sql) or die($sql);
                    if ($db->sql_numrows($r) > 0) //encontró algo, rechazar
                    {
                        $rechazados[] = $linea;//linea en la que lo botamos
                        $rechazados_motivo[$linea] = "CONTACTO REPETIDO (TELEFONO EN LA BD FINALIZADA: $telefono)";
                        $motivo_de_rechazo_2++;
                        continue;
                    }
                }
                if ($modelo == "" || !in_array($modelo, $unidades)) //checar que el vehí­culo está en la lista
                {
                    $rechazados[] = $linea;//linea en la que lo botamos
                    $rechazados_motivo[$linea] = "MODELO INVALIDO: $modelo";
                    $motivo_de_rechazo_3++;
                    continue;
                }
    
                //más adelante hay más tratamiendo de errores
                $tel_casa = "";
                if ($lada)
                    $tel_casa .= "($lada)";
                $tel_casa .= " $telefono";
                $domicilio = $direccion;
                $persona_moral = 0;
                $poblacion = $ciudad;

            	$nombre = strtoupper($nombre);
                $apellidos = strtoupper($apellidos);
                $espacio = strpos($apellidos, " ");
                if ($espacio)
                {
                    $apellido_paterno = substr($apellidos, 0, $espacio);
                    $apellido_materno = substr($apellidos, $espacio + 1);
                }
                else
                {
                    $apellido_paterno = $apellidos;
                    $apellido_materno ='';
                }
                $entidad_federativa_id = array_search($estado, $_entidades_federativas);
                if ($entidad_federativa_id)
                    $entidad_federativa_id++;//por que el array es 0 aguascalientes

                //rechazar si el nombre está repetido, para eso checamos en la db ahora,
                //es en este momento por que hasta ahorita procesamos el nombre

                if( ($nombre == '') && ($apellidos == '') )
                {
                    $rechazados[] = $linea;//linea en la que lo botamos
        			$rechazados_motivo[$linea] = "EL NOMBRE DEL CONTACTO NO PUEDE SER VACIO";
            		$motivo_de_rechazo_6++;
                	continue;
                }
                else
                {
                    $sql = "SELECT contacto_id FROM crm_contactos WHERE nombre='$nombre' AND apellido_paterno='$apellido_paterno' AND apellido_materno='$apellido_materno'";
                    $r = $db->sql_query($sql) or die($sql);
                    $num_rows_c = $db->sql_numrows($r);

                    $sql = "SELECT contacto_id FROM crm_contactos_no_asignados WHERE nombre='$nombre' AND apellido_paterno='$apellido_paterno' AND apellido_materno='$apellido_materno'";//solo checo tel_casa por que es el único que agrego en este script
                    $r_na = $db->sql_query($sql) or die($sql);
                    $num_rows_c_na = $db->sql_numrows($r_na);
                    if ($num_rows_c > 0) //encontró algo, rechazar
                    {
                        $rechazados[] = $linea;//linea en la que lo botamos
                        $rechazados_motivo[$linea] = "CONTACTO REPETIDO (NOMBRE EN LA BD CRM CONTACTOS: $nombre $apellido_paterno $apellido_materno)";
                        $motivo_de_rechazo_2++;
                        continue;
                    }
                    elseif ($num_rows_c_na > 0) //encontró algo, rechazar
                    {
                        $rechazados[] = $linea;//linea en la que lo botamos
                        $rechazados_motivo[$linea] = "CONTACTO REPETIDO (NOMBRE EN LA BD DE LOS CONTACTOS NO ASIGNADOS: $nombre $apellido_paterno $apellido_materno)";
                        $motivo_de_rechazo_2++;
                        continue;
                    }
                    //tambien checar en la tabla de finalizados
                    //rechazar si el nombre está repetido, para eso checamos en la db ahora,
                    //es en este momento por que hasta ahorita procesamos el nombre
                    $sql = "SELECT contacto_id FROM crm_contactos_finalizados WHERE nombre='$nombre' AND apellido_paterno='$apellido_paterno' AND apellido_materno='$apellido_materno'";//solo checo tel_casa por que es el único que agrego en este script
                    $r = $db->sql_query($sql) or die($sql);
                    if ($db->sql_numrows($r) > 0) //encontró algo, rechazar
                    {
                        $rechazados[] = $linea;//linea en la que lo botamos
                        $rechazados_motivo[$linea] = "CONTACTO REPETIDO (NOMBRE EN LA BD FINALIZADA: $nombre $apellido_paterno $apellido_materno)";
                        $motivo_de_rechazo_2++;
                        continue;
                    }
                }
                //buscar la concesionaria
                $gid = array_search($concesionaria, $groups);
                if ($gid === FALSE)
                { //buscar en las satelites
                    foreach ($satelites AS $id => $aliases) //obtener el gid y el array de nombres
                    {
                		if (array_search($concesionaria, $aliases) !== FALSE)//checar que esté en los aliases el nombre
                		{
                			$gid = $id;
                			break;
                		}
                    }
                }
                if ($gid === FALSE)
                {
                    $rechazados[] = $linea;//linea en la que lo botamos
                    $rechazados_motivo[$linea] = "CONCESIONARIA: ".($concesionaria);
                    $rechazados_concesionaria[] = implode("|",$data);
                    if (!in_array($concesionaria, $concesionarias))
                        $concesionarias[] = "$concesionaria";
                    $motivo_de_rechazo_4++;
                    continue;
                }
                else
                {
                    if (!in_array($gid, $gid_founds))
                        $gid_founds[] = $gid;
                }
                $asignados_por_gid[$gid]++;
	
                if ($origen == "Momento")
                {
                    $origen_id = "-3";
                    $origen_momento++;
                }
                elseif ($origen == "newslvw")
                {
                    $origen_id = "-11";
                    $origen_news++;
                }
                elseif ($origen == "GTIA6")
                {
                    $origen_id = "-109";
                    $origen_GTIA6++;
                }
                else
                {
                    $origen_id = "-1";
                    $origen_portal++;
                }
                $prioridad = $prioridades[$prioridad];
                if($prioridad < 1 || $prioridad > 5)
                {
                    $alertas[] = $linea;//linea en la que lo botamos
                    $alertas_motivo[$linea] = "El contacto tiene asignada una prioridad que no existe";
                    $alerta++;
                }
  	   
                $tel_oficina = genera_telefono($lada_oficina,$telefono_oficina);
                $tel_oficina_2 = genera_telefono($lada_oficina_2,$telefono_oficina_2);
                $tel_movil = genera_telefono($lada_movil,$telefono_movil);
                $tel_movil_2 = genera_telefono($lada_movil_2,$telefono_movil_2);
                $tel_casa_2 = genera_telefono($lada_casa_2,$telefono_casa_2);
                
    
                //siempre es insert por que no tenemos un id en el layout
                //el origen siempre es -1 = portal
                $sql = "INSERT INTO crm_contactos_no_asignados (
                     apellido_paterno,apellido_materno,nombre,tel_casa,email,persona_moral,domicilio,poblacion,
                     fecha_importado,gid,entidad_id,origen_id,prioridad,medio_contacto,tel_casa_2,tel_oficina,
                     tel_oficina_2,tel_movil,tel_movil_2,horario_preferido_casa,horario_preferido_oficina,
                     horario_preferido_movil,horario_preferido_casa_2,horario_preferido_oficina_2,horario_preferido_movil_2
                     ) VALUES (
                     '$apellido_paterno','$apellido_materno','$nombre','$tel_casa','$email','$persona_moral',
                     '$domicilio','$ciudad','$fecha_de_registro','$gid','$entidad_federativa_id','$origen_id',
                     '$prioridad','$medio_contacto','$tel_casa_2','$tel_oficina','$tel_oficina_2','$tel_movil',
                     '$tel_movil_2','$horario_preferido_casa','$horario_preferido_oficina','$horario_preferido_movil',
                     '$horario_preferido_casa_2','$horario_preferido_oficina_2','$horario_preferido_movil_2')";
                $insertados++;
            	$r2 = $db->sql_query($sql) or die("Error<br>$sql<br>".print_r($db->sql_error()));
            	$contacto_id = $db->sql_nextid($r2);

                //meterlo en la campaña correspondiente
                $sql = "SELECT c.campana_id FROM crm_campanas AS c, crm_campanas_groups AS g WHERE c.campana_id=g.campana_id AND g.gid='$gid' ORDER BY campana_id ASC LIMIT 1";//la primer campaña de la concesionaria
                $r2 = $db->sql_query($sql) or die("Error<br>$sql<br>".print_r($db->sql_error()));
                list($campana_id) = $db->sql_fetchrow($r2);
                $sql = "INSERT INTO crm_campanas_llamadas_no_asignados (contacto_id, campana_id)VALUES('$contacto_id', '$campana_id')";
                $db->sql_query($sql);
                //guardar el log de asignacion
                //insertar modelo
                $sql = "insert into crm_prospectos_unidades_no_asignados (contacto_id, modelo)VALUES('$contacto_id', '$modelo')";
                $r2 = $db->sql_query($sql) or die("Error<br>$sql<br>".print_r($db->sql_error()));
            }
        }
        $msg = "<table align='center'>
         <thead>
         <tr>
		   <td colspan=\"2\">Registro de asignación de prospectos no asignados desde el Portal Web de VW</td>
		 </tr>
		 </thead>
		 <tr class=\"row1\">
		   <td colspan=\"2\">$total_de_registros_esperados registros esperados</td>
		 </tr>
		 <tr class=\"row2\">
		   <td colspan=\"2\">$procesados registros procesados</td>
		 </tr>
		 <tr class=\"row1\">
		   <td colspan=\"2\">$insertados agregados</td>
		 </tr>";

        if ($asignados_por_gid)
        {
            $msg .= "<thead>
	         <tr>
			   <td colspan=\"2\">Cantidad asignada por distribuidor</td>
			 </tr>
			 <tr>
			   <td>Distribuidor</td>
			   <td>Cantidad</td>
			 </tr>
			 </thead>";
            foreach ($asignados_por_gid AS $gid=>$cuantos)
            {
                $msg .= "<tr class=\"row".(($c++%2)+1)."\">
	             <td>$gid</td>
	             <td>$cuantos</td>
                </tr>";
            }
        }

        $msg .= "<thead>
	       <tr>
		     <td colspan=\"2\">Cantidad asignada por campaña</td>
		   </tr>
		   <tr>
		     <td>Campaña</td>
		     <td>Cantidad</td>
		   </tr>
		 </thead>";
        $msg .= "<tr class=\"row1\">
           <td>Momento</td>
           <td>$origen_momento</td>
		 </tr>
		 <tr class=\"row2\">
           <td>NewslVW</td>
           <td>$origen_news<td>
		 </tr>
		 <tr class=\"row2\">
           <td>Portal</td>
           <td>$origen_portal</td>
		 </tr>
		 <tr class=\"row2\">
           <td>Portal</td>
           <td>$origen_GTIA6</td>
		 </tr>";
        if ($rechazados)
        {
            $msg .= "<thead>
            <tr>
                <td colspan=\"2\">Lista de lineas rechazadas</td>
		    </tr>
		    <tr>
                <td>Motivo</td>
                <td>Cantidad</td>
		    </tr>
            </thead>";
            $msg .= "<tr class=\"row1\">
                 <td>Rechazados por no especificar el nombre del contacto </td>
                 <td>$motivo_de_rechazo_6</td>
                <tr class=\"row2\">
	           <td>Rechazados por tener teléfono inválido</td>
	           <td>$motivo_de_rechazo_1</td>
			 </tr>
			 <tr class=\"row1\">
	           <td >Rechazados por ser un contacto repetido</td>
	           <td>$motivo_de_rechazo_2</td>
			 </tr>
			 <tr class=\"row2\">
	           <td>Rechazados por presentar un modelo de unidad inválido</td>
	           <td>$motivo_de_rechazo_3</td>
			 </tr>
			 <tr class=\"row1\">
	           <td>Rechazados por presentar una distribuidora inválida</td>
	           <td>$motivo_de_rechazo_4</td>
			 </tr>
			 <tr class=\"row2\">
	           <td>Alertas</td>
	           <td>$alerta</td>
			 </tr>";    
            $msg .= "<thead>
               <tr>
	             <td colspan=\"2\">Lineas Rechazadas</td>
			   </tr>
	           <tr>
	             <td>Linea</td>
				 <td>Motivo</td>
			   </tr>
			 </thead>";
            foreach ($rechazados AS $linea)
            {
                $motivo = $rechazados_motivo[$linea];
                $msg .= "<tr class=\"row".(($c++%2)+1)."\">
	             <td>$linea</td>
				 <td>$motivo</td>
			   </tr>";      
            }
            $msg .= "<thead>
	           <tr>
			     <td colspan=\"2\">ALERTAS</td>
			   </tr>
			   <tr>
	             <td>Linea</td>
				 <td>Motivo</td>
			   </tr>
			 </thead>";
            foreach ($alertas AS $linea)
            {
                $motivo = $alertas_motivo[$linea];
                $msg .= "<tr class=\"row".(($c++%2)+1)."\">
	             <td>$linea</td>
				 <td>$motivo</td>
			   </tr>";
            }
        }
        if ($rechazados_concesionaria)
        {
            $msg .= "<thead>
	           <tr>
			     <td colspan=\"2\">distribuidoras no encontradas en la BD, requieren atención</td>
			   </tr>
			   <tr>
	             <td colspan=\"2\">Linea</td>
			   </tr>
			 </thead>";
        	foreach ($concesionarias AS $linea)
            {
                $linea = stripslashes($linea);
                $msg .= "<tr class=\"row".(($c++%2)+1)."\">
		           <td colspan=\"2\">$linea</td>
				 </tr>";
            }
            $msg .= "<thead>
	           <tr>
			     <td colspan=\"2\">A continuación se agrega el segmento de carga que se debe de volver a cargar</td>
			   </tr>
			   ";
            $msg .= "<tr>
	           <td colspan=\"2\">Total de registros: ".count($rechazados_concesionaria)."</td>
			 </tr>
			 </thead>
			 ";
            foreach ($rechazados_concesionaria AS $linea)
            {
                $linea = stripslashes($linea);
                $msg .= "<tr class=\"row".(($c++%2)+1)."\">
	               <td colspan=\"2\">$linea</td>
			     </tr>";
            }
        }
        $msg .= "</table>";
        $_tabla_carga = $msg;
  
        /*filename = 'log/carga_portal-'.date('YmdHi').'.log';
        if (!$handle = fopen($filename, 'w+'))
        {
            echo "Error al abrir ($filename)\n$msg";
            exit;
        }
          // Write $somecontent to our opened file.
        if (fwrite($handle, $msg) === FALSE) {
            echo "Error al escribir ($filename)\n$msg";
            exit;
        }
        fclose($handle);*/
        $_email_headers  = 'MIME-Version: 1.0' . "\r\n";
        $_email_headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
        $_email_from = "noreply@pcsmexico.com";
        $_email_gerente_gral = "gerardo.garcia@vw.com.mx";
        $_email_headers .= "from:$_email_from\r\n";
        mail("otoral@pcsmexico.com", "Carga de datos de VW ".date("Y-m-d"), $msg, $_email_headers);
        mail("jgodoy@pcsmexico.com", "Carga de datos de VW ".date("Y-m-d"), $msg, $_email_headers);
        mail("orangel@pcsmexico.com", "Carga de datos de VW ".date("Y-m-d"), $msg, $_email_headers);
        mail($_email_gerente_gral, "Carga de datos de VW ".date("Y-m-d"), $msg, $_email_headers);
    }
    else
    {
        $_tabla_carga= "<center><font color='#ff0000'>".$msge."</font></center>";
    }
}
?>