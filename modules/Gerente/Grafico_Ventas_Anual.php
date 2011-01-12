<?php
class Grafico_Ventas_Anual
{
    var $db;
    var $uid;
    var $xml;
    var $include;
    var $filtro;
    var $gid;
    var $array_metas;
    var $array_ventas;
    var $array_anos;
    var $array_anos_numerico;
    var $intervalos;
    var $array_intervalos_dinero;
    var $array_intervalos_fechas;
    var $total_ventas;
    var $total_precio;
    var $buffer;
    var $ano_id;

    function  __construct($db,$uid,$_includesdir,$intervalos,$ano_id) {
        $this->ano_id = $ano_id;

        $this->intervalos=$intervalos + 0;
        if($this->intervalos == 0)
            $this->intervalos=4;

        $this->total_ventas=0;
        $this->include=$_includesdir;
        $this->db=$db;
        $this->uid=$uid;
        $this->array_campanas=array();
        $this->array_intervalos_dinero=array();
        $this->array_intervalos_fechas=array();
        $this->array_metas=array();
        $this->array_ventas=array();

        $this->total_precio=0;
        $this->total_prospectos=0;
        $this->buffer="";
        $this->Genera_Anos();
        $this->Filtro();
        $this->Consulta_Informacion_Metas();
        if(count($this->array_metas) > 0)
        {
            $this->Consulta_informacion_Ventas();
            $this->buffer.=$this->Regresa_Tabla();
            $this->buffer.="<br><h><br>";
            $this->buffer.=$this->Regresa_Imagen();
        }
    }
    function Genera_Anos()
    {
        $this->array_anos=array();
        $this->array_anos_numerico=array();
        $sql="SELECT DISTINCT YEAR(fecha_inicio) as ano FROM crm_proyeccion ORDER BY YEAR(fecha_inicio);";
        $res=$this->db->sql_query($sql) or die ("Error en la consulta:  ".$sql);
        if($this->db->sql_numrows($res) > 0)
        {
            while(list($ano) = $this->db->sql_fetchrow($res))
            {
                $this->array_anos[$ano]=$ano;
                $this->array_anos_numerico[(count($this->array_anos_numerico)+ 1)]=$ano;
            }
        }
    }
    function Regresa_Imagen()
    {
        $atributes = "toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,copyhistory=no,resizable=yes";
        $xml="<chart palette='1' showBorder='1' caption='Comparación Proyección / Ventas por año  ".$nm_subprograma."' showValues='0' decimals='0' formatNumberScale='0' yAxisMinValue='0' yAxisMaxValue='100' exportEnabled='1' exportAtClient='1' exportHandler='fcExporter".$contador_div."c' exportType='PNG=Exportar como imagen'><categories>";

        foreach($this->array_anos as $j => $nm_ano)
        {
            $xml.="<category showlabel='1' label='". substr($nm_ano,0,4). "'/>";
	}
        $xml.="</categories>";

        $xml.="<dataset seriesName='Proyección'>";
        foreach($this->array_anos as $j => $value)
        {
            #$url="index.php?_module=Gerente%26_op=ciclo_venta_mensual%26uid=".$this->uid."%26ano_id=".$j;
            $prom=0;
            if( $this->array_metas[$j] > 0 )
                $prom=number_format(( ($this->array_ventas[$j] / $this->array_metas[$j]) * 100),2,'.',',');
            $total_mes=$this->array_metas[$j] + 0;
            $xml.="<set value='".$total_mes. "' showLabel='1' toolText='Proyección de mes:  ".$this->array_anos[str_pad($j,2,'0',STR_PAD_LEFT)]."\nProyección:   ".$this->array_metas[$j]."\nVenta:  ".$this->array_ventas[$j]."\nPromedio: ".$prom."'/>";
        }
	$xml.="</dataset>";
        $xml.="<dataset seriesName='Ventas'  renderAs='Line'>";
        foreach($this->array_anos as $j => $value)
        {
            $url="index.php?_module=Gerente%26_op=ciclo_venta_mensual%26uid=".$this->uid."%26ano_id=".$this->ano_id."%26mes_id=".$j;
            $prom=0;
            if( $this->array_metas[$j] > 0 )
                $prom=number_format(( ($this->array_ventas[$j] / $this->array_metas[$j]) * 100),2,'.',',');
            $total_mes=$this->array_ventas[$j] + 0;
            $xml.="<set value='" .$this->array_ventas[$j]. "'/>";
        }
	$xml.="</dataset>";
	$xml.="</chart>";
        $buf.="<table><tr><td align='left'><b>Proyecci&oacute;n Anual</b>: ".$this->ano_id."</td></tr><tr><td>";
        $buf.=renderChartHTML("includes/fusion/MSColumnLine3D.swf", "", $xml, "Proyección de Metas y Ventas por mes", 750, 350, false, false);
        $buf.="</td></tr></table>";
        return $buf;
    }
    function Regresa_Tabla()
    {
        $buf.="<table width='100%' align='center' border='0' class='tablesorter'>
                 <thead><tr heigth='40'><th>&nbsp;</th>";
        foreach($this->array_anos as $ano_id => $nm_ano)
        {
            $buf.="<th align='center'>".$nm_ano."</th>";
        }
        $buf.="<th>Totales</th></tr></thead><tbody><tr heigth='30'  class='row2'><td>Proyecci&oacute;n</td>";
        $total_anual=0;
        foreach($this->array_anos as $ano_id => $value)
        {
            $total_anual=$total_anual+ $this->array_metas[$ano_id];
            $buf.="<td align='right'>".number_format(($this->array_metas[$ano_id]+0),0)."</td>";
        }
        $buf.="<td align='right'>".number_format($total_anual,0)."</td></tr><tr  heigth='30' class='row1'><td>Ventas</td>";
        $ventas_anual=0;
        foreach($this->array_anos as $ano_id => $value)
        {
            $ventas_anual=$ventas_anual + $this->array_ventas[$ano_id];
            $buf.="<td width='7%' align='right'>".number_format(($this->array_ventas[$ano_id]+0),0)."</td>";
        }
        $buf.="<td align='right'>".number_format($ventas_anual,0)."</td></tr><tr  heigth='30' class='row2'><td>Promedios</td>";
        $promedio_anual=0;
        foreach($this->array_anos as $ano_id => $valor)
        {
            $prom_anual=0;
            if($this->array_metas[$ano_id] > 0)
                $prom_anual=( ($this->array_ventas[$ano_id] / $this->array_metas[$ano_id]) * 100);

            $promedio_anual=$promedio_anual + $prom_anual;
            $buf.="<td width='7%' align='right'>".number_format($prom_anual,2,'.',',')." %</td>";
        }
        $prom_total=0;
        if($total_anual>0)
            $prom_total=(($ventas_anual/ $total_anual) * 100);
        $buf.="<td align='right'>".number_format($prom_total,2,'.',',')."%</td></tr></tbody></table>";
        return $buf;
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


    function Consulta_informacion_Ventas()
    {
        $sql="SELECT year(a.timestamp) AS ano, sum(a.precio) AS total
              FROM crm_prospectos_ventas as a left join crm_contactos as b on a.contacto_id=b.contacto_id
              WHERE a.eliminar = 0  AND b.gid='".$this->gid."' ".$this->filtro."
              GROUP BY substr(b.timestamp,1,4)
              ORDER BY substr(b.timestamp,1,4)";
        $res=$this->db->sql_query($sql) or die ("Error en la consulta:  ".$sql);
        if($this->db->sql_numrows($res) > 0)
        {
            while(list($ano,$cantidad_ventas) = $this->db->sql_fetchrow($res))
            {
                $cantidad_ventas=$cantidad_ventas+0;
                $this->array_ventas[$ano]=$cantidad_ventas;

            }
        }
    }

    function Consulta_Informacion_Metas()
    {
        #Sacamos la meta, para saber fechas y monto de la misma
        $sql="SELECT b.id,b.uid,year(b.fecha_inicio) AS ano, sum(b.no_dias) as no_dias,count(substr(b.fecha_inicio,1,4)) AS no_regs,
              sum(b.cantidad) AS total,a.name
              FROM crm_proyeccion as b LEFT JOIN users as a ON b.uid = a.uid
              WHERE b.active = 1 AND b.gid='".$this->gid."' ".$this->filtro."
              GROUP BY substr(b.fecha_inicio,1,4)
              ORDER BY substr(b.fecha_inicio,1,4)";
        $res=$this->db->sql_query($sql) or die ("Error en la consulta:  ".$sql);
        if($this->db->sql_numrows($res) > 0)
        {
            while(list($id,$_uid,$ano,$no_dias,$no_reg,$cantidad,$name) = $this->db->sql_fetchrow($res))
            {
                $this->array_metas[$ano]= $cantidad;
            }
        }
    }

    function normaliza_a_meses_totales($array)
    {
        $array_regreso = array();
	if (count($array) > 0)
        {
            $array_regreso = $this->inicializa_arreglo(count($array));
            $total = 0;
            foreach ($array_regreso as $clave => $valor)
            {
                $valor_array = $array[$clave] + 0;
		$array_regreso[$clave] = $valor_array;
		$total = $total + $valor_array;
            }
        }
	return $array_regreso;
    }

    function inicializa_arreglo($max)
    {
	for ($pos = 1; $pos <= $max; $pos++) {
            $tmp=$this->array_anos_numerico[$pos];
            $array_tmp[$tmp] = 0;
	}
	return $array_tmp;
    }
    function Obten_Grafico_Ventas()
    {
        return  $this->buffer;
    }
}
?>