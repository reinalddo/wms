<?php

namespace AdministradorPedidos;

class AdministradorPedidos 
{
    const TABLE = 'th_pedido';
    var $identifier;

    public function __construct( $Fol_folio = false, $key = false ) 
    {
        if( $Fol_folio ) 
        {
            $this->Fol_folio = (int) $Fol_folio;
        }

        if($key) 
        {
            $sql = sprintf(' SELECT Fol_folio FROM %s WHERE Fol_folio = ? ', self::TABLE );
            $sth = \db()->prepare($sql);
            $sth->setFetchMode(\PDO::FETCH_CLASS, '\AdministradorPedidos\AdministradorPedidos');
            $sth->execute(array($key));
            $Fol_folio = $sth->fetch();
            $this->Fol_folio = $Fol_folio->Fol_folio;
        }
    }

    private function load() 
    {
        $sql = sprintf(' SELECT * FROM %s WHERE Fol_folio = ? ', self::TABLE );
        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\AdministradorPedidos\AdministradorPedidos' );
        $sth->execute( array( $this->Fol_folio ) );
        $this->data = $sth->fetch();
    }

    function getAll() 
    {
        $sql = ' SELECT * FROM ' . self::TABLE . ' where Activo=1 ';
        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\AdministradorPedidos\AdministradorPedidos' );
        $sth->execute( array( Fol_folio ) );
        return $sth->fetchAll();
    }

    public function generateExcel($data)
    {
        include '../../../app/vendor/mk-j/php_xlsxwriter/xlsxwriter.class.php';
        $title = "Reporte de Pedidos.xlsx";
        $criterio = $data['criterio'];
        $fecha_inicio = $data['fechaInicio'];
        $fecha_fin = $data['fechaFin'];
        $filtro = $data['filtro'];
        $status = $data['status'];
        $almacen = $data['almacen'];
        $factura_inicio = $data['facturaInicio'];
        $factura_fin = $data['facturaFin'];    

        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME); 
        mysqli_set_charset($conn, 'utf8');
        if (!empty($fecha_inicio)) 
        {
            $fecha_inicio = date("Y-m-d", strtotime($fecha_inicio));
        }
        if (!empty($fecha_fin)) 
        {
            $fecha_fin = date("Y-m-d", strtotime($fecha_fin));
        }

        $sql = "
            SELECT
                o.Fol_folio AS orden,
                IFNULL(o.Pick_Num, '--') AS orden_cliente,
                IFNULL(p.Descripcion, '--') AS prioridad,
                IFNULL(e.DESCRIPCION, '--') AS status,
                IFNULL(c.RazonSocial, '--') AS cliente,
                IFNULL(c.CalleNumero, '--') AS direccion,
                IFNULL(c.CodigoPostal, '--') AS dane,
                IFNULL(c.Ciudad, '--') AS ciudad,
                IFNULL(c.Estado, '--') AS estado,
                COALESCE(SUM(od.Num_cantidad), 0) AS cantidad,
                IFNULL(DATE_FORMAT(o.Fec_Entrada, '%d-%m-%Y'), '--') AS fecha_pedido,
                IFNULL(DATE_FORMAT(o.Fec_Entrega, '%d-%m-%Y'), '--') AS fecha_entrega,
                IFNULL(u.nombre_completo, '--') AS usuario,
                o.id_pedido
            FROM th_pedido o
                LEFT JOIN t_tiposprioridad p ON p.ID_Tipoprioridad = o.ID_Tipoprioridad
                LEFT JOIN cat_estados e ON e.ESTADO = o.status
                LEFT JOIN c_cliente c On c.Cve_Clte = o.Cve_clte
                LEFT JOIN c_usuario u ON u.cve_usuario = o.Cve_Usuario
                LEFT JOIN td_pedido od ON od.Fol_folio = o.Fol_folio
            WHERE o.Activo = 1;
        ";
        if (!empty($almacen)) 
        {
            $sql .= " AND o.cve_almac = '$almacen'";
        }
        if (!empty($status)) 
        {
            $sql .= " AND o.status = '$status'";
        }
        if (!empty($criterio) && !empty($filtro)) 
        {
            $sql .= "AND {$filtro} like '%$criterio%'";
        }
        if (!empty($fecha_inicio) && !empty($fecha_fin)) 
        {
            $sql .= " AND o.Fec_Pedido BETWEEN '$fecha_inicio' AND '$fecha_fin'";
        } 
        elseif (!empty($fecha_inicio)) 
        {
            $sql .= " AND o.Fec_Pedido >= '$fecha_inicio'";
        } 
        elseif (!empty($fecha_fin)) 
        {
            $sql .= " AND o.Fec_Pedido <= '$fecha_fin'";
        }
        if (!empty($factura_inicio) && !empty($factura_fin)) 
        {
            $sql .= " AND o.Fol_folio BETWEEN '$factura_inicio' AND '$factura_fin'";
        } 
        elseif (!empty($factura_inicio)) 
        {
            $sql .= " AND o.Fol_folio >= $factura_inicio";
        } 
        elseif (!empty($factura_fin)) 
        {
            $sql .= " AND o.Fol_folio <= $factura_fin";
        }

