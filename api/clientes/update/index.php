<?php
include '../../../app/load.php';
// Initalize Slim
$app = new \Slim\Slim();

if( !isset( $_SESSION['id_user'] ) AND !$_SESSION['id_user'] ) {
    exit();
}

$ga = new \Clientes\Clientes();
$ga_destinatarios = new \Destinatarios\Destinatarios();

if( $_POST['action'] == 'add' ) 
{
  $ga->save($_POST);
  if($_POST['usar_direccion'] == 1)
  {
     $ga_destinatarios->save_destinatario_cliente($_POST);
  }
//  else
//    $ga_destinatarios->BorrarDireccionPrincipal($_POST);

	$arr = array(
    "success"=>true
	);
  echo json_encode($arr);
} 

if( $_POST['action'] == 'edit' ) {
    $ga->actualizarClientes($_POST);
    $tiene_principal = $ga_destinatarios->ConsultarDireccionPrincipal($_POST);

    $dest_res = "";
    if($_POST['usar_direccion'] == 1 && !$tiene_principal)
    {
        $ga_destinatarios->save_destinatario_cliente($_POST);
        $dest_res = 1;
    }
    else
    {
        $dest_res = $ga_destinatarios->actualizarDestinatarioPrincipal($_POST);
    }
    

//    else if($tiene_principal)
//        $ga_destinatarios->BorrarDireccionPrincipal($_POST);

		$arr = array(
		"success"=>true,
        "usar_direccion" => $_POST['usar_direccion'],
        "tiene_principal" => $tiene_principal,
        "dest_res" => $dest_res
	);

    echo json_encode($arr);
}

if( $_POST['action'] == 'exists' ) 
{
  $Cve_Clte = $ga->exist($_POST["Cve_Clte"]);
  $Cve_Clte_otro_almacen = $ga->existe_en_otro_almacen($_POST["Cve_Clte"], $_POST["id_almacen"]);

  $success_otro_almacen = false;
  if($Cve_Clte == true)
  {
    $success = true;
    if($Cve_Clte_otro_almacen == true)
        $success_otro_almacen = true;
  }
  else
  {
    $success= false;
  }
  $arr = array(
    "success"=>$success,
    "success_otro_almacen"=>$success_otro_almacen
  );
  echo json_encode($arr);
}

if( $_POST['action'] == 'CopiarClienteA_Almacen' ) 
{
    $cve_cliente = $_POST['cve_cliente'];
    $id_almacen = $_POST['id_almacen'];
    $sql = "INSERT INTO c_cliente(Cve_Clte, RazonSocial, RazonComercial, CalleNumero, Colonia, Ciudad, Estado, Pais, CodigoPostal, RFC, Telefono1, Telefono2, Telefono3, ClienteTipo, ClienteTipo2, ClienteGrupo, ClienteFamilia, CondicionPago, MedioEmbarque, ViaEmbarque, CondicionEmbarque, ZonaVenta, ID_Proveedor, Cve_CteProv, Activo, Cve_Almacenp, Fol_Serie, Contacto, longitud, latitud, IdEmpresa, email_cliente, Cve_SAP, Encargado, Referencia, credito, limite_credito, dias_credito, credito_actual, saldo_inicial, saldo_actual, validar_gps, cliente_general) (SELECT Cve_Clte, RazonSocial, RazonComercial, CalleNumero, Colonia, Ciudad, Estado, Pais, CodigoPostal, RFC, Telefono1, Telefono2, Telefono3, ClienteTipo, ClienteTipo2, ClienteGrupo, ClienteFamilia, CondicionPago, MedioEmbarque, ViaEmbarque, CondicionEmbarque, ZonaVenta, ID_Proveedor, Cve_CteProv, Activo, '{$id_almacen}', Fol_Serie, Contacto, longitud, latitud, IdEmpresa, email_cliente, Cve_SAP, Encargado, Referencia, credito, limite_credito, dias_credito, credito_actual, saldo_inicial, saldo_actual, validar_gps, cliente_general FROM c_cliente WHERE Cve_Clte = '{$cve_cliente}' LIMIT 1)";
    $Sql = \db()->prepare($sql);
    $Sql->execute();

  $arr = array(
    "success"=>true
  );
  echo json_encode($arr);
}



