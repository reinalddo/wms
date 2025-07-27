<?php namespace Api\QaCuarentena;

use Framework\Helpers\Utils;
use Framework\Http\Response;
use Framework\Http\Controller;
use Application\Models\AlmacenP;
use Application\Models\InvPiezas;
use Application\Models\QaCuarentena;


error_reporting(0);
include_once $_SERVER['DOCUMENT_ROOT'].'/Framework/app.php';
include_once $_SERVER['DOCUMENT_ROOT'].'/Framework/Http/Controller.php';
include_once $_SERVER['DOCUMENT_ROOT'].'/Framework/Database/Model.php';
include_once $_SERVER['DOCUMENT_ROOT'].'/Application/Models/AlmacenP.php';
include_once $_SERVER['DOCUMENT_ROOT'].'/Application/Models/InvPiezas.php';
include_once $_SERVER['DOCUMENT_ROOT'].'/Application/Models/QaCuarentena.php';
include_once $_SERVER['DOCUMENT_ROOT'].'/Framework/Http/Response.php';
include_once $_SERVER['DOCUMENT_ROOT'].'/Framework/Helpers/Utils.php';

class QaCuarentenaAPI extends Controller
{
    public function all()
    {

        $almacen   = $this->pSQL($_GET['almacen']);
        $articulo   = $this->pSQL($_GET['articulo']);
        $zona   = $this->pSQL($_GET['zona']);

        if ( $almacen != '') {
            $almacen = "AND a.id = '{$almacen}'";
        } else $almacen = '';


        if ( $zona != '') {
            $zona = "AND z.cve_almac = '{$zona}'";

        } else $zona = '';
      
        if ( $articulo != '' ) {
            $articulo = "AND e.cve_articulo = '{$articulo}'";
        } else $articulo = '';    


        $model = new AlmacenP();
        $rows = $model->execute("SELECT 
            e.id AS id
            , e.NConteo AS conteo
            , e.idy_ubica AS ubicacion
            , e.cve_articulo AS clave_producto
            , e.Cantidad AS existencia
            , e.cve_lote AS lote
            , p.des_articulo AS nombre_producto
            , a.id almacen_id  
            , a.clave AS clave_almacen
            , a.nombre AS nombre_almacen	
            , z.cve_almac AS zona_id
            , z.des_almac AS nombre_zona
            , 1 AS tipo
            , (SELECT CADUCIDAD FROM c_lotes WHERE LOTE = e.cve_lote LIMIT 1 ) caducidad
            , u.cve_pasillo AS pasillo
            , u.cve_rack AS rack
            , u.cve_nivel AS nivel
            , u.Seccion AS seccion
      
            , e.cuarentena_ini
            , e.cuarentena_ini_user
            , (SELECT nombre_completo FROM c_usuario WHERE id_user = e.cuarentena_ini_user) cuarentena_ini_user_desc

            , e.cuarentena_fin
            , e.cuarentena_fin_user
            , (SELECT nombre_completo FROM c_usuario WHERE id_user = e.cuarentena_fin_user) cuarentena_fin_user_desc
        FROM t_invpiezas e
            JOIN c_articulo p ON e.cve_articulo = p.cve_articulo
            JOIN c_almacen z ON z.cve_almac = (SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = e.idy_ubica LIMIT 1)
            JOIN c_almacenp a ON z.cve_almacenp = a.id
            JOIN c_ubicacion u ON u.idy_ubica = e.idy_ubica
        WHERE (e.cuarentena = 1 OR e.cuarentena = 2) {$where} {$almacen} {$zona} {$articulo}

        UNION ALL 

        SELECT 
           e.id_invcajas AS id
            , e.NConteo AS conteo
            , e.idy_ubica ubicacion
            , e.cve_articulo AS clave_producto
            , e.Cantidad AS existencia
            , e.cve_lote AS lote
            , p.des_articulo AS nombre_producto
            , a.id almacen_id  
            , a.clave AS clave_almacen
            , a.nombre AS nombre_almacen	
            , z.cve_almac AS zona_id
            , z.des_almac AS nombre_zona
            , 1 AS tipo
            , (SELECT CADUCIDAD FROM c_lotes WHERE LOTE = e.cve_lote LIMIT 1 ) caducidad
            , u.cve_pasillo AS pasillo
            , u.cve_rack AS rack
            , u.cve_nivel AS nivel
            , u.Seccion AS seccion

            , e.cuarentena_ini
            , e.cuarentena_ini_user
            , (SELECT nombre_completo FROM c_usuario WHERE id_user = e.cuarentena_ini_user) cuarentena_ini_user_desc

            , e.cuarentena_fin
            , e.cuarentena_fin_user
            , (SELECT nombre_completo FROM c_usuario WHERE id_user = e.cuarentena_fin_user) cuarentena_fin_user_desc

        FROM t_invcajas e
            JOIN c_articulo p ON e.cve_articulo = p.cve_articulo
            JOIN c_almacen z ON z.cve_almac = (SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = e.idy_ubica LIMIT 1)
            JOIN c_almacenp a ON z.cve_almacenp = a.id
            JOIN c_ubicacion u ON u.idy_ubica = e.idy_ubica
        WHERE (e.cuarentena = 1 OR e.cuarentena = 2)

        ORDER BY cuarentena_ini ASC, nombre_producto ASC
        ")->get();

        $page       = $_GET['page']; // get the requested page
        $limit      = $_GET['rows']; // get how many rows we want to have into the grid
        $sidx       = $_GET['sidx']; // get index row - i.e. user click to sort
        $sord       = $_GET['sord']; 
        $articulo   = $_GET["producto"];
        $almacen    = $_GET["almacen"];
        $zona       = $_GET["zona"];

        //Paginación
        $start = $limit * $page - $limit; // do not put $limit*($page - 1)
        if(!$sidx) $sidx =1;
    
        $count = count($rows);

        $_page = 0;
    
        if (intval($page)>0){
            $_page = ($page-1) * $limit;
        }
       
        if( $count >0 ) {
            $total_pages = ceil($count/$limit);
        } else {
            $total_pages = 0;
        }
        
        if ($page > $total_pages){
            $page = $total_pages;
        }
            
    
        $response = [
            'page' => $page,
            'total' => $total_pages,
            'records' => $count,
            'rows' => $rows
        ];


        $this->response(200,$response);
    }


    public function findProductos()
    {
        $page   = $_GET['page']; // get the requested page
        $limit  = $_GET['rows']; // get how many rows we want to have into the grid
        $sidx   = $_GET['sidx']; // get index row - i.e. user click to sort
        $sord   = $_GET['sord']; // get the direction
    
        //Se recibe los parametros POST del Grid
        $criterio   = $this->pSQL($_GET['criterio']);
    
        if( $criterio == '' ){
            $this->response(404, []);
        }
    
        /**
         * Fitros: 
         *  1: Producto (Default)
         *  2: Almacén   
         *  3: Zona                                    
         *  4: Lote
         *  5: Todos
         */   
        $filtro = $this->pSQL($_GET['filtro']);
    
        switch ($filtro) {
            case '2': //Almacén  
                $where = "a.clave LIKE '%{$criterio}%' OR a.nombre LIKE '%{$criterio}%'";
            break;
            case '3': //Zona de almacenaje
                $where = "z.clave_almacen LIKE '%%' OR z.des_almac LIKE '%%'";
            break;
            case '4': //Lote
                $where = "l.LOTE LIKE '%{$criterio}%'";
            break;

            case '5': //Todos
                $where = "a.clave LIKE '%{$criterio}%' OR a.nombre LIKE '%{$criterio}%' OR ".
                          "z.clave_almacen LIKE '%{$criterio}%' OR z.des_almac LIKE '%{$criterio}%' OR ".
                          "l.LOTE LIKE '%{$criterio}%'";
            break;
            default: //Producto (Default)
                $where = "e.cve_articulo LIKE '%{$criterio}%' OR p.des_articulo LIKE '%{$criterio}%'";
            break;
        }
       
    
        $sql = "SELECT 
                e.id
                , e.NConteo conteo
                , e.idy_ubica ubicacion_id
                , e.cve_articulo AS clave_producto
                , e.Cantidad AS existencia
                , e.cve_lote AS lote
                , p.des_articulo AS nombre_producto
                , a.id almacen_id  
                , a.clave AS clave_almacen
                , a.nombre AS nombre_almacen	
                , z.cve_almac AS zona_id
                , z.des_almac AS nombre_zona
                , z.des_almac AS nombre_zona
                , 1 as tipo
            FROM t_invpiezas e
                JOIN c_articulo p ON e.cve_articulo = p.cve_articulo
                JOIN c_almacen z ON z.cve_almac = (SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = e.idy_ubica LIMIT 1)
            JOIN c_almacenp a ON z.cve_almacenp = a.id   
            WHERE ({$where}) AND e.cuarentena = 0
                AND e.Cantidad > 0 AND e.NConteo = (
                    SELECT 
                        MAX(e2.NConteo) conteo FROM t_invpiezas e2 
                    WHERE  e2.cve_articulo = e.cve_articulo AND e2.idy_ubica = e.idy_ubica AND e2.cve_lote = e.cve_lote AND e2.ID_Inventario = e.ID_Inventario
                )

            UNION ALL 
            
            SELECT 	 
                e.id_invcajas id
                , e.NConteo conteo
                , e.idy_ubica ubicacion_id
                , e.cve_articulo AS clave_producto
                , e.Cantidad AS existencia
                , e.cve_lote AS lote
                , p.des_articulo AS nombre_producto
                , a.id almacen_id  
                , a.clave AS clave_almacen
                , a.nombre AS nombre_almacen	
                , z.cve_almac AS zona_id
                , z.des_almac AS nombre_zona
                , z.des_almac AS nombre_zona
                , 2 as tipo
            FROM t_invcajas e
                JOIN c_articulo p ON e.cve_articulo = p.cve_articulo
                JOIN c_almacen z ON z.cve_almac = (SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = e.idy_ubica LIMIT 1)
            JOIN c_almacenp a ON z.cve_almacenp = a.id   
            WHERE ({$where}) AND e.cuarentena = 0
                AND e.Cantidad > 0 AND e.NConteo = (
                    SELECT 
                        MAX(e2.NConteo) conteo FROM t_invpiezas e2 
                    WHERE  e2.cve_articulo = e.cve_articulo AND e2.idy_ubica = e.idy_ubica AND e2.cve_lote = e.cve_lote AND e2.ID_Inventario = e.ID_Inventario
                )            
            ORDER BY nombre_producto ASC";


        $model = new AlmacenP();
        $rows = $model->execute($sql)->get();

        $response = $this->prepareStructureResponseGrid($rows, $page, $limit, $sidx, $sord);
       
    
        $i = 0;
        foreach ($rows as $key => $value) {      
            $response['rows'][$i]['cell'] = [
                $value['id'],
                $value['ubicacion_id'],
                $value['clave_producto'],
                $value['nombre_producto'],
                $value['clave_almacen'],
                $value['nombre_almacen'],
                $value['zona_id'],
                $value['nombre_zona'],
                $value['lote'],
                $value['existencia'],
                $value['tipo'],
                ($value['tipo'] == '1' ? 'Piezas' : 'Paquete'),
                ''
            ];
            $i++;
        }
       $this->response(200,$response);
    }


    public function agregarACuarentena()
    {
        $items = $this->pSQL($_POST['items']);
        $model = new InvPiezas();

        $sqlPatron = "UPDATE %s SET
                cuarentena = 1,
                cuarentena_ini = NOW(),
                cuarentena_ini_user ='%s'
            WHERE cve_articulo = '%s' AND cve_lote = '%s' AND idy_ubica = '%s' AND %s = '%s'
        ";

        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        foreach ($items as $key => $value) {

            if( $value['tipo'] == 1 ) {
                $tabla ='t_invpiezas';
                $id = 'id';
            } elseif( $value['tipo'] == 2 ) {
                $tabla ='t_invcajas';
                $id = 'id_invcajas';
            } else {
                $tabla ='t_invtarima';
            }

            $sql = sprintf($sqlPatron, $tabla ,
                $_SESSION['id_user'],
                $value['clave_producto'],
                $value['lote'],
                $value['ubicacion_id'],
                $id,
                $value['id']
            );

            //exit($sql);
            if (!($res = mysqli_query($conn, $sql))) {
                echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
            }
        }     

        //$response = $this->prepareStructureResponseyGrid( $rows, $page, $limit, $sidx, $sord );

        $this->response(200,[]);

    }


    public function sacarDeCuarentena()
    {
        $items = $this->pSQL($_POST['items']);
        $model = new InvPiezas();

        $sqlPatron = "UPDATE %s SET
                cuarentena = 2,
                cuarentena_fin = NOW(),
                cuarentena_fin_user ='%s'
                WHERE %s = '%s'
        ";

        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        foreach ($items as $key => $value) {

            if( $value['tipo'] == 1 ) {
                $tabla ='t_invpiezas';
                $pk = 'id';
            } elseif( $value['tipo'] == 2 ) {
                $tabla ='t_invcajas';
                $pk = 'id_invcajas';
            } else {
                $tabla ='t_invtarima';
            }

            $sql = sprintf($sqlPatron, $tabla ,
                $_SESSION['id_user'],
                $pk,
                $value['id']
            );

            
            if (!($res = mysqli_query($conn, $sql))) {
                echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
            }
        }     

        //$response = $this->prepareStructureResponseyGrid( $rows, $page, $limit, $sidx, $sord );

        $this->response(200,[]);

    }


    public function sacarUno()
    {
        $id = $this->pSQL($_POST['id']);
        $tipo = $this->pSQL($_POST['tipo']);
        $model = new InvPiezas();

        $sqlPatron = "UPDATE %s SET
                cuarentena = 2,
                cuarentena_fin = NOW(),
                cuarentena_fin_user ='%s'
                WHERE %s = '%s'
        ";

        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
      
        if( $tipo == 1 ) {
            $tabla ='t_invpiezas';
            $pk = 'id';
        } elseif( $tipo == 2 ) {
            $tabla ='t_invcajas';
            $pk = 'id_invcajas';
        } else {
            $tabla ='t_invtarima';
        }

        $sql = sprintf($sqlPatron, $tabla ,
            $_SESSION['id_user'],
            $pk,
            $id
        );

        //exit($sql);
        if (!($res = mysqli_query($conn, $sql))) {
            echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
        }
        
        $this->response(200,[]);

    }


    public function reporteExcel()
    {

        $almacen   = $this->pSQL($_GET['almacen']);
        $articulo   = $this->pSQL($_GET['articulo']);
        $zona   = $this->pSQL($_GET['zona']);

        if ( $almacen != '') {
            $almacen = "AND a.id = '{$almacen}'";
        } else $almacen = '';


        if ( $zona != '') {
            $zona = "AND z.cve_almac = '{$zona}'";
        } else $zona = '';
      
        if ( $articulo != '' ) {
            $articulo = "AND e.cve_articulo = '{$articulo}'";
        } else $articulo = '';    


        $model = new AlmacenP();
        $rows = $model->execute("SELECT 
            e.NConteo conteo
            , e.idy_ubica ubicacion
            , e.cve_articulo AS clave_producto
            , e.Cantidad AS existencia
            , e.cve_lote AS lote
            , p.des_articulo AS nombre_producto
            , a.id almacen_id  
            , a.clave AS clave_almacen
            , a.nombre AS nombre_almacen	
            , z.cve_almac AS zona_id
            , z.des_almac AS nombre_zona
            , z.des_almac AS nombre_zona
            , 1 AS tipo
            , (SELECT CADUCIDAD FROM c_lotes WHERE LOTE = e.cve_lote LIMIT 1 ) caducidad
            , u.cve_pasillo AS pasillo
            , u.cve_rack AS rack
            , u.cve_nivel AS nivel
            , u.Seccion AS seccion
      
            , e.cuarentena_ini
            , e.cuarentena_ini_user
            , (SELECT nombre_completo FROM c_usuario WHERE id_user = e.cuarentena_ini_user) cuarentena_ini_user_desc

            , e.cuarentena_fin
            , e.cuarentena_fin_user
            , (SELECT nombre_completo FROM c_usuario WHERE id_user = e.cuarentena_fin_user) cuarentena_fin_user_desc
        FROM t_invpiezas e
            JOIN c_articulo p ON e.cve_articulo = p.cve_articulo
            JOIN c_almacen z ON z.cve_almac = (SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = e.idy_ubica LIMIT 1)
            JOIN c_almacenp a ON z.cve_almacenp = a.id
            JOIN c_ubicacion u ON u.idy_ubica = e.idy_ubica
        WHERE (e.cuarentena = 1 OR e.cuarentena = 2) {$where} {$almacen} {$zona} {$articulo}

        UNION ALL 

        SELECT 
            e.NConteo conteo
            , e.idy_ubica ubicacion
            , e.cve_articulo AS clave_producto
            , e.Cantidad AS existencia
            , e.cve_lote AS lote
            , p.des_articulo AS nombre_producto
            , a.id almacen_id  
            , a.clave AS clave_almacen
            , a.nombre AS nombre_almacen	
            , z.cve_almac AS zona_id
            , z.des_almac AS nombre_zona
            , z.des_almac AS nombre_zona
            , 1 AS tipo
            , (SELECT CADUCIDAD FROM c_lotes WHERE LOTE = e.cve_lote LIMIT 1 ) caducidad
            , u.cve_pasillo AS pasillo
            , u.cve_rack AS rack
            , u.cve_nivel AS nivel
            , u.Seccion AS seccion

            , e.cuarentena_ini
            , e.cuarentena_ini_user
            , (SELECT nombre_completo FROM c_usuario WHERE id_user = e.cuarentena_ini_user) cuarentena_ini_user_desc

            , e.cuarentena_fin
            , e.cuarentena_fin_user
            , (SELECT nombre_completo FROM c_usuario WHERE id_user = e.cuarentena_fin_user) cuarentena_fin_user_desc

        FROM t_invcajas e
            JOIN c_articulo p ON e.cve_articulo = p.cve_articulo
            JOIN c_almacen z ON z.cve_almac = (SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = e.idy_ubica LIMIT 1)
            JOIN c_almacenp a ON z.cve_almacenp = a.id
            JOIN c_ubicacion u ON u.idy_ubica = e.idy_ubica
        WHERE (e.cuarentena = 1 OR e.cuarentena = 2)

        ORDER BY cuarentena_ini ASC, nombre_producto ASC
        ")->get();


        include PATH_ROOT . 'app/vendor/mk-j/php_xlsxwriter/xlsxwriter.class.php';
        $title = "Reporte de Cuarentena.xlsx";

        $header = array(
            'Almacén',
            'Zona de Almacenaje',
            'Pasillo',
            'Rack',
            'Nivel',
            'Sección',
            'Ubicación',
            'Clave',
            'Descripción',
            'Lote',
            'Caducidad',
            'N. Serie',
            'Cantidad',
            'Entrada a cuarentena',
            'Responsable',
            'Salida de cuarentena',
            'Responsable'
        );
        $excel = new \XLSXWriter();
        $excel->writeSheetRow('Sheet1', $header );

        foreach($rows as $d){
            $row = array(
                $d['almacen'],
                $d['zona'],
                $d['pasillo'],
                $d['rack'],
                $d['nivel'],
                $d['seccion'],
                $d['ubicacion'],
                $d['clave'],
                $d['descripcion'],
                $d['lote'],
                $d['caducidad'],
                $d['nserie'],
                $d['cantidad'],
                $d['cuarentena_ini'],
                $d['cuarentena_ini_user_desc'],                
                $d['cuarentena_fin'],
                $d['cuarentena_fin_user_desc'],
            );
            $excel->writeSheetRow('Sheet1', $row );
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $title . '"');
        header('Cache-Control: max-age=0');
        $excel->writeToStdOut($title);
        exit;
        $app->stop();
    }

    public function reportePDF()
    {

        $almacen   = $this->pSQL($_GET['almacen']);
        $articulo   = $this->pSQL($_GET['articulo']);
        $zona   = $this->pSQL($_GET['zona']);

        if ( $almacen != '') {
            $almacen = "AND a.id = '{$almacen}'";
        } else $almacen = '';


        if ( $zona != '') {
            $zona = "AND z.cve_almac = '{$zona}'";
        } else $zona = '';
      
        if ( $articulo != '' ) {
            $articulo = "AND e.cve_articulo = '{$articulo}'";
        } else $articulo = '';    


        $model = new AlmacenP();
        $rows = $model->execute("SELECT 
            e.NConteo conteo
            , e.idy_ubica ubicacion
            , e.cve_articulo AS clave_producto
            , e.Cantidad AS existencia
            , e.cve_lote AS lote
            , p.des_articulo AS nombre_producto
            , a.id almacen_id  
            , a.clave AS clave_almacen
            , a.nombre AS nombre_almacen	
            , z.cve_almac AS zona_id
            , z.des_almac AS nombre_zona
            , z.des_almac AS nombre_zona
            , 1 AS tipo
            , (SELECT CADUCIDAD FROM c_lotes WHERE LOTE = e.cve_lote LIMIT 1 ) caducidad
            , u.cve_pasillo AS pasillo
            , u.cve_rack AS rack
            , u.cve_nivel AS nivel
            , u.Seccion AS seccion
      
            , e.cuarentena_ini
            , e.cuarentena_ini_user
            , (SELECT nombre_completo FROM c_usuario WHERE id_user = e.cuarentena_ini_user) cuarentena_ini_user_desc

            , e.cuarentena_fin
            , e.cuarentena_fin_user
            , (SELECT nombre_completo FROM c_usuario WHERE id_user = e.cuarentena_fin_user) cuarentena_fin_user_desc
        FROM t_invpiezas e
            JOIN c_articulo p ON e.cve_articulo = p.cve_articulo
            JOIN c_almacen z ON z.cve_almac = (SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = e.idy_ubica LIMIT 1)
            JOIN c_almacenp a ON z.cve_almacenp = a.id
            JOIN c_ubicacion u ON u.idy_ubica = e.idy_ubica
        WHERE (e.cuarentena = 1 OR e.cuarentena = 2) {$where} {$almacen} {$zona} {$articulo}

        UNION ALL 

        SELECT 
            e.NConteo conteo
            , e.idy_ubica ubicacion
            , e.cve_articulo AS clave_producto
            , e.Cantidad AS existencia
            , e.cve_lote AS lote
            , p.des_articulo AS nombre_producto
            , a.id almacen_id  
            , a.clave AS clave_almacen
            , a.nombre AS nombre_almacen	
            , z.cve_almac AS zona_id
            , z.des_almac AS nombre_zona
            , z.des_almac AS nombre_zona
            , 1 AS tipo
            , (SELECT CADUCIDAD FROM c_lotes WHERE LOTE = e.cve_lote LIMIT 1 ) caducidad
            , u.cve_pasillo AS pasillo
            , u.cve_rack AS rack
            , u.cve_nivel AS nivel
            , u.Seccion AS seccion

            , e.cuarentena_ini
            , e.cuarentena_ini_user
            , (SELECT nombre_completo FROM c_usuario WHERE id_user = e.cuarentena_ini_user) cuarentena_ini_user_desc

            , e.cuarentena_fin
            , e.cuarentena_fin_user
            , (SELECT nombre_completo FROM c_usuario WHERE id_user = e.cuarentena_fin_user) cuarentena_fin_user_desc

        FROM t_invcajas e
            JOIN c_articulo p ON e.cve_articulo = p.cve_articulo
            JOIN c_almacen z ON z.cve_almac = (SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = e.idy_ubica LIMIT 1)
            JOIN c_almacenp a ON z.cve_almacenp = a.id
            JOIN c_ubicacion u ON u.idy_ubica = e.idy_ubica
        WHERE (e.cuarentena = 1 OR e.cuarentena = 2)

        ORDER BY cuarentena_ini ASC, nombre_producto ASC
        ")->get();


        include PATH_ROOT . 'app/vendor/mk-j/php_xlsxwriter/xlsxwriter.class.php';
        $title = "Reporte de Cuarentena.xlsx";

        $header = array(
            'Almacén',
            'Zona de Almacenaje',
            'Pasillo',
            'Rack',
            'Nivel',
            'Sección',
            'Ubicación',
            'Clave',
            'Descripción',
            'Lote',
            'Caducidad',
            'N. Serie',
            'Cantidad',
            'Entrada a cuarentena',
            'Responsable',
            'Salida de cuarentena',
            'Responsable'
        );
        $excel = new \XLSXWriter();
        $excel->writeSheetRow('Sheet1', $header );

        foreach($rows as $d){
            $row = array(
                $d['almacen'],
                $d['zona'],
                $d['pasillo'],
                $d['rack'],
                $d['nivel'],
                $d['seccion'],
                $d['ubicacion'],
                $d['clave'],
                $d['descripcion'],
                $d['lote'],
                $d['caducidad'],
                $d['nserie'],
                $d['cantidad'],
                $d['cuarentena_ini'],
                $d['cuarentena_ini_user_desc'],                
                $d['cuarentena_fin'],
                $d['cuarentena_fin_user_desc'],
            );
            $excel->writeSheetRow('Sheet1', $row );
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $title . '"');
        header('Cache-Control: max-age=0');
        $excel->writeToStdOut($title);
        exit;
        $app->stop();
    }



}

Response::send( QaCuarentenaAPI::class );