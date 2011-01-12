<?php
/* 
 * Las siguientes funciones calculan las horas de retraso en atencion y compromiso de un contacto
 */
/*
 * Obtiene las horas de retraso de atencion del contacto
 * @param $contact_id -> El id del contacto
 * @param $uidt -> el id del vendedor
 * @return -> int $totalHoursAsignacion
 */
function getHoursDelayAttention($db, $contact_id, $uid)
{    
    $sqlEventos = "select eventos.evento_id from  crm_campanas_llamadas as llamadas,
    crm_campanas_llamadas_eventos as eventos  where  llamadas.id=eventos.llamada_id
    and llamadas.contacto_id='$contact_id'  and eventos.uid='$uid';";

    $sqlAsignacion = "select asignacion.timestamp from unicos_log as asignacion where asignacion.contacto_id='$contact_id';";
    $totalHorasAsginacion = 0;

    $resultSqlAsignacion = $db->sql_query($sqlAsignacion) or die("Error al obtener la fecha de asignacion del contacto->".$sqlAsignacion);
    //si existe fecha de asignacion para este contacto
    if($db->sql_numrows($resultSqlAsignacion) > 0)
    {
        // comprobar si tiene eventos
        $resultSqlEventos = $db->sql_query($sqlEventos) or die("Error al obtener los eventos del contacto->".$sqlEventos);
        if($db->sql_numrows($resultSqlEventos) > 0)// si tiene eventos entonces no tiene de retardo de atencion
        $totalHorasAsginacion = 0;
        else // si no existen eventos para este contactos entonces se calcula las horas de retraso en atencion
        {
            //el timestamp de la fecha en que se asigno el contacto al vendedor
            
            list($fechaContactoAsignado) =  $db->sql_fetchrow($resultSqlAsignacion);
            $now = date('Y-m-d H:i:s');            
            $sql = "SELECT TIMESTAMPDIFF(HOUR,'$fechaContactoAsignado','$now')";//se calcula si existe un retraso
            list($diffDate) = $db->sql_fetchrow($db->sql_query($sql));
            if($diffDate < 0)
            return 0;
            else
            return $diffDate;
        }
    }
    else
    $totalHorasAsginacion = 0;
    return $totalHorasAsginacion;
}
/*
 * Obtiene el total, el maximo y el promedio de horas de retrado en compromiso de una concesionaria
 * @param int $gid -> el id de la concesionaria
 * @return Array $totatles -> array con el total, maximo y promedio en horas de retraso en atencion
 */
function getHoursDelayAttentionByGroup($db, $gid)
{
    $totales = array();
    $maximo = 0;
    $count = 0;
    $sql = "select contacto_id, uid from crm_contactos where gid='$gid'";
    $result = $db->sql_query($sql) or die("Erro al obtener los contactos de una concesionaria->".$sql);
    while(list($contactoId, $uid) = $db->sql_fetchrow($result))
    {
        $totales["HRA"] = $totoles["HRA"] + getHoursDelayAttention($db, $contactoId, $uid) ;
        if($maximo < $totales["HRA"])
        $maximo = $totales["HRA"];
        $count ++;
    }
    $totales["AVG"] = $totales["HRA"] / $count;
    $totales["MAX"] = $maximo;
    return $totales;
}
/*
 * Obtiene el total, el maximo y el promedio de horas de retrado en compromiso de una concesionaria
 * @param int $gid -> el id de la concesionaria
 * @return Array $totales *  -> array con el total, maximo y promedio en horas de retraso en atencion y compromiso
 */
