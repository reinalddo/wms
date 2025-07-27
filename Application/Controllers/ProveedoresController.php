<?php

namespace Application\Controllers;

use Framework\View\View;
use Framework\Helpers\Utils;
use Framework\Http\Response;
use Framework\Http\Controller;
use jakeroid\tools\SimpleXLSX;
use Application\Models\Proveedores;
use Illuminate\Database\Capsule\Manager as Capsule;


/**
 * @version 1.0.0
 * @category Usuarios
 * @author Brayan Rincon <brayan262@gemail.com>
 */
class ProveedoresController extends Controller
{
    const CLAVE = 0;
    const ARTICULO = 1;
    const CADUCIDAD = 2;

    private $camposRequeridos = [
        self::CLAVE => 'Clave', 
        self::ARTICULO => 'Artículo', 
        self::CADUCIDAD => 'Caducidad',
    ];
    
    /**
     * Renderiza la vista general
     *
     * @return void
     */
    public function index()
    {
       
    }

    const CLAVE_EMPRESA      = 0;
    const NOMBRE_PROV        = 1;
    const RUT                = 2;
    const DIRECCION          = 3;
    const CODIGO_DANE        = 4;
    const CVE_PROV           = 5;
    const COLONIA            = 6;
    const CIUDAD             = 7;
    const ESTADO             = 8;
    const PAIS               = 9;
    const TELEFONO1          = 10;
    const TELEFONO2          = 11;
    const EMPRESA_PROV       = 12;
    const ES_TRANSPORTADORA  = 13;

    public function importar()
    {
        $dir_cache = PATH_APP . 'Cache/';
        $file = $dir_cache . basename($_FILES['file']['name']);
        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

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
        foreach ($xlsx->rows() as $row)
        {
            if($linea == 1) {
                $linea++;continue;
            }

                $cve_proveedor = $this->pSQL($row[self::CVE_PROV]);
                $sql = "SELECT COUNT(*) as existe FROM c_proveedores WHERE cve_proveedor = '$cve_proveedor'";
                $rs = mysqli_query($conn, $sql);
                $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                $existe_prov = $resul['existe'];

                if($existe_prov) continue;

                $clave_empresa = $this->pSQL($row[self::CLAVE_EMPRESA]);
                $sql = "SELECT cve_cia FROM c_compania WHERE clave_empresa = '$clave_empresa'";
                $rs = mysqli_query($conn, $sql);
                $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                $id_empresa = $resul['cve_cia'];

                $empresa_prov = $this->pSQL($row[self::EMPRESA_PROV]);
                if($empresa_prov == 'S') $empresa_prov = 1;
                if($empresa_prov == 'N') $empresa_prov = 0;

            if($id_empresa)
            {
            $model = new Proveedores();

            $es_transportista = 0; 

            if($this->pSQL($row[self::ES_TRANSPORTADORA]) == 'S')
                $es_transportista = 1; 

            $model->empresa          = $id_empresa;
            $model->nombre           = $this->pSQL($row[self::NOMBRE_PROV]);
            $model->rut              = $this->pSQL($row[self::RUT]);
            $model->direccion        = $this->pSQL($row[self::DIRECCION]);
            $model->cve_dane         = $this->pSQL($row[self::CODIGO_DANE]);
            $model->cve_proveedor    = $this->pSQL($row[self::CVE_PROV]);
            $model->colonia          = $this->pSQL($row[self::COLONIA]);
            $model->ciudad           = $this->pSQL($row[self::CIUDAD]);
            $model->estado           = $this->pSQL($row[self::ESTADO]);
            $model->pais             = $this->pSQL($row[self::PAIS]);
            $model->telefono1        = $this->pSQL($row[self::TELEFONO1]);
            $model->telefono2        = $this->pSQL($row[self::TELEFONO2]);
            $model->es_cliente       = $empresa_prov;
            $model->es_transportista = $es_transportista;

            $model->save();
          
            /*
            $clave = $this->pSQL($row[self::CLAVE]);
            $element = Proveedores::where('LOTE', '=', $clave)->first();

            if($element != NULL){
                $model = $element; 
            }
            else {
                $model = new Proveedores(); 
            }
            
            $model->LOTE            = $clave;
            $model->cve_articulo    = $this->pSQL($row[self::ARTICULO]);
            $model->CADUCIDAD       = $this->pSQL($row[self::CADUCIDAD]);
            $model->save();
            */
            $linea++;
            }
        }
        
        @unlink($file);
        $linea -= 2;

        if($linea > 0)
            $this->response(200, [
                'statusText' =>  "Proveedores importados con exito. Total de Proveedores: \"{$linea}\"",
            ]);
        else
            $this->response(200, [
                'statusText' =>  "No se ha importador ningún Proveedor, Por favor Revise el Layout",
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
            'empresa',
            'nombre',
            'rut',
            'direccion',
            'cve_dane',
            'cve_proveedor',
            'colonia',
            'ciudad',
            'estado',
            'pais',
            'telefono1',
            'telefono2',
        ];

        $data_proveedor = Proveedores::get([
            'Empresa',
            'Nombre',
            'RUT',
            'direccion',
            'cve_dane',
            'cve_proveedor',
            'colonia',
            'ciudad',
            'estado',
            'pais',
            'telefono1',
            'telefono2',
        ]);

        $filename = "proveedores_".date('Ymd') . ".xls";

        ob_clean();
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        
        foreach($columnas as $column) {            
            echo $column . "\t" ;            
        }
        print("\r\n");

        foreach($data_proveedor as $row)
        {            
            echo $this->clear_column($row->Empresa) . "\t";
            echo $this->clear_column($row->Nombre) . "\t";
            echo $this->clear_column($row->RUT) . "\t";
            echo $this->clear_column($row->direccion) . "\t";
            echo $this->clear_column($row->cve_dane) . "\t";
            echo $this->clear_column($row->cve_proveedor) . "\t";
            echo $this->clear_column($row->colonia) . "\t";
            echo $this->clear_column($row->ciudad) . "\t";
            echo $this->clear_column($row->estado) . "\t";
            echo $this->clear_column($row->pais) . "\t";
            echo $this->clear_column($row->telefono1) . "\t";
            echo $this->clear_column($row->telefono2) . "\t";
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
