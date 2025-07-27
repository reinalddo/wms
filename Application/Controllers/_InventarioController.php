<?php

namespace Application\Controllers;

use Framework\View\View;
use Framework\Helpers\Utils;
use Framework\Http\Response;
use Framework\Http\Controller;
use jakeroid\tools\SimpleXLSX;
use Application\Models\AlmacenP;
use Application\Models\Usuarios;
use Application\Models\ArticulosGrupos;
use Illuminate\Database\Capsule\Manager as Capsule;


/**
 * @SWG\Info(title="My First API", version="0.1")
 */

/**
 * @SWG\Get(
 *     path="/api/resource.json",
 *     @SWG\Response(response="200", description="An example resource")
 * )
 */

/**
 * @version 1.0.0
 * @category Inventario
 * @author Brayan Rincon <brayan262@gemail.com>
 */
class InventarioController extends Controller
{
    const CLAVE = 0;
    const DESCRIPCION = 2;

    private $camposRequeridos = [
        self::CLAVE => 'Clave', 
        self::DESCRIPCION => 'Descripción',
    ];

    
    /**
     * Renderiza la vista general
     *
     * @return void
     */
    public function index()
    {

    }


    /**
     * Retorna un JSON con los registros del Administrador de 
     * inventario compatible con JQGRID
     *
     * @return void
     */
    public function paginate()
    {
        $page   = $this->getInput('page', 1); // get the requested page
        $limit  = $this->getInput('rows', 30); // get how many rows we want to have into the grid
        $sidx   = $this->getInput('sidx'); // get index row - i.e. user click to sort
        $sord   = $this->getInput('sord'); // get the direction

        $status   = $this->getInput('status', 'A');
        $almacen   = $this->getInput('almacen', '100');
        
        // se conecta a la base de datos
        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        mysqli_set_charset($conn, 'utf8');
    
        $sql = "SELECT * FROM(SELECT 
                    inv.ID_Inventario AS consecutivo,
                    DATE_FORMAT(COALESCE(MIN(v_inv.fecha), inv.Fecha),'%d-%m-%Y %H:%i:%s') AS fecha_inicio,
                    (CASE 
                        WHEN inv.`Status` = 'T' OR inv.`Status` = 'O' THEN IFNULL(DATE_FORMAT((SELECT t_invpiezas.fecha_fin FROM t_invpiezas WHERE t_invpiezas.ID_Inventario = inv.ID_Inventario AND t_invpiezas.fecha_fin IS NOT null AND t_invpiezas.NConteo = (SELECT MAX(t_invpiezas.NConteo) FROM t_invpiezas WHERE t_invpiezas.ID_Inventario = inv.ID_Inventario) LIMIT 1),'%d-%m-%Y %H:%i:%s'),'--')
                        WHEN inv.`Status` = 'A' THEN '--' 
                    END) AS fecha_final,
                    almac.nombre AS almacen,
                    IFNULL(c_almacen.des_almac, 'Inventario Total') AS zona,
                    IFNULL(
                        (SELECT 
                            u.nombre_completo 
                        FROM c_usuario u
                        WHERE 
                            (CONVERT(u.cve_usuario USING UTF8) = 
                                (SELECT 
                                    conteo.cve_usuario
                                FROM t_conteoinventario conteo 
                                WHERE 
                                    conteo.ID_Inventario = inv.ID_Inventario AND 
                                    conteo.NConteo = 
                                        (SELECT 
                                            MAX(conteo2.NConteo) 
                                        FROM t_conteoinventario conteo2 
                                        WHERE conteo2.ID_Inventario = inv.ID_Inventario
                                        
                                        ) 					
                                LIMIT 1) 
                            ) 
                        ) 
                        ,'--'
                     ) AS usuario,
                
                    (CASE 
                        WHEN inv.`Status` = 'T' OR inv.`Status` = 'O' THEN 'Cerrado' 
                        WHEN inv.`Status` = 'A' THEN 'Abierto' 
                    END) AS `status`,
                
                    IF(inv.status = 'T',IFNULL((
                        SELECT 
                        SUM(ABS(ExistenciaTeorica - Cantidad))
                        FROM t_invpiezas
                        WHERE ID_Inventario = inv.ID_Inventario
                        AND t_invpiezas.NConteo = (Select max(t_invpiezas.NConteo) from t_invpiezas where t_invpiezas.ID_Inventario = inv.ID_Inventario)
                            GROUP BY ID_Inventario
                    
                    ), '--'),0) AS diferencia,
                    
                    if(inv.status = 'T',ROUND(
                      IFNULL((
                        SELECT 
                        ((sum(ExistenciaTeorica) - (if(ExistenciaTeorica-Cantidad < 0,((ExistenciaTeorica-Cantidad)*-1),(ExistenciaTeorica-Cantidad))))/sum(ExistenciaTeorica))*100 as Porcentaje
                        FROM t_invpiezas
                        WHERE ID_Inventario = inv.ID_Inventario
                        AND t_invpiezas.NConteo = (Select max(t_invpiezas.NConteo) from t_invpiezas where t_invpiezas.ID_Inventario = inv.ID_Inventario)
                            GROUP BY ID_Inventario
                    ), '--'), 2),0) AS porcentaje,
                    
                    
                    'Físico' AS tipo, 

                    IFNULL((SELECT 
                        nombre_completo 
                    FROM c_usuario 
                    WHERE cve_usuario = 
                        (SELECT cve_supervisor FROM t_conteoinventario WHERE ID_Inventario = inv.ID_Inventario LIMIT 1)
                    ), '--') AS supervisor,

                    IFNULL((
                        SELECT 
                        IF(MAX(NConteo) < 1, 0, (MAX(NConteo))) conteo
                        FROM t_invpiezas
                        WHERE ID_Inventario = consecutivo
                            GROUP BY ID_Inventario
                        
                    ), '--') AS n_inventario  
                FROM th_inventario inv
                    LEFT JOIN c_almacenp almac ON inv.cve_almacen = CONVERT(almac.clave USING UTF8)
                    LEFT JOIN c_almacen ON inv.cve_zona = c_almacen.cve_almac
                    LEFT JOIN V_Inventario v_inv ON v_inv.ID_Inventario = inv.ID_Inventario
                WHERE inv.Activo = 1  AND inv.`Status` = '{$status}'
                and almac.clave = '{$almacen}'
                GROUP BY inv.ID_Inventario
                
                UNION 
                
                SELECT
                    DISTINCT cab.ID_PLAN AS consecutivo, 	
                    DATE_FORMAT(cab.FECHA_INI, '%d-%m-%Y %H:%i:%s') AS fecha_inicio, 	
                    DATE_FORMAT(cab.FECHA_FIN, '%d-%m-%Y %H:%i:%s') AS fecha_final,	
                    ap.nombre AS almacen, 	
                    '--' AS zona,	
                    (SELECT u.cve_usuario FROM c_usuario u, t_conteoinventariocicl cic WHERE cic.cve_usuario = u.cve_usuario AND cic.ID_PLAN=cab.ID_PLAN LIMIT 1) AS usuario,
                    (CASE 
                    WHEN d.status = 'A' THEN 'Abierto'
                    WHEN d.status = 'T' THEN 'Cerrado'
                    ELSE 'Sin Definir'
                    END) AS status,	
                    
                    if(d.status = 'T',IFNULL((
                        SELECT 
                        SUM(ABS(ExistenciaTeorica - Cantidad))
                        FROM t_invpiezas
                        WHERE ID_Inventario = cab.ID_PLAN
                            GROUP BY ID_Inventario
                        
                    ), '--'),0) AS diferencia,
                    
                  if(d.status = 'T',ROUND(
                    IFNULL((
                        SELECT 
                        ((sum(ExistenciaTeorica) - (if(ExistenciaTeorica-Cantidad < 0,((ExistenciaTeorica-Cantidad)*-1),(ExistenciaTeorica-Cantidad))))/sum(ExistenciaTeorica))*100 as Porcentaje
                        FROM t_invpiezas
                        WHERE ID_Inventario = cab.ID_PLAN
                            GROUP BY ID_Inventario
                    ), '--'), 2),0) AS porcentaje,
                    
                    'Cíclico' AS tipo,	

                    IFNULL((SELECT 
                        nombre_completo 
                    FROM c_usuario 
                    WHERE id_user = 
                        (SELECT cve_supervisor FROM t_conteoinventariocicl WHERE ID_PLAN = cab.ID_PLAN LIMIT 1)
                    ), '--') AS supervisor,

                    IFNULL((
                        SELECT 
                        IF(MAX(NConteo) < 1, 0, (MAX(NConteo))) conteo
                        FROM t_invpiezasciclico
                        WHERE ID_PLAN = cab.ID_PLAN
                            GROUP BY ID_PLAN
                        
                    ), 0) AS n_inventario
                FROM det_planifica_inventario d
                    LEFT JOIN c_articulo a ON d.cve_articulo = a.cve_articulo 
                    LEFT JOIN c_almacenp ap ON a.cve_almac = ap.id 
                    LEFT JOIN cab_planifica_inventario cab ON cab.ID_PLAN = d.ID_PLAN
                WHERE  d.`Status` = '{$status}'
                and ap.clave = '{$almacen}'
                ) W
              ORDER BY STR_TO_DATE(fecha_inicio, '%d-%m-%Y %H:%i:%s') DESC";
    
