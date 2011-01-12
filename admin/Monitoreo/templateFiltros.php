<?php
$buffer_filtros="

    <table width=\"40%\" height=\"190px\" border=\"0\" align=\"center\">
        <thead>
            <tr>
                <td colspan=\"2\">Opciones de reporte</td>
            </tr>
	</thead>
	<thead>
            <tr>
                <td colspan=2><img src=\"../img/date.png\" height=\"16\" wight=\"16\">&nbsp;Fecha de Importaci&oacute;n</td>
            </tr>
        </thead>
            <tr class=\"row1\">
                <td style=\"width:100px;\">Fecha de inicio</td>
		<td style=\"width:200px;\"><input name=\"fecha_ini\" id=\"fecha_ini\" value=\"$fecha_ini\"><img src=\"../img/calendar.gif\" id=\"f_trigger_c\" style=\"border: 1px solid white; cursor: pointer;\" title=\"Fecha\" onmouseover=\"this.style.background='red';\" onmouseout=\"this.style.background=''\"></td>
            </tr>
            <script>
			function update_fecha_fin(cal)
			{
			  //checamos si es mayor la ini que la fin y cambiar el fin
			  var fecha_fin = document.getElementById('fecha_fin').value;
			  if (fecha_fin == '') return false;
			  var guion_1 = fecha_fin.indexOf('-');
			  var guion_2 = fecha_fin.indexOf('-', guion_1 + 1);
			  var guion_3 = fecha_fin.length;//fecha_fin.indexOf('-', guion_2 + 1);
			  var fin_d = fecha_fin.substring(0, guion_1);
			  var fin_m = fecha_fin.substring(guion_1 + 1, guion_2);
			  var fin_y = fecha_fin.substring(guion_2 + 1, guion_3);
			  var fin  = new Date(fin_y, fin_m - 1, fin_d);
			  var ini  = new Date(cal.date.getFullYear(), cal.date.getMonth(), cal.date.getDate());
			  if (ini.getTime() > fin.getTime())
			  {
			   document.getElementById('fecha_fin').value = cal.date.print('%Y-%m-%d');
			  }
			}

			function update_fecha_ini(cal)
			{
			  //checamos si es mayor la ini que la fin y cambiar el fin
			  var fecha_ini = document.getElementById('fecha_ini').value;
			  if (fecha_ini == '') return false;
			  var guion_1 = fecha_ini.indexOf('-');
			  var guion_2 = fecha_ini.indexOf('-', guion_1 + 1);
			  var guion_3 = fecha_ini.length;//fecha_ini.indexOf('-', guion_2 + 1);
			  var ini_d = fecha_ini.substring(0, guion_1);
			  var ini_m = fecha_ini.substring(guion_1 + 1, guion_2);
			  var ini_y = fecha_ini.substring(guion_2 + 1, guion_3);
			  var ini  = new Date(ini_y, ini_m - 1, ini_d);
			  var fin  = new Date(cal.date.getFullYear(), cal.date.getMonth(), cal.date.getDate());
			  if (ini.getTime() > fin.getTime())
			  {
			   document.getElementById('fecha_ini').value = cal.date.print('%Y-%m-%d');
			  }
			}
            Calendar.setup({inputField :'fecha_ini',ifFormat :'%Y-%m-%d',onUpdate : update_fecha_fin,button : 'f_trigger_c'});</script>
            <tr class=\"row1\">
                <td>Fecha de fin</td>
                <td><input name=\"fecha_fin\" id=\"fecha_fin\" value=\"$fecha_fin\"><img src=\"../img/calendar.gif\" id=\"f_trigger_d\" style=\"border: 1px solid white; cursor: pointer;\" title=\"Fecha\" onmouseover=\"this.style.background='red';\" onmouseout=\"this.style.background=''\"></td>
            </tr>
            <script>Calendar.setup({inputField :'fecha_fin',ifFormat :'%Y-%m-%d',onUpdate : update_fecha_ini,button : 'f_trigger_d'});</script>
            <thead>
                <tr>
                    <td colspan=2 ><img src=\"../img/user_go.png\" height=\"16\" widht=\"16\"><font align=\"center\">&nbsp;Grupo Empresarial</font></td>
                </tr>
            </thead>
            <tr class=\"row1\"><td width=\"22%\">Grupo</td><td>".$select_empresarial."</td></tr>
            </table>
            <div id=\"ubicacion\">
            <table width=\"40%\" height=\"190px\" border=\"0\" align=\"center\">
             <thead>
			<tr>
			 <td colspan=2 ><img src=\"../img/mexico2.png\" height=\"16\" widht=\"16\"><font align=\"center\">&nbsp;Ubicación</font></td>
			</tr>
			</thead>
			<tr class=\"row1\"><td>Regi&oacute;n</td><td>".$select_regiones."</td></tr>
			<tr class=\"row1\"><td>Zona</td><td>".$select_zonas."</td></tr>
			<tr class=\"row1\"><td>Plaza</td><td>".$select_plaza."</td></tr>
			<tr class=\"row1\"><td>Distribuidor</td><td>".$select_concesion."</td></tr>
            </table>
            </div>
            <table width=\"40%\" height=\"190px\" border=\"0\" align=\"center\">
            <thead>
                <tr>
                    <td colspan=2 ><font align=\"center\">Fuente</font></td>
		</tr>
            </thead>
            <tr class=\"row1\"><td></td><td>".$select_origenPadre."</td></tr>
            <thead>
            <tr>
            <tr class=\"row2\">
                <td colspan=\"2\" align=\"center\"><input type=\"submit\" name=\"submit\" value=\"Aceptar\"></td>
            </tr>
	</table>";