if( $_POST['action'] == 'delete' ) {
    $ga->borrarCliente($_POST);
    $ga->Cve_Clte = $_POST["Cve_Clte"];
    $ga->__get("Cve_Clte");
    $arr = array(
        "success" => true,
        //"nombre_proveedor" => $ga->data->Empresa,
        //"contacto" => $ga->data->VendId
    );

    echo json_encode($arr);

}
if( $_POST['action'] == 'load' ) {
	
	$ga->Cve_Clte = $_POST["codigo"];
    $ga->__get("Cve_Clte");	

    $arr = array(
        "success" => true,
        "id_cliente" => $ga->data->id_cliente,
		"Cve_Clte" => $ga->data->Cve_Clte,        
        "Cve_CteProv" => $ga->data->Cve_CteProv,
        "RazonSocial" => $ga->data->RazonSocial,
        "RazonComercial" => $ga->data->RazonComercial,
        "CalleNumero" => $ga->data->CalleNumero,
        "Colonia" => $ga->data->Colonia,
        "CodigoPostal" => $ga->data->CodigoPostal,
		"departamento" => $ga->data->departamento,
		"des_municipio" => $ga->data->des_municipio,		
        "Ciudad" => $ga->data->Ciudad,
        "Estado" => $ga->data->Estado,
        "Pais" => $ga->data->Pais,
		"RFC" => $ga->data->RFC,
        "Telefono1" => $ga->data->Telefono1,
        "Telefono2" => $ga->data->Telefono2,
        "email_cliente" => $ga->data->email_cliente,
        "latitud" => $ga->data->latitud,
        "longitud" => $ga->data->longitud,
        "ID_Proveedor" => $ga->data->ID_Proveedor,
		"almacenp" => $ga->data->Cve_Almacenp,
        "destinatarios" => $ga->data->destinatarios,
        "id_destinatario" => $ga->data->id_destinatario,
        "dir_principal" => $ga->data->dir_principal,
        "contacto" => $ga->data->Contacto,
        "Encargado" => $ga->data->Encargado,
        "Referencia" => $ga->data->Referencia,
        "credito" => $ga->data->credito,
        "ClienteTipo" => $ga->data->ClienteTipo,
        "ClienteTipo2" => $ga->data->ClienteTipo2,
        "ClienteGrupo" => $ga->data->ClienteGrupo,
        "limite_credito" => $ga->data->limite_credito,
        "dias_credito" => $ga->data->dias_credito,
        "credito_actual" => $ga->data->credito_actual,
        "saldo_inicial" => $ga->data->saldo_inicial,
        "saldo_actual" => $ga->data->saldo_actual,
        "validar_gps" => $ga->data->validar_gps,
        "cliente_general" => $ga->data->cliente_general
    );
/*
        "CondicionPago" => $ga->data->CondicionPago,
        "ClienteTipo" => $ga->data->ClienteTipo,
        "ZonaVenta" => $ga->data->ZonaVenta ,
*/
    echo json_encode($arr);
} 
if( $_POST['action'] == 'loadClientes' ) {   
    $clientes = $ga->getAll();
    $arr = array(
        "success" => true
    );
    $associativeArray = array();
    foreach ($clientes as $Cliente)
    {     
		$store_data[] = array(
			'id' => $Cliente->Cve_Clte,
			'razon_social' => $Cliente->RazonSocial            
		); 
    }   

    $arr = array_merge($arr,$store_data);

    echo json_encode($arr);

}
if($_POST['action'] == 'loadClientsRuta' ){
    /*$model_ruta = new \Ruta\Ruta();
    $model_ruta->ID_Ruta = $_POST["ID_Ruta"];
    $model_ruta->__get("ID_Ruta"); 


     $arr = array(
        "success" => true,
        "ID" => $model_ruta->data->cve_ruta  
    );

    echo json_encode($arr);*/

    $clientes = $ga->getAll();
    $arr = array(
        "success" => true
    );
    $associativeArray = array();
    foreach ($clientes as $Cliente)
    {
            $store_data[] = array(
                'id' => $Cliente->Cve_Clte,
                'razon_social' => $Cliente->RazonSocial,
                'cve_ruta' => $Cliente -> cve_ruta
            ); 
        
    }   

    $arr = array_merge($arr,$store_data);

    echo json_encode($arr); 

}

