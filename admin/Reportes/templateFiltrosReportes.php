<?php
$buffer_filtros="
        <table width=\"100%\" height=\"190px\" border=\"0\" align=\"center\">
            <thead>
            <tr height='25'>
              <td colspan=\"3\" align=\"center\">Opciones de reporte</td>
            </tr>
            </thead>
              <tr>
              <td colspan=\"3\" align=\"center\">&nbsp;</td>
            </tr>

            <tr class=\"row1\">
                <td width=\"50%\" valign=\"top\" >

            <table width='100%' border='0'>
                <thead>
                    <tr height='22'><td colspan=2 >Tipo de grafica</td></tr>
                </thead>
                <tbody>
                    <tr><td>Tipo de grafica:</td><td>".$select_grafico."</td></tr>
                </tbody>
                <thead>
                    <tr><td colspan=\"2\">&nbsp;Fecha de Importaci&oacute;n</td></tr>
                </thead>
                <tbody>
                    <tr class=\"row1\">
                    <td style=\"width:100px;\">Fecha de inicio</td>
                    <td style=\"width:200px;\"><input name=\"fecha_ini\" id=\"fecha_ini\" value=\"$fecha_ini\"><img src=\"../img/calendar.gif\" id=\"f_trigger_c\" style=\"border: 1px solid white; cursor: pointer;\" title=\"Fecha\" onmouseover=\"this.style.background='red';\" onmouseout=\"this.style.background=''\"></td>
                    </tr>
                    <script>
                    Calendar.setup({inputField :'fecha_ini',ifFormat :'%Y-%m-%d',onUpdate : update_fecha_fin,button : 'f_trigger_c'});
                    </script>
                    <tr class=\"row1\">
                      <td>Fecha de fin</td>
                      <td><input name=\"fecha_fin\" id=\"fecha_fin\" value=\"$fecha_fin\"><img src=\"../img/calendar.gif\" id=\"f_trigger_d\" style=\"border: 1px solid white; cursor: pointer;\" title=\"Fecha\" onmouseover=\"this.style.background='red';\" onmouseout=\"this.style.background=''\"></td>
                    </tr>
                    <tbody>
                    <script>
                    Calendar.setup({inputField :'fecha_fin',ifFormat :'%Y-%m-%d',onUpdate : update_fecha_ini,button : 'f_trigger_d'});
                    </script>
                    <tr>
                        <td colspan=\"2\">
                            <table id=\"displayFilter\" style=\"text-align: center; width: 100%;\" border=\"0\" cellpadding=\"2\" cellspacing=\"2\">
                            <thead>
                            <tr>
                                <td colspan=2 align=\"left\">&nbsp;&nbsp;Filtro por vehiculo</td>
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
                            <tr><td>&nbsp;</td></tr>
                            </tbody>
                            <thead>
                            <tr>
                                <td colspan=2 align='left'>&nbsp;Origen</td>
                            </tr>
                            </thead>
                            <tr class=\"row1\"><td>Origen Padre</td><td>".$select_origenPadre."</td></tr>
                            </table>
                            </td>
                        </tr>
                    </table>
                </td>
                <td width=\"50%\" valign=\"top\" >
                  <table width=\"100%\" border=\"0\">
                    <thead>
                        <tr>
                        <td colspan=2 >&nbsp;Grupo Empresarial</td>
                        </tr>
                    </thead>
                        <tr class=\"row1\"><td width=\"22%\">Grupo</td><td>".$select_empresarial."</td></tr>
                    </table>
                    <div id=\"ubicacion\">
                    <table width=\"100%\" border=\"0\" align=\"center\">
                        <thead>
                        <tr>
                        <td colspan=2 >&nbsp;Ubicación</td>
                        </tr>
                        </thead>
                        <tr class=\"row1\"><td>Regi&oacute;n</td><td>".$select_regiones."</td></tr>
                        <tr class=\"row1\"><td>Zona</td><td>".$select_zonas."</td></tr>
                        <tr class=\"row1\"><td>Entidad</td><td>".$select_entidad."</td></tr>
                        <tr class=\"row1\"><td>Plaza</td><td>".$select_plaza."</td></tr>
                        <!--<tr class=\"row1\"><td>Distribuidor</td><td>".$select_concesion."</td></tr>-->
                    </table>
                    </div>
                    <table width=\"100%\"  border=\"0\" align=\"center\">
                        <!--<thead>
                        <tr>
                        <td colspan=2 >Nivel de Distribuidor</td>
                        </tr>
                        </thead>
                        <tr class=\"row1\"><td>Nivel</td><td>$select_categoria</td></tr>-->
                    </table>
                    </td>
                </tr>
                <thead><tr>
                     <td colspan=\"2\" align=\"center\">&#32;<!--<input type=\"submit\" name=\"filterVehicle\"  id='filterVehicle' value=\"Generar Gráfica\">--></td>
                </tr></thead>
               </table>
               <center><input type=\"submit\" name=\"filterVehicle\"  id='filterVehicle' value=\"Generar Gráfica\"></center>
";