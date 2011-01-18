<?php
class Grafico_Ventas_Anual
{
    var $db;
    var $uid;
    var $xml;
    var $include;
    var $filtro;
    var $gid;
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
        $this->Consulta_informacion_Ventas();
        if(count($this->array_ventas) > 0)
        {
            $this->buffer.=$this->Regresa_Tabla();
            $this->buffer.="<br><h><br>";
            $this->buffer.=$this->Regresa_Imagen();
        }
    }
    function Genera_Anos()
    {
        $this->array_anos=array('2010' => '2010','2011' => '2011','2012' => '2012','2013' => '2013','2014' => '2014','2015' => '2015');
        $this->array_anos_numerico=array('1' => '2010','2' => '2011','3' => '2012','4' => '2013','5' => '2014','6' => '2015');
    }
    function Regresa_Imagen()
    {
        $contador_div=12;
        $xml="<chart palette='1' showBorder='1' caption='Licencias Vendidas por año' showValues='0' decimals='0' formatNumberScale='0' yAxisMinValue='0' yAxisMaxValue='100' exportEnabled='1' exportAtClient='1' exportHandler='fcExporter".$contador_div."c' exportType='PNG=Exportar como imagen'>";
        foreach($this->array_anos as $j => $value)
        {
            $total_mes=$this->array_ventas[$j] + 0;
            $xml.="<set label='".$j."' value='".$total_mes. "' showLabel='1'  toolText='Licencias de año:  ".$this->ano_id."\nNo. Licencias:  ".$total_mes."'/>";
        }
	$xml.="</chart>";
        $buf.="<table><tr><td align='left'><b>Venta de Licencias por Año</b>: ".$this->ano_id."</td></tr><tr><td>";
        $buf.=renderChartHTML("includes/fusion/Column3D.swf", "", $xml, "No. de licencias Vendidas por año", 750, 350, false, false);
        $buf.="</td></tr></table>";
        return $buf;
    }
    function Regresa_Tabla()
    {
        $buf.="<table width='100%' align='center' border='0' class='tablesorter'>
                 <thead><tr heigth='40'><th>Licencias Vendidas Anualmente</th>";
        foreach($this->array_anos as $ano_id => $nm_ano)
        {
            $buf.="<th align='center'>".$nm_ano."</th>";
        }
        $buf.="<th>Totales</th></tr></thead><tbody><tr heigth='30'  class='row2'><td>No. de Licencias</td>";
        $ventas_anual=0;
        foreach($this->array_anos as $ano_id => $value)
        {
            $ventas_anual=$ventas_anual + $this->array_ventas[$ano_id];
            $buf.="<td width='7%' align='right'>".number_format(($this->array_ventas[$ano_id]+0),0)."</td>";
        }
        $buf.="<td align='right'>".number_format($ventas_anual,0)."</td></tr></tbody></table>";
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