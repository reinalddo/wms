<?php
include '../../../config.php';

error_reporting(0);
 
if(isset($_GET) && !empty($_GET)){


    $search = "";
    if(isset($_GET['search']['value']))
    {
        if($_GET['search']['value'])
        {
            $search = $_GET['search']['value'];
        }
    }


    $almacen = $_GET['almacen'];
    $filtro_where_concentrado = ""; $filtro_where_concentrado_na = "";
    if(isset($_GET['filtro_concentrado']))
    {
        if($_GET['filtro_concentrado'])
        {
            $filtro_where_concentrado    = $_GET['filtro_concentrado'];
            $filtro_where_concentrado_na = $_GET['filtro_concentrado'];
        }
    }
    else if(isset($_POST['filtro_concentrado']))
    {
        if($_POST['filtro_concentrado'])
        {
            $filtro_where_concentrado    = $_POST['filtro_concentrado'];
            $filtro_where_concentrado_na = $_POST['filtro_concentrado'];
        }
    }
 
    if($filtro_where_concentrado)
        $filtro_where_concentrado .= " AND concentrado.clave_alm = '{$almacen}'";
    else
        $filtro_where_concentrado = " WHERE 1 AND concentrado.clave_alm = '{$almacen}'";


    if($filtro_where_concentrado_na)
        $filtro_where_concentrado_na .= " ";
    else
        $filtro_where_concentrado_na = " WHERE 1 ";


    $filtro_diferencias = ""; $order_by_diferencias = "";
    if(isset($_GET['filtro_diferencias_select']))
    {
        if($_GET['filtro_diferencias_select'])
            $filtro_diferencias = $_GET['filtro_diferencias_select'];
    }
    else if(isset($_POST['filtro_diferencias_select']))
    {
        if($_POST['filtro_diferencias_select'])
            $filtro_diferencias = $_POST['filtro_diferencias_select'];
    }

    if($filtro_diferencias)
    {
        $array_diferencias = explode("SEPARADOR", $filtro_diferencias);
        $filtro_diferencias = $array_diferencias[0];
        $order_by_diferencias = $array_diferencias[1];
    }

    $sqlLotes = "";
    if(isset($_GET['lotes']))
    {
        $lote = $_GET['lotes'];
        if($lote)
            $sqlLotes = " AND concentrado.lote LIKE '%{$lote}%'";
    }
    else if(isset($_POST['lotes']))
    {
        $lote = $_POST['lotes'];
        if($lote)
            $sqlLotes = " AND concentrado.lote LIKE '%{$lote}%'";
    }



    $ands = ""; $ands2 = "";
    if (!empty($search)){
        $ands.=" and a.cve_articulo like '%".$search."%' ";
        $ands2.=" and ar.cve_articulo like '%".$search."%' ";
    }
    
    //$sql1 = 'SELECT * FROM c_almacenp WHERE id = "'.$almacen.'"';
    //$result = getArraySQL($sql1);  
 
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    //$sql = "SELECT SUM(Existencia) AS cantidad, COUNT(DISTINCT cve_articulo) AS total FROM V_ExistenciaGralProduccion WHERE Existencia > 0;";

    $page  = $_GET['page']; // get the requested page
    $lim = $_GET['rows']; // get how many rows we want to have into the grid
    $sidx  = $_GET['sidx']; // get index row - i.e. user click to sort
    $sord  = $_GET['sord']; // get the direction

    //$almacen = $_GET["almacen"];
    $articulo = $_GET["articulo"];
    $proveedor = $_GET["proveedor"];
    $grupo = $_GET["grupo"];

    $sqlArticulo1 = !empty($articulo) ? " AND a.cve_articulo = '{$articulo}' " : "";
    $sqlArticulo2 = !empty($articulo) ? " AND ar.cve_articulo = '{$articulo}' " : "";

    $sqlProveedor = !empty($proveedor) ? " AND p.ID_Proveedor = '{$proveedor}' " : "";

    $sqlGrupo = !empty($grupo) ? " AND g.id = '{$grupo}' " : "";

    $ands = $sqlArticulo1.$sqlProveedor.$sqlGrupo;
    $ands2 = $sqlArticulo2.$sqlProveedor.$sqlGrupo;

    $lim = $lim+0;//hay que convertirlo a entero
    $_page = 0;
    if (intval($page)>0) $_page = ($page-1)*$lim;//



    $filtro_clientes = "";
    if(isset($_GET['cve_proveedor']))
    {
      if($_GET['cve_proveedor'])
      {
          $proveedor = $_GET['cve_proveedor'];
          $filtro_clientes = "AND concentrado.id_proveedor = {$proveedor}";
      }
    }

    if(isset($_POST['cve_proveedor']))
    {
      if($_POST['cve_proveedor'])
      {
          $proveedor = $_POST['cve_proveedor'];
          $filtro_clientes = "AND concentrado.id_proveedor = {$proveedor}";
      }
    }


    $sql_in = " AND ar.cve_articulo NOT IN (SELECT  
            z.cve_articulo
        FROM td_aduana z
            LEFT JOIN th_aduana adz ON z.num_orden = adz.num_pedimento
        WHERE adz.Cve_Almac = '{$almacen}') AND ar.cve_articulo NOT IN (SELECT 
        cve_articulo FROM V_ExistenciaGralProduccion)";

    $sql_in_na = " ar.cve_articulo NOT IN (SELECT  
            z.cve_articulo
        FROM td_aduana z
            LEFT JOIN th_aduana adz ON z.num_orden = adz.num_pedimento) AND 
         ar.cve_articulo NOT IN (SELECT 
        cve_articulo FROM V_ExistenciaGralProduccion)";

    $sql = "";

    if($almacen != '')
    {
    $sql = "
    SELECT concentrado.ubicacion, concentrado.folio, concentrado.clave_alm, concentrado.Nombre_Almacen, concentrado.grupo, 
       concentrado.id_proveedor, concentrado.proveedor, concentrado.articulo, concentrado.nombre, concentrado.lote, concentrado.caducidad, concentrado.caducidad_sap, concentrado.Existencia_SAP, SUM(concentrado.existencia) AS existencia
    FROM (
    SELECT  DISTINCT
            vg.Cve_Contenedor AS Cve_Contenedor,
            vg.cve_ubicacion AS ubicacion,
            ad.num_orden AS folio,
            alm.clave AS clave_alm,
            alm.nombre AS Nombre_Almacen,
            g.des_gpoart AS grupo,
            p.ID_Proveedor AS id_proveedor,
            p.Nombre AS proveedor,
            a.cve_articulo AS articulo,
            IFNULL(a.des_articulo, '--') AS nombre,
            IFNULL(l.Lote, '') AS lote,
            IF(a.Caduca = 'S', DATE_FORMAT(l.Caducidad, '%d-%m-%Y'), '') as caducidad,
            IF(a.Caduca = 'S', DATE_FORMAT(tm.Caducidad, '%d-%m-%Y'), '') as caducidad_sap,
        IFNULL(tm.Num_Cantidad, 0) AS Existencia_SAP,
        vg.Existencia as existencia

        FROM c_articulo a
            LEFT JOIN c_gpoarticulo g ON g.cve_gpoart = a.grupo
            LEFT JOIN th_aduana v  ON v.Cve_Almac = '{$almacen}'
            LEFT JOIN td_aduana ad ON v.num_pedimento = ad.num_orden AND ad.cve_articulo != ''
            LEFT JOIN V_ExistenciaGral vg ON a.cve_articulo = vg.cve_articulo
            LEFT JOIN c_lotes l ON l.cve_articulo = a.cve_articulo AND l.Lote = vg.cve_lote
            LEFT JOIN c_almacenp alm ON alm.clave = '{$almacen}'
            LEFT JOIN c_proveedores p ON p.ID_Proveedor = vg.ID_Proveedor
            LEFT JOIN t_match tm ON tm.Cve_Almac = alm.clave AND tm.Cve_Articulo = a.cve_articulo AND p.cve_proveedor = tm.Cve_Proveedor 
                        AND tm.Cve_Lote = vg.cve_lote
        WHERE a.cve_articulo IN (SELECT cve_articulo FROM V_ExistenciaGralProduccion) AND vg.Id_Proveedor = p.ID_Proveedor 
        AND vg.cve_almac = (SELECT id FROM c_almacenp WHERE clave = '{$almacen}') AND vg.tipo = 'ubicacion'
        $ands
        GROUP BY vg.cve_ubicacion,alm.clave, g.id, a.cve_articulo, p.ID_Proveedor, l.Lote, vg.Cve_Contenedor
#v.Cve_Almac = '{$almacen}' AND
        UNION

        SELECT  
            '' AS Cve_Contenedor,
            '' AS ubicacion,
            '' AS folio,
            c_almacenp.clave AS clave_alm,
            c_almacenp.nombre AS Nombre_Almacen,
            g.des_gpoart AS grupo,
            '' as id_proveedor,
            '' AS proveedor,
            ar.cve_articulo AS articulo,
            IFNULL(ar.des_articulo, '--') AS nombre,
            '' AS lote,
            '' as caducidad,
            '' as caducidad_sap,
            0 AS Existencia_SAP,
            0 AS existencia
        FROM c_articulo ar
            LEFT JOIN c_gpoarticulo g ON g.cve_gpoart = ar.grupo
            LEFT JOIN t_artcompuesto art ON art.Cve_Articulo = ar.cve_articulo
            LEFT JOIN c_proveedores p ON p.ID_Proveedor = ar.ID_Proveedor
            LEFT JOIN c_almacenp ON c_almacenp.id = ar.cve_almac
        WHERE c_almacenp.clave = '{$almacen}' $sql_in 
            $ands2
        GROUP BY ar.cve_articulo
        ORDER BY Nombre_Almacen
        ) AS concentrado {$filtro_where_concentrado} {$filtro_diferencias} {$sqlLotes}
        GROUP BY concentrado.grupo, concentrado.id_proveedor, concentrado.articulo, concentrado.lote
        {$order_by_diferencias}";
    }
    else
    {
    $sql = "
    SELECT concentrado.ubicacion, concentrado.folio, concentrado.clave_alm, concentrado.Nombre_Almacen, concentrado.grupo, 
       concentrado.id_proveedor, concentrado.proveedor, concentrado.articulo, concentrado.nombre, concentrado.lote, concentrado.caducidad, concentrado.caducidad_sap, concentrado.Existencia_SAP, SUM(concentrado.existencia) AS existencia
    FROM (
    SELECT  DISTINCT
            vg.cve_ubicacion AS ubicacion,
            ad.num_orden AS folio,
            alm.clave AS clave_alm,
            alm.nombre AS Nombre_Almacen,
            g.des_gpoart AS grupo,
            p.ID_Proveedor AS id_proveedor,
            p.Nombre AS proveedor,
            a.cve_articulo AS articulo,
            IFNULL(a.des_articulo, '--') AS nombre,
            IFNULL(l.Lote, '') AS lote,
            IF(a.Caduca = 'S', DATE_FORMAT(l.Caducidad, '%d-%m-%Y'), '') as caducidad,
            IF(a.Caduca = 'S', DATE_FORMAT(tm.Caducidad, '%d-%m-%Y'), '') as caducidad_sap,
        IFNULL(tm.Num_Cantidad, 0) AS Existencia_SAP,
        vg.Existencia as existencia

        FROM c_articulo a
            LEFT JOIN c_gpoarticulo g ON g.cve_gpoart = a.grupo
            LEFT JOIN V_ExistenciaGral vg ON a.cve_articulo = vg.cve_articulo
            LEFT JOIN c_lotes l ON l.cve_articulo = a.cve_articulo AND l.Lote = vg.cve_lote
            LEFT JOIN c_almacenp alm ON alm.id = vg.cve_almac
            LEFT JOIN th_aduana v  ON v.Cve_Almac = alm.clave
            LEFT JOIN td_aduana ad ON v.num_pedimento = ad.num_orden AND ad.cve_articulo != ''
            LEFT JOIN c_proveedores p ON p.ID_Proveedor = vg.ID_Proveedor
            LEFT JOIN t_match tm ON tm.Cve_Almac = alm.clave AND tm.Cve_Articulo = a.cve_articulo AND p.cve_proveedor = tm.Cve_Proveedor 
                        AND tm.Cve_Lote = vg.cve_lote
        WHERE a.cve_articulo IN (SELECT cve_articulo FROM V_ExistenciaGralProduccion) AND vg.Id_Proveedor = p.ID_Proveedor 
        AND vg.cve_almac = (SELECT id FROM c_almacenp WHERE clave = alm.clave) AND vg.tipo = 'ubicacion'
        $ands
        GROUP BY vg.cve_ubicacion,alm.clave, g.id, a.cve_articulo, p.ID_Proveedor, l.Lote
#v.Cve_Almac = '{$almacen}' AND
        UNION

        SELECT  
            '' AS ubicacion,
            '' AS folio,
            c_almacenp.clave AS clave_alm,
            c_almacenp.nombre AS Nombre_Almacen,
            g.des_gpoart AS grupo,
            '' as id_proveedor,
            '' AS proveedor,
            ar.cve_articulo AS articulo,
            IFNULL(ar.des_articulo, '--') AS nombre,
            '' AS lote,
            '' as caducidad,
            '' as caducidad_sap,
            0 AS Existencia_SAP,
            0 AS existencia
        FROM c_articulo ar
            LEFT JOIN c_gpoarticulo g ON g.cve_gpoart = ar.grupo
            LEFT JOIN t_artcompuesto art ON art.Cve_Articulo = ar.cve_articulo
            LEFT JOIN c_proveedores p ON p.ID_Proveedor = ar.ID_Proveedor
            LEFT JOIN c_almacenp ON c_almacenp.id = ar.cve_almac
        WHERE $sql_in_na
            $ands2
        GROUP BY ar.cve_articulo
        ) AS concentrado {$filtro_where_concentrado_na} {$filtro_diferencias} {$sqlLotes}
        GROUP BY concentrado.grupo, concentrado.id_proveedor, concentrado.articulo, concentrado.lote
        {$order_by_diferencias}";
    }
