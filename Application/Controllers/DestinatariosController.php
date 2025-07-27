<?php
namespace Application\Controllers;

use Framework\View\View;
use Framework\Helpers\Utils;
use Framework\Http\Response;
use Framework\Http\Controller;
use jakeroid\tools\SimpleXLSX;
use Application\Models\AlmacenP;
use Application\Models\Clientes;
use Application\Models\Usuarios;
use Application\Models\CodigoDane;
use Application\Models\Proveedores;
use Application\Models\Destinatarios;
use Application\Models\ClientesRutas;
use Application\Models\TiposDeCliente;
use Application\Models\VisitasClientes;
use Application\Models\ZonasDeAlmacenaje;
use Illuminate\Database\Capsule\Manager as Capsule;


/**
 * @version 1.0.0
 * @category Destinatarios
 * @author Brayan Rincon <brayan262@gemail.com>
 */
class DestinatariosController extends Controller
{
    const CLAVE = 0;
    const RAZONSOCIAL = 1;
    const DIRECCION = 2;
    const COLONIA = 3;
    const POSTAL = 4;
    const CIUDAD = 5;
    const ESTADO = 6;
    const CONTACTO = 7;
    const TELEFONO = 8;
    const CLAVE_DESTINATARIO = 9;
    const RUTA = 10;
    //const ACTIVO = 9;


    const CLAVE_RUTA_PV      =  0;
    const CLAVE_CLIENTE_PV   =  1;
    //const ID_DESTINATARIO_PV =  2;
    const CLAVE_VENDEDOR_PV  =  2;
    const LUNES_PV           =  3;
    const MARTES_PV          =  4;
    const MIERCOLES_PV       =  5;
    const JUEVES_PV          =  6;
    const VIERNES_PV         =  7;
    const SABADO_PV          =  8;
    const DOMINGO_PV         =  9;

    private $camposRequeridos = [
        self::CLAVE => 'Clave', 
        self::RAZONSOCIAL => 'Razón Social', 
        self::DIRECCION => 'Dirección',
    ];
    
    /**
     * Renderiza la vista general
     *
     * @return void
     */
    public function index()
    {
        $proveedores = Proveedores::get();
        $clientes = Clientes::where('Activo', '1')->get();
        $almacenes = AlmacenP::where('Activo', '1')->get();
        $zonas = ZonasDeAlmacenaje::get();
        $tipos_cliente = TiposDeCliente::get();
        $codigos_dane = CodigoDane::get();

        return new View('clientes.destinatarios', compact([
            'proveedores', 'clientes', 'almacenes', 'zonas', 'tipos_cliente', 'codigos_dane'
        ]));
    }


