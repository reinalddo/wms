<?php

require_once '../../app/vendor/autoload.php';
require_once '../../Framework/autoload.php';
require_once '../../config.php';

use Framework\Helpers\Utils;

function crearConsolidadoDeOla()
{
    $folios = $_POST['folios'];

    $totalFolio = count($folios) - 1;
    $foliosStr = '';
    
    foreach($folios as $key => $value){
        $foliosStr .= "'{$value}'";
        if($key !== $totalFolio){
            $foliosStr .= ',';
        }
    }

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $folio = $folios[0];

    //$sql = "SELEC FROM "'Fol_folio', $folio;

    if (!($res = mysqli_query($conn, $sqlCount))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

    $proveedor_clave = $folio['Cve_CteProv'];

    $instance = new ConsolidadosOla();
    $instance->CodB_Prov    = Utils::pSQL($request->nombre);
    $instance->NIT_Prov     = Utils::pSQL($request->nombre);
    $instance->Nom_Prov     = Utils::pSQL($request->nombre);

    $instance->Cve_CteCon   = Utils::pSQL($request->nombre);
    $instance->CodB_CteCon  = Utils::pSQL($request->nombre);
    $instance->Nom_CteCon   = Utils::pSQL($request->nombre);
    $instance->Dir_CteCon   = Utils::pSQL($request->nombre);
    $instance->Cd_CteCon    = Utils::pSQL($request->nombre);
    $instance->NIT_CteCon   = Utils::pSQL($request->nombre);
    $instance->Cod_CteCon   = Utils::pSQL($request->nombre);
    
    $instance->CodB_CteEnv  = Utils::pSQL($request->nombre);
    $instance->Nom_CteEnv   = Utils::pSQL($request->nombre);
    $instance->Dir_CteEnv   = Utils::pSQL($request->nombre);
    $instance->Cd_CteEnv    = Utils::pSQL($request->nombre);
    $instance->Tel_CteEnv   = Utils::pSQL($request->nombre);
    $instance->Fec_Entrega  = Utils::pSQL($request->nombre);
    $instance->Tot_Cajas    = Utils::pSQL($request->nombre);
    $instance->Tot_Pzs      = Utils::pSQL($request->nombre);
    $instance->Placa_Trans  = Utils::pSQL($request->nombre);
    $instance->Sellos       = Utils::pSQL($request->nombre);
    $instance->Fol_PedidoCon = Utils::pSQL($request->nombre);
    $instance->No_OrdComp   = Utils::pSQL($request->nombre);
    $instance->Status       = Utils::pSQL($request->nombre);



    $instance->save();

    echo json_encode($response);
}

if( ! empty($_POST) && $_POST['action'] === 'crearConsolidadoDeOla'){
    crearConsolidadoDeOla();
}

