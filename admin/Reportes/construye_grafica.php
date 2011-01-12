<?php
include_once($_includesdir."/Genera_Excel.php");
global $db,$_site_name;
$grafica=$_REQUEST['grafica'];
$tabla_sin_vendedores='';
$tablas='';
    switch($grafica)
    {
        case 1:
        {
            $sql_create = " CREATE TABLE tmp_unidades AS SELECT DISTINCT(b.contacto_id) AS contacto_id,b.gid,b.uid,d.modelo as modelo,d.modelo_id as modelo_id
            FROM crm_contactos AS b LEFT JOIN crm_prospectos_unidades AS d ON b.contacto_id=d.contacto_id
            WHERE d.contacto_id>0  ".$filtro_modelo." ".$filtro." ORDER BY d.modelo";
            $res_create=$db->sql_query($sql_create) or die("Error al CREAR la tabla:  ".$sql_create);
            if($filtro_conce != '')
            {
                $filtro_ventas.=" e.gid=a.gid ".$filtro_conce;
                $tabla.=", groups_ubications a ";
            }

            $sql_count="SELECT COUNT(modelo) AS total_global FROM tmp_unidades e ".$tabla." WHERE 1 ".$filtro_ventas.";";
            $res_count=$db->sql_query($sql_count);

            $sql="SELECT '' as ids,e.modelo AS leyenda,count(e.modelo) AS total FROM tmp_unidades e ".$tabla."  WHERE 1 ".$filtro_ventas." group by e.modelo;";
            $res=$db->sql_query($sql);

            $sql_drop="DROP TABLE tmp_unidades;";
            $res_drop=$db->sql_query($sql_drop) or die("Error al ELIMINAR la tabla:  ".$sql_drop);

            $titulo="Autos Prospectados";
            $tmp_celda="Producto";
            break;
        }
        case 2:
        {
            $array_zonas=Regresa_zonas($db);
            $sql_create = "CREATE TABLE tmp_zonas AS SELECT b.gid, b.contacto_id, b.uid, a.zona_id,b.titulo as nombre
                           FROM crm_contactos b
                           LEFT JOIN groups_ubications a ON b.gid = a.gid
                           WHERE 1 ".$filtro." ".$filtro_conce."
                           ORDER BY a.zona_id, b.contacto_id;";
            $res_create=$db->sql_query($sql_create) or die("Error al CREAR la tabla:  ".$sql_create);
            $db->sql_query("update tmp_zonas set zona_id=0,nombre='' where zona_id is NULL;");
            $udp_zona="update tmp_zonas as a SET a.nombre=(SELECT b.nombre FROM crm_zonas  as b WHERE b.zona_id=a.zona_id);";
            $res_udp_zona=$db->sql_query($udp_zona) or die("Error al Actualizar la tabla:  ".$udp_zona);
            if(!empty($filtro_modelo))
            {
                $sql_count="SELECT COUNT(*) AS total_global FROM tmp_zonas c, crm_prospectos_unidades d
                            WHERE c.contacto_id = d.contacto_id $filtro_modelo ";
                $sql="SELECT c.zona_id as ids,c.nombre as leyenda, count( c.zona_id ) AS total 
                      FROM tmp_zonas c, crm_prospectos_unidades d 
                      WHERE c.contacto_id = d.contacto_id $filtro_modelo
                      GROUP BY c.zona_id ORDER BY c.zona_id;";
            }
            else
            {
                $sql_count="SELECT COUNT(*) AS total_global FROM tmp_zonas ;";
                $sql="SELECT c.zona_id as ids,c.nombre as leyenda,count(c.zona_id) AS total FROM tmp_zonas c WHERE zona_id is not null
                GROUP BY c.zona_id ORDER BY c.zona_id;";
            }
            $res_count=$db->sql_query($sql_count);
            $res=$db->sql_query($sql);

            $sql_drop="DROP TABLE tmp_zonas;";
            $res_drop=$db->sql_query($sql_drop) or die("Error al ELIMINAR la tabla:  ".$sql_drop);
            $titulo="Reporte de Zonas";
            $tmp_celda="Zona";            
            break;
        }
        case 3:
        {
            $sql_create = " CREATE TABLE tmp_origenes AS select o.nombre, b.contacto_id,b.gid,b.uid,b.origen_id from crm_fuentes as o, crm_contactos as b where b.origen_id = o.fuente_id $filtro ORDER BY b.origen_id";
            $res_create=$db->sql_query($sql_create) or die("Error al CREAR la tabla:  ".$sql_create);
            if(!empty($filtro_conce))
            {
                $sql_count="SELECT COUNT(*) AS total_global FROM tmp_origenes e, groups_ubications a WHERE e.gid=a.gid $filtro_conce;";
                $sql="SELECT e.origen_id as ids,e.nombre AS leyenda,count(e.origen_id) AS total FROM tmp_origenes e, groups_ubications a
                WHERE e.gid=a.gid $filtro_conce group by e.origen_id;";

            }
            else
            {
                $sql_count="SELECT COUNT(*) AS total_global FROM tmp_origenes e ;";
                $sql="SELECT e.origen_id as ids,e.nombre AS leyenda,count(e.origen_id) AS total FROM tmp_origenes e
                WHERE 1  $filtro_conce group by e.origen_id;";
            }
            $res_count=$db->sql_query($sql_count);
            $res=$db->sql_query($sql);
            $sql_drop="DROP TABLE tmp_origenes;";
            $res_drop=$db->sql_query($sql_drop) or die("Error al ELIMINAR la tabla:  ".$sql_drop);
            $titulo="Reporte de Origenes";
            $tmp_celda="Origïén";
            break;
        }
        case 6:
        {
            $sql_create = "CREATE TABLE tmp_vtas AS SELECT c.chasis,c.contacto_id,c.modelo_id,c.version_id,c.transmision_id,c.uid,u.name as vendedor,u.gid,u.email as name,1 as region_id,10 as zona_id,10 as entidad_id,100 as plaza_id,1 as nivel_id,100 as grupo_empresarial_id
            FROM crm_prospectos_ventas c LEFT JOIN users u ON c.uid = u.uid
            WHERE c.eliminar=0 ".$filtro_fecha." ORDER BY u.gid,c.contacto_id";
            $res_create=$db->sql_query($sql_create) or die("Error al CREAR la tabla:  ".$sql_create);
            $udp_vtas="update tmp_vtas as a SET a.name=(SELECT b.name FROM groups as b WHERE b.gid=a.gid);";
            $res_udp_vtas=$db->sql_query($udp_vtas) or die("Error al Actualizar la tabla:  ".$udp_vtas);
            $res_upd_r=$db->sql_query("update tmp_vtas as a SET a.region_id=(SELECT b.region_id FROM groups_ubications as b WHERE b.gid=a.gid);");
            $res_upd_r=$db->sql_query("update tmp_vtas as a SET a.zona_id=(SELECT b.zona_id FROM groups_ubications as b WHERE b.gid=a.gid);");
            $res_upd_r=$db->sql_query("update tmp_vtas as a SET a.entidad_id=(SELECT b.entidad_id FROM groups_ubications as b WHERE b.gid=a.gid);");
            $res_upd_r=$db->sql_query("update tmp_vtas as a SET a.plaza_id=(SELECT b.plaza_id FROM groups_ubications as b WHERE b.gid=a.gid);");
            $res_upd_r=$db->sql_query("update tmp_vtas as a SET a.nivel_id=(SELECT b.nivel_id FROM groups_ubications as b WHERE b.gid=a.gid);");
            $res_upd_r=$db->sql_query("update tmp_vtas as a SET a.grupo_empresarial_id=(SELECT b.grupo_empresarial_id FROM groups_ubications as b WHERE b.gid=a.gid);");            
            if($filtro_conce != '')
            {
                $filtro_ventas.=$filtro_conce;
            }
            if($filtro != '')
            {
                $tablas.= ", crm_contactos b";
                $filtro_ventas.= " AND a.contacto_id=b.contacto_id".$filtro." ";
            }
            if($filtro_modelo !='')
            {
                $filtro_ventas.= $filtro_modelo." ";
            }
            $sql_count="select count(*) AS total_global FROM tmp_vtas a ".$tablas." WHERE a.name is not null ".$filtro_ventas.";";
            $sql="select a.gid as ids,a.name as leyenda,count(a.gid) AS total FROM tmp_vtas a ".$tablas." WHERE a.name is not null ".$filtro_ventas." GROUP BY a.gid ORDER BY a.gid;";
            $res_count=$db->sql_query($sql_count);
            $res=$db->sql_query($sql);
            $tabla_sin_vendedores=Muestra_ventas_sin_vendedores($db,$filtro_ventas);
            $sql_drop="DROP TABLE tmp_vtas;";
            $res_drop=$db->sql_query($sql_drop) or die("Error al ELIMINAR la tabla:  ".$sql_drop);
            $titulo="Reporte de Ventas por distribuidora";
            $tmp_celda="Distribuidora";
            break;
        }
        case 7:
        {
            $sql_create = "CREATE TABLE tmp_prospectos AS SELECT b.gid,b.contacto_id,b.compania as name,1 as region_id,10 as zona_id,10 as entidad_id,100 as plaza_id,1 as nivel_id,100 as grupo_empresarial_id
            FROM crm_contactos b
            WHERE b.contacto_id>0 ".$filtro." ORDER BY b.gid,b.contacto_id";
            $res_create=$db->sql_query($sql_create) or die("Error al CREAR la tabla:  ".$sql_create);
            $res_udp_p=$db->sql_query("update tmp_prospectos as a SET a.name=(SELECT b.name FROM groups as b WHERE b.gid=a.gid);");
            $res_upd_r=$db->sql_query("update tmp_prospectos as a SET a.region_id=(SELECT b.region_id FROM groups_ubications as b WHERE b.gid=a.gid);");
            $res_upd_r=$db->sql_query("update tmp_prospectos as a SET a.zona_id=(SELECT b.zona_id FROM groups_ubications as b WHERE b.gid=a.gid);");
            $res_upd_r=$db->sql_query("update tmp_prospectos as a SET a.entidad_id=(SELECT b.entidad_id FROM groups_ubications as b WHERE b.gid=a.gid);");
            $res_upd_r=$db->sql_query("update tmp_prospectos as a SET a.plaza_id=(SELECT b.plaza_id FROM groups_ubications as b WHERE b.gid=a.gid);");
            $res_upd_r=$db->sql_query("update tmp_prospectos as a SET a.nivel_id=(SELECT b.nivel_id FROM groups_ubications as b WHERE b.gid=a.gid);");
            $res_upd_r=$db->sql_query("update tmp_prospectos as a SET a.grupo_empresarial_id=(SELECT b.grupo_empresarial_id FROM groups_ubications as b WHERE b.gid=a.gid);");
            if($filtro_conce != '')
            {
                $filtro_ventas.=$filtro_conce;
            }
            if($filtro_modelo !='')
            {
                $tablas.= " LEFT JOIN crm_prospectos_unidades d ON a.contacto_id=d.contacto_id";
            }
            $sql_count="select count(a.contacto_id) AS total_global FROM tmp_prospectos a ".$tablas." WHERE a.contacto_id > 0 ".$filtro_modelo.";";
            $sql="select a.gid as ids,a.name as leyenda,count(a.gid) AS total FROM tmp_prospectos a ".$tablas." WHERE a.contacto_id > 0  ".$filtro_modelo." GROUP BY a.gid ORDER BY a.gid;";
            $res_count=$db->sql_query($sql_count);
            $res=$db->sql_query($sql);
            $titulo="Reporte de Prospectos por distribuidora";
            $tmp_celda="Distribuidora";
            $sql_drop="DROP TABLE tmp_prospectos;";
            $res_drop=$db->sql_query($sql_drop) or die("Error al ELIMINAR la tabla:  ".$sql_drop);
            break;
        }
        case 8:
        {
            $sql_create="CREATE TABLE tmp_procesados AS SELECT DISTINCT(e.llamada_id),b.contacto_id,b.gid,b.compania as name,1 as region_id,10 as zona_id,10 as entidad_id,100 as plaza_id,1 as nivel_id,100 as grupo_empresarial_id
            FROM crm_contactos AS b, crm_campanas_llamadas AS l, crm_campanas_llamadas_eventos AS e
            WHERE b.contacto_id = l.contacto_id AND e.llamada_id = l.id ".$filtro.";";
            $res_create=$db->sql_query($sql_create) or die("Error al CREAR la tabla:  ".$sql_create);
            $res_udp_p=$db->sql_query("update tmp_procesados as a SET a.name=(SELECT b.name FROM groups as b WHERE b.gid=a.gid);");
            $res_upd_r=$db->sql_query("update tmp_procesados as a SET a.region_id=(SELECT b.region_id FROM groups_ubications as b WHERE b.gid=a.gid);");
            $res_upd_r=$db->sql_query("update tmp_procesados as a SET a.zona_id=(SELECT b.zona_id FROM groups_ubications as b WHERE b.gid=a.gid);");
            $res_upd_r=$db->sql_query("update tmp_procesados as a SET a.entidad_id=(SELECT b.entidad_id FROM groups_ubications as b WHERE b.gid=a.gid);");
            $res_upd_r=$db->sql_query("update tmp_procesados as a SET a.plaza_id=(SELECT b.plaza_id FROM groups_ubications as b WHERE b.gid=a.gid);");
            $res_upd_r=$db->sql_query("update tmp_procesados as a SET a.nivel_id=(SELECT b.nivel_id FROM groups_ubications as b WHERE b.gid=a.gid);");
            $res_upd_r=$db->sql_query("update tmp_procesados as a SET a.grupo_empresarial_id=(SELECT b.grupo_empresarial_id FROM groups_ubications as b WHERE b.gid=a.gid);");
            if($filtro_conce != '')
            {
                $filtro_ventas.=$filtro_conce;
            }
            if($filtro_modelo !='')
            {
                $tablas.= ", crm_prospectos_unidades d";
                $filtro_ventas.= " AND a.contacto_id=d.contacto_id ".$filtro_modelo." ";
            }
            $sql_count="select count(*) AS total_global FROM tmp_procesados a ".$tablas." WHERE a.gid > 0 ".$filtro_ventas.";";
            $sql="select a.gid as ids,a.name as leyenda,count(a.gid) AS total FROM tmp_procesados a ".$tablas." WHERE a.gid > 0  ".$filtro_ventas." GROUP BY a.gid ORDER BY a.gid;";
            $res_count=$db->sql_query($sql_count);
            $res=$db->sql_query($sql);
            $titulo="Reporte de Procesados por distribuidora";
            $tmp_celda="Distribuidora";
            $sql_drop="DROP TABLE tmp_procesados;";
            $res_drop=$db->sql_query($sql_drop) or die("Error al ELIMINAR la tabla:  ".$sql_drop);
            break;
        }
    }

