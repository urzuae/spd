<?php

class Componente
{
	private $nombre;
    private $id;
    private $bloqueado;
	public function Componente ($id,$nombre,$bloqueado)
	{
		$this->nombre = $nombre;
        $this->id = $id;
        $this->bloqueado='';
        if($bloqueado == 0)
            $this->bloqueado="<span class='arbol_bloqueado'>Bloqueado</span>";

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