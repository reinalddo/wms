<?php

require_once __DIR__ . '/host.php';

  /**
    * Log out
  **/

  $app->get('/account/out', function() use ($app) {
    $id = $_SESSION['id_user'];
    $sql = 'DELETE FROM `users_online` WHERE id_usuario = '.$id.';';
    $data = mysqli_query(\db2(), $sql);

    if(isset($_SESSION['cve_usuario']))
    {
        if($_SESSION['cve_usuario'] != 'wmsmaster')
        {
            $sql = 'SELECT id FROM users_bitacora WHERE cve_usuario = "'.$_SESSION['cve_usuario'].'" ORDER BY id DESC LIMIT 1';
            $data = mysqli_query(\db2(), $sql);
            $id = mysqli_fetch_assoc($data);
            $id = $id['id'];

            $sql = 'UPDATE users_bitacora SET fecha_cierre = NOW(), sesion_cerrada = 1 WHERE cve_usuario = "'.$_SESSION['cve_usuario'].'" AND id = '.$id.';';
            $data = mysqli_query(\db2(), $sql);
        }
    }

    session_destroy();
    $app->redirect( '/login' );

  });

  /**
    * Update data settings
  **/

  $app->map('/settings', function() use ($app) {

    $app->render( 'page/settings/index.php' );

  })->via( 'GET', 'POST' );

  $app->map('/dispositivos', function() use ($app) {

    $app->render( 'page/dispositivos/index.php' );

  })->via( 'GET', 'POST' );

  $app->map('/acercade', function() use ($app) {

    $app->render( 'page/acercade/index.php' );

  })->via( 'GET', 'POST' );


  /**
    * Update design settings
  **/

  $app->map('/settings/design', function() use ($app) {

    $app->render( 'page/settings/design.php' );

  })->via( 'GET', 'POST' );



  /**
    * Payments
  **/

  $app->map('/settings/payments', function() use ($app) {

    $app->render( 'page/settings/payment.php' );

  })->via( 'GET', 'POST' );

?><?php

/**
 * clientes
 **/

$app->map('/acomodo', function() use ($app) {

    $app->render( 'page/acomodo/index.php' );

})->via( 'GET', 'POST' );


/**
 * Lists
 **/

$app->map('/acomodo/lists', function() use ($app) {

    $app->render( 'page/acomodo/lists.php' );

})->via( 'GET', 'POST' );


$app->map('/acomodo/report', function() use ($app) {

    $app->render( 'page/acomodo/report.php' );

})->via( 'GET', 'POST' );
/**
 * Pending
 **/

$app->map('/acomodo/pending', function() use ($app) {

    $app->render( 'page/acomodo/pending.php' );

})->via( 'GET', 'POST' );


/**
 * Edit Subscriber
 **/

$app->map('/acomodo/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );

/**
 * View Subscriber
 **/

$app->map('/acomodo/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/acomodo/view.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );
?><?php

  /**
    * protocolos
  **/

  $app->map('/adminembarques', function() use ($app) {

    $app->render( 'page/adminembarques/index.php' );

  })->via( 'GET', 'POST' );


  /**
    * Lists
  **/

  $app->map('/adminembarques/lists', function() use ($app) {

    $app->render( 'page/adminembarques/lists.php' );

  })->via( 'GET', 'POST' );


  /**
    * Pending
  **/

  $app->map('/adminembarques/pending', function() use ($app) {

    $app->render( 'page/adminembarques/pending.php' );

  })->via( 'GET', 'POST' );


  /**
    * Edit Subscriber
  **/

  $app->map('/adminembarques/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
      'id_subscriber' => $id_subscriber
    ) );

  })->via( 'GET', 'POST' );

  /**
    * View Subscriber
  **/

  $app->map('/adminembarques/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/adminembarques/view.php', array(
      'id_subscriber' => $id_subscriber
    ) );

  })->via( 'GET', 'POST' );
?><?php

/**
 * clientes
 **/

$app->map('/adminentrada', function() use ($app) {

    $app->render( 'page/adminentrada/index.php' );

})->via( 'GET', 'POST' );


/**
 * Lists
 **/

$app->map('/adminentrada/lists', function() use ($app) {

    $app->render( 'page/adminentrada/lists.php' );

})->via( 'GET', 'POST' );


$app->map('/adminentrada/report', function() use ($app) {

    $app->render( 'page/adminentrada/report.php' );

})->via( 'GET', 'POST' );
/**
 * Pending
 **/

$app->map('/adminentrada/pending', function() use ($app) {

    $app->render( 'page/adminentrada/pending.php' );

})->via( 'GET', 'POST' );


/**
 * Edit Subscriber
 **/

$app->map('/adminentrada/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );

/**
 * View Subscriber
 **/

$app->map('/adminentrada/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/adminentrada/view.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );
?><?php 
/*
$app->map('/administracionubicaciones', function() use ($app) {
    $app->render( 'page/administracionubicaciones/lists.php' );
})->via( 'GET', 'POST' );
*/
$app->map('/administracionubicaciones', function() use ($app) {
    $app->render( 'page/rutasacomodo/lists.php' );
})->via( 'GET', 'POST' );

$app->map('/administracionrecepcion', function() use ($app) {
    $app->render( 'page/administracionrecepcion/lists.php' );
})->via( 'GET', 'POST' );

$app->map('/administracionembarque', function() use ($app) {
    
    //if( isNikken() ){
        $app->render( 'page/administracionembarque/lists_nikken.php' );
    //} else {
    //    $app->render( 'page/administracionembarque/lists.php' );
    //}

})->via( 'GET', 'POST' );
////////////////////////////////////////////////////////
$app->map('/crossdock/lists', function() use ($app) {
    $app->render( 'page/crossdock/lists.php' );
  })->via( 'GET', 'POST' );


?><?php

$app->get('/api/v2/pedidos', 'Application\Controllers\PedidosController:paginate');
$app->get('/api/v2/pedidosStatus', 'Application\Controllers\PedidosController:paginateStatus');


$app->post('/api/v2/pedidos/crearConsolidadoDeOla', 'Application\Controllers\PedidosController:crearConsolidadoDeOla');
$app->post('/api/v2/consolidadoOla/items', 'Application\Controllers\PedidosController:detallesConsolidado');
$app->delete('/api/v2/consolidadosOla', 'Application\Controllers\PedidosController:borrarConsolidado');


$app->post('/pedidos/importar', 'Application\Controllers\PedidosController:importar');
$app->post('/pedidos/importarOlas', 'Application\Controllers\PedidosController:importarOlas');
$app->post('/pedidos/importarw', 'Application\Controllers\PedidosController:importar_welldex');
$app->post('/pedidos/importarth', 'Application\Controllers\PedidosController:ImportarTH');
$app->post('/pedidos/importarLP', 'Application\Controllers\PedidosController:ImportarLP');
$app->post('/pedidos/importarLPw', 'Application\Controllers\PedidosController:ImportarLP_welldex');
$app->get('/api/v2/pedidos/exportar-cabecera', 'Application\Controllers\PedidosController:exportarCabecera');
$app->get('/api/v2/pedidos/exportar-detalles(/:id)', 'Application\Controllers\PedidosController:exportarDetalles');

$app->get('/api/v2/crossdock', 'Application\Controllers\CrossdockController:paginate');


$app->post('/api/v2/crossdock/crearConsolidadoDeOla', 'Application\Controllers\CrossdockController:crearConsolidadoDeOla');
$app->post('/api/v2/consolidadoOla/items', 'Application\Controllers\CrossdockController:detallesConsolidado');
$app->delete('/api/v2/consolidadosOla', 'Application\Controllers\CrossdockController:borrarConsolidado');


$app->post('/crossdock/importar', 'Application\Controllers\CrossdockController:importar');
$app->get('/api/v2/crossdock/exportar-cabecera', 'Application\Controllers\CrossdockController:exportarCabecera');
$app->get('/api/v2/crossdock/exportar-detalles(/:id)', 'Application\Controllers\CrossdockController:exportarDetalles');




/*
$app->post('/pedidos/importar', 'Application\Controllers\PedidosController:importar_pedidos');
$app->get('/api/v2/pedidos/exportar-cabecera', 'Application\Controllers\PedidosController:exportarCabecera');
$app->get('/api/v2/pedidos/exportar-detalles(/:id)', 'Application\Controllers\PedidosController:exportarDetalles');
*/

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

  $app->map('/statuspedidos/lists', function() use ($app) {

    $app->render( 'page/statuspedidos/lists.php' );

  })->via( 'GET', 'POST' );



  /**
    * Pending
  **/

  $app->map('/administradorpedidos/pending', function() use ($app) {

    $app->render( 'page/administradorpedidos/pending.php' );

  })->via( 'GET', 'POST' );


  $app->map('/listadeprecios/list', function() use ($app) {

    $app->render( 'page/listadeprecios/lists.php' );

  })->via( 'GET', 'POST' );

  $app->map('/listadescuentos/list', function() use ($app) {

    $app->render( 'page/listadescuentos/lists.php' );

  })->via( 'GET', 'POST' );

  $app->map('/promociones/list', function() use ($app) {

    $app->render( 'page/listapromociones/lists.php' );

  })->via( 'GET', 'POST' );

$app->map('/grupopromociones/list', function() use ($app) {

    $app->render( 'page/listapromociones/grupos.php' );

})->via( 'GET', 'POST' );

$app->map('/formasdepago/list', function() use ($app) {

    $app->render( 'page/formasdepago/list.php' );

})->via( 'GET', 'POST' );

$app->map('/sfa/tickets', function() use ($app) {

    $app->render( 'page/ticket/index.php' );

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
?><?php

/**
 * adminpedidos
 **/

$app->map('/adminpedidos', function() use ($app) {

    $app->render( 'page/adminpedidos/index.php' );

})->via( 'GET', 'POST' );


/**
 * Lists
 **/

$app->map('/adminpedidos/lists', function() use ($app) {

    $app->render( 'page/adminpedidos/lists.php' );

})->via( 'GET', 'POST' );


/**
 * Pending
 **/

$app->map('/adminpedidos/pending', function() use ($app) {

    $app->render( 'page/adminpedidos/pending.php' );

})->via( 'GET', 'POST' );


/**
 * Edit Subscriber
 **/

$app->map('/adminpedidos/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );

/**
 * View Subscriber
 **/

$app->map('/adminpedidos/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/adminpedidos/view.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );
?><?php

  /**
    * adminrutasurtido
  **/

  $app->map('/adminrutasurtido', function() use ($app) {

    $app->render( 'page/adminrutasurtido/index.php' );

  })->via( 'GET', 'POST' );


  /**
    * Lists
  **/

  $app->map('/adminrutasurtido/lists', function() use ($app) {

    $app->render( 'page/adminrutasurtido/lists.php' );

  })->via( 'GET', 'POST' );


  /**
    * Pending
  **/

  $app->map('/adminrutasurtido/pending', function() use ($app) {

    $app->render( 'page/adminrutasurtido/pending.php' );

  })->via( 'GET', 'POST' );


  /**
    * Edit Subscriber
  **/

  $app->map('/adminrutasurtido/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
      'id_subscriber' => $id_subscriber
    ) );

  })->via( 'GET', 'POST' );

  /**
    * View Subscriber
  **/

  $app->map('/adminrutasurtido/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/adminrutasurtido/view.php', array(
      'id_subscriber' => $id_subscriber
    ) );

  })->via( 'GET', 'POST' );
?><?php

/**
 * clientes
 **/


$app->map('/ajustedeentradas', function() use ($app) {

    $app->render( 'page/ajustedeentradas/index.php' );

})->via( 'GET', 'POST' );


/**
 * Lists
 **/

$app->map('/ajustedeentradas/lists', function() use ($app) {

    $app->render( 'page/ajustedeentradas/lists.php' );

})->via( 'GET', 'POST' );


/**
 * Pending
 **/

$app->map('/ajustedeentradas/pending', function() use ($app) {

    $app->render( 'page/ajustedeentradas/pending.php' );

})->via( 'GET', 'POST' );


/**
 * Edit Subscriber
 **/

$app->map('/ajustedeentradas/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );

/**
 * View Subscriber
 **/

$app->map('/ajustedeentradas/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/ajustedeentradas/view.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );
?><?php 

$app->map('/ajustes/codigocsd', function() use ($app) {

    $app->render( 'page/ajustes/codigocsd.php' );

})->via( 'GET');

$app->map('/ajustes/instancia', function() use ($app) {

    $app->render( 'page/ajustes/instancia.php' );

})->via( 'GET');

$app->map('/ajustes/configuraciongeneral', function() use ($app) {

    $app->render( 'page/ajustes/configuraciongeneral.php' );

})->via( 'GET');

$app->map('/ajustes/sfa', function() use ($app) {

    $app->render( 'page/ajustes/sfa.php' );

})->via( 'GET');

?><?php

/**
 * ajustesexistencias
 **/

$app->map('/ajustesexistencias', function() use ($app) {

    $app->render( 'page/ajustesexistencias/index.php' );

})->via( 'GET', 'POST' );


/**
 * Lists
 **/

$app->map('/ajustesexistencias/lists', function() use ($app) {

    $app->render( 'page/ajustesexistencia/lists.php' );

})->via( 'GET', 'POST' );


$app->map('/controlcuarentena/lists', function() use ($app) {

    $app->render( 'page/controlcuarentena/lists.php' );

})->via( 'GET', 'POST' );

$app->map('/controlcalidad/lists', function() use ($app) {

    $app->render( 'page/controlcalidad/lists.php' );

})->via( 'GET', 'POST' );

$app->map('/adminajusteexis/lists', function() use ($app) {

    $app->render( 'page/adminajusteexis/lists.php' );

})->via( 'GET', 'POST' );



/**
 * Pending
 **/

$app->map('/ajustesexistencias/pending', function() use ($app) {

    $app->render( 'page/ajustesexistencias/pending.php' );

})->via( 'GET', 'POST' );


/**
 * Edit Subscriber
 **/

$app->map('/ajustesexistencias/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );

/**
 * View Subscriber
 **/

$app->map('/ajustesexistencias/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/ajustesexistencias/view.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );
?><?php

/**
    * protocolos
  **/

$app->map('/almacen', function() use ($app) {

  $app->render( 'page/almacen/index.php' );

})->via( 'GET', 'POST' );



/*
* importar
*/

$app->post('/zona-de-almacenaje/importar', 'Application\Controllers\ZonasDeAlmacenajeController:importar');
$app->get('/api/v2/zona-de-almacenaje/exportar', 'Application\Controllers\ZonasDeAlmacenajeController:exportar');




$app->post('/almacen/import', function() use($app){
  /* Archivo Excel del formulario */
  $file = $_FILES['layout']['tmp_name'];
  /* Creando lector excel */
  $inputFileType = PHPExcel_IOFactory::identify($file);
  $objReader = PHPExcel_IOFactory::createReader($inputFileType);
  $objPHPExcel = $objReader->load($file);
  /* Obteniendo hoja activa */
  $sheet = $objPHPExcel->getActiveSheet();
  /* Obteniendo identificador de última columna y fila */
  $sheetInfo = $sheet->getHighestRowAndColumn();
  $highestRow = $sheetInfo['row'];
  $highestColumn = $sheetInfo['column'];
  /* Empezamos en A2 ya que A1 corresponde a los titulos */
  $sheetRange = "A2:$highestColumn$highestRow";
  $rowData = $sheet->rangeToArray($sheetRange);

  /* Guardando sql para ejecutarlos juntos */
  $insert = "";
  /* Leyendo cada celda del excel */
  foreach($rowData as $cell){
    if($cell[3]!=''){
      $data = [
        "c_almacen.clave_almacen"      =>  $cell[0],
        "c_almacen.cve_almacenp"      =>  $cell[1],
        "c_almacen.des_almac"       =>  $cell[2],
        "c_almacen.des_direcc"        =>  $cell[3]
      ];
    }
    else
    {
      $data = [
        "c_almacen.clave_almacen"      =>  $cell[0],
        "c_almacen.cve_almacenp"      =>  $cell[1],
        "c_almacen.des_almac"       =>  $cell[2]
      ];
    }
    $last_key = key( array_slice( $data, -1, 1, TRUE ) );
    $insertStm = "INSERT IGNORE INTO c_almacen SET ";
    $set = "";
    foreach($data as $setName => $setValue){
      if(!empty($setValue)){
        $set .= " $setName = \"$setValue\"";
        if($setName !== $last_key){
          $set .= ",";
        }
      }
    }
    $insertStm .= $set."; ";
    $insert .= $insertStm;
  }
  $query = mysqli_multi_query(\db2(), $insert);
  if($query){
    $app->response->redirect('/almacen/lists');
  }

});


/**
    * Lists
  **/

$app->map('/almacen/lists', function() use ($app) {

  $app->render( 'page/almacen/lists.php' );

})->via( 'GET', 'POST' );


/**
    * Pending
  **/

$app->map('/almacen/pending', function() use ($app) {

  $app->render( 'page/almacen/pending.php' );

})->via( 'GET', 'POST' );


/**
    * Edit Subscriber
  **/

$app->map('/almacen/edit/:id', function( $id_subscriber ) use ($app) {

  $app->render( 'page/subscribers/edit.php', array(
    'id_subscriber' => $id_subscriber
  ) );

})->via( 'GET', 'POST' );

/**
    * View Subscriber
  **/

$app->map('/almacen/view/:id', function( $id_subscriber ) use ($app) {

  $app->render( 'page/almacen/view.php', array(
    'id_subscriber' => $id_subscriber
  ) );

})->via( 'GET', 'POST' );
?><?php

  /**
    * protocolos
  **/

  $app->map('/almacenp', function() use ($app) {

    $app->render( 'page/almacenp/index.php' );

  })->via( 'GET', 'POST' );


  /**
    * Lists
  **/

  $app->map('/almacenp/lists', function() use ($app) {

    $app->render( 'page/almacenp/lists.php' );

  })->via( 'GET', 'POST' );


  /**
    * Pending
  **/

  $app->map('/almacenp/pending', function() use ($app) {

    $app->render( 'page/almacenp/pending.php' );

  })->via( 'GET', 'POST' );


  /**
    * Edit Subscriber
  **/

  $app->map('/almacenp/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
      'id_subscriber' => $id_subscriber
    ) );

  })->via( 'GET', 'POST' );

  /**
    * View Subscriber
  **/

  $app->map('/almacenp/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/almacenp/view.php', array(
      'id_subscriber' => $id_subscriber
    ) );

  })->via( 'GET', 'POST' );
?><?php

/**
 * clientes
 **/

$app->map('/areaembarque', function() use ($app) {

    $app->render( 'page/areaembarque/index.php' );

})->via( 'GET', 'POST' );


/*
* importar
*/

$app->post('/area-de-embarque/importar', 'Application\Controllers\AreaDeEmbarqueController:importar');
$app->get('/api/v2/area-de-embarque/exportar', 'Application\Controllers\AreaDeEmbarqueController:exportar');


$app->post('/areaembarque/import', function() use($app){
    /* Archivo Excel del formulario */
    $file = $_FILES['layout']['tmp_name'];
    /* Creando lector excel */
    $inputFileType = PHPExcel_IOFactory::identify($file);
    $objReader = PHPExcel_IOFactory::createReader($inputFileType);
    $objPHPExcel = $objReader->load($file);
    /* Obteniendo hoja activa */
    $sheet = $objPHPExcel->getActiveSheet();
    /* Obteniendo identificador de última columna y fila */
    $sheetInfo = $sheet->getHighestRowAndColumn();
    $highestRow = $sheetInfo['row'];
    $highestColumn = $sheetInfo['column'];
    /* Empezamos en A2 ya que A1 corresponde a los titulos */
    $sheetRange = "A2:$highestColumn$highestRow";
    $rowData = $sheet->rangeToArray($sheetRange);

    /* Guardando sql para ejecutarlos juntos */
    $insert = "";
    /* Leyendo cada celda del excel */
    foreach($rowData as $cell){
        $data = [
            "t_ubicacionembarque.cve_ubicacion"      =>  $cell[0],
            "t_ubicacionembarque.cve_almac"      =>  $cell[1],
            "t_ubicacionembarque.status"       =>  $cell[2],
            "t_ubicacionembarque.descripcion"        =>  $cell[3]
        ];
        $last_key = key( array_slice( $data, -1, 1, TRUE ) );
        $insertStm = "INSERT IGNORE INTO t_ubicacionembarque SET ";
        $set = "";
        foreach($data as $setName => $setValue){
            if(!empty($setValue)){
                $set .= " $setName = \"$setValue\"";
                 if($setName !== $last_key){
                    $set .= ",";
                }
            }
        }
        $insertStm .= $set."; ";
        $insert .= $insertStm;
    }
    $query = mysqli_multi_query(\db2(), $insert);
    if($query){
        $app->response->redirect('/areaembarque/lists');
    }
});


/**
 * Lists
 **/

$app->map('/areaembarque/lists', function() use ($app) {

    $app->render( 'page/areaembarque/lists.php' );

})->via( 'GET', 'POST' );


/**
 * Pending
 **/

$app->map('/areaembarque/pending', function() use ($app) {

    $app->render( 'page/areaembarque/pending.php' );

})->via( 'GET', 'POST' );


/**
 * Edit Subscriber
 **/

