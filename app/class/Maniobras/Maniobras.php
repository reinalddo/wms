<?php

namespace Maniobras;

class Maniobras {

    const TABLE = 'th_pedservicios';
    var $identifier;

    public function __construct( $Fol_folio = false, $key = false ) {

        if( $Fol_folio ) {
            $this->Fol_folio = (int) $Fol_folio;
        }

        if($key) {

            $sql = sprintf('
          SELECT
            Fol_folio
          FROM
            %s
          WHERE
            Fol_folio = ?
        ',
                self::TABLE
            );

            $sth = \db()->prepare($sql);
            $sth->setFetchMode(\PDO::FETCH_CLASS, '\Maniobras\Maniobras');
            $sth->execute(array($key));

            $Fol_folio = $sth->fetch();

            $this->Fol_folio = $Fol_folio->Fol_folio;

        }

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
      
    
    function ConvertirEnLetras($numero)
    {
        $numf = $this->milmillon($numero);
        return $numf;
    }


    private function load() {

        $sql = sprintf('
        SELECT
          *
        FROM
          %s
        WHERE
          Fol_folio = ?
      ',
            self::TABLE
        );

        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\Maniobras\Maniobras' );
        $sth->execute( array( $this->Fol_folio ) );

        $this->data = $sth->fetch();

    }

    function getFormasPago($almacen) {

        $sql = "
        SELECT
          *
        FROM
          FormasPag 
        WHERE Status=1 AND IdEmpresa = '{$almacen}'
      ";

        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\Maniobras\Maniobras' );
        //$sth->execute( array( Fol_folio ) );
        $sth->execute( array(  ) );

        return $sth->fetchAll();

    }

    function getAll() {

        $sql = '
        SELECT
          *
        FROM
          ' . self::TABLE . '
		  where Activo=1
      ';

        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\Maniobras\Maniobras' );
        $sth->execute( array( Fol_folio ) );

        return $sth->fetchAll();

    }
        function __get( $key ) {

        switch($key) {
            case 'Fol_folio':
                $this->load();
                return @$this->data->$key;
            default:
                return $this->key;
        }

    }

  function GenerarFolioDocumento()
  {
      $sql = "SELECT RIGHT(YEAR(CURDATE()), 2) AS yearr, DAYOFYEAR(CURDATE()) AS nday, RIGHT(MAX(Documento), 3) AS Consecutivo FROM Venta";
      $sth = \db()->prepare( $sql );$sth->execute();$res_fechas = $sth->fetch();
      $year         = $res_fechas['yearr'];
      $nday         = $res_fechas['nday'];
      $consecutivo  = $res_fechas['Consecutivo'];

      if($consecutivo == 999) $consecutivo = 0;

      $consecutivo++;

      $nday = str_pad($nday, 3, "0", STR_PAD_LEFT);
      $consecutivo = str_pad($consecutivo, 3, "0", STR_PAD_LEFT);
      $documento = $year.$nday.$consecutivo;

      return $documento;
  }

  function GenerarDiaOperativo($id_ruta, $clave_almacen)
  {
      //$sql = "SELECT * FROM DiasO WHERE Fecha = CURDATE() AND RutaId = $id_ruta AND IdEmpresa = '$clave_almacen'";
      //$sth = \db()->prepare( $sql );$sth->execute();

      $sql = "SELECT IFNULL(IFNULL(DiaO, 1), '') AS DiaO, Fecha, CURDATE() AS F_Actual FROM DiasO WHERE RutaId = $id_ruta AND Id = (SELECT MAX(Id) FROM DiasO WHERE RutaId = $id_ruta)";
      $sth = \db()->prepare( $sql );$sth->execute();$res = $sth->fetch();
      $DiaO = $res['DiaO'];
      $Fecha = $res['Fecha'];
      $F_Actual = $res['F_Actual'];

      if($DiaO == '') 
      {
        $DiaO = 1;
        $sql = "INSERT IGNORE INTO DiasO(DiaO, Fecha, RutaId, IdEmpresa) VALUES({$DiaO}, CURDATE(), {$id_ruta}, '{$clave_almacen}')";
        $sth = \db()->prepare( $sql );$sth->execute();

        $sql = "UPDATE Continuidad SET UDiaO = $DiaO WHERE RutaID = $id_ruta";
        $sth = \db()->prepare( $sql );$sth->execute();

      }

      if($Fecha."" != $F_Actual."")
      {
          $DiaO++;
          $sql = "INSERT IGNORE INTO DiasO(DiaO, Fecha, RutaId, IdEmpresa) VALUES({$DiaO}, CURDATE(), {$id_ruta}, '{$clave_almacen}')";
          $sth = \db()->prepare( $sql );$sth->execute();
      }

      return $DiaO;
  }

  function save( $_post ) {

    try {
      $tipo_pedido = "P";
      $rango_hora = $_post['hora_desde']." - ".$_post['hora_hasta'];
      $statusaurora = "";
      $Cve_CteProv = "(SELECT DISTINCT Cve_CteProv from c_cliente where Cve_clte = '".$_post['Cve_clte']."' LIMIT 1)";
      $cve_almac = $_post['cve_almac'];
      $ots_con_lp = $_post['ots_con_lp'];
      $lp_ot = (isset($_post['lp_ot']))?($_post['lp_ot']):"";
      $tipo_lp = (isset($_post['tipo_lp']))?($_post['tipo_lp']):"0";
      $tipo_ptl = (isset($_post['tipo_ptl']))?($_post['tipo_ptl']):0;

      if($tipo_lp == "") $tipo_lp = "0";

      if(isset($_post['TipoPedido']))
        $tipo_pedido = $_post['TipoPedido'];

        if($tipo_pedido == 'T')
        {
          $ots_con_lp = "";
          $lp_ot = "0";
        }
      if($_post['tipo_traslado'] == 1) 
      {
          $statusaurora = $_post['cve_almac'];
          $cve_almac = $_post['almacen_dest']; 
          $Cve_CteProv = "'".$_post['Cve_clte']."'";
          $tipo_pedido = $_post['tipo_traslado_int_ext'];
      }

      if(isset($_post['statusaurora_traslado']))
      {
          $statusaurora = $_post['statusaurora_traslado'];
          $cve_almac = $_post['cve_almac']; 
          $Cve_CteProv = "'".$_post['Cve_clte']."'";
          $tipo_pedido = $_post['TipoPedido'];
      }


      $fol_verif = $_post['Fol_folio'];
      $sql = "SELECT COUNT(*) as existe FROM th_pedservicios WHERE Fol_folio = '{$fol_verif}'";
      $sth = \db()->prepare( $sql );$sth->execute();$res_fol = $sth->fetch();
      $existe_folio  = $res_fol['existe'];

      if($existe_folio) 
      {
            if($_post['tipo_traslado'] == 1) 
                $_post['Fol_folio'] = $this->consecutivo_folio_traslado();
            else
                $_post['Fol_folio'] = $this->consecutivo_folio();
      }

      //***************************************************************************************************
      //                      PROCESO PARA REGISTRAR EL PEDIDO TIPO VENTA (TABLA Venta)
      //***************************************************************************************************
            $tipo_venta = $_post['tipo_venta'];
            $tipo_negociacion = $_post['tipo_negociacion'];

            if($tipo_venta == 'venta' && $_post['tipo_traslado'] != 1) 
            {
                  $sql = "SELECT clave FROM c_almacenp WHERE id = {$cve_almac} LIMIT 1";
                  $sth = \db()->prepare( $sql );$sth->execute();$res_almacen = $sth->fetch();
                  $clave_almacen  = $res_almacen['clave'];

                  $cve_ruta = $_post['rutas_ventas'];
                  $sql = "SELECT ID_Ruta FROM t_ruta WHERE cve_ruta = '{$cve_ruta}' LIMIT 1";
                  $sth = \db()->prepare( $sql );$sth->execute();$res_ruta = $sth->fetch();
                  $id_ruta  = $res_ruta['ID_Ruta'];

                  $cve_vendedor = $_post['cve_Vendedor'];
                  $sql = "SELECT id_user FROM c_usuario WHERE cve_usuario = '{$cve_vendedor}' LIMIT 1";
                  $sth = \db()->prepare( $sql ); $sth->execute(); $res_vendedor = $sth->fetch();
                  $id_user  = $res_vendedor['id_user'];

                  //$documento = $this->GenerarFolioDocumento();

                  $destinatario = $_post['destinatario'];
                  $Cve_Clte = '';
                  if($destinatario)
                  {
                      $sql = "SELECT Cve_Clte FROM c_destinatarios WHERE id_destinatario = {$destinatario} LIMIT 1";
                      $sth = \db()->prepare( $sql ); $sth->execute(); $res = $sth->fetch();
                      $Cve_Clte  = $res['Cve_Clte'];
                  }

                  $cve_vendedor = $_post['cve_Vendedor'];
                  $sql = "SELECT dias_credito AS DiasCred, credito_actual AS CreditoDispo, saldo_inicial AS Saldo, DATE_ADD(CURDATE(), INTERVAL dias_credito DAY) AS Fvence FROM c_cliente WHERE Cve_Clte = '{$Cve_Clte}' LIMIT 1";
                  $sth = \db()->prepare( $sql ); $sth->execute(); $res = $sth->fetch();

                  $DiasCred     = $res['DiasCred'];
                  $CreditoDispo = $res['CreditoDispo'];
                  $Saldo        = $res['Saldo'];
                  $Fvence       = $res['Fvence'];

                  $subtotal = 0; $iva = 0; $total = 0; $items_productos = 0; $kg = 0;

                  foreach ($_post["arrDetalle"] as $detVenta) 
                  {
                      $cve_articulo = $detVenta['Cve_articulo'];
                      $sql = "SELECT unidadMedida FROM c_articulo WHERE cve_articulo = '{$cve_articulo}' LIMIT 1";
                      $sth = \db()->prepare( $sql ); $sth->execute(); $res = $sth->fetch();
                      $tipo  = $res['unidadMedida'];

                      $importe = $detVenta['Num_cantidad']*$detVenta['precio_unitario'];
                      $descuento_monto = ($detVenta['desc_importe']/100)*$detVenta['Num_cantidad'];//*$importe;
                      $sql = "INSERT IGNORE INTO DetalleVet(Articulo, Descripcion, Precio, Pza, Kg, DescMon, DescPorc,  Tipo, Docto, Importe, IVA, IEPS, RutaId, IdEmpresa, Comisiones, Utilidad) 
                              VALUES('".$detVenta['Cve_articulo']."', '".$detVenta['des_articulo']."', ".$detVenta['precio_unitario'].", ".$detVenta['Num_cantidad'].", ".$detVenta['peso'].", ".$detVenta['desc_importe'].", ".$descuento_monto.", ".$tipo.", '".$_post['Fol_folio']."', ".$importe.", ".$detVenta['iva'].", 0, ".$id_ruta.", '".$clave_almacen."', 0, 0)";

                      $rs = mysqli_query(\db2(), $sql) or die("Error description: " . mysqli_error(\db2()).$sql);

                      $total    += ($detVenta['precio_unitario']*$detVenta['Num_cantidad']);
                      $subtotal += $detVenta['sub_total'];
                      $iva      += $detVenta['iva'];
                      $kg       += $detVenta['peso'];
                      $items_productos++;
                  }

                  $EnLetra = $this->ConvertirEnLetras($total);

                  $DiaOperativo = $this->GenerarDiaOperativo($id_ruta, $clave_almacen);

                  $sql = "INSERT INTO Venta (RutaId, VendedorId, CodCliente, Documento, Fecha, TipoVta, DiasCred, CreditoDispo, 
                                             Saldo, Fvence, SubTotal, IVA, IEPS, TOTAL, EnLetra, Items, FormaPag, DocSalida, DiaO, 
                                             Cancelada, Kg, IdEmpresa)";
                        $sql .= "VALUES (";
                        $sql .= "'".$id_ruta."',";
                        $sql .= "'".$id_user."',";
                        $sql .= "'".$_post['destinatario']."',";
                        $sql .= "'".$_post['Fol_folio']."',";
                        $sql .= "NOW(),";
                        $sql .= "'".$tipo_negociacion."',";
                        $sql .= "'".$DiasCred."',";
                        $sql .= "'".$CreditoDispo."',";
                        $sql .= "'".$Saldo."',";
                        $sql .= "'".$Fvence."',";
                        $sql .= "'".$subtotal."',";
                        $sql .= "'".$iva."',";
                        $sql .= "0,";
                        $sql .= "'".$total."',";
                        $sql .= "'".$EnLetra."',";
                        $sql .= "'".$items_productos."',";
                        $sql .= "'".$_post['forma_pago']."',";
                        $sql .= "'Ticket',";
                        $sql .= "'".$DiaOperativo."',";
                        $sql .= "'0',";
                        $sql .= "'".$kg."',";
                        $sql .= "'".$clave_almacen."')";
                        $rs = mysqli_query(\db2(), $sql) or die("Error description: " . mysqli_error(\db2()).$sql);
            }

      //***************************************************************************************************

      $DiaOperativo = 'NULL';
      if($_post['rutas_ventas'])
      {
          $sql = "SELECT clave FROM c_almacenp WHERE id = {$cve_almac} LIMIT 1";
          $sth = \db()->prepare( $sql );$sth->execute();$res_almacen = $sth->fetch();
          $clave_almacen  = $res_almacen['clave'];

          $cve_ruta = $_post['rutas_ventas'];
          $sql = "SELECT ID_Ruta FROM t_ruta WHERE cve_ruta = '{$cve_ruta}' LIMIT 1";
          $sth = \db()->prepare( $sql );$sth->execute();$res_ruta = $sth->fetch();
          $id_ruta  = $res_ruta['ID_Ruta'];

          $DiaOperativo = $this->GenerarDiaOperativo($id_ruta, $clave_almacen);
      }


      //if($tipo_venta != 'venta' && $_post['tipo_traslado'] != 1) 
      //{ //preventa
      $tipoDoc = "";
      //if($ots_con_lp != '')
        //$tipoDoc = 'lp_ot';
      if($tipo_lp == '1')
         $tipoDoc = 'tipo_lp';

       
        $sql = "INSERT INTO " . self::TABLE . " (Fol_Folio, Cve_Almac, Cve_Usuario, Fec_Pedido, Cve_clte, Cve_CteProv, Fec_Inicio, Fec_Fin, Status, Observaciones, Activo, Forma_Pago, Docto_Ref, Docto_Ped, Id_RegFis, Id_CFDI)";
            $sql .= "Values (";
            $sql .= "'".$_post['Fol_folio']."',";
            $sql .= "'".$cve_almac."',";
            $sql .= "(SELECT DISTINCT cve_usuario from c_usuario where id_user = '".$_post['Cve_Usuario']."' LIMIT 1),";
            $sql .= "'".$_post['Fec_Pedido']."',";
            $sql .= "'".$_post['Cve_clte']."',";
            $sql .= $Cve_CteProv.",";
            $sql .= "'".$_post['Fec_Entrega']."',";//Fec_Inicio
            $sql .= "'".$_post['Fec_Entrega']."',";//Fec_Fin
            $sql .= "'".$_post['status']."',";
            $sql .= "'".$_post['Observaciones']."',";
            $sql .= "'1',";
            $sql .= "'".$_post['tipo_negociacion']."',";
            $sql .= "'".$_post['nroOC']."',";
            $sql .= "'".$_post['pedimentoW']."',";
            $sql .= "'".$_post['rfiscal']."',";
            $sql .= "'".$_post['ucfdi']."')";



            //echo $sql;
            $rs = mysqli_query(\db2(), $sql) or die("Error description: " . mysqli_error(\db2()).$sql);
          
            $precio_iva = 0;

            if (!empty($_post["arrDetalle"])) {
              //peso, precio_unitario, importe_total
                foreach ($_post["arrDetalle"] as $item) {
                    $sql = "INSERT INTO td_pedservicios (Fol_Folio, Cve_Almac, Cve_Servicio, Num_cantidad, id_unimed, status, itemPos, Activo, Precio_unitario, Desc_importe, IVA, Id_Moneda, Referencia) Values ";
                    $sql .= "('".$_post['Fol_folio']."', '$cve_almac', '".$item['Cve_articulo']."', '".$item['Num_cantidad']."', '".$item['id_unimed']."', 'A' , '', '1', '".$item['precio_unitario']."', '".$item['desc_importe']."', '".$item['iva']."', '".$_post['moneda']."', '');";
                    //echo $sql;
                    $rs = mysqli_query(\db2(), $sql) or die("Error description: " . mysqli_error(\db2()));
                }
            }

        //} //preventa
            if($_post['tipo_negociacion'] == 'Credito')
            {
                $sql = "INSERT INTO Cobranza(Cliente, Documento, Saldo, Status, RutaId, UltPago, FechaReg, FechaVence, FolioInterno, TipoDoc, DiaO, IdEmpresa) VALUES('".$_post['destinatario']."', '".$_post['Fol_folio']."', IFNULL((SELECT SUM(IFNULL(Precio_unitario, 0)*IFNULL(Num_cantidad, 0)) FROM td_pedido WHERE Fol_folio = '".$_post['Fol_folio']."'), (SELECT SUM(IFNULL(Precio, 0)*IFNULL(Pza, 0)) FROM DetalleVet WHERE Docto = '".$_post['Fol_folio']."')), 1, (SELECT ID_Ruta from t_ruta where cve_ruta = '".$_post['rutas_ventas']."'), 0, CURDATE(), (SELECT DATE_FORMAT(CURDATE()+IFNULL(dias_credito, 0), '%Y-%m-%d') FROM c_cliente WHERE Cve_Clte = '".$_post['Cve_clte']."'), 0, 'Ticket', '".$DiaOperativo."', '".$clave_almacen."')";
                $rs = mysqli_query(\db2(), $sql) or die("Error description: " . mysqli_error(\db2()).$sql);
            }

            /*if (!empty($_post["destinatario"])) {
               
                $destinatario = $_post["destinatario"];

                $sql = "INSERT INTO `Rel_PedidoDest`(
                            `Fol_Folio`, `Cve_Almac`, `Id_Destinatario`
                         ) VALUES (
                             '".$_post['Fol_folio']."', 
                             '".$_post['cve_almac']."', 
                             '".$destinatario."'
                        );";
                $rs = mysqli_query(\db2(), $sql) or die("Error description: " . mysqli_error(\db2()));
                
            }*/
        return $_post['Fol_folio'];
    } catch(Exception $e) {
            return 'ERROR: ' . $e->getMessage();
        }
    }
  
