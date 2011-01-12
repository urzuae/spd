<?php
require_once("$_includesdir/mail.php");
global $db;

$debugMsg = "";
$hace_8_horas = time() - (8 * 60 * 60);
$hace_24_horas = time() - (24 * 60 * 60);
$hace_48_horas = time() - (48 * 60 * 60);

//si es viernes o sábado, ampliar los rangos de tiempo
$dia_de_la_semana = date('w');
if ($dia_de_la_semana == '5' || $dia_de_la_semana == '6')
{
    $hace_24_horas = time() - (48 * 60 * 60);
    $hace_48_horas = time() - (72 * 60 * 60);
}
$uids = array();
//buscar a todos los vendedores
$sql = "SELECT uid, gid, name, email FROM users WHERE super='8'";
$sql .= $sql_extra_gid_constraints;
$r = $db->sql_query($sql) or die("Error");
$cuantos = $db->sql_numrows($r);
echo"\nTotal de Vendedores :   ".$cuantos;
sleep(3);

while(list($uid, $gid, $name, $email) = $db->sql_fetchrow($r))
{
    $date=date("Y-m-d H:i:s");
    echo "\n=$uid\n";
    $no_segumiento=0;
    $no_asignacion=0;
    $tmp_status='';
    //primero checar si el vendedor tiene prospectos asignados

    $sql = "SELECT contacto_id FROM crm_contactos WHERE uid='$uid';";
    $r2 = $db->sql_query($sql) or die("Error");
    $cuantos_contactos_asignados = $db->sql_numrows($r2);
    if ($cuantos_contactos_asignados) //si no tiene nada no vale la pena hacer nada
    {
        $contactos=array();
        $uids[] = $uid;
        $gids[$uid] = $gid;
        $names[$uid] = $name;
        $emails[$uid] = $email;
        $contador=1;
        while(list($contacto_id) = $db->sql_fetchrow($r2))
        {
            
            $contactos[$contacto_id]=$origen_id;
            $status_id=Revisa_Status($db,$contacto_id);
            if ( ($status_id== 0) || ($status_id == 1000) )
            {
                // Checamos en los logs para saber si ya tuvo alguna fecha de cita
                if(Revisa_Log_Llamadas($db,$contacto_id,$uid))
                {
                    // la fecha de retraso la sacamos de su ultima fecha de cita
                    $no_segumiento++;
                    $tmp_status='seg con log';
                    $ts_ultimo_contacto=Calcula_Tiempo_Retraso_Seguimiento_Log($db,$contacto_id);
                    if ($ts_ultimo_contacto < $hace_48_horas)
                        $contactos_seguimiento_mas_de_48[$uid][] = $contacto_id;
                    elseif ($ts_ultimo_contacto < $hace_24_horas)
                        $contactos_seguimiento_mas_de_24[$uid][] = $contacto_id;
                    elseif ($ts_ultimo_contacto < $hace_8_horas)
                        $contactos_seguimiento_mas_de_8[$uid][] = $contacto_id;
                }
                else
                {
                    $tmp_status='asig';
                    $no_asignacion++;
                    $ts_asignacion=Calcula_Tiempo_Retraso_Asignacion($db,$contacto_id);
                    if ($ts_asignacion < $hace_48_horas)
                        $contactos_mas_de_48[$uid][] = $contacto_id."  ".$tmp_fecha;
                    elseif ($ts_asignacion < $hace_24_horas)
                        $contactos_mas_de_24[$uid][] = $contacto_id."  ".$tmp_fecha;
                    elseif ($ts_asignacion < $hace_8_horas)
                        $contactos_mas_de_8[$uid][] = $contacto_id."  ".$tmp_fecha;
                }
            }
            else
            {
                $no_segumiento++;
                if($status_id == -2)
                {
                    $tmp_status='seg sin log';
                    $ts_fecha_cita=Calcula_Tiempo_Retraso_Seguimiento($db,$contacto_id);
                    if ($ts_fecha_cita < $hace_48_horas)
                        $contactos_seguimiento_mas_de_48[$uid][] = $contacto_id;
                    elseif ($ts_fecha_cita < $hace_24_horas)
                        $contactos_seguimiento_mas_de_24[$uid][] = $contacto_id;
                    elseif ($ts_fecha_cita < $hace_8_horas)
                        $contactos_seguimiento_mas_de_8[$uid][] = $contacto_id;
                }
                elseif($status_id == 1)
                {
                    $tmp_status='seg con log';
                    $ts_ultimo_contacto=Calcula_Tiempo_Retraso_Seguimiento_Log($db,$contacto_id);
                    if ($ts_ultimo_contacto < $hace_48_horas)
                        $contactos_seguimiento_mas_de_48[$uid][] = $contacto_id;
                    elseif ($ts_ultimo_contacto < $hace_24_horas)
                        $contactos_seguimiento_mas_de_24[$uid][] = $contacto_id;
                    elseif ($ts_ultimo_contacto < $hace_8_horas)
                        $contactos_seguimiento_mas_de_8[$uid][] = $contacto_id;
                }
            }
            #echo"\n".$contador."         Contacto:   ".$contacto_id."    origen_id  ".$origen_id."   status:   ".$status_id."    ".$tmp_status;
            $contador++;
        }
    }
}