/*
1 #v.Existencia > 0
            #AND v.`tipo`='ubicacion'
            AND


1 
            AND #v.Existencia > 0
            #AND v.`tipo`='ubicacion'
*/
$sql_consulta = $sql;
    if (!($res = mysqli_query($conn, $sql))) 
    {
        echo json_encode(array( "error" => "Error al procesar la petición 1: (" . mysqli_error($conn) . ") "))."--- ".$sql;
    }
    $count = mysqli_num_rows($res);

    $res = "";
    if(!isset($_GET['boton_pdf']))
    $sql.= " LIMIT ".$_page.",".$lim."";

    //$sql2 = $sql;
    if (!($res = mysqli_query($conn, $sql))) 
    {
        echo json_encode(array( "error" => "Error al procesar la petición 2: (" . mysqli_error($conn) . ") "))."---".$lim."--- ".$sql;
    }

    $data = array();
    $responce = array();
    $i = 0;
    $num_unidades = 0;

    while ($row = mysqli_fetch_array($res)) {

        //**************************************************
        //FUNCION PARA LAS COLUMNAS PALLET | CAJAS | PIEZAS
        //**************************************************
        //Archivos donde se encuentra esta función:
        //api\embarques\lista\index.php
        //api\reportes\lista\existenciaubica.php
        //api\reportes\lista\concentradoexistencia.php
        //app\template\page\reportes\existenciaubica.php
        //\Application\Controllers\EmbarquesController.php
        //\Application\Controllers\InventarioController.php
        //**************************************************
        //clave busqueda: COLPCP
        //**************************************************
/*
        $valor1 = 0;
        if($row['piezasxcajas'] > 0)
           $valor1 = $row['existencia']/$row['piezasxcajas'];

        if($row['cajasxpallets'] > 0)
           $valor1 = $valor1/$row['cajasxpallets'];
       else
           $valor1 = 0;

        $row['Pallet'] = intval($valor1);

        $valor2 = 0;
        $cantidad_restante = $row['existencia'] - ($row['Pallet']*$row['piezasxcajas']*$row['cajasxpallets']);
        if(!is_int($valor1) || $valor1 == 0)
        {
            if($row['piezasxcajas'] > 0)
               $valor2 = ($cantidad_restante/$row['piezasxcajas']);// - ($row['Pallet']*$row['existencia']);
        }
        $row['Caja'] = intval($valor2);

        $row['Piezas'] = 0;
        if($row['piezasxcajas'] == 1) 
        {
            $valor2 = 0; 
            $row['Caja'] = $cantidad_restante;
            $row['Piezas'] = 0;
        }
        else if($row['piezasxcajas'] == 0 || $row['piezasxcajas'] == "")
        {
            if($row['piezasxcajas'] == "") $row['piezasxcajas'] = 0;
            $row['Caja'] = 0;
            $row['Piezas'] = $cantidad_restante;
        }
        $cantidad_restante = $cantidad_restante - ($row['Caja']*$row['piezasxcajas']);

        if(!is_int($valor2))
        {
           //$row['Piezas'] = ($row['Caja']*$cantidad_restante) - $row['piezasxcajas'];
            $row['Piezas'] = $cantidad_restante;
        }
        //**************************************************
*/
        //**************************************************
        //Existencia no debe considerar productos en área de Kitting
        //**************************************************
        //if($row['existencia'])
        //$row['existencia'] -= $row['Prod_kit'];
        //**************************************************

            $responce["rows"][$i]['id']=$row['articulo'];
            $responce["rows"][$i]['cell']=array(
                utf8_decode($row['articulo']),
                utf8_decode($row['nombre']),
                $row['lote'],
                $row['caducidad'],
                $row['caducidad_sap'],
                number_format($row['existencia'], 2),
                number_format($row['Existencia_SAP'], 2), 
                number_format(($row['existencia'] - $row['Existencia_SAP']), 2), 
/*
                $row['Pallet'],
                $row['Caja'],
                $row['Piezas'],
                $row['Prod_OC'],
                $row['Prod_RTM'],
                $row['Res_Pick'],
                $row['Prod_QA'],
                $row['Obsoletos'],
                $row['RTS'],
                $row['Prod_kit'],
*/
                utf8_decode($row['proveedor']),
                utf8_decode($row['Nombre_Almacen']),
                utf8_decode($row['grupo'])
              );

        //$num_unidades += $row['existencia'];
        $row = array_map('utf8_encode', $row);
        $data[] = $row;
        $i++;
    }
        

    $draw = $_GET["draw"];

    $sql = "
      SELECT SUM(concentrado.total) AS total, SUM(concentrado.existencia) AS existencia FROM(
         SELECT 
            IFNULL(l.Lote, '') AS lote,
            alm.clave AS clave_alm,
            COUNT(DISTINCT vg.cve_articulo) AS total,
            vg.Existencia AS existencia
            FROM c_articulo a
            LEFT JOIN c_gpoarticulo g ON g.cve_gpoart = a.grupo
            LEFT JOIN th_aduana v  ON v.Cve_Almac = '{$almacen}'
            LEFT JOIN td_aduana ad ON v.num_pedimento = ad.num_orden AND ad.cve_articulo != ''
            LEFT JOIN V_ExistenciaGral vg ON a.cve_articulo = vg.cve_articulo
            LEFT JOIN c_lotes l ON l.cve_articulo = a.cve_articulo AND l.Lote = vg.cve_lote
            LEFT JOIN c_almacenp alm ON alm.clave = '{$almacen}'
            LEFT JOIN c_proveedores p ON p.ID_Proveedor = vg.ID_Proveedor
            LEFT JOIN t_match tm ON tm.Cve_Almac = alm.clave AND tm.Cve_Articulo = a.cve_articulo AND tm.Cve_Lote = vg.cve_lote AND p.cve_proveedor = tm.Cve_Proveedor
            WHERE a.cve_articulo IN (SELECT cve_articulo FROM V_ExistenciaGralProduccion) AND vg.Id_Proveedor = p.ID_Proveedor
            $ands
                GROUP BY a.cve_articulo, p.ID_Proveedor, l.Lote, alm.clave
                ) as concentrado {$filtro_where_concentrado} {$sqlLotes}
            #where x.lote != '--'
            ";

