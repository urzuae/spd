<?php
class Grafico_Ventas_Horizontales
{
    var $db;
    var $uid;
    var $mes_id;
    var $ano_id;
    var $xml;
    var $include;
    var $filtro;
    var $gid;
    var $array_metas;
    var $array_ventas;
    var $intervalos;
    var $array_intervalos_dinero;
    var $array_intervalos_fechas;
    var $total_ventas;
    var $total_precio;
    var $buffer;


    function  __construct($db,$uid,$_includesdir,$ano_id,$mes_id,$intervalos) {
        $this->db=$db;
        $this->uid=$uid;
        $this->include=$_includesdir;
        $this->ano_id=$ano_id;
        $this->mes_id=$mes_id;
        $this->intervalos=$intervalos + 0;
        if($this->intervalos == 0)  $this->intervalos=4;

        $this->total_ventas=0;
        $this->array_campanas=array();
        $this->array_intervalos_dinero=array();
        $this->array_intervalos_fechas=array();
        $this->array_metas=array();
        $this->array_ventas=array();

        $this->total_precio=0;
        $this->total_prospectos=0;
        $this->buffer="";
        $this->Filtro();
        $this->Consulta_Informacion_Metas();
        if(count($this->array_metas) > 0)
        {
            foreach($this->array_metas as $_uid => $array_datos)
            {
                $this->Genera_Intervalos_Montos($_uid);
                $this->Genera_Intervalos_Tiempo($_uid);
                $this->Genera_Imagen($_uid);
            }
        }
    }

    function Filtro()
    {
        $this->filtro='';
        $sql  = "SELECT gid, super FROM users WHERE uid='".$this->uid."'";
        $result = $this->db->sql_query($sql) or die("Error");
        list($gid, $super) = $this->db->sql_fetchrow($result);

        if($super == 8)
            $this->filtro = " AND b.uid = '".$this->uid."' ";
        $this->gid=$gid;
    }

    function Genera_Intervalos_Montos($_uid)
    {
        $inter_inicial = 0;
        $money_tmp = 0;
        $key_mon='';
        if($this->array_metas[$_uid]['cantidad'] == 0)
            $this->array_metas[$_uid]['cantidad']=1;
        $inter_dinero=round($this->array_metas[$_uid]['cantidad'] / $this->intervalos);
        $this->array_intervalos_dinero[$_uid][]=0;
        for($i=1; $i <= $this->intervalos; $i++)
        {
            $money_tmp = $money_tmp + $inter_dinero;
            $key_mon=$inter_inicial.'|'.$money_tmp;
            $this->array_intervalos_dinero[$_uid][]=$money_tmp;
            $inter_inicial=($money_tmp+1);
        }
    }

    function Genera_Intervalos_Tiempo($_uid)
    {
        if($this->array_metas[$_uid]['no_dias'] == 0)
            $this->array_metas[$_uid]['no_dias']=30;

        $tmp=number_format(($this->array_metas[$_uid]['no_dias'] / $this->intervalos),0);
        $fecha_tmp=substr($this->array_metas[$_uid]['fecha_inicio'],0,10)." 00:01:01";
        $tmp_fecha=substr($this->array_metas[$_uid]['fecha_inicio'],0,10);
        $tmp_fecha=substr($tmp_fecha,8,2).'-'.substr($tmp_fecha,5,2).'-'.substr($tmp_fecha,0,4);
        $this->array_intervalos_fechas[$_uid][]=$tmp_fecha;
        
        $this->array_ventas[$_uid][]=0;
        for($i=1; $i <= $this->intervalos; $i++)
        {
            $sql="SELECT DATE_ADD('".$fecha_tmp."', INTERVAL ".$tmp." DAY);";
            $res=$this->db->sql_query($sql) or die ("Error en la consulta:  ".$sql);
            if($this->db->sql_numrows($res) > 0)
            {
                // calculo para intervalo de fechas
                $fecha_inicio =substr($fecha_tmp,0,10)." 00:01:01";
                list($fecha_tmp)= $this->db->sql_fetchrow($res);

                if($fecha_tmp > $this->array_metas[$_uid]['fecha_concluye'])
                    $fecha_tmp = $this->array_metas[$_uid]['fecha_concluye'];
                
                $fecha_termino=substr($fecha_tmp,0,10)." 23:59:59";
                // saco las ventas por intervalo de tiempo
                $this->Genera_Totales_Venta($_uid,$fecha_inicio,$fecha_termino,$i);
                $tmp_fecha=substr($fecha_termino,0,10);
                $tmp_fecha=substr($tmp_fecha,8,2).'-'.substr($tmp_fecha,5,2).'-'.substr($tmp_fecha,0,4);
                $this->array_intervalos_fechas[$_uid][]=$tmp_fecha;
            }
        }
    }

