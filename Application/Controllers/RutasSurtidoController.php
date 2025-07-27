<?php

namespace Application\Controllers;

use Framework\View\View;
use Framework\Helpers\Utils;
use Framework\Http\Response;
use Framework\Http\Controller;
use jakeroid\tools\SimpleXLSX;
use Application\Models\RutasSurtido;
use Illuminate\Database\Capsule\Manager as Capsule;


/**
 * @version 1.0.0
 * @category Usuarios
 * @author Brayan Rincon <brayan262@gemail.com>
 */
class RutasSurtidoController extends Controller
{
    const BL = 0;
    const SECUENCIA = 1;
  
/*
    private $camposRequeridos = [
        self::CLAVE => 'Clave', 
        self::DESCRIPCION => 'Descripcion', 
        self::CLAVE_ALMACEN => 'Clave almacén',
    ];
*/    
    /**
     * Renderiza la vista general
     *
     * @return void
     */
    public function index()
    {
       
    }

    public function importar()
    {
        $dir_cache = PATH_APP . 'Cache/';
        $file = $dir_cache . basename($_FILES['file']['name']);

        if (! move_uploaded_file($_FILES['file']['tmp_name'], $file)) {
            $this->response(400, [
                'statusText' =>  "Error al recibir el fichero, verifique que se tenga permisos para escribir en Cache",
            ]);
        }


        if ( $_FILES['file']['type'] != 'application/vnd.ms-excel' AND
                $_FILES['file']['type'] != 'application/msexcel' AND
                $_FILES['file']['type'] != 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' AND
                $_FILES['file']['type'] != 'application/xls' )
            {
            @unlink($file);
            $this->response(400, [
                'statusText' =>  "Error en el formato del fichero",
            ]);
        }

        $xlsx = new SimpleXLSX( $file );

        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
      $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))
        echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);

