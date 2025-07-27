<?php 

/*
    ** Created by kemdmq on 24/11/2017 **
*/
namespace ReportePDF;
/*Libreria TCPDF*/

$tools = new \Tools\Tools();

require dirname(dirname(dirname(__DIR__))).'/TCPDF/tcpdf.php';
require dirname(dirname(dirname(__DIR__))).'/FPDF/fpdf.php';
require dirname(dirname(dirname(__DIR__))).'/UPCA/ean13.php';

include_once $_SERVER['DOCUMENT_ROOT']."/app/host.php";
include_once  $_SERVER['DOCUMENT_ROOT']."/config.php";

/*Clase PDF con la plantilla personalizada*/
class EntradasPDF {
    private $articulos;
    private $empresa;
    private $destinatario;
    private $folio;
    private $surtidor;

    public function __construct($folio)
    {
        // $this->setData($folio);

        $this->tools = new \Tools\Tools();
    }
  
    
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
                $numd = $numd."Y ".($this->unidad($numdero - 90));
        }
        else if ($numdero >= 80 && $numdero <= 89)
        {
            $numd = "OCHENTA ";
            if ($numdero > 80)
                $numd = $numd."Y ".($this->unidad($numdero - 80));
        }
        else if ($numdero >= 70 && $numdero <= 79)
        {
            $numd = "SETENTA ";
            if ($numdero > 70)
                $numd = $numd."Y ".($this->unidad($numdero - 70));
        }
        else if ($numdero >= 60 && $numdero <= 69)
        {
            $numd = "SESENTA ";
            if ($numdero > 60)
                $numd = $numd."Y ".($this->unidad($numdero - 60));
        }
        else if ($numdero >= 50 && $numdero <= 59)
        {
            $numd = "CINCUENTA ";
            if ($numdero > 50)
                $numd = $numd."Y ".($this->unidad($numdero - 50));
        }
        else if ($numdero >= 40 && $numdero <= 49)
        {
            $numd = "CUARENTA ";
            if ($numdero > 40)
                $numd = $numd."Y ".($this->unidad($numdero - 40));
        }
        else if ($numdero >= 30 && $numdero <= 39)
        {
            $numd = "TREINTA ";
            if ($numdero > 30)
                $numd = $numd."Y ".($this->unidad($numdero - 30));
        }
        else if ($numdero >= 20 && $numdero <= 29)
        {
            if ($numdero == 20)
                $numd = "VEINTE ";
            else
                $numd = "VEINTI".($this->unidad($numdero - 20));
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
            $numd = $this->unidad($numdero);
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
                    $numce = $numce.($this->decena($numc - 900));
            }
            else if ($numc >= 800 && $numc <= 899)
            {
                $numce = "OCHOCIENTOS ";
                if ($numc > 800)
                    $numce = $numce.($this->decena($numc - 800));
            }
            else if ($numc >= 700 && $numc <= 799)
            {
                $numce = "SETECIENTOS ";
                if ($numc > 700)
                    $numce = $numce.($this->decena($numc - 700));
            }
            else if ($numc >= 600 && $numc <= 699)
            {
                $numce = "SEISCIENTOS ";
                if ($numc > 600)
                    $numce = $numce.($this->decena($numc - 600));
            }
            else if ($numc >= 500 && $numc <= 599)
            {
                $numce = "QUINIENTOS ";
                if ($numc > 500)
                    $numce = $numce.($this->decena($numc - 500));
            }
            else if ($numc >= 400 && $numc <= 499)
            {
                $numce = "CUATROCIENTOS ";
                if ($numc > 400)
                    $numce = $numce.($this->decena($numc - 400));
            }
            else if ($numc >= 300 && $numc <= 399)
            {
                $numce = "TRESCIENTOS ";
                if ($numc > 300)
                    $numce = $numce.($this->decena($numc - 300));
            }
            else if ($numc >= 200 && $numc <= 299)
            {
                $numce = "DOSCIENTOS ";
                if ($numc > 200)
                    $numce = $numce.($this->decena($numc - 200));
            }
            else if ($numc >= 100 && $numc <= 199)
            {
                if ($numc == 100)
                    $numce = "CIEN ";
                else
                    $numce = "CIENTO ".($this->decena($numc - 100));
            }
        }
        else
            $numce = $this->decena($numc);
        return $numce;	
    }

    function miles($nummero){
        if ($nummero >= 1000 && $nummero < 2000)
        {
            $numm = "MIL ".($this->centena($nummero%1000));
        }
        if ($nummero >= 2000 && $nummero <10000)
        {
            $numm = $this->unidad(Floor($nummero/1000))." MIL ".($this->centena($nummero%1000));
        }
        if ($nummero < 1000)
            $numm = $this->centena($nummero);
        return $numm;
    }

    function decmiles($numdmero)
    {
        if ($numdmero == 10000)
          $numde = "DIEZ MIL";
        if ($numdmero > 10000 && $numdmero <20000)
        {
            $numde = $this->decena(Floor($numdmero/1000))."MIL ".($this->centena($numdmero%1000));		
        }
        if ($numdmero >= 20000 && $numdmero <100000)
        {
            $numde = $this->decena(Floor($numdmero/1000))." MIL ".($this->miles($numdmero%1000));		
        }		
        if ($numdmero < 10000)
            $numde = $this->miles($numdmero);
        return $numde;
    }		

    function cienmiles($numcmero)
    {
        if ($numcmero == 100000)
            $num_letracm = "CIEN MIL";
        if ($numcmero >= 100000 && $numcmero <1000000)
        {
            $num_letracm = $this->centena(Floor($numcmero/1000))." MIL ".($this->centena($numcmero%1000));		
        }
        if ($numcmero < 100000)
            $num_letracm = $this->decmiles($numcmero);
        return $num_letracm;
    }	
	
    function millon($nummiero)
    {
        if ($nummiero >= 1000000 && $nummiero <2000000)
        {
            $num_letramm = "UN MILLON ".($this->cienmiles($nummiero%1000000));
        }
        if ($nummiero >= 2000000 && $nummiero <10000000)
        {
            $num_letramm = $this->unidad(Floor($nummiero/1000000))." MILLONES ".($this->cienmiles($nummiero%1000000));
        }
        if ($nummiero < 1000000)
            $num_letramm = $this->cienmiles($nummiero);
        return $num_letramm;
    }	

    function decmillon($numerodm)
    {
        if ($numerodm == 10000000)
            $num_letradmm = "DIEZ MILLONES";
        if ($numerodm > 10000000 && $numerodm <20000000)
        {
            $num_letradmm = $this->decena(Floor($numerodm/1000000))."MILLONES ".($this->cienmiles($numerodm%1000000));		
        }
        if ($numerodm >= 20000000 && $numerodm <100000000)
        {
            $num_letradmm = $this->decena(Floor($numerodm/1000000))." MILLONES ".($this->millon($numerodm%1000000));		
        }
        if ($numerodm < 10000000)
        {
            $num_letradmm = $this->millon($numerodm);
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
            $num_letracms = $this->centena(Floor($numcmeros/1000000))." MILLONES ".($this->millon($numcmeros%1000000));		
        }
        if ($numcmeros < 100000000)
        {
            $num_letracms = $this->decmillon($numcmeros);
        }
        return $num_letracms;
    }	

    function milmillon($nummierod)
    {
        if ($nummierod >= 1000000000 && $nummierod <2000000000)
        {
            $num_letrammd = "MIL ".($this->cienmillon($nummierod%1000000000));
        }
        if ($nummierod >= 2000000000 && $nummierod <10000000000)
        {
            $num_letrammd = unidad(Floor($nummierod/1000000000))." MIL ".($this->cienmillon($nummierod%1000000000));
        }
        if ($nummierod < 1000000000)
        {
            $num_letrammd = $this->cienmillon($nummierod);
        }
        return $num_letrammd;
    }	
			
		
    function convertir($numero)
    {
        $numf = $this->milmillon($numero);
        return $numf;
    }
    public function generarReporteEntradas($folio,$tipo = 0)
    {
        /*$sql = "
            SELECT 
                th_aduana.ID_Aduana,
                th_aduana.num_pedimento,
                th_aduana.fech_pedimento,
                th_aduana.factura,
                th_aduana.fech_llegPed,
                th_aduana.status,
                th_aduana.ID_Proveedor,
                th_aduana.ID_Protocolo,
                th_aduana.Consec_protocolo,
                th_aduana.cve_usuario,
                th_aduana.Cve_Almac,
                th_aduana.recurso,
                th_aduana.procedimiento,
                th_aduana.dictamen,
                th_aduana.presupuesto,
                th_aduana.condicionesDePago,
                th_aduana.lugarDeEntrega,
                th_aduana.fechaDeFallo,
                th_aduana.plazoDeEntrega,
                th_aduana.numeroDeExpediente,
                th_aduana.areaSolicitante,
                th_aduana.numSuficiencia,
                th_aduana.fechaSuficiencia,
                th_aduana.fechaContrato,
                th_aduana.montoSuficiencia,
                th_aduana.numeroContrato,

                c_presupuestos.id,
                c_presupuestos.nombreDePresupuesto,
                c_presupuestos.anoDePresupuesto,
                c_presupuestos.claveDePartida,
                c_presupuestos.conceptoDePartida,
                c_presupuestos.monto,

                td_aduana.ID_Aduana,
                td_aduana.cve_articulo,
                td_aduana.cantidad,
                td_aduana.cve_lote,
                td_aduana.num_orden,
                td_aduana.costo,
                td_aduana.costo*td_aduana.cantidad as subtotal,

                c_articulo.cve_articulo,
                c_articulo.des_articulo,
                c_articulo.tipo_producto,
                c_articulo.umas,
                
                c_proveedores.Nombre as proveedor
            FROM th_aduana
                LEFT JOIN c_presupuestos on th_aduana.presupuesto = c_presupuestos.id
                LEFT JOIN td_aduana on th_aduana.num_pedimento = td_aduana.num_orden
                LEFT JOIN c_articulo on td_aduana.cve_articulo = c_articulo.cve_articulo
                LEFT JOIN c_proveedores on c_proveedores.ID_Proveedor = th_aduana.ID_Proveedor
            WHERE num_pedimento = ".$folio.";
        ";*/
      //0 => Folio de Entrada de almacen
      //1 => Folio de OC

      $sql = "";
      $titulo = "";
      $titulo_fecha ="";
      if($tipo == "OC" || $tipo == 1)
      {
					$titulo ="Orden de Compra";
					$datos_artculos ="
							td_aduana.ID_Aduana,
							td_aduana.cve_articulo,
							td_aduana.cantidad,
							td_aduana.cve_lote,
							td_aduana.num_orden,
							td_aduana.costo,
							td_aduana.costo*td_aduana.cantidad as subtotal,
                            th_entalmacen.tipo,
                            th_aduana.cve_usuario,
					";
					$where = "WHERE th_aduana.num_pedimento = ".$folio;
					$from = "th_aduana
							LEFT JOIN td_aduana on td_aduana.num_orden = th_aduana.num_pedimento";
					$join = "LEFT JOIN th_entalmacen on th_entalmacen.id_ocompra = th_aduana.num_pedimento
							LEFT JOIN c_articulo on td_aduana.cve_articulo = c_articulo.cve_articulo
                            LEFT JOIN c_compania ON c_compania.cve_cia = (SELECT cve_cia FROM c_usuario WHERE id_user = th_aduana.cve_usuario)
                            ";

                        $sql = "
                            SELECT distinct
                                th_aduana.ID_Aduana,
                                th_aduana.fech_pedimento,
                                if(th_aduana.num_pedimento != '',th_aduana.num_pedimento,th_entalmacen.fol_folio) as num_pedimento,
                                th_aduana.factura,
                                th_aduana.fech_llegPed,
                                th_aduana.status,
                                th_aduana.ID_Proveedor,
                                th_aduana.ID_Protocolo,
                                th_aduana.Consec_protocolo,
                                th_aduana.Cve_Almac,
                                th_aduana.recurso,
                                th_aduana.procedimiento,
                                th_aduana.dictamen,
                                th_aduana.presupuesto,
                                th_aduana.condicionesDePago,
                                th_aduana.lugarDeEntrega,
                                th_aduana.fechaDeFallo,
                                th_aduana.plazoDeEntrega,
                                th_aduana.numeroDeExpediente,
                                th_aduana.areaSolicitante,
                                th_aduana.numSuficiencia,
                                th_aduana.fechaSuficiencia,
                                th_aduana.fechaContrato,
                                th_aduana.montoSuficiencia,
                                th_aduana.numeroContrato,

                                c_presupuestos.id,
                                c_presupuestos.nombreDePresupuesto,
                                c_presupuestos.anoDePresupuesto,
                                c_presupuestos.claveDePartida,
                                c_presupuestos.conceptoDePartida,
                                c_presupuestos.monto,

                                {$datos_artculos}

                                c_articulo.cve_articulo,
                                c_articulo.des_articulo,
                                c_articulo.tipo_producto,
                                m.des_umed AS umas,
                                c_articulo.mav_pctiva as iva,
                                
                                c_proveedores.Nombre as proveedor,

                                c_compania.imagen AS logo,
                                
                                (select concat(c_compania.des_cia,'<br>', c_compania.des_rfc,'<br>',c_compania.des_direcc, ' ',c_compania.des_cp) from c_compania where cve_cia = 1) as datos_facturacion,
                                (SELECT CONCAT(Nombre,'<br>',RUT,'<br>',direccion, '<br>',colonia,' ',cve_dane,'<br>',ciudad,', ',estado,', ',pais ) FROM c_proveedores WHERE ID_Proveedor = th_entalmacen.Cve_Proveedor) datos_proveedor
                            from {$from}
                            {$join}

                            LEFT JOIN c_presupuestos on th_aduana.presupuesto = c_presupuestos.id
                            LEFT JOIN c_proveedores on c_proveedores.ID_Proveedor = th_entalmacen.Cve_Proveedor
                            LEFT JOIN c_unimed m ON m.id_umed = c_articulo.unidadMedida

                            {$where};
                        ";//th_aduana.cve_usuario
      }
      else
			{
                    if($tipo == 'RL') $titulo = "Recepcion Libre";
                    if($tipo == 'CD') $titulo = "Cross Docking";
                    if($tipo == 'DV' || $tipo == 'DVP') $titulo = "Devolución";
                    if($tipo == 'DVL' || $tipo == 'DPL') $titulo = "Devolución Libre";
					$datos_artculos ="
							'' as ID_Aduana,
							td_entalmacen.cve_articulo,
							td_entalmacen.CantidadRecibida as cantidad,
							td_entalmacen.cve_lote,
							td_entalmacen.num_orden,
							td_entalmacen.costoUnitario,
							td_entalmacen.costoUnitario*td_entalmacen.CantidadRecibida as subtotal,
                            th_entalmacen.tipo,
                            th_entalmacen.Cve_Usuario,
					";
					$where = "where th_entalmacen.Fol_Folio = ".$folio;
					$from = "th_entalmacen";
					$join = "LEFT JOIN td_entalmacen on td_entalmacen.fol_folio = th_entalmacen.Fol_Folio
							LEFT JOIN c_articulo on td_entalmacen.cve_articulo = c_articulo.cve_articulo
                            LEFT JOIN c_compania ON c_compania.cve_cia = (SELECT cve_cia FROM c_usuario WHERE cve_usuario = th_entalmacen.Cve_Usuario)";

                        $sql = "
                            select distinct
                                th_entalmacen.Fec_Entrada as fech_pedimento,
                                th_entalmacen.Fol_Folio as num_pedimento,
                                th_entalmacen.Fact_Prov as factura,
                                th_entalmacen.Fec_Entrada as fech_llegPed,
                                th_entalmacen.STATUS as status,
                                th_entalmacen.Cve_Proveedor as ID_Proveedor,
                                th_entalmacen.ID_Protocolo,
                                th_entalmacen.Consec_protocolo,
                                th_entalmacen.Cve_Almac,
                                (SELECT direccion FROM c_almacenp WHERE clave = th_entalmacen.Cve_Almac) AS lugarDeEntrega,

                                '' as recurso,
                                '' as procedimiento,
                                '' as dictamen,
                                '' as presupuesto,
                                '' as condicionesDePago,
                                '' as fechaDeFallo,
                                '' as plazoDeEntrega,
                                '' as numeroDeExpediente,
                                '' as areaSolicitante,
                                '' as numSuficiencia,
                                '' as fechaSuficiencia,
                                '' as fechaContrato,
                                '' as montoSuficiencia,
                                '' as numeroContrato,

                                '' AS id,
                                '' AS nombreDePresupuesto,
                                '' AS anoDePresupuesto,
                                '' AS claveDePartida,
                                '' AS conceptoDePartida,
                                '' AS monto,

                                {$datos_artculos}

                                c_articulo.cve_articulo,
                                c_articulo.des_articulo,
                                c_articulo.tipo_producto,
                                c_articulo.umas,
                                c_articulo.mav_pctiva as iva,
                                
                                c_proveedores.Nombre as proveedor,

                                c_compania.imagen AS logo,
                                
                                (select concat(c_compania.des_cia,'<br>', c_compania.des_rfc,'<br>',c_compania.des_direcc, ' ',c_compania.des_cp) from c_compania where cve_cia = 1) as datos_facturacion,
                                (select concat(Nombre,'<br>',RUT,'<br>',direccion, '<br>',colonia,' ',cve_dane,'<br>',ciudad,', ',estado,', ',pais ) from c_proveedores where ID_Proveedor = th_entalmacen.Cve_Proveedor) datos_proveedor
                            from {$from}
                            {$join}

                            LEFT JOIN c_proveedores on c_proveedores.ID_Proveedor = th_entalmacen.Cve_Proveedor

                            {$where};
                        ";//th_aduana.cve_usuario
      }
      
      
        $query = mysqli_query(\db2(), $sql);
        //$datos = mysqli_fetch_array($query, MYSQLI_ASSOC);
        $datos = "";
        $rows = array();
        while(($row = mysqli_fetch_array($query, MYSQLI_ASSOC))) 
        {
            $rows[] = $row;
            $datos = $row;
            //if($row['tipo'] == 'RL') $titulo = "Recepcion Libre";
            //if($row['tipo'] == 'CD') $titulo = "Cross Docking";
        }
        $usuario_logueado = "";
        if($tipo == "OC")
        {
            $usuario = $datos['cve_usuario'];
            $sql_usuario = "SELECT nombre_completo FROM c_usuario WHERE id_user = {$usuario}";
            $query = mysqli_query(\db2(), $sql_usuario);
            $row_usuario = mysqli_fetch_array($query, MYSQLI_ASSOC);
            $usuario_logueado = $row_usuario["nombre_completo"];
            $titulo_fecha = "Fecha Solicitada";
        }
        else
        {
            $usuario = $datos['Cve_Usuario'];
            $sql_usuario = "SELECT nombre_completo FROM c_usuario WHERE cve_usuario = '{$usuario}'";
            $query = mysqli_query(\db2(), $sql_usuario);
            $row_usuario = mysqli_fetch_array($query, MYSQLI_ASSOC);
            $usuario_logueado = $row_usuario["nombre_completo"];
            $titulo_fecha = "Fecha Recepción";
            if($tipo == "DV" || $tipo == "DVL" || $tipo == "DVP" || $tipo == "DPL") $titulo_fecha = "Fecha Devolución";
        }

        $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT , array(152.4, 101.6), true, 'UTF-8', false);
        $pdf->SetCreator('wms');
        $pdf->SetAuthor('wms');
        $pdf->SetTitle('Reporte de Entradas');
        $filename = "Hoja de remision orden #{$folio}.pdf";
        $base_url = $_SERVER['DOCUMENT_ROOT'];
        //$imgLogosSP = $base_url."img/logos_sp.jpg";
        $imgLogosSP = $base_url.$datos["logo"];
        ob_start(); 
        ?>
                <div class="col-md-12" >
                    &nbsp;&nbsp;&nbsp;&nbsp;<img style="height: 50px;" src="<?=$imgLogosSP?>" >
                </div>
            <table style="width:100%;">
                <tr style="width:100%;">
                    <td style="width: 10px;"></td>
                    <td style="width:340px;">
                        <table>

                            <tr>
                                <td colspan="4" style=""></td>
                                <td colspan="8" style="border: solid 1px black;  height: 2px; line-height: 12px; text-align:right; font-size: 6px;">Expediente No. <?php echo utf8_encode($datos["numeroDeExpediente"]); ?>&nbsp;&nbsp;&nbsp;&nbsp;</td>
                            </tr>
                            <tr>
                                <td colspan="12" style="">&nbsp;</td>
                            </tr>
                            <tr>
                                <td colspan="12" style="border: 1px solid black; white-space:nowrap; text-align: center; background-color: #DCDCDC; font-size: 7px;"> <?php echo $titulo." ".utf8_encode($datos["num_pedimento"]) ?></td>
                            </tr>
                            <tr>
                                <td colspan="12" style="">&nbsp;</td>
                            </tr>
                            <tr>
                                <td colspan="4" style="border: 1px solid black; white-space:nowrap; line-height: 14px; text-align: center; font-size: 6px; background-color: #DCDCDC;">Datos de Facturación</td>
                                <td colspan="1" style=""></td>
                                <td colspan="2" style="border: 1px solid black; white-space:nowrap; line-height: 14px; text-align: center; font-size: 6px; background-color: #DCDCDC;">Fecha</td>
                                <td colspan="3" style="border: 1px solid black; white-space:nowrap; line-height: 14px; text-align: center; font-size: 6px; background-color: #DCDCDC;">Partida Presupuestal</td>
                                <td colspan="3" style="border: 1px solid black; white-space:nowrap; text-align: center; font-size: 6px; background-color: #DCDCDC;">Suficiencia Presupuestal</td>
                            </tr>
                            <tr>
                                <td colspan="4" rowspan="4" style="border: 1px solid black; white-space:nowrap; text-align: center; font-size: 6px;"><?php echo utf8_encode($datos["datos_facturacion"]); ?></td>
                                <td colspan="1" style=""></td>
                                <td colspan="2" style="border: 1px solid black; white-space:nowrap; text-align: center; font-size: 6px;">
                                    <?php $Date = date("d-m-Y", strtotime(utf8_encode($datos["fech_pedimento"]))); echo $Date; ?></td>
                                <td colspan="3" style="border: 1px solid black; white-space:nowrap; text-align: center; font-size: 6px;"><?php echo utf8_encode($datos["claveDePartida"]) ?></td>
                                <td colspan="3" style="border: 1px solid black; white-space:nowrap; text-align: center; font-size: 6px;"><?php echo utf8_encode($datos["montoSuficiencia"]) ?></td>
                            </tr>
                            <tr>
                                <td colspan="12" style="">&nbsp;</td>
                            </tr> 
                            <tr>
                                <td colspan="1" style=""></td>
                                <td colspan="4" style="border: 1px solid black; white-space:nowrap; line-height: 14px; text-align: center; font-size: 6px; background-color: #DCDCDC;">Condiciones de Pago</td>
                                <td colspan="4" style="border: 1px solid black; white-space:nowrap; line-height: 14px; text-align: center; font-size: 6px; background-color: #DCDCDC;"><?php echo$titulo_fecha; ?></td>
                            </tr>
                            <tr>
                                <td colspan="1" style=""></td>
                                <td colspan="4" style="border: 1px solid black; white-space:nowrap; text-align: center; font-size: 6px;"><?php echo utf8_encode(utf8_encode($datos["condicionesDePago"])) ?></td>
                                <td colspan="4" style="border: 1px solid black; white-space:nowrap; text-align: center; font-size: 6px;">
                                    <?php $Date = date("d-m-Y", strtotime(utf8_encode($datos["fech_llegPed"]))); echo $Date; ?></td>
                            </tr>
                            <tr>
                                <td colspan="12" style="">&nbsp;</td>
                            </tr> 
                            <tr>
                                <td colspan="4" style="border: 1px solid black; white-space:nowrap; line-height: 14px; text-align: center; font-size: 6px; background-color: #DCDCDC;">Proveedor</td>
                                <td colspan="1" style=""></td>
                                <td colspan="7" style="border: 1px solid black; white-space:nowrap; line-height: 14px; text-align: center; font-size: 6px; background-color: #DCDCDC;">Lugar de Entrega</td>
                            </tr>
                            <tr>
                                <td colspan="4" rowspan="4" style="border: 1px solid black; white-space:nowrap; text-align: center; font-size: 6px;"><?php echo utf8_encode($datos["datos_proveedor"]); ?></td>
                                <td colspan="1" style=""></td>
                                <td colspan="7" rowspan="2" style="border: 1px solid black; white-space:nowrap; text-align: center; font-size: 6px;"><?php echo utf8_encode($datos["lugarDeEntrega"]); ?></td>
                            </tr>
                            <tr>
                                <td colspan="1" style=""></td>
                            </tr>
                            <tr>
                                <td colspan="1" style=""></td>
                                <td colspan="7" style="border: 1px solid black; white-space:nowrap; line-height: 14px; text-align: center; font-size: 6px; background-color: #DCDCDC;">Area Solicitante</td>
                            </tr>
                            <tr>
                                <td colspan="1" style=""></td>
                                <td colspan="7" style="border: 1px solid black; white-space:nowrap; text-align: center; font-size: 6px; "><?php echo utf8_encode(utf8_encode($datos["areaSolicitante"])) ?></td>
                            </tr>
                            <tr>
                                <td colspan="12" style="">&nbsp;</td>
                            </tr> 
                            <tr>
                                <td colspan="12" style="white-space:nowrap; text-align: left; font-size: 5px; ">LA PRESENTE FACTURA SE EMITE EN ESTRICTO CUMPLIMIENTO  DEL CONTRATO No. <?php echo utf8_encode($datos["numeroContrato"]) ?></td>
                            </tr>
                            <tr>
                                <td colspan="12" style="">&nbsp;</td>
                            </tr> 
                            <tr>
                              <td colspan="2" style="border: 1px solid black; white-space:nowrap; line-height: 14px; text-align: center; font-size: 6px; background-color: #DCDCDC;">PARTIDA</td>
                              <td colspan="4" style="border: 1px solid black; white-space:nowrap; line-height: 14px; text-align: center; font-size: 6px; background-color: #DCDCDC;">CONCEPTO</td>
                              <td colspan="2" style="border: 1px solid black; white-space:nowrap; line-height: 14px; text-align: center; font-size: 6px; background-color: #DCDCDC;">CANTIDAD</td>
                              <td colspan="1" style="border: 1px solid black; white-space:nowrap; line-height: 14px; text-align: center; font-size: 6px; background-color: #DCDCDC;">U.M.</td>
                              <td colspan="1" style="border: 1px solid black; white-space:nowrap; line-height: 14px; text-align: center; font-size: 6px; background-color: #DCDCDC;">P.U.</td>
                              <td colspan="2" style="border: 1px solid black; white-space:nowrap; line-height: 14px; text-align: center; font-size: 6px; background-color: #DCDCDC;">SUBTOTAL</td>
                            </tr>
                    
                            <?php 
                            $sumaSubtotales = 0;
                            $ivaCalculado = 0;
                            foreach($rows as $row)
                            { 
                            ?>
                                <tr>
                                    <td colspan="2" rowspan="1" style="border: 1px solid black; white-space:nowrap; text-align: center; font-size: 5px;"><?php echo $row["cve_articulo"] ?></td>
                                    <td colspan="4" rowspan="1" style="border: 1px solid black; white-space:nowrap; text-align: center; font-size: 5px;"><?php echo utf8_encode($row["des_articulo"]) ?></td>
                                    <td colspan="2" rowspan="1" style="border: 1px solid black; white-space:nowrap; text-align: center; font-size: 5px;"><?php echo number_format($row["cantidad"], 2); ?></td>
                                    <td colspan="1" rowspan="1" style="border: 1px solid black; white-space:nowrap; text-align: center; font-size: 5px;"><?php echo $row["umas"] ?></td>
                                    <td colspan="1" rowspan="1" style="border: 1px solid black; white-space:nowrap; text-align: center; font-size: 5px;"><?php echo $row["costo"] ?></td>
                                    <td colspan="2" rowspan="1" style="border: 1px solid black; white-space:nowrap; text-align: center; font-size: 5px;"><?php echo round($row["subtotal"], 2); ?></td>
                                </tr>
                            <?php 
                                $sumaSubtotales += $row["subtotal"];
                                $iva = 0;
                                if($datos["iva"]) $iva = $sumaSubtotales - $sumaSubtotales/(1+($datos["iva"]/100));
                                //$ivaCalculado = $sumaSubtotales*$iva;
                                $ivaCalculado += round($iva, 2);
                                //$totalCalculado = $sumaSubtotales + $ivaCalculado;
                                $totalCalculado = $sumaSubtotales;
                                $xcifra = $totalCalculado;
                                $letras = $this->convertir($xcifra);
                            } 

                            ?>
                    
                            <tr>
                                <td colspan="8" style=""></td>
                                <td colspan="2" style="border: 1px solid black; white-space:nowrap; line-height: 10px; text-align: center; font-size: 5px;">SUBTOTAL</td>
                                <td colspan="2" style="border: 1px solid black; white-space:nowrap; line-height: 10px; text-align: center; font-size: 5px;"><?php echo number_format($sumaSubtotales, 2); ?></td> 
                            </tr>
                            <tr>
                                <td colspan="8" style=""></td>
                                <td colspan="2" style="border: 1px solid black; white-space:nowrap; line-height: 10px; text-align: center; font-size: 5px;">I.V.A (16 %)</td>
                                <td colspan="2" style="border: 1px solid black; white-space:nowrap; line-height: 10px; text-align: center; font-size: 5px;"><?php echo number_format($ivaCalculado, 2); ?></td>
                            </tr>
                            <tr>
                                <td colspan="7" style="white-space:nowrap; text-align: left; font-size: 5px; font-family: Calibri"><b>PRESUPUESTO: <?php echo utf8_encode($datos["nombreDePresupuesto"]);  ?></b></td>
                                <td colspan="1" style=""></td>
                                <td colspan="2" style="border: 1px solid black; white-space:nowrap; line-height: 10px; text-align: center; font-size: 5px;">TOTAL</td>
                                <td colspan="2" style="border: 1px solid black; white-space:nowrap; line-height: 10px; text-align: center; font-size: 5px;">$ <?php echo number_format($totalCalculado, 2); ?></td>
                            </tr>
                            <tr>
                                <td colspan="12" style="white-space:nowrap; line-height: 10px; text-align: left; font-size: 5px; "><b>CANTIDAD CON LETRA (<?php echo $letras ?> PESOS 00/100 M.N.)</b></td>
                            </tr>
                            <tr>
                                <td colspan="12" style=""></td>
                            </tr> 
                            <tr>
                                <td colspan="12" style=""></td>
                            </tr> 
                            <tr>
                                <td colspan="5" style="white-space:nowrap; text-align: center; font-size: 5px;   display: block; position: running(footer); "><b>AUTORIZO</b></td>
                                <td colspan="2" style=""></td>
                                <td colspan="5" style="white-space:nowrap; text-align: center; font-size: 5px;   display: block; position: running(footer); "><b>PROVEEDOR</b></td>
                            </tr>
                            <tr>
                                <td colspan="12" style=""></td>
                            </tr> 
                            <tr>
                                <td colspan="5" style="white-space:nowrap; text-align: center; font-size: 5px;   display: block; position: running(footer);"><b>______________________________________</b></td>
                                <td colspan="2" style=""></td>
                                <td colspan="5" style="white-space:nowrap; text-align: center; font-size: 5px;   display: block; position: running(footer);"><b>______________________________________</b></td>
                            </tr>
                            <tr>
                                <td colspan="5" style="white-space:nowrap; text-align: center; font-size: 5px;   display: block; text-align: center; position: running(footer);"> <?php echo $usuario_logueado; ?> </td>
                                <td colspan="2" style=""></td>
                                <td colspan="5" style="white-space:nowrap; text-align: center; font-size: 5px;   display: block; text-align: center; position: running(footer);"><?php echo utf8_encode($datos["proveedor"]) ?></td>
                            </tr>
                        </table>
                    </td>
                    <td style="width: 10px;"></td>
                </tr>
            </table>
          <?php
          $desProducto = ob_get_clean();
          $pdf->AddPage();
          $style = array(
              'position'     => '',
              'align'        => 'C',
              'stretch'      => false,
              'fitwidth'     => false,
              'cellfitalign' => '',
              'border'       => false,
              'baseline'     => false,
              'hpadding'     => 'auto',
              'vpadding'     => 'auto',
              'fgcolor'      => array(0, 0, 0),
              'bgcolor'      => false,
              'text'         => true,
              'font'         => 'helvetica',
              'fontsize'     => 6,
              'stretchtext'  => 4
          );
         // $pdf->write1DBarcode($this->folio, 'C128', 65, 5, $width, $height, 0.2, $style, 'N');

          $pdf->SetXY(6, 8);
          $pdf->SetDrawColor(255,255,255);
          $pdf->SetFillColor(255,255,255);
          $pdf->SetLineWidth(3);
          $pdf->Cell(0, 0, "", 1, 1, 'L', 1, 0);

          $pdf->SetAutoPageBreak(TRUE, 5);
          $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO); 
          $pdf->setMargins(0, 5, 0, 0);
          $pdf->SetXY(5, 5);
          $pdf->SetFont('helvetica', '', '8px', '', 'default', true);
          $pdf->WriteHTML($desProducto, true, false, true, '');
          //echo var_dump($desProducto);
          $pdf->Output($filename, 'I');


      }


    public function generarReporteEntradasCodigoDeBarras($folio,$proveedor, $oc)
    {
        $res = ""; $res2 = "";
        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

          $sql1 = "
              SELECT DISTINCT
                    tde.cve_articulo AS clave,
                    a.des_articulo AS descripcion,
                    IFNULL(a.control_lotes, 'N') AS band_lote,
                    IFNULL(a.Caduca, 'N') AS band_caducidad,
                    IFNULL(a.control_numero_series, 'N') AS band_serie,
                    tde.CantidadPedida AS cantidad_pedida,
                    tde.cve_lote AS lote,
                    tde.numero_serie AS serie,
                    DATE_FORMAT(cl.Caducidad, '%d-%m-%Y') AS caducidad,
                    tde.status AS STATUS,
                    tde.CantidadRecibida AS cantidad_recibid,
                    td.Cantidad AS cantidad_recibida,
                    DATE_FORMAT(tde.fecha_inicio, '%d-%m-%Y') AS fecha_recepcion,
                    DATE_FORMAT(tde.fecha_inicio, '%d-%m-%Y %h:%i:%s %p') AS fecha_inicio,
                    DATE_FORMAT(tde.fecha_fin, '%d-%m-%Y %h:%i:%s %p') AS fecha_fin,
                    tde.CantidadPedida - tde.CantidadRecibida AS cantidad_faltante,
                    tde.CantidadRecibida - tde.CantidadDisponible AS cantidad_danada,
                    td.ClaveEtiqueta,
                    u.cve_usuario AS usuario
              FROM td_entalmacenxtarima td
                    LEFT JOIN td_entalmacen tde ON tde.fol_folio = td.fol_folio AND tde.cve_articulo = td.cve_articulo AND td.cve_lote = tde.cve_lote
                    LEFT JOIN c_lotes cl ON cl.LOTE = tde.cve_lote AND cl.Activo=1
                    LEFT JOIN c_articulo a ON a.cve_articulo = tde.cve_articulo 
                    LEFT JOIN c_usuario u ON u.cve_usuario = tde.cve_usuario
              WHERE tde.fol_folio = '$folio' AND td.Cantidad > 0

UNION

SELECT DISTINCT
                    tde.cve_articulo AS clave,
                    a.des_articulo AS descripcion,
                    IFNULL(a.control_lotes, 'N') AS band_lote,
                    IFNULL(a.Caduca, 'N') AS band_caducidad,
                    IFNULL(a.control_numero_series, 'N') AS band_serie,
                    tde.CantidadPedida AS cantidad_pedida,
                    tde.cve_lote AS lote,
                    tde.numero_serie AS serie,
                    DATE_FORMAT(cl.Caducidad, '%d-%m-%Y') AS caducidad,
                    tde.status AS STATUS,
                    tde.CantidadRecibida AS cantidad_recibid,
                    0 AS cantidad_recibida,
                    DATE_FORMAT(tde.fecha_inicio, '%d-%m-%Y') AS fecha_recepcion,
                    DATE_FORMAT(tde.fecha_inicio, '%d-%m-%Y %h:%i:%s %p') AS fecha_inicio,
                    DATE_FORMAT(tde.fecha_fin, '%d-%m-%Y %h:%i:%s %p') AS fecha_fin,
                    tde.CantidadPedida - tde.CantidadRecibida AS cantidad_faltante,
                    tde.CantidadRecibida - tde.CantidadDisponible AS cantidad_danada,
                    '' AS ClaveEtiqueta,
                    u.cve_usuario AS usuario
              FROM td_entalmacen tde 
                    LEFT JOIN c_lotes cl ON cl.LOTE = tde.cve_lote AND cl.Activo=1
                    LEFT JOIN c_articulo a ON a.cve_articulo = tde.cve_articulo 
                    LEFT JOIN c_usuario u ON u.cve_usuario = tde.cve_usuario
              WHERE tde.fol_folio = '$folio'";//LEFT JOIN c_articulo a ON a.cve_articulo = tde.cve_articulo AND cl.cve_articulo=a.cve_articulo

          $sql2 = "
              SELECT DISTINCT
                    tdtar.ClaveEtiqueta AS contenedor,
                    DATE_FORMAT(MAX(tde.fecha_inicio), '%d/%m/%Y') AS fecha_ingreso,
                    IF(cch.CveLP != '', cch.CveLP,CONCAT('LP', LPAD(CONCAT(cch.IDContenedor,tde.fol_folio), 9, 0))) AS LP
              FROM td_entalmacen tde
                    LEFT JOIN td_entalmacenxtarima tdtar ON tdtar.fol_folio = tde.fol_folio
                    LEFT JOIN c_charolas cch ON cch.clave_contenedor = tdtar.ClaveEtiqueta
              WHERE tde.fol_folio = '$folio'
              GROUP BY contenedor
          ";

          // hace una llamada previa al procedimiento almacenado Lis_Facturas
          $res  = mysqli_query($conn, $sql1);
          $res2 = mysqli_query($conn, $sql2);
          if(!$res || !$res2)
          {
            echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
          }

        //************************************************
        // Proceso para unir los resultados de las tablas
        //************************************************
        $lp_reg = ""; $pallet_contenedor = ""; $pallets_diferentes = true; $fecha_ingreso = ""; 
        $num_pallets = mysqli_num_rows($res2);
/*
        if($num_pallets == 1)
        {
          $row = mysqli_fetch_array($res2);
          $lp_reg = $row['LP'];
          $pallet_contenedor = $row['contenedor'];
          $fecha_ingreso = $row['fecha_ingreso'];
          $pallets_diferentes = false;
        }
*/
        //************************************************
      
        //$query = mysqli_query(\db2(), $sql);
        //$datos = mysqli_fetch_array($query, MYSQLI_ASSOC);


        $filename = "codigo de barras #{$folio}.pdf";
        $base_url = $_SERVER['DOCUMENT_ROOT'];
        //$imgLogosSP = $base_url."img/logos_sp.jpg";
        $imgLogosSP = $base_url.$datos["logo"];
        //ob_start(); 
        if($num_pallets == 1)
        {
            ob_start(); 
            $row2 = mysqli_fetch_array($res2, MYSQLI_ASSOC);
            $lp_reg = $row2['LP'];
            $pallet_contenedor = $row2['contenedor'];
            $fecha_ingreso = $row2['fecha_ingreso'];

            $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT , array(152.4, 101.6), true, 'UTF-8', false);
            $pdf->SetCreator('wms');
            $pdf->SetAuthor('wms');
            $pdf->SetTitle('Reporte Código de Barras');


            $pdf->AddPage();
            $pdf->SetXY(10, 4.5);
            $pdf->SetFillColor(0,0,0);
            $pdf->SetTextColor(255,255,255);
            //$pdf->SetLineWidth(3);
            $pdf->SetFontSize(12);
            $pdf->Cell(84, 4, "License Plate Control   AssistPro ADL", 1, 1, 'C', 1, 0);


            $pdf->SetXY(10, 14);
            $pdf->SetFontSize(8);
            $pdf->SetFillColor(255,255,255);
            $pdf->SetTextColor(0,0,0);
            $pdf->Cell(30, 4, "Fecha Ingreso: ", 0, 1, 'L', 1, 0);

            $pdf->SetXY(30, 14);
            $pdf->SetFontSize(8);
            $pdf->SetFillColor(255,255,255);
            $pdf->SetTextColor(0,0,0);
            $pdf->Cell(18, 4, $fecha_ingreso, 0, 1, 'R', 1, 0);

            $pdf->SetXY(70, 14);
            $pdf->SetFontSize(8);
            $pdf->SetFillColor(255,255,255);
            $pdf->SetTextColor(0,0,0);
            $pdf->Cell(10, 4, "Folio: ", 0, 1, 'L', 1, 0);

            $pdf->SetXY(80, 14);
            $pdf->SetFontSize(8);
            $pdf->SetFillColor(255,255,255);
            $pdf->SetTextColor(0,0,0);
            $pdf->Cell(13, 4, $folio, 0, 1, 'R', 1, 0);


            $pdf->SetXY(10, 20);
            $pdf->SetFontSize(8);
            $pdf->SetFillColor(255,255,255);
            $pdf->SetTextColor(0,0,0);
            $pdf->Cell(30, 4, "Proveedor: ", 0, 1, 'L', 1, 0);

            $pdf->SetTextColor(0,0,0);
            $pdf->MultiCell(50, 4, $proveedor, 0, 'L', true, 1, 32, 20);

            $pdf->SetXY(35, 30);
            $pdf->SetFontSize(8);
            $pdf->SetFillColor(0,0,0);
            $pdf->SetTextColor(255,255,255);
            $pdf->Cell(30, 4, "Pallet/Contenedor", 0, 1, 'C', 1, 0);

          $style = array(
              'position'     => '',
              'align'        => 'C',
              'stretch'      => false,
              'fitwidth'     => false,
              'cellfitalign' => '',
              'border'       => false,
              'baseline'     => false,
              'hpadding'     => 'auto',
              'vpadding'     => 'auto',
              'fgcolor'      => array(0, 0, 0),
              'bgcolor'      => false,
              'text'         => true,
              'font'         => 'helvetica',
              'fontsize'     => 10,
              'stretchtext'  => 4
          );

            $pdf->write1DBarcode($lp_reg, 'C128', 15, 35, '70', '20', 0.6, $style, 'N');

            $datos = "";
            $rows = array();
            //ob_start(); 
            ?>
                <table style="width:300px;">
                <?php 
                while(($row = mysqli_fetch_array($res, MYSQLI_ASSOC))) 
                {
                    $rows[] = $row;
                    $datos = $row;

    $band_serie     = $row['band_serie'];
    $band_lote      = $row['band_lote'];
    $band_caducidad = $row['band_caducidad'];
    $lote = $datos["lote"];
    $serie = $datos["serie"];
    $caducidad = $datos["caducidad"];

    if($band_serie == 'S')
    {
        $serie = $lote;
        $lote = "";
        $caducidad = "";
    }
    else if($band_lote == 'S')
    {
        $serie = "";
        if($band_caducidad == 'N') $caducidad = "";
    }
    else
    {
      $lote = "";
      $serie = "";
      $caducidad = "";
    }

                ?>
                    <tr style="background-color: #000; color: #fff;line-height: 10px;">
                        <td colspan="2" style="width: 100px;">Clave</td>
                        <td colspan="2" style="width: 200px;">Descripción</td>
                    </tr>
                    <tr>
                        <td style="font-size: 12px;width: 100px;" colspan="2"><?php echo $datos["clave"]; ?></td>
                        <td style="font-size: 12px;width: 200px;" colspan="2"><?php echo utf8_encode($datos["descripcion"]); ?></td>
                    </tr>

                    <tr>
                        <td style="width: 100px;">Lote</td>
                        <td style="width: 100px;">Caducidad</td>
                        <td style="width: 50px;">Serie</td>
                        <td align="center" style="width: 50px;">Cantidad</td>
                    </tr>
                    <tr>
                        <td style="font-size: 12px;width: 100px;"><?php echo $lote; ?></td>
                        <td style="font-size: 12px;width: 100px;"><?php echo $caducidad; ?></td>
                        <td style="font-size: 12px;width: 50px;"><?php echo $serie; ?></td>
                        <td style="font-size: 12px;width: 50px;" align="center"><?php echo $datos["cantidad_recibida"]; ?></td>
                    </tr>
                <?php  
                }
                ?>
                </table>
                <?php 
              $desProducto = ob_get_clean();
              $pdf->SetAutoPageBreak(TRUE, 5);
              $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO); 
              //$pdf->setMargins(0, 5, 0, 0);
              $pdf->SetXY(10, 60);
              $pdf->SetTextColor(0,0,0);
              $pdf->SetFont('helvetica', '', '5px', '', 'default', true);
              $pdf->WriteHTML($desProducto, true, false, true, '');
              $pdf->Output($filename, 'I');
          }
          else
          {
            $row_array = array(); $row2_array = array();
            while($row = mysqli_fetch_array($res, MYSQLI_ASSOC))
            {
                $row_array[] = $row;
            }

            while($row2 = mysqli_fetch_array($res2, MYSQLI_ASSOC))
            {
                $row2_array[] = $row2;
            }
                $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT , array(152.4, 101.6), true, 'UTF-8', false);
                $pdf->SetCreator('wms');
                $pdf->SetAuthor('wms');
                $pdf->SetTitle('Reporte Código de Barras');
            
            for($i = 0; $i < $num_pallets; $i++)
            {
                //$row2 = mysqli_fetch_array($res2, MYSQLI_ASSOC);
                ob_start(); 
                $row2 = $row2_array[$i];

                $lp_reg = $row2['LP'];
                $pallet_contenedor = $row2['contenedor'];
                $fecha_ingreso = $row2['fecha_ingreso'];


                $pdf->AddPage();
                $pdf->SetXY(10, 4.5);
                $pdf->SetFillColor(0,0,0);
                $pdf->SetTextColor(255,255,255);
                $pdf->SetFontSize(12);
                //$pdf->SetLineWidth(3);
                $pdf->Cell(84, 4, "License Plate Control   AssistPro ADL", 1, 1, 'C', 1, 0);


                $pdf->SetXY(10, 14);
                $pdf->SetFontSize(8);
                $pdf->SetFillColor(255,255,255);
                $pdf->SetTextColor(0,0,0);
                $pdf->Cell(30, 4, "Fecha Ingreso: ", 0, 1, 'L', 1, 0);

                $pdf->SetXY(30, 14);
                $pdf->SetFontSize(8);
                $pdf->SetFillColor(255,255,255);
                $pdf->SetTextColor(0,0,0);
                $pdf->Cell(18, 4, $fecha_ingreso, 0, 1, 'R', 1, 0);

            $pdf->SetXY(70, 14);
            $pdf->SetFontSize(8);
            $pdf->SetFillColor(255,255,255);
            $pdf->SetTextColor(0,0,0);
            $pdf->Cell(10, 4, "Folio: ", 0, 1, 'L', 1, 0);

            $pdf->SetXY(80, 14);
            $pdf->SetFontSize(8);
            $pdf->SetFillColor(255,255,255);
            $pdf->SetTextColor(0,0,0);
            $pdf->Cell(13, 4, $folio, 0, 1, 'R', 1, 0);

                $pdf->SetXY(10, 20);
                $pdf->SetFontSize(8);
                $pdf->SetFillColor(255,255,255);
                $pdf->SetTextColor(0,0,0);
                $pdf->Cell(30, 4, "Proveedor: ", 0, 1, 'L', 1, 0);

                $pdf->SetTextColor(0,0,0);
                $pdf->MultiCell(50, 4, $proveedor, 0, 'L', true, 1, 32, 20);

if($oc)
{
            $pdf->SetXY(70, 20);
            $pdf->SetFontSize(8);
            $pdf->SetFillColor(255,255,255);
            $pdf->SetTextColor(0,0,0);
            $pdf->Cell(10, 4, "OC: ", 0, 1, 'L', 1, 0);

            $pdf->SetXY(80, 20);
            $pdf->SetFontSize(8);
            $pdf->SetFillColor(255,255,255);
            $pdf->SetTextColor(0,0,0);
            $pdf->Cell(13, 4, $oc, 0, 1, 'R', 1, 0);
}
                $pdf->SetXY(35, 30);
                $pdf->SetFontSize(8);
                $pdf->SetFillColor(0,0,0);
                $pdf->SetTextColor(255,255,255);
                $pdf->Cell(30, 4, "Pallet/Contenedor", 0, 1, 'C', 1, 0);

              $style = array(
                  'position'     => '',
                  'align'        => 'C',
                  'stretch'      => false,
                  'fitwidth'     => false,
                  'cellfitalign' => '',
                  'border'       => false,
                  'baseline'     => false,
                  'hpadding'     => 'auto',
                  'vpadding'     => 'auto',
                  'fgcolor'      => array(0, 0, 0),
                  'bgcolor'      => false,
                  'text'         => true,
                  'font'         => 'helvetica',
                  'fontsize'     => 10,
                  'stretchtext'  => 4
              );

                $pdf->write1DBarcode($lp_reg, 'C128', 15, 35, '70', '20', 0.6, $style, 'N');

            ?>
                <?php 
                $datos = "";
                //$rows = array();
                //ob_start(); 
                //$row = mysqli_fetch_array($res, MYSQLI_ASSOC);
                //$rows[] = $row;
                $row = $row_array[$i];
                $datos = $row;
                ?>
                    <table style="width:300px;">
                        <tr style="background-color: #000; color: #fff;line-height: 10px;">
                            <td colspan="2" style="width: 100px;">Clave</td>
                            <td colspan="2" style="width: 200px;">Descripción</td>
                        </tr>
                        <tr>
                            <td style="font-size: 12px;width: 100px;" colspan="2"><?php echo $datos["clave"]; ?></td>
                            <td style="font-size: 12px;width: 200px;" colspan="2"><?php echo $datos["descripcion"]; ?></td>
                        </tr>

                        <tr>
                            <td style="width: 100px;">Lote</td>
                            <td style="width: 100px;">Caducidad</td>
                            <td style="width: 50px;">Serie</td>
                            <td align="center" style="width: 50px;">Cantidad</td>
                        </tr>
                        <tr>
                            <td style="font-size: 12px;width: 100px;"><?php echo $datos["lote"]; ?></td>
                            <td style="font-size: 12px;width: 100px;"><?php echo $datos["caducidad"]; ?></td>
                            <td style="font-size: 12px;width: 50px;"><?php echo $datos["serie"]; ?></td>
                            <td style="font-size: 12px;width: 50px;" align="center"><?php echo $datos["cantidad_recibida"]; ?></td>
                        </tr>
                    </table>
                    <?php 
                  $desProducto = ob_get_clean();
                  $pdf->SetAutoPageBreak(TRUE, 5);
                  $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO); 
                  //$pdf->setMargins(0, 5, 0, 0);
                  $pdf->SetXY(10, 60);
                  $pdf->SetTextColor(0,0,0);
                  $pdf->SetFont('helvetica', '', '5px', '', 'default', true);
                  $pdf->WriteHTML($desProducto, true, false, true, '');
                  //$pdf->lastPage();
                  //$pdf->Output($filename, 'I');
                  //ob_end_clean();
                  //ob_end_flush();
                  //break;
            }
            $pdf->Output($filename, 'I');
          }

      }


    public function generarReporteEntradasOC($folio,$erp, $tipo)
    {
        $res = "";
        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

          $sql = "
               SELECT DISTINCT * FROM (
               SELECT th_entalmacen.fol_folio AS numero_oc,
               (SELECT MIN( DATE_FORMAT(td_entalmacen.fecha_inicio, '%d-%m-%Y')) FROM td_entalmacen WHERE fol_folio = th_entalmacen.Fol_Folio) AS fecha_recepcion, 
               c_proveedores.nombre AS proveedor, 
               th_entalmacen.fact_prov AS facprov, 
               (SELECT nombre FROM c_almacenp WHERE clave = th_entalmacen.Cve_Almac) AS nombre_almacen, 
               th_entalmacen.Cve_Almac AS almacen
               FROM th_entalmacen 
               LEFT JOIN c_usuario ON c_usuario.cve_usuario = th_entalmacen.cve_usuario 
               LEFT JOIN th_aduana ON th_aduana.num_pedimento = th_entalmacen.fol_folio 
               LEFT JOIN td_aduana ON td_aduana.num_orden = th_entalmacen.fol_folio 
               LEFT JOIN c_proveedores ON th_entalmacen.cve_proveedor = c_proveedores.id_proveedor 
               LEFT JOIN td_entalmacen ON th_entalmacen.fol_folio = td_entalmacen.fol_folio 
               LEFT JOIN c_articulo ON td_aduana.cve_articulo = c_articulo.cve_articulo 
               LEFT JOIN th_entalmacen_log ON th_entalmacen_log.fol_folio = th_entalmacen.fol_folio 
               LEFT JOIN c_presupuestos ON th_aduana.presupuesto = c_presupuestos.id 
               GROUP BY numero_oc, td_entalmacen.cantidadrecibida) A 
               WHERE A.numero_oc = $folio
          ";

          $res  = mysqli_query($conn, $sql);

          if(!$res)
          {
            echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
          }

          $datos_documento = mysqli_fetch_array($res, MYSQLI_ASSOC);

          $fecha_documento      = $datos_documento['fecha_recepcion'];
          $compania_depositante = $datos_documento['proveedor'];
          $factura              = $datos_documento['facprov'];
          $nombre_almacen       = $datos_documento['nombre_almacen'];
          $almacen              = $datos_documento['almacen'];

          $sql = "SELECT cve_cia FROM c_almacenp WHERE clave = $almacen";
          $res  = mysqli_query($conn, $sql);
          if(!$res){echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
          $cve_empresa = mysqli_fetch_array($res, MYSQLI_ASSOC);
          $cve_cia = $cve_empresa['cve_cia'];

          $sql = "SELECT imagen FROM c_compania WHERE cve_cia = $cve_cia";
          $res  = mysqli_query($conn, $sql);
          if(!$res){echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
          $imagen_empresa = mysqli_fetch_array($res, MYSQLI_ASSOC);
          $PDF_HEADER_LOGO = $imagen_empresa['imagen'];

        $filename = "Entradas Recepcion OC #{$folio}.pdf";
        $base_url = $_SERVER['DOCUMENT_ROOT'];
        //$imgLogosSP = $base_url."img/logos_sp.jpg";
        //$imgLogosSP = $base_url.$datos["logo"];
        //ob_start(); 
          $sql = "
               SELECT Operador, No_Unidad, Placas, Linea_Transportista, Observaciones, Sello, 
                      DATE_FORMAT(Fec_Ingreso, '%d-%m-%Y') Fecha_Ingreso, DATE_FORMAT(Fec_Salida, '%d-%m-%Y') Fecha_Salida, 
                      DATE_FORMAT(Fec_Ingreso, '%h:%i:%s %p') Hora_Ingreso, DATE_FORMAT(Fec_Salida, '%h:%i:%s %p') Hora_Salida 
                FROM t_entalmacentransporte WHERE Fol_Folio = $folio
          ";

          $res  = mysqli_query($conn, $sql);

          if(!$res)
          {
            echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
          }

          $datos_transporte = mysqli_fetch_array($res, MYSQLI_ASSOC);

          $numero_placa        = $datos_transporte['Placas'];
          $Fecha_Recibo        = $datos_transporte['Fecha_Ingreso'];
          $Linea_Transportista = $datos_transporte['Linea_Transportista'];
          $Operador            = $datos_transporte['Operador'];
          $No_Unidad           = $datos_transporte['No_Unidad'];
          $Hora_Ingreso        = $datos_transporte['Hora_Ingreso'];
          $Hora_Salida         = $datos_transporte['Hora_Salida'];

            //ob_start(); 
                                                              //array(152.4, 101.6)
            $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT , 'A4', true, 'UTF-8', false);
            //$pdf->SetCreator('wms');
            //$pdf->SetAuthor('wms');
            $pdf->SetTitle('Reporte de Recepción OC');
            //setHeaderData($ln='', $lw=0, $ht='', $hs='', $tc=array(0,0,0), $lc=array(0,0,0))
            $pdf->SetHeaderData('', 0, '', '', array(255, 255, 255), array(255, 255, 255));
            //setFooterData($tc=array(0,0,0), $lc=array(0,0,0))
            $pdf->setFooterData(array(255,255,255), array(255,255,255));
//$pdf->SetHeaderMargin(26);
//$pdf->SetHeaderData($PDF_HEADER_LOGO, $PDF_HEADER_LOGO_WIDTH, $PDF_HEADER_TITLE, $PDF_HEADER_STRING);
//$pdf->SetHeaderData($PDF_HEADER_LOGO, 0, '', '', array(0, 0, 0), array(0, 0, 0));

            $pdf->AddPage();
            $pdf->SetXY(10, 6);
            $pdf->SetFillColor(255,255,255);
            $pdf->SetTextColor(0,0,0);
            //$pdf->SetLineWidth(3);
            //$pdf->SetFontSize(12);
            $pdf->Cell(192, 1, "", 0, 0, 'C', 2, '', 0);
            $pdf->Image($PDF_HEADER_LOGO, 6, 6, 60, 40, '', '', 'center', false, 150, '', false, false, 0, false, false, false);

//*******************************************************************************************
            $pdf->SetXY(95, 14);
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->SetFillColor(255,255,255);
            $pdf->SetTextColor(0,0,0);
            $pdf->Cell(100, 4, "Fecha de Documento", 0, 1, 'L', 1, 0);

            $pdf->SetXY(140, 14);
            $pdf->SetFont('helvetica', '', 10);
            $pdf->SetFillColor(255,255,255);
            $pdf->SetTextColor(0,0,0);
            $pdf->Cell(55, 4, "_____________________________", 0, 1, 'C', 1, 0);

            $pdf->SetXY(140, 13);
            $pdf->SetFont('helvetica', '', 10);
            $pdf->SetFillColor(255,255,255);
            $pdf->SetTextColor(0,0,0);
            $pdf->Cell(55, 3, $fecha_documento, 0, 1, 'C', 1, 0);

//*******************************************************************************************

            $pdf->SetXY(95, 25);
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->SetFillColor(255,255,255);
            $pdf->SetTextColor(0,0,0);
            if($tipo == "DV" || $tipo == "DVL" || $tipo == "DVP" || $tipo == "DPL")
                $pdf->Cell(100, 4, "# de Folio de Devolución", 0, 1, 'L', 1, 0);
            else    
                $pdf->Cell(100, 4, "# de Folio de Recibo", 0, 1, 'L', 1, 0);

            $pdf->SetXY(140, 25);
            $pdf->SetFont('helvetica', '', 10);
            $pdf->SetFillColor(255,255,255);
            $pdf->SetTextColor(0,0,0);
            $pdf->Cell(55, 4, "_____________________________", 0, 1, 'C', 1, 0);

            $pdf->SetXY(140, 24);
            $pdf->SetFont('helvetica', '', 10);
            $pdf->SetFillColor(255,255,255);
            $pdf->SetTextColor(0,0,0);
            $pdf->Cell(55, 3, $folio, 0, 1, 'C', 1, 0);

//*******************************************************************************************

            $pdf->SetXY(0, 50);
            $pdf->SetFont('helvetica', '', 14);
            $pdf->SetFillColor(255,255,255);
            $pdf->SetTextColor(204, 204, 204);
            if($tipo == "DV" || $tipo == "DVL" || $tipo == "DVP" || $tipo == "DPL")
                $pdf->Cell(210, 4, "AVISO DE DEVOLUCIÓN DE MATERIALES", 0, 1, 'C', 1, 0);
            else
                $pdf->Cell(210, 4, "AVISO DE RECIBO DE MATERIALES", 0, 1, 'C', 1, 0);

//*******************************************************************************************

            $pdf->SetXY(20, 70);
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->SetFillColor(255,255,255);
            $pdf->SetTextColor(0,0,0);
            $pdf->Cell(50, 4, "Compañía Depositante", 0, 1, 'L', 1, 0);

            $pdf->SetXY(70, 70);
            $pdf->SetFont('helvetica', '', 10);
            $pdf->SetFillColor(255,255,255);
            $pdf->SetTextColor(0,0,0);
            $pdf->Cell(125, 4, "_________________________________________________________________", 0, 1, 'C', 1, 0);

            $pdf->SetXY(70, 69);
            $pdf->SetFont('helvetica', '', 10);
            $pdf->SetFillColor(255,255,255);
            $pdf->SetTextColor(0,0,0);
            $pdf->Cell(125, 3, $compania_depositante, 0, 1, 'C', 1, 0);

//*******************************************************************************************
            $pdf->SetXY(30, 85);
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->SetFillColor(255,255,255);
            $pdf->SetTextColor(0,0,0);
            $pdf->Cell(40, 4, "# OC", 0, 1, 'C', 1, 0);

            $pdf->SetXY(70, 85);
            $pdf->SetFont('helvetica', '', 10);
            $pdf->SetFillColor(255,255,255);
            $pdf->SetTextColor(0,0,0);
            $pdf->Cell(45, 4, "____________________", 0, 1, 'C', 1, 0);

            $pdf->SetXY(70, 84);
            $pdf->SetFont('helvetica', '', 10);
            $pdf->SetFillColor(255,255,255);
            $pdf->SetTextColor(0,0,0);
            $pdf->Cell(45, 3, $erp, 0, 1, 'C', 1, 0);

//*********

            $pdf->SetXY(115, 85);
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->SetFillColor(255,255,255);
            $pdf->SetTextColor(0,0,0);
            $pdf->Cell(40, 4, "# Factura", 0, 1, 'C', 1, 0);

            $pdf->SetXY(155, 85);
            $pdf->SetFont('helvetica', '', 10);
            $pdf->SetFillColor(255,255,255);
            $pdf->SetTextColor(0,0,0);
            $pdf->Cell(45, 4, "____________________", 0, 1, 'C', 1, 0);

            $pdf->SetXY(155, 84);
            $pdf->SetFont('helvetica', '', 10);
            $pdf->SetFillColor(255,255,255);
            $pdf->SetTextColor(0,0,0);
            $pdf->Cell(45, 3, $factura, 0, 1, 'C', 1, 0);

//*******************************************************************************************
            
            $pdf->SetXY(30, 100);
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->SetFillColor(255,255,255);
            $pdf->SetTextColor(0,0,0);
            $pdf->Cell(40, 4, "Almacén de Entrada", 0, 1, 'C', 1, 0);

            $pdf->SetXY(70, 100);
            $pdf->SetFont('helvetica', '', 10);
            $pdf->SetFillColor(255,255,255);
            $pdf->SetTextColor(0,0,0);
            $pdf->Cell(45, 4, "____________________", 0, 1, 'C', 1, 0);

            $pdf->SetXY(70, 99);
            $pdf->SetFont('helvetica', '', 10);
            $pdf->SetFillColor(255,255,255);
            $pdf->SetTextColor(0,0,0);
            $pdf->Cell(45, 3, $nombre_almacen, 0, 1, 'C', 1, 0);

//*********

            $pdf->SetXY(115, 100);
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->SetFillColor(255,255,255);
            $pdf->SetTextColor(0,0,0);
            $pdf->Cell(40, 4, "# Almacén", 0, 1, 'C', 1, 0);

            $pdf->SetXY(155, 100);
            $pdf->SetFont('helvetica', '', 10);
            $pdf->SetFillColor(255,255,255);
            $pdf->SetTextColor(0,0,0);
            $pdf->Cell(45, 4, "____________________", 0, 1, 'C', 1, 0);

            $pdf->SetXY(155, 99);
            $pdf->SetFont('helvetica', '', 10);
            $pdf->SetFillColor(255,255,255);
            $pdf->SetTextColor(0,0,0);
            $pdf->Cell(45, 3, $almacen, 0, 1, 'C', 1, 0);

//*******************************************************************************************

            $pdf->SetXY(30, 115);
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->SetFillColor(255,255,255);
            $pdf->SetTextColor(0,0,0);
            if($tipo == "DV" || $tipo == "DVL" || $tipo == "DVP" || $tipo == "DPL")
                $pdf->Cell(40, 4, "Fecha de Devolución", 0, 1, 'C', 1, 0);
            else
                $pdf->Cell(40, 4, "Fecha de Recibo", 0, 1, 'C', 1, 0);

            $pdf->SetXY(70, 115);
            $pdf->SetFont('helvetica', '', 10);
            $pdf->SetFillColor(255,255,255);
            $pdf->SetTextColor(0,0,0);
            $pdf->Cell(45, 4, "____________________", 0, 1, 'C', 1, 0);

            $pdf->SetXY(70, 114);
            $pdf->SetFont('helvetica', '', 10);
            $pdf->SetFillColor(255,255,255);
            $pdf->SetTextColor(0,0,0);
            $pdf->Cell(45, 3, $Fecha_Recibo, 0, 1, 'C', 1, 0);

//********

            $pdf->SetXY(115, 115);
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->SetFillColor(255,255,255);
            $pdf->SetTextColor(0,0,0);
            $pdf->Cell(40, 4, "Linea Transportista", 0, 1, 'C', 1, 0);

            $pdf->SetXY(155, 115);
            $pdf->SetFont('helvetica', '', 10);
            $pdf->SetFillColor(255,255,255);
            $pdf->SetTextColor(0,0,0);
            $pdf->Cell(45, 4, "____________________", 0, 1, 'C', 1, 0);

            $pdf->SetXY(155, 114);
            $pdf->SetFont('helvetica', '', 10);
            $pdf->SetFillColor(255,255,255);
            $pdf->SetTextColor(0,0,0);
            $pdf->Cell(45, 3, $Linea_Transportista, 0, 1, 'C', 1, 0);

//*******************************************************************************************

            $pdf->SetXY(30, 130);
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->SetFillColor(255,255,255);
            $pdf->SetTextColor(0,0,0);
            $pdf->Cell(40, 4, "Nombre Operador", 0, 1, 'C', 1, 0);

            $pdf->SetXY(70, 130);
            $pdf->SetFont('helvetica', '', 10);
            $pdf->SetFillColor(255,255,255);
            $pdf->SetTextColor(0,0,0);
            $pdf->Cell(45, 4, "____________________", 0, 1, 'C', 1, 0);

            $pdf->SetXY(70, 129);
            $pdf->SetFont('helvetica', '', 10);
            $pdf->SetFillColor(255,255,255);
            $pdf->SetTextColor(0,0,0);
            $pdf->Cell(45, 3, $Operador, 0, 1, 'C', 1, 0);

//*********

            $pdf->SetXY(115, 130);
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->SetFillColor(255,255,255);
            $pdf->SetTextColor(0,0,0);
            $pdf->Cell(40, 4, "# de Unidad", 0, 1, 'C', 1, 0);

            $pdf->SetXY(155, 130);
            $pdf->SetFont('helvetica', '', 10);
            $pdf->SetFillColor(255,255,255);
            $pdf->SetTextColor(0,0,0);
            $pdf->Cell(45, 4, "____________________", 0, 1, 'C', 1, 0);

            $pdf->SetXY(155, 129);
            $pdf->SetFont('helvetica', '', 10);
            $pdf->SetFillColor(255,255,255);
            $pdf->SetTextColor(0,0,0);
            $pdf->Cell(45, 3, $No_Unidad, 0, 1, 'C', 1, 0);

//*******************************************************************************************

            $pdf->SetXY(30, 145);
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->SetFillColor(255,255,255);
            $pdf->SetTextColor(0,0,0);
            $pdf->Cell(40, 4, "Hora Entrada Unidad", 0, 1, 'C', 1, 0);

            $pdf->SetXY(70, 145);
            $pdf->SetFont('helvetica', '', 10);
            $pdf->SetFillColor(255,255,255);
            $pdf->SetTextColor(0,0,0);
            $pdf->Cell(45, 4, "____________________", 0, 1, 'C', 1, 0);

            $pdf->SetXY(70, 144);
            $pdf->SetFont('helvetica', '', 10);
            $pdf->SetFillColor(255,255,255);
            $pdf->SetTextColor(0,0,0);
            $pdf->Cell(45, 3, $Hora_Ingreso, 0, 1, 'C', 1, 0);

//*********

            $pdf->SetXY(115, 145);
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->SetFillColor(255,255,255);
            $pdf->SetTextColor(0,0,0);
            $pdf->Cell(40, 4, "Hora Salida Unidad", 0, 1, 'C', 1, 0);

            $pdf->SetXY(155, 145);
            $pdf->SetFont('helvetica', '', 10);
            $pdf->SetFillColor(255,255,255);
            $pdf->SetTextColor(0,0,0);
            $pdf->Cell(45, 4, "____________________", 0, 1, 'C', 1, 0);

            $pdf->SetXY(155, 144);
            $pdf->SetFont('helvetica', '', 10);
            $pdf->SetFillColor(255,255,255);
            $pdf->SetTextColor(0,0,0);
            $pdf->Cell(45, 3, $Hora_Salida, 0, 1, 'C', 1, 0);

//*******************************************************************************************

            $pdf->SetXY(0, 160);
            $pdf->SetFont('helvetica', '', 14);
            $pdf->SetFillColor(255,255,255);
            $pdf->SetTextColor(204, 204, 204);
            if($tipo == "DV" || $tipo == "DVL" || $tipo == "DVP" || $tipo == "DPL")
                $pdf->Cell(210, 4, "DETALLE DE MERCANCIA DEVUELTA", 0, 1, 'C', 1, 0);
            else
                $pdf->Cell(210, 4, "DETALLE DE MERCANCIA RECIBIDA", 0, 1, 'C', 1, 0);

//*******************************************************************************************

            $datos = "";
            $rows = array();
            ob_start(); 
            
            
            ?>
                <style>
                    .td1 {
                        width: 50px;
                    }
                    .td2 {
                        width: 80px;
                    }
                    .td3 {
                        width: 180px;
                    }
                    .td4 {
                        width: 100px;
                    }

                    .th
                    {
                        color: #ccc;
                        font-size: 12px;
                        font-weight: bold;
                        height: 70px;
                        line-height: 1.2;
                        text-align: center;
                    }

                    .td
                    {
                        height: 50px;
                        text-align: center;
                    }

                    .td3 div
                    {
                        width: 80%;
                    }

                    table, tr, td
                    {
                        border: 1px solid #ccc;
                    }

                    
                </style>
                <table>
                        <tr>
                            <td class="th td1"><div><br><br>Par</div></td>
                            <td class="th td2"><div><br><br># SKU</div></td>
                            <td class="th td3"><div><br><br>Descripción Producto</div></td>
                            <td class="th td2"><div><br><br>Cantidad Solicitada</div></td>
                            <td class="th td2"><div><br><br>
                                <?php 
                            if($tipo == "DV" || $tipo == "DVL" || $tipo == "DVP" || $tipo == "DPL") 
                                echo "Cantidad Devuelta";
                            else
                                echo "Cantidad Recibida";
                                ?></div></td>
                            <td class="th td2"><div><br><br>Pendiente | Dañado</div></td>
                            <td class="th td4"><div><br><br>Observaciones / Variaciones</div></td>
                        </tr>

<?php 
    $i = 1;
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $sql = "SELECT DISTINCT
            tde.id AS id,
            tde.cve_articulo AS clave,
            a.des_articulo AS descripcion,
            IFNULL(a.control_lotes, 'N') AS band_lote,
            IFNULL(a.Caduca, 'N') AS band_caducidad,
            IFNULL(a.control_numero_series, 'N') AS band_serie,
            #tde.CantidadPedida AS cantidad_pedida,
            TRUNCATE((IF(IFNULL(a.num_multiplo, 0) = 0, tde.CantidadPedida, tde.CantidadPedida/a.num_multiplo)), 0) AS cantidad_pedida,
            tde.cve_lote AS lote,
            tde.numero_serie AS serie,
            DATE_FORMAT(c_lotes.Caducidad, '%d-%m-%Y') AS caducidad,
            tde.status AS STATUS,
            #TRUNCATE((IF(IFNULL(a.num_multiplo, 0) = 0, tde.CantidadRecibida, tde.CantidadRecibida/a.num_multiplo)), 2) AS cantidad_recibida,
            TRUNCATE((IF(IFNULL(a.num_multiplo, 0) = 0, tde.CantidadRecibida, tde.CantidadRecibida/a.num_multiplo)), 0) AS cantidad_recibida,
            DATE_FORMAT(tde.fecha_inicio, '%d-%m-%Y') AS fecha_recepcion,
            DATE_FORMAT(tde.fecha_inicio, '%d-%m-%Y %h:%i:%s %p') AS fecha_inicio,
            DATE_FORMAT(tde.fecha_fin, '%d-%m-%Y %h:%i:%s %p') AS fecha_fin,
            #tde.CantidadPedida - (IF(IFNULL(a.num_multiplo, 0) = 0, tde.CantidadRecibida, tde.CantidadRecibida/a.num_multiplo)) AS cantidad_faltante,
            (IF(IFNULL(a.num_multiplo, 0) = 0, tde.CantidadPedida - tde.CantidadRecibida, (tde.CantidadPedida - tde.CantidadRecibida)/a.num_multiplo)) AS cantidad_faltante,
            #TRUNCATE(tde.CantidadPedida - (IF(IFNULL(a.num_multiplo, 0) = 0, tde.CantidadRecibida, tde.CantidadRecibida/a.num_multiplo)), 2) AS cantidad_danada,
            TRUNCATE((IF(IFNULL(a.num_multiplo, 0) = 0, tde.CantidadPedida - tde.CantidadRecibida, (tde.CantidadPedida - tde.CantidadRecibida)/a.num_multiplo)), 0) AS cantidad_danada,
            u.cve_usuario AS usuario,
            IF(c_charolas.CveLP != '',c_charolas.CveLP, '') AS pallet,
            ta.ClaveEtiqueta AS contenedor
      FROM td_entalmacen tde
          LEFT JOIN td_aduana tda ON tda.cve_articulo = tde.cve_articulo AND tda.num_orden = tde.num_orden
          LEFT JOIN c_articulo a ON a.cve_articulo = tde.cve_articulo
          LEFT JOIN c_usuario u ON u.cve_usuario = tde.cve_usuario
          LEFT JOIN th_entalmacen ON th_entalmacen.Fol_Folio = tde.fol_folio
          LEFT JOIN c_lotes ON c_lotes.LOTE = tde.cve_lote AND c_lotes.cve_articulo = tde.cve_articulo
          LEFT JOIN td_entalmacenxtarima ta ON ta.fol_folio = tde.fol_folio AND tde.cve_articulo = ta.cve_articulo AND tde.cve_lote = ta.cve_lote AND tde.CantidadRecibida = ta.Cantidad
    LEFT JOIN c_charolas ON c_charolas.clave_contenedor = ta.ClaveEtiqueta
      WHERE tde.fol_folio = '$folio'";

      $sql_order = $sql." ORDER BY clave DESC";
      //LEFT JOIN c_articulo a ON a.cve_articulo = tde.cve_articulo AND cl.cve_articulo=a.cve_articulo

            $res = mysqli_query($conn, $sql_order);

            $par_defectuoso = array();
            $ids_defectuoso = array();

            $clave_actual = ""; $num_rows = 1; $i_rows = 1;
            $total_cajas = 0;
            while($row = mysqli_fetch_array($res, MYSQLI_ASSOC))
            {
                if($clave_actual != $row['clave'])
                {
                    $res_count = mysqli_query($conn, $sql." AND tde.cve_articulo = '".$row['clave']."'");
                    $num_rows = mysqli_num_rows($res_count);
                    $i_rows = 1;
                }
                else
                {
                    $num_rows = 1;
                }

                $lote = $row['lote'];
                $caducidad = $row['caducidad'];
                $serie = $row['serie'];

                if($row['band_serie'] == 'S')
                {
                    $serie = $lote;
                    $lote = "";
                    $caducidad = "";
                }
                else if($row['band_lote'] == 'S')
                {
                    $serie = "";
                    if($row['band_caducidad'] == 'N') $caducidad = "";
                }


                $id = $row['id'];
                $sql2 = "SELECT DISTINCT descripcion FROM td_entalmacen_fotos WHERE td_entalmacen_producto_id = $id";
                $res2 = mysqli_query($conn, $sql2);
                $row2 = mysqli_fetch_array($res2, MYSQLI_ASSOC);
?>
                <tr>
                    <td class="td td1"><div><br><?php echo $i; ?></div></td>
                    <td class="td td2"><div><br><?php echo $row['clave']; ?></div></td>
                    <td class="td td3"><div><br><?php 
                        echo utf8_encode($row['descripcion'])."<br>";
                        if($lote && $lote != " ")
                            echo "Lote: ".$lote."<br>"; 
                        if($caducidad)
                            echo "Caducidad: ".$caducidad."<br>"; 
                        if($serie)
                            echo "Serie: ".$serie."<br>"; 
                        ?> </div></td>
                    <td class="td td2"><div><br><?php echo $row['cantidad_pedida']; ?></div></td>
                    <td class="td td2"><div><br><?php echo $row['cantidad_recibida']; $total_cajas += $row['cantidad_recibida']; ?></div></td>
                    <td class="td td2"><div><br><?php
                        $cantidad_danada = 0;
                        if($tipo == "DV" || $tipo == "DVL" || $tipo == "DVP" || $tipo == "DPL")
                        {
                            $sql3 = "SELECT defectuoso, cantidad_devuelta FROM c_devclientes WHERE folio_entrada = '{$folio}'";
                            if($tipo == "DVP" || $tipo == "DPL")
                                $sql3 = "SELECT defectuoso, devueltas as cantidad_devuelta FROM c_devproveedores WHERE folio_entrada = '{$folio}'";
                            $res3 = mysqli_query($conn, $sql3);
                            $row3 = mysqli_fetch_array($res3, MYSQLI_ASSOC);
                            $defectuoso = $row3['defectuoso'];
                            if($defectuoso == 1)
                               $cantidad_danada = $row3['cantidad_devuelta'];
                        }
                        else /*if($i_rows == $num_rows) {*/
                        if($row['cantidad_danada']) $cantidad_danada = $row['cantidad_danada']; else $cantidad_danada = 0;
                        //} 
                         //else if(!$row['cantidad_danada']) $cantidad_danada = 0;

                         echo $cantidad_danada;
                    ?></div></td>
                    <td class="td td4"><div><br><?php 
                        if($row2['descripcion'])
                        {
                            $descripcion_defectuoso = $row2['descripcion'];
                            array_push($par_defectuoso,$i);
                            array_push($ids_defectuoso,$id);
                        } 
                        if($i_rows == $num_rows) {echo $descripcion_defectuoso; $i_rows = 1;}

                        ?></div></td>
                </tr>
<?php
                $i_rows++;
                $i++; 
            }
?>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td class="td td2"><br><br>TOTAL</td>
                    <td class="td td2"><br><br><?php echo $total_cajas; ?></td>
                    <td></td>
                    <td></td>
                </tr>
                </table>
                <?php 
              $desProducto = ob_get_clean();
              $pdf->SetAutoPageBreak(TRUE, 20);
              $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO); 
              //$pdf->setMargins(0, 5, 0, 0);
              $pdf->SetXY(15, 180);
              $pdf->SetTextColor(0,0,0);
              $pdf->SetFont('helvetica', '', '10px', '', 'default', true);
              @$pdf->writeHTML($desProducto, true, false, true, false, '');

//*******************************************************************************************
            $pdf->AddPage();
            $pdf->SetXY(10, 6);
            $pdf->SetFillColor(255,255,255);
            $pdf->SetTextColor(0,0,0);
            //$pdf->SetLineWidth(3);
            //$pdf->SetFontSize(12);
            $pdf->Cell(192, 1, "", 0, 0, 'C', 2, '', 0);
            $pdf->Image($PDF_HEADER_LOGO, 6, 6, 60, 40, '', '', 'center', false, 150, '', false, false, 0, false, false, false);

//*******************************************************************************************
            $pdf->SetXY(95, 14);
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->SetFillColor(255,255,255);
            $pdf->SetTextColor(0,0,0);
            $pdf->Cell(100, 4, "Fecha de Documento", 0, 1, 'L', 1, 0);

            $pdf->SetXY(140, 14);
            $pdf->SetFont('helvetica', '', 10);
            $pdf->SetFillColor(255,255,255);
            $pdf->SetTextColor(0,0,0);
            $pdf->Cell(55, 4, "_____________________________", 0, 1, 'C', 1, 0);

            $pdf->SetXY(140, 13);
            $pdf->SetFont('helvetica', '', 10);
            $pdf->SetFillColor(255,255,255);
            $pdf->SetTextColor(0,0,0);
            $pdf->Cell(55, 3, $fecha_documento, 0, 1, 'C', 1, 0);

//*******************************************************************************************

            $pdf->SetXY(95, 25);
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->SetFillColor(255,255,255);
            $pdf->SetTextColor(0,0,0);

            if($tipo == "DV" || $tipo == "DVL" || $tipo == "DVP" || $tipo == "DPL")
                $pdf->Cell(100, 4, "# de Folio de Devolución", 0, 1, 'L', 1, 0);
            else
                $pdf->Cell(100, 4, "# de Folio de Recibo", 0, 1, 'L', 1, 0);

            $pdf->SetXY(140, 25);
            $pdf->SetFont('helvetica', '', 10);
            $pdf->SetFillColor(255,255,255);
            $pdf->SetTextColor(0,0,0);
            $pdf->Cell(55, 4, "_____________________________", 0, 1, 'C', 1, 0);

            $pdf->SetXY(140, 24);
            $pdf->SetFont('helvetica', '', 10);
            $pdf->SetFillColor(255,255,255);
            $pdf->SetTextColor(0,0,0);
            $pdf->Cell(55, 3, $folio, 0, 1, 'C', 1, 0);

//*******************************************************************************************

            $pdf->SetXY(0, 50);
            $pdf->SetFont('helvetica', '', 14);
            $pdf->SetFillColor(255,255,255);
            $pdf->SetTextColor(204, 204, 204);
            $pdf->Cell(210, 4, "INSPECCIÓN DEL CAMIÓN", 0, 1, 'C', 1, 0);

// Image($file, $x='', $y='', $w=0, $h=0, $type='', $link='', $align='', $resize=false, $dpi=300, $palign='', $ismask=false, $imgmask=false, $border=0, $fitbox=false, $hidden=false, $fitonpage=false)

            $sql = "SELECT id, th_entalmacen_folio, ruta, descripcion, type, foto FROM th_entalmacen_fotos WHERE th_entalmacen_folio = $folio";
            $res = mysqli_query($conn, $sql);

            $x = 18; $y = 70; $w = 50; $h = 55; $fotosxlinea = 0;
            $pdf->SetFont('helvetica', '', 10);
            $pdf->SetFillColor(255,255,255);
            $pdf->SetDrawColor(204, 204, 204);
            $pdf->SetTextColor(0,0,0);

            while($row = mysqli_fetch_array($res, MYSQLI_ASSOC))
            {
                $pdf->Image($row['ruta'], $x, $y, $w, $h, '', '', 'center', false, 150, '', false, false, 1, false, false, false);
                //$pdf->SetXY($x, $y+$h);
                //$pdf->MultiCell($w, 20, utf8_encode($row['descripcion']), 1, 1, 'C', 1, 0);
                //$pdf->MultiCell(50, 4, $proveedor, 0, 'L', true, 1, 32, 20);
                $pdf->MultiCell($w, 20, utf8_encode($row['descripcion']), 1, 'C', true, 1, $x, $y+$h);

                $x += $w+10;

                $fotosxlinea++;
                if($fotosxlinea == 3)
                {
                    $x = 18;
                    $y += $h+30;
                    $fotosxlinea = 0;
                }

            }

//*******************************************************************************************
            for($i = 0; $i < count($ids_defectuoso); $i++)
            {
                $pdf->AddPage();
                $pdf->SetXY(10, 6);
                $pdf->SetFillColor(255,255,255);
                $pdf->SetTextColor(0,0,0);
                //$pdf->SetLineWidth(3);
                //$pdf->SetFontSize(12);
                $pdf->Cell(192, 1, "", 0, 0, 'C', 2, '', 0);
                $pdf->Image($PDF_HEADER_LOGO, 6, 6, 60, 40, '', '', 'center', false, 150, '', false, false, 0, false, false, false);

    //*******************************************************************************************
                $pdf->SetXY(95, 14);
                $pdf->SetFont('helvetica', 'B', 10);
                $pdf->SetFillColor(255,255,255);
                $pdf->SetTextColor(0,0,0);
                $pdf->Cell(100, 4, "Fecha de Documento", 0, 1, 'L', 1, 0);

                $pdf->SetXY(140, 14);
                $pdf->SetFont('helvetica', '', 10);
                $pdf->SetFillColor(255,255,255);
                $pdf->SetTextColor(0,0,0);
                $pdf->Cell(55, 4, "_____________________________", 0, 1, 'C', 1, 0);

                $pdf->SetXY(140, 13);
                $pdf->SetFont('helvetica', '', 10);
                $pdf->SetFillColor(255,255,255);
                $pdf->SetTextColor(0,0,0);
                $pdf->Cell(55, 3, $fecha_documento, 0, 1, 'C', 1, 0);

    //*******************************************************************************************

                $pdf->SetXY(95, 25);
                $pdf->SetFont('helvetica', 'B', 10);
                $pdf->SetFillColor(255,255,255);
                $pdf->SetTextColor(0,0,0);
                if($tipo == "DV" || $tipo == "DVL" || $tipo == "DVP" || $tipo == "DPL")
                    $pdf->Cell(100, 4, "# de Folio de Devolución", 0, 1, 'L', 1, 0);
                else
                    $pdf->Cell(100, 4, "# de Folio de Recibo", 0, 1, 'L', 1, 0);

                $pdf->SetXY(140, 25);
                $pdf->SetFont('helvetica', '', 10);
                $pdf->SetFillColor(255,255,255);
                $pdf->SetTextColor(0,0,0);
                $pdf->Cell(55, 4, "_____________________________", 0, 1, 'C', 1, 0);

                $pdf->SetXY(140, 24);
                $pdf->SetFont('helvetica', '', 10);
                $pdf->SetFillColor(255,255,255);
                $pdf->SetTextColor(0,0,0);
                $pdf->Cell(55, 3, $folio, 0, 1, 'C', 1, 0);

    //*******************************************************************************************
                $pdf->SetXY(0, 50);
                $pdf->SetFont('helvetica', '', 14);
                $pdf->SetFillColor(255,255,255);
                $pdf->SetTextColor(204, 204, 204);
                $pdf->Cell(210, 4, "ARTÍCULOS DAÑADOS", 0, 1, 'C', 1, 0);

                //$pdf->SetXY(0, 100);
                //$pdf->Cell(210, 4, "par_defectuoso = ".$par_defectuoso[1], 0, 1, 'C', 1, 0);
                //$pdf->Image($row['ruta'], $x, $y, $w, $h, '', '', 'center', false, 150, '', false, false, 1, false, false, false);
                

                //$ids = implode(",", $ids_defectuoso);

                $x = 18; $y = 70; $w = 50; $h = 55; $h_multicell = 30; $fotosxlinea = 0;
                $id = $ids_defectuoso[$i];
                $sql = "SELECT id, td_entalmacen_producto_id, ruta, descripcion, type, foto FROM td_entalmacen_fotos WHERE td_entalmacen_producto_id = $id";
                $res = mysqli_query($conn, $sql);

                $sql_clave = "SELECT cve_articulo FROM td_entalmacen WHERE id = $id";
                $res_clave = mysqli_query($conn, $sql_clave);
                $row_clave = mysqli_fetch_array($res_clave, MYSQLI_ASSOC);
                $clave = $row_clave["cve_articulo"];

                $sql_des_art = "SELECT des_articulo FROM c_articulo WHERE cve_articulo = '$clave'";
                $res_des_art = mysqli_query($conn, $sql_des_art);
                $row_des_art = mysqli_fetch_array($res_des_art, MYSQLI_ASSOC);
                $des_art = $row_des_art["des_articulo"];

                $texto_multicell  = "Par ".$par_defectuoso[$i]."\n";
                $texto_multicell .= "Clave ".$clave."\n";
                $texto_multicell .= utf8_encode($des_art)."\n";
                $pdf->SetFont('helvetica', '', 17);
                $pdf->SetFillColor(255,255,255);
                $pdf->SetTextColor(0,0,0);
                $pdf->MultiCell(180, $h_multicell, $texto_multicell, 0, 'C', true, 1, $x, $y);

                while($row = mysqli_fetch_array($res, MYSQLI_ASSOC))
                {
                    $pdf->Image($row['ruta'], $x, $y+$h_multicell, $w, $h, '', '', 'center', false, 150, '', false, false, 0, false, false, false);
                    $x += $w+10;

                    $fotosxlinea++;
                    if($fotosxlinea == 3)
                    {
                        $x = 18;
                        $y += $h+30;
                        $fotosxlinea = 0;
                    }
                }
                //$x = 18;
                //$y += $h+60;
                //$fotosxlinea = 0;
            }


//*******************************************************************************************


              $pdf->Output($filename, 'I');

      }

    public function generarReporteEntradasInformePedidos($folio, $consolidado)
    {
        $pdf_upca=new \PDF_EAN13();

        $res = ""; $res2 = "";
        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

        //if($consolidado == 1)
        //$where = "tdp.Fol_folio = '$folio'";
/*
        $where = "";
        if($consolidado == 1)
        {
              $sql2 = "SELECT DISTINCT Fol_PedidoCon FROM td_consolidado WHERE Fol_Folio = '$folio'";
              $res2  = mysqli_query($conn, $sql2);
              $where = "XYZ";
              while($row = mysqli_fetch_array($res2, MYSQLI_ASSOC))
              {
                    $where .= " OR tdp.Fol_folio = '".$row['Fol_PedidoCon']."'";
              }
              //$folio = $Fol_PedidoCon['Fol_PedidoCon'];
              $where = str_replace("XYZ OR ", "", $where);
        }
*/
/*
        if($consolidado == 0)
        {
              $sql2 = "SELECT DISTINCT Fol_Folio FROM td_consolidado WHERE Fol_PedidoCon = '$folio'";
              $res2  = mysqli_query($conn, $sql2);
              $where = "XYZ";
              while($row = mysqli_fetch_array($res2, MYSQLI_ASSOC))
              {
                    $where .= " OR tdp.Fol_folio = '".$row['Fol_Folio']."'";
              }
              //$folio = $Fol_PedidoCon['Fol_PedidoCon'];
              $where = str_replace("XYZ OR ", "", $where);
        }
*/
        if($consolidado)
          $sql = "
            SELECT DISTINCT 
              tdp.fol_folio AS folio,
              0 AS sufijo,
              c.RazonSocial AS cliente,
              tdp.Cve_articulo AS clave,
              a.des_articulo AS articulo,
              cac.Descripcion AS Descripcion,
              cac.Codigo AS SKU,
              cac.Sku_R AS Sku_R,
              SUM(ROUND((a.peso * tdp.Num_cantidad),4)) AS peso,
              SUM(ROUND(((a.alto/1000) * (a.ancho/1000) * (a.fondo/1000) * tdp.Num_cantidad),4)) AS volumen,
              SUM(tdp.Num_cantidad) AS Pedido_Total,
              (SELECT SUM(Existencia) AS Existencia_Total FROM VS_ExistenciaParaSurtido WHERE VS_ExistenciaParaSurtido.cve_articulo = tdp.Cve_articulo GROUP BY cve_articulo) AS Existencia_Total
          FROM td_pedido tdp
              LEFT JOIN c_articulo a ON a.cve_articulo = tdp.Cve_articulo
              LEFT JOIN th_pedido ON th_pedido.Fol_folio = tdp.Fol_folio
              LEFT JOIN c_cliente c ON c.Cve_Clte = th_pedido.Cve_clte
              LEFT JOIN c_articulo_codigo cac ON cac.Cve_Articulo = tdp.Cve_articulo AND cac.Cve_Clte = th_pedido.Cve_clte
              LEFT JOIN td_surtidopiezas s ON s.fol_folio = tdp.Fol_folio AND s.Cve_articulo = tdp.Cve_articulo AND s.cve_almac = th_pedido.cve_almac
              LEFT JOIN (
                  SELECT 
                      V_ExistenciaGral.cve_articulo,
                      c_ubicacion.CodigoCSD,
                      MIN(V_ExistenciaGral.cve_ubicacion) AS MIN 
                  FROM V_ExistenciaGral 
                      LEFT JOIN c_ubicacion ON V_ExistenciaGral.cve_ubicacion = c_ubicacion.idy_ubica 
                  GROUP BY cve_articulo
              ) x ON x.cve_articulo = tdp.Cve_articulo
          WHERE tdp.Fol_folio = '$folio'  GROUP BY clave
          ";
        else
          $sql = "
            SELECT DISTINCT 
              tdp.fol_folio AS folio,
              0 AS sufijo,
              c.RazonSocial AS cliente,
              tdp.Cve_articulo AS clave,
              a.des_articulo AS articulo,
              cac.Descripcion AS Descripcion,
              cac.Codigo AS SKU,
              cac.Sku_R AS Sku_R,
              ROUND((a.peso * tdp.Num_cantidad),4) AS peso,
              ROUND(((a.alto/1000) * (a.ancho/1000) * (a.fondo/1000) * tdp.Num_cantidad),4) AS volumen,
              tdp.Num_cantidad AS Pedido_Total,
              (SELECT SUM(Existencia) AS Existencia_Total FROM VS_ExistenciaParaSurtido WHERE VS_ExistenciaParaSurtido.cve_articulo = tdp.Cve_articulo GROUP BY cve_articulo) AS Existencia_Total
          FROM td_pedido tdp
              LEFT JOIN c_articulo a ON a.cve_articulo = tdp.Cve_articulo
              LEFT JOIN th_pedido ON th_pedido.Fol_folio = tdp.Fol_folio
              LEFT JOIN c_cliente c ON c.Cve_Clte = th_pedido.Cve_clte
              LEFT JOIN c_articulo_codigo cac ON cac.Cve_Articulo = tdp.Cve_articulo
              LEFT JOIN td_surtidopiezas s ON s.fol_folio = tdp.Fol_folio AND s.Cve_articulo = tdp.Cve_articulo AND s.cve_almac = th_pedido.cve_almac
              LEFT JOIN (
                  SELECT 
                      V_ExistenciaGral.cve_articulo,
                      c_ubicacion.CodigoCSD,
                      MIN(V_ExistenciaGral.cve_ubicacion) AS MIN 
                  FROM V_ExistenciaGral 
                      LEFT JOIN c_ubicacion ON V_ExistenciaGral.cve_ubicacion = c_ubicacion.idy_ubica 
                  GROUP BY cve_articulo
              ) x ON x.cve_articulo = tdp.Cve_articulo
          WHERE tdp.Fol_folio = '$folio'
          ";
          //tdp.Fol_folio = '$folio'
          // hace una llamada previa al procedimiento almacenado Lis_Facturas
          //echo $sql;
          $res  = mysqli_query($conn, $sql);
          if(!$res)
          {
            echo "Falló la preparación: (" . mysqli_error($conn) . ")";
            //Folio = ".$folio."--------- SQL = ".$sql
          }

          $sql2 = 'SELECT des_cia FROM c_compania WHERE cve_cia = 1';
          $res2  = mysqli_query($conn, $sql2);
          $nombre_empresa = mysqli_fetch_array($res2, MYSQLI_ASSOC);
          $nombre_empresa = $nombre_empresa['des_cia'];


          //$sql3 = "SELECT destinatario, Pick_Num, cve_almac FROM th_pedido WHERE Fol_folio = '$folio'";
          $sql3 = "SELECT Cve_CteProv, Pick_Num, cve_almac FROM th_pedido WHERE Fol_folio = '$folio'";
          
          $res3  = mysqli_query($conn, $sql3);
          $resul = mysqli_fetch_array($res3, MYSQLI_ASSOC);
          //$destinatario = $resul['destinatario'];
          //$Cve_clte     = $resul['Cve_clte'];
          $Cve_clte     = $resul['Cve_CteProv'];
          $orden_compra = $resul['Pick_Num'];
          $cve_almac    = $resul['cve_almac'];

          $filename = "PH_$orden_compra.pdf";

            ob_start(); 

            //$pdf = new \FPDF();
            //$pdf_upca = new \PDF_EAN13();
            $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT , 'LETTER', true, 'UTF-8', false);
            $pdf->SetCreator('wms');
            $pdf->SetAuthor('wms');
            $pdf->SetTitle('Informe Pedidos');
            $pdf->SetFont('helvetica', '', '10px', '', 'default', true);

            $pdf->AddPage();
