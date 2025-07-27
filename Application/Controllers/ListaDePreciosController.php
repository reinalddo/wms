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
class ListaDePreciosController extends Controller
{

    const CVE_ARTICULO   = 0;
    const PRECIO_MINIMO  = 1;
    const PRECIO_MAXIMO  = 2;
    const COMISION_PORC  = 3;
    const COMISION_MONTO = 4;
    const DESCRIPCION    = 5;

 ///////////////////////////////////////////////////////////////////////////////////////////////////// 
    public function importarLP()
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
        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

        $linea = 1; $productos = 0;
        $lineas = $xlsx->rows();

        $nombre_lista       = $_POST['nombre_lista_import'];
        $Almcen             = $_POST['Almcen_import'];
        $fechaini           = $_POST['fechaini_import'];
        $fechafin           = $_POST['fechafin_import'];
        $tipo_lista         = $_POST['tipo_lista_env'];
        $id_lista_import    = $_POST['id_lista_import'];
        $importar_servicios = $_POST['importar_servicios'];
        $moneda_id          = $_POST['lista_monedas_import'];

        $id = $id_lista_import; $articulos_no_existentes = "";

       $tipo_servicio = 'N';
       if(isset($_POST['importar_servicios']))
       {
          if($_POST['importar_servicios'] == 1)
            $tipo_servicio = 'S';
       }

        $sql = "SELECT id FROM c_almacenp WHERE clave = '{$Almcen}'";
        $res = "";
        if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
        $id_almac  = mysqli_fetch_assoc($res)['id'];

        if($id_lista_import == "")
        {
            $sql = "INSERT INTO listap(Lista, Tipo, FechaIni, FechaFin, Cve_Almac, TipoServ, id_moneda) VALUES('{$nombre_lista}', {$tipo_lista}, STR_TO_DATE('{$fechaini}', '%d-%m-%Y'), STR_TO_DATE('{$fechafin}', '%d-%m-%Y'), {$id_almac}, '$tipo_servicio', '$moneda_id')";

            $tracking_codigo = $sql."\n\n";
            $res = "";
              if (!($res = mysqli_query($conn, $sql))) {
                  echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
              }


            $sql = "SELECT IFNULL(MAX(id), 0) as id FROM listap";
            $tracking_codigo .= $sql."\n\n";
            $res = "";
            if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
            $id = mysqli_fetch_assoc($res)['id'];
        }
        else
        {
            $sql = "DELETE FROM detallelp WHERE ListaId = '{$id}'";
              if (!($res = mysqli_query($conn, $sql))) {
                  echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
              }
        }

        foreach ($xlsx->rows() as $row)
        {
            if($linea == 1) {
                $linea++;continue;
            }
            $cve_articulo = $this->pSQL($row[self::CVE_ARTICULO]);
            $preciomin    = $this->pSQL($row[self::PRECIO_MINIMO]);
            $preciomax    = $this->pSQL($row[self::PRECIO_MAXIMO]);
            $comisionporc = $this->pSQL($row[self::COMISION_PORC]);
            $comisionprec = $this->pSQL($row[self::COMISION_MONTO]);
            $descripcion  = $this->pSQL($row[self::DESCRIPCION]);

            $sql = "SELECT COUNT(*) as existe FROM c_articulo WHERE cve_articulo = '{$cve_articulo}'";
            $tracking_codigo .= $sql."\n\n";
            $rs = mysqli_query($conn, $sql);
            $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
            $existe_articulo = $resul['existe'];

            $tracking_codigo .= "Existe: ".$existe_articulo."\n\n";

            if(!$existe_articulo) {$articulos_no_existentes .= $cve_articulo."\n"; $linea++; continue;}

            $precio_min = $preciomin;
            $precio_max = $preciomax;

            $comision_porc = $comisionporc;
            $comision_prec = $comisionprec;

            $tracking_codigo .= "Tipo Lista: ".$tipo_lista."\n\n";

            if($tipo_lista == 1)

            {
                if($precio_min == '' && $precio_max != '')
                   $precio_min = $precio_max;
                else if(($precio_min != '' && $precio_max == '') || ($precio_min != '' && $precio_max != ''))
                    $precio_max = $precio_min;



                if($precio_min == ""){$linea++; continue;}

                if($comision_porc)
                {
                    $comision_prec = ($comision_porc*$precio_min)/100;
                    $comision_prec = number_format($comision_prec, 2);
                }
                else if($comision_prec)
                {
                    $comision_porc = ($comision_prec/$precio_min)*100;
                    $comision_porc = number_format($comision_porc, 2);
                }

                if($comision_porc == "" && $comision_prec == ""){$comision_porc = "0.000"; $comision_prec = "0.000";}
            }
            else //if($tipo_lista == 2)
            {
                if($precio_min == "" || $precio_max == "" || $precio_max == $precio_min){$linea++; continue;}

                if($preciomin > $preciomax)
                {
                    $precio_min = $preciomax;
                    $precio_max = $preciomin;
                }

                if($comision_porc)
                {
                    $comision_prec = "0.000";
                }
                else if($comision_prec)
                {
                    $comision_porc = "0.000";
                }
            }

            $sql = "INSERT INTO detallelp(ListaId, Cve_Articulo, PrecioMin, PrecioMax, Cve_Almac, ComisionPor, ComisionMon) VALUES({$id}, '{$cve_articulo}', {$precio_min}, {$precio_max}, {$id_almac}, {$comision_porc}, {$comision_prec})";
            $tracking_codigo .= $sql."\n\n";
            if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}

