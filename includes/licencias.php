<?php
global $db,$_licenses,$_menu_c,$fecha_sistema,$msg_ciclo;

$mostrar=0;
$msg_ciclo='';
if (isset($_COOKIE['_admin_id']))
{
    $sql="SELECT COUNT(*) FROM users WHERE active=1";
    $res=$db->sql_query($sql) or die("Error en el count de licencias:  ".$sql);
    list($_licenses_used) = $db->sql_fetchrow($res);
    $_licenses_not_used=($_licenses - $_licenses_used);

    $sql="SELECT COUNT(*) FROM crm_campanas  ;";
    $res=$db->sql_query($sql) or die("Error en el count de campanas:  ".$sql);
    list($_etapas) = $db->sql_fetchrow($res);

    $sql="SELECT COUNT(*) FROM groups WHERE active =1 ;";
    $res=$db->sql_query($sql) or die("Error en el count de groups:  ".$sql);
    list($_distribuidoras) = $db->sql_fetchrow($res);

    $sql="SELECT COUNT(*) FROM crm_unidades WHERE active =1 ;";
    $res=$db->sql_query($sql) or die("Error en el count de productos:  ".$sql);
    list($_productos) = $db->sql_fetchrow($res);

    $sql="SELECT COUNT(*) FROM crm_fuentes WHERE fuente_id > 1 AND active =1 ;";
    $res=$db->sql_query($sql) or die("Error en el count de fuentes:  ".$sql);
    list($_fuentes) = $db->sql_fetchrow($res);

    $_menu=0;
    if( ($_etapas > 0) && ($_distribuidoras > 0) && ($_productos > 0) && ($_fuentes > 0))
        $_menu_c=1;

    if($_etapas == 0)
        $no_module=1;
    if( ($_etapas > 0) && ($_productos == 0) )
        $no_module=2;
    if( ($_etapas > 0) && ($_productos > 0) && ($_fuentes == 0))
        $no_module=3;
    if( ($_etapas > 0) && ($_productos > 0) && ($_fuentes > 0) && ($_distribuidoras == 0))
        $no_module=4;


    switch($no_module)
    {
        case 1:
            $current_actual=3;
            $url="index.php?_module=Campanas";
            $mostrar=1;
            break;
        case 2:
            $current_actual=4;
            $url="index.php?_module=Modelos";
            $mostrar=1;
            break;
        case 3:
            $current_actual=5;
            $url="index.php?_module=Catalogos";
            $mostrar=1;
            break;
        case 4:
            $current_actual=6;
            $url="index.php?_module=Concesionarias&_op=new";
            $mostrar=1;
            break;

    }
    if ($mostrar == 1 )
    {
        $array=array('Activación','Registro','Personalización','Ciclo de venta','Productos','Origenes','Distribuidores');
        $contador=0;
        $msg_ciclo="<table width='95%' align='center' border='0'>
        <tr><td colspan='7' align=center><b>Para continuar, por favor de clic en la etapa señalada por la flecha</b></td></tr>
        <tr></tr>
        <tr></tr>
        <tr></tr>
        <tr></tr>
        <tr>";
        foreach($array as $clave => $etapa)
        {
            $tmp_url=$etapa;
            if($clave < $current_actual)
            {
                $tmp="current_bar";
                $img='<img src="../img/icon-check.png" width="16" height="16">';
            }
            if($clave == $current_actual)
            {
                $tmp="current_bar";
                $img='<img src="../img/right_arrow.gif" width="16" height="16">';
                $tmp_url="<a href='".$url."'>".$etapa."</a>";
            }
            if($clave > $current_actual)
            {
                $tmp='active_bar';
                $img='';
            }
            if($clave == (count($array) -1))
            {
                $temp='';
                $img='';
            }
            if($clave == $current_actual)
            {
                if($clave == (count($array) -1))
                {
                    $img='<img src="../img/right_arrow.gif" width="16" height="16">';
                }
            }
            
            $msg_ciclo.="<td align='right'>".$temp."<table align='center'><tr><td>
                <div class='".$tmp."'>
                <div class='circle_number'>".($contador + 1)."</div>
                <div class='text'>".$tmp_url."</div></td><td>
                </div>".$img."</td></tr>
                
                </table></td>"                
                ;
            
            $contador++;
        }
        $msg_ciclo.="</tr></table>";
    }
}
?>