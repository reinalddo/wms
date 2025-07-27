<?php 

/*
    ** Created by kemdmq on 03/08/2017 **
*/
namespace ReportePDF;
/*Libreria TCPDF*/
require dirname(dirname(dirname(__DIR__))).'/TCPDF/tcpdf.php';
/*Clase PDF con la plantilla personalizada*/
class Etiquetas {

    public function XgenerarCodigoCajaX($data){
        $pdf = new \TCPDF('P', 'mm', array(75, 50), true, 'UTF-8', false);
        $pdf->SetCreator('wms');
        $pdf->SetAuthor('wms');
        $pdf->SetTitle('Etiquetas de artículos');
        $pdf->SetSubject('Código de Barra para artículos');
        $pdf->SetKeywords('producto, articulo');
        $pdf->SetDisplayMode('real', 'default');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $style = array(
            'position'     => '',
            'align'        => 'C',
            'stretch'      => false,
            'fitwidth'     => false,
            'cellfitalign' => '',
            'border'       => false,
            'hpadding'     => 'auto',
            'vpadding'     => 'auto',
            'fgcolor'      => array(0, 0, 0),
            'bgcolor'      => false,
            'text'         => true,
            'font'         => 'helvetica',
            'fontsize'     => 8,
            'stretchtext'  => 4
        );

        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

        if(empty($data['lote'])){
            $data['lote'] = '000000';
        }
        if(empty($data['ordenp'])){
            $data['ordenp'] = '000000';
        }
        if(empty($data['barras_caja'])){
            $data['barras_caja'] = '000000000000000';
        }
        
        $filename = $data['articulo']." (".$data['clave'].").pdf";
        //$codigobarra = $data['clave'].$data['lote'].$data['ordenp'].$data['cantidad'];
        $codigobarra = $data['lote'];
        $width = '40';
        $height = '12';

        for($i = 1; $i <= $data['etiquetas']; $i++){

        ob_start(); ?>
            <table >
                <tr>
                    <td>COD PROD:</td>
                    <td><?php echo $data['clave'];?></td>
                </tr>
                <tr>
                    <td>LOTE: </td>
                    <td><?php echo $data['lote'];?></td>
                </tr>
                <tr>
                    <td>CADUCIDAD: </td>
                    <td><?php echo $data['caducidad'];?></td>
                </tr>
                <tr>
                    <td>OP:</td>
                    <td><?php echo $data['ordenp'];?></td>
                </tr>
                <?php 
                if($data['n_pedido'])
                {
                ?>
                <tr>
                    <td>No. PEDIDO:</td>
                    <td><?php echo $data['n_pedido'];?></td>
                </tr>
                <?php 
                }
                ?>
                <tr>
                    <td>PESO|UNIDADES:</td>
                    <td><?php echo $data['cantidad'];?></td>
                </tr>
                <?php 
                if($data['check_pallet'])
                {
                    $id_contenedor = $data['pallet'];
                    $Folio = $data['ordenp'];
                    $cantidad = $data['cantidad'];
                    $cve_articulo = $data['clave'];
                    $lote = $data['lote'];

                    $sql = "SELECT (SELECT MAX(ch.IDContenedor)+1 FROM c_charolas ch) AS id_contenedor, cve_almac, descripcion, tipo, alto, ancho, fondo, peso, pesomax, capavol FROM c_charolas WHERE TipoGen = 1 AND IDContenedor = {$id_contenedor} AND tipo = 'Pallet' ORDER BY IDContenedor ASC LIMIT 1";

                    $rs = mysqli_query($conn, $sql);
                    $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);

                    //if(!$resul['id_contenedor']) break;

                    $id_contenedor = $resul['id_contenedor'];
                    $descripcion   = $resul['descripcion'];
                    $tipo          = $resul['tipo'];
                    $alto          = $resul['alto'];
                    $ancho         = $resul['ancho'];
                    $fondo         = $resul['fondo'];
                    $peso          = $resul['peso'];
                    $pesomax       = $resul['pesomax'];
                    $capavol       = $resul['capavol'];
                    $id_almacen    = $resul['cve_almac'];

                    $label_lp = "LP".str_pad($id_contenedor.$Folio, 9, "0", STR_PAD_LEFT);

                    $sql = "INSERT INTO c_charolas(cve_almac, clave_contenedor, descripcion, Permanente, tipo, alto, ancho, fondo, peso, pesomax, capavol, CveLP, TipoGen) 
                            VALUES ({$id_almacen}, '{$label_lp}', '{$descripcion}', 0, 'Pallet', {$alto}, {$ancho}, {$fondo}, {$peso}, {$pesomax}, {$capavol}, '{$label_lp}', 0)";
                    $rs = mysqli_query($conn, $sql);


                    $sql = "UPDATE ts_existenciapiezas SET Existencia = Existencia - {$cantidad} WHERE cve_articulo = '{$cve_articulo}' AND cve_lote = '{$lote}'";
                    $rs = mysqli_query($conn, $sql);   

                    $sql = "SELECT idy_ubica, ID_Proveedor FROM ts_existenciapiezas WHERE cve_articulo = '{$cve_articulo}' AND cve_lote = '{$lote}'";
                    $rs = mysqli_query($conn, $sql);
                    $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                    $idy_ubica    = $resul['idy_ubica'];
                    $ID_Proveedor = $resul['ID_Proveedor'];

                    $sql = "INSERT INTO ts_existenciatarima(cve_almac, idy_ubica, cve_articulo, lote, Fol_Folio, ntarima, capcidad, existencia, Activo, ID_Proveedor, Cuarentena) VALUES({$id_almacen}, {$idy_ubica}, '{$cve_articulo}', '{$lote}', 0, {$id_contenedor}, 0, {$cantidad}, 1, {$ID_Proveedor}, 0) ON DUPLICATE KEY UPDATE existencia = existencia + {$cantidad}";
                    $rs = mysqli_query($conn, $sql);

                ?>
                <tr>
                    <td>LP:</td>
                    <td><?php echo $label_lp;?></td>
                </tr>
                <?php 
                }
                ?>
            </table>
        <?php
        $desProducto = ob_get_clean();


            $pdf->AddPage();
            $pdf->setMargins(0, 0, 0, 0);
            $pdf->SetAutoPageBreak(false);
            $pdf->SetXY(0, 5);
            $pdf->SetFont('helvetica', 'b', '10px', '', 'default', true);
            $pdf->MultiCell(0, 0, $data['articulo'], 0, 'C');
            $pdf->SetXY(12, 15);
            $pdf->SetFont('helvetica', '', '6px', '', 'default', true);
            $pdf->WriteHTML($desProducto, true, false, true, '');
            $pdf->StartTransform();
            //$pdf->Rotate(-90, 15, 20);
            $pdf->write1DBarcode($data['clave'], 'C128', 5, 35, $width, $height, 0.2, $style, 'N');
            $pdf->write1DBarcode($codigobarra, 'C128', 5, 46, $width, $height, 0.2, $style, 'N'); 
            $pdf->write1DBarcode($data['caducidad'], 'C128', 5, 58, $width, $height, 0.2, $style, 'N'); 
            $pdf->StopTransform();
            $pdf->lastPage();
        }        
        $pdf->Output($filename, 'I');
    }


    public function generarCodigoCaja($data){
        $pdf = new \TCPDF('P', 'mm', array(75, 50), true, 'UTF-8', false);
        $pdf->SetCreator('wms');
        $pdf->SetAuthor('wms');
        $pdf->SetTitle('ProdTerminado');
        $pdf->SetSubject('Código de Barra para artículos');
        $pdf->SetKeywords('producto, articulo');
        $pdf->SetDisplayMode('real', 'default');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $style = array(
            'position'     => '',
            'align'        => 'C',
            'stretch'      => false,
            'fitwidth'     => false,
            'cellfitalign' => '',
            'border'       => false,
            'hpadding'     => 'auto',
            'vpadding'     => 'auto',
            'fgcolor'      => array(0, 0, 0),
            'bgcolor'      => false,
            'text'         => true,
            'font'         => 'helvetica',
            'fontsize'     => 8,
            'stretchtext'  => 4
        );

        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

        if(empty($data['lote'])){
            //$data['lote'] = '000000';
            $data['lote'] = '';
        }
        if(empty($data['ordenp'])){
            $data['ordenp'] = '000000';
        }
        if(empty($data['barras_caja'])){
            $data['barras_caja'] = '000000000000000';
        }
        
        $filename = $data['articulo']." (".$data['clave'].").pdf";
        //$codigobarra = $data['clave'].$data['lote'].$data['ordenp'].$data['cantidad'];
        $codigobarra = $data['lote'];
        $width = '40';
        $height = '12';

        $ordenp = $data['ordenp'];
        $articulo = $data['articulo'];
        $lote = $data['lote'];
        $caducidad = $data['caducidad'];
        $cantidad = $data['cantidad'];
        $clave_articulo = $data['clave'];
        $n_etiquetas = $data['etiquetas'];

        if($data['consultar'] == "1")
        {

            $sql = "SELECT COUNT(*) as total_cajas FROM th_cajamixta WHERE fol_folio = '{$ordenp}'";
            $rs = mysqli_query($conn, $sql);
            $total_cajas = mysqli_fetch_array($rs, MYSQLI_ASSOC)["total_cajas"];
/*
            $sql = "SELECT t.cve_articulo, t.lote, t.existencia,ch.CveLP
                    FROM ts_existenciatarima t
                    LEFT JOIN c_charolas ch ON ch.IDContenedor = t.ntarima
                    WHERE cve_articulo = '$clave_articulo' AND lote = '$lote';";
*/
            $sql = "SELECT t.cve_articulo, t.lote, 
                           IF(IFNULL(t.existencia, 0) = 0, tt.cantidad, t.existencia) AS existencia,
                           ch.CveLP, IFNULL(thc.NCaja, 0) AS NCaja
                    FROM ts_existenciatarima t
                    LEFT JOIN c_charolas ch ON ch.IDContenedor = t.ntarima
                    INNER JOIN t_tarima tt ON tt.ntarima = t.ntarima AND tt.fol_folio = '{$ordenp}'
                    LEFT JOIN th_cajamixta thc ON thc.Cve_CajaMix = tt.Caja_ref
                    WHERE t.cve_articulo = '{$clave_articulo}' AND IFNULL(t.lote, '') = '{$lote}'
                    ORDER BY thc.NCaja";
            $rs = mysqli_query($conn, $sql);
            while($resul = mysqli_fetch_array($rs, MYSQLI_ASSOC))
            {//while
                    ob_start(); ?>
                        <table >
                            <?php 
                            $label_lp = $resul['CveLP'];
                            $NCaja = $resul['NCaja'];
                            $cantidad = round($resul['existencia'], 2);
                            ?>
                        </table>
                    <?php
                    $desProducto = ob_get_clean();


                        $pdf->AddPage();
                        $pdf->setMargins(0, 0, 0, 0);
                        $pdf->SetAutoPageBreak(false);
                        //$pdf->SetXY(0, 5);
                        //$pdf->SetFont('helvetica', '', '6px', '', 'default', true);
                        //$pdf->MultiCell(0, 0, $data['articulo'], 0, 'C');
                        //$pdf->MultiCell(0, 0, "License Plate Control", 0, 'C');

                        $pdf->SetXY(10, 5);
                        $pdf->SetFont('helvetica', '', '6px', '', 'default', true);
                        $pdf->SetFillColor(0,0,0);
                        $pdf->SetTextColor(255,255,255);
                        //$pdf->MultiCell(0, 0, "License Plate Control", 0, 'C');
                        $pdf->Cell(30, 3, "License Plate Control", 1, 1, 'C', 1, 0);

                        $pdf->SetTextColor(0,0,0);

                        $pdf->SetXY(5, 9);
                        $pdf->SetFont('helvetica', '', '6px', '', 'default', true);
                        $pdf->MultiCell(0, 0, "OP: ".$data['ordenp'], 0, 'L');

                        $pdf->SetXY(3, 25);
                        $pdf->SetFont('helvetica', '', '7px', '', 'default', true);
                        $pdf->MultiCell(0, 0, $articulo, 0, 'C');
                        //$pdf->Cell(30, 3, $data['articulo'], 1, 1, 'C', 1, 0);

                        $pdf->SetXY(3, 45);
                        $pdf->SetFont('helvetica', '', '6px', '', 'default', true);
                        $pdf->MultiCell(0, 0, "Lote: ".$lote, 0, 'L');

                        $pdf->SetXY(3, 48);
                        $pdf->SetFont('helvetica', '', '6px', '', 'default', true);
                        $pdf->MultiCell(0, 0, "Caducidad: ".$caducidad, 0, 'L');

                        $pdf->SetXY(3, 51);
                        $pdf->SetFont('helvetica', '', '6px', '', 'default', true);
                        $pdf->MultiCell(0, 0, "PESO|UNIDADES: ".$cantidad, 0, 'L');

                        if($NCaja > 0)
                        {
                            $style_bc = array(
                                'border' => 2,
                                'vpadding' => 'auto',
                                'hpadding' => 'auto',
                                'fgcolor' => array(0,0,0),
                                'bgcolor' => false, //array(255,255,255)
                                'module_width' => 1, // width of a single module in points
                                'module_height' => 1 // height of a single module in points
                            );

                            $pdf->write2DBarcode($label_lp.'$'.$clave_articulo.'$'.$cantidad.'$'.$NCaja, 'QRCODE,H', 33, 43, 8, 8, $style_bc, 'N');
                            //$pdf->Text(10, 40, $ordenp.'$'.$clave_articulo.'$'.$cantidad.'$'.$NCaja);

                            $pdf->SetXY(3, 51);
                            $pdf->SetFont('helvetica', '', '6px', '', 'default', true);
                            $pdf->MultiCell(40, 0, "Caja: ".$NCaja."/".$total_cajas, 0, 'R');
                        }

                        $pdf->SetXY(10, 70);
                        $pdf->SetFont('helvetica', '', '6px', '', 'default', true);
                        $pdf->SetFillColor(0,0,0);
                        $pdf->SetTextColor(255,255,255);
                        //$pdf->MultiCell(0, 0, "License Plate Control", 0, 'C');
                        $pdf->Cell(30, 3, "AssistPro WMS", 1, 1, 'C', 1, 0);

                        $pdf->SetXY(12, 15);
                        $pdf->SetFont('helvetica', '', '6px', '', 'default', true);
                        $pdf->WriteHTML($desProducto, true, false, true, '');
                        //$pdf->WriteHTML("License Plate Control", true, false, true, '');
                        

                        $pdf->StartTransform();
                        //$pdf->Rotate(-90, 15, 20);
                        $pdf->write1DBarcode($clave_articulo, 'C128', 5, 30, $width, $height, 0.2, $style, 'N');
                        $pdf->write1DBarcode($codigobarra, 'C128', 5, 55, $width, $height, 0.2, $style, 'N'); 
                        //$pdf->write1DBarcode($data['caducidad'], 'C128', 5, 58, $width, $height, 0.2, $style, 'N'); 
                        if($label_lp)
                            $pdf->write1DBarcode($label_lp, 'C128', 5, 13, $width, $height, 0.2, $style, 'N'); 

                        $pdf->StopTransform();
                        $pdf->lastPage();
                    

            }//while
        }
        else 
        {
            for($i = 1; $i <= $n_etiquetas; $i++)
            {
                ob_start(); 

                if($data['consultar'] != "2") //app/template/page/reportes/productos.php
                {
                ?>
                    <table>
                        <?php 
                        $label_lp = "";
                        if($data['check_pallet'])
                        {
                            $id_contenedor = $data['pallet'];
                            $Folio = $data['ordenp'];
                            $cantidad = $data['cantidad'];
                            $cve_articulo = $data['clave'];
                            $clave_articulo = $cve_articulo;
                            $lote = $data['lote'];

                            $sql = "SELECT (SELECT MAX(ch.IDContenedor)+1 FROM c_charolas ch) AS id_contenedor, cve_almac, descripcion, tipo, alto, ancho, fondo, peso, pesomax, capavol FROM c_charolas WHERE TipoGen = 1 AND IDContenedor = {$id_contenedor}  ORDER BY IDContenedor ASC LIMIT 1";
                                //#AND tipo = 'Pallet'
                            $rs = mysqli_query($conn, $sql);
                            $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);

                            //if(!$resul['id_contenedor']) break;

                            $id_contenedor = $resul['id_contenedor'];
                            $descripcion   = $resul['descripcion'];
                            $tipo          = $resul['tipo'];
                            $alto          = $resul['alto'];
                            $ancho         = $resul['ancho'];
                            $fondo         = $resul['fondo'];
                            $peso          = $resul['peso'];
                            $pesomax       = $resul['pesomax'];
                            $capavol       = $resul['capavol'];
                            $id_almacen    = $resul['cve_almac'];

                            $label_lp = "LP".str_pad($id_contenedor.$Folio, 9, "0", STR_PAD_LEFT);

                            $sql = "INSERT INTO c_charolas(cve_almac, clave_contenedor, descripcion, Permanente, tipo, alto, ancho, fondo, peso, pesomax, capavol, CveLP, TipoGen) 
                                    VALUES ({$id_almacen}, '{$label_lp}', '{$descripcion}', 0, 'Pallet', {$alto}, {$ancho}, {$fondo}, {$peso}, {$pesomax}, {$capavol}, '{$label_lp}', 0)";
                            $rs = mysqli_query($conn, $sql);


                            $sql = "UPDATE ts_existenciapiezas SET Existencia = Existencia - {$cantidad} WHERE cve_articulo = '{$cve_articulo}' AND cve_lote = '{$lote}' AND cve_almac = '{$id_almacen}'";
                            $rs = mysqli_query($conn, $sql);   

/*
                            $sql = "SELECT idy_ubica, ID_Proveedor FROM ts_existenciapiezas WHERE cve_articulo = '{$cve_articulo}' AND cve_lote = '{$lote}' AND cve_almac = '{$id_almacen}'";
                            $rs = mysqli_query($conn, $sql);
                            $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                            $idy_ubica    = $resul['idy_ubica'];
                            $ID_Proveedor = $resul['ID_Proveedor'];
*/
                            //$sql = "SELECT idy_ubica FROM c_ubicacion WHERE AreaProduccion = 'S' AND Activo = 1 AND cve_almac IN (SELECT DISTINCT cve_almac FROM c_almacen WHERE cve_almacenp = '{$id_almacen}')";
                            $sql = "SELECT IFNULL(idy_ubica_dest, idy_ubica) as idy_ubica FROM t_ordenprod WHERE Folio_Pro = '$Folio'";
                            $rs = mysqli_query($conn, $sql);
                            $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                            $idy_ubica    = $resul['idy_ubica'];

                            $sql = "SELECT ID_Proveedor FROM t_ordenprod WHERE Folio_Pro = '{$Folio}'";
                            $rs = mysqli_query($conn, $sql);
                            $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                            $ID_Proveedor = $resul['ID_Proveedor'];
                            
                            $sql = "INSERT INTO ts_existenciatarima(cve_almac, idy_ubica, cve_articulo, lote, Fol_Folio, ntarima, capcidad, existencia, Activo, ID_Proveedor, Cuarentena) VALUES({$id_almacen}, {$idy_ubica}, '{$cve_articulo}', '{$lote}', 0, {$id_contenedor}, 0, {$cantidad}, 1, {$ID_Proveedor}, 0) ON DUPLICATE KEY UPDATE existencia = existencia + {$cantidad}";
                            $rs = mysqli_query($conn, $sql);

                            $sql = "SELECT (MAX(Cve_CajaMix)+1) as Cve_CajaMix FROM th_cajamixta";
                            $rs = mysqli_query($conn, $sql);
                            $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                            $Cve_CajaMix = $resul['Cve_CajaMix'];

                            $sql = "INSERT INTO th_cajamixta(Cve_CajaMix, fol_folio, Sufijo, NCaja, abierta, embarcada, Activo) VALUES({$Cve_CajaMix}, '{$Folio}', 1, $i, 'N', 'N', 1)";
                            $rs = mysqli_query($conn, $sql);

                            $sql = "INSERT INTO td_cajamixta(Cve_CajaMix, Cve_articulo, Cantidad, Cve_Lote, Num_Empacados, Ban_Embarcado, Activo) VALUES({$Cve_CajaMix}, '{$cve_articulo}', {$cantidad}, '{$lote}', {$cantidad}, 'S', 1)";
                            $rs = mysqli_query($conn, $sql);

                            $sql = "INSERT INTO t_tarima(ntarima, Fol_Folio, Sufijo, cve_articulo, lote, cantidad, Num_Empacados, Caja_ref, Ban_Embarcado, Abierta, Activo) VALUES({$id_contenedor}, '{$Folio}', 1, '{$cve_articulo}', '{$lote}', {$cantidad}, {$cantidad}, {$Cve_CajaMix}, 'S', 0, 1)";
                            $rs = mysqli_query($conn, $sql);
                        }
                        ?>
                    </table>
                <?php
                    }
                $desProducto = ob_get_clean();


                    $pdf->AddPage();
                    $pdf->setMargins(0, 0, 0, 0);
                    $pdf->SetAutoPageBreak(false);
                    //$pdf->SetXY(0, 5);
                    //$pdf->SetFont('helvetica', '', '6px', '', 'default', true);
                    //$pdf->MultiCell(0, 0, $data['articulo'], 0, 'C');
                    //$pdf->MultiCell(0, 0, "License Plate Control", 0, 'C');

                    $pdf->SetXY(10, 5);
                    $pdf->SetFont('helvetica', '', '6px', '', 'default', true);
                    $pdf->SetFillColor(0,0,0);
                    $pdf->SetTextColor(255,255,255);
                    //$pdf->MultiCell(0, 0, "License Plate Control", 0, 'C');
                    $pdf->Cell(30, 3, "License Plate Control", 1, 1, 'C', 1, 0);

                    $pdf->SetTextColor(0,0,0);

                    $pdf->SetXY(5, 9);
                    $pdf->SetFont('helvetica', '', '6px', '', 'default', true);
                    $pdf->MultiCell(0, 0, "OP: ".$data['ordenp'], 0, 'L');

                    $pdf->SetXY(3, 25);
                    $pdf->SetFont('helvetica', '', '7px', '', 'default', true);
                    $pdf->MultiCell(0, 0, $data['articulo'], 0, 'C');
                    //$pdf->Cell(30, 3, $data['articulo'], 1, 1, 'C', 1, 0);

                    $pdf->SetXY(3, 45);
                    $pdf->SetFont('helvetica', '', '6px', '', 'default', true);
                    $pdf->MultiCell(0, 0, "Lote: ".$data['lote'], 0, 'L');

                    if($data['consultar'] == "2") //app/template/page/reportes/productos.php
                    {

                        $sql = "SELECT IF(a.control_lotes = 'S' AND a.Caduca = 'S' AND IFNULL(l.Lote, '') != '', DATE_FORMAT(l.Caducidad, '%d-%m-%Y'), '') as Caducidad 
                                FROM c_lotes l 
                                LEFT JOIN c_articulo a ON a.cve_articulo = l.cve_articulo
                                WHERE l.cve_articulo = '$clave_articulo' AND l.Lote = '".$data['lote']."'";
                        $rs = mysqli_query($conn, $sql);
                        $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                        $Caducidad    = $resul['Caducidad'];

                        $pdf->SetXY(3, 48);
                        $pdf->SetFont('helvetica', '', '6px', '', 'default', true);
                        $pdf->MultiCell(0, 0, "Caducidad: ".$Caducidad, 0, 'L');
                    }
                    else
                    {
                        $pdf->SetXY(3, 48);
                        $pdf->SetFont('helvetica', '', '6px', '', 'default', true);
                        $pdf->MultiCell(0, 0, "Caducidad: ".$data['caducidad'], 0, 'L');
                    }

                    $style_bc = array(
                        'border' => 2,
                        'vpadding' => 'auto',
                        'hpadding' => 'auto',
                        'fgcolor' => array(0,0,0),
                        'bgcolor' => false, //array(255,255,255)
                        'module_width' => 1, // width of a single module in points
                        'module_height' => 1 // height of a single module in points
                    );

                    $pdf->write2DBarcode($label_lp.'$'.$clave_articulo.'$'.$data['cantidad'].'$'.$i, 'QRCODE,H', 33, 43, 8, 8, $style_bc, 'N');

                    $pdf->SetXY(3, 51);
                    $pdf->SetFont('helvetica', '', '6px', '', 'default', true);
                    $pdf->MultiCell(0, 0, "PESO|UNIDADES: ".round($data['cantidad'],2), 0, 'L');

                    $pdf->SetXY(3, 51);
                    $pdf->SetFont('helvetica', '', '6px', '', 'default', true);
                    $pdf->MultiCell(40, 0, "Caja: ".$i."/".$n_etiquetas, 0, 'R');


                    $pdf->SetXY(10, 70);
                    $pdf->SetFont('helvetica', '', '6px', '', 'default', true);
                    $pdf->SetFillColor(0,0,0);
                    $pdf->SetTextColor(255,255,255);
                    //$pdf->MultiCell(0, 0, "License Plate Control", 0, 'C');
                    $pdf->Cell(30, 3, "AssistPro WMS", 1, 1, 'C', 1, 0);

                    $pdf->SetXY(12, 15);
                    $pdf->SetFont('helvetica', '', '6px', '', 'default', true);
                    $pdf->WriteHTML($desProducto, true, false, true, '');
                    //$pdf->WriteHTML("License Plate Control", true, false, true, '');
                    

                    $pdf->StartTransform();
                    //$pdf->Rotate(-90, 15, 20);
                    $pdf->write1DBarcode($clave_articulo, 'C128', 5, 30, $width, $height, 0.2, $style, 'N');
                    $pdf->write1DBarcode($codigobarra, 'C128', 5, 55, $width, $height, 0.2, $style, 'N'); 
                    //$pdf->write1DBarcode($data['caducidad'], 'C128', 5, 58, $width, $height, 0.2, $style, 'N'); 
                    if($label_lp)
                        $pdf->write1DBarcode($label_lp, 'C128', 5, 13, $width, $height, 0.2, $style, 'N'); 

                    $pdf->StopTransform();
                    $pdf->lastPage();
                }
            }        
        $pdf->Output($filename, 'I');
    }


    public function generarCodigoArticulo($data){
        $filename = $data['articulo']." (".$data['clave'].").pdf";
        $style = array(
            'position'     => '',
            'align'        => 'C',
            'stretch'      => false,
            'fitwidth'     => false,
            'cellfitalign' => '',
            'border'       => false,
            'hpadding'     => 'auto',
            'vpadding'     => 'auto',
            'fgcolor'      => array(0, 0, 0),
            'bgcolor'      => false,
            'text'         => true,
            'font'         => 'helvetica',
            'fontsize'     => 6,
            'stretchtext'  => 4
        );
        $pdf = new \TCPDF('L', 'mm', array(32, 22), true, 'UTF-8', false);
        $pdf->SetCreator('wms');
        $pdf->SetAuthor('wms');
        $pdf->SetTitle('Etiquetas de artículos');
        $pdf->SetSubject('Código de Barra para artículos');
        $pdf->SetKeywords('producto, articulo');
        $pdf->SetDisplayMode('real', 'default');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        for($i = 1; $i <= $data['etiquetas']; $i++){
            $pdf->AddPage();
            $pdf->setMargins(0, 0, 0, 0);
            $pdf->SetAutoPageBreak(false);
            $pdf->SetXY(0, 5);
            $pdf->SetFont('helvetica', 'b', '6px', '', 'default', true);
            $pdf->MultiCell(0, 0, $data['articulo'], 0, 'C');
            $pdf->write1DBarcode($data['clave'], 'EAN13', 0, 10, $width, $height, 0.2, $style, 'N');
            $pdf->lastPage();
        }
        $pdf->Output($filename, 'I');
    }
    public function generarCodigoPallet(){}
}

?>