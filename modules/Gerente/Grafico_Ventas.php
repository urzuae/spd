<?php
class Grafico_Ventas
{
    var $db;
    var $uid;
    var $xml;
    var $include;
    var $filtro;
    var $gid;
    var $array_ventas;
    var $array_meses;
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

        $this->array_meses= array('01' => 'Enero','02' => 'Febrero','03' => 'Marzo','04' => 'Abril','05' => 'Mayo','06' => 'Junio',
        '07' => 'Julio','08' => 'Agosto','09' => 'Septiembre','10' => 'Octubre','11' => 'Noviembre','12' => 'Diciembre');
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
        $this->Filtro();
        $this->Consulta_informacion_Ventas();
        if(count($this->array_ventas) > 0)
        {
            $this->buffer.=$this->Regresa_Tabla();
            $this->buffer.="<br><h><br>";
            $this->buffer.=$this->Regresa_Imagen();
        }
    }
    function Regresa_Imagen()
    {
        $contador_div=1;
        $xml="<chart palette='1' showBorder='1' caption='Licencias vendidas por mes ' showValues='0' decimals='0' formatNumberScale='0' yAxisMinValue='0' yAxisMaxValue='100' exportEnabled='1' exportAtClient='1' exportHandler='fcExporter".$contador_div."c' exportType='PNG=Exportar como imagen'>";
        for ($j = 1; $j <= 12; $j++)
        {
            $nm_mes=substr($this->array_meses[str_pad($j,2,'0',STR_PAD_LEFT)],0,3);
            $total_mes=$this->array_ventas[$j] + 0;
            $xml.="<set value='".$total_mes. "' label='".$nm_mes."' showLabel='1'  toolText='Licencias de mes:  ".$nm_mes."\nNo. Licencias:  ".$total_mes."'/>";
        }
	$xml.="</chart>";
        $buf.="<table><tr><td align='left'><b>Licencias Vendidas en el a&ntilde;o</b>: ".$this->ano_id."</td></tr><tr><td>";
        $buf.=renderChartHTML("includes/fusion/Column3D.swf", "", $xml, "Licencias Vendidas por mes", 750, 350, false, false);
        $buf.="</td></tr></table>";
        return $buf;
    }
    function Regresa_Tabla()
    {
        $buf.="
                <table width='100%' align='center' border='0' class='tablesorter'>
                 <thead><tr heigth='40'><th>&nbsp;</th>";
        foreach($this->array_meses as $mes_id => $nm_mes)
        {
            $buf.="<th align='center'>".$nm_mes."</th>";
        }
        $buf.="<th>Total de Licencias</th></tr></thead><tbody><tr  heigth='30' class='row1'><td>Ventas</td>";
        $ventas_anual=0;
        $this->array_ventas=$this->normaliza_a_meses_totales($this->array_ventas);
        foreach($this->array_ventas as $mes_id => $valor)
        {
            $total_anual=$total_anual + $valor;
            $buf.="<td width='7%' align='right'>".number_format($valor,0)."</td>";
        }
        $buf.="</tr></tbody></table>";
        return $buf;
    }
    function normaliza_a_meses_totales($array)
    {
        $array_regreso = array();
	if (count($array) > 0)
        {
            $array_regreso = $this->inicializa_arreglo();
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
        $sql="SELECT year(a.timestamp) AS ano, month(a.timestamp) AS mes,sum(a.precio) AS total
              FROM crm_prospectos_ventas as a left join crm_contactos as b on a.contacto_id=b.contacto_id
              WHERE a.eliminar = 0 AND year(a.timestamp) ='".$this->ano_id."' AND b.gid='".$this->gid."' ".$this->filtro."
              GROUP BY substr(b.timestamp,1,7)
              ORDER BY substr(b.timestamp,1,7)";
        $res=$this->db->sql_query($sql) or die ("Error en la consulta:  ".$sql);
        if($this->db->sql_numrows($res) > 0)
        {
            while(list($ano,$mes,$cantidad_ventas) = $this->db->sql_fetchrow($res))
            {
                $cantidad_ventas=$cantidad_ventas+0;
                $this->array_ventas[$mes]=$cantidad_ventas;

            }
        }
    }

    function Obten_Grafico_Ventas()
    {
        return  $this->buffer;
    }
}
?>