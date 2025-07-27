<?php 

namespace Application\Controllers;

use Framework\View\View;
use Framework\Helpers\Utils;
use Framework\Http\Response;
use Framework\Http\Controller;
use Application\Models\AlmacenP;
use Application\Models\Usuarios;
use Application\Models\QaCuarentena;
use Application\Models\InventarioCajas;
use Application\Models\InventarioPiezas;
use Application\Models\InventarioTarima;
use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * @version 1.0.0
 * @category QA Cuarentena
 * @author Brayan Rincon <brayan262@gemail.com>
 */
class QACuarentenaController extends Controller
{

    public function __construct( )
    {
        
    }

       /**
     * Renderiza la vista general
     *
     * @return void
     */
    public function index()
    {
        //Obtener todas las empresas
        $almacenes = Capsule::table('c_almacenp')->where('Activo', 1)->get();

        return new View('qacuarentena.index', compact([
            'almacenes',
            'allowView', 'allowAdd', 'allowEdit', 'allowDelete'
        ]) );
    }


        /**
     * Devuelve todos los registros
     *
     * @return void
     */
    public function paginate()
    {     
        $page   = $this->pSQL($_POST['page']); // get the requested page
        $limit  = $this->pSQL($_POST['rows']); // get how many rows we want to have into the grid
        $sidx   = $this->pSQL($_POST['sidx']); // get index row - i.e. user click to sort
        $sord   = $this->pSQL($_POST['sord']); // get the direction
        $skip   = 0;

        $almacen    = $this->pSQL($this->pSQL($_GET['almacen']));
        $articulo   = $this->pSQL($_GET['articulo']);
        $zona       = $this->pSQL($_GET['zona']);

        $criterio = $this->pSQL($_POST['criterio']);

        if ( $almacen != '') {
            $almacen = "AND a.id = '{$almacen}'";
        } else $almacen = '';


        if ( $zona != '') {
            $zona = "AND z.cve_almac = '{$zona}'";

        } else $zona = '';
      
        if ( $articulo != '' ) {
            $articulo = "AND e.cve_articulo = '{$articulo}'";
        } else $articulo = '';    
            
       
        $sql = "SELECT 
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
                    , e.cuarentena status      
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
                WHERE (e.cuarentena = 1 OR e.cuarentena = 2) {$almacen} {$zona} {$articulo}

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

                    , e.cuarentena status 
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

                ORDER BY cuarentena_ini ASC, nombre_producto ASC";
        
        $rows = Capsule::select(Capsule::raw($sql));
        
        $count = count($rows);
        $data = [];
        $i = 0;
        foreach ($rows as $value) {

            if($value->status == 1) {
                $status = '<strong style="color:red">En cuarentena</strong>';
            }
            elseif($value->status == 2){
                $status = '<strong style="color:green">Liberado</strong>';
            } 
            else  {
                $status = '';
            }

            $data[$i]['id'] = $value->id    ;
            $data[$i]['cell'] = [
                $value->id,
                $value->clave_producto,
                $value->nombre_producto,
                $value->lote,
                $value->caducidad,
                $value->nserie,
                $value->existencia,
                $status,
                $value->nombre_almacen,
                $value->nombre_zona,
                $value->pasillo,
                $value->rack,
                $value->nivel,
                $value->seccion,
                $value->ubicacion,
                $value->tipo,
                $value->cuarentena_ini ? date_format(date_create($value->cuarentena_ini),"d/m/Y H:i:s") : '' ,
                $value->cuarentena_ini_user_desc,
                $value->cuarentena_fin ? date_format(date_create($value->cuarentena_fin),"d/m/Y H:i:s") : '',
                $value->cuarentena_fin_user_desc,
            ];
            $i++;
        }
        $response = $this->responseJQGrid($data);
        ob_clean();
        echo json_encode($response);exit;
    }



    public function buscarProductos()
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

        $rows = Capsule::select(Capsule::raw($sql));
     
    
        $i = 0; 
        $data = [];
        foreach ($rows as $key => $value) {      
            $data[$i]['cell'] = [
                $value->id,
                $value->ubicacion_id,
                $value->clave_producto,
                $value->nombre_producto,
                $value->clave_almacen,
                $value->nombre_almacen,
                $value->zona_id,
                $value->nombre_zona,
                $value->lote,
                $value->existencia,
                $value->tipo,
                ($value->tipo == '1' ? 'Piezas' : 'Paquete'),
                ''
            ];
            $i++;
        }
        $response = $this->responseJQGrid($data);
        ob_clean();
        echo json_encode($response);exit;
    }

 

    public function agregarProductoACuarentena()
    {
        $items = $this->pSQL($_POST['items']);
        $user = $_SESSION['id_user'];

        foreach ($items as $key => $value) 
        {
            if( $value['tipo'] == 1 ) {
                InventarioPiezas::where('cve_articulo', $value['clave_producto'])
                    ->where('idy_ubica', $value['ubicacion_id'])
                    ->where('cve_lote', $value['lote'])
                    ->where('id', $value['id'])
                    ->update(['cuarentena' => 1, 'cuarentena_ini' =>  Capsule::raw('NOW()'), 'cuarentena_ini_user' => $user]);
            }
            elseif( $value['tipo'] == 2 ) {
                InventarioCajas::where('cve_articulo', $value['clave_producto'])
                    ->where('idy_ubica', $value['ubicacion_id'])
                    ->where('cve_lote', $value['lote'])
                    ->where('id_invcajas', $value['id'])
                    ->update(['cuarentena' => 1, 'cuarentena_ini' => Capsule::raw('NOW()'), 'cuarentena_ini_user' => $user]);
            }
            else {
                InventarioTarima::where('cve_articulo', $value['clave_producto'])
                    ->where('idy_ubica', $value['ubicacion_id'])
                    ->where('cve_lote', $value['lote'])
                    ->where('id', $value['id'])
                    ->update(['cuarentena' => 1, 'cuarentena_ini' => Capsule::raw('NOW()'), 'cuarentena_ini_user' => $user]);
            }
        }

        $this->response(200,[]);
    }


    public function sacarProductoDeCuarentena()
    {
        $items = $this->pSQL($_POST['items']);
        $user = $_SESSION['id_user'];

        foreach ($items as $key => $value) 
        {
            if( $value['tipo'] == 1 ) {
                InventarioPiezas::where('id', $value['id'])
                    ->update(['cuarentena' => 2, 'cuarentena_fin' => Capsule::raw('NOW()'), 'cuarentena_fin_user' => $user]);
            }
            elseif( $value['tipo'] == 2 ) {
                InventarioCajas::where('id_invcajas', $value['id'])
                    ->update(['cuarentena' => 2, 'cuarentena_fin' => Capsule::raw('NOW()'), 'cuarentena_fin_user' => $user]);
            }
            else {
                InventarioTarima::where('id', $value['id'])
                    ->update(['cuarentena' => 2, 'cuarentena_fin' => Capsule::raw('NOW()'), 'cuarentena_fin_user' => $user]);
            }
        }

        $this->response(200,[]);

    }


    public function reporteExcel()
    {

        $almacen    = $this->pSQL($_GET['almacen']);
        $articulo   = $this->pSQL($_GET['articulo']);
        $zona       = $this->pSQL($_GET['zona']);

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