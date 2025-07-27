<?php

namespace Application\Controllers;

use Framework\View\View;
use Framework\Helpers\Utils;
use Framework\Http\Response;
use Framework\Http\Controller;
use jakeroid\tools\SimpleXLSX;
use Application\Models\Usuarios;
use Application\Models\Articulos;
use Application\Models\ArticulosImportados;
use Application\Models\Lotes;
use Application\Models\OrdenesDeCompra;
use Application\Models\OrdenesDeCompraItems;
use Illuminate\Database\Capsule\Manager as Capsule;


/**
 * @version 1.0.0
 * @category Usuarios
 * @author Brayan Rincon <brayan262@gemail.com>
 */
class OrdenesDeCompraController extends Controller
{
  //no estoy seguro para que es esto
    const CLAVE = 0;
    const DESCRIPCION = 1;
    const PROVEEDOR = 2;
    const ALMACEN = 3;
    const CODIGO_BARRA = 4;
    const ALTO = 5;
    const FONDO = 6;
    const ANCHO = 7;
    const GRUPO = 8;
    const CLASIFICACION = 9;
    
  
 ///////////////////////////////////////////////////////////////////////////////////////////////////// 
    public function importarOC()
    {
        $errores = [];
        $dir_cache = PATH_APP . 'Cache/';
        $file = $dir_cache . basename($_FILES['file']['name']);

        if (! move_uploaded_file($_FILES['file']['tmp_name'], $file)) {
            $this->response(400, [
                'statusText' =>  "Error al recibir el fichero, verifique que se tenga permisos para escribir en Cache",
            ]);
        }


        $xlsx = new SimpleXLSX( $file );
        $linea = 1;
        /*
        foreach ($xlsx->rows() as $row)
        {
            if($linea == 1) {
                $linea++;continue;
            }
            $eval = $this->validarRequeridosImportarOC($row);
            $linea++;
        }
        */
        $linea = 1;
        $lineas = $xlsx->rows()  ;
       
        $current_pedido = "";
        $num_pedimento = "";
        $ID_Proveedor = "";
        $mensaje_res = "";

        foreach ($xlsx->rows() as $row)
        {
            if($linea == 1) {

                $linea++;
                continue;
            }
            /*
            if($linea == 1) {
                $columnas = array("NumeroDeOrden","Fecha","FechaEntrega","ClaveAlmacen","ClaveArticulo","Status","IdProveedor","Cantidad","Protocolo","PrecioUnitario","ID_Presupuesto","TipoDeRecurso","TipoDeProcedimiento","FechaDeFallo","PlazoDeEntrega","CondicionesDePago","LugarDeEntrega","NumeroDeExpediente","NumDeDictamen","AreaSolicitante","NumeroDeSuficiencia","FechaDeSuficiencia","FechaDeContrato","MontoDeSuficiencia","NumeroDeContrato");
                $cl_excel = array("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z");
                foreach($columnas as $k => $v)
                {
                    if($this->pSQL($row[$k])!=$v)
                    {
                        $k_i=$k+1;
                        $this->response(400, [
                            'statusText' =>  "ERROR Falta el titulo “".$v."” en la columna “".$cl_excel[$k]."” de su archivo excel",
                        ]); 
                    }
                }
                $linea++;
                continue;
            }
            
            else
            {
                $columnas = array("NumeroDeOrden","Fecha","FechaEntrega","ClaveAlmacen","ClaveArticulo","Status","IdProveedor","Cantidad","Protocolo","PrecioUnitario");
                $cl_excel = array("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z");
                foreach($columnas as $k => $v)
                {
                    if($this->pSQL($row[$k])=="")
                    {
                        // Datos incorrecto en la fila “Numero de fila” de la columna “Letra de columna”
                        $k_i=$k+1;
                        $this->response(400, [
                            'statusText' =>  "ERROR Dato incorrecto en la fila “".$linea."” columna “".$cl_excel[$k]."”. Este dato es necesario.",
                        ]); 
                    }
                }
            }
            */
          /*
            $importe ="";   
          
            foreach($columnas as $k => $v)
            {
                if($this->pSQL($row[$k])=="Cantidad")
                {
                    $cant = $this->pSQL($row[7]);
                    $precio = $this->pSQL($row[9]);
                    $importe = $cant*$precio;
                }
            echo var_dump($importe);
            die;
            }
          */
            $factura = trim($this->pSQL($row[0]));
            $Cve_Almac = trim($this->pSQL($row[3]));
            $proveedor = trim($this->pSQL($row[6]));
            $protocolo = trim($this->pSQL($row[8]));
            $folio_protocolo = 0;
            $element = OrdenesDeCompra::where('factura', $factura)->where('Cve_Almac', $Cve_Almac)->first(); //cambiar las tablas e consulta

              if($current_pedido != $row[0])
              {
                //query para traer el valor que se la asigna a num_pedimento y compara con num_orden
                $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
                mysqli_set_charset($conn, 'utf8');
                $sql = "SELECT MAX(num_pedimento) AS Maximo FROM th_aduana";
                if (!($res = mysqli_query($conn, $sql)))
                {
                  echo "Falló la preparación: (" . mysqli_error($conn) . ") "; exit;
                }
                $max = mysqli_fetch_assoc($res)["Maximo"];
                
                $num_pedimento = ($max+1);
                $current_pedido = $row[0];

                $sqls = "";
                $sql = "SELECT ID_Proveedor FROM c_proveedores WHERE cve_proveedor = '$proveedor'";
                if (!($res = mysqli_query($conn, $sql)))
                {
                  echo "Falló la preparación 0: (" . mysqli_error($conn) . ") "; exit;
                }
                //$sqls .= $sql."  ----   ";
                $ID_Proveedor = mysqli_fetch_assoc($res)["ID_Proveedor"];

                $sql = "SELECT (IFNULL(FOLIO, 0)+1) AS FOLIO FROM t_protocolo WHERE ID_Protocolo = '$protocolo'";
                if (!($res = mysqli_query($conn, $sql)))
                {
                  echo "Falló la preparación 1: (" . mysqli_error($conn) . ") "; exit;
                }
                //$sqls .= $sql."  ----   ";

                $folio_protocolo = mysqli_fetch_assoc($res)["FOLIO"];

                $sql = "UPDATE t_protocolo SET FOLIO = {$folio_protocolo}  WHERE ID_Protocolo = '$protocolo'";
                if (!($res = mysqli_query($conn, $sql)))
                {
                  echo "Falló la preparación 2: (" . mysqli_error($conn) . ") "; exit;
                }
                //$sqls .= $sql."  ----   ";

//              }

              //if($element != NULL){
              //    $model_orden = $element; 
              //}
              //else {
              //    $model_orden = new OrdenesDeCompra(); 
              //}
             
              //$data_pedido = OrdenesDeCompra::where('factura',$row[0])->first();
              //$mensaje_res = $data_pedido;
              //if( $data_pedido == NULL ){
//              if($current_pedido != $row[0]){
                $model_orden = new OrdenesDeCompra();
                $model_orden->factura             = trim($this->pSQL($row[0]));
                $model_orden->fech_pedimento      = trim($this->pSQL($row[1]));
                $model_orden->fech_llegPed        = trim($this->pSQL($row[2]));
                $model_orden->Cve_Almac           = trim($this->pSQL($row[3]));
                //$model_orden->cve_articulo     = $this->pSQL($row[4]);//esto es en td_aduana
                $model_orden->status              = trim($this->pSQL($row[5]));
                $model_orden->ID_Proveedor        = $ID_Proveedor;
                $model_orden->num_pedimento       = trim($this->pSQL($num_pedimento));
                $model_orden->ID_Protocolo        = trim($this->pSQL($row[8]));
                $model_orden->Consec_protocolo    = $protocolo;
                $model_orden->cve_usuario         = $_SESSION["id_user"];
                $model_orden->Activo              = 1;
                $model_orden->presupuesto         = trim($this->pSQL($row[10]));
                $model_orden->recurso             = trim($this->pSQL($row[11]));
                $model_orden->procedimiento       = trim($this->pSQL($row[12]));
                $model_orden->fechaDeFallo        = trim($this->pSQL($row[13]));
                $model_orden->plazoDeEntrega      = trim($this->pSQL($row[14]));
                $model_orden->condicionesDePago   = trim($this->pSQL($row[15]));
                $model_orden->lugarDeEntrega      = trim($this->pSQL($row[16]));
                $model_orden->Proyecto            = trim($this->pSQL($row[17]));
                $model_orden->dictamen            = trim($this->pSQL($row[18]));
                
                $model_orden->areaSolicitante     = trim($this->pSQL($row[19]));
                $model_orden->numSuficiencia      = trim($this->pSQL($row[20]));
                $model_orden->fechaSuficiencia    = trim($this->pSQL($row[21]));
                $model_orden->fechaContrato       = trim($this->pSQL($row[22]));
                $model_orden->montoSuficiencia    = trim($this->pSQL($row[23]));
                $model_orden->numeroContrato      = trim($this->pSQL($row[24]));
                $model_orden->save();
              }
            
              //$num_orden = ($num_pedimento);
              //$items = OrdenesDeCompraItems::where('num_orden', $num_orden)->first();
              
              if($items != NULL){
                  $model = $items; 
              }
              else {
                  $model = new OrdenesDeCompraItems(); 
              }
            
              if( $items == NULL ){
                $model = new OrdenesDeCompraItems();
                $model->cve_articulo     = trim($this->pSQL($row[4]));
                $model->cantidad         = trim($this->pSQL($row[7]));
                $model->num_orden        = trim($this->pSQL($num_pedimento));
                $model->costo            = trim($this->pSQL($row[9]));
                $model->Activo           = 1;
                $model->save();
                $linea++;
              }
        }
      
        $linea--;$linea--;
            $this->response(200, [
                'success' => true,
                'statusText' =>  "Pedidos importados con exito. Total de Pedidos: \"{$linea}\"",
                'mensaje_res' => $mensaje_res,
              "lineas"=>$lineas,
            ]);
    }

    public function importarFotosTHOC()
    {
        $statusText = "Fotos Importadas con Éxito";
        $statusType = 1;
        $id_th_entalmacen_folio = $_POST['folio_foto_th'];
        $ruta_path = 'data/uploads/fotos_folio/';
        $descripcion_foto = $_POST['descripcion_foto'];
        $type = $_FILES['image_file_th']['type'];

        $nombre = $_FILES['image_file_th']['name'];
        $nombre_archivo = $nombre;
        $extension = "";

        for($i = strlen($nombre_archivo)-1; $i > 0; $i--)
        {
          $extension .= $nombre_archivo[$i];
          if($nombre_archivo[$i] == ".")
          {
            break;
          }
        }
        $extension = strrev($extension);
        $_FILES['image_file_th']['name'] = "1"."-".$id_th_entalmacen_folio.$extension;

        $ruta = $ruta_path.$_FILES['image_file_th']['name'];

        while(file_exists($ruta))
        {
            $nombre = $_FILES['image_file_th']['name'];
            $arr = explode("-", $nombre);
            $num = (int)$arr[0];
            $num++;
            $_FILES['image_file_th']['name'] = str_replace(($num-1)."-", $num."-", $nombre);
            $ruta = $ruta_path.$_FILES['image_file_th']['name'];
        }

        $tmp_file = $_FILES['image_file_th']['tmp_name'];
        $foto = file_get_contents($tmp_file);

        if(move_uploaded_file($tmp_file, $ruta))
        {
            $sql = "INSERT INTO th_entalmacen_fotos(th_entalmacen_folio, ruta, descripcion, type, foto) VALUES (?, ?, ?, ?, ?)";
            $sth = \db()->prepare($sql);
            $sth->bindParam(1, $id_th_entalmacen_folio);
            $sth->bindParam(2, $ruta);
            $sth->bindParam(3, $descripcion_foto);
            $sth->bindParam(4, $type);
            $sth->bindParam(5, $foto);
        }
        //$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

          if (!$sth->execute())
          {
              $statusText = "Falló la preparación: (" . mysqli_error($conn) . ")"; 
              $statusType = 0;
          }

        $this->response(200, [
            'statusType' =>  $statusType,
            'statusText' =>  $statusText
        ]);
    }

    public function importarFotosTDOC()
    {
        $statusText = "Descripción actualizada con Éxito";
        $statusType = 1;
        $id_foto_td = $_POST['id_foto_td'];
        $descripcion_foto_td = $_POST['descripcion_foto_td'];
        if($_FILES['image_file_td']['name'])
        {
                $statusText = "Fotos Importadas con Éxito";
                $ruta_path = 'data/uploads/fotos_detalle_producto/';
                $type = $_FILES['image_file_td']['type'];
    
                $nombre = $_FILES['image_file_td']['name'];
                $nombre_archivo = $nombre;
                $extension = "";
    
                for($i = strlen($nombre_archivo)-1; $i > 0; $i--)
                {
                  $extension .= $nombre_archivo[$i];
                  if($nombre_archivo[$i] == ".")
                  {
                    break;
                  }
                }
                $extension = strrev($extension);
                $_FILES['image_file_td']['name'] = "1"."-".$id_foto_td.$extension;
    
                $ruta = $ruta_path.$_FILES['image_file_td']['name'];
    
                while(file_exists($ruta))
                {
                    $nombre = $_FILES['image_file_td']['name'];
                    $arr = explode("-", $nombre);
                    $num = (int)$arr[0];
                    $num++;
                    $_FILES['image_file_td']['name'] = str_replace(($num-1)."-", $num."-", $nombre);
                    $ruta = $ruta_path.$_FILES['image_file_td']['name'];
                }
    
                $tmp_file = $_FILES['image_file_td']['tmp_name'];
                $foto = file_get_contents($tmp_file);
    
                if(move_uploaded_file($tmp_file, $ruta))
                {
                    $sql = "INSERT INTO td_entalmacen_fotos(td_entalmacen_producto_id, ruta, type, foto) VALUES (?, ?, ?, ?)";
                    $sth = \db()->prepare($sql);
                    $sth->bindParam(1, $id_foto_td);
                    $sth->bindParam(2, $ruta);
                    $sth->bindParam(3, $type);
                    $sth->bindParam(4, $foto);
                }
                //$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    
                  if (!$sth->execute())
                  {
                      $statusText = "Falló la preparación: (" . mysqli_error($conn) . ")"; 
                      $statusType = 0;
                  }
        }
        $sql = "UPDATE td_entalmacen_fotos SET descripcion = '$descripcion_foto_td' WHERE td_entalmacen_producto_id = $id_foto_td";
        $sth = \db()->prepare($sql);
        $sth->execute();

        $this->response(200, [
            'statusType' =>  $statusType,
            'statusText' =>  $statusText
        ]);
    }