function getHoursDelayAttentionAndCompromiseByGroup($db, $gid)
{
    $totalesAtencion = array();
    $totalesCompromiso = array();
    $contactoEnSeguimiento = 0;

    $maximo = 0;
    $maximoCompromiso = 0;
    $countAtencion = 0;
    $countCompromiso = 0;
    $sql = "select contacto_id, uid from crm_contactos where gid='$gid'";
    $result = $db->sql_query($sql) or die("Erro al obtener los contactos de una concesionaria->".$sql);
    while(list($contactoId, $uid) = $db->sql_fetchrow($result))
    {
        $totalesAtencion["HRA"] = $totalesAtencion["HRA"] + getHoursDelayAttention($db, $contactoId, $uid) ;
        if($maximo < $totalesAtencion["HRA"])
        $maximo = $totalesAtencion["HRA"];
        $contactoEnSeguimiento = getStatusFollowing($db, $contactoId, $uid, $gid);
        if($contactoEnSeguimiento[0])
        {
            $totalesCompromiso["HRC"] = $totalesCompromiso["HRC"] +  $contactoEnSeguimiento[1];
            if($maximoCompromiso < $totalesCompromiso["HRC"] )
            $maximoCompromiso = $totalesCompromiso["HRC"];
            $countCompromiso++;
        }
        $countAtencion ++;
    }
    if($countAtencion > 0)
    $totalesAtencion["AVG"] = $totalesAtencion["HRA"] / $countAtencion;
    $totalesAtencion["MAX"] = $maximo;
    if($countCompromiso > 0)
    $totalesCompromiso["AVG"] = $totalesCompromiso["HRC"] / $countCompromiso;
    $totalesCompromiso["MAX"] = $maximoCompromiso;
    //los totales
    $totales["HRA"] = $totalesAtencion;
    $totales["HRC"] = $totalesCompromiso;
    $totales["PS"] = $countCompromiso;
    return $totales;
}

/*
 * Obtiene las horas de retraso en compromiso de un contacto
 * @param Object $db -> conexion a la bd
 * @param int $contactId -> el id del contacto
 * @return Array ->Arreglo[0]: indica si el prospecto esta en seguimiento o no
 *               -> Arreglo[1]:las horas de retraso
 */
function getStatusFollowing($db, $contactId, $uid, $gid)
{
    //comprobar si el contactos esta en seguimiento o no
    $isSeguimiento = 0;
    $horasRetraso = 0;

    $sql = "select llamada.id as llamada_id, evento.evento_id,evento.fecha_cita,
            evento.timestamp from crm_campanas_llamadas as llamada,
            crm_campanas_llamadas_eventos as evento where llamada.id = evento.llamada_id
            and llamada.contacto_id='$contactId' and evento.uid='$uid' order by evento.fecha_cita desc limit 1";
    $result = $db->sql_query($sql) or die("Error al obtener los eventos del contacto");
    if($db->sql_numrows($result))//tiene eventos, entonces esta en seguimiento
    {
        $isSeguimiento = 1;
        list($llamadaId, $eventoId, $fechaCita,$timeStamp) = $db->sql_fetchrow($result);
        $sql = "select cierre.cierre_id from `crm_campanas_llamadas_eventos_cierres`
                as cierre, `crm_campanas_llamadas_eventos` as evento where
                cierre.evento_id= evento.evento_id and evento.evento_id='$eventoId'";
        $resultCierreId = $db->sql_query($sql);
        if(!$db->sql_numrows($resultCierreId))//si el evento no esta cerrado
        $horasRetraso = getDelayHours($db, $fechaCita);// obtener las horas de retaso del evento

    }
    return array(0 => $isSeguimiento, 1 => $horasRetraso);
}

function getDelayHours($db, $dateEvent)
{
    $now = date('Y-m-d H:i:s');
    $sql = "SELECT TIMESTAMPDIFF(HOUR,'$dateEvent','$now')";//se calcula si existe un retraso
    $result  = $db->sql_query($sql) or die ("Error al obtener las difencia");
    list($diffDate) = $db->sql_fetchrow($db->sql_query($sql));
    if($diffDate < 0)
    $diffDate = 0;
    return $diffDate;
}
function brigeFormatDayToDayHour($totalHours)
{

    $hoursForDay = 24;
    $days = "" +  ($totalHours/$hoursForDay);
    list($day,$decimalDay) = explode(".",$days);
    $decimalDay =  ((float)(".".$decimalDay))*$hoursForDay;
    return "$day d ".(int)$decimalDay." h";
}
function getPorcentAsignedContact($totalContact, $asignedContact)
{
    if($totalContact == 0)
    return 0;
    else
    return number_format((($asignedContact * 100)/$totalContact),2,".","");
}

