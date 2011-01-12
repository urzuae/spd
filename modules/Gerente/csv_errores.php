<?php
include_once("admin/Clases/Regresa_nombre_sistema.php");
$file_csv = $system_name."-".date("Y-m-d").".csv";
header('Content-type: application/csv');
header(sprintf('Content-Disposition: attachment; filename="%s"',$file_csv));
readfile("./files/".$file_csv);

exit;
?>