if($_POST['action'] == 'getDane'){


    if($_POST['codigo'])
    {
        $codDaneSql = \db()->prepare("SELECT COUNT(*) as existe  FROM c_dane WHERE cod_municipio='".$_POST["codigo"]."'");
        $codDaneSql->execute();
        $codDane = $codDaneSql->fetchAll(PDO::FETCH_ASSOC);
        $existe = $codDane[0]['existe'];

        if($existe)
        {
        $codDaneSql = \db()->prepare("SELECT departamento,des_municipio  FROM c_dane WHERE cod_municipio='".$_POST["codigo"]."'");
        $codDaneSql->execute();
        $codDane = $codDaneSql->fetchAll(PDO::FETCH_ASSOC);
        //$departamento = $codDaneSql->fetch()['departamento'];
        //$municipio = $codDaneSql->fetch()['des_municipio'];

        $arr = array(
            "success" => true,
            "departamento" =>$codDane[0]["departamento"],
            "municipio" =>$codDane[0]["des_municipio"]
        );
        echo json_encode($arr);
        }
        else
        {
            $arr = array(
                "success" => false,
                "departamento" =>"",
                "municipio" =>""
            );
            echo json_encode($arr);
        }
    }
}

if($_GET['action'] == 'asignarLista'){

    $asignar_todos = $_GET['asignar_todos'];
    $destinatarios = $_GET['destinatarios'];
    $almacen = $_GET['almacen'];
    $lista_precios_select = $_GET['lista_precios_select'];
    $lista_descuentos_select = $_GET['lista_descuentos_select'];
    $lista_promociones_select = $_GET['lista_promociones_select'];
    $modificar_listap = $_GET['modificar_listap'];
    $modificar_listad = $_GET['modificar_listad'];
    $modificar_listagp = $_GET['modificar_listagp'];
    //$Sql = \db()->prepare("SELECT departamento,des_municipio  FROM c_dane WHERE cod_municipio='".$_POST["codigo"]."'");
    //$Sql->execute();

    $destinatarios = explode(",",$destinatarios);

    $msj = "";

    if($asignar_todos == 0)
    {
        foreach($destinatarios as $d)
        {
            $sql_existe = "SELECT COUNT(*) AS existe FROM RelCliLis WHERE Id_Destinatario = {$d}";
            $Sql = \db()->prepare($sql_existe);
            $Sql->execute();
            $existeSQL = $Sql->fetchAll(PDO::FETCH_ASSOC);
            $existe     = $existeSQL[0]["existe"];

            if($existe)
            {
                $sql = "SELECT ListaP, ListaD, ListaPromo FROM RelCliLis WHERE Id_Destinatario = {$d}";
                $Sql = \db()->prepare($sql);
                $Sql->execute();
                $existeSQL = $Sql->fetchAll(PDO::FETCH_ASSOC);

                $ListaP     = $existeSQL[0]["ListaP"];
                $ListaD     = $existeSQL[0]["ListaD"];
                $ListaPromo = $existeSQL[0]["ListaPromo"];


                if(!$lista_precios_select && $modificar_listap == 1) 
                    $lista_precios_select = 'NULL';
                if(!$lista_precios_select && $modificar_listap == 0) 
                    $lista_precios_select = $ListaP;

                //if($modificar_listap == 1)
                    //$lista_precios_select = $ListaP;

                if(!$lista_descuentos_select && $modificar_listad == 1) 
                    $lista_descuentos_select = 'NULL';
                if(!$lista_descuentos_select && $modificar_listad == 0) 
                    $lista_descuentos_select = $ListaD;

                //if($modificar_listad == 1)
                    //$lista_descuentos_select = $ListaD;

                if(!$lista_promociones_select && $modificar_listagp == 1) 
                    $lista_promociones_select = 'NULL';
                if(!$lista_promociones_select && $modificar_listagp == 0) 
                    $lista_promociones_select = $ListaPromo;

                //if($modificar_listagp == 1)
                    //$lista_promociones_select = $ListaPromo;


                $Sql = \db()->prepare("UPDATE RelCliLis SET ListaP = IFNULL('{$lista_precios_select}', ''), 
                                                            ListaD = IFNULL('{$lista_descuentos_select}', ''), 
                                                            ListaPromo = IFNULL('{$lista_promociones_select}', '') 
                                        WHERE Id_Destinatario = {$d};");

                $Sql->execute();

                $Sql = \db()->prepare("COMMIT;");
                $Sql->execute();
            }
            else
            {
                if(!$lista_precios_select && $modificar_listap == 0) $lista_precios_select = 'NULL';
                if(!$lista_descuentos_select && $modificar_listad == 0) $lista_descuentos_select = 'NULL';
                if(!$lista_promociones_select && $modificar_listagp == 0) $lista_promociones_select = 'NULL';
                $Sql = \db()->prepare("INSERT INTO RelCliLis(Id_Destinatario, ListaP, ListaD, ListaPromo) VALUES({$d}, {$lista_precios_select}, {$lista_descuentos_select}, {$lista_promociones_select})");
                echo $sql;
                $Sql->execute();
                $Sql = \db()->prepare("COMMIT;");
                $Sql->execute();
            }

            //$msj .= $existe.", ";
        }
    }
    else
    {
        $Sql = \db()->prepare("INSERT INTO RelCliLis(Id_Destinatario) (SELECT id_destinatario FROM c_destinatarios WHERE dir_principal = 1 AND id_destinatario NOT IN (SELECT Id_Destinatario FROM RelCliLis) AND Cve_Clte IN (SELECT Cve_Clte FROM c_cliente WHERE Cve_Almacenp = '{$almacen}'))");
        $Sql->execute();

        //$Sql = \db()->prepare("COMMIT;");
        //$Sql->execute();

        $array_set = array();

            
        if($modificar_listap == 1) 
        {
            $lista_precios_select = "ListaP = IFNULL('{$lista_precios_select}', '')";
            array_push($array_set, $lista_precios_select);
        }

        if($modificar_listad == 1) 
        {
            $lista_descuentos_select = "ListaD = IFNULL('{$lista_descuentos_select}', '')";
            array_push($array_set, $lista_descuentos_select);
        }

        if($modificar_listagp == 1) 
        {
            $lista_promociones_select = "ListaPromo = IFNULL('{$lista_promociones_select}', '')";
            array_push($array_set, $lista_promociones_select);
        }

        $set_update = implode(", ", $array_set);

        $criterio = $_GET['criterio'];
        $ruta_clientes = $_GET['ruta_clientes'];
        $codigoP = $_GET['codigo'];
        $id_proveedor = $_GET['id_proveedor'];

        $ands = ""; $ands2 =""; 

        if(!empty($criterio)){

            $ands = " AND (RazonSocial LIKE '%$criterio%' OR Cve_Clte LIKE '%$criterio%' OR id_cliente LIKE '%$criterio%' OR Cve_CteProv LIKE '%$criterio%' OR CalleNumero LIKE '%$criterio%' OR Colonia LIKE '%$criterio%' OR Ciudad LIKE '%$criterio%' OR Estado LIKE '%$criterio%' OR Pais LIKE '%$criterio%' OR CodigoPostal LIKE '%$criterio%' OR RFC LIKE '%$criterio%') ";
        }
        if(!empty($codigoP)){
            $ands .= " AND CodigoPostal = '$codigoP' ";
        }

      if(!empty($ruta_clientes)){
            $ands2.=" AND id_destinatario IN (SELECT clave_cliente FROM t_clientexruta WHERE clave_ruta = (SELECT ID_Ruta FROM t_ruta WHERE cve_ruta = '$ruta_clientes')) ";
        }

      if(!empty($id_proveedor)){
            $ands.=" AND ID_Proveedor = '$id_proveedor' ";
        }

        $Sql = \db()->prepare("UPDATE RelCliLis SET $set_update WHERE Id_Destinatario IN (SELECT id_destinatario FROM c_destinatarios WHERE dir_principal = 1 
            AND Cve_Clte IN (SELECT Cve_Clte FROM c_cliente WHERE Cve_Almacenp = '{$almacen}' {$ands}) {$ands2} )");

        $Sql->execute();

                $Sql = \db()->prepare("COMMIT;");
                $Sql->execute();
    }

    $arr = array(
        "success" => true,
        "mensaje" =>$msj,
        "destinatarios" =>$destinatarios
    );

    echo json_encode($arr);

}

if($_GET['action'] == 'listas_selects'){

    $almacen = $_GET['almacen'];

    $sql_precios = "SELECT * FROM (
                SELECT  l.id AS id, 
                  IF(STR_TO_DATE(l.FechaFin, '%Y-%m-%d') >= STR_TO_DATE(CURDATE(), '%Y-%m-%d'), '1', '0') AS status_res,
                  l.Lista AS Lista, 
                  IF(l.Tipo = 1, 'Precio Normal', 'Precio por Rango') AS Tipo,
                  DATE_FORMAT(l.FechaIni, '%d-%m-%Y') AS FechaIni,
                  DATE_FORMAT(l.FechaFin, '%d-%m-%Y') AS FechaFin
                FROM listap l 
                LEFT JOIN c_almacenp a ON a.id = l.Cve_Almac 
                LEFT JOIN detallelp d ON l.id = d.ListaId
                LEFT JOIN RelCliLis r ON r.ListaP = l.id 
                WHERE a.id = '{$almacen}'
                GROUP BY id
                ) AS precios WHERE precios.status_res = 1";
    $Sql = \db()->prepare($sql_precios);
    $Sql->execute();
    $lista_precios = $Sql->fetchAll(PDO::FETCH_ASSOC);

    $lista_precios_select = "<option value=''>Seleccione Lista de Precios</option>";
    foreach($lista_precios as $p)
    {
        $lista_precios_select .= "<option value='".$p["id"]."'>[".$p["Lista"]."] - [".$p["Tipo"]."]</option>";
    }

    $sql_descuentos = "SELECT * FROM (
                SELECT  l.id AS id, 
                  IF(STR_TO_DATE(l.FechaFin, '%Y-%m-%d') >= STR_TO_DATE(CURDATE(), '%Y-%m-%d') OR l.Caduca = 0, '1', '0') AS status_res,
                  l.Lista AS Lista, 
                  IF(l.Tipo = 1, 'Descuento Normal', 'Descuento por Rango') AS Tipo,
                  DATE_FORMAT(l.FechaIni, '%d-%m-%Y') AS FechaIni,
                  DATE_FORMAT(l.FechaFin, '%d-%m-%Y') AS FechaFin,
                  a.nombre AS Almacen,
                  COUNT(DISTINCT d.Articulo) AS total_productos,
                  COUNT(DISTINCT r.Id_Destinatario) AS total_clientes
                FROM listad l 
                LEFT JOIN c_almacenp a ON a.id = l.Cve_Almac 
                LEFT JOIN detalleld d ON l.id = d.ListaId
                LEFT JOIN RelCliLis r ON r.ListaD = l.id 
                WHERE a.id = '{$almacen}'
                GROUP BY id
                ) AS descuentos WHERE descuentos.status_res = 1";

    $Sql = \db()->prepare($sql_descuentos);
    $Sql->execute();
    $lista_descuentos = $Sql->fetchAll(PDO::FETCH_ASSOC);

    $lista_descuentos_select = "<option value=''>Seleccione Lista de Descuentos</option>";
    foreach($lista_descuentos as $p)
    {
        $lista_descuentos_select .= "<option value='".$p["id"]."'>[".$p["Lista"]."] - [".$p["Tipo"]."]</option>";
    }
