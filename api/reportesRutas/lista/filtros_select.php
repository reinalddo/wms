<?php 
include '../../../config.php';

error_reporting(0);

if(isset($_POST) && $_POST['action'] == 'extraer_diaso')
{
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $cve_ruta = $_POST['cve_ruta'];

   	$sqlRutas = "";
    if($cve_ruta)
	    $sqlRutas = " t_ruta.cve_ruta = '{$cve_ruta}' AND ";


/*
    $sql = "SELECT distinct * FROM (
			select DISTINCT DiaO from Venta 
			INNER JOIN t_ruta ON t_ruta.ID_Ruta = Venta.RutaId
			where {$sqlRutas} DiaO is not null

			UNION

			select DISTINCT DiaO from V_Cabecera_Pedido 
			INNER JOIN t_ruta ON t_ruta.ID_Ruta = V_Cabecera_Pedido.Ruta
			where {$sqlRutas} DiaO is not null
			ORDER BY DiaO Desc
			) as diaso";
*/

    $sqlEnvase = "";
    if(isset($_POST['envases']))
    {
        $sqlEnvase = " AND DiaO IN (SELECT DiaO FROM DevEnvases WHERE RutaId = (SELECT ID_Ruta FROM t_ruta WHERE cve_ruta = '{$cve_ruta}')) ";
    }

/*
    $sql = "SELECT DISTINCT DiaO, DATE_FORMAT(Fecha, '%d-%m-%Y') as Fecha 
            FROM DiasO 
            INNER JOIN t_ruta ON t_ruta.ID_Ruta = DiasO.RutaId
            WHERE t_ruta.cve_ruta = '{$cve_ruta}' {$sqlEnvase} {$sqlCobranza} 
            ORDER BY DiaO DESC";
*/
/*
      $sql = "SELECT IFNULL(IFNULL(DiaO, 1), '') AS DiaO, DATE_FORMAT(Fecha, '%d-%m-%Y') as Fecha 
              FROM DiasO 
              WHERE RutaId = (SELECT ID_Ruta FROM t_ruta WHERE cve_ruta = '{$cve_ruta}') AND Id = (SELECT MAX(Id) FROM DiasO WHERE RutaId = (SELECT ID_Ruta FROM t_ruta WHERE cve_ruta = '{$cve_ruta}')) {$sqlEnvase} {$sqlCobranza} 
              ORDER BY DiaO DESC";
*/
      $sql = "SELECT IFNULL(IFNULL(DiaO, 1), '') AS DiaO, DATE_FORMAT(Fecha, '%d-%m-%Y') as Fecha 
              FROM DiasO 
              WHERE RutaId = (SELECT ID_Ruta FROM t_ruta WHERE cve_ruta = '{$cve_ruta}') AND Id IN (SELECT Id FROM DiasO WHERE RutaId = (SELECT ID_Ruta FROM t_ruta WHERE cve_ruta = '{$cve_ruta}')) {$sqlEnvase} 
              ORDER BY DiasO.Fecha DESC";

    $sqlCobranza = "";
    if(isset($_POST['cobranza']))
    {
        $sqlCobranza = " AND DiaO IN (SELECT DiaO FROM Cobranza WHERE RutaId = (SELECT ID_Ruta FROM t_ruta WHERE cve_ruta = '{$cve_ruta}')) ";
      $sql = "SELECT IFNULL(IFNULL(DiaO, 1), '') AS DiaO, DATE_FORMAT(Fecha, '%d-%m-%Y') as Fecha 
              FROM DiasO 
              WHERE RutaId = (SELECT ID_Ruta FROM t_ruta WHERE cve_ruta = '{$cve_ruta}') 
              {$sqlCobranza} 
              ORDER BY STR_TO_DATE(Fecha, '%Y-%m-%d') DESC";
    }


    if(isset($_POST['bitacora']))
    {
            $sql = "SELECT DISTINCT DiaO, DATE_FORMAT(Fecha, '%d-%m-%Y') as Fecha 
                    FROM DiasO 
                    INNER JOIN t_ruta ON t_ruta.ID_Ruta = DiasO.RutaId
                    WHERE t_ruta.cve_ruta = '{$cve_ruta}' 
                    ORDER BY DiaO DESC";
    }

    if(isset($_POST['ventas']))
    {
    $sql = "SELECT DISTINCT DiaO, DATE_FORMAT(Fecha, '%d-%m-%Y') as Fecha 
            FROM DiasO 
            INNER JOIN t_ruta ON t_ruta.ID_Ruta = DiasO.RutaId
            WHERE t_ruta.cve_ruta = '{$cve_ruta}' {$sqlEnvase} {$sqlCobranza} 
            ORDER BY DiaO DESC";
    }

    if(isset($_POST['noventas']))
    {
        $almacen = $_POST['almacen'];
        $sql = "SELECT DISTINCT DiaO, DATE_FORMAT(Fecha, '%d-%m-%Y') as Fecha FROM Noventas WHERE IdEmpresa = (SELECT clave FROM c_almacenp WHERE id = {$almacen}) AND RutaId = (SELECT ID_Ruta FROM t_ruta WHERE cve_ruta = '{$cve_ruta}') ORDER BY DiaO DESC";
    }

    if (!($res = mysqli_query($conn, $sql))) {
        echo json_encode(array( "error" => "Error al procesar la petición: (" . mysqli_error($conn) . ") "));
    }

    $options = "<option value=''>Seleccione DiaO</option>";
    while($row = mysqli_fetch_array($res)){
        extract($row);
        $options .= "<option value='{$DiaO}'>{$DiaO} | {$Fecha}</option>";
    };
    mysqli_close();
    header('Content-type: application/json');
    echo json_encode( $options);
}
?>