$app->map('/areaembarque/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );

/**
 * View Subscriber
 **/

$app->map('/areaembarque/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/areaembarque/view.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );
?><?php

/**
 * clientes
 **/

$app->map('/arearevision', function() use ($app) {

    $app->render( 'page/arearevision/index.php' );

})->via( 'GET', 'POST' );



/*
* importar
*/

$app->post('/area-de-revision/importar', 'Application\Controllers\AreaDeRevisionController:importar');
$app->get('/api/v2/area-de-revision/exportar', 'Application\Controllers\AreaDeRevisionController:exportar');



$app->post('/arearevision/import', function() use($app){
    /* Archivo Excel del formulario */
    $file = $_FILES['layout']['tmp_name'];
    /* Creando lector excel */
    $inputFileType = PHPExcel_IOFactory::identify($file);
    $objReader = PHPExcel_IOFactory::createReader($inputFileType);
    $objPHPExcel = $objReader->load($file);
    /* Obteniendo hoja activa */
    $sheet = $objPHPExcel->getActiveSheet();
    /* Obteniendo identificador de última columna y fila */
    $sheetInfo = $sheet->getHighestRowAndColumn();
    $highestRow = $sheetInfo['row'];
    $highestColumn = $sheetInfo['column'];
    /* Empezamos en A2 ya que A1 corresponde a los titulos */
    $sheetRange = "A2:$highestColumn$highestRow";
    $rowData = $sheet->rangeToArray($sheetRange);

    /* Guardando sql para ejecutarlos juntos */
    $insert = "";
    /* Leyendo cada celda del excel */
    foreach($rowData as $cell){
        $data = [
            "t_ubicaciones_revision.cve_almac"      =>  $cell[0],
            "t_ubicaciones_revision.cve_ubicacion"      =>  $cell[1],
            "t_ubicaciones_revision.fol_folio"       =>  $cell[2],
            "t_ubicaciones_revision.sufijo"        =>  $cell[3],
            "t_ubicaciones_revision.Checado"        =>  $cell[4],
            "t_ubicaciones_revision.descripcion"        =>  $cell[5]
        ];
        $last_key = key( array_slice( $data, -1, 1, TRUE ) );
        $insertStm = "INSERT IGNORE INTO t_ubicaciones_revision SET ";
        $set = "";
        foreach($data as $setName => $setValue){
            if(!empty($setValue)){
                $set .= " $setName = \"$setValue\"";
                if($setName !== $last_key){
                    $set .= ",";
                }
            }
        }
        $insertStm .= $set."; ";
        $insert .= $insertStm;
    }
    $query = mysqli_multi_query(\db2(), $insert);
    if($query){
        $app->response->redirect('/arearevision/lists');
    }
});

/**
 * Lists
 **/

$app->map('/arearevision/lists', function() use ($app) {

    $app->render( 'page/arearevision/lists.php' );

})->via( 'GET', 'POST' );


/**
 * Pending
 **/

$app->map('/arearevision/pending', function() use ($app) {

    $app->render( 'page/arearevision/pending.php' );

})->via( 'GET', 'POST' );


/**
 * Edit Subscriber
 **/

$app->map('/arearevision/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );

/**
 * View Subscriber
 **/

$app->map('/arearevision/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/arearevision/view.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );
?><?php

/**
 * clientes
 **/

$app->map('/articulos', function() use ($app) {

    $app->render( 'page/articulos/index.php' );

})->via( 'GET', 'POST' );

/*
* importar
*/

$app->get('/api/v2/articulos/exportar', 'Application\Controllers\ArticulosController:exportar');
$app->get('/api/v2/BOM_Articulos/exportartodo', 'Application\Controllers\OrdenDeProduccionController:exportarBOMTodos');
$app->get('/api/v2/ocpendientes/exportar', 'Application\Controllers\OrdenesDeCompraController:exportar');
$app->post('/articulos/importar', 'Application\Controllers\ArticulosController:importar');
$app->post('/comparativa/importar', 'Application\Controllers\ComparativaController:importar');
$app->get('/api/v2/presupuestos/exportar', 'Application\Controllers\PresupuestosController:exportar');
$app->post('/presupuestos/importar', 'Application\Controllers\PresupuestosController:importar');

$app->post('/entradasrl/importar', 'Application\Controllers\EntradasRLController:importarRL');
$app->post('/entradasrl/importarW', 'Application\Controllers\EntradasRLController:importarRLW');
$app->post('/embarques/importarfotosth', 'Application\Controllers\EmbarquesController:importarFotosTH');
$app->post('/embarques/archivosdocumentos', 'Application\Controllers\EmbarquesController:DocumentosEmbarque');
$app->post('/articulos/archivosdocumentos', 'Application\Controllers\ArticulosController:DocumentosArticulo');
$app->post('/maximosyminimos/importar', 'Application\Controllers\ArticulosController:importarMyM');
$app->post('/listadeprecios/importar', 'Application\Controllers\ListaDePreciosController:importarLP');
$app->get('/listadeprecios/exportarsfa', 'Application\Controllers\ListaDePreciosController:exportar_ventas_sfa');
//$app->post('/ordenescompra/importar', 'Application\Controllers\OrdenesDeCompraController:importarOC');
$app->post('/ordenescompra/importar', 'Application\Controllers\OrdenesDeCompraController:ImportarOCMasivo');
$app->post('/ordenescompra/importarasl', 'Application\Controllers\OrdenesDeCompraController:importarOC_ASL');
$app->post('/ordenescompra/importarfotosth', 'Application\Controllers\OrdenesDeCompraController:importarFotosTHOC');
$app->post('/ordenescompra/importarfotostd', 'Application\Controllers\OrdenesDeCompraController:importarFotosTDOC');
$app->post('/ordenescompra/importarocentradas', 'Application\Controllers\OrdenesDeCompraController:importarocentradas');

$app->get('/embarques/exportar', 'Application\Controllers\EmbarquesController:exportar');

$app->get('/concentrado/exportar_excel', 'Application\Controllers\InventarioController:exportar_concentrado');
$app->post('/traslados/importar', 'Application\Controllers\InventarioController:TrasladosMasivos');

$app->get('/comparativo/exportar_excel', 'Application\Controllers\InventarioController:exportar_comparativo');

$app->get('/existenciaubicacion/exportar_excel', 'Application\Controllers\InventarioController:exportar_existenciaubica');
$app->get('/kardex/reporte_kardex', 'Application\Controllers\InventarioController:exportar_kardex');
$app->get('/inventario/conteos', 'Application\Controllers\InventarioController:inventario_conteo');
$app->get('/inventario/consolidado', 'Application\Controllers\InventarioController:inventario_consolidado');
/*exportar en Administracion de pedidos
$app->get('pedidos/exportar', 'Application\Controllers\PedidosController:exportarPedidos');
*/
/**
 * Lists
 **/

$app->map('/articulos/lists', function() use ($app) {

    $app->render( 'page/articulos/lists.php' );

})->via( 'GET', 'POST' );

$app->map('/articulos/cdigital', function() use ($app) {

    $app->render( 'page/articulos/catalogodigital.php' );

})->via( 'GET', 'POST' );

//Presupuestos
$app->map('/presupuestos/lists', function() use ($app) {

    $app->render( 'page/presupuestos/lists.php' );

})->via( 'GET', 'POST' );

//Partidas
$app->map('/partidas/lists', function() use ($app) {

    $app->render( 'page/partidas/lists.php' );

})->via( 'GET', 'POST' );

//Tipos de Recursos
$app->map('/tiposderecursos/lists', function() use ($app) {

    $app->render( 'page/tiposderecursos/lists.php' );

})->via( 'GET', 'POST' );

//Tipos de Procedimientos
$app->map('/tiposdeprocedimientos/lists', function() use ($app) {

    $app->render( 'page/tiposdeprocedimientos/lists.php' );

})->via( 'GET', 'POST' );

  


/**
 * Pending
 **/

$app->map('/articulos/pending', function() use ($app) {

    $app->render( 'page/articulos/pending.php' );

})->via( 'GET', 'POST' );


/**
 * Edit Subscriber
 **/

$app->map('/articulos/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );

/**
 * View Subscriber
 **/

$app->map('/articulos/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/articulos/view.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );

$app->get('/articulosrutas/lists', function() use ($app) {

    $app->render( 'page/articulos/articulosrutas.php');

});?><?php

/**
 * clientes
 **/

$app->map('/asignacionalmacenesusuarios', function() use ($app) {

    $app->render( 'page/asignacionalmacenesusuarios/index.php' );

})->via( 'GET', 'POST' );


/**
 * Lists
 **/

$app->map('/asignacionalmacenesusuarios/lists', function() use ($app) {

    $app->render( 'page/asignacionalmacenesusuarios/lists.php' );

})->via( 'GET', 'POST' );


/**
 * Pending
 **/

$app->map('/asignacionalmacenesusuarios/pending', function() use ($app) {

    $app->render( 'page/asignacionalmacenesusuarios/pending.php' );

})->via( 'GET', 'POST' );


/**
 * Edit Subscriber
 **/

$app->map('/asignacionalmacenesusuarios/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );

/**
 * View Subscriber
 **/

$app->map('/asignacionalmacenesusuarios/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/asignacionalmacenesusuarios/view.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );


/**
 * clientes
 **/

$app->map('/asignacionusuariosalmacen', function() use ($app) {

    $app->render( 'page/asignacionusuariosalmacen/index.php' );

})->via( 'GET', 'POST' );


/**
 * Lists
 **/

$app->map('/asignacionusuariosalmacen/lists', function() use ($app) {

    $app->render( 'page/asignacionusuariosalmacen/lists.php' );

})->via( 'GET', 'POST' );


/**
 * Pending
 **/

$app->map('/asignacionusuariosalmacen/pending', function() use ($app) {

    $app->render( 'page/asignacionusuariosalmacen/pending.php' );

})->via( 'GET', 'POST' );


/**
 * Edit Subscriber
 **/

$app->map('/asignacionusuariosalmacen/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );

/**
 * View Subscriber
 **/

$app->map('/asignacionusuariosalmacen/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/asignacionusuariosalmacen/view.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );


/**
 * clientes
 **/

$app->map('/clientes', function() use ($app) {

    $app->render( 'page/clientes/index.php' );

})->via( 'GET', 'POST' );

/*
 * importar
*/

$app->post('/api/v2/clientes/importar', 'Application\Controllers\ClientesController:importar');
$app->get('/api/v2/clientes/exportar', 'Application\Controllers\ClientesController:exportar');

$app->post('/clientes/destinatarios/importar', 'Application\Controllers\DestinatariosController:importar');
$app->post('/clientes/destinatarios/importarpv', 'Application\Controllers\DestinatariosController:importarpv');
$app->get('/api/v2/clientes/destinatarios/exportar', 'Application\Controllers\DestinatariosController:exportar');

$app->get('/api/v2/monitoreo/exportar', 'Application\Controllers\MonitorEntregasNikkenController:exportar');



$app->post('/clientes/import', function() use($app){
    /* Archivo Excel del formulario */
    $file = $_FILES['layout']['tmp_name'];
    /* Creando lector excel */
    $inputFileType = PHPExcel_IOFactory::identify($file);
    $objReader = PHPExcel_IOFactory::createReader($inputFileType);
    $objPHPExcel = $objReader->load($file);
    /* Obteniendo hoja activa */
    $sheet = $objPHPExcel->getActiveSheet();
    /* Obteniendo identificador de última columna y fila */
    $sheetInfo = $sheet->getHighestRowAndColumn();
    $highestRow = $sheetInfo['row'];
    $highestColumn = $sheetInfo['column'];
    /* Empezamos en A2 ya que A1 corresponde a los titulos */
    $sheetRange = "A2:$highestColumn$highestRow";
    $rowData = $sheet->rangeToArray($sheetRange);

    /* Guardando sql para ejecutarlos juntos */
    $insert = "";
    /* Leyendo cada celda del excel */
    foreach($rowData as $cell){
        $data = [
            "c_cliente.Cve_Clte"          =>  $cell[0],
            "c_cliente.RazonSocial"       =>  $cell[1],
            "c_cliente.RazonComercial"    =>  $cell[2],
            "c_cliente.CalleNumero"       =>  $cell[3],
            "c_cliente.Colonia"           =>  $cell[4],
            "c_cliente.Ciudad"            =>  $cell[5],
            "c_cliente.Estado"            =>  $cell[6],
            "c_cliente.Pais"              =>  $cell[7],
            "c_cliente.CodigoPostal"      =>  $cell[8],
            "c_cliente.RFC"               =>  $cell[9],
            "c_cliente.Telefono1"         =>  $cell[10],
            "c_cliente.Telefono2"         =>  $cell[11],
            "c_cliente.Telefono3"         =>  $cell[12],
            "c_cliente.ClienteTipo"       =>  $cell[13],
            "c_cliente.ClienteGrupo"       =>  $cell[14],
            "c_cliente.ClienteFamilia"       =>  $cell[15],
            "c_cliente.CondicionPago"     =>  $cell[16],
            "c_cliente.MedioEmbarque"     =>  $cell[17],
            "c_cliente.ViaEmbarque"     =>  $cell[18],
            "c_cliente.CondicionEmbarque"     =>  $cell[19],
            "c_cliente.cve_ruta"     =>  $cell[20],
            "c_cliente.ID_Proveedor"      =>  $cell[21],
            "c_cliente.Cve_CteProv"      =>  $cell[22],
            "c_cliente.Cve_Almacenp"      =>  $cell[23]
        ];
        $last_key = key( array_slice( $data, -1, 1, TRUE ) );
        $insertStm = "INSERT IGNORE INTO c_cliente SET ";
        $set = "";
        foreach($data as $setName => $setValue){
            if(!empty($setValue)){
                $set .= " $setName = \"$setValue\"";
                if($setName !== $last_key){
                    $set .= ",";
                }
            }
        }
        $insertStm .= $set."; ";
        $insert .= $insertStm;
    }
    $query = mysqli_multi_query(\db2(), $insert);
    
    if($query){
        $app->response->redirect('/clientes/lists');
    }
});

/**
 * Lists
 **/

$app->map('/clientes/lists', function() use ($app) {

    $app->render( 'page/clientes/lists.php' );

})->via( 'GET', 'POST' );

$app->map('/clientes/grupos', function() use ($app) {

    $app->render( 'page/clientes/grupos.php' );

})->via( 'GET', 'POST' );

$app->map('/clientes/clasificacion', function() use ($app) {

    $app->render( 'page/clientes/clasificacion.php' );

})->via( 'GET', 'POST' );

$app->map('/clientes/clasificacion2', function() use ($app) {

    $app->render( 'page/clientes/clasificacion2.php' );

})->via( 'GET', 'POST' );

$app->map('/correcciondir/lists', function() use ($app) {

    $app->render( 'page/correcciondir/lists.php' );

})->via( 'GET', 'POST' );

$app->map('/activos/lists', function() use ($app) {

    $app->render( 'page/activos/lists.php' );

})->via( 'GET', 'POST' );

$app->map('/activos/administracion', function() use ($app) {

    $app->render( 'page/adminactivos/lists.php' );

})->via( 'GET', 'POST' );

/**
 * Pending
 **/

$app->map('/clientes/pending', function() use ($app) {

    $app->render( 'page/clientes/pending.php' );

})->via( 'GET', 'POST' );


/**
 * Edit Subscriber
 **/

$app->map('/clientes/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );

/**
 * View Subscriber
 **/

$app->map('/clientes/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/clientes/view.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );



/**
 * pedidos
 **/

$app->map('/companias', function() use ($app) {

    $app->render( 'page/companias/index.php' );

})->via( 'GET', 'POST' );


/**
 * Lists
 **/

$app->map('/companias/lists', function() use ($app) {

    $app->render( 'page/companias/lists.php' );

})->via( 'GET', 'POST' );


/**
 * Pending
 **/

$app->map('/companias/pending', function() use ($app) {

    $app->render( 'page/companias/pending.php' );

})->via( 'GET', 'POST' );


/**
 * Edit Subscriber
 **/

$app->map('/companias/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );

/**
 * View Subscriber
 **/

$app->map('/companias/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/companias/view.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );



  /**
    * protocolos
  **/

  $app->map('/contenedores', function() use ($app) {

    $app->render( 'page/contenedores/index.php' );

  })->via( 'GET', 'POST' );



/*
* importar
*/

$app->post('/pallets/importar', 'Application\Controllers\PalletController:importar');
$app->get('/api/v2/pallets/exportar', 'Application\Controllers\PalletController:exportar');

$app->post('/contenedores/import', function() use($app){
    /* Archivo Excel del formulario */
    $file = $_FILES['layout']['tmp_name'];
    /* Creando lector excel */
    $inputFileType = PHPExcel_IOFactory::identify($file);
    $objReader = PHPExcel_IOFactory::createReader($inputFileType);
    $objPHPExcel = $objReader->load($file);
    /* Obteniendo hoja activa */
    $sheet = $objPHPExcel->getActiveSheet();
    /* Obteniendo identificador de última columna y fila */
    $sheetInfo = $sheet->getHighestRowAndColumn();
    $highestRow = $sheetInfo['row'];
    $highestColumn = $sheetInfo['column'];
    /* Empezamos en A2 ya que A1 corresponde a los titulos */
    $sheetRange = "A2:$highestColumn$highestRow";
    $rowData = $sheet->rangeToArray($sheetRange);

    /* Guardando sql para ejecutarlos juntos */
    $insert = "";
    /* Leyendo cada celda del excel */
    foreach($rowData as $cell){
        $data = [
            "c_charolas.cve_almac"      =>  $cell[0],
            "c_charolas.charola"      =>  $cell[1],
            "c_charolas.Pedido"       =>  $cell[2],
            "c_charolas.sufijo"        =>  $cell[3],
          "c_charolas.tipo"        =>  $cell[4],
          "c_charolas.alto"        =>  $cell[5],
          "c_charolas.ancho"        =>  $cell[6],
          "c_charolas.fondo"        =>  $cell[7],
          "c_charolas.peso"        =>  $cell[8],
          "c_charolas.clave_contenedor"        =>  $cell[9],
          "c_charolas.pesomax"        =>  $cell[10],
          "c_charolas.capavol"        =>  $cell[11],
          "c_charolas.descripcion"        =>  $cell[12]
        ];
        $last_key = key( array_slice( $data, -1, 1, TRUE ) );
        $insertStm = "INSERT IGNORE INTO c_charolas SET ";
        $set = "";
        foreach($data as $setName => $setValue){
            if(!empty($setValue)){
                $set .= " $setName = \"$setValue\"";
                 if($setName !== $last_key){
                    $set .= ",";
                }
            }
        }
        $insertStm .= $set."; ";
        $insert .= $insertStm;
    }
    $query = mysqli_multi_query(\db2(), $insert);
    if($query){
        $app->response->redirect('/contenedores/lists');
    }
});

  /**
    * Lists
  **/

  $app->map('/contenedores/lists', function() use ($app) {

    $app->render( 'page/contenedores/lists.php' );

  })->via( 'GET', 'POST' );

  $app->map('/licenseplate/list', function() use ($app) {

    $app->render( 'page/licenseplate/lists.php' );

  })->via( 'GET', 'POST' );


  /**
    * Pending
  **/

  $app->map('/contenedores/pending', function() use ($app) {

    $app->render( 'page/contenedores/pending.php' );

  })->via( 'GET', 'POST' );


  /**
    * Edit Subscriber
  **/

  $app->map('/contenedores/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
      'id_subscriber' => $id_subscriber
    ) );

  })->via( 'GET', 'POST' );

  /**
    * View Subscriber
  **/

  $app->map('/contenedores/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/contenedores/view.php', array(
      'id_subscriber' => $id_subscriber
    ) );

  })->via( 'GET', 'POST' );


  

  /**
    * Index
  **/

  $app->get('/', function() use ($app) {

    $app->render( 'page/index.php' );

  });

  $app->map('/dashboard/monitoreopedidos', function() use ($app) {

    $app->render( 'page/index.monitoreopedidos.php' );
  })->via( 'GET', 'POST' );

  $app->map('/dashboard/inventario', function() use ($app) {

    $app->render( 'page/index.inventario.php' );
  })->via( 'GET', 'POST' );


  $app->map('/dashboard/resumen', function() use ($app) {

    $app->render( 'page/index.resumen.ejecutivo.php' );

  })->via( 'GET', 'POST' );

  $app->map('/dashboard/testing', function() use ($app) {

    $app->render( 'page/index.testing.ejecutivo.php' );

  })->via( 'GET', 'POST' );


  $app->map('/dashboard/monitoreo_old', function() use ($app) {

    $app->render( 'page/index.monitoreo.php' );

  })->via( 'GET', 'POST' );


/** 
 * --------------------------------------------------------------------------
 * Monitoreo de entregas de Nikken
 * -------------------------------------------------------------------------- 
 * @version 1.0.0
 * @author Brayan Rincon <brayan262@gmail.com>
 * @category Monitoreode Entregas de Nikken
 */
$app->get('/dashboard/monitoreo', 'Application\Controllers\MonitorEntregasNikkenController:index');
$app->get('/api/v2/monitoreo-de-entrega/paginate', 'Application\Controllers\MonitorEntregasNikkenController:paginate');
$app->get('/api/v2/monitoreo-de-entrega/retrasados', 'Application\Controllers\MonitorEntregasNikkenController:retrasados');
$app->get('/api/v2/monitoreo-de-entrega/paginate-avanzado', 'Application\Controllers\MonitorEntregasNikkenController:paginate');

 
  $app->post('/api/sendMail/', 'sendMail');

  function sendMail () {

    $templates = new \Templates\Templates();
    $file = new \File\File();

    $id = $_POST['id'];

    $templates->id_template = $id;
    $row = $templates->__get('id_template');
    $title = $templates->__get('title');
    $container = $templates->__get('content');
    $mailer = new PHPMailer();

    $mailer->From     = SMTP_USERNAME;
    $mailer->FromName = SITE_TITLE;
    $mailer->AddAddress("luisfraino@gmail.com");
    $mailer->isHTML(true);
    $mailer->Body = $container;
    $mailer->Subject = "UPLOAD";

    foreach ($file->getList($id) AS $f ) {
      $_f = "/data/uploads/".trim($f->filename);
      $mailer->AddAttachment($_f, $f->filename);
    }

    header("Content-Type: application/json");
    $_arr = array("success" => "true");
    echo json_encode($_arr);
    
    if(!$mailer->Send()) {
      echo "<script>alert('Mailer Error: " . $mailer->ErrorInfo."')</script>";
    } else {
      echo "<script>alert('Your request has been submitted. We will contact you soon.')</script>";
      //Header('Location: main.php');
    }

    exit();
  }
  /**
    * Before all Routes
  **/
function getRealIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP']))
        return $_SERVER['HTTP_CLIENT_IP'];
       
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
   
    return $_SERVER['REMOTE_ADDR'];
}

  $app->hook('slim.before.dispatch', function() use ($app) {
    if(!isset($_REQUEST['nofooternoheader'])){
      $id = $_SESSION['id_user'];
      $IP = getRealIP();
      //$IP = "1";

      //EL TIEMPO EN SESIÓN PARA QUE SE SALGA SOLO, ESTÁ EN footer.php USANDO JQUERY

      $sql = 'INSERT INTO users_online (id_usuario, last_updated, IP_Address) VALUES('.$id.', NOW(), "'.$IP.'") ON DUPLICATE KEY UPDATE last_updated = NOW();';
      $data = mysqli_query(\db2(), $sql);
      $subdomain = $_SESSION['identifier'];

      @$app->render( 'header.php', array('page' => $page[0]) );
    }

  });

  /**
    * After all Routes
  **/

  $app->hook('slim.after.dispatch', function() use ($app) {

    if(!isset($_REQUEST['nofooternoheader'])){
      $page = explode( '/', ltrim( $_SERVER['REQUEST_URI'], '/' ) );
      $subdomain = $_SESSION['identifier'];
      $app->render( 'footer.php' );
    }

  });

  

/**
    * protocolos
  **/

$app->map('/cortinaentrada', function() use ($app) {

  $app->render( 'page/cortinaentrada/index.php' );

})->via( 'GET', 'POST' );



/*
* importar
*/


$app->post('/area-de-recepcion/importar', 'Application\Controllers\AreaDeRecepcionController:importar');
$app->get('/api/v2/area-de-recepcion/exportar', 'Application\Controllers\AreaDeRecepcionController:exportar');


$app->post('/cortinaentrada/import', function() use($app){
  /* Archivo Excel del formulario */
  $file = $_FILES['layout']['tmp_name'];
  /* Creando lector excel */
  $inputFileType = PHPExcel_IOFactory::identify($file);
  $objReader = PHPExcel_IOFactory::createReader($inputFileType);
  $objPHPExcel = $objReader->load($file);
  /* Obteniendo hoja activa */
  $sheet = $objPHPExcel->getActiveSheet();
  /* Obteniendo identificador de última columna y fila */
  $sheetInfo = $sheet->getHighestRowAndColumn();
  $highestRow = $sheetInfo['row'];
  $highestColumn = $sheetInfo['column'];
  /* Empezamos en A2 ya que A1 corresponde a los titulos */
  $sheetRange = "A2:$highestColumn$highestRow";
  $rowData = $sheet->rangeToArray($sheetRange);

  /* Guardando sql para ejecutarlos juntos */
  $insert = "";
  /* Leyendo cada celda del excel */
  foreach($rowData as $cell){
    $data = [
      "tubicacionesretencion.cve_ubicacion"      =>  $cell[0],
      "tubicacionesretencion.cve_almacp"      =>  $cell[1],
      "tubicacionesretencion.desc_ubicacion"       =>  $cell[2]
    ];
    $last_key = key( array_slice( $data, -1, 1, TRUE ) );
    $insertStm = "INSERT IGNORE INTO tubicacionesretencion SET ";
    $set = "";
    foreach($data as $setName => $setValue){
      if(!empty($setValue)){
        $set .= " $setName = \"$setValue\"";
        if($setName !== $last_key){
          $set .= ",";
        }
      }
    }
    $insertStm .= $set."; ";
    $insert .= $insertStm;
  }
  $query = mysqli_multi_query(\db2(), $insert);
  if($query){
    $app->response->redirect('/cortinaentrada/lists');
  }
});

/**
    * Lists
  **/

$app->map('/cortinaentrada/lists', function() use ($app) {

  $app->render( 'page/cortinaentrada/lists.php' );

})->via( 'GET', 'POST' );


/**
    * Pending
  **/

$app->map('/cortinaentrada/pending', function() use ($app) {

  $app->render( 'page/cortinaentrada/pending.php' );

})->via( 'GET', 'POST' );


/**
    * Edit Subscriber
  **/

$app->map('/cortinaentrada/edit/:id', function( $id_subscriber ) use ($app) {

  $app->render( 'page/subscribers/edit.php', array(
    'id_subscriber' => $id_subscriber
  ) );

})->via( 'GET', 'POST' );

/**
    * View Subscriber
  **/

$app->map('/cortinaentrada/view/:id', function( $id_subscriber ) use ($app) {

  $app->render( 'page/cortinaentrada/view.php', array(
    'id_subscriber' => $id_subscriber
  ) );

})->via( 'GET', 'POST' );



$app->map('/dashboard/productos-en-piso', function() use ($app) {
    $app->render( 'page/index.productos_en_piso.php' );
})->via( 'GET', 'POST' );


  /**
    * destinatarios
  **/

  
  $app->map('/destinatarios', function() use ($app) {

    $app->render( 'page/destinatarios/index.php' );

  })->via( 'GET', 'POST' );


  /**
    * Lists
  **/

  $app->get('/destinatarios/lists', 'Application\Controllers\DestinatariosController:index');
  $app->get('/api/v2/destinatarios', 'Application\Controllers\DestinatariosController:paginate');


  $app->map('/destinatarios-old/lists', function() use ($app) {
  //$app->map('/destinatarios/lists', function() use ($app) {

    $app->render( 'page/destinatarios/lists.php' );

  })->via( 'GET', 'POST' );


  /**
    * Pending
  **/

  $app->map('/destinatarios/pending', function() use ($app) {

    $app->render( 'page/destinatarios/pending.php' );

  })->via( 'GET', 'POST' );


  /**
    * Edit Subscriber
  **/

  $app->map('/destinatarios/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
      'id_subscriber' => $id_subscriber
    ) );

  })->via( 'GET', 'POST' );

  /**
    * View Subscriber
  **/

  $app->map('/destinatarios/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/destinatarios/view.php', array(
      'id_subscriber' => $id_subscriber
    ) );

  })->via( 'GET', 'POST' );

  

  /**
    * protocolos
  **/

  $app->map('/embarques', function() use ($app) {

    $app->render( 'page/embarques/index.php' );

  })->via( 'GET', 'POST' );


  /**
    * Lists
  **/

  $app->map('/embarques/lists', function() use ($app) {

    $app->render( 'page/embarques/lists.php' );

  })->via( 'GET', 'POST' );


  /**
    * Pending
  **/

  $app->map('/embarques/pending', function() use ($app) {

    $app->render( 'page/embarques/pending.php' );

  })->via( 'GET', 'POST' );


  /**
    * Edit Subscriber
  **/

  $app->map('/embarques/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
      'id_subscriber' => $id_subscriber
    ) );

  })->via( 'GET', 'POST' );

  /**
    * View Subscriber
  **/

  $app->map('/embarques/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/embarques/view.php', array(
      'id_subscriber' => $id_subscriber
    ) );

  })->via( 'GET', 'POST' );


 $app->get('/embarques/informe', function() use ($app) {

    $app->render( 'page/embarques/informe.php' );

  });

  $app->post('/embarques/informe', function() use ($app) {
    $folio = $_POST['folio'];
    $enlace = "<a href='{$_SERVER['REQUEST_URI']}'>Regresar</a>";
    echo sprintf("Sección en construcción su folio es %s. Será redireccionado en 5 seg. %s", $folio, $enlace);
  });
  



  /**
    * Frontend
  **/

  //$app->map('/f/:identifier', function( $identifier ) use ( $app ) {
  /*$app->map('/:identifier/', function( $identifier ) use ( $app ) {

    $app->render( 'page/frontend/index.php', array(
      'identifier' => $identifier
    ) );

  })->via( 'GET', 'POST' );


  /**
    * Frontend Single View
  **/

  //$app->map('/f/:identifier/:id', function( $identifier, $id ) use ( $app ) {
  /*$app->map('/:identifier/:id/', function( $identifier, $id ) use ( $app ) {

    $app->render( 'page/frontend/single.php', array(
      'identifier' => $identifier
    , 'id_template' => $id
    ) );

  })->via( 'GET', 'POST' );*/

  

