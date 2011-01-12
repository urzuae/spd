<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
//global $leyenda_filtros, $filtroVehiculo, $tabla_vendedores, $filterHeader;

$filtrosPrincipalScreen = $filtro;
$filterHeader = "
    <table id=\"displayFilter\" style=\"text-align: left; width: 50%;\" border=\"0\" cellpadding=\"2\" cellspacing=\"2\">
    <thead>
        <tr>
            <td colspan=2 ><img src=\"../img/new.png\" height=\"16\" wight=\"16\"><font align=\"center\">&nbsp;&nbsp;Filtros seleccionados</font></td>
        </tr>
    </thead>
    <tbody>
        <tr>
            $leyenda_filtros
        </tr>
    </tbody>
    <thead>
        <tr>
            <td colspan=2 ><img src=\"../img/vehiculo.png\" height=\"16\" wight=\"16\"><font align=\"center\">&nbsp;&nbsp;Filtro por Producto</font></td>
        </tr>
    </thead>
    <tbody class=\"filterVehicle row1\">
        <tr class=\"showUnited\">
            <td>Producto</td>
            <td class='list row1'><select style='width: 200px;' name='listVersion' id='listVersion'><option value='0'>Todos</option></select></td>
        </tr>
        <tr class=\"showVersion row2\">
            <td>Categoria</td>
            <td class='list row2'><select style='width: 200px;' name='listVersion' id='listVersion'><option value='0'>Todos</option></select></td>
        </tr>
        <tr class=\"showTransmision row1\">
            <td>Sub Categoria</td>
            <td class='list row1'><select style='width: 200px;' name='listVersion' id='listVersion'><option value='0'>Todos</option></select></td>
        </tr>
        <tr class=\"row2\">
			  <td colspan=\"2\" align=\"center\"><input type=\"submit\" name=\"filterVehicle\"  id='filterVehicle' value=\"Filtrar\"></td>
        </tr>
    </tbody>
</table>
$tabla_vendedores
$tabla_campanas";                     
?>