/*
 * Para la parte de vendedor en la pantalla de seguimiento
 */
/*
 * Obtiene el total, el maximo y el promedio de horas de retrado en compromiso de un vendedor
 * @param int $uid -> el id del vendedor
 * @return Array $totales *  -> array con el total, maximo y promedio en horas de retraso en atencion y compromiso
 */
function getHoursDelayAttentionAndCompromise($db, $uid, $gid)
{
    $totalesAtencion = array();
    $totalesCompromiso = array();
    $contactoEnSeguimiento = 0;

    $maximo = 0;
    $maximoCompromiso = 0;
    $countAtencion = 0;
    $countCompromiso = 0;
    $horasRetrasoEnAtencion = 0;
    $horasRetrasoEnCompromiso = 0;
    //para la pantalla principal de seguimiento
    if($uid == 0)
    $sql = "select contacto_id, uid from crm_contactos where  gid='$gid' and uid <> 0";
    //para la pantalla de vendedores en en modulo de seguimiento
    if($uid && $gid)
    $sql = "select contacto_id, uid from crm_contactos where uid='$uid' and gid='$gid'";
    $result = $db->sql_query($sql) or die("Erro al obtener los contactos de una concesionaria->".$sql);    
    while(list($contactoId, $uid) = $db->sql_fetchrow($result))
    {
        $horasRetrasoEnAtencion = getHoursDelayAttention($db, $contactoId, $uid);

        if($maximo < $horasRetrasoEnAtencion)
        $maximo = $horasRetrasoEnAtencion;

        $totalesAtencion["HRA"] = $totalesAtencion["HRA"] +  $horasRetrasoEnAtencion;
        if($uid !=0)
        {
            $contactoEnSeguimiento = getStatusFollowing($db, $contactoId,$uid, $gid);
            if($contactoEnSeguimiento[0])
            {
                $horasRetrasoEnCompromiso = $contactoEnSeguimiento[1];
                if($maximoCompromiso < $horasRetrasoEnCompromiso )
                $maximoCompromiso = $horasRetrasoEnCompromiso;

                $totalesCompromiso["HRC"] = $totalesCompromiso["HRC"] + $horasRetrasoEnCompromiso ;
                $countCompromiso++;
            }
        }
        $countAtencion ++;
    }
    if($countAtencion > 0)
    $totalesAtencion["AVG"] = $totalesAtencion["HRA"] / $countAtencion;
    $totalesAtencion["MAX"] = $maximo;
    if($countCompromiso > 0)
    $totalesCompromiso["AVG"] = $totalesCompromiso["HRC"] / $countCompromiso;
    $totalesCompromiso["MAX"] = $maximoCompromiso;
    //los totales
    $totales["HRA"] = $totalesAtencion;
    $totales["HRC"] = $totalesCompromiso;
    $totales["PS"] = $countCompromiso;
    return $totales;
}

    /*
     * Obtiene la fecha del  primer contacto de un prospecto
     * @param $db -> conexion a la bd
     * @parma $idContact -> id del contacto
     * @return -> fecha del primer contacto
     */
function getFirstDateContact($db, $idContact)
{
    $sql = "select eventos.fecha_cita from crm_campanas_llamadas as llamadas,
        crm_campanas_llamadas_eventos as eventos where llamadas.id=eventos.llamada_id and
        llamadas.contacto_id='$idContact' order by eventos.fecha_cita asc limit 1";
    $result = $db->sql_query($sql);
    list($timestamp) = $db->sql_fetchrow($result);
    list($fecha, $hora) = explode(" ",$timestamp);
    return date_reverse($fecha);
}
function getLastDateContact($db, $idContact)
{
    $sql = "select eventos.fecha_cita from crm_campanas_llamadas as llamadas,
        crm_campanas_llamadas_eventos as eventos where llamadas.id=eventos.llamada_id and
        llamadas.contacto_id='$idContact' order by eventos.fecha_cita desc limit 1";
    $result = $db->sql_query($sql);
    list($timestamp) = $db->sql_fetchrow($result);
    list($fecha, $hora) = explode(" ",$timestamp);
    return date_reverse($fecha);
}

?>