/*
    $sql = "
      SELECT * FROM(
         SELECT DISTINCT 
            COUNT(DISTINCT e.cve_articulo) AS total,
            SUM(e.Existencia) AS cantidad
            FROM
                V_ExistenciaGral e
            LEFT JOIN c_articulo a ON a.cve_articulo = e.cve_articulo
            LEFT JOIN c_gpoarticulo g ON g.cve_gpoart = a.grupo
            LEFT JOIN c_ubicacion u ON u.idy_ubica = e.cve_ubicacion
            LEFT JOIN c_lotes l ON l.LOTE = e.cve_lote AND l.cve_articulo = e.cve_articulo
            LEFT JOIN c_almacen z ON z.cve_almac = (SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = e.cve_ubicacion)
            LEFT JOIN c_almacenp ap ON ap.id = z.cve_almacenp
            LEFT JOIN c_charolas ch ON ch.clave_contenedor = e.Cve_Contenedor
            LEFT JOIN c_proveedores p ON p.ID_Proveedor = e.ID_Proveedor
                WHERE e.cve_almac = (SELECT id FROM c_almacenp WHERE clave = '{$almacen}')  AND e.tipo = 'ubicacion' AND e.Existencia > 0  
                $ands 
                )x
            #where x.lote != '--'
            WHERE 1 ";
*/
//{$sqlArticulo} {$sqlContenedor} {$sqlZona} {$sqlbl_search} {$sqlProveedor} $zona_rts {$sqlproveedor_tipo}

    if (!($res = mysqli_query($conn, $sql))) 
    {
        echo json_encode(array( "error" => "Error al procesar la petición 4: (" . mysqli_error($conn) . ") "))."---".$sql;
    }

    $row = mysqli_fetch_array($res);
    $cantidad = $row["existencia"];
    //$count = $row[1];

    $cantidad = number_format($cantidad, 2);

    mysqli_close();

    if( $count >0 ) {
        $total_pages = ceil($count/$lim);
        //$total_pages = ceil($count/1);
    } else {
        $total_pages = 0;
    } if ($page > $total_pages)
        $page=$total_pages;

    if(isset($_GET['boton_pdf']))
    {
/*
        if (!($res = mysqli_query($conn, $sql_consulta)))
        {
            echo json_encode(array( "error" => "Error al procesar la petición PDF: (" . mysqli_error($conn) . ") "))."---".$sql_consulta;
        }

        $data = array();
        //$data = mysqli_fetch_array($res);

        while ($row = mysqli_fetch_array($res)) {

            $data[] = $row;
        }
*/
        header('Content-type: application/json');
        $output = array(
            "draw" => $_GET["draw"],
            "recordsTotal" => $count,
            "recordsFiltered" => $count,
            "data" => $data,
            "productos" => $i,
            "unidades" => $cantidad,
            "sql_consulta" => $sql_consulta,
            "cantidadTotal" => $cantidad
        ); 
        echo json_encode($output);
    }
    else
    {
        $responce["page"] = $page;
        $responce["total"] = $total_pages;
        $responce["records"] = $count;
        $responce["productos"] = $count;
        $responce["unidades"] = $cantidad;
        $responce["cantidadTotal"] = $cantidad;
        //$responce["data"] = $data;
        $responce["sql_consulta"] = $sql_consulta;
        $responce["sql"] = $sql;
        echo json_encode($responce);
    }
}

