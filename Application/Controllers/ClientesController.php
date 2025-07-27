<?php

namespace Application\Controllers;

use Framework\View\View;
use Framework\Helpers\Utils;
use Framework\Http\Response;
use Framework\Http\Controller;
use jakeroid\tools\SimpleXLSX;
use Application\Models\Contactos;
use Application\Models\Clientes;
use Application\Models\ClientesRutas;
use Illuminate\Database\Capsule\Manager as Capsule;


/**
 * @version 1.0.0
 * @category Clientes
 * @author Brayan Rincon <brayan262@gemail.com>
 */
class ClientesController extends Controller
{
    const CLAVE = 0;
    const ALMACEN_ID = 4;
/*
    const CLAVE = 0;
    const CLAVE_CLIENTE_PROVEEDOR = 1;
    const RAZON_SOCIAL = 2;
    const RAZON_COMERCIAL = 3;
    const ALMACEN_ID = 4;
    const CALLE = 5;
    const COLONIA = 6;
    const CIUDAD = 7;
    const ESTADO = 8;
    const PAIS = 9;
    const POSTAL = 10;
    const CONTACTO = 11;
    const RFC = 12;
    const TELEFONO1 = 13;
    const TELEFONO2 = 14;
    const CONDICION_PAGO = 15;
    const PROVEEDOR_ID = 16;
    const TIPO = 17;
    const ZONA_VENTA = 18;
    const ACTIVO = 19;
    const RUTA = 20;  
*/
    const CLAVE_ALMACEN           =  0;
    const CLAVE_CLIENTE           =  1;
    const CLAVE_CLIENTE_PROVEEDOR =  2;
    const NOMBRE_COMERCIAL        =  3;
    const RAZON_SOCIAL            =  4;
    const CALLE                   =  5;
    const COLONIA                 =  6;
    const POSTAL                  =  7;
    const ESTADO                  =  8;
    const CIUDAD                  =  9;
    const PAIS                    = 10;
    const RFC                     = 11;
    const CONTACTO                = 12;
    const TELEFONO1               = 13;
    const TELEFONO2               = 14;
    const CLAVE_PROVEEDOR         = 15;
    const EMAIL                   = 16;
    const LATITUD                 = 17;
    const LONGITUD                = 18;
    const CREDITO                 = 19;
    const LIMITE_CREDITO          = 20;
    const DIAS_CREDITO            = 21;



    const CLAVE_CTO     = 0;
    const NOMBRE_CTO    = 1;
    const APELLIDO_CTO  = 2;
    const EMAIL_CTO     = 3;
    const TELEF1_CTO    = 4;
    const TELEF2_CTO    = 5;
    const PAIS_CTO      = 6;
    const ESTADO_CTO    = 7;
    const CIUDAD_CTO    = 8;
    const DIRECCION_CTO = 9;


    private $camposRequeridos = [
        self::CLAVE => 'Clave', 
        self::CLAVE_CLIENTE_PROVEEDOR => 'Cliente Proveedor', 
        self::RAZON_SOCIAL => 'Razón social',
        self::ALMACEN_ID => 'Almacén',
    ];
    
    /**
     * Renderiza la vista general
     *
     * @return void
     */
    public function index(){}

