<?
/*  if (!defined('_IN_ADMIN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}

global $db, $submit, $del;
if ($submit)
{
  $filename = $_FILES['f']['tmp_name'];
  $fh = fopen($filename, "r");
  $table = "<table border=1>";
  while(list($n, $gid) = fgetcsv($fh, 1000, ","))
  {
    list($pat, $mat, $n1) = explode(" ", $n);
    $nick = $n1[0].$pat.$mat[0];
    $sql = "INSERT INTO users (gid, user, name, password)VALUES('$gid', '$nick', '$n', PASSWORD('".strtolower($nick)."'))";
    $db->sql_query($sql);// or die(print_r($db->sql_error())."<br>$sql");
  }

}*/

  if (!defined('_IN_ADMIN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}
global $db,$submit,$del,$groups,$email,$row,$sql,$user;
function comprobar_email($email){
    $mail_correcto = 0;
    //compruebo unas cosas primeras
    if ((strlen($email) >= 6) && (substr_count($email,"@") == 1) && (substr($email,0,1) != "@") && (substr($email,strlen($email)-1,1) != "@")){
       if ((!strstr($email,"'")) && (!strstr($email,"$")) && (!strstr($email," "))) {
          //miro si tiene caracter .
          if (substr_count($email,".")>= 1){
             //obtengo la terminacion del dominio
             $term_dom = substr(strrchr ($email, '.'),1);
             //compruebo que la terminaci?n del dominio sea correcta
             if (strlen($term_dom)>1 && strlen($term_dom)<5 && (!strstr($term_dom,"@")) ){
                //compruebo que lo de antes del dominio sea correcto
                $antes_dom = substr($email,0,strlen($email) - strlen($term_dom) - 1);
                $caracter_ult = substr($antes_dom,strlen($antes_dom)-1,1);
                if ($caracter_ult != "@" && $caracter_ult != "."){
                   $mail_correcto = 1;
                }
             }
          }
       }
    }
    if ($mail_correcto)
       	return true;
    else
        return false;
 }

 function getGroups($db){
 //obtener la lista de concesionarias
		$grupo = array();
		$sql = "SELECT gid FROM groups";
		$r = $db->sql_query($sql) or die($sql);
		while($row=$db->sql_fetchrow($r)){
			$grupo[]=$row['gid'];
		}
		return $grupo;
 }

 function getUsersType($db){
 //obtener la lista de tipo de usuarios
		$tipo = array();
		$sql = "SELECT tipo_id FROM users_types";
		$r = $db->sql_query($sql) or die($sql);
		while($row=$db->sql_fetchrow($r)){
			$tipo[]=$row['tipo_id'];
		}
 		return $tipo;
 }

 function getUser($user,$db){
 //obtener la lista de tipo de usuarios
		$sql = "SELECT user FROM users where user='".$user."'";
		$r=$db->sql_query($sql);
		$row=$db->sql_fetchrow($r);
		if($row['user']!='')
				return false;
			else
				return true;
}

if ($submit)
{
 	 $error=0;

	 $filename = $_FILES['f']['tmp_name'];

	if($_FILES['f']['type']!='text/csv'){
		$error++;
		$msge="Tipo de archivo inv?lido ";
	}
	if(!$filename){
		$error++;
		$msge="No hay archivo, favor de cargarlo ";
	}

	if($error==0){
		$msge="";

		  $fh = fopen($filename, "r");
		  $table = "<table border=1>";
		  $contRow=0;
		  $contRowBad=0;
		  $contRowOk=0;
		  $arrayError=array();
		  while (($data = fgetcsv($fh, 1000, "|")) !== FALSE) {
			    	$contRow++;
			    	$errorColum=0;
		    		list($gid,$super,$user,$email)=$data;
		  			if(!array_search($gid,getGroups($db))){
			    		$errorColum++;
			    		$arrayError[$contRow]['gid']="GID <".$gid."> Incorrecto";
			    	}

		  			if(in_array($super,getUsersType($db))==false){
		    			$errorColum++;
		    			$arrayError[$contRow]['super']="Tipo de Usuario <".$super."> Incorrecto";
			    	}

			    	if($user==''){
			    		$errorColum++;
			    		$arrayError[$contRow]['user']="Falta incluir user";
			    	}

			    	if(getUser($user,$db)==false){
			    		$errorColum++;
			    		$arrayError[$contRow]['user']="Ya existe el user: ".$user;
			    	}

		  			if(comprobar_email($email)==false){
		    			$errorColum++;
		    			$arrayError[$contRow]['mail']="email < ".$email." > Incorrecto";
		    		}

		    	if ($errorColum==0){
			    		$contRowOk++;
			    		$sql = "INSERT INTO users (gid, super, user, password, name, email, active)VALUES('$gid', '$super', '$user', PASSWORD('".strtolower($user)."'),'$user','$email',0)";
						$db->sql_query($sql) or die(print_r($db->sql_error())."<br>$sql");
					}else
						$contRowBad++;

		    }


		     fclose($fh);

		     $msgResult = "<table align='center'>
	          <thead>
	            <tr>
	              <td colspan=\"2\"> Registro de la carga de usuarios</td>
	            </tr>
	          </thead>
	          <tr class=\"row1\">
	            <td colspan=\"2\">$contRow registros esperados</td>
	          </tr>
	          <tr class=\"row2\">
	            <td colspan=\"2\">$contRowOk registros procesados</td>
	          </tr>
	          <tr class=\"row1\">
	            <td colspan=\"2\">$contRowBad registros incorrectos</td>
	          </tr>
	         ";

	         $msgResultError.="
	         	<table align='center'>
	          <thead>
	            <tr>
	              <td colspan=\"5\" align='center'> Reporte de errores</td>
	            </tr>
	            <tr align='center'>
	             <td>No</td>
	            <td>GID</td>
	            <td>Super</td>
	            <td>User</td>
	            <td>email</td>
	          </tr>
	          ";

	      foreach($arrayError as $clave => $valor){
	      $cont=0;
			       foreach($valor as $c => $v)
			       {
					if($cont<1){
			       $msgResultError.="
						<tr class=\"row1\" align='center'>
			             <td>".$clave."</td>
			            <td>".$valor['gid']."</td> <td>".$valor['super']."</td> <td>".$valor['user']."</td> <td>".$valor['mail']."</td>
			          </tr>
						";
					}$cont++;
			       }
	    	    }

	      	$msgResultError.="</table>";
		}
	}
 ?>