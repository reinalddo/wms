<?php
include '../../../config.php';

error_reporting(0);


if (isset($_POST) && !empty($_POST)) {
    $page = $_POST['page']; // get the requested page
    $limit = $_POST['rows']; // get how many rows we want to have into the grid
    $sidx = $_POST['sidx']; // get index row - i.e. user click to sort
    $sord = $_POST['sord']; // get the direction
	$almacenp = $_POST['almacen'];
    //////////////////////////////////////////////se recibe los parametros POST del grid////////////////////////////////////////////////
    $_criterio = $_POST['criterio'];
    $ruta_clientes = $_POST['ruta_clientes'];
    $codigoP = $_POST['codigo'];
    $id_proveedor = $_POST['id_proveedor'];

    $ands = ""; $ands2 =""; $ands3 = ""; $ands4 =""; $ands5 = "";
    if(!empty($_criterio)){
        //$ands =" AND concat(c.RazonSocial,' ',c.Cve_Clte,' ',a.nombre, ' ', c.id_cliente, ' ', c.Cve_CteProv, ' ', c.CalleNumero, ' ',c.Colonia, ' ',c.Ciudad, ' ',c.Estado, ' ',c.Pais, ' ',c.CodigoPostal, ' ',c.RFC) like '%$_criterio%' ";

        $ands = " AND (c.RazonSocial LIKE '%$_criterio%' OR RazonComercial LIKE '%$_criterio%' OR c.Cve_Clte LIKE '%$_criterio%' OR c.id_cliente LIKE '%$_criterio%' OR c.Cve_CteProv LIKE '%$_criterio%' OR c.CalleNumero LIKE '%$_criterio%' 
       OR c.Colonia LIKE '%$_criterio%' OR c.Ciudad LIKE '%$_criterio%' OR c.Estado LIKE '%$_criterio%' OR c.Pais LIKE '%$_criterio%' OR c.CodigoPostal LIKE '%$_criterio%' OR c.RFC LIKE '%$_criterio%')";

        $ands2 =" AND concat(cr.RazonSocial,' ',cr.Cve_Clte,' ',a.nombre, ' ', cr.id_cliente, ' ', cr.Cve_CteProv, ' ', c.CalleNumero, ' ',c.Colonia, ' ',c.Ciudad, ' ',c.Estado, ' ',c.Pais, ' ',c.CodigoPostal, ' ',c.RFC) like '%$_criterio%' ";
        //$ands2 .= " AND r.clave_ruta = '$_criterio'";
    }
    if(!empty($codigoP)){
        $ands.=" AND c.CodigoPostal = '$codigoP' ";
        $ands2.=" AND cr.CodigoPostal = '$codigoP' ";
    }
	
  if(!empty($ruta_clientes)){
        $ands3.=" AND ruta2.cve_ruta = '$ruta_clientes' ";
        $ands4.=" AND ruta.cve_ruta = '$ruta_clientes' ";
    }
  
  if(!empty($id_proveedor)){
        $ands5.=" AND (c.ID_Proveedor = '$id_proveedor' OR IFNULL(c.ID_Proveedor, 0) = 0) ";
    }

    $start = $limit*$page - $limit; // do not put $limit*($page - 1)

    if(!$sidx) $sidx =1;

    // se conecta a la base de datos
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'utf8') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset2'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);

    // prepara la llamada al procedimiento almacenado Lis_Facturas