/**
 * grupoarticulos
 **/

$app->map('/grupoarticulos', function() use ($app) {

    $app->render( 'page/grupoarticulos/index.php' );

})->via( 'GET', 'POST' );



/*
* importar Grupo de articulos
*/
$app->post('/articulos/grupos/importar', 'Application\Controllers\ArticulosGruposController:importar');
$app->get('/api/v2/grupos-de-articulos/exportar', 'Application\Controllers\ArticulosGruposController:exportar');


/**
 * Lists
 **/

$app->map('/grupoarticulos/list', function() use ($app) {

    $app->render( 'page/grupoarticulos/lists.php' );

})->via( 'GET', 'POST' );


/**
 * Pending
 **/

$app->map('/grupoarticulos/pending', function() use ($app) {

    $app->render( 'page/grupoarticulos/pending.php' );

})->via( 'GET', 'POST' );


/**
 * Edit Subscriber
 **/

$app->map('/grupoarticulos/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );

/**
 * View Subscriber
 **/

$app->map('/grupoarticulos/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/grupoarticulos/view.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );



/**
 * Lists
 **/

$app->map('/importar/clientes', function() use ($app) {

    $app->render( 'page/importar/clientes.php' );

})->via( 'GET');

$app->map('/importar/pedidos', function() use ($app) {

    $app->render( 'page/importar/pedidos.php' );

})->via( 'GET');

$app->map('/importar/ocentradas', function() use ($app) {

    $app->render( 'page/importar/ocentradas.php' );

})->via( 'GET');

$app->map('/importar/cross', function() use ($app) {

    $app->render( 'page/importar/cross.php' );

})->via( 'GET');

$app->get('/importar/stock', function() use ($app) {

    $app->render( 'page/importar/stock.php' );

});

$app->get('/importar/rutasSurtido', function() use ($app) {

    $app->render( 'page/importar/rutas_surtido.php' );

});

$app->get('/importar/destinatarios', function() use ($app) {

    $app->render( 'page/importar/destinatarios.php' );

});

$app->get('/importar/proveedores', function() use ($app) {

    $app->render( 'page/importar/proveedores.php' );

});

$app->get('/importar/rutas', function() use ($app) {

    $app->render( 'page/importar/rutas.php' );

});

$app->get('/importar/transportes', function() use ($app) {

    $app->render( 'page/importar/transportes.php' );

});

$app->get('/importar/productos', function() use ($app) {

    $app->render( 'page/importar/productos.php' );

});

$app->get('/importar/ubicaciones', function() use ($app) {

    $app->render( 'page/importar/ubicaciones.php' );

});

$app->get('/importar/palletsycontenedores', function() use ($app) {

    $app->render( 'page/importar/palletsycontenedores.php' );

});



$app->post('/importar/pedidos', function() use ($app){
	$tipo = $_POST['tipo'];
	$file = $_FILES['layout']['tmp_name'];
    $reader = fopen($file, "r");
    
    if($reader){
    	$insert = "";
    	while (($line = fgets($reader)) !== false) {
    		$data = explode("|", $line);
    		$data = array_map("trim", $data);
    		if(count($data) > 1){
    			if($tipo === "th" && count($data) === 21){
    				$insertData = [
    					"Fol_folio"			=> $data[0],
    					"Fec_Pedido"		=> !empty($data[1]) ? date('Y-m-d', strtotime($data[1])) : '',
    					"Cve_clte"			=> $data[2],
    					"status"			=> 'A',
    					"Fec_Entrega"		=> !empty($data[4]) ? date('Y-m-d', strtotime($data[4])) : '',
    					"cve_Vendedor"		=> $data[5],
    					"Num_Meses"			=> $data[6],
    					"Observaciones"		=> $data[7],
    					"ID_Tipoprioridad"	=> $data[8],
    					"Fec_Entrada"		=> !empty($data[9]) ? date('Y-m-d', strtotime($data[9])) : '',
    					"transporte"		=> $data[10],
    					"cve_almac"			=> $data[16],
    					"Cve_Usuario"		=> $data[18],
    					"Ship_Num"			=> $data[19]

    				];
    				$last_key = key( array_slice( $insertData, -1, 1, TRUE ) );
    				$insertStm = "INSERT IGNORE INTO th_pedido SET ";
    				foreach($insertData as $setName => $setValue){  
	                    if(!empty($setValue)){
	                        $insertStm .= " {$setName} = \"$setValue\",";
	             
	                    }
	                }
	                $insertStm .= ";";
	                if(substr($insertStm, -2) === ',;'){
	                	$insertStm = substr($insertStm, 0, -2)."; ";
	                }
	                $insert .= $insertStm;
    			}
                if($tipo === "td" && count($data) === 11){
                    $insertData = [
                        "Fol_folio"         => $data[0],
                        "Cve_articulo"      => $data[1],
                        "Num_cantidad"      => intval($data[2]),
                        "SurtidoXPiezas"    => $data[9],
                        "status"            => "A"
                    ];
                    $last_key = key( array_slice( $insertData, -1, 1, TRUE ) );
                    $insertStm = "INSERT IGNORE INTO td_pedido SET ";
                    foreach($insertData as $setName => $setValue){  
                        if(!empty($setValue)){
                            $insertStm .= " {$setName} = \"$setValue\",";
                 
                        }
                    }
                    $insertStm .= ";";
                    if(substr($insertStm, -2) === ',;'){
                        $insertStm = substr($insertStm, 0, -2)."; ";
                    }
                    $insert .= $insertStm;
                }
    		}
    	}
    }
    fclose($reader);
    $query = mysqli_multi_query(\db2(), $insert);

    if($query){
        $app->render('page/importar/pedidos.php', array("message" => "Pedidos importados exitosamente"));
    }
    else{
        $app->render('page/importar/pedidos.php', array("error" => "Ocurrio un error al importar los pedidos ".mysqli_error(\db2())));
    }

});



$app->post('/crossdocking/importar', 'Application\Controllers\CrossdockingController:importar');
$app->get('/api/v2/crosdocking/exportar-cabecera', 'Application\Controllers\CrossdockingController:exportarCabecera');
$app->get('/api/v2/crosdocking/exportar-detalles(/:id)', 'Application\Controllers\CrossdockingController:exportarDetalles');



$app->post('/importar/cross', function() use ($app){
    $tipo = $_POST['tipo'];
    $file = $_FILES['layout']['tmp_name'];
    $reader = fopen($file, "r");
    
    if($reader){
        $insert = "";
        while (($line = fgets($reader)) !== false) {
            $data = explode("|", $line);
            $data = array_map("trim", $data); 
            if(count($data) >1){
                if($tipo === "th" && count($data) === 19){
                    $insertData = [
                        "CodB_Prov"         =>  $data[0],
                        "NIT_Prov"          =>  $data[1],
                        "Nom_Prov"          =>  addslashes($data[2]),
                        "Cve_CteCon"        =>  $data[4],
                        "CodB_CteCon"       =>  $data[5],
                        "Nom_CteCon"        =>  addslashes($data[6]),
                        "Dir_CteCon"        =>  addslashes($data[7]),
                        "Cd_CteCon"         =>  addslashes($data[8]),
                        "NIT_CteCon"        =>  $data[9],
                        "Cod_CteCon"        =>  $data[10],
                        "CodB_CteEnv"       =>  $data[11],
                        "Nom_CteEnv"        =>  addslashes($data[12]),
                        "Dir_CteEnv"        =>  addslashes($data[13]),
                        "Cd_CteEnv"         =>  addslashes($data[14]),
                        "Tel_CteEnv"        =>  $data[15],
                        "Fec_Entrega"       =>  date("Y-m-d",strtotime($data[16])),
                        "Tot_Cajas"         =>  0,
                        "Tot_Pzs"           =>  $data[17],
                        "No_OrdComp"        =>  $data[3],
                        "Status"            =>  "A",
                        "Activo"            =>  1
                    ];
                    $last_key = key( array_slice( $insertData, -1, 1, TRUE ) );
                    $insertStm = "INSERT IGNORE INTO th_consolidado SET ";                    
                    foreach($insertData as $setName => $setValue){  
                        if(!empty($setValue)){
                            $insertStm .= " {$setName} = \"$setValue\",";
                        }
                    }
                    $insertStm .= ";";
                    if(substr($insertStm, -2) === ',;'){
                        $insertStm = substr($insertStm, 0, -2)."; ";
                    }
                    $insert .= $insertStm;
                }
                if($tipo === "td" && count($data) === 20){
                    $insertData = [
                        "Fol_PedidoCon"         =>  $data[13],
                        "No_OrdComp"            =>  $data[0],
                        "Fec_OrdCom"            =>  date("Y-m-d", strtotime($data[1])),
                        "Cve_Articulo"          =>  $data[2],
                        "Cant_Pedida"           =>  $data[8],
                        "Unid_Empaque"          =>  addslashes($data[10]),
                        "Tot_Cajas"             =>  $data[9],
                        "Status"                =>  "A",
                        "Fact_Madre"            =>  $data[13],
                        "Cve_Clte"              =>  $data[15],
                        "Cve_CteProv"           =>  $data[16],
                        "Fol_Folio"             =>  $data[17],
                        "CodB_Cte"              =>  $data[11],
                        "Cod_PV"                =>  $data[18]
                    ];
                    $last_key = key( array_slice( $insertData, -1, 1, TRUE ) );
                    $insertStm = "INSERT IGNORE INTO td_consolidado SET ";                    
                    foreach($insertData as $setName => $setValue){  
                        if(!empty($setValue)){
                            $insertStm .= " {$setName} = \"$setValue\",";
                        }
                    }
                    $insertStm .= ";";
                    if(substr($insertStm, -2) === ',;'){
                        $insertStm = substr($insertStm, 0, -2)."; ";
                    }
                    $insert .= $insertStm;
                }
            }
        }
    }

    fclose($reader);
    $query = mysqli_multi_query(\db2(), $insert);

    if($query){
        $app->render('page/importar/pedidos.php', array("message" => "Crossdocking importados exitosamente"));
    }
    else{
        $app->render('page/importar/pedidos.php', array("error" => "Ocurrio un error al importar Crossdocking".mysqli_error(\db2())));
    }

});


// Retorna un JSON con los registros del Administrador de inventario compatible con JQGRID
$app->get('/inventario/paginate', 'Application\Controllers\InventarioController:paginate');
//$app->post('/inventario/paginate', 'Application\Controllers\InventarioController:paginate');
$app->get('/inventario/detalles-inventario-fisico/:id(/:conteo)', 'Application\Controllers\InventarioController:cargarDeatllesInventarioFisico');
$app->get('/inventario/detalles-inventario-ciclico/:id(/:conteo)', 'Application\Controllers\InventarioController:cargarDeatllesInventarioCiclico');
$app->post('/inventario/importar', 'Application\Controllers\InventarioController:importar');
$app->get('/api/v2/inventario/existencias-por-ubicaciones', 'Application\Controllers\InventarioController:existenciasPorUbicaciones');

$app->post('/stock/importar', 'Application\Controllers\StockController:importar');

/** 
 * --------------------------------------------------------------------------
 * API de Inventario
 * -------------------------------------------------------------------------- 
 * @version 2.0.0
 * @author Brayan Rincon <brayan262@gmail.com>
 * @category Inventario
 */
$app->post('/api/v2/inventario/asignar-supervisor', 'Application\Controllers\InventarioController:asignarSupervisor');
$app->post('/api/v2/inventario/guardar-conteo-fisico', 'Application\Controllers\InventarioController:guardarStockFisico');
$app->post('/api/v2/inventario/guardar-conteo-ciclico', 'Application\Controllers\InventarioController:guardarStockCiclico');

$app->post('/api/v2/inventario/activar-conteo-cierre', 'Application\Controllers\InventarioController:activarConteoCierre');






/**
 * clientes
 **/

$app->map('/incidencias', function() use ($app) {

    $app->render( 'page/incidencias/index.php' );

})->via( 'GET', 'POST' );


/**
 * Lists
 **/

$app->map('/incidencias/lists', function() use ($app) {

    $app->render( 'page/incidencias/lists.php' );

})->via( 'GET', 'POST' );


/**
 * Pending
 **/

$app->map('/incidencias/pending', function() use ($app) {

    $app->render( 'page/incidencias/pending.php' );

})->via( 'GET', 'POST' );


/**
 * Edit Subscriber
 **/

$app->map('/incidencias/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );

/**
 * View Subscriber
 **/

$app->map('/incidencias/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/incidencias/view.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );


$app->post('/incidencias/pdf', function() use ($app) {
    $sql = "SELECT
            i.ID_Incidencia AS numero,
            a.nombre AS almacen,
            c.Cve_Clte AS clave,
            p.Nombre AS proveedor,
            c.RazonSocial AS razon_social,
            i.Fol_folio AS folio,
            (
                CASE
                        WHEN i.tipo_reporte = 'P' THEN 'Petición'
                        WHEN i.tipo_reporte = 'Q' THEN 'Queja'
                        WHEN i.tipo_reporte = 'R' THEN 'Reclamo'
                        WHEN i.tipo_reporte = 'S' THEN 'Sugerencia'
                END
            ) AS tipo_reporte,
            DATE_FORMAT(i.Fecha, '%Y-%m-%d') AS fecha_inicio,
            (SELECT nombre_completo FROM c_usuario WHERE cve_usuario = i.reportador) AS usuario_registro,
            DATE_FORMAT(i.Fecha_accion, '%Y-%m-%d') AS fecha_fin,
            (SELECT nombre_completo FROM c_usuario WHERE cve_usuario = i.responsable_recibo) AS usuario_cierre,
            (
                CASE
                      WHEN i.status = 'A' THEN 'Abierto'
                      WHEN i.status = 'C' THEN 'Cerrado'
                END
            ) AS status
    FROM th_incidencia i
    LEFT JOIN c_almacenp a ON a.clave = i.centro_distribucion
    LEFT JOIN c_cliente c ON c.Cve_Clte = i.cliente
    LEFT JOIN c_proveedores p ON p.ID_Proveedor = c.ID_Proveedor
    WHERE i.Activo = 1 ";
    mysqli_set_charset(\db2(), 'utf8');
    $query = mysqli_query(\db2(), $sql);
    ?>
        <table style="width: 100%; border-collapse: collapse; border-spacing: 0">
            <thead>
                <tr>
                    <th style="border: 1px solid #ccc">Incidencia Nº</th>
                    <th style="border: 1px solid #ccc">Almacén</th>
                    <th style="border: 1px solid #ccc">Clave</th>
                    <th style="border: 1px solid #ccc">Proveedor</th>
                    <th style="border: 1px solid #ccc">Razón Social</th>
                    <th style="border: 1px solid #ccc">Folio Pedido/Factura</th>
                    <th style="border: 1px solid #ccc">Tipo Reporte</th>
                    <th style="border: 1px solid #ccc">Fecha/Hora Inicio</th>
                    <th style="border: 1px solid #ccc">Usuario Registro</th>
                    <th style="border: 1px solid #ccc">Fecha/Hora Fin</th>
                    <th style="border: 1px solid #ccc">Usuario Cierre</th>
                    <th style="border: 1px solid #ccc">Status</th>
                </tr>
            </thead>
    <?php
    $content = ob_get_clean();
    if($query->num_rows > 0){
        $body = '<tbody>';
        while ($res = mysqli_fetch_array($query)){
            extract($res);
            $body .= "<tr>
                <td style='border: 1px solid #ccc'>$numero</td>
                <td style='border: 1px solid #ccc'>$almacen</td>
                <td style='border: 1px solid #ccc'>$clave</td>
                <td style='border: 1px solid #ccc'>$proveedor</td>
                <td style='border: 1px solid #ccc'>$razon_social</td>
                <td style='border: 1px solid #ccc'>$folio</td>
                <td style='border: 1px solid #ccc'>$tipo_reporte</td>
                <td style='border: 1px solid #ccc'>$fecha_inicio</td>
                <td style='border: 1px solid #ccc'>$usuario_registro</td>
                <td style='border: 1px solid #ccc'>$fecha_fin</td>
                <td style='border: 1px solid #ccc'>$usuario_cierre</td>
                <td style='border: 1px solid #ccc'>$status</td>
            </tr>";
        }
        $body .= '</tbody>';
    }
    $content .= $body;
    $content .= '</table>';
    $pdf = new \ReportePDF\PDF($_POST['cia'], $_POST['title'], 'L');
    $pdf->setContent($content);
    $pdf->stream();
    $app->stop();
});

$app->post('/incidencias/excel', function() use ($app) {
    include dirname(__DIR__).'/vendor/mk-j/php_xlsxwriter/xlsxwriter.class.php';
    $title = $_POST['title'].".xlsx";
    $header = array(
        'Incidencia Nº'   => 'string',
        'Almacén'       => 'string',
        'Clave'             => 'string',
        'Proveedor'            => 'string',
        'Razón Social'            => 'string',
        'Folio Pedido/Factura'            => 'string',
        'Tipo Reporte'            => 'string',
        'Fecha/Hora Inicio'            => 'string',
        'Usuario Registro'            => 'string',
        'Fecha/Hora Fin'            => 'string',
        'Usuario Cierre'            => 'string',
        'Status'            => 'string'
    );
    $excel = new XLSXWriter();
    $excel->writeSheetHeader('Sheet1', $header );
    $sql = "SELECT
            i.ID_Incidencia AS numero,
            a.nombre AS almacen,
            c.Cve_Clte AS clave,
            p.Nombre AS proveedor,
            c.RazonSocial AS razon_social,
            i.Fol_folio AS folio,
            (
                CASE
                        WHEN i.tipo_reporte = 'P' THEN 'Petición'
                        WHEN i.tipo_reporte = 'Q' THEN 'Queja'
                        WHEN i.tipo_reporte = 'R' THEN 'Reclamo'
                        WHEN i.tipo_reporte = 'S' THEN 'Sugerencia'
                END
            ) AS tipo_reporte,
            DATE_FORMAT(i.Fecha, '%Y-%m-%d') AS fecha_inicio,
            (SELECT nombre_completo FROM c_usuario WHERE cve_usuario = i.reportador) AS usuario_registro,
            DATE_FORMAT(i.Fecha_accion, '%Y-%m-%d') AS fecha_fin,
            (SELECT nombre_completo FROM c_usuario WHERE cve_usuario = i.responsable_recibo) AS usuario_cierre,
            (
                CASE
                      WHEN i.status = 'A' THEN 'Abierto'
                      WHEN i.status = 'C' THEN 'Cerrado'
                END
            ) AS status
    FROM th_incidencia i
    LEFT JOIN c_almacenp a ON a.clave = i.centro_distribucion
    LEFT JOIN c_cliente c ON c.Cve_Clte = i.cliente
    LEFT JOIN c_proveedores p ON p.ID_Proveedor = c.ID_Proveedor
    WHERE i.Activo = 1 ";
    $query = mysqli_query(\db2(), $sql);
    if($query->num_rows > 0){
        while ($res = mysqli_fetch_array($query)){
            extract($res);
            $row = array(
                $numero,
                $almacen,
                $clave,
                $proveedor,
                $razon_social,
                $folio,
                $tipo_reporte,
                $fecha_inicio,
                $usuario_registro,
                $fecha_fin,
                $usuario_cierre,
                $status
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

$app->post('/incidencias/singlepdf', function() use ($app) {
    $id = $_POST['id'];
    $sql = "SELECT
                    Fol_folio AS folio,
                    (SELECT nombre FROM c_almacenp WHERE clave = centro_distribucion) AS centro,
                    (SELECT RazonSocial FROM c_cliente WHERE Cve_Clte = cliente) AS cliente,
                    ID_Incidencia AS numero,
                    (
                        CASE
                                WHEN tipo_reporte = 'P' THEN 'Petición'
                                WHEN tipo_reporte = 'Q' THEN 'Queja'
                                WHEN tipo_reporte = 'R' THEN 'Reclamo'
                                WHEN tipo_reporte = 'S' THEN 'Sugerencia'
                        END
                    ) AS tipo_reporte,
                    reportador,
                    cargo_reportador,
                    DATE_FORMAT(Fecha, '%d-%m-%Y') AS fecha_reporte,
                    Descripcion  AS descripcion,
                    (SELECT nombre_completo FROM c_usuario WHERE cve_usuario = responsable_recibo) AS responsable_recibo,
                    responsable_caso,
                    plan_accion,
                    (SELECT nombre_completo FROM c_usuario WHERE cve_usuario = responsable_plan) AS responsable_plan,
                    DATE_FORMAT(Fecha_accion, '%d-%m-%Y') AS fecha_plan,
                    (SELECT nombre_completo FROM c_usuario WHERE cve_usuario = responsable_verificacion) AS responsable_verificacion,
                    (
                        CASE
                                WHEN status = 'A' THEN 'Abierto'
                                WHEN status = 'C' THEN 'Cerrado'
                        END
                    ) AS status
            FROM th_incidencia
            WHERE ID_Incidencia = {$id} AND Activo = 1;";
    mysqli_set_charset(\db2(), 'utf8');
    $query = mysqli_query(\db2(), $sql);
    ob_clean();
    ob_start();
    ?>

    <?php
    $content = ob_get_clean();
    if($query->num_rows > 0){
        $res = mysqli_fetch_array($query);
        ob_start();
        ?>
        <table width="780" cellspacing="1" cellpadding="10" border="0" bgcolor="#020202" style="border:1px solid black;border-right:0px; border-top: 0px; border-width: 1px"> 
<tr> 
   <td bgcolor="#FEFDFD"> 
   <font size=4 face="arial, verdana, helvetica"> 
   <b>CENTRO DISTRIBUCION</b><?php echo $res['centro']?> &nbsp;&nbsp;&nbsp;&nbsp;<b>CLIENTE</b>  <?php echo $res['cliente']?>  &nbsp;&nbsp;&nbsp;&nbsp;<b>NRO</b>  <?php echo $res['folio']?>  
   </font> 
   </td> 
</tr> 
<tr> 
   <td bgcolor="#FEFDFD"> 
   <font size=4 face="arial, verdana, helvetica"> 
   <b>TIPO DE REPORTE:</b> 
    <?php if ($res['tipo_reporte'] == "Petición"){ ?>
    &nbsp;&nbsp;&nbsp;&nbsp;PETICION <input type="checkbox" id="cbox1" value="primer_checkbox" checked="true"> <?php }
    else{ ?>
    &nbsp;&nbsp;&nbsp;&nbsp;PETICION <input type="checkbox" id="cbox1" value="primer_checkbox">
    <?php  } 
    if ($res['tipo_reporte'] == "Queja"){ ?>
    &nbsp;&nbsp;&nbsp;&nbsp;QUEJA <input type="checkbox" id="cbox2" value="second_checkbox" checked="true"> <?php } 
    else{ ?>
    &nbsp;&nbsp;&nbsp;&nbsp;QUEJA <input type="checkbox" id="cbox2" value="second_checkbox"> <?php } 
    if ($res['tipo_reporte'] == "Reclamo"){ ?>
    &nbsp;&nbsp;&nbsp;&nbsp;RECLAMO <input type="checkbox" id="cbox3" value="tercer_checkbox" checked="true"> <?php }
    else{ ?>
    &nbsp;&nbsp;&nbsp;&nbsp;RECLAMO <input type="checkbox" id="cbox3" value="tercer_checkbox"> <?php }
    if ($res['tipo_reporte'] == "Sugerencia"){ ?>
    &nbsp;&nbsp;&nbsp;&nbsp;SUGERENCIA <input type="checkbox" id="cbox4" value="cuarto_checkbox" checked="true"> <?php }
    else{ ?>
    &nbsp;&nbsp;&nbsp;&nbsp;SUGERENCIA <input type="checkbox" id="cbox4" value="cuarto_checkbox"> <?php } ?>
   </font> 
  
   </td> 
</tr>
</table>
<br>
<table width="750" height="10" cellspacing="1" cellpadding="7" border="0" bgcolor="#020202" style="border:1px solid black;border-right:0px; border-top: 0px; border-width: 1px"> 
<tr align="center">
   <td width="360" bgcolor="#C4C1C1"> 
   <font size=2 face="arial, verdana, helvetica"> 
   <b>NOMBRE DE QUIEN REPORTA</b>
   </font> 
   </td> 
   <td width="200" bgcolor="#C4C1C1"> 
   <font size=2 face="arial, verdana, helvetica"> 
   <b>CARGO</b>
   </font> 
   </td>
   <td bgcolor="#C4C1C1"> 
   <font size=2 face="arial, verdana, helvetica"> 
   <b>FECHA</b>
   </font> 
   </td>
</tr> 
<tr align="center" height="30"> 
   <td bgcolor="#FEFDFD"> 
   <font size=4 face="arial, verdana, helvetica"> 
    <?php echo $res['reportador']?>
   </font> 
   </td> 
   <td bgcolor="#FEFDFD"> 
   <font size=4 face="arial, verdana, helvetica"> 
    <?php echo $res['cargo_reportador']?>
   </font> 
   </td> 
   <td bgcolor="#FEFDFD"> 
   <font size=4 face="arial, verdana, helvetica"> 
   <?php echo $res['fecha_reporte']?>
   </font> 
   </td>
 
</tr>
</table>
<br>
 <table width="750" height="10" cellspacing="1" cellpadding="7" border="0" bgcolor="#020202" style="border:1px solid black;border-right:0px; border-top: 0px; border-width: 1px"> 
<tr>
   <td bgcolor="#C4C1C1"> 
   <font size=2 face="arial, verdana, helvetica"> 
   <b>1. DESCRIPCION DE LA PQRS: (Hechos y datos concretos, claros, precisos y verificables</b>
   </font> 
   </td> 
</tr> 
<tr> 
   <td bgcolor="#FEFDFD"> 
   <font face="arial, verdana, helvetica"> 
    <?php echo $res['descripcion']?>
   </font> 
   </td> 
</tr>
</table>
<br>
<table width="750" height="10" cellspacing="1" cellpadding="7" border="0" bgcolor="#020202"> 
<tr>
   <td bgcolor="#C4C1C1" style="border:1px solid black;border-right:0px; border-top: 0px; border-width: 1px"> 
   <font size=2 face="arial, verdana, helvetica"> 
   <b>2. PARA DILIGENCIAMIENTO DE LA ATENCION DE LA PQRS</b>
   </font> 
   </td> 
</tr> 
<tr> 
   <td bgcolor="#FEFDFD" style="border:1px solid black;border-right:0px; border-top: 0px; border-width: 1px"> 
   <font size=2 face="arial, verdana, helvetica"> 
   Responsable de recibir la PQRS: <h3><?php echo $res['responsable_recibo']?></h3>
   </font> 
   </td> 
</tr>
</table>
 <table width="750" height="10" border=0 cellpadding="7" cellspacing="1" style="border:0.5px solid black;" bordercolor=#020202> 
<tr>
   <td bgcolor="#C4C1C1" style="border:0.5px solid black;border-right:0px; border-top: 0px; border-width: 1px 0"> 
   <font size=2 face="arial, verdana, helvetica"> 
   <b>3. VERIFICACION DE LA PQRS</b>
   </font> 
   </td> 
</tr> 
<tr > 
   <td bgcolor="#FEFDFD"> 
   <font size=2 face="arial, verdana, helvetica"> 
   Responsable del caso reportado: 
   </font> 
   </td> 
</tr>
<tr > 
   <td bgcolor="#FEFDFD"> 
   <font size=2 face="arial, verdana, helvetica"> 
    <?php if ($res['responsable_caso'] == "ASL"){ ?>
    &nbsp;&nbsp;&nbsp;&nbsp;ASL <input type="radio" name="group1" checked="true"> <?php }
    else{ ?>
    &nbsp;&nbsp;&nbsp;&nbsp;ASL <input type="radio" name="group1">
    <?php  }
    if ($res['responsable_caso'] == "Transportador"){ ?>
    &nbsp;&nbsp;&nbsp;&nbsp;TRANSPORTADOR <input type="radio" name="group2" checked="true"> <?php }
    else{ ?>
    &nbsp;&nbsp;&nbsp;&nbsp;TRANSPORTADOR <input type="radio" name="group2">
    <?php  }
    if ($res['responsable_caso'] == "Cliente"){ ?>
    &nbsp;&nbsp;&nbsp;&nbsp;CLIENTE <input type="radio" name="group3" checked="true"> <?php }
    else{ ?>
    &nbsp;&nbsp;&nbsp;&nbsp;CLIENTE <input type="radio" name="group3">
    <?php  }
    if ($res['responsable_caso'] != "Cliente" && $res['responsable_caso'] != "Transportador" && $res['responsable_caso'] != "ASL"){ ?>
    &nbsp;&nbsp;&nbsp;&nbsp;OTRO <input type="radio" name="group4" checked="true"> ¿Cual?:<?php echo $res['responsable_caso']?> <?php } 
     else{ ?>
    &nbsp;&nbsp;&nbsp;&nbsp;OTRO <input type="radio" name="group4"> &nbsp;&nbsp;¿Cual?: ______________________
    <?php  }?>
    </font>
   </td> 
</tr>
</table>
<table width="750" height="10" border=0 cellpadding="7" bgcolor="#C4C1C1" style="border:1px solid black; "> 
<tr>
   <td bgcolor="#C4C1C1" style="border:1px solid black; border-right:0px; border-top: 0px; border-width: 1px 0;" COLSPAN="3"> 
   <font size=2 face="arial, verdana, helvetica"> 
   <b>4. PLANTEAMIENTO DE ACCIONES</b>
   </font> 
   </td> 
</tr> 
<tr align="center" height="5"> 
   <td bgcolor="#FEFDFD"> 
   <font size=1 face="arial, verdana, helvetica"> 
   <b>PLAN DE ACCION</b>
   </font> 
   </td> 
   <td bgcolor="#FEFDFD"> 
   <font size=1 face="arial, verdana, helvetica"> 
   <b>RESPONSABLE</b>
   </font> 
   </td> 
   <td bgcolor="#FEFDFD"> 
   <font size=1 face="arial, verdana, helvetica"> 
   <b>FECHA</b>
   </font> 
   </td> 
</tr> 
<tr align="center" height="25"> 
   <td bgcolor="#FEFDFD"> 
   <font face="arial, verdana, helvetica"> 
    <?php echo $res['plan_accion']?>
   </font> 
   </td> 
   <td bgcolor="#FEFDFD"> 
   <font face="arial, verdana, helvetica"> 
    <?php echo $res['responsable_plan']?>
   </font> 
   </td> 
   <td bgcolor="#FEFDFD"> 
   <font face="arial, verdana, helvetica"> 
    <?php echo $res['fecha_plan']?>
   </font> 
   </td> 
</tr>
</table>
<table width="750" height="10" border=0 cellpadding="7" style="border:0.5px solid black;"cellspacing="1" bordercolor=#000000> 
<tr>
   <td bgcolor="#C4C1C1" style="border:1px solid black; border-right:0px; border-top: 0px; border-width: 1px 0;" COLSPAN="2"> 
   <font size=2 face="arial, verdana, helvetica"> 
   <b>5. VERIFICACION DE ACCIONES TOMADAS: SOLUCION Y SATISFACCION</b>
   </font> 
   </td> 
</tr> 
<tr> 
   <td bgcolor="#FEFDFD"> 
   <font size=2 face="arial, verdana, helvetica"> 
   RESPONSABLE:<?php echo $res['responsable_verificacion']?> 
   <br>
   <p>
   <font size=2 face="arial, verdana, helvetica"> 
   FIRMA: __________________________
   </font> 
    </p>
</br>
   </font> 
   </td>
   <td style="border:1px solid black; border-right:0px; border-top: 0px; border-width: 1px; "> 
   <font size=1 face="arial, verdana, helvetica"> 
   <b>Cierre de la PQRS</b>
   <div row>
   <?php 
    if ($res['status'] == "Abierto"){ ?>
    <h2>SI</h2> <input type="checkbox" id="cbox5"><div row><h2>NO</h2> <input type="checkbox" id="cbox5" checked="true"> <?php }
    else{ ?>
    <h2>SI</h2> <input type="checkbox" id="cbox5" checked="true"><div row><h2>NO</h2> <input type="checkbox" id="cbox5">  <?php } ?>
   </font> 
   </td>  
</tr>

</table>
        <?php
        $body = ob_get_clean();
        ob_end_flush();
    }
    $content .= $body;
    $content .= '</table>';
    $pdf = new \ReportePDF\PDF($_POST['cia'], $_POST['title']." Nº".$res['numero']);
    $pdf->setContent($content);
    ob_clean();
    $pdf->stream();
    $app->stop();
    exit;
});

$app->post('/incidencias/singleexcel', function() use ($app) {
    include dirname(__DIR__).'/vendor/mk-j/php_xlsxwriter/xlsxwriter.class.php';
    $title = $_POST['title'].".xlsx";
    $excel = new XLSXWriter();
    $header = array(
        'Nº'                                => 'string',
        'Centro de Distribución'            => 'string',
        'Cliente'                           => 'string',
        'Tipo de Reporte'                   => 'string',
        'Nombre (Quien Reporta)'            => 'string',
        'Cargo'                             => 'string',
        'Fecha de Reporte'                  => 'string',
        'Descripcion de la PQRS'            => 'string',
        'Responsable de recibir la PQRS'    => 'string',
        'Responsable del caso reportado'    => 'string',
        'Plan de Acción'                    => 'string',
        'Responsable del Plan de Acción'    => 'string',
        'Fecha de Acción'                   => 'string',
        'Responsable de Verificación'       => 'string',
        'Status de la PQRS'                 => 'string',
        'Número de Folio o Factura'         => 'string',
    );
    $excel->writeSheetHeader('Sheet1', $header);
    $id = $_POST['id'];
    $sql = "SELECT
                    Fol_folio AS folio,
                    (SELECT nombre FROM c_almacenp WHERE clave = centro_distribucion) AS centro,
                    (SELECT RazonSocial FROM c_cliente WHERE Cve_Clte = cliente) AS cliente,
                    ID_Incidencia AS numero,
                    (
                        CASE
                                WHEN tipo_reporte = 'P' THEN 'Petición'
                                WHEN tipo_reporte = 'Q' THEN 'Queja'
                                WHEN tipo_reporte = 'R' THEN 'Reclamo'
                                WHEN tipo_reporte = 'S' THEN 'Sugerencia'
                        END
                    ) AS tipo_reporte,
                    reportador,
                    cargo_reportador,
                    DATE_FORMAT(Fecha, '%d-%m-%Y') AS fecha_reporte,
                    Descripcion  AS descripcion,
                    (SELECT nombre_completo FROM c_usuario WHERE cve_usuario = responsable_recibo) AS responsable_recibo,
                    responsable_caso,
                    plan_accion,
                    (SELECT nombre_completo FROM c_usuario WHERE cve_usuario = responsable_plan) AS responsable_plan,
                    DATE_FORMAT(Fecha_accion, '%d-%m-%Y') AS fecha_plan,
                    (SELECT nombre_completo FROM c_usuario WHERE cve_usuario = responsable_verificacion) AS responsable_verificacion,
                    (
                        CASE
                                WHEN status = 'A' THEN 'Abierto'
                                WHEN status = 'C' THEN 'Cerrado'
                        END
                    ) AS status
            FROM th_incidencia
            WHERE ID_Incidencia = {$id} AND Activo = 1;";
    $query = mysqli_query(\db2(), $sql);
    if($query->num_rows > 0){
        $res = mysqli_fetch_array($query);
        $row = array(
            $res['numero'],
            $res['centro'],
            $res['cliente'],
            $res['tipo_reporte'],
            $res['reportador'],
            $res['cargo_reportador'],
            $res['fecha_reporte'],
            $res['descripcion'],
            $res['responsable_recibo'],
            $res['responsable_caso'],
            $res['plan_accion'],
            $res['responsable_plan'],
            $res['fecha_plan'],
            $res['responsable_verificacion'],
            $res['status'],
            $res['folio'],
        );
        $excel->writeSheetRow('Sheet1', $row );
    }
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $title . '"');
    header('Cache-Control: max-age=0');
    $excel->writeToStdOut($title);
    $app->stop();
});
?><?php
$app->map('/importar/clientes', function() use ($app) {

    phpinfo();

})->via( 'GET');?><?php

/**
 * clientes
 **/

$app->map('/inventariosciclicos', function() use ($app) {

    $app->render( 'page/inventariosciclicos/index.php' );

})->via( 'GET', 'POST' );


/**
 * Lists
 **/

$app->map('/inventariosciclicos/lists', function() use ($app) {

    $app->render( 'page/inventariosciclicos/lists.php' );

})->via( 'GET', 'POST' );


/**
 * Pending
 **/

$app->map('/inventariosciclicos/pending', function() use ($app) {

    $app->render( 'page/inventariosciclicos/pending.php' );

})->via( 'GET', 'POST' );


/**
 * Edit Subscriber
 **/

$app->map('/inventariosciclicos/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );

/**
 * View Subscriber
 **/

$app->map('/inventariosciclicos/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/inventariosciclicos/view.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );
?><?php

  /**
    * protocolos
  **/

  $app->map('/inventariosfisicos', function() use ($app) {

    $app->render( 'page/inventariosfisicos/index.php' );

  })->via( 'GET', 'POST' );


  /**
    * Lists
  **/

  $app->map('/inventariosfisicos/lists', function() use ($app) {

    $app->render( 'page/inventariosfisicos/lists.php' );

  })->via( 'GET', 'POST' );


/**
    * Lists
  **/

  $app->map('/inventariosfisicos/admin', function() use ($app) {

    $app->render( 'page/inventariosfisicos/admin_v2.php' );

  })->via( 'GET', 'POST' );


  /**
    * Pending
  **/

  $app->map('/inventariosfisicos/pending', function() use ($app) {

    $app->render( 'page/inventariosfisicos/pending.php' );

  })->via( 'GET', 'POST' );


  /**
    * Edit Subscriber
  **/

  $app->map('/inventariosfisicos/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
      'id_subscriber' => $id_subscriber
    ) );

  })->via( 'GET', 'POST' );

  /**
    * View Subscriber
  **/

  $app->map('/inventariosfisicos/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/inventariosfisicos/view.php', array(
      'id_subscriber' => $id_subscriber
    ) );

  })->via( 'GET', 'POST' );
?><?php

  /**
    * Frontend
  **/

  $app->get('/ipn', function( ) use ( $app ) {

    $transactions = new \Transactions\Transactions();
    $mp = new MP( MERCADO_CLIENT, MERCADO_SECRET );

    $params = ["access_token" => $mp->get_access_token()];

    // Get the payment reported by the IPN. Glossary of attributes response in https://developers.mercadopago.com
    if($_GET["topic"] == 'payment'){
    	$payment_info = $mp->get("/collections/notifications/" . $_GET["id"], $params, false);
    	$merchant_order_info = $mp->get("/merchant_orders/" . $payment_info["response"]["collection"]["merchant_order_id"], $params, false);
    // Get the merchant_order reported by the IPN. Glossary of attributes response in https://developers.mercadopago.com
    }else if($_GET["topic"] == 'merchant_order'){
    	$merchant_order_info = $mp->get("/merchant_orders/" . $_GET["id"], $params, false);
    }

    //If the payment's transaction amount is equal (or bigger) than the merchant order's amount you can release your items
    if ($merchant_order_info["status"] == 200) {
    	$transaction_amount_payments= 0;
    	$transaction_amount_order = $merchant_order_info["response"]["total_amount"];
        $payments=$merchant_order_info["response"]["payments"];
        foreach ($payments as  $payment) {
        	if($payment['status'] == 'approved'){
    	    	$transaction_amount_payments += $payment['transaction_amount'];
    	    }
        }
        if($transaction_amount_payments >= $transaction_amount_order){


          $transactions->save( array(
            'id_user' => 1
          , 'name' => ''
          , 'email' => ''
          , 'transaction_id' => ''
          , 'transaction_amount' => $merchant_order_info["response"]["total_amount"]
          , 'transaction_fee' => ''
          ) );


          print_r( $merchant_order_info['response'] );

        } else{
          print_r( $merchant_order_info['response'] );
    		echo "dont release your items";
    	}
    }

  });
?><?php

  /**
    * protocolos
  **/

 $app->map('/kardex', function() use ($app) {

    $app->render( 'page/kardex/index.php' );

})->via( 'GET', 'POST' );

 $app->map('/kardexw', function() use ($app) {

    $app->render( 'page/kardex/indexw.php' );

})->via( 'GET', 'POST' );

?><?php

  /**
    * protocolos
  **/

  $app->map('/lotes', function() use ($app) {

    $app->render( 'page/lotes/index.php' );

  })->via( 'GET', 'POST' );



/*
* importar
*/
$app->get('/api/v2/lotes/exportar', 'Application\Controllers\LotesController:exportar');
$app->post('/lotes/importar', 'Application\Controllers\LotesController:importar');


$app->post('/lotes/import', function() use($app){
    /* Archivo Excel del formulario */
    $file = $_FILES['layout']['tmp_name'];
    /* Creando lector excel */
    $inputFileType = PHPExcel_IOFactory::identify($file);
    $objReader = PHPExcel_IOFactory::createReader($inputFileType);
    $objPHPExcel = $objReader->load($file);
    /* Obteniendo hoja activa */
    $sheet = $objPHPExcel->getActiveSheet();
    /* Obteniendo identificador de última columna y fila */
    $sheetInfo = $sheet->getHighestRowAndColumn();
    $highestRow = $sheetInfo['row'];
    $highestColumn = $sheetInfo['column'];
    /* Empezamos en A2 ya que A1 corresponde a los titulos */
    $sheetRange = "A2:$highestColumn$highestRow";
    $rowData = $sheet->rangeToArray($sheetRange);

    /* Guardando sql para ejecutarlos juntos */
    $insert = "";
    /* Leyendo cada celda del excel */
    foreach($rowData as $cell){
        $data = [
            "c_lotes.cve_articulo"      =>  $cell[0],
            "c_lotes.LOTE"      =>  $cell[1],
            "c_lotes.CADUCIDAD"       =>  $cell[2]
        ];
        $last_key = key( array_slice( $data, -1, 1, TRUE ) );
        $insertStm = "INSERT IGNORE INTO c_lotes SET ";
        $set = "";
        foreach($data as $setName => $setValue){
            if(!empty($setValue)){
                $set .= " $setName = \"$setValue\"";
                 if($setName !== $last_key){
                    $set .= ",";
                }
            }
        }
        $insertStm .= $set."; ";
        $insert .= $insertStm;
    }
    $query = mysqli_multi_query(\db2(), $insert);
    if($query){
        $app->response->redirect('/lotes/lists');
    }
});

  /**
    * Lists
  **/

  $app->map('/lotes/lists', function() use ($app) {

    $app->render( 'page/lotes/lists.php' );

  })->via( 'GET', 'POST' );


  /**
    * Pending
  **/

  $app->map('/lotes/pending', function() use ($app) {

    $app->render( 'page/lotes/pending.php' );

  })->via( 'GET', 'POST' );


  /**
    * Edit Subscriber
  **/

  $app->map('/lotes/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
      'id_subscriber' => $id_subscriber
    ) );

  })->via( 'GET', 'POST' );

  /**
    * View Subscriber
  **/

  $app->map('/lotes/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/lotes/view.php', array(
      'id_subscriber' => $id_subscriber
    ) );

  })->via( 'GET', 'POST' );

  

  /**
    * Press Release
  **/

  $app->map('/mail/press', function() use ($app) {

    $app->render( 'page/mail/press.php' );

  })->via( 'GET', 'POST' );

  /**
    * Edit Press Release
  **/

  $app->map('/mail/press/:id_template', function( $id_template ) use ($app) {

    $app->render( 'page/mail/edit.php', array(
      'id_template' => $id_template
    ) );

  })->via( 'GET', 'POST' );


  /**
    * Edit Press Release
  **/

  $app->map('/mail/invitations/:id_template', function( $id_template ) use ($app) {

    $app->render( 'page/mail/view.php', array(
      'id_template' => $id_template
    ) );

  })->via( 'GET', 'POST' );


  /**
    * Upload file
  **/

  $app->map('/mail/upload', function() use ($app) {

    $app->render( 'page/mail/upload.php' );

  })->via( 'GET', 'POST' );


  /**
    * Invitations
  **/

  $app->map('/mail/invitations', function() use ($app) {

    $app->render( 'page/mail/invitations.php' );

  })->via( 'GET', 'POST' );


$app->map('/reportes/rutas/estadodecuenta', function() use ($app) {

    $app->render( 'page/reportes/rutas/estadodecuenta.php' );

})->via( 'GET', 'POST' );

$app->map('/reportes/rutas/reporte_liquidacion_concentrado', function() use ($app) {

    $app->render( 'page/reportes/rutas/reporte_liquidacion_concentrado.php' );

})->via( 'GET', 'POST' );

$app->map('/reportes/rutas/venta_venta', function() use ($app) {

    $app->render( 'page/reportes/rutas/venta_venta.php' );

})->via( 'GET', 'POST' );

$app->map('/reportes/rutas/cobranza', function() use ($app) {

    $app->render( 'page/reportes/rutas/cobranza.php' );

})->via( 'GET', 'POST' );

$app->map('/reportes/rutas/cobranza2', function() use ($app) {

    $app->render( 'page/reportes/rutas/cobranza2.php' );

})->via( 'GET', 'POST' );

$app->map('/reportes/rutas/cobranzaconsolidado', function() use ($app) {

    $app->render( 'page/reportes/rutas/cobranzaconsolidado.php' );

})->via( 'GET', 'POST' );

$app->map('/reportes/rutas/inventario', function() use ($app) {

    $app->render( 'page/reportes/rutas/reprut_inventario.php' );

})->via( 'GET', 'POST' );

$app->map('/reportes/rutas/bitacora', function() use ($app) {

    $app->render( 'page/reportes/rutas/bitacoratiempos.php' );

})->via( 'GET', 'POST' );

$app->map('/reportes/envase/list', function() use ($app) {

    $app->render( 'page/reportes/rutas/envases.php' );

})->via( 'GET', 'POST' );



$app->map('/reportes/vpc', function() use ($app) {

    $app->render( 'page/reportes/rutas/vpc.php' );

})->via( 'GET', 'POST' );


$app->map('/reportes/semaforoventas', function() use ($app) {

    $app->render( 'page/reportes/rutas/semaforodeventas.php' );

})->via( 'GET', 'POST' );


$app->map('/reportes/controlentregas', function() use ($app) {

    $app->render( 'page/reportes/rutas/controldeentregas.php' );

})->via( 'GET', 'POST' );


$app->map('/reportes/pedidosruta', function() use ($app) {

    $app->render( 'page/reportes/rutas/pedidosrutas.php' );

})->via( 'GET', 'POST' );



$app->map('/reportes/rutas/noventas', function() use ($app) {

    $app->render( 'page/reportes/rutas/noventas.php' );

})->via( 'GET', 'POST' );

$app->map('/reportes/rutas/descargas', function() use ($app) {

    $app->render( 'page/reportes/rutas/descargas.php' );

})->via( 'GET', 'POST' );

$app->map('/reportes/mobiliario/list', function() use ($app) {

    $app->render( 'page/reportes/rutas/mobiliario.php' );

})->via( 'GET', 'POST' );


$app->map('/reportes/list', function() use ($app) {

    $app->render( 'page/reportes/rutas/reportes_sfa.php' );

})->via( 'GET', 'POST' );


$app->map('/reportes/list2', function() use ($app) {

    $app->render( 'page/reportes/rutas/reportes_sfa2.php' );

})->via( 'GET', 'POST' );


$app->map('/reportes/preventa/list', function() use ($app) {

    $app->render( 'page/reportes/rutas/reportes_sfa_preventa.php' );

})->via( 'GET', 'POST' );

$app->map('/reportes/entregas/list', function() use ($app) {

    $app->render( 'page/reportes/rutas/reportes_sfa_entregas.php' );

})->via( 'GET', 'POST' );


/**
 * pedidos
 **/

$app->map('/manufactura', function() use ($app) {

    $app->render( 'page/manufactura/index.php' );

})->via( 'GET', 'POST' );

$app->map('/manufactura/editarcomponentes', function() use ($app) {

    $app->render( 'page/manufactura/editarcomponentes.php' );

})->via( 'GET', 'POST' );


$app->map('/manufactura/ordentrabajo', function() use ($app) {

    $app->render( 'page/ordentrabajo/agregarorden.php' );

})->via( 'GET', 'POST' );

$app->map('/manufactura/administracion', function() use ($app) {

    $app->render( 'page/adminordentrabajo/list.php' );

})->via( 'GET', 'POST' );

/**
 * Edit Subscriber
 **/

$app->map('/manufactura/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );

/**
 * View Subscriber
 **/

$app->map('/manufactura/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/manufactura/view.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );



/**
 * pedidos
 **/

$app->map('/maximosyminimos', function() use ($app) {

    $app->render( 'page/maximosyminimos/index.php' );

})->via( 'GET', 'POST' );


/**
 * Lists
 **/

$app->map('/maximosyminimos/lists', function() use ($app) {

    $app->render( 'page/maximosyminimos/lists.php' );

})->via( 'GET', 'POST' );


/**
 * Pending
 **/

$app->map('/maximosyminimos/pending', function() use ($app) {

    $app->render( 'page/maximosyminimos/pending.php' );

})->via( 'GET', 'POST' );


/**
 * Edit Subscriber
 **/

$app->map('/maximosyminimos/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );

/**
 * View Subscriber
 **/

$app->map('/maximosyminimos/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/maximosyminimos/view.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );



/**
 * motivodevolucion
 **/

$app->map('/motivodedevolucion', function() use ($app) {

    $app->render( 'page/motivodedevolucion/index.php' );

})->via( 'GET', 'POST' );


/**
 * características
 **/

$app->map('/caracteristicas/tipos', function() use ($app) {

    $app->render( 'page/caracteristicas/tipos.php' );

})->via( 'GET', 'POST' );

$app->map('/caracteristicas', function() use ($app) {

    $app->render( 'page/caracteristicas/index.php' );

})->via( 'GET', 'POST' );


/*
* importar
*/



$app->post('/motivo-de-devolucion/importar', 'Application\Controllers\MotivosDeDevolucionController:importar');
$app->get('/api/v2/motivo-de-devolucion/exportar', 'Application\Controllers\MotivosDeDevolucionController:exportar');


$app->post('/motivodedevolucion/import', function() use($app){
    /* Archivo Excel del formulario */
    $file = $_FILES['layout']['tmp_name'];
    /* Creando lector excel */
    $inputFileType = PHPExcel_IOFactory::identify($file);
    $objReader = PHPExcel_IOFactory::createReader($inputFileType);
    $objPHPExcel = $objReader->load($file);
    /* Obteniendo hoja activa */
    $sheet = $objPHPExcel->getActiveSheet();
    /* Obteniendo identificador de última columna y fila */
    $sheetInfo = $sheet->getHighestRowAndColumn();
    $highestRow = $sheetInfo['row'];
    $highestColumn = $sheetInfo['column'];
    /* Empezamos en A2 ya que A1 corresponde a los titulos */
    $sheetRange = "A2:$highestColumn$highestRow";
    $rowData = $sheet->rangeToArray($sheetRange);

    /* Guardando sql para ejecutarlos juntos */
    $insert = "";
    /* Leyendo cada celda del excel */
    foreach($rowData as $cell){
        $data = [
            "motivos_devolucion.MOT_DESC"      =>  $cell[0],
            "motivos_devolucion.Clave_motivo"      =>  $cell[1]
        ];
        $last_key = key( array_slice( $data, -1, 1, TRUE ) );
        $insertStm = "INSERT IGNORE INTO motivos_devolucion SET ";
        $set = "";
        foreach($data as $setName => $setValue){
            if(!empty($setValue)){
                $set .= " $setName = \"$setValue\"";
                 if($setName !== $last_key){
                    $set .= ",";
                }
            }
        }
        $insertStm .= $set."; ";
        $insert .= $insertStm;
    }
    $query = mysqli_multi_query(\db2(), $insert);
    if($query){
        $app->response->redirect('/motivodedevolucion/lists');
    }
});

$app->map('/motivoajuste/list', function() use ($app) {

    $app->render( 'page/motivoajuste/lists.php' );

})->via( 'GET', 'POST' ); 


$app->map('/motivocuarentena/list', function() use ($app) {

    $app->render( 'page/motivocuarentena/lists.php' );

})->via( 'GET', 'POST' ); 
/**
 * Lists
 **/

$app->map('/motivodevolucion/list', function() use ($app) {

    $app->render( 'page/motivodevolucion/lists.php' );

})->via( 'GET', 'POST' );

$app->map('/motivosnoventas/list', function() use ($app) {

    $app->render( 'page/motivosnoventas/lists.php' );

})->via( 'GET', 'POST' );


/**
 * Pending
 **/

$app->map('/motivodevolucion/pending', function() use ($app) {

    $app->render( 'page/motivodevolucion/pending.php' );

})->via( 'GET', 'POST' );


/**
 * Edit Subscriber
 **/

$app->map('/motivodevolucion/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );

/**
 * View Subscriber
 **/

$app->map('/motivodevolucion/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/motivodevolucion/view.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );



/**
 * clientes
 **/

$app->map('/moveryacomodar', function() use ($app) {

    $app->render( 'page/moveryacomodar/index.php' );

})->via( 'GET', 'POST' );


/**
 * Lists
 **/
/*
$app->map('/moveryacomodar/lists', function() use ($app) {

    $app->render( 'page/moveryacomodar/lists.php' );

})->via( 'GET', 'POST' );
*/
$app->map('/moveryacomodar/acomodo', function() use ($app) {

    $app->render( 'page/moveryacomodar/acomodo.php' );

})->via( 'GET', 'POST' );

$app->map('/moveryacomodar/traslado', function() use ($app) {

    $app->render( 'page/moveryacomodar/traslado.php' );

})->via( 'GET', 'POST' );

$app->map('/movimientosdelalmacen', function() use ($app) {

    $app->render( 'page/moveryacomodar/movimientos.php' );

})->via( 'GET', 'POST' );

/**
 * Pending
 **/

$app->map('/moveryacomodar/pending', function() use ($app) {

    $app->render( 'page/moveryacomodar/pending.php' );

})->via( 'GET', 'POST' );


/**
 * Edit Subscriber
 **/

$app->map('/moveryacomodar/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );

/**
 * View Subscriber
 **/

$app->map('/moveryacomodar/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/moveryacomodar/view.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );




/**
 * nuevospedidos
 **/

$app->map('/nuevospedidos', function() use ($app) {

    $app->render( 'page/nuevospedidos/index.php' );

})->via( 'GET', 'POST' );


/**
 * Lists
 **/

$app->map('/nuevospedidos/lists', function() use ($app) {

    $app->render( 'page/nuevospedidos/lists.php' );

})->via( 'GET', 'POST' );

$app->map('/maniobras/lists', function() use ($app) {

    $app->render( 'page/maniobras/lists.php' );

})->via( 'GET', 'POST' );

$app->map('/vas/administrador', function() use ($app) {

    $app->render( 'page/vasadministrador/cobranza.php' );

})->via( 'GET', 'POST' );

$app->map('/vas/proformas', function() use ($app) {

    $app->render( 'page/vasproformas/list.php' );

})->via( 'GET', 'POST' );



/**
 * clientes
 **/

$app->map('/ordendecompra', function() use ($app) {

    $app->render( 'page/ordendecompra/index.php' );

})->via( 'GET', 'POST' );


/**
 * Lists
 **/

$app->map('/ordendecompra/lists', function() use ($app) {

    $app->render( 'page/ordendecompra/lists.php' );

})->via( 'GET', 'POST' );

$app->map('/ordendetraslado/lists', function() use ($app) {

    $app->render( 'page/ordendetraslado/lists.php' );

})->via( 'GET', 'POST' );


/**
 * Pending
 **/

$app->map('/ordendecompra/pending', function() use ($app) {

    $app->render( 'page/ordendecompra/pending.php' );

})->via( 'GET', 'POST' );


/**
 * Edit Subscriber
 **/

$app->map('/ordendecompra/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );

/**
 * View Subscriber
 **/

$app->map('/ordendecompra/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/ordendecompra/view.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );



/**
 * pedidos
 **/

$app->map('/pedidos', function() use ($app) {

    $app->render( 'page/pedidos/index.php' );

})->via( 'GET', 'POST' );


/**
 * Lists
 **/

$app->map('/pedidos/lists', function() use ($app) {

    $app->render( 'page/pedidos/lists.php' );

})->via( 'GET', 'POST' );


/**
 * Pending
 **/

$app->map('/pedidos/pending', function() use ($app) {

    $app->render( 'page/pedidos/pending.php' );

})->via( 'GET', 'POST' );


/**
 * Edit Subscriber
 **/

$app->map('/pedidos/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );

/**
 * View Subscriber
 **/

$app->map('/pedidos/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/pedidos/view.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );



  /**
    * tipodeprioridad
  **/

  $app->map('/pedidosurgentes', function() use ($app) {

    $app->render( 'page/pedidosurgentes/index.php' );

  })->via( 'GET', 'POST' );


  /**
    * Lists
  **/

  $app->map('/pedidosurgentes/lists', function() use ($app) {

    $app->render( 'page/pedidosurgentes/lists.php' );

  })->via( 'GET', 'POST' );


  /**
    * Pending
  **/

  $app->map('/pedidosurgentes/pending', function() use ($app) {

    $app->render( 'page/pedidosurgentes/pending.php' );

  })->via( 'GET', 'POST' );


  /**
    * Edit Subscriber
  **/

  $app->map('/pedidosurgentes/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
      'id_subscriber' => $id_subscriber
    ) );

  })->via( 'GET', 'POST' );

  /**
    * View Subscriber
  **/

  $app->map('/pedidosurgentes/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/pedidosurgentes/view.php', array(
      'id_subscriber' => $id_subscriber
    ) );

  })->via( 'GET', 'POST' );

  

/**
 * clientes
 **/

$app->map('/perfil', function() use ($app) {

    $app->render( 'page/perfil/index.php' );

})->via( 'GET', 'POST' );


/**
 * Lists
 **/

$app->get('/permisos', 'Application\Controllers\PermisosController:index');
$app->get('/api/v2/permisos/:id', 'Application\Controllers\PermisosController:find');


$app->map('/perfil/lists', function() use ($app) {

    $app->render( 'page/perfil/lists.php' );

})->via( 'GET', 'POST' );


/**
 * Pending
 **/

$app->map('/perfil/pending', function() use ($app) {

    $app->render( 'page/perfil/pending.php' );

})->via( 'GET', 'POST' );

$app->map('/dashboard/onlinetracking', function() use ($app) {

    $app->render( 'page/dashboard/onlinetracking.php' );

})->via( 'GET', 'POST' );


/**
 * Edit Subscriber
 **/

$app->map('/perfil/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );

/**
 * View Subscriber
 **/

$app->map('/perfil/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/perfil/view.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );?><?php

  /**
    * tipodeprioridad
  **/

  $app->map('/poblaciones', function() use ($app) {

    $app->render( 'page/poblaciones/index.php' );

  })->via( 'GET', 'POST' );


  /**
    * Lists
  **/

  $app->map('/poblaciones/lists', function() use ($app) {

    $app->render( 'page/poblaciones/lists.php' );

  })->via( 'GET', 'POST' );


  /**
    * Pending
  **/

  $app->map('/poblaciones/pending', function() use ($app) {

    $app->render( 'page/poblaciones/pending.php' );

  })->via( 'GET', 'POST' );


  /**
    * Edit Subscriber
  **/

  $app->map('/poblaciones/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
      'id_subscriber' => $id_subscriber
    ) );

  })->via( 'GET', 'POST' );

  /**
    * View Subscriber
  **/

  $app->map('/poblaciones/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/poblaciones/view.php', array(
      'id_subscriber' => $id_subscriber
    ) );

  })->via( 'GET', 'POST' );
?><?php

/**
 * pedidos
 **/

$app->map('/promocion', function() use ($app) {

    $app->render( 'page/promocion/index.php' );

})->via( 'GET', 'POST' );


/**
 * Lists
 **/

$app->map('/promocion/lists', function() use ($app) {

    $app->render( 'page/promocion/lists.php' );

})->via( 'GET', 'POST' );


/**
 * Pending
 **/

$app->map('/promocion/pending', function() use ($app) {

    $app->render( 'page/promocion/pending.php' );

})->via( 'GET', 'POST' );


/**
 * Edit Subscriber
 **/

$app->map('/promocion/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );

/**
 * View Subscriber
 **/

$app->map('/promocion/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/promocion/view.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );
?><?php

  /**
    * protocolos
  **/

  $app->map('/protocolos', function() use ($app) {

    $app->render( 'page/protocolos/index.php' );

  })->via( 'GET', 'POST' );


/*
* importar
*/

$app->post('/protocolos/importar', 'Application\Controllers\ProtocolosController:importar');
$app->get('/api/v2/protocolos/exportar', 'Application\Controllers\ProtocolosController:exportar');


$app->post('/protocolos/import', function() use($app){
    /* Archivo Excel del formulario */
    $file = $_FILES['layout']['tmp_name'];
    /* Creando lector excel */
    $inputFileType = PHPExcel_IOFactory::identify($file);
    $objReader = PHPExcel_IOFactory::createReader($inputFileType);
    $objPHPExcel = $objReader->load($file);
    /* Obteniendo hoja activa */
    $sheet = $objPHPExcel->getActiveSheet();
    /* Obteniendo identificador de última columna y fila */
    $sheetInfo = $sheet->getHighestRowAndColumn();
    $highestRow = $sheetInfo['row'];
    $highestColumn = $sheetInfo['column'];
    /* Empezamos en A2 ya que A1 corresponde a los titulos */
    $sheetRange = "A2:$highestColumn$highestRow";
    $rowData = $sheet->rangeToArray($sheetRange);

    /* Guardando sql para ejecutarlos juntos */
    $insert = "";
    /* Leyendo cada celda del excel */
    foreach($rowData as $cell){
        $data = [
            "t_protocolo.ID_Protocolo"      =>  $cell[0],
            "t_protocolo.descripcion"      =>  $cell[1],
            "t_protocolo.FOLIO"       =>  $cell[2]
        ];
        $last_key = key( array_slice( $data, -1, 1, TRUE ) );
        $insertStm = "INSERT IGNORE INTO t_protocolo SET ";
        $set = "";
        foreach($data as $setName => $setValue){
            if(!empty($setValue)){
                $set .= " $setName = \"$setValue\"";
                 if($setName !== $last_key){
                    $set .= ",";
                }
            }
        }
        $insertStm .= $set."; ";
        $insert .= $insertStm;
    }
    $query = mysqli_multi_query(\db2(), $insert);
    if($query){
        $app->response->redirect('/protocolos/lists');
    }
});


  /**
    * Lists
  **/

  $app->map('/protocolos/lists', function() use ($app) {

    $app->render( 'page/protocolos/lists.php' );

  })->via( 'GET', 'POST' );


  /**
    * Pending
  **/

  $app->map('/protocolos/pending', function() use ($app) {

    $app->render( 'page/protocolos/pending.php' );

  })->via( 'GET', 'POST' );


  /**
    * Edit Subscriber
  **/

  $app->map('/protocolos/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
      'id_subscriber' => $id_subscriber
    ) );

  })->via( 'GET', 'POST' );

  /**
    * View Subscriber
  **/

  $app->map('/protocolos/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/protocolos/view.php', array(
      'id_subscriber' => $id_subscriber
    ) );

  })->via( 'GET', 'POST' );
?><?php

  /**
    * proveedores
  **/

  $app->map('/proveedores', function() use ($app) {

    $app->render( 'page/proveedores/index.php' );

  })->via( 'GET', 'POST' );



/*
* importar
*/

$app->get('/api/v2/proveedores/exportar', 'Application\Controllers\ProveedoresController:exportar');
$app->post('/proveedores/importar', 'Application\Controllers\ProveedoresController:importar');


$app->post('/proveedores/import', function() use($app){
    /* Archivo Excel del formulario */
    $file = $_FILES['layout']['tmp_name'];
    /* Creando lector excel */
    $inputFileType = PHPExcel_IOFactory::identify($file);
    $objReader = PHPExcel_IOFactory::createReader($inputFileType);
    $objPHPExcel = $objReader->load($file);
    /* Obteniendo hoja activa */
    $sheet = $objPHPExcel->getActiveSheet();
    /* Obteniendo identificador de última columna y fila */
    $sheetInfo = $sheet->getHighestRowAndColumn();
    $highestRow = $sheetInfo['row'];
    $highestColumn = $sheetInfo['column'];
    /* Empezamos en A2 ya que A1 corresponde a los titulos */
    $sheetRange = "A2:$highestColumn$highestRow";
    $rowData = $sheet->rangeToArray($sheetRange);

    /* Guardando sql para ejecutarlos juntos */
    $insert = "";
    /* Leyendo cada celda del excel */
    foreach($rowData as $cell){
      //array_map("utf8_encode", $cell);
      $Empresa = $cell[0];
      $Nombre = addslashes($cell[1]);
      $RUT = $cell[2];
      $direccion = addslashes($cell[3]);
      $cve_dane = $cell[4];
      $ID_Externo = $cell[5];
      $cve_proveedor = $cell[6];
      $colonia = addslashes($cell[7]);
      $ciudad = addslashes($cell[8]);
      $estado = addslashes($cell[9]);
      $pais = addslashes($cell[10]);
      $telefono1 = $cell[11];
      $telefono2 = $cell[12];

      $insert .= "INSERT IGNORE INTO c_proveedores (Empresa, Nombre, RUT, direccion, cve_dane, ID_Externo, cve_proveedor, colonia, ciudad, estado, pais, telefono1, telefono2) VALUES ('$Empresa', '$Nombre', '$RUT', '$direccion', '$cve_dane', '$ID_Externo', '$cve_proveedor', '$colonia', '$ciudad', '$estado', '$pais', '$telefono1', '$telefono2');\n";
    }
    mysqli_set_charset(\db2(), 'utf8');
    $query = mysqli_multi_query(\db2(), $insert);
    if($query){
        $app->response->redirect('/proveedores/lists');
    }
});



  /**
    * Lists
  **/

  $app->map('/proveedores/lists', function() use ($app) {

    $app->render( 'page/proveedores/lists.php' );

  })->via( 'GET', 'POST' );


  /**
    * Pending
  **/

  $app->map('/proveedores/pending', function() use ($app) {

    $app->render( 'page/proveedores/pending.php' );

  })->via( 'GET', 'POST' );


  /**
    * Edit Subscriber
  **/

  $app->map('/proveedores/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
      'id_subscriber' => $id_subscriber
    ) );

  })->via( 'GET', 'POST' );

  /**
    * View Subscriber
  **/

  $app->map('/proveedores/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/proveedores/view.php', array(
      'id_subscriber' => $id_subscriber
    ) );

  })->via( 'GET', 'POST' );
