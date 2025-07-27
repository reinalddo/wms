<?php
    use \koolreport\widgets\google\ColumnChart;
    use \koolreport\widgets\koolphp\Table;
    use \koolreport\bootstrap4\Theme;

include '../../../../../../config.php';


    function unidad($numuero)
    {
        switch ($numuero)
        {
            case 9:
            {
                $numu = "NUEVE";
                break;
            }
            case 8:
            {
                $numu = "OCHO";
                break;
            }
            case 7:
            {
                $numu = "SIETE";
                break;
            }   
            case 6:
            {
                $numu = "SEIS";
                break;
            }   
            case 5:
            {
                $numu = "CINCO";
                break;
            }   
            case 4:
            {
                $numu = "CUATRO";
                break;
            }   
            case 3:
            {
                $numu = "TRES";
                break;
            }   
            case 2:
            {
                $numu = "DOS";
                break;
            }   
            case 1:
            {
                $numu = "UN";
                break;
            }   
            case 0:
            {
                $numu = "";
                break;
            }   
        }
        return $numu; 
    }

    function decena($numdero)
    {
        if ($numdero >= 90 && $numdero <= 99)
        {
            $numd = "NOVENTA ";
            if ($numdero > 90)
                $numd = $numd."Y ".(unidad($numdero - 90));
        }
        else if ($numdero >= 80 && $numdero <= 89)
        {
            $numd = "OCHENTA ";
            if ($numdero > 80)
                $numd = $numd."Y ".(unidad($numdero - 80));
        }
        else if ($numdero >= 70 && $numdero <= 79)
        {
            $numd = "SETENTA ";
            if ($numdero > 70)
                $numd = $numd."Y ".(unidad($numdero - 70));
        }
        else if ($numdero >= 60 && $numdero <= 69)
        {
            $numd = "SESENTA ";
            if ($numdero > 60)
                $numd = $numd."Y ".(unidad($numdero - 60));
        }
        else if ($numdero >= 50 && $numdero <= 59)
        {
            $numd = "CINCUENTA ";
            if ($numdero > 50)
                $numd = $numd."Y ".(unidad($numdero - 50));
        }
        else if ($numdero >= 40 && $numdero <= 49)
        {
            $numd = "CUARENTA ";
            if ($numdero > 40)
                $numd = $numd."Y ".(unidad($numdero - 40));
        }
        else if ($numdero >= 30 && $numdero <= 39)
        {
            $numd = "TREINTA ";
            if ($numdero > 30)
                $numd = $numd."Y ".(unidad($numdero - 30));
        }
        else if ($numdero >= 20 && $numdero <= 29)
        {
            if ($numdero == 20)
                $numd = "VEINTE ";
            else
                $numd = "VEINTI".(unidad($numdero - 20));
        }
        else if ($numdero >= 10 && $numdero <= 19)
        {
            switch ($numdero)
            {
                case 10:
                {
                    $numd = "DIEZ ";
                    break;
                }
                case 11:
                {       
                    $numd = "ONCE ";
                    break;
                }
                case 12:
                {
                    $numd = "DOCE ";
                    break;
                }
                case 13:
                {
                    $numd = "TRECE ";
                    break;
                }
                case 14:
                {
                    $numd = "CATORCE ";
                    break;
                }
                case 15:
                {
                    $numd = "QUINCE ";
                    break;
                }
                case 16:
                {
                    $numd = "DIECISEIS ";
                    break;
                }
                case 17:
                {
                    $numd = "DIECISIETE ";
                    break;
                }
                case 18:
                {
                    $numd = "DIECIOCHO ";
                    break;
                }
                case 19:
                {
                    $numd = "DIECINUEVE ";
                    break;
                }
            } 
        }
        else
            $numd = unidad($numdero);
        return $numd;
    }

    function centena($numc)
    {
        if ($numc >= 100)
        {
            if ($numc >= 900 && $numc <= 999)
            {
                $numce = "NOVECIENTOS ";
                if ($numc > 900)
                    $numce = $numce.(decena($numc - 900));
            }
            else if ($numc >= 800 && $numc <= 899)
            {
                $numce = "OCHOCIENTOS ";
                if ($numc > 800)
                    $numce = $numce.(decena($numc - 800));
            }
            else if ($numc >= 700 && $numc <= 799)
            {
                $numce = "SETECIENTOS ";
                if ($numc > 700)
                    $numce = $numce.(decena($numc - 700));
            }
            else if ($numc >= 600 && $numc <= 699)
            {
                $numce = "SEISCIENTOS ";
                if ($numc > 600)
                    $numce = $numce.(decena($numc - 600));
            }
            else if ($numc >= 500 && $numc <= 599)
            {
                $numce = "QUINIENTOS ";
                if ($numc > 500)
                    $numce = $numce.(decena($numc - 500));
            }
            else if ($numc >= 400 && $numc <= 499)
            {
                $numce = "CUATROCIENTOS ";
                if ($numc > 400)
                    $numce = $numce.(decena($numc - 400));
            }
            else if ($numc >= 300 && $numc <= 399)
            {
                $numce = "TRESCIENTOS ";
                if ($numc > 300)
                    $numce = $numce.(decena($numc - 300));
            }
            else if ($numc >= 200 && $numc <= 299)
            {
                $numce = "DOSCIENTOS ";
                if ($numc > 200)
                    $numce = $numce.(decena($numc - 200));
            }
            else if ($numc >= 100 && $numc <= 199)
            {
                if ($numc == 100)
                    $numce = "CIEN ";
                else
                    $numce = "CIENTO ".(decena($numc - 100));
            }
        }
        else
            $numce = decena($numc);
        return $numce;  
    }

    function miles($nummero){
        if ($nummero >= 1000 && $nummero < 2000)
        {
            $numm = "MIL ".(centena($nummero%1000));
        }
        if ($nummero >= 2000 && $nummero <10000)
        {
            $numm = unidad(Floor($nummero/1000))." MIL ".(centena($nummero%1000));
        }
        if ($nummero < 1000)
            $numm = centena($nummero);
        return $numm;
    }

    function decmiles($numdmero)
    {
        if ($numdmero == 10000)
          $numde = "DIEZ MIL";
        if ($numdmero > 10000 && $numdmero <20000)
        {
            $numde = decena(Floor($numdmero/1000))."MIL ".(centena($numdmero%1000));    
        }
        if ($numdmero >= 20000 && $numdmero <100000)
        {
            $numde = decena(Floor($numdmero/1000))." MIL ".(miles($numdmero%1000));   
        }   
        if ($numdmero < 10000)
            $numde = miles($numdmero);
        return $numde;
    }   

    function cienmiles($numcmero)
    {
        if ($numcmero == 100000)
            $num_letracm = "CIEN MIL";
        if ($numcmero >= 100000 && $numcmero <1000000)
        {
            $num_letracm = centena(Floor($numcmero/1000))." MIL ".(centena($numcmero%1000));    
        }
        if ($numcmero < 100000)
            $num_letracm = decmiles($numcmero);
        return $num_letracm;
    } 
  
    function millon($nummiero)
    {
        if ($nummiero >= 1000000 && $nummiero <2000000)
        {
            $num_letramm = "UN MILLON ".(cienmiles($nummiero%1000000));
        }
        if ($nummiero >= 2000000 && $nummiero <10000000)
        {
            $num_letramm = unidad(Floor($nummiero/1000000))." MILLONES ".(cienmiles($nummiero%1000000));
        }
        if ($nummiero < 1000000)
            $num_letramm = cienmiles($nummiero);
        return $num_letramm;
    } 

    function decmillon($numerodm)
    {
        if ($numerodm == 10000000)
            $num_letradmm = "DIEZ MILLONES";
        if ($numerodm > 10000000 && $numerodm <20000000)
        {
            $num_letradmm = decena(Floor($numerodm/1000000))."MILLONES ".(cienmiles($numerodm%1000000));    
        }
        if ($numerodm >= 20000000 && $numerodm <100000000)
        {
            $num_letradmm = decena(Floor($numerodm/1000000))." MILLONES ".(millon($numerodm%1000000));    
        }
        if ($numerodm < 10000000)
        {
            $num_letradmm = millon($numerodm);
        }
        return $num_letradmm;
    }

    function cienmillon($numcmeros)
    {
        if ($numcmeros == 100000000)
        {
            $num_letracms = "CIEN MILLONES";
        }
        if ($numcmeros >= 100000000 && $numcmeros <1000000000)
        {
            $num_letracms = centena(Floor($numcmeros/1000000))." MILLONES ".(millon($numcmeros%1000000));   
        }
        if ($numcmeros < 100000000)
        {
            $num_letracms = decmillon($numcmeros);
        }
        return $num_letracms;
    } 

    function milmillon($nummierod)
    {
        if ($nummierod >= 1000000000 && $nummierod <2000000000)
        {
            $num_letrammd = "MIL ".(cienmillon($nummierod%1000000000));
        }
        if ($nummierod >= 2000000000 && $nummierod <10000000000)
        {
            $num_letrammd = unidad(Floor($nummierod/1000000000))." MIL ".(cienmillon($nummierod%1000000000));
        }
        if ($nummierod < 1000000000)
        {
            $num_letrammd = cienmillon($nummierod);
        }
        return $num_letrammd;
    } 
      
    
    function ConvertirEnLetras($numero)
    {
        $numf = milmillon($numero);
        return $numf;
    }

