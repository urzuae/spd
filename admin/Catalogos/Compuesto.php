<?php
include_once 'Component.php';
global $treeString;
class Compuesto extends Componente
{
    //private ArrayList hijo = new ArrayList();
    private $treeString = "";
    private $hijo = array();
    public function Compuesto ($id, $name,$bloqueado)
    {
        parent::Componente($id,$name,$bloqueado);
    }
    public function agregar(Componente $componente)
    {
        //hijo.add(componente);
        $this->hijo[] = $componente;
    }
    public function remover(Componente $componente)
    {
        //$hijo.remove(componente);
    }
    public function getTreeString()
    {
        return $this->treeString;
    }
    public function mostrar($profundidad,&$tree)
    {
        $class = "";
        $div = "";
        $classTree = "";
        $hfef = '<a href="?/enewsletters/index.cfm">Airdrie eNewsletters </a>';
        if(sizeof($this->hijo) > 1)
        {
            $class = "class='expandable'";
            $div = '<div class="hitarea collapsable-hitarea"></div>';
        }
        if($profundidad == 1)
        $classTree = 'class="tree"';

        $url='';
        if(sizeof($this->hijo) < 1)
            $url="&nbsp;&nbsp;&nbsp;<a href='?_module=Catalogos&_op=concesionarias&fuente_id=".parent::getId()."'><span class='arbol_color'>Distribuidores</span></a>";

        $tree .= "<ul $classTree><li $class>".$div."<a name='".parent::getNombre().
                    "' id='".parent::getId()."' class='basic demo' href='".
                    "index.php?_module=Catalogos&_op=mostrarArbol'>".
        parent::getNombre()."</a>         ".parent::getBloqueado()."&nbsp;&nbsp;".$url;
        for ($i = 0; $i < sizeof($this->hijo); $i++)
        $this->hijo[$i]->mostrar($profundidad + 1,$tree);
        $tree .="</li></ul>";
    }
}
?>
