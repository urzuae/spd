<?php

if (!defined('_IN_ADMIN_MAIN_INDEX')) {
    die("No puedes acceder directamente a este archivo...");
}

global $db, $submit, $del;

$insertados1 = 0;
$insertados2 = 0;
$prioridades = array();
$sql = "SELECT prioridad_id, valor FROM crm_prioridades_contactos";
$r = $db->sql_query($sql) or die($sql);
while (list($prioridad_id, $prioridad_val) = $db->sql_fetchrow($r)) {
    $prioridades[$prioridad_val] = $prioridad_id;
}
if ($submit) {
    require_once("$_includesdir/mail.php");
    include("$_includesdir/select.php");
    global $db;
    global $_entidades_federativas;
    $error = 0;
    $filename = $_FILES['f']['tmp_name'];

    if ($_FILES['f']['type'] != 'text/plain') {
        $error++;
        $msge = "Tipo de archivo inválido ";
    }
    if (!$filename) {
        $error++;
        $msge = "No hay archivo, favor de cargarlo ";
    }

    if ($error == 0) {
        $msge = "";
        $fh = fopen($filename, "r");
        if (!$fh) {
            die("Error, no se puede leer el archivo (tal vez sea demasiado grande)" . $filename);
            return;
        }

        //obtener la lista de veh?culos
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
        while (list($id, $n) = $db->sql_fetchrow($r)) {
            if (!is_array($satelites[$id]))
                $satelites[$id] = array();
            if (!in_array($n, $satelites[$id])) //checar que no est? ya
                $satelites[$id][] = $n; //metemos en el array dentro de este id el nombre


        }

        //los gids que se encuentran
        $gid_founds = array();
        //iniciar la lista para checar telefonos repetidos
        $telefonos = array();
        $motivo_de_rechazo_1 = $motivo_de_rechazo_2 = $motivo_de_rechazo_3 = $motivo_de_rechazo_4 = $motivo_de_rechazo_5 = 0;
        //   $_edo_civil = array( 0=>"Otro", 1=>"Soltero", 2=>"Casado", 3=>"Divorciado", 4=>"Union Libre", 5=>"Viudo" );
        $linea = $total_de_registros_esperados = $procesados = $insertados = $origen_momento = $origen_portal = $origen_news = $origen_GTIA6 = 0;
        $rechazados_concesionaria = array();
        $concesionarias = array();
        while (1) {

            $linea++;
            //las primeras 4 lineas son de control, la 1, 3 y 4 est???n vacias
            if ($linea == 1 || $linea == 3 || $linea == 4) {
                $data = fgets($fh, 1000); //quitar la linea
                continue;
            } elseif ($linea == 2) { //la tercer linea indica el n???mero de registros
                $data = fgets($fh, 1000);
                if (!$data)
                    break;
                list($texto, $total_de_registros_esperados) = explode(": ", $data);
                //quitarle el ???ltimo caracter (salto de linea)
                $total_de_registros_esperados = intval($total_de_registros_esperados);
                continue;
            }
            elseif ($linea > 4) {
                //leer normalmente
                $data = fgetcsv($fh, 1000, "|");
                if (!$data)
                    break; //Si no hay datos te saca del archivo
            $procesados++;
                $data2 = array();
                foreach ($data as $undato) {
                    $data2[] = addslashes($undato);
                }
                list($nombre,$apellidos,$email,$lada,$telefono,$modelo,$medio_contacto,$estado,
                        $ciudad,$concesionaria,$origen,$prioridad,$fecha_de_registro) = $data2;

                //concesionaria nunca la checamos con sql, as? que le podemos quitar las slashes
                $concesionaria = stripslashes($concesionaria);

                list($fecha_registro, $horas, $ampm) = explode(" ", $fecha_de_registro);
                $fecha_registro = date_reverse2(str_replace("/", "-", $fecha_registro));

                //CAMBIAR HORA A FORMATO 24 SI $ampm ES IGUAL "p.m."
                if ($ampm == "p.m.") {
                    $hora = explode(":", $horas);
                    switch ($hora[0]) {
                        case 1: $hora[0] = 13;
                            break;
                        case 2: $hora[0] = 14;
                            break;
                        case 3: $hora[0] = 15;
                            break;
                        case 4: $hora[0] = 16;
                            break;
                        case 5: $hora[0] = 17;
                            break;
                        case 6: $hora[0] = 18;
                            break;
                        case 7: $hora[0] = 19;
                            break;
                        case 8: $hora[0] = 20;
                            break;
                        case 9: $hora[0] = 21;
                            break;
                        case 10: $hora[0] = 22;
                            break;
                        case 11: $hora[0] = 23;
                            break;
                        case 12: $hora[0] = 0;
                            break;
                    }
                    $horas = $hora[0] . ":" . $hora[1] . ":" . $hora[2];
                }
                $fecha_importado = $fecha_registro . " " . $horas;
                //rechazar si no tienen tel?fono no email (puede tener nada mas uno)
                if (!$telefono && !$email) {
                    $rechazados[] = $linea; //linea en la que lo botamos
                    $rechazados_motivo[$linea] = "TELEFONO VACIO";
                    $motivo_de_rechazo_1++;
                    continue;
                }
                if ($telefono) { //si el tel???fono no est??? vacio, correr filtros
                    //rechazar si est??? repetido el tel???fono
                    if (in_array($telefono, $telefonos)) {
                        //buscamos el tel?fono y vemos que modelo tiene dado de alta
                        //si es el mismo modelo que tenemos en la db no agregarlo
                        $sql = "SELECT contacto_id FROM crm_contactos WHERE tel_casa='$telefono'"; //solo checo tel_casa por que es el???nico que agrego en este script
                        $r = $db->sql_query($sql) or die($sql);
                        list($c_id) = $db->sql_fetchrow($r);
                        if ($c_id) {
                            $sql = "SELECT modelo FROM crm_prospectos_unidades WHERE contacto_id='$contacto_id'"; //solo checo tel_casa por que es el???nico que agrego en este script
                            $r = $db->sql_query($sql) or die($sql);
                            list($modelo_) = $db->sql_fetchrow($r);
                            if ($modelo_ == $modelo) { //si es diferente el modelo asignarlo
                                $rechazados[] = $linea; //linea en la que lo botamos
                                $rechazados_motivo[$linea] = "CONTACTO REPETIDO (TELEFONO EN ESTA CARGA: $telefono)";
                                $motivo_de_rechazo_2++;
                                continue;
                            }
                        }
                    }
                    $telefonos[] = $telefono; //guardar para comparar
                    //rechazar si el tel???fono est??? repetido, para eso checamos en la db ahora
                    $sql = "SELECT contacto_id FROM crm_contactos WHERE tel_casa='$telefono'"; //solo checo tel_casa por que es el???nico que agrego en este script
                    $r = $db->sql_query($sql) or die($sql);
                    if ($db->sql_numrows($r) > 0) { //encontr??? algo, rechazar
                        //buscamos el tel?fono y vemos que modelo tiene dado de alta
                        //si es el mismo modelo que tenemos en la db no agregarlo
                        list($c_id) = $db->sql_fetchrow($r);
                        if ($c_id) {
                            $sql = "SELECT modelo FROM crm_prospectos_unidades WHERE contacto_id='$contacto_id'"; //solo checo tel_casa por que es el???nico que agrego en este script
                            $r = $db->sql_query($sql) or die($sql);
                            list($modelo_) = $db->sql_fetchrow($r);
                            if ($modelo_ == $modelo) { //si es diferente el modelo asignarlo
                                $rechazados[] = $linea; //linea en la que lo botamos
                                $rechazados_motivo[$linea] = "CONTACTO REPETIDO (TELEFONO EN LA BD: $telefono)";
                                $motivo_de_rechazo_2++;
                                continue;
                            }
                        }
                    }
                    //rechazar si el tel???fono est??? repetido, para eso checamos en la db ahora
                    $sql = "SELECT contacto_id FROM crm_contactos_finalizados WHERE tel_casa='$telefono'"; //solo checo tel_casa por que es el???nico que agrego en este script
                    $r = $db->sql_query($sql) or die($sql);
                    if ($db->sql_numrows($r) > 0) { //encontr??? algo, rechazar
                        $rechazados[] = $linea; //linea en la que lo botamos
                        $rechazados_motivo[$linea] = "CONTACTO REPETIDO (TELEFONO EN LA BD FINALIZADA: $telefono)";
                        $motivo_de_rechazo_2++;
                        continue;
                    }
                }
                $unidades = Genera_Unidades($db);
                $modelo_id = Revisa_modelo($db, strtoupper(trim($modelo)));
                $modelo = $unidades[$modelo_id];
                if ($modelo == "" || !in_array($modelo, $unidades)) { //checar que el veh???culo est??? en la lista
                    $rechazados[] = $linea; //linea en la que lo botamos
                    $rechazados_motivo[$linea] = "MODELO INVALIDO: $modelo";
                    $motivo_de_rechazo_3++;
                    continue;
                }
                //m???s adelante hay m???s tratamiendo de errores
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
                if ($espacio) {
                    $apellido_paterno = substr($apellidos, 0, $espacio);
                    $apellido_materno = substr($apellidos, $espacio + 1);
                } else {
                    $apellido_paterno = $apellidos;
                    $apellido_materno = '';
                }

                $entidad_federativa_id = array_search($estado, $_entidades_federativas);
                if ($entidad_federativa_id)
                    $entidad_federativa_id++; //por que el array es 0 aguascalientes
                    //rechazar si el nombre est??? repetido, para eso checamos en la db ahora,
                //es en este momento por que hasta ahorita procesamos el nombre
                if (($nombre == '') && ($apellidos == '')) {
                    $rechazados[] = $linea; //linea en la que lo botamos
                    $rechazados_motivo[$linea] = "EL NOMBRE DEL CONTACTO NO PUEDE SER VACIO";
                    $motivo_de_rechazo_6++;
                    continue;
                } else {
                    $sql = "SELECT contacto_id FROM crm_contactos WHERE nombre='$nombre' AND apellido_paterno='$apellido_paterno' AND apellido_materno='$apellido_materno'";
                    $r = $db->sql_query($sql) or die($sql);
                    if ($db->sql_numrows($r) > 0) { //encontr??? algo, rechazar
                        $rechazados[] = $linea; //linea en la que lo botamos
                        $rechazados_motivo[$linea] = "CONTACTO REPETIDO (NOMBRE EN LA BD DE CRM CONTACTOS: $nombre $apellido_paterno $apellido_materno)";
                        $motivo_de_rechazo_2++;
                        continue;
                    }

                    //NO ASIGNADOS
                    //rechazar si el nombre est??? repetido, para eso checamos en la db ahora,
                    //es en este momento por que hasta ahorita procesamos el nombre
                    $sql = "SELECT contacto_id FROM crm_contactos_no_asignados WHERE nombre='$nombre' AND apellido_paterno='$apellido_paterno' AND apellido_materno='$apellido_materno'"; //solo checo tel_casa por que es el???nico que agrego en este script
                    $r = $db->sql_query($sql) or die($sql);
                    if ($db->sql_numrows($r) > 0) { //encontr??? algo, rechazar
                        $rechazados[] = $linea; //linea en la que lo botamos
                        $rechazados_motivo[$linea] = "CONTACTO REPETIDO (NOMBRE EN LA BD DE CRM CONTACTOS NO ASIGNADOS: $nombre $apellido_paterno $apellido_materno)";
                        $motivo_de_rechazo_2++;
                        continue;
                    }

                    //tambien checar en la tabla de finalizados
                    //rechazar si el nombre est??? repetido, para eso checamos en la db ahora,
                    //es en este momento por que hasta ahorita procesamos el nombre
                    $sql = "SELECT contacto_id FROM crm_contactos_finalizados WHERE nombre='$nombre' AND apellido_paterno='$apellido_paterno' AND apellido_materno='$apellido_materno'"; //solo checo tel_casa por que es el???nico que agrego en este script
                    $r = $db->sql_query($sql) or die($sql);
                    if ($db->sql_numrows($r) > 0) { //encontr??? algo, rechazar
                        $rechazados[] = $linea; //linea en la que lo botamos
                        $rechazados_motivo[$linea] = "CONTACTO REPETIDO (NOMBRE EN LA BD DE DE CRM CONTACTOS FINALIZADA: $nombre $apellido_paterno $apellido_materno)";
                        $motivo_de_rechazo_2++;
                        continue;
                    }

                    //NO ASIGNADOS
                    //tambien checar en la tabla de finalizados
                    //rechazar si el nombre est??? repetido, para eso checamos en la db ahora,
                    //es en este momento por que hasta ahorita procesamos el nombre
                    $sql = "SELECT contacto_id FROM crm_contactos_no_asignados_finalizados WHERE nombre='$nombre' AND apellido_paterno='$apellido_paterno' AND apellido_materno='$apellido_materno'"; //solo checo tel_casa por que es el???nico que agrego en este script
                    $r = $db->sql_query($sql) or die($sql);
                    if ($db->sql_numrows($r) > 0) { //encontr??? algo, rechazar
                        $rechazados[] = $linea; //linea en la que lo botamos
                        $rechazados_motivo[$linea] = "CONTACTO REPETIDO (NOMBRE EN LA BD DE CRM CONTACTOS NO ASIGNADOS FINALIZADA: $nombre $apellido_paterno $apellido_materno)";
                        $motivo_de_rechazo_2++;
                        continue;
                    }
                }
                //buscar la concesionaria
                $gid = array_search($concesionaria, $groups);
                if ($gid === FALSE) { //buscar en las satelites
                    foreach ($satelites AS $id => $aliases) { //obtener el gid y el array de nombres
                        if (array_search($concesionaria, $aliases) !== FALSE) {//checar que est? en los aliases el nombre
                            $gid = $id;
                            break;
                        }
                    }
                }
                if ($gid === FALSE) {
                    $rechazados[] = $linea; //linea en la que lo botamos
                    $rechazados_motivo[$linea] = "CONCESIONARIA: " . ($concesionaria);
                    $rechazados_concesionaria[] = implode("|", $data);
                    if (!in_array($concesionaria, $concesionarias))
                        $concesionarias[] = "$concesionaria";
                    $motivo_de_rechazo_4++;
                    continue;
                }
                else {
                    if (!in_array($gid, $gid_founds))
                        $gid_founds[] = $gid;
                }
                $origen_id=1;
                $asignados_por_gid[$gid]++;
                $sql="SELECT fuente_id FROM crm_fuentes WHERE upper(nombre)='".$origen."'";
                $r = $db->sql_query($sql) or die($sql);
                if ($db->sql_numrows($r) > 0) {
                    list($origen_id) = $db->sql_fetchrow($r);
                }

                $prioridad = $prioridades[$prioridad];
                if ($prioridad < 1 || $prioridad > 5) {
                    $alertas[] = $linea; //linea en la que lo botamos
                    $alertas_motivo[$linea] = "El contacto tiene asignada una prioridad que no existe";
                    $alerta++;
                }

                //buscar si el gid se encuentra en la tabla "groups_asignar"
                $sql = "select count(gid) from groups_asignar where gid='$gid'";
                $rs = mysql_fetch_row(mysql_query($sql));
                if ($rs[0] > 0) {
                    $sql = "INSERT INTO crm_contactos_no_asignados (
                        apellido_paterno,apellido_materno,nombre,tel_casa,email,persona_moral,domicilio,
                        poblacion,fecha_importado,fecha_alta,gid,entidad_id,origen_id,prioridad,medio_contacto)
                        VALUES ('$apellido_paterno','$apellido_materno','$nombre','$tel_casa','$email',
                        '$persona_moral','$domicilio','$ciudad','$fecha_importado',NOW(),'$gid','$entidad_federativa_id',
                        '$origen_id','$prioridad','$medio_contacto')";

                    $r2 = $db->sql_query($sql) or die("Error<br>$sql<br>" . print_r($db->sql_error()));
                    $contacto_id = $db->sql_nextid($r2);

                    //Comprobar que la fecha de importado se insert? bien
                    $sql = "select year(fecha_importado),month(fecha_importado),day(fecha_importado),nombre,apellido_paterno,apellido_materno from crm_contactos_no_asignados where contacto_id = '$contacto_id'";
                    $r2 = $db->sql_query($sql);
                    list($import_year, $import_month, $import_day, $nombre_db, $apellido_paterno_db, $apellido_materno_db) = $db->sql_fetchrow($r2);
                    if ($import_year < 1970 or $import_month < 1 or $import_month > 12 or $import_day < 1 or $import_day > 31) {
                        $sql = "delete from crm_contactos_no_asignados where contacto_id = '$contacto_id'";
                        $r2 = $db->sql_query($sql);
                        $motivo_de_rechazo_5++;
                        $rechazados[] = $linea; //linea en la que lo botamos
                        $rechazados_motivo[$linea] = "FECHA IMPORTADO INCORRECTA EN CONTACTO: $nombre_db $apellido_paterno_db $apellido_materno_db";
                    } else {
                        //meterlo en la campa???a correspondiente
                        $sql = "SELECT c.campana_id FROM crm_campanas AS c, crm_campanas_groups AS g WHERE c.campana_id=g.campana_id AND g.gid='$gid' ORDER BY campana_id ASC LIMIT 1"; //la primer campa???a de la concesionaria
                        $r2 = $db->sql_query($sql) or die("Error<br>$sql<br>" . print_r($db->sql_error()));
                        list($campana_id) = $db->sql_fetchrow($r2);
                        $sql = "INSERT INTO crm_campanas_llamadas_no_asignados (contacto_id, campana_id) VALUES('$contacto_id', '$campana_id')";
                        $db->sql_query($sql);
                        //guardar el log de asignacion
                        $sql = "INSERT INTO crm_contactos_asignacion_log (contacto_id, uid, from_uid, to_uid, from_gid, to_gid) VALUES('$contacto_id','0','0','0','0','$gid')";
                        $db->sql_query($sql) or die("Error");
                        //insertar modelo
                        $sql = "insert into crm_prospectos_unidades_no_asignados (contacto_id, modelo,modelo_id) VALUES('$contacto_id', '$modelo','$modelo_id')";
                        $r2 = $db->sql_query($sql) or die("Error<br>$sql<br>" . print_r($db->sql_error()));
                        $insertados2++;
                        $insertados++;
                    }
                } else {
                    $sql = "INSERT INTO crm_contactos (apellido_paterno,apellido_materno,nombre,tel_casa,email,
                            persona_moral,domicilio,poblacion,fecha_importado,fecha_alta,gid,entidad_id,origen_id,
                            prioridad,medio_contacto) VALUES ('$apellido_paterno','$apellido_materno','$nombre',
                            '$tel_casa','$email','$persona_moral','$domicilio','$ciudad','$fecha_importado',
                            NOW(),'$gid','$entidad_federativa_id','$origen_id','$prioridad','$medio_contacto')";
                    $r2 = $db->sql_query($sql) or die("Error<br>$sql<br>" . print_r($db->sql_error()));
                    $contacto_id = $db->sql_nextid($r2);
                    $sql = "SELECT c.campana_id FROM crm_campanas AS c, crm_campanas_groups AS g WHERE c.campana_id=g.campana_id AND g.gid='$gid' ORDER BY campana_id ASC LIMIT 1"; //la primer campa???a de la concesionaria
                    $r2 = $db->sql_query($sql) or die("Error<br>$sql<br>" . print_r($db->sql_error()));
                    list($campana_id) = $db->sql_fetchrow($r2);
                    $sql = "INSERT INTO crm_campanas_llamadas (contacto_id, campana_id)VALUES('$contacto_id', '$campana_id')";
                    $db->sql_query($sql);
                    $sql = "INSERT INTO crm_contactos_asignacion_log (contacto_id, uid, from_uid, to_uid, from_gid, to_gid)VALUES('$contacto_id','0','0','0','0','$gid')";
                    $db->sql_query($sql) or die("Error");
                    $modelo_id = Revisa_modelo($db, $modelo);
                    $sql = "insert into crm_prospectos_unidades (contacto_id, modelo,modelo_id)VALUES('$contacto_id', '$modelo','$modelo_id')";
                    $r2 = $db->sql_query($sql) or die("Error<br>$sql<br>" . print_r($db->sql_error()));
                    $insertados1++;
                    $insertados++;
                }
            }
        }
        fclose($fh); //ya se leyo todo del archivo
        $msg = "<table align='center'>
	          <thead>
	            <tr>
	              <td colspan=\"2\"> Registro de asignaci&oacute;nn de prospectos asignados y no asignados desde el Portal Web de VW</td>
	            </tr>
	          </thead>
	          <tr class=\"row1\">
	            <td colspan=\"2\">$total_de_registros_esperados registros esperados</td>
	          </tr>
	          <tr class=\"row2\">
	            <td colspan=\"2\">$procesados registros procesados</td>
	          </tr>
	          <tr class=\"row1\">
	            <td colspan=\"2\">$insertados1 \"asignados\" agregados</td>
	          </tr>
              <tr class=\"row1\">
	            <td colspan=\"2\">$insertados2 \"no asignados\" agregados</td>
	          </tr>";
        if ($asignados_por_gid) {
            $msg .= "<thead>
                <tr>
			   <td colspan=\"2\">Cantidad asignada por distribuidor</td>
			 </tr>
			 <tr>
			   <td>Distribuidor</td>
			   <td>Cantidad</td>
			 </tr>
			 </thead>";
            list($ano, $dia, $mes) = explode("-", $fecha_registro);
            $fecha_registro = sprintf("%s-%s-%s", $ano, $mes, $dia);
            foreach ($asignados_por_gid AS $gid => $cuantos) {
                $msg .= "<tr class=\"row" . (($c++ % 2) + 1) . "\">
	             <td>$gid</td>
	             <td>$cuantos</td>
			   </tr>";
                $sql = sprintf("INSERT INTO carga_prospectos_log
		        (cantidad, gid, fecha_contacto)
		        VALUES
		        ('%s','%s','%s')", $cuantos, $gid, $fecha_registro);
                $resultado = $db->sql_query($sql) or die("Error<br>$sql<br>" . print_r($db->sql_error()));
            }
        }
        $msg .= "<thead>
	       <tr>
		     <td colspan=\"2\">Cantidad asignada por campa&ntilde;a</td>
		   </tr>
		   <tr>
		     <td>Campa&ntilde;a</td>
		     <td>Cantidad</td>
		   </tr>
		 </thead>";
        if ($rechazados) {
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
               </tr><tr class=\"row2\">
                 <td>Rechazados por tener tel&eacute;fono inv&aacute;lido</td>
                 <td>$motivo_de_rechazo_1</td>
               </tr>
               <tr class=\"row1\">
                 <td >Rechazados por ser un contacto repetido</td>
                 <td>$motivo_de_rechazo_2</td>
               </tr>
               <tr class=\"row2\">
                 <td>Rechazados por presentar un modelo de unidad inv&aacute;lido</td>
                 <td>$motivo_de_rechazo_3</td>
               </tr>
               <tr class=\"row1\">
                 <td>Rechazados por presentar una concesionaria inv&aacute;lida</td>
                 <td>$motivo_de_rechazo_4</td>
               </tr>
                <tr class=\"row2\">
                 <td>Rechazados por presentar una fecha de importado inv&aacute;lida</td>
                 <td>$motivo_de_rechazo_5</td>
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
            foreach ($rechazados AS $linea) {
                $motivo = $rechazados_motivo[$linea];
                $msg .= "<tr class=\"row" . (($c++ % 2) + 1) . "\">
      	          <td>$linea</td>
      	          <td>$motivo</td>
      	         </tr>";
            }
        }
        if ($rechazados_concesionaria) {
            $msg .= "<thead>
	           <tr>
	             <td colspan=\"2\">Concesionarias no encontradas en la BD, requieren atenci?n</td>
	           </tr>
			   <tr>
	             <td colspan=\"2\">Linea</td>
			   </tr>
			   </thead>";
            foreach ($concesionarias AS $linea) {
                $linea = stripslashes($linea);
                $msg .= "<tr class=\"row" . (($c++ % 2) + 1) . "\">
		           <td colspan=\"2\">$linea</td>
				 </tr>";
            }
            $msg .= "<thead>
	           <tr>
			     <td colspan=\"2\">A continuaci?n se agrega el segmento de carga que se debe de volver a cargar</td>
			   </tr>";
            $msg .= "<tr>
	             <td>Total de registros: " . count($rechazados_concesionaria) . "</td>
			   </tr>
			   </thead>";
            foreach ($rechazados_concesionaria AS $linea) {
                $linea = stripslashes($linea);
                $msg .= "<tr class=\"row" . (($c++ % 2) + 1) . "\">
	               <td colspan=\"2\">$linea</td>
			     </tr>";
            }
        }
        $msg .= "</table>";
        $_tabla_carga = $msg;

        $_email_headers = 'MIME-Version: 1.0' . "\r\n";
        $_email_headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";


        $_email_from = "noreply@pcsmexico.com";
        $_email_gerente_gral = "gerardo.garcia@vw.com.mx";
        $_email_headers .= "from:$_email_from\r\n";

        mail("lahernandez@pcsmexico.com", "Carga de datos desde Aministrador ", $msg, $_email_headers);
        mail("orangel@pcsmexico.com", "Carga de datos desde Aministrador " . date("Y-m-d"), $msg, $_email_headers);
        mail($_email_gerente_gral, "Carga de datos desde Aministrador " . date("Y-m-d"), $msg, $_email_headers);
    }
}

function Revisa_modelo($db, $modelo) {
    $id = 0;
    $sql = "SELECT unidad_id FROM crm_unidades where upper(nombre)='" . $modelo . "';";
    $res = $db->sql_query($sql) or die("Error en el query:  " . $sql);
    if ($db->sql_numrows($res) > 0)
        $id = $db->sql_fetchfield(0, 0, $res);
    return $id;
}

function Genera_Unidades($db) {
    $sql = "SELECT unidad_id, nombre FROM crm_unidades";
    $r = $db->sql_query($sql) or die($sql);
    $unidades = array();
    while (list($id, $n) = $db->sql_fetchrow($r))
        $unidades[$id] = $n;
    return $unidades;
}

?>