?>

<html>
<head>
<meta charset="UTF-8"/>
<title>Reporte de Venta</title>
</head>
<body style="margin: 30px;">
<style>
    .encabezado
    {
        font-size: 14px;
        float: right;
        text-align: right;
        right: 0px;
        position: absolute;
        top: 0;
    }

    .under_line
    {
        padding-bottom: 10px;
        border-bottom: 1px solid #ccc;
    }

    .datos_cliente_entrega
    {
        margin-top: 50px;
        font-size: 18px;
    }

</style>
<div class="report-content" >
    <?php 
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $cve_cia = $_GET['cve_cia'];
    $folio = $_GET['folio'];

    $Reimpresion = false;
    if(isset($_GET['arrDetalle']))
    {
        if(!$_GET['arrDetalle'])
        {
            /*
            arrDetalle
            //destinatario
            tipoventa
            tipo_negociacion
            sfa
            cve_almacen
            -----------
            folio
            cve_cia
            cliente

            arrDetalle="Cve_articulo=46"des_articulo ="Jam Camp Tort4.8 Kg des_detallada= cve_lote="L20240924001""Num_cantidad"=50.0000"id_unimed=1,Num_Meses="",peso=240.0000,"precio_unitario=145.0000,desc_importe=sub_total=6250.0000,unidad_medida(Pz) Pieza ", proyecto="" , iva=1000.0000
            */
            $Reimpresion = true;
            $sql = "SELECT td.Cve_articulo, a.des_articulo, a.des_detallada, IFNULL(td.cve_lote, '') AS cve_lote, td.Num_cantidad, td.id_unimed, td.Num_Meses, 
                           (IFNULL(a.peso, 0)*td.Num_cantidad) AS peso, td.Precio_unitario AS precio_unitario, td.Desc_Importe AS desc_importe, 
                           TRUNCATE((((IFNULL(td.Precio_unitario, 0)*td.Num_cantidad)/(1+(a.mav_pctiva/100)))), 2) AS sub_total,  
                           CONCAT('(', u.cve_umed, ') ', u.des_umed) AS unidad_medida, td.Proyecto AS proyecto, td.IVA AS iva
                    FROM td_pedido td
                    LEFT JOIN c_articulo a ON a.cve_articulo = td.Cve_articulo
                    LEFT JOIN c_unimed u ON u.id_umed= a.unidadMedida
                    WHERE td.Fol_Folio = '{$folio}'";

            if (!($res = mysqli_query($conn, $sql))){
                echo "Falló la preparación(2): (" . mysqli_error($conn) . $sql. ") ";
            }

            $arrDetalle = array();
            //$row = mysqli_fetch_assoc($res);

            while($row = mysqli_fetch_array($res))
            {
                $arrDetalle[] = $row;
                /*
                $arrDetalle['Cve_articulo']    = $row['Cve_articulo'];
                $arrDetalle['des_articulo']    = $row['des_articulo'];
                $arrDetalle['des_detallada']   = $row['des_detallada'];
                $arrDetalle['cve_lote']        = $row['cve_lote'];
                $arrDetalle['Num_cantidad']    = $row['Num_cantidad'];
                $arrDetalle['id_unimed']       = $row['id_unimed'];
                $arrDetalle['Num_Meses']       = $row['Num_Meses'];
                $arrDetalle['peso']            = $row['peso'];
                $arrDetalle['precio_unitario'] = $row['precio_unitario'];
                $arrDetalle['desc_importe']    = $row['desc_importe'];
                $arrDetalle['sub_total']       = $row['sub_total'];
                $arrDetalle['unidad_medida']   = $row['unidad_medida'];
                $arrDetalle['proyecto']        = $row['proyecto'];
                $arrDetalle['iva']             = $row['iva'];
                */
            }

            //$arrDetalle = json_encode($row, true);
            //$_GET['arrDetalle'] = $arrDetalle;
            //var_dump($arrDetalle);
            $_GET['arrDetalle'] = $arrDetalle;

            $sql = "SELECT DISTINCT Cve_Clte FROM th_pedido WHERE Fol_Folio = '{$folio}'";

            if (!($res = mysqli_query($conn, $sql))){
                echo "Falló la preparación(2): (" . mysqli_error($conn) . $sql. ") ";
            }
            $row = mysqli_fetch_array($res);
            $_GET['cliente'] = $row['Cve_Clte'];

            $sql = "SELECT DISTINCT Id_Destinatario FROM Rel_PedidoDest WHERE Fol_Folio = '{$folio}'";

            if (!($res = mysqli_query($conn, $sql))){
                echo "Falló la preparación(2): (" . mysqli_error($conn) . $sql. ") ";
            }
            $row = mysqli_fetch_array($res);
            $_GET['destinatario'] = $row['Id_Destinatario'];


            $sql = "SELECT tipo_negociacion, tipo_venta, (SELECT clave FROM c_almacenp WHERE id = th_pedido.cve_almac) AS cve_almacen FROM th_pedido WHERE Fol_Folio = '$folio'";

            if (!($res = mysqli_query($conn, $sql))){
                echo "Falló la preparación(2): (" . mysqli_error($conn) . $sql. ") ";
            }
            $row = mysqli_fetch_array($res);
            $_GET['tipo_negociacion'] = $row['tipo_negociacion'];
            $_GET['tipoventa'] = $row['tipo_venta'];
            $_GET['cve_almacen'] = $row['cve_almacen'];


            $sqlSFA = "SELECT Valor FROM t_configuraciongeneral WHERE cve_conf = 'SFA'";
            if (!($res = mysqli_query($conn, $sqlSFA))) {
                echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
            }
            $valor_sfa = "0";
            if(mysqli_num_rows($res) == 0)
                $valor_sfa = "0";
            else
            {
                $row = mysqli_fetch_array($res);
                $valor_sfa = $row['Valor'];
            }
            $_GET['sfa'] = $valor_sfa;

        }
    }

    $cve_almacen = "";
    if(isset($_GET['cve_almacen']))
        $cve_almacen = $_GET['cve_almacen'];

    $sql = "SET NAMES 'utf8mb4';";

    $tipo_negociacion = "";
    if(isset($_GET['tipo_negociacion']))
    {
        $tipo_negociacion = $_GET['tipo_negociacion'];
        if($tipo_negociacion == 'Credito')
            $tipo_negociacion = "Crédito";
    }

    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(2): (" . mysqli_error($conn) . $sql. ") ";
    }