    /**
     * Undocumented function
     *
     * @return void
     */
    public function paginate()
    {
        $paginas  = $this->getInput('page');
        $counter  = $this->getInput('count');
        $page    = $this->getInput('page', $paginas); // get the requested page
        //$limit   = $this->getInput('rows', $counter); // get how many rows we want to have into the grid
        $limit = $counter;

        $sidx    = $this->getInput('sidx'); // get index row - i.e. user click to sort
        $sord    = $this->getInput('sord'); // get the direction
        $status  = $this->getInput('status');
        //$search  = $this->getInput('search');
        $codigo  = $this->getInput('codigo');
        $rutas  = $this->getInput('rutas');
        $dias  = $this->getInput('dias');
        $agentes  = $this->getInput('agentes');
        $criterio  = $this->getInput('criterio');
        $almacen  = $this->getInput('almacen');



        $ands ="";
        $ands2 ="";
        $cp_and = "";
        $cp_and2 = "";
        $and_agente = ""; 
        $and_vendedor = "";
        $and_vendedor_relday = "";

        if (!empty($criterio)){
           $criterio = trim($criterio);

           $ands .= " AND (d.RazonSocial LIKE '%{$criterio}%' OR d.Cve_Clte LIKE '%{$criterio}%' OR c.Cve_Clte LIKE '%{$criterio}%' OR d.clave_destinatario LIKE '%{$criterio}%' OR d.id_destinatario LIKE '%{$criterio}%' OR d.contacto LIKE '%{$criterio}%' OR d.direccion LIKE '%{$criterio}%' OR c.CalleNumero LIKE '%{$criterio}%') ";

           $ands2 .= " AND (des.RazonSocial LIKE '%{$criterio}%' OR des.Cve_Clte LIKE '%{$criterio}%' OR c.Cve_Clte LIKE '%{$criterio}%' OR des.clave_destinatario LIKE '%{$criterio}%' OR des.id_destinatario LIKE '%{$criterio}%' OR des.contacto LIKE '%{$criterio}%' OR des.direccion LIKE '%{$criterio}%') ";
        }

        if (!empty($codigo)) {
            $cp_and .= " AND d.postal = '{$codigo}' ";
            $cp_and2 .= " AND des.postal = '{$codigo}' ";
        }

        if (!empty($rutas)) {
            $ands .= " AND t_ruta.cve_ruta = '{$rutas}' ";
            //$ands2 .= " AND 0 ";
        }

        if (!empty($agentes)) {
            $and_agente = " AND d.id_destinatario IN (SELECT Id_destinatario FROM RelDayCli WHERE Cve_Vendedor = '{$agentes}') ";
            $and_vendedor = "AND ra.cve_vendedor = (SELECT id_user FROM c_usuario WHERE cve_usuario = '{$agentes}')";
            $and_vendedor_relday = "AND RelDayCli.Cve_Vendedor = (SELECT id_user FROM c_usuario WHERE cve_usuario = '{$agentes}')";
        }

        /*
        $data = Capsule::select( 
                Capsule::raw("SELECT 
                                IFNULL(d.id_destinatario, '--') AS id
                        FROM c_destinatarios d
                            LEFT JOIN c_cliente c ON d.Cve_Clte = c.Cve_Clte
                            LEFT JOIN t_clientexruta ON t_clientexruta.clave_cliente = d.id_destinatario
                            LEFT JOIN t_ruta ON t_ruta.ID_Ruta = t_clientexruta.clave_ruta
                            LEFT JOIN RelDayCli ON RelDayCli.Cve_Cliente = d.Cve_Clte AND t_ruta.cve_ruta = RelDayCli.Cve_Ruta AND d.id_destinatario = RelDayCli.Id_Destinatario $and_agente
                        WHERE d.Activo = '1' AND c.Cve_Almacenp = '{$almacen}' AND c.Cve_Clte = d.Cve_Clte $ands $cp_and
                        GROUP BY clave_cliente

                        UNION 

                        SELECT DISTINCT
                            IFNULL(des.id_destinatario, '--') AS id
                        FROM c_destinatarios des
                            LEFT JOIN c_cliente c ON des.Cve_Clte = c.Cve_Clte
                        WHERE des.Activo = '1' AND c.Cve_Almacenp = '{$almacen}' AND c.Cve_Clte = des.Cve_Clte 
                        AND des.id_destinatario NOT IN (SELECT DISTINCT clave_cliente FROM t_clientexruta)
                "
            )
        );

        $response = new \stdClass;
        $response->data = [];
        $count = count($data);
        */
        $order_by = "ruta DESC";//id AND
        $and_dias = "";
        $comparar_dias = ""; $contador_dias ="";
        if($dias != "''")
        {
            $and_dias = " AND RelDayCli.Cve_Cliente = d.Cve_Clte AND d.id_destinatario = RelDayCli.Id_Destinatario";
            $order_by = "CASE WHEN Secuencia = '' THEN 200000 END ASC, Secuencia*1 ASC"; //Secuencia*1 permite pasar a entero y que se organice por entero cómo números y no como varchar
              if($dias == "IFNULL(RelDayCli.Lu, 20000)") 
                {
                    $comparar_dias = " AND IFNULL(RelDayCli.Lu, 20000) = RelDayCli.Lu"; 
                    $contador_dias = "AND IFNULL(RelDayCli.Lu, 20000) != 20000";
                }
              if($dias == "IFNULL(RelDayCli.Ma, 20000)") 
                {
                    $comparar_dias = " AND IFNULL(RelDayCli.Ma, 20000) = RelDayCli.Ma"; 
                    $contador_dias = "AND IFNULL(RelDayCli.Ma, 20000) != 20000";
                }
              if($dias == "IFNULL(RelDayCli.Mi, 20000)") 
                {
                    $comparar_dias = " AND IFNULL(RelDayCli.Mi, 20000) = RelDayCli.Mi"; 
                    $contador_dias = "AND IFNULL(RelDayCli.Mi, 20000) != 20000";
                }
              if($dias == "IFNULL(RelDayCli.Ju, 20000)") 
                {
                    $comparar_dias = " AND IFNULL(RelDayCli.Ju, 20000) = RelDayCli.Ju"; 
                    $contador_dias = "AND IFNULL(RelDayCli.Ju, 20000) != 20000";
                }
              if($dias == "IFNULL(RelDayCli.Vi, 20000)") 
                {
                    $comparar_dias = " AND IFNULL(RelDayCli.Vi, 20000) = RelDayCli.Vi"; 
                    $contador_dias = "AND IFNULL(RelDayCli.Vi, 20000) != 20000";
                }
              if($dias == "IFNULL(RelDayCli.Sa, 20000)") 
                {
                    $comparar_dias = " AND IFNULL(RelDayCli.Sa, 20000) = RelDayCli.Sa"; 
                    $contador_dias = "AND IFNULL(RelDayCli.Sa, 20000) != 20000";
                }
              if($dias == "IFNULL(RelDayCli.Do, 20000)") 
                {
                    $comparar_dias = " AND IFNULL(RelDayCli.Do, 20000) = RelDayCli.Do"; 
                    $contador_dias = "AND IFNULL(RelDayCli.Do, 20000) != 20000";
                }

        }

        $and_select2 = "";
        if($and_dias || $and_vendedor_relday || $comparar_dias || $and_vendedor /*|| $ands*/)
        {
            //$and_select2 = "AND c.cve_ruta = '00xxyy77'"; // esto es para que cuando hay algún filtro, no busque en el select 2 de la union 
        }

        $sql = "SELECT DISTINCT
                                IFNULL(d.id_destinatario, '--') AS id,
                                $dias AS Secuencia,
                                IFNULL(c.Cve_Clte, '__') AS clave_cliente,
                                IFNULL(c.RazonSocial, '--') AS cliente, 
                                IFNULL(c.RazonComercial, '--') AS razoncomercial, 
                                IFNULL(d.razonsocial, '--') AS destinatario,
                                GROUP_CONCAT(DISTINCT IFNULL(t_ruta.cve_ruta,'--') SEPARATOR ', ') AS ruta,
                                #IFNULL(d.id_destinatario, '--') AS clave_destinatario,
                                #IFNULL(d.clave_destinatario, '--') AS clave_sucursal,
                                #IFNULL(d.id_destinatario, '--') AS clave_destinatario,
                                #IFNULL(d.id_destinatario, '--') AS clave_destinatario,
                                IF(IFNULL(d.clave_destinatario, '') = '', d.id_destinatario, d.clave_destinatario) AS clave_destinatario,
                                IF(ra.cve_vendedor != '', GROUP_CONCAT(DISTINCT u.nombre_completo SEPARATOR ', '), '') AS Agente,
                                IFNULL(d.direccion, '--') AS direccion,
                                IFNULL(d.colonia, '--') AS colonia,
                                IFNULL(d.postal, '--') AS postal,
                                IFNULL(d.ciudad, '--') AS ciudad,
                                IFNULL(d.estado, '--') AS estado,
                                IF(d.dir_principal = 1, IFNULL(c.latitud, '--'), IFNULL(d.latitud, '--')) AS latitud,
                                IF(d.dir_principal = 1, IFNULL(c.longitud, '--'), IFNULL(d.longitud, '--')) AS longitud,
                                ##SE QUITÓ LATITUD Y LONGITUD PORQUE GENERA UN ERROR DE Illegal mix of collations for operation 'UNION'
                                #'' AS latitud,
                                #'' AS longitud,
                                IFNULL(d.contacto, '--') AS contacto,
                                IFNULL(d.telefono, '--') AS telefono
                        FROM c_destinatarios d
                            LEFT JOIN c_cliente c ON d.Cve_Clte = c.Cve_Clte
                            LEFT JOIN t_clientexruta ON t_clientexruta.clave_cliente = d.id_destinatario
                            LEFT JOIN t_ruta ON t_ruta.ID_Ruta = t_clientexruta.clave_ruta
                            LEFT JOIN RelDayCli ON t_ruta.ID_Ruta = RelDayCli.Cve_Ruta $and_dias $and_vendedor_relday $comparar_dias
                            LEFT JOIN Rel_Ruta_Agentes ra ON ra.cve_ruta = t_ruta.ID_Ruta $and_vendedor
                            LEFT JOIN c_usuario u ON u.id_user = ra.cve_vendedor
                            LEFT JOIN c_dane cp ON cp.cod_municipio = d.postal
                        WHERE d.Activo = '1' AND c.Cve_Almacenp = '{$almacen}' AND c.Cve_Clte = d.Cve_Clte AND t_ruta.cve_almacenp = '{$almacen}' $ands $cp_and
                        GROUP BY clave_cliente

                        UNION 

                        SELECT DISTINCT
                            IFNULL(des.id_destinatario, '--') AS id,
                            '' AS Secuencia,
                            IFNULL(c.Cve_Clte, '__') AS clave_cliente,
                            IFNULL(c.RazonSocial, '--') AS cliente, 
                            IFNULL(c.RazonComercial, '--') AS razoncomercial, 
                            IFNULL(des.razonsocial, '--') AS destinatario,
                            '--' AS ruta,
                            #IFNULL(d.id_destinatario, '--') AS clave_destinatario,
                            #IFNULL(d.clave_destinatario, '--') AS clave_sucursal,
                            #IFNULL(d.id_destinatario, '--') AS clave_destinatario,
                            IF(IFNULL(des.clave_destinatario, '') = '', des.id_destinatario, des.clave_destinatario) AS clave_destinatario,
                            '' AS Agente,
                            IFNULL(des.direccion, '--') AS direccion,
                            IFNULL(des.colonia, '--') AS colonia,
                            IFNULL(des.postal, '--') AS postal,
                            IFNULL(des.ciudad, '--') AS ciudad,
                            IFNULL(des.estado, '--') AS estado,
                            IF(des.dir_principal = 1, IFNULL(c.latitud, '--'), IFNULL(des.latitud, '--')) AS latitud,
                            IF(des.dir_principal = 1, IFNULL(c.longitud, '--'), IFNULL(des.longitud, '--')) AS longitud,
                            ##SE QUITÓ LATITUD Y LONGITUD PORQUE GENERA UN ERROR DE Illegal mix of collations for operation 'UNION'
                            #'' AS latitud,
                            #'' AS longitud,
                            IFNULL(des.contacto, '--') AS contacto,
                            IFNULL(des.telefono, '--') AS telefono
                        FROM c_destinatarios des
                            LEFT JOIN c_cliente c ON des.Cve_Clte = c.Cve_Clte 
                            LEFT JOIN c_dane cp ON cp.cod_municipio = des.postal
                        WHERE des.Activo = '1' AND c.Cve_Almacenp = '{$almacen}' AND c.Cve_Clte = des.Cve_Clte $and_select2 $cp_and2
                        AND des.id_destinatario NOT IN (SELECT DISTINCT clave_cliente FROM t_clientexruta) $ands2
                        ORDER BY $order_by 
                ";

/*
        $data = Capsule::select( 
                Capsule::raw($sql)
        );

        $response = new \stdClass;
        $response->data = [];
        $count = count($data);

        $sql_tabla = $sql;
        $sql_tabla .= " LIMIT {$start}, {$limit} ";

        $data = Capsule::select( 
                Capsule::raw($sql_tabla)
        );
*/
            //$count = 0;
            $res = "";
            $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $chs = "charset";
    if(strpos($_SERVER['HTTP_HOST'], 'sctp') == true)
    {
        $chs = "charset2";
    }

$sql_charset = "SELECT DISTINCT IFNULL(Valor, 'latin1') as charset FROM t_configuraciongeneral WHERE cve_conf = '$chs'";
if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
$charset = mysqli_fetch_array($res_charset)['charset'];
mysqli_set_charset($conn , $charset);

            //mysqli_set_charset($conn,"utf8");
            if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
            $count = mysqli_num_rows($res); 

            $start = 0;
            $start = $limit * $page - $limit; // do not put $limit*($page - 1)
             //if (intval($page)>0) $start = ($page-1)*$limit;//

            if ($count >0) {
                $total_pages = ceil($count/$limit);
            }
            else {
                $total_pages = 0;
            }
            
            if ($page > $total) {
                $page = $total_pages;
            }


            $sql .= " LIMIT {$start}, {$limit} ";
            $sql_tabla = $sql;
            if (!($res = mysqli_query($conn, $sql_tabla))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
            $data = array();
            //$data = mysqli_fetch_array($res);

        $i = 0;
        //foreach ($data as $row) {
        while ($row = mysqli_fetch_assoc($res)){
/*
            if((strpos($_SERVER['HTTP_HOST'], 'rfs')) || (strpos($_SERVER['HTTP_HOST'], 'rie')))
            {
                $response->data[] = [
                    'id' => utf8_encode($row['id']),
                    'Secuencia' => utf8_encode($row['Secuencia']),
                    'clave_cliente' => utf8_encode($row['clave_cliente']),
                    'cliente' => utf8_encode($row['cliente']),
                    'ruta' => utf8_encode($row['ruta']),
                    'destinatario' => utf8_encode($row['destinatario']),
                    'Agente' => utf8_encode($row['Agente']),
                    'clave_destinatario' => utf8_encode($row['clave_destinatario']),
                    'clave_sucursal' => utf8_encode($row['clave_sucursal']),
                    'direccion' => utf8_encode($row['direccion']),
                    'colonia' => utf8_encode($row['colonia']),
                    'postal' => utf8_encode($row['postal']),
                    'ciudad' => utf8_encode($row['ciudad']),
                    'estado' => utf8_encode($row['estado']),
                    'latitud' => utf8_encode($row['latitud']),
                    'longitud' => utf8_encode($row['longitud']),
                    'contacto' => utf8_encode($row['contacto']),
                    'telefono' => utf8_encode($row['telefono'])
                ];
            }
*/
            //else
            //{

if(utf8_encode($row['id'])) $row['id'] = utf8_encode($row['id']); else $row['id'] = utf8_decode($row['id']);
if(utf8_encode($row['Secuencia'])) $row['Secuencia'] = utf8_encode($row['Secuencia']); else $row['Secuencia'] = utf8_decode($row['Secuencia']);
if(utf8_encode($row['clave_cliente'])) $row['clave_cliente'] = utf8_encode($row['clave_cliente']); else $row['clave_cliente'] = utf8_decode($row['clave_cliente']);
if(utf8_encode($row['cliente'])) $row['cliente'] = utf8_encode($row['cliente']); else $row['cliente'] = utf8_decode($row['cliente']);
if(utf8_encode($row['razoncomercial'])) $row['razoncomercial'] = utf8_encode($row['razoncomercial']); else $row['razoncomercial'] = utf8_decode($row['razoncomercial']);
if(utf8_encode($row['ruta'])) $row['ruta'] = utf8_encode($row['ruta']); else $row['ruta'] = utf8_decode($row['ruta']);
if(utf8_encode($row['destinatario'])) $row['destinatario'] = utf8_encode($row['destinatario']); else $row['destinatario'] = utf8_decode($row['destinatario']);
if(utf8_encode($row['Agente'])) $row['Agente'] = utf8_encode($row['Agente']); else $row['Agente'] = utf8_decode($row['Agente']);
if(utf8_encode($row['clave_destinatario'])) $row['clave_destinatario'] = utf8_encode($row['clave_destinatario']); else $row['clave_destinatario'] = utf8_decode($row['clave_destinatario']);
if(utf8_encode($row['clave_sucursal'])) $row['clave_sucursal'] = utf8_encode($row['clave_sucursal']); else $row['clave_sucursal'] = utf8_decode($row['clave_sucursal']);
if(utf8_encode($row['direccion'])) $row['direccion'] = utf8_encode($row['direccion']); else $row['direccion'] = utf8_decode($row['direccion']);
if(utf8_encode($row['colonia'])) $row['colonia'] = utf8_encode($row['colonia']); else $row['colonia'] = utf8_decode($row['colonia']);
if(utf8_encode($row['postal'])) $row['postal'] = utf8_encode($row['postal']); else $row['postal'] = utf8_decode($row['postal']);
if(utf8_encode($row['ciudad'])) $row['ciudad'] = utf8_encode($row['ciudad']); else $row['ciudad'] = utf8_decode($row['ciudad']);
if(utf8_encode($row['estado'])) $row['estado'] = utf8_encode($row['estado']); else $row['estado'] = utf8_decode($row['estado']);
if(utf8_encode($row['latitud'])) $row['latitud'] = utf8_encode($row['latitud']); else $row['latitud'] = utf8_decode($row['latitud']);
if(utf8_encode($row['longitud'])) $row['longitud'] = utf8_encode($row['longitud']); else $row['longitud'] = utf8_decode($row['longitud']);
if(utf8_encode($row['contacto'])) $row['contacto'] = utf8_encode($row['contacto']); else $row['contacto'] = utf8_decode($row['contacto']);
if(utf8_encode($row['telefono'])) $row['telefono'] = utf8_encode($row['telefono']); else $row['telefono'] = utf8_decode($row['telefono']);

    if(strpos($_SERVER['HTTP_HOST'], 'sctp') == true)
    {
        $row['cliente'] = utf8_decode($row['cliente']);
        $row['razoncomercial'] = utf8_decode($row['razoncomercial']);
        $row['Agente'] = utf8_decode($row['Agente']);
        $row['direccion'] = utf8_decode($row['direccion']);
        $row['colonia'] = utf8_decode($row['colonia']);
        $row['ciudad'] = utf8_decode($row['ciudad']);
        $row['estado'] = utf8_decode($row['estado']);
        $row['contacto'] = utf8_decode($row['contacto']);
        $row['destinatario'] = utf8_decode($row['destinatario']);
    }

                $response->data[] = [
                    'id' => ($row['id']),
                    'Secuencia' => ($row['Secuencia']),
                    'clave_cliente' => ($row['clave_cliente']),
                    'cliente' => ($row['cliente']),
                    'razoncomercial' => ($row['razoncomercial']),
                    'ruta' => ($row['ruta']),
                    'destinatario' => ($row['destinatario']),
                    'Agente' => ($row['Agente']),
                    'clave_destinatario' => ($row['clave_destinatario']),
                    'clave_sucursal' => ($row['clave_sucursal']),
                    'direccion' => ($row['direccion']),
                    'colonia' => ($row['colonia']),
                    'postal' => ($row['postal']),
                    'ciudad' => ($row['ciudad']),
                    'estado' => ($row['estado']),
                    'latitud' => ($row['latitud']),
                    'longitud' => ($row['longitud']),
                    'contacto' => ($row['contacto']),
                    'telefono' => ($row['telefono'])
                ];

/*
                $response->data[] = [
                    'id' => utf8_decode($row['id']),
                    'Secuencia' => utf8_decode($row['Secuencia']),
                    'clave_cliente' => utf8_decode($row['clave_cliente']),
                    'cliente' => utf8_decode($row['cliente']),
                    'ruta' => utf8_decode($row['ruta']),
                    'destinatario' => utf8_decode($row['destinatario']),
                    'Agente' => utf8_decode($row['Agente']),
                    'clave_destinatario' => utf8_decode($row['clave_destinatario']),
                    'clave_sucursal' => utf8_decode($row['clave_sucursal']),
                    'direccion' => utf8_decode($row['direccion']),
                    'colonia' => utf8_decode($row['colonia']),
                    'postal' => utf8_decode($row['postal']),
                    'ciudad' => utf8_decode($row['ciudad']),
                    'estado' => utf8_decode($row['estado']),
                    'latitud' => utf8_decode($row['latitud']),
                    'longitud' => utf8_decode($row['longitud']),
                    'contacto' => utf8_decode($row['contacto']),
                    'telefono' => utf8_decode($row['telefono'])
                ];
*/
            //}
            $i++;
        }

       // $response->data = array_slice($response->data, $start, $limit);
        //$response->data = array_slice($data, $start, $limit);

//*********************************************************************************

        $sql_select = "
                    SELECT DISTINCT * FROM (
                        SELECT DISTINCT
                                cp.cod_municipio,
                                cp.des_municipio,
                                cp.departamento
                        FROM c_destinatarios d
                            LEFT JOIN c_cliente c ON d.Cve_Clte = c.Cve_Clte
                            LEFT JOIN t_clientexruta ON t_clientexruta.clave_cliente = d.id_destinatario
                            LEFT JOIN t_ruta ON t_ruta.ID_Ruta = t_clientexruta.clave_ruta
                            LEFT JOIN RelDayCli ON t_ruta.cve_ruta = RelDayCli.Cve_Ruta $and_dias $and_vendedor_relday $comparar_dias
                            LEFT JOIN Rel_Ruta_Agentes ra ON ra.cve_ruta = t_ruta.ID_Ruta $and_vendedor
                            LEFT JOIN c_dane cp ON cp.cod_municipio = d.postal
                        WHERE d.Activo = '1' AND c.Cve_Almacenp = '{$almacen}' AND c.Cve_Clte = d.Cve_Clte $ands
                        GROUP BY clave_cliente

                        UNION 

                        SELECT DISTINCT
                            cp.cod_municipio,
                            cp.des_municipio,
                            cp.departamento
                        FROM c_destinatarios des
                            LEFT JOIN c_cliente c ON des.Cve_Clte = c.Cve_Clte 
                            LEFT JOIN c_dane cp ON cp.cod_municipio = des.postal
                        WHERE des.Activo = '1' AND c.Cve_Almacenp = '{$almacen}' AND c.Cve_Clte = des.Cve_Clte $and_select2
                        AND des.id_destinatario NOT IN (SELECT DISTINCT clave_cliente FROM t_clientexruta) $ands2
                        ORDER BY cod_municipio
                    ) AS select_codigo WHERE select_codigo.cod_municipio != ''
                ";

        $data_select = Capsule::select( 
                Capsule::raw($sql_select)
        );

        if(!empty($response->data_select))
            $response->data_select = [];
  
        $select_codigo_postal = "<option value=''>Seleccione Código Postal</option>";
        foreach ($data_select as $row) {

            $cod = $row->cod_municipio;
                if(strlen($cod) == 4)
                    $cod = "0".$cod;
            $select_codigo_postal.= "<option value='".$cod."'>".$cod." - ".($row->des_municipio)." | ".($row->departamento)."</option>";
            //if((strpos($_SERVER['HTTP_HOST'], 'rfs')) || (strpos($_SERVER['HTTP_HOST'], 'rie')))
                //$select_codigo_postal.= "<option value='".$cod."'>".$cod." - ".utf8_encode($row->des_municipio)." | ".utf8_encode($row->departamento)."</option>";
            //else
                //$select_codigo_postal.= "<option value='".$cod."'>".$cod." - ".utf8_decode($row->des_municipio)." | ".utf8_decode($row->departamento)."</option>";
        }


//*********************************************************************************
        //$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

        $clientesxruta = 0;
        if (!empty($rutas)) 
        { 
            $sql3 = "SELECT DISTINCT
                      COUNT(DISTINCT d.id_destinatario) AS clientesxruta
                    FROM c_destinatarios d
                        LEFT JOIN c_cliente c ON d.Cve_Clte = c.Cve_Clte
                        LEFT JOIN t_clientexruta ON t_clientexruta.clave_cliente = d.id_destinatario
                        LEFT JOIN t_ruta ON t_ruta.ID_Ruta = t_clientexruta.clave_ruta
                        LEFT JOIN RelDayCli ON t_ruta.cve_ruta = RelDayCli.Cve_Ruta 
                    WHERE d.Activo = '1' AND c.Cve_Almacenp = '{$almacen}' AND c.Cve_Clte = d.Cve_Clte AND t_ruta.cve_ruta = '{$rutas}' 
                    ";
            if (!($res3 = mysqli_query($conn, $sql3))) {
                echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
            }
            $row = mysqli_fetch_array($res3);
            $clientesxruta = $row['clientesxruta'];
        }

        $clientesxdia = 0;
        $sql3_clientesxdia = "";
        if ($dias != "''") 
        { 
            $sql3 = "SELECT DISTINCT
                            IFNULL(d.id_destinatario, '--') AS id
                        FROM c_destinatarios d
                            LEFT JOIN c_cliente c ON d.Cve_Clte = c.Cve_Clte
                            LEFT JOIN t_clientexruta ON t_clientexruta.clave_cliente = d.id_destinatario
                            LEFT JOIN t_ruta ON t_ruta.ID_Ruta = t_clientexruta.clave_ruta
                            LEFT JOIN RelDayCli ON t_ruta.ID_Ruta = RelDayCli.Cve_Ruta  $and_dias $and_vendedor_relday $comparar_dias 
                            LEFT JOIN Rel_Ruta_Agentes ra ON ra.cve_ruta = t_ruta.ID_Ruta $and_vendedor
                        WHERE d.Activo = '1' AND c.Cve_Almacenp = '{$almacen}' AND c.Cve_Clte = d.Cve_Clte  AND t_ruta.cve_ruta = '{$rutas}' $contador_dias
                        GROUP BY clave_cliente";
            if (!($res3 = mysqli_query($conn, $sql3))) {
                echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
            }
            //$row = mysqli_fetch_array($res3);
            //$clientesxdia = $row['clientesxdia'];
            $clientesxdia = mysqli_num_rows($res3);
            $sql3_clientesxdia = $sql3;
        }

//*********************************************************************************

        if(!empty($response->from)) $response->from = ($start == 0 ? 1 : $start) ; else $response->from = ($start == 0 ? 1 : $start) ;
        if(!empty($response->to)) $response->to = ($start + $limit); else $response->to = ($start + $limit);
        if(!empty($response->page)) $response->page = $page; else $response->page = $page;
        if(!empty($response->total_pages)) $response->total_pages = $total_pages; else $response->total_pages = $total_pages;
        if(!empty($response->total)) $response->total = $count; else $response->total = $count;
        //$response->records = $count;
        //$response->total = $total_pages;
        if(!empty($response->status)) $response->status = 200; else $response->status = 200;
        if(!empty($response->sql)) $response->sql = $sql_tabla; else $response->sql = $sql_tabla;
        if(!empty($response->select_codigo_postal)) $response->select_codigo_postal = $select_codigo_postal; else $response->select_codigo_postal = $select_codigo_postal;
        if(!empty($response->codigo_postal)) $response->codigo_postal = $codigo; else $response->codigo_postal = $codigo;
        if(!empty($response->clientes_por_ruta)) $response->clientes_por_ruta = $clientesxruta; else $response->clientes_por_ruta = $clientesxruta;
        if(!empty($response->clientes_por_dia)) $response->clientes_por_dia = $clientesxdia; else $response->clientes_por_dia = $clientesxdia;
        $response->sql3_clientesxdia = $sql3_clientesxdia;
/*
        $response['from'] = ($start == 0 ? 1 : $start) ;
        $response['to'] = ($start + $limit);
        $response['page'] = $page;
        $response['total_pages'] = $total_pages;
        $response['total'] = $count;
        //$response['records'] = $count;
        //$response['total'] = $total_pages;
        $response['status'] = 200;
        //$response['sql'] = $sql_tabla;
        $response['select_codigo_postal'] = $select_codigo_postal;
        $response['codigo_postal'] = $codigo;
        $response['clientes_por_ruta'] = $clientesxruta;
        $response['clientes_por_dia'] = $clientesxdia;
*/
        ob_clean();
        //header('Content-Type: application/json; charset=utf-8');
        header('Content-Type: application/json; charset=$charset');
        echo json_encode($response,JSON_PRETTY_PRINT);exit;	
        //echo json_encode($response);
    }

    /**
     * Undocumented function
     *
     * @return void
     */
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

      if ( $_FILES['file']['type'] != 'application/vnd.ms-excel' AND
      $_FILES['file']['type'] != 'application/msexcel' AND
      $_FILES['file']['type'] != 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' AND
      $_FILES['file']['type'] != 'application/xls' )
      {
        @unlink($file);
        $this->response(400, ['statusText' =>  "Error en el formato del fichero",]);
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
        if( $eval === TRUE )
        {
        }
        else 
        {
          $this->response(400, ['statusText' =>  "La columna \"{$eval}\" de la Fila Nro. {$linea} está vacía. Corrija esto para continuar con la importación.",]);
        }
        $linea++;
      }
*/
      $linea = 1;
      foreach ($xlsx->rows() as $row)
      {
        if($linea == 1) 
        {
          $linea++;continue;
        }
        $clave = $this->pSQL($row[self::CLAVE]);
        $razonsocial = $this->pSQL($row[self::RAZONSOCIAL]);
        $direccion = $this->pSQL($row[self::DIRECCION]);
        $element = Destinatarios::where('Cve_Clte', $clave)->where('razonsocial', $razonsocial)->where('direccion', $direccion)->first();
        if($element != NULL)
        {
          $model = $element; 
        }
        else 
        {
          $model = new Destinatarios(); 
        }
        $model->Cve_Clte            = $this->pSQL($row[self::CLAVE]);
        $model->razonsocial         = $this->pSQL($row[self::RAZONSOCIAL]);
        $model->direccion           = $this->pSQL($row[self::DIRECCION]);
        $model->colonia             = $this->pSQL($row[self::COLONIA]);
        $model->postal              = $this->pSQL($row[self::POSTAL]);
        $model->ciudad              = $this->pSQL($row[self::CIUDAD]);
        $model->estado              = $this->pSQL($row[self::ESTADO]);
        $model->contacto            = $this->pSQL($row[self::CONTACTO]);
        $model->telefono            = $this->pSQL($row[self::TELEFONO]);
        $model->clave_destinatario  = $this->pSQL($row[self::CLAVE_DESTINATARIO]);
        $model->save();

        $ruta =  $this->pSQL($row[self::RUTA]);
        if($ruta !== NULL)
        {
          $clave = $this->pSQL($row[self::CLAVE]);
          $elementRuta = ClientesRutas::where('clave_cliente', $clave)->where('clave_ruta',$ruta)->first();
          if($elementRuta == NULL)
          {
            $modelRuta = new ClientesRutas();
            $modelRuta->clave_cliente  = $clave;
            $modelRuta->clave_ruta     = $ruta;
            $modelRuta->save();
          }
        }
        $linea++;
      }
      @unlink($file);
      $this->response(200, [
        'debug' => $debug,
        'statusText' =>  "Destinatarios importados con exito. Total de Destinatarios: \"{$linea}\"",
      ]);
    }