//obtener emails de los gerentes CRM
$sql = "SELECT uid, gid, name, email FROM users WHERE super='4'";
$sql .= $sql_extra_gid_constraints;
$r = $db->sql_query($sql) or die("Error");
$cuantos = $db->sql_numrows($r);
while(list($uid, $gid, $name, $email) = $db->sql_fetchrow($r))
{
    $email_gte_crms[$gid] = $email;
}

//obtener emails de los gerentes de ventas
$sql = "SELECT uid, gid, name, email FROM users WHERE super='6'";
$sql .= $sql_extra_gid_constraints;
$r = $db->sql_query($sql) or die("Error");
$cuantos = $db->sql_numrows($r);
while(list($uid, $gid, $name, $email) = $db->sql_fetchrow($r))
{
    $email_gte_vtass[$gid] = $email;
}


//informativo son menos de 8 horas
//preventivo es 8 horas
//correctivo son 24 horas
//retiro es a las 48 horas
//ahora ya tenemos los contactos
foreach ($uids AS $uid) //solo los que tienen contactos
{
    sleep(5);
    echo"\n".$uid;
    $email = $emails[$uid];
    $name = $names[$uid];
    $gid = $gids[$uid];

    $email_gte_crm = $email_gte_crms[$gid];
    $email_gte_vtas = $email_gte_vtass[$gid];
    $cuantos_contactos_mas_de_8   = count($contactos_mas_de_8[$uid]);
    $cuantos_contactos_mas_de_24  = count($contactos_mas_de_24[$uid]);
    $cuantos_contactos_mas_de_48  = count($contactos_mas_de_48[$uid]);

    $cuantos_contactos_seguimiento_mas_de_8   = count($contactos_seguimiento_mas_de_8[$uid]);
    $cuantos_contactos_seguimiento_mas_de_24  = count($contactos_seguimiento_mas_de_24[$uid]);
    $cuantos_contactos_seguimiento_mas_de_48  = count($contactos_seguimiento_mas_de_48[$uid]);
    print_r($contactos_seguimiento_mas_de_48[$uid]);
    echo"\nuid:  ".$uid;
    echo"\n\tProspectos en asignacion:      ".$no_asignacion;
    echo"\n";
    echo"\ncuantos_contactos_mas_de_8       ".$cuantos_contactos_mas_de_8;
    echo"\ncuantos_contactos_mas_de_24      ".$cuantos_contactos_mas_de_24;
    echo"\ncuantos_contactos_mas_de_48      ".$cuantos_contactos_mas_de_48;
    echo"\n\n\n";
    echo"\n\tProspectos en seguimiento:  ".$no_segumiento;
    echo"\n";
    echo"\ncuantos_contactos_seguimiento_mas_de_8     ".$cuantos_contactos_seguimiento_mas_de_8;
    echo"\ncuantos_contactos_seguimiento_mas_de_24    ".$cuantos_contactos_seguimiento_mas_de_24;
    echo"\ncuantos_contactos_seguimiento_mas_de_48    ".$cuantos_contactos_seguimiento_mas_de_48;
    echo"\n\n\n";
    if ($cuantos_contactos_mas_de_8)
    {
        $msg = "$name, usted tiene $cuantos_contactos_mas_de_8 prospectos asignados y no les ha dado seguimiento desde hace mas de 8 horas.";
        @mail("$email", "$msg", "$msg.", $_email_headers);
        echo $msg."\n";
    }
    if ($cuantos_contactos_mas_de_24)
    {
        $msg = "$name, usted tiene $cuantos_contactos_mas_de_24 prospectos asignados y no les ha dado seguimiento desde hace mas de 24 horas. Es posible que se le retire";
        $msg_gte = "El vendedor $name tiene $cuantos_contactos_mas_de_24 prospectos asignados y no los ha tratado desde hace mas de 24 horas";
        @mail("$email", "$msg", "$msg.", $_email_headers);
#        @mail("$email_gte_crm", "e-mail correctivo del vendedor $name", "$msg_gte.", $_email_headers);
        @mail("$email_gte_vtas", "e-mail correctivo del vendedor $name", "$msg_gte.", $_email_headers);
        echo $msg."\n";
    }
    if ($cuantos_contactos_mas_de_48)
    {
        $contactos_desasignar = array();
        $contactos_no_desasignar = array();
        $cuantos_no_desasignar = $cuantos_desasignar = 0;
        $msg_no_desasignar = $msg_desasignar = "";
        foreach ($contactos_mas_de_48[$uid]  AS $contacto_id)
        {
            //reviso que la fuente el valor de la fuente padre
//            $padre_id=Revisa_Fuente($db,$contacto_id);
            $padre_id=1;
            if ($padre_id == 1)
            {
                $contactos_no_desasignar[]= $contacto_id;
            }
            elseif($padre_id == 2)
            {
                $contactos_desasignar[] = $contacto_id;
            }
        }
        $cuantos_desasignar = count($contactos_desasignar);
        $cuantos_no_desasignar = count($contactos_no_desasignar);
        if ($cuantos_desasignar)
        {
            $msg_desasignar = " $cuantos_desasignar prospectos le fueron retirados y fue penalizado con $cuantos_desasignar puntos.";
            foreach ($contactos_desasignar AS $contacto_id)    //ahora quitarselo y desasignar y penalizar
            {
                $sql = "UPDATE crm_contactos SET uid='0' WHERE contacto_id='$contacto_id'";
                $db->sql_query($sql) or die($sql);
                $sql = "UPDATE users SET score=score-1 WHERE uid='$uid'";
                $db->sql_query($sql) or die($sql);
                $sql = "INSERT INTO users_penalties (uid,uid_super,score) VALUES('$uid','0','-1')";
                $db->sql_query($sql) or die($sql);
                $sql = "INSERT INTO `crm_contactos_asignacion_log` (contacto_id,uid,from_uid,to_uid)VALUES('$contacto_id','0','$uid','0')";
                $db->sql_query($sql) or die($sql);
            }
        }
        if ($cuantos_no_desasignar)
        {
            $msg_no_desasignar = " $cuantos_no_desasignar prospectos se preservaron por ser personales.";
        }

        $msg = "$name $uid, usted tenía $cuantos_contactos_mas_de_48 prospectos sin tratar y no les había dado seguimiento desde hace mas de 48 horas.$msg_desasignar$msg_no_desasignar\n";
        $msg_gte = "El vendedor $name tenía $cuantos_contactos_mas_de_48 prospectos sin tratar y no les había dado seguimiento desde hace mas de 48 horas.$msg_desasignar$msg_no_desasignar\n";
        $msg_gral .= "El vendedor $name de la distribuidora tenía $cuantos_contactos_mas_de_48 prospectos sin tratar y no les había dado seguimiento desde hace mas de 48 horas.$msg_desasignar$msg_no_desasignar\n";
        //enviar correo correctivo
        @mail("$email", "$msg", "$msg.", $_email_headers);
        #@mail("$email_gte_crm", "e-mail de retiro de prospectos en seguimiento del vendedor $name", "$msg_gte.", $_email_headers);
        @mail("$email_gte_vtas", "e-mail de retiro de prospectos en seguimiento del vendedor $name", "$msg_gte.", $_email_headers);
        echo $msg."\n";

    }

    if ($cuantos_contactos_seguimiento_mas_de_8)
    {
        $msg = "$name, usted tiene $cuantos_contactos_seguimiento_mas_de_8 prospectos en seguimiento y no les ha dado seguimiento desde hace mas de 8 horas.";
        @mail("$email", "$msg", "$msg.", $_email_headers);
        echo $msg."\n";
    }
    if ($cuantos_contactos_seguimiento_mas_de_24)
    {
        $msg = "$name, usted tiene $cuantos_contactos_seguimiento_mas_de_24 prospectos en seguimiento y no les ha dado seguimiento desde hace mas de 24 horas. Es posible que se le retire";
        $msg_gte = "El vendedor $name tiene $cuantos_contactos_seguimiento_mas_de_24 prospectos en seguimiento y no los ha tratado desde hace mas de 24 horas.";
        @mail("$email", "$msg", "$msg.", $_email_headers);
        #@mail("$email_gte_crm", "e-mail correctivo del vendedor $name", "$msg_gte.", $_email_headers);
        @mail("$email_gte_vtas", "e-mail correctivo del vendedor $name", "$msg_gte.", $_email_headers);
        echo $msg."\n";
    }
    if ($cuantos_contactos_seguimiento_mas_de_48)
    {
        $contactos_desasignar = array();
        $contactos_no_desasignar = array();
        $cuantos_no_desasignar = $cuantos_desasignar = 0;
        $msg_no_desasignar = $msg_desasignar = "";
        foreach ($contactos_seguimiento_mas_de_48[$uid]  AS $contacto_id)
        {
            //$padre_id=Revisa_Fuente($db,$contacto_id);
            $padre_id=1;
            if ($padre_id == 1)     $contactos_no_desasignar[]= $contacto_id;
            elseif($padre_id == 2)  $contactos_desasignar[] = $contacto_id;
        }
        $cuantos_desasignar = count($contactos_desasignar);
        $cuantos_no_desasignar = count($contactos_no_desasignar);
        if ($cuantos_desasignar)
        {
            $msg_desasignar = " $cuantos_desasignar prospectos le fueron retirados y fue penalizado con $cuantos_desasignar puntos.";
            foreach ($contactos_desasignar AS $contacto_id)    //ahora quitarselo y desasignar y penalizar
            {
                $sql = "UPDATE crm_contactos SET uid='0' WHERE contacto_id='$contacto_id'";
                $db->sql_query($sql) or die($sql);
                $sql = "UPDATE users SET score=score-1 WHERE uid='$uid'";
                $db->sql_query($sql) or die($sql);
                $sql = "INSERT INTO users_penalties (uid,uid_super,score) VALUES('$uid','0','-1')";
                $db->sql_query($sql) or die($sql);
                $sql = "INSERT INTO `crm_contactos_asignacion_log` (contacto_id,uid,from_uid,to_uid)VALUES('$contacto_id','0','$uid','0')";
                $db->sql_query($sql) or die($sql);
            }
        }
        if ($contactos_no_desasignar)
        {
            $msg_no_desasignar = " $cuantos_no_desasignar prospectos se preservaron por ser personales.";
        }
        $msg = "$name $uid, usted tenía $cuantos_contactos_seguimiento_mas_de_48 prospectos en seguimiento y no les había dado seguimiento desde hace mas de 48 horas.$msg_desasignar$msg_no_desasignar\n";
        $msg_gte = "El vendedor $name tenía $cuantos_contactos_seguimiento_mas_de_48 prospectos en seguimiento y no les había dado seguimiento desde hace mas de 48 horas.$msg_desasignar$msg_no_desasignar\n";
        $msg_gral .= "El vendedor $name de la distribuidora tenía $cuantos_contactos_seguimiento_mas_de_48 prospectos en seguimiento y no les había dado seguimiento desde hace mas de 48 horas.$msg_desasignar$msg_no_desasignar\n";
        @mail("$email", "$msg", "$msg.", $_email_headers);
        #@mail("$email_gte_crm", "e-mail de retiro de prospectos en seguimiento del vendedor $name", "$msg_gte.", $_email_headers);
        @mail("$email_gte_vtas", "e-mail de retiro de prospectos en seguimiento del vendedor $name", "$msg_gte.", $_email_headers);
        echo $msg."\n";
    }
}
echo $_email_gerente_gral;
if ($msg_gral)
{
    @mail("$_email_gerente_gral", "e-mail de retiro de prospectos", "$msg_gral.", $_email_headers);
    @mail("orangel@pcsmexico.com", "CRM PCS. Email de retiro de prospectos", "$msg_gral.", $_email_headers);
    @mail("lahernandez@pcsmexico.com", "CRM PCS. Email de retiro de prospectos", "$msg_gral.", $_email_headers);
}
else
{
    @mail("orangel@pcsmexico.com", "CRM PCS. No hubo retiro de prospectos de vendedores", "", $_email_headers);
    @mail("lahernandez@pcsmexico.com", "CRM PCS. No hubo retiro de prospectos de vendedores", "", $_email_headers);
}