if(isset($_POST) && !empty($_POST) && $_POST['action'] === 'concentrado_pdf')
{
    $almacen = $_POST['almacen'];
 
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql = "
        SELECT  
            (SELECT 
                fol_folio 
            FROM td_entalmacen 
            WHERE cve_articulo = v.cve_articulo 
                AND cve_lote = v.cve_lote 
            LIMIT 1) AS folio,
            IFNULL(
                (SELECT 
                    Nombre 
                 FROM c_proveedores 
                 WHERE ID_Proveedor = (
                    SELECT 
                        ID_Proveedor 
                    FROM th_aduana 
                    WHERE num_pedimento = (SELECT folio))),'') AS proveedor,
            v.cve_articulo AS articulo,
            IFNULL(a.des_articulo, '--') AS nombre,
            a.cajas_palet cajasxpallets,
            a.num_multiplo piezasxcajas, 
            0 as Pallet,
            0 as Caja, 
            0 as Piezas,
            SUM(v.Existencia) AS existencia
        FROM V_ExistenciaGralProduccion v
            LEFT JOIN c_articulo a ON a.cve_articulo = v.cve_articulo
            LEFT JOIN c_proveedores p ON p.ID_Proveedor = a.ID_Proveedor
            LEFT JOIN c_almacenp on c_almacenp.id = v.cve_almac
        WHERE v.Existencia > 0
            AND v.`tipo`='ubicacion'
            AND c_almacenp.clave = '{$almacen}'
        GROUP BY v.cve_articulo
    ";

    if (!($res = mysqli_query($conn, $sql))) {echo json_encode(array( "error" => "Error al procesar la petición: (" . mysqli_error($conn) . ") "));}
  
    $data = array();
    $i = 0;
    while ($row = mysqli_fetch_array($res)) {

        $valor1 = 0;
        if($row['piezasxcajas'] > 0)
           $valor1 = $row['existencia']/$row['piezasxcajas'];

        if($row['cajasxpallets'] > 0)
           $valor1 = $valor1/$row['cajasxpallets'];
       else
           $valor1 = 0;

        $row['Pallet'] = intval($valor1);

        $valor2 = 0;
        $cantidad_restante = $row['existencia'] - ($row['Pallet']*$row['piezasxcajas']*$row['cajasxpallets']);
        if(!is_int($valor1) || $valor1 == 0)
        {
            if($row['piezasxcajas'] > 0)
               $valor2 = ($cantidad_restante/$row['piezasxcajas']);// - ($row['Pallet']*$row['existencia']);
        }
        $row['Caja'] = intval($valor2);

        $row['Piezas'] = 0;
        if($row['piezasxcajas'] == 1 || $row['piezasxcajas'] == 0 || $row['piezasxcajas'] == "") 
        {
            if($row['piezasxcajas'] == "") $row['piezasxcajas'] = 0;
            $valor2 = 0; 
            $row['Caja'] = $cantidad_restante;
            $row['Piezas'] = 0;
        }
        $cantidad_restante = $cantidad_restante - ($row['Caja']*$row['piezasxcajas']);

        if(!is_int($valor2))
        {
            $row['Piezas'] = $cantidad_restante;
        }

        $row = array_map('utf8_encode', $row);
        $data[] = $row;
        $i++;
    }
  
    mysqli_close();
    header('Content-type: application/json');
    $output = array(
        "productos" => $data,
    ); 
    echo json_encode($output);
}