    function Genera_Totales_Venta($_uid,$fecha_inicio,$fecha_concluye,$i)
    {
        $total_precio=0.00;
        $date=date("Y-m-d H:i:s");
        $sql="SELECT a.precio,a.timestamp FROM crm_prospectos_ventas AS a LEFT JOIN crm_contactos AS b ON a.contacto_id=b.contacto_id
              WHERE  a.timestamp BETWEEN '".$fecha_inicio."' AND '".$fecha_concluye."' AND a.timestamp <= '".$date."' AND b.gid='".$this->gid."' AND b.uid=".$_uid.";";
        $res=$this->db->sql_query($sql) or die ("Error en la consulta:  ".$sql);
        if($this->db->sql_numrows($res) > 0)
        {
            while(list($precio,$timestamp) =  $this->db->sql_fetchrow($res))
            {
                $total_precio = $total_precio + $precio;
            }
        }
        $this->array_ventas[$_uid][$i]=$total_precio;
    }

    function Consulta_Informacion_Metas()
    {
        #Sacamos la meta, para saber fechas y monto de la misma
        $sql="SELECT b.id,b.uid,b.cantidad,b.fecha_inicio,b.fecha_concluye,b.no_dias,a.name FROM crm_proyeccion as b left join users as a on b.uid=a.uid 
              WHERE b.active=1 AND YEAR(fecha_inicio)='".$this->ano_id."' AND MONTH(fecha_inicio)='".$this->mes_id."' AND b.gid='".$this->gid."' ".$this->filtro." ORDER BY b.fecha_inicio DESC;";
        $res=$this->db->sql_query($sql) or die ("Error en la consulta:  ".$sql);
        if($this->db->sql_numrows($res) > 0)
        {
            while(list($id,$_uid,$cantidad,$fecha_inicio,$fecha_concluye,$dias,$name) = $this->db->sql_fetchrow($res))
            {
                $this->array_metas[$_uid]['no_dias'] = $dias;
                $this->array_metas[$_uid]['cantidad'] = $cantidad;
                $this->array_metas[$_uid]['fecha_inicio'] = $fecha_inicio;
                $this->array_metas[$_uid]['fecha_concluye'] = $fecha_concluye;
                $this->array_metas[$_uid]['name'] = $name;
            }
        }
    }