    const FOLIO_OCMSV           = 0; 
    const PROVEEDOR_OCMSV       = 1; 
    const CVE_ARTICULO_OCMSV    = 2; 
    const LOTE_OCMSV            = 3; 
    const CADUCIDAD_OCMSV       = 4; 
    const EXISTENCIA_OCMSV      = 5; 
    const BL_OCMSV              = 6;
    const LP_OCMSV              = 7;
    const CANT_PEDIDA_OCMSV     = 8;
    const FEC_ENTRADA_OCMSV     = 9;
    const COSTO_OCMSV           = 10;
    const MONEDA_OCMSV          = 11;
    const FACTURA_ART_OCMSV     = 12;
    const PROYECTO_OCMSV        = 13;
    const NOMBRE_OPERADOR_OCMSV = 14;
    const CVE_CHOFER_OCMSV      = 15;
    const NUMERO_UNIDAD_OCMSV   = 16;
    const CVE_TRANSP_OCMSV      = 17;
    const PLACA_OCMSV           = 18;
    const SELLO_OCMSV           = 19;
    const OBSERVACIONES_OCMSV   = 20;
    const FECHA_OCMSV           = 21;
    const HORA_OCMSV            = 22;
    const PROTOCOLO_OCMSV       = 23;

    public function getFechaActual()
    {
        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        $sql = "SELECT CURDATE() as Fecha_Actual FROM DUAL";
        $rs = mysqli_query($conn, $sql);
        $Fecha_Actual = mysqli_fetch_array($rs, MYSQLI_ASSOC);
        $Fecha_Actual = $Fecha_Actual['Fecha_Actual'];

        return $Fecha_Actual;
    }

