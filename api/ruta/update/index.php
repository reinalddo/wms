<?php

include '../../../app/load.php';

// Initalize Slim
$app = new \Slim\Slim();

if( !isset( $_SESSION['id_user'] ) AND !$_SESSION['id_user'] ) {
    exit();
}

$ga = new \Ruta\Ruta();

if( $_POST['action'] == 'load' ) {

    $clientes = new \Clientes\Clientes();
    if(isset($_POST['Cve_Ruta']))
    {
        $clientes = $clientes->traerClientesDeRutaClave($_POST["Cve_Ruta"]);

        $i = 0;
        $option = "";
        foreach ($clientes as $cliente) {
            $option .= "<option value='".$cliente["id_cliente"]."'>( ".$cliente["Cve_Clte"]." ) - ".$cliente["RazonSocial"]."</option>";
            $i++;
        }

        $options = "<option value=''>Seleccione (".$i.")</option>".$option;
        $arr = array(
            "success" => true,
            "clientes" => $options
        );
        $arr = array_merge($arr);

        echo json_encode($arr);
    }
    else
    {
        $ga->ID_Ruta = $_POST["ID_Ruta"];
        $clientes = $clientes->traerClientesDeRuta($_POST["ID_Ruta"]);

        $ga->__get("ID_Ruta");

        $arr = array(
            "success" => true,
            "ID_Ruta" => $ga->data->ID_Ruta,
            "cve_ruta" => $ga->data->cve_ruta,
            "descripcion" => $ga->data->descripcion,
            "status" => $ga->data->status,
            "Activo" => $ga->data->Activo,
            "cve_almacenp" => $ga->data->cve_almacenp,
            "venta_preventa" => $ga->data->venta_preventa,
            "control_pallets_cont" => $ga->data->control_pallets_cont,
            "ID_Proveedor" => $ga->data->ID_Proveedor,
            "clientes" => $clientes
        );


        $arr = array_merge($arr);

        echo json_encode($arr);

    }
/*
    // $store_data["Clientes"]="";
    $store_data = array();

    foreach ($clientes as $Cliente)
    {
        $store_data['clientes'][] = array (
            'id_cliente' => $Cliente->id_cliente,
			'cve_clte' =>	$Cliente->Cve_Clte,
            'razon_social' => $Cliente->RazonSocial
        );
    }*/

    //array_push($store_data,$store_data1);
}


if( $_POST['action'] == 'add' ) {    

	$ga->save($_POST);		
	$model_cliente = new \Clientes\Clientes();		
    $idRuta = $ga->getLastInsertId()->ID;	
    $ga->ID_Ruta = $idRuta;
    $ga->__get("ID_Ruta");		
	//var_dump($_POST['clientes'][0]); exit; 


    if(!empty($_POST['clientes'])) 
    {
        for($i=0;$i<count($_POST['clientes']);$i++)
        {
            //for($j=0;$j<count($_POST['clientes'][0]);$j++)
            //{
				$ga->asignarRutaClientes($_POST['clientes'][$i],$idRuta);
				
                //$model_cliente->asignarRutaCliente($_POST['clientes'][$i][$j],$ga->data->cve_ruta);
            //}
        }
    }
	$arr = array(
		"success"=>true
	);
    echo json_encode($arr);
} 