/*
    $sql_promociones = "SELECT * FROM (
            SELECT  l.id AS id, 
              IF(STR_TO_DATE(l.FechaF, '%Y-%m-%d') >= STR_TO_DATE(CURDATE(), '%Y-%m-%d') OR l.Caduca = 0, '1', '0') AS status_res,
              l.Lista AS Clave, 
              l.Descripcion AS Lista,
              IF(l.Tipo = 'unidad', 'Unidad', 'Grupo') AS Tipo,
              IF(l.Tipo = 'unidad', CONCAT('(',ar.cve_articulo, ') - ' ,ar.des_articulo), CONCAT('(', g.cve_sgpoart,') - ' ,g.des_sgpoart)) AS articulo_grupo,
              DATE_FORMAT(l.FechaI, '%d-%m-%Y') AS FechaIni,
              DATE_FORMAT(l.FechaF, '%d-%m-%Y') AS FechaFin,
              a.nombre AS Almacen
            FROM ListaPromo l 
            LEFT JOIN c_almacenp a ON a.id = l.Cve_Almac 
            LEFT JOIN c_articulo ar ON ar.cve_articulo = l.Grupo
            LEFT JOIN c_sgpoarticulo g ON g.cve_gpoart = l.Grupo
            WHERE a.id = '{$almacen}'
            GROUP BY id
        ) AS promociones WHERE promociones.status_res = 1";
*/
    $sql_promociones = "SELECT p.Id, p.ListaMaster 
                        FROM ListaPromoMaster p
                        LEFT JOIN c_almacenp a ON a.id = p.Cve_Almac
                        WHERE a.id = '{$almacen}'";

    $Sql = \db()->prepare($sql_promociones);
    $Sql->execute();
    $lista_promociones = $Sql->fetchAll(PDO::FETCH_ASSOC);
