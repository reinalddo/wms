<?php

  /**
    * protocolos
  **/

  $app->map('/administradorpedidos', function() use ($app) {

    $app->render( 'page/administradorpedidos/index.php' );

  })->via( 'GET', 'POST' );


  /**
    * Lists
  **/

  $app->map('/administradorpedidos/lists', function() use ($app) {

    $app->render( 'page/administradorpedidos/lists.php' );

  })->via( 'GET', 'POST' );


  /**
    * Pending
  **/

  $app->map('/administradorpedidos/pending', function() use ($app) {

    $app->render( 'page/administradorpedidos/pending.php' );

  })->via( 'GET', 'POST' );


  /**
    * Edit Subscriber
  **/

  $app->map('/administradorpedidos/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
      'id_subscriber' => $id_subscriber
    ) );

  })->via( 'GET', 'POST' );

  /**
    * View Subscriber
  **/

  $app->map('/administradorpedidos/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/administradorpedidos/view.php', array(
      'id_subscriber' => $id_subscriber
    ) );

  })->via( 'GET', 'POST' );


  $app->post('/administradorpedidos/consolidado/pdf', function() use ($app){
    var_dump($_POST);
  });
  $app->post('/administradorpedidos/consolidado/excel', function() use ($app){
    include dirname(__DIR__).'/vendor/mk-j/php_xlsxwriter/xlsxwriter.class.php';
    $title = 'Consolidado.xls';
    $folios = $_POST['folio'];
    $header = array(
        'Clave'          => 'string',
        'Artículo'       => 'string',
        'Pedidas'        => 'string',
        'Surtidas'       => 'string'
    );
    $excel = new XLSXWriter();
    $excel->writeSheetHeader('Sheet1', $header );
    $sql = "SELECT
                    c.Fol_PedidoCon AS folio,
                    c.Cve_Articulo AS clave,
                    IFNULL(a.des_articulo, '--') AS articulo,
                    IFNULL(c.Cant_Pedida, 0) AS pedidas,
                    IFNULL(p.Num_cantidad,0) AS surtidas
            FROM td_consolidado c
            LEFT JOIN c_articulo a ON a.cve_articulo = c.Cve_Articulo
            LEFT JOIN td_pedido p ON p.Fol_folio = c.Fol_PedidoCon AND p.Cve_articulo = c.Cve_Articulo
            WHERE c.Fol_PedidoCon IN ({$folios})
            ORDER BY c.Fol_PedidoCon, c.Cve_Articulo;";
    $query = mysqli_query(\db2(), $sql);
    if($query->num_rows > 0){
        while ($res = mysqli_fetch_array($query)){
            extract($res);
            $row = array(
                $clave,
                $articulo,
                $pedidas,
                $surtidas
            );
            $excel->writeSheetRow('Sheet1', $row );
        }
    }
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $title . '"');
    header('Cache-Control: max-age=0');
    $excel->writeToStdOut($title);
    $app->stop();
  });
  $app->post('/administradorpedidos/surtido/pdf', function() use ($app){
    var_dump($_POST);
  });
  $app->post('/administradorpedidos/surtido/excel', function() use ($app){
    include dirname(__DIR__).'/vendor/mk-j/php_xlsxwriter/xlsxwriter.class.php';
    $title = 'Orden de Surtido.xls';
    $folios = $_POST['folio'];
    $header = array(
        'Familia'     => 'string',
        'Clave'       => 'string',
        'Artículo'    => 'string',
        'Lote'        => 'string',
        'Ubicación'   => 'string',
        'Existencias' => 'string',
        'Pedidas'     => 'string',
        'Cajas'       => 'string'
    );
    $excel = new XLSXWriter();
    $excel->writeSheetHeader('Sheet1', $header );
    $sql = "SELECT
                    p.Fol_folio AS folio,
                    '--' AS familia,
                    p.Cve_articulo AS clave,
                    IFNULL(a.des_articulo, '--') AS articulo,
                    IFNULL(p.cve_lote, '--') AS lote,
                    (SELECT cve_ubicacion FROM th_pedido WHERE Fol_folio = p.Fol_folio) AS ubicacion,
                    IFNULL(p.Num_cantidad * p.SurtidoXCajas, 0) AS existencias,
                    '0' AS pedidas,
                    IFNULL(p.Num_cantidad, 0) AS cajas
            FROM td_pedido p
            LEFT JOIN c_articulo a ON a.cve_articulo = p.Cve_articulo
            WHERE p.Fol_folio IN ({$folios})
            ORDER BY p.Fol_folio, p.Cve_articulo;";
    $query = mysqli_query(\db2(), $sql);
    if($query->num_rows > 0){
        while ($res = mysqli_fetch_array($query)){
            extract($res);
            $row = array(
                $familia,
                $clave,
                $articulo,
                $lote,
                $ubicacion,
                $existencias,
                $pedidas,
                $cajas
            );
            $excel->writeSheetRow('Sheet1', $row );
        }
    }
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $title . '"');
    header('Cache-Control: max-age=0');
    $excel->writeToStdOut($title);
    $app->stop();
  });