if($grafica > 0)
{
    if(file_exists($archivo))
        unlink($archivo);
    $colors = array();
    $data_titulos = array();
    $data_valores = array();
    $archivo='../files/blanco.png';
    $archivom='';
    $count_totales=1;
    $count_porcen=0;
    if($db->sql_numrows($res_count) > 0)
    {
        $count_totales=$db->sql_fetchfield(0,0, $res_count);
        $num=$db->sql_numrows($res);
        if( $num > 0)
        {
            $archivo='../files/'.$_site_name.'-salidag.jpg';
            $archivom='../files/'.$_site_name.'-salidam.jpg';
            $tabla_resul="<table width='90%' class='tablesorter' align='center border='0'>
                          <thead><tr><th>Id</th><th>$tmp_celda</th><th>T</th><th>%</th>";
            if($num <= 15)
            {
                $tabla_resul.=" <th>Color</th>";
            }
            $tabla_resul.="</tr></thead><tbody>";
            $totales=0;
            while ($fila=$db->sql_fetchrow($res))
            {
                $totales=$totales  + $fila['total'];
                $porcentaje=number_format((($fila['total'] / $count_totales)*100),2,'.','');
                $randomColor = dechex(rand(100, 100000));
                $randomColor="#".str_pad($randomColor,6,'0',STR_PAD_RIGHT);
                $count_porcen = $count_porcen + $porcentaje;
                $data_titulos[]=$fila['leyenda'];
                $data_valores[]=$fila['total'];
                $data_ids[]="Id:  ".$fila['ids'];
                $colors[] = $randomColor;
                $tabla_resul.="<tr class=\"row".($class_row++%2?"2":"1")."\"><td>".$fila['ids']."</td><td>".$fila['leyenda']."</td><td>".
                $fila['total']."</td ><td>".$porcentaje."</td>";
                if($num <= 15)
                {
                    $tabla_resul.="<td bgcolor='$randomColor'></td>";
                }
                $tabla_resul.="</tr>";
            }
            $tabla_resul.="<thead><tr><td></td><td>Totales: ".count($data_valores)."</td><td>".$totales."</td><td>".number_format($count_porcen,0,'.','')."%</td>";
            if($num <= 15)
            {
               $tabla_resul.="<td></td>";
            }
            $tabla_resul.="</tr></thead></table>";
            $tabla_resul.="<br>".$tabla_sin_vendedores;
            if(count($data_valores) > 0)
            {
               include ("$_includesdir/jpgraph/jpgraph.php");
               if(count($data_valores) <= 15)
                {
                    include("$_includesdir/jpgraph/jpgraph_pie.php");
                    include("$_includesdir/jpgraph/jpgraph_pie3d.php");
                    $graph = new PieGraph(600,650,"auto");
                    $graph->title->Set($titulo);
                    $graph->title->SetFont(FF_FONT1,FS_BOLD);
                    $p1 = new PiePlot3D($data_valores);
                    $p1->SetSliceColors($colors);
                    $p1->SetSize(.35);
                    $p1->SetCenter(.45);
                    $p1->SetStartAngle(0);
                    $p1->SetLegends($data_titulos);
                    $p1->SetLabelType(PIE_VALUE_ABS);
                    $p1->value->SetFormat('%d');
                    $p1->value->Show();
                    $p1->ExplodeAll(20);
                    $graph->Add($p1);
                    $graph->legend->Pos(0.01,0.99,"right", "bottom");
                    $graph->Stroke($archivo);
                }
                else
                {
                    include ("$_includesdir/jpgraph/jpgraph_bar.php");
                    include ("$_includesdir/jpgraph/jpgraph_scatter.php");
                    switch($grafica)
                    {
                        case 0:
                        case 1:
                        case 2:
                        case 3:
                            pinta_grafica_vehiculos($archivo,$data_titulos,$data_valores,$data_ids,$colors,$titulo,$archivom,$titulo_izq,$grafica);
                            break;
                        case 6:
                        case 7:
                        case 8:
                        {
                            if(count($data_valores) <= 35)
                                pinta_grafica_concesionarias($archivo,$data_titulos,$data_valores,$data_ids,$colors,$titulo,$archivom);
                             else
                                pinta_grafica_concesionarias_totales($archivo,$data_titulos,$data_valores,$data_ids,$colors,$titulo,$archivom);
                            break;
                        }
                    }
                }
                $objeto = new Genera_Excel($tabla_resul,$titulo,$_site_name);
                $boton_excel=$objeto->Obten_href();
                $boton_grafica="<br><a href=".$archivo." target='_blank'>Ver Gráfico m&aacute;s grande</a>";
            }
        }
    }
}