            if($descripcion != '')
            {
                $sql = "UPDATE c_articulo SET des_articulo = '$descripcion' WHERE cve_articulo = '$cve_articulo'";
                if($tipo_servicio == 'S')
                $sql = "UPDATE c_servicios SET Des_Servicio = '$descripcion' WHERE Cve_Servicio = '$cve_articulo'";
                //$tracking_codigo .= $sql."\n\n";
                if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}            
            }

            $productos++;
            $linea++;
        }

        $mensaje_articulos_no_existentes = "";
        if($articulos_no_existentes)
        {
            $mensaje_articulos_no_existentes = "<br>Los siguientes artículos no existen en el sistema: <br><textarea rows='3' style='width:100%;'>".$articulos_no_existentes."</textarea>";

            if($productos == 0 && $id_lista_import == "")
            {
                $sql = "DELETE FROM listap WHERE id = '{$id}'";
                $tracking_codigo .= $sql."\n\n";
                $rs = mysqli_query($conn, $sql);
            }
        }
        $this->response(200, [
            'statusText' =>  "Lista importada con éxito. Total de Productos: \"{$productos}\" <br><br>".$mensaje_articulos_no_existentes,
            //'mensaje_articulos_no_existentes' => $mensaje_articulos_no_existentes,
            //'mensaje_bl_no_existentes' => $mensaje_bl_no_existentes,
            "tracking_codigo"=>$tracking_codigo,
            "lineas"=>$lineas
        ]);
    }

