<?php
if (!preg_match("/index.php/",$_SERVER['PHP_SELF'])) {
    Header("Location: index.php");
    die();
}
//valores para la libreria db

$_dbtype = 'MySQL';
$_dbhost = 'localhost';

//valores para el cms (directorios)
$_include_files='files';
$_include_img='img';
$_includesdir = 'includes';
$_modulesdir = 'modules';
$_htmldir = 'html'; //lo convertiremos en esto -> "$modulesdir/$_module/html"
$_configsdir = 'configs';

//opciones
$_defaultmodule = 'Bienvenida';
$_site_title = 'Sales Funnel';
$_csv_delimiter = ",";

if (defined('_IN_ADMIN_MAIN_INDEX'))
{
    $_includesdir = "../".$_includesdir;
    $_configsdir = "../".$_configsdir;
}
require_once("$_includesdir/site_config.php");
$_site_name = get_site_name();
$title = $_site_name;
$date = date('d-m-Y h:i:s A');

$_dbuname = "sf_$_site_name";
$_dbname = "sf_$_site_name";
$_dbpass = "sf_$_site_name";

if (!file_exists("$_configsdir/$_site_name.php")) 
    die("No existe CONFIG para el sitio \"$_site_name\"");
require_once("$_configsdir/$_site_name.php");