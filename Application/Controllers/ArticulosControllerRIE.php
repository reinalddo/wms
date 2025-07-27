<?php

namespace Application\Controllers;

use Framework\View\View;
use Framework\Helpers\Utils;
use Framework\Http\Response;
use Framework\Http\Controller;
use jakeroid\tools\SimpleXLSX;
use Application\Models\Usuarios;
use Application\Models\Articulos;
use Application\Models\ArticulosExtencion;
use Application\Models\ArticulosImportados;
use Illuminate\Database\Capsule\Manager as Capsule;


/**
 * @version 1.0.0
 * @category Usuarios
 * @author Brayan Rincon <brayan262@gemail.com>
 */
class ArticulosController extends Controller
{
/*
    const CLAVE               = 0;
    const DESCRIPCION         = 1;
    const UNIDAD_DE_MEDIDA    = 2;
    const TIPO                = 3;
    const PIEZAS_X_CAJA       = 4;
    const OBSERVACIONES       = 5;
    const ALMACEN             = 6;
    const COSTO               = 7;
    const TIPO_DE_CAJA        = 8;
    const CODIGO_BARRAS       = 9;
    const PROVEEDOR           = 10;
    const PESO                = 11;
    const CODIGO_BARRAS_CAJA  = 12;
    const KIT                 = 13;
    const MANEJA_LOTE         = 14;
    const MANEJA_SERIE        = 15;
    const ALTO                = 16;
    const LARGO               = 17;
    const ANCHO               = 18;
    const PRECIO_UNITARIO     = 19;
    const GRUPO               = 20;
*/

    const CLAVE_ART          = 0;
    const DESCRIPCION        = 1;
    const CLAVE_ALMACEN      = 2;
    const COD_BARRAS         = 3;
    const CVE_UNIDAD_MEDIDA  = 4;
    const OBSERVACIONES      = 5;
    const COSTO_UNITARIO     = 6;
    const PRECIO_UNITARIO    = 7;
    const IVA                = 8;
    const PESO               = 9;
    const ALTO               = 10;
    const LARGO              = 11;
    const ANCHO              = 12;
    const CVE_PROVEEDOR      = 13;
    const CANT_EQUIVALENTE   = 14;
    const CVE_UNI_MED_EQUIV  = 15;
    const CVE_CAJA_ORIGEN    = 16;
    const COD_BARRAS_CAJA    = 17;
    const CAJAS_POR_TARIMA   = 18;
    const COD_BARRAS_PALLET  = 19;
    const GRUPO              = 20;
    const CLASE              = 21;
    const TIPO               = 22;
    const BAND_KIT           = 23;
    const BAND_PESO          = 24;
    const BAND_SERIE         = 25;
    const BAND_LOTE          = 26;
    const BAND_CADUCIDAD     = 27;
    const BAND_REFRIGERACION = 28;
    const BAND_RIESGO        = 29;
    const BAND_ACTIVO_FIJO   = 30;