function pinta_grafica_concesionarias($archivo,$data_titulos,$data_valores,$data_ids,$colors,$titulo,$archivom)
{
    $width=750;
    $height=900;
    $top = 120;
    $bottom = 30;
    $left = 100;
    $right = 30;

    $graph = new Graph($width,$height,'auto');
    $graph->SetScale("textlin");
    $graph->Set90AndMargin($left,$right,$top,$bottom);
    $graph->title->Set($titulo);
     $graph->title->SetColor("#ba8c30");
    $graph->title->SetFont(FF_FONT2,FS_BOLD);

    $graph->xaxis->SetTickLabels($data_ids);
    $graph->xaxis->SetLabelAlign('right','center','right');
    $graph->yaxis->SetLabelAlign('center','bottom');

    $txt = new Text();
    $txt->SetFont(FF_FONT1,FS_BOLD);
    $txt->SetColor("#3e4f88");
    $txt->Set("Id's   de   Concesionarias");
    $txt->SetPos(200,800,'center','center');
    $txt->SetOrientation(90);

    $txtx = new Text("Total de prospectos");
    $txtx->SetPos(0,480,'left','top');
    $txtx->SetFont(FF_FONT1,FS_BOLD);
    $txtx->SetColor("#3e4f88");

    $graph->AddText($txt);
    $graph->AddText($txtx);

    $bplot = new BarPlot($data_valores);
    $bplot->SetFillColor("#E6E6EB");
    $bplot->SetWidth(0.6);
    $bplot->value->SetFormat('%d');
    $bplot->value->Show();
    $bplot->value->SetAlign('center','center');
    $graph->Add($bplot);
    $graph->Stroke($archivo);
}

