<?php
class Grafico_Ciclo_Venta
{
    var $db;
    var $uid;
    var $no_etapas;

    var $array_campanas;
    var $total_prospectos;
    var $xml;
    var $include;
    var $filtro;
    var $gid;
    var $buffer;

    function  __construct($db,$uid,$_includesdir) {
        $this->ano_id=$ano_id;
        $this->include=$_includesdir;
        $this->db=$db;
        $this->uid=$uid;
        $this->array_campanas=array();
        $this->total_prospectos=0;
        $this->buffer="";
        $this->Filtro();
        $this->Consulta_Informacion();
        if($this->total_prospectos > 0)
        {
            $this->Ordena_Array();
        }
    }

    function Regresa_No_Prospectos($campana_id)
    {
        $total=0;
        $sql_c="SELECT count(a.contacto_id) as total FROM crm_campanas_llamadas as a LEFT JOIN crm_contactos as b
          ON a.contacto_id=b.contacto_id WHERE a.campana_id='".$campana_id."' ".$this->filtro.";";
        $res_c=$this->db->sql_query($sql_c) or die("Error en le consulta:  ".$sql_c);
        list($total)  = $this->db->sql_fetchrow($res_c);
        return $total;
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
    function Consulta_Informacion()
    {
        $array_campanas=array();
        $sql="SELECT a.campana_id,b.nombre  FROM crm_campanas_groups AS a INNER JOIN crm_campanas AS b
            ON a.campana_id=b.campana_id  WHERE a.gid='".$this->gid."' ORDER BY campana_id;";
        $res=$this->db->sql_query($sql);
        $no_etapas=$this->db->sql_numrows($res);
        $total_prospectos=0;
        if( $no_etapas > 0)
        {
            while(list($campana_id,$nombre) = $this->db->sql_fetchrow($res))
            {
                $no_contactos=$this->Regresa_No_Prospectos($campana_id);
                $this->array_campanas[$nombre]=($no_contactos + 0);
                $this->total_prospectos = $this->total_prospectos + $no_contactos + 0;
            }
        }
    }
    
    function Ordena_Array()
    {
        if($this->total_prospectos > 0)
        {
            foreach($this->array_campanas as $nm_etapa => $prospectos)
            {
                $array[$nm_etapa]=$prospectos;
            }
            $this->Genera_Imagen_Barras($array);
        }
    }
    
    function Genera_Imagen_Barras($array)
    {
        $propiedad='';
        if(count($array) > 7)
            $propiedad=" labelDisplay='Rotate' slantLabels='1' ";
        
        $this->xml="<chart showBorder='1' ".$propiedad." caption='No de prospectos por etapa del ciclo de venta' xAxisName='Etapas' yAxisName='No. de Prospectos' showValues='1' decimals='0' formatNumberScale='0'>\n";
        $total_prospectos=0;
        $titulos='';
        foreach($array as $nm_etapa => $prospectos)
        {
            $this->xml.="<set label='".substr($nm_etapa,5,strlen($nm_etapa))."' showLabel='1' value='".$prospectos."' tooltext='".substr($nm_etapa,5,strlen($nm_etapa))."'/>\n";
            $total_prospectos=$total_prospectos + $prospectos;
        }
        $this->xml.="</chart>";
        $this->buffer="<table width='70%' align='center' border='0'>
                       <tr><td align='left'><b>Ciclo de Venta</b>:  No de Prospectos :  ".$total_prospectos."</td></tr>
                       <tr><td align='center'>".renderChartHTML($this->include."/fusion/Column3D.swf", "", $this->xml, "Ciclo de Ventas", 750, 350, false)."</td></tr>
                       </table><br>";

    }






    function Genera_Imagen($array)
    {
        $this->xml="<chart bgColor='FFFF99,FFFFFF' caption='Sales Funnel' subcaption='Total de prospectos de la Distribuidora: ".$this->total_prospectos."'  FontSize= '18'   showPercentValues='0' decimals='0' baseFontSize='11' isSliced='1' formatNumberScale='1' showValues='1' formatNumberScale='0' showBorder='1'>\n";
        $increm=0.0000000000000001;
        foreach($array as $nm_etapa => $prospectos)
        {
            $this->xml.="<set label='".$nm_etapa."' value='".($prospectos + $increm)."'/>\n";
            $increm=$increm + 0.0000000000000001;
        }
        $this->xml.="
            <styles>
                <definition>
                    <style type='font' name='captionFont' size='22'/>
                </definition>
                <application>
                    <apply toObject='CAPTION' styles='captionFont'/>
                </application>
            </styles>
        </chart>";
        $this->buffer=renderChartHTML($this->include."/fusion/Funnel.swf", "", $this->xml, "grafico_ciclo", 400, 450, false);
    }

    function Obten_Grafico()
    {        
        return $this->buffer;
    }
}
?>