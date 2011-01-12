<?php
class Ciclo_Venta
{
    
    var $no_etapas;
    var $array_campanas;
    var $total_prospectos;
    var $xml;


    function  __construct($no_etapas,$array_campanas,$total_prospectos) {
        $this->no_etapas=$no_etapas;
        $this->array_campanas=$array_campanas;
        $this->total_prospectos=$total_prospectos;
        $this->Ordena_Array();
        $this->Genera_Imagen();
    }
    function Ordena_Array()
    {
        foreach($this->array_campanas as $nm_etapa => $prospectos)
        {
            if($prospectos > 0)
            {
                $array[$nm_etapa]=$prospectos;
            }
            else
            {
                $sin_prosp.="\n".$nm_etapa;
            }
        }
        if(strlen($sin_prosp) > 0)
        {
            $array[$sin_prosp]=0;
        }
        $this->array_campanas=$array;
    }
    
    function Genera_Imagen()
    {
        $this->xml="<chart bgColor='FFFF99,FFFFFF' caption='Sales Funnel' FontSize= '18'   showPercentValues='0' decimals='0' baseFontSize='11' isSliced='1' formatNumberScale='1' showValues='1' formatNumberScale='0' showBorder='1'>\n
        <set label='Total de prospectos de la Distribuidora' value='".$this->total_prospectos."'/>\n";
        $increm=0.0000000000000001;
        foreach($this->array_campanas as $nm_etapa => $prospectos)
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
    }

    function Obten_Xml()
    {
        return $this->xml;
    }
}
?>