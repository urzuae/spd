<?
/**
 * Clase para poder hacer una paginación de registros
 *
 * @author Victor Vargas Oyarzo
 * @copyright Copyright (c) 2007-2008 PCS Mexico (http://www.pcsmexico.com)
 * @package paginacion.php
 * @version 1.0
 */

class paginacion
{
	/**
	 * Total de registros de una consulta SQL sin aplicar limit.
	 *
	 * @var int
	 */
	var $cantidad_total_registros;
	
	/**
	 * Cantidad de registros por página que desea mostrar.
	 *
	 * @var int
	 */
	var $cantidad_registros_por_pagina;
	/**
	 * Indica la alineación de los números de página.
	 * left = izquierda, center = centro, right = derecha
	 *
	 * @var string
	 */
	var $align;
	
	/**
	 * Número de página o texto "Atrás" o "Adelante"
	 *
	 * @var string
	 */
	var $numero;
	
	/**
	 * Número de página actual.
	 *
	 * @var int
	 */
	var $actual;
	
	/**
	 * Ruta al directorio donde se encuentra el archivo css
	 *
	 * @var string
	 */
	var $css_path;
	
	/**
	 * Párametros de inicialización de la clase a instanciar
	 *
	 * @param int $cantidad_total_registros
	 * @param int $cantidad_registros_por_pagina
	 * @return paginacion
	 * @access public
	 */	
	public function paginacion($cantidad_total_registros,$cantidad_registros_por_pagina,$css_path)
	{
		$this->cantidad_total_registros = $cantidad_total_registros;
		$this->cantidad_registros_por_pagina = $cantidad_registros_por_pagina;
		$this->limite_paginas_mostradas = $limite_paginas_mostradas;
		$this->paginas = ceil($this->cantidad_total_registros/$this->cantidad_registros_por_pagina);
		$this->css_path = $css_path;
		
		if(isset($_REQUEST["pagina"]))
			$this->pagina_actual = $_REQUEST["pagina"];
		else 
			$this->pagina_actual = 1;
	}

	/**
	 * Regresa el limite para ponerlo al final de la consulta SQL.
	 *
	 * @return string
	 * @access public
	 */
	public function sql_limit()
	{
		for($x=0; $x<$this->paginas; ++$x)
		{
			if($this->pagina_actual == $x+1)
			{
				$limit = ($this->cantidad_registros_por_pagina * $this->pagina_actual) - $this->cantidad_registros_por_pagina;
				return "limit $limit,".$this->cantidad_registros_por_pagina;
				break;
			}
		}
	}
	
	/**
	 * Imprime la salida de los números de página en formato HTML usando etiquetas DIV.
	 * 
	 * @param string $align
	 * @return string
	 * @access public
	 */
	public function imprimir_paginas($align = null)
	{
		$this->html = '<link rel="stylesheet" href="'.$this->css_path.'/paginacion.css" type="text/css">';
		$this->html .= '<div id="paginacion-marco-links" style="text-align:'.$align.';width:100%;">';
		$this->html .= '<table border="0" cellspacing="1" cellpadding="0"><tr>';
		
		if($this->pagina_actual > 1)
			$this->html.= "<td>".$this->cuadro_numero_pagina("< Atrás",$this->pagina_actual)."</td>";
			
		for($x=1; $x<=$this->paginas; ++$x)
		{
			$this->html .= '<td>'.$this->cuadro_numero_pagina($x,$this->pagina_actual).'</td>';
		}
		
		if($this->pagina_actual < $x-1)
			$this->html.= "<td>".$this->cuadro_numero_pagina("Siguiente >",$this->pagina_actual)."</td>";
			
		$this->html .= '</tr></table>';
		$this->html .= '</div>';
		return $this->html;
	}
	
	/**
	 * Captura las variables de la url excluyendo la de página
	 *
	 * @return string
	 * @author public
	 */
	public function url_vars()
	{
		$this->vars = "?";
		foreach ($_REQUEST as $key=>$value)
		{
			if($key != "pagina")
				$this->vars .= $key.'='.$value.'&';
			
		}
		return $this->vars;
	}
	
	/**
	 * Crea un pequeño cuadro con el número de página, conteniendo un link hacia las otra paginas, y resaltando el cuadro de la página actual.
	 *
	 * @param string $numero
	 * @param int $actual
	 * @return string
	 * @access private
	 */
	private function cuadro_numero_pagina($numero,$actual)
	{
		$vars = $this->url_vars();
		if($numero == "< Atrás")
		{
			$this->cuadro_pagina = '<a href="'.$vars.'pagina='.($actual-1).'" class="style_links_pagina">';
			$this->cuadro_pagina .= '<div class="style_cuadro_pagina_text" onmouseover="this.style.backgroundColor=\'#EEEEEE\'" onmouseout="this.style.backgroundColor=\'lightgray\'">';	
			$this->cuadro_pagina .= $numero;
			$this->cuadro_pagina .= '</div></a>';
		}
		elseif ($numero == "Siguiente >")
		{
			$this->cuadro_pagina = '<a href="'.$vars.'pagina='.($actual+1).'" class="style_links_pagina">';
			$this->cuadro_pagina .= '<div class="style_cuadro_pagina_text" onmouseover="this.style.backgroundColor=\'#EEEEEE\'" onmouseout="this.style.backgroundColor=\'lightgray\'">';	
			$this->cuadro_pagina .= $numero;
			$this->cuadro_pagina .= '</div></a>';
		}
		elseif($actual == $numero)
		{
			$this->cuadro_pagina = '<div class="style_cuadro_pagina_selected">';	
			$this->cuadro_pagina .= $numero;
			$this->cuadro_pagina .= '</div>';
		}
		else
		{
			$this->cuadro_pagina = '<a href="'.$vars.'pagina='.$numero.'" class="style_links_pagina">';
			$this->cuadro_pagina .= '<div class="style_cuadro_pagina" onmouseover="this.style.backgroundColor=\'#DDDDDD\'" onmouseout="this.style.backgroundColor=\'white\'">';	
			$this->cuadro_pagina .= $numero;
			$this->cuadro_pagina .= '</div></a>';
		}		
		return $this->cuadro_pagina;
	}
}
?>