if( $_POST['action'] == 'edit' ) {
	
	$success = true;
	//$ga->actualizarRuta($_POST);	
    $model_cliente = new \Clientes\Clientes();

	$idRuta       = $_POST["ID_Ruta"];
    $cve_ruta     = $_POST["cve_ruta"];
    $descripcion  = $_POST["descripcion"];
    $cve_almacenp = $_POST["cve_almacenp"];
    $clave_almacen = $_POST["clave_almacen"];
    $venta_preventa = $_POST["venta_preventa"];
    $id_proveedor = $_POST["id_proveedor"];
    $envases_ruta = $_POST["envases_ruta"];
    $rels_rutas = $_POST["rels_rutas"][0];

    $activo = 0;
    $status = $_POST['status'];

    if($status == 'A')
    {
      $activo = 1;
    }

    $ga->actualizarDatosRuta($idRuta, $descripcion, $cve_almacenp ,$activo, $status, $venta_preventa, $envases_ruta, $clave_almacen, $id_proveedor);

	//$clientes = $ga->borrarClientes($_POST);
	
	if(!empty($_POST['clientes'])) {
        $clientes = $ga->borrarClientes($_POST);

        for($i=0;$i<count($_POST['clientes']);$i++)
        {            
			$ga->asignarRutaClientes($_POST['clientes'][$i],$idRuta, $cve_ruta);
        }
    }   

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    if($venta_preventa == 2)
    {

        $sql = "DELETE FROM rel_RutasEntregas WHERE id_ruta_entrega = $idRuta";
        if (!($res = mysqli_query($conn, $sql))) {
            echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
        }
            //echo "count = ".count($rels_rutas);

          if(count($rels_rutas))
          {
            for($i=0;$i<count($rels_rutas);$i++)
            {
                //$rels_ruta = $_POST['rels_rutas'][$i];
                //var_dump($rels_rutas);
        $sql = "INSERT INTO rel_RutasEntregas(id_ruta_entrega, id_ruta_venta_preventa) VALUES ($idRuta, $rels_rutas[$i])";
                if (!($res = mysqli_query($conn, $sql))) {
                    echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
                }
            }
          }
    }

    //Aquí realizará una limpieza borrarando todas las rutas que estén en rel_RutasEntregas pero que no estén en t_ruta marcados como venta_preventa o que se hayan eliminado  
    $sql = "DELETE FROM rel_RutasEntregas WHERE id_ruta_entrega IN (SELECT ID_Ruta FROM t_ruta WHERE venta_preventa != 2 OR (venta_preventa = 2 AND Activo = 0))";
                if (!($res = mysqli_query($conn, $sql))) {
                    echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
                }

    $arr = array(
        "success" => $success,
    );
    echo json_encode($arr);

}

if( $_POST['action'] == 'AsignarTransporte' ) {

    $cve_ruta = $_POST['cve_ruta'];
    $id_transporte = $_POST['id_transporte'];

    $ga->AsignarTransporteRuta($cve_ruta, $id_transporte);

    $arr = array(
        "success" => true
    );

    echo json_encode($arr);

}

if( $_POST['action'] == 'EliminarTransporte' ) {

    $success = true;
    $cve_ruta = $_POST['cve_ruta'];
    $id_transporte = $_POST['id_transporte'];

    $res = $ga->EliminarTransporteRuta($cve_ruta, $id_transporte);

    if(!$res) $success = false;

    $arr = array(
        "success" => $success
    );
    echo json_encode($arr);

}

if( $_POST['action'] == 'AsignarChofer' ) {

    $success = true;
    $cve_ruta = $_POST['cve_ruta'];
    $id_agente = $_POST['id_agente'];

    $res = $ga->AsignarChoferRuta($cve_ruta, $id_agente);

    if(!$res) $success = false;

    $arr = array(
        "success" => $success
    );

    echo json_encode($arr);

}

if( $_POST['action'] == 'EliminarCliente' ) {

    $success = true;
    $cve_ruta = $_POST['cve_ruta'];
    $id_cliente = $_POST['id_cliente'];

    $res = $ga->EliminarClienteRuta($cve_ruta, $id_cliente);

    if(!$res) $success = false;

    $arr = array(
        "success" => $success
    );

    echo json_encode($arr);

}

if( $_POST['action'] == 'EliminarChofer' ) {

    $success = true;
    $cve_ruta = $_POST['cve_ruta'];
    $id_agente = $_POST['id_agente'];

    $res = $ga->EliminarChoferRuta($cve_ruta, $id_agente);

    if(!$res) $success = false;

    $arr = array(
        "success" => $success
    );

    echo json_encode($arr);

}

if( $_POST['action'] == 'exists' ) {
    $ga->cve_ruta = $_POST["cve_ruta"];
    $ga->validaClave("cve_ruta");

    $success = false;

    if (!empty($ga->data->cve_ruta)) {
        $success = true;
    }

    $arr = array(
        "success" => $success
    );

    echo json_encode($arr);

}