/*
        $linea = 1;
        foreach ($xlsx->rows() as $row)
        {
            if($linea == 1) {
                $linea++;continue;
            }

            $eval = $this->validarRequeridosImportar($row);
            if( $eval !== TRUE ){
                $this->response(400, [
                    'statusText' =>  "La columna \"{$eval}\" de la Fila Nro. {$linea} está vacía. Corrija esto para continuar con la importación.",
                ]);
            }
            $linea++;
        }
*/
        $cve_almac = $_POST['almacen'];
        $usuarios = $_POST['usuarios'];
        $agregarubicaciones = $_POST['agregarubicaciones'];

        $linea = 1; $primer_valor_ok = false; $idr = 0;$registros = 0;
        foreach ($xlsx->rows() as $row)
        {
            if($linea == 1) {
                $linea++;continue;
            }

            $codigobl = $this->pSQL($row[self::BL]);

            $sql = "SELECT COUNT(*) AS existe
                    FROM c_ubicacion u
                    INNER JOIN c_almacen c ON c.cve_almac = u.cve_almac
                    INNER JOIN td_ruta_surtido r ON r.idy_ubica = u.idy_ubica AND u.CodigoCSD = '$codigobl' AND u.picking = 'S' AND u.AreaProduccion = 'N' AND r.Activo = 1 
                    WHERE c.cve_almacenp = '$cve_almac'";
            $rs = mysqli_query($conn, $sql);
            $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
            $existe             = $resul['existe'];

            $sql = "SELECT u.idy_ubica, IFNULL(u.picking, 'N') as picking, IFNULL(u.AreaProduccion, 'N') AS AreaProduccion 
                    FROM c_ubicacion u
                    INNER JOIN c_almacen c ON c.cve_almac = u.cve_almac
                    WHERE u.CodigoCSD = '$codigobl' AND u.Activo = 1 AND c.cve_almacenp = '$cve_almac'";
            $rs = mysqli_query($conn, $sql);
            $existe_bl = mysqli_num_rows($rs);
            $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
            $idy_ubica          = $resul['idy_ubica'];
            $es_picking         = $resul['picking'];
            $es_area_produccion = $resul['AreaProduccion'];

            if($es_picking == 'N' || $es_area_produccion == 'S' || $existe_bl == 0) {
                $linea++;continue;
            }


            if(!$existe)
            {
                if(!$primer_valor_ok && $agregarubicaciones == "")
                {
                    $nombre    = $_POST['nombre_ruta'];
                    //$cve_almac = $_POST['zonaalmacenajei'];

                    $sql = "INSERT INTO th_ruta_surtido(nombre, status, cve_almac) VALUES('$nombre', 'A', $cve_almac)";
                    $rs = mysqli_query($conn, $sql);

                    $sql = "SELECT IFNULL(MAX(idr), 0) AS idr FROM th_ruta_surtido";
                    $rs = mysqli_query($conn, $sql);
                    $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                    $idr = $resul['idr'];

                    $primer_valor_ok = true;
                }

                if($agregarubicaciones != "" && !$primer_valor_ok)
                {
                    $primer_valor_ok = true;
                    $idr = $agregarubicaciones;

                    $sql = "SELECT IFNULL(MAX(orden_secuencia), 0) AS orden_secuencia FROM td_ruta_surtido WHERE idr = $idr";
                    $rs = mysqli_query($conn, $sql);
                    $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                    $registros = $resul['orden_secuencia'];
                }
                
                if($primer_valor_ok)
                {
                    $registros++;
                    //$orden_secuencia = $this->pSQL($row[self::SECUENCIA]);
                    $orden_secuencia = $registros;
                    $sql = "INSERT INTO td_ruta_surtido(idr, idy_ubica, orden_secuencia) VALUES($idr, $idy_ubica, $orden_secuencia)";
                    $rs = mysqli_query($conn, $sql);
                }

            }
            $linea++;
        }

        $usuarios_arr = explode(",", $usuarios);

        for($i = 0; $i < count($usuarios_arr); $i++)
        {
            $sql = "INSERT INTO rel_usuario_ruta(id_usuario, id_ruta) VALUES($usuarios_arr[$i], $idr)";
            $rs = mysqli_query($conn, $sql);
        }

        @unlink($file);

        $this->response(200, [
            'statusText' =>  "Ruta de Surtido agregada con exito. Total de Rutas: \"{$registros}\""
        ]);

    }

    public function validarRequeridosImportar($row)
    {
        foreach ($this->camposRequeridos as $key => $campo){
            if( empty($row[$key]) ){
                return $campo;
            }
        }
        return true;
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    public function exportar()
    {
        $columnas = [
            'clave_ruta',
            'descripcion',
            'estatus',
            'direccion',
            'clave_almacen',
            'activo',
        ];

        $data_rutas = Rutas::get([
            'cve_ruta',
            'descripcion',
            'status',
            'direccion',
            'cve_almacenp',
            'Activo',
        ]);

        $filename = "proveedores_".date('Ymd') . ".xls";

        ob_clean();
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        
        foreach($columnas as $column) {            
            echo $column . "\t" ;            
        }
        print("\r\n");

        foreach($data_rutas as $row)
        {            
            echo $this->clear_column($row->cve_ruta) . "\t";
            echo $this->clear_column($row->descripcion) . "\t";
            echo $this->clear_column($row->status) . "\t";
            echo $this->clear_column($row->direccion) . "\t";
            echo $this->clear_column($row->cve_almacenp) . "\t";
            echo $this->clear_column($row->Activo) . "\t";
            echo  "\r\n";
        }
        exit;
        
    }

    /**
     * Undocumented function
     *
     * @param [type] $str
     * @return void
     */
    private function clear_column(&$str)
    {
        $str = preg_replace("/\t/", "\\t", $str);
        $str = preg_replace("/\r?\n/", "\\n", $str);
        if(strstr($str, '"')) $str = '"' . str_replace('"', '""', $str) . '"';
        return $str;
    }


}