?><?php

/**
 * clientes
 **/

$app->map('/qaauditoria', function() use ($app) {

    $app->render( 'page/qaauditoria/index.php' );

})->via( 'GET', 'POST' );


/**
 * Lists
 **/

$app->map('/qaauditoria/lists', function() use ($app) {

    $app->render( 'page/qaauditoria/lists.php' );

})->via( 'GET', 'POST' );




$app->map('/qaauditoria/admin', function() use ($app) {

    $app->render( 'page/qaauditoria/admin.php' );

})->via( 'GET', 'POST' );


/**
 * Pending
 **/

$app->map('/qaauditoria/pending', function() use ($app) {

    $app->render( 'page/qaauditoria/pending.php' );

})->via( 'GET', 'POST' );


/**
 * Edit Subscriber
 **/

$app->map('/qaauditoria/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );

/**
 * View Subscriber
 **/

$app->map('/qaauditoria/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/qaauditoria/view.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );


$app->get('/qacuarentena/lists', 'Application\Controllers\QACuarentenaController:index');
$app->get('/api/v1/qacuarentena/all', 'Application\Controllers\QACuarentenaController:paginate');
$app->get('/api/v1/qacuarentena/productos', 'Application\Controllers\QACuarentenaController:buscarProductos');
$app->post('/api/v1/qacuarentena/agregar', 'Application\Controllers\QACuarentenaController:agregarProductoACuarentena');
$app->post('/api/v1/qacuarentena/sacar', 'Application\Controllers\QACuarentenaController:sacarProductoDeCuarentena');


/*$app->group('/qacuarentena', function() use ($app)
{
    $app->get('/lists', function() use ($app) {
        $app->render( 'page/qacuarentena/index.php' );
    });
});
*/



/**
 * Edit Subscriber
 **/

$app->map('/qacuarentena/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );

/**
 * View Subscriber
 **/

$app->map('/qacuarentena/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/qacuarentena/view.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );
?><?php

/**
 * Lists
 **/

$app->map('/qaguia/lists', function() use ($app) {

    $app->render( 'page/qaguia/lists.php' );

})->via( 'GET', 'POST' );
?><?php

/**
 * protocolos
 **/

$app->map('/reabasto', function() use ($app) {

    $app->render( 'page/reabasto/reabasto.php' );

})->via( 'GET', 'POST' );


$app->map('/reabasto/picking', function() use ($app) {

    $app->render( 'page/reabasto/picking.php' );

})->via( 'GET', 'POST' );


/**
 * Lists
 **/

$app->map('/reabasto/ptl', function() use ($app) {

    $app->render( 'page/reabasto/ptl.php' );

})->via( 'GET', 'POST' );

?><?php

/**
 * clientes
 **/

$app->map('/recepcionoc', function() use ($app) {

    $app->render( 'page/recepcionoc/index.php' );

})->via( 'GET', 'POST' );


/**
 * Lists
 **/

$app->map('/recepcionoc/lists', function() use ($app) {

    $app->render( 'page/recepcionoc/lists.php' );

})->via( 'GET', 'POST' );
 
     
     
     
$app->map('/recepcionoc/lists2', function() use ($app) {

    $app->render( 'page/recepcionoc/lists2.php' );

})->via( 'GET', 'POST' );
     
$app->map('/recepcionoc/actions.js', function() use ($app) {

    $app->render( 'page/recepcionoc/actions.js' );

})->via( 'GET', 'POST' );
     
$app->map('/recepcionoc/style.css', function() use ($app) {

    $app->render( 'page/recepcionoc/style.css' );

})->via( 'GET', 'POST' );


/**
 * Pending
 **/

$app->map('/recepcionoc/pending', function() use ($app) {

    $app->render( 'page/recepcionoc/pending.php' );

})->via( 'GET', 'POST' );


/**
 * Edit Subscriber
 **/

$app->map('/recepcionoc/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );

/**
 * View Subscriber
 **/

$app->map('/recepcionoc/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/recepcionoc/view.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );
?><?php

/**
 * protocolos
 **/

$app->map('/reportes', function() use ($app) {

    $app->render( 'page/reportes/index.php' );

})->via( 'GET', 'POST' );


/**
 * Lists
 **/

$app->map('/reportes/ocupacionalmacen', function() use ($app) {

    $app->render( 'page/reportes/ocupacionAlmacen.php' );

})->via( 'GET', 'POST' );

$app->map('/reportes/qa', function() use ($app) {

    $app->render( 'page/reportes/qa.php' );

})->via( 'GET', 'POST' );


$app->map('/reportes/asn', function() use ($app) {

    $app->render( 'page/reportes/asn.php' );

})->via( 'GET', 'POST' );



$app->map('/reportes/concentradoexistencia', function() use ($app) {

    $app->render( 'page/reportes/concentradoexistencia.php' );

})->via( 'GET', 'POST' );

$app->map('/reportes/comparativoexistencia', function() use ($app) {

    $app->render( 'page/reportes/comparativoexistencia.php' );

})->via( 'GET', 'POST' );

$app->map('/reportes/ajustesexistencia', function() use ($app) {

    $app->render( 'page/reportes/ajustesexistencia.php' );

})->via( 'GET', 'POST' );


$app->map('/reportes/entrada', function() use ($app) {

    $app->render( 'page/reportes/entrada.php' );

})->via( 'GET', 'POST' );

$app->map('/reportes/comprobantei', function() use ($app) {

    $app->render( 'page/reportes/comprobantei.php' );

})->via( 'GET', 'POST' );

$app->map('/reportes/invfidet', function() use ($app) {

    $app->render( 'page/reportes/invfidet.php' );

})->via( 'GET', 'POST' );

$app->map('/reportes/invfidetconc', function() use ($app) {

    $app->render( 'page/reportes/invfidetconc.php' );

})->via( 'GET', 'POST' );

$app->map('/reportes/difconteos', function() use ($app) {

    $app->render( 'page/reportes/difconteos.php' );

})->via( 'GET', 'POST' );


$app->map('/reportes/invcicl', function() use ($app) {

    $app->render( 'page/reportes/invcicl.php' );

})->via( 'GET', 'POST' );


$app->map('/reportes/invcicldet', function() use ($app) {

    $app->render( 'page/reportes/invcicldet.php' );

})->via( 'GET', 'POST' );

$app->map('/reportes/maxmin', function() use ($app) {

    $app->render( 'page/reportes/maxmin.php' );

})->via( 'GET', 'POST' );

$app->map('/reportes/guiasembarque', function() use ($app) {

    $app->render( 'page/reportes/guiasembarque.php' );

})->via( 'GET', 'POST' );

$app->map('/reportes/ubicaciones', function() use ($app) {

    $app->render( 'page/reportes/ubicaciones.php' );

})->via( 'GET', 'POST' );

$app->map('/reportes/productos', function() use ($app) {

    $app->render( 'page/reportes/productos.php' );

})->via( 'GET', 'POST' );

$app->map('/reportes/lotesPorVencer', function() use ($app) {

    $app->render( 'page/reportes/lotesPorVencer.php' );

})->via( 'GET', 'POST' );

$app->map('/reportes/lotesVencidos', function() use ($app) {

    $app->render( 'page/reportes/lotesVencidos.php' );

})->via( 'GET', 'POST' );

$app->map('/reportes/existenciaubica', function() use ($app) {

    $app->render( 'page/reportes/existenciaubica.php' );

})->via( 'GET', 'POST' );
     
$app->map('/reportes/salidas', function() use ($app) {

    $app->render( 'page/reportes/salidas.php' );

})->via( 'GET', 'POST' );


$app->map('/reportes/operaciones', function() use ($app) {

    $app->render( 'page/reportes/operaciones.php' );

})->via( 'GET', 'POST' );


$app->map('/reportes/existenciacontenedor', function() use ($app) {

    $app->render( 'page/reportes/existenciacontenedor.php' );

})->via( 'GET', 'POST' );
     
$app->map('/reportes/existenciaszonas', function() use ($app) {

    $app->render( 'page/reportes/existenciaszonas.php' );

})->via( 'GET', 'POST' );

/**
 * Pending
 **/

$app->map('/reportes/pending', function() use ($app) {

    $app->render( 'page/reportes/pending.php' );

})->via( 'GET', 'POST' );


/**
 * Edit Subscriber
 **/

$app->map('/reportes/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );

/**
 * View Subscriber
 **/

$app->map('/reportes/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/reportes/view.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );


$app->get('/reportes/pendienteacomodo', function() use ($app) {
    $app->render( 'page/reportes/pendienteacomodo.php');
});

$app->get('/reportes/pdf/pendienteacomodo', function() use ($app) {
    $pdf = new \ReportePDF\PDF($_GET['cia'], 'Reporte Pendiente de Acomodo', 'L');
    $data = new \Reportes\PendienteAcomodo();
    $content = $data->obtenerTodos();
    $pdf->setContent($content);
    $pdf->stream();
    exit;
});

$app->post('/reportes/pdf/etiquetas', function() use ($app) {
    $folio = $_POST['ordenp'];
    //$sql = "UPDATE t_ordenprod SET Status = 'T', Hora_Fin = NOW() WHERE Folio_Pro = '$folio'";
    //$query = mysqli_query(\db2(), $sql);

    $sql = "SELECT IFNULL(Fol_folio, '') as Folio FROM th_pedido WHERE Ship_Num = '$folio'";
    $query = mysqli_query(\db2(), $sql);
    $folio_rel = mysqli_fetch_array($query)["Folio"];

    $pdf = new \ReportePDF\Etiquetas();

    $data = array(
        "articulo"      => $_POST['des_articulo'],
        "clave"         => $_POST['cve_articulo'],
        "lote"          => $_POST['lote'],
        "caducidad"     => $_POST['caducidad_etiq'],
        "consultar"     => $_POST['consultar'],
        "ordenp"        => $_POST['ordenp'],
        "n_pedido"      => $folio_rel,
        "check_pallet"  => $_POST['check_pallet'],
        "pallet"        => $_POST['select-pallets'],
        "cantidad"      => $_POST['unidades_caja'],
        "etiquetas"     => $_POST['numero_impresiones'],
        "barras_art"    => $_POST['barras2'],
        "barras_caja"   => $_POST['barras3']
    );

    if($_POST['etiqueta'] === 'caja'){
        $pdf->generarCodigoCaja($data);
    }
    if($_POST['etiqueta'] === 'articulo'){
        $pdf->generarCodigoArticulo($data);
    }
    else{
        echo 'Etiqueta no disponible';
    }
    exit;
});

$app->post('/reportes/pdf/remision', function() use ($app) {
    $folio = $_POST['folio'];
    $pdf = new \ReportePDF\Remision($folio);
    $pdf->generarHojaSurtido();
    exit;
});
     
$app->post('/reportes/pdf/resguardo', function() use ($app) {
    $folio = $_POST['folio'];
    $pdf = new \ReportePDF\Resguardo($folio);
    $pdf->generarHojaSurtido();
    exit;
});     
     
$app->post('/reportes/pdf/Activos', function() use ($app) {
  $pdf = new \ReportePDF\Activos($_POST['claveActivo']);
  $pdf->toPrint();
  exit;
});

     ?><?php
     
$app->post('/entradas/pdf/exportar', function() use ($app) {
    $folio = $_POST['folio'];
    $tipo = $_POST['tipo'];
    $codigobarras = $_POST['codigobarras'];
    $pdf = new \ReportePDF\EntradasPDF($folio,$tipo, $codigobarras);
    $pdf->generarReporteEntradas($folio,$tipo, $codigobarras);
    exit;
});?><?php

$app->post('/entradas/pdf/codigobarras', function() use ($app) {
    $folio = $_POST['folio'];
    $proveedor = $_POST['proveedor'];
    $oc = $_POST['oc'];
    $lp = $_POST['lp'];
    $idy_ubica = $_POST['idy_ubica'];
    $pdf = new \ReportePDF\EntradasPDF($folio,$proveedor);
    $pdf->generarReporteEntradasCodigoDeBarras($folio,$proveedor, $oc, $lp, $idy_ubica);
    exit;
});?><?php

$app->post('/entradas/pdf/exportarentradasoc', function() use ($app) {
    $folio = $_POST['folio'];
    $oc = $_POST['erp'];
    $tipo = $_POST['tipo'];
    $pdf = new \ReportePDF\EntradasPDF($folio,$oc, $tipo);
    $pdf->generarReporteEntradasOC($folio,$oc, $tipo);
    exit;
});?><?php

$app->post('/pedidos/export/informe', function() use ($app) {
    $folio = $_POST['folio'];
    $pdf = new \ReportePDF\EntradasPDF($folio);
    $pdf->generarReporteEntradasInformePedidos($folio, 0);
    exit;
});?><?php

$app->post('/pedidos/export/informeconsolidado', function() use ($app) {
    $folio = $_POST['folio'];
    $pdf = new \ReportePDF\EntradasPDF($folio);
    $pdf->generarReporteEntradasInformePedidos($folio, 1);
    exit;
});?><?php

  /**
    * protocolos
  **/

$app->post('/embarque/pdf/exportar', function() use ($app) {
    $id = $_POST['folio'];
    $cia = $_POST['cia'];
    $folio_pedido = $_POST['folio_pedido'];
    $pdf = new \ReportePDF\Embarque($id,$cia);
    $pdf->getDataPDF($id,$cia, $folio_pedido);
    exit;
});

$app->post('/embarque/pdf/exportarprecios', function() use ($app) {
    $id = $_POST['folio'];
    $cia = $_POST['cia'];
    $folio_pedido = $_POST['folio_pedido'];
    $pdf = new \ReportePDF\Embarque($id,$cia);
    $pdf->getDataPDFPrecios($id,$cia, $folio_pedido);
    exit;
});

$app->post('/ordentrabajo/pdf/exportar', function() use ($app) {
    $folio_ot = $_POST['folio_ot'];
    $pdf = new \ReportePDF\OrdenTrabajo();
    $pdf->getDataPDF($folio_ot);
    exit;
});

$app->post('/ordentrabajo/excel/exportar', function() use ($app) {
    $folio_ot = $_POST['folio_ot'];
    $pdf = new \ReportePDF\OrdenTrabajo();
    $pdf->getDataExcel($folio_ot);
    exit;
});

$app->post('/empaque/pdf/exportar', function() use ($app) {
    $id = $_POST['folio'];
    $folio_pedidos = $_POST['folio_pedidos'];
    $cia = $_POST['cia'];
    $pdf = new \ReportePDF\Embarque($id,$cia, $folio_pedidos);
    $pdf->getDataPDFEmpaque($id,$cia, $folio_pedidos);
    exit;
});

$app->post('/auditoria/pdf/exportar', function() use ($app) {
    $id = $_POST['folio'];
    $folio_pedidos = $_POST['folio_pedidos'];
    $cia = $_POST['cia'];
    $pdf = new \ReportePDF\Embarque($id,$cia, $folio_pedidos);
    $pdf->getDataPDFAuditoria($id,$cia, $folio_pedidos);
    exit;
});

$app->post('/discrepancias/pdf/exportar', function() use ($app) {
    $id = $_POST['folio'];
    $folio_pedidos = $_POST['folio_pedidos'];
    $cia = $_POST['cia'];
    $pdf = new \ReportePDF\Embarque($id,$cia, $folio_pedidos);
    $pdf->getDataPDFDiscrepancias($id,$cia, $folio_pedidos);
    exit;
});

$app->post('/monitoreo/pdf/exportar', function() use ($app) {
    $id = $_POST['folio'];
    $folio_pedidos = $_POST['folio_pedidos'];
    $cia = $_POST['cia'];
    $pdf = new \ReportePDF\Monitoreo();
    $pdf->getDataPDFMonitoreo($id,$cia, $folio_pedidos);
    //$pdf->getDataPDFDiscrepancias($id,$cia, $folio_pedidos);
    exit;
});

$app->post('/empaque/pdf/exportarCajas', function() use ($app) {
    $id = $_POST['folio'];
    $folio_pedidos = $_POST['folio_pedidos'];
    $cia = $_POST['cia'];
    $pdf = new \ReportePDF\Embarque($id,$cia, $folio_pedidos);
    $pdf->getDataPDFEmpaqueCajas($id,$cia, $folio_pedidos);
    exit;
});

?><?php  
		 
$app->post('/ajusteexis/pdf/exportar', function() use ($app) {
    $id = $_POST['folio'];
	  $cia = $_POST['cia'];
    $pdf = new \ReportePDF\Ajusteexis($id,$cia);
    $pdf->getDataPDF($id,$cia);
    exit;
});?><?php 
     
  $app->map('/roles', function() use ($app) {

    $app->render( 'page/roles/index.php' );

  })->via( 'GET', 'POST' );


  /**
    * Lists
  **/

  $app->map('/roles/lists', function() use ($app) {

    $app->render( 'page/roles/lists.php' );

  })->via( 'GET', 'POST' );


  /**
    * Pending
  **/

  $app->map('/roles/pending', function() use ($app) {

    $app->render( 'page/roles/pending.php' );

  })->via( 'GET', 'POST' );


  /**
    * Edit Subscriber
  **/

  $app->map('/roles/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
      'id_subscriber' => $id_subscriber
    ) );

  })->via( 'GET', 'POST' );

  /**
    * View Subscriber
  **/

  $app->map('/roles/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/roles/view.php', array(
      'id_subscriber' => $id_subscriber
    ) );

  })->via( 'GET', 'POST' );
?><?php

  /**
    * protocolos
  **/

  $app->map('/ruta', function() use ($app) {

    $app->render( 'page/ruta/index.php' );

  })->via( 'GET', 'POST' );


/*
* importar
*/

$app->get('/api/v2/rutas/exportar', 'Application\Controllers\RutasController:exportar');
$app->post('/rutas/importar', 'Application\Controllers\RutasController:importar');
$app->post('/rutassurtido/importar', 'Application\Controllers\RutasSurtidoController:importar');

$app->post('/ruta/import', function() use($app){
    /* Archivo Excel del formulario */
    $file = $_FILES['layout']['tmp_name'];
    /* Creando lector excel */
    $inputFileType = PHPExcel_IOFactory::identify($file);
    $objReader = PHPExcel_IOFactory::createReader($inputFileType);
    $objPHPExcel = $objReader->load($file);
    /* Obteniendo hoja activa */
    $sheet = $objPHPExcel->getActiveSheet();
    /* Obteniendo identificador de última columna y fila */
    $sheetInfo = $sheet->getHighestRowAndColumn();
    $highestRow = $sheetInfo['row'];
    $highestColumn = $sheetInfo['column'];
    /* Empezamos en A2 ya que A1 corresponde a los titulos */
    $sheetRange = "A2:$highestColumn$highestRow";
    $rowData = $sheet->rangeToArray($sheetRange);

    /* Guardando sql para ejecutarlos juntos */
    $insert = "";
    /* Leyendo cada celda del excel */
    foreach($rowData as $cell){
        $data = [
            "t_ruta.cve_ruta"      =>  $cell[0],
            "t_ruta.descripcion"      =>  $cell[1],
            "t_ruta.status"       =>  $cell[2],
            "t_ruta.cve_almacenp"        =>  $cell[3]
        ];
        $last_key = key( array_slice( $data, -1, 1, TRUE ) );
        $insertStm = "INSERT IGNORE INTO t_ruta SET ";
        $set = "";
        foreach($data as $setName => $setValue){
            if(!empty($setValue)){
                $set .= " $setName = \"$setValue\"";
                 if($setName !== $last_key){
                    $set .= ",";
                }
            }
        }
        $insertStm .= $set."; ";
        $insert .= $insertStm;
    }
    $query = mysqli_multi_query(\db2(), $insert);
    if($query){
        $app->response->redirect('/ruta/lists');
    }
});


  /**
    * Lists
  **/

  $app->map('/ruta/lists', function() use ($app) {

    $app->render( 'page/ruta/lists.php' );

  })->via( 'GET', 'POST' );


  /**
    * Pending
  **/

  $app->map('/ruta/pending', function() use ($app) {

    $app->render( 'page/ruta/pending.php' );

  })->via( 'GET', 'POST' );


  /**
    * Edit Subscriber
  **/

  $app->map('/ruta/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
      'id_subscriber' => $id_subscriber
    ) );

  })->via( 'GET', 'POST' );

  /**
    * View Subscriber
  **/

  $app->map('/ruta/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/ruta/view.php', array(
      'id_subscriber' => $id_subscriber
    ) );

  })->via( 'GET', 'POST' );