/*
    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset))) echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn, $charset);
*/
    $sql = "SELECT imagen, des_cia, des_direcc, distrito, des_telef, des_email, DATE_FORMAT(CURDATE(), '%d-%m-%Y') as fecha_actual FROM c_compania WHERE cve_cia = {$cve_cia}";

    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(2): (" . mysqli_error($conn) . $sql. ") ";
    }
    $row = mysqli_fetch_array($res);
    extract($row);

    $cliente = ""; $usuario = ""; $fecha = "";
    if(isset($_GET['pedido_venta']))
    {
        $sql = "SELECT Cve_clte, Cve_Usuario, DATE_FORMAT(Fec_Pedido, '%d-%m-%Y') AS Fec_Pedido FROM th_pedido WHERE Fol_folio = '{$folio}'";

        if (!($res = mysqli_query($conn, $sql))){
            echo "Falló la preparación(2): (" . mysqli_error($conn) . $sql. ") ";
        }

        $row_cliente = mysqli_fetch_array($res);
        extract($row_cliente);
        $cliente = $Cve_clte;
        $usuario = $Cve_Usuario;
        $fecha = $Fec_Pedido;
    }


    ?>
    <div class="row under_line">
        <div class="col-4 text-center encabezado_logo">

            <img src="<?php echo $imagen; ?>" height='120'>

        </div>

        <?php  
                $sql = "SELECT IFNULL(Valor, '0') AS SFA FROM t_configuraciongeneral WHERE cve_conf = 'SFA' LIMIT 1";
                if (!($res = mysqli_query($conn, $sql))){
                    echo "Falló la preparación(2): (" . mysqli_error($conn) . $sql. ") ";
                }
                $sfa = mysqli_fetch_array($res)["SFA"];


                if($sfa == 1)
                {
                    $sql = "SELECT * FROM CTiket WHERE IdEmpresa = '{$cve_almacen}';";
                    $query = mysqli_query($conn, $sql);

                    if (!($res = mysqli_query($conn, $sql))){
                        echo "Falló la preparación(2): (" . mysqli_error($conn) . $sql. ") ";
                    }

                if(mysqli_num_rows($res) > 0)
                {
                    $row = mysqli_fetch_array($res);
                    extract($row);
            ?>
                    <div class="col-8 encabezado">
                    <span><?php echo /*utf8_encode*/($Linea1); ?></span><br>
                    <span><?php echo /*utf8_encode*/($Linea2); ?></span><br>
                    <span><?php echo /*utf8_encode*/($Linea3); ?></span><br>
                    <span><?php echo /*utf8_encode*/($Linea4); ?></span><br>
                    <span><?php echo /*utf8_encode*/($Mensaje); ?></span><br>
                    </div>
            <?php 
                }
                }
                else
                {
            ?>
                    <div class="col-8 encabezado">
                    <span><?php echo /*utf8_encode*/($des_cia); ?></span><br>
                    <span><?php echo /*utf8_encode*/($des_direcc); ?></span><br>
                    <span><?php echo /*utf8_encode*/($distrito); ?></span><br>
                    <span><?php echo /*utf8_encode*/($des_telef); ?></span><br>
                    <span><?php echo /*utf8_encode*/($des_email); ?></span><br>
                    <span><?php if($fecha) echo ($fecha); ?></span><br>
                    </div>
            <?php 
                }
        ?>

    </div>

    <?php 

        if(isset($_GET['cliente']))
            $cliente = $_GET['cliente'];


        $sql = "SELECT RazonSocial, CalleNumero, Estado, Ciudad, CodigoPostal, Pais, Contacto, Telefono1, Telefono2, Telefono3, email_cliente FROM c_cliente WHERE Cve_Clte = '{$cliente}'";

        if (!($res = mysqli_query($conn, $sql))){
            echo "Falló la preparación(2): (" . mysqli_error($conn) . $sql. ") ";
        }

        if(mysqli_num_rows($res))
        {
            $row = mysqli_fetch_array($res);
            extract($row);
    ?>

    <div class="row datos_cliente_entrega" style="font-size: 10pt;">
        <div class="col-xs-6">
            <b>Datos del cliente</b><br>
            <span><?php echo /*utf8_decode*/($RazonSocial); ?></span><br>
            <span><?php echo /*utf8_decode*/($CalleNumero); ?></span><br>
            <?php /* ?><span>Transito</span><br><?php */ ?>
            <span><?php echo /*utf8_decode*/($Estado).", "./*utf8_decode*/($Ciudad); ?></span><br>
            <span><?php echo $CodigoPostal.", "./*utf8_decode*/($Pais); ?></span><br>
            <span>Contacto: <?php echo /*utf8_decode*/($Contacto); ?></span><br>
            <span>Tel. de Contacto:</span><br>
            <?php if($Telefono1){ ?>
            <span><?php echo $Telefono1; ?></span><br>
            <?php } ?>
            <?php if($Telefono2){ ?>
            <span><?php echo $Telefono2; ?></span><br>
            <?php } ?>
            <?php if($Telefono3){ ?>
            <span><?php echo $Telefono3; ?></span><br>
            <?php } ?>
            <?php if($email_cliente){ ?>
            <span><?php echo /*utf8_decode*/($email_cliente); ?></span><br>
            <?php } ?>
        </div>

        <div class="col-xs-1">&nbsp;</div>

    <?php 
        $destinatario = "";
        if(isset($_GET['destinatario']))
           $destinatario = $_GET['destinatario'];
        else
        {
            $sql = "SELECT id_destinatario FROM c_destinatarios WHERE Cve_Clte = '{$cliente}'";

            if (!($res = mysqli_query($conn, $sql))){
                echo "Falló la preparación(2): (" . mysqli_error($conn) . $sql. ") ";
            }
            if(mysqli_num_rows($res) > 0)
            {
                $row_destinatario = mysqli_fetch_array($res);
                extract($row_destinatario);
                $destinatario = $id_destinatario;
                $sql2 = $sql;
            }
        }

        $sql = "SELECT razonsocial, direccion, estado, ciudad, postal, contacto, telefono  FROM c_destinatarios WHERE id_destinatario = '{$destinatario}'";

        if (!($res = mysqli_query($conn, $sql))){
            echo "Falló la preparación(2): (" . mysqli_error($conn) . $sql. ") ";
        }

        if(mysqli_num_rows($res) > 0)
        {
            $row = mysqli_fetch_array($res);
            extract($row);
    ?>

        <div class="col-xs-5" style="font-size: 10pt;">
            <b>Dirección de Entrega</b><br>
            <span><?php echo /*utf8_decode*/($razonsocial); ?></span><br>
            <span><?php echo /*utf8_decode*/($direccion); ?></span><br>
            <?php /* ?><span>Transito</span><br><?php */ ?>
            <span><?php echo /*utf8_decode*/($estado).", "./*utf8_decode*/($ciudad); ?></span><br>
            <span><?php echo $postal; ?></span><br>
            <?php if($contacto){ ?>
            <span><?php echo /*utf8_decode*/($contacto); ?></span><br>
            <?php } ?>
            <?php if($telefono){ ?>
            <span><?php echo /*utf8_decode*/($telefono); ?></span><br>
            <?php } ?>
        </div>
    <?php 
        }
    ?>
    </div>
    <?php 
    }
    ?>