    public function importarpv()
    {
      $dir_cache = PATH_APP . 'Cache/';
      $file = $dir_cache . basename($_FILES['file']['name']);
      $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

      if (! move_uploaded_file($_FILES['file']['tmp_name'], $file)) 
      {
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
        $this->response(400, ['statusText' =>  "Error en el formato del fichero",]);
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
        if( $eval === TRUE )
        {
        }
        else 
        {
          $this->response(400, ['statusText' =>  "La columna \"{$eval}\" de la Fila Nro. {$linea} está vacía. Corrija esto para continuar con la importación.",]);
        }
        $linea++;
      }
*/
      $linea = 1;
      $cve_almac = $_POST['cve_almacen'];
      foreach ($xlsx->rows() as $row)
      {
        if($linea == 1) 
        {
          $linea++;continue;
        }

        $model = new VisitasClientes(); 

        if($this->pSQL($row[self::CLAVE_RUTA_PV]))
        {
            $Cve_Ruta = $this->pSQL($row[self::CLAVE_RUTA_PV]);
            $Cve_Cliente = $this->pSQL($row[self::CLAVE_CLIENTE_PV]);
            //$Id_Destinatario = $this->pSQL($row[self::ID_DESTINATARIO_PV]);
            $Cve_Vendedor = $this->pSQL($row[self::CLAVE_VENDEDOR_PV]);
            $lunes = $this->pSQL($row[self::LUNES_PV]);
            $martes = $this->pSQL($row[self::MARTES_PV]);
            $miercoles = $this->pSQL($row[self::MIERCOLES_PV]);
            $jueves = $this->pSQL($row[self::JUEVES_PV]);
            $viernes = $this->pSQL($row[self::VIERNES_PV]);
            $sabado = $this->pSQL($row[self::SABADO_PV]);
            $domingo = $this->pSQL($row[self::DOMINGO_PV]);

            if(!$lunes) $lunes = 0; //$lunes = 'NULL';
            if(!$martes) $martes = 0; //$martes = 'NULL';
            if(!$miercoles) $miercoles = 0; //$miercoles = 'NULL';
            if(!$jueves) $jueves = 0; //$jueves = 'NULL';
            if(!$viernes) $viernes = 0; //$viernes = 'NULL';
            if(!$sabado) $sabado = 0; //$sabado = 'NULL';
            if(!$domingo) $domingo = 0; //$domingo = 'NULL';
/*
            $sql = "
            SELECT DISTINCT
                COUNT(*) AS existe
            FROM c_destinatarios d
                LEFT JOIN c_cliente c ON d.Cve_Clte = c.Cve_Clte
                LEFT JOIN t_clientexruta ON t_clientexruta.clave_cliente = d.id_destinatario
                LEFT JOIN t_ruta ON t_ruta.ID_Ruta = t_clientexruta.clave_ruta
                LEFT JOIN RelDayCli ON RelDayCli.Cve_Cliente = d.Cve_Clte AND t_ruta.cve_ruta = RelDayCli.Cve_Ruta
                LEFT JOIN Rel_Ruta_Agentes ra ON ra.cve_ruta = t_ruta.ID_Ruta
            WHERE d.Activo = '1' AND c.Cve_Clte = d.Cve_Clte 
            AND t_ruta.cve_ruta = '{$Cve_Ruta}' AND d.id_destinatario = '{$Id_Destinatario}' AND d.Cve_Clte = '{$Cve_Cliente}' AND ra.cve_vendedor = '{$Cve_Vendedor}'
            GROUP BY clave_cliente";

            if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
            $fila = mysqli_fetch_array($res);

            $existe = $fila['existe'];
            if($existe == "") $existe = 0;
*/

            $sql = "SELECT ID_Ruta FROM t_ruta WHERE cve_ruta = '{$Cve_Ruta}'";
            if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
            $fila = mysqli_fetch_array($res);
            $id_ruta = $fila['ID_Ruta'];

            $sql = "SELECT id_user FROM c_usuario WHERE cve_usuario = '{$Cve_Vendedor}'";
            if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
            $fila = mysqli_fetch_array($res);
            $id_vendedor = $fila['id_user'];

            $sql = "SELECT COUNT(*) as vendedor_existe FROM Rel_Ruta_Agentes WHERE cve_ruta = '{$id_ruta}'";
            if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
            $fila = mysqli_fetch_array($res);
            $vendedor_existe = $fila['vendedor_existe'];

            $sql = "SELECT COUNT(*) as cliente_existe FROM c_cliente WHERE Cve_Clte = '{$Cve_Cliente}'";
            if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
            $fila = mysqli_fetch_array($res);
            $cliente_existe = $fila['cliente_existe'];

            $sql = "SELECT id_destinatario FROM c_destinatarios WHERE Cve_Clte = '{$Cve_Cliente}' LIMIT 1";
            if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
            $fila = mysqli_fetch_array($res);
            $Id_Destinatario = $fila['id_destinatario'];

            $sql = "SELECT COUNT(*) as destinatario_existe FROM c_destinatarios WHERE id_destinatario = '{$Id_Destinatario}'";
            if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
            $fila = mysqli_fetch_array($res);
            $destinatario_existe = $fila['destinatario_existe'];

            $sql = "SELECT COUNT(*) as existeRDC FROM RelDayCli WHERE Cve_Ruta = '{$id_ruta}' AND Cve_Cliente = '{$Cve_Cliente}' AND Id_Destinatario = '{$Id_Destinatario}' AND Cve_Vendedor = '{$id_vendedor}'";
            if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
            $fila = mysqli_fetch_array($res);
            $existeRDC = $fila['existeRDC'];

            $sql = "SELECT COUNT(*) as existetcxr FROM t_clientexruta WHERE clave_ruta = '{$id_ruta}' AND clave_cliente = '{$Cve_Cliente}'";
            if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
            $fila = mysqli_fetch_array($res);
            $existetcxr = $fila['existetcxr'];

            if($id_ruta && $id_vendedor && $destinatario_existe && $cliente_existe && $existeRDC)
            {
                //$sql = "UPDATE RelDayCli SET Lu = '{$lunes}', Ma = '{$martes}', Mi = '{$miercoles}', Ju = '{$jueves}', Vi = '{$viernes}', Sa = '{$sabado}', Do = '{$domingo}' WHERE Cve_Ruta = '{$id_ruta}' AND Cve_Cliente = '{$Cve_Cliente}' AND Id_Destinatario = '{$Id_Destinatario}' AND Cve_Vendedor = '{$id_vendedor}'";
                //if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}

                if($lunes)
                {
                    $sql = "UPDATE RelDayCli SET Lu = IFNULL(Lu, 0)+'{$lunes}' WHERE Cve_Ruta = '{$id_ruta}' AND Cve_Cliente = '{$Cve_Cliente}' AND Id_Destinatario = '{$Id_Destinatario}' AND Cve_Vendedor = '{$id_vendedor}'";
                    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
                }

                if($martes)
                {
                    $sql = "UPDATE RelDayCli SET Ma = IFNULL(Ma, 0)+'{$martes}' WHERE Cve_Ruta = '{$id_ruta}' AND Cve_Cliente = '{$Cve_Cliente}' AND Id_Destinatario = '{$Id_Destinatario}' AND Cve_Vendedor = '{$id_vendedor}'";
                    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
                }

                if($miercoles)
                {
                    $sql = "UPDATE RelDayCli SET Mi = IFNULL(Mi, 0)+'{$miercoles}' WHERE Cve_Ruta = '{$id_ruta}' AND Cve_Cliente = '{$Cve_Cliente}' AND Id_Destinatario = '{$Id_Destinatario}' AND Cve_Vendedor = '{$id_vendedor}'";
                    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
                }

                if($jueves)
                {
                    $sql = "UPDATE RelDayCli SET Ju = IFNULL(Ju, 0)+'{$jueves}' WHERE Cve_Ruta = '{$id_ruta}' AND Cve_Cliente = '{$Cve_Cliente}' AND Id_Destinatario = '{$Id_Destinatario}' AND Cve_Vendedor = '{$id_vendedor}'";
                    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
                }

                if($viernes)
                {
                    $sql = "UPDATE RelDayCli SET Vi = IFNULL(Vi, 0)+'{$viernes}' WHERE Cve_Ruta = '{$id_ruta}' AND Cve_Cliente = '{$Cve_Cliente}' AND Id_Destinatario = '{$Id_Destinatario}' AND Cve_Vendedor = '{$id_vendedor}'";
                    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
                }

                if($sabado)
                {
                    $sql = "UPDATE RelDayCli SET Sa = IFNULL(Sa, 0)+'{$sabado}' WHERE Cve_Ruta = '{$id_ruta}' AND Cve_Cliente = '{$Cve_Cliente}' AND Id_Destinatario = '{$Id_Destinatario}' AND Cve_Vendedor = '{$id_vendedor}'";
                    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
                }

                if($domingo)
                {
                    $sql = "UPDATE RelDayCli SET Do = IFNULL(Do, 0)+'{$domingo}' WHERE Cve_Ruta = '{$id_ruta}' AND Cve_Cliente = '{$Cve_Cliente}' AND Id_Destinatario = '{$Id_Destinatario}' AND Cve_Vendedor = '{$id_vendedor}'";
                    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
                }
                if($vendedor_existe)
                {
                    $sql = "UPDATE Rel_Ruta_Agentes SET cve_vendedor = '{$id_vendedor}' WHERE cve_ruta = '{$id_ruta}'";
                    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
                }
                else
                {
                    $sql = "INSERT IGNORE INTO Rel_Ruta_Agentes (cve_ruta, cve_vendedor) VALUES('{$id_ruta}', '{$id_vendedor}')";
                    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
                }

                if(!$existetcxr)
                {
                    $sql = "INSERT IGNORE INTO t_clientexruta (clave_ruta, clave_cliente) VALUES('{$id_ruta}', '{$Id_Destinatario}')";
                    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
                    $sql = "INSERT IGNORE INTO RelClirutas (IdCliente, IdRuta, IdEmpresa, Fecha) VALUES('{$Id_Destinatario}', '{$id_ruta}', '$cve_almac', CURDATE())";
                    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
                }
            }
            else if($id_ruta && $id_vendedor && $destinatario_existe && $cliente_existe)
            {
                if(!$lunes) $lunes = 0;
                if(!$martes) $martes = 0;
                if(!$miercoles) $miercoles = 0;
                if(!$jueves) $jueves = 0;
                if(!$viernes) $viernes = 0;
                if(!$sabado) $sabado = 0;
                if(!$domingo) $domingo = 0;

                if($id_ruta && $id_vendedor) 
                {
                    if($lunes) 
                    {
                        $sql = "SELECT MAX(Lu) as Lu FROM RelDayCli WHERE Cve_Ruta = '{$id_ruta}' AND Cve_Vendedor = '{$id_vendedor}'";
                        if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
                        $fila = mysqli_fetch_array($res);
                        $Lu = $fila['Lu'];
                        $lunes = $Lu+1;
                    }
                    if($martes) 
                    {
                        $sql = "SELECT MAX(Ma) as Ma FROM RelDayCli WHERE Cve_Ruta = '{$id_ruta}' AND Cve_Vendedor = '{$id_vendedor}'";
                        if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
                        $fila = mysqli_fetch_array($res);
                        $Ma = $fila['Ma'];
                        $martes = $Ma+1;
                    }
                    if($miercoles) 
                    {
                        $sql = "SELECT MAX(Mi) as Mi FROM RelDayCli WHERE Cve_Ruta = '{$id_ruta}' AND Cve_Vendedor = '{$id_vendedor}'";
                        if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
                        $fila = mysqli_fetch_array($res);
                        $Mi = $fila['Mi'];
                        $miercoles = $Mi+1;
                    }
                    if($jueves) 
                    {
                        $sql = "SELECT MAX(Ju) as Ju FROM RelDayCli WHERE Cve_Ruta = '{$id_ruta}' AND Cve_Vendedor = '{$id_vendedor}'";
                        if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
                        $fila = mysqli_fetch_array($res);
                        $Ju = $fila['Ju'];
                        $jueves = $Ju+1;
                    }
                    if($viernes) 
                    {
                        $sql = "SELECT MAX(Vi) as Vi FROM RelDayCli WHERE Cve_Ruta = '{$id_ruta}' AND Cve_Vendedor = '{$id_vendedor}'";
                        if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
                        $fila = mysqli_fetch_array($res);
                        $Vi = $fila['Vi'];
                        $viernes = $Vi+1;
                    }
                    if($sabado) 
                    {
                        $sql = "SELECT MAX(Sa) as Sa FROM RelDayCli WHERE Cve_Ruta = '{$id_ruta}' AND Cve_Vendedor = '{$id_vendedor}'";
                        if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
                        $fila = mysqli_fetch_array($res);
                        $Sa = $fila['Sa'];
                        $sabado = $Sa+1;
                    }
                    if($domingo) 
                    {
                        $sql = "SELECT MAX(Do) as Do FROM RelDayCli WHERE Cve_Ruta = '{$id_ruta}' AND Cve_Vendedor = '{$id_vendedor}'";
                        if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
                        $fila = mysqli_fetch_array($res);
                        $Do = $fila['Do'];
                        $domingo = $Do+1;
                    }
                }

                $model->Cve_Ruta            = $id_ruta;
                $model->Cve_Cliente         = $Cve_Cliente;
                $model->Id_Destinatario     = $Id_Destinatario;
                $model->Cve_Vendedor        = $id_vendedor;
                $model->Lu                  = $lunes;
                $model->Ma                  = $martes;
                $model->Mi                  = $miercoles;
                $model->Ju                  = $jueves;
                $model->Vi                  = $viernes;
                $model->Sa                  = $sabado;
                $model->Do                  = $domingo;
                $model->Cve_Almac           = $cve_almac;

                $model->save();

                if($vendedor_existe)
                {
                    $sql = "UPDATE Rel_Ruta_Agentes SET cve_vendedor = '{$id_vendedor}' WHERE cve_ruta = '{$id_ruta}'";
                    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
                }
                else
                {
                    $sql = "INSERT IGNORE INTO Rel_Ruta_Agentes (cve_ruta, cve_vendedor) VALUES('{$id_ruta}', '{$id_vendedor}')";
                    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
                }

                if(!$existetcxr)
                {
                    $sql = "INSERT IGNORE INTO t_clientexruta (clave_ruta, clave_cliente) VALUES('{$id_ruta}', '{$Id_Destinatario}')";
                    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}

                    $sql = "INSERT IGNORE INTO RelClirutas (IdCliente, IdRuta, IdEmpresa, Fecha) VALUES('{$Id_Destinatario}', '{$id_ruta}', '$cve_almac', CURDATE())";
                    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
                }
            }
                $linea++;

        }
      }

