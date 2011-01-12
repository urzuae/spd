<?php
global $db, $submit, $del;
$filename = $argv[2];
if(!file_exists($filename))
{
    die("El archivo de carga [$filename] no existe\n");
}

$insertados2 = 0;
$prioridades = array();
$sql = "SELECT prioridad_id, valor FROM crm_prioridades_contactos";
$r = $db->sql_query($sql) or die($sql);
while(list($prioridad_id, $prioridad_val) = $db->sql_fetchrow($r))
{
  $prioridades[$prioridad_val] = $prioridad_id;
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


$submit = true;
if ($submit)
{
	require_once("$_includesdir/mail.php");
	include("$_includesdir/select.php");
	global $db;
	global $_entidades_federativas;
	//$filename = $_FILES['f']['tmp_name'];
	$fh = fopen($filename, "r");
	if (!$fh)
    {
	  die("Error, no se puede leer el archivo (tal vez sea demasiado grande)".$filename);
	  return;
	}

	//obtener la lista de vehículos
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
		if (!in_array($n, $satelites[$id])) //checar que no esté ya
		  $satelites[$id][] = $n;//metemos en el array dentro de este id el nombre
	}
	//los gids que se encuentran
	$gid_founds = array();
	$telefonos = array();
	$motivo_de_rechazo_1 = $motivo_de_rechazo_2 = $motivo_de_rechazo_3 = $motivo_de_rechazo_4 = $motivo_de_rechazo_5 = 0;
    $motivo_de_rechazo_7 = 0;
	$linea = $total_de_registros_esperados = $procesados = $insertados = $origen_momento = $origen_portal = $origen_news = 0;
	$rechazados_concesionaria = array();
	$concesionarias = array();
	while(1)
    {
		$linea++;
		if ($linea == 1 || $linea == 3 || $linea == 4)
        {
			$data = fgets($fh, 1000);//quitar la linea
			continue;
		}
		elseif ($linea == 2) //la tercer linea indica el nï¿?mero de registros
		{
			$data = fgets($fh, 1000);
			if (!$data) break;
			list($texto, $total_de_registros_esperados) = explode(": ", $data);
			//quitarle el ï¿?ltimo caracter (salto de linea)
			$total_de_registros_esperados = intval($total_de_registros_esperados);
			continue;
		}
		elseif ($linea > 4)
        {
			$data = fgetcsv($fh, 1000, "|");
			if (!$data) break;
			$procesados++;
			$data2 = array();
			foreach ($data as $undato)
            {
				$data2[] = addslashes($undato);
			}
			list($nombre, $apellidos,$email,$lada, $telefono,$modelo,$medio_contacto,$estado,$ciudad,$concesionaria,
			     $origen,$prioridad,$fecha_de_registro,$horario_preferido_casa,$lada_casa_2,$telefono_casa_2,
			     $horario_preferido_casa_2,$lada_oficina,$telefono_oficina,$horario_preferido_oficina,$lada_oficina_2,
                 $telefono_oficina_2,$horario_preferido_oficina_2,$lada_movil,$telefono_movil,$horario_preferido_movil,
			     $lada_movil_2,$telefono_movil_2,$horario_preferido_movil_2
			     )= $data2;

		
			$concesionaria = stripslashes($concesionaria);
			list($fecha_registro,$horas,$ampm) = explode(" ",$fecha_de_registro);
			$fecha_registro = date_reverse2(str_replace("/","-",$fecha_registro));

            //CAMBIAR HORA A FORMATO 24 SI $ampm ES IGUAL "p.m."
            if($ampm == "p.m.")
            {
                $hora = explode(":",$horas);
                switch($hora[0])
                {
                    case 1: $hora[0] = 13; break;
                    case 2: $hora[0] = 14; break;
                    case 3: $hora[0] = 15; break;
                    case 4: $hora[0] = 16; break;
                    case 5: $hora[0] = 17; break;
                    case 6: $hora[0] = 18; break;
                    case 7: $hora[0] = 19; break;
                    case 8: $hora[0] = 20; break;
                    case 9: $hora[0] = 21; break;
                    case 10: $hora[0] = 22; break;
                    case 11: $hora[0] = 23; break;
                    case 12: $hora[0] = 0; break;
                }
                $horas = $hora[0].":".$hora[1].":".$hora[2];
            }
            $fecha_importado = $fecha_registro." ".$horas;
			//rechazar si no tienen teléfono no email (puede tener nada mas uno)
			if (!$telefono && !$email)
            {
				$rechazados[] = $linea;//linea en la que lo botamos
				$rechazados_motivo[$linea] = "TELEFONO VACIO";
				$motivo_de_rechazo_1++;
				continue;
			}
			if ($telefono) //si el telï¿?fono no estï¿? vacio, correr filtros
			{
				//rechazar si estï¿? repetido el telï¿?fono
				if (in_array($telefono, $telefonos) )
                {
					$sql = "SELECT contacto_id FROM crm_contactos WHERE tel_casa='$telefono'";//solo checo tel_casa por que es elï¿?nico que agrego en este script
					$r = $db->sql_query($sql) or die($sql);
					list($c_id) = $db->sql_fetchrow($r);
					if ($c_id)
                    {
						$sql = "SELECT modelo FROM crm_prospectos_unidades WHERE contacto_id='$contacto_id'";//solo checo tel_casa por que es elï¿?nico que agrego en este script
						$r = $db->sql_query($sql) or die($sql);
						list($modelo_) = $db->sql_fetchrow($r);
						if ($modelo_ == $modelo) //si es diferente el modelo asignarlo
						{
							$rechazados[] = $linea;//linea en la que lo botamos
							$rechazados_motivo[$linea] = "CONTACTO REPETIDO (TELEFONO EN ESTA CARGA: $telefono)";
							$motivo_de_rechazo_2++;
							continue;
						}
					}
				}
				$telefonos[] = $telefono;//guardar para comparar
				
				$sql = "SELECT contacto_id FROM crm_contactos WHERE tel_casa='$telefono'";//solo checo tel_casa por que es elï¿?nico que agrego en este script
				$r = $db->sql_query($sql) or die($sql);
				if ($db->sql_numrows($r) > 0) //encontrï¿? algo, rechazar
				{
					//buscamos el teléfono y vemos que modelo tiene dado de alta
					//si es el mismo modelo que tenemos en la db no agregarlo
					list($c_id) = $db->sql_fetchrow($r);
					if ($c_id)
                    {
						$sql = "SELECT modelo FROM crm_prospectos_unidades WHERE contacto_id='$contacto_id'";//solo checo tel_casa por que es elï¿?nico que agrego en este script
						$r = $db->sql_query($sql) or die($sql);
						list($modelo_) = $db->sql_fetchrow($r);
						if ($modelo_ == $modelo) //si es diferente el modelo asignarlo
						{
							$rechazados[] = $linea;//linea en la que lo botamos
							$rechazados_motivo[$linea] = "CONTACTO REPETIDO (TELEFONO EN LA BD: $telefono)";
							$motivo_de_rechazo_2++;
							continue;
						}
					}
				}
				//rechazar si el telï¿?fono estï¿? repetido, para eso checamos en la db ahora
				$sql = "SELECT contacto_id FROM crm_contactos_finalizados WHERE tel_casa='$telefono'";//solo checo tel_casa por que es elï¿?nico que agrego en este script
				$r = $db->sql_query($sql) or die($sql);
				if ($db->sql_numrows($r) > 0) //encontrï¿? algo, rechazar
				{
					$rechazados[] = $linea;//linea en la que lo botamos
					$rechazados_motivo[$linea] = "CONTACTO REPETIDO (TELEFONO EN LA BD FINALIZADA: $telefono)";
					$motivo_de_rechazo_2++;
					continue;
                }
            }// fin de validacion de telefono

            $unidades  = Genera_Unidades($db);
            $modelo_id=Revisa_modelo($db,strtoupper(trim($modelo)));
            $modelo=$unidades[$modelo_id];
			if ($modelo == "" || !in_array($modelo, $unidades)) //checar que el vehï¿?culo estï¿? en la lista
			{
                $rechazados[] = $linea;//linea en la que lo botamos
				$rechazados_motivo[$linea] = "MODELO INVALIDO: $modelo";
				$motivo_de_rechazo_3++;
				continue;
            }
			//mas adelante hay mas tratamiendo de errores
			$tel_casa = "";
			if ($lada) $tel_casa .= "($lada)";
				$tel_casa .= " $telefono";
			$domicilio = $direccion;
			$persona_moral = 0;
			$poblacion = $ciudad;
			$nombre = strtoupper(trim($nombre));
			$apellidos = strtoupper(trim($apellidos));
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

            //rechazar si el nombre esta repetido, para eso checamos en la db ahora,
			//es en este momento por que hasta ahorita procesamos el nombre
			$sql = "SELECT contacto_id FROM crm_contactos WHERE nombre='$nombre' AND apellido_paterno='$apellido_paterno' AND apellido_materno='$apellido_materno'";//solo checo tel_casa por que es elï¿?nico que agrego en este script
			$r = $db->sql_query($sql) or die($sql);
			if ($db->sql_numrows($r) > 0) //encontrï¿? algo, rechazar
			{
                $rechazados[] = $linea;//linea en la que lo botamos
				$rechazados_motivo[$linea] = "CONTACTO REPETIDO (NOMBRE EN LA BD: $nombre $apellido_paterno $apellido_materno)";
				$motivo_de_rechazo_2++;
				continue;
            }

			//NO ASIGNADOS
			//rechazar si el nombre estï¿? repetido, para eso checamos en la db ahora,
			//es en este momento por que hasta ahorita procesamos el nombre
			$sql = "SELECT contacto_id FROM crm_contactos_no_asignados WHERE nombre='$nombre' AND apellido_paterno='$apellido_paterno' AND apellido_materno='$apellido_materno'";//solo checo tel_casa por que es elï¿?nico que agrego en este script
			$r = $db->sql_query($sql) or die($sql);
			if ($db->sql_numrows($r) > 0) //encontrï¿? algo, rechazar
			{
                $rechazados[] = $linea;//linea en la que lo botamos
				$rechazados_motivo[$linea] = "CONTACTO REPETIDO (NOMBRE EN LA BD: $nombre $apellido_paterno $apellido_materno)";
				$motivo_de_rechazo_2++;
				continue;
            }

			//tambien checar en la tabla de finalizados
			//rechazar si el nombre estï¿? repetido, para eso checamos en la db ahora,
			//es en este momento por que hasta ahorita procesamos el nombre
			$sql = "SELECT contacto_id FROM crm_contactos_finalizados WHERE nombre='$nombre' AND apellido_paterno='$apellido_paterno' AND apellido_materno='$apellido_materno'";//solo checo tel_casa por que es elï¿?nico que agrego en este script
			$r = $db->sql_query($sql) or die($sql);
			if ($db->sql_numrows($r) > 0) //encontrï¿? algo, rechazar
			{
                $rechazados[] = $linea;//linea en la que lo botamos
				$rechazados_motivo[$linea] = "CONTACTO REPETIDO (NOMBRE EN LA BD FINALIZADA: $nombre $apellido_paterno $apellido_materno)";
				$motivo_de_rechazo_2++;
				continue;
            }

            //NO ASIGNADOS
			//tambien checar en la tabla de finalizados
			//rechazar si el nombre estï¿? repetido, para eso checamos en la db ahora,
			//es en este momento por que hasta ahorita procesamos el nombre
			$sql = "SELECT contacto_id FROM crm_contactos_no_asignados_finalizados WHERE nombre='$nombre' AND apellido_paterno='$apellido_paterno' AND apellido_materno='$apellido_materno'";//solo checo tel_casa por que es elï¿?nico que agrego en este script
			$r = $db->sql_query($sql) or die($sql);
			if ($db->sql_numrows($r) > 0) //encontrï¿? algo, rechazar
			{
                $rechazados[] = $linea;//linea en la que lo botamos
				$rechazados_motivo[$linea] = "CONTACTO REPETIDO (NOMBRE EN LA BD FINALIZADA: $nombre $apellido_paterno $apellido_materno)";
				$motivo_de_rechazo_2++;
				continue;
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
            $sql = "select fuente_id,active from crm_fuentes where nombre like '%$origen%' AND active = 1;";
            $resultOrigen = $db->sql_query($sql);
            if($db->sql_numrows($$resultOrigen)>0)
            {
                list($origen_id,$active) = $db->sql_fetchrow($resultOrigen);
                if($active < 1)
                {
                    $rechazados[] = $linea;//linea en la que lo botamos
        			$rechazados_motivo[$linea] = "CONTACTO REPETIDO (NOMBRE EN LA BD FINALIZADA: $nombre $apellido_paterno $apellido_materno)";
            		$motivo_de_rechazo_7++;
                	continue;
                }
            }
            else
            #if(($origen_id == null) || ($origen_id == "") || ($origen_id == 0))
            {
                $alertas[] = $linea;//linea en la que lo botamos
                $alertas_motivo[] = array("msg" => "No se ha encontrado un id asociado con la etiqueta de origen: $origen .","linea" => $linea);
                $origen_id = -8;
            }
            
            $prioridad = $prioridades[$prioridad];
			if($prioridad < 1 || $prioridad > 5)
            {
                $alertas[] = $linea;//linea en la que lo botamos
                $alertas_motivo[] = array("msg" => "El contacto tiene asignada una prioridad que no existe: $prioridad .","linea" => $linea);
            }

            $tel_oficina = genera_telefono($lada_oficina,$telefono_oficina);
			$tel_oficina_2 = genera_telefono($lada_oficina_2,$telefono_oficina_2);
			$tel_movil = genera_telefono($lada_movil,$telefono_movil);
			$tel_movil_2 = genera_telefono($lada_movil_2,$telefono_movil_2);
			$tel_casa_2 = genera_telefono($lada_casa_2,$telefono_casa_2);

            //buscar si el gid se encuentra en la tabla "groups_asignar"
            $sql = "INSERT INTO crm_contactos_no_asignados (apellido_paterno,apellido_materno,nombre,tel_casa,
                    email,persona_moral,domicilio,poblacion,fecha_importado,fecha_alta,gid,entidad_id,origen_id,
                    prioridad,medio_contacto,tel_casa_2,tel_oficina,tel_oficina_2,tel_movil,tel_movil_2,
                    horario_preferido_casa,horario_preferido_oficina,horario_preferido_movil,horario_preferido_casa_2,
                    horario_preferido_oficina_2,horario_preferido_movil_2) VALUES (
                    '$apellido_paterno','$apellido_materno','$nombre','$tel_casa','$email','$persona_moral',
                    '$domicilio','$ciudad','$fecha_importado',NOW(),'$gid','$entidad_federativa_id','$origen_id',
                    '$prioridad','$medio_contacto','$tel_casa_2','$tel_oficina','$tel_oficina_2','$tel_movil',
                    '$tel_movil_2','$horario_preferido_casa','$horario_preferido_oficina','$horario_preferido_movil',
                    '$horario_preferido_casa_2','$horario_preferido_oficina_2','$horario_preferido_movil_2')";
            $r2 = $db->sql_query($sql) or die("Error<br>$sql<br>".print_r($db->sql_error()));
            $contacto_id = $db->sql_nextid($r2);

            //Comprobar que la fecha de importado se insert? bien
            $sql = "select year(fecha_importado),month(fecha_importado),day(fecha_importado),nombre,apellido_paterno,apellido_materno from crm_contactos_no_asignados where contacto_id = '$contacto_id'";
            $r2 = $db->sql_query($sql);
            list($import_year,$import_month,$import_day,$nombre_db,$apellido_paterno_db,$apellido_materno_db) = $db->sql_fetchrow($r2);
            if($import_year < 1970 or $import_month < 1 or $import_month > 12 or $import_day < 1 or $import_day > 31)
            {
                $sql = "delete from crm_contactos_no_asignados where contacto_id = '$contacto_id'";
                $r2 = $db->sql_query($sql);
                $motivo_de_rechazo_5++;
                $rechazados[] = $linea;//linea en la que lo botamos
                $rechazados_motivo[$linea] = "FECHA IMPORTADO INCORRECTA EN CONTACTO: $nombre_db $apellido_paterno_db $apellido_materno_db";
            }
            else
            {
                //meterlo en la campaï¿?a correspondiente
                $sql = "SELECT c.campana_id FROM crm_campanas AS c, crm_campanas_groups AS g WHERE c.campana_id=g.campana_id AND g.gid='$gid' ORDER BY campana_id ASC LIMIT 1";//la primer campaï¿?a de la concesionaria
                $r2 = $db->sql_query($sql) or die("Error<br>$sql<br>".print_r($db->sql_error()));
                list($campana_id) = $db->sql_fetchrow($r2);
                $sql = "INSERT INTO crm_campanas_llamadas_no_asignados (contacto_id, campana_id) VALUES('$contacto_id', '$campana_id')";
                $db->sql_query($sql);

                //guardar el log de asignacion
                $sql = "INSERT INTO crm_contactos_asignacion_log (contacto_id, uid, from_uid, to_uid, from_gid, to_gid) VALUES('$contacto_id','0','0','0','0','$gid')";
                $db->sql_query($sql) or die("Error");

                //insertar modelo
                $modelo_id=Revisa_modelo($db,$modelo);
                $sql = "insert into crm_prospectos_unidades_no_asignados (contacto_id, modelo,modelo_id) VALUES('$contacto_id', '$modelo','$modelo_id')";
                $r2 = $db->sql_query($sql) or die("Error<br>$sql<br>".print_r($db->sql_error()));
                $insertados2++;
            }
		}
	}
	fclose($fh);//ya se leyo todo del archivo
	$msg = "<table>
	          <thead>
	            <tr>
	              <td colspan=\"2\"> Registro de asignación de prospectos asignados y no asignados desde el Portal Web de VW</td>
	            </tr>
	          </thead>
	          <tr class=\"row1\">
	            <td colspan=\"2\">$total_de_registros_esperados registros esperados</td>
	          </tr>
	          <tr class=\"row2\">
	            <td colspan=\"2\">$procesados registros procesados</td>
	          </tr>
              <tr class=\"row1\">
	            <td colspan=\"2\">$insertados2 \"no asignados\" agregados</td>
	          </tr>";
	if ($asignados_por_gid){
	  $msg .= "<thead>
	         <tr>
			   <td colspan=\"2\">Cantidad asignada por distribuidora</td>
			 </tr>
			 <tr>
			   <td>Distribuidor</td>
			   <td>Cantidad</td>
			 </tr>
			 </thead>";
	  list($ano,$dia,$mes) = explode("-",$fecha_registro);
	  $fecha_registro = sprintf("%s-%s-%s",$ano,$mes,$dia);
      if(count($asignados_por_gid) > 0)
      {
        foreach ($asignados_por_gid AS $gid=>$cuantos)
        {
            $msg .= "<tr class=\"row".(($c++%2)+1)."\">
	             <td>$gid</td>
	             <td>$cuantos</td>
			   </tr>";
            $sql = sprintf("INSERT INTO carga_prospectos_log
		        (cantidad, gid, fecha_contacto)
		        VALUES
		        ('%s','%s','%s')",$cuantos,$gid,$fecha_registro);
            $resultado = $db->sql_query($sql) or die("Error<br>$sql<br>".print_r($db->sql_error()));
        }
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
	if ($rechazados){
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
                 <td>Rechazados por tener teléfono inválido</td>
                 <td>$motivo_de_rechazo_1</td>
               </tr>
               <tr class=\"row2\">
                 <td >Rechazados por ser un contacto repetido</td>
                 <td>$motivo_de_rechazo_2</td>
               </tr>
               <tr class=\"row1\">
                 <td>Rechazados por presentar un modelo de unidad inválido</td>
                 <td>$motivo_de_rechazo_3</td>
               </tr>
               <tr class=\"row2\">
                 <td>Rechazados por presentar una distribuidora inválida</td>
                 <td>$motivo_de_rechazo_4</td>
               </tr>
                <tr class=\"row2\">
                 <td>Rechazados por presentar una fecha de importado inválida</td>
                 <td>$motivo_de_rechazo_5</td>
               </tr>
                <tr class=\"row2\">
                 <td>Rechazados por presentar una fuente bloqueada</td>
                 <td>$motivo_de_rechazo_7</td>
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
      if(count($rechazados) > 0)
      {
        foreach ($rechazados AS $linea)
        {
            $motivo = $rechazados_motivo[$linea];
            $msg .= "<tr class=\"row".(($c++%2)+1)."\">
      	          <td>$linea</td>
      	          <td>$motivo</td>
      	         </tr>";
        }
      }
	}
	if ($rechazados_concesionaria){
	  $msg .= "<thead>
	           <tr>
	             <td colspan=\"2\">Distribuidoras no encontradas en la BD, requieren atención</td>
	           </tr>
			   <tr>
	             <td colspan=\"2\">Linea</td>
			   </tr>
			   </thead>";
      if(count($concesionarias) > 0)
      {
        foreach ($concesionarias AS $linea)
        {
            $linea = stripslashes($linea);
            $msg .= "<tr class=\"row".(($c++%2)+1)."\">
		           <td colspan=\"2\">$linea</td>
				 </tr>";
        }
	  }
	  $msg .= "<thead>
	           <tr>
			     <td colspan=\"2\">A continuación se agrega el segmento de carga que se debe de volver a cargar</td>
			   </tr>";
	  $msg .= "<tr>
	             <td>Total de registros: ".count($rechazados_concesionaria)."</td>
			   </tr>
			   </thead>";
      if(count($rechazados_concesionaria) > 0)
      {
        foreach ($rechazados_concesionaria AS $linea)
        {
    		$linea = stripslashes($linea);
        	$msg .= "<tr class=\"row".(($c++%2)+1)."\">
	               <td colspan=\"2\">$linea</td>
			     </tr>";
        }
	  }
  }
  /* Para los alerts*/
  $msg .= "<thead>
           <tr>
             <td colspan=\"2\">A continuación se listan las alertas</td>
           </tr>";
  $msg .= "<tr>
             <td>Total de registros: ".count($alertas_motivo)."</td>
           </tr>
           </thead>";
  if(count($alertas_motivo) > 0)
  {
    foreach ($alertas_motivo as $alerta)
    {
        $alerta["msg"] = stripslashes($alerta["msg"]);
        $msg .= "<tr class=\"row".(($c++%2)+1)."\"><td colspan=\"2\">".$alerta["msg"].
            " En la linea ".$alerta["linea"]."</td>
             </tr>";
    }
  }
  $msg .= "</table>";
  $_tabla_carga = $msg;

  $_email_headers  = 'MIME-Version: 1.0' . "\r\n";
  $_email_headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";


  $_email_from = "noreply@pcsmexico.com";
  $_email_headers .= "from:$_email_from\r\n";

  mail("orangel@pcsmexico.com", "Carga de datos de VW ".date("Y-m-d"), $msg, $_email_headers);
  mail("lahernandez@pcsmexico.com", "Carga de datos de VW ".date("Y-m-d"), $msg, $_email_headers);
  mail("otoral@pcsmexico.com", "Carga de datos de VW ".date("Y-m-d"), $msg, $_email_headers);
  mail($_email_gerente_gral, "Carga de datos de VW ".date("Y-m-d"), $msg, $_email_headers);

}
die("\n\nTotal de registros: $total_de_registros_esperados
Registros procesados: $procesados
Registros agregados: $insertados2\n\n");
?>