<style>
    #datos_venta, #precios_venta
    {
        margin-top: 50px;
    }

    #datos_venta .num
    {
        width: 50px;
        text-align: center;
        background-color: #cccccc !important;
    }

    #datos_venta thead tr th
    {
        background-color: #cccccc !important;
    }

    #datos_venta td
    {
        background-color: #e6e6e6 !important;
        padding: 10px;
    }

    #datos_venta td, th
    {
        border: 1px solid #fff;
    }

    #datos_venta .desc
    {
        padding: 10px;
        text-align: left;
        width: 600px;
    }

    #datos_venta .desc2
    {
        padding: 10px;
        text-align: left;
        width: 100px;
    }

    #datos_venta .precios
    {
        padding: 10px;
        text-align: center;
        width: 150px;
    }

    #datos_venta .precios, #datos_venta .num, #datos_venta .desc
    {
        font-size: 10pt;
    }

    #precios_venta .titulo_precio_venta, #precios_venta .titulo_precio_venta_last
    {
        width: 950px;
        text-align: right;
        padding: 15px;
    }

    #precios_venta .titulo_precio_venta
    {
        border-bottom: 1px solid #cccccc;
    }

    #precios_venta .valor_precio_venta, #precios_venta .valor_precio_venta_last
    {
        width: 150;
        text-align: right;
        padding: 15px;
    }

    #precios_venta .valor_precio_venta
    {
        background-color: #cccccc !important;
        border-bottom: 1px solid #fff;
    }