/*
            $pdf->SetXY(10, 4.5);
            $pdf->SetFillColor(0,0,0);
            $pdf->SetTextColor(255,255,255);
            //$pdf->SetLineWidth(3);
            $pdf->SetFontSize(12);
            $pdf->Cell(84, 4, "License Plate Control   AssistPro ADL", 1, 1, 'C', 1, 0);


            $pdf->SetXY(10, 14);
            $pdf->SetFontSize(8);
            $pdf->SetFillColor(255,255,255);
            $pdf->SetTextColor(0,0,0);
            $pdf->Cell(30, 4, "Fecha Ingreso: ", 0, 1, 'L', 1, 0);

            $pdf->SetXY(30, 14);
            $pdf->SetFontSize(8);
            $pdf->SetFillColor(255,255,255);
            $pdf->SetTextColor(0,0,0);
            $pdf->Cell(18, 4, '00-00-000', 0, 1, 'R', 1, 0);

            $pdf->SetXY(10, 20);
            $pdf->SetFontSize(8);
            $pdf->SetFillColor(255,255,255);
            $pdf->SetTextColor(0,0,0);
            $pdf->Cell(30, 4, "Proveedor: ", 0, 1, 'L', 1, 0);

            $pdf->SetTextColor(0,0,0);
            $pdf->MultiCell(50, 4, 'Cliente', 0, 'L', true, 1, 32, 20);
*/
            $pdf->SetXY(0, 10);
            $pdf->SetFontSize(8);
            $pdf->SetFillColor(255,255,255);
            $pdf->SetTextColor(0,0,0);
            $pdf->Cell(0, 4, "                                                                               ", 0, 1, 'L', 1, 0);

            $pdf->SetXY(10, 10);
            $pdf->SetFontSize(16);
            $pdf->SetFillColor(255,255,255);
            $pdf->SetTextColor(0,0,0);
            $pdf->Cell(30, 5, $nombre_empresa, 0, 1, 'L', 1, 0);

            $pdf->SetXY(10, 20);
            $pdf->SetFontSize(10);
            $pdf->SetFillColor(255,255,255);
            $pdf->SetTextColor(0,0,0);
            $pdf->Cell(30, 5, 'TIENDA', 0, 1, 'L', 1, 0);

            $pdf->SetXY(50, 20);
            $pdf->SetFontSize(10);
            $pdf->SetFillColor(255,255,255);
            $pdf->SetTextColor(0,0,0);
            //$pdf->Cell(30, 5, $destinatario, 0, 1, 'L', 1, 0);
            $pdf->Cell(30, 5, $Cve_clte, 0, 1, 'L', 1, 0);

            $pdf->SetXY(140, 20);
            $pdf->SetFontSize(10);
            $pdf->SetFillColor(255,255,255);
            $pdf->SetTextColor(0,0,0);
            $pdf->Cell(30, 5, "PEDIDO: ", 0, 1, 'C', 1, 0);

            $pdf->SetXY(165, 20);
            $pdf->SetFontSize(10);
            $pdf->SetFillColor(255,255,255);
            $pdf->SetTextColor(0,0,0);
            $pdf->Cell(40, 5, "$orden_compra", 0, 1, 'L', 1, 0);

            $datos = "";
            $rows = array();
            //ob_start(); 
              $style = array(
                  'position'     => '',
                  'align'        => 'C',
                  'stretch'      => false,
                  'fitwidth'     => false,
                  'cellfitalign' => '',
                  'border'       => false,
                  'baseline'     => false,
                  'hpadding'     => 'auto',
                  'vpadding'     => 'auto',
                  'fgcolor'      => array(0, 0, 0),
                  'bgcolor'      => false,
                  'text'         => true,
                  'font'         => 'helvetica',
                  'fontsize'     => 8,
                  'stretchtext'  => 4
              );

            ?>
                <table style="width:700px;" border="1">
                    <tr>
                        <td style="text-align: center;width: 100px;">SKU</td>
                        <td style="text-align: center;width: 200px;">CODIGO</td>
                        <td style="text-align: center;width: 100px;">REFERENCIA</td>
                        <td style="text-align: center;width: 200px;">DESCRIPCIÓN</td>
                        <td style="text-align: center;width: 100px;">TOTAL</td>
                    </tr>
                <?php 
                $y_codigobarra = 35;
                $height = 70;
                $total = 0;
                while(($row = mysqli_fetch_array($res, MYSQLI_ASSOC)))
                {
                    //$rows[] = $row;
                    $datos = $row;
                    $clave_referencia = $datos["clave"];
                    $Sku_R = $datos["Sku_R"];
                    $descripcion = $datos["Descripcion"];
                ?>
                    <tr>
                        <td valign="middle" align="center" style="height: <?php echo $height; ?>px;width: 100px;">
                            <div>
                                <?php 
                                      echo $Sku_R;
                                ?>
                            </div>
                        </td>
                        <td valign="middle" align="center" style="height: <?php echo $height; ?>px;width: 200px;">
                        <?php 
                              $pdf->write1DBarcode($Sku_R, 'UPCA', 17, $y_codigobarra, '100', '20', 0.4, $style, 'N');
                        ?>
                        </td>
                        <td valign="middle" align="center" style="height: <?php echo $height; ?>px;width: 100px;">
                            <div>
                                <?php echo $clave_referencia; ?>
                            </div>
                        </td>
                        <td valign="middle" align="center" style="height: <?php echo $height; ?>px;width: 200px;">
                            <div><?php echo $descripcion; ?></div>
                        </td>
                        <td valign="middle" align="center" style="height: <?php echo $height; ?>px;width: 100px;">
                            <div><?php echo $datos["Pedido_Total"]; $total += $datos["Pedido_Total"]; ?></div>
                        </td>
                    </tr>

                <?php  
                    $y_codigobarra += 19;
                }
                ?>
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td align="center">TOTAL</td>
                        <td align="center"><?php echo $total; ?></td>
                    </tr>
                </table>
                <?php 
              $desProducto = ob_get_clean();
              $pdf->SetAutoPageBreak(TRUE, 5);
              $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO); 
              //$pdf->setMargins(0, 5, 0, 0);
              $pdf->SetXY(10, 30);
              $pdf->SetTextColor(0,0,0);
              //$pdf->SetFont('helvetica', '', '10px', '', 'default', true);
              $pdf->WriteHTML($desProducto, true, false, true, '');
              $pdf->Output($filename, 'I');

      }

    public function Header() {
            $this->setJPEGQuality(90);
            $this->Image('logo.png', 120, 10, 75, 0, 'PNG', 'http://www.finalwebsites.com');
    }

      // Page footer
      public function Footer() 
      {
          // Position at 15 mm from bottom
          $this->SetY(-15);
          // Set font
          $this->SetFont('helvetica', 'I', 8);
          // Page number
          $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
      }

      private function setData($folio)
      {
          mysqli_set_charset(\db2(), 'utf8');
          $this->folio = $folio;
          $this->oc = '';

          $sql = "
              SELECT pick_num oc
              FROM th_pedido 
              WHERE Fol_folio = '{$folio}';
          ";
          $query = mysqli_query(\db2(), $sql);

          if($query->num_rows > 0)
          {
              $this->oc = mysqli_fetch_all($query, MYSQLI_ASSOC)[0]['oc'];
          }
          $sql = "
              SELECT  
                  p.Cve_articulo AS articulo,  
                  a.des_articulo AS descripcion,
                  p.Num_cantidad AS cantidad
              FROM td_pedido p 
                  LEFT JOIN c_articulo a ON a.cve_articulo = p.Cve_articulo
              WHERE Fol_folio = '$folio';
          ";
          $query = mysqli_query(\db2(), $sql);

          if($query->num_rows > 0)
          {
              $this->articulos = mysqli_fetch_all($query, MYSQLI_ASSOC);
          }
          $usuario = $_SESSION['id_user'];
          $sql = "
              SELECT 
                  des_cia, 
                  des_direcc 
              FROM c_compania 
              WHERE cve_cia = (SELECT cve_cia FROM c_usuario WHERE id_user = {$usuario});
          ";
          $query = mysqli_query(\db2(), $sql);
          if($query->num_rows > 0)
          {
              $this->empresa = mysqli_fetch_all($query, MYSQLI_ASSOC)[0];
          }
          $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
          $sql = "
              SELECT 
                  d.razonsocial, 
                  CONCAT(direccion, '<br/> ', colonia, '<br/> ', ciudad, '<br/> ', estado,  '<br/> ', postal) AS direccion 
              FROM Rel_PedidoDest p
                  LEFT JOIN c_destinatarios d ON p.Id_Destinatario = d.id_destinatario
              WHERE p.Fol_Folio = '{$folio}';
          ";
          $query = mysqli_query($conn, $sql);
          if($query->num_rows > 0)
          {
              $this->destinatario = mysqli_fetch_all($query, MYSQLI_ASSOC)[0];
          }
          $sql = "SELECT nombre_completo FROM c_usuario WHERE cve_usuario = (SELECT cve_usuario FROM th_subpedido WHERE fol_folio = '$folio' LIMIT 1);";
          $query = mysqli_query(\db2(), $sql);
          if($query->num_rows > 0)
          {
            $this->surtidor = mysqli_fetch_all($query, MYSQLI_ASSOC)[0]['nombre_completo'];
          }
      }
  }
?>