    public function ImportarOCMasivo()
    {
        $errores = [];
        $dir_cache = PATH_APP . 'Cache/';
        $file = $dir_cache . basename($_FILES['fileOC']['name']);
        //$tipo = $_POST["tipo"];
        //$factura   = $_POST['NumOrden'];
        //$almacenes                        = $_POST['almacenesOC'];
        $almacenes                        = $_POST['almacen_clave_oc'];
        
        //$proveedor = $_POST['proveedor'];
        $empresa                          = $_POST['empresaOC'];
        $protocolo                        = $_POST['ProtocolOC'];
        $Consecut                         = $_POST['ConsecutOC'];
        $tipo_cambio                      = $_POST['tipo_cambioOC'];
        $palletizar_oc                    = $_POST['palletizar_entradaOC'];
        $sobreescribir_existencias        = $_POST['sobreescribir_existenciasOC'];
        $registrar_soloOC                 = $_POST['registrar_soloOC'];
        $zonarecepcioni                   = $_POST['zonarecepcioniOC'];
        $mensaje_pallet_diferente_almacen = "";


        $sql_tracking = "";
        //$this->pSQL($row[self::LOTE_SERIE]);
        $fecha_actual = $this->getFechaActual();
/*
        $date=date_create($_POST["fecha_oc"]);
        $fecha_actual = date_format($date,"Y-m-d");

        $date=date_create($_POST["fechaestimada"]);
        $fech_llegPed = date_format($date,"Y-m-d");
*/
        if (! move_uploaded_file($_FILES['fileOC']['tmp_name'], $file)) {
            $this->response(400, ['statusText' =>  "Error al recibir el fichero, verifique que se tenga permisos para escribir en Cache",]);
        }
        $xlsx = new SimpleXLSX( $file );

        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

        $columnas = array("Numero de Documento","Fecha del Documento (aaammdd)","Código de Producto","Cantidad","Lote","Fecha de Vencimiento (aaammdd)");
        //if($tipo=="vp"){$columnas[] = "BL";}
        $cl_excel = array("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z");

        $current_pedido = "";
        $num_pedimento = "";


        //$valido = false;

        $sql = "SELECT id FROM c_almacenp WHERE clave = '{$almacenes}'";
        if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación 2: (" . mysqli_error($conn) . ") "; exit;}
        $row_alm = mysqli_fetch_assoc($res);
        $id_almacen     = $row_alm["id"];

    $valido = true;
/*
    $sql = "SELECT COUNT(*) Factura FROM th_aduana WHERE factura='$factura' AND factura<>'';";
    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") "; exit;}
    $row_f = mysqli_fetch_assoc($res);
    $num_factura = $row_f["Factura"];

    if($num_factura > 0) $valido = false;

        if($valido == true)
*/
        $ga = new \OrdenCompra\OrdenCompra();


        $procesar_entrada = true; $bls_unicos_por_lp = true; $lp_unico = true; $primer_lp_distinto = ""; $primer_bl_distinto = "";
        $n_cambio_lp = 0; $n_cambio_bl = 0; $bl_no_existente = ""; $lp_ocupado = "";$numerodeorden_ocupado = ""; $num_ord_disponible = true;
        $factura_disponible = true; $factura_ocupada = ""; $articulo_existe = true; $articulo_no_existe_desc = ""; $proveedor_existe = true; $proveedor_no_existe_desc = ""; $protocolo_existe = true; $protocolo_no_existe_desc = ""; $fecha_entrada_rev = ""; $fecha_valida = true; $existencias_rev = "";
        $linea = 1;
        $lineas = $xlsx->rows()  ;

         $sql = "SELECT IFNULL(Valor, '') AS instancia FROM t_configuraciongeneral WHERE cve_conf = 'instancia' LIMIT 1";
        if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación 2: (" . mysqli_error($conn) . ") "; exit;}
        $row_instancia = mysqli_fetch_assoc($res);
        $instancia     = $row_instancia["instancia"];
/*
        if($instancia != 'foam' && $instancia != 'repremundo')
        foreach ($xlsx->rows() as $row)
        {
            if($linea == 1) {
                $linea++;continue;
            }

            $lp = trim($this->pSQL($row[self::LP_OCMSV]));
            $bl = trim($this->pSQL($row[self::BL_OCMSV]));
            $factura_oc = trim($this->pSQL($row[self::FOLIO_OCMSV]));
            $articulo_rev = trim($this->pSQL($row[self::CVE_ARTICULO_OCMSV]));
            $proveedor_rev = trim($this->pSQL($row[self::PROVEEDOR_OCMSV]));
            $protocolo_rev = trim($this->pSQL($row[self::PROTOCOLO_OCMSV]));
            $fecha_entrada_rev = trim($this->pSQL($row[self::FEC_ENTRADA_OCMSV]));
            $existencias_rev = trim($this->pSQL($row[self::EXISTENCIA_OCMSV]));

            //if($articulo_rev && !$existencias_rev)
            //{
            //    $procesar_entrada = false;
            //    break;
            //}

            if(!$ga->VerificarFacturaOC_ERP_Repetido($factura_oc))
            {
                $numerodeorden_ocupado = $factura_oc;
                $num_ord_disponible = false;
                $procesar_entrada = false;
                break;
            }

            //if(!$ga->VerificarFacturaEntrada_Repetida($factura_remision))
            //{
            //    $factura_ocupada = $factura_remision;
            //    $factura_disponible = false;
            //    $procesar_entrada = false;
            //    break;
            //}





//***************************************************************************************
//                         COMPROBAR FECHAS VALIDAS
//***************************************************************************************

            if($fecha_entrada_rev != '')
            {
                $f_rev = explode("-", $fecha_entrada_rev);

                $year = $f_rev[0];
                $mes  = $f_rev[1];
                $dia  = $f_rev[2];

                if(count($f_rev) != 3)
                {
                    $f_rev = explode("-", date_create($fecha_entrada_rev));
                    $year = $f_rev[0];
                    $mes  = $f_rev[1];
                    $dia  = $f_rev[2];
                }

                if(strlen($year) < 4) {$fecha_valida = false; $procesar_entrada = false; break;}
                if(strlen($mes) < 2 || $mes < 1 || $mes > 12) {$fecha_valida = false; $procesar_entrada = false; break;}
                if(strlen($dia) < 2 || $dia < 1 || $mes > 31) {$fecha_valida = false; $procesar_entrada = false; break;}
            }

//***************************************************************************************
//                         COMPROBAR ARTICULOS EXISTENTES
//***************************************************************************************

            $sql = "SELECT COUNT(*) AS existe
                    FROM c_articulo a
                    INNER JOIN Rel_Articulo_Almacen r ON r.Cve_Almac = $id_almacen AND r.Cve_Articulo = a.cve_articulo
                    WHERE a.cve_articulo = '$articulo_rev'
                    ";
            $rs = mysqli_query($conn, $sql);
            $row_max = mysqli_fetch_array($rs, MYSQLI_ASSOC);
            $existe = $row_max["existe"];
            //echo "-6-"." _ EXISTE = ".$existe." SQL = ".$sql;exit;

            //echo ":lp = ".$lp.":bl = ".$bl.":factura_oc = ".$factura_oc.":articulo_rev = ".$articulo_rev.":proveedor_rev = ".$proveedor_rev.":protocolo_rev = ".$protocolo_rev.":fecha_entrada_rev = ".$fecha_entrada_rev.":existencias_rev = ".$existencias_rev."";exit;
            //echo ":lp = ".$lp.":bl = ".$bl.":factura_oc = ".$factura_oc.":articulo_rev = ".$articulo_rev.":proveedor_rev = ".$proveedor_rev.":protocolo_rev = ".$protocolo_rev.":fecha_entrada_rev = ".$fecha_entrada_rev.":existencias_rev = ".$existencias_rev.""."-6-".$sql;exit;

            if($existe == 0)
            {
                $articulo_existe = false;
                $articulo_no_existe_desc = $articulo_rev;
                $procesar_entrada = false;
                break;
            }
//***************************************************************************************
//                         COMPROBAR PROVEEDORES EXISTENTES
//***************************************************************************************

            $sql = "SELECT COUNT(*) AS existe
                    FROM c_proveedores p
                    WHERE p.cve_proveedor = '$proveedor_rev'
                    ";
            $rs = mysqli_query($conn, $sql);
            $row_max = mysqli_fetch_array($rs, MYSQLI_ASSOC);
            $existe = $row_max["existe"];

            if($existe == 0)
            {
                $proveedor_existe = false;
                $proveedor_no_existe_desc = $proveedor_rev;
                $procesar_entrada = false;
                break;
            }
//***************************************************************************************
//                         COMPROBAR PROTOCOLOS EXISTENTES
//***************************************************************************************

            if($protocolo_rev != '')
            {
                $sql = "SELECT COUNT(*) AS existe
                        FROM t_protocolo p
                        WHERE p.ID_Protocolo = '$protocolo_rev'
                        ";
                $rs = mysqli_query($conn, $sql);
                $row_max = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                $existe = $row_max["existe"];

                if($existe == 0)
                {
                    $protocolo_existe = false;
                    $protocolo_no_existe_desc = $protocolo_rev;
                    $procesar_entrada = false;
                    break;
                }
            }
//***************************************************************************************

            if($lp != '')
            {
                $sql = "SELECT COUNT(*) AS ocupado 
                        FROM td_entalmacenxtarima et 
                        INNER JOIN c_charolas ch ON ch.clave_contenedor = et.ClaveEtiqueta AND ch.cve_almac = $id_almacen
                        WHERE ch.CveLP = '$lp'";
                $rs = mysqli_query($conn, $sql);
                $row_max = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                $ocupado = $row_max["ocupado"];


                if($bl != '')
                {
                    $sql = "SELECT COUNT(*) AS existe
                            FROM c_ubicacion u
                            INNER JOIN c_almacen a ON a.cve_almac = u.cve_almac
                            WHERE u.CodigoCSD = CONVERT('$bl', CHAR) AND a.cve_almacenp = $id_almacen AND u.Activo = 1";
                    $rs = mysqli_query($conn, $sql);
                    $row_max = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                    $existe = $row_max["existe"];

                    if($existe == 0)
                    {
                        $bl_no_existente = $bl;
                        $procesar_entrada = false;
                        break;
                    }
                }

                if($ocupado > 0)
                {
                    $lp_unico = false;
                    $lp_ocupado = $lp;
                    $procesar_entrada = false;
                    break;
                }

                if($lp != $primer_lp_distinto)
                {
                   $primer_lp_distinto = $lp;
                   $primer_bl_distinto = "";
                   $n_cambio_lp = 0;
                   $n_cambio_bl = 0;
                }
                else
                    $n_cambio_lp++;

                if($bl != $primer_bl_distinto)
                {
                   $primer_bl_distinto = $bl;
                   //$n_cambio_lp = 0;
                   //$n_cambio_bl = 0;
                }
                else
                    $n_cambio_bl++;

                if($n_cambio_bl != $n_cambio_lp)
                {
                    $bls_unicos_por_lp = false;
                    $procesar_entrada = false;
                    $n_cambio_lp = 0;
                    $n_cambio_bl = 0;
                    break;
                }
            }
            else if($bl != '')
            {
                $sql = "SELECT COUNT(*) AS existe
                        FROM c_ubicacion u
                        INNER JOIN c_almacen a ON a.cve_almac = u.cve_almac
                        WHERE u.CodigoCSD = CONVERT('$bl', CHAR) AND a.cve_almacenp = $id_almacen AND u.Activo = 1";
                $rs = mysqli_query($conn, $sql);
                $row_max = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                $existe = $row_max["existe"];

                if($existe == 0)
                {
                    $bl_no_existente = $bl;
                    $procesar_entrada = false;
                    break;
                }
            }

        }
*/
    if($procesar_entrada)
    {//if($procesar_entrada)

        $formato_fecha_incorrecto = '';
        $linea = 0;
        $lineas = $xlsx->rows()  ;
        $bls_no_existentes = "";
        foreach ($xlsx->rows() as $row)
        {
          if($linea >= 1)
          {
            $factura = trim($this->pSQL($row[self::FOLIO_OCMSV]));
            $Cve_Almac = $almacenes;//$this->pSQL($row[3]);
            $element = OrdenesDeCompra::where('Factura', $factura)->where('Cve_Almac', $Cve_Almac)->first(); //cambiar las tablas e consulta


            $bl = trim($this->pSQL($row[self::BL_OCMSV]));

            $sql = "SELECT COUNT(*) as bl_existe FROM c_ubicacion WHERE CodigoCSD = '{$bl}'";
            if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación 2: (" . mysqli_error($conn) . ") "; exit;}
            $row_bl = mysqli_fetch_assoc($res);
            $bl_existe = $row_bl["bl_existe"];

            if($bl_existe == 0) {$bls_no_existentes .= $bl."\n"; $bl = '';}

            $cantidad_pedida = trim($this->pSQL($row[self::CANT_PEDIDA_OCMSV]));

            if(!$cantidad) $cantidad = $cantidad_pedida;

              $proyecto = trim($this->pSQL($row[self::PROYECTO_OCMSV]));

//REGLAS DE IMPORTACION DE SERIE, LOTE Y CADUCIDAD:
//hay dos columnas lote y caducidad > ningun dato es obligatorio en la importacion. Pero...
//1 si el producto en la base de datos no pide ni lote, ni lote +caducidad, ni serie aunque encuentres un dato en el excel NO DEBES SUBIRLO
//2 Si el producto maneja lote, no puede manejar serie, y NO puede llevar caducidad. > debe subirse solo lote y en caducidad se imprime la fecha del día de importacion de datos > esa fecha se toma como la fecha de ingreso y sirve para el concepto de primeras entradas primeras salidas
//3 si el producto en la.base de datos tiene el check de serie > el dato de la columna lote será el número de serie y NO DEBE SUBIR NADA EN FECHA
//4 si el producto en la base de datos tiene el check de lote y caducidad > FORZOSAMENTE (DE AJURO) DEBE TENER AMBOS DATOS NO PUEDE SUBIR LOTE SIN CADUCIDAD NI CADUCIDAD SIN LOTE

            $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
            mysqli_set_charset($conn, 'utf8');
            $cve_art = trim($this->pSQL($row[self::CVE_ARTICULO_OCMSV]));
            $sql = "SELECT cve_articulo, des_articulo, control_lotes, control_numero_series, IFNULL(Caduca, 'N') Caduca FROM c_articulo WHERE cve_articulo = '".$cve_art."'";
            if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") "; exit;}

            $valores = mysqli_fetch_assoc($res);

            $tiene_serie     = $valores["control_numero_series"];
            $tiene_lote      = $valores["control_lotes"];
            $tiene_caducidad = $valores["Caduca"];

            $cve_proveedor = trim($this->pSQL($row[self::PROVEEDOR_OCMSV]));

            $sql = "SELECT ID_Proveedor FROM c_proveedores WHERE cve_proveedor = '{$cve_proveedor}'";
            if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación 2: (" . mysqli_error($conn) . ") "; exit;}
            $row_prov = mysqli_fetch_assoc($res);
            $proveedor_id     = $row_prov["ID_Proveedor"];
            //echo $sql." ---  ".$proveedor_id; exit;


            //************************************************************************
            //Implementación Regla 1
            //************************************************************************
            $valores_regla_1 = true;
/*
            if($tiene_serie == 'N' && $tiene_lote == 'N' && $tiene_caducidad == 'N')
              $valores_regla_1 = false;
*/
            //************************************************************************
            //Implementación Regla 2
            //************************************************************************
            $valores_regla_2 = true;
            if($tiene_serie == 'S' && $tiene_lote == 'S')
              $valores_regla_2 = false;
            //************************************************************************
            //Implementación Regla 3
            //************************************************************************
            $valores_regla_3 = false;
            if($tiene_serie == 'S' && $tiene_lote == 'N')
              $valores_regla_3 = true;
            //************************************************************************
            //Implementación Regla 4
            //************************************************************************
            $valores_regla_4 = false;
            $lote_y_caducidad_tipo1 = false; $lote_y_caducidad_tipo2 = false; $lote_y_caducidad_tipo3 = false;
            if($tiene_lote == 'S' && $tiene_caducidad == 'S' && $tiene_serie == 'N')
            {
              //if($row[4] != "" && $row[5] != "")
                 $lote_y_caducidad_tipo1 = true;
            }

            if($tiene_lote == 'S' && $tiene_caducidad == 'N' && $tiene_serie == 'N')
            {
              //if($row[4] != "")
                 $lote_y_caducidad_tipo2 = true;
            }

            if($tiene_lote == 'N' && $tiene_caducidad == 'N' && $tiene_serie == 'S')
            {
              //if($row[4] != "")
                 $lote_y_caducidad_tipo3 = true;
            }

            if($lote_y_caducidad_tipo1 == true || $lote_y_caducidad_tipo2 == true || $lote_y_caducidad_tipo3 == true || $valores_regla_1)
              $valores_regla_4 = true;
            //************************************************************************

            $valores_reglas = false;
            if($valores_regla_1 == true && $valores_regla_2 == true && $valores_regla_4 == true) $valores_reglas = true;


        if($valores_reglas)
        {
            if($current_pedido != $factura)
            {
              //query para traer el valor que se la asigna a num_pedimento y compara con num_orden
              //$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
              mysqli_set_charset($conn, 'utf8');

            if($protocolo == '')
                $protocolo = trim($this->pSQL($row[self::PROTOCOLO_OCMSV]));

            $sql = "UPDATE t_protocolo SET FOLIO = FOLIO+1  WHERE ID_Protocolo = '$protocolo'";
            if (!($res = mysqli_query($conn, $sql)))
            {
              echo "Falló la preparación 2: (" . mysqli_error($conn) . ") "; exit;
            }

            if($Consecut == '')
            {
                $sql  = "SELECT FOLIO as Consec_protocolo from t_protocolo where ID_Protocolo='$protocolo'";

                if (!($res = mysqli_query($conn, $sql)))
                {
                  echo "Falló la preparación 1: (" . mysqli_error($conn) . ") "; exit;
                }

                $Consecut = mysqli_fetch_assoc($res)["Consec_protocolo"];
            }


              $sql = "SELECT IFNULL(MAX(num_pedimento), 0) AS Maximo FROM th_aduana";
              if (!($res = mysqli_query($conn, $sql)))
              {
                echo "Falló la preparación 1: (" . mysqli_error($conn) . ") "; exit;
              }
              
              if($current_pedido != $factura)
              {
                $max = mysqli_fetch_assoc($res)["Maximo"];
                $num_pedimento = ($max+1);
                $current_pedido = $factura;
              }
            //}

            if($element != NULL){
                $model_orden = $element; 
            }
            else {
                $model_orden = new OrdenesDeCompra(); 
            }
/*           
            //$data_pedido = OrdenesDeCompra::where('factura',$row[self::FOLIO_OCMSV])->first();
            if($current_pedido != $row[self::FOLIO_OCMSV])//{//if( $data_pedido == NULL ){
              $model_orden = new OrdenesDeCompra();
            if(!$current_pedido) $current_pedido = 0;
              $model_orden->factura             = $current_pedido; //$this->pSQL($row[0]);
*/

              //$excel_date = $this->pSQL($row[1]);//$row[1];

              //$unix_date = ($excel_date - 25569) * 86400;
              //$excel_date = 25569 + ($unix_date / 86400);
              //$unix_date = ($excel_date - 25569) * 86400;
              //$Fecha_convert = gmdate("d-m-Y", $unix_date);

              //$date=date_create($excel_date);
              //if(!$date)
              //{
              //      $formato_fecha_incorrecto = "Formato de Fecha Incorrecto, debe ser dd-mm-aaaa con la celda en formato texto";
              //      break;
              //}
              //$Fecha_convert = date_format($date,"Y-m-d");

            //$cve_proveedor = $this->pSQL($row[self::PROVEEDOR_OCMSV]);

           // $sql = "SELECT ID_Proveedor FROM c_proveedores WHERE cve_proveedor = '{$cve_proveedor}'";
           // if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación 2: (" . mysqli_error($conn) . ") "; exit;}
           // $row_prov = mysqli_fetch_assoc($res);
           // $proveedor     = $row_prov["ID_Proveedor"];

            $id_user = $_SESSION["id_user"];
            $cve_usuario_entrada = $_SESSION["cve_usuario"];
            //if($zonarecepcioni!= '')
            //{
            //echo "CVEProvseedor = ".$cve_proveedor;exit;

            /*
              $sql = "INSERT INTO th_entalmacen(Cve_Almac, Fec_Entrada, Fol_OEP, Cve_Usuario, STATUS, Cve_Autorizado, tipo, BanCrossD, id_ocompra, HoraInicio, ID_Protocolo, Consec_protocolo, cve_ubicacion, HoraFin, Proyecto, Fact_Prov, Cve_Proveedor) VALUES('{$almacenes}', NOW(), '{$factura}', '{$cve_usuario_entrada}', 'E', '', 'OC', 'N', {$num_pedimento}, NOW(), '{$protocolo}', '{$Consecut}', '{$zonarecepcioni}', NOW(), '{$proyecto}','{$factura}', (SELECT ID_Proveedor FROM c_proveedores WHERE cve_proveedor = '$cve_proveedor'))";
              if (!($res = mysqli_query($conn, $sql)))
              {
                echo $sql;
                echo " --- Falló la preparación 3: (" . mysqli_error($conn) . ") "; exit;
              }
              */
            //}
              $statusOC = 'T';
              if($registrar_soloOC)
                $statusOC = 'C';

              $model_orden->fech_pedimento      = $fecha_actual;

              $model_orden->fech_llegPed        = $fecha_actual;

              $model_orden->Cve_Almac           = $almacenes;//$this->pSQL($row[3]);
              //$model_orden->cve_articulo     = $this->pSQL($row[4]);//esto es en td_aduana
              $model_orden->status              = $statusOC;//$this->pSQL($row[5]);
              $model_orden->ID_Proveedor        = $empresa;//$this->pSQL($row[6]);
              $model_orden->num_pedimento       = trim($this->pSQL($num_pedimento));
              $model_orden->ID_Protocolo        = $protocolo;//$this->pSQL($row[8]);
              $model_orden->Activo              = 1;
              $model_orden->presupuesto         = "";//$this->pSQL($row[10]);
              $model_orden->recurso             = "";//$this->pSQL($row[11]);
              $model_orden->procedimiento       = $cve_proveedor;//$this->pSQL($row[12]);
              $model_orden->fechaDeFallo        = "";//$this->pSQL($row[13]);
              $model_orden->plazoDeEntrega      = "";//$this->pSQL($row[14]);
              $model_orden->condicionesDePago   = "";//$this->pSQL($row[15]);
              $model_orden->lugarDeEntrega      = "";//$this->pSQL($row[16]);
              $model_orden->Proyecto            = trim($this->pSQL($row[self::PROYECTO_OCMSV]));
              $model_orden->dictamen            = "";//$this->pSQL($row[18]);
              $model_orden->Factura             = $factura;
              $model_orden->Consec_protocolo    = $Consecut;
              $model_orden->Tipo_Cambio         = $tipo_cambio;
              $model_orden->areaSolicitante     = "";//$this->pSQL($row[19]);
              $model_orden->numSuficiencia      = "";//$this->pSQL($row[20]);
              $model_orden->fechaSuficiencia    = "";//$this->pSQL($row[21]);
              $model_orden->fechaContrato       = "";//$this->pSQL($row[22]);
              $model_orden->montoSuficiencia    = "";//$this->pSQL($row[23]);
              $model_orden->numeroContrato      = "";//$this->pSQL($row[24]);
              $model_orden->cve_usuario         = $_SESSION["id_user"];
              $model_orden->save();


              if(!$registrar_soloOC)
              {
                  $sql = "INSERT INTO th_entalmacen(Cve_Almac, Fec_Entrada, Fol_OEP, Cve_Usuario, STATUS, Cve_Autorizado, tipo, BanCrossD, id_ocompra, HoraInicio, ID_Protocolo, Consec_protocolo, cve_ubicacion, HoraFin, Proyecto, Fact_Prov, Cve_Proveedor) VALUES('{$almacenes}', NOW(), '{$factura}', '{$cve_usuario_entrada}', 'E', '', 'OC', 'N', {$num_pedimento}, NOW(), '{$protocolo}', '{$Consecut}', '{$zonarecepcioni}', NOW(), '{$proyecto}','{$factura}', (SELECT ID_Proveedor FROM c_proveedores WHERE cve_proveedor = '$cve_proveedor'))";
                  $fec_entrada = '';
                  if(trim($this->pSQL($row[self::FEC_ENTRADA_OCMSV])) != '')
                  {
                      $excel_date = trim($this->pSQL($row[self::FEC_ENTRADA_OCMSV]));
                      if($excel_date != "")
                      {
                          $date=date_create($excel_date);
                          $Fecha_convert = date_format($date,"Y-m-d");
                          $fec_entrada = $Fecha_convert;
                      }

                      $sql = "INSERT INTO th_entalmacen(Cve_Almac, Fec_Entrada, Fol_OEP, Cve_Usuario, STATUS, Cve_Autorizado, tipo, BanCrossD, id_ocompra, HoraInicio, ID_Protocolo, Consec_protocolo, cve_ubicacion, HoraFin, Proyecto, Fact_Prov, Cve_Proveedor) VALUES('{$almacenes}', '{$fec_entrada}', '{$factura}', '{$cve_usuario_entrada}', 'E', '', 'OC', 'N', {$num_pedimento}, NOW(), '{$protocolo}', '{$Consecut}', '{$zonarecepcioni}', NOW(), '{$proyecto}','{$factura}', (SELECT ID_Proveedor FROM c_proveedores WHERE cve_proveedor = '$cve_proveedor'))";
                  }

                  if (!($res = mysqli_query($conn, $sql)))
                  {
                    echo $sql;
                    echo " --- Falló la preparación 3: (" . mysqli_error($conn) . ") "; exit;
                  }
              }

            }
          
            $num_orden = $num_pedimento;
  /*
              $items = OrdenesDeCompraItems::where('num_orden', $num_orden)->first();

            if($items != NULL){
                $model = $items; 
            }
            else {
                $model = new OrdenesDeCompraItems(); 
            }
*/
            //if( $items == NULL )
            {
              $model = new OrdenesDeCompraItems();
              $cve_articulo = trim($this->pSQL($row[self::CVE_ARTICULO_OCMSV]));

              //$sql = "SELECT COUNT(*) as num_articulo FROM c_articulo WHERE cve_articulo = '$cve_articulo'";
              $sql = "SELECT COUNT(*) AS num_articulo FROM c_articulo a, Rel_Articulo_Almacen r  WHERE a.cve_articulo = '{$cve_articulo}' AND a.cve_articulo = r.Cve_Articulo AND r.Cve_Almac = $id_almacen";

              $rs = mysqli_query($conn, $sql);
              $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
              $num_articulo = $resul['num_articulo'];
                  $sql_tracking .= $sql."\n";

                  //echo $sql_tracking;
              if($num_articulo)
              {
  /*
                $sql = "SELECT COUNT(*) serie FROM c_articulo WHERE cve_articulo = '$cve_articulo' AND control_numero_series = 'S'";
                  $rs = mysqli_query($conn, $sql);
                  $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                  $serie = $resul['serie'];
*/
                  $sql = "SELECT DATE_FORMAT(CURDATE(), '%Y-%m-%d') fecha_actual FROM DUAL";
                  $rs = mysqli_query($conn, $sql);
                  $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                  $fecha_actual = $resul['fecha_actual'];

                  $lote = trim($this->pSQL($row[self::LOTE_OCMSV]));
                  $cantidad = trim($this->pSQL($row[self::EXISTENCIA_OCMSV]));

                  //if(!$lote || ($lote && $cantidad > 1 && $tiene_serie == 'S')) $lote = "";
                  if(!$lote) $lote = "";

                  if($tiene_serie == 'S') $cantidad = 1;

                  $model->cve_articulo     = $cve_articulo;
                  $model->cantidad         = $cantidad;
                  $model->num_orden        = $this->pSQL($num_pedimento);
                  $model->cve_lote         = $this->pSQL($lote);

                  //if($this->pSQL($row[self::COSTO_OCMSV]) != '')
                  $costo_oc = trim($this->pSQL($row[self::COSTO_OCMSV]));
                  if($costo_oc == '') $costo_oc = 0;

                  //$model->costo            = trim($this->pSQL($row[self::COSTO_OCMSV]));
                  $model->costo            = $costo_oc;
                  if($this->pSQL($row[self::FACTURA_ART_OCMSV]) != '')
                  $model->Factura          = trim($this->pSQL($row[self::FACTURA_ART_OCMSV]));

/*
                  $caducidad = "0000-00-00";
                  $excel_date = trim($this->pSQL($row[self::CADUCIDAD_OCMSV]));//$row[5];
                  if($excel_date != "")
                  {
                      //$unix_date = ($excel_date - 25569) * 86400;
                      //$excel_date = 25569 + ($unix_date / 86400);
                      //$unix_date = ($excel_date - 25569) * 86400;
                      //$Fecha_convert = gmdate("d-m-Y", $unix_date);
                      $date=date_create($excel_date);
                      //$Fecha_convert = date_format($date,"Y-m-d");
                      //$caducidad = $Fecha_convert;
                      $caducidad = $date;
                  }
                  if($row[self::LOTE_OCMSV] != "")
                  {
                    //if(($tiene_lote == 'S' || $tiene_serie == 'S') && $tiene_caducidad == 'N')
                       //$caducidad = $fecha_actual;

                    $model->caducidad = $caducidad;
                  }
                  else
                  {
                      $model->caducidad = "0000-00-00";
                  }

                  $sql = "SELECT COUNT(*) as num_lotes FROM c_lotes WHERE Lote = '$lote' AND cve_articulo = '$cve_articulo'";
                  $rs = mysqli_query($conn, $sql);
                  $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                  $num_lotes = $resul['num_lotes'];

                  $sql_tracking .= $sql." - ".$num_lotes."\n";
                  //$sql = "SELECT COUNT(*) serie_N FROM c_articulo WHERE cve_articulo = '$cve_articulo' AND control_numero_series = 'N'";
                  $sql = "SELECT control_numero_series FROM c_articulo WHERE cve_articulo = '$cve_articulo'";
                  $rs = mysqli_query($conn, $sql);
                  $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                  $serie_SN = $resul['control_numero_series'];

                  $sql_tracking .= $sql." - ".$serie_SN."\n";

                  if($num_lotes == 0 && $serie_SN == 'N' && $lote != '')
                  {
                      $sql = "INSERT INTO c_lotes(cve_articulo, Lote, Caducidad, Activo) VALUES('$cve_articulo', '$lote', DATE_FORMAT('$excel_date', '%Y-%m-%d'), 1)";
                      if($caducidad == "0000-00-00" || $tiene_caducidad == 'N')
                         $sql = "INSERT INTO c_lotes(cve_articulo, Lote, Activo) VALUES('$cve_articulo', '$lote', 1)";
                      $sql_tracking .= $sql."\n";
                      $rs = mysqli_query($conn, $sql);
                  }
*/

//**********************************************************************************************************************
//**********************************************************************************************************************
            $sql = "SELECT control_lotes, Caduca, control_numero_series FROM c_articulo WHERE cve_articulo = '{$cve_articulo}'";
            $rs = mysqli_query($conn, $sql);
            $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
            $control_lotes  = $resul['control_lotes'];
            $caduca         = $resul['Caduca'];
            $control_series = $resul['control_numero_series'];

            $caducidad = trim($this->pSQL($row[self::CADUCIDAD_OCMSV]));//$row[5];

            if($control_lotes == 'S')
            {
                $sql_charset = "SET NAMES 'utf8mb4';";
                $rs_charset = mysqli_query($conn, $sql_charset);

                $sql = "SELECT COUNT(*) as existe FROM c_lotes WHERE cve_articulo = '{$cve_articulo}' AND Lote = CONVERT('{$lote}', CHAR)";
                $rs = mysqli_query($conn, $sql);
                $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                $existe  = $resul['existe'];

                if(!$existe && $lote != '')
                {
                    if($caduca == 'S')
                    {
                          //$date=date_create($caducidad);
                          //$caducidad = date_format($date,"Y-m-d");
                    }

                    if(!$caducidad) 
                    {
                        $caducidad = '0000-00-00';
                        $sql = "INSERT INTO c_lotes(cve_articulo, Lote) VALUES ('{$cve_articulo}', CONVERT('{$lote}', CHAR))";
                    }
                    else
                        $sql = "INSERT INTO c_lotes(cve_articulo, Lote, Caducidad) VALUES ('{$cve_articulo}', CONVERT('{$lote}', CHAR), DATE_FORMAT('{$caducidad}', '%Y-%m-%d'))";

                    $rs = mysqli_query($conn, $sql);
                }
            }
            else if($control_series == 'S')
            {
                $sql = "SELECT COUNT(*) as existe FROM c_serie WHERE cve_articulo = '{$cve_articulo}' AND numero_serie = CONVERT('{$lote}', CHAR)";
                $rs = mysqli_query($conn, $sql);
                $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                $existe  = $resul['existe'];

                if(!$existe && $lote != '') 
                {
                    $sql = "INSERT INTO c_serie(cve_articulo, numero_serie, fecha_ingreso) VALUES ('{$cve_articulo}', CONVERT('{$lote}', CHAR), NOW())";
                    $rs = mysqli_query($conn, $sql);
                }
            }else
            {
                $lote = '';
            }
//**********************************************************************************************************************
//**********************************************************************************************************************
                  $model->costo            = trim($this->pSQL($row[self::COSTO_OCMSV]));
                  $model->Activo           = 1;

                  $existe = 0;
                  //if($serie_SN == 'N')
                  //{
                      $sql = "SELECT COUNT(*) existe FROM td_aduana WHERE cve_articulo = '$cve_articulo' AND IFNULL(cve_lote, '') = '$lote' AND num_orden = '$num_pedimento'";
                      $rs = mysqli_query($conn, $sql);
                      $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                      $existe = $resul['existe'];
                  //}

                  if($existe)// && $serie_SN == 'N' && $tiene_lote == 'S')
                  {
                        $costo_oc = trim($this->pSQL($row[self::COSTO_OCMSV]));
                        if($costo == '') $costo = 0;
                      $sql = "UPDATE td_aduana SET cantidad=cantidad+$cantidad WHERE cve_articulo = '$cve_articulo' AND IFNULL(cve_lote, '') = '$lote' AND num_orden = '$num_pedimento'";
                      $rs = mysqli_query($conn, $sql);


                    //if($zonarecepcioni!= '')
                    if(!$registrar_soloOC)
                    {
                        $cantidad_pedida = trim($this->pSQL($row[self::CANT_PEDIDA_OCMSV]));
                        if(!$cantidad_pedida) $cantidad_pedida = 0;
                      $sql = "UPDATE td_entalmacen SET CantidadPedida=CantidadPedida+$cantidad_pedida, CantidadRecibida=CantidadRecibida+$cantidad WHERE cve_articulo = '$cve_articulo' AND cve_lote = '$lote' AND num_orden = '$num_pedimento'";
                      if($zonarecepcioni)
                      $sql = "UPDATE td_entalmacen SET CantidadPedida=CantidadPedida+$cantidad_pedida, CantidadRecibida=CantidadRecibida+$cantidad, CantidadUbicada=CantidadUbicada+$cantidad WHERE cve_articulo = '$cve_articulo' AND cve_lote = '$lote' AND num_orden = '$num_pedimento'";
                      $rs = mysqli_query($conn, $sql);


                      $sql = "INSERT INTO t_cardex(cve_articulo, cve_lote, fecha, origen, destino, stockinicial, cantidad, ajuste, id_TipoMovimiento, cve_usuario, Cve_Almac, Activo) VALUES ('$cve_articulo', '$lote', NOW(), (SELECT MAX(Fol_Folio) as maximo FROM th_entalmacen),'$zonarecepcioni', $cantidad, $cantidad, 0, 1, '$cve_usuario_entrada', (SELECT id FROM c_almacenp WHERE clave = '$almacenes'), 1)";
                      $rs = mysqli_query($conn, $sql);
                    }
                      //$model->save();

                      //$sql = "SELECT MAX(ID_Aduana) Max_Aduana FROM td_aduana";
                      //$rs = mysqli_query($conn, $sql);
                      //$resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                      //$max_Aduana = $resul['Max_Aduana'];

                      //$sql = "DELETE FROM td_aduana WHERE ID_Aduana = $max_Aduana";
                      //$rs = mysqli_query($conn, $sql);
                  }
                  else if(!$registrar_soloOC)
                  {
                    //if($zonarecepcioni!= '')
                    //{
                    $cantidad_pedida = trim($this->pSQL($row[self::CANT_PEDIDA_OCMSV]));
                    if(!$cantidad_pedida) $cantidad_pedida = 0;

                    $cantidad_ubicada = 0;
                    if($bl != '') $cantidad_ubicada = $cantidad;

                      $sql = "INSERT INTO td_entalmacen(fol_folio, cve_articulo, cve_lote, CantidadPedida, CantidadRecibida, CantidadDisponible, CantidadUbicada, status, cve_usuario, cve_ubicacion, fecha_inicio, fecha_fin, num_orden) VALUES((SELECT MAX(Fol_Folio) as maximo FROM th_entalmacen), '$cve_articulo', '$lote', $cantidad, $cantidad, 0, $cantidad_ubicada, 'E', '$cve_usuario_entrada', '$zonarecepcioni', NOW(), NOW(), '$num_pedimento')";
                      $rs = mysqli_query($conn, $sql);

                      $sql = "INSERT INTO t_cardex(cve_articulo, cve_lote, fecha, origen, destino, stockinicial, cantidad, ajuste, id_TipoMovimiento, cve_usuario, Cve_Almac, Activo) VALUES ('$cve_articulo', '$lote', NOW(), (SELECT MAX(Fol_Folio) as maximo FROM th_entalmacen),'$zonarecepcioni', $cantidad, $cantidad, 0, 1, '$cve_usuario_entrada', (SELECT id FROM c_almacenp WHERE clave = '$almacenes'), 1)";
                      $rs = mysqli_query($conn, $sql);
                   //}
                    $model->save();
                  }
                  else
                    $model->save();


                      if($row[self::LP_OCMSV] != '' || ($row[self::LP_OCMSV] == '' && $palletizar_oc == 1))
                      {
                        $lp = $row[self::LP_OCMSV];

                        if($lp)
                        {
                            $sql = "SELECT IFNULL(a.clave, '') AS almacen_pallet 
                                    FROM c_charolas ch
                                    LEFT JOIN c_almacenp a ON a.id = ch.cve_almac
                                    WHERE IFNULL(ch.CveLP, ch.clave_contenedor) = '$lp'";
                            $rs = mysqli_query($conn, $sql);
                            $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);

                            if($resul['almacen_pallet'] != $almacenes && $resul['almacen_pallet'] != '')
                            {
                                $mensaje_pallet_diferente_almacen .= "\n El Pallet $lp No pertenece al almacén actual ";
                                continue;
                            }
                        }
/*
                        $sql = "SELECT (SELECT MAX(ch.IDContenedor)+1 FROM c_charolas ch) AS id_contenedor, (SELECT id FROM c_almacenp WHERE clave = '$almacenes') as cve_almac, descripcion, tipo, alto, ancho, fondo, peso, pesomax, capavol FROM c_charolas WHERE TipoGen = 1 AND tipo = 'Pallet' ORDER BY IDContenedor ASC LIMIT 1";
                        $rs = mysqli_query($conn, $sql);
                        $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);

                        if(!$resul['id_contenedor']) break;
*/
                        //IDContenedor NOT IN (SELECT ntarima FROM ts_existenciatarima) AND
                        $sql = "SELECT IDContenedor AS id_contenedor, clave_contenedor, (SELECT id FROM c_almacenp WHERE clave = '$almacenes') as cve_almac, descripcion, tipo, alto, ancho, fondo, peso, pesomax, capavol FROM c_charolas WHERE  CveLP = '{$lp}' AND cve_almac = (SELECT id FROM c_almacenp WHERE clave = '$almacenes')";
                        $rs = mysqli_query($conn, $sql);
                        $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                        if(mysqli_num_rows($rs) > 0)
                           $existe_lp = $resul['id_contenedor'];
                        else
                        {
                            $sql = "SELECT (SELECT MAX(ch.IDContenedor)+1 FROM c_charolas ch) AS id_contenedor, clave_contenedor, (SELECT id FROM c_almacenp WHERE clave = '$almacenes') as cve_almac, descripcion, tipo, alto, ancho, fondo, peso, pesomax, capavol FROM c_charolas WHERE TipoGen = 1 AND tipo = 'Pallet' ORDER BY IDContenedor ASC LIMIT 1";
                            $rs = mysqli_query($conn, $sql);
                            $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                        }

                        //if(!$resul['id_contenedor']) break;
                        $clave_contenedor = "";
                       if($resul['id_contenedor'])
                       {
                            $id_contenedor    = $resul['id_contenedor'];
                            $id_almacen       = $resul['cve_almac'];
                            $clave_contenedor = $resul['clave_contenedor'];
                            $descripcion      = $resul['descripcion'];
                            $tipo             = $resul['tipo'];
                            $alto             = $resul['alto'];
                            $ancho            = $resul['ancho'];
                            $fondo            = $resul['fondo'];
                            $peso             = $resul['peso'];
                            $pesomax          = $resul['pesomax'];
                            $capavol          = $resul['capavol'];
                        }

                        $label_lp = $lp;

                        if($lp == "")
                           $label_lp = "LP".str_pad($resul['id_contenedor'].$num_pedimento, 9, "0", STR_PAD_LEFT);

                       if(!$registrar_soloOC)
                       {
                        $sql = "INSERT IGNORE INTO c_charolas(cve_almac, clave_contenedor, descripcion, Permanente, tipo, alto, ancho, fondo, peso, pesomax, capavol, CveLP, TipoGen) 
                                VALUES ({$id_almacen}, '{$label_lp}', '{$descripcion}', 0, 'Pallet', {$alto}, {$ancho}, {$fondo}, {$peso}, {$pesomax}, {$capavol}, '{$label_lp}', 0)";
                        $rs = mysqli_query($conn, $sql);
                        }

                        $sql_tracking .= $sql."\n";

                    if($palletizar_oc || $label_lp)
                    {

                        $sql = "SELECT COUNT(*) AS existe FROM td_entalmacenxtarima WHERE fol_folio = (SELECT MAX(Fol_Folio) as maximo FROM th_entalmacen) AND cve_articulo = '{$cve_articulo}' AND cve_lote = CONVERT('{$lote}', CHAR) AND ClaveEtiqueta = '{$clave_contenedor}'";

                        //$sql_entradas = $sql; 
                        $rs = mysqli_query($conn, $sql);
                        $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                        $existeTarima = $resul['existe'];

                        if(!$registrar_soloOC)
                        {
                            $ubicada = "N";
                            if($bl != '') $ubicada = "S";
                            $sql = "INSERT INTO td_entalmacenxtarima(fol_folio, cve_articulo, cve_lote, ClaveEtiqueta, Cantidad, Ubicada, PzsXCaja) 
                                    VALUES ((SELECT MAX(Fol_Folio) as maximo FROM th_entalmacen), '{$cve_articulo}', '{$lote}', '{$clave_contenedor}', {$cantidad}, '$ubicada', 1) ON DUPLICATE KEY UPDATE Cantidad = Cantidad + {$cantidad}";
                            $rs = mysqli_query($conn, $sql);

                            $sql = "INSERT INTO t_MovCharolas (id_kardex, Cve_Almac, ID_Contenedor, Fecha, Origen, Destino, Id_TipoMovimiento, Cve_Usuario, Status) VALUES ((SELECT MAX(id) FROM t_cardex), '$almacenes', $id_contenedor, NOW(), (SELECT MAX(Fol_Folio) as maximo FROM th_entalmacen), '$zonarecepcioni', 1, '$cve_usuario_entrada', 'I')";
                            $rs = mysqli_query($conn, $sql);

                            $proyecto = trim($this->pSQL($row[self::PROYECTO_OCMSV]));
                            $sql = "INSERT INTO t_trazabilidad_existencias (cve_almac, idy_ubica, cve_articulo, cve_lote, cantidad, ntarima, id_proveedor, folio_entrada, folio_oc, factura_ent, factura_oc, proyecto, id_tipo_movimiento) VALUES ({$id_almacen}, NULL, '{$cve_articulo}', CONVERT('{$lote}', CHAR), {$cantidad}, {$id_contenedor}, {$proveedor_id}, (SELECT MAX(Fol_Folio) as maximo FROM th_entalmacen), '{$num_pedimento}', '{$factura}', '{$factura}', '{$proyecto}', 1)";
                            $rs = mysqli_query($conn, $sql);

                            $sql = "INSERT INTO td_aduanaxtarima(Num_Orden, Cve_Articulo, Cve_Lote, ClaveEtiqueta, Cantidad, Recibida) 
                                    VALUES ('$num_pedimento', '{$cve_articulo}', '{$lote}', '{$label_lp}', {$cantidad}, 'N') ON DUPLICATE KEY UPDATE Cantidad = Cantidad + {$cantidad}";
                            if($existeTarima)
                                $sql = "UPDATE td_aduanaxtarima SET Cantidad = Cantidad + {$cantidad} WHERE Num_Orden = '{$num_pedimento}' AND Cve_Articulo = '{$cve_articulo}' AND Cve_Lote = CONVERT('{$lote}', CHAR) AND ClaveEtiqueta = '{$label_lp}'";
                            $rs = mysqli_query($conn, $sql);
                        }
                        else
                        {
                            $proyecto = trim($this->pSQL($row[self::PROYECTO_OCMSV]));
                            $sql = "INSERT INTO t_trazabilidad_existencias (cve_almac, idy_ubica, cve_articulo, cve_lote, cantidad, ntarima, id_proveedor, folio_entrada, folio_oc, factura_ent, factura_oc, proyecto, id_tipo_movimiento) VALUES ({$id_almacen}, NULL, '{$cve_articulo}', CONVERT('{$lote}', CHAR), {$cantidad}, {$id_contenedor}, {$proveedor_id}, NULL, '{$num_pedimento}', '{$factura}', '{$factura}', '{$proyecto}', 1)";
                            $rs = mysqli_query($conn, $sql);

                            $sql = "INSERT INTO td_aduanaxtarima(Num_Orden, Cve_Articulo, Cve_Lote, ClaveEtiqueta, Cantidad, Recibida) 
                                    VALUES ('$num_pedimento', '{$cve_articulo}', '{$lote}', '{$label_lp}', {$cantidad}, 'N') ON DUPLICATE KEY UPDATE Cantidad = Cantidad + {$cantidad}";
                            $rs = mysqli_query($conn, $sql);
                        }

                    }
                    else
                    {
                        $proyecto = trim($this->pSQL($row[self::PROYECTO_OCMSV]));
                        $sql = "INSERT INTO t_trazabilidad_existencias (cve_almac, idy_ubica, cve_articulo, cve_lote, cantidad, ntarima, id_proveedor, folio_entrada, folio_oc, factura_ent, factura_oc, proyecto, id_tipo_movimiento) VALUES ({$id_almacen}, NULL, '{$cve_articulo}', CONVERT('{$lote}', CHAR), {$cantidad}, NULL, {$proveedor_id}, (SELECT MAX(Fol_Folio) as maximo FROM th_entalmacen), '{$num_pedimento}', '{$factura}', '{$factura}', '{$proyecto}', 1)";

                        if(!$registrar_soloOC)
                            $sql = "INSERT INTO t_trazabilidad_existencias (cve_almac, idy_ubica, cve_articulo, cve_lote, cantidad, ntarima, id_proveedor, folio_entrada, folio_oc, factura_ent, factura_oc, proyecto, id_tipo_movimiento) VALUES ({$id_almacen}, NULL, '{$cve_articulo}', CONVERT('{$lote}', CHAR), {$cantidad}, {$id_contenedor}, {$proveedor_id}, NULL, '{$num_pedimento}', '{$factura}', '{$factura}', '{$proyecto}', 1)";

                        $rs = mysqli_query($conn, $sql);
                    }


                      }
                    else
                    {
                        $proyecto = trim($this->pSQL($row[self::PROYECTO_OCMSV]));
                        $sql = "INSERT INTO t_trazabilidad_existencias (cve_almac, idy_ubica, cve_articulo, cve_lote, cantidad, ntarima, id_proveedor, folio_entrada, folio_oc, factura_ent, factura_oc, proyecto, id_tipo_movimiento) VALUES ({$id_almacen}, NULL, '{$cve_articulo}', CONVERT('{$lote}', CHAR), {$cantidad}, NULL, {$proveedor_id}, (SELECT MAX(Fol_Folio) as maximo FROM th_entalmacen), '{$num_pedimento}', '{$factura}', '{$factura}', '{$proyecto}', 1)";
                        $rs = mysqli_query($conn, $sql);
                    }

                //$bl = trim($this->pSQL($row[self::BL_OCMSV]));
//    @mysqli_close($conn);
//    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

                $nombre_operador_ocmsv = trim($this->pSQL($row[self::NOMBRE_OPERADOR_OCMSV]));
                $cve_chofer_ocmsv = trim($this->pSQL($row[self::CVE_CHOFER_OCMSV]));
                $numero_unidad_ocmsv = trim($this->pSQL($row[self::NUMERO_UNIDAD_OCMSV]));
                $placa_ocmsv = trim($this->pSQL($row[self::PLACA_OCMSV]));
                $cve_transp_ocmsv = trim($this->pSQL($row[self::CVE_TRANSP_OCMSV]));
                $observaciones_ocmsv = trim($this->pSQL($row[self::OBSERVACIONES_OCMSV]));
                $sello_ocmsv = trim($this->pSQL($row[self::SELLO_OCMSV]));
                $fecha_ocmsv = trim($this->pSQL($row[self::FECHA_OCMSV]));
                $hora_ocmsv = trim($this->pSQL($row[self::HORA_OCMSV]));

                //$fecha_ocmsv=date_create($fecha_ocmsv." ".$hora_ocmsv);
                //$hora_ocmsv=date_create($hora_ocmsv);


            //$sql = "CALL SPWS_AgregaTransporteEntrada ((SELECT MAX(Fol_Folio) as maximo FROM th_entalmacen), '".$nombre_operador_ocmsv."', '".$cve_chofer_ocmsv."', '".$numero_unidad_ocmsv."', '".$placa_ocmsv."', '".$cve_transp_ocmsv."', '".$observaciones_ocmsv."', '".$sello_ocmsv."', ".$fecha_ocmsv.")";
            //echo $sql;
            if(!$registrar_soloOC)
            {
            $sql = "INSERT INTO t_entalmacentransporte (Fol_Folio, Operador, No_Unidad, Placas, Linea_Transportista, Observaciones, Sello, Fec_Ingreso, Id_Operador) VALUES ((SELECT MAX(Fol_Folio) as maximo FROM th_entalmacen), '$nombre_operador_ocmsv', '$numero_unidad_ocmsv', '$placa_ocmsv', '$cve_transp_ocmsv', '$observaciones_ocmsv', '$sello_ocmsv', DATE_FORMAT('$fecha_ocmsv $hora_ocmsv', '%Y-%m-%d %H:%i:%S'), '$cve_chofer_ocmsv')";
            $rs = mysqli_query($conn, $sql);
            }
  //  @mysqli_close($conn);
  //  $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

                if($zonarecepcioni!= '' && $bl != '' && !$registrar_soloOC)
                {
                    /*
                    $sql = "INSERT INTO t_pendienteacomodo(cve_articulo, cve_lote, Cantidad, cve_ubicacion, ID_Proveedor) 
                            VALUES ('{$cve_articulo}', '{$lote}', {$cantidad}, '{$zonarecepcioni}', '{$proveedor}') ON DUPLICATE KEY UPDATE Cantidad = Cantidad + $cantidad";
                    $rs = mysqli_query($conn, $sql);
                    */

            $sql = "SELECT idy_ubica, IF(AcomodoMixto = '', 'N', IFNULL(AcomodoMixto, 'N')) as es_mixto FROM c_ubicacion WHERE CodigoCSD = CONVERT('{$bl}' USING UTF8MB4) AND cve_almac IN (SELECT cve_almac FROM c_almacen WHERE cve_almacenp = $id_almacen) LIMIT 1";
            //echo $sql;
            $rs = mysqli_query($conn, $sql);
            $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
            $idy_ubica = $resul['idy_ubica'];
            $es_mixto = $resul['es_mixto'];

            //***********************************************************************
            //                              ACOMODAR 
            //***********************************************************************

            $sql = "SELECT COUNT(*) AS existe FROM ts_existenciapiezas WHERE cve_articulo = '{$cve_articulo}' AND cve_lote = CONVERT('{$lote}', CHAR) AND idy_ubica = '{$idy_ubica}' AND ID_Proveedor = {$proveedor_id} AND cve_almac = {$id_almacen}";

            if($palletizar_oc || $label_lp)
            {
                $sql = "SELECT COUNT(*) AS existe FROM ts_existenciatarima WHERE cve_articulo = '{$cve_articulo}' AND lote = CONVERT('{$lote}', CHAR) AND idy_ubica = '{$idy_ubica}' AND ID_Proveedor = {$proveedor_id} AND cve_almac = {$id_almacen} AND ntarima = {$id_contenedor}";
            }
            //echo $sql; exit;
            $rs = mysqli_query($conn, $sql);
            $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
            $existe = $resul['existe'];

            if(!$existe)
            {
                $sql = "INSERT INTO ts_existenciapiezas(cve_almac, idy_ubica, cve_articulo, cve_lote, Existencia, ID_Proveedor) 
                            VALUES ({$id_almacen}, {$idy_ubica}, '{$cve_articulo}', CONVERT('{$lote}', CHAR), {$cantidad}, {$proveedor_id})";
                if($palletizar_oc || $label_lp)
                {
                    $sql = "INSERT INTO ts_existenciatarima(cve_almac, idy_ubica, cve_articulo, lote, Fol_Folio, ntarima, capcidad, existencia, ID_Proveedor) 
                            VALUES ({$id_almacen}, {$idy_ubica}, '{$cve_articulo}', CONVERT('{$lote}', CHAR), (SELECT MAX(Fol_Folio) as maximo FROM th_entalmacen), {$id_contenedor}, 0, {$cantidad}, {$proveedor_id})";
                }
                $rs = mysqli_query($conn, $sql);
            }
            else
            {
                $sql = "UPDATE ts_existenciapiezas SET Existencia = existencia + {$cantidad} WHERE cve_articulo = '{$cve_articulo}' AND cve_lote = CONVERT('{$lote}', CHAR) AND idy_ubica = '{$idy_ubica}' AND ID_Proveedor = {$proveedor_id} AND cve_almac = {$id_almacen}";

                if($sobreescribir_existencias)
                    $sql = "UPDATE ts_existenciapiezas SET Existencia = {$cantidad} WHERE cve_articulo = '{$cve_articulo}' AND cve_lote = CONVERT('{$lote}', CHAR) AND idy_ubica = '{$idy_ubica}' AND ID_Proveedor = {$proveedor_id} AND cve_almac = {$id_almacen}";

                if($palletizar_oc || $label_lp)
                {
                    $sql = "UPDATE ts_existenciatarima SET existencia = existencia + {$cantidad} WHERE cve_articulo = '{$cve_articulo}' AND lote = CONVERT('{$lote}', CHAR) AND idy_ubica = '{$idy_ubica}' AND ID_Proveedor = {$proveedor_id} AND cve_almac = {$id_almacen} AND ntarima = {$id_contenedor}";

                    if($sobreescribir_existencias)
                        $sql = "UPDATE ts_existenciatarima SET existencia = {$cantidad} WHERE cve_articulo = '{$cve_articulo}' AND lote = CONVERT('{$lote}', CHAR) AND idy_ubica = '{$idy_ubica}' AND ID_Proveedor = {$proveedor_id} AND cve_almac = {$id_almacen} AND ntarima = {$id_contenedor}";
                }
                $rs = mysqli_query($conn, $sql);
            }

            $sql = "INSERT INTO t_cardex(cve_articulo, cve_lote, fecha, origen, destino, cantidad, id_TipoMovimiento, cve_usuario, Cve_Almac, Activo, Fec_Ingreso) 
                    VALUES ('{$cve_articulo}', CONVERT('{$lote}', CHAR), NOW(), '{$cve_ubicacion}', '{$idy_ubica}', {$cantidad}, 2, '{$cve_usuario_entrada}', {$id_almacen}, 1, NOW())";
            $rs = mysqli_query($conn, $sql);

            if($palletizar_oc || $label_lp)
            {
                $sql = "INSERT INTO t_MovCharolas(Cve_Almac, ID_Contenedor, Fecha, Origen, Destino, Id_TipoMovimiento, Cve_Usuario, Status)
                        VALUES('{$almacenes}', {$id_contenedor}, NOW(), '{$cve_ubicacion}', '{$idy_ubica}', 2, '{$cve_usuario_entrada}', 'I')";
                $rs = mysqli_query($conn, $sql);

                $proyecto = trim($this->pSQL($row[self::PROYECTO_OCMSV]));
                $sql = "INSERT INTO t_trazabilidad_existencias (cve_almac, idy_ubica, cve_articulo, cve_lote, cantidad, ntarima, id_proveedor, folio_entrada, folio_oc, factura_ent, factura_oc, proyecto, id_tipo_movimiento) VALUES ({$id_almacen}, '{$idy_ubica}', '{$cve_articulo}', CONVERT('{$lote}', CHAR), {$cantidad}, {$id_contenedor}, {$proveedor_id}, (SELECT MAX(Fol_Folio) as maximo FROM th_entalmacen), '{$num_pedimento}', '{$factura}', '{$factura}', '{$proyecto}', 2)";
                $rs = mysqli_query($conn, $sql);
            }
            else if($bl != '')
            {
                $proyecto = trim($this->pSQL($row[self::PROYECTO_OCMSV]));
                $sql = "INSERT INTO t_trazabilidad_existencias (cve_almac, idy_ubica, cve_articulo, cve_lote, cantidad, ntarima, id_proveedor, folio_entrada, folio_oc, factura_ent, factura_oc, proyecto, id_tipo_movimiento) VALUES ({$id_almacen}, '{$idy_ubica}', '{$cve_articulo}', CONVERT('{$lote}', CHAR), {$cantidad}, NULL, {$proveedor_id}, (SELECT MAX(Fol_Folio) as maximo FROM th_entalmacen), '{$num_pedimento}', '{$factura}', '{$factura}', '{$proyecto}', 2)";
                $rs = mysqli_query($conn, $sql);
            }


            //***********************************************************************
            //***********************************************************************


                }
                else if(!$registrar_soloOC)
                {
                    $sql = "INSERT INTO t_pendienteacomodo(cve_articulo, cve_lote, Cantidad, cve_ubicacion, ID_Proveedor) 
                            VALUES ('{$cve_articulo}', '{$lote}', {$cantidad}, '{$zonarecepcioni}', '{$proveedor_id}') ON DUPLICATE KEY UPDATE Cantidad = Cantidad + $cantidad";
                    $rs = mysqli_query($conn, $sql);

                    if($bl != '')
                    {
                    $proyecto = trim($this->pSQL($row[self::PROYECTO_OCMSV]));
                    $sql = "INSERT INTO t_trazabilidad_existencias (cve_almac, idy_ubica, cve_articulo, cve_lote, cantidad, ntarima, id_proveedor, folio_entrada, folio_oc, factura_ent, factura_oc, proyecto, id_tipo_movimiento) VALUES ({$id_almacen}, '{$idy_ubica}', '{$cve_articulo}', CONVERT('{$lote}', CHAR), {$cantidad}, NULL, {$proveedor_id}, (SELECT MAX(Fol_Folio) as maximo FROM th_entalmacen), '{$num_pedimento}', '{$factura}', '{$factura}', '{$proyecto}', 2)";
                    $rs = mysqli_query($conn, $sql);
                    }

                }

                  $linea++;
              }
            }
          }
          }
          if($linea == 0) $linea = 1;
        }