</style>

<br><br><br>
<b style="font-size: 18px;">Solicitud: <?php echo $_GET['folio']; ?></b>
<?php if(isset($_GET['sfa'])) if($_GET['sfa'] == 1){ ?><br><br><b style="font-size: 18px;">Tipo: <?php if($_GET['tipoventa'] == 'preventa') echo "Pre Venta";else echo "Venta"; ?></b><?php } ?>
<?php if(isset($_GET['pedido_venta'])){ ?><br><b style="font-size: 18px;">Usuario: <?php echo $usuario; ?></b><?php } ?>
<?php if($tipo_negociacion != ''){ ?><br><b style="font-size: 18px;">Tipo Negociación: <?php echo $tipo_negociacion; ?></b><?php } ?>


<table id="datos_venta">
  <thead>
    <tr>
      <?php if(!isset($_GET['pedido_venta'])){ ?><th class="desc">Proyecto</th><?php } ?>
      <th class="num"><?php if(!isset($_GET['pedido_venta'])){ ?>Clave<?php } ?></th>
      <th class="desc">Descripción</th>
      <th class="precios">Lote</th>
      <th class="precios">Cantidad</th>
      <?php if(!isset($_GET['pedido_venta'])){ ?><th class="precios">UM</th><?php } ?>
      <?php if(!isset($_GET['pedido_venta'])){ ?><th class="precios">P.U.</th><?php } ?>
      <?php if(!isset($_GET['pedido_venta'])){ ?><th class="precios">Desc</th><?php } ?>
      <?php if(!isset($_GET['pedido_venta'])){ ?><th class="precios">IVA</th><?php } ?>
      <?php if(!isset($_GET['pedido_venta'])){ ?><th class="precios">Total</th><?php } ?>
    </tr>
  </thead>
  <tbody>

    <?php 
    if(isset($_GET['pedido_venta']))
    {
        $sql = "SELECT DISTINCT td.Cve_articulo, a.des_articulo, a.des_detallada, IFNULL(tds.LOTE, td.cve_lote) AS cve_lote, td.Num_cantidad, td.Precio_unitario, td.Desc_Importe, td.IVA
                FROM td_pedido td 
                LEFT JOIN td_surtidopiezas tds ON tds.fol_folio= td.Fol_folio AND td.Cve_articulo = tds.Cve_articulo
                LEFT JOIN c_articulo a ON a.cve_articulo = td.Cve_articulo
                WHERE td.fol_folio = '{$folio}'";

        if (!($res = mysqli_query($conn, $sql))){
            echo "Falló la preparación(2): (" . mysqli_error($conn) . $sql. ") ";
        }
        if(mysqli_num_rows($res)>0)
        {
             $i = 0; $subtotal = 0; $descuento = 0; $total = 0; $iva = 0;
            while($row = mysqli_fetch_array($res))
            {
                extract($row);
                $i++;
            ?>
            <tr>
              <td class="num"><?php echo $i; ?></td>
              <td class="desc">
                <b><?php echo $Cve_articulo; ?></b><br>
                <b><?php echo utf8_decode($des_articulo); ?></b><br>
                <span><?php echo nl2br(utf8_decode(utf8_encode($des_detallada))); ?></span><br>
              </td>
              <td class="precios"><?php echo $cve_lote; ?></td>
              <td class="precios"><?php echo $Num_cantidad; ?></td>
              <?php if(!isset($_GET['pedido_venta'])){ ?>
              <td class="precios"><b>$ <?php echo ($Precio_unitario*$Num_cantidad); ?></b></td>
              <?php } ?>
            </tr>
            <?php 
            }

        }
    }
    else
    {
        $arrDetalle = $_GET['arrDetalle'];
         //var_dump($arrDetalle);
        //$json = "";
        //$json = json_decode($arrDetalle, true);
        if(!$Reimpresion) $json = json_decode($arrDetalle, true);
        else $json = $arrDetalle;
        // var_dump($json);
         $i = 0; $subtotal = 0; $descuento = 0; $total = 0; $iva = 0;
        foreach ($json as $art) 
        {
            $i++;
        ?>

        <tr>
          <td class="desc2"><b><?php echo $art['proyecto']; ?></b></td>
          <td class="desc"><b><?php echo $art['Cve_articulo']; ?></b></td>
          <td class="desc">
            <b><?php echo ($art['des_articulo']); ?></b><br>
            <span><?php echo nl2br((($art['des_detallada']))); ?></span><br>
          </td>
          <td class="precios"><?php echo $art['cve_lote']; ?></td>
          <td class="precios"><?php echo $art['Num_cantidad']; ?></td>
          <td class="precios"><?php echo $art['unidad_medida']; ?></td>
          <td class="precios"><?php echo number_format($art['precio_unitario'],2); ?></td>
          <td class="precios"><?php echo number_format($art['desc_importe'],2); ?></td>
          <td class="precios"><?php echo $art['iva']; ?></td>
          <td class="precios"><b><?php echo number_format(($art['precio_unitario']*$art['Num_cantidad'])-$art['desc_importe'],2); ?></b></td>
        </tr>
        <?php 
            $subtotal += $art['sub_total'];
            $descuento += $art['desc_importe'];
            $iva += $art['iva'];
        }
    }
    ?>

  </tbody>
