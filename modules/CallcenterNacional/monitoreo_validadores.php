<?php
global $db,$_site_title;

$_site_title = "Monitoreo";
$style1 = "row1";
$style2 = "row2";


//ORDEN
$link = "index.php?_module=CallcenterNacional&_op=monitoreo_validadores";

$orderby_ID = "$link&orderby=ID";
$orderby_user = "$link&orderby=user";
$orderby_name = "$link&orderby=name";

if($_REQUEST["order_ID"] == "desc") $orderby_ID .= "&order_ID=asc"; else $orderby_ID .= "&order_ID=desc";
if($_REQUEST["order_user"] == "desc") $orderby_user .= "&order_user=asc"; else $orderby_user .= "&order_user=desc";
if($_REQUEST["order_name"] == "desc") $orderby_name .= "&order_name=asc"; else $orderby_name .= "&order_name=desc";

switch($_REQUEST["orderby"])
{
    case "ID":
        $order = "order by uid ".$_REQUEST["order_ID"];
        break;
    case "user":
        $order = "order by user ".$_REQUEST["order_user"];
        break;
    case "name":
        $order = "order by name ".$_REQUEST["order_name"];
        break;
    default:
        $order = "order by uid asc";
        break;
}


$sql = "select uid, user, name from users where super in(9,10) and gid = '0001' $order";
$cs = $db->sql_query($sql);
$cant = $db->sql_affectedrows($cs);
while(list($uid,$user,$name) = $db->sql_fetchrow($cs))
{
    if($style == $style1)
        $style = $style2;
    else
        $style = $style1;

    $sql2 = 'SELECT count(user_id)
    FROM crm_campanas_llamadas_no_asignados na inner join crm_contactos_no_asignados c on na.contacto_id=c.contacto_id
    WHERE user_id=\''.$uid.'\' and intentos > 0 
    ORDER BY na.contacto_id, na.timestamp  ASC';
    $cs2 = $db->sql_query($sql2);
    list($uids) = $db->sql_fetchrow($cs2);
    
    if($uids <= 0){
    	$sql2 = "SELECT count(log_id) FROM crm_contactos_no_asignados_log where uid = '$uid'";
    	$cs2 = $db->sql_query($sql2);
    	list($uids) = $db->sql_fetchrow($cs2);
    }

    $_listado_validadores .= "<tr><td class=\"$style\">$uid</td><td class=\"$style\">$user</td><td class=\"$style\">$name</td><td class=\"$style\">";
    if($uids > 0)
        $_listado_validadores .= "<center><a href=\"index.php?_module=CallcenterNacional&_op=monitoreo_validadores_prospectos&uid=$uid\"><img src=\"img/search.png\" border=\"0\"></a></center>";
    $_listado_validadores .= "</td></tr>";
    unset($uids);
}
?>