        //echo var_dump($sql);
        //die();
        // hace una llamada previa al procedimiento almacenado Lis_Facturas
        //$query = mysqli_query($conn, $sql);
        if (!($res = mysqli_query($conn, $sql))) 
        {
          echo "Falló la preparación: (" . mysqli_error($conn) . ") "; exit;
        }
       
      
        $i = 0;
        $rows = [];
        $data = [];
        while ($rows[] = mysqli_fetch_array($res));
        /*usort($rows, function( $a, $b ) 
        {
          return strtotime($a['fecha_inicio']) - strtotime($b['fecha_inicio']);
        });*/
        //$rows = array_reverse($rows);
        foreach ($rows as $value)
        {
          if($value == NULL)
          {
            continue;
          }
          extract($value);
          $fecha_i = date('Y-m-d H:i:s', strtotime($fecha_inicio));
          $fecha_f = $fecha_final !== '--' ? date('Y-m-d H:i:s', strtotime($fecha_final)) : false;
          $efectuado = "";
          if(!$fecha_f)
          {
            $efectuado = '<i class="fa fa-circle yellow"></i>';
          }
          else 
          {
            if($fecha_i >= $fecha_f)
            {
              $efectuado = '<i class="fa fa-circle green"></i>';
            }
            else 
            {
              $efectuado = '<i class="fa fa-circle red"></i>';
            }
          }
          /*if($status == 'Abierto'){
              $status = '<span class="red"><strong>'.$status.'</strong></span>';
          } else {
              $status = '<span class="green"><strong>'.$status.'</strong></span>';
          }*/
         
          
          $data[$i]['id'] = $consecutivo;
          $data[$i]['cell'] = [
            '',
            $consecutivo,
            $almacen, 
            $zona, 
            $fecha_inicio, 
            $fecha_final, 
            $supervisor, 
            $diferencia,
            $porcentaje,
            $status, 
            $tipo,
            $n_inventario, 
            $efectuado
          ];
          $i++;
        }
        $response = $this->responseJQGrid($data);
        ob_clean();
        echo json_encode($response);exit;
    }

    private function ordenar( $a, $b ) {
        return strtotime($a['fecha_inicio']) - strtotime($b['fecha_inicio']);
    }

    public function XcargarDeatllesInventarioFisicoX($id_inventario, $conteo = FALSE)
    {
      $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
      $start = 1;
      $limit = 9999;
      $sql = "(SELECT MAX(NConteo) as conteo FROM t_conteoinventario WHERE ID_Inventario = {$id_inventario})";
      $query = mysqli_query($conn, $sql);
      $conteo_actual = mysqli_fetch_array($query)["conteo"];
//       if($conteo_actual>2)
//       {
//         $conteo = ($conteo !== false) ? intval($conteo) : "(SELECT MAX(NConteo)-1 FROM t_conteoinventario WHERE ID_Inventario = {$id_inventario})";
//       }
//       else
//       {
         $conteo = ($conteo !== false) ? intval($conteo) : "(SELECT MAX(NConteo) FROM t_conteoinventario WHERE ID_Inventario = {$id_inventario})";
//       }
      
      /* Revisar si es un inventario fisico o planeacion */
     /* $sql = "SELECT 
                  IF(LENGTH(fecha_final) > 2, 'f', 'p') AS tipo
              FROM V_AdministracionInventario
              WHERE consecutivo = {$id_inventario}";*/
      
      $sql = "SELECT 
              IF(LENGTH(NConteo) > 2, 'f', 'p') AS tipo 
              FROM t_invpiezas 
              WHERE ID_Inventario = {$id_inventario}
              GROUP BY tipo";
      $sth = \db()->prepare($sql);
      $sth->execute();
      $tipo = $sth->fetch(\PDO::FETCH_ASSOC)['tipo'];
      if($tipo === 'f' && gettype($conteo) !== "integer")
      {
        $sql = 
          "
          SELECT DISTINCT
            V_ExistenciaFisica.ubicacion,
            IFNULL(c_articulo.cve_articulo, '--') as clave,
            IFNULL(c_articulo.des_articulo, '--') as descripcion,   
            if(V_ExistenciaFisica.cve_lote = '', '--', V_ExistenciaFisica.cve_lote) as lote,
            IFNULL(c_lotes.CADUCIDAD, '--') as caducidad,
            IFNULL(c_serie.numero_serie, '--') as serie,
            (SELECT DISTINCT Cantidad FROM t_invpiezas WHERE ID_Inventario = {$id_inventario} AND cve_articulo = V_ExistenciaFisica.cve_articulo AND idy_ubica = V_ExistenciaFisica.idy_ubica AND NConteo =(SELECT MAX(NConteo) FROM t_conteoinventario WHERE ID_Inventario = {$id_inventario})) as stockTeorico,
            IFNULL(V_ExistenciaFisica.Existencia, '--') as stockFisico,
            IFNULL(((SELECT stockTeorico) - (SELECT stockFisico)), '--') as diferencia,
            V_ExistenciaFisica.NConteo as conteo,
            V_ExistenciaFisica.usuario,
            'Piezas' as unidad_medida
        FROM V_ExistenciaFisica 
            LEFT JOIN c_articulo ON c_articulo.cve_articulo = V_ExistenciaFisica.cve_articulo
            LEFT JOIN c_lotes ON c_lotes.LOTE = V_ExistenciaFisica.cve_lote
            LEFT JOIN c_serie ON c_serie.cve_articulo = c_articulo.cve_articulo
            LEFT JOIN V_ExistenciaGral ON V_ExistenciaGral.cve_ubicacion = V_ExistenciaFisica.idy_ubica and V_ExistenciaGral.cve_articulo = V_ExistenciaFisica.cve_articulo
        WHERE 
            V_ExistenciaFisica.idy_ubica IN (SELECT DISTINCT cve_ubicacion from V_Ubicacion_Inventario where ID_Inventario = {$id_inventario} AND tipo is not null) AND
            V_ExistenciaFisica.ID_Inventario = {$id_inventario} AND 
            V_ExistenciaFisica.NConteo = {$conteo}
        ";
      }
      
      else if($tipo === 'p' || gettype($conteo) === "integer")
      {
        $sql = "
         SELECT 
          t_invpiezas.cve_articulo, 
          c_articulo.des_articulo, 
          (CASE
            WHEN t_ubicacioninventario.cve_ubicacion IS NOT NULL THEN 
              (SELECT desc_ubicacion FROM tubicacionesretencion WHERE t_ubicacioninventario.cve_ubicacion = tubicacionesretencion.cve_ubicacion)
            WHEN t_ubicacioninventario.idy_ubica IS NOT NULL THEN
              (SELECT c_ubicacion.CodigoCSD FROM c_ubicacion WHERE c_ubicacion.idy_ubica = t_ubicacioninventario.idy_ubica)
           ELSE '--'
           END) as ubicacion,
          t_invpiezas.id,
          if(t_invpiezas.cve_lote = '', '--', t_invpiezas.cve_lote) as lote,
          IFNULL(c_lotes.CADUCIDAD, '--') as caducidad,
          '' as serie,
          ExistenciaTeorica as stockTeorico,
          '' as stockFisico,
          (ExistenciaTeorica - '') as diferencia,
          t_invpiezas.NConteo,
          c_usuario.nombre_completo,
          'Piezas' as unidad_medida
          FROM t_invpiezas 
          LEFT JOIN c_articulo ON c_articulo.cve_articulo = t_invpiezas.cve_articulo
          LEFT JOIN c_ubicacion ON c_ubicacion.idy_ubica = t_invpiezas.idy_ubica
          LEFT JOIN c_lotes ON c_lotes.cve_articulo = t_invpiezas.cve_articulo 
          
          LEFT JOIN t_ubicacioninventario ON t_ubicacioninventario.ID_Inventario = t_invpiezas.ID_Inventario and t_ubicacioninventario.idy_ubica = t_invpiezas.idy_ubica 
          LEFT JOIN c_usuario ON c_usuario.cve_usuario = t_invpiezas.cve_usuario
          WHERE t_invpiezas.ID_Inventario = {$id_inventario}
          AND   t_invpiezas.NConteo = {$conteo}
          AND t_invpiezas.Art_Cerrado IS NULL
          AND t_invpiezas.Cantidad <> t_invpiezas.ExistenciaTeorica
          ORDER BY t_invpiezas.id
          ";
      }
      
      //echo var_dump($sql);
      //die();
      $sth = \db()->prepare($sql);
      $sth->execute();
      $data = $sth->fetchAll(\PDO::FETCH_ASSOC);
      
      $sql2 = "SELECT t_conteoinventario.cve_usuario, c_usuario.nombre_completo 
              FROM `t_conteoinventario`  
              LEFT JOIN c_usuario ON t_conteoinventario.cve_usuario = c_usuario.cve_usuario
              WHERE t_conteoinventario.ID_Inventario = {$id_inventario}
              AND t_conteoinventario.NConteo = (SELECT MAX(NConteo) FROM t_conteoinventario WHERE t_conteoinventario.ID_Inventario = {$id_inventario})
              ORDER BY `t_conteoinventario`.`ID_Inventario` DESC , NConteo  DESC;";
      //t_conteoinventario.ID_Inventario, t_conteoinventario.NConteo ,t_conteoinventario.cve_usuario, 
      $sth = \db()->prepare($sql2);
      $sth->execute();
      $users = $sth->fetchAll(\PDO::FETCH_ASSOC);
      
      
      $response_1 = array();
      foreach($users as $value)
      {
        $response_1[] = array(
          'nombre'            =>  $value["nombre_completo"],
          'clave'             =>  $value["cve_usuario"]
        );
      }
      //echo var_dump($response_1); die();
      
      //$ga = new \InventariosFisicos\InventariosFisicos();
      //$data = $ga->loadDetalle($id_inventario, 0, 100000, $conteo);
      $response = array();
      foreach($data as $value)
      {
        $response[] = array(
//           'accion'        =>  "",
          'id'            =>  $value["id"],
          'clave'         =>  $value["cve_articulo"], 
          'ubicacion'     =>  $value["ubicacion"],
//           'zona'          =>  $value["zona"],
          'descripcion'   =>  $value["des_articulo"], 
          'lote'          =>  $value["lote"], 
          'caducidad'     =>  $value["caducidad"], 
          'serie'         =>  $value["serie"], 
          'stockTeorico'  =>  $value["stockTeorico"], 
          'stockFisico'   =>  $value["stockFisico"], 
          'diferencia'    =>  $value["diferencia"], 
          'conteo'        =>  $value["NConteo"], 
          'usuario'       =>  $users, 
          'unidad_medida' =>  $value["unidad_medida"]
        );
      }
      $res = array(
        'data'=> $response,
        'users'=> $response_1,
        'sql' => $sql
      );
      $this->response(200, $res);
    }


    /**
     * Undocumented function
     *
     * @param [type] $id_inventario
     * @param boolean $conteo
     * @return void
     */
    public function cargarDeatllesInventarioCiclico($id_inv, $conteo = false)
    {
        $start = 1;
        $limit = 9999;

        $vec_id_con = explode("-", $id_inv);

        $id_inventario = $vec_id_con[0];
        $conteo_inv = $vec_id_con[1];
        $conteo_tipo2 = $conteo_inv - 1;
        $cantidad_existente = "";
        if($conteo_inv > 1) $cantidad_existente = "AND inv_ciclico.Cantidad != 0";
        $sql="
        SELECT * FROM (
        SELECT
                c.des_almac zona,
                v.cve_ubicacion, 
                (CASE 
                    WHEN v.tipo = 'area' AND v.cve_ubicacion IS NOT NULL THEN 
                        (SELECT desc_ubicacion AS ubicacion FROM tubicacionesretencion WHERE cve_ubicacion = v.cve_ubicacion) 
                    WHEN v.tipo = 'ubicacion' AND v.cve_ubicacion IS NOT NULL THEN 
                        (SELECT u.CodigoCSD AS ubicacion FROM c_ubicacion u WHERE u.idy_ubica = v.cve_ubicacion)
                    ELSE '--'
                END) AS ubicacion,
                IFNULL(c_articulo.cve_articulo, '--') as clave,
                IFNULL(c_articulo.des_articulo, '--') as descripcion,
                if(v.cve_lote = '', '', v.cve_lote) as lote,
                IF(c_articulo.Caduca = 'S', IFNULL(c_lotes.CADUCIDAD, '--'), '') AS caducidad,
                #(SELECT MAX(NConteo) FROM t_conteoinventariocicl WHERE ID_PLAN = {$id_inventario}) as conteo,
                IFNULL(inv.NConteo, 0) AS conteo, 
                IFNULL(v.Existencia, 0) as stockTeorico,
                inv.Cantidad AS Cantidad_reg,
                (SELECT Cantidad FROM t_invpiezasciclico iv WHERE iv.ID_PLAN = {$id_inventario} AND iv.cve_articulo = v.cve_articulo AND iv.idy_ubica = v.cve_ubicacion AND iv.cve_lote = v.cve_lote 
                AND iv.NConteo = {$conteo_tipo2}) AS Cantidad,
                IFNULL((SELECT Cantidad FROM t_invpiezasciclico WHERE ID_PLAN = {$id_inventario} AND NConteo = (SELECT conteo) AND cve_articulo = v.cve_articulo AND idy_ubica = v.cve_ubicacion AND cve_lote = v.cve_lote GROUP BY cve_articulo LIMIT 1), 0) AS stockFisico,
                (IFNULL((SELECT stockFisico) - (SELECT stockTeorico), 0)) AS diferencia,
                IFNULL((SELECT nombre_completo FROM c_usuario WHERE cve_usuario = (SELECT cve_usuario FROM t_conteoinventariocicl WHERE ID_PLAN = {$id_inventario} AND NConteo = {$conteo_inv} ORDER BY id DESC LIMIT 1)), '--') as usuario,
                'Piezas' as unidad_medida,
                (SELECT Status FROM th_inventario WHERE ID_inventario = {$id_inventario} LIMIT 1) AS Status,
                CONCAT(v.tipo, '|', v.cve_ubicacion, '|', v.cve_articulo, '|', v.cve_lote, '|', {$id_inventario}, '|', (SELECT conteo), '|', IF((SELECT cve_usuario FROM t_conteoinventariocicl WHERE NConteo = (SELECT conteo) AND ID_PLAN = {$id_inventario}) = '', '-', (SELECT cve_usuario FROM t_conteoinventariocicl WHERE NConteo = (SELECT conteo) AND ID_PLAN = {$id_inventario}))) AS id
            FROM V_ExistenciaGralProduccion v
                LEFT JOIN c_articulo on c_articulo.cve_articulo = v.cve_articulo
                INNER JOIN cab_planifica_inventario i on i.cve_articulo = v.cve_articulo AND i.ID_PLAN = {$id_inventario}
                LEFT JOIN c_almacen AS c ON c.cve_almac=(SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = v.cve_ubicacion) 
                LEFT JOIN c_lotes on c_lotes.LOTE = v.cve_lote
                LEFT JOIN t_invpiezasciclico inv ON inv.ID_PLAN = {$id_inventario} AND inv.cve_articulo = v.cve_articulo AND inv.idy_ubica = v.cve_ubicacion AND inv.cve_lote = v.cve_lote 
            WHERE v.Existencia > 0 AND v.tipo='ubicacion' AND inv.NConteo = {$conteo_inv}
            GROUP BY v.cve_articulo,v.cve_ubicacion,v.cve_lote
            ) AS inv_ciclico WHERE inv_ciclico.Cantidad NOT IN (SELECT ic.Cantidad FROM t_invpiezasciclico ic WHERE ic.ID_PLAN = {$id_inventario} AND ic.cve_articulo = inv_ciclico.clave AND ic.idy_ubica = inv_ciclico.cve_ubicacion 
            AND ic.cve_lote = inv_ciclico.lote AND ic.NConteo < {$conteo_tipo2}) {$cantidad_existente} #AND inv_ciclico.Cantidad != 0
            ";

        $sth = \db()->prepare($sql);
        $sth->execute();
        $data = $sth->fetchAll(\PDO::FETCH_ASSOC);

        foreach($data as $value){
            extract($value);
            $response[] = [
                'id' => $id,
                'clave' => $clave,
                'clave' => $clave, 
                'descripcion' => $descripcion,
                'zona' => $zona,
                'ubicacion' => $ubicacion, 
                'serie' => $serie, 
                'lote' => $lote, 
                'caducidad' => $caducidad, 
                'stockTeorico' => $stockTeorico, 
                'stockFisico' => $stockFisico, 
                'diferencia' => $diferencia,
                'conteo' => $conteo, 
                'Status' => $Status, 
                'usuario' => $usuario,
                'unidad_medida' => $unidad_medida, 
                'Cantidad_reg' => $Cantidad_reg
            ];
        }
        $this->response(200, [
            'data'=>$response,
        ]);

    }


    public function cargarDeatllesInventarioFisico($id_inv, $conteo = false)
    {
        $start = 1;
        $limit = 9999;

        $vec_id_con = explode("-", $id_inv);

        $id_inventario = $vec_id_con[0];
        $conteo_inv = $vec_id_con[1];
        $conteo_tipo2 = $conteo_inv - 1;
        $cantidad_existente = "";
        if($conteo_inv > 1) $cantidad_existente = "AND inv_fisico.Cantidad != 0";
        $sql="
        SELECT * FROM (
        SELECT
                c.des_almac zona,
                v.cve_ubicacion, 
                (CASE 
                    WHEN v.tipo = 'area' AND v.cve_ubicacion IS NOT NULL THEN 
                        (SELECT desc_ubicacion AS ubicacion FROM tubicacionesretencion WHERE cve_ubicacion = v.cve_ubicacion) 
                    WHEN v.tipo = 'ubicacion' AND v.cve_ubicacion IS NOT NULL THEN 
                        (SELECT u.CodigoCSD AS ubicacion FROM c_ubicacion u WHERE u.idy_ubica = v.cve_ubicacion)
                    ELSE '--'
                END) AS ubicacion,
                IFNULL(c_articulo.cve_articulo, '--') as clave,
                IFNULL(c_articulo.des_articulo, '--') as descripcion,
                if(v.cve_lote = '', '', v.cve_lote) as lote,
                IF(c_articulo.Caduca = 'S', IFNULL(c_lotes.CADUCIDAD, '--'), '') AS caducidad,
                #(SELECT MAX(NConteo) FROM t_conteoinventario WHERE ID_Inventario = {$id_inventario}) as conteo,
                IFNULL(inv.NConteo, 0) AS conteo, 
                #IFNULL(v.Existencia, 0) as stockTeorico,
                IFNULL(inv.ExistenciaTeorica, 0) AS stockTeorico,
                inv.Cantidad AS Cantidad_reg,
                (SELECT Cantidad FROM t_invpiezas iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = v.cve_articulo AND iv.idy_ubica = v.cve_ubicacion AND iv.cve_lote = v.cve_lote 
                AND iv.NConteo = {$conteo_tipo2}) AS Cantidad,
                IFNULL((SELECT Cantidad FROM t_invpiezas WHERE ID_Inventario = {$id_inventario} AND NConteo = (SELECT conteo) AND cve_articulo = v.cve_articulo AND idy_ubica = v.cve_ubicacion AND cve_lote = v.cve_lote GROUP BY cve_articulo LIMIT 1), 0) AS stockFisico,
                (IFNULL((SELECT stockFisico) - (SELECT stockTeorico), 0)) AS diferencia,
                IFNULL((SELECT nombre_completo FROM c_usuario WHERE cve_usuario = (SELECT cve_usuario FROM t_conteoinventario WHERE ID_Inventario = {$id_inventario} AND NConteo = {$conteo_inv} ORDER BY id DESC LIMIT 1)), '--') as usuario,
                'Piezas' as unidad_medida,
                (SELECT Status FROM th_inventario WHERE ID_inventario = {$id_inventario} LIMIT 1) AS Status,
                CONCAT(v.tipo, '|', v.cve_ubicacion, '|', v.cve_articulo, '|', v.cve_lote, '|', {$id_inventario}, '|', (SELECT conteo), '|', IF((SELECT cve_usuario FROM t_conteoinventario WHERE NConteo = (SELECT conteo) AND ID_Inventario = {$id_inventario}) = '', '-', (SELECT cve_usuario FROM t_conteoinventario WHERE NConteo = (SELECT conteo) AND ID_Inventario = {$id_inventario}))) AS id
            FROM V_ExistenciaGralProduccion v
                LEFT JOIN c_articulo on c_articulo.cve_articulo = v.cve_articulo
                #INNER JOIN cab_planifica_inventario i on i.cve_articulo = v.cve_articulo AND i.ID_PLAN = {$id_inventario}
                LEFT JOIN c_almacen AS c ON c.cve_almac=(SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = v.cve_ubicacion) 
                LEFT JOIN c_lotes on c_lotes.LOTE = v.cve_lote
                LEFT JOIN t_invpiezas inv ON inv.ID_Inventario = {$id_inventario} AND inv.cve_articulo = v.cve_articulo AND inv.idy_ubica = v.cve_ubicacion AND inv.cve_lote = v.cve_lote 
            WHERE v.Existencia > 0 AND v.tipo='ubicacion' AND inv.NConteo = {$conteo_inv}
            GROUP BY v.cve_articulo,v.cve_ubicacion,v.cve_lote
            ) AS inv_fisico WHERE inv_fisico.Cantidad NOT IN (SELECT ic.Cantidad FROM t_invpiezas ic WHERE ic.ID_Inventario = {$id_inventario} AND ic.cve_articulo = inv_fisico.clave AND ic.idy_ubica = inv_fisico.cve_ubicacion 
            AND ic.cve_lote = inv_fisico.lote AND ic.NConteo < {$conteo_tipo2}) {$cantidad_existente} AND IFNULL(inv_fisico.Cantidad, 0) != inv_fisico.stockTeorico #AND inv_fisico.Cantidad != 0
            ";

        $sth = \db()->prepare($sql);
        $sth->execute();
        $data = $sth->fetchAll(\PDO::FETCH_ASSOC);

        foreach($data as $value){
            extract($value);
            $response[] = [
                'id' => $id,
                'clave' => $clave,
                'clave' => $clave, 
                'descripcion' => $descripcion,
                'zona' => $zona,
                'ubicacion' => $ubicacion, 
                'serie' => $serie, 
                'lote' => $lote, 
                'caducidad' => $caducidad, 
                'stockTeorico' => $stockTeorico, 
                'stockFisico' => $stockFisico, 
                'diferencia' => $diferencia,
                'conteo' => $conteo, 
                'Status' => $Status, 
                'usuario' => $usuario,
                'unidad_medida' => $unidad_medida, 
                'Cantidad_reg' => $Cantidad_reg
            ];
        }
        $this->response(200, [
            'data'=>$response,
        ]);

    }

    
    /**
     * Undocumented function
     *
     * @return void
     */
    public function XguardarStockFisicoX()
    {
      $info = json_decode($_POST['info'],true);
      
      $stocks = $info['conteos'];
      $datos = $info['datos'];
      $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
      $cerrar_inventario = false;
      //$sql = "(SELECT ID_Inventario from t_invpiezas where id = {$id})";
      foreach ($stocks as $key => $value) 
      {
        $id = $value['id'];
        $cantidad = $value['value'];
        $sql = 
          //Aqui se debe ubdatear la clave de usuario 
          "UPDATE  t_invpiezas 
              SET Cantidad = {$cantidad}, cve_usuario = '{$datos[$key]["usuario"]}'
            WHERE id = {$id}
          ";
        $query = mysqli_query($conn, $sql);
        //traer ID_inventario
        $sql = "(SELECT ID_Inventario from t_invpiezas where id = {$id})";
        $query = mysqli_query($conn, $sql);
        $inventario = mysqli_fetch_array($query)["ID_Inventario"];
        //traer clave de articulos del inventario, del conteo cero
        $sql = "SELECT t_invpiezas.cve_articulo FROM `t_invpiezas` WHERE ID_Inventario = '{$inventario}' AND NConteo = '0'";
        $query = mysqli_query($conn, $sql);
        $articulos = mysqli_fetch_all($query);
        $k = 0;
        //por cada articulo del inventario, del conteo cero se realiza los siguiente
        foreach($articulos as $art)
        {
          $k++;
          //Trae los conteos que coincidan de un mismo articulo que no tengan ajuste de inventario
          $sql = "SELECT MAX(coincide) as coincidencias from(SELECT  SUM(1) as coincide, cve_articulo, Cantidad
                FROM `t_invpiezas` 
                WHERE `ID_Inventario` = {$inventario}
                AND cve_articulo = '{$art[0]}'
                AND Cantidad = ExistenciaTeorica
                GROUP BY  Cantidad
                ORDER BY cve_articulo) x";
          $query = mysqli_query($conn, $sql);
          $coincidencias_0 = mysqli_fetch_array($query)["coincidencias"];
          //Trae los conteos que coincidan que sean diferentes al conteo 0
          $sql = "SELECT MAX(coincide) as coincidencias, Cantidad from(SELECT  SUM(1) as coincide, cve_articulo, Cantidad
                FROM `t_invpiezas` 
                WHERE `ID_Inventario` = {$inventario}
                AND t_invpiezas.NConteo > 0
                AND cve_articulo = '{$art[0]}'
                GROUP BY  Cantidad
                ORDER BY cve_articulo) x";
          $query = mysqli_query($conn, $sql);
          $resultado = mysqli_fetch_all($query);
          $coincidencias = $resultado[0][0];
          $cantidad_contada = $resultado[0][1];
          
          if($coincidencias > 1 || $coincidencias_0 > 0)
          {
            $sql = "SELECT  MAX(NConteo) as cont FROM t_invpiezas WHERE ID_Inventario = {$inventario}";
            $query = mysqli_query($conn, $sql);
            $conteo_maximo = mysqli_fetch_array($query)["cont"];
            //Cierra el articulo            
            $sql = "UPDATE  t_invpiezas
                    SET Art_Cerrado = '1'
                    where cve_articulo = '{$art[0]}'
                    AND ID_Inventario = {$inventario}
                    ";
            $query = mysqli_query($conn, $sql);
          }
        }
        
        
        
        
        
        /*
        $sql = "SELECT COUNT(*) as conteo_productos From (SELECT cve_articulo, Cantidad, ExistenciaTeorica, (Cantidad-ExistenciaTeorica) as diferencia 
                  FROM t_invpiezas 
                  WHERE ID_Inventario = {$inventario}
                  AND NConteo = (SELECT  MAX(NConteo) 
                                  FROM t_invpiezas 
                                  WHERE ID_Inventario = {$inventario})) x";
        $query = mysqli_query($conn, $sql);
        $conteo_productos = mysqli_fetch_array($query)["conteo_productos"];
        if($conteo_productos == 1)
        {
          $sql = "SELECT cve_articulo, Cantidad, ExistenciaTeorica, (Cantidad-ExistenciaTeorica) as diferencia 
                  FROM t_invpiezas 
                  WHERE ID_Inventario = {$inventario} 
                  AND NConteo = (SELECT  MAX(NConteo) 
                                  FROM t_invpiezas 
                                  WHERE ID_Inventario = {$inventario})";
          $query = mysqli_query($conn, $sql);
          $diferenciaFinal = mysqli_fetch_array($query)["diferencia"];
          if($diferenciaFinal == 0)
          {
            $similares = true;
          }
        }
        else
        {
          $sql = "SELECT  MAX(NConteo) as cont FROM t_invpiezas WHERE ID_Inventario = {$inventario}";
          $query = mysqli_query($conn, $sql);
          $conteo_maximo = mysqli_fetch_array($query)["cont"];
          $sql = "SELECT 
                  SUM(ABS(ExistenciaTeorica - Cantidad)) AS diferencia, 
                  NConteo, 
                  ID_Inventario 
                FROM t_invpiezas 
                WHERE ID_Inventario = {$inventario} 
                AND NConteo = {$conteo_maximo}
                GROUP BY ID_Inventario, NConteo
                ";    
          $query = mysqli_query($conn, $sql);
          $rows = mysqli_fetch_all($query, MYSQLI_ASSOC);
          if(count($rows) === 1)
          {
            if(intval($rows[0]['diferencia']) === 0)
            {
              $similares = true;
            }
          }
        }*/
       
      }
      
      
      $sql = "SELECT COUNT(*) AS numero_articulos_cerrados 
                FROM(SELECT `cve_articulo`, `Art_Cerrado` 
                      FROM `t_invpiezas` 
                      WHERE ID_Inventario = {$inventario} 
                      AND Art_Cerrado = 1
                      GROUP BY cve_articulo,cve_lote) x";
      $query = mysqli_query($conn, $sql);
      $numero_articulos_cerrados = mysqli_fetch_array($query)["numero_articulos_cerrados"];
      $sql = "SELECT COUNT(*) AS numero_articulos_inventario
                FROM `t_invpiezas` 
                WHERE ID_Inventario = {$inventario}
                and NConteo = 0
                ";
      $query = mysqli_query($conn, $sql);
      $numero_articulos_inventario = mysqli_fetch_array($query)["numero_articulos_inventario"];
      if($numero_articulos_cerrados == $numero_articulos_inventario)
      {
        $similares = true;
      }
      
     if($similares)
      {
        $cerrar_inventario = true;
        $motivo_cierre = "Por Coincidencia de Stock Fisico en 2 Conteos";
      }
      else
      {
        $cerrar_inventario = false;
      }
      
      if($cerrar_inventario)
      {
        $sql = "UPDATE th_inventario SET Status = 'T' WHERE ID_Inventario = {$inventario}";
        mysqli_multi_query($conn, $sql);
        
        $sql = "UPDATE t_conteoinventario SET Status = 'T' WHERE ID_Inventario = {$inventario}";
        mysqli_multi_query($conn, $sql);
      }
      $this->response(200, [
        'cerrado' => $cerrar_inventario,
        'motivo' => $motivo_cierre,
        'status' => 200
      ]);
    }

    public function activarConteoCierre()
    {
      $inventario = "";
      $stocks = $_POST['conteos'];
      $datos = $_POST['datos'];
      $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
      $cerrar_inventario = false;
      $sql = "(SELECT ID_Inventario from t_invpiezas where id = {$id})";
      foreach ($stocks as $key => $value) 
      {
        $id = $value['id'];
        $sql = "(SELECT ID_Inventario from t_invpiezas where id = {$id})";
        $query = mysqli_query($conn, $sql);
        $inventario = mysqli_fetch_array($query)["ID_Inventario"];
      }
      $sql = "SELECT  MAX(NConteo) as cont FROM t_invpiezas WHERE ID_Inventario = {$inventario}";
      $query = mysqli_query($conn, $sql);
      $conteo_maximo = mysqli_fetch_array($query)["cont"];
      $siguienteConteo = $conteo_maximo + 1;
      $sql ="
        SELECT * 
        FROM (SELECT * 
              FROM (SELECT * 
                    FROM `t_invpiezas` 
                    WHERE ID_Inventario = '{$inventario}' 
                    ORDER BY id DESC) x 
              GROUP BY cve_articulo) y 
        ORDER BY id ASC";
      $query = mysqli_query($conn, $sql);
      $datos_ultimoConteo = mysqli_fetch_all($query);
      foreach($datos_ultimoConteo as $k=>$v)
      {
        $sql= "
        INSERT INTO `t_invpiezas`(
          `ID_Inventario`,
          `NConteo`,
          `idy_ubica`,
          `cve_articulo`,
          `cve_lote`,
          `Cantidad`,
          `ExistenciaTeorica`,
          `cve_usuario`,
          `fecha`,
          `ClaveEtiqueta`,
          `Activo`,
          `Art_Cerrado`) 
        VALUES (
          '{$v[1]}',
          '{$siguienteConteo}',
          '{$v[3]}',
          '{$v[4]}',
          '{$v[5]}',
          '{$v[6]}',
          '{$v[7]}',
          '{$v[8]}',
          '{$v[9]}',
          '{$v[10]}',
          '{$v[11]}',
          '{$v[12]}')
        ";
        $query = mysqli_query($conn, $sql);
      }
      foreach($datos_ultimoConteo as $k=>$v)
      {
        $sql = "INSERT INTO `t_conteoinventario`(
          `ID_Inventario`,
          `NConteo`,
          `cve_usuario`,
          `Status`,
          `Activo`)
        VALUES (
          '{$v[1]}',
          '{$siguienteConteo}',
          '{$v[8]}',
          'T',
          '1');
          ";
        $query = mysqli_query($conn, $sql);
      }
      foreach($datos_ultimoConteo as $k=>$v)
      {
        $sql = 
          "UPDATE `ts_existenciapiezas` 
          SET 
            `Existencia`='{$v[6]}'
          WHERE cve_articulo = '{$v[4]}'
          AND idy_ubica = '{$v[3]}'";
        $query = mysqli_query($conn, $sql);
      }
      $sql = "UPDATE `t_invpiezas` SET `fecha_fin`= NOW() WHERE ID_Inventario = '{$inventario}' AND NConteo = '{$siguienteConteo}'";
      $query = mysqli_query($conn, $sql);
      $this->response(200, [
        'ultimoConteo' => $datos_ultimoConteo,
        'conteo' => $siguienteConteo,
        'status' => 200
      ]);
    }

    /**
     * Undocumented function
     *     * @return void
     */


    public function guardarStockFisico()
    {
        $stocks = $_POST['conteos'];
        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        $cerrar_inventario = false;
        $fech = date('Y-m-d');

        // Verificar si existe el conteo si no se crea
        ob_clean();
        list($tipo, $ubicacion, $articulo, $lote, $inventario, $conteo, $user) = explode('|',$stocks[0]['id']);
        $count = Capsule::table('t_conteoinventario')
                            ->where('ID_Inventario', $inventario)
                            ->where('NConteo', $conteo)
                            ->count();

        if($count< 1){
          if($user == '-') $user = '';
            Capsule::table('t_conteoinventario')
                        ->insert([
                            'ID_Inventario' => $inventario,
                            'NConteo' => $conteo+1,
                            'cve_usuario' => $user,
                            'Status' => 'A',
                            'Activo' => '1',
                            'cve_usuario' => NULL
                        ]);
        }

        $rastreo = "count = ".$count."\n";
        foreach ($stocks as $key => $value) {        

            list($tipo, $ubicacion, $articulo, $lote, $inventario, $conteo, $user) = explode('|',$value['id']);

            $rastreo .= "tipo = ".$tipo." - ubicacion = ".$ubicacion." - articulo = ".$articulo." - lote = ".$lote." - inventario = ".$inventario." - conteo = ".$conteo." - user = ".$user." - value = ".$value['value']."\n";

            $stock_fisico = $value['value'];
    
            $sql = "SELECT * FROM t_invpiezas  WHERE ID_Inventario = {$inventario} AND NConteo = {$conteo} AND idy_ubica = '{$ubicacion}' AND  cve_articulo = '{$articulo}' AND cve_lote = '{$lote}'";
            //$rastreo .= "SQL1 = ".$sql."\n";
            $query = mysqli_query($conn, $sql);        
            $cua = mysqli_num_rows($query);
            //$rastreo .= "cua = ".$cua."\n";

            if($user == '-') $user = '';

            if($cua>0){
                $conteo_mas = $conteo + 1;
                $cantidad = 0;
                if($conteo > 0)
                  $cantidad = $value['value'];
                $sql = "UPDATE t_invpiezas SET 
                            Cantidad = {$cantidad}
                        WHERE 
                            ID_Inventario = {$inventario} AND 
                            NConteo = {$conteo} AND 
                            cve_articulo = '{$articulo}' AND 
                            idy_ubica = '{$ubicacion}'; ";
                $query = mysqli_query($conn, $sql);
            }
            else {
                $sql = "INSERT INTO t_invpiezas (Cantidad,ID_Inventario,NConteo,cve_articulo, cve_lote,idy_ubica,cve_usuario,fecha,Activo) VALUES ({$stock_fisico},{$inventario},{$conteo},'{$articulo}','{$lote}',{$ubicacion},'{$user}','{$fech}','1');";
                //$rastreo .= "SQL2 = ".$sql."\n";
                $query = mysqli_query($conn, $sql);
            }

            //Chequear status del inventario
            /*
            $sql = "SELECT 
                        SUM(ABS(ExistenciaTeorica - Cantidad)) AS diferencia, 
                        NConteo, 
                        ID_Inventario 
                    FROM t_invpiezas 
                    WHERE 
                        ID_Inventario = {$inventario} AND 
                        NConteo > 0  
                    GROUP BY ID_Inventario, NConteo";    
            */
            $sql = "SELECT COUNT(DISTINCT cve_articulo) AS cuenta FROM t_invpiezas WHERE ID_Inventario = {$inventario}";
            $query = mysqli_query($conn, $sql);
            $data = mysqli_fetch_assoc($query);
            $num_productos = $data['cuenta'];

            $sql_cerrar = "SELECT DISTINCT c.idy_ubica, c.cve_articulo, c.cve_lote, c.Cantidad FROM t_invpiezas c WHERE ID_Inventario = {$inventario} AND (SELECT COUNT(*) FROM t_invpiezas a WHERE Cantidad = c.Cantidad AND ID_Inventario = {$inventario} AND ((SELECT COUNT(*) FROM t_invpiezas a WHERE (a.Cantidad = c.Cantidad) AND a.ID_Inventario = {$inventario} AND c.cve_articulo = a.cve_articulo AND c.cve_lote = a.cve_lote) = 2 OR (c.Cantidad = c.ExistenciaTeorica))) AND c.Cantidad > 0 ORDER BY cve_articulo";
            $query = mysqli_query($conn, $sql_cerrar);

            $cerrar_inventario = false;
            if($query->num_rows == $num_productos)
            {
              $cerrar_inventario = true;
              while($row_data = mysqli_fetch_assoc($query))
              {
                $idy_ubica    = $row_data["idy_ubica"];
                $cve_articulo = $row_data["cve_articulo"];
                $cve_lote     = $row_data["cve_lote"];
                $Cantidad     = $row_data["Cantidad"];

                $sql = "UPDATE ts_existenciapiezas SET Existencia = {$Cantidad} WHERE idy_ubica = '{$idy_ubica}' AND cve_articulo = '{$cve_articulo}' AND cve_lote = '{$cve_lote}'";
                $res = mysqli_query($conn, $sql);
              }
            }
              /*
            if($query->num_rows > 0){

                $data = mysqli_fetch_all($query, MYSQLI_ASSOC);
                $cerrar_inventario = false;
                
                if(count($data) === 1){
                    if(intval($data[0]['diferencia']) === 0){
                        $cerrar_inventario = true;
                    }
                } 
                else {
                    $lastIndex = count($data) -1;
                    if(intval($data[$lastIndex]['diferencia']) === 0){
                        $cerrar_inventario = true;
                    }
                    else{
                        $diferencias = array();
                        foreach($data as $diff){
                            $diferencias [] = intval($diff['diferencia']);
                        }
                        $diferencias = array_count_values($diferencias);
                        arsort($diferencias);
                        if(array_shift($diferencias) > 1){
                            $cerrar_inventario = true;
                        }
                    }
                } 
                if($cerrar_inventario){
                    $sql = "UPDATE det_planifica_inventario SET Status = 'T' WHERE ID_PLAN = {$inventario}";
                    mysqli_multi_query($conn, $sql);
                }
            }
            */
            
        }
    
    
        $this->response(200, [
            //'rastreo' => $rastreo,
            //'num_productos' => $num_productos,
            //'sql_cerrar' => $sql_cerrar,
            'cerrado' => $cerrar_inventario,
            'status' => 200
        ]);
    }

    public function guardarStockCiclico()
    {
        $stocks = $_POST['conteos'];
        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        $cerrar_inventario = false;
        $fech = date('Y-m-d');

        // Verificar si existe el conteo si no se crea
        ob_clean();
        list($tipo, $ubicacion, $articulo, $lote, $inventario, $conteo, $user) = explode('|',$stocks[0]['id']);
        $count = Capsule::table('t_conteoinventariocicl')
                            ->where('ID_PLAN', $inventario)
                            ->where('NConteo', $conteo)
                            ->count();

        if($count< 1){
          if($user == '-') $user = '';
            Capsule::table('t_conteoinventariocicl')
                        ->insert([
                            'ID_PLAN' => $inventario,
                            'NConteo' => $conteo+1,
                            'cve_usuario' => $user,
                            'Status' => 'A',
                            'Activo' => '1',
                            'cve_usuario' => NULL
                        ]);
        }

        $rastreo = "count = ".$count."\n";
        foreach ($stocks as $key => $value) {        

            list($tipo, $ubicacion, $articulo, $lote, $inventario, $conteo, $user) = explode('|',$value['id']);

            $rastreo .= "tipo = ".$tipo." - ubicacion = ".$ubicacion." - articulo = ".$articulo." - lote = ".$lote." - inventario = ".$inventario." - conteo = ".$conteo." - user = ".$user." - value = ".$value['value']."\n";

            $stock_fisico = $value['value'];
    
            $sql = "SELECT * FROM t_invpiezasciclico  WHERE ID_PLAN = {$inventario} AND NConteo = {$conteo} AND idy_ubica = '{$ubicacion}' AND  cve_articulo = '{$articulo}' AND cve_lote = '{$lote}'";
            //$rastreo .= "SQL1 = ".$sql."\n";
            $query = mysqli_query($conn, $sql);        
            $cua = mysqli_num_rows($query);
            //$rastreo .= "cua = ".$cua."\n";

            if($user == '-') $user = '';

            if($cua>0){
                $conteo_mas = $conteo + 1;
                $cantidad = $value['value'];
                $sql = "UPDATE t_invpiezasciclico SET 
                            Cantidad = {$cantidad}
                        WHERE 
                            ID_PLAN = {$inventario} AND 
                            NConteo = {$conteo} AND 
                            cve_articulo = '{$articulo}' AND 
                            idy_ubica = '{$ubicacion}'; ";
                $query = mysqli_query($conn, $sql);
            }
            else {
                $sql = "INSERT INTO t_invpiezasciclico (Cantidad,ID_PLAN,NConteo,cve_articulo, cve_lote,idy_ubica,cve_usuario,fecha,Activo) VALUES ({$stock_fisico},{$inventario},{$conteo},'{$articulo}','{$lote}',{$ubicacion},'{$user}','{$fech}','1');";
                //$rastreo .= "SQL2 = ".$sql."\n";
                $query = mysqli_query($conn, $sql);
            }

            //Chequear status del inventario
            $sql = "SELECT 
                        SUM(ABS(ExistenciaTeorica - Cantidad)) AS diferencia, 
                        NConteo, 
                        ID_PLAN 
                    FROM t_invpiezasciclico 
                    WHERE 
                        ID_PLAN = {$inventario} AND 
                        NConteo > 0  
                    GROUP BY ID_PLAN, NConteo";    
            $query = mysqli_query($conn, $sql);

            if($query->num_rows > 0){

                $data = mysqli_fetch_all($query, MYSQLI_ASSOC);                
                $cerrar_inventario = false;
                
                if(count($data) === 1){
                    if(intval($data[0]['diferencia']) === 0){
                        $cerrar_inventario = true;
                    }
                } 
                else {
                    $lastIndex = count($data) -1;
                    if(intval($data[$lastIndex]['diferencia']) === 0){
                        $cerrar_inventario = true;
                    }
                    else{
                        $diferencias = array();
                        foreach($data as $diff){
                            $diferencias [] = intval($diff['diferencia']);
                        }
                        $diferencias = array_count_values($diferencias);
                        arsort($diferencias);
                        if(array_shift($diferencias) > 1){
                            $cerrar_inventario = true;
                        }
                    }
                } 
                if($cerrar_inventario){
                    $sql = "UPDATE det_planifica_inventario SET Status = 'T' WHERE ID_PLAN = {$inventario}";
                    mysqli_multi_query($conn, $sql);
                }
            }
            
        }
    
    
        $this->response(200, [
            'rastreo' => $rastreo,
            'cerrado' => $cerrar_inventario,
            'status' => 200
        ]);
    }


    /**
     * Undocumented function
     *
     * @return void
     */
    public function asignarSupervisor()
    {
      $user = $this->getPost('user');
      $password = $this->getPost('password');
      $inventario = $this->getPost('inventario');
      $stocks = $this->getPost('conteos');
      $tipo = $this->getPost('tipo_inventario');
      $coincidencide_supervisor = false;

      $data = Usuarios::where('cve_usuario', $user)
              ->where('pwd_usuario', $password)
              ->get(['id_user']);//Trae el id del usuario si coinciden sus accesos. 
      if( count($data) < 1 )
      {
        $this->response(400, [
          'statusText' => 'Combinación de usuario y contraseña incorrecta',
          'request' => $_POST
        ]);
      }
      $usuario = $data[0];
      $id_user = $usuario->id_user;
      if($tipo == 'fisico')
      {
        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        $sql="SELECT t_conteoinventario.cve_usuario FROM `t_conteoinventario` WHERE ID_Inventario = '{$inventario}' AND t_conteoinventario.cve_usuario!='' GROUP BY cve_usuario";
        $query = mysqli_query($conn, $sql);
        $claves_usuarios_conteos = mysqli_fetch_all($query);
        $sql="SELECT c_usuario.cve_usuario FROM `c_usuario` WHERE id_user = '{$id_user}'";
        $query = mysqli_query($conn, $sql);
        $clave_supervisor = mysqli_fetch_array($query)["cve_usuario"];
        foreach($claves_usuarios_conteos as $key => $value)
        {
          if($value[0] == $clave_supervisor)
          {
            $coincidencide_supervisor = true;
          }
        }
        Capsule::table('t_conteoinventario')
        ->where('ID_Inventario', $inventario)
        ->update(['cve_supervisor' => $clave_supervisor]);

        Capsule::table('th_inventario')
        ->where('ID_Inventario', $inventario)
        ->update(['Status' => 'T']);

        foreach ($stocks as $key => $value) 
        {
          list($tipo, $ubicacion, $articulo, $inventario, $conteo) = explode('|',$value['id']);
          /*$sql = "
            UPDATE t_invpiezas SET 
              ExistenciaTeorica = Cantidad
            WHERE 
              ID_Inventario = {$inventario} 
              AND cve_articulo = '{$articulo}' 
              AND idy_ubica = '{$ubicacion}' 
              AND NConteo = (SELECT MAX(NConteo) 
                              FROM t_conteoinventario 
                              WHERE ID_Inventario = {$inventario})";
          mysqli_multi_query($conn, $sql);*/
        }
      }
      else 
      {
        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        $sql="SELECT t_conteoinventariocicl.cve_usuario FROM `t_conteoinventariocicl` WHERE ID_PLAN = '{$inventario}' GROUP BY cve_usuario";
        $query = mysqli_query($conn, $sql);
        $claves_usuarios_conteos = mysqli_fetch_all($query);
        $sql="SELECT c_usuario.cve_usuario FROM `c_usuario` WHERE id_user = '{$id_user}'";
        $query = mysqli_query($conn, $sql);
        $clave_supervisor = mysqli_fetch_array($query)["cve_usuario"];
        foreach($claves_usuarios_conteos as $key => $value)
        {
          if($value[0] == $clave_supervisor)
          {
            $coincidencide_supervisor = true;
          }
        }
        Capsule::table('t_conteoinventariocicl')
        ->where('ID_PLAN', $inventario)
        ->update(['cve_usuario' => $id_user ]);

        Capsule::table('th_inventario')
        ->where('ID_Inventario', $inventario)
        ->update(['Status' => 'T']);

        foreach ($stocks as $key => $value) 
        {
          list($tipo, $ubicacion, $articulo, $inventario, $conteo) = explode('|',$value['id']);
          /*$sql = "
            UPDATE t_invpiezas SET 
              ExistenciaTeorica = Cantidad
            WHERE 
              ID_Inventario = {$inventario} 
              AND cve_articulo = '{$articulo}' 
              AND idy_ubica = '{$ubicacion}' 
              AND NConteo = (SELECT MAX(NConteo) 
                              FROM t_conteoinventario 
                              WHERE ID_Inventario = {$inventario})";
          mysqli_multi_query($conn, $sql);*/
        }
      }
      if($coincidencide_supervisor)
      {
        $this->response(400, [
                'statusText' => 'El usuario que intenta asignar, ya fue asignado en los conteos',
                'request' => $_POST
              ]);
      }
      
      $this->response(200, [
          'statusText' => 'Inventario cerrado y supervisor asignado con éxito',
          'request' => $_POST
      ]);
    }


    public function existenciasPorUbicaciones()
    {
        $articulo = $this->getInput('articulo');
        $almacen = $this->getInput('almacen');

        $almacen = AlmacenP::where('clave', $almacen)->get(['id']);
        $almacen = $almacen[0];
        $almacen_id = $almacen->id;

        $data = Capsule::select
                (Capsule::raw(
            "SELECT DISTINCT
                u.cve_almac id,
                u.CodigoCSD ubicacion,
                e.cve_articulo cve_articulo,
                e.cve_lote lote,
                IF(art.Caduca = 'S', DATE_FORMAT(l.Caducidad, '%d-%m-%Y'), '') AS caducidad,
                e.Existencia cantidad
            FROM ts_existenciapiezas e
                LEFT JOIN c_articulo art ON art.cve_articulo = e.cve_articulo
                LEFT JOIN c_ubicacion u ON e.idy_ubica = u.idy_ubica
                LEFT JOIN c_almacen a ON e.idy_ubica = a.cve_almac 
                LEFT JOIN c_lotes l ON l.Lote = e.cve_lote
            WHERE e.cve_almac = '{$almacen_id}' AND e.cve_articulo = '{$articulo}'AND e.Existencia > 0"
            )
        );

        $rows = [];
        $count = 0;
        foreach( $data as $value)
        {
            $rows[$count]['cell'] = [
                $value->id,
                $value->ubicacion,
                $value->cve_articulo,
                $value->lote, 
                $value->caducidad, 
                $value->cantidad, 
            ];
            $count++;
        }

        $response = $this->responseJQGrid($rows);
        ob_clean();
        echo json_encode($response);exit;

    }


    /**
     * Importar artículos al inventario mediante un archov de excel
     *
     * @return void
     */
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
            $element = ArticulosGrupos::where('cve_gpoart', '=', $clave)->first();

            if($element != NULL){
                $model = $element; 
            }
            else {
                $model = new ArticulosGrupos(); 
            }
            
            $model->cve_gpoart  = $clave;
            $model->des_gpoart  = $this->pSQL($row[self::DESCRIPCION]);
            $model->save();
            $linea++;
        }
        
        @unlink($file);

        $this->response(200, [
            'statusText' =>  "Lotes importados con exito. Total de Lotes: \"{$linea}\"",
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

    public function exportar_concentrado()
    {

//***************************************************************************************************
    $almacen = $_GET['almacen'];
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql="
        SELECT  
            (SELECT 
                fol_folio 
            FROM td_entalmacen 
            WHERE cve_articulo = v.cve_articulo 
                AND cve_lote = v.cve_lote 
            LIMIT 1) AS folio,
            IFNULL(
                (SELECT 
                    Nombre 
                 FROM c_proveedores 
                 WHERE ID_Proveedor = (
                    SELECT 
                        ID_Proveedor 
                    FROM th_aduana 
                    WHERE num_pedimento = (SELECT folio))),'') AS proveedor,
            v.cve_articulo AS articulo,
            IFNULL(a.des_articulo, '--') AS nombre,
            a.cajas_palet cajasxpallets,
            a.num_multiplo piezasxcajas, 
            0 as Pallet,
            0 as Caja, 
            0 as Piezas,
            SUM(v.Existencia) AS existencia
        FROM V_ExistenciaGralProduccion v
            LEFT JOIN c_articulo a ON a.cve_articulo = v.cve_articulo
            LEFT JOIN c_proveedores p ON p.ID_Proveedor = a.ID_Proveedor
            LEFT JOIN c_almacenp on c_almacenp.id = v.cve_almac
        WHERE v.Existencia > 0
            AND v.`tipo`='ubicacion'
            AND c_almacenp.clave = '".$almacen."'
        GROUP BY v.cve_articulo
    ";
    $res = "";
    if (!($res = mysqli_query($conn, $sql))) 
    {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

//***************************************************************************************************

        $columnas = [
          'Proveedor',
          'Clave',
          utf8_decode('Descripción'),
          'Pallet',
          'Caja',
          'Piezas',
          'Existencia'
        ];

        $filename = "Concentrado de Existencias" . ".xls";
        ob_clean();

        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");

        foreach($columnas as $column) 
        {            
            echo $column . "\t" ;            
        }
        print("\r\n");

        while($row = mysqli_fetch_object($res))
        {
            $Nserie = "";
            $NLote = "";
        //**************************************************
        //FUNCION PARA LAS COLUMNAS PALLET | CAJAS | PIEZAS
        //**************************************************
        //Archivos donde se encuentra esta función:
        //api\embarques\lista\index.php
        //api\reportes\lista\existenciaubica.php
        //api\reportes\lista\concentradoexistencia.php
        //app\template\page\reportes\existenciaubica.php
        //\Application\Controllers\EmbarquesController.php
        //\Application\Controllers\InventarioController.php
        //**************************************************
        //clave busqueda: COLPCP
        //**************************************************

            $valor1 = 0;
            if($row->piezasxcajas > 0)
               $valor1 = $row->existencia/$row->piezasxcajas;

            if($row->cajasxpallets > 0)
               $valor1 = $valor1/$row->cajasxpallets;
           else
               $valor1 = 0;

            $Pallet = intval($valor1);

            $valor2 = 0;
            $cantidad_restante = $row->existencia - ($Pallet*$row->piezasxcajas*$row->cajasxpallets);
            if(!is_int($valor1) || $valor1 == 0)
            {
                if($row->piezasxcajas > 0)
                   $valor2 = ($cantidad_restante/$row->piezasxcajas);// - ($Pallet*$existencia);
            }
            $Cajas = intval($valor2);

            $Piezas = 0;
            if($row->piezasxcajas == 1) 
            {
                $valor2 = 0; 
                $Cajas = $cantidad_restante;
                $Piezas = 0;
            }
            else if($row->piezasxcajas == 0 || $row->piezasxcajas == "")
            {
                if($piezasxcajas == "") $piezasxcajas = 0;
                $valor2 = 0; 
                $Cajas = 0;
                $Piezas = $cantidad_restante;
            }
            $cantidad_restante = $cantidad_restante - ($Cajas*$row->piezasxcajas);

            if(!is_int($valor2))
            {
               //$Piezas = ($Cajas*$cantidad_restante) - $piezasxcajas;
                $Piezas = $cantidad_restante;
            }
        //**************************************************

            echo $row->proveedor . "\t";
            echo $row->articulo . "\t";
            echo $row->nombre . "\t";
            echo $Pallet . "\t";
            echo $Cajas . "\t";
            echo $Piezas . "\t";
            echo $row->existencia . "\t";
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

    public function exportar_existenciaubica()
    {

//***************************************************************************************************
    $almacen = $_GET['almacen'];
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
/*
    $sql="
        SELECT
            ap.nombre as almacen,
            z.des_almac as zona,
            u.CodigoCSD as codigo,
            a.cve_articulo as clave,
            a.des_articulo as descripcion,
            COALESCE(l.LOTE, '--') as lote,
            COALESCE(l.CADUCIDAD, '--') as caducidad,
            COALESCE(s.numero_serie, '--') as nserie,
            e.Existencia as cantidad,
            (SELECT fol_folio FROM td_entalmacen WHERE cve_articulo = e.cve_articulo AND cve_lote = e.cve_lote LIMIT 1) AS folio,
            COALESCE((SELECT Nombre FROM c_proveedores WHERE ID_Proveedor = (SELECT ID_Proveedor FROM th_aduana WHERE num_pedimento = (SELECT folio))),'--') AS proveedor, 
            COALESCE((SELECT DATE_FORMAT(fecha_fin, '%d-%m-%Y') FROM td_entalmacen WHERE cve_articulo = e.cve_articulo AND cve_lote = e.cve_lote ORDER BY id DESC LIMIT 1), '--') AS fecha_ingreso,
            CONCAT(CASE
                        WHEN u.Tipo = 'L' THEN 'Libre'
                        WHEN u.Tipo = 'R' THEN 'Reservada'
                        WHEN u.Tipo = 'Q' THEN 'Cuarentena'
                    END, '| Picking ',
                    CASE 
                        WHEN u.Picking = 'S' THEN '✓'
                        WHEN u.Picking = 'N' THEN '✕'
                    END
             ) AS tipo_ubicacion,
             a.costoPromedio as costoPromedio,
             a.costoPromedio*e.Existencia as subtotalPromedio,
            (SELECT SUM(a.costoPromedio*e.Existencia) FROM V_ExistenciaGralProduccion e LEFT JOIN c_articulo a ON a.cve_articulo = e.cve_articulo) as importeTotalPromedio,
            (SELECT BL FROM c_almacenp WHERE id = '$almacen' LIMIT 1) AS codigo_BL
      FROM V_ExistenciaGralProduccion e
          LEFT JOIN c_articulo a ON a.cve_articulo = e.cve_articulo
          LEFT JOIN c_ubicacion u ON u.idy_ubica = e.cve_ubicacion
          LEFT JOIN c_lotes l ON l.LOTE = e.cve_lote AND l.cve_articulo = e.cve_articulo
          LEFT JOIN c_almacen z ON z.cve_almac = (SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = e.cve_ubicacion)
          LEFT JOIN c_almacenp ap ON ap.id = z.cve_almacenp
          LEFT JOIN c_serie s ON s.cve_articulo = e.cve_articulo
      WHERE e.cve_almac = '$almacen' AND e.tipo = 'ubicacion' AND e.Existencia > 0 
    ";
*/
      $sql = "SELECT * FROM(
         SELECT
            IFNULL(ap.nombre, '') as almacen,
            IFNULL(z.des_almac, '') as zona,
            IFNULL(u.CodigoCSD, '') as codigo,
            if(e.Cuarentena = 1, 'Si','No') as QA,
            IFNULL(e.Cve_Contenedor, '') as contenedor,
            IFNULL(ch.CveLP, '') AS LP,
            a.cve_articulo as clave,
            a.des_articulo as descripcion,
            a.cajas_palet cajasxpallets,
            a.num_multiplo piezasxcajas, 
            0 as Pallet,
            0 as Caja, 
            0 as Piezas,
            COALESCE(if(a.control_lotes ='S',l.LOTE,''), '--') as lote,
            IFNULL(COALESCE(if(a.control_lotes = 'S',if(l.Caducidad = '0000-00-00','',date_format(l.Caducidad,'%d-%m-%Y')),'')), '') as caducidad,
            COALESCE(if(a.control_numero_series = 'S',e.cve_lote,'' ), ' ') as nserie,
            e.Existencia as cantidad,
            (SELECT fol_folio FROM td_entalmacen WHERE cve_articulo = e.cve_articulo AND cve_lote = e.cve_lote LIMIT 1) AS folio,
            #COALESCE((SELECT Nombre FROM c_proveedores WHERE ID_Proveedor = (SELECT ID_Proveedor FROM th_aduana WHERE num_pedimento = (SELECT folio))),'--') AS proveedor, 
            IFNULL(
                (select nombre from c_proveedores where ID_Proveedor = (
                    IFNULL(
                        (select ID_Proveedor from ts_existenciapiezas where ts_existenciapiezas.idy_ubica = e.cve_ubicacion and ts_existenciapiezas.cve_articulo = e.cve_articulo limit 1),
                        IFNULL(
                            (select ID_Proveedor from ts_existenciacajas where ts_existenciacajas.idy_ubica = e.cve_ubicacion and ts_existenciacajas.cve_articulo = e.cve_articulo limit 1),
                            IFNULL(
                                (select ID_Proveedor from ts_existenciatarima where ts_existenciatarima.idy_ubica = e.cve_ubicacion and ts_existenciatarima.cve_articulo = e.cve_articulo limit 1),
                                0
                            )
                        )
                    )
                )),'--'
            )as proveedor,
            COALESCE((SELECT DATE_FORMAT(fecha_fin, '%d-%m-%Y') FROM td_entalmacen WHERE cve_articulo = e.cve_articulo AND cve_lote = e.cve_lote ORDER BY id DESC LIMIT 1), '--') AS fecha_ingreso,
                CASE 
                    WHEN u.Picking = 'S' THEN 'Si'
                    WHEN u.Picking = 'N' THEN 'No'
                END
            AS tipo_ubicacion,
            truncate(a.costoPromedio,2) as costoPromedio,
            truncate(a.costoPromedio*e.Existencia,2) as subtotalPromedio
            FROM
                V_ExistenciaGralProduccion e
            LEFT JOIN c_articulo a ON a.cve_articulo = e.cve_articulo
            LEFT JOIN c_ubicacion u ON u.idy_ubica = e.cve_ubicacion
            LEFT JOIN c_lotes l ON l.LOTE = e.cve_lote AND l.cve_articulo = e.cve_articulo
            LEFT JOIN c_almacen z ON z.cve_almac = (SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = e.cve_ubicacion)
            LEFT JOIN c_almacenp ap ON ap.id = z.cve_almacenp
            LEFT JOIN c_charolas ch ON ch.clave_contenedor = e.Cve_Contenedor
                WHERE e.cve_almac = '{$almacen}'  AND e.tipo = 'ubicacion' AND e.Existencia > 0  {$sqlArticulo} {$sqlZona}
            order by l.CADUCIDAD ASC
                )x
            where 1";

    $res = "";
    if (!($res = mysqli_query($conn, $sql))) 
    {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

//***************************************************************************************************

        $columnas = [
            'Codigo BL',
            'Pallet|Cont',
            'License Plate (LP)',
            'Clave',
            utf8_decode('Descripción'),
            'Lote',
            'Caducidad',
            'N. Serie',
            'Pallet',
            'Caja',
            'Piezas',
            'Stock Pzas'
        ];

        $filename = "Reporte de Existencias" . ".xls";
        ob_clean();

        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");

        foreach($columnas as $column) 
        {            
            echo $column . "\t" ;            
        }
        print("\r\n");

        while($row = mysqli_fetch_object($res))
        {
            $Nserie = "";
            $NLote = "";

            $valor1 = 0;
            if($row->piezasxcajas > 0)
               $valor1 = $row->cantidad/$row->piezasxcajas;

            if($row->cajasxpallets > 0)
               $valor1 = $valor1/$row->cajasxpallets;
           else
               $valor1 = 0;

            $Pallet = intval($valor1);

            $valor2 = 0;
            $cantidad_restante = $row->cantidad - ($Pallet*$row->piezasxcajas*$row->cajasxpallets);
            if(!is_int($valor1) || $valor1 == 0)
            {
                if($row->piezasxcajas > 0)
                   $valor2 = ($cantidad_restante/$row->piezasxcajas);// - ($Pallet*$cantidad);
            }
            $Cajas = intval($valor2);

            $Piezas = 0;
            if($row->piezasxcajas == 1 || $piezasxcajas == 0 || $piezasxcajas == "") 
            {
                if($piezasxcajas == "") $piezasxcajas = 0;
                $valor2 = 0; 
                $Caja = $cantidad_restante;
                $Piezas = 0;
            }
            $cantidad_restante = $cantidad_restante - ($Cajas*$row->piezasxcajas);

            if(!is_int($valor2))
            {
               //$Piezas = ($Cajas*$cantidad_restante) - $piezasxcajas;
                $Piezas = $cantidad_restante;
            }

            echo $row->codigo . "\t";
            echo $row->contenedor . "\t";
            echo $row->LP . "\t";
            echo $row->clave . "\t";
            echo $row->descripcion . "\t";
            echo $row->lote . "\t";
            echo $row->caducidad . "\t";
            echo $row->nserie . "\t";
            echo $Pallet . "\t";
            echo $Caja . "\t";
            echo $Piezas . "\t";
            echo $row->cantidad . "\t";
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
 