function pinta_grafica_vehiculos($archivo,$data_titulos,$data_valores,$data_ids,$colors,$titulo,$archivom,$titulo_izq,$grafica)
{
    $width=750;
    $height=2000;
    $top = 120;
    $bottom = 30;
    $left = 120;
    $right = 30;
    $graph = new Graph($width,$height,'auto');
    $graph->SetScale("textlin");

    $graph->Set90AndMargin($left,$right,$top,$bottom);
    $graph->title->Set($titulo);
    $graph->title->SetColor("#ba8c30");
    $graph->title->SetFont(FF_FONT2,FS_BOLD);
    $graph->xaxis->SetColor("#000000");
    $graph->xaxis->SetTickLabels($data_titulos);
    $graph->xaxis->SetLabelAlign('right','center','right');
    $graph->yaxis->SetLabelAlign('center','bottom');

    $txt = new Text();
    $txt->SetFont(FF_FONT1,FS_BOLD);
    $txt->SetColor("#3e4f88");
    $txt->Set($titulo_izq);
    $txt->SetPos(-58,1350,'left','top');
    $txt->SetOrientation(90);


    $txtx = new Text("Total de prospectos");
    $txtx->SetPos(-550,1000,'left','top');
    $txtx->SetFont(FF_FONT1,FS_BOLD);
    $txtx->SetColor("#3e4f88");

    $graph->AddText($txt);
    $graph->AddText($txtx);

    $bplot = new BarPlot($data_valores);
    $bplot->SetFillColor("#E6E6EB");
    $bplot->SetWidth(0.5);
    $bplot->value->SetFormat('%d');
    $bplot->value->Show();
    $bplot->value->SetAlign('center','center');
    $graph->Add($bplot);
    $graph->Stroke($archivo);
}
function pinta_grafica_concesionarias_totales($archivo,$data_titulos,$data_valores,$data_ids,$colors,$titulo,$archivom)
{
    $width=750;
    $height=3900;
    $top = 120;
    $bottom = 30;
    $left = 100;
    $right = 30;

    $graph = new Graph($width,$height,'auto');
    $graph->SetScale("textlin");
    $graph->Set90AndMargin($left,$right,$top,$bottom);
    $graph->title->Set($titulo);
    $graph->title->SetColor("#ba8c30");
    $graph->title->SetFont(FF_FONT2,FS_BOLD);

    $graph->xaxis->SetColor("#000000");
    $graph->xaxis->SetTickLabels($data_ids);
    $graph->xaxis->SetLabelAlign('right','center','right');
    $graph->yaxis->SetLabelAlign('center','bottom');

    $txt = new Text();
    $txt->SetFont(FF_FONT1,FS_BOLD);
    $txt->SetColor("#3e4f88");
    $txt->Set("Id's   de   Concesionarias");
    $txt->SetPos(-1300,2300,'center','center');
    $txt->SetOrientation(90);

    $txtx = new Text("Total de prospectos");
    $txtx->SetPos(-1500,2000,'left','top');
    $txtx->SetFont(FF_FONT1,FS_BOLD);
    $txtx->SetColor("#3e4f88");

    $graph->AddText($txt);
    $graph->AddText($txtx);

    $bplot = new BarPlot($data_valores);
    $bplot->SetFillColor("#E6E6EB");
    $bplot->SetWidth(0.6);
    $bplot->value->SetFormat('%d');
    $bplot->value->Show();
    $bplot->value->SetAlign('center','center');
    $graph->Add($bplot);
    $graph->Stroke($archivo);
}

