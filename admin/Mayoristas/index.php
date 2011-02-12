<?

	if(!defined('_IN_ADMIN_MAIN_INDEX'))
		die("No puede acceder directamente a esta ruta");
		
	global $db, $gid, $_site_title, $del;
	
	$_site_title = "Mayorista";
  
  $_html .= "<h1>Mayoristas</h1>";
  
  $_html .= "
    <script>
      function del(id,name)
      {
        if(confirm('Esta seguro que desea eliminar a la Distribuidora '+name))
          location.href=('index.php?_module=$_module&del='+id);                
      }
    </script>";
  $_html .= "<table>
			<tr>
				<th>Razon Social</th>
				<th>RFC</th>
				<th>Nombre de contacto</th>
				<th>Telefono</th>
				<th>Email</th>
        <th>Acccion</th>
			</tr>";
	
  if($del)
  {
    $db->sql_query("UPDATE crm_mayoristas SET status=0 WHERE id_mayorista='$del'");
  }
  
	$sql = "SELECT id_mayorista, razon_social, rfc, contacto_nombre, contacto_telefono, contacto_email FROM crm_mayoristas WHERE status=1";
	$resultado = $db->sql_query($sql) or die("Error");
  
  $count = 1;
	while(list($mid, $razon_social, $rfc, $contacto_nombre, $contacto_telefono, $contacto_email) = $db->sql_fetchrow($resultado))
	{
    $cls_cnt = $count % 2 == 1 ? "class='row1'" : "";
		$_html .= "
			<tr $cls_cnt>
				<td style=\"padding:8px;\">$razon_social</td>
				<td style=\"padding:8px;\">$rfc</td>
				<td style=\"padding:8px;\">$contacto_nombre</td>
				<td style=\"padding:8px;\">$contacto_telefono</td>
        <td style=\"padding:8px;\">$contacto_email</td>
        <td><a href='index.php?_module=$_module&del=$mid' onclick='if(confirm('Deseas eliminar a $razon_social'))return true;else return false;'><img border='0' onmouseover=\"return escape('Borrar')\" src='../img/del.gif'</a></td>
			</tr>";
      $count++;
	}
  
  $_html .= "</table>";

?>