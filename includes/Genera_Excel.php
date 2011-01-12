<?php
class Genera_Excel {
    private $data;
    private $archivo;
    private $path_save;
    private $buffer_html;
    private $buffer;

    function __construct($data, $tipo,$system_name,$opc=0) {
        
        $this->archivo=$system_name.'-'.$tipo.'-'.date("Y-m-d-H-i-s").".xls";
        $this->path_save="../files/".$this->archivo;
        if($opc == 1)
            $this->path_save="files/".$this->archivo;
        
        $this->path_save=str_replace('_','-',$this->path_save);
        $this->data = $data;
        $this->Genera_Archivo();
    }

    function Genera_Archivo() {
        $this->data = str_replace('<th>&nbsp;</th>', '', $this->data);
        $this->data = str_replace('Vendedores', '', $this->data);
        $this->data = str_replace('Prospectos', '', $this->data);
        $boton = '<table width="90%">
                    <tr><td align="right">
                        <a href="' . $this->path_save. '" target="_blank">Exportar a Excel</a>
                    </td></tr></table>';
        $this->buffer_html = $this->data;
        $this->buffer = $boton;
        $f1 = fopen($this->path_save, "w+") or die ("No se puede abrir el archivo");
        fwrite($f1, $this->buffer_html);
        fclose($f1);
    }

    function Obten_href() {
        return $this->buffer;
    }
}
?>