    /*
    private $camposRequeridos = [
        self::CLAVE => 'Clave', 
        self::DESCRIPCION => 'Descripcion', 
        self::ALMACEN => 'Almacen'
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

        if (! move_uploaded_file($_FILES['file']['tmp_name'], $file)) 
        {
            $this->response(400, [
                'statusText' =>  "Error al recibir el fichero. Verifique que se tenga permisos para escribir en Cache",
            ]);
        }
        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

        if ( $_FILES['file']['type'] != 'application/vnd.ms-excel' AND
                $_FILES['file']['type'] != 'application/msexcel' AND
                $_FILES['file']['type'] != 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' AND
                $_FILES['file']['type'] != 'application/xls' )
        {
            @unlink($file);
            $this->response(400, [
                'statusText' =>  "Error en elformato del fichero",
            ]);
        }

        $xlsx = new SimpleXLSX( $file );
        $linea = 1;
        $extendido = array();
        $debug = "";
/*
        foreach ($xlsx->rows() as $row)
        {
            
            if($linea == 1) 
            {
                $linea++;
                if(count($row)>19)
                {
                    for($i = 20;$i < count($row);$i++)
                    {
                      $encabezado = explode(":",$row[$i])[1];
                      $extendido[$i] = $encabezado;
                    }
                }
                continue;
            }

            $eval = $this->validarRequeridosImportar($row);
            if( $eval !== TRUE )
            {
                $this->response(400, [
                    'statusText' =>  "La columna \"{$eval}\" de la Fila Nro. {$linea} está vacía. Corrija esto para continuar con la importación.",
                ]);
            }
            $linea++;
        }
*/
        $linea = 1; $importados = 0;
        foreach ($xlsx->rows() as $row)
        {
            if($row[self::CLAVE_ART]!="")
            {
                if($linea == 1) 
                {
                    $linea++;continue;
                }
                $clave_articulo = $this->pSQL($row[self::CLAVE_ART]);
                $sql = "SELECT COUNT(*) as articulo FROM c_articulo WHERE cve_articulo = '$clave_articulo'";
                $rs = mysqli_query($conn, $sql);
                $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                $articulo_existe = $resul['articulo'];

                $clave_almacen = $this->pSQL($row[self::CLAVE_ALMACEN]);
                $sql = "SELECT id FROM c_almacenp WHERE clave = '$clave_almacen'";
                $rs = mysqli_query($conn, $sql);
                $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                $almacen = $resul['id'];

                $clave_proveedor = $this->pSQL($row[self::CVE_PROVEEDOR]);
                $sql = "SELECT COUNT(*) as proveedores FROM c_proveedores WHERE cve_proveedor = '$clave_proveedor'";
                $rs = mysqli_query($conn, $sql);
                $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                $proveedor_existe = $resul['proveedores'];

                if(!$proveedor_existe) $clave_proveedor ="";
                else
                {
                    $sql = "SELECT ID_Proveedor FROM c_proveedores WHERE cve_proveedor = '$clave_proveedor'";
                    $rs = mysqli_query($conn, $sql);
                    $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                    $clave_proveedor = $resul['ID_Proveedor'];
                }

                $clave_tipocaja = $this->pSQL($row[self::CVE_CAJA_ORIGEN]);
                $sql = "SELECT id_tipocaja FROM c_tipocaja WHERE clave = '$clave_tipocaja'";
                $rs = mysqli_query($conn, $sql);
                $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                $tipo_caja = $resul['id_tipocaja'];

                $cve_umed = $this->pSQL($row[self::CVE_UNIDAD_MEDIDA]);
                $sql = "SELECT id_umed FROM c_unimed WHERE cve_umed = '$cve_umed'";
                $rs = mysqli_query($conn, $sql);
                $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                $unidadMedida = $resul['id_umed'];

                $cve_umed_empaque = $this->pSQL($row[self::CVE_UNI_MED_EQUIV]);
                $sql = "SELECT id_umed FROM c_unimed WHERE cve_umed = '$cve_umed_empaque'";
                $rs = mysqli_query($conn, $sql);
                $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                $unidadMedidaEmpaque = $resul['id_umed'];
//                $element = Articulos::where('cve_articulo', '=', $clave_articulo)->first();

//                if($element != NULL)
//                {
//                    $model = $element; 
//                }
//                else 
//                {
//                }

                if(!$articulo_existe)
                {
                    if($proveedor_existe)
                    {
                        $sql = "INSERT INTO rel_articulo_proveedor(Cve_Articulo, Id_Proveedor) VALUES ('$clave_articulo', $clave_proveedor)";
                        $rs = mysqli_query($conn, $sql);
                    }

                    $model = new Articulos(); 
                    $model->cve_articulo           = $clave_articulo;
                    $model->des_articulo           = $this->pSQL($row[self::DESCRIPCION]);
                    $model->unidadMedida           = $unidadMedida;
                    $model->tipo                   = $this->pSQL($row[self::TIPO]);
                    $model->num_multiplo           = $this->pSQL($row[self::CANT_EQUIVALENTE]);
                    $model->empq_cveumed           = $unidadMedidaEmpaque;
                    $model->des_observ             = $this->pSQL($row[self::OBSERVACIONES]);
                    $model->cve_almac              = $almacen;
                    $model->PrecioVenta            = $this->pSQL($row[self::PRECIO_UNITARIO]);
                    $model->tipo_caja              = $tipo_caja;
                    $model->cve_codprov            = $this->pSQL($row[self::COD_BARRAS]);
                    $model->ID_Proveedor           = $clave_proveedor;
                    $model->peso                   = $this->pSQL($row[self::PESO]);
                    $model->barras2                = $this->pSQL($row[self::COD_BARRAS_CAJA]);
                    $model->barras3                = $this->pSQL($row[self::COD_BARRAS_PALLET]);
                    $model->Compuesto              = $this->pSQL($row[self::BAND_KIT]);
                    $model->control_peso           = $this->pSQL($row[self::BAND_PESO]);
                    $model->control_lotes          = $this->pSQL($row[self::BAND_LOTE]);
                    $model->control_numero_series  = $this->pSQL($row[self::BAND_SERIE]);
                    $model->alto                   = $this->pSQL($row[self::ALTO]);
                    $model->fondo                  = $this->pSQL($row[self::LARGO]);
                    $model->ancho                  = $this->pSQL($row[self::ANCHO]);
                    $model->costo                  = $this->pSQL($row[self::COSTO_UNITARIO]);
                    $model->grupo                  = $this->pSQL($row[self::GRUPO]);
                    $model->clasificacion          = $this->pSQL($row[self::CLASE]);
                    $model->mav_pctiva             = $this->pSQL($row[self::IVA]);
                    $model->cajas_palet            = $this->pSQL($row[self::CAJAS_POR_TARIMA]);
                    $model->mat_peligroso          = $this->pSQL($row[self::BAND_RIESGO]);
                    $model->Caduca                 = $this->pSQL($row[self::BAND_CADUCIDAD]);
                    $model->req_refrigeracion      = $this->pSQL($row[self::BAND_REFRIGERACION]);
                    $model->tipo_producto          = $this->pSQL($row[self::BAND_ACTIVO_FIJO]);

                    $model->save();
                    $importados++;
                }

                /*
                if(count($extendido) > 0)
                {
                    $debug = $id = Articulos::where('cve_articulo', '=', $clave)->first()["id"];
                    $element2 = ArticulosExtencion::where('id_articulo', '=', $id)->first();

                    if($element2 != NULL)
                    {
                        $model_ext = $element2; 
                    }
                    else 
                    {
                        $model_ext = new ArticulosExtencion(); 
                    }
                  
                    $model_ext->id_articulo = $id;
                    foreach($extendido as $key => $val)
                    {
                        $model_ext[$val] = $this->pSQL($row[$key]);
                    }
                    $model_ext->save();
                }
                */
            }
            $linea++;
        }
        @unlink($file);
        $this->response(200, [
            'debug' => $debug,
            'ext' => $extendido,
            'statusText' =>  "Artículos importados con exito. Total de artículos: \"{$importados}\"",
        ]);
    }

    public function validarRequeridosImportar($row)
    {
        foreach ($this->camposRequeridos as $key => $campo){
            if( empty($row[$key]) )
            {
                return $campo;
            }
        }
        return true;
    }
  
    public function exportar()
    {
        $columnas = [
            'clave',
            'descripcion',
            'almacen',
            'proveedor',
            'barras2',
            'alto',
            'fondo',
            'ancho',
            'clasificacion',
            'costo',
            'grupo',
            'control_lotes',
            'control_numero_series',
            'control_peso',
            'control_volumen',
            'req_refrigeracion',
            'mat_peligroso',
            'Compuesto',
            'tipo_caja',
        ];

        $data_rutas = Articulos::get();
        $filename = "articulos_".date('Ymd') . ".xls";
        ob_clean();
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        
        foreach($columnas as $column) 
        {            
            echo $column . "\t" ;            
        }
        print("\r\n");

        foreach($data_rutas as $row)
        {            
            echo $this->clear_column($row->cve_articulo) . "\t";
            echo $this->clear_column($row->des_articulo) . "\t";
            echo $this->clear_column($row->cve_almac) . "\t";
            echo $this->clear_column($row->cve_codprov) . "\t";
            echo $this->clear_column($row->barras2) . "\t";
            echo $this->clear_column($row->alto) . "\t";
            echo $this->clear_column($row->fondo) . "\t";
            echo $this->clear_column($row->ancho) . "\t";
            echo $this->clear_column($row->clasificacion) . "\t";
            echo $this->clear_column($row->costo) . "\t";
            echo $this->clear_column($row->grupo) . "\t";
            echo $this->clear_column($row->control_lotes) . "\t";
            echo $this->clear_column($row->control_numero_series) . "\t";
            echo $this->clear_column($row->control_peso) . "\t";
            echo $this->clear_column($row->control_volumen) . "\t";
            echo $this->clear_column($row->req_refrigeracion) . "\t";
            echo $this->clear_column($row->mat_peligroso) . "\t";
            echo $this->clear_column($row->Compuesto) . "\t";
            echo $this->clear_column($row->tipo_caja) . "\t";
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
