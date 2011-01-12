<?php
class Grafico_Semaforo
{
    var $db;
    var $uid;
    var $xml;
    var $include;
    var $filtro;
    var $gid;
    var $buffer;
    var $ano_id;
    var $array_metas;
    var $intervalos;

    function  __construct($db,$uid,$_includesdir,$ano_id) {
        $this->ano_id=$ano_id;
        $this->include=$_includesdir;
        $this->db=$db;
        $this->uid=$uid;
        $this->intervalos=3;
        $this->array_campanas=array();
        $this->total_prospectos=0;
        $this->array_metas=array();
        $this->Filtro();
        $this->Consulta_Informacion_Metas();
        if(count($this->array_metas) > 0)
        {
            $this->Genera_Intervalos_Montos();
            $this->Genera_Intervalos_Tiempo();
            $this->Genera_Imagen();
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
    function Consulta_Informacion_Metas()
    {
        $fecha_inicio  =$this->ano_id.'-01-01 00:01:01';
        $fecha_concluye=$this->ano_id.'-12-31 23:59:59';
        $sql="select TIMESTAMPDIFF(DAY,'".$fecha_inicio."','".$fecha_concluye."') as dias_transcurridos;";
        $res=$this->db->sql_query($sql) or die ("Error en la consulta:  ".$sql);
        list($dias)= $this->db->sql_fetchrow($res);
        $dias=$dias + 1 ;

        $sql="SELECT sum(b.cantidad) as cantidad FROM crm_proyeccion as b WHERE b.active=1 AND b.timestamp BETWEEN '".$fecha_inicio."' AND '".$fecha_concluye."' AND b.gid='".$this->gid."' ".$this->filtro." limit 1;";
        $res=$this->db->sql_query($sql) or die ("Error en la consulta:  ".$sql);
        if($this->db->sql_numrows($res) > 0)
        {
            list($cantidad) = $this->db->sql_fetchrow($res);
            if($cantidad > 0 )
            {
                $this->array_metas['no_dias'] = $dias;
                $this->array_metas['cantidad'] = $cantidad;
                $this->array_metas['fecha_inicio'] = $fecha_inicio;
                $this->array_metas['fecha_concluye'] = $fecha_concluye;
            }
        }
    }

    function Genera_Intervalos_Montos()
    {
        $inter_inicial = 0;
        $money_tmp = 0;
        $key_mon='';
        if($this->array_metas['cantidad'] == 0)
            $this->array_metas['cantidad']=1;
        $inter_dinero=round($this->array_metas['cantidad'] / $this->intervalos);
        $this->array_intervalos_dinero[]=0;
        for($i=1; $i <= $this->intervalos; $i++)
        {
            $money_tmp = $money_tmp + $inter_dinero;
            $key_mon=$inter_inicial.'|'.$money_tmp;
            $this->array_intervalos_dinero[]=$money_tmp;
            $inter_inicial=($money_tmp+1);
        }
    }

    function Genera_Intervalos_Tiempo()
    {
        if($this->array_metas['no_dias'] == 0)
            $this->array_metas['no_dias']=365;

        $tmp=number_format(($this->array_metas['no_dias'] / $this->intervalos),0);
        $fecha_tmp=substr($this->array_metas['fecha_inicio'],0,10)." 00:01:01";
        $this->array_intervalos_fechas[]=substr($this->array_metas['fecha_inicio'],0,10);
        $this->array_ventas[]=0;
        for($i=1; $i <= $this->intervalos; $i++)
        {
            $sql="SELECT DATE_ADD('".$fecha_tmp."', INTERVAL ".$tmp." DAY);";
            $res=$this->db->sql_query($sql) or die ("Error en la consulta:  ".$sql);
            if($this->db->sql_numrows($res) > 0)
            {
                // calculo para intervalo de fechas
                $fecha_inicio =substr($fecha_tmp,0,10)." 00:01:01";
                list($fecha_tmp)= $this->db->sql_fetchrow($res);

                if($fecha_tmp > $this->array_metas['fecha_concluye'])
                    $fecha_tmp = $this->array_metas['fecha_concluye'];
                
                $fecha_termino=substr($fecha_tmp,0,10)." 23:59:59";
                // saco las ventas por intervalo de tiempo
                $this->Genera_Totales_Venta($fecha_inicio,$fecha_termino,$i);
                $this->array_intervalos_fechas[]=substr($fecha_termino,0,10);
            }
        }
    }

    function Genera_Totales_Venta($fecha_inicio,$fecha_concluye,$i)
    {
        $total_precio=0.00;
        $date=date("Y-m-d H:i:s");
        $sql="SELECT a.precio,a.timestamp FROM crm_prospectos_ventas AS a LEFT JOIN crm_contactos AS b ON a.contacto_id=b.contacto_id
              WHERE a.timestamp BETWEEN '".$fecha_inicio."' AND '".$fecha_concluye."' AND a.timestamp <= '".$date."' AND b.gid='".$this->gid."' ".$this->filtro.";";
        $res=$this->db->sql_query($sql) or die ("Error en la consulta:  ".$sql);
        if($this->db->sql_numrows($res) > 0)
        {
            while(list($precio,$timestamp) =  $this->db->sql_fetchrow($res))
            {
                $total_precio = $total_precio + $precio;
            }
        }
        $this->array_ventas[$i]=$total_precio;
    }

    function Genera_Imagen()
    {
                # generamos el numero de dias que hay desde la fecha de inicio a la actual
         $fecha_actual=date('Y-m-d H:i:s');
         $sql="select TIMESTAMPDIFF(DAY,'".$this->array_metas['fecha_inicio']."','".$fecha_actual."') as dias_transcurridos;";
         $res=$this->db->sql_query($sql) or die ("Error en la consulta:  ".$sql);
         list($no_dias_transcurridos)= $this->db->sql_fetchrow($res);

         #sacamos el monto que debe llevar el vendedor en la fecha actual
         #sabemos que array_metas['no_dias']   es el 100%
         #sabemos que array_metas['cantidad']  es el 100 % en monto

         if($this->array_metas['no_dias'] == 0)  $this->array_metas['no_dias']=1;
         if($this->array_metas['cantidad'] == 0) $this->array_metas['cantidad']=1;
            $monto_promedio= number_format((($no_dias_transcurridos / $this->array_metas['no_dias']) * $this->array_metas['cantidad']),0);

        $precio_total=0;
        if($this->array_metas)
        {
            if(count($this->array_ventas) > 0)
            {
                foreach($this->array_ventas as $key => $total)
                {
                    $paso.="<point value='".$this->array_intervalos_dinero[$key]."' displayValue='".$this->array_intervalos_fechas[$key]."\n$".number_format($this->array_intervalos_dinero[$key],0)."\n\n' dashed='1' dashLen='1' dashGap='3' color='FFFFFF' thickness='3'/>\n";
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
                $this->xml="<Chart bgColor='FFFFFF' bgAlpha='0' showBorder='1' upperLimit='". $this->array_metas['cantidad']."' lowerLimit='0' numberPrefix='$' gaugeRoundRadius='0' ticksBelowGauge='1' placeValuesInside='0' showGaugeLabels='0' valueAbovePointer='0' pointerOnTop='0' pointerRadius='6' chartTopMargin='15' chartBottomMargin='5' chartLeftMargin='45' chartRightMargin='45' majorTMColor='800000' gaugeRoundRadius='10'>\n
                            <colorRange>
                                <color minValue='0' maxValue='".$this->array_metas['cantidad']."' code='".$color."' />
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
            $tmp_fecha_i=substr($this->array_metas['fecha_inicio'],0,10);
            $tmp_fecha_i=substr($tmp_fecha_i,8,2).'-'.substr($tmp_fecha_i,5,2).'-'.substr($tmp_fecha_i,0,4);

            $tmp_fecha_c=substr($this->array_metas['fecha_concluye'],0,10);
            $tmp_fecha_c=substr($tmp_fecha_c,8,2).'-'.substr($tmp_fecha_c,5,2).'-'.substr($tmp_fecha_c,0,4);

            $this->buffer.="<table width='40%' align='center' border='0'>
                            <tr height='30'><td align='left'><b>Acumulado</b>: Periodo ".$tmp_fecha_i."&nbsp;&nbsp;&nbsp;&nbsp;al&nbsp;&nbsp;&nbsp;&nbsp;".$tmp_fecha_c."</td></tr>
                            <tr><td align='center'>".renderChartHTML($this->include."/fusion/VLED.swf", "", $this->xml, "graficos", 350, 480, false)."</td></tr></table><br>";
        }
    }

    function Genera_Imagen_Demo()
    {
        $this->xml="<chart upperLimit='100' lowerLimit='0' numberSuffix='%25' majorTMNumber='11' majorTMColor='646F8F' majorTMHeight='9' minorTMNumber='2' minorTMColor='646F8F' minorTMHeight='3' majorTMThickness='1' decimalPrecision='0' ledGap='0' ledSize='1' ledBorderThickness='4'>
        <colorRange>
        <color minValue='0' maxValue='30' code='cf0000'/>
        <color minValue='30' maxValue='60' code='ffcc33'/>
        <color minValue='60' maxValue='100' code='99cc00'/>
        </colorRange>
        <value>95</value>
        </chart>";
        $this->buffer=renderChartHTML($this->include."/fusion/VLED.swf", "", $this->xml, "graficsssso", 120, 300, false);
    }

    function Obten_Semaforo()
    {
        return $this->buffer;
    }
}
?>