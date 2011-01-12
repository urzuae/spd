<?php
if (!defined('_IN_MAIN_INDEX') && !defined('_IN_ADMIN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}


require_once("$_includesdir/db/db.php"); //no usamos db aki aun

if (!ini_get("register_globals")) {
    import_request_variables('GPC'); //get, post & cookies
}
if (!function_exists('fputcsv'))
{
    function fputcsv($filePointer,$dataArray,$delimiter=",",$enclosure="\""){
    // Write a line to a file
    // $filePointer = the file resource to write to
    // $dataArray = the data to write out
    // $delimeter = the field separator
        
    // Build the string
    $string = "";
    
    // for each array element, which represents a line in the csv file...
//     foreach($dataArray as $line){
    $line = $dataArray;
    
        // No leading delimiter
        $writeDelimiter = FALSE;
        
        foreach($line as $dataElement){
            // Replaces a double quote with two double quotes
            $dataElement=str_replace("\"", "\"\"", $dataElement);
            
            // Adds a delimiter before each field (except the first)
            if($writeDelimiter) $string .= $delimiter;
            
            // Encloses each field with $enclosure and adds it to the string
            $string .= $enclosure . $dataElement . $enclosure;
            
            // Delimiters are used every time except the first.
            $writeDelimiter = TRUE;
        }
        // Append new line
        $string .= "\n";
    
//     } // end foreach($dataArray as $line)
    
    // Write the string to the file
    fwrite($filePointer,$string);
    }

}
// foreach ($_GET as $secvalue) {
//     if ((eregi("<[^>]*script*\"?[^>]*>", $secvalue)) ||
// 	(eregi("<[^>]*object*\"?[^>]*>", $secvalue)) ||
// 	(eregi("<[^>]*iframe*\"?[^>]*>", $secvalue)) ||
// 	(eregi("<[^>]*applet*\"?[^>]*>", $secvalue)) ||
// 	(eregi("<[^>]*meta*\"?[^>]*>", $secvalue)) ||
// 	(eregi("<[^>]*style*\"?[^>]*>", $secvalue)) ||
// 	(eregi("<[^>]*form*\"?[^>]*>", $secvalue)) ||
// 	(eregi("<[^>]*img*\"?[^>]*>", $secvalue)) ||
// 	(eregi("\([^>]*\"?[^)]*\)", $secvalue)))
// 	die ("I don't like you...");
// }

// foreach ($_POST as $secvalue) {
//     if ((eregi("<[^>]*script*\"?[^>]*>", $secvalue)) ||	(eregi("<[^>]*style*\"?[^>]*>", $secvalue))) {
//         Header("Location: index.php");
//         die();
//     }
// }

//funciones comunes para todo el sistema

//LOGIN

//la siguiente funcion toma un user y pass y los cheka en la db
//si esta bien entonces krea la kookie, debe de llamarse antes de imprimir algo en pantalla
//$_POST[_user], $_POST[_password]
function login($user, $password) { 
	global $db;	
  $user = strtoupper($user);
  $password = strtoupper($password); 
  $password_orig = $password;
  $user_orig = $user;
	$sql = "SELECT uid, user, password, last_login FROM users WHERE user='$user' AND password=PASSWORD('$password')";
	$result = $db->sql_query($sql) or die("Error al consultar user y password en db");
	if ($db->sql_numrows($result) > 0) {
		list($uid, $user, $password, $last_login) = $db->sql_fetchrow($result);
 		setcookie("_uid", $uid); //si no tiene expire se cierra con el navegador, si no poner esto: time()+60*60*24*1 en 1 dia
 		setcookie("_user", md5($user));
 		setcookie("_password", md5($password));
        //registrar kuando hicimos el login y de donde
        $from = $_SERVER['REMOTE_ADDR'];
        if ($_SERVER['REMOTE_HOST'] != "") $from .= "->".$_SERVER['REMOTE_HOST'];
        $db->sql_query("UPDATE users SET last_login=NOW(), logged_from='$from' WHERE uid='$uid' LIMIT 1");
        $db->sql_query("INSERT INTO users_logins_logs (`uid`,`from`,`timestamp`)VALUES('$uid', '$from', NOW())");
        //necesitamos recargar para ke las cookies tengan efekto
        global $_module, $_op, $_defaultmodule, $_op;

	      //si no se establece nada diferente mandar vacio para ke se setee al default afterlogin
	      if (($_module == $_defaultmodule) && ($_op == "" || $_op == "index" || !isset($_op)))
	          header("location: index.php");
	      else
	          header("location: index.php?_module=$_module&_op=$_op&campana_id=$campana_id");

	}
	else 
        return false;
}
function logout() {
	$time = time();
	setcookie ("_user", "", $time-3200);
	setcookie ("_password", "", $time-3200);
	setcookie ("_uid", "", $time-3200);
	header("Location: index.php"); 
}

//REDIRECCION
function redirect($where, $message) {
	echo "<html><head><title>$message</title><meta http-equiv=\"refresh\" content=\"5;url=$where\" /></head><body><h2>$message</h2><a href=\"$where\">Continuar</a></body></html>";
	die();
}

//funcion ke se usa en index.php para del html regresar solo lo de adentro del body
function _html_get_body($html)
{
    //si se encuentra un tag body entonces solo usar lo que esta dentro
    if (stristr($html, "<body"))
    {
        $html = stristr($html, "<body");
        $html = stristr($html, ">");
        $html = substr($html, 1); //aki ya no tiene <body>
        $tmp = stristr($html, "</body>");
        $html = substr($html, 0, strlen($html) - strlen($tmp));
    }
    return $html;
}

// funcion para ke konvierte lo ke esta dentro del array en karakteres seguros en html
function htmlize($array)
{
    if (is_array($array))
    {
            $array2 = array(); //por alguna razon hay ke krear uno nuevo para poder regresarlo
            foreach ($array as $key => $ent)
            {
                $array2[$key] = (htmlentities($ent, ENT_QUOTES, "ISO-8859-1"));
            }
             reset($array2);
            return $array2;
    }
    return false;
}
function date_reverse($date)
{
    if ($date == "") return "";
    list($y, $m, $d) = explode("-", $date);
    return "$d-$m-$y";
}
function date_reverse2($date)
{
    if ($date == "") return "";
    list($m, $d, $y) = explode("-", $date);
    return "$y-$m-$d";
}

function money_format2($x, $n)
{
  return "$ ".number_format($n, 2);
}

function remove_money_format2($money)
{
  return ereg_replace("[-, $]", "", $money);
}


function fckeditor($height = "400", $name = "text", $value = "")
{
    global $_includesdir;
    include("$_includesdir/fckeditor/fckeditor.php");
    $oFCKeditor = new FCKeditor("$name") ;
    $oFCKeditor->BasePath = "$_includesdir/fckeditor/";
    $oFCKeditor->Height = "$height";
    $oFCKeditor->Config['AutoDetectLanguage']   = false ;
    $oFCKeditor->Config['DefaultLanguage']      = "es";
    $oFCKeditor->Value = "$value";
    return $oFCKeditor->CreateHtml() ;

}

?>