    public function importar()
    {
        $dir_cache = PATH_APP . 'Cache/';
        $file = $dir_cache . basename($_FILES['file']['name']);
    
        if (! move_uploaded_file($_FILES['file']['tmp_name'], $file)) 
        {
            $this->response(400, [
                'statusText' =>  "Error al recibir el fichero, verifique que se tenga permisos para escribir en Cache",
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
                'statusText' =>  "Error en el formato del fichero",
            ]);
        }

        $xlsx = new SimpleXLSX( $file );
        $linea = 1;
        $debug = "";
/*
        foreach ($xlsx->rows() as $row)
        {
            if($linea == 1) 
            {
                $linea++;continue;
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
        $mensaje_proveedor = ""; $proveedores_no_existentes = ""; $mostrar_mensaje = false;
        $mensaje_almacen   = ""; $almacenes_no_existentes   = "";
        $mensaje_cliente   = ""; $clientes_no_existentes    = "";

        foreach ($xlsx->rows() as $row)
        {
            
            if($linea == 1) 
            {
                $linea++;continue;
            }
            //$clave = $this->pSQL($row[self::CLAVE_CLIENTE]);
            //$element = Clientes::where('Cve_Clte', $clave)->first();

            //if($element != NULL)
            //{
            //    $model = $element; 
            //}
            //else 
            //{
                $model = new Clientes(); 
            //}
/*            
            $model->Cve_Clte            = $this->pSQL($row[self::CLAVE_CLIENTE]);
            $model->RazonSocial         = $this->pSQL($row[self::RAZON_SOCIAL]);
            $model->RazonComercial      = $this->pSQL($row[self::RAZON_COMERCIAL]);
            $model->Cve_CteProv         = $this->pSQL($row[self::CLAVE_CLIENTE_PROVEEDOR]);
            $model->CalleNumero         = $this->pSQL($row[self::CALLE]);
            $model->Colonia             = $this->pSQL($row[self::COLONIA]);
            $model->Ciudad              = $this->pSQL($row[self::CIUDAD]);
            $model->Estado              = $this->pSQL($row[self::ESTADO]);
            $model->Pais                = $this->pSQL($row[self::PAIS]);
            $model->CodigoPostal        = $this->pSQL($row[self::POSTAL]);
            $model->RFC                 = $this->pSQL($row[self::RFC]);
            $model->Telefono1           = $this->pSQL($row[self::TELEFONO1]);
            $model->Telefono2           = $this->pSQL($row[self::TELEFONO2]);
            $model->ClienteTipo         = $this->pSQL($row[self::TIPO]);
            $model->ZonaVenta           = $this->pSQL($row[self::ZONA_VENTA]);
            $model->ID_Proveedor        = $this->pSQL($row[self::CLAVE_PROVEEDOR]);
            $model->Cve_Almacenp        = $this->pSQL($row[self::CLAVE_ALMACEN]);
            $model->Contacto            = $this->pSQL($row[self::CONTACTO]);
            $model->CondicionPago       = $this->pSQL($row[self::CONDICION_PAGO]);
            $model->Activo              = $this->pSQL($row[self::ACTIVO]);

CLAVE_ALMACEN           =  0; CLAVE_CLIENTE           =  1;CLAVE_CLIENTE_PROVEEDOR =  2;RAZON_SOCIAL            =  3;
CALLE                   =  4;COLONIA                  =  5;POSTAL                  =  6;ESTADO                  =  7;
CIUDAD                  =  8;PAIS                     =  9;RFC                     = 10;CONTACTO                = 11;
TELEFONO1               = 12;TELEFONO2                = 13;CLAVE_PROVEEDOR         = 14;EMAIL                   = 15;
LATITUD                 = 16;LONGITUD                 = 17;
*/

          $clave_cliente           = $this->pSQL($row[self::CLAVE_CLIENTE]);
          $clave_cliente_proveedor = $this->pSQL($row[self::CLAVE_CLIENTE_PROVEEDOR]);
          $clave_proveedor         = $this->pSQL($row[self::CLAVE_PROVEEDOR]);
          $clave_almacen           = $this->pSQL($row[self::CLAVE_ALMACEN]);

    //if($clave_proveedor)
    //{
          $sql = "SELECT COUNT(*) as cliente FROM c_cliente WHERE Cve_Clte = '$clave_cliente' AND Cve_Almacenp = (SELECT id FROM c_almacenp WHERE clave = '$clave_almacen')";
          // AND ID_Proveedor = (SELECT ID_Proveedor FROM c_proveedores WHERE cve_proveedor = '$clave_proveedor')
          //AND Cve_CteProv = '$clave_cliente_proveedor'
          $rs = mysqli_query($conn, $sql);
          $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
          $cliente_existe = $resul['cliente'];
          //$cliente_existe = mysqli_num_rows($rs);
          //$ID_Proveedor_Exist = $resul['ID_Proveedor'];

          $sql = "SELECT COUNT(*) as proveedores FROM c_proveedores WHERE cve_proveedor = '$clave_proveedor'";
          $rs = mysqli_query($conn, $sql);
          $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
          $proveedor_existe = $resul['proveedores'];

          $sql = "SELECT id FROM c_almacenp WHERE clave = '$clave_almacen'";
          $rs = mysqli_query($conn, $sql);
          $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
          $cve_almacen = $resul['id'];


        if($cve_almacen)
        {
          //if(!$proveedor_existe) $clave_proveedor = "";
          //else
          if($proveedor_existe)
          {
              $sql = "SELECT ID_Proveedor FROM c_proveedores WHERE cve_proveedor = '$clave_proveedor'";
              $rs = mysqli_query($conn, $sql);
              $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
              $clave_proveedor = $resul['ID_Proveedor'];
          }

            /*&& $proveedor_existe > 0*/
            if($cliente_existe == 0)
            {
                $cve_clte_prov = $this->pSQL($row[self::CLAVE_CLIENTE_PROVEEDOR]);
                if(!$cve_clte_prov)  $cve_clte_prov = $clave_cliente;
                $model->Cve_Clte            = $clave_cliente;
                $model->RazonSocial         = $this->pSQL($row[self::RAZON_SOCIAL]);//////
                $model->RazonComercial      = $this->pSQL($row[self::NOMBRE_COMERCIAL]);//////
                $model->Cve_CteProv         = $cve_clte_prov;
                $model->CalleNumero         = $this->pSQL($row[self::CALLE]);//////
                $model->Colonia             = $this->pSQL($row[self::COLONIA]);//////
                $model->Ciudad              = $this->pSQL($row[self::CIUDAD]);//////
                $model->Estado              = $this->pSQL($row[self::ESTADO]);//////
                $model->Pais                = $this->pSQL($row[self::PAIS]);
                $model->CodigoPostal        = $this->pSQL($row[self::POSTAL]);
                $model->RFC                 = $this->pSQL($row[self::RFC]);
                $model->Telefono1           = $this->pSQL($row[self::TELEFONO1]);
                $model->Telefono2           = $this->pSQL($row[self::TELEFONO2]);
                $model->ID_Proveedor        = $clave_proveedor;
                $model->Cve_Almacenp        = $cve_almacen;
                $model->Contacto            = $this->pSQL($row[self::CONTACTO]);
                $model->email_cliente       = $this->pSQL($row[self::EMAIL]);
                $model->latitud             = $this->pSQL($row[self::LATITUD]);
                $model->longitud            = $this->pSQL($row[self::LONGITUD]);
                $model->credito             = $this->pSQL($row[self::CREDITO]);
                $model->limite_credito      = $this->pSQL($row[self::LIMITE_CREDITO]);
                $model->dias_credito        = $this->pSQL($row[self::DIAS_CREDITO]);
                $model->save();
                $importados++;

                $razon        = $this->pSQL($row[self::RAZON_SOCIAL]);
                $direccion    = $this->pSQL($row[self::CALLE]);
                $colonia      = $this->pSQL($row[self::COLONIA]);
                $codigop      = $this->pSQL($row[self::POSTAL]);
                $ciudad       = $this->pSQL($row[self::CIUDAD]);
                $estado       = $this->pSQL($row[self::ESTADO]);
                $contacto     = $this->pSQL($row[self::CONTACTO]);
                $telefono     = $this->pSQL($row[self::TELEFONO1]);
                $principal    = 1;
                $emailDest    = $this->pSQL($row[self::EMAIL]);
                $latitudDest  = $this->pSQL($row[self::LATITUD]);
                $longitudDest = $this->pSQL($row[self::LONGITUD]);


              $sql = "  SELECT COUNT(*) AS existe_dest FROM c_destinatarios 
                        WHERE REPLACE(razonsocial, ' ', '') = REPLACE('$razon', ' ', '') AND 
                              REPLACE(direccion, ' ', '')   = REPLACE('$direccion', ' ', '') AND 
                              REPLACE(colonia, ' ', '')     = REPLACE('$colonia', ' ', '') AND 
                              REPLACE(postal, ' ', '')      = REPLACE('$codigop', ' ', '') AND 
                              REPLACE(ciudad, ' ', '')      = REPLACE('$ciudad', ' ', '') AND 
                              REPLACE(estado, ' ', '')      = REPLACE('$estado', ' ', '')
                              AND Cve_Clte = '$clave_cliente'
                    ";
              $rs = mysqli_query($conn, $sql);
              $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
              $existe_dest = $resul['existe_dest'];

                if($existe_dest == 0)
                {
                    $sql = 
                          "
                          INSERT INTO c_destinatarios 
                            (Cve_Clte, 
                            razonsocial,
                            direccion, 
                            colonia,
                            postal, 
                            ciudad, 
                            estado, 
                            contacto, 
                            telefono,
                            dir_principal,
                            email_destinatario,
                            latitud,
                            longitud) 
                          VALUES 
                            ('{$clave_cliente}',
                            '{$razon}', 
                            '{$direccion}',
                            '{$colonia}',
                            '{$codigop}', 
                            '{$ciudad}', 
                            '{$estado}', 
                            '{$contacto}',
                            '{$telefono}',
                            '{$principal}',
                            '{$emailDest}',
                            '{$latitudDest}',
                            '{$longitudDest}');
                          ";
                    $rs = mysqli_query($conn, $sql);
                }

            }
/*
            if($proveedor_existe == 0)
            { 
                $proveedores_no_existentes .= $this->pSQL($row[self::CLAVE_PROVEEDOR])."\n"; 
                $mostrar_mensaje = true;
            }
*/
            if($cliente_existe == 0)
            { 
                $clientes_no_existentes .= $this->pSQL($row[self::CLAVE_CLIENTE])."\n"; 
            }

        }
        else
            $almacenes_no_existentes .= $cve_almacen."\n";
    //}
    //else if($proveedor_existe == 0){ $proveedores_no_existentes .= $this->pSQL($row[self::CLAVE_PROVEEDOR])."\n"; $mostrar_mensaje = true;}

            /*
            $debug = $ruta =  $this->pSQL($row[self::RUTA]);
            if($ruta !== NULL)
            {
                $clave = $this->pSQL($row[self::CLAVE_CLIENTE]);
                $elementRuta = ClientesRutas::where('clave_cliente', $clave)->where('clave_ruta',$ruta)->first();
              
                if($elementRuta == NULL)
                {
                    $modelRuta = new ClientesRutas();
                    $modelRuta->clave_cliente  = $clave;
                    $modelRuta->clave_ruta     = $ruta;
                    $modelRuta->save();
                }
            }
            */
            $linea++;
        }
        