?><?php

  /**
    * rutassurtido
  **/

  $app->map('/rutasSurtido', function() use ($app) {

    $app->render( 'page/rutassurtido/index.php' );

  })->via( 'GET', 'POST' );


  /**
    * Lists
  **/

  $app->map('/rutasSurtido/lists', function() use ($app) {

    $app->render( 'page/rutassurtido/lists.php' );

  })->via( 'GET', 'POST' );


  /**
    * Pending
  **/

  $app->map('/rutasSurtido/pending', function() use ($app) {

    $app->render( 'page/rutassurtido/pending.php' );

  })->via( 'GET', 'POST' );


  /**
    * Edit Subscriber
  **/

  $app->map('/rutasSurtido/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
      'id_subscriber' => $id_subscriber
    ) );

  })->via( 'GET', 'POST' );

  /**
    * View Subscriber
  **/

  $app->map('/rutasSurtido/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/rutassurtido/view.php', array(
      'id_subscriber' => $id_subscriber
    ) );

  })->via( 'GET', 'POST' );
?><?php

/**
 * subgrupoarticulos
 **/

$app->map('/ssubgrupodearticulos', function() use ($app) {

    $app->render( 'page/ssubgrupodearticulos/index.php' );

})->via( 'GET', 'POST' );




/*
* importar Tipos
*/