    function consecutivo_folio() 
    {
      $sql = "SELECT IF(MONTH(CURRENT_DATE()) < 10, CONCAT(0, MONTH(CURRENT_DATE())), MONTH(CURRENT_DATE())) AS mes, YEAR(CURRENT_DATE()) AS _year FROM DUAL";
      $sth = \db()->prepare( $sql );
      $sth->execute();
      $fecha = $sth->fetch();

      $mes  = $fecha['mes'];
      $year = $fecha['_year'];


      $count = 1;
      while(true)
      {
          if($count < 10)
            $count = "0".$count;

          $folio_next = "S".$year.$mes.$count;
          $sql = "SELECT COUNT(*) as Consecutivo FROM th_pedservicios WHERE Fol_Folio = '$folio_next'";// OR '$folio_next' in (SELECT Documento FROM Venta)
          $sth = \db()->prepare( $sql );
          $sth->execute();
          $data = $sth->fetch();

          if($data["Consecutivo"] == 0)
            break;
          else
          {
              $count += 0; //convirtiendo a entero
              $count++;
          }
      }

      return $folio_next;
    }

    function consecutivo_folio_traslado() 
    {
      $sql = "SELECT IF(MONTH(CURRENT_DATE()) < 10, CONCAT(0, MONTH(CURRENT_DATE())), MONTH(CURRENT_DATE())) AS mes, YEAR(CURRENT_DATE()) AS _year FROM DUAL";
      $sth = \db()->prepare( $sql );
      $sth->execute();
      $fecha = $sth->fetch();

      $mes  = $fecha['mes'];
      $year = $fecha['_year'];


      $count = 1;
      while(true)
      {
          if($count < 10)
            $count = "0".$count;

          $folio_next = "TR".$year.$mes.$count;
          $sql = "SELECT COUNT(*) as Consecutivo FROM th_pedido WHERE Fol_Folio = '$folio_next'";
          $sth = \db()->prepare( $sql );
          $sth->execute();
          $data = $sth->fetch();

          if($data["Consecutivo"] == 0)
            break;
          else
          {
              $count += 0; //convirtiendo a entero
              $count++;
          }
      }

      return $folio_next;
    }


}