        $linea--;
        if($bls_no_existentes) $bls_no_existentes = "\nLos Siguientes BLs no existen por lo tanto se dejaron pendientes en RTM para su acomodo: \n\n". $bls_no_existentes;

        $mensaje = "Orden de compra importada con éxito. Total: \"{$linea}\""."\n".$bls_no_existentes;
        $pedidos = 1;
        if($formato_fecha_incorrecto)
        {
            $pedidos = 0;
            $mensaje = $formato_fecha_incorrecto;
        }

        if($valido == true)
        {
            $this->response(200, [
                'statusText' =>  $mensaje.$mensaje_pallet_diferente_almacen,
                "success" => 1,
                'pedidos' => $pedidos,
              "lineas"=>$lineas,
              "sql_tracking"=>$sql_tracking
            ]);
        }
        else
        {
            $this->response(400, [
                'statusText' =>  "El Numero de Orden (ERP) ya está usado",
                'pedidos' => 0,
              "lineas"=>0,
              "sql_tracking"=>$sql_tracking
            ]);
        }
    }//if($procesar_entrada)
    else
    {
        $mensaje = "";
        if(!$bls_unicos_por_lp)
        {
            $mensaje = "El LP {$primer_lp_distinto} está ubicado en más de 1 BL";
        }
        else if($bl_no_existente)
        {
            $mensaje = "El BL {$bl_no_existente} No existe en el sistema, está registrado en otro almacén o está inactivo";
        }
        else if(!$lp_unico)
        {
            $mensaje = "El LP {$lp_ocupado} ya se ocupó en otra entrada";
        }
        else if(!$num_ord_disponible)
        {
            $mensaje = "El Numero de Orden de OC {$numerodeorden_ocupado} ya se ocupó en otra OC";
        }
        else if(!$factura_disponible)
        {
            $mensaje = "La Factura {$factura_ocupada} ya está ocupada";
        }
        else if($articulo_no_existe_desc != '')
        {
            $mensaje = "El artículo {$articulo_no_existe_desc} No existe";
        }
        else if($proveedor_no_existe_desc != '')
        {
            $mensaje = "El proveedor {$proveedor_no_existe_desc} No existe";
        }
        else if($protocolo_no_existe_desc != '')
        {
            $mensaje = "La clave de protocolo {$protocolo_no_existe_desc} No existe";
        }
        else if($fecha_valida == false)
        {
            $mensaje = "La fecha tiene formato incorrecto, debe ser con formato de celda de texto en el excel (YYYY-MM-DD)";
        }
        /*
        else if($existencias_rev == "")
        {
            $mensaje = "Las existencias no deben estar vacías";
        }
        */



        $this->response(200, [
            'statusText' =>  $mensaje,
            "success" => 0,
            "bls_unicos_por_lp" => $bls_unicos_por_lp,
            "bl_no_existente" => $bl_no_existente,
            "lp_unico" => $lp_unico,
            //'mensaje_articulos_no_existentes' => $mensaje_articulos_no_existentes,
            //'mensaje_bl_no_existentes' => $mensaje_bl_no_existentes,
            "lineas"=>$lineas
        ]);

    }


    }


    public function importarocentradas()
    {
        $errores = [];
        $dir_cache = PATH_APP . 'Cache/';
        $file = $dir_cache . basename($_FILES['file']['name']);
        $tipo = $_POST["tipo"];
        $factura   = $_POST['NumOrden'];
        $almacenes = $_POST['almacenes'];
        $proveedor = $_POST['proveedor'];
        $empresa = $_POST['empresa'];
        $protocolo = $_POST['Protocol'];
        $Consecut = $_POST['Consecut'];
        $tipo_cambio = $_POST['tipo_cambio'];
        $palletizar_oc = $_POST['palletizar_oc'];
        $zonarecepcioni = $_POST['zonarecepcioni'];
        $mensaje_pallet_diferente_almacen = "";

        $sql_tracking = "";

        $date=date_create($_POST["fecha_oc"]);
        $fecha_actual = date_format($date,"Y-m-d");

        $date=date_create($_POST["fechaestimada"]);
        $fech_llegPed = date_format($date,"Y-m-d");

        if (! move_uploaded_file($_FILES['file']['tmp_name'], $file)) {
            $this->response(400, ['statusText' =>  "Error al recibir el fichero, verifique que se tenga permisos para escribir en Cache",]);
        }
        $xlsx = new SimpleXLSX( $file );

        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

        $columnas = array("Numero de Documento","Fecha del Documento (aaammdd)","Código de Producto","Cantidad","Lote","Fecha de Vencimiento (aaammdd)");
        if($tipo=="vp"){$columnas[] = "BL";}
        $cl_excel = array("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z");

        $current_pedido = "";
        $num_pedimento = "";

        $formato_fecha_incorrecto = '';
        $linea = 0;
        $lineas = $xlsx->rows()  ;

        $valido = false;

    $valido = true;

    $sql = "SELECT COUNT(*) Factura FROM th_aduana WHERE factura='$factura' AND factura<>'';";
    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") "; exit;}
    $row_f = mysqli_fetch_assoc($res);
    $num_factura = $row_f["Factura"];

    if($num_factura > 0) $valido = false;

        if($valido == true)
        foreach ($xlsx->rows() as $row)
        {
          if($linea >= 1)
          {
            //$factura = $this->pSQL($NumOrden);
            $Cve_Almac = $almacenes;//$this->pSQL($row[3]);
            $element = OrdenesDeCompra::where('factura', $factura)->where('Cve_Almac', $Cve_Almac)->first(); //cambiar las tablas e consulta

//REGLAS DE IMPORTACION DE SERIE, LOTE Y CADUCIDAD:
//hay dos columnas lote y caducidad > ningun dato es obligatorio en la importacion. Pero...
//1 si el producto en la base de datos no pide ni lote, ni lote +caducidad, ni serie aunque encuentres un dato en el excel NO DEBES SUBIRLO
//2 Si el producto maneja lote, no puede manejar serie, y NO puede llevar caducidad. > debe subirse solo lote y en caducidad se imprime la fecha del día de importacion de datos > esa fecha se toma como la fecha de ingreso y sirve para el concepto de primeras entradas primeras salidas
//3 si el producto en la.base de datos tiene el check de serie > el dato de la columna lote será el número de serie y NO DEBE SUBIR NADA EN FECHA
//4 si el producto en la base de datos tiene el check de lote y caducidad > FORZOSAMENTE (DE AJURO) DEBE TENER AMBOS DATOS NO PUEDE SUBIR LOTE SIN CADUCIDAD NI CADUCIDAD SIN LOTE

            $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
            mysqli_set_charset($conn, 'utf8');
            $sql = "SELECT cve_articulo, des_articulo, control_lotes, control_numero_series, IFNULL(Caduca, 'N') Caduca FROM c_articulo WHERE cve_articulo = '".$row[0]."'";
            if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") "; exit;}

            $valores = mysqli_fetch_assoc($res);

            $tiene_serie     = $valores["control_numero_series"];
            $tiene_lote      = $valores["control_lotes"];
            $tiene_caducidad = $valores["Caduca"];

            //************************************************************************
            //Implementación Regla 1
            //************************************************************************
            $valores_regla_1 = true;
/*
            if($tiene_serie == 'N' && $tiene_lote == 'N' && $tiene_caducidad == 'N')
              $valores_regla_1 = false;
*/
            //************************************************************************
            //Implementación Regla 2
            //************************************************************************
            $valores_regla_2 = true;
            if($tiene_serie == 'S' && $tiene_lote == 'S')
              $valores_regla_2 = false;
            //************************************************************************
            //Implementación Regla 3
            //************************************************************************
            $valores_regla_3 = false;
            if($tiene_serie == 'S' && $tiene_lote == 'N')
              $valores_regla_3 = true;
            //************************************************************************
            //Implementación Regla 4
            //************************************************************************
            $valores_regla_4 = false;
            $lote_y_caducidad_tipo1 = false; $lote_y_caducidad_tipo2 = false; $lote_y_caducidad_tipo3 = false;
            if($tiene_lote == 'S' && $tiene_caducidad == 'S' && $tiene_serie == 'N')
            {
              //if($row[4] != "" && $row[5] != "")
                 $lote_y_caducidad_tipo1 = true;
            }

            if($tiene_lote == 'S' && $tiene_caducidad == 'N' && $tiene_serie == 'N')
            {
              //if($row[4] != "")
                 $lote_y_caducidad_tipo2 = true;
            }

            if($tiene_lote == 'N' && $tiene_caducidad == 'N' && $tiene_serie == 'S')
            {
              //if($row[4] != "")
                 $lote_y_caducidad_tipo3 = true;
            }

            if($lote_y_caducidad_tipo1 == true || $lote_y_caducidad_tipo2 == true || $lote_y_caducidad_tipo3 == true || $valores_regla_1)
              $valores_regla_4 = true;
            //************************************************************************

            $valores_reglas = false;
            if($valores_regla_1 == true && $valores_regla_2 == true && $valores_regla_4 == true) $valores_reglas = true;

        if($valores_reglas)
        {
            if($current_pedido != $factura)
            {
              //query para traer el valor que se la asigna a num_pedimento y compara con num_orden
              $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
              mysqli_set_charset($conn, 'utf8');

            $sql = "UPDATE t_protocolo SET FOLIO = FOLIO+1  WHERE ID_Protocolo = '$protocolo'";
            if (!($res = mysqli_query($conn, $sql)))
            {
              echo "Falló la preparación 2: (" . mysqli_error($conn) . ") "; exit;
            }


              $sql = "SELECT IFNULL(MAX(num_pedimento), 0) AS Maximo FROM th_aduana";
              if (!($res = mysqli_query($conn, $sql)))
              {
                echo "Falló la preparación 1: (" . mysqli_error($conn) . ") "; exit;
              }
              
              if($current_pedido != $factura)
              {
                $max = mysqli_fetch_assoc($res)["Maximo"];
                $num_pedimento = ($max+1);
                $current_pedido = $factura;
              }
            //}

            if($element != NULL){
                $model_orden = $element; 
            }
            else {
                $model_orden = new OrdenesDeCompra(); 
            }
           
            //$data_pedido = OrdenesDeCompra::where('factura',$row[0])->first();
            //if($current_pedido != $row[0]){//if( $data_pedido == NULL ){
              $model_orden = new OrdenesDeCompra();
              if(!$current_pedido) $current_pedido = 0;
              //$model_orden->factura             = $current_pedido; //$this->pSQL($row[0]);

              //$excel_date = $this->pSQL($row[1]);//$row[1];

              //$unix_date = ($excel_date - 25569) * 86400;
              //$excel_date = 25569 + ($unix_date / 86400);
              //$unix_date = ($excel_date - 25569) * 86400;
              //$Fecha_convert = gmdate("d-m-Y", $unix_date);

              //$date=date_create($excel_date);
              //if(!$date)
              //{
              //      $formato_fecha_incorrecto = "Formato de Fecha Incorrecto, debe ser dd-mm-aaaa con la celda en formato texto";
              //      break;
              //}
              //$Fecha_convert = date_format($date,"Y-m-d");



            $sql = "SELECT cve_proveedor FROM c_proveedores WHERE ID_Proveedor = '{$proveedor}'";
            if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación 2: (" . mysqli_error($conn) . ") "; exit;}
            $row_prov = mysqli_fetch_assoc($res);
            $cve_proveedor     = $row_prov["cve_proveedor"];

            $id_user = $_SESSION["id_user"];
            $cve_usuario_entrada = $_SESSION["cve_usuario"];
            $status_orden = 'C';
            if($zonarecepcioni!= '')
            {
              $sql = "INSERT INTO th_entalmacen(Cve_Almac, Fec_Entrada, Fol_OEP, Cve_Usuario, Cve_Proveedor, STATUS, Cve_Autorizado, tipo, BanCrossD, id_ocompra, HoraInicio, ID_Protocolo, Consec_protocolo, cve_ubicacion, HoraFin) VALUES('$almacenes', NOW(), '$factura', $id_user, $proveedor, 'E', '', 'OC', 'N', $num_pedimento, NOW(), '$protocolo', '$Consecut', '$zonarecepcioni', NOW())";
              if (!($res = mysqli_query($conn, $sql)))
              {
                echo "Falló la preparación 33: (" . mysqli_error($conn) . ") "; exit;
              }
              $status_orden = 'T';
            }


              $model_orden->fech_pedimento      = $fecha_actual;

              $model_orden->fech_llegPed        = $fech_llegPed;

              $model_orden->Cve_Almac           = $almacenes;//$this->pSQL($row[3]);
              //$model_orden->cve_articulo     = $this->pSQL($row[4]);//esto es en td_aduana
              $model_orden->status              = $status_orden;//$this->pSQL($row[5]);
              $model_orden->ID_Proveedor        = $empresa;//$this->pSQL($row[6]);
              $model_orden->num_pedimento       = $this->pSQL($num_pedimento);
              $model_orden->ID_Protocolo        = $protocolo;//$this->pSQL($row[8]);
              $model_orden->Activo              = 1;
              $model_orden->presupuesto         = "";//$this->pSQL($row[10]);
              $model_orden->recurso             = "";//$this->pSQL($row[11]);
              $model_orden->procedimiento       = $cve_proveedor;//$this->pSQL($row[12]);
              $model_orden->fechaDeFallo        = "";//$this->pSQL($row[13]);
              $model_orden->plazoDeEntrega      = "";//$this->pSQL($row[14]);
              $model_orden->condicionesDePago   = "";//$this->pSQL($row[15]);
              $model_orden->lugarDeEntrega      = "";//$this->pSQL($row[16]);
              $model_orden->Proyecto            = "";//$this->pSQL($row[17]);
              $model_orden->dictamen            = "";//$this->pSQL($row[18]);
              $model_orden->Factura             = $factura;
              $model_orden->Consec_protocolo    = $Consecut;
              $model_orden->Tipo_Cambio         = $tipo_cambio;
              $model_orden->areaSolicitante     = "";//$this->pSQL($row[19]);
              $model_orden->numSuficiencia      = "";//$this->pSQL($row[20]);
              $model_orden->fechaSuficiencia    = "";//$this->pSQL($row[21]);
              $model_orden->fechaContrato       = "";//$this->pSQL($row[22]);
              $model_orden->montoSuficiencia    = "";//$this->pSQL($row[23]);
              $model_orden->numeroContrato      = "";//$this->pSQL($row[24]);
              $model_orden->cve_usuario         = $_SESSION["id_user"];
              $model_orden->save();
            }
          
            //$num_orden = $num_pedimento;
            //$items = OrdenesDeCompraItems::where('num_orden', $num_orden)->first();

            if($items != NULL){
                $model = $items; 
            }
            else {
                $model = new OrdenesDeCompraItems(); 
            }

            if( $items == NULL ){
              $model = new OrdenesDeCompraItems();
              $cve_articulo = $this->pSQL($row[0]);

              $sql = "SELECT COUNT(*) as num_articulo FROM c_articulo WHERE cve_articulo = '$cve_articulo'";
              $rs = mysqli_query($conn, $sql);
              $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
              $num_articulo = $resul['num_articulo'];

              if($num_articulo)
              {
  /*
                $sql = "SELECT COUNT(*) serie FROM c_articulo WHERE cve_articulo = '$cve_articulo' AND control_numero_series = 'S'";
                  $rs = mysqli_query($conn, $sql);
                  $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                  $serie = $resul['serie'];
*/
                  $sql = "SELECT DATE_FORMAT(CURDATE(), '%Y/%m/%d') fecha_actual FROM DUAL";
                  $rs = mysqli_query($conn, $sql);
                  $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                  $fecha_actual = $resul['fecha_actual'];

                  $lote = $this->pSQL($row[2]);
                  $cantidad = $this->pSQL($row[1]);

                  //if(!$lote || ($lote && $cantidad > 1 && $tiene_serie == 'S')) $lote = "";
                  if(!$lote) $lote = "";

                  if($tiene_serie == 'S') $cantidad = 1;

                  $model->cve_articulo     = $cve_articulo;
                  $model->cantidad         = $cantidad;
                  $model->num_orden        = $this->pSQL($num_pedimento);
                  $model->cve_lote         = $lote;

                  if($this->pSQL($row[5]) != '')
                  $model->costo            = $this->pSQL($row[5]);
                  if($this->pSQL($row[6]) != '')
                  $model->Factura          = $this->pSQL($row[6]);


                  $caducidad = "0000-00-00";
                  $excel_date = $this->pSQL($row[3]);//$row[5];
                  if($excel_date != "")
                  {
                      //$unix_date = ($excel_date - 25569) * 86400;
                      //$excel_date = 25569 + ($unix_date / 86400);
                      //$unix_date = ($excel_date - 25569) * 86400;
                      //$Fecha_convert = gmdate("d-m-Y", $unix_date);
                      $date=date_create($excel_date);
                      $Fecha_convert = date_format($date,"Y-m-d");
                      $caducidad = $Fecha_convert;
                  }
                  if($row[2] != "")
                  {
                    //if(($tiene_lote == 'S' || $tiene_serie == 'S') && $tiene_caducidad == 'N')
                       //$caducidad = $fecha_actual;

                    $model->caducidad = $caducidad;
                  }
                  else
                  {
                      $model->caducidad = "0000-00-00";
                  }

                  $sql = "SELECT COUNT(*) as num_lotes FROM c_lotes WHERE Lote = '$lote' AND cve_articulo = '$cve_articulo'";
                  $rs = mysqli_query($conn, $sql);
                  $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                  $num_lotes = $resul['num_lotes'];

                  $sql_tracking .= $sql." - ".$num_lotes."\n";
                  //$sql = "SELECT COUNT(*) serie_N FROM c_articulo WHERE cve_articulo = '$cve_articulo' AND control_numero_series = 'N'";
                  $sql = "SELECT control_numero_series FROM c_articulo WHERE cve_articulo = '$cve_articulo'";
                  $rs = mysqli_query($conn, $sql);
                  $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                  $serie_SN = $resul['control_numero_series'];

                  $sql_tracking .= $sql." - ".$serie_SN."\n";
                  if($num_lotes == 0 && $serie_SN == 'N' && $lote != '')
                  {
                      $sql = "INSERT INTO c_lotes(cve_articulo, Lote, Caducidad, Activo) VALUES('$cve_articulo', '$lote', DATE_FORMAT('$caducidad', '%Y-%m-%d'), 1)";
                      if($caducidad == "0000-00-00")
                         $sql = "INSERT INTO c_lotes(cve_articulo, Lote, Activo) VALUES('$cve_articulo', '$lote', 1)";
                      $sql_tracking .= $sql."\n";
                      $rs = mysqli_query($conn, $sql);
                  }

                  $model->costo            = 0;//$this->pSQL($row[9]);
                  $model->Activo           = 1;

                  $existe = 0;
                  //if($serie_SN == 'N')
                  //{
                      $sql = "SELECT COUNT(*) existe FROM td_aduana WHERE cve_articulo = '$cve_articulo' AND IFNULL(cve_lote, '') = '$lote' AND num_orden = '$num_pedimento'";
                      $rs = mysqli_query($conn, $sql);
                      $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                      $existe = $resul['existe'];
                  //}

                  if($existe)// && $serie_SN == 'N' && $tiene_lote == 'S')
                  {
                      $sql = "UPDATE td_aduana SET cantidad=cantidad+$cantidad WHERE cve_articulo = '$cve_articulo' AND IFNULL(cve_lote, '') = '$lote' AND num_orden = '$num_pedimento'";
                      $rs = mysqli_query($conn, $sql);


                    if($zonarecepcioni!= '')
                    {
                      $sql = "UPDATE td_entalmacen SET CantidadPedida=CantidadPedida+$cantidad, CantidadRecibida=CantidadRecibida+$cantidad WHERE cve_articulo = '$cve_articulo' AND cve_lote = '$lote' AND num_orden = '$num_pedimento'";
                      $rs = mysqli_query($conn, $sql);

                      $sql = "INSERT INTO t_cardex(cve_articulo, cve_lote, fecha, origen, destino, stockinicial, cantidad, ajuste, id_TipoMovimiento, cve_usuario, Cve_Almac, Activo) VALUES ('$cve_articulo', '$lote', NOW(), (SELECT MAX(Fol_Folio) as maximo FROM th_entalmacen),'$zonarecepcioni', $cantidad, $cantidad, 0, 1, '$cve_usuario_entrada', (SELECT id FROM c_almacenp WHERE clave = '$almacenes'), 1)";
                      $rs = mysqli_query($conn, $sql);
                    }
                      //$model->save();

                      //$sql = "SELECT MAX(ID_Aduana) Max_Aduana FROM td_aduana";
                      //$rs = mysqli_query($conn, $sql);
                      //$resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                      //$max_Aduana = $resul['Max_Aduana'];

                      //$sql = "DELETE FROM td_aduana WHERE ID_Aduana = $max_Aduana";
                      //$rs = mysqli_query($conn, $sql);
                  }
                  else 
                  {
                    if($zonarecepcioni!= '')
                    {
                      $sql = "INSERT INTO td_entalmacen(fol_folio, cve_articulo, cve_lote, CantidadPedida, CantidadRecibida, CantidadDisponible, CantidadUbicada, status, cve_usuario, cve_ubicacion, fecha_inicio, fecha_fin, num_orden) VALUES((SELECT MAX(Fol_Folio) as maximo FROM th_entalmacen), '$cve_articulo', '$lote', $cantidad, $cantidad, 0, 0, 'E', '$cve_usuario_entrada', '$zonarecepcioni', NOW(), NOW(), '$num_pedimento')";
                      $rs = mysqli_query($conn, $sql);

                      $sql = "INSERT INTO t_cardex(cve_articulo, cve_lote, fecha, origen, destino, stockinicial, cantidad, ajuste, id_TipoMovimiento, cve_usuario, Cve_Almac, Activo) VALUES ('$cve_articulo', '$lote', NOW(), (SELECT MAX(Fol_Folio) as maximo FROM th_entalmacen),'$zonarecepcioni', $cantidad, $cantidad, 0, 1, '$cve_usuario_entrada', (SELECT id FROM c_almacenp WHERE clave = '$almacenes'), 1)";
                      $rs = mysqli_query($conn, $sql);
                   }
                    $model->save();
                  }

                      if($row[4] != '' || ($row[4] == '' && $palletizar_oc == 1))
                      {
                        $lp = $row[4];

                        if($lp)
                        {
                            $sql = "SELECT a.clave AS almacen_pallet 
                                    FROM c_charolas ch
                                    LEFT JOIN c_almacenp a ON a.id = ch.cve_almac
                                    WHERE IFNULL(ch.CveLP, ch.clave_contenedor) = '$lp'";
                            $rs = mysqli_query($conn, $sql);
                            $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);

                            if($resul['almacen_pallet'] != $almacenes)
                            {
                                $mensaje_pallet_diferente_almacen .= "\n El Pallet $lp No pertenece al almacén actual ";
                                continue;
                            }
                        }

                        $sql = "SELECT (SELECT MAX(ch.IDContenedor)+1 FROM c_charolas ch) AS id_contenedor, (SELECT id FROM c_almacenp WHERE clave = '$almacenes') as cve_almac, descripcion, tipo, alto, ancho, fondo, peso, pesomax, capavol FROM c_charolas WHERE TipoGen = 1 AND tipo = 'Pallet' ORDER BY IDContenedor ASC LIMIT 1";
                        $rs = mysqli_query($conn, $sql);
                        $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);

                        if(!$resul['id_contenedor']) break;

                        $id_contenedor = $resul['id_contenedor'];
                        $id_almacen    = $resul['cve_almac'];
                        $descripcion   = $resul['descripcion'];
                        $tipo          = $resul['tipo'];
                        $alto          = $resul['alto'];
                        $ancho         = $resul['ancho'];
                        $fondo         = $resul['fondo'];
                        $peso          = $resul['peso'];
                        $pesomax       = $resul['pesomax'];
                        $capavol       = $resul['capavol'];

                        $label_lp = $lp;

                        if($lp == "")
                           $label_lp = "LP".str_pad($resul['id_contenedor'].$num_pedimento, 9, "0", STR_PAD_LEFT);

                        $sql = "INSERT IGNORE INTO c_charolas(cve_almac, clave_contenedor, descripcion, Permanente, tipo, alto, ancho, fondo, peso, pesomax, capavol, CveLP, TipoGen) 
                                VALUES ({$id_almacen}, '{$label_lp}', '{$descripcion}', 0, 'Pallet', {$alto}, {$ancho}, {$fondo}, {$peso}, {$pesomax}, {$capavol}, '{$label_lp}', 0)";
                        $rs = mysqli_query($conn, $sql);

                        $sql_tracking .= $sql."\n";

                    if($zonarecepcioni!= '')
                    {

                        $sql = "INSERT INTO td_entalmacenxtarima(fol_folio, cve_articulo, cve_lote, ClaveEtiqueta, Cantidad, Ubicada, PzsXCaja) 
                                VALUES ((SELECT MAX(Fol_Folio) as maximo FROM th_entalmacen), '{$cve_articulo}', '{$lote}', '{$label_lp}', {$cantidad}, 'N', 1)";
                        $rs = mysqli_query($conn, $sql);

                        $sql = "INSERT INTO t_MovCharolas (id_kardex, Cve_Almac, ID_Contenedor, Fecha, Origen, Destino, Id_TipoMovimiento, Cve_Usuario, Status) VALUES ((SELECT MAX(id) FROM t_cardex), '$almacenes', $id_contenedor, NOW(), (SELECT MAX(Fol_Folio) as maximo FROM th_entalmacen), '$zonarecepcioni', 1, '$cve_usuario_entrada', 'I')";
                        $rs = mysqli_query($conn, $sql);
                    }
                        $sql = "INSERT INTO td_aduanaxtarima(Num_Orden, Cve_Articulo, Cve_Lote, ClaveEtiqueta, Cantidad, Recibida) 
                                VALUES ('$num_pedimento', '{$cve_articulo}', '{$lote}', '{$label_lp}', {$cantidad}, 'N')";
                        $rs = mysqli_query($conn, $sql);


                      }


                if($zonarecepcioni!= '')
                {
                    $sql = "INSERT INTO t_pendienteacomodo(cve_articulo, cve_lote, Cantidad, cve_ubicacion, ID_Proveedor) 
                            VALUES ('{$cve_articulo}', '{$lote}', {$cantidad}, '{$zonarecepcioni}', '{$proveedor}') ON DUPLICATE KEY UPDATE Cantidad = Cantidad + $cantidad";
                    $rs = mysqli_query($conn, $sql);
                }

                  $linea++;
              }
            }
          }
          }
          if($linea == 0) $linea = 1;
        }

        $linea--;
            $mensaje = "Orden de compra importada con éxito. Total: \"{$linea}\"";
            $pedidos = 1;
        if($formato_fecha_incorrecto)
        {
            $pedidos = 0;
            $mensaje = $formato_fecha_incorrecto;
        }

        if($valido == true)
        {
            $this->response(200, [
                'statusText' =>  $mensaje.$mensaje_pallet_diferente_almacen,
                'pedidos' => $pedidos,
              "lineas"=>$lineas,
              "sql_tracking"=>$sql_tracking
            ]);
        }
        else
        {
            $this->response(400, [
                'statusText' =>  "El Numero de Orden (ERP) ya está usado",
                'pedidos' => 0,
              "lineas"=>0,
              "sql_tracking"=>$sql_tracking
            ]);
        }

    }

    public function validarRequeridosImportarOC($row)
    {
        foreach ($this->camposRequeridosOC as $key => $campo){
            if( empty($row[$key]) ){
                return $campo;
            }
        }
        return true;
    }
  
    private $camposRequeridosOC = [
        self::CLAVE => 'Clave', 
    ];
  
  
    public function importarOC_ASL()
    {
        $errores = [];
        $dir_cache = PATH_APP . 'Cache/';
        $file = $dir_cache . basename($_FILES['file']['name']);

        if (! move_uploaded_file($_FILES['file']['tmp_name'], $file)) {
            $this->response(400, [
                'statusText' =>  "Error al recibir el fichero, verifique que se tenga permisos para escribir en Cache",
            ]);
        }
       
        $xlsx = new SimpleXLSX( $file );
        $linea = 1;
        $fecha ='';
        $documento = '';
        
        foreach ($xlsx->rows() as $row)
        {
            if($linea == 3)
            {
                $fecha = date($row[3]);
                $documento = trim($row[1]);
            }
            //$eval = $this->validarRequeridosImportarOC($row);
            $linea++;
        }
        $linea = 1;
        $lineas = $xlsx->rows()  ;
       
        $current_pedido = "";
        $num_pedimento = "";
       
        foreach ($xlsx->rows() as $row)
        {
            if($linea == 3)
            {
              if((strpos($row[0], "Documento") === false && strpos($row[2], "Fecha") === false) || ($row[1] == "" && $row[3] == ""))
              {
                $this->response(400, [
                    'statusText' =>  "ERROR Falta el encabezado de su archivo excel.",
                ]); 
              }
            }
            elseif($linea == 4) {
                $columnas = array("Item","Lote","Cantidad","Fec Vcmto");
                $cl_excel = array("A","F","H","L");
                foreach($columnas as $k => $v)
                {
                    if(!in_array($v,$row))
                    {
                        $k_i=$k+1;
                        $this->response(400, [
                            'statusText' =>  "ERROR Falta el titulo “".$v."” en la columna “".$cl_excel[$k]."” de su archivo excel",
                        ]); 
                    }
                }
                $linea++;
                continue;
            }
            elseif($linea >= 5)
            {
                $columnas = array(0,5,7,11);
                $cl_excel = array("A","F","H","L");
                foreach($columnas as $k => $v)
                {
                    if($row[$v] == "")
                    {
                        // Datos incorrecto en la fila “Numero de fila” de la columna “Letra de columna”
                        $k_i=$k+1;
                        $this->response(400, [
                            'statusText' =>  "ERROR Campo vacío en la línea “".$linea."” y columna “".$cl_excel[$k]."”. Este dato es necesario.",
                        ]); 
                    }
                }
            }
          /*
            $importe ="";   
          
            foreach($columnas as $k => $v)
            {
                if($this->pSQL($row[$k])=="Cantidad")
                {
                    $cant = $this->pSQL($row[7]);
                    $precio = $this->pSQL($row[9]);
                    $importe = $cant*$precio;
                }
            echo var_dump($importe);
            die;
            }
          */
            if($linea >= 5){
              $factura = $documento;
              $Cve_Almac = '0002';
              $element = OrdenesDeCompra::where('factura', $factura)->where('Cve_Almac', $Cve_Almac)->first(); //cambiar las tablas e consulta

              if($current_pedido != $factura)
              {

                //query para traer el valor que se la asigna a num_pedimento y compara con num_orden
                $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
                mysqli_set_charset($conn, 'utf8');
                $sql = "SELECT MAX(num_pedimento) AS MaximoPedimento FROM th_aduana";
                if (!($res = mysqli_query($conn, $sql)))
                {
                  echo "Falló la preparación: (" . mysqli_error($conn) . ") "; exit;
                }
                $max = mysqli_fetch_assoc($res)["MaximoPedimento"];

                $sqlp = "SELECT MAX(Consec_protocolo) AS MaximoProtocolo FROM th_aduana where ID_Protocolo = '1000'";
                if (!($resp = mysqli_query($conn, $sqlp)))
                {
                  echo "Falló la preparación: (" . mysqli_error($conn) . ") "; exit;
                }
                $maxp = mysqli_fetch_assoc($resp)["MaximoProtocolo"];

                $num_protocolo = ($maxp+1);
                $num_pedimento = ($max+1);
                $current_pedido = $factura;
              

                  if($element != NULL){
                      $model_orden = $element; 
                  }
                  else {
                      $model_orden = new OrdenesDeCompra(); 
                  }

                  $data_pedido = OrdenesDeCompra::where('factura',$row[0])->first();
                  if( $data_pedido == NULL ){
                    $model_orden = new OrdenesDeCompra();
                    $model_orden->num_pedimento       = $this->pSQL($num_pedimento);
                    $model_orden->fech_pedimento      = $this->pSQL($fecha);
                    $model_orden->factura             = $this->pSQL($factura);
                    $model_orden->fech_llegPed        = $this->pSQL($fecha);
                    $model_orden->status              = $this->pSQL('A');
                    $model_orden->ID_Proveedor        = $this->pSQL(1);
                    $model_orden->ID_Protocolo        = $this->pSQL('1000');
                    $model_orden->Consec_protocolo    = $this->pSQL($num_protocolo);
                    $model_orden->cve_usuario         = $this->pSQL('admin');
                    $model_orden->Cve_Almac           = $this->pSQL($Cve_Almac);
                    $model_orden->Activo              = $this->pSQL(1);
                    $model_orden->save();
                  }
              }

              if($items != NULL){
                  $model = $items; 
              }
              else {
                  $model = new OrdenesDeCompraItems(); 
              }

              $cve_articulo = Articulos::where('cve_codprov', trim($row[0]))->first()["cve_articulo"];
              if( $items == NULL ){
                $model = new OrdenesDeCompraItems();
                $model->cve_articulo     = $this->pSQL($cve_articulo);
                $model->cantidad         = $this->pSQL(trim($row[7]));
                $model->cve_lote         = $this->pSQL(trim($row[5]));
                $model->caducidad        = $this->pSQL(date(trim($row[11])));
                $model->num_orden        = $this->pSQL($num_pedimento);
                $model->Activo           = 1;
                $model->costo            = 0.00;              
                $model->save();
                
              }
              
              $lote = Lotes::where('cve_articulo', trim($row[5]))->first()["LOTE"];
              if($lote == NULL)
              {
                $model = new Lotes();
                $model->cve_articulo  = $this->pSQL($cve_articulo);
                $model->LOTE          = $this->pSQL(trim($row[5]));
                $model->CADUCIDAD     = $this->pSQL(trim($row[11]));
                $model->Activo        = $this->pSQL(1);
              }
            }
            $linea++;
        }
      
        $linea--;
        $this->response(200, [
            'statusText' =>  "Pedidos importados con exito. Total de Pedidos: \"{$linea}\"",
          "lineas"=>$lineas,
        ]);
    }
  /*
    public function calcularImporteOrden($num_orden)
    {
      $sql = 'SELECT SUM(td_aduana.cantidad*td_aduana.costo) as Importe FROM `td_aduana` WHERE td_aduana.num_orden ="'.$num_orden.'"';
      $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
      mysqli_set_charset($conn, 'utf8');
      $result = mysqli_query($conn, $sql);
      $importe = 0;
      while($row = mysqli_fetch_assoc($result)) { $importe = $row["Importe"]; }
      return $importe;
    }
  
    function insertarImporte($num_pedimento,$importe)
    {
    $sql = 'UPDATE `th_aduana` 
            SET `importeOrden`="'.$importe.'"
            WHERE th_aduana.num_pedimento ="'.$num_pedimento.'"';

    $sth = \db()->prepare($sql);
    $sth->execute();
    }
  */

    public function exportar()
    {
        $columnas = [
            'Fecha Solicitud',
            'Folio',
            'OC | Entrada',
            'Empresa | Proveedor',
            'Tipo OC | Entrada',
            'Cons. Protocolo',
            'Fecha Requerida',
            'Status',
            utf8_decode('Almacén'),
            'Usuario'
        ];


        $almacen = $_GET['almacen'];
        $status =  $_GET['status'];
        $statusDesc = "";

        if($status == "C") $statusDesc = "Pendiente de Recibir";
        if($status == "I") $statusDesc = "Recibiendo";
        if($status == "T") $statusDesc = "Cerrada";
        if($status == "K") $statusDesc = "Cancelada";

        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

        $sql = "SELECT DISTINCT
                        th_aduana.ID_Aduana,
                        th_aduana.num_pedimento,
                        DATE_FORMAT(fech_pedimento,'%d-%m-%Y'  ) as fech_pedimento,
                        DATE_FORMAT(fech_llegPed,'%d-%m-%Y'  ) as fech_llegPed,
                        th_aduana.aduana,
                        th_aduana.factura as ocentrada,
                        #th_aduana.status as status,
                        'Pendiente de Recibir' as status,
                        th_aduana.ID_Proveedor,
                        th_aduana.ID_Protocolo,
                        th_aduana.Consec_protocolo,
                        c_usuario.nombre_completo as usuario,
                        th_aduana.Cve_Almac,
                        th_aduana.cve_usuario,
                        th_aduana.Activo,
                        c_proveedores.ID_Proveedor,
                            t_protocolo.descripcion as Protocolo,
                        c_proveedores.Nombre as Empresa,
                              c_almacenp.nombre as Almacen,
                        recurso,
                        procedimiento,
                        dictamen,
                        c_presupuestos.nombreDePresupuesto,
                        presupuesto,
                        condicionesDePago,
                        lugarDeEntrega,
                        DATE_FORMAT(fechaDeFallo,'%d-%m-%Y'  ) as fechaDeFallo,
                        plazoDeEntrega,
                        Proyecto as numeroDeExpediente,
                        CONCAT('$', TRUNCATE((SELECT (SUM(td_aduana.cantidad*td_aduana.costo)) FROM td_aduana WHERE num_pedimento = td_aduana.num_orden), 2)) AS importe,
                        areaSolicitante,
                        numSuficiencia,
                        fechaSuficiencia,
                        fechaContrato,
                        montoSuficiencia,
                        numeroContrato
                      from th_aduana
                INNER JOIN c_proveedores ON th_aduana.ID_Proveedor = c_proveedores.ID_Proveedor
                    LEFT JOIN t_protocolo ON th_aduana.ID_Protocolo= t_protocolo.ID_Protocolo
                    LEFT JOIN cat_estados ON th_aduana.status=cat_estados.ESTADO
                    LEFT JOIN c_almacenp ON th_aduana.Cve_Almac= c_almacenp.clave
                    LEFT JOIN c_usuario ON th_aduana.cve_usuario = c_usuario.id_user
                LEFT JOIN c_presupuestos ON th_aduana.presupuesto = c_presupuestos.id
                LEFT JOIN td_aduana ON th_aduana.num_pedimento = td_aduana.num_orden
                      WHERE th_aduana.Activo = '1' and th_aduana.Cve_Almac='$almacen' AND th_aduana.status = '$status'
                ORDER BY num_pedimento DESC";

            if (!($res = mysqli_query($conn, $sql))) {
                echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
            }

        //$data_oc = mysqli_fetch_assoc($res); .date('d-m-Y')
        $filename = "Ordenes de Compra Por Status".".xls";
        ob_clean();
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        
        foreach($columnas as $column) 
        {            
            echo $column . "\t" ;            
        }
        print("\r\n");

        //foreach($data_oc as $row)
        while($row = mysqli_fetch_assoc($res))
        {
            echo $this->clear_column($row{'fech_pedimento'}) . "\t";
            echo $this->clear_column($row{'num_pedimento'}) . "\t";
            echo $this->clear_column($row{'ocentrada'}) . "\t";
            echo $this->clear_column($row{'Empresa'}) . "\t";
            echo $this->clear_column($row{'Protocolo'}) . "\t";
            echo $this->clear_column($row{'Consec_protocolo'}) . "\t";
            echo $this->clear_column($row{'fech_llegPed'}) . "\t";
            echo $this->clear_column($statusDesc) . "\t";
            echo $this->clear_column($row{'Almacen'}) . "\t";
            echo $this->clear_column($row{'cve_usuario'}) . "\t";
            echo  "\r\n";
        }
        exit;
        
    }

    private function clear_column(&$str)
    {
        $str = preg_replace("/\t/", "\\t", $str);
        $str = preg_replace("/\r?\n/", "\\n", $str);
        if(strstr($str, '"')) $str = '"' . str_replace('"', '""', $str) . '"';
        return $str;
    }

}
