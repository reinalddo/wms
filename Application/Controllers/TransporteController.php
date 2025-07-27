<?php

namespace Application\Controllers;

use Framework\View\View;
use Framework\Helpers\Utils;
use Framework\Http\Response;
use Framework\Http\Controller;
use jakeroid\tools\SimpleXLSX;
use Application\Models\Transportes;
use Application\Models\Proveedores;
use Illuminate\Database\Capsule\Manager as Capsule;


/**
 * @version 1.0.0
 * @category Transportes
 * @author Brayan Rincon <brayan262@gemail.com>
 */
class TransporteController extends Controller
{
    const CLAVE = 0;
    const NOMBRE = 1;
    const PLACAS = 2;    
    const CVE_CIA = 3;
    const TIPO = 4;
    const ACTIVO = 5;

    private $camposRequeridos = [
        self::CLAVE => 'Clave', 
        self::NOMBRE => 'Nombre', 
        self::PLACAS => 'Placas',         
        self::CVE_CIA => 'Clave cia',
        self::TIPO => 'Tipo de transporte',
    ];
    
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
        $linea = 1;
        $registros = 0;
        
        foreach ($xlsx->rows() as $row)
        {
            if($linea == 1) {
                $linea++;continue;
            }

            $eval = $this->validarRequeridosImportar($row);
            if( $eval === TRUE ){
          
            }
            else {
                $this->response(400, [
                    'statusText' =>  "La columna \"{$eval}\" de la Fila Nro. {$linea} está vacía. Corrija esto para continuar con la importación.",
                ]);
            }
            $linea++;
        }

        $linea = 1;
        foreach ($xlsx->rows() as $row)
        {
            if($linea == 1) {
                $linea++;continue;
            }
            $clave = $this->pSQL($row[self::CLAVE]);
            $element = Transportes::where('ID_Transporte', $clave)->first();


        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

            $clave_cia = $this->pSQL($row[self::CVE_CIA]);
            $sql = "SELECT ID_Proveedor FROM c_proveedores WHERE cve_proveedor = '$clave_cia'";
            $rs = mysqli_query($conn, $sql);
            $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
            $clave_cia = $resul['ID_Proveedor'];


            if($element != NULL){
                $model = $element; 
            }
            else {
                $model = new Transportes(); 
            }
            
            $model->ID_Transporte   = $clave;
            $model->Nombre          = $this->pSQL($row[self::NOMBRE]);
            $model->Placas          = $this->pSQL($row[self::PLACAS]);
            $model->cve_cia         = $clave_cia;
            $model->id_almac        = $_SESSION['id_almacen'];
            $model->tipo_transporte = $this->pSQL($row[self::TIPO]);
            $model->Activo          = 1;

            $model->save();
            $linea++;
            $registros++;
        }
        
        @unlink($file);

        $this->response(200, [
            'statusText' =>  "Registros importados con exito. Total de registros: \"{$linea}\"",
        ]);

    }

    public function validarRequeridosImportar($row)
    {
        foreach ($this->camposRequeridos as $key => $campo){
            if( $row[$key] == '' ){
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
            'clave',
            'nombre',
            'placas',
            'clave_cia',
            'tipo',
            'activo'
            
        ];

        $data_rutas = Transportes::get([
            'ID_Transporte',
            'Nombre',
            'Placas',
            'cve_cia',
            'tipo_transporte',
            'Activo'
        ]);

        $filename = "transportes_".date('Ymd') . ".xls";

        ob_clean();
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        
        foreach($columnas as $column) {            
            echo $column . "\t" ;            
        }
        print("\r\n");

        foreach($data_rutas as $row)
        {            
            echo $this->clear_column($row->ID_Transporte) . "\t";
            echo $this->clear_column($row->Nombre) . "\t";
            echo $this->clear_column($row->Placas) . "\t";
            echo $this->clear_column($row->cve_cia) . "\t";
            echo $this->clear_column($row->tipo_transporte) . "\t";
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