$app->post('/articulos/tipos/importar', 'Application\Controllers\ArticulosTiposController:importar');
$app->get('/api/v2/articulos/tipos/exportar', 'Application\Controllers\ArticulosTiposController:exportar');


$app->post('/ssubgrupodearticulos/import', function() use($app){

    exit;
    /* Archivo Excel del formulario */
    $file = $_FILES['layout']['tmp_name'];
    /* Creando lector excel */
    $inputFileType = PHPExcel_IOFactory::identify($file);
    $objReader = PHPExcel_IOFactory::createReader($inputFileType);
    $objPHPExcel = $objReader->load($file);
    /* Obteniendo hoja activa */
    $sheet = $objPHPExcel->getActiveSheet();
    /* Obteniendo identificador de última columna y fila */
    $sheetInfo = $sheet->getHighestRowAndColumn();
    $highestRow = $sheetInfo['row'];
    $highestColumn = $sheetInfo['column'];
    /* Empezamos en A2 ya que A1 corresponde a los titulos */
    $sheetRange = "A2:$highestColumn$highestRow";
    $rowData = $sheet->rangeToArray($sheetRange);

    /* Guardando sql para ejecutarlos juntos */
    $insert = "";
    /* Leyendo cada celda del excel */
    foreach($rowData as $cell){
        $data = [
            "c_ssgpoarticulo.cve_ssgpoart"      =>  $cell[0],
            "c_ssgpoarticulo.cve_sgpoart"      =>  $cell[1],
            "c_ssgpoarticulo.des_ssgpoart"       =>  $cell[2]
        ];
        $last_key = key( array_slice( $data, -1, 1, TRUE ) );
        $insertStm = "INSERT IGNORE INTO c_ssgpoarticulo SET ";
        $set = "";
        foreach($data as $setName => $setValue){
            if(!empty($setValue)){
                $set .= " $setName = \"$setValue\"";
                 if($setName !== $last_key){
                    $set .= ",";
                }
            }
        }
        $insertStm .= $set."; ";
        $insert .= $insertStm;
    }
    $query = mysqli_multi_query(\db2(), $insert);
    if($query){
        $app->response->redirect('/ssubgrupodearticulos/lists');
    }
});

