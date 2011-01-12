<? 
if (!defined('_IN_ADMIN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}

global $db, $submit, $fecha_ini, $fecha_fin;

//servicio
$encuesta_id = "-2";
if ($encuesta_id)
{
  $fp = fopen("encuesta$encuesta_id.csv", 'w');
  $sql = "SELECT resultado_id, contacto_id, fecha, llamada_id FROM crm_encuestas_resultados WHERE encuesta_id='$encuesta_id' ORDER BY fecha";
  $result = $db->sql_query($sql) or die("Error al consultar preguntas 1<br>$sql");
  while(list($resultado_id, $contacto_id, $fecha, $llamada_id) = $db->sql_fetchrow($result))
  { 
    list($fecha, $hora) = explode(" ", $fecha);
    $fecha = date_reverse($fecha)." $hora";
    //checar si es de la fecha que se indico
    
    $and_fecha = "";
    if ($fecha_ini)
    {
      $rango .= " desde el $fecha_ini";
      $fecha_ini = date_reverse($fecha_ini);
      $and_fecha .= " AND s.fecha>='$fecha_ini 00:00:00'";
    }
    if ($fecha_fin)
    {
      $rango .= " hasta el $fecha_fin";
      $fecha_fin = date_reverse($fecha_fin);
      $and_fecha .= " AND s.fecha<='$fecha_fin 23:59:59'";
    }
    
    $sql  = "SELECT s.fecha, s.orden, s.tipo, s.modelo, s.uid FROM crm_campanas_llamadas AS l, crm_clientes_servicios AS s WHERE l.aux_id=s.clientes_servicio_id AND l.id='$llamada_id'$and_fecha";
    $result2 = $db->sql_query($sql) or die("Error relación<br>$sql<br>".print_r($db->sql_error()));
    list($fecha_cliente, $orden, $tipo, $modelo, $asesor_uid) = $db->sql_fetchrow($result2);
    if ($fecha_cliente == "") continue;

    $sql = "SELECT name FROM users WHERE uid='$asesor_uid'";
    $result2 = $db->sql_query($sql) or die("Error asesor");
    list($asesor) = $db->sql_fetchrow($result2);
    
    $sql = "SELECT nombre, apellido_paterno, apellido_materno, tel_casa, tel_oficina, tel_movil, tel_otro FROM crm_contactos WHERE contacto_id='$contacto_id'";
    $result1 = $db->sql_query($sql) or die("Error al consultar contacto");
    list( $nombre, $apellido_paterno, $apellido_materno, $tel_casa, $tel_oficina, $tel_movil, $tel_otro ) = $db->sql_fetchrow($result1);
    $array_preguntas = array("ID", "Fecha", "Fecha de facturación", "Orden", "Nombre", "Teléfono", "Tipo", "Modelo", "Asesor");
    if ($tel_casa) $telefono = $tel_casa;
    else if ($tel_oficina) $telefono = $tel_oficina;
    else if ($tel_movil) $telefono = $tel_movil;
    else $telefono = $tel_otro;
    $array_respuestas = array($resultado_id, $fecha, $fecha_cliente, $orden, "$apellido_paterno $apellido_materno $nombre", "$telefono", "$tipo", $modelo, $asesor);
    $sql = "SELECT pregunta_id, pregunta, tipo_id, observacion FROM crm_encuestas_preguntas WHERE encuesta_id='$encuesta_id' ORDER BY orden";
    $result1 = $db->sql_query($sql) or die("Error al consultar preguntas 2");
    while(list($pregunta_id, $pregunta, $tipo_id, $observacion) = $db->sql_fetchrow($result1))
    {

      
      switch($tipo_id)
      {
        case "1": //si no
                  $sql = "SELECT respuesta FROM crm_encuestas_respuestas_tipo_1 AS t WHERE t.pregunta_id='$pregunta_id' AND resultado_id='$resultado_id'";
                  $result2 = $db->sql_query($sql) or die("Error al consultar preguntas tipo<br>".print_r($db->sql_error()));
                  list($respuesta) = $db->sql_fetchrow($result2);
                    if ($respuesta)
                      $r = "VERDADERO";
                    else
                      $r = "FALSO";
                    $array_respuestas[] = "$r";

                  break;
        case "2": //opcion múltiple
                  $sql = "SELECT respuesta FROM crm_encuestas_respuestas_tipo_2 AS t WHERE t.pregunta_id='$pregunta_id' AND resultado_id='$resultado_id'";
                  $result2 = $db->sql_query($sql) or die("Error al consultar preguntas tipo<br>".print_r($db->sql_error()));
                  list($respuesta) = $db->sql_fetchrow($result2);

                  $sql = "SELECT opcion, valor FROM crm_encuestas_preguntas_tipo_2 WHERE opcion_id='$respuesta' LIMIT 1";
                  $result3 = $db->sql_query($sql) or die("Error al consultar preguntas tipo<br>".print_r($db->sql_error()));
                  list($respuesta, $valor) = $db->sql_fetchrow($result3);
                  if ($pregunta_id == 48)
                    $array_respuestas[] = "$respuesta"; //el area
                  else
                    $array_respuestas[] = "$valor"; //todos los demás el valor

                  break;
        case "3": //abierta
                  //solo hay 2 abiertas que exportamos, checar si es alguna (cuanto tardo)
                  if ($pregunta_id == 6) break;
                  $sql = "SELECT respuesta FROM crm_encuestas_respuestas_tipo_3 AS t WHERE t.pregunta_id='$pregunta_id' AND resultado_id='$resultado_id'";
                  $result2 = $db->sql_query($sql) or die("Error al consultar preguntas tipo<br>".print_r($db->sql_error()));
                  list($respuesta) = $db->sql_fetchrow($result2);

                  $array_respuestas[] = "$respuesta";

                  break;
        case "4": //selección múltiple
                  $sql = "SELECT respuesta FROM crm_encuestas_respuestas_tipo_4 AS t WHERE t.pregunta_id='$pregunta_id' AND resultado_id='$resultado_id'";
                  $result2 = $db->sql_query($sql) or die("Error al consultar preguntas tipo<br>".print_r($db->sql_error()));
                  $index_resp = 0;
                  $respuestas = "";
                  while (list($respuesta) = $db->sql_fetchrow($result2))
                  {
                    $sql = "SELECT opcion FROM crm_encuestas_preguntas_tipo_4 WHERE opcion_id='$respuesta' LIMIT 1";
                    $result3 = $db->sql_query($sql) or die("Error al consultar preguntas tipo<br>".print_r($db->sql_error()));
                    list($respuesta) = $db->sql_fetchrow($result3);
                    if ($index_resp++) $respuestas .= ", ";
                    $respuestas .= "$respuesta";
                  }
//                   $array_respuestas[] = "$respuestas";
                  break;
        case "5": //usuario
                  $sql = "SELECT respuesta FROM crm_encuestas_respuestas_tipo_5 AS t WHERE t.pregunta_id='$pregunta_id' AND resultado_id='$resultado_id'";
                  $result2 = $db->sql_query($sql) or die("Error al consultar preguntas tipo<br>".print_r($db->sql_error()));
                  list($respuesta) = $db->sql_fetchrow($result2);

                  $sql = "SELECT name FROM users WHERE uid='$respuesta' LIMIT 1";
                  $result3 = $db->sql_query($sql) or die("Error al consultar preguntas tipo 5<br>".print_r($db->sql_error()));
                  list($respuesta) = $db->sql_fetchrow($result3);
//                   $array_respuestas[] = "$respuesta";

                  break;
        case "6": //status
                  $sql = "SELECT respuesta FROM crm_encuestas_respuestas_tipo_6 AS t WHERE t.pregunta_id='$pregunta_id' AND resultado_id='$resultado_id'";
                  $result2 = $db->sql_query($sql) or die("Error al consultar preguntas tipo<br>".print_r($db->sql_error()));
                  list($respuesta) = $db->sql_fetchrow($result2);

                  $sql = "SELECT opcion, opcion_id FROM crm_encuestas_preguntas_tipo_6 WHERE opcion_id='$respuesta' LIMIT 1";
                  $result3 = $db->sql_query($sql) or die("Error al consultar preguntas tipo<br>".print_r($db->sql_error()));
                  list($respuesta, $opcion_id) = $db->sql_fetchrow($result3);
                  if ($opcion_id != "2" && $opcion_id != "")//diferente de -
                      $r = "VERDADERO";
                  else
                      $r = "FALSO";
                  $array_respuestas[] = "$r";
//                   $array_respuestas[] = "$respuesta";

                  break;
      }//switch
      //observaciones
      if (!($index_header_preguntas)) 
          if ((count($array_preguntas)) == (count($array_respuestas)-1))
            $array_preguntas[] = "$pregunta";

      if ($observacion)
      {
          if (!($index_header_preguntas)) $array_preguntas[] = "Observaciones";
          $sql = "SELECT observacion FROM crm_encuestas_respuestas_observaciones WHERE resultado_id='$resultado_id' AND pregunta_id='$pregunta_id'";
          $result2 = $db->sql_query($sql) or die("Error al consultar observaciones<br>".print_r($db->sql_error()));
          list($observacion_r) = $db->sql_fetchrow($result2);
          $array_respuestas[] = "$observacion_r";
      }
      
      //agregar G. I. E si es casi el final
      if ($pregunta_id == 47)
      {
        if (!($index_header_preguntas)) 
          $array_preguntas[] = "Elaborado por";
        $array_respuestas[] = "G.I.E";
      }
    }//while preguntas

    
    if (!($index_header_preguntas++)) fputcsv($fp, $array_preguntas);
    fputcsv($fp, $array_respuestas);
  }
  fclose($fp);
  header("location:encuesta$encuesta_id.csv");
}
else
{

}

?> 