        @unlink($file);

        $proveedores_no_existentes = str_replace(" ", "", $proveedores_no_existentes);
        $almacenes_no_existentes   = str_replace(" ", "", $almacenes_no_existentes);
        if($proveedores_no_existentes && $mostrar_mensaje == true) $mensaje_proveedor .= "\n\nLos siguientes proveedores no existen en el sistema: \n".$proveedores_no_existentes;

        if($almacenes_no_existentes) $mensaje_almacen .= "\n\nLas siguientes Claves de Almacén no existen en el sistema: \n".$almacenes_no_existentes;

        if($clientes_no_existentes) $mensaje_cliente .= "\n\nLas siguientes Clientes no existen en el sistema: \n".$clientes_no_existentes;
        //{$mensaje_proveedor} {$almacenes_no_existentes} {$clientes_no_existentes}
        $this->response(200, [
            'debug' => $debug,
            'statusText' =>  "Clientes importados con exito. Total de Clientes: \"{$importados}\"",
        ]);exit;

    }

    public function importarcontactos()
    {
        $dir_cache = PATH_APP . 'Cache/';
        $file = $dir_cache . basename($_FILES['file']['name']);
    
        if (! move_uploaded_file($_FILES['file']['tmp_name'], $file)) 
        {
            $this->response(400, [
                'statusText' =>  "Error al recibir el fichero, verifique que se tenga permisos para escribir en Cache",
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
                'statusText' =>  "Error en el formato del fichero",
            ]);
        }

        $xlsx = new SimpleXLSX( $file );
        $linea = 1;
        $debug = "";
/*
        foreach ($xlsx->rows() as $row)
        {
            if($linea == 1) 
            {
                $linea++;continue;
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
        $mostrar_mensaje = false;

        foreach ($xlsx->rows() as $row)
        {
            
            if($linea == 1) 
            {
                $linea++;continue;
            }
            //$clave = $this->pSQL($row[self::CLAVE_CLIENTE]);
            //$element = Clientes::where('Cve_Clte', $clave)->first();

            //if($element != NULL)
            //{
            //    $model = $element; 
            //}
            //else 
            //{
                $model = new Contactos(); 
            //}

          $clave     = $this->pSQL($row[self::CLAVE_CTO]);
          $nombre    = $this->pSQL($row[self::NOMBRE_CTO]);
          $apellido  = $this->pSQL($row[self::APELLIDO_CTO]);
          $correo    = $this->pSQL($row[self::EMAIL_CTO]);
          $telefono1 = $this->pSQL($row[self::TELEF1_CTO]);
          $telefono2 = $this->pSQL($row[self::TELEF2_CTO]);
          $pais      = $this->pSQL($row[self::PAIS_CTO]);
          $estado    = $this->pSQL($row[self::ESTADO_CTO]);
          $ciudad    = $this->pSQL($row[self::CIUDAD_CTO]);
          $direccion = $this->pSQL($row[self::DIRECCION_CTO]);

          $sql = "SELECT COUNT(*) as existe FROM c_contactos WHERE clave = '$clave'";
          $rs = mysqli_query($conn, $sql);
          $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
          $contacto_existe = $resul['existe'];

            if($contacto_existe == 0 && $clave != '' && $nombre != '' && $apellido != '' && $correo != '')
            {
                $model->clave     = $clave;
                $model->nombre    = $nombre;
                $model->apellido  = $apellido;
                $model->correo    = $correo;
                $model->telefono1 = $telefono1;
                $model->telefono2 = $telefono2;
                $model->pais      = $pais;
                $model->estado    = $estado;
                $model->ciudad    = $ciudad;
                $model->direccion = $direccion;
                $model->save();
                $importados++;
            }
            $linea++;
        }
        
        @unlink($file);

        $this->response(200, [
            'debug' => $debug,
            'statusText' =>  "Contactos importados con exito. Total de Contactos: \"{$importados}\"",
        ]);exit;

    }

    /**
     * Undocumented function
     *
     * @param [type] $row
     * @return void
     */
    public function validarRequeridosImportar($row)
    {
        foreach ($this->camposRequeridos as $key => $campo)
        {
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
            'clave_cliente_proveedor',
            'razon_social',
            'almacen_id',
            'calle',
            'colonia',
            'ciudad',
            'estado',
            'postal',
            'contacto',
            'rfc',
            'telefono1',
            'telefono2',
            'condicion_pago',
            'proveedor_id',
            'tipo',
            'zona_venta',
            'activo'
        ];
/*
        $data_clientes = Clientes::get([
            'Cve_Clte',
            'Cve_CteProv',
            'RazonSocial',
            'Cve_Almacenp',
            'CalleNumero',
            'Colonia',
            'Ciudad',
            'Estado',
            'CodigoPostal',
            'Contacto',
            'RFC',
            'Telefono1',
            'Telefono2',
            'CondicionPago',
            'ID_Proveedor',
            'ClienteTipo',
            'ZonaVenta',
            'Activo'
        ]);
*/
        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        $clave_almacen = $_GET['almacen'];

    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);


        $sql = "SELECT id FROM c_almacenp WHERE clave = '$clave_almacen'";
        $rs = mysqli_query($conn, $sql);
        $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);
        $cve_almacen = $resul['id'];