/**
 * Lists
 **/

$app->map('/ssubgrupodearticulos/list', function() use ($app) {

    $app->render( 'page/ssubgrupodearticulos/lists.php' );

})->via( 'GET', 'POST' );


/**
 * Pending
 **/

$app->map('/ssubgrupodearticulos/pending', function() use ($app) {

    $app->render( 'page/ssubgrupodearticulos/pending.php' );

})->via( 'GET', 'POST' );


/**
 * Edit Subscriber
 **/

$app->map('/ssubgrupodearticulos/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );

/**
 * View Subscriber
 **/

$app->map('/ssubgrupodearticulos/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/ssubgrupodearticulos/view.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );
?><?php

  /**
    * Statistics
  **/

  $app->get('/statistics', function() use ($app) {

    $app->render( 'page/statistics/index.php' );

  });
?><?php

/**
 * subgrupoarticulos
 **/

$app->map('/subgrupodearticulos', function() use ($app) {

    $app->render( 'page/subgrupodearticulos/index.php' );

})->via( 'GET', 'POST' );



/*
* importar Clasificación
*/
$app->post('/articulos/clasificaciones/importar', 'Application\Controllers\ArticulosClasificacionController:importar');
$app->get('/api/v2/articulos/clasificaciones/exportar', 'Application\Controllers\ArticulosClasificacionController:exportar');



$app->post('/subgrupodearticulos/import', function() use($app){
    /* Archivo Excel del formulario */
    $file = $_FILES['layout']['tmp_name'];
    /* Creando lector excel */
    $inputFileType = PHPExcel_IOFactory::identify($file);
    $objReader = PHPExcel_IOFactory::createReader($inputFileType);
    $objPHPExcel = $objReader->load($file);
    /* Obteniendo hoja activa */
    $sheet = $objPHPExcel->getActiveSheet();
    /* Obteniendo identificador de última columna y fila */
    $sheetInfo = $sheet->getHighestRowAndColumn();
    $highestRow = $sheetInfo['row'];
    $highestColumn = $sheetInfo['column'];
    /* Empezamos en A2 ya que A1 corresponde a los titulos */
    $sheetRange = "A2:$highestColumn$highestRow";
    $rowData = $sheet->rangeToArray($sheetRange);

    /* Guardando sql para ejecutarlos juntos */
    $insert = "";
    /* Leyendo cada celda del excel */
    foreach($rowData as $cell){
        $data = [
            "c_sgpoarticulo.cve_sgpoart"      =>  $cell[0],
            "c_sgpoarticulo.cve_gpoart"      =>  $cell[1],
            "c_sgpoarticulo.des_sgpoart"       =>  $cell[2]
        ];
        $last_key = key( array_slice( $data, -1, 1, TRUE ) );
        $insertStm = "INSERT IGNORE INTO c_sgpoarticulo SET ";
        $set = "";
        foreach($data as $setName => $setValue){
            if(!empty($setValue)){
                $set .= " $setName = \"$setValue\"";
                 if($setName !== $last_key){
                    $set .= ",";
                }
            }
        }
        $insertStm .= $set."; ";
        $insert .= $insertStm;
    }
    $query = mysqli_multi_query(\db2(), $insert);
    if($query){
        $app->response->redirect('/subgrupodearticulos/lists');
    }
});


/**
 * Lists
 **/

$app->map('/subgrupodearticulos/list', function() use ($app) {

    $app->render( 'page/subgrupodearticulos/lists.php' );

})->via( 'GET', 'POST' );

$app->map('/impresoras/list', function() use ($app) {

    $app->render( 'page/dispositivos/impresoras.php' );

})->via( 'GET', 'POST' );


/**
 * Pending
 **/

$app->map('/subgrupodearticulos/pending', function() use ($app) {

    $app->render( 'page/subgrupodearticulos/pending.php' );

})->via( 'GET', 'POST' );


/**
 * Edit Subscriber
 **/

$app->map('/subgrupodearticulos/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );

/**
 * View Subscriber
 **/

$app->map('/subgrupodearticulos/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subgrupodearticulos/view.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );
?><?php

  /**
    * Subscribers
  **/

  $app->map('/subscribers', function() use ($app) {

    $app->render( 'page/subscribers/index.php' );

  })->via( 'GET', 'POST' );


  /**
    * Lists
  **/

  $app->map('/subscribers/lists', function() use ($app) {

    $app->render( 'page/subscribers/lists.php' );

  })->via( 'GET', 'POST' );


  /**
    * Pending
  **/

  $app->map('/subscribers/pending', function() use ($app) {

    $app->render( 'page/subscribers/pending.php' );

  })->via( 'GET', 'POST' );


  /**
    * Edit Subscriber
  **/

  $app->map('/subscribers/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
      'id_subscriber' => $id_subscriber
    ) );

  })->via( 'GET', 'POST' );

  /**
    * View Subscriber
  **/

  $app->map('/subscribers/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/view.php', array(
      'id_subscriber' => $id_subscriber
    ) );

  })->via( 'GET', 'POST' );
?><?php

/**
 * pedidos
 **/

$app->map('/sucursal', function() use ($app) {

    $app->render( 'page/sucursal/index.php' );

})->via( 'GET', 'POST' );


/**
 * Lists
 **/

$app->map('/sucursal/lists', function() use ($app) {

    $app->render( 'page/sucursal/lists.php' );

})->via( 'GET', 'POST' );


/**
 * Pending
 **/

$app->map('/sucursal/pending', function() use ($app) {

    $app->render( 'page/sucursal/pending.php' );

})->via( 'GET', 'POST' );


/**
 * Edit Subscriber
 **/

$app->map('/sucursal/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );

/**
 * View Subscriber
 **/

$app->map('/sucursal/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/sucursal/view.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );
?><?php

  /**
    * tipoalmacen
  **/

  $app->map('/tipoalmacen', function() use ($app) {

    $app->render( 'page/tipoalmacen/index.php' );

  })->via( 'GET', 'POST' );


  /**
    * Lists
  **/

  $app->map('/tipoalmacen/lists', function() use ($app) {

    $app->render( 'page/tipoalmacen/lists.php' );

  })->via( 'GET', 'POST' );


  /**
    * Pending
  **/

  $app->map('/tipoalmacen/pending', function() use ($app) {

    $app->render( 'page/tipoalmacen/pending.php' );

  })->via( 'GET', 'POST' );


  /**
    * Edit Subscriber
  **/

  $app->map('/tipoalmacen/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
      'id_subscriber' => $id_subscriber
    ) );

  })->via( 'GET', 'POST' );

  /**
    * View Subscriber
  **/

  $app->map('/tipoalmacen/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/tipoalmacen/view.php', array(
      'id_subscriber' => $id_subscriber
    ) );

  })->via( 'GET', 'POST' );
?><?php

/**
 * tipocaja
 **/

$app->map('/tipocaja', function() use ($app) {

    $app->render( 'page/tipocaja/index.php' );

})->via( 'GET', 'POST' );


/*
* importar
*/
$app->post('/tipos-de-cajas/importar', 'Application\Controllers\TipoDeCajasController:importar');
$app->get('/api/v2/tipos-de-cajas/exportar', 'Application\Controllers\TipoDeCajasController:exportar');


$app->post('/tipocaja/import', function() use($app){
    /* Archivo Excel del formulario */
    $file = $_FILES['layout']['tmp_name'];
    /* Creando lector excel */
    $inputFileType = PHPExcel_IOFactory::identify($file);
    $objReader = PHPExcel_IOFactory::createReader($inputFileType);
    $objPHPExcel = $objReader->load($file);
    /* Obteniendo hoja activa */
    $sheet = $objPHPExcel->getActiveSheet();
    /* Obteniendo identificador de última columna y fila */
    $sheetInfo = $sheet->getHighestRowAndColumn();
    $highestRow = $sheetInfo['row'];
    $highestColumn = $sheetInfo['column'];
    /* Empezamos en A2 ya que A1 corresponde a los titulos */
    $sheetRange = "A2:$highestColumn$highestRow";
    $rowData = $sheet->rangeToArray($sheetRange);

    /* Guardando sql para ejecutarlos juntos */
    $insert = "";
    /* Leyendo cada celda del excel */
    foreach($rowData as $cell){
        $data = [
            "c_tipocaja.clave"      =>  $cell[0],
            "c_tipocaja.descripcion"      =>  $cell[1],
            "c_tipocaja.largo"       =>  $cell[2],
            "c_tipocaja.alto"        =>  $cell[3],
            "c_tipocaja.ancho"        =>  $cell[4]
        ];
        $last_key = key( array_slice( $data, -1, 1, TRUE ) );
        $insertStm = "INSERT IGNORE INTO c_tipocaja SET ";
        $set = "";
        foreach($data as $setName => $setValue){
            if(!empty($setValue)){
                $set .= " $setName = \"$setValue\"";
                if($setName !== $last_key){
                    $set .= ",";
                }
            }
        }
        $insertStm .= $set."; ";
        $insert .= $insertStm;
    }
    $query = mysqli_multi_query(\db2(), $insert);
    if($query){
        $app->response->redirect('/tipocaja/lists');
    }
});

/**
 * Lists
 **/

$app->map('/tipocaja/list', function() use ($app) {

    $app->render( 'page/tipocaja/lists.php' );

})->via( 'GET', 'POST' );


/**
 * Pending
 **/

$app->map('/tipocaja/pending', function() use ($app) {

    $app->render( 'page/tipocaja/pending.php' );

})->via( 'GET', 'POST' );


/**
 * Edit Subscriber
 **/

$app->map('/tipocaja/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );

/**
 * View Subscriber
 **/

$app->map('/tipocaja/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/tipocaja/view.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );
?><?php

  /**
    * tipocompania
  **/

  $app->map('/tipocompania', function() use ($app) {

    $app->render( 'page/tipocompania/index.php' );

  })->via( 'GET', 'POST' );


$app->map('/reabasto/reabastoOC', function() use ($app) {

    $app->render( 'page/reabasto/reabastoOC.php' );

})->via( 'GET', 'POST' );

  /**
    * Lists
  **/

  $app->map('/tipocompania/lists', function() use ($app) {

    $app->render( 'page/tipocompania/lists.php' );

  })->via( 'GET', 'POST' );


  /**
    * Pending
  **/

  $app->map('/tipocompania/pending', function() use ($app) {

    $app->render( 'page/tipocompania/pending.php' );

  })->via( 'GET', 'POST' );


  /**
    * Edit Subscriber
  **/

  $app->map('/tipocompania/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
      'id_subscriber' => $id_subscriber
    ) );

  })->via( 'GET', 'POST' );

  /**
    * View Subscriber
  **/

  $app->map('/tipocompania/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/tipocompania/view.php', array(
      'id_subscriber' => $id_subscriber
    ) );

  })->via( 'GET', 'POST' );
?><?php

  /**
    * tipodeprioridad
  **/

  $app->map('/tipodeprioridad', function() use ($app) {

    $app->render( 'page/tipodeprioridad/index.php' );

  })->via( 'GET', 'POST' );


/*
* importar
*/

$app->post('/prioridades/importar', 'Application\Controllers\PrioridadesController:importar');
$app->get('/api/v2/prioridades/exportar', 'Application\Controllers\PrioridadesController:exportar');