function Regresa_zonas($db)
{
    $array=array();
    $sql="select * from crm_zonas ORDER BY zona_id;";
    $res=$db->sql_query($sql) or die("Error en la consulta:  ".$sql);
    if($db->sql_numrows($res)> 0)
    {
        while($fila = $db->sql_fetchrow($res))
        {
            $array[$fila['zona_id']]=$fila['nombre'];
        }
    }
    return $array;
}
function Muestra_ventas_sin_vendedores($db,$filtro_ventas)
{
    $concesionarias=Regresa_Groups($db);
    $total_tuplas_fin=0;
    $total_tuplas_con=0;
    //$sql="select b.gid,count(b.gid) as total from tmp_vtas a, crm_contactos_finalizados b where a.contacto_id=b.contacto_id and a.vendedor IS NULL group by b.gid order by b.gid;";
    $sql="select b.gid,count(b.gid) as total from tmp_vtas a, crm_contactos_finalizados b where a.contacto_id=b.contacto_id and a.vendedor IS NULL $filtro_ventas group by b.gid order by b.gid;";  
    $res=$db->sql_query($sql);
    $no_tuplas=$db->sql_numrows($res);
    if($no_tuplas> 0)
    {
        while($fila = $db->sql_fetchrow($res))
        {
            $array[$fila['gid']+ 0]=$fila['total'];
            $total_tuplas_fin = $total_tuplas_fin + $fila['total'];
        }
    }
    $sql="select b.gid,count(b.gid) as total from tmp_vtas a, crm_contactos b where a.contacto_id=b.contacto_id and a.vendedor IS NULL $filtro_ventas group by b.gid order by b.gid;"; 
    $res=$db->sql_query($sql);
    $no_tuplas_c=$db->sql_numrows($res);
    
    if($no_tuplas_c> 0)
    {
        while($fila = $db->sql_fetchrow($res))
        {
            $array[$fila['gid']+ 0]=  $array[$fila['gid']] + $fila['total'];
            $total_tuplas_con = $total_tuplas_con  + $fila['total'];
        }
    }
    $total_tuplas=$total_tuplas_fin + $total_tuplas_con;
    $buffer='';
    if(count($array) > 0)
    {
        $total=0;
        $totalprom=0;
        $buffer.="<Table width='50%' align='center' border='0' class='tablesorter'>
            <thead>
            <tr bgcolor='#333333'>
            <td>Id</td>
            <td>Distribuidor</td>
            <td>Ventas sin Vendedor</td>
            <td>%</td>
            </tr></thead><tbody>";
        foreach($array as $clave => $valor)
        {
            $total=$total + $valor;
            $porcen=($valor*100)/$total_tuplas ;
            $totalprom= $totalprom +$porcen;
            $buffer.="
            <tr class=\"row".($class_row++%2?"2":"1")."\">
            <td>$clave</td>
            <td>$concesionarias[$clave]</td>
            <td>$valor</td>
            <td>".number_format($porcen,2,'.','')."</td>
            </tr>";
        }
        $buffer.="</tbody>
            <thead>
            <tr bgcolor='#333333'>
              <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>$total</td>
            <td>".number_format($totalprom,0,'.','')." %</td>
            </tr></thead></table>";
    }
    return $buffer;   
}
function Regresa_Groups($db)
{
    $array=array();
    $sql="select gid,name FROM groups where gid>3 ORDER BY gid;";
    $res=$db->sql_query($sql);
    if($db->sql_numrows($res)> 0)
    {
        while($fila = $db->sql_fetchrow($res))
        {
            $array[$fila['gid']+ 0]=$fila['name'];
        }
    }
    return $array;
}

?>