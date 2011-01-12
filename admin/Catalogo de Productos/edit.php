<? 
if (!defined('_IN_ADMIN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}

global $db, $art_id, $nombre, $parent_id, $img, $doc, $submit;
if ($parent_id == "") $parent_id = 0;
if (!$art_id) //nuevo
{
    if ($submit) //guardar
    {
        $sql = "INSERT INTO crm_catalogo VALUES('','$nombre', '$parent_id')";
        $db->sql_query($sql) or die("Error al insertar. ".print_r($db->sql_error()));
        $new_id = $db->sql_nextid();
        if (is_uploaded_file($_FILES['img']['tmp_name']))
        {
            $name = $_FILES['img']['name'];
            //buscar la extensión del archivo
            $offset = 0;
            while ($offset = strpos($name, ".", $offset + 1))
                $lastdot = $offset;
            if ($lastdot <= 0) break;
//             $ext = substr($name, $lastdot);
            $name = $new_id.".jpg";
            move_uploaded_file($_FILES['img']['tmp_name'], "$_module/files/$name");
            chmod("$_module/files/$name", 0666);
//             copy("$_module/img/$name", "$_module/img/$new_id");
            //el enlace es para ke se adivine la extensión
//             symlink("$name", "$_module/img/$new_id");
        }
        if (is_uploaded_file($_FILES['doc']['tmp_name']))
        {
            $name = $_FILES['doc']['name'];
            //buscar la extensión del archivo
            $offset = 0;
            while ($offset = strpos($name, ".", $offset + 1))
                $lastdot = $offset;
            if ($lastdot <= 0) break;
//             $ext = substr($name, $lastdot);
            $name = $new_id.".pdf";
            move_uploaded_file($_FILES['doc']['tmp_name'], "$_module/files/$name");
            chmod("$_module/files/$name", 0666);
//             copy("$_module/doc/$name", "$_module/doc/$new_id");
//             symlink("$name", "$_module/doc/$new_id");
        }
        header("location:index.php?_module=$_module");
    }
    $msg = "Artículo nuevo";
}
else
{
    if ($submit) //guardar
    {
        $sql = "UPDATE crm_catalogo SET nombre='$nombre' WHERE art_id='$art_id'";
        $db->sql_query($sql) or die("Error al actualizar. ".print_r($db->sql_error()));
        $new_id = $art_id;
        if (is_uploaded_file($_FILES['img']['tmp_name']))
        {
            $name = $_FILES['img']['name'];
            //buscar la extensión del archivo
            $offset = 0;
            while ($offset = strpos($name, ".", $offset + 1))
                $lastdot = $offset;
            if ($lastdot <= 0) break;
//             $ext = substr($name, $lastdot);
            $name = $new_id.".jpg";
            move_uploaded_file($_FILES['img']['tmp_name'], "$_module/files/$name");
            chmod("$_module/files/$name", 0666);
//             symlink("$name", "$_module/img/$new_id");
        }
        if (is_uploaded_file($_FILES['doc']['tmp_name']))
        {
            $name = $_FILES['doc']['name'];
            //buscar la extensión del archivo
            $offset = 0;
            while ($offset = strpos($name, ".", $offset + 1))
                $lastdot = $offset;
            if ($lastdot <= 0) break;
//             $ext = substr($name, $lastdot);
            $name = $new_id.".pdf";
            move_uploaded_file($_FILES['doc']['tmp_name'], "$_module/files/$name");
            chmod("$_module/files/$name", 0666);
//             symlink("$name", "$_module/doc/$new_id");
        }
        header("location:index.php?_module=$_module");
    }
    //leer valores
    $sql = "SELECT nombre, parent_id FROM crm_catalogo WHERE art_id='$art_id'";
    $result = $db->sql_query($sql) or die("Error al leer.".print_r($db->sql_error()));
    list($nombre, $parent_id) = htmlize($db->sql_fetchrow($result));
    if (file_exists("$_module/files/$art_id.jpg"))
        $link_img = "<a href=\"$_module/files/".("$art_id").".jpg\" target=\"link\">link</a>";
    if (file_exists("$_module/files/$art_id.pdf"))
    $link_doc = "<a href=\"$_module/files/".("$art_id").".pdf\" target=\"link\">link</a>";
    $msg = "Editando artículo: $nombre";
}

?> 