$app->post('/tipodeprioridad/import', function() use($app){
    /* Archivo Excel del formulario */
    $file = $_FILES['layout']['tmp_name'];
    /* Creando lector excel */
    $inputFileType = PHPExcel_IOFactory::identify($file);
    $objReader = PHPExcel_IOFactory::createReader($inputFileType);
    $objPHPExcel = $objReader->load($file);
    /* Obteniendo hoja activa */
    $sheet = $objPHPExcel->getActiveSheet();
    /* Obteniendo identificador de última columna y fila */
    $sheetInfo = $sheet->getHighestRowAndColumn();
    $highestRow = $sheetInfo['row'];
    $highestColumn = $sheetInfo['column'];
    /* Empezamos en A2 ya que A1 corresponde a los titulos */
    $sheetRange = "A2:$highestColumn$highestRow";
    $rowData = $sheet->rangeToArray($sheetRange);

    /* Guardando sql para ejecutarlos juntos */
    $insert = "";
    /* Leyendo cada celda del excel */
    foreach($rowData as $cell){
        $data = [
            "t_tiposprioridad.Descripcion"      =>  $cell[0],
            "t_tiposprioridad.Prioridad"      =>  $cell[1],
            "t_tiposprioridad.Status"       =>  $cell[2],
            "t_tiposprioridad.Clave"        =>  $cell[3]
        ];
        $last_key = key( array_slice( $data, -1, 1, TRUE ) );
        $insertStm = "INSERT IGNORE INTO t_tiposprioridad SET ";
        $set = "";
        foreach($data as $setName => $setValue){
            if(!empty($setValue)){
                $set .= " $setName = \"$setValue\"";
                 if($setName !== $last_key){
                    $set .= ",";
                }
            }
        }
        $insertStm .= $set."; ";
        $insert .= $insertStm;
    }
    $query = mysqli_multi_query(\db2(), $insert);
    if($query){
        $app->response->redirect('/tipodeprioridad/lists');
    }
});


  /**
    * Lists
  **/

  $app->map('/tipodeprioridad/lists', function() use ($app) {

    $app->render( 'page/tipodeprioridad/lists.php' );

  })->via( 'GET', 'POST' );


  /**
    * Pending
  **/

  $app->map('/tipodeprioridad/pending', function() use ($app) {

    $app->render( 'page/tipodeprioridad/pending.php' );

  })->via( 'GET', 'POST' );


  /**
    * Edit Subscriber
  **/

  $app->map('/tipodeprioridad/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
      'id_subscriber' => $id_subscriber
    ) );

  })->via( 'GET', 'POST' );

  /**
    * View Subscriber
  **/

  $app->map('/tipodeprioridad/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/tipodeprioridad/view.php', array(
      'id_subscriber' => $id_subscriber
    ) );

  })->via( 'GET', 'POST' );
?><?php

/**
    * protocolos
  **/

$app->map('/tipotransporte', function() use ($app) {

  $app->render( 'page/tipotransporte/index.php' );

})->via( 'GET', 'POST' );



/*
* importar
*/
$app->post('/tipos-de-transporte/importar', 'Application\Controllers\TipoDeTransporteController:importar');
$app->get('/api/v2/tipos-de-transporte/exportar', 'Application\Controllers\TipoDeTransporteController:exportar');


$app->post('/tipotransporte/import', function() use($app){
  /* Archivo Excel del formulario */
  $file = $_FILES['layout']['tmp_name'];
  /* Creando lector excel */
  $inputFileType = PHPExcel_IOFactory::identify($file);
  $objReader = PHPExcel_IOFactory::createReader($inputFileType);
  $objPHPExcel = $objReader->load($file);
  /* Obteniendo hoja activa */
  $sheet = $objPHPExcel->getActiveSheet();
  /* Obteniendo identificador de última columna y fila */
  $sheetInfo = $sheet->getHighestRowAndColumn();
  $highestRow = $sheetInfo['row'];
  $highestColumn = $sheetInfo['column'];
  /* Empezamos en A2 ya que A1 corresponde a los titulos */
  $sheetRange = "A2:$highestColumn$highestRow";
  $rowData = $sheet->rangeToArray($sheetRange);

  /* Guardando sql para ejecutarlos juntos */
  $insert = "";
  /* Leyendo cada celda del excel */
  foreach($rowData as $cell){
    $data = [
      "tipo_transporte.clave_ttransporte"      =>  $cell[0],
      "tipo_transporte.alto"      =>  $cell[1],
      "tipo_transporte.fondo"       =>  $cell[2],
      "tipo_transporte.ancho"        =>  $cell[3],
      "tipo_transporte.capacidad_carga"        =>  $cell[4],
      "tipo_transporte.desc_ttransporte"        =>  $cell[5],
      "tipo_transporte.imagen"        =>  $cell[6]
    ];
    $last_key = key( array_slice( $data, -1, 1, TRUE ) );
    $insertStm = "INSERT IGNORE INTO tipo_transporte SET ";
    $set = "";
    foreach($data as $setName => $setValue){
      if(!empty($setValue)){
        $set .= " $setName = \"$setValue\"";
        if($setName !== $last_key){
          $set .= ",";
        }
      }
    }
    $insertStm .= $set."; ";
    $insert .= $insertStm;
  }
  $query = mysqli_multi_query(\db2(), $insert);
  if($query){
    $app->response->redirect('/tipotransporte/lists');
  }
});


/**
    * Lists
  **/

$app->map('/tipotransporte/lists', function() use ($app) {

  $app->render( 'page/tipotransporte/lists.php' );

})->via( 'GET', 'POST' );


/**
    * Pending
  **/

$app->map('/tipotransporte/pending', function() use ($app) {

  $app->render( 'page/tipotransporte/pending.php' );

})->via( 'GET', 'POST' );


/**
    * Edit Subscriber
  **/

$app->map('/tipotransporte/edit/:id', function( $id_subscriber ) use ($app) {

  $app->render( 'page/subscribers/edit.php', array(
    'id_subscriber' => $id_subscriber
  ) );

})->via( 'GET', 'POST' );

/**
    * View Subscriber
  **/

$app->map('/tipotransporte/view/:id', function( $id_subscriber ) use ($app) {

  $app->render( 'page/tipotransporte/view.php', array(
    'id_subscriber' => $id_subscriber
  ) );

})->via( 'GET', 'POST' );
?><?php

  /**
    * protocolos
  **/

  $app->map('/transporte', function() use ($app) {

    $app->render( 'page/transporte/index.php' );

  })->via( 'GET', 'POST' );


/*
* importar
*/

$app->post('/transporte/importar', 'Application\Controllers\TransporteController:importar');
$app->get('/api/v2/transporte/exportar', 'Application\Controllers\TransporteController:exportar');


$app->post('/transporte/import', function() use($app){
    /* Archivo Excel del formulario */
    $file = $_FILES['layout']['tmp_name'];
    /* Creando lector excel */
    $inputFileType = PHPExcel_IOFactory::identify($file);
    $objReader = PHPExcel_IOFactory::createReader($inputFileType);
    $objPHPExcel = $objReader->load($file);
    /* Obteniendo hoja activa */
    $sheet = $objPHPExcel->getActiveSheet();
    /* Obteniendo identificador de última columna y fila */
    $sheetInfo = $sheet->getHighestRowAndColumn();
    $highestRow = $sheetInfo['row'];
    $highestColumn = $sheetInfo['column'];
    /* Empezamos en A2 ya que A1 corresponde a los titulos */
    $sheetRange = "A2:$highestColumn$highestRow";
    $rowData = $sheet->rangeToArray($sheetRange);

    /* Guardando sql para ejecutarlos juntos */
    $insert = "";
    /* Leyendo cada celda del excel */
    foreach($rowData as $cell){
        $data = [
            "t_transporte.ID_Transporte"      =>  $cell[0],
            "t_transporte.Nombre"      =>  $cell[1],
            "t_transporte.Placas"       =>  $cell[2],
            "t_transporte.cve_cia"        =>  $cell[3],
            "t_transporte.tipo_transporte"        =>  $cell[4]
        ];
        $last_key = key( array_slice( $data, -1, 1, TRUE ) );
        $insertStm = "INSERT IGNORE INTO t_transporte SET ";
        $set = "";
        foreach($data as $setName => $setValue){
            if(!empty($setValue)){
                $set .= " $setName = \"$setValue\"";
                 if($setName !== $last_key){
                    $set .= ",";
                }
            }
        }
        $insertStm .= $set."; ";
        $insert .= $insertStm;
    }
    $query = mysqli_multi_query(\db2(), $insert);
    if($query){
        $app->response->redirect('/transporte/lists');
    }
});


  /**
    * Lists
  **/

  $app->map('/transporte/lists', function() use ($app) {

    $app->render( 'page/transporte/lists.php' );

  })->via( 'GET', 'POST' );


  /**
    * Pending
  **/

  $app->map('/transporte/pending', function() use ($app) {

    $app->render( 'page/transporte/pending.php' );

  })->via( 'GET', 'POST' );


  /**
    * Edit Subscriber
  **/

  $app->map('/transporte/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
      'id_subscriber' => $id_subscriber
    ) );

  })->via( 'GET', 'POST' );

  /**
    * View Subscriber
  **/

  $app->map('/transporte/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/transporte/view.php', array(
      'id_subscriber' => $id_subscriber
    ) );

  })->via( 'GET', 'POST' );
?><?php

/**
 * ubicacion
 **/

$app->map('/ubicacionalmacenaje', function() use ($app) {

    $app->render( 'page/ubicacionalmacenaje/index.php' );

})->via( 'GET', 'POST' );


/*
* importar
*/
$app->post('/articulos-compuestos/importar', 'Application\Controllers\ArticulosCompuestosController:importar');
$app->post('/orden-de-produccion/importar', 'Application\Controllers\OrdenDeProduccionController:importar');
$app->post('/orden-de-produccion/importar_foam', 'Application\Controllers\OrdenDeProduccionController:importar_foam');
$app->post('/orden-de-produccion/get_folios', 'Application\Controllers\OrdenDeProduccionController:get_folios');
$app->post('/orden-de-produccion/revisar_progreso', 'Application\Controllers\OrdenDeProduccionController:revisar_progreso');
$app->get('/api/v2/otpendientes/exportar', 'Application\Controllers\OrdenDeProduccionController:exportar');

$app->post('/ubicacion-de-almacenaje/importar', 'Application\Controllers\UbicacionesController:importar');
$app->get('/api/v2/ubicacion-de-almacenaje/exportar', 'Application\Controllers\UbicacionesController:exportar');


$app->post('/ubicacionalmacenaje/import', function() use($app){
    /* Archivo Excel del formulario */
    $file = $_FILES['layout']['tmp_name'];
    /* Creando lector excel */
    $inputFileType = PHPExcel_IOFactory::identify($file);
    $objReader = PHPExcel_IOFactory::createReader($inputFileType);
    $objPHPExcel = $objReader->load($file);
    /* Obteniendo hoja activa */
    $sheet = $objPHPExcel->getActiveSheet();
    /* Obteniendo identificador de última columna y fila */
    $sheetInfo = $sheet->getHighestRowAndColumn();
    $highestRow = $sheetInfo['row'];
    $highestColumn = $sheetInfo['column'];
    /* Empezamos en A2 ya que A1 corresponde a los titulos */
    $sheetRange = "A2:$highestColumn$highestRow";
    $rowData = $sheet->rangeToArray($sheetRange);

    /* Guardando sql para ejecutarlos juntos */
    $insert = "";
    /* Leyendo cada celda del excel */
    foreach($rowData as $cell){
        $data = [
            "c_ubicacion.cve_almac"      =>  $cell[0],
            "c_ubicacion.cve_pasillo"      =>  $cell[1],
            "c_ubicacion.cve_rack"       =>  $cell[2],
            "c_ubicacion.cve_nivel"        =>  $cell[3],
            "c_ubicacion.num_ancho"        =>  $cell[4],
            "c_ubicacion.num_largo"        =>  $cell[5],
            "c_ubicacion.num_alto"        =>  $cell[6],
            "c_ubicacion.num_volumenDisp"        =>  $cell[7],
            "c_ubicacion.Status"        =>  $cell[8],
            "c_ubicacion.picking"        =>  $cell[9],
            "c_ubicacion.Seccion"        =>  $cell[10],
            "c_ubicacion.Ubicacion"        =>  $cell[11],
            "c_ubicacion.orden_secuencia"        =>  $cell[12],
            "c_ubicacion.PesoMaximo"        =>  $cell[13],
            "c_ubicacion.PesoOcupado"        =>  $cell[14],
            "c_ubicacion.claverp"        =>  $cell[15],
            "c_ubicacion.CodigoCSD"        =>  $cell[16],
            "c_ubicacion.TECNOLOGIA"        =>  $cell[17],
            "c_ubicacion.Maneja_Cajas"        =>  $cell[18],
            "c_ubicacion.Maneja_Piezas"        =>  $cell[19],
            "c_ubicacion.Reabasto"        =>  $cell[20],
            "c_ubicacion.Tipo"        =>  $cell[21]
        ];
        $last_key = key( array_slice( $data, -1, 1, TRUE ) );
        $insertStm = "INSERT IGNORE INTO c_ubicacion SET ";
        $set = "";
        foreach($data as $setName => $setValue){
            if(!empty($setValue)){
                $set .= " $setName = \"$setValue\"";
                if($setName !== $last_key){
                    $set .= ",";
                }
            }
        }
        $insertStm .= $set."; ";
        $insert .= $insertStm;
    }
    $query = mysqli_multi_query(\db2(), $insert);
    if($query){
        $app->response->redirect('/ubicacionalmacenaje/lists');
    }
  
});

$app->map('/paletsycontenedores/list', function() use ($app) {

    $app->render( 'page/paletsycontenedores/lists.php' );

})->via( 'GET', 'POST' );

/**
    * Lists
  **/

  $app->map('/devpalletsycontenedores/list', function() use ($app) {

    $app->render( 'page/devpalletsycontenedores/lists.php' );

  })->via( 'GET', 'POST' );

  $app->map('/devproveedores/list', function() use ($app) {

    $app->render( 'page/devproveedores/lists.php' );

  })->via( 'GET', 'POST' );

  $app->map('/devclientes/list', function() use ($app) {

    $app->render( 'page/devclientes/lists.php' );

  })->via( 'GET', 'POST' );


/**
 * Lists
 **/

$app->map('/ubicacionalmacenaje/lists', function() use ($app) {

    $app->render( 'page/ubicacionalmacenaje/lists.php' );

})->via( 'GET', 'POST' );


/**
 * Pending
 **/

$app->map('/ubicacionalmacenaje/pending', function() use ($app) {

    $app->render( 'page/ubicacionalmacenaje/pending.php' );

})->via( 'GET', 'POST' );


/**
 * Edit Subscriber
 **/

$app->map('/ubicacionalmacenaje/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );

/**
 * View Subscriber
 **/

$app->map('/ubicacionalmacenaje/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/ubicacionalmacenaje/view.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );
?><?php

/**
 * ubicacion
 **/

$app->map('/ubicaciones', function() use ($app) {

    $app->render( 'page/ubicaciones/index.php' );

})->via( 'GET', 'POST' );


/**
 * Lists
 **/

$app->map('/ubicaciones/lists', function() use ($app) {

    $app->render( 'page/ubicaciones/lists.php' );

})->via( 'GET', 'POST' );


/**
 * Pending
 **/

$app->map('/ubicaciones/pending', function() use ($app) {

    $app->render( 'page/ubicaciones/pending.php' );

})->via( 'GET', 'POST' );


/**
 * Edit Subscriber
 **/

$app->map('/ubicaciones/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );

/**
 * View Subscriber
 **/

$app->map('/ubicaciones/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/ubicaciones/view.php', array(
        'id_subscriber' => $id_subscriber
    ) );

})->via( 'GET', 'POST' );
?><?php

  /**
    * unidadesmedida
  **/

  $app->map('/unidadesmedida', function() use ($app) {

    $app->render( 'page/unidadesmedida/index.php' );

  })->via( 'GET', 'POST' );


/*
* importar
*/


$app->post('/contactos/importar', 'Application\Controllers\ClientesController:importarcontactos');
$app->post('/unidades-medida/importar', 'Application\Controllers\UnidadesMedidaController:importar');
$app->get('/api/v2/unidades-medida/exportar', 'Application\Controllers\UnidadesMedidaController:exportar');



$app->post('/unidadesmedida/import', function() use($app){
    /* Archivo Excel del formulario */
    $file = $_FILES['layout']['tmp_name'];
    /* Creando lector excel */
    $inputFileType = PHPExcel_IOFactory::identify($file);
    $objReader = PHPExcel_IOFactory::createReader($inputFileType);
    $objPHPExcel = $objReader->load($file);
    /* Obteniendo hoja activa */
    $sheet = $objPHPExcel->getActiveSheet();
    /* Obteniendo identificador de última columna y fila */
    $sheetInfo = $sheet->getHighestRowAndColumn();
    $highestRow = $sheetInfo['row'];
    $highestColumn = $sheetInfo['column'];
    /* Empezamos en A2 ya que A1 corresponde a los titulos */
    $sheetRange = "A2:$highestColumn$highestRow";
    $rowData = $sheet->rangeToArray($sheetRange);

    /* Guardando sql para ejecutarlos juntos */
    $insert = "";
    /* Leyendo cada celda del excel */
    foreach($rowData as $cell){
        $data = [
            "c_unimed.cve_umed"      =>  $cell[0],
            "c_unimed.des_umed"      =>  $cell[1],
            "c_unimed.mav_cveunimed"       =>  $cell[2],
            "c_unimed.imp_cosprom"        =>  $cell[3]
        ];
        $last_key = key( array_slice( $data, -1, 1, TRUE ) );
        $insertStm = "INSERT IGNORE INTO c_unimed SET ";
        $set = "";
        foreach($data as $setName => $setValue){
            if(!empty($setValue)){
                $set .= " $setName = \"$setValue\"";
                 if($setName !== $last_key){
                    $set .= ",";
                }
            }
        }
        $insertStm .= $set."; ";
        $insert .= $insertStm;
    }
    $query = mysqli_multi_query(\db2(), $insert);
    if($query){
        $app->response->redirect('/unidadesmedida/lists');
    }
});


  /**
    * Lists
  **/

  $app->map('/unidadesmedida/lists', function() use ($app) {

    $app->render( 'page/unidadesmedida/lists.php' );

  })->via( 'GET', 'POST' );

  $app->map('/contactos/lists', function() use ($app) {

    $app->render( 'page/contactos/lists.php' );

  })->via( 'GET', 'POST' );

  $app->map('/proyectos/list', function() use ($app) {

    $app->render( 'page/proyectos/lists.php' );

  })->via( 'GET', 'POST' );

  $app->map('/servicios', function() use ($app) {

    $app->render( 'page/servicios/lists.php' );

  })->via( 'GET', 'POST' );

  $app->map('/gposervicios', function() use ($app) {

    $app->render( 'page/gruposervicios/lists.php' );

  })->via( 'GET', 'POST' );

  $app->map('/servicios/G01', function() use ($app) {

    $app->render( 'page/servicios/list1.php' );

  })->via( 'GET', 'POST' );


  $app->map('/servicios/G02', function() use ($app) {

    $app->render( 'page/servicios/list2.php' );

  })->via( 'GET', 'POST' );

  $app->map('/servicios/G03', function() use ($app) {

    $app->render( 'page/servicios/list3.php' );

  })->via( 'GET', 'POST' );

  /**
    * Pending
  **/

  $app->map('/unidadesmedida/pending', function() use ($app) {

    $app->render( 'page/unidadesmedida/pending.php' );

  })->via( 'GET', 'POST' );


  /**
    * Edit Subscriber
  **/

  $app->map('/unidadesmedida/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
      'id_subscriber' => $id_subscriber
    ) );

  })->via( 'GET', 'POST' );

  /**
    * View Subscriber
  **/

  $app->map('/unidadesmedida/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/unidadesmedida/view.php', array(
      'id_subscriber' => $id_subscriber
    ) );

  })->via( 'GET', 'POST' );
?><?php

  /**
    * protocolos
  **/

  

  
  /*$app->map('/usuarios', function() use ($app) {

    $app->render( 'page/usuarios/index.php' );

  })->via( 'GET', 'POST' );
*/

  /**
    * Lists
  **/



/** 
 * --------------------------------------------------------------------------
 * API de Usuarios
 * -------------------------------------------------------------------------- 
 * @version 1.0.0
 * @category Usuarios
 */
$app->get('/usuarios/lists', 'Application\Controllers\UsuariosController:index');
$app->get('/usuarios/paginate', 'Application\Controllers\UsuariosController:paginate');


/** 
 * --------------------------------------------------------------------------
 * API de Usuarios
 * -------------------------------------------------------------------------- 
 * @version 2.0.0
 * @author Brayan Rincon <brayan262@gmail.com>
 * @category Usuarios
 */
$app->get('/api/v2/usuarios/administradores', 'Application\Controllers\UsuariosController:usuariosAdministradores');





  /*$app->map('/usuarios/lists', function() use ($app) {
    $app->render( 'page/usuarios/lists.php' );
  })->via( 'GET', 'POST' );
*/

  /**
    * Pending
  **/

  $app->map('/usuarios/pending', function() use ($app) {

    $app->render( 'page/usuarios/pending.php' );

  })->via( 'GET', 'POST' );


  /**
    * Edit Subscriber
  **/

  $app->map('/usuarios/edit/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/subscribers/edit.php', array(
      'id_subscriber' => $id_subscriber
    ) );

  })->via( 'GET', 'POST' );

  /**
    * View Subscriber
  **/

  $app->map('/usuarios/view/:id', function( $id_subscriber ) use ($app) {

    $app->render( 'page/usuarios/view.php', array(
      'id_subscriber' => $id_subscriber
    ) );

  })->via( 'GET', 'POST' );
?><?php 

$app->map('/utileria/mensajes', function() use ($app) {

  $app->render( 'page/utileria/mensajes.php' );

})->via( 'GET', 'POST' );
?><?php


$app->map('/utileria/interfases', function() use ($app) {

  $app->render( 'page/interfases/lists.php' );

})->via( 'GET', 'POST' );
?><?php


/**
 * Lists
 **/

$app->map('/zonahoraria', function() use ($app) {

    $app->render( 'page/zonahoraria/lists.php' );

})->via( 'GET', 'POST' );

     

 