/*
        $sql = "SELECT c.*, IFNULL(GROUP_CONCAT(DISTINCT r.cve_ruta), '') AS rutas
                FROM c_cliente c 
                LEFT JOIN c_almacenp al ON al.id = c.Cve_Almacenp
                LEFT JOIN c_destinatarios d ON d.Cve_Clte = c.Cve_Clte 
                LEFT JOIN RelClirutas rcr ON rcr.IdCliente = d.id_destinatario AND rcr.IdEmpresa = al.clave
                LEFT JOIN t_ruta r ON r.ID_Ruta = rcr.IdRuta
                WHERE c.Cve_Almacenp = '$cve_almacen' AND c.Activo = 1 
                GROUP BY c.id_cliente";
                */

        $sql = "SELECT DISTINCT c.*, IFNULL(GROUP_CONCAT(DISTINCT ruta.cve_ruta), '') AS rutas
      FROM c_cliente c  
        LEFT JOIN c_almacenp a ON a.id=c.Cve_Almacenp
        LEFT JOIN c_dane d ON  d.cod_municipio=c.CodigoPostal
        LEFT JOIN c_destinatarios dt ON dt.Cve_Clte = c.Cve_Clte AND dt.dir_principal = 1
        LEFT JOIN t_clientexruta r ON r.clave_cliente = dt.id_destinatario
        #LEFT JOIN t_clientexruta r ON r.clave_cliente = c.id_cliente
        LEFT JOIN c_tipocliente t ON t.Cve_TipoCte = c.ClienteTipo
        LEFT JOIN c_tipocliente2 t2 ON t2.Cve_TipoCte = c.ClienteTipo2 AND t2.id_tipocliente = t.id
        LEFT JOIN c_gpoclientes g ON g.cve_grupo = c.ClienteGrupo
        #LEFT JOIN c_destinatarios dt ON dt.Cve_Clte = c.Cve_Clte AND dt.dir_principal = 1
        LEFT JOIN t_ruta ruta ON ruta.ID_Ruta = r.clave_ruta
        LEFT JOIN RelCliLis rl ON rl.Id_Destinatario = dt.id_destinatario
        LEFT JOIN listap lp ON lp.id = rl.ListaP
        LEFT JOIN listad ld ON ld.id = rl.ListaD
        LEFT JOIN ListaPromoMaster lpr ON lpr.Id = rl.ListaPromo 

      WHERE c.Activo = '1' 
       AND c.Cve_Almacenp='$cve_almacen'    
       
       GROUP BY Cve_Clte
      ORDER BY c.RazonSocial ASC";
        $rs = mysqli_query($conn, $sql);
        //$data_clientes = mysqli_fetch_array($rs, MYSQLI_ASSOC);

        $filename = "clientes_".date('Ymd') . ".xls";

        ob_clean();
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        
        foreach($columnas as $column) 
        {            
            echo $column . "\t" ;            
        }
        print("\r\n");

        //foreach($data_clientes as $row)
        while($row = mysqli_fetch_array($rs, MYSQLI_ASSOC))
        {
            //if($row->Cve_Almacenp == $cve_almacen && $row->Activo == 1)
            //{
                echo $this->clear_column($row["Cve_Clte"]) . "\t";
                echo $this->clear_column($row["Cve_CteProv"]) . "\t";
                echo $this->clear_column($row["RazonSocial"]) . "\t";
                echo $this->clear_column($row["Cve_Almacenp"]) . "\t";
                echo $this->clear_column($row["CalleNumero"]) . "\t";
                echo $this->clear_column($row["Colonia"]) . "\t";
                echo $this->clear_column($row["Ciudad"]) . "\t";
                echo $this->clear_column($row["Estado"]) . "\t";
                echo $this->clear_column($row["CodigoPostal"]) . "\t";
                echo $this->clear_column($row["Contacto"]) . "\t";
                echo $this->clear_column($row["RFC"]) . "\t";
                echo $this->clear_column($row["Telefono1"]) . "\t";
                echo $this->clear_column($row["Telefono2"]) . "\t";
                echo $this->clear_column($row["CondicionPago"]) . "\t";
                echo $this->clear_column($row["ID_Proveedor"]) . "\t";
                echo $this->clear_column($row["ClienteTipo"]) . "\t";
                echo $this->clear_column($row["rutas"]) . "\t";
                echo $this->clear_column($row["Activo"]) . "\t";
                echo  "\r\n";
            //}
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