// funciones auxiliares
function recupera_padres($db,$origen)
{
    $sql_tmp="SELECT padre_id,hijo_id FROM `crm_fuentes_arbol`WHERE hijo_id ='".$origen."';";
    $res_tmp=$db->sql_query($sql_tmp);
    if($db->sql_numrows($res_tmp) > 0)
    {
        $tmp_padre=$db->sql_fetchfield(0, 0, $res_tmp);
        $tmp_hijo=$db->sql_fetchfield(1, 0, $res_tmp);
        if($tmp_padre != 0)
        {
           $tmp_padre=recupera_padres($db,$tmp_padre);
           $id_padre=$tmp_padre;
        }
        else
        {
            $id_padre=$tmp_hijo;
        }
    }
    return $id_padre;
}

function Revisa_Status($db,$contacto_id)
{
    $status=1000;
    $sql_1 = "SELECT contacto_id,status_id FROM crm_campanas_llamadas WHERE contacto_id='$contacto_id'";
    $res_1 = $db->sql_query($sql_1) or die("Error");
    if ($db->sql_numrows($res_1) > 0)
    {
        $status=$db->sql_fetchfield(1,0,$res_1);
    }
    return $status;
}

function Revisa_Log_Llamadas($db,$contacto_id,$uid)
{
    $reg=false;
    $sql_2 = "SELECT contacto_id FROM crm_campanas_llamadas_log WHERE contacto_id='$contacto_id' AND uid='$uid';";
    $res_2 = $db->sql_query($sql_2) or die("Error");
    if ($db->sql_numrows($res_2) > 0)
    {
        $reg=true;
    }
    return $reg;
}
function Calcula_Tiempo_Retraso_Asignacion($db,$contacto_id)
{
    $tiempo=0;
    $sql_3  = "SELECT UNIX_TIMESTAMP(timestamp), timestamp FROM `crm_contactos_asignacion_log` WHERE contacto_id='$contacto_id' AND to_uid='$uid' ORDER BY timestamp DESC LIMIT 1"; //checar la última vez que se asigno
    $res_3 = $db->sql_query($sql_3) or die("Error:  ".$sql_3);
    if($db->sql_numrows($res_3) > 0)
    {
        $tiempo=$db->sql_fetchfield(0,0,$res_3);
    }
    #list($ts_asignacion, $ts) = $db->sql_fetchrow($r4);
    return $tiempo;
}
function Calcula_Tiempo_Retraso_Seguimiento($db,$contacto_id)
{
    $tiempo=0;
    $sql_4 = "SELECT UNIX_TIMESTAMP(fecha_cita),fecha_cita FROM `crm_campanas_llamadas` WHERE contacto_id='$contacto_id'";
    $res_4 = $db->sql_query($sql_4) or die("Error   ".$sql_4);
    if($db->sql_numrows($res_4) > 0)
    {
        $tiempo=$db->sql_fetchfield(0,0,$res_4);
    }
    #list($status_id, $ts_fecha_cita,$fecha_cita) = $db->sql_fetchrow($res_4);
    return $tiempo;
}
function Calcula_Tiempo_Retraso_Seguimiento_Log($db,$contacto_id)
{
    $tiempo=0;
    $sql_5 = "SELECT UNIX_TIMESTAMP(timestamp),timestamp FROM `crm_campanas_llamadas_log` WHERE contacto_id='$contacto_id' ORDER BY timestamp DESC LIMIT 1"; //checar la última vez que se asigno
    $res_5 = $db->sql_query($sql_5) or die("Error:  ".$sql_5);
    if($db->sql_numrows($res_5) > 0)
    {
        $tiempo=$db->sql_fetchfield(0,0,$res_5);
    }
    #list($ts_ultimo_contacto,$timestamp) = $db->sql_fetchrow($r4);
    return $tiempo;
}
function Revisa_Fuente($db,$contacto_id)
{
    $padre_id=0;
    $origen_id=0;
    $sql_6 = "SELECT origen_id FROM crm_contactos WHERE contacto_id='$contacto_id';";
    $res_6 = $db->sql_query($sql_6) or die("ERROR:   ".$sql_6);
    if($db->sql_numrows($res_5) > 0)
    {
        $origen_id=$db->sql_fetchfield(0, 0,$res_6);
        $padre_id=recupera_padres($db,$origen_id);
    }
    return $padre_id;
}
?>