/*
    $lista_promociones_select = "<option value=''>Seleccione Lista de Promociones</option>";
    foreach($lista_promociones as $p)
    {
        $lista_promociones_select .= "<option value='".$p["id"]."'>[".$p["Clave"]."] - [".$p["Lista"]."] - [".$p["Tipo"]."] - [".$p["articulo_grupo"]."]</option>";
    }
*/
    $lista_promociones_select = "<option value=''>Seleccione Grupo de Promociones</option>";
    foreach($lista_promociones as $p)
    {
        $lista_promociones_select .= "<option value='".$p["Id"]."'>[".$p["Id"]."] - [".$p["ListaMaster"]."]</option>";
    }

    $arr = array(
        "success" => true,
        "lista_precios" => $lista_precios_select,
        "lista_descuentos" => $lista_descuentos_select,
        "lista_promociones" => $lista_promociones_select
    );

    echo json_encode($arr);

}


if( $_POST['action'] == 'getClasificacion1' ) 
{
    $grupocliente = $_POST['grupocliente'];

        $sql_id = "SELECT id FROM c_gpoclientes WHERE cve_grupo = '{$grupocliente}'";
        $Sql = \db()->prepare($sql_id);
        $Sql->execute();
        $row_id = $Sql->fetchAll(PDO::FETCH_ASSOC);
        $id_grupo = $row_id[0]["id"];

    $sql_options = "SELECT id, Cve_TipoCte, Des_TipoCte FROM c_tipocliente WHERE id_grupo = {$id_grupo} AND Activo = 1";

    $Sql = \db()->prepare($sql_options);
    $Sql->execute();
    $lista_options = $Sql->fetchAll(PDO::FETCH_ASSOC);

    $lista_options_select = "<option value=''>Seleccione Clasificación</option>";
    foreach($lista_options as $p)
    {
        if(isset($_POST['clasif2']))
            $lista_options_select .= "<option value='".$p["id"]."'>[".$p["Cve_TipoCte"]."] - ".$p["Des_TipoCte"]."</option>";
        else
            $lista_options_select .= "<option value='".$p["Cve_TipoCte"]."'>[".$p["Cve_TipoCte"]."] - ".$p["Des_TipoCte"]."</option>";
    }


    $arr = array(
        "success" => true,
        "lista_options" => $lista_options_select
    );

    echo json_encode($arr);

}