if( $_POST['action'] == 'delete' ) {
    $ga->borrarRuta($_POST);
    $ga->ID_Ruta = $_POST["ID_Ruta"];
    $ga->__get("ID_Ruta");
	$ga->borrarClientes($_POST);
    $id_ruta = $_POST["ID_Ruta"];
	

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    //Aquí realizará una limpieza borrarando todas las rutas que estén en rel_RutasEntregas pero que no estén en t_ruta marcados como venta_preventa o que se hayan eliminado  
    $sql = "DELETE FROM rel_RutasEntregas WHERE id_ruta_entrega IN (SELECT ID_Ruta FROM t_ruta WHERE venta_preventa != 2 OR (venta_preventa = 2 AND Activo = 0))";
                if (!($res = mysqli_query($conn, $sql))) {
                    echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
                }

    $sql = "DELETE FROM Rel_Ruta_Transporte WHERE cve_ruta IN (SELECT cve_ruta FROM t_ruta WHERE ID_Ruta = $id_ruta)";
                if (!($res = mysqli_query($conn, $sql))) {
                    echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
                }

    $arr = array(
        "success" => true,
        //"nombre_proveedor" => $ga->data->Empresa,
        //"contacto" => $ga->data->VendId
    );

    echo json_encode($arr);

} 
if( $_POST['action'] == 'tieneCliente' ) {
    $ga->ID_Ruta = $_POST["ID_Ruta"];
    
    $ga->tieneCliente("ID_Ruta");

    $success = false;

    if (!empty($ga->data->ID_Ruta)) {
        $success = true;
    }

    $arr = array(
        "success" => $success
    );

    echo json_encode($arr);

}


 
if( $_POST['action'] == 'recovery' ) {
    $ga->recoveryRuta($_POST);
    $ga->ID_Ruta = $_POST["ID_Ruta"];
    $ga->__get("ID_Ruta");
    $arr = array(
        "success" => true,
    );

    echo json_encode($arr);
}

if( $_POST['action'] == 'clientexruta' ) {    
	$clientes = array();
    $clientes = $ga->traerClientesxRuta();

    $arr = array(
        "success" => true,
		"clientes" => $clientes
    );

    $arr = array_merge($arr);

    echo json_encode($arr);

}

if( $_POST['action'] == 'getOperadoresAsignadosRuta' ) {

    if(isset($_POST['Cve_Ruta']))
    {
        $asignados_si_no = $_POST['asignados_si_no'];
        $operadores = $ga->OperadoresRuta($_POST["Cve_Ruta"], $asignados_si_no, $_POST["cve_almac"]);

        $i = 0;
        $option = "";
        foreach ($operadores as $operador) {
            $option .= "<option value='".$operador["cve_usuario"]."'>( ".$operador["cve_usuario"]." ) - ".$operador["nombre_completo"]."</option>";
            $i++;
        }

        //"<option value=''>Seleccione Agente | Operador (".$i.")</option>".
        $options = $option;
        $arr = array(
            "success" => true,
            "operadores" => $options
        );
        $arr = array_merge($arr);

        echo json_encode($arr);
    }
}

if( $_POST['action'] == 'traerRutasAsignadasDisponibles' ) 
{
    $id_ruta = $_POST['ID_Ruta'];
    $id_almacen = $_POST['id_almacen'];

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql = "SELECT * FROM t_ruta WHERE venta_preventa = 1 AND cve_almacenp = {$id_almacen} AND ID_Ruta NOT IN (SELECT id_ruta_venta_preventa FROM rel_RutasEntregas)";
    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $rows_from = array();
    while($row = mysqli_fetch_array($res))
    {
        $rows_from[] = $row;
    }

    $rows_to = array();
    if($id_ruta)
    {
        $sql = "SELECT 
                r.* 
                FROM t_ruta r
                INNER JOIN rel_RutasEntregas rr ON rr.id_ruta_venta_preventa = r.ID_Ruta AND rr.id_ruta_entrega = {$id_ruta}";

        if (!($res = mysqli_query($conn, $sql))) {
            echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
        }

        while($row = mysqli_fetch_array($res))
        {
            $rows_to[] = $row;
        }
    }

    $arr = array(
        "success" => true,
        "from" => $rows_from,
        "to" => $rows_to
    );

    echo json_encode($arr);
}