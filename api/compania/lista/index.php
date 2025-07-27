<?php
include '../../../config.php';

error_reporting(0);

if (isset($_POST) && !empty($_POST)) {
    $page = $_POST['page']; // get the requested page
    $limit = $_POST['rows']; // get how many rows we want to have into the grid
    $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
    $sord = $_POST['sord']; // get the direction

    //////////////////////////////////////////////se recibe los parametros POST del grid////////////////////////////////////////////////
    $_criterio = $_POST['criterio'];

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    if (!empty($_FecIni)) $_FecIni = date("Y-m-d", strtotime($_FecIni));
    if (!empty($_FecFin)) $_FecFin = date("Y-m-d", strtotime($_FecFin));

    $start = $limit*$page - $limit; // do not put $limit*($page - 1)

    if(!$sidx) $sidx =1;

    // se conecta a la base de datos
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    // prepara la llamada al procedimiento almacenado Lis_Facturas
    $sqlCount = "SELECT count(clave_empresa) as cuenta from c_compania Where Activo = '1';";
    if (!($res = mysqli_query($conn, $sqlCount))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $row = mysqli_fetch_array($res);
    $count = $row[0];

    mysqli_close();

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    mysqli_set_charset ( $conn, "utf8");

    $sql = "SELECT c.*, 
            IF(IFNULL(c.Es_3PL, 'N') = 'N', 'No', 'Si') as es3pl,
            #IF(IFNULL(c.es_transportista, 0) = 1, 'Si', 'No') as es_transportadora,
            t.des_tipcia 
            FROM c_compania c LEFT JOIN c_tipocia t ON t.cve_tipcia = c.cve_tipcia Where c.des_cia like '%".$_criterio."%' and c.Activo = '1' ORDER by des_cia";

    // hace una llamada previa al procedimiento almacenado Lis_Facturas
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
        $arr[] = $row;
        $responce->rows[$i]['id']=$row['cve_cia'];
        $responce->rows[$i]['cell']=array('',$row['cve_cia'],$row['clave_empresa'], $row['des_cia'], $row['des_tipcia']/*, $row['es_transportadora']*/, $row['des_direcc'], $row['des_contacto'], $row['des_telef'], $row['des_email'], $row['es3pl']);
        $i++;
    }
    echo json_encode($responce);
}