    function Genera_Imagen($_uid)
    {
        # generamos el numero de dias que hay desde la fecha de inicio a la actual
         $fecha_actual=date('Y-m-d H:i:s');
         $sql="select TIMESTAMPDIFF(DAY,'".$this->array_metas[$_uid]['fecha_inicio']."','".$fecha_actual."') as dias_transcurridos;";
         $res=$this->db->sql_query($sql) or die ("Error en la consulta:  ".$sql);
         list($no_dias_transcurridos)= $this->db->sql_fetchrow($res);
         $no_dias_transcurridos=$no_dias_transcurridos+1;
         #sacamos el monto que debe llevar el vendedor en la fecha actual
         #sabemos que array_metas['no_dias']   es el 100%
         #sabemos que array_metas['cantidad']  es el 100 % en monto
         
         if($this->array_metas[$_uid]['no_dias'] == 0)  $this->array_metas[$_uid]['no_dias']=1;
         if($this->array_metas[$_uid]['cantidad'] == 0) $this->array_metas[$_uid]['cantidad']=1;
            $monto_promedio= number_format((($no_dias_transcurridos / $this->array_metas[$_uid]['no_dias']) * $this->array_metas[$_uid]['cantidad']),0);

        $precio_total=0;
        if($this->array_metas[$_uid])
        {
            if(count($this->array_ventas[$_uid]) > 0)
            {
                foreach($this->array_ventas[$_uid] as $key => $total)
                {
                    $paso.="<point value='".$this->array_intervalos_dinero[$_uid][$key]."' displayValue='".$this->array_intervalos_fechas[$_uid][$key]."\n$".number_format($this->array_intervalos_dinero[$_uid][$key],0)."\n\n' dashed='1' dashLen='1' dashGap='3' color='FFFFFF' thickness='3'/>\n";
                    $precio_total= $precio_total +  $total;
                }
                // insertamos el punto del dia actual
                $fecha_actual=substr($fecha_actual,0,10);
                if($monto_promedio == 0) $monto_promedio=1;
                $monto_promedio=str_replace(',','',$monto_promedio);

                $porcentaje_relacion_dia=(($precio_total /$monto_promedio)*100);
                $porcentaje_relacion_dia=number_format($porcentaje_relacion_dia,0);
                
                if($porcentaje_relacion_dia<=60)
                    $color="FF0000";
                if( ($porcentaje_relacion_dia>60) && ($porcentaje_relacion_dia<86))
                        $color="FFFF00";
                if($porcentaje_relacion_dia>85)
                    $color="00CC00";

                $paso.="<point value='".$monto_promedio."' displayValue='".$fecha_actual."   $".number_format($monto_promedio,0)."' dashGap='3' color='00000' thickness='4' showOnTop='1' />\n";
                $this->xml="<Chart bgColor='FFFFFF' bgAlpha='0' showBorder='1' upperLimit='". $this->array_metas[$_uid]['cantidad']."' lowerLimit='0' numberPrefix='$' ticksBelowGauge='1' placeValuesInside='0' showGaugeLabels='0' valueAbovePointer='0' pointerOnTop='0' pointerRadius='6' chartTopMargin='15' chartBottomMargin='5' chartLeftMargin='45' chartRightMargin='45' majorTMColor='800000' gaugeRoundRadius='10'>\n
                            <colorRange>
                                <color minValue='0' maxValue='".$this->array_metas[$_uid]['cantidad']."' code='".$color."' />
                             </colorRange>\n
                             <value>".$precio_total."</value>\n
                             <trendpoints>\n".$paso."</trendpoints>
                             <styles>
                                <definition>
                                    <style name='valueFont' type='Font' bgColor='800000' size='8' color='FFFFFF'/>
                                </definition>
                                <application>
                                    <apply toObject='VALUE' styles='valueFont'/>
                                </application>
                             </styles>
                             </Chart>";
            }
            $f=fopen('salida.xml','w+');
            fwrite($f,$this->xml);
            fclose($f);
            $this->buffer.="<table width='70%' align='center' border='0'>
                            <tr><td align='left'><b>Vendedor</b>:  ".$this->array_metas[$_uid]['name']."<br>
                                                 <b>A&ntilde;o</b>:  ".$this->ano_id."<br>
                                                 <b>Proyectado</b>:  $".$this->array_metas[$_uid]['cantidad']."</td></tr>
                            <tr><td align='center'>".renderChartHTML($this->include."/fusion/HLinearGauge.swf", "", $this->xml, "graficos", 750, 180, false)."</td></tr></table><br>";
        }
    }
    

    function Obten_Grafico_Ventas()
    {
        return  $this->buffer;
    }
}
/**
 * a.eliminar=0 AND YEAR(timestamp)='".$this->ano_id."' AND MONTH(timestamp)='".$this->mes_id."'
 */
?>