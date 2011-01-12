<?
if (!defined('_IN_ADMIN_MAIN_INDEX'))
{
    die ("No puedes acceder directamente a este archivo...");
}
global $db, $submit, $del;
$counter=0;
$modelo_almacenado=0;
$version_almacenado=0;
$transmision_almacenado=0;
$version_transmision_id=0;
$modelo_version_id=0;

if ($submit)
{
    $filename = $_FILES['f']['tmp_name'];
    $file= $_FILES['f']['name'];
    if($filename == '')
    {
        $msg="<font color='red' size='2'>Favor de seleccionar un archivo</font><br>";
    }
    else
    {
        if(substr($file,(strlen($file)-4),strlen($file)) != ".csv")
        {
            $msg="<font color='red' size='2'>El archivo debe estar en formato CSV y de limitados por comas(,).</font><br>";
        }
        else
        {
            $fh = fopen($filename, "r");
            if (!$fh)
            {
                $msg="<font color='red' size='2'>Error, no se puede leer el archivo (tal vez sea demasiado grande o no este delimitado por comas (,)) ".$filename."</font><br>";
            }
            else
            {
                $unidades      = Genera_Modelos($db);
                $versiones     = Genera_Versiones($db);
                $transmisiones = Genera_Transmisiones($db);
                
                while($data = fgetcsv($fh, 1000, ","))
                {
                    if (!($ii++))
                    continue; //se salta el primer campo
                    $data2 = array();
                    foreach ($data as $undato)
                    {
                        $data2[] = addslashes($undato);
                    }
                    list($modelo,$version,$transmision) = $data2;
                    $counter++;

                    // Reviso que el modelo exista
                    $modelo_carga=$modelo;
                    $modelo_id=Revisa_Modelo($db,strtoupper(trim($modelo)));
                    $modelo=$unidades[$modelo_id];
                    if($modelo_id == 0)
                    {
                        // lo damos de alta
                        $sql_modelo="INSERT INTO crm_unidades (nombre) VALUES ('".$modelo_carga."');";
                        if($db->sql_query($sql_modelo) or die ("Error al cargar el modelo  ".$sql_modelo))
                        {
                            $modelo_almacenado++;
                            $modelo_id=$db->sql_nextid();
                        }
                    }
                    // Reviso que el version exista
                    $version_carga=$version;
                    $version_id=Revisa_Version($db,strtoupper(trim($version)));
                    $version=$versiones[$version_id];
                    if($version_id == 0)
                    {
                        // lo damos de alta
                        $sql_version="INSERT INTO crm_versiones (nombre) VALUES ('".$version_carga."');";
                        if($db->sql_query($sql_version) or die ("Error al cargar la version  ".$sql_version))
                        {
                            $version_almacenado++;
                            $version_id=$db->sql_nextid();
                        }
                    }
                    // Reviso que el transmision exista
                    $transmision_carga=$transmision;
                    $transmision_id=Revisa_Transmision($db,strtoupper(trim($transmision)));
                    $transmision=$transmisiones[$transmision_id];
                    if($transmision_id == 0)
                    {
                        // lo damos de alta
                        $sql_transmision="INSERT INTO crm_transmisiones (nombre) VALUES ('".$transmision_carga."');";
                        if($db->sql_query($sql_transmision) or die ("Error al cargar la transmision  ".$sql_transmision))
                        {
                            $transmision_almacenado++;
                            $transmision_id=$db->sql_nextid();
                        }
                    }
                    //Revisamos los catalogos Vehiculos - Version
                    $sql_v_v="SELECT * FROM crm_vehiculo_versiones WHERE vehiculo_id=".$modelo_id." AND	version_id=".$version_id.";";
                    $res_v_v=$db->sql_query($sql_v_v);
                    if($db->sql_numrows($res_v_v) == 0)
                    {
                        $sql_ins_vv="INSERT INTO crm_vehiculo_versiones (vehiculo_id,version_id) VALUES (".$modelo_id.",".$version_id.");";
                        if( $db->sql_query($sql_ins_vv))
                            $modelo_version_id++;
                    }

                    //Revisamos los catalogos  Version - Transmision
                    $sql_v_t="SELECT * FROM crm_version_transmisiones WHERE version_id=".$version_id." and transmision_id=".$transmision_id.";";
                    $res_v_t=$db->sql_query($sql_v_t);
                    if($db->sql_numrows($res_v_t) == 0)
                    {
                        $sql_ins_vt="INSERT INTO crm_version_transmisiones (version_id,transmision_id) VALUES (".$version_id.",".$transmision_id.");";
                        if( $db->sql_query($sql_ins_vt))
                            $version_transmision_id++;
                    }


                }
            }
            $msg = "$counter registros procesados.<br>
                    Modelos dados de alta:  ".($modelo_almacenado + 0)."<br>
                    Versiones dadas de alta:  ".($version_almacenado + 0)."<br>
                    Transmisiones dadas de alta:  ".($transmision_almacenado + 0)."<br>
                    Catalogo Modelo - versiones dadas de alta: ".($modelo_version_id + 0)."<br>
                    Catalogo Version - Transmision dadas de alta:   ".($version_transmision_id + 0)."<br><br>";
        }
    }
}
function Genera_Modelos($db)
{
    $sql = "SELECT unidad_id, nombre FROM crm_unidades";
    $r = $db->sql_query($sql) or die($sql);
    $unidades = array();
    while (list($id, $n) = $db->sql_fetchrow($r))
    $unidades[$id] = $n;
    return $unidades;
}
function Revisa_Modelo($db,$modelo)
{
    $id=0;
    $sql="SELECT unidad_id FROM crm_unidades where upper(nombre)='".$modelo."';";
    $res=$db->sql_query($sql) or die("Error en el query:  ".$sql);
    if($db->sql_numrows($res)>0)
        $id=$db->sql_fetchfield(0,0,$res);
    return $id;
}
function Revisa_Version($db,$version)
{
    $id=0;
    $sql="SELECT version_id FROM crm_versiones where upper(nombre)='".$version."';";
    $res=$db->sql_query($sql) or die("Error en el query:  ".$sql);
    if($db->sql_numrows($res)>0)
        $id=$db->sql_fetchfield(0,0,$res);
    return $id;
}
function Genera_Versiones($db)
{
    $sql = "SELECT version_id, nombre FROM crm_versiones";
    $r = $db->sql_query($sql) or die($sql);
    $versiones = array();
    while (list($id, $n) = $db->sql_fetchrow($r))
    $versiones[$id] = $n;
    return $versiones;
}
function Revisa_Transmision($db,$transmision)
{
    $id=0;
    $sql="SELECT transmision_id FROM crm_transmisiones where upper(nombre)='".$transmision."';";
    $res=$db->sql_query($sql) or die("Error en el query:  ".$sql);
    if($db->sql_numrows($res)>0)
        $id=$db->sql_fetchfield(0,0,$res);
    return $id;
}
function Genera_Transmisiones($db)
{
    $sql = "SELECT transmision_id, nombre FROM crm_transmisiones";
    $r = $db->sql_query($sql) or die($sql);
    $transmisiones = array();
    while (list($id, $n) = $db->sql_fetchrow($r))
    $transmisiones[$id] = $n;
    return $transmisiones;
}
 ?>