        $sql .= " GROUP BY o.Fol_folio ORDER BY o.Fec_Entrada DESC;";
        $query = mysqli_query($conn, $sql);
        $data = mysqli_fetch_all($query, MYSQLI_ASSOC);
        $header = array(
            'No. Orden'         => 'string',
            'No. OC Cliente'    => 'string',
            'Prioridad'         => 'string',
            'Status'            => 'string',
            'Cliente'           => 'string',
            'Dirección'         => 'string',
            'Código Dane'       => 'string',
            'Ciudad'            => 'string',
            'Estado'            => 'string',
            'Cantidad'          => 'string',
            'Volumen'           => 'string',
            'Peso'              => 'string',
            'Fecha Registro'    => 'string',
            'Fecha Entrega'     => 'string',
            'Usuario Activo'    => 'string',
            '% Surtido'         => 'string'
        );
        $excel = new \XLSXWriter();
        $excel->writeSheetHeader('Sheet1', $header);
        foreach($data as $d)
        {
            extract($d);
            $sqlMore = "
                SELECT * FROM (
                    (
                        SELECT COALESCE(SUM((alto/1000) * (fondo/1000) * (ancho/1000)), 0) AS volumen 
                        FROM c_articulo 
                        WHERE cve_articulo IN (SELECT cve_articulo FROM td_pedido WHERE Fol_folio = '$orden')
                    ) AS volumen,
                    (
                        SELECT IFNULL(SUM(a.peso), 0) AS peso 
                        FROM td_pedido tdp LEFT JOIN c_articulo a ON a.cve_articulo = tdp.Cve_articulo 
                        WHERE tdp.Fol_folio = '$orden'
                    ) AS peso,
                    (SELECT PorcentajeSurtidoOrden('$orden') AS surtido) AS surtido
                );
            ";
            $queryMore = mysqli_query($conn, $sqlMore);
            $extraData = mysqli_fetch_assoc($queryMore);
            extract($extraData);

            $row = array(
                $orden, 
                $orden_cliente, 
                $prioridad, 
                $status, 
                $cliente, 
                $direccion, 
                $dane, 
                $ciudad, 
                $estado, 
                $cantidad, 
                $volumen, 
                $peso, 
                $fecha_pedido, 
                $fecha_entrega, 
                $usuario, 
                $surtido
            );
            $excel->writeSheetRow('Sheet1', $row );
        }
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $title . '"');
        header('Cache-Control: max-age=0');
        header('Content-Transfer-Encoding: binary');
        header('Cache-Control: must-revalidate');
        $excel->writeToStdOut($title);
    }

    public function loadCajaDetalle1($data)
    {
        $folio = $data['folio'];
        $detalles_productos = [];
        $detalles_cajas = [];
        $success = false;
        $error = false;

        $sqlProductos = "
            SELECT 
                SUM(p.Num_cantidad) AS total, 
                SUM(a.peso * p.Num_cantidad) AS peso, 
                TRUNCATE(SUM((a.alto/1000) * (a.ancho/1000) * (a.fondo/1000) * p.Num_cantidad), 4) AS volumen,
                MAX(a.alto / 1000) AS alto_maximo
            FROM td_pedido p 
                LEFT JOIN c_articulo a ON a.cve_articulo = p.Cve_articulo 
            WHERE Fol_folio = '{$folio}';
        ";
        $queryProductos = mysqli_query(\db2(), $sqlProductos);
        if($queryProductos->num_rows > 0)
        {
            $detalles_productos = mysqli_fetch_all($queryProductos, MYSQLI_ASSOC)[0];
        }

        $sqlCaja = "
            SELECT
                ca.descripcion, 
                CONCAT(TRUNCATE(ca.alto, 0), 'x', TRUNCATE(ca.ancho, 0), 'x', TRUNCATE(ca.largo, 0)) AS dimensiones,
                cm.NCaja AS cantidad,
                TRUNCATE(((ca.largo/1000) * (ca.alto/1000) * (ca.ancho/1000)), 4) AS volumen,
                ca.peso
            FROM `c_tipocaja` ca 
                LEFT JOIN `th_cajamixta` cm ON ca.id_tipocaja = cm.cve_tipocaja
            WHERE cm.fol_folio = '{$folio}' ORDER BY cantidad DESC;
        ";

        $caja = [];
        $volumen_requerido = $detalles_productos['volumen'] * 1.20;
        $alto_requerido = $detalles_productos['alto_maximo'];

        //No existe la caja la creamos
        $sql = "SELECT *, TRUNCATE((alto/1000)*(ancho/1000)*(largo/1000),8) AS dimensiones FROM `c_tipocaja` WHERE (alto/1000) > {$alto_requerido} AND Packing = 'S' HAVING dimensiones >= {$volumen_requerido} ORDER BY dimensiones ASC LIMIT 1";
        $query = mysqli_query(\db2(), $sql);
      
        /*  -------- DEBUG --------  */
      
        $sqlProductos_info = "SELECT * FROM td_pedido limit 5";
        $queryProductos_info = mysqli_query(\db2(), $sqlProductos_info);

        if($queryProductos_info->num_rows > 0)
        {
            $info["detalles_productos_info"] = mysqli_fetch_all($queryProductos_info, MYSQLI_ASSOC);
        }
        else
        {
          $info["detalles_productos_info"] = "No se encontraron resultados";
        }
      
        $info["data"] = $data;
        $info["detalles_productos"] = $detalles_productos;
        $info["volumen_requerido"] = $volumen_requerido;
        $info["alto_requerido"] = $alto_requerido;
        $querytest = mysqli_query(\db2(), "Select *, TRUNCATE((alto/1000)*(ancho/1000)*(largo/1000),8) AS dimensiones, (alto/1000) as alto_requerido from c_tipocaja WHERE Packing = 'S'");
        $info["cajas_numrows"] = $querytest->num_rows;
        $info["cajas_numrows_original"] = $query->num_rows;
        $cajatest = mysqli_fetch_all($querytest, MYSQLI_ASSOC);
        foreach($cajatest as $row)
        {
          $info["cajas"][] = $row;
        }

        /*  -------- END DEBUG --------  */
        
        if($query->num_rows > 0)
        {
            //obtenemos valores de la caja
            $caja = mysqli_fetch_all($query, MYSQLI_ASSOC)[0];
            $caja['necesarias'] = 1;
        }
        else 
        {
            //Buscamos otra caja que nos sirva
            $sql = "SELECT *, TRUNCATE((alto/1000)*(ancho/1000)*(largo/1000),8) AS dimensiones, CEIL({$volumen_requerido} / (SELECT dimensiones)) AS necesarias FROM `c_tipocaja` WHERE Packing = 'S' AND (alto/1000) > {$alto_requerido} ORDER BY dimensiones DESC LIMIT 1";
            $query = mysqli_query(\db2(), $sql);
            if($query->num_rows > 0)
            {
                //obtenemos valores de la caja
                $caja = mysqli_fetch_all($query, MYSQLI_ASSOC)[0];
            } 
            else 
            {
                //$error = "No hay cajas que soporten las dimensiones (alto) de los productos del pedido. No se pudo crear la caja de embarque";
            }
        }

        if(gettype($error) !== 'string')
        {
            $queryCaja = mysqli_query(\db2(), $sqlCaja);
            $algoritmo='Ya tiene cajas';
            //echo var_dump($resultDetalle);
            if($queryCaja->num_rows == 0)
            {
                $algoritmo = 'asignar cajas';
                $sqlBorrarCajas = "DELETE FROM th_cajamixta WHERE fol_folio = '{$folio}'";
                $queryBorrarCajas = mysqli_query(\db2(), $sqlBorrarCajas);
                //guardamos la caja
                for($i=1; $i <= $caja['necesarias']; $i++)
                {
                  $sqlGuardar = "INSERT INTO  th_cajamixta(Cve_CajaMix, fol_folio, Sufijo, NCaja, Activo, cve_tipocaja) VALUES ((SELECT MAX(cm2.Cve_CajaMix) + 1 FROM th_cajamixta cm2), '$folio', '1', '{$i}', '1', '{$caja['id_tipocaja']}') ON DUPLICATE KEY UPDATE NCaja = '{$i}', cve_tipocaja = '{$caja['id_tipocaja']}';";
                  $queryGuardar = mysqli_query(\db2(), $sqlGuardar);
                }

                if($queryGuardar)
                {
                    //Guardamos detalles de la caja
                    $sqlGuardarDetalle = "DELETE FROM td_cajamixta WHERE Cve_CajaMix IN (SELECT Cve_CajaMix FROM th_cajamixta WHERE fol_folio = '$folio'); INSERT INTO td_cajamixta(Cve_CajaMix, Cve_articulo, Cantidad, Cve_Lote, Activo) SELECT (SELECT Cve_CajaMix FROM th_cajamixta WHERE fol_folio = '$folio'), Cve_articulo, Num_cantidad, cve_lote, '1' FROM td_pedido WHERE Fol_folio = '$folio';";
                    $queryGuardarDetalle = mysqli_multi_query(\db2(), $sqlGuardarDetalle);

                    if($queryGuardarDetalle)
                    {
                        $result=mysqli_store_result(\db());
                        mysqli_next_result(\db2());
                        mysqli_free_result($result);
                        $queryCaja = mysqli_query(\db2(), $sqlCaja);
                    }
                }
            }
            $detalles_cajas = mysqli_fetch_all($queryCaja, MYSQLI_ASSOC)[0];
            $success = $queryProductos && $queryCaja;
        } 
        else 
        {
            //Borramos la caja guardada porque no cumple las condiciones
            $sqlDelete = "DELETE FROM td_cajamixta WHERE Cve_CajaMix = (SELECT Cve_CajaMix FROM th_cajamixta WHERE fol_folio = '$folio'); UPDATE th_cajamixta SET NCaja = 0, cve_tipocaja = NULL, TipoCaja = NULL WHERE fol_folio = '$folio';";
            $queryDelete = mysqli_multi_query(\db2(),$sqlDelete);
        }
      
        $detalles_productos["peso"] = round($detalles_productos["peso"],4);
      
        return array(
            "detalles_productos"  => $detalles_productos,
            "detalles_cajas"      => $detalles_cajas,
            "success"             => $success,
            "error"               => $error,
            "info"                => $info,
            "algoritmo"           => $algoritmo
        );
    }
  
    public function loadCajaDetalle($data)
    {
        /* Nuevo algoritmo */
        $folio = $data['folio'];
        $detalles_productos = [];
        $detalles_cajas = [];
        $success = false;
        $error = false;
        $libre = 1;//$data['libre'];
        $algoritmo = '';
        $debug = "";
        $tipoCaja = '';
    
        $sqlCajas_por_pedido = "
            SELECT *
            FROM `c_tipocaja` ca 
                LEFT JOIN `th_cajamixta` cm ON ca.id_tipocaja = cm.cve_tipocaja
            WHERE cm.fol_folio = '{$folio}';
        ";
        $queryCajas_por_pedido = mysqli_query(\db2(), $sqlCajas_por_pedido);
        $detalleCajasPedidoI = mysqli_fetch_all($queryCajas_por_pedido, MYSQLI_ASSOC);

        $sqlCajasTodas = "
          SELECT 
              *,
              GREATEST(largo,alto,ancho) as 'dMax',
              (largo*alto*ancho) as 'volumen'
          FROM `c_tipocaja` 
          WHERE `Activo` = 1 or `Packing` = 'S' 
          ORDER BY volumen DESC;
        ";
        $cajasTodas = mysqli_query(\db2(), $sqlCajasTodas);
        $resCajasTodas = mysqli_fetch_all($cajasTodas, MYSQLI_ASSOC);
      
        $sqlProductosPedido = "
            select 
                td_pedido.cve_articulo, 
                td_pedido.Num_cantidad,
                des_articulo, 
                alto, 
                ancho, 
                fondo, 
                GREATEST(fondo,alto,ancho) as 'dMax', 
                ((alto/1000)*(ancho/1000)*(fondo/1000)) as 'volumen', 
                if((alto*ancho*fondo) > (SELECT MAX(largo*alto*ancho) as 'volumen' FROM `c_tipocaja` WHERE `Activo` = 1 or `Packing` = 'S'),1,0) as 'caja' 
            FROM c_articulo 
                INNER JOIN td_pedido on td_pedido.Cve_articulo = c_articulo.cve_articulo
            WHERE td_pedido.Fol_folio = '{$folio}'
            ORDER BY dMax DESC;
        ";
        $productosPedido = mysqli_query(\db2(), $sqlProductosPedido);
        $resProductosPedidos = mysqli_fetch_all($productosPedido, MYSQLI_ASSOC);

        $debug["001_hay_productos"] = $resProductosPedidos->num_rows;

        if($resProductosPedidos->num_rows >= 0)
        {
            $volTotalPedido = 0;
            $totalGenerica = 0;
            $productosCaja=array();
            foreach($resProductosPedidos as $row)
            {
                if($row['caja'] == 0)
                {
                    $volTotalPedido+=($row["volumen"]*$row['Num_cantidad']);
                    array_push($productosCaja,$row);
                }
                else
                {
                    $totalGenerica+=$row["Num_cantidad"];
                }
            }
            $alto_requerido =  intval($productosCaja[0]['dMax']);
            $cajaPorVolumen='';
            $cajaPorDMax='';
            $volumen_requerido = $volTotalPedido*$libre; 
            if(count($productosCaja) > 0)
            {
                foreach($resCajasTodas as $key=>$row)
                {
                    if($volumen_requerido < intval($row['volumen']) and $alto_requerido < intval($row['dMax']))
                    {
                        $cajaPorVolumen=$key;
                    }
                    else if($alto_requerido < intval($row['dMax']))
                    {
                        $cajaPorDMax=$key;
                    }
                }
            }
            $totalCajas = 0;
            if($cajaPorVolumen == '' and $cajaPorDMax != '')
            {
                $totalCajas = $volTotalPedido/$resCajasTodas[$cajaPorDMax]['volumen'];
                $totalCajas = round($totalCajas, 0, PHP_ROUND_HALF_UP);
                $tipoCaja = $resCajasTodas[$cajaPorDMax]['id_tipocaja'];
            }
            else
            {
                $totalCajas = 1;
                $tipoCaja = $resCajasTodas[$cajaPorVolumen]['id_tipocaja'];
            }
            $debug["002_total_cajas"] = $totalCajas;
            /*
            //inserta cajas genericas
            $x=0;
            for($i=1;$i<=$totalGenerica;$i++){
              $sqlGuardar = "INSERT INTO  th_cajamixta(Cve_CajaMix, fol_folio, Sufijo, NCaja, Activo, cve_tipocaja) VALUES ((SELECT MAX(cm2.Cve_CajaMix) + 1 FROM th_cajamixta cm2), '$folio', '1', '{$i}', '1', '1') ON DUPLICATE KEY UPDATE NCaja = '{$i}', cve_tipocaja = '1';";
              $guardarCaja = mysqli_query(\db2(), $sqlGuardar);
              $x++;
            }
            //inserta cajas restantes
            $y=0;
            for($i=1;$i<=$totalCajas;$i++){
              $sqlGuardar = "INSERT INTO  th_cajamixta(Cve_CajaMix, fol_folio, Sufijo, NCaja, Activo, cve_tipocaja) VALUES ((SELECT MAX(cm2.Cve_CajaMix) + 1 FROM th_cajamixta cm2), '$folio', '1', '{$i}', '1', '{$tipoCaja}') ON DUPLICATE KEY UPDATE NCaja = '{$i}', cve_tipocaja = '{$tipoCaja}';";   
              $guardarCaja = mysqli_query(\db2(), $sqlGuardar);
              $y++;
            }
            $debug[4]['g']=$x;
            $debug[4]['n']=$y;
            */
        }
        else
        {
          $error = "Este pedido no cuenta con productos";
        }
      
        if($tipoCaja==""){$tipoCaja=1;}
        $sqlCajas_por_pedido2 = "
            SELECT
                ca.descripcion, 
                ca.clave,
                CONCAT(TRUNCATE(ca.alto, 0), 'x', TRUNCATE(ca.ancho, 0), 'x', TRUNCATE(ca.largo, 0)) AS dimensiones,
                TRUNCATE(((ca.largo/1000) * (ca.alto/1000) * (ca.ancho/1000)), 4) AS volumen,
                ifnull(ca.peso,'--') as peso
            FROM `c_tipocaja` ca 
            WHERE ca.id_tipocaja = '{$tipoCaja}';
        ";
        $sqlProductos = "
            SELECT 
                SUM(p.Num_cantidad) AS total, 
                SUM(a.peso * p.Num_cantidad) AS peso, 
                TRUNCATE(SUM((a.alto/1000) * (a.ancho/1000) * (a.fondo/1000) * p.Num_cantidad), 4) AS volumen,
                MAX(a.alto / 1000) AS alto_maximo
            FROM td_pedido p 
            LEFT JOIN c_articulo a ON a.cve_articulo = p.Cve_articulo 
            WHERE Fol_folio = '{$folio}';
        ";
        $queryProductos_info = mysqli_query(\db2(), $sqlProductos);
        $detalleProductosPedido = mysqli_fetch_all($queryProductos_info, MYSQLI_ASSOC);
        if(count($detalleProductosPedido) > 0)
        {
            $info["detalles_productos_info"] =$detalleProductosPedido[0];
        }
        else
        {
            $info["detalles_productos_info"] = "No se encontraron resultados";
        }
        if(!$error)
        {
            $success = true;
        }
        $queryCajas_por_pedido = mysqli_query(\db2(), $sqlCajas_por_pedido2);
        $detalles_cajas = mysqli_fetch_all($queryCajas_por_pedido, MYSQLI_ASSOC)[0];
        //$detalles_cajas = array();
        $detalles_cajas["cantidad"] = $totalCajas;
        /*$detalles_cajas["clave"] = "600";
        $detalles_cajas["descripcion"] = "600";
        $detalles_cajas["dimensiones"] = "260x260x110";
        $detalles_cajas["peso"] = "0";
        $detalles_cajas["volumen"] = "0.0074";*/

        $detalles_productos = $info["detalles_productos_info"];
        $detalles_productos["peso"] = round($detalles_productos["peso"],4);

        $info["data"] = $data;
        $info["detalles_productos"] = $detalles_productos;
        $info["volumen_requerido"] = $volumen_requerido;
        $info["alto_requerido"] = $alto_requerido;
        $debug=$tipoCaja;
     
        return array(
          "detalles_productos"  => $detalles_productos,
          "detalles_cajas"      => $detalles_cajas,
          "success"             => $success,
          "error"               => $error,
          "info"                => $info,
          "algoritmo"           => $algoritmo,
          "debug"               => $debug
        );
    }
  
    public function changeNumberBox($data)
    {
        $folio = $data['folio'];
        $numero = $data['numero'];
        $success = false;
        $debug ="";

        $sqlPaquete = "select status from th_pedido where Fol_folio = '{$folio}'";
        $queryCaja = mysqli_query(\db2(), $sqlPaquete);
        $status_pedido = mysqli_fetch_all($queryCaja, MYSQLI_ASSOC)[0];

        $debug = 'Estado:'.$status_pedido['status'];

        if($status_pedido['status'] == 'S' || $status_pedido['status'] == 'A' || $status_pedido['status'] == 'L' || $status_pedido['status'] == 'P')
        {
            $sqlProductos = "
                SELECT 
                    SUM(p.Num_cantidad) AS total
                FROM td_pedido p 
                    LEFT JOIN c_articulo a ON a.cve_articulo = p.Cve_articulo 
                WHERE Fol_folio = '{$folio}';
            ";
            $queryProductos = mysqli_query(\db2(), $sqlProductos);

            if($queryProductos->num_rows > 0)
            {
                $detalles_productos = mysqli_fetch_all($queryProductos, MYSQLI_ASSOC)[0];
                $total = $detalles_productos['total'];
            }

            $debug .='Total: '.$total.'Cajas: '.$numero;
        
            if($total >= $numero)
            {
                $sqlCaja = "SELECT ca.descripcion, cm.cve_tipocaja, CONCAT(TRUNCATE(ca.alto, 0), 'x', TRUNCATE(ca.ancho, 0), 'x', TRUNCATE(ca.largo, 0)) AS dimensiones, cm.NCaja AS cantidad, TRUNCATE(((ca.largo/1000) * (ca.alto/1000) * (ca.ancho/1000)), 4) AS volumen, ca.peso FROM `c_tipocaja` ca LEFT JOIN `th_cajamixta` cm ON ca.id_tipocaja = cm.cve_tipocaja WHERE cm.fol_folio = '{$folio}' ORDER BY cantidad DESC";
                $queryCaja = mysqli_query(\db2(), $sqlCaja);
                $detalles_cajas = mysqli_fetch_all($queryCaja, MYSQLI_ASSOC)[0];
                //echo var_dump($detalles_cajas);
                //die;
                if($queryCaja->num_rows != 0)
                {
                    if($detalles_cajas['cantidad'] < $numero)
                    {
                        for($i=$detalles_cajas['cantidad']+1; $i <= $numero; $i++)
                        {
                            $sqlGuardar = "INSERT INTO  th_cajamixta(Cve_CajaMix, fol_folio, Sufijo, NCaja, Activo, cve_tipocaja) VALUES ((SELECT MAX(cm2.Cve_CajaMix) + 1 FROM th_cajamixta cm2), '$folio', '1', '{$i}', '1', '{$detalles_cajas['cve_tipocaja']}') ON DUPLICATE KEY UPDATE NCaja = '{$i}', cve_tipocaja = '{$detalles_cajas['cve_tipocaja']}';";
                            $queryGuardar = mysqli_query(\db2(), $sqlGuardar);
                        }
                        $success = true;
                    }
                    else if($detalles_cajas['cantidad'] > $numero)
                    {
                        //Borrar cajas
                        $sqlBorrarCajas = "DELETE FROM th_cajamixta WHERE fol_folio = '{$folio}' and NCaja > {$numero}";
                        $queryGuardar = mysqli_query(\db2(), $sqlBorrarCajas);
                        $success = true;
                    }
                    else if($detalles_cajas['cantidad'] == $numero)
                    {
                        $success = true;
                    }
                }
                else
                {
                    for($i=$detalles_cajas['cantidad']+1; $i <= $numero; $i++)
                    {
                        $sqlGuardar = "INSERT INTO  th_cajamixta(Cve_CajaMix, fol_folio, Sufijo, NCaja, Activo, cve_tipocaja) VALUES ((SELECT MAX(cm2.Cve_CajaMix) + 1 FROM th_cajamixta cm2), '$folio', '1', '{$i}', '1', '1') ON DUPLICATE KEY UPDATE NCaja = '{$i}', cve_tipocaja = '1';";
                        $queryGuardar = mysqli_query(\db2(), $sqlGuardar);
                    }
                    $success = true;
                }
            }
            else
            {
                $success = false;
                $error = "El número de cajas revasa el total de productos del pedido";
            }
        }
        else
        {
            $success = false;
            $error = "No es pocible modificar en este estado de pedido";
        } 
        return array(
            "success" => $success,
            "error"   => $error,
            "debug"   => $debug
        );
    }

    public function cambiarStatus($data)
    {
      extract($data);
      $sql = "UPDATE th_pedido SET status = '$status' WHERE Fol_folio = '$folio';";
      $query = mysqli_query(\db2(), $sql);
      return $query;
    }

    private function loadDetalle() 
    {
        $sql = "
            SELECT
                td_pedido.Fol_folio,
                td_pedido.Cve_articulo,
                td_pedido.Num_cantidad,
                c_articulo.des_articulo
            FROM td_pedido
            INNER JOIN c_articulo ON td_pedido.Cve_articulo = c_articulo.cve_articulo 
            WHERE td_pedido.Fol_folio = '".$this->Fol_folio."';
        ";
        $rs = mysqli_query(\db2(), $sql) or die("Error description: " . mysqli_error(\db2()));
        $arr = array();
        while ($row = mysqli_fetch_array($rs)) 
        {
            $arr[] = $row;
        }
        $this->dataDetalle = $arr;
    }

    function __get( $key ) 
    {
        switch($key) 
        {
            case 'Fol_folio':
                $this->load();
                return @$this->data->$key;
            default:
                return $this->key;
        }
    }

    function __getDetalle( $key ) 
    {
        switch($key) 
        {
            case 'Fol_folio':
                $this->loadDetalle();
                return @$this->dataDetalle->$key;
            default:
                return $this->key;
        }
    }

    function save( $_post ) 
    {
		    try {
			      $sql = "INSERT INTO " . self::TABLE . " (Fol_folio, Cve_clte, status, cve_Vendedor, Fec_Entrada, Pick_Num, Cve_Usuario, Observaciones, cve_almac, Activo)";
            $sql .= "Values (";
            $sql .= "'".$_post['Fol_folio']."',";
            $sql .= "'".$_post['Cve_clte']."',";
            $sql .= "'B',";
            $sql .= "'".$_post['cve_Vendedor']."',";
            $sql .= "now(),";
            $sql .= "'".$_post['Pick_Num']."',";
            $sql .= "'".$_post['user']."',";
            $sql .= "'".$_post['Observaciones']."',";
            $sql .= "'".$_post['cve_almac']."', '1');";

            $rs = mysqli_query(\db2(), $sql) or die("Error description: " . mysqli_error(\db2()));

            if (!empty($_post["arrDetalle"])) 
            {
                $sql = "Delete From td_pedido WHERE Fol_folio = '".$_post['Fol_folio']."'";
                $rs = mysqli_query(\db2(), $sql) or die("Error description: " . mysqli_error(\db2()));

                foreach ($_post["arrDetalle"] as $item) 
                {
                    $sql = "INSERT INTO td_pedido (Cve_articulo, Num_cantidad, Fol_folio, Activo) Values ";
                    $sql .= "('".$item['codigo']."', '".$item['CantPiezas']."', '".$_post['Fol_folio']."', '1');";
                    $rs = mysqli_query(\db2(), $sql) or die("Error description: " . mysqli_error(\db2()));
                }
            }
		    } 
        catch(Exception $e) 
        {
            return 'ERROR: ' . $e->getMessage();
        }
    }

    function borrarPedido( $data ) 
    {
        $sql = ' UPDATE ' . self::TABLE . ' SET Activo = 0 WHERE Fol_folio = ? ';
        $this->save = \db()->prepare($sql);
        $this->save->execute( array(
            $data['Fol_folio']
        ) );
    }

    function actualizarPedido( $_post ) 
    {
        try {
            $sql = "Delete From " . self::TABLE . " WHERE Fol_folio = '".$_post['Fol_folio']."'";
            $rs = mysqli_query(\db2(), $sql) or die("Error description: " . mysqli_error(\db2()));

            $sql = "INSERT INTO " . self::TABLE . " (Fol_folio, Cve_clte, cve_Vendedor, Fec_Entrada, Pick_Num, Observaciones, cve_almac, Activo)";
            $sql .= "Values (";
            $sql .= "'".$_post['Fol_folio']."',";
            $sql .= "'".$_post['Cve_clte']."',";
            $sql .= "'".$_post['cve_Vendedor']."',";
            $sql .= "now(),";
            $sql .= "'".$_post['Pick_Num']."',";
            $sql .= "'".$_post['Observaciones']."',";
            $sql .= "'".$_post['cve_almac']."', '1');";

            $rs = mysqli_query(\db2(), $sql) or die("Error description: " . mysqli_error(\db2()));

            if (!empty($_post["arrDetalle"])) 
            {
                $sql = "Delete From td_pedido WHERE Fol_folio = '".$_post['Fol_folio']."'";
                $rs = mysqli_query(\db2(), $sql) or die("Error description: " . mysqli_error(\db2()));

                foreach ($_post["arrDetalle"] as $item) 
                {
                    $sql = "INSERT INTO td_pedido (Cve_articulo, Num_cantidad, Fol_folio, Activo) Values ";
                    $sql .= "('".$item['codigo']."', '".$item['CantPiezas']."', '".$_post['Fol_folio']."', '1');";
                    $rs = mysqli_query(\db2(), $sql) or die("Error description: " . mysqli_error(\db2()));
                }
            }
        } 
        catch(Exception $e) 
        {
            return 'ERROR: ' . $e->getMessage();
        }
    }
	
  	function loadArticulosWave($folios) 
    {
	      $sql="
            SELECT 
                td_pedido.Cve_articulo AS clave,
                c_articulo.des_articulo AS articulo,
                td_pedido.Num_cantidad AS pedidas,
                td_pedido.SurtidoXPiezas AS surtidas
			      FROM td_pedido, th_pedido, c_articulo
			      WHERE td_pedido.Fol_folio=th_pedido.Fol_folio 
            AND c_articulo.cve_articulo=td_pedido.Cve_articulo 
            AND th_pedido.Fol_folio IN (".$folios.");
        ";
	      $sth = \db()->prepare( $sql );
        $sth->execute();
        //$this->data = $sth->fetch();
		    return $sth->fetchAll();
	  }
    
    /**
     * Undocumented function
     *
     * @param [type] $folio
     * @return void
     */
    function getPesoPedido($folio)
    {
        $sql="
            SELECT 
                COALESCE(
                    SUM(COALESCE(A.peso,0) * COALESCE(S.Cantidad,0)) +
                    SUM(COALESCE(A.peso,0) * COALESCE(R.Cantidad,0))
                ,0) peso
            FROM	TD_Pedido P 
                INNER JOIN TH_SubPedido H On P.Fol_Folio = H.Fol_Folio
                INNER JOIN C_Articulo A On P.Cve_Articulo = A.Cve_Articulo
                LEFT OUTER JOIN TD_SurtidoPiezas S ON S.cve_articulo = P.Cve_articulo AND S.Fol_Folio = P.Fol_Folio
                LEFT OUTER JOIN T_Recorrido_Surtido R ON R.Cve_Articulo = P.Cve_Articulo And R.Fol_Folio = P.Fol_Folio
            WHERE H.Placas_T = '{$folio}' AND H.HIE = NOW();
        ";	

        $sth = \db()->prepare( $sql );      
        $sth->execute();
        $this->data = $sth->fetch();
        $peso = $sth->fetch()[0];
        if( ! is_array($peso) or count($peso) < 1 ) $peso = 0;
        return $peso;
	}
	
	function loadNumeroOla() 
  {
	    $sql="
          SELECT AUTO_INCREMENT as id
			    FROM information_schema.tables
			    WHERE table_name = 'th_consolidado'
			    AND table_schema = DATABASE();
      ";
      $sth = \db()->prepare( $sql );
      $sth->execute();
  		return $sth->fetch();
	}
    
  /**
   * Undocumented function
   *
   * @param [type] $id
   * @return void
   */
  
  //EDG117
  function verificarStatus($folio, $is_backorder, $sufijo)
  {
    if($is_backorder)
        $sql_status = "SELECT Status as status FROM `th_backorder` WHERE `Fol_Folio` = '{$folio}';";
    else
    {
        if($sufijo == 0)
           $sql_status = "SELECT status FROM `th_pedido` WHERE `Fol_folio` = '{$folio}';";
       else
           $sql_status = "SELECT status FROM th_subpedido WHERE fol_folio = '{$folio}' AND Sufijo = '{$sufijo}';";
    }
    $sth = \db()->prepare($sql_status);
    $sth->execute();
    $status = $sth->fetch();
    return $status;
  }
  
	function loadArticulos($id,$almacen,$status_pedido, $sufijo, $is_backorder, $folio_rel, $con_recorrido) 
  {
    /* - Se debe dividir para cuando el pedido ya está asignado o está pendiente por asignar - */
    //$res = array();
    $listoPorAsignar = false;
    $surtiendo = false;
    $status = $status_pedido;
    if($status == 'A' || $status == 'O'){$listoPorAsignar = true;}
    if($status == 'S'){$surtiendo = true;}
    if($status == 'C'){$surtiendo = true;}
    if($status == 'E'){$surtiendo = true;}
    if($status == 'F'){$surtiendo = true;}
    if($status == 'I'){$surtiendo = true;}
    if($status == 'K'){$surtiendo = true;}
    if($status == 'L'){$surtiendo = true;}
    if($status == 'M'){$surtiendo = true;}
    if($status == 'P'){$surtiendo = true;}
    if($status == 'R'){$surtiendo = true;}
    if($status == 'T'){$surtiendo = true;}

    if($listoPorAsignar)
    {
        $sql = "";
            if($status == 'A')
            {
                if($is_backorder == 0 && $sufijo == 0)
                {
                    if($folio_rel)
                    {
                        $sql = "
                                SELECT 
                                  tsb.Fol_folio AS folio,
                                  '' AS sufijo,
                                  c.RazonSocial AS cliente,
                                  tsb.Cve_articulo AS clave,
                                  a.des_articulo AS articulo,
                                  '' AS surtidor,
                                  '' AS ubicacion,
                                  '' AS idy_ubica,
                                  '' AS secuencia,
                                  '' AS ruta,
                                    0 AS Pallet,
                                    0 AS Caja, 
                                    0 AS Piezas,
                                    IFNULL(a.cajas_palet, 0) cajasxpallets,
                                    IFNULL(a.num_multiplo, 0) piezasxcajas, 
                                  IF(a.control_lotes = 'S', tsb.cve_lote, '') AS lote,
                                  IF(a.control_lotes = 'S' AND (a.Caduca = 'S' OR (th_pedido.Ship_Num != '' AND IFNULL(a.Caduca, 'N') = 'N')), DATE_FORMAT(lo.Caducidad, '%d-%m-%Y'), '') AS caducidad,
                                  IF(a.control_numero_series = 'S', tsb.cve_lote, '') AS serie,
                                  #SUM(ROUND((a.peso * tsb.Num_cantidad),4)) AS peso,
                                  #IF(op.id_umed = a.unidadMedida OR IFNULL(op.id_umed, 0) = 0, SUM(ROUND((a.peso * tsb.Num_cantidad),4)), tsb.Num_cantidad) AS peso,
                                  IF((op.id_umed = a.unidadMedida OR IFNULL(op.id_umed, 0) = 0), IF(((SELECT DISTINCT Fol_Folio FROM td_consolidado WHERE Fol_PedidoCon = tsb.fol_folio LIMIT 1) LIKE 'OT%'), tsb.Num_cantidad, SUM(ROUND((a.peso * tsb.Num_cantidad),4))), tsb.Num_cantidad) AS peso,
                                  SUM(ROUND(((a.alto/1000) * (a.ancho/1000) * (a.fondo/1000) * tsb.Num_cantidad),4)) AS volumen,
                                  #SUM(tsb.Num_cantidad) AS pedidas,
                                  tsb.Num_cantidad AS pedidas,
                                  #IFNULL(s.Cantidad, 0) AS surtidas,
                                  tsb.Num_cantidad AS surtidas,
                                  IFNULL(umed2.mav_cveunimed, umed.mav_cveunimed) AS id_medida,
                                  IFNULL(umed2.des_umed, umed.des_umed) AS unidad_medida,
                                  #tsb.Num_cantidad AS existencia
                                  #IF(COALESCE((SELECT SUM(Existencia) AS suma FROM VS_ExistenciaParaSurtido V_ExistenciaGral WHERE V_ExistenciaGral.cve_articulo = tsb.Cve_Articulo AND V_ExistenciaGral.cve_almac = '{$almacen}' AND V_ExistenciaGral.cve_lote = (SELECT Lote FROM c_lotes WHERE cve_articulo = V_ExistenciaGral.cve_articulo AND Caducidad > CURDATE())), 0) >= tsb.Num_cantidad, tsb.Num_cantidad, COALESCE((SELECT SUM(Existencia) AS suma FROM VS_ExistenciaParaSurtido V_ExistenciaGral WHERE V_ExistenciaGral.cve_articulo = tsb.Cve_Articulo AND V_ExistenciaGral.cve_almac = '{$almacen}' AND V_ExistenciaGral.cve_lote = (SELECT Lote FROM c_lotes WHERE cve_articulo = V_ExistenciaGral.cve_articulo AND Caducidad > CURDATE())), 0)) AS existencia

                    IF(IFNULL(tsb.cve_lote, '') = '',
                                IF(COALESCE((SELECT SUM(Existencia) AS suma 
                    FROM V_ExistenciaGralProduccion V_ExistenciaGral 
                    LEFT JOIN c_ubicacion u ON u.idy_ubica = V_ExistenciaGral.cve_ubicacion
                    WHERE V_ExistenciaGral.cve_articulo = tsb.Cve_Articulo AND V_ExistenciaGral.cve_almac = '{$almacen}' 
                    AND u.picking = 'S'
                    #AND V_ExistenciaGral.cve_lote = IFNULL(tsb.cve_lote, '') 
                    AND ((V_ExistenciaGral.cve_lote IN (SELECT Lote FROM c_lotes WHERE cve_articulo = V_ExistenciaGral.cve_articulo AND Caducidad > CURDATE())) OR (V_ExistenciaGral.cve_articulo = a.cve_articulo AND IFNULL(a.Caduca, 'N') = 'N'))), 0) >= tsb.Num_cantidad, tsb.Num_cantidad, COALESCE((SELECT SUM(Existencia) AS suma FROM VS_ExistenciaParaSurtido V_ExistenciaGral LEFT JOIN c_ubicacion u ON u.idy_ubica = V_ExistenciaGral.idy_ubica WHERE V_ExistenciaGral.cve_articulo = tsb.Cve_Articulo 
                    AND u.picking = 'S'
                    #AND V_ExistenciaGral.cve_lote = IFNULL(tsb.cve_lote, '') 
                    AND V_ExistenciaGral.cve_almac = '{$almacen}' AND ((V_ExistenciaGral.cve_lote IN (SELECT Lote FROM c_lotes WHERE cve_articulo = V_ExistenciaGral.cve_articulo AND Caducidad > CURDATE())) OR (V_ExistenciaGral.cve_articulo = a.cve_articulo AND IFNULL(a.Caduca, 'N') = 'N'))), 0)) 
                    ,
                                IF(COALESCE((SELECT SUM(Existencia) AS suma 
                    FROM V_ExistenciaGralProduccion V_ExistenciaGral 
                    LEFT JOIN c_ubicacion u ON u.idy_ubica = V_ExistenciaGral.cve_ubicacion
                    WHERE V_ExistenciaGral.cve_articulo = tsb.Cve_Articulo AND V_ExistenciaGral.cve_almac = '{$almacen}' 
                    AND u.picking = 'S'
                    AND V_ExistenciaGral.cve_lote = IFNULL(tsb.cve_lote, '') 
                    AND ((V_ExistenciaGral.cve_lote IN (SELECT Lote FROM c_lotes WHERE cve_articulo = V_ExistenciaGral.cve_articulo AND Caducidad > CURDATE())) OR (V_ExistenciaGral.cve_articulo = a.cve_articulo AND IFNULL(a.Caduca, 'N') = 'N'))), 0) >= tsb.Num_cantidad, tsb.Num_cantidad, COALESCE((SELECT SUM(Existencia) AS suma FROM VS_ExistenciaParaSurtido V_ExistenciaGral LEFT JOIN c_ubicacion u ON u.idy_ubica = V_ExistenciaGral.idy_ubica WHERE V_ExistenciaGral.cve_articulo = tsb.Cve_Articulo 
                    AND u.picking = 'S'
                    AND V_ExistenciaGral.cve_lote = IFNULL(tsb.cve_lote, '') 
                    AND V_ExistenciaGral.cve_almac = '{$almacen}' AND ((V_ExistenciaGral.cve_lote IN (SELECT Lote FROM c_lotes WHERE cve_articulo = V_ExistenciaGral.cve_articulo AND Caducidad > CURDATE())) OR (V_ExistenciaGral.cve_articulo = a.cve_articulo AND IFNULL(a.Caduca, 'N') = 'N'))), 0))                     ) AS existencia

                                #IF(a.control_peso = 'S', 
            #IF(COALESCE((SELECT SUM(Existencia*a.num_multiplo) AS suma 
                    #FROM V_ExistenciaGralProduccion V_ExistenciaGral 
                    #WHERE V_ExistenciaGral.cve_articulo = tsb.Cve_Articulo AND V_ExistenciaGral.cve_almac = '{$almacen}' 
                    #AND ((V_ExistenciaGral.cve_lote IN (SELECT Lote FROM c_lotes WHERE cve_articulo = V_ExistenciaGral.cve_articulo AND Caducidad > CURDATE())) OR (V_ExistenciaGral.cve_articulo = a.cve_articulo AND IFNULL(a.Caduca, 'N') = 'N'))), 0) >= (tsb.Num_cantidad*a.num_multiplo), (tsb.Num_cantidad*a.num_multiplo), COALESCE((SELECT SUM(Existencia*a.num_multiplo) AS suma FROM VS_ExistenciaParaSurtido V_ExistenciaGral WHERE V_ExistenciaGral.cve_articulo = tsb.Cve_Articulo AND V_ExistenciaGral.cve_almac = '{$almacen}' AND ((V_ExistenciaGral.cve_lote IN (SELECT Lote FROM c_lotes WHERE cve_articulo = V_ExistenciaGral.cve_articulo AND Caducidad > CURDATE())) OR (V_ExistenciaGral.cve_articulo = a.cve_articulo AND IFNULL(a.Caduca, 'N') = 'N'))), 0))
                                #,IF(COALESCE((SELECT SUM(Existencia) AS suma 
                    #FROM V_ExistenciaGralProduccion V_ExistenciaGral 
                    #WHERE V_ExistenciaGral.cve_articulo = tsb.Cve_Articulo AND V_ExistenciaGral.cve_almac = '{$almacen}' 
                    #AND ((V_ExistenciaGral.cve_lote IN (SELECT Lote FROM c_lotes WHERE cve_articulo = V_ExistenciaGral.cve_articulo AND Caducidad > CURDATE())) OR (V_ExistenciaGral.cve_articulo = a.cve_articulo AND IFNULL(a.Caduca, 'N') = 'N'))), 0) >= tsb.Num_cantidad, tsb.Num_cantidad, COALESCE((SELECT SUM(Existencia) AS suma FROM VS_ExistenciaParaSurtido V_ExistenciaGral WHERE V_ExistenciaGral.cve_articulo = tsb.Cve_Articulo AND V_ExistenciaGral.cve_almac = '{$almacen}' AND ((V_ExistenciaGral.cve_lote IN (SELECT Lote FROM c_lotes WHERE cve_articulo = V_ExistenciaGral.cve_articulo AND Caducidad > CURDATE())) OR (V_ExistenciaGral.cve_articulo = a.cve_articulo AND IFNULL(a.Caduca, 'N') = 'N'))), 0))) AS existencia


                            FROM td_pedido tsb
                              LEFT JOIN c_articulo a ON a.cve_articulo = tsb.Cve_articulo
                              LEFT JOIN c_unimed umed ON umed.id_umed = a.unidadMedida
                              LEFT JOIN c_unimed umed2 ON umed2.id_umed = tsb.id_unimed
                              LEFT JOIN th_pedido ON th_pedido.Fol_folio = tsb.Fol_folio
                              LEFT JOIN c_cliente c ON c.Cve_Clte = th_pedido.Cve_clte
                              LEFT JOIN c_lotes lo ON lo.cve_articulo = a.cve_articulo AND lo.Lote = tsb.cve_lote
                              LEFT JOIN c_serie se ON se.cve_articulo = a.cve_articulo AND se.numero_serie = tsb.cve_lote
                              #LEFT JOIN td_surtidopiezas s ON s.fol_folio = th_pedido.Fol_Folio AND s.Cve_articulo = tsb.Cve_Articulo 
                              #LEFT JOIN t_recorrido_surtido t ON t.fol_folio = th_pedido.Fol_folio AND t.Cve_articulo = tsb.Cve_articulo 
                              LEFT JOIN t_ordenprod op ON op.Folio_Pro = tsb.fol_folio
                            WHERE tsb.Fol_folio = '{$id}' 
                            AND th_pedido.cve_almac = '{$almacen}' 
                            GROUP BY clave, lote, serie 
                        ";
                }
                else
                {
                        $sql = "
                                SELECT 
                                  tsb.Fol_folio AS folio,
                                  '' AS sufijo,
                                  c.RazonSocial AS cliente,
                                  tsb.Cve_articulo AS clave,
                                  a.des_articulo AS articulo,
                                  '' AS surtidor,
                                  '' AS ubicacion,
                                  '' AS idy_ubica,
                                  '' AS secuencia,
                                  '' AS ruta,
                                    0 AS Pallet,
                                    0 AS Caja, 
                                    0 AS Piezas,
                                    IFNULL(a.cajas_palet, 0) cajasxpallets,
                                    IFNULL(a.num_multiplo, 0) piezasxcajas, 
                                  IF(a.control_lotes = 'S', tsb.cve_lote, '') AS lote,
                                  IF(a.control_lotes = 'S' AND a.Caduca = 'S', DATE_FORMAT(lo.Caducidad, '%d-%m-%Y'), '') AS caducidad,
                                  IF(a.control_numero_series = 'S', tsb.cve_lote, '') AS serie,
                                  #SUM(ROUND((a.peso * tsb.Num_cantidad),4)) AS peso,
                                  #IF(op.id_umed = a.unidadMedida OR IFNULL(op.id_umed, 0) = 0, SUM(ROUND((a.peso * tsb.Num_cantidad),4)), tsb.Num_cantidad) AS peso,
                                  IF((op.id_umed = a.unidadMedida OR IFNULL(op.id_umed, 0) = 0), IF(((SELECT DISTINCT Fol_Folio FROM td_consolidado WHERE Fol_PedidoCon = tsb.fol_folio LIMIT 1) LIKE 'OT%'), tsb.Num_cantidad*(IF(IFNULL(umed2.mav_cveunimed, umed.mav_cveunimed) = 'XBX', a.num_multiplo, 1)), SUM(ROUND((a.peso * tsb.Num_cantidad*(IF(IFNULL(umed2.mav_cveunimed, umed.mav_cveunimed) = 'XBX', a.num_multiplo, 1))),4))), tsb.Num_cantidad*(IF(IFNULL(umed2.mav_cveunimed, umed.mav_cveunimed) = 'XBX', a.num_multiplo, 1))) AS peso,
                                  SUM(ROUND(((a.alto/1000) * (a.ancho/1000) * (a.fondo/1000) * tsb.Num_cantidad*(IF(IFNULL(umed2.mav_cveunimed, umed.mav_cveunimed) = 'XBX', a.num_multiplo, 1))),4)) AS volumen,
                                  #SUM(tsb.Num_cantidad) AS pedidas,
                                  (tsb.Num_cantidad*(IF(IFNULL(umed2.mav_cveunimed, umed.mav_cveunimed) = 'XBX', a.num_multiplo, 1)) - IF(pr.Tipmed = 'Caja', IFNULL(pr.Cant*a.num_multiplo, 0), IFNULL(pr.Cant, 0))) AS pedidas,
                                  #IFNULL(s.Cantidad, 0) AS surtidas,
                                  (tsb.Num_cantidad*(IF(IFNULL(umed2.mav_cveunimed, umed.mav_cveunimed) = 'XBX', a.num_multiplo, 1)) - IF(pr.Tipmed = 'Caja', IFNULL(pr.Cant*a.num_multiplo, 0), IFNULL(pr.Cant, 0))) AS surtidas,
                                  IFNULL(umed2.mav_cveunimed, umed.mav_cveunimed) AS id_medida,
                                  IFNULL(umed2.des_umed, umed.des_umed) AS unidad_medida,
                                  #tsb.Num_cantidad AS existencia
                                  #IF(COALESCE((SELECT SUM(Existencia) AS suma FROM VS_ExistenciaParaSurtido V_ExistenciaGral WHERE V_ExistenciaGral.cve_articulo = tsb.Cve_Articulo AND V_ExistenciaGral.cve_almac = '{$almacen}' AND V_ExistenciaGral.cve_lote = (SELECT Lote FROM c_lotes WHERE cve_articulo = V_ExistenciaGral.cve_articulo AND Caducidad > CURDATE())), 0) >= tsb.Num_cantidad, tsb.Num_cantidad, COALESCE((SELECT SUM(Existencia) AS suma FROM VS_ExistenciaParaSurtido V_ExistenciaGral WHERE V_ExistenciaGral.cve_articulo = tsb.Cve_Articulo AND V_ExistenciaGral.cve_almac = '{$almacen}' AND V_ExistenciaGral.cve_lote = (SELECT Lote FROM c_lotes WHERE cve_articulo = V_ExistenciaGral.cve_articulo AND Caducidad > CURDATE())), 0)) AS existencia

                        IF(IFNULL(tsb.cve_lote, '') = '',
                                IF(COALESCE((SELECT SUM(Existencia) AS suma 
                    FROM VS_ExistenciaParaSurtido V_ExistenciaGral 
                    LEFT JOIN c_ubicacion u ON u.idy_ubica = V_ExistenciaGral.idy_ubica
                    WHERE V_ExistenciaGral.cve_articulo = tsb.Cve_Articulo #AND V_ExistenciaGral.cve_lote = IFNULL(tsb.cve_lote, '') 
                    AND u.picking = 'S'
                    AND V_ExistenciaGral.cve_almac = '{$almacen}' 
                    AND ((V_ExistenciaGral.cve_lote IN (SELECT Lote FROM c_lotes WHERE cve_articulo = V_ExistenciaGral.cve_articulo AND Caducidad > CURDATE())) OR (V_ExistenciaGral.cve_articulo = a.cve_articulo AND IFNULL(a.Caduca, 'N') = 'N'))), 0) >= (tsb.Num_cantidad*(IF(IFNULL(umed2.mav_cveunimed, umed.mav_cveunimed) = 'XBX', a.num_multiplo, 1)) - IF(pr.Tipmed = 'Caja', IFNULL(pr.Cant*a.num_multiplo, 0), IFNULL(pr.Cant, 0))), (tsb.Num_cantidad*(IF(IFNULL(umed2.mav_cveunimed, umed.mav_cveunimed) = 'XBX', a.num_multiplo, 1)) - IF(pr.Tipmed = 'Caja', IFNULL(pr.Cant*a.num_multiplo, 0), IFNULL(pr.Cant, 0))), COALESCE((SELECT SUM(Existencia) AS suma FROM VS_ExistenciaParaSurtido V_ExistenciaGral LEFT JOIN c_ubicacion u ON u.idy_ubica = V_ExistenciaGral.idy_ubica WHERE V_ExistenciaGral.cve_articulo = tsb.Cve_Articulo 
                        #AND V_ExistenciaGral.cve_lote = IFNULL(tsb.cve_lote, '') 
                        AND u.picking = 'S'
                        AND V_ExistenciaGral.cve_almac = '{$almacen}' AND ((V_ExistenciaGral.cve_lote IN (SELECT Lote FROM c_lotes WHERE cve_articulo = V_ExistenciaGral.cve_articulo AND Caducidad > CURDATE())) OR (V_ExistenciaGral.cve_articulo = a.cve_articulo AND IFNULL(a.Caduca, 'N') = 'N'))), 0))
                        , 
                        IF(COALESCE((SELECT SUM(Existencia) AS suma 
                    FROM VS_ExistenciaParaSurtido V_ExistenciaGral 
                    LEFT JOIN c_ubicacion u ON u.idy_ubica = V_ExistenciaGral.idy_ubica
                    WHERE V_ExistenciaGral.cve_articulo = tsb.Cve_Articulo AND V_ExistenciaGral.cve_lote = IFNULL(tsb.cve_lote, '') 
                    AND V_ExistenciaGral.cve_almac = '{$almacen}' 
                    AND u.picking = 'S'
                    AND ((V_ExistenciaGral.cve_lote IN (SELECT Lote FROM c_lotes WHERE cve_articulo = V_ExistenciaGral.cve_articulo AND Caducidad > CURDATE())) OR (V_ExistenciaGral.cve_articulo = a.cve_articulo AND IFNULL(a.Caduca, 'N') = 'N'))), 0) >= (tsb.Num_cantidad*(IF(IFNULL(umed2.mav_cveunimed, umed.mav_cveunimed) = 'XBX', a.num_multiplo, 1)) - IF(pr.Tipmed = 'Caja', IFNULL(pr.Cant*a.num_multiplo, 0), IFNULL(pr.Cant, 0))), (tsb.Num_cantidad*(IF(IFNULL(umed2.mav_cveunimed, umed.mav_cveunimed) = 'XBX', a.num_multiplo, 1)) - IF(pr.Tipmed = 'Caja', IFNULL(pr.Cant*a.num_multiplo, 0), IFNULL(pr.Cant, 0))), COALESCE((SELECT SUM(Existencia) AS suma FROM VS_ExistenciaParaSurtido V_ExistenciaGral LEFT JOIN c_ubicacion u ON u.idy_ubica = V_ExistenciaGral.idy_ubica WHERE V_ExistenciaGral.cve_articulo = tsb.Cve_Articulo 
                        AND u.picking = 'S'
                        AND V_ExistenciaGral.cve_lote = IFNULL(tsb.cve_lote, '') 
                        AND V_ExistenciaGral.cve_almac = '{$almacen}' AND ((V_ExistenciaGral.cve_lote IN (SELECT Lote FROM c_lotes WHERE cve_articulo = V_ExistenciaGral.cve_articulo AND Caducidad > CURDATE())) OR (V_ExistenciaGral.cve_articulo = a.cve_articulo AND IFNULL(a.Caduca, 'N') = 'N'))), 0))
                        ) AS existencia

                            FROM td_pedido tsb
                              LEFT JOIN c_articulo a ON a.cve_articulo = tsb.Cve_articulo
                              LEFT JOIN c_unimed umed ON umed.id_umed = a.unidadMedida
                              LEFT JOIN c_unimed umed2 ON umed2.id_umed = tsb.id_unimed
                              LEFT JOIN th_pedido ON th_pedido.Fol_folio = tsb.Fol_folio
                              LEFT JOIN c_cliente c ON c.Cve_Clte = th_pedido.Cve_clte
                              LEFT JOIN c_lotes lo ON lo.cve_articulo = a.cve_articulo AND lo.Lote = tsb.cve_lote
                              LEFT JOIN c_serie se ON se.cve_articulo = a.cve_articulo AND se.numero_serie = tsb.cve_lote
                              LEFT JOIN PRegalado pr ON pr.Docto = tsb.Fol_folio AND pr.SKU = tsb.Cve_articulo
                              #LEFT JOIN td_surtidopiezas s ON s.fol_folio = th_pedido.Fol_Folio AND s.Cve_articulo = tsb.Cve_Articulo 
                              #LEFT JOIN t_recorrido_surtido t ON t.fol_folio = th_pedido.Fol_folio AND t.Cve_articulo = tsb.Cve_articulo 
                              LEFT JOIN t_ordenprod op ON op.Folio_Pro = tsb.fol_folio
                            WHERE tsb.Fol_folio = '{$id}' 
                            AND th_pedido.cve_almac = '{$almacen}' AND (tsb.Num_cantidad*(IF(IFNULL(umed2.mav_cveunimed, umed.mav_cveunimed) = 'XBX', a.num_multiplo, 1)) - IF(pr.Tipmed = 'Caja', IFNULL(pr.Cant*a.num_multiplo, 0), IFNULL(pr.Cant, 0))) > 0
                            GROUP BY clave, lote, serie 
                        ";
                }
              }
              else if($is_backorder == 0 && $sufijo > 0)
              {
              $sql = "
                    SELECT 
                          tsb.Fol_folio AS folio,
                          tsb.Sufijo AS sufijo,
                          c.RazonSocial AS cliente,
                          tsb.Cve_articulo AS clave,
                          a.des_articulo AS articulo,
                          t.cve_usuario AS surtidor,
                          t.claverp AS ubicacion,
                          t.idy_ubica AS idy_ubica,
                          t.orden_secuencia AS secuencia,
                                    0 AS Pallet,
                                    0 AS Caja, 
                                    0 AS Piezas,
                                    IFNULL(a.cajas_palet, 0) cajasxpallets,
                                    IFNULL(a.num_multiplo, 0) piezasxcajas, 
                          (SELECT Descripcion FROM V_RutaSurtido_Usuario WHERE orden_secuencia = t.orden_secuencia AND cve_usuario = t.cve_usuario AND idy_ubica = t.idy_ubica) AS ruta,
                          IF(t.cve_lote != '', IF(a.control_lotes = 'S',  t.cve_lote, ''), '') AS lote,
                          IF(t.cve_lote != '', IF(((SELECT Caducidad FROM c_lotes WHERE LOTE = t.cve_lote AND tsb.Cve_articulo = cve_articulo) != '0000-00-00') AND (a.control_lotes = 'S'),  (SELECT Caducidad FROM c_lotes WHERE LOTE = t.cve_lote AND tsb.Cve_articulo = cve_articulo), '' ), '') AS caducidad,
                          IF(t.cve_lote != '', IF(a.control_numero_series = 'S',  t.cve_lote, ''), '') AS serie,
                          #SUM(ROUND((a.peso * tsb.Num_cantidad),4)) AS peso,
                          #IF(op.id_umed = a.unidadMedida OR IFNULL(op.id_umed, 0) = 0, SUM(ROUND((a.peso * tsb.Num_cantidad),4)), tsb.Num_cantidad) AS peso,
                          IF((op.id_umed = a.unidadMedida OR IFNULL(op.id_umed, 0) = 0), IF(((SELECT DISTINCT Fol_Folio FROM td_consolidado WHERE Fol_PedidoCon = tsb.fol_folio LIMIT 1) LIKE 'OT%'), tsb.Num_cantidad, SUM(ROUND((a.peso * tsb.Num_cantidad),4))), tsb.Num_cantidad) AS peso,
                          SUM(ROUND(((a.alto/1000) * (a.ancho/1000) * (a.fondo/1000) * tsb.Num_cantidad),4)) AS volumen,
                          #SUM(tsb.Num_cantidad) AS pedidas,
                          tsb.Num_cantidad AS pedidas,
                          IFNULL(s.Cantidad, 0) AS surtidas,
                          IFNULL(umed2.mav_cveunimed, umed.mav_cveunimed) AS id_medida,
                          IFNULL(umed2.des_umed, umed.des_umed) AS unidad_medida,
                          #tsb.Num_cantidad AS existencia
                          IF(COALESCE((SELECT SUM(Existencia) AS suma FROM V_ExistenciaGral WHERE V_ExistenciaGral.cve_articulo = tsb.Cve_Articulo AND V_ExistenciaGral.cve_almac = '{$almacen}'), 0) >= tsb.Num_cantidad, tsb.Num_cantidad, COALESCE((SELECT SUM(Existencia) AS suma FROM V_ExistenciaGral WHERE V_ExistenciaGral.cve_articulo = tsb.Cve_Articulo AND V_ExistenciaGral.cve_almac = '{$almacen}'), 0)) AS existencia
                    FROM td_subpedido tsb
                      LEFT JOIN c_articulo a ON a.cve_articulo = tsb.Cve_articulo
                      LEFT JOIN c_unimed umed ON umed.id_umed = a.unidadMedida
                      LEFT JOIN c_unimed umed2 ON umed2.id_umed = tsb.id_unimed
                      LEFT JOIN th_pedido ON th_pedido.Fol_folio = tsb.Fol_folio
                      LEFT JOIN c_cliente c ON c.Cve_Clte = th_pedido.Cve_clte
                      LEFT JOIN td_surtidopiezas s ON s.fol_folio = th_pedido.Fol_Folio AND s.Cve_articulo = tsb.Cve_Articulo 
                      LEFT JOIN (
                          SELECT 
                          V_ExistenciaGral.cve_articulo,
                          c_ubicacion.CodigoCSD,
                          MIN(V_ExistenciaGral.cve_ubicacion) AS MIN 
                          FROM V_ExistenciaGral 
                          LEFT JOIN c_ubicacion ON V_ExistenciaGral.cve_ubicacion = c_ubicacion.idy_ubica 
                          GROUP BY cve_articulo
                      ) X ON X.cve_articulo = tsb.Cve_articulo
                      LEFT JOIN t_recorrido_surtido t ON t.fol_folio = th_pedido.Fol_folio AND t.Cve_articulo = tsb.Cve_articulo AND t.Sufijo = $sufijo
                      LEFT JOIN t_ordenprod op ON op.Folio_Pro = tsb.fol_folio
                    WHERE tsb.Fol_folio = '{$id}' AND tsb.Sufijo = $sufijo GROUP BY clave ORDER BY t.orden_secuencia
              ";//AND t.cve_lote = tsb.cve_lote
              }
                  else
                    $sql = "
                        SELECT 
                              td_backorder.Folio_BackO AS folio,
                              t.Sufijo AS sufijo,
                              c.RazonSocial AS cliente,
                              td_backorder.Cve_Articulo AS clave,
                              a.des_articulo AS articulo,
                              t.cve_usuario AS surtidor,
                              t.claverp AS ubicacion,
                              t.idy_ubica AS idy_ubica,
                              t.orden_secuencia AS secuencia,
                                    0 AS Pallet,
                                    0 AS Caja, 
                                    0 AS Piezas,
                                    IFNULL(a.cajas_palet, 0) cajasxpallets,
                                    IFNULL(a.num_multiplo, 0) piezasxcajas, 
                              (SELECT Descripcion FROM V_RutaSurtido_Usuario WHERE orden_secuencia = t.orden_secuencia AND cve_usuario = t.cve_usuario AND idy_ubica = t.idy_ubica) AS ruta,
                              IF(t.cve_lote != '', IF(a.control_lotes = 'S',  t.cve_lote, ''), '') AS lote,
                              IF(t.cve_lote != '', IF(((SELECT Caducidad FROM c_lotes WHERE LOTE = t.cve_lote AND td_backorder.Cve_Articulo = cve_articulo) != '0000-00-00') AND (a.control_lotes = 'S'),  (SELECT Caducidad FROM c_lotes WHERE LOTE = t.cve_lote AND td_backorder.Cve_Articulo = cve_articulo), '' ), '') AS caducidad,
                              IF(t.cve_lote != '', IF(a.control_numero_series = 'S',  t.cve_lote, ''), '') AS serie,
                              SUM(ROUND((a.peso * td_backorder.Cantidad_Pedido),4)) AS peso,
                              SUM(ROUND(((a.alto/1000) * (a.ancho/1000) * (a.fondo/1000) * td_backorder.Cantidad_Pedido),4)) AS volumen,
                              #SUM(td_backorder.Cantidad_Pedido) AS pedidas,
                                IFNULL(umed.mav_cveunimed, '') AS id_medida,
                                IFNULL(umed.des_umed, '') AS unidad_medida,
                              td_backorder.Cantidad_Pedido AS pedidas,
                              IFNULL(s.Cantidad, 0) AS surtidas,
                              td_backorder.Cantidad_BO AS existencia
                          FROM td_backorder
                          LEFT JOIN c_articulo a ON a.cve_articulo = td_backorder.Cve_Articulo
                          LEFT JOIN c_unimed umed ON umed.id_umed = a.unidadMedida
                          LEFT JOIN th_backorder ON th_backorder.Folio_BackO= td_backorder.Folio_BackO  
                          LEFT JOIN c_cliente c ON c.Cve_Clte = th_backorder.Cve_Clte OR th_backorder.Cve_Clte = ''
                          LEFT JOIN td_surtidopiezas s ON s.fol_folio = th_backorder.Fol_Folio AND s.Cve_articulo = td_backorder.Cve_Articulo 
                          LEFT JOIN (
                          SELECT 
                              V_ExistenciaGral.cve_articulo,
                              c_ubicacion.CodigoCSD,
                              MIN(V_ExistenciaGral.cve_ubicacion) AS MIN 
                          FROM V_ExistenciaGral 
                              LEFT JOIN c_ubicacion ON V_ExistenciaGral.cve_ubicacion = c_ubicacion.idy_ubica 
                          GROUP BY cve_articulo
                          ) X ON X.cve_articulo = td_backorder.Cve_Articulo
                          LEFT JOIN t_recorrido_surtido t ON t.fol_folio = th_backorder.Fol_Folio AND t.Cve_articulo = td_backorder.Cve_articulo AND t.cve_lote = td_backorder.Cve_Lote
                          WHERE th_backorder.Fol_Folio = '{$id}'
                          GROUP BY clave ORDER BY t.orden_secuencia
                ";
            }

      //#LEFT JOIN t_recorrido_surtido on t_recorrido_surtido.fol_folio = td_pedido.Fol_folio
            //echo $sql;
      $sth = \db()->prepare($sql);
      $sth->execute();
      $res[0]=$sth->fetchAll();
      $res[2]="status_pedido = ".$status_pedido."...AAAA".$sql;
      $res[3]=$status;
    }
    if($surtiendo)
    {
      /* Articulos ya Surtidos */
      /*
      $sql_surtidos = 
          "
           SELECT * FROM (SELECT 
              td_pedido.fol_folio AS folio,
              s.Sufijo as sufijo,
              c.RazonSocial AS cliente,
              td_pedido.Cve_articulo AS clave,
              a.des_articulo AS articulo,
              ROUND((a.peso * td_pedido.Num_cantidad),4) AS peso,
              ROUND(((a.alto/1000) * (a.ancho/1000) * (a.fondo/1000) * td_pedido.Num_cantidad),4) AS volumen,
              td_pedido.Num_cantidad as pedidas,
              IFNULL(c_lotes.LOTE, '') AS lote,
              IFNULL(FLOOR(s.Cantidad), 0) AS existencia,
			  IFNULL(FLOOR(s.Cantidad), 0) AS surtidas
		FROM td_pedido
			  LEFT JOIN th_pedido ON th_pedido.Fol_folio = td_pedido.Fol_folio
        INNER JOIN td_surtidopiezas s ON s.fol_folio = td_pedido.Fol_folio AND s.Cve_articulo = td_pedido.Cve_articulo AND s.cve_almac = th_pedido.cve_almac
			  LEFT JOIN c_lotes on c_lotes.id = s.LOTE 
        LEFT JOIN c_articulo a ON a.cve_articulo = td_pedido.Cve_articulo
        LEFT JOIN c_cliente c ON c.Cve_Clte = th_pedido.Cve_clte
			  LEFT JOIN (
                  Select 
                      V_ExistenciaGral.cve_articulo,
                      c_ubicacion.CodigoCSD,
                      min(V_ExistenciaGral.cve_ubicacion) as min 
                  FROM V_ExistenciaGral 
                      LEFT JOIN c_ubicacion on V_ExistenciaGral.cve_ubicacion = c_ubicacion.idy_ubica 
                  GROUP by cve_articulo
              ) x ON x.cve_articulo = td_pedido.Cve_articulo
           WHERE td_pedido.Fol_folio = '$id') X
          WHERE X.surtidas <> 0
          ";
      $sth = \db()->prepare($sql_surtidos);
      $sth->execute();

      $res[4]=$sth->fetchAll();
      
      // Articulos Pendientes por surtir
      if($sufijo > 0) $sufijo = " AND t_recorrido_surtido.Sufijo = $sufijo"; else $sufijo = "";
      $sql_pendientes = "
          SELECT * FROM (SELECT 
              td_pedido.fol_folio AS folio,
              t_recorrido_surtido.Sufijo as sufijo,
              c.RazonSocial AS cliente,
              td_pedido.Cve_articulo AS clave,
              a.des_articulo AS articulo,
              ROUND((a.peso * td_pedido.Num_cantidad),4) AS peso,
              ROUND(((a.alto/1000) * (a.ancho/1000) * (a.fondo/1000) * td_pedido.Num_cantidad),4) AS volumen, 
              #td_pedido.Num_cantidad as pedidas,
              ifnull(t_recorrido_surtido.Cantidad,td_pedido.Num_cantidad) as pedidas,
              t_recorrido_surtido.Cantidad as existencia,
              #(SELECT sum(Existencia) as existencia FROM `VS_ExistenciaParaSurtido` WHERE VS_ExistenciaParaSurtido.cve_articulo = td_pedido.Cve_articulo GROUP BY cve_articulo)  as existencia,
              #(td_pedido.Num_cantidad - td_pedido.Num_cantidad) as backorder, 
              0 AS surtidas,
              (
                  SELECT nombre
                      FROM `th_ruta_surtido` 
                      INNER JOIN td_ruta_surtido ON td_ruta_surtido.idr = th_ruta_surtido.idr
                      left join rel_usuario_ruta on rel_usuario_ruta.id_ruta = th_ruta_surtido.idr
                      inner join c_usuario on c_usuario.id_user = rel_usuario_ruta.id_usuario
                      WHERE td_ruta_surtido.idy_ubica = t_recorrido_surtido.idy_ubica
                      and cve_usuario = t_recorrido_surtido.cve_usuario
              ) as ruta,
              IFNULL(t_recorrido_surtido.claverp,'--') as ubicacion,
              t_recorrido_surtido.orden_secuencia as secuencia,
              c_usuario.nombre_completo as surtidor,
              t_recorrido_surtido.idy_ubica as id_ubicacion,
              #t_recorrido_surtido.cve_lote as lote.
              IFNULL(c_lotes.LOTE, '') as lote
          FROM td_pedido
              INNER JOIN t_recorrido_surtido on t_recorrido_surtido.fol_folio = td_pedido.Fol_folio and t_recorrido_surtido.Cve_articulo = td_pedido.Cve_articulo
              LEFT JOIN c_lotes on c_lotes.id = t_recorrido_surtido.cve_lote
              LEFT JOIN c_usuario on c_usuario.cve_usuario = t_recorrido_surtido.cve_usuario
              LEFT JOIN c_articulo a ON a.cve_articulo = td_pedido.Cve_articulo
              LEFT JOIN th_pedido ON th_pedido.Fol_folio = td_pedido.Fol_folio
              LEFT JOIN c_cliente c ON c.Cve_Clte = th_pedido.Cve_clte
              #LEFT JOIN td_surtidopiezas s ON s.fol_folio = td_pedido.Fol_folio AND s.Cve_articulo = td_pedido.Cve_articulo AND s.cve_almac = th_pedido.cve_almac AND s.Cantidad = t_recorrido_surtido.Cantidad AND s.LOTE = t_recorrido_surtido.cve_lote
              LEFT JOIN (
                  Select 
                      V_ExistenciaGral.cve_articulo,
                      c_ubicacion.CodigoCSD,
                      min(V_ExistenciaGral.cve_ubicacion) as min 
                  FROM V_ExistenciaGral 
                      LEFT JOIN c_ubicacion on V_ExistenciaGral.cve_ubicacion = c_ubicacion.idy_ubica 
                  GROUP by cve_articulo
              ) x ON x.cve_articulo = td_pedido.Cve_articulo
          WHERE td_pedido.Fol_folio = '$id' $sufijo) X 
          WHERE X.surtidas = 0
      ";
      */

        if($is_backorder == 0 && $sufijo > 0)
        {
            //CON RECORRIDO SURTIDO
            if($con_recorrido == 1)
            {
              $sql = "
                    SELECT 
                          tsb.Fol_folio AS folio,
                          tsb.Sufijo AS sufijo,
                          c.RazonSocial AS cliente,
                          tsb.Cve_articulo AS clave,
                          a.des_articulo AS articulo,
                          IFNULL(t.cve_usuario, t_sin_lote.cve_usuario) AS surtidor,
                          IFNULL(t.claverp, t_sin_lote.claverp) AS ubicacion,
                          IFNULL(t.idy_ubica, t_sin_lote.idy_ubica) AS idy_ubica,
                          IFNULL(t.orden_secuencia, t_sin_lote.orden_secuencia) AS secuencia,
                          IFNULL(a.control_peso, 'N') as control_peso,
                                    0 AS Pallet,
                                    0 AS Caja, 
                                    0 AS Piezas,
                                    IFNULL(a.cajas_palet, 0) cajasxpallets,
                                    IFNULL(a.num_multiplo, 0) piezasxcajas, 
                          (SELECT Descripcion FROM V_RutaSurtido_Usuario WHERE orden_secuencia = IFNULL(t.orden_secuencia, t_sin_lote.orden_secuencia) AND cve_usuario = IFNULL(t.cve_usuario, t_sin_lote.cve_usuario) AND idy_ubica = IFNULL(t.idy_ubica, t_sin_lote.idy_ubica)) AS ruta,
                          IF(IFNULL(t.cve_lote, t_sin_lote.cve_lote) != '', IF(a.control_lotes = 'S',  IFNULL(t.cve_lote, t_sin_lote.cve_lote), ''), '') AS lote,
                          IF(a.Caduca = 'S', IF(IFNULL(t.cve_lote, t_sin_lote.cve_lote) != '', IF(((SELECT DATE_FORMAT(Caducidad, '%d-%m-%Y') FROM c_lotes WHERE LOTE = IFNULL(t.cve_lote, t_sin_lote.cve_lote) AND tsb.Cve_articulo = cve_articulo) != '0000-00-00') AND (a.control_lotes = 'S'),  (SELECT DATE_FORMAT(Caducidad, '%d-%m-%Y') FROM c_lotes WHERE LOTE = IFNULL(t.cve_lote, t_sin_lote.cve_lote) AND tsb.Cve_articulo = cve_articulo), '' ), ''), '') AS caducidad,
                          IF(IFNULL(t.cve_lote, t_sin_lote.cve_lote) != '', IF(a.control_numero_series = 'S',  IFNULL(t.cve_lote, t_sin_lote.cve_lote), ''), '') AS serie,
                          #SUM(ROUND((a.peso * tsb.Num_cantidad),4)) AS peso,
                          #IF(op.id_umed = a.unidadMedida OR IFNULL(op.id_umed, 0) = 0, SUM(ROUND((a.peso * tsb.Num_cantidad),4)), t.Cantidad) AS peso,
                          IF((op.id_umed = a.unidadMedida OR IFNULL(op.id_umed, 0) = 0), IF(('OT' LIKE (SELECT DISTINCT Fol_Folio FROM td_consolidado WHERE Fol_PedidoCon = tsb.fol_folio LIMIT 1)), SUM(ROUND((a.peso * tsb.Num_cantidad),4)), tsb.Num_cantidad), tsb.Num_cantidad) AS peso,
                          SUM(ROUND(((a.alto/1000) * (a.ancho/1000) * (a.fondo/1000) * tsb.Num_cantidad),4)) AS volumen,
                          #SUM(tsb.Num_cantidad) AS pedidas,
                          #IF(a.control_peso = 'S', TRUNCATE(tsb.Num_cantidad, 4), TRUNCATE(tsb.Num_cantidad,0) ) AS pedidas,
                          IF(a.control_peso = 'S', TRUNCATE(IFNULL(t.Cantidad,t_sin_lote.Cantidad), 4), TRUNCATE(IFNULL(t.Cantidad,t_sin_lote.Cantidad),0) ) AS pedidas,
                          IFNULL(s.Cantidad, 0) AS surtidas,
                          umed.mav_cveunimed AS id_medida,
                          umed.des_umed AS unidad_medida,
                          #tsb.Num_cantidad AS existencia
                          #IF(COALESCE((SELECT SUM(Existencia) AS suma FROM V_ExistenciaGral WHERE V_ExistenciaGral.cve_ubicacion = t.idy_ubica AND V_ExistenciaGral.cve_articulo = tsb.Cve_Articulo AND V_ExistenciaGral.cve_almac = '{$almacen}'), 0) >= tsb.Num_cantidad, tsb.Num_cantidad, COALESCE((SELECT SUM(Existencia) AS suma FROM V_ExistenciaGral WHERE V_ExistenciaGral.cve_ubicacion = t.idy_ubica AND V_ExistenciaGral.cve_articulo = tsb.Cve_Articulo AND V_ExistenciaGral.cve_almac = '{$almacen}'), 0)) AS existencia
                          #t.Cantidad AS existencia
                          IFNULL(t.Cantidad, tsb.Num_cantidad) AS existencia
                    FROM td_subpedido tsb
                      LEFT JOIN c_articulo a ON a.cve_articulo = tsb.Cve_articulo
                      LEFT JOIN c_unimed umed ON umed.id_umed = a.unidadMedida
                      LEFT JOIN th_pedido ON th_pedido.Fol_folio = tsb.Fol_folio
                      LEFT JOIN c_cliente c ON c.Cve_Clte = th_pedido.Cve_clte
                      LEFT JOIN td_surtidopiezas s ON s.fol_folio = th_pedido.Fol_Folio AND s.Cve_articulo = tsb.Cve_Articulo 
                      LEFT JOIN (
                          SELECT 
                          V_ExistenciaGral.cve_articulo,
                          c_ubicacion.CodigoCSD,
                          MIN(V_ExistenciaGral.cve_ubicacion) AS MIN 
                          FROM V_ExistenciaGral 
                          LEFT JOIN c_ubicacion ON V_ExistenciaGral.cve_ubicacion = c_ubicacion.idy_ubica 
                          GROUP BY cve_articulo
                      ) X ON X.cve_articulo = tsb.Cve_articulo
                      LEFT JOIN t_recorrido_surtido t ON t.fol_folio = th_pedido.Fol_folio AND t.Cve_articulo = tsb.Cve_articulo AND t.cve_lote = tsb.cve_lote AND t.Sufijo = $sufijo
                      LEFT JOIN t_recorrido_surtido t_sin_lote ON t_sin_lote.fol_folio = th_pedido.Fol_folio AND t_sin_lote.Cve_articulo = tsb.Cve_articulo AND t_sin_lote.Sufijo = $sufijo
                      LEFT JOIN t_ordenprod op ON op.Folio_Pro = tsb.fol_folio
                    WHERE tsb.Fol_folio = '{$id}' AND tsb.Sufijo = $sufijo 
                    #GROUP BY t.claverp,t.Cve_articulo, serie, lote, existencia, ubicacion, clave
                    GROUP BY IFNULL(t.claverp,t_sin_lote.claverp), IFNULL(t.cve_lote, t_sin_lote.cve_lote), IFNULL(t.idy_ubica, t_sin_lote.idy_ubica), IFNULL(t.Cve_articulo, t_sin_lote.Cve_articulo), IFNULL(t.fol_folio, t_sin_lote.fol_folio)
                    ORDER BY t.orden_secuencia
              ";//AND t.cve_lote = tsb.cve_lote
          }
          else if($con_recorrido == 0)
          {
              //SIN RECORRIDO SURTIDO
              $sql = "SELECT * FROM (
                    SELECT 
                          tsb.Fol_folio AS folio,
                          tsb.Sufijo AS sufijo,
                          c.RazonSocial AS cliente,
                          tsb.Cve_articulo AS clave,
                          a.des_articulo AS articulo,
                          (SELECT cve_usuario FROM th_subpedido WHERE fol_folio = '{$id}') AS surtidor,
                          (SELECT CodigoCSD FROM c_ubicacion WHERE idy_ubica = IFNULL(t.Idy_Ubica, t_sin_lote.Idy_Ubica)) AS ubicacion,
                          IFNULL(t.idy_ubica, t_sin_lote.idy_ubica) AS idy_ubica,
                          '' AS secuencia,
                          IFNULL(a.control_peso, 'N') AS control_peso,
                                    0 AS Pallet,
                                    0 AS Caja, 
                                    0 AS Piezas,
                                    IFNULL(a.cajas_palet, 0) cajasxpallets,
                                    IFNULL(a.num_multiplo, 0) piezasxcajas, #IFNULL(t.cve_usuario, t_sin_lote.cve_usuario)
                          (SELECT Descripcion FROM V_RutaSurtido_Usuario WHERE cve_usuario = (SELECT cve_usuario FROM th_subpedido WHERE fol_folio = '{$id}') AND idy_ubica = IFNULL(t.idy_ubica, t_sin_lote.idy_ubica) AND Cve_Almac = (SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = IFNULL(t.idy_ubica, t_sin_lote.idy_ubica))) AS ruta,
                          IF(IFNULL(t.cve_lote, t_sin_lote.cve_lote) != '', IF(a.control_lotes = 'S',  IFNULL(t.cve_lote, t_sin_lote.cve_lote), ''), '') AS lote,
                          IF(a.Caduca = 'S', IF(IFNULL(t.cve_lote, t_sin_lote.cve_lote) != '', IF(((SELECT DATE_FORMAT(Caducidad, '%d-%m-%Y') FROM c_lotes WHERE LOTE = IFNULL(t.cve_lote, t_sin_lote.cve_lote) AND tsb.Cve_articulo = cve_articulo) != '0000-00-00') AND (a.control_lotes = 'S'),  (SELECT DATE_FORMAT(Caducidad, '%d-%m-%Y') FROM c_lotes WHERE LOTE = IFNULL(t.cve_lote, t_sin_lote.cve_lote) AND tsb.Cve_articulo = cve_articulo), '' ), ''), '') AS caducidad,
                          IF(IFNULL(t.cve_lote, t_sin_lote.cve_lote) != '', IF(a.control_numero_series = 'S',  IFNULL(t.cve_lote, t_sin_lote.cve_lote), ''), '') AS serie,
                          IF((op.id_umed = a.unidadMedida OR IFNULL(op.id_umed, 0) = 0), IF(('OT' LIKE (SELECT DISTINCT Fol_Folio FROM td_consolidado WHERE Fol_PedidoCon = tsb.fol_folio LIMIT 1)), SUM(ROUND((a.peso * tsb.Num_cantidad),4)), tsb.Num_cantidad), tsb.Num_cantidad) AS peso,
                          SUM(ROUND(((a.alto/1000) * (a.ancho/1000) * (a.fondo/1000) * tsb.Num_cantidad),4)) AS volumen,
                          IF(a.control_peso = 'S', TRUNCATE(tsb.Num_cantidad, 4), TRUNCATE(tsb.Num_cantidad,0) ) AS pedidas,
                          IFNULL(s.Cantidad, 0) AS surtidas,
                          umed.mav_cveunimed AS id_medida,
                          umed.des_umed AS unidad_medida,
                          IFNULL(IFNULL(t.Existencia, t_sin_lote.Existencia), tsb.Num_cantidad) AS existencia
                    FROM td_subpedido tsb
                      LEFT JOIN c_articulo a ON a.cve_articulo = tsb.Cve_articulo
                      LEFT JOIN c_unimed umed ON umed.id_umed = a.unidadMedida
                      LEFT JOIN th_pedido ON th_pedido.Fol_folio = tsb.Fol_folio
                      LEFT JOIN c_cliente c ON c.Cve_Clte = th_pedido.Cve_clte
                      LEFT JOIN td_surtidopiezas s ON s.fol_folio = th_pedido.Fol_Folio AND s.Cve_articulo = tsb.Cve_Articulo 
                      LEFT JOIN (
                          SELECT 
                          V_ExistenciaGral.cve_articulo,
                          c_ubicacion.CodigoCSD,
                          MIN(V_ExistenciaGral.cve_ubicacion) AS MIN 
                          FROM V_ExistenciaGral 
                          LEFT JOIN c_ubicacion ON V_ExistenciaGral.cve_ubicacion = c_ubicacion.idy_ubica 
                          GROUP BY cve_articulo
                      ) X ON X.cve_articulo = tsb.Cve_articulo
                        LEFT JOIN VS_ExistenciaParaSurtido t ON t.cve_articulo = tsb.Cve_articulo AND t.cve_lote = tsb.cve_lote AND tsb.Sufijo = $sufijo
                        LEFT JOIN VS_ExistenciaParaSurtido t_sin_lote ON t_sin_lote.Cve_articulo = tsb.Cve_articulo AND tsb.Sufijo = $sufijo
                      LEFT JOIN t_ordenprod op ON op.Folio_Pro = tsb.fol_folio
                    WHERE tsb.Fol_folio = '{$id}' AND tsb.Sufijo = $sufijo AND tsb.Cve_articulo = IFNULL(t.cve_articulo, t_sin_lote.cve_articulo) 
                    GROUP BY ubicacion, serie, lote, existencia, ubicacion, clave
                    ORDER BY clave
            ) AS sin_recorrido #WHERE ruta IS NOT NULL";
        }
              if($status != 'S')
              {
                $sql = "SELECT 
                          tsb.Fol_folio AS folio,
                          tsb.Sufijo AS sufijo,
                          c.RazonSocial AS cliente,
                          tsb.Cve_articulo AS clave,
                          a.des_articulo AS articulo,
                          #t.cve_usuario AS surtidor,
                          ths.cve_usuario AS surtidor,
                          #t.claverp AS ubicacion,
                          '' AS ubicacion,
                          #t.idy_ubica AS idy_ubica,
                          '' AS idy_ubica,
                          #t.orden_secuencia AS secuencia,
                          '' AS secuencia,
                          #(SELECT Descripcion FROM V_RutaSurtido_Usuario WHERE orden_secuencia = t.orden_secuencia AND cve_usuario = t.cve_usuario AND idy_ubica = t.idy_ubica) AS ruta,
                          '' AS ruta,
                          #IF(t.cve_lote != '', IF(a.control_lotes = 'S',  t.cve_lote, ''), '') AS lote,
                                    0 AS Pallet,
                                    0 AS Caja, 
                                    0 AS Piezas,
                                    IFNULL(a.cajas_palet, 0) cajasxpallets,
                                    IFNULL(a.num_multiplo, 0) piezasxcajas, 
                          IFNULL(a.control_peso, 'N') as control_peso,
                          IF(s.LOTE != '', IF(a.control_lotes = 'S',  s.LOTE, ''), '') AS lote,
                          #'' AS lote,
                          IF(s.LOTE != '', IF(((SELECT DATE_FORMAT(Caducidad, '%d-%m-%Y') FROM c_lotes WHERE LOTE = s.LOTE AND tsb.Cve_articulo = cve_articulo) != '0000-00-00') AND (a.control_lotes = 'S'),  (SELECT DATE_FORMAT(Caducidad, '%d-%m-%Y') FROM c_lotes WHERE LOTE = s.LOTE AND tsb.Cve_articulo = cve_articulo), '' ), '') AS caducidad,
                          #'' AS caducidad,
                          IF(s.LOTE != '', IF(a.control_numero_series = 'S',  s.LOTE, ''), '') AS serie,
                          #'' AS serie,
                          SUM(ROUND((a.peso * tsb.Num_cantidad),4)) AS peso,
                          SUM(ROUND(((a.alto/1000) * (a.ancho/1000) * (a.fondo/1000) * tsb.Num_cantidad),4)) AS volumen,
                          #SUM(tsb.Num_cantidad) AS pedidas,
                          tsb.Num_cantidad AS pedidas,
                          IFNULL(s.Cantidad, 0) AS surtidas
                          #tsb.Num_cantidad AS existencia
                          #IF(COALESCE((SELECT SUM(Existencia) AS suma FROM V_ExistenciaGral WHERE V_ExistenciaGral.cve_ubicacion = t.idy_ubica AND V_ExistenciaGral.cve_articulo = tsb.Cve_Articulo AND V_ExistenciaGral.cve_almac = '{$almacen}'), 0) >= tsb.Num_cantidad, tsb.Num_cantidad, COALESCE((SELECT SUM(Existencia) AS suma FROM V_ExistenciaGral WHERE V_ExistenciaGral.cve_ubicacion = t.idy_ubica AND V_ExistenciaGral.cve_articulo = tsb.Cve_Articulo AND V_ExistenciaGral.cve_almac = '{$almacen}'), 0)) AS existencia
                    FROM td_subpedido tsb
                      LEFT JOIN c_articulo a ON a.cve_articulo = tsb.Cve_articulo
                      LEFT JOIN th_pedido ON th_pedido.Fol_folio = tsb.Fol_folio
                      LEFT JOIN th_subpedido ths ON ths.fol_folio = tsb.Fol_folio
                      LEFT JOIN c_cliente c ON c.Cve_Clte = th_pedido.Cve_clte
                      LEFT JOIN td_surtidopiezas s ON s.fol_folio = tsb.Fol_Folio AND s.Cve_articulo = tsb.Cve_Articulo
                      LEFT JOIN (
                          SELECT 
                          V_ExistenciaGral.cve_articulo,
                          c_ubicacion.CodigoCSD,
                          MIN(V_ExistenciaGral.cve_ubicacion) AS MIN 
                          FROM V_ExistenciaGral 
                          LEFT JOIN c_ubicacion ON V_ExistenciaGral.cve_ubicacion = c_ubicacion.idy_ubica 
                          GROUP BY cve_articulo
                      ) X ON X.cve_articulo = tsb.Cve_articulo
                      #LEFT JOIN t_recorrido_surtido t ON t.fol_folio = tsb.Fol_folio AND t.Sufijo = 1 #AND t.Cve_articulo = tsb.Cve_articulo
                    WHERE tsb.Fol_folio = '{$id}' AND tsb.Sufijo = $sufijo 
                    GROUP BY clave, serie, lote";
              }
          }
          else 
          {
            if($is_backorder == 0 && $sufijo == 0)
                  $sql = "
                        SELECT 
                              tsb.Fol_folio AS folio,
                              '' AS sufijo,
                              c.RazonSocial AS cliente,
                              tsb.Cve_articulo AS clave,
                              a.des_articulo AS articulo,
                              t.cve_usuario AS surtidor,
                              t.claverp AS ubicacion,
                              t.idy_ubica AS idy_ubica,
                              t.orden_secuencia AS secuencia,
                                    0 AS Pallet,
                                    0 AS Caja, 
                                    0 AS Piezas,
                                    IFNULL(a.cajas_palet, 0) cajasxpallets,
                                    IFNULL(a.num_multiplo, 0) piezasxcajas, 
                              (SELECT Descripcion FROM V_RutaSurtido_Usuario WHERE orden_secuencia = t.orden_secuencia AND cve_usuario = t.cve_usuario AND idy_ubica = t.idy_ubica) AS ruta,
                              IF(t.cve_lote != '', IF(a.control_lotes = 'S',  t.cve_lote, ''), '') AS lote,
                              IF(t.cve_lote != '', IF(((SELECT Caducidad FROM c_lotes WHERE LOTE = t.cve_lote AND tsb.Cve_articulo = cve_articulo) != '0000-00-00') AND (a.control_lotes = 'S'),  (SELECT Caducidad FROM c_lotes WHERE LOTE = t.cve_lote AND tsb.Cve_articulo = cve_articulo), '' ), '') AS caducidad,
                              IF(t.cve_lote != '', IF(a.control_numero_series = 'S',  t.cve_lote, ''), '') AS serie,
                              SUM(ROUND((a.peso * tsb.Num_cantidad),4)) AS peso,
                              SUM(ROUND(((a.alto/1000) * (a.ancho/1000) * (a.fondo/1000) * tsb.Num_cantidad),4)) AS volumen,
                              #SUM(tsb.Num_cantidad) AS pedidas,
                              tsb.Num_cantidad AS pedidas,
                              IFNULL(s.Cantidad, 0) AS surtidas,
                              #tsb.Num_cantidad AS existencia
                              IF(COALESCE((SELECT SUM(Existencia) AS suma FROM V_ExistenciaGral WHERE V_ExistenciaGral.cve_articulo = tsb.Cve_Articulo AND V_ExistenciaGral.cve_almac = '{$almacen}'), 0) >= tsb.Num_cantidad, tsb.Num_cantidad, COALESCE((SELECT SUM(Existencia) AS suma FROM V_ExistenciaGral WHERE V_ExistenciaGral.cve_articulo = tsb.Cve_Articulo AND V_ExistenciaGral.cve_almac = '{$almacen}'), 0)) AS existencia
                        FROM td_pedido tsb
                          LEFT JOIN c_articulo a ON a.cve_articulo = tsb.Cve_articulo
                          LEFT JOIN th_pedido ON th_pedido.Fol_folio = tsb.Fol_folio
                          LEFT JOIN c_cliente c ON c.Cve_Clte = th_pedido.Cve_clte
                          LEFT JOIN td_surtidopiezas s ON s.fol_folio = th_pedido.Fol_Folio AND s.Cve_articulo = tsb.Cve_Articulo 
                          LEFT JOIN (
                              SELECT 
                              V_ExistenciaGral.cve_articulo,
                              c_ubicacion.CodigoCSD,
                              MIN(V_ExistenciaGral.cve_ubicacion) AS MIN 
                              FROM V_ExistenciaGral 
                              LEFT JOIN c_ubicacion ON V_ExistenciaGral.cve_ubicacion = c_ubicacion.idy_ubica 
                              GROUP BY cve_articulo
                          ) X ON X.cve_articulo = tsb.Cve_articulo
                          LEFT JOIN t_recorrido_surtido t ON t.fol_folio = th_pedido.Fol_folio AND t.Cve_articulo = tsb.Cve_articulo 
                        WHERE tsb.Fol_folio = '{$id}' GROUP BY clave ORDER BY t.orden_secuencia
                  ";//AND t.cve_lote = tsb.cve_lote
            else if($is_backorder == 1)
                    $sql = "
                        SELECT 
                              td_backorder.Folio_BackO AS folio,
                              t.Sufijo AS sufijo,
                              c.RazonSocial AS cliente,
                              td_backorder.Cve_Articulo AS clave,
                              a.des_articulo AS articulo,
                              t.cve_usuario AS surtidor,
                              t.claverp AS ubicacion,
                              t.idy_ubica AS idy_ubica,
                              t.orden_secuencia AS secuencia,
                                    0 AS Pallet,
                                    0 AS Caja, 
                                    0 AS Piezas,
                                    IFNULL(a.cajas_palet, 0) cajasxpallets,
                                    IFNULL(a.num_multiplo, 0) piezasxcajas, 
                              (SELECT Descripcion FROM V_RutaSurtido_Usuario WHERE orden_secuencia = t.orden_secuencia AND cve_usuario = t.cve_usuario AND idy_ubica = t.idy_ubica) AS ruta,
                              IF(t.cve_lote != '', IF(a.control_lotes = 'S',  t.cve_lote, ''), '') AS lote,
                              IF(t.cve_lote != '', IF(((SELECT Caducidad FROM c_lotes WHERE LOTE = t.cve_lote AND td_backorder.Cve_Articulo = cve_articulo) != '0000-00-00') AND (a.control_lotes = 'S'),  (SELECT Caducidad FROM c_lotes WHERE LOTE = t.cve_lote AND td_backorder.Cve_Articulo = cve_articulo), '' ), '') AS caducidad,
                              IF(t.cve_lote != '', IF(a.control_numero_series = 'S',  t.cve_lote, ''), '') AS serie,
                              SUM(ROUND((a.peso * td_backorder.Cantidad_Pedido),4)) AS peso,
                              SUM(ROUND(((a.alto/1000) * (a.ancho/1000) * (a.fondo/1000) * td_backorder.Cantidad_Pedido),4)) AS volumen,
                              #SUM(td_backorder.Cantidad_Pedido) AS pedidas,
                                IFNULL(umed.mav_cveunimed, '') AS id_medida,
                                IFNULL(umed.des_umed, '') AS unidad_medida,
                              td_backorder.Cantidad_Pedido AS pedidas,
                              IFNULL(s.Cantidad, 0) AS surtidas,
                              td_backorder.Cantidad_BO AS existencia
                          FROM td_backorder
                          LEFT JOIN c_articulo a ON a.cve_articulo = td_backorder.Cve_Articulo
                          LEFT JOIN c_unimed umed ON umed.id_umed = a.unidadMedida
                          LEFT JOIN th_backorder ON th_backorder.Folio_BackO= td_backorder.Folio_BackO  
                          LEFT JOIN c_cliente c ON c.Cve_Clte = th_backorder.Cve_Clte
                          LEFT JOIN td_surtidopiezas s ON s.fol_folio = th_backorder.Fol_Folio AND s.Cve_articulo = td_backorder.Cve_Articulo 
                          LEFT JOIN (
                          SELECT 
                              V_ExistenciaGral.cve_articulo,
                              c_ubicacion.CodigoCSD,
                              MIN(V_ExistenciaGral.cve_ubicacion) AS MIN 
                          FROM V_ExistenciaGral 
                              LEFT JOIN c_ubicacion ON V_ExistenciaGral.cve_ubicacion = c_ubicacion.idy_ubica 
                          GROUP BY cve_articulo
                          ) X ON X.cve_articulo = td_backorder.Cve_Articulo
                          LEFT JOIN t_recorrido_surtido t ON t.fol_folio = th_backorder.Fol_Folio AND t.Cve_articulo = td_backorder.Cve_articulo AND t.cve_lote = td_backorder.Cve_Lote
                          WHERE th_backorder.Fol_Folio = '{$id}' 
                          GROUP BY clave ORDER BY t.orden_secuencia
                ";
            else
          $sql = "
              SELECT 
                  td_pedido.fol_folio AS folio,
                  #t_recorrido_surtido.Sufijo as sufijo,
                  0 as sufijo,
                  c.RazonSocial AS cliente,
                  td_pedido.Cve_articulo AS clave,
                  a.des_articulo AS articulo,
                    0 AS Pallet,
                    0 AS Caja, 
                    0 AS Piezas,
                    IFNULL(a.cajas_palet, 0) cajasxpallets,
                    IFNULL(a.num_multiplo, 0) piezasxcajas, 
                  ROUND((a.peso * td_pedido.Num_cantidad),4) AS peso,
                  ROUND(((a.alto/1000) * (a.ancho/1000) * (a.fondo/1000) * td_pedido.Num_cantidad),4) AS volumen,
                  td_pedido.Num_cantidad as Pedido_Total,
                  (SELECT sum(Existencia) as Existencia_Total FROM `VS_ExistenciaParaSurtido` WHERE VS_ExistenciaParaSurtido.cve_articulo = td_pedido.Cve_articulo GROUP BY cve_articulo)  as Existencia_Total
              FROM td_pedido
                  
                  LEFT JOIN c_articulo a ON a.cve_articulo = td_pedido.Cve_articulo
                  LEFT JOIN th_pedido ON th_pedido.Fol_folio = td_pedido.Fol_folio
                  LEFT JOIN c_cliente c ON c.Cve_Clte = th_pedido.Cve_clte
                  LEFT JOIN td_surtidopiezas s ON s.fol_folio = td_pedido.Fol_folio AND s.Cve_articulo = td_pedido.Cve_articulo AND s.cve_almac = th_pedido.cve_almac
                  LEFT JOIN (
                      Select 
                          V_ExistenciaGral.cve_articulo,
                          c_ubicacion.CodigoCSD,
                          min(V_ExistenciaGral.cve_ubicacion) as min 
                      FROM V_ExistenciaGral 
                          LEFT JOIN c_ubicacion on V_ExistenciaGral.cve_ubicacion = c_ubicacion.idy_ubica 
                      GROUP by cve_articulo
                  ) x ON x.cve_articulo = td_pedido.Cve_articulo
              WHERE td_pedido.Fol_folio = '$id'
          ";
            }

      $sth = \db()->prepare($sql);
      $sth->execute();

      $res[0]=$sth->fetchAll();
      $res[2]="status_pedido = ".$status_pedido."...SSSS".$sql;
      $res[3]=$status;
      
    }
