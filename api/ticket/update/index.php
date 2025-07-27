<?php
include '../../../app/load.php';

error_reporting(0);
// Initalize Slim
$app = new \Slim\Slim();

if( !isset( $_SESSION['id_user'] ) AND !$_SESSION['id_user'] ) {
    exit();
}


if( $_POST['action'] == 'extraer_ticket' ) 
{
    $IdEmpresa = $_POST['almacen'];

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql = "SELECT Linea1, Linea2, Linea3, Linea4, Mensaje, Tdv, MLiq FROM CTiket WHERE IdEmpresa = '{$IdEmpresa}';";
    $query = mysqli_query($conn, $sql);

    $row = mysqli_fetch_array($query);
    extract($row);

    $array = ["Linea1" => $Linea1, 
              "Linea2" => $Linea2, 
              "Linea3" => $Linea3, 
              "Linea4" => $Linea4, 
              "Mensaje" => $Mensaje, 
              "Tdv" => $Tdv, 
              "MLiq" => $MLiq];


    echo json_encode($array);
}


if( $_POST['action'] == 'registrar_actualizar' ) 
{
    $IdEmpresa = $_POST['almacen'];
    $Linea1    = $_POST['Linea1'];
    $Linea2    = $_POST['Linea2'];
    $Linea3    = $_POST['Linea3'];
    $Linea4    = $_POST['Linea4'];
    $Mensaje   = $_POST['Mensaje'];
    $Tdv       = $_POST['Tdv'];
    $MLiq      = $_POST['MLiq'];

    $resul = "";

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql = "SELECT Linea1, Linea2, Linea3, Linea4, Mensaje, Tdv, MLiq FROM CTiket WHERE IdEmpresa = '{$IdEmpresa}';";
    $query = mysqli_query($conn, $sql);
    if(mysqli_num_rows($query))
    {
        $sql = "UPDATE CTiket SET Linea1 = '{$Linea1}', Linea2 = '{$Linea2}', Linea3 = '{$Linea3}', Linea4 = '{$Linea4}', Mensaje = '{$Mensaje}', Tdv = '{$Tdv}', MLiq = '{$MLiq}' WHERE IdEmpresa = '{$IdEmpresa}';";
    }
    else 
    {
        $sql = "INSERT INTO CTiket(Linea1, Linea2, Linea3, Linea4, Mensaje, Tdv, MLiq, IdEmpresa) VALUES ('{$Linea1}', '{$Linea2}', '{$Linea3}', '{$Linea4}', '{$Mensaje}', '{$Tdv}', '{$MLiq}', '{$IdEmpresa}');";
    }


    $query = mysqli_query($conn, $sql);

    echo 1;
}