/*
    $sqlCount = 
      "
      SELECT     
        COUNT(c.id_cliente) AS cuenta, (SELECT COUNT(cr.credito) FROM c_cliente cr LEFT JOIN t_clientexruta r2 ON r2.clave_cliente = cr.id_cliente LEFT JOIN t_ruta ruta2 ON ruta2.ID_Ruta = r2.clave_ruta WHERE cr.credito = 1 {$and2} {$ands3}) AS credito
       FROM c_cliente c  
        LEFT JOIN c_almacenp a ON a.id=c.Cve_Almacenp
        LEFT JOIN c_dane d ON  d.cod_municipio=c.CodigoPostal
        LEFT JOIN t_clientexruta r ON r.clave_cliente = c.id_cliente
        LEFT JOIN t_ruta ruta ON ruta.ID_Ruta = r.clave_ruta
        LEFT JOIN rel_cliente_almacen rca ON rca.Cve_Clte = c.Cve_Clte AND rca.Cve_Almac = a.id
      WHERE c.Activo = '1' 
        #AND c.Cve_Almacenp='$almacenp' 
       AND rca.Cve_Almac = '$almacenp' {$ands} {$ands4}
      ";
*/
    $sqlCount = "SELECT COUNT(DISTINCT Cve_Clte) AS credito FROM c_cliente WHERE credito = 1 AND Cve_Almacenp='$almacenp' AND Activo = 1";#IFNULL(limite_credito, 0) > 0
	
    if (!($res = mysqli_query($conn, $sqlCount))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $row = mysqli_fetch_array($res);
    $credito = $row['credito'];

    $sqlCount = "SELECT COUNT(DISTINCT Cve_Clte) as n_clientes FROM c_cliente WHERE Cve_Almacenp='$almacenp' AND Activo = 1";#IFNULL(limite_credito, 0) > 0
    
    if (!($res = mysqli_query($conn, $sqlCount))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $row = mysqli_fetch_array($res);
    $n_clientes = $row['n_clientes'];

    //IFNULL(t_ruta.descripcion,'--') AS ruta

    $sql_rca = "AND rca.Cve_Almac = '$almacenp'";
    $left_join = "LEFT JOIN rel_cliente_almacen rca ON rca.Cve_Clte = c.Cve_Clte ";
    #AND rca.Cve_Almac = a.id    

    $sql_proveedor = "";
    if(isset($_POST['cve_proveedor']))
    {
        if($_POST['cve_proveedor'])
        {
            $cve_proveedor = $_POST['cve_proveedor'];
            $sql_proveedor = " AND (c.ID_Proveedor = '{$cve_proveedor}' OR IFNULL(c.ID_Proveedor, 0) = 0)";
        }
    }

    if((strpos($_SERVER['HTTP_HOST'], 'dicoisa') === false) /*&& (strpos($_SERVER['HTTP_HOST'], 'dicoisa') === false)*/)
    {
        $left_join = ""; $sql_rca = "AND c.Cve_Almacenp='$almacenp' ";
    }
    $sql = 
      "
      SELECT DISTINCT c.id_cliente,
        c.Cve_Clte, c.RazonSocial, c.RazonComercial, c.CalleNumero, c.Colonia, c.Ciudad, c.Estado, c.Pais, c.CodigoPostal, c.RFC, 
        c.Telefono1, c.Telefono2,  c.Telefono3, c.ClienteTipo, c.ClienteGrupo, c.ClienteFamilia, t2.Des_TipoCte as tipo2,
        c.CondicionPago, c.MedioEmbarque, c.ViaEmbarque, c.CondicionEmbarque, c.ZonaVenta, c.cve_ruta, c.Id_Proveedor, c.Cve_CteProv, 
        c.Cve_Almacenp, c.Fol_Serie, c.Contacto, c.id_destinatario, c.longitud, c.latitud, c.IdEmpresa, c.email_cliente, c.Cve_SAP, 
        c.Encargado, c.Referencia, c.credito, c.limite_credito, c.dias_credito, c.credito_actual, c.saldo_inicial, c.saldo_actual, 
        c.validar_gps, d.departamento, d.des_municipio, a.nombre AS almacenp, IFNULL(dt.id_destinatario, '') AS id_destinatario_principal, 
        GROUP_CONCAT(DISTINCT IFNULL(ruta.cve_ruta,'') SEPARATOR ',') AS ruta, 
        t.Des_TipoCte, g.des_grupo, 
        IFNULL(lp.Lista, '') AS lista_precios, IFNULL(ld.Lista, '') AS lista_descuento, IFNULL(lpr.ListaMaster, '') AS lista_promocion, lp.id as id_listaprecios, ld.id as id_listadescuentos, lpr.Id as id_listapromo
      FROM c_cliente c  
        LEFT JOIN c_almacenp a ON a.id=c.Cve_Almacenp
        LEFT JOIN c_dane d ON  d.cod_municipio=c.CodigoPostal
        LEFT JOIN c_destinatarios dt ON dt.Cve_Clte = c.Cve_Clte AND dt.dir_principal = 1
        LEFT JOIN t_clientexruta r ON r.clave_cliente = dt.id_destinatario
        #LEFT JOIN t_clientexruta r ON r.clave_cliente = c.id_cliente
        LEFT JOIN c_tipocliente t ON t.Cve_TipoCte = c.ClienteTipo
        LEFT JOIN c_tipocliente2 t2 ON t2.Cve_TipoCte = c.ClienteTipo2 and t2.id_tipocliente = t.id
        LEFT JOIN c_gpoclientes g ON g.cve_grupo = c.ClienteGrupo
        #LEFT JOIN c_destinatarios dt ON dt.Cve_Clte = c.Cve_Clte AND dt.dir_principal = 1
        LEFT JOIN t_ruta ruta ON ruta.ID_Ruta = r.clave_ruta
        LEFT JOIN RelCliLis rl ON rl.Id_Destinatario = dt.id_destinatario
        LEFT JOIN listap lp ON lp.id = rl.ListaP
        LEFT JOIN listad ld ON ld.id = rl.ListaD
        LEFT JOIN ListaPromoMaster lpr ON lpr.Id = rl.ListaPromo 
        {$left_join}
      WHERE c.Activo = '1' 
       {$sql_rca} {$ands} {$ands4} {$ands5}
       {$sql_proveedor}
       GROUP BY Cve_Clte
      ORDER BY c.RazonSocial ASC
      ";
      //LEFT JOIN t_ruta ON t_ruta.ID_Ruta = t_clientexruta.clave_ruta

    // hace una llamada previa al procedimiento almacenado Lis_Facturas
    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }
    $count = mysqli_num_rows($res);

    $sql .= " LIMIT {$start}, {$limit}; ";
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
    $responce->sql = $sql;
    $responce->total = $total_pages;
    $responce->records = $count;
    $responce->num_clientes = number_format($n_clientes, 0);
    $responce->credito = $credito;
    $responce->contado = number_format($n_clientes - $credito, 0);

    $arr = array();
    $i = 0;
    while ($row = mysqli_fetch_array($res)) {
		//$row=array_map('utf8',$row);
        $arr[] = $row;
        $responce->rows[$i]['id']=$row['id_cliente'];
        $responce->rows[$i]['cell']=array(
                                          '',
                                          '',
                                          $row['ruta'],
                                          $row['id_cliente'], 
                                          $row['Cve_Clte'],
                                          $row['Cve_CteProv'],
                                          (strtoupper($row['RazonComercial'])),
                                          (strtoupper($row['RazonSocial'])),
                                          (strtoupper($row['CalleNumero'])),
                                          $row['CodigoPostal'],
                                          $row['Colonia'],
                                          $row['Estado'],
                                          $row['Ciudad'],
                                          $row['des_grupo'],
                                          $row['Des_TipoCte'],
                                          $row['tipo2'],
                                          $row['RFC'],
                                          $row['limite_credito'],
                                          $row['saldo_inicial'],
                                          $row['lista_precios'],
                                          $row['lista_descuento'],
                                          $row['lista_promocion'],
                                          $row['id_destinatario_principal'],
                                          $row['latitud'],
                                          $row['longitud'],
                                          $row['almacenp'], 
                                          $row['id_listaprecios'],
                                          $row['id_listadescuentos'],
                                          $row['id_listapromo']
                                        );
        $i++;
    }
    echo json_encode($responce);
}

if(isset($_GET) && !empty($_GET) && $_GET['action'] === 'obtenerClaveDestinatario'){
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $sql = "SELECT COALESCE(MAX(id_destinatario), 0) + 1 AS clave FROM c_destinatarios;";
    $query = mysqli_query($conn, $sql);
    $clave = '';
    if($query->num_rows > 0){
        $clave = mysqli_fetch_assoc($query)['clave'];
    }
    mysqli_close($conn);
    echo json_encode(array(
        "clave"  => $clave
    ));
}