function eliminar_acentos($cadena){
        
        //Reemplazamos la A y a
        $cadena = str_replace(
        array('Á', 'À', 'Â', 'Ä', 'á', 'à', 'ä', 'â', 'ª'),
        array('A', 'A', 'A', 'A', 'a', 'a', 'a', 'a', 'a'),
        $cadena
        );

        //Reemplazamos la E y e
        $cadena = str_replace(
        array('É', 'È', 'Ê', 'Ë', 'é', 'è', 'ë', 'ê'),
        array('E', 'E', 'E', 'E', 'e', 'e', 'e', 'e'),
        $cadena );

        //Reemplazamos la I y i
        $cadena = str_replace(
        array('Í', 'Ì', 'Ï', 'Î', 'í', 'ì', 'ï', 'î'),
        array('I', 'I', 'I', 'I', 'i', 'i', 'i', 'i'),
        $cadena );

        //Reemplazamos la O y o
        $cadena = str_replace(
        array('Ó', 'Ò', 'Ö', 'Ô', 'ó', 'ò', 'ö', 'ô'),
        array('O', 'O', 'O', 'O', 'o', 'o', 'o', 'o'),
        $cadena );

        //Reemplazamos la U y u
        $cadena = str_replace(
        array('Ú', 'Ù', 'Û', 'Ü', 'ú', 'ù', 'ü', 'û'),
        array('U', 'U', 'U', 'U', 'u', 'u', 'u', 'u'),
        $cadena );

        //Reemplazamos la N, n, C y c
        $cadena = str_replace(
        array('Ñ', 'ñ', 'Ç', 'ç'),
        array('N', 'n', 'C', 'c'),
        $cadena
        );
        
        return $cadena;
    }

    public function exportar_ventas_sfa()
    {

//***************************************************************************************************
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);


    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);

    $almacen        = $_GET['almacen'];
    $ruta           = $_GET['ruta'];
    $diao           = $_GET['diao'];
    $operacion      = $_GET['operacion'];
    $fecha_inicio   = $_GET['fechaini'];
    $fecha_fin      = $_GET['fechafin'];
    $cliente        = $_GET['clientes'];
    $tipoV          = $_GET['tipoV'];
    $articulos      = $_GET['articulos'];
    $articulos_obsq = $_GET['articulos_obsq'];
    $criterio       = $_GET['criterio'];

    //////////////////////////////////////////////se recibe los parametros POST del grid////////////////////////////////////////////////
    $sql_almacen = "SELECT clave FROM c_almacenp WHERE id = '$almacen'";
    if (!($res_almacen = mysqli_query($conn, $sql_almacen))) echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $cve_almacen = mysqli_fetch_array($res_almacen)['clave'];

    if(!$diao) $diao = 0;
    if($fecha_inicio == '') $fecha_inicio = 'NULL';
    else
    {
        $fecha = explode('-', $fecha_inicio);
        $fecha_inicio = $fecha[2]."-".$fecha[1]."-".$fecha[0];
        $fecha_inicio = "'".$fecha_inicio."'";
    }
    if($fecha_fin == '') $fecha_fin = 'NULL';
    else
    {
        $fecha = explode('-', $fecha_fin);
        $fecha_fin = $fecha[2]."-".$fecha[1]."-".$fecha[0];
        $fecha_fin = "'".$fecha_fin."'";
    }

    if($ruta == 'todas') $ruta = "";

    $sql = "CALL SPAD_ReporteVtas('$cve_almacen', '',$fecha_inicio,$fecha_fin,'$ruta',0,'$operacion','','','$cliente',$diao,'$articulos')";
    if($operacion == 'PreVenta' || $operacion == 'Entrega')
        $sql = "CALL SPAD_ReportePedidosEntregas('$cve_almacen', '',$fecha_inicio,$fecha_fin,'$ruta',0,'$operacion','','','$cliente',$diao,'$articulos')";

    if (!($res = mysqli_query($conn, $sql))) {
        echo "9Falló la preparación: (" . mysqli_error($conn) . ") " . $sql;
    }
        $columnas = [
            'Folio', 
            'Fecha', 
            'Cliente', 
            'Nombre Comercial', 
            'Tipo', 
            'Operacion', 
            'Ruta', 
            'DO', 
            'Cve Articulo', 
            'Descripcion', 
            'Unidad de Medida', 
            'Cajas', 
            'Piezas', 
            'Precio',
            'Importe',
            'IVA',
            'Descuento',
            'Total',
            'Cancelada'
        ];

        $filename = "Reporte de Ventas" . ".xls";

        ob_clean();

        header("Content-Disposition: attachment; filename=\"$filename\"");
        //header('Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        //header("Content-Type: application/vnd.ms-excel");
        header( "Content-type: application/vnd.ms-excel; charset=UTF-8" );


        foreach($columnas as $column) 
        {            
            echo $column . "\t" ;            
        }
        print("\r\n");

        while($row = mysqli_fetch_object($res))
        {
            //extract($row);

            echo $row->Folio."\t";
            echo $row->Fecha."\t";
            echo $row->Cliente."\t";
            echo utf8_decode($row->Responsable)."\t";
            echo $row->Tipo."\t";
            echo $row->Operacion."\t";
            echo $row->rutaName."\t";
            echo $row->DiaO."\t";
            echo $row->cve_articulo."\t";
            echo $this->eliminar_acentos($row->Articulo)."\t";
            echo $row->unidadMedida."\t";
            echo ($row->cajas_total)."\t";
            echo ($row->piezas_total)."\t";
            echo number_format($row->Precio, 2)."\t";
            echo number_format($row->Importe, 2)."\t";
            echo number_format($row->IVA, 2)."\t";
            echo number_format($row->Descuento, 2)."\t";
            echo number_format($row->Importe+$row->IVA-$row->Descuento, 2)."\t";
            echo $row->Cancelada."\t";
            echo  "\r\n";


        }
    mysqli_close($conn);
/*
        $this->response(200, [
            'id' =>  $id,
            'folios' => $folios,
            'Embarque' => 'Embarque OK'
        ]);
*/
        exit;
    }


}
