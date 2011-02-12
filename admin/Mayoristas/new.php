<?
  if (!defined('_IN_ADMIN_MAIN_INDEX'))
    die("No puedes accesar directamente a esta ruta");
    
  global $db, $uid, $gid, $submit, $razon_social, $contacto_nombre, $contacto_email, $contacto_telefono, $rfc;
  
  $html = "";
  
  if($submit)
  {
    if(!$rfc && !preg_match('/\A[a-z]{3,4}\d{2}[0-1]\d[0-3]\d[a-z]{2}\d\z/i',$rfc))
      $html .= "RFC debe ser un campo correcto";
    if(!$razon_social)
      $html .= "Razon social debe ser un campo correcto";
    if(!$contacto_nombre)
      $html .= "Nombre debe ser un campo correcto";
    if(!$contacto_telefono && !preg_match('/\A[\d]{8,13}\z/', $contacto_telefono))
      $html .= "Telefono debe ser un campo correcto";
    if(!$contacto_email && !preg_match('/\A[a-z]([a-z\d][\w\.]?)+@[a-z\d]+[\w\-]?(\.[\w]+)+\z/i', $email))
      $html .= "Email debe ser un campo correcto";
    
    if($html)
      print "$html";
    else{
      $sql = "INSERT INTO crm_mayoristas (razon_social, rfc, contacto_nombre, contacto_telefono, contacto_email)
        VALUES ('$razon_social', '$rfc', '$contacto_nombre', '$contacto_telefono', '$contacto_email')";
      $db->sql_query($sql) or die("+Error al crear mayorista+".print_r($db->sql_error()));
      header("location: index.php?_module=Mayoristas");
    }
  }
  
?>