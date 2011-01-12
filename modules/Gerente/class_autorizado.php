<?php
class Fecha_autorizado
{
     private $fecha_autorizacion;
     private $fecha_firmado;
     private $semaforo;
     private $db;
     private $no_dias;
     
     function __construct($db,$fecha_autorizado,$fecha_firmado)
     {
         $this->no_dias=0;
         $this->db=$db;
         $this->semaforo='';
         $opc=0;
         if( ($fecha_autorizado != '0000-00-00 00:00:00') && ($fecha_firmado != '0000-00-00 00:00:00') )
         {
            $this->fecha_autorizacion=$fecha_autorizado;
            $this->fecha_firmado=$fecha_firmado;
            $opc=1;
         }
         if( ($fecha_autorizado != '0000-00-00 00:00:00') && ($fecha_firmado == '0000-00-00 00:00:00') )
         {
            $this->fecha_autorizacion=$fecha_autorizado;
            $this->fecha_firmado=date("Y-m-d H:i:s");
            $opc=1;
         }
         if($opc == 1)
         {
            $this->Saca_diferencia_en_Dias();
            $this->Genera_Semaforo();
         }
     }

     function Saca_diferencia_en_Dias()
     {
         $sql="SELECT TIMESTAMPDIFF(DAY , '".$this->fecha_autorizacion."', '".$this->fecha_firmado."' ) AS retrasos;";
         $res=$this->db->sql_query($sql);
         if($this->db->sql_numrows($res) > 0)
         {
            $this->no_dias=($this->db->sql_fetchfield(0,0,$res)) + 0;
         }
     }
     function Genera_Semaforo()
     {
         if($this->no_dias < 5)
         {
            $this->semaforo="#00FF00";
         }
         if( ($this->no_dias > 4) && ($this->no_dias < 16) )
         {
            $this->semaforo="#FFFF00";
         }
         if ($this->no_dias > 15)
         {
            $this->semaforo="#FF0000";
         }
     }
     function Obten_Semaforo()
     {
         return $this->semaforo;
     }
}
?>