//*********************************************************************************************************
//*********************************************************************************************************
          $sql = "
                SELECT pr.SKU AS cve_articulo, a.des_articulo, CONCAT('(', r.cve_ruta, ') ', r.descripcion) AS ruta, 
                        d.razonsocial AS cliente, pr.Cant, IFNULL(pr.Tipmed, '') AS unidad_medida
                FROM PRegalado pr 
                LEFT JOIN c_articulo a ON a.cve_articulo = pr.SKU
                LEFT JOIN t_ruta r ON r.ID_Ruta = pr.RutaId
                LEFT JOIN c_destinatarios d ON d.id_destinatario = pr.Cliente
                LEFT JOIN c_almacenp al ON al.clave = pr.IdEmpresa
                WHERE  al.id = {$almacen} AND ('$id' LIKE CONCAT('%',pr.Docto) OR pr.Docto IN (SELECT DISTINCT Fol_Folio FROM td_consolidado WHERE Fol_PedidoCon = '$id'));
          ";

      $sth = \db()->prepare($sql);
      $sth->execute();

      $res[4]=$sth->fetchAll();
//*********************************************************************************************************
//*********************************************************************************************************
    //$res["debug"][6]="6";
    //echo $sql;
    return $res;
  }
  
  function detallesPedidoCabecera($id_pedido, $id_almacen)
  {
      $sql = "
          SELECT 
              td_pedido.Fol_folio, 
              SUM(td_pedido.Num_cantidad) AS Cantidad_Pedida,
              if(
                  th_pedido.status = 'S',
                  sum((Select sum(t_recorrido_surtido.Cantidad) FROM t_recorrido_surtido WHERE t_recorrido_surtido.fol_folio = th_pedido.Fol_folio)),
                  SUM((SELECT sum(Existencia) as Existencia_Total FROM `VS_ExistenciaParaSurtido` WHERE VS_ExistenciaParaSurtido.cve_articulo = td_pedido.Cve_articulo GROUP BY cve_articulo))         	
              )as Existencia_Total
          FROM td_pedido 
              LEFT JOIN c_articulo a ON a.cve_articulo = td_pedido.Cve_articulo
              LEFT JOIN th_pedido ON th_pedido.Fol_folio = td_pedido.Fol_folio 
          WHERE td_pedido.Fol_folio = '{$id_pedido}' 
              AND th_pedido.cve_almac = '{$id_almacen}' 
          GROUP BY Fol_folio
      ";
      $sth = \db()->prepare($sql);
      $sth->execute();
      $res = $sth->fetchAll();
      return $res;
  }
  
  function articulo_surtir($cve_articulo)
  {
      $sql="
          select 
              ts_existenciapiezas.cve_articulo,
              c_articulo.des_articulo, 
              ts_existenciapiezas.Existencia,
              c_ubicacion.CodigoCSD,
              TRUNCATE(((c_articulo.alto/1000)*(c_articulo.ancho/1000)*(c_articulo.fondo/1000)),4) as volumen,
              c_articulo.peso
          from ts_existenciapiezas
              left join c_lotes on c_lotes.LOTE = ts_existenciapiezas.cve_lote
              inner join c_ubicacion on c_ubicacion.idy_ubica = ts_existenciapiezas.idy_ubica
              inner join c_articulo on c_articulo.cve_articulo = ts_existenciapiezas.cve_articulo
          WHERE ts_existenciapiezas.cve_articulo = {$cve_articulo}
          ORDER by c_lotes.CADUCIDAD asc;
      ";
      $sth = \db()->prepare( $sql );
      $sth->execute();
      return $sth->fetchAll();
  }

  /**
   * 
   */
  function loadUsers($articulos) 
  {
      $resultado_usuarios = array();
      $data = array();
      $data2 = array();
      $i = 0;
      $ids = array();
      foreach($articulos as $art)
      {
          $ids[] = "'".$art[3]."'";
      }
      $cve_articulo = implode(",",$ids);
    $sql = "";
      if((strpos($_SERVER['HTTP_HOST'], 'nikken') === false))
      {
          $sql = "
              SELECT 
                  td_ruta_surtido.idr, 
                  rel_usuario_ruta.id_usuario, 
                  c_usuario.cve_usuario, 
                  c_usuario.nombre_completo
              FROM `VS_ExistenciaParaSurtido`
                INNER JOIN td_ruta_surtido ON td_ruta_surtido.idy_ubica = VS_ExistenciaParaSurtido.Idy_Ubica 
                INNER JOIN rel_usuario_ruta ON rel_usuario_ruta.id_ruta = td_ruta_surtido.idr
                INNER JOIN c_usuario ON c_usuario.id_user = rel_usuario_ruta.id_usuario
              WHERE cve_articulo IN ({$cve_articulo})
              GROUP BY rel_usuario_ruta.id_usuario;
          ";
      }
      else{
          $sql = "
            SELECT
                c.id_user AS id_usuario,
                c.cve_usuario, 
                c.nombre_completo 
            FROM c_usuario c, V_PermisosUsuario v
            WHERE v.ID_PERMISO = 2 AND c.cve_usuario = v.cve_usuario
            AND c.Activo =1";
      }
/*
          $sql = "
            SELECT 
                c.id_user as id_usuario,
                c.cve_usuario, 
                c.nombre_completo 
            FROM c_usuario c
            WHERE c.Activo =1";
*/

      $sth = \db()->prepare($sql);
      $sth->execute();
      $resultado_usuarios = $sth->fetchAll();

      foreach($resultado_usuarios as $datos)
      {
          $data[] = $datos["nombre_completo"];
          $data2[] = $datos["cve_usuario"];
      }
      $arr = array(
          "nombre" => $data,
          "clave" => $data2
      );
      $arr = array_merge($arr);
      return ($arr);
  }

  function usuariosDisponiblesParaSurtir() 
  {
      $sql = "
          SELECT 
              u.id_user AS id_usuario,
              u.cve_usuario AS clave_usuario, 
              u.nombre_completo AS nombre_usuario
          FROM c_usuario u
          WHERE u.cve_usuario NOT IN  (SELECT s.cve_usuario FROM th_subpedido s WHERE s.status <> 'S') 
          AND u.Activo =1
      ";
      $sth = \db()->prepare( $sql );
      $sth->setFetchMode( \PDO::FETCH_CLASS, '\AdministradorPedidos\AdministradorPedidos' );
      $sth->execute( array( $user ) );
      return $sth->fetchAll();
  }

  function planificar($folio) {		
      $sql = "CALL SPRF_PreparaPedidoSurtido ('".$folio."');";
      $sth = \db()->prepare( $sql );      
      $sth->execute();
  }

  function saveAE( $data ) {
    $sql = sprintf(' UPDATE ' . self::TABLE . ' SET cve_ubicacion = :cve_ubicacion   WHERE Fol_folio = :Fol_folio ');
    $this->save = \db()->prepare($sql);
    $this->save->bindValue( ':cve_ubicacion', $data['cve_ubicacion'], \PDO::PARAM_STR );
    $this->save->bindValue( ':Fol_folio', $data['Fol_folio'], \PDO::PARAM_STR );
    $this->save->execute();
    if(!$this->data)
        return true; 
    else 
        return false;
  }

	function guardarOla($ola) 
  {	
	  	$sql ='INSERT INTO th_consolidado SET Fol_PedidoCon = "'.$ola['numero_ola'].'", Status = "P", Fec_Entrega = STR_TO_DATE("'.$ola['fec_entrega'].'", "%d-%m-%Y");';
      $this->save = \db()->prepare($sql);
      $this->save->execute();	
	}
  
  function guardarSubpedido($POST, $folio) 
  { 
      $sql = '
          INSERT INTO th_subpedido
          SET
              fol_folio = "'.$folio.'",
              cve_almac = (SELECT cve_almac FROM th_pedido WHERE Fol_folio = "'.$folio.'"),
              Sufijo = (SELECT sp.Sufijo+1 FROM th_subpedido sp ORDER BY sp.Sufijo DESC LIMIT 1),
              Fec_Entrada =  "'.$POST['fecha'].'",
              cve_usuario = "'.$POST['usuarios'][0][0].'",
              status = "T";
      ';
    
      $this->save = \db()->prepare($sql);
      $this->save->execute(); 
      $sql = '
          INSERT INTO td_subpedido
          SET
              fol_folio = "'.$folio.'",
              cve_almac = (SELECT cve_almac FROM th_pedido WHERE Fol_folio = "'.$folio.'"),
              Sufijo = (SELECT sp.Sufijo+1 FROM th_subpedido sp ORDER BY sp.Sufijo DESC LIMIT 1),
              Fec_Entrada =  "'.$POST['fecha'].'",
              cve_usuario = "'.$POST['usuarios'][0][0].'",
              status = "T";
      ';
  }
  
  function guardarSubpedidoTD($POST, $folio) 
  {  
    $sql = '
        INSERT INTO td_subpedido
        SET
            fol_folio = "'.$folio.'",
            cve_almac = (SELECT cve_almac FROM th_pedido WHERE Fol_folio = "'.$folio.'"),
            Sufijo = (SELECT sp.Sufijo FROM th_subpedido sp ORDER BY sp.Sufijo DESC LIMIT 1),
            Cve_articulo =  "'.$POST['articulo'].'",
            Num_cantidad =  "'.$POST['pedidas'].'",
            Nun_Surtida =  "'.$POST['surtidas'].'",
            Status = "A";
    ';
    $this->save = \db()->prepare($sql);
    $this->save->execute();
  }

	function guardarOlaPedido_T($numeroOla,$folio) 
  {	
  		$sql = ' INSERT INTO t_consolidado SET Fol_PedidoCon = "'.$numeroOla.'", Fol_Folio = "'.$folio.'" ';
	  	$this->save = \db()->prepare($sql);
      $this->save->execute();	
	}
	
	function guardarOlaPedido_TD($numeroOla,$folio) 
  {
  		$sql = ' INSERT INTO td_consolidado SET Fol_PedidoCon = "'.$numeroOla.'", Fol_Folio = "'.$folio.'" ';
  		$this->save = \db()->prepare($sql);
      $this->save->execute();	
	}
	
	function actualizarPedidoOla($id,$usuario) 
  {		
      $sql = "UPDATE th_pedido SET status = 'B', Cve_Usuario = '".$usuario."' WHERE  id_pedido='".$id."';";
      $this->save = \db()->prepare($sql);
      $this->save->execute(); 
	}
	
	function agregarOlaComoPedido($ola) 
  {		
	  	$sql = '
          INSERT INTO th_pedido
          SET
              Fec_Pedido = now(),
              Fol_Folio = "'.$ola['numero_ola'].'",
              status = "A",
              Fec_Entrega = "'.$ola['fec_entrega'].'"
		  ';
  		$this->save = \db()->prepare($sql);
      $this->save->execute();		
	}
	
	function asignar($sql) 
    {
      $res = mysqli_multi_query(\db2(), $sql);
      while ($row = mysqli_fetch_array($res))
      {   
          $res2[] = $row; 
      }
      return array($res2,$sql);
	}

	/*
	function guardarOla($ola) 
  {	
	  	$sql ='
          INSERT INTO th_ola
          SET
              numero_ola = "'.$ola['numero_ola'].'",
              status = "P",
              fec_entrega = "'.$ola['fec_entrega'].'",
              fec_asignacion = "'.$ola['fec_asignacion'].'",
              id_ruta = '.$ola['id_ruta'].',
              clave_usuario = "'.$ola['usuarios'][0][0].'";
      ';
  		$this->save = \db()->prepare($sql);
      $this->save->execute();	
	}
	
	function guardarOlaPedido($numeroOla,$folio) 
  {	
	  	$sql = sprintf(' INSERT INTO th_ola_pedido SET numero_ola = "'.$numeroOla.'", Fol_folio = "'.$folio.'" ');
  		$this->save = \db()->prepare($sql);
      $this->save->execute();	
	}*/
    function consecutivo_folio_backorder() 
    {
      $sql = "SELECT IF(MONTH(CURRENT_DATE()) < 10, CONCAT(0, MONTH(CURRENT_DATE())), MONTH(CURRENT_DATE())) AS mes, YEAR(CURRENT_DATE()) AS _year FROM DUAL";
      $sth = \db()->prepare( $sql );
      $sth->execute();
      $fecha = $sth->fetch();

      $mes  = $fecha['mes'];
      $year = $fecha['_year'];


      $count = 1;
      while(true)
      {
          if($count < 10)
            $count = "0".$count;

          $folio_next = "BO".$year.$mes.$count;
          $sql = "SELECT COUNT(*) as Consecutivo FROM th_backorder WHERE Folio_BackO = '$folio_next'";
          $sth = \db()->prepare( $sql );
          $sth->execute();
          $data = $sth->fetch();

          if($data["Consecutivo"] == 0)
            break;
          else
          {
              $count += 0; //convirtiendo a entero
              $count++;
          }
      }

      return $folio_next;
    }

    function fecha_actual() 
    {
      $sql = "SELECT NOW() fecha_actual FROM DUAL";
      $sth = \db()->prepare( $sql );
      $sth->execute();
      $fecha = $sth->fetch();

      $fecha_actual  = $fecha['fecha_actual'];

      return $fecha_actual;
    }

}