</table>

<?php 
if(!isset($_GET['pedido_venta']))
{ 
?>
<table id="precios_venta">
    <tbody>
        <tr>
            <td class="titulo_precio_venta">Sub Total</td>
            <td class="valor_precio_venta">$ <?php echo number_format(($subtotal+$descuento), 2); ?></td>
        </tr>
        <tr>
            <td class="titulo_precio_venta">Descuentos</td>
            <td class="valor_precio_venta">$ <?php echo number_format($descuento, 2); ?></td>
        </tr>
        <tr>
            <td class="titulo_precio_venta">Total</td>
            <td class="valor_precio_venta">$ <?php echo number_format($subtotal, 2); ?></td>
        </tr>
        <tr>
            <td class="titulo_precio_venta">Impuesto 16%</td>
            <td class="valor_precio_venta">$ <?php echo number_format($iva, 2); ?></td>
        </tr>
        <tr>
            <td class="titulo_precio_venta_last"><b>Total</b> </td>
            <td class="valor_precio_venta_last">$ <?php echo number_format(($subtotal+$iva), 2); ?></td>
        </tr>
    </tbody>
</table>
<?php 
}
?>

<div style="padding: 10px 100px;">
<?php 
/*
?>
<table class="table">
  <thead>
    <tr>
      <th scope="col">Clave</th>
      <th scope="col">Descripción</th>
      <th scope="col">Lote</th>
      <th scope="col">Caducidad</th>
      <th scope="col" width="150" align="center">BL</th>
      <th scope="col">Cantidad Solicitada</th>
      <th scope="col">Cantidad Surtida</th>
      <th scope="col">Usuario</th>
    </tr>
  </thead>
  <tbody>
  <?php 

    $folio = $_GET['folio'];

    $sql = "
        SELECT DISTINCT td.Cve_articulo AS Clave, a.des_articulo AS Descripcion, IFNULL(ts.LOTE, '') AS Lote, 
                        IF(a.Caduca = 'S', DATE_FORMAT(L.Caducidad, '%d-%m-%Y'), '') AS Caducidad,
                        u.CodigoCSD AS BL, TRUNCATE(td.Num_cantidad, 3) AS Cantidad_Solicitada, TRUNCATE(ts.Cantidad, 3) AS Cantidad_Surtida, 
                        c.nombre_completo as Usuario
        FROM th_pedido th 
        LEFT JOIN td_pedido td ON td.Fol_folio = th.Fol_folio
        LEFT JOIN td_surtidopiezas ts ON ts.fol_folio = th.Fol_folio
        LEFT JOIN th_subpedido ths ON ths.fol_folio = ts.fol_folio AND ths.Sufijo = ts.Sufijo
        LEFT JOIN c_usuario c ON c.cve_usuario = ths.cve_usuario
        LEFT JOIN c_articulo a ON a.cve_articulo = ts.Cve_articulo
        LEFT JOIN c_lotes L ON L.cve_articulo = ts.Cve_articulo AND IFNULL(L.Lote, '') = IFNULL(ts.LOTE, '')
        LEFT JOIN t_cardex tc ON tc.cve_articulo = ts.Cve_articulo AND IFNULL(ts.LOTE, '') = IFNULL(tc.cve_lote, '') AND tc.destino LIKE '%{$folio}%'
        LEFT JOIN c_ubicacion u ON u.idy_ubica = tc.origen
        WHERE th.Fol_folio = '{$folio}' AND td.Cve_articulo = ts.Cve_articulo AND L.Lote = ts.LOTE
    ";
    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";
    }

    while ($row = mysqli_fetch_array($res)) {

        extract($row);

  ?>
    <tr>
      <th scope="row"><?php echo $Clave; ?></th>
      <td><?php echo $Descripcion; ?></td>
      <td><?php echo $Lote; ?></td>
      <td><?php echo $Caducidad; ?></td>
      <td width="150" align="center"><?php echo $BL; ?></td>
      <td align="right"><?php echo $Cantidad_Solicitada; ?></td>
      <td align="right"><?php echo $Cantidad_Surtida; ?></td>
      <td><?php echo $Usuario; ?></td>
    </tr>
    <?php 
    }
    ?>
  </tbody>
</table>
<?php 
*/
?>
</div>
<?php 
if(isset($_GET['tipo_negociacion']))
    if($_GET['tipo_negociacion'] =='Credito')
    {
?>
<div class="page-footer" style="font-size: 14pt; line-height: 1.5; height: 100px;">
    Debo y pagaré incondicionalmente a la orden de <?php echo strtoupper($des_cia); ?> la cantidad de $<?php echo number_format(($subtotal+$iva), 2); ?> (<?php echo strtoupper(ConvertirEnLetras($subtotal+$iva)); $decimales = explode(".", round(($subtotal+$iva), 2)); ?> con <?php if(count($decimales) == 2) echo $decimales[1]; else echo "00"; ?>/100) en esta ciudad en que el suscriptor tenga su domicilio a elección del beneficiario en la fecha de <?php echo $fecha_actual; ?>, por el valor recibido en mercancía y/o producto a mi entera conformidad para las operaciones de venta y reparto
    <br><br>
    <div style="text-align:center;">
    <br><br><br>_____________________________________<br>
    <?php echo $RazonSocial; ?>
</div>

</div>
<?php 
    }
?>
</div>
</body>
</html>

