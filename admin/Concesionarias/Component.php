<?php

class Componente
{
	private $nombre;
    private $id;
    private $bloqueado;
    private $visible;
	public function Componente ($id,$nombre,$bloqueado,$visible)
	{
		$this->nombre = $nombre;
        $this->id = $id;
        $this->bloqueado="";
        $this->visible="";
        if($visible==0)
            $this->visible="<font color='#800000'>No Visible</font>";
        if($bloqueado == 0)
            $this->bloqueado="<font color='#800000'>Bloqueado</font>";

	}
    public function getNombre()
    {
        return $this->nombre;
    }
    public function getId()
    {
        return $this->id;
    }
    public function getBloqueado()
    {
        return $this->bloqueado;
    }
    public function getVisible()
    {
        return $this->visible;
    }

    public function agregar(Componente $c)
    {
        
    }
    public function remover(Componente $c)
    {
        
    }
    public function mostrar(int $prioridad)
    {
        
    }	
}

?>