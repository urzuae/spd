<?

include_once("Monitoreo/genera_excel.php");
  if (!defined('_IN_ADMIN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}
global $db, $campana_id, $gid, $uid, $fecha_ini, $fecha_fin, $origen_id ;
global $_admin_menu2, $_admin_menu,$_excel;

include_once("crea_Filtros_Reportes_T.php");
$filtro='';
if( count($tmp_filtros) > 0)
{
    $filtro=" AND ".implode(" AND ",$tmp_filtros);
}
if(count($tmp_filtros_conc) > 0)
{
    $filtro_conce=" AND ".implode(" AND ",$tmp_filtros_conc);
}
if(count($tmp_filtros_mod) > 0)
{
    $filtro_modelo=" AND ".implode(" AND ",$tmp_filtros_mod);
}
if(count($tmp_filtros_v)>0)
{
   $filtro_fecha=" AND ".implode(" AND ",$tmp_filtros_v);
}
include_once("templateFiltrosReportes_T.php");
include_once("construye_grafica_t.php");
?>