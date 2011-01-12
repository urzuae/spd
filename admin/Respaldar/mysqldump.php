<?
global $_dbname;
$fname = $_dbname."-".date("ymdHi").".sql";
$cmd = "mysqldump -u root --databases $_dbname > ".$fname;
$salida = array();
$var_retorno = "";
exec($cmd, $salida, $var_retorno);
if ($var_retorno)
{
  @unlink($fname);
  $_html = "<h1>Error al crear archivo</h1>";
}
else
  $_html = "<h1>Archivo creado exitosamente</h1><br>Descargar <a href=\"$fname\">$fname</a> ";

?>