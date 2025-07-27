<?php
include '../../../config.php';

error_reporting(0);

if (isset($_POST) && !empty($_POST)) {
    $page  = $_POST['page']; // get the requested page
    $limit = $_POST['rows']; // get how many rows we want to have into the grid
    $sidx  = $_POST['sidx']; // get index row - i.e. user click to sort
    $sord  = $_POST['sord']; // get the direction

    $ands ="";

    $_criterio = $_POST['criterio'];
    if (!empty($_criterio)){
        $ands.=' WHERE 
            id LIKE "%'.$_criterio.'%" 
            OR clave_partida LIKE "%'.$_criterio.'%" 
            OR nombre_partida LIKE "%'.$_criterio.'%" 
            OR id_presupuesto LIKE "%'.$_criterio.'%" ';
    }


    $start = $limit*$page - $limit; // do not put $limit*($page - 1)

    if(!$sidx) $sidx =1;

    // se conecta a la base de datos
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    // prepara la llamada al procedimiento almacenado Lis_Facturas
    $sqlCount = "SELECT * FROM `c_partidas`".$ands;
  
    if (!($res = mysqli_query($conn, $sqlCount))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }
    
    $row = mysqli_fetch_array($res);
    $count = $row[0];
    $_page = 0;
    if (intval($page)>0) $_page = ($page-1)*$limit;
    
    $sql = "SELECT id, 
            (SELECT c_presupuestos.nombreDePresupuesto FROM `c_presupuestos` where c_presupuestos.id = c_partidas.id_presupuesto) as presupuesto, 
            clave_partida, 
            nombre_partida
            FROM c_partidas".$ands.' order by id ';

    // hace una llamada previa al procedimiento
    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    if( $count >0 ) {
        $total_pages = ceil($count/$limit);
        //$total_pages = ceil($count/1);
    } else {
        $total_pages = 0;
    } if ($page > $total_pages)
        $page=$total_pages;

    $responce->page = $page;
    $responce->total = $total_pages;
    $responce->records = $count;

    $arr = array();
    $i = 0;

    while ($row = mysqli_fetch_array($res)) {
        //echo var_dump($row);
        $row=array_map('utf8_encode', $row);
        $arr[] = $row;
        $responce->rows[$i]['id']=$row['id'];
        $responce->rows[$i]['cell']=array(
                                          $row["id"],
                                          $row["clave_partida"],
                                          $row["nombre_partida"],
                                          $row["presupuesto"],
                                          );
        $i++;
    }
    //echo var_dump($responce);
    
    echo json_encode($responce);exit;
}