    $sql = "UPDATE RelDayCli SET Lu = NULL WHERE Lu = 0";
    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}

    $sql = "UPDATE RelDayCli SET Ma = NULL WHERE Ma = 0";
    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}

    $sql = "UPDATE RelDayCli SET Mi = NULL WHERE Mi = 0";
    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}

    $sql = "UPDATE RelDayCli SET Ju = NULL WHERE Ju = 0";
    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}

    $sql = "UPDATE RelDayCli SET Vi = NULL WHERE Vi = 0";
    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}

    $sql = "UPDATE RelDayCli SET Sa = NULL WHERE Sa = 0";
    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}

    $sql = "UPDATE RelDayCli SET Do = NULL WHERE Do = 0";
    if (!($res = mysqli_query($conn, $sql))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}

      @unlink($file);
      $linea -= 2;
      $this->response(200, [
        'debug' => $debug,
        'statusText' =>  "Visitas importadas con exito. Total: \"{$linea}\"",
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
            'clave',
            'razonsocial',
            'direccion',
            'colonia',
            'postal',
            'ciudad',
            'estado',
            'contacto',
            'telefono',
            'activo',
        ];

        $data_clientes = Destinatarios::get();

        $filename = "destinatarios_".date('Ymd') . ".xls";

        ob_clean();
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        
        foreach($columnas as $column) {            
            echo $column . "\t" ;            
        }
        print("\r\n");


        foreach($data_clientes as $row)
        {            
            echo $this->clear_column($row->Cve_Clte) . "\t";
            echo $this->clear_column($row->razonsocial) . "\t";
            echo $this->clear_column($row->direccion) . "\t";
            echo $this->clear_column($row->colonia) . "\t";
            echo $this->clear_column($row->postal) . "\t";
            echo $this->clear_column($row->ciudad) . "\t";
            echo $this->clear_column($row->estado) . "\t";
            echo $this->clear_column($row->contacto) . "\t";
            echo $this->clear_column($row->telefono) . "\t";
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
