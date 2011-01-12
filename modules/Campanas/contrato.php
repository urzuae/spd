<?
  if (!defined('_IN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}
$_theme = "";
$_css = $_themedir."/style.css";
global $_site_title, $db, $submit, $how_many, $from, $campana_id, $contacto_id, $campana_id, $uid,
                    $nombre, $apellido_paterno, $apellido_materno,
                    $sexo, $nacionalidad,
                    $rfc,
                    $dia, $mes, $ano, //$fecha_de_nacimiento,
                    $edo_de_nac,
                    $domicilio,$domicilio_ext, $domicilio_int, $colonia,
                    $cp, $poblacion,
                    $entidad_id,
                    $tel_casa,
                    $tel_movil, $tel_otro,
                    $email,
                    $edo_civil, $dependientes,
                    $grado_de_estudios, $tipo_de_vivienda,
                    $residencia_anos, $residencia_meses,
                    $nombre_tarjeta,
                    $color,
                    $tamano,
                    $donde_recibir_edo_cuenta,
                    $nombre_empresa,
                    $tel_oficina, $tel_oficina_ext,
                    $domicilio_trabajo,$domicilio_trabajo_ext, $domicilio_trabajo_int,
                    $colonia_trabajo,
                    $poblacion_trabajo,
                    $cp_trabajo,
                    $entidad_id_trabajo,
                    $giro_empresa,
                    $actividad_laboral,
                    $ocupacion,
                    $ingresos,
                    $otros_ingresos,
                    $fuente_otros_ingresos,
                    $antiguedad_anos,
                    $antiguedad_meses,
                    $antiguedad_anos_anterior,
                    $antiguedad_meses_anterior,
                    $referencia_familiar_nombre,
                    $referencia_familiar_parentezco,
                    $referencia_familiar_telefono, $referencia_familiar_telefono_ext,
                    $referencia_no_familiar_nombre,
                    $referencia_no_familiar_telefono, $referencia_no_familiar_telefono_ext,
                    $seguro,
                    $proteccion,
                    $apellido_paterno_adicional1,
                    $apellido_materno_adicional1,
                    $nombre_adicional1,
                    $dia_adicional1, $mes_adicional1, $ano_adicional1, //$fecha_nacimiento_adicional1
                    $rfc_adicional1,
                    $parentezco_adicional1,
                    $nombre_tarjeta_adicional1,
                    $color_adicional1,
                    $tamano_adicional1,
                    $apellido_paterno_adicional2,
                    $apellido_materno_adicional2,
                    $nombre_adicional2,
                    $dia_adicional2, $mes_adicional2, $ano_adicional2, //$fecha_nacimiento_adicional2
                    $rfc_adicional2,
                    $parentezco_adicional2,
                    $nombre_tarjeta_adicional2,
                    $color_adicional2,
                    $tamano_adicional2,
                    $tdc1, $banco1,
                    $tdc2, $banco2,
                    $tdc3, $banco3,
                    $tdc4, $banco4,
                    $tdc5,
                    $banco5,
                    $banco6,
                    $banco7,
                    $banco8,
                    $banco9,
                    $domicilio_recoleccion,$domicilio_recoleccion_ext, $domicilio_recoleccion_int,
                    $colonia_recoleccion,
                    $poblacion_recoleccion,
                    $cp_recoleccion,
                    $entidad_id_recoleccion
                    ;
if (!$submit)
{
    //OBTENEMOS DATOS DE DB
    //datos de la persona
    $sql = "SELECT 
                    nombre, apellido_paterno, apellido_materno,
                    sexo,
                    rfc,
                    fecha_de_nacimiento,
                    domicilio, colonia,
                    cp, poblacion,
                    entidad_id,
                    tel_casa, tel_oficina,
                    tel_movil, tel_otro,
                    email
                FROM crm_contactos WHERE contacto_id='$contacto_id' LIMIT 1";
    $result = $db->sql_query($sql) or die("Error al consultar datos del contacto".print_r($db->sql_error()));
    list(
                    $nombre, $apellido_paterno, $apellido_materno,
                    $sexo,
                    $rfc,
                    $fecha_de_nacimiento,
                    $domicilio, $colonia,
                    $cp, $poblacion,
                    $entidad_id,
                    $tel_casa, $tel_oficina,
                    $tel_movil, $tel_otro,
                    $email
    ) = htmlize($db->sql_fetchrow($result));
    list($ano, $mes, $dia) = explode("-", $fecha_de_nacimiento);
}
else //guardar
{
    $sql = "INSERT INTO crm_contratos
    (
                    campana_id, contacto_id, uid,
                    nombre, apellido_paterno, apellido_materno,
                    sexo, nacionalidad,
                    rfc,
                    fecha_de_nacimiento, edo_de_nac,
                    domicilio, domicilio_ext, domicilio_int, colonia,
                    cp, poblacion,
                    entidad_id,
                    tel_casa, tel_movil,
                    email,
                    edo_civil, dependientes,
                    grado_de_estudios, tipo_de_vivienda,
                    residencia_anos, residencia_meses,
                    nombre_tarjeta,
                    color,
                    tamano,
                    donde_recibir_edo_cuenta,
                    nombre_empresa,
                    tel_oficina, tel_oficina_ext,
                    domicilio_trabajo, domicilio_trabajo_ext, domicilio_trabajo_int,
                    colonia_trabajo,
                    poblacion_trabajo,
                    cp_trabajo,
                    entidad_id_trabajo,
                    giro_empresa,
                    actividad_laboral,
                    ocupacion,
                    ingresos,
                    otros_ingresos,
                    fuente_otros_ingresos,
                    antiguedad_anos,
                    antiguedad_meses,
                    antiguedad_anos_anterior,
                    antiguedad_meses_anterior,
                    referencia_familiar_nombre,
                    referencia_familiar_parentezco,
                    referencia_familiar_telefono, referencia_familiar_telefono_ext,
                    referencia_no_familiar_nombre,
                    referencia_no_familiar_telefono, referencia_no_familiar_telefono_ext,
                    seguro,
                    proteccion,
                    apellido_paterno_adicional1,
                    apellido_materno_adicional1,
                    nombre_adicional1,
                    fecha_nacimiento_adicional1,
                    rfc_adicional1,
                    parentezco_adicional1,
                    nombre_tarjeta_adicional1,
                    color_adicional1,
                    tamano_adicional1,
                    apellido_paterno_adicional2,
                    apellido_materno_adicional2,
                    nombre_adicional2,
                    fecha_nacimiento_adicional2,
                    rfc_adicional2,
                    parentezco_adicional2,
                    nombre_tarjeta_adicional2,
                    color_adicional2,
                    tamano_adicional2,
                    tdc1, banco1,
                    tdc2, banco2,
                    tdc3, banco3,
                    tdc4, banco4,
                    tdc5,
                    banco5,
                    banco6,
                    banco7,
                    banco8,
                    banco9,
                    domicilio_recoleccion, domicilio_recoleccion_ext, domicilio_recoleccion_int,
                    colonia_recoleccion,
                    poblacion_recoleccion,
                    cp_recoleccion,
                    entidad_id_recoleccion
                    
    ) VALUES (".strtoupper("
                    '$campana_id', '$contacto_id', '$uid',
                    '$nombre', '$apellido_paterno', '$apellido_materno',
                    '$sexo', '$nacionalidad',
                    '$rfc',
                    '$ano-$mes-$dia', '$edo_de_nac',
                    '$domicilio', '$domicilio_ext', '$domicilio_int', '$colonia',
                    '$cp', '$poblacion',
                    '$entidad_id',
                    '$tel_casa', '$tel_movil',
                    '$email',
                    '$edo_civil', '$dependientes',
                    '$grado_de_estudios', '$tipo_de_vivienda',
                    '$residencia_anos', '$residencia_meses',
                    '$nombre_tarjeta',
                    '$color',
                    '$tamano',
                    '$donde_recibir_edo_cuenta',
                    '$nombre_empresa',
                    '$tel_oficina', '$tel_oficina_ext',
                    '$domicilio_trabajo', '$domicilio_trabajo_ext', '$domicilio_trabajo_int',
                    '$colonia_trabajo',
                    '$poblacion_trabajo',
                    '$cp_trabajo',
                    '$entidad_id_trabajo',
                    '$giro_empresa',
                    '$actividad_laboral',
                    '$ocupacion',
                    '$ingresos',
                    '$otros_ingresos',
                    '$fuente_otros_ingresos',
                    '$antiguedad_anos',
                    '$antiguedad_meses',
                    '$antiguedad_anos_anterior',
                    '$antiguedad_meses_anterior',
                    '$referencia_familiar_nombre',
                    '$referencia_familiar_parentezco',
                    '$referencia_familiar_telefono', '$referencia_familiar_telefono_ext',
                    '$referencia_no_familiar_nombre',
                    '$referencia_no_familiar_telefono', '$referencia_no_familiar_telefono_ext',
                    '$seguro',
                    '$proteccion',
                    '$apellido_paterno_adicional1',
                    '$apellido_materno_adicional1',
                    '$nombre_adicional1',
                    '$ano_adicional1-$mes_adicional1-$dia_adicional1',
                    '$rfc_adicional1',
                    '$parentezco_adicional1',
                    '$nombre_tarjeta_adicional1',
                    '$color_adicional1',
                    '$tamano_adicional1',
                    '$apellido_paterno_adicional2',
                    '$apellido_materno_adicional2',
                    '$nombre_adicional2',
                    '$ano_adicional2-$mes_adicional2-$dia_adicional2',
                    '$rfc_adicional2',
                    '$parentezco_adicional2',
                    '$nombre_tarjeta_adicional2',
                    '$color_adicional2',
                    '$tamano_adicional2',
                    '$tdc1', '$banco1',
                    '$tdc2', '$banco2',
                    '$tdc3', '$banco3',
                    '$tdc4', '$banco4',
                    '$tdc5',
                    '$banco5',
                    '$banco6',
                    '$banco7',
                    '$banco8',
                    '$banco9',
                    '$domicilio_recoleccion', '$domicilio_recoleccion_ext', '$domicilio_recoleccion_int',
                    '$colonia_recoleccion',
                    '$poblacion_recoleccion',
                    '$cp_recoleccion',
                    '$entidad_id_recoleccion'
    )
    ");
    
    $db->sql_query($sql) or die("Error al guardar contrato.<br>$sql<br>".print_r($db->sql_error()));
    die("<html><body onload=\"window.close();\"></body></html>");
// die("$sql<br><br>".$db->sql_nextid());
}
include("$_includesdir/select.php");
$select_nacionalidad = select_array("nacionalidad", array("Mexicana", "Extranjera"));
$select_entidades = select_entidades_federativas($entidad_id);
$select_dia = select_dia($dia);
$select_mes = select_mes($mes);
$select_ano = select_ano($ano);
global $_entidades_federativas;
$select_edo_de_nac = select_array("edo_de_nac", $_entidades_federativas);
$select_sexo = select_sexo($sexo);
$select_edo_civil = select_edo_civil();
$select_grado_de_estudios = select_array("grado_de_estudios", array("Sin estudios", "Primaria", "Secundaria", "Preparatoria","Técnica comercial","Licenciatura","Posgrado"));
$select_tipo_de_vivienda = select_array("tipo_de_vivienda", array("Propia", "Rentada", "Hipotecada o pagándola", "Familiar"));
$select_color = select_array("color", array("Azul", "Verde", "Amarillo", "Rojo","Rosa"));
$select_tamano = select_array("tamano", array("Normal", "Mini", "Ambas"));
$select_donde_recibir_edo_cuenta = select_array("donde_recibir_edo_cuenta", array("Casa", "Oficina"));

$select_entidades_trabajo = select_array("entidad_id_trabajo", $_entidades_federativas);
$select_giro_empresa = select_array("giro_empresa", array("Agropecuario", "Comercio", "Construcción", "Gobierno", "Financiero", "Industria/Manufactura", "Educacion", "Turismo", "Otro"));
$select_actividad_laboral = select_array("actividad_laboral", array("Empleado", "Independente", "Comerciante", "Comisionista", "Negocio", "Otro"));
$select_ocupacion = select_array("ocupacion", array("Dueño", "Ejecutivo", "Profesionista", "Supervisor", "Trabajo de oficina", "Chofer", "Obrero", "Guardia", "Vendedor", "Ama de casa", "Estudiante", "Jubilado", "Otro"));
$select_fuente_otros_ingresos = select_array("fuente_otros_ingresos", array("Cónyugue", "Rentas", "Familiar", "Otro trabajo", "Negocio", "Inversiones"));
$select_referencia_familiar_parentezco = select_array("referencia_familiar_parentezco", array("Cónyuge", "Hijo", "Padre", "Madre","Hermano"));
$select_seguro = select_sino("seguro");
$select_proteccion = select_sino("proteccion");
global $_dias, $_meses, $_anos;
$select_dia_adicional1 = select_dia_extra("dia_adicional1");
$select_mes_adicional1 = select_mes_extra("mes_adicional1");
$select_ano_adicional1 = select_array("ano_adicional1", $_anos);
$select_parentezco_adicional1 = select_array("select_parentezco_adicional1", array("Cónyuge", "Hijo", "Padre", "Madre","Hermano"));
$select_color_adicional1 = select_array("color_adicional1", array("Azul", "Verde", "Amarillo", "Rojo","Rosa"));
$select_parentezco_adicional2 = select_array("select_parentezco_adicional2", array("Cónyuge", "Hijo", "Padre", "Madre","Hermano"));
$select_dia_adicional2 = select_dia_extra("dia_adicional2");
$select_mes_adicional2 = select_mes_extra("mes_adicional2");
$select_ano_adicional2 = select_array("ano_adicional2", $_anos);
$select_color_adicional2 = select_array("color_adicional2", array("Azul", "Verde", "Amarillo", "Rojo","Rosa"));
$select_tamano_adicional1 = select_array("tamano_adicional1", array("Normal", "Mini", "Ambas"));
$select_tamano_adicional2 = select_array("tamano_adicional2", array("Normal", "Mini", "Ambas"));
$select_entidades_recoleccion = select_array("entidad_id_recoleccion", $_entidades_federativas);

$_site_title = "Contrato";
?>