if(isset($_POST) && !empty($_POST) && $_POST['action'] === 'concentrado_excel')
{
    $almacen = $_POST['almacen'];
 
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql = "
        SELECT  
            (SELECT 
                fol_folio 
            FROM td_entalmacen 
            WHERE cve_articulo = v.cve_articulo 
                AND cve_lote = v.cve_lote 
            LIMIT 1) AS folio,
            IFNULL(
                (SELECT 
                    Nombre 
                 FROM c_proveedores 
                 WHERE ID_Proveedor = (
                    SELECT 
                        ID_Proveedor 
                    FROM th_aduana 
                    WHERE num_pedimento = (SELECT folio))),'') AS proveedor,
            v.cve_articulo AS articulo,
            IFNULL(a.des_articulo, '--') AS nombre,
            a.cajas_palet cajasxpallets,
            a.num_multiplo piezasxcajas, 
            0 as Pallet,
            0 as Caja, 
            0 as Piezas,
            SUM(v.Existencia) AS existencia
        FROM V_ExistenciaGralProduccion v
            LEFT JOIN c_articulo a ON a.cve_articulo = v.cve_articulo
            LEFT JOIN c_proveedores p ON p.ID_Proveedor = a.ID_Proveedor
            LEFT JOIN c_almacenp on c_almacenp.id = v.cve_almac
        WHERE v.Existencia > 0
            AND v.`tipo`='ubicacion'
            AND c_almacenp.clave = '{$almacen}'
        GROUP BY v.cve_articulo
    ";

    if (!($res = mysqli_query($conn, $sql))) {echo json_encode(array( "error" => "Error al procesar la petición: (" . mysqli_error($conn) . ") "));}
  
    $data = array();
    $i = 0;
    while ($row = mysqli_fetch_array($res)) {

        $valor1 = 0;
        if($row['piezasxcajas'] > 0)
           $valor1 = $row['existencia']/$row['piezasxcajas'];

        if($row['cajasxpallets'] > 0)
           $valor1 = $valor1/$row['cajasxpallets'];
       else
           $valor1 = 0;

        $row['Pallet'] = intval($valor1);

        $valor2 = 0;
        $cantidad_restante = $row['existencia'] - ($row['Pallet']*$row['piezasxcajas']*$row['cajasxpallets']);
        if(!is_int($valor1) || $valor1 == 0)
        {
            if($row['piezasxcajas'] > 0)
               $valor2 = ($cantidad_restante/$row['piezasxcajas']);// - ($row['Pallet']*$row['existencia']);
        }
        $row['Caja'] = intval($valor2);

        $row['Piezas'] = 0;
        if($row['piezasxcajas'] == 1 || $row['piezasxcajas'] == 0 || $row['piezasxcajas'] == "") 
        {
            if($row['piezasxcajas'] == "") $row['piezasxcajas'] = 0;
            $valor2 = 0; 
            $row['Caja'] = $cantidad_restante;
            $row['Piezas'] = 0;
        }
        $cantidad_restante = $cantidad_restante - ($row['Caja']*$row['piezasxcajas']);

        if(!is_int($valor2))
        {
            $row['Piezas'] = $cantidad_restante;
        }

        $row = array_map('utf8_encode', $row);
        $data[] = $row;
        $i++;
    }
  
    mysqli_close();

    include '../../../app/vendor/mk-j/php_xlsxwriter/xlsxwriter.class.php';
    $title = "Reporte de Concentrado de existencias.xls";

    $header = array(
        'Proveedor',
        'Clave',
        'Descripción',
        'Pallet',
        'Caja',
        'Piezas',
        'Existencia'
    );
    $excel = new XLSXWriter();
    $excel->writeSheetRow('Sheet1', $header );

    $sum = 0;
    foreach($data as $d)
    {
       $sum+= $d['existencia'];
    }
  
    $sum1 = $sum;
  
    foreach($data as $d){
      
        $row = array(
            $d['proveedor'],
            $d['clave'],
            $d['nombre'],
            $d['Pallet'],
            $d['Caja'],
            $d['Piezas'],
            $d['existencia'],
            $sum1
          
        );
        $sum1 = "";
        $excel->writeSheetRow('Sheet1', $row );
    }
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $title . '"');
    header('Cache-Control: max-age=0');
    $excel->writeToStdOut($title);
    $app->stop();

}

if (isset($_POST) && !empty($_POST) && $_POST['action'] === 'totales')
{
    $almacen = $_POST['almacen'];
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
     
    $sqlTotales ="
        SELECT  
            (SELECT COUNT(*) FROM c_articulo WHERE cve_almac = (SELECT id FROM c_almacenp WHERE clave = '{$almacen}')) AS productos,
            IFNULL(TRUNCATE(SUM(v.Existencia),2), 0) AS unidades
        FROM V_ExistenciaGralProduccion v
            LEFT JOIN c_articulo a ON a.cve_articulo = v.cve_articulo
        WHERE v.cve_almac = (SELECT id FROM c_almacenp WHERE clave = '{$almacen}');

    ";
    
    if (!($res = mysqli_query($conn, $sqlTotales))) {echo json_encode(array( "error" => "Error al procesar la petición: (" . mysqli_error($conn) . ") "));}
    //echo var_dump($sqlTotales);
    
    $data = array();
    $i = 0;
    while ($row = mysqli_fetch_array($res)) {
        $row = array_map('utf8_encode', $row);
        $data[] = $row;
        $i++;
    }
  
    mysqli_close();
    header('Content-type: application/json');
    $output = array("data" => $data); 
    echo json_encode($output);
}

?>
