<?php
  if (!defined('_IN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}
global $db, $uid, $id_user,$_includesdir,$_modulesdir,$ano_id,$buscar_proyeccion;
include_once("funcion_metas.php");
$select_ano  = Regresa_Combo_Anos($ano_id);

if($ano_id == '') $ano_id=date('Y');

$id_user=0;
$filtro='';
$sql  = "SELECT gid, super FROM users WHERE uid='$uid'";
$result = $db->sql_query($sql) or die("Error");
list($gid, $super) = $db->sql_fetchrow($result);
if ($super == "6")
{
    if($ano_id != '')
    {
        if($super == 8) $id_user=$uid;   #asigna a id_user el id del vendedor en caso que lo sea

        $array_vendedores=Regresa_Uids_Vendedores($db,$gid,$id_user);  #recupero los ids de los vendedores de la concesionaria
        $array_nms_vendedores=Regresa_Vendedores($db,$gid,$id_user);   #recupero los nombrede de los vendedeores de la con
        $array_meses=Regresa_Array_Meses();  #array con meses

        $buffer.="<hr><br><p align='center' class='title'>
                   Proyecci&oacute;n mensual del a&ntilde;o ".$ano_id."</p><br>
                   <table border='0' align='center' class='tablesorter' width='100%'>
                    <thead><tr height='30'>
                    <th width='9%'>Vendedor</th>";
        foreach($array_meses as $id_mes => $nm_mes)   #Pinto encabezados por mes
        {
            $buffer.="<th>".substr($nm_mes,0,3)."</th>";
        }
        $buffer.="<th>Total</th><th>Eliminar</th></tr></thead><tbody>";

        #recorro todos los vendedores
        $array_totales=array();
        foreach($array_vendedores as $_uid)
        {
            $total=0;
            $array_datos=Recupera_proyeccion($db,$gid,$_uid,$ano_id);  # veo si tiene asignadas metas
            if(count($array_datos) > 0)
            {
                $array_metas[$_uid]=normaliza_a_meses_totales($array_datos);
                $buffer.="<tr class='row2'><td>".$array_nms_vendedores[$_uid]."</td>";
                foreach($array_meses as $mes_id => $valor)
                {
                    $mes_id=$mes_id + 0 ;
                    $total=$total + $array_metas[$_uid][$mes_id];
                    $array_totales[$mes_id] = $array_totales[$mes_id] + $array_metas[$_uid][$mes_id];
                    $buffer.="<td width='7%' align='right'><a href='index.php?_module=Gerente&_op=actualiza_proyeccion&uid=".$uid."&user_id=".$_uid."&ano=".$ano_id."&mes=".$mes_id."'>".number_format($array_metas[$_uid][$mes_id],0)."</a></td>";
                }
                $buffer.="<td align='right'>".number_format($total,0)."</td><td align='right'>
                    <a href=\"#\" onclick=\"elimina_meta('$gid','$n_uid','$n_ano','$n_mes')\"><img src=\"img/del.gif\" onmouseover=\"return escape('Eliminar Proyeccion del Vendedor')\"  border=\"0\"></a>
                    </td></tr>";
            }
        }
        $buffer.="</tbody><thead><tr><td>Totales</td>";
        foreach($array_meses as $mes_id => $valor)
        {
            $mes_id=$mes_id + 0 ;
            $total_anual=$total_anual + $array_totales[$mes_id];
            $buffer.="<td width='7%' align='right'>".number_format($array_totales[$mes_id],0)."</td>";
        }
        $buffer.="<td align='right'>".number_format($total_anual,0)."</td><td>&nbsp;</td></tr></thead></table>";
        #saco las metas registradas
    }
}

    function Recupera_proyeccion($db,$gid,$_uid,$ano_id)
    {
        $array=array();
        $sql="SELECT month(b.fecha_inicio) AS mes,sum(b.cantidad) AS total
              FROM crm_proyeccion as b
              WHERE b.active = 1 AND year(b.fecha_inicio) ='".$ano_id."' AND b.gid='".$gid."' AND b.uid=".$_uid."
              GROUP BY substr(b.fecha_inicio,1,7)
              ORDER BY substr(b.fecha_inicio,1,7)";
        $res=$db->sql_query($sql) or die("Error:   ".$sql);
        $num=$db->sql_numrows($res);
        if( $num > 0)
        {
            $total=0;
            while(list($mes,$cantidad) = $db->sql_fetchrow($res))
            {
               $array[$mes]  = $cantidad;
            }
        }
        return $array;
    }

    
    function normaliza_a_meses_totales($array)
    {
        $array_regreso = array();
	if (count($array) > 0)
        {
            $array_regreso = inicializa_arreglo();
            $total = 0;
            foreach ($array_regreso as $clave => $valor)
            {
                $valor_array = $array[$clave] + 0;
		$array_regreso[$clave] = $valor_array;
		$total = $total + $valor_array;
            }
            $array_regreso[13] = $total;
        }
	return $array_regreso;
    }
    function inicializa_arreglo()
    {
        $max = 12;
	for ($pos = 1; $pos <= $max; $pos++) {
            $array_tmp[$pos] = 0;
	}
	return $array_tmp;
    }

?>