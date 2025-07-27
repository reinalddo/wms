<?php 

//include '../../../config.php';
include '../../../app/load.php';
$app = new \Slim\Slim();
if( !isset( $_SESSION['id_user'] ) AND !$_SESSION['id_user'] ) {exit();}

//error_reporting(0);

if( $_POST['action'] == 'revisar_folios' ) 
{
//ini_set('default_socket_timeout', 6000);
    $folios = $_POST['folios'];

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $sql1 = "SELECT COUNT(*) AS total FROM t_ordenprod WHERE Referencia IN ($folios)";
    $query1 = mysqli_query($conn, $sql1);
    $total = mysqli_fetch_array($query1)['total'];

    $sql2 = "SELECT COUNT(DISTINCT Origen) AS progreso FROM t_MovCharolas WHERE origen IN (SELECT CONCAT('PT_',Folio_Pro) FROM t_ordenprod WHERE Referencia IN ($folios))";
    $query2 = mysqli_query($conn, $sql2);
    $progreso = mysqli_fetch_array($query2)['progreso'];

    $porcentaje = 0;

    if($total > 0)
    {
        $porcentaje = round(($progreso/$total), 2)*100;
    }

    echo json_encode(array("porcentaje" =>  $porcentaje, "folios" => $folios, "sql1" => $sql1, "sql2" => $sql2));
    //echo 'folio';
}
else if(isset($_GET) && $_GET){
    $clave = $_GET['cve_articulo'];
    $lote = $_GET['lote'];

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $sql = "SELECT Folio_Pro, Cantidad FROM t_ordenprod WHERE Cve_Articulo = '$clave' AND Cve_Lote = '$lote' LIMIT 0,1";
    $query = mysqli_query($conn, $sql);
    $op = '';
    $cantidad = '';
    if($query->num_rows > 0){
        $row = mysqli_fetch_array($query);
        $op = $row['Folio_Pro'];
        $cantidad = $row['Cantidad'];
    }
    mysqli_close($conn);
    echo json_encode(array('cantidad' => $cantidad, 'ordenp' => $op));
}


?>