if( $_POST['action'] == 'getClasificacion2' ) 
{
    $tipocliente = $_POST['tipocliente'];

        $sql_id = "SELECT id FROM c_tipocliente WHERE Cve_TipoCte = '{$tipocliente}'";
        $Sql = \db()->prepare($sql_id);
        $Sql->execute();
        $row_id = $Sql->fetchAll(PDO::FETCH_ASSOC);
        $id_tipocliente = $row_id[0]["id"];

    $sql_options = "SELECT Cve_TipoCte, Des_TipoCte FROM c_tipocliente2 WHERE id_tipocliente = {$id_tipocliente} AND Activo = 1";

    $Sql = \db()->prepare($sql_options);
    $Sql->execute();
    $lista_options = $Sql->fetchAll(PDO::FETCH_ASSOC);

    $lista_options_select = "<option value=''>Seleccione Clasificación</option>";
    foreach($lista_options as $p)
    {
        $lista_options_select .= "<option value='".$p["Cve_TipoCte"]."'>[".$p["Cve_TipoCte"]."] - ".$p["Des_TipoCte"]."</option>";
    }


    $arr = array(
        "success" => true,
        "lista_options" => $lista_options_select
    );

    echo json_encode($arr);

}

if( $_POST['action'] == 'recovery' ) {
    $ga->recoveryCliente($_POST);
    $ga->id_cliente = $_POST["id_cliente"];
    $ga->__get("id_cliente");
    $arr = array(
        "success" => true,
    );

    echo json_encode($arr);

}

if( $_POST['action'] == 'asignarRutaACliente' ) 
{
  $ga->asignarRutaACliente($_POST);
	$arr = array(
    "success"=>true
	);
  echo json_encode($arr);
} 

if( $_POST['action'] == 'getConsecutivo' ) 
{
  $consecutivo = $ga->getConsecutivo();
 
  echo json_encode($consecutivo);
}

