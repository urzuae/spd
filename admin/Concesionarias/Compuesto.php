<?php
include_once 'Component.php';
global $treeString;
class Compuesto extends Componente
{
    //private ArrayList hijo = new ArrayList();
    private $treeString = "";
    private $hijo = array();
    public function Compuesto ($id, $name, $bloqueado,$visible)
    {
        parent::Componente($id,$name,$bloqueado,$visible);
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
        $checked="";
        if(parent::getVisible()=="<font color='#800000'>No Visible</font>")
        {
            $checked="checked";
        }
        $checkbox="<input type='checkbox' name='checkboxtrees_".parent::getId()."' id='checkboxtrees_".parent::getId()."' value='fuenteid_".parent::getId()."' ".$checked.">";
        $classTree = "";
        if($profundidad == 1)
        $classTree = 'class="unorderedlisttree" id="docheckchildren"';
        $tree .= "<ul $classTree><li>".$checkbox."<label>".
        parent::getNombre()."</label>     ".parent::getVisible()."&nbsp;&nbsp;".parent::getBloqueado();
        for ($i = 0; $i < sizeof($this->hijo); $i++)
        {
            $this->hijo[$i]->mostrar($profundidad + 1,$tree);
        }
        $tree .="</li></ul>";
    }
}
?>
