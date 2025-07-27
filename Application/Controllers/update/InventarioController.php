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
//set_include_path( get_include_path().PATH_SEPARATOR."../..");
//include_once("xlsxwriter/xlsxwriter.class.php");

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
        $almacen   = $this->getInput('almacen', $_SESSION['cve_almacen']);
        $busq   = $this->getInput('busq', '');

        $fechai= $this->getInput('fechaInicio', '');
        $fechaf= $this->getInput('fechaFin', '');

        $fechai = !empty($fechai) ? date('d-m-Y', strtotime($fechai)) : '';
        $fechaf = !empty($fechaf) ? date('d-m-Y', strtotime($fechaf)) : '';

        // se conecta a la base de datos
        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        mysqli_set_charset($conn, 'utf8');
    
        $SQLBusq = "";
        $SQLBusq2 = "";

      $start = $limit * $page - $limit; // do not put $limit*($page - 1) 
      $count = 0;

    $sql = "SELECT DATE_FORMAT(DATE_ADD(CURDATE(), INTERVAL -7 DAY), '%d-%m-%Y') AS fecha_semana FROM DUAL";
    if (!($res = mysqli_query($conn, $sql))) {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }
    $row = mysqli_fetch_array($res);
    $fecha_semana = $row['fecha_semana'];

    //$SQLFechaSemana = " WHERE W.fecha_inicio >= STR_TO_DATE('$fecha_semana', '%d-%m-%Y') "; 

        if($busq) 
        {

            $SQLBusq  = " AND inv.ID_Inventario = '{$busq}' ";
            $SQLBusq2 = " AND d.ID_PLAN = '{$busq}' ";
            $SQLFechaSemana = "";
        }



        $aditionalSearch  = "";
        $aditionalSearch2 = "";
        if($busq == '')
        {
            if(!empty($fechai) && !empty($fechaf)){
                if($fechai === $fechaf){
                  //$aditionalSearch .= " AND DATE_FORMAT(th_entalmacen.Fec_Entrada, '%d-%m-%Y %I:%i:%s %p') like '%$fechai%'";
                    $aditionalSearch  .= " AND inv.Fecha like '%STR_TO_DATE('$fechai', '%d-%m-%Y')%' ";
                    $aditionalSearch2 .= " AND cab.FECHA_INI like '%STR_TO_DATE('$fechai', '%d-%m-%Y')%' ";
                }else{
                  //$aditionalSearch .= " AND DATE_FORMAT(th_entalmacen.Fec_Entrada, '%d-%m-%Y %I:%i:%s %p') BETWEEN '$fechai' AND '$fechaf'";
                    $aditionalSearch  .= " AND inv.Fecha BETWEEN STR_TO_DATE('$fechai', '%d-%m-%Y') AND STR_TO_DATE('$fechaf', '%d-%m-%Y') ";
                    $aditionalSearch2 .= " AND cab.FECHA_INI BETWEEN STR_TO_DATE('$fechai', '%d-%m-%Y') AND STR_TO_DATE('$fechaf', '%d-%m-%Y') ";
                }
            }
            else{
                if(!empty($fechai)){
                    //buscar por fecha mayor
                    //$aditionalSearch .= " AND DATE_FORMAT(th_entalmacen.Fec_Entrada, '%d-%m-%Y %I:%i:%s %p') >= '$fechai'";
                    $aditionalSearch  .= " AND inv.Fecha >= STR_TO_DATE('$fechai', '%d-%m-%Y') ";
                    $aditionalSearch2 .= " AND cab.FECHA_INI >= STR_TO_DATE('$fechai', '%d-%m-%Y') ";
                }
                if(!empty($fechaf)){
                    //buscar por fecha menor
                    //$aditionalSearch .= " AND DATE_FORMAT(th_entalmacen.Fec_Entrada, '%d-%m-%Y %I:%i:%s %p') <= '$fechaf'";
                    $aditionalSearch  .= " AND inv.Fecha <= STR_TO_DATE('$fechaf', '%d-%m-%Y') ";
                    $aditionalSearch2 .= " AND cab.FECHA_INI <= STR_TO_DATE('$fechaf', '%d-%m-%Y') ";
                }
            }
        }

        $utf8Sql = "SET NAMES 'utf8mb4';";
        $res_charset = mysqli_query($conn, $utf8Sql);

        $sql = "SELECT * FROM(SELECT 
                    inv.ID_Inventario AS consecutivo,
                    DATE_FORMAT(COALESCE(MIN(v_inv.fecha), inv.Fecha),'%d-%m-%Y %H:%i:%s') AS fecha_inicio,
                    (CASE 
                        WHEN inv.`Status` = 'T' OR inv.`Status` = 'O' THEN IFNULL(DATE_FORMAT(COALESCE(MAX(v_inv.fecha), inv.Fecha),'%d-%m-%Y %H:%i:%s'),'--')
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
                
#                    IF(inv.status = 'T',IFNULL((
#                        SELECT 
#                        SUM(ABS(ExistenciaTeorica - Cantidad))
#                        FROM t_invpiezas
#                        WHERE ID_Inventario = inv.ID_Inventario
#                        #AND t_invpiezas.NConteo = (Select max(t_invpiezas.NConteo) from t_invpiezas where t_invpiezas.ID_Inventario = inv.ID_Inventario)
#                            GROUP BY ID_Inventario
                    #
#                    ), '--'),0) AS diferencia,
                    '' AS diferencia,
                    
#                    if(inv.status = 'T',ROUND(
#                      IFNULL((
#                        SELECT 
#                        ((sum(ExistenciaTeorica) - (if(ExistenciaTeorica-Cantidad < 0,((ExistenciaTeorica-Cantidad)*-1),(ExistenciaTeorica-Cantidad))))/sum(ExistenciaTeorica))*100 as Porcentaje
#                        FROM t_invpiezas
#                        WHERE ID_Inventario = inv.ID_Inventario
#                        AND t_invpiezas.NConteo = (Select max(t_invpiezas.NConteo) from t_invpiezas where t_invpiezas.ID_Inventario = inv.ID_Inventario)
#                            GROUP BY ID_Inventario
#                    ), '--'), 2),0) AS porcentaje,
                    '' AS porcentaje,

                    '' AS tipo_ubic,
                    
                    #IF(inv.Inv_Inicial = 0, 'Físico', 'Inicial') AS tipo, 
                    'Físico' AS tipo, 

                    IFNULL((SELECT 
                        nombre_completo 
                    FROM c_usuario 
                    WHERE cve_usuario = 
                        (SELECT cve_supervisor FROM t_conteoinventario WHERE ID_Inventario = inv.ID_Inventario LIMIT 1)
                    ), '--') AS supervisor,

                    #((SELECT COUNT(DISTINCT idy_ubica) FROM t_invpiezas WHERE ID_Inventario = inv.ID_Inventario AND idy_ubica NOT IN (SELECT idy_ubica FROM t_invtarima WHERE ID_Inventario = inv.ID_Inventario)) + (SELECT COUNT(DISTINCT idy_ubica) FROM t_invtarima WHERE ID_Inventario = inv.ID_Inventario)) AS num_bls,

                    '' AS num_bls,

#                    (
#                        (
#                        SELECT COUNT(*) FROM 
#                        (
#                          SELECT DISTINCT v.ID_Inventario, v.cve_articulo FROM
#                           t_invpiezas v 
#                           WHERE IFNULL(Cantidad, 0) > 0 AND v.NConteo > 0 AND 
#                           v.cve_articulo NOT IN (SELECT cve_articulo FROM t_invtarima WHERE ID_Inventario = v.ID_Inventario)
#                        ) AS i WHERE i.ID_Inventario = inv.ID_Inventario
#                        ) 
#                        + 
#                        (
#                        SELECT COUNT(*) FROM 
#                        (
#                          SELECT DISTINCT v.ID_Inventario, v.cve_articulo FROM
#                           t_invtarima v 
#                           WHERE IFNULL(existencia, 0) > 0 AND v.NConteo > 0 
#                        ) AS i WHERE i.ID_Inventario = inv.ID_Inventario
#                        ) 
#
#                    ) AS num_productos,
                     '' AS num_productos,


                    #((SELECT COUNT(DISTINCT idy_ubica) FROM t_invpiezas WHERE ID_Inventario = inv.ID_Inventario AND NConteo > 0 AND idy_ubica NOT IN (SELECT idy_ubica FROM t_invtarima WHERE ID_Inventario = inv.ID_Inventario AND NConteo > 0)) + (SELECT COUNT(DISTINCT idy_ubica) FROM t_invtarima WHERE ID_Inventario = inv.ID_Inventario AND NConteo > 0)) AS num_bls_cont,
                    '' AS num_bls_cont,

                    #((SELECT COUNT(DISTINCT idy_ubica) FROM t_invpiezas WHERE ID_Inventario = inv.ID_Inventario AND NConteo = 0 AND idy_ubica NOT IN (SELECT idy_ubica FROM t_invpiezas WHERE ID_Inventario = inv.ID_Inventario AND NConteo > 0) AND idy_ubica NOT IN (SELECT idy_ubica FROM t_invtarima WHERE ID_Inventario = inv.ID_Inventario AND NConteo > 0)) + (SELECT COUNT(DISTINCT idy_ubica) FROM t_invtarima WHERE ID_Inventario = inv.ID_Inventario AND NConteo = 0 AND idy_ubica NOT IN (SELECT idy_ubica FROM t_invtarima WHERE ID_Inventario = inv.ID_Inventario AND NConteo > 0) AND idy_ubica NOT IN (SELECT idy_ubica FROM t_invpiezas WHERE ID_Inventario = inv.ID_Inventario AND NConteo > 0))) AS num_bls_vacios,
                    '' AS num_bls_vacios,


                    '' AS num_bls_no_inv,



#                    IFNULL((
#                    IF((SELECT MAX(NConteo) FROM t_invpiezas WHERE ID_Inventario = inv.ID_Inventario) > (SELECT MAX(NConteo) FROM t_invtarima WHERE ID_Inventario = inv.ID_Inventario), (SELECT MAX(NConteo) FROM t_invpiezas WHERE ID_Inventario = inv.ID_Inventario), (SELECT MAX(NConteo) FROM t_invtarima WHERE ID_Inventario = inv.ID_Inventario))
#                    ), 0) AS n_inventario  
                    '' AS n_inventario  

                FROM th_inventario inv
                    LEFT JOIN c_almacenp almac ON inv.cve_almacen = CONVERT(almac.clave USING UTF8)
                    LEFT JOIN c_almacen ON inv.cve_zona = c_almacen.cve_almac
                    LEFT JOIN V_Inventario v_inv ON v_inv.ID_Inventario = inv.ID_Inventario
                WHERE inv.Activo = 1  AND inv.`Status` = '{$status}'
                and almac.clave = '{$almacen}' {$SQLBusq} {$aditionalSearch}
                GROUP BY inv.ID_Inventario
                
                UNION 
                
                SELECT
                    DISTINCT cab.ID_PLAN AS consecutivo, 	
                    DATE_FORMAT(cab.FECHA_INI, '%d-%m-%Y %H:%i:%s') AS fecha_inicio, 	
                    DATE_FORMAT(cab.FECHA_FIN, '%d-%m-%Y %H:%i:%s') AS fecha_final,	
                    ap.nombre AS almacen, 	
                    a.des_articulo AS zona,	
                    (SELECT u.cve_usuario FROM c_usuario u, t_conteoinventariocicl cic WHERE cic.cve_usuario = u.cve_usuario AND cic.ID_PLAN=cab.ID_PLAN LIMIT 1) AS usuario,
                    (CASE 
                    WHEN d.status = 'A' THEN 'Abierto'
                    WHEN d.status = 'T' THEN 'Cerrado'
                    ELSE 'Sin Definir'
                    END) AS status,	
                    
                    #if(d.status = 'T',IFNULL((
                    #    SELECT 
                    #    SUM(ABS(ExistenciaTeorica - Cantidad))
                    #    FROM t_invpiezas
                    #    WHERE ID_Inventario = cab.ID_PLAN
                    #        GROUP BY ID_Inventario
                    #    
                    #), '--'),0) AS diferencia,
                    '' AS diferencia,
                    
#                  if(d.status = 'T',ROUND(
#                    IFNULL((
#                        SELECT 
#                        #((sum(ExistenciaTeorica) - (if(ExistenciaTeorica-Cantidad < 0,((ExistenciaTeorica-Cantidad)*-1),(ExistenciaTeorica-Cantidad))))/sum(ExistenciaTeorica))*100 as Porcentaje
#                        FROM t_invpiezas
#                        WHERE ID_Inventario = cab.ID_PLAN
#                            GROUP BY ID_Inventario
#                    ), '--'), 2),0) AS porcentaje,
                    '' AS porcentaje,
                    
                    '' AS tipo_ubic,
                    
                    'Cíclico' AS tipo,	

                    IFNULL((SELECT 
                        nombre_completo 
                    FROM c_usuario 
                    WHERE id_user = 
                        (SELECT cve_supervisor FROM t_conteoinventariocicl WHERE ID_PLAN = cab.ID_PLAN LIMIT 1)
                    ), '--') AS supervisor,

                    #(SELECT COUNT(DISTINCT num_bl.idy_ubica) FROM (SELECT idy_ubica, ID_PLAN FROM t_invpiezasciclico UNION SELECT idy_ubica, ID_PLAN FROM t_invtarimaciclico ) AS num_bl WHERE num_bl.ID_PLAN = d.ID_PLAN) AS num_bls,
                    '' AS num_bls,
                    #(SELECT COUNT(DISTINCT num_bl.cve_articulo) FROM (SELECT cve_articulo, ID_PLAN FROM t_invpiezasciclico UNION SELECT cve_articulo, ID_PLAN FROM t_invtarimaciclico ) AS num_bl WHERE num_bl.ID_PLAN = d.ID_PLAN) AS num_productos,
                    '' AS num_productos,
                    #(SELECT COUNT(DISTINCT num_bl.idy_ubica) FROM (SELECT idy_ubica, ID_PLAN FROM t_invpiezasciclico WHERE NConteo > 0 UNION SELECT idy_ubica, ID_PLAN FROM t_invtarimaciclico WHERE NConteo > 0) AS num_bl WHERE num_bl.ID_PLAN = d.ID_PLAN) AS num_bls_cont,
                    '' AS num_bls_cont,

                    '' AS num_bls_vacios,
                    '' AS num_bls_no_inv,

                    #IFNULL((
                    #    SELECT 
                    #    IF(MAX(NConteo) < 1, 0, (MAX(NConteo))) conteo
                    #    FROM t_invpiezasciclico
                    #    WHERE ID_PLAN = cab.ID_PLAN
                    #        GROUP BY ID_PLAN
                    #    
                    #), 0) AS n_inventario
                    '' AS n_inventario
                FROM det_planifica_inventario d
                    LEFT JOIN c_articulo a ON d.cve_articulo = a.cve_articulo 
                    LEFT JOIN cab_planifica_inventario cab ON cab.ID_PLAN = d.ID_PLAN
                    LEFT JOIN c_almacenp ap ON cab.id_almacen = ap.id 
                WHERE  d.`Status` = '{$status}' {$SQLBusq2} {$aditionalSearch2} 
                and ap.clave = '{$almacen}'
                ) AS W
                {$SQLFechaSemana}
              ORDER BY STR_TO_DATE(W.fecha_inicio, '%d-%m-%Y %H:%i:%s') DESC";
    
        //echo var_dump($sql);
        //die();
        // hace una llamada previa al procedimiento almacenado Lis_Facturas
        //$query = mysqli_query($conn, $sql);

        $sql_count = "SELECT * FROM th_inventario WHERE cve_almacen = '{$almacen}' AND Status = 'T'";
        if (!($res_count = mysqli_query($conn, $sql_count))) 
        {
          echo "Falló la preparación: (" . mysqli_error($conn) . ") "; exit;
        }
       

      $count = mysqli_num_rows($res_count);

      $sql .= " LIMIT ".$start.", ".$limit;
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
         
/*
          $sql = "CALL SPRP_ReporteInvFis({$consecutivo})";
          if (!($res_num = mysqli_query($conn, $sql))) 
          $num_productos = mysqli_num_rows($res_num);
*/
          $data[$i]['id'] = $consecutivo;
          $data[$i]['cell'] = [
            '',
            $consecutivo,
            $zona, 
            $tipo_ubic,
            $num_bls,
            $num_productos,
            $num_bls_cont,
            $num_bls_vacios,
            //$num_bls_no_inv,
            '',//($num_bls - $num_bls_cont),
            $fecha_inicio, 
            $fecha_final, 
            $supervisor, 
            $diferencia,
            $porcentaje,
            $status, 
            $tipo,
            $n_inventario, 
            $almacen, 
            $efectuado
          ];
          $i++;
        }

      if ($count >0) 
      {
        $total_pages = ceil($count/$limit);
      }
      else 
      {
        $total_pages = 0;
      }

      if ($page > $total_pages) 
      {
        $page = $total_pages;
      }

      //$data["from"] = ($start == 0 ? 1 : $start) ;
      //$data["to"] = ($start + $limit);
      //$data["page"] = $page;
      //$data["total_pages"] = $total_pages;
      //$data["total"] = $count;
      //$data["status"] = 200;
      //$data["sql"] = $sql;

        $response = $this->responseJQGrid($data);

      $response["from"] = ($start == 0 ? 1 : $start) ;
      $response["to"] = ($start + $limit);
      $response["page"] = $page;
      $response["total_pages"] = $total_pages;
      $response["total"] = $count;
      $response["status"] = 200;
      $response["sql"] = $sql;

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
                IFNULL((SELECT nombre_completo FROM c_usuario WHERE cve_usuario = (SELECT DISTINCT cve_usuario FROM t_conteoinventariocicl WHERE ID_PLAN = {$id_inventario} AND NConteo = {$conteo_inv} ORDER BY id DESC LIMIT 1)), '--') as usuario,
                'Piezas' as unidad_medida,
                (SELECT Status FROM th_inventario WHERE ID_inventario = {$id_inventario} LIMIT 1) AS Status,
                CONCAT(v.tipo, '|', v.cve_ubicacion, '|', v.cve_articulo, '|', v.cve_lote, '|', {$id_inventario}, '|', (SELECT conteo), '|', IF((SELECT DISTINCT cve_usuario FROM t_conteoinventariocicl WHERE NConteo = (SELECT conteo) AND ID_PLAN = {$id_inventario}) = '', '-', (SELECT DISTINCT cve_usuario FROM t_conteoinventariocicl WHERE NConteo = (SELECT conteo) AND ID_PLAN = {$id_inventario}))) AS id
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
/* //CON FILTRO DE LOTES
        $sql="
        SELECT DISTINCT 
                c.des_almac zona,
                v.cve_ubicacion, 
                (CASE 
                    WHEN v.tipo = 'area' AND v.cve_ubicacion IS NOT NULL THEN 
                        (SELECT desc_ubicacion AS ubicacion FROM tubicacionesretencion WHERE cve_ubicacion = v.cve_ubicacion) 
                    WHEN v.tipo = 'ubicacion' AND v.cve_ubicacion IS NOT NULL THEN 
                        (SELECT u.CodigoCSD AS ubicacion FROM c_ubicacion u WHERE u.idy_ubica = v.cve_ubicacion)
                    ELSE '--'
                END) AS ubicacion,
                IFNULL(c_articulo.cve_articulo, '--') AS clave,
                IFNULL(c_articulo.des_articulo, '--') AS descripcion,
                IF(v.cve_lote = '', '', v.cve_lote) AS lote,
                IF(c_articulo.Caduca = 'S', IFNULL(c_lotes.CADUCIDAD, '--'), '') AS caducidad,
                IFNULL(inv.NConteo, 0) AS conteo, 
                GROUP_CONCAT(DISTINCT CONCAT(inv.NConteo,'-',inv.Cantidad) ORDER BY inv.NConteo SEPARATOR ',') AS NConteo_Cantidad_reg,
                GROUP_CONCAT(DISTINCT inv.NConteo SEPARATOR ',') AS Nconteo, 
                (SELECT ext.ExistenciaTeorica FROM t_invpiezas ext WHERE ext.ID_Inventario = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = v.cve_articulo AND ext.idy_ubica = v.cve_ubicacion AND ext.cve_lote = v.cve_lote) AS stockTeorico,
                GROUP_CONCAT(DISTINCT inv.Cantidad ORDER BY inv.NConteo SEPARATOR ',') AS Cantidad_reg,
                (SELECT Cantidad FROM t_invpiezas iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = v.cve_articulo AND iv.idy_ubica = v.cve_ubicacion AND iv.cve_lote = v.cve_lote AND iv.NConteo = MAX(inv.NConteo)) AS Cantidad,
                IF(COUNT(DISTINCT inv.NConteo) = (SELECT COUNT(DISTINCT Cantidad) FROM t_invpiezas iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = v.cve_articulo AND iv.idy_ubica = v.cve_ubicacion AND iv.cve_lote = v.cve_lote), 0, 1) AS Cerrar,
                IFNULL((SELECT Cantidad FROM t_invpiezas WHERE ID_Inventario = {$id_inventario} AND NConteo = (SELECT conteo) AND cve_articulo = v.cve_articulo AND idy_ubica = v.cve_ubicacion AND cve_lote = v.cve_lote GROUP BY cve_articulo LIMIT 1), 0) AS stockFisico,
                IFNULL(ABS((SELECT Cantidad FROM t_invpiezas iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = v.cve_articulo AND iv.idy_ubica = v.cve_ubicacion AND iv.cve_lote = v.cve_lote AND iv.NConteo = MAX(inv.NConteo)) - (SELECT ext.ExistenciaTeorica FROM t_invpiezas ext WHERE ext.ID_Inventario = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = v.cve_articulo AND ext.idy_ubica = v.cve_ubicacion AND ext.cve_lote = v.cve_lote)), 0) AS diferencia,
                inv.cve_usuario AS usuario,
                'Piezas' AS unidad_medida,
                (SELECT Status FROM th_inventario WHERE ID_inventario = {$id_inventario} LIMIT 1) AS Status
                #CONCAT(v.tipo, '|', v.cve_ubicacion, '|', v.cve_articulo, '|', v.cve_lote, '|', {$id_inventario}, '|', (SELECT conteo), '|', IF((SELECT cve_usuario FROM t_conteoinventario WHERE NConteo = (SELECT conteo) AND ID_Inventario = {$id_inventario}) = '', '-', (SELECT cve_usuario FROM t_conteoinventario WHERE NConteo = (SELECT conteo) AND ID_Inventario = {$id_inventario}))) AS id
            FROM V_ExistenciaGralProduccion v
                LEFT JOIN c_articulo ON c_articulo.cve_articulo = v.cve_articulo
                LEFT JOIN c_almacen AS c ON c.cve_almac=(SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = v.cve_ubicacion) 
                LEFT JOIN c_lotes ON c_lotes.LOTE = v.cve_lote
                LEFT JOIN t_invpiezas inv ON inv.ID_Inventario = {$id_inventario} AND inv.cve_articulo = v.cve_articulo AND inv.idy_ubica = v.cve_ubicacion AND inv.cve_lote = v.cve_lote 
            WHERE v.Existencia > 0 AND v.tipo='ubicacion' AND inv.idy_ubica = v.cve_ubicacion AND inv.cve_articulo = v.cve_articulo AND inv.cve_lote = v.cve_lote 
            GROUP BY clave,cve_ubicacion,lote
            ";
*/
// SIN FILTRO DE LOTES

        $sql="
        SELECT DISTINCT * FROM (
        SELECT DISTINCT 
                c.des_almac zona,
                v.cve_ubicacion, 
                (CASE 
                    WHEN v.tipo = 'area' AND v.cve_ubicacion IS NOT NULL THEN 
                        (SELECT desc_ubicacion AS ubicacion FROM tubicacionesretencion WHERE cve_ubicacion = v.cve_ubicacion) 
                    WHEN v.tipo = 'ubicacion' AND v.cve_ubicacion IS NOT NULL THEN 
                        (SELECT u.CodigoCSD AS ubicacion FROM c_ubicacion u WHERE u.idy_ubica = v.cve_ubicacion)
                    ELSE '--'
                END) AS ubicacion,
                IFNULL(c_articulo.cve_articulo, '--') AS clave,
                IFNULL(c_articulo.des_articulo, '--') AS descripcion,
                IF(v.cve_lote = '', '', v.cve_lote) AS lote,
                IF(c_articulo.Caduca = 'S', IFNULL(DATE_FORMAT(c_lotes.CADUCIDAD, '%d-%m-%Y'), '--'), '') AS caducidad,
                IFNULL(inv.NConteo, 0) AS conteo, 
                GROUP_CONCAT(DISTINCT CONCAT(inv.NConteo,'-',inv.Cantidad) ORDER BY inv.NConteo SEPARATOR ',') AS NConteo_Cantidad_reg,
                GROUP_CONCAT(DISTINCT inv.NConteo SEPARATOR ',') AS Nconteo, 
                #(SELECT DISTINCT SUM(ext.ExistenciaTeorica) FROM t_invpiezas ext WHERE ext.ID_Inventario = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = v.cve_articulo AND ext.idy_ubica = v.cve_ubicacion AND ext.cve_lote = v.cve_lote) AS stockTeorico,
                IFNULL((SELECT SUM(Existencia) FROM V_ExistenciaGralProduccion WHERE tipo = 'ubicacion' AND cve_ubicacion = v.cve_ubicacion AND cve_lote = v.cve_lote AND cve_articulo = v.cve_articulo), 0) AS stockTeorico,
                GROUP_CONCAT(DISTINCT inv.Cantidad ORDER BY inv.NConteo SEPARATOR ',') AS Cantidad_reg,
                (SELECT DISTINCT SUM(Cantidad) FROM t_invpiezas iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = v.cve_articulo AND iv.idy_ubica = v.cve_ubicacion AND iv.cve_lote = v.cve_lote AND iv.NConteo = MAX(inv.NConteo)) AS Cantidad,
                #IF(COUNT(DISTINCT inv.NConteo) = (SELECT COUNT(DISTINCT Cantidad) FROM t_invpiezas iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = v.cve_articulo AND iv.cve_lote = v.cve_lote AND iv.idy_ubica = v.cve_ubicacion), 0, 1) AS Cerrar,

                #IF((COUNT(DISTINCT inv.NConteo) = (SELECT COUNT(DISTINCT iv.Cantidad) FROM t_invpiezas iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = v.cve_articulo AND iv.idy_ubica = v.cve_ubicacion )) OR ((SELECT COUNT(*) FROM t_invpiezas iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = v.cve_articulo AND iv.idy_ubica = v.cve_ubicacion AND iv.Cantidad = 0)=3 OR AVG(inv.Cantidad) = 0), 0, 1) AS Cerrar,

                #IF((SELECT COUNT(Cantidad) FROM t_invpiezas iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = v.cve_articulo AND iv.idy_ubica = v.cve_ubicacion AND iv.cve_lote = v.cve_lote AND iv.Cantidad = 0)=3, 1, 0) AS Cerrar0,
                '' AS LP,
                IFNULL((SELECT Cantidad FROM t_invpiezas WHERE ID_Inventario = {$id_inventario} AND NConteo = (SELECT conteo) AND cve_articulo = v.cve_articulo AND cve_lote = v.cve_lote AND idy_ubica = v.cve_ubicacion GROUP BY cve_articulo LIMIT 1), 0) AS stockFisico,
                IFNULL((SELECT DISTINCT SUM(Cantidad) FROM t_invpiezas iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = v.cve_articulo AND iv.idy_ubica = v.cve_ubicacion AND iv.cve_lote = v.cve_lote AND iv.NConteo = MAX(inv.NConteo)) - (SELECT DISTINCT SUM(ext.ExistenciaTeorica) FROM t_invpiezas ext WHERE ext.ID_Inventario = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = v.cve_articulo AND ext.idy_ubica = v.cve_ubicacion AND ext.cve_lote = v.cve_lote), 0) AS diferencia,
                inv.cve_usuario AS usuario,
                'Piezas' AS unidad_medida,
                (SELECT Status FROM th_inventario WHERE ID_inventario = {$id_inventario} LIMIT 1) AS Status
                #CONCAT(v.tipo, '|', v.cve_ubicacion, '|', v.cve_articulo, '|', v.cve_lote, '|', {$id_inventario}, '|', (SELECT conteo), '|', IF((SELECT cve_usuario FROM t_conteoinventario WHERE NConteo = (SELECT conteo) AND ID_Inventario = {$id_inventario}) = '', '-', (SELECT cve_usuario FROM t_conteoinventario WHERE NConteo = (SELECT conteo) AND ID_Inventario = {$id_inventario}))) AS id
            FROM V_ExistenciaGralProduccion v
                LEFT JOIN c_articulo ON c_articulo.cve_articulo = v.cve_articulo
                LEFT JOIN c_almacen AS c ON c.cve_almac=(SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = v.cve_ubicacion) 
                LEFT JOIN c_lotes ON c_lotes.LOTE = v.cve_lote
                LEFT JOIN t_invpiezas inv ON inv.ID_Inventario = {$id_inventario} AND inv.cve_articulo = v.cve_articulo AND inv.idy_ubica = v.cve_ubicacion AND inv.cve_lote = v.cve_lote
            WHERE v.Existencia > 0 AND v.tipo='ubicacion' AND inv.idy_ubica = v.cve_ubicacion AND inv.cve_articulo = v.cve_articulo AND inv.cve_lote = v.cve_lote 
            AND CONCAT(v.cve_articulo,v.cve_lote) NOT IN (SELECT CONVERT(CONCAT(cve_articulo,cve_lote), CHAR) FROM t_invtarima WHERE ID_Inventario = {$id_inventario})
            #AND CONCAT(inv.NConteo,'-',inv.Cantidad) != '0-0'
            GROUP BY LP, clave,cve_ubicacion,lote

UNION

        SELECT DISTINCT 
                c.des_almac zona,
                inv.idy_ubica AS cve_ubicacion, 
                (SELECT u.CodigoCSD AS ubicacion FROM c_ubicacion u WHERE u.idy_ubica = inv.idy_ubica) AS ubicacion,
                IFNULL(c_articulo.cve_articulo, '--') AS clave,
                IFNULL(c_articulo.des_articulo, '--') AS descripcion,
                IF(inv.cve_lote = '', '', inv.cve_lote) AS lote,
                IF(c_articulo.Caduca = 'S', IFNULL(c_lotes.CADUCIDAD, '--'), '') AS caducidad,
                IFNULL(inv.NConteo, 0) AS conteo, 
                GROUP_CONCAT(DISTINCT CONCAT(inv.NConteo,'-',inv.Cantidad) ORDER BY inv.NConteo SEPARATOR ',') AS NConteo_Cantidad_reg,
                GROUP_CONCAT(DISTINCT inv.NConteo SEPARATOR ',') AS Nconteo, 
                #IFNULL((SELECT ext.ExistenciaTeorica FROM t_invpiezas ext WHERE ext.ID_Inventario = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote), 0) AS stockTeorico,
                IFNULL((SELECT SUM(Existencia) FROM V_ExistenciaGralProduccion WHERE tipo = 'ubicacion' AND cve_ubicacion = inv.idy_ubica AND cve_lote = inv.cve_lote AND cve_articulo = inv.cve_articulo), 0) AS stockTeorico,
                GROUP_CONCAT(DISTINCT inv.Cantidad ORDER BY inv.NConteo SEPARATOR ',') AS Cantidad_reg,
                (SELECT DISTINCT SUM(Cantidad) FROM t_invpiezas iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica  AND iv.NConteo = MAX(inv.NConteo)) AS Cantidad,
                #IF((COUNT(DISTINCT inv.NConteo) = (SELECT COUNT(DISTINCT iv.Cantidad) FROM t_invpiezas iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica AND iv.cve_lote = inv.cve_lote)) OR ((SELECT COUNT(*) FROM t_invpiezas iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica AND iv.cve_lote = inv.cve_lote AND iv.Cantidad = 0)=3 OR AVG(inv.Cantidad) = 0), 0, 1) AS Cerrar,
                #IF((SELECT COUNT(Cantidad) FROM t_invpiezas iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica AND iv.Cantidad = 0)=3, 1, 0) AS Cerrar0,
                '' AS LP,
                IFNULL((SELECT Cantidad FROM t_invpiezas WHERE ID_Inventario = {$id_inventario} AND NConteo = (SELECT conteo) AND cve_articulo = inv.cve_articulo AND idy_ubica = inv.idy_ubica GROUP BY cve_articulo LIMIT 1), 0) AS stockFisico,
                IFNULL((SELECT DISTINCT SUM(Cantidad) FROM t_invpiezas iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica AND iv.NConteo = MAX(inv.NConteo)) - (SELECT DISTINCT SUM(ext.ExistenciaTeorica) FROM t_invpiezas ext WHERE ext.ID_Inventario = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote), 0) AS diferencia,
                inv.cve_usuario AS usuario,
                'Piezas' AS unidad_medida,
                (SELECT STATUS FROM th_inventario WHERE ID_inventario = {$id_inventario} LIMIT 1) AS STATUS
            FROM t_invpiezas inv
                LEFT JOIN c_articulo ON c_articulo.cve_articulo = inv.cve_articulo
                LEFT JOIN c_almacen AS c ON c.cve_almac=(SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = inv.idy_ubica) 
                LEFT JOIN c_lotes ON c_lotes.LOTE = inv.cve_lote
            WHERE (inv.ID_Inventario = {$id_inventario} AND inv.cve_articulo = c_articulo.cve_articulo AND CONCAT(inv.cve_articulo,inv.cve_lote) NOT IN (SELECT CONVERT(CONCAT(cve_articulo,cve_lote), CHAR) FROM t_invtarima WHERE ID_Inventario = {$id_inventario} AND NConteo = 0))
            GROUP BY LP, clave,cve_ubicacion,lote

UNION

        SELECT DISTINCT 
                c.des_almac zona,
                inv.idy_ubica AS cve_ubicacion, 
                (SELECT u.CodigoCSD AS ubicacion FROM c_ubicacion u WHERE u.idy_ubica = inv.idy_ubica) AS ubicacion,
                '' AS clave,
                '' AS descripcion,
                '' AS lote,
                '' AS caducidad,
                IFNULL(inv.NConteo, 0) AS conteo, 
                GROUP_CONCAT(DISTINCT CONCAT(inv.NConteo,'-',inv.Cantidad) ORDER BY inv.NConteo SEPARATOR ',') AS NConteo_Cantidad_reg,
                GROUP_CONCAT(DISTINCT inv.NConteo SEPARATOR ',') AS Nconteo, 
                #IFNULL((SELECT ext.ExistenciaTeorica FROM t_invpiezas ext WHERE ext.ID_Inventario = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote), 0) AS stockTeorico,
                IFNULL((SELECT SUM(Existencia) FROM V_ExistenciaGralProduccion WHERE tipo = 'ubicacion' AND cve_ubicacion = inv.idy_ubica AND cve_lote = inv.cve_lote AND cve_articulo = inv.cve_articulo), 0) AS stockTeorico,
                GROUP_CONCAT(DISTINCT inv.Cantidad ORDER BY inv.NConteo SEPARATOR ',') AS Cantidad_reg,
                (SELECT DISTINCT SUM(Cantidad) FROM t_invpiezas iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica  AND iv.NConteo = MAX(inv.NConteo)) AS Cantidad,
                #IF((COUNT(DISTINCT inv.NConteo) = (SELECT COUNT(DISTINCT iv.Cantidad) FROM t_invpiezas iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica )) OR ((SELECT COUNT(*) FROM t_invpiezas iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica AND iv.Cantidad = 0)=3 OR AVG(inv.Cantidad) = 0), 0, 1) AS Cerrar,
                #IF((SELECT COUNT(Cantidad) FROM t_invpiezas iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica AND iv.Cantidad = 0)=3, 1, 0) AS Cerrar0,
                '' AS LP,
                IFNULL((SELECT Cantidad FROM t_invpiezas WHERE ID_Inventario = {$id_inventario} AND NConteo = (SELECT conteo) AND cve_articulo = inv.cve_articulo AND idy_ubica = inv.idy_ubica GROUP BY cve_articulo LIMIT 1), 0) AS stockFisico,
                IFNULL((SELECT DISTINCT SUM(Cantidad) FROM t_invpiezas iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica AND iv.NConteo = MAX(inv.NConteo)) - (SELECT DISTINCT SUM(ext.ExistenciaTeorica) FROM t_invpiezas ext WHERE ext.ID_Inventario = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote), 0) AS diferencia,
                inv.cve_usuario AS usuario,
                'Piezas' AS unidad_medida,
                (SELECT STATUS FROM th_inventario WHERE ID_inventario = {$id_inventario} LIMIT 1) AS STATUS
            FROM t_invpiezas inv
                LEFT JOIN c_almacen AS c ON c.cve_almac=(SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = inv.idy_ubica) 
            WHERE inv.ID_Inventario = {$id_inventario} AND inv.cve_articulo = ''
            GROUP BY LP, clave,cve_ubicacion,lote


UNION

        SELECT DISTINCT 
                c.des_almac zona,
                inv.idy_ubica AS cve_ubicacion, 
                (SELECT u.CodigoCSD AS ubicacion FROM c_ubicacion u WHERE u.idy_ubica = inv.idy_ubica) AS ubicacion,
                IFNULL(c_articulo.cve_articulo, '--') AS clave,
                IFNULL(c_articulo.des_articulo, '--') AS descripcion,
                IF(inv.cve_lote = '', '', inv.cve_lote) AS lote,
                IF(c_articulo.Caduca = 'S', IFNULL(c_lotes.CADUCIDAD, '--'), '') AS caducidad,
                IFNULL(inv.NConteo, 0) AS conteo, 
                GROUP_CONCAT(DISTINCT CONCAT(inv.NConteo,'-',inv.existencia) ORDER BY inv.NConteo SEPARATOR ',') AS NConteo_Cantidad_reg,
                GROUP_CONCAT(DISTINCT inv.NConteo SEPARATOR ',') AS Nconteo, 
                #IFNULL((SELECT SUM(ext.Teorico) FROM t_invtarima ext WHERE ext.ID_Inventario = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.Cve_Lote = inv.cve_lote), 0) AS stockTeorico,
                IFNULL((SELECT SUM(Existencia) FROM V_ExistenciaGralProduccion WHERE tipo = 'ubicacion' AND cve_ubicacion = inv.idy_ubica AND cve_lote = inv.cve_lote AND cve_articulo = inv.cve_articulo), 0) AS stockTeorico,
                GROUP_CONCAT(DISTINCT inv.existencia ORDER BY inv.NConteo SEPARATOR ',') AS Cantidad_reg,
                (SELECT DISTINCT SUM(existencia) FROM t_invtarima iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica AND iv.ntarima = inv.ntarima AND iv.Cve_Lote = inv.Cve_Lote AND iv.NConteo = MAX(inv.NConteo)) AS Cantidad,
                #IF((COUNT(DISTINCT inv.NConteo) = (SELECT COUNT(DISTINCT iv.existencia) FROM t_invtarima iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica AND iv.ntarima = inv.ntarima AND iv.Cve_Lote = inv.Cve_Lote)) OR ((SELECT COUNT(*) FROM t_invtarima iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica AND iv.ntarima = inv.ntarima AND iv.Cve_Lote = inv.Cve_Lote AND iv.existencia = 0)=3 OR AVG(inv.existencia) = 0), 0, 1) AS Cerrar,
                #IF((SELECT COUNT(existencia) FROM t_invtarima iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica AND iv.existencia = 0)=3, 1, 0) AS Cerrar0,
                IFNULL(ch.CveLP, '') AS LP,
                IFNULL((SELECT SUM(existencia) FROM t_invtarima WHERE ID_Inventario = {$id_inventario} AND NConteo = (SELECT conteo) AND cve_articulo = inv.cve_articulo AND idy_ubica = inv.idy_ubica GROUP BY cve_articulo LIMIT 1), 0) AS stockFisico,
                IFNULL((SELECT DISTINCT SUM(existencia) FROM t_invtarima iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica AND iv.Cve_Lote = inv.Cve_Lote AND iv.ntarima = inv.ntarima AND iv.NConteo = MAX(inv.NConteo)) - (SELECT DISTINCT SUM(ext.Teorico) FROM t_invtarima ext WHERE ext.ID_Inventario = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote AND ext.ntarima = inv.ntarima), 0) AS diferencia,
                inv.cve_usuario AS usuario,
                'Piezas' AS unidad_medida,
                (SELECT STATUS FROM th_inventario WHERE ID_inventario = {$id_inventario} LIMIT 1) AS STATUS
            FROM t_invtarima inv
                LEFT JOIN c_articulo ON c_articulo.cve_articulo = inv.cve_articulo
                LEFT JOIN c_almacen AS c ON c.cve_almac=(SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = inv.idy_ubica) 
                LEFT JOIN c_lotes ON c_lotes.LOTE = inv.cve_lote
                LEFT JOIN c_charolas ch ON ch.IDContenedor = inv.ntarima
            WHERE (inv.ID_Inventario = {$id_inventario} AND inv.cve_articulo = c_articulo.cve_articulo )
            #AND CONCAT(inv.cve_articulo,inv.cve_lote) NOT IN (SELECT CONCAT(cve_articulo,cve_lote) FROM t_invtarima WHERE ID_Inventario = {$id_inventario} AND NConteo = 0))
            GROUP BY LP, clave,cve_ubicacion,lote

            ORDER BY ubicacion ASC
            ) as t
            ";


        //$sql = "CALL SPRP_ReporteInvFisico({$id_inventario})";

        $sth = \db()->prepare($sql);
        $sth->execute();
        $data = $sth->fetchAll(\PDO::FETCH_ASSOC);

        foreach($data as $value){
            extract($value);
/*
            $response[] = [
                'id' => $Id,
                'clave' => $Clave, 
                'descripcion' => $descripcion,
                'zona' => $Zona,
                'cve_ubicacion' => $Cve_Ubicacion,
                'ubicacion' => $Ubicacion, 
                'lote' => $LoteSerie, 
                'caducidad' => $Caducidad, 
                'stockTeorico' => $Teorico, 
                'stockFisico' => $stockFisico, 
                'diferencia' => $diferencia,
                'Conteo1' => $Conteo1, 
                'Conteo2' => $Conteo2, 
                'Conteo3' => $Conteo3, 
                'Conteo4' => $Conteo4, 
                'Conteo5' => $Conteo5, 
                'Status' => 'A', 
                'usuario' => $Usuario,
                'unidad_medida' => $unidad_medida, 
                'Cantidad' => $Cantidad, 
                'Cerrar' => $Cerrado, 
                'LP' => $Lp
            ];
            */

            $response[] = [
                'id' => $id,
                'clave' => $clave, 
                'descripcion' => $descripcion,
                'zona' => $zona,
                'cve_ubicacion' => $cve_ubicacion,
                'ubicacion' => $ubicacion, 
                'serie' => $serie, 
                'lote' => $lote, 
                'caducidad' => $caducidad, 
                'stockTeorico' => $stockTeorico, 
                'stockFisico' => $stockFisico, 
                'diferencia' => $diferencia,
                'conteo' => $conteo, 
                'NConteo_Cantidad_reg' => $NConteo_Cantidad_reg,
                'Nconteo' => $Nconteo, 
                'Status' => $Status, 
                'usuario' => $usuario,
                'unidad_medida' => $unidad_medida, 
                'Cantidad' => $Cantidad, 
                'Cerrar' => $Cerrar, 
                'Cerrar0' => $Cerrar0, 
                'LP' => $LP, 
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


    public function Ejecutar_Infinity_WS($conex, $clave, $Lote, $cantidad, $um, $clave_almacen, $id_inventario)
    {

        //$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

          //{
                $sql = "SELECT Url, Servicio, User, Pswd, Empresa, TO_BASE64(CONCAT(User,':',Pswd)) Codificado, CONCAT(Url,':',Puerto,'/',Servicio) AS url_curl, NOW() AS hora_movimiento FROM c_datos_ws WHERE Servicio = 'wms_trasp' AND Activo = 1";
                $query = mysqli_query($conex, $sql);
                $row_infinity = mysqli_fetch_array($query);
                $url_curl = $row_infinity['url_curl'];
                $Url_inf = $row_infinity['Url'];
                $Servicio_inf = $row_infinity['Servicio'];
                $User_inf = $row_infinity['User'];
                $Pswd_inf = $row_infinity['Pswd'];
                $Empresa_inf = $row_infinity['Empresa'];
                $hora_movimiento = $row_infinity['hora_movimiento'];
                $Codificado = $row_infinity['Codificado'];

          $json = "[";

            $json .= "{";
            $json .= '"item":"'.$clave.'","um":"'.$um.'","batch":"'.$Lote.'", "qty": '.$cantidad.',"typeMov":"T","warehouse":"'.$clave_almacen.'","dataOpe":"'.$hora_movimiento.'"';
            $json .= "}";
          //$json[strlen($json)-1] = ' ';
          $json .= "]";

          $curl = curl_init();
          //$url_curl = $Url_inf.':8080/'.$Servicio_inf;

              $curl = curl_init();
              //$url_curl = $Url_inf.':8080/'.$Servicio_inf;

              curl_setopt_array($curl, array(
                // Esta URL es para salidas (wms_sal), para entradas cambia al final por 'wms_entr' y para traslados cambia por 'wms_trasp'
                CURLOPT_URL => "$url_curl",
                //CURLOPT_URL => 'https://testinf01.finproject.com:8080/wms_trasp',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS =>
                // Aquí cambia la cadena JSON
                $json,
                CURLOPT_HTTPHEADER => array(
                  'Content-Type: application/json',
                  //'Authorization: Basic YXNzaXRwcm86MmQ0YmE1ZWQtOGNhMS00NTQyLWI4YTYtOWRkYzllZTc1Nzky'
                  'Authorization: Basic '.$Codificado.''
                )
                ,CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_SSL_VERIFYPEER => false
              ));

              $response = curl_exec($curl);

              curl_close($curl);      
              //echo $response; $response = '';

              $sql = "INSERT INTO t_log_ws(Fecha, Referencia, Mensaje, Respuesta, Proceso, Dispositivo) VALUES (NOW(), '$Servicio_inf', '$json', '$response', 'Inventario $id_inventario', 'WEB')";
              $query = mysqli_query($conex, $sql);


          //}
          //*******************************************************************************/
          //*******************************************************************************
    }

    public function asignarSupervisor()
    {
      $user = $this->getPost('user');
      $password = $this->getPost('password');
      $inventario = $this->getPost('inventario');
      //$stocks = $this->getPost('conteos');
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
      //if($tipo == 'fisico'){
        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
/*
        $sql="SELECT t_conteoinventario.cve_usuario FROM `t_conteoinventario` WHERE ID_Inventario = '{$inventario}' AND t_conteoinventario.cve_usuario!='' GROUP BY cve_usuario";
        $query = mysqli_query($conn, $sql);
        $claves_usuarios_conteos = mysqli_fetch_all($query);
*/
      $sql = "SET NAMES 'utf8mb4';";
      $query = mysqli_query($conn, $sql);

        $sql="SELECT c_usuario.cve_usuario FROM `c_usuario` WHERE id_user = '{$id_user}'";
        $query = mysqli_query($conn, $sql);
        $clave_supervisor = mysqli_fetch_array($query)["cve_usuario"];
        /*
        foreach($claves_usuarios_conteos as $key => $value)
        {
          if($value[0] == $clave_supervisor)
          {
            $coincidencide_supervisor = true;
          }
        }
        */
        if($tipo == 'fisico')
        {
            Capsule::table('t_conteoinventario')
            ->where('ID_Inventario', $inventario)
            ->update(['cve_supervisor' => $clave_supervisor]);

            Capsule::table('th_inventario')
            ->where('ID_Inventario', $inventario)
            ->update(['Status' => 'T']);
        }
        else
        {
            Capsule::table('t_conteoinventariocicl')
            ->where('ID_PLAN', $inventario)
            ->update(['cve_supervisor' => $clave_supervisor]);

            Capsule::table('det_planifica_inventario')
            ->where('ID_PLAN', $inventario)
            ->update(['status' => 'T']);
        }


//        foreach ($stocks as $key => $value) 
//        {
//          list($tipo, $ubicacion, $articulo, $inventario, $conteo) = explode('|',$value['id']);
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
//        }

          /*
            $sql_cerrar = "SELECT iv.idy_ubica, iv.cve_articulo, iv.cve_lote,iv.Cantidad FROM t_invpiezas iv WHERE iv.ID_Inventario = {$inventario} AND iv.NConteo = (SELECT MAX(t_inv.NConteo) FROM t_invpiezas t_inv WHERE t_inv.Cantidad != 0 AND t_inv.ID_Inventario = {$inventario})";
            $query = mysqli_query($conn, $sql_cerrar);

            while($row_data = mysqli_fetch_assoc($query))
            {
              $idy_ubica    = $row_data["idy_ubica"];
              $cve_articulo = $row_data["cve_articulo"];
              $cve_lote     = $row_data["cve_lote"];
              $Cantidad     = $row_data["Cantidad"];

              $sql = "UPDATE ts_existenciapiezas SET Existencia = {$Cantidad} WHERE idy_ubica = '{$idy_ubica}' AND cve_articulo = '{$cve_articulo}' AND cve_lote = '{$cve_lote}'";
              $res = mysqli_query($conn, $sql);
            }
            */
/*
            $sql = "SELECT COUNT(DISTINCT cve_articulo) AS cuenta FROM t_invpiezas WHERE ID_Inventario = {$inventario}";
            $query = mysqli_query($conn, $sql);
            $data = mysqli_fetch_assoc($query);
            $num_productos = $data['cuenta'];
*/
/*
            $sql_cerrar = "SELECT DISTINCT c.idy_ubica, c.cve_articulo, c.cve_lote, c.Cantidad FROM t_invpiezas c WHERE ID_Inventario = {$inventario} AND (SELECT COUNT(*) FROM t_invpiezas a WHERE Cantidad = c.Cantidad AND ID_Inventario = {$inventario} AND ((SELECT COUNT(*) FROM t_invpiezas a WHERE (a.Cantidad = c.Cantidad) AND a.ID_Inventario = {$inventario} AND c.cve_articulo = a.cve_articulo AND c.cve_lote = a.cve_lote) = 2 )) AND c.Cantidad > 0 ORDER BY cve_articulo";
            $query = mysqli_query($conn, $sql_cerrar);

            if(mysqli_num_rows($query) == $num_productos)
            {
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
*/

            //$sql_cerrar = "CALL SPRP_ReporteInvFisico({$inventario})";
/*
            $sql_cerrar = "SELECT (SELECT p.id FROM th_inventario i LEFT JOIN c_almacenp p ON p.clave = i.cve_almacen WHERE i.ID_Inventario = {$inventario}) AS cve_almac, NConteo, idy_ubica, cve_articulo, cve_lote, '' as ntarima, Cantidad FROM t_invpiezas WHERE ID_Inventario = {$inventario} AND NConteo > 0 AND CONCAT(idy_ubica, cve_articulo, cve_lote) NOT IN (SELECT CONCAT(idy_ubica, cve_articulo, Cve_Lote) FROM t_invtarima WHERE ID_Inventario = {$inventario} AND NConteo > 0)

                UNION 

                SELECT (SELECT p.id FROM th_inventario i LEFT JOIN c_almacenp p ON p.clave = i.cve_almacen WHERE i.ID_Inventario = {$inventario}) AS cve_almac, NConteo, idy_ubica, cve_articulo, cve_lote, ntarima, existencia AS Cantidad FROM t_invtarima WHERE ID_Inventario = {$inventario} AND NConteo > 0

                ORDER BY NConteo ASC"; 
*/

          //*******************************************************************************
          //                          EJECUTAR EN INFINITY
          //*******************************************************************************
          $sql = "SELECT COUNT(*) as existe FROM t_configuraciongeneral WHERE cve_conf = 'ejecutar_infinity' AND Valor = '1'";
          $query = mysqli_query($conn, $sql);
          $ejecutar_infinity = mysqli_fetch_array($query)['existe'];
          $almacen_infinity = "";
          //*******************************************************************************

            $sql_cerrar = "
                SELECT DISTINCT (SELECT i.id_almacen FROM cab_planifica_inventario i WHERE i.ID_PLAN = {$inventario}) AS cve_almac, tp.NConteo, tp.idy_ubica, tp.cve_articulo, tp.cve_lote, '' AS ntarima, tp.Cantidad,
                #IFNULL((SELECT DISTINCT GROUP_CONCAT(ID_Proveedor) FROM t_invpiezasciclico WHERE cve_articulo = tp.cve_articulo AND cve_lote = tp.cve_lote AND idy_ubica = tp.idy_ubica AND NConteo = 0 AND ID_PLAN = {$inventario}), (SELECT DISTINCT Cve_Proveedor FROM th_entalmacen WHERE fol_folio IN (SELECT distinct fol_folio FROM td_entalmacen WHERE cve_articulo = tp.cve_articulo AND cve_lote = tp.cve_lote) LIMIT 1)) AS proveedor
                #tpc.Id_Proveedor AS proveedor
                (SELECT Id_Proveedor FROM t_invpiezasciclico tpc WHERE tpc.cve_articulo = tp.cve_articulo AND tpc.cve_lote = tp.cve_lote AND tpc.NConteo = 0 AND tpc.ID_PLAN = {$inventario}) AS proveedor
                FROM t_invpiezasciclico tp
                #LEFT JOIN t_invpiezasciclico tpc ON tpc.ID_PLAN = tp.ID_PLAN AND tpc.NConteo = tp.NConteo AND tpc.cve_articulo = tp.cve_articulo AND tpc.cve_lote = tp.cve_lote AND tpc.Id_Proveedor = tp.Id_Proveedor AND tpc.NConteo = 0
                WHERE tp.ID_PLAN = {$inventario} AND tp.NConteo > 0
                AND CONCAT(tp.idy_ubica, tp.cve_articulo, tp.cve_lote) NOT IN (SELECT CONCAT(idy_ubica, cve_articulo, Cve_Lote) FROM t_invtarimaciclico WHERE ID_PLAN = {$inventario} AND NConteo > 0)
                AND tp.Nconteo = (SELECT MAX(NConteo) FROM t_invpiezasciclico WHERE idy_ubica = tp.idy_ubica AND cve_articulo = tp.cve_articulo AND cve_lote = tp.cve_lote AND ID_PLAN = {$inventario} AND Cantidad >= 0)
                AND IFNULL(tp.cve_articulo, '') != ''

                UNION 

                SELECT DISTINCT (SELECT i.id_almacen FROM cab_planifica_inventario i WHERE i.ID_PLAN = {$inventario}) AS cve_almac, tt.NConteo, tt.idy_ubica, tt.cve_articulo, tt.cve_lote, tt.ntarima, tt.existencia AS Cantidad,
                #IFNULL((SELECT DISTINCT ID_Proveedor FROM t_invtarimaciclico WHERE cve_articulo = tt.cve_articulo AND cve_lote = tt.cve_lote AND idy_ubica = tt.idy_ubica AND NConteo = 0 AND ntarima = tt.ntarima AND ID_PLAN = {$inventario}), (SELECT DISTINCT Cve_Proveedor FROM th_entalmacen WHERE fol_folio IN (SELECT fol_folio FROM td_entalmacen WHERE cve_articulo = tt.cve_articulo AND cve_lote = tt.cve_lote) LIMIT 1)) AS proveedor
                #ttc.Id_Proveedor AS proveedor
                (SELECT Id_Proveedor FROM t_invtarimaciclico ttc WHERE ttc.cve_articulo = tt.cve_articulo AND ttc.cve_lote = tt.cve_lote AND ttc.ntarima = tt.ntarima AND ttc.NConteo = 0 AND ttc.ID_PLAN = {$inventario}) AS proveedor
                FROM t_invtarimaciclico tt 
                #LEFT JOIN t_invtarimaciclico ttc ON ttc.ID_PLAN = tt.ID_PLAN AND ttc.NConteo = tt.NConteo AND ttc.cve_articulo = tt.cve_articulo AND ttc.cve_lote = tt.cve_lote AND ttc.Id_Proveedor = tt.Id_Proveedor AND ttc.ntarima = tt.ntarima AND ttc.NConteo = 0
                WHERE tt.ID_PLAN = {$inventario} AND tt.NConteo > 0
                AND tt.Nconteo = (SELECT MAX(NConteo) FROM t_invtarimaciclico WHERE idy_ubica = tt.idy_ubica AND cve_articulo = tt.cve_articulo AND cve_lote = tt.cve_lote AND ntarima = tt.ntarima AND ID_PLAN = {$inventario} AND existencia >= 0) 
                AND IFNULL(tt.cve_articulo, '') != '' 
                ORDER BY NConteo ASC
            ";
            if($tipo == 'fisico')
            {
                $sql = "SELECT IFNULL(Inv_Inicial, 0) as Inv_Inicial FROM th_inventario WHERE ID_Inventario = {$inventario}";
                if (!($res = mysqli_query($conn, $sql))){
                    echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";
                }
                $tipo_inicial = 1;
                $rowinv = mysqli_fetch_array($res);
                $tipo_inicial = $rowinv['Inv_Inicial'];

                $valor = 0;
                if($tipo_inicial == 0)
                    $valor = 1;

                $sql_cerrar = "
                        SELECT DISTINCT (SELECT p.id FROM th_inventario i LEFT JOIN c_almacenp p ON p.clave = i.cve_almacen WHERE i.ID_Inventario = {$inventario}) AS cve_almac, tp.NConteo, tp.idy_ubica, tp.cve_articulo, tp.cve_lote as cve_lote, '' AS ntarima, tp.Cantidad,
                        #(SELECT ID_Proveedor FROM V_ExistenciaGralProduccion WHERE cve_articulo = tp.cve_articulo AND cve_lote = tp.cve_lote AND cve_ubicacion = tp.idy_ubica AND Cve_Contenedor = '') AS proveedor
                        IFNULL(IFNULL((SELECT DISTINCT ID_Proveedor FROM t_invpiezas WHERE cve_articulo = tp.cve_articulo AND cve_lote = tp.cve_lote AND idy_ubica = tp.idy_ubica AND NConteo = 0 AND ID_Inventario = {$inventario} LIMIT 1), (SELECT DISTINCT Cve_Proveedor FROM th_entalmacen WHERE fol_folio IN (SELECT fol_folio FROM td_entalmacen WHERE cve_articulo = tp.cve_articulo AND cve_lote = tp.cve_lote) LIMIT 1)), IFNULL((SELECT DISTINCT ID_Proveedor FROM t_invpiezas WHERE cve_articulo = tp.cve_articulo AND cve_lote = tp.cve_lote AND idy_ubica = tp.idy_ubica AND NConteo = 1 AND ID_Inventario = {$inventario} LIMIT 1), (SELECT DISTINCT Cve_Proveedor FROM th_entalmacen WHERE fol_folio IN (SELECT fol_folio FROM td_entalmacen WHERE cve_articulo = tp.cve_articulo AND cve_lote = tp.cve_lote) LIMIT 1))) AS proveedor, IFNULL(tp.Cuarentena, 0) AS cuarentena
                        FROM t_invpiezas tp
                        WHERE tp.ID_Inventario = {$inventario} AND tp.NConteo > {$valor} 

                        #AND CONCAT(tp.idy_ubica, tp.cve_articulo, tp.cve_lote) NOT IN (SELECT CONCAT(idy_ubica, cve_articulo, Cve_Lote) FROM t_invtarima WHERE ID_Inventario = {$inventario} AND NConteo > 0)

                        AND tp.Nconteo = (SELECT MAX(NConteo) FROM t_invpiezas WHERE idy_ubica = tp.idy_ubica AND cve_articulo = tp.cve_articulo AND cve_lote = tp.cve_lote AND ID_Inventario = {$inventario} AND Cantidad >= 0)
                        AND IFNULL(tp.cve_articulo, '') != ''

                        UNION 

                        SELECT DISTINCT (SELECT p.id FROM th_inventario i LEFT JOIN c_almacenp p ON p.clave = i.cve_almacen WHERE i.ID_Inventario = {$inventario}) AS cve_almac, tt.NConteo, tt.idy_ubica, tt.cve_articulo, tt.Cve_Lote as cve_lote, tt.ntarima, tt.existencia AS Cantidad,
                        #(SELECT ID_Proveedor FROM V_ExistenciaGralProduccion WHERE cve_articulo = tt.cve_articulo AND cve_lote = tt.cve_lote AND cve_ubicacion = tt.idy_ubica AND Cve_Contenedor = (SELECT clave_contenedor FROM c_charolas WHERE IDContenedor  = tt.ntarima)) AS proveedor
                        IFNULL(IFNULL((SELECT DISTINCT ID_Proveedor FROM t_invtarima WHERE cve_articulo = tt.cve_articulo AND cve_lote = tt.cve_lote AND idy_ubica = tt.idy_ubica AND NConteo = 0 AND ntarima = tt.ntarima AND ID_Inventario = {$inventario} LIMIT 1), (SELECT DISTINCT Cve_Proveedor FROM th_entalmacen WHERE fol_folio IN (SELECT fol_folio FROM td_entalmacen WHERE cve_articulo = tt.cve_articulo AND cve_lote = tt.Cve_Lote) LIMIT 1)), IFNULL((SELECT DISTINCT ID_Proveedor FROM t_invtarima WHERE cve_articulo = tt.cve_articulo AND cve_lote = tt.Cve_Lote AND idy_ubica = tt.idy_ubica AND NConteo = 1 AND ntarima = tt.ntarima AND ID_Inventario = {$inventario} LIMIT 1), (SELECT DISTINCT Cve_Proveedor FROM th_entalmacen WHERE fol_folio IN (SELECT fol_folio FROM td_entalmacen WHERE cve_articulo = tt.cve_articulo AND cve_lote = tt.cve_lote) LIMIT 1))) AS proveedor, IFNULL(tt.Cuarentena, 0) AS cuarentena
                        FROM t_invtarima tt WHERE tt.ID_Inventario = {$inventario} AND tt.NConteo > {$valor} 
                        AND tt.Nconteo = (SELECT MAX(NConteo) FROM t_invtarima WHERE idy_ubica = tt.idy_ubica AND cve_articulo = tt.cve_articulo AND cve_lote = tt.Cve_Lote AND ntarima = tt.ntarima AND ID_Inventario = {$inventario} AND existencia >= 0)
                        AND IFNULL(tt.cve_articulo, '') != ''
                        ORDER BY NConteo ASC";
            }

            //$query = mysqli_query($conn, $sql_cerrar);
            if (!($query = mysqli_query($conn, $sql_cerrar))) 
            {
                echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
            }

              //$cve_articulo_arr = array(); $cve_lote_arr = array(); $existencia_art_prod_arr = array(); $um_arr = array(); $clave_almacen_arr = array();

              while($row_data = mysqli_fetch_array($query))
              {
/*
                $idy_ubica    = $row_data["Cve_Ubicacion"];
                $cve_articulo = $row_data["Clave"];
                $cve_lote     = $row_data["LoteSerie"];
                $Conteo1      = $row_data["Conteo1"];
                $Conteo2      = $row_data["Conteo2"];
                $Conteo3      = $row_data["Conteo3"];
                $Conteo4      = $row_data["Conteo4"];
                $Conteo5      = $row_data["Conteo5"];
                $Cantidad     = 0;

                if($Conteo5) $Cantidad = $Conteo5;
                else if($Conteo4) $Cantidad = $Conteo4;
                else if($Conteo3) $Cantidad = $Conteo3;
                else if($Conteo2) $Cantidad = $Conteo2;
                else if($Conteo1) $Cantidad = $Conteo1;
                $LP = $row_data["Lp"];
*/
                $cve_almac    = $row_data["cve_almac"];
                $idy_ubica    = $row_data["idy_ubica"];
                $NConteo      = $row_data["NConteo"];
                $cve_articulo = $row_data["cve_articulo"];
                $cve_lote     = $row_data["cve_lote"];
                $Cantidad     = $row_data["Cantidad"];
                $proveedor    = $row_data["proveedor"];
                $Cuarentena    = $row_data["cuarentena"];
                $LP = $row_data["ntarima"];

                $almacen_infinity = $cve_almac;
                $sql = ""; $res_kardex = ""; $ajuste = 0; $stockinicial = 0;
                if($NConteo > 0)
                {
                    if($LP)
                    {
                        $sql = "SELECT * FROM ts_existenciatarima 
                                WHERE idy_ubica = {$idy_ubica} AND cve_almac = {$cve_almac} AND cve_articulo = '{$cve_articulo}' AND lote = '{$cve_lote}' 
                                AND ntarima = {$LP}";
                        $res_kardex = mysqli_query($conn, $sql);
                        //$existe = mysqli_fetch_array($res_kardex)["existe"];
                        $existe = mysqli_num_rows($res_kardex);

                        if($existe == 0)
                        {
                            $sql = "INSERT INTO ts_existenciatarima(cve_almac, idy_ubica, cve_articulo, lote, Fol_Folio, ntarima, capcidad, existencia, Activo, ID_Proveedor, Cuarentena) VALUES ({$cve_almac}, {$idy_ubica}, '{$cve_articulo}', '{$cve_lote}', 0, {$LP}, 0, {$Cantidad}, 1, {$proveedor}, {$Cuarentena})";
                            //ON DUPLICATE KEY UPDATE existencia = {$Cantidad}
                            $res = mysqli_query($conn, $sql);
                        }
                        else
                        {
                            $stockinicial = mysqli_fetch_array($res_kardex)["existencia"];
                            $ajuste = $Cantidad - $stockinicial;
                            $sql = "UPDATE ts_existenciatarima SET existencia = {$Cantidad}
                                WHERE idy_ubica = {$idy_ubica} AND cve_almac = {$cve_almac} AND cve_articulo = '{$cve_articulo}' AND lote = '{$cve_lote}' 
                                AND ntarima = {$LP}";
                            //ON DUPLICATE KEY UPDATE existencia = {$Cantidad}
                            $res = mysqli_query($conn, $sql);
                        }
                    }
                    else
                    {
                        $sql = "SELECT * FROM ts_existenciapiezas
                                WHERE idy_ubica = {$idy_ubica} AND cve_almac = {$cve_almac} AND cve_articulo = '{$cve_articulo}' AND cve_lote = '{$cve_lote}'";
                        $res_kardex = mysqli_query($conn, $sql);
                        //$existe = mysqli_fetch_array($res_kardex)["existe"];
                        $existe = mysqli_num_rows($res_kardex);

                        if($existe == 0)
                        {
                            $sql = "INSERT INTO ts_existenciapiezas(cve_almac, idy_ubica, cve_articulo, cve_lote, Existencia, ID_Proveedor, Cuarentena) VALUES ({$cve_almac}, {$idy_ubica}, '{$cve_articulo}', '{$cve_lote}', {$Cantidad}, 2, {$Cuarentena})";
                            //ON DUPLICATE KEY UPDATE Existencia = {$Cantidad}
                            $res = mysqli_query($conn, $sql);
                        }
                        else
                        {
                            $stockinicial = mysqli_fetch_array($res_kardex)["Existencia"];
                            $ajuste = $Cantidad - $stockinicial;
                            $sql = "UPDATE ts_existenciapiezas SET Existencia = {$Cantidad}
                                WHERE idy_ubica = {$idy_ubica} AND cve_almac = {$cve_almac} AND cve_articulo = '{$cve_articulo}' AND cve_lote = '{$cve_lote}'";
                            $res = mysqli_query($conn, $sql);
                        }
                    }

                    $sql = "INSERT INTO t_cardex(cve_articulo, cve_lote, fecha, origen, destino, stockinicial, cantidad, ajuste, id_TipoMovimiento, cve_usuario, Cve_Almac) VALUES ('{$cve_articulo}', '{$cve_lote}', CURDATE(), 'Inv_{$inventario}', '{$idy_ubica}', {$stockinicial}, {$Cantidad}, {$ajuste}, 13, '{$clave_supervisor}',{$cve_almac})";
                    $res = mysqli_query($conn, $sql);

                    if($LP)
                    {
                        $sql = "INSERT INTO t_MovCharolas(id_kardex, Cve_Almac, ID_Contenedor, Fecha, Origen, Destino, Id_TipoMovimiento, Cve_Usuario, Status) VALUES 
                        ((SELECT MAX(id) FROM t_cardex), (SELECT clave FROM c_almacenp WHERE id = {$cve_almac}), {$LP}, CURDATE(),'Inv_{$inventario}', '{$idy_ubica}', 13, '{$clave_supervisor}', 'I')";
                        $res = mysqli_query($conn, $sql);
                    }

                        //$sql = "SELECT SUM(Existencia) as existencia_art_prod, (SELECT clave FROM c_almacenp WHERE id = {$cve_almac}) as clave_almacen FROM V_ExistenciaProduccion WHERE cve_articulo = '{$cve_articulo}' and cve_almac = '$cve_almac'";
  

                    /*
                    if($ejecutar_infinity)
                    {
                        $sql = "SELECT SUM(e.Existencia) AS existencia_art_prod, (SELECT clave FROM c_almacenp WHERE id = {$cve_almac}) AS clave_almacen, u.cve_umed 
                                FROM V_ExistenciaGral e
                                LEFT JOIN c_articulo a ON a.cve_articulo = e.cve_articulo
                                LEFT JOIN c_unimed u ON u.id_umed = a.unidadMedida
                                WHERE e.cve_articulo = '{$cve_articulo}' AND e.cve_almac = '{$cve_almac}'";
                        $query_inf = mysqli_query($conn, $sql);
                        $row_ord = mysqli_fetch_array($query_inf);
                        $existencia_art_prod = $row_ord['existencia_art_prod'];
                        $clave_almacen = $row_ord['clave_almacen'];
                        $um = $row_ord['cve_umed'];

                        $this->Ejecutar_Infinity_WS($conn, $cve_articulo, $cve_lote, $existencia_art_prod, $um, $clave_almacen);
                    }
                    */

                }

              }

/*
        if($ejecutar_infinity)
        {
            for($i = 0; $i < count($cve_articulo_arr); $i++)
                $this->Ejecutar_Infinity_WS($conn, $cve_articulo_arr[$i], $cve_lote_arr[$i], $existencia_art_prod_arr[$i], $um_arr[$i], $clave_almacen_arr[$i]);
        }
*/
        if($ejecutar_infinity)
        {
            $sql = "SELECT e.cve_articulo, SUM(e.Existencia) AS existencia_art_prod, (SELECT clave FROM c_almacenp WHERE id = {$almacen_infinity}) AS clave_almacen, u.cve_umed 
                    FROM V_ExistenciaGralProduccion e
                    LEFT JOIN c_articulo a ON a.cve_articulo = e.cve_articulo
                    LEFT JOIN c_unimed u ON u.id_umed = a.unidadMedida
                    WHERE e.cve_almac = '{$almacen_infinity}' AND e.cve_articulo IN (SELECT cve_articulo FROM t_invpiezas WHERE ID_Inventario = {$inventario} UNION SELECT cve_articulo FROM t_invtarima WHERE ID_Inventario = {$inventario}) AND e.tipo = 'ubicacion' 
                    GROUP BY cve_articulo

                    UNION

                    SELECT c.cve_articulo, c.Cantidad as existencia_art_prod, (SELECT clave FROM c_almacenp WHERE id = {$almacen_infinity}) AS clave_almacen, uc.cve_umed 
                    FROM (
                        $sql_cerrar
                    ) as c 
                    LEFT JOIN c_articulo ac ON ac.cve_articulo = c.cve_articulo
                    LEFT JOIN c_unimed uc ON uc.id_umed = ac.unidadMedida 
                    WHERE c.Cantidad = 0";
            $query_inf = mysqli_query($conn, $sql);
            while($row_ord = mysqli_fetch_array($query_inf))
            {
                $cve_articulo_ws = $row_ord['cve_articulo'];
                $existencia_art_prod = $row_ord['existencia_art_prod'];
                $clave_almacen = $row_ord['clave_almacen'];
                $um = $row_ord['cve_umed'];
                //$this->Ejecutar_Infinity_WS($conn, $cve_articulo_ws, $cve_lote, $existencia_art_prod, $um, $clave_almacen);
                $this->Ejecutar_Infinity_WS($conn, $cve_articulo_ws, "", $existencia_art_prod, $um, $clave_almacen, $inventario);
            }

        }


      //}if($tipo == 'fisico')
/*
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
        //}
      //}
      /*
      if($coincidencide_supervisor)
      {
        $this->response(400, [
                'statusText' => 'El usuario que intenta asignar, ya fue asignado en los conteos',
                'request' => $_POST
              ]);
      }
      */
      $this->response(200, [
          'statusText' => 'Inventario cerrado y supervisor asignado con éxito',
          'existencia_art_prod_arr' => $existencia_art_prod_arr,
          'sql' => $sql,
          'request' => $_POST
      ]);
    }


    public function existenciasPorUbicaciones()
    {
        $articulo = $this->getInput('articulo');
        $almacen = $this->getInput('almacen');
        $articulos_produccion = $this->getInput('articulos_produccion');
        $articulos_cuarentena = $this->getInput('articulos_cuarentena');
        $articulos_obsoletos = $this->getInput('articulos_obsoletos');



        $almacen = AlmacenP::where('clave', $almacen)->get(['id']);
        $almacen = $almacen[0];
        $almacen_id = $almacen->id;
        $tabla = 'V_ExistenciaGral';
        $sqlCuarentena = " AND IFNULL(e.Cuarentena, 0) = 0 ";
        $sqlObsoletos  = " AND IF(art.Caduca = 'S', l.Caducidad, CURDATE()+1) > CURDATE() ";

        if($articulos_produccion == 1) $tabla = 'V_ExistenciaGralProduccion';
        if($articulos_cuarentena == 1) $sqlCuarentena = "";
        if($articulos_obsoletos == 1) $sqlObsoletos = "";

    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql = "SET NAMES 'utf8mb4';";
    $query = mysqli_query($conn, $sql);   

        $data = Capsule::select
        (Capsule::raw(
            "SELECT DISTINCT
                u.cve_almac id,
                u.CodigoCSD ubicacion,
                e.cve_articulo cve_articulo,
                e.cve_lote lote,
                IF(art.Caduca = 'S', DATE_FORMAT(l.Caducidad, '%d-%m-%Y'), '') AS caducidad,
                e.tipo,
                e.Existencia cantidad
            FROM {$tabla} e
                LEFT JOIN c_articulo art ON art.cve_articulo = e.cve_articulo
                LEFT JOIN c_ubicacion u ON e.cve_ubicacion = u.idy_ubica
                LEFT JOIN c_almacen a ON a.cve_almac = u.cve_almac AND e.cve_almac = a.cve_almacenp
                LEFT JOIN c_lotes l ON l.Lote = e.cve_lote AND l.cve_articulo = e.cve_articulo
            WHERE e.cve_almac = '{$almacen_id}' AND a.cve_almacenp = '{$almacen_id}' AND e.cve_articulo = '{$articulo}' AND e.Existencia > 0 {$sqlCuarentena} {$sqlObsoletos} ")
        );

        $rows = [];
        $count = 0;
        foreach( $data as $value)
        {
          if($value->tipo == 'ubicacion')
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


    public function exportar_comparativo()
    {

   $almacen = $_GET['almacen'];

    $filtro_where_concentrado = ""; $filtro_where_concentrado_na = "";
    if(isset($_GET['filtro_concentrado']))
    {
        if($_GET['filtro_concentrado'])
        {
            $filtro_where_concentrado    = $_GET['filtro_concentrado'];
            $filtro_where_concentrado_na = $_GET['filtro_concentrado'];
        }
    }
    else if(isset($_POST['filtro_concentrado']))
    {
        if($_POST['filtro_concentrado'])
        {
            $filtro_where_concentrado    = $_POST['filtro_concentrado'];
            $filtro_where_concentrado_na = $_POST['filtro_concentrado'];
        }
    }
 
    if($filtro_where_concentrado)
        $filtro_where_concentrado .= " AND concentrado.clave_alm = '{$almacen}'";
    else
        $filtro_where_concentrado = " WHERE 1 AND concentrado.clave_alm = '{$almacen}'";


    if($filtro_where_concentrado_na)
        $filtro_where_concentrado_na .= " ";
    else
        $filtro_where_concentrado_na = " WHERE 1 ";


    $filtro_diferencias = ""; $order_by_diferencias = "";
    if(isset($_GET['filtro_diferencias_select']))
    {
        if($_GET['filtro_diferencias_select'])
            $filtro_diferencias = $_GET['filtro_diferencias_select'];
    }

    if($filtro_diferencias)
    {
        $array_diferencias = explode("SEPARADOR", $filtro_diferencias);
        $filtro_diferencias = $array_diferencias[0];
        $order_by_diferencias = $array_diferencias[1];
    }

    $sqlLotes = "";
    if(isset($_GET['lotes']))
    {
        $lote = $_GET['lotes'];
        if($_GET['lotes'])
            $sqlLotes = " AND concentrado.lote LIKE '%{$lote}%'";
    }

    $ands = ""; $ands2 = "";
    if (!empty($search)){
        $ands.=" and a.cve_articulo like '%".$search."%' ";
        $ands2.=" and ar.cve_articulo like '%".$search."%' ";
    }
    $articulo = $_GET["articulo"];
    $proveedor = $_GET["proveedor"];
    $grupo = $_GET["grupo"];

    $sqlArticulo1 = !empty($articulo) ? " AND a.cve_articulo = '{$articulo}' " : "";
    $sqlArticulo2 = !empty($articulo) ? " AND ar.cve_articulo = '{$articulo}' " : "";

    $sqlProveedor = !empty($proveedor) ? " AND p.ID_Proveedor = '{$proveedor}' " : "";

    $sqlGrupo = !empty($grupo) ? " AND g.id = '{$grupo}' " : "";

    $ands = $sqlArticulo1.$sqlProveedor.$sqlGrupo;
    $ands2 = $sqlArticulo2.$sqlProveedor.$sqlGrupo;
    //$sql1 = 'SELECT * FROM c_almacenp WHERE id = "'.$almacen.'"';
    //$result = getArraySQL($sql1);  
 
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql = "SELECT SUM(Existencia) AS cantidad, COUNT(DISTINCT cve_articulo) AS total FROM V_ExistenciaGralProduccion WHERE Existencia > 0;";

    $query = mysqli_query($conn, $sql);   
    if($query->num_rows > 0){
        $row = mysqli_fetch_row($query);
        $cantidad = $row[0];
        $count = $row[1];
    }

    $filtro_clientes = "";
    if(isset($_GET['cve_proveedor']))
    {
      if($_GET['cve_proveedor'])
      {
          $proveedor = $_GET['cve_proveedor'];
          $filtro_clientes = "AND concentrado.id_proveedor = {$proveedor}";
      }
    }

    if(isset($_POST['cve_proveedor']))
    {
      if($_POST['cve_proveedor'])
      {
          $proveedor = $_POST['cve_proveedor'];
          $filtro_clientes = "AND concentrado.id_proveedor = {$proveedor}";
      }
    }

    $sql_in_na = " ar.cve_articulo NOT IN (SELECT  
            z.cve_articulo
        FROM td_aduana z
            LEFT JOIN th_aduana adz ON z.num_orden = adz.num_pedimento) AND 
         ar.cve_articulo NOT IN (SELECT 
        cve_articulo FROM V_ExistenciaGralProduccion)";

    $sql_in = " AND ar.cve_articulo NOT IN (SELECT  
            z.cve_articulo
        FROM td_aduana z
            LEFT JOIN th_aduana adz ON z.num_orden = adz.num_pedimento
        WHERE adz.Cve_Almac = '{$almacen}') AND ar.cve_articulo NOT IN (SELECT 
        cve_articulo FROM V_ExistenciaGralProduccion)";

    //$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    $sql = "";
    //$almacen = '100';
    if($almacen != '')
    {
    $sql = "
    SELECT concentrado.ubicacion, concentrado.folio, concentrado.clave_alm, concentrado.Nombre_Almacen, concentrado.grupo, 
       concentrado.id_proveedor, concentrado.proveedor, concentrado.articulo, concentrado.nombre, concentrado.lote, concentrado.caducidad, concentrado.caducidad_sap, concentrado.Existencia_SAP, SUM(concentrado.existencia) AS existencia
    FROM (
    SELECT  DISTINCT
            vg.Cve_Contenedor AS Cve_Contenedor,
            vg.cve_ubicacion AS ubicacion,
            ad.num_orden AS folio,
            alm.clave AS clave_alm,
            alm.nombre AS Nombre_Almacen,
            g.des_gpoart AS grupo,
            p.ID_Proveedor AS id_proveedor,
            p.Nombre AS proveedor,
            a.cve_articulo AS articulo,
            IFNULL(a.des_articulo, '--') AS nombre,
            IFNULL(l.Lote, '') AS lote,
            IF(a.Caduca = 'S', DATE_FORMAT(l.Caducidad, '%d-%m-%Y'), '') as caducidad,
            IF(a.Caduca = 'S', DATE_FORMAT(tm.Caducidad, '%d-%m-%Y'), '') as caducidad_sap,
        IFNULL(tm.Num_Cantidad, 0) AS Existencia_SAP,
        vg.Existencia as existencia

        FROM c_articulo a
            LEFT JOIN c_gpoarticulo g ON g.cve_gpoart = a.grupo
            LEFT JOIN th_aduana v  ON v.Cve_Almac = '{$almacen}'
            LEFT JOIN td_aduana ad ON v.num_pedimento = ad.num_orden AND ad.cve_articulo != ''
            LEFT JOIN V_ExistenciaGral vg ON a.cve_articulo = vg.cve_articulo
            LEFT JOIN c_lotes l ON l.cve_articulo = a.cve_articulo AND l.Lote = vg.cve_lote
            LEFT JOIN c_almacenp alm ON alm.clave = '{$almacen}'
            LEFT JOIN c_proveedores p ON p.ID_Proveedor = vg.ID_Proveedor
            LEFT JOIN t_match tm ON tm.Cve_Almac = alm.clave AND tm.Cve_Articulo = a.cve_articulo AND p.cve_proveedor = tm.Cve_Proveedor 
                        AND tm.Cve_Lote = vg.cve_lote
        WHERE a.cve_articulo IN (SELECT cve_articulo FROM V_ExistenciaGralProduccion) AND vg.Id_Proveedor = p.ID_Proveedor 
        AND vg.cve_almac = (SELECT id FROM c_almacenp WHERE clave = '{$almacen}') AND vg.tipo = 'ubicacion'
        $ands
        GROUP BY vg.cve_ubicacion,alm.clave, g.id, a.cve_articulo, p.ID_Proveedor, l.Lote, vg.Cve_Contenedor

        UNION

        SELECT  
            '' AS Cve_Contenedor,
            '' AS ubicacion,
            '' AS folio,
            c_almacenp.clave AS clave_alm,
            c_almacenp.nombre AS Nombre_Almacen,
            g.des_gpoart AS grupo,
            '' as id_proveedor,
            '' AS proveedor,
            ar.cve_articulo AS articulo,
            IFNULL(ar.des_articulo, '--') AS nombre,
            '' AS lote,
            '' as caducidad,
            '' as caducidad_sap,
            0 AS Existencia_SAP,
            0 AS existencia
        FROM c_articulo ar
            LEFT JOIN c_gpoarticulo g ON g.cve_gpoart = ar.grupo
            LEFT JOIN t_artcompuesto art ON art.Cve_Articulo = ar.cve_articulo
            LEFT JOIN c_proveedores p ON p.ID_Proveedor = ar.ID_Proveedor
            LEFT JOIN c_almacenp ON c_almacenp.id = ar.cve_almac
        WHERE c_almacenp.clave = '{$almacen}' $sql_in 
            $ands2
        GROUP BY ar.cve_articulo
        ORDER BY Nombre_Almacen
        ) AS concentrado {$filtro_where_concentrado} {$filtro_diferencias} {$sqlLotes}
        GROUP BY concentrado.grupo, concentrado.id_proveedor, concentrado.articulo, concentrado.lote
        {$order_by_diferencias}";
    }
    else
    {
    $sql = "
    SELECT concentrado.ubicacion, concentrado.folio, concentrado.clave_alm, concentrado.Nombre_Almacen, concentrado.grupo, 
       concentrado.id_proveedor, concentrado.proveedor, concentrado.articulo, concentrado.nombre, concentrado.lote, concentrado.caducidad, concentrado.caducidad_sap, concentrado.Existencia_SAP, SUM(concentrado.existencia) AS existencia
    FROM (
    SELECT  DISTINCT
            vg.cve_ubicacion AS ubicacion,
            ad.num_orden AS folio,
            alm.clave AS clave_alm,
            alm.nombre AS Nombre_Almacen,
            g.des_gpoart AS grupo,
            p.ID_Proveedor AS id_proveedor,
            p.Nombre AS proveedor,
            a.cve_articulo AS articulo,
            IFNULL(a.des_articulo, '--') AS nombre,
            IFNULL(l.Lote, '') AS lote,
            IF(a.Caduca = 'S', DATE_FORMAT(l.Caducidad, '%d-%m-%Y'), '') as caducidad,
            IF(a.Caduca = 'S', DATE_FORMAT(tm.Caducidad, '%d-%m-%Y'), '') as caducidad_sap,
        IFNULL(tm.Num_Cantidad, 0) AS Existencia_SAP,
        vg.Existencia as existencia

        FROM c_articulo a
            LEFT JOIN c_gpoarticulo g ON g.cve_gpoart = a.grupo
            LEFT JOIN V_ExistenciaGral vg ON a.cve_articulo = vg.cve_articulo
            LEFT JOIN c_lotes l ON l.cve_articulo = a.cve_articulo AND l.Lote = vg.cve_lote
            LEFT JOIN c_almacenp alm ON alm.id = vg.cve_almac
            LEFT JOIN th_aduana v  ON v.Cve_Almac = alm.clave
            LEFT JOIN td_aduana ad ON v.num_pedimento = ad.num_orden AND ad.cve_articulo != ''
            LEFT JOIN c_proveedores p ON p.ID_Proveedor = vg.ID_Proveedor
            LEFT JOIN t_match tm ON tm.Cve_Almac = alm.clave AND tm.Cve_Articulo = a.cve_articulo AND p.cve_proveedor = tm.Cve_Proveedor 
                        AND tm.Cve_Lote = vg.cve_lote
        WHERE a.cve_articulo IN (SELECT cve_articulo FROM V_ExistenciaGralProduccion) AND vg.Id_Proveedor = p.ID_Proveedor 
        AND vg.cve_almac = (SELECT id FROM c_almacenp WHERE clave = alm.clave) AND vg.tipo = 'ubicacion'
        $ands
        GROUP BY vg.cve_ubicacion,alm.clave, g.id, a.cve_articulo, p.ID_Proveedor, l.Lote
#v.Cve_Almac = '{$almacen}' AND
        UNION

        SELECT  
            '' AS ubicacion,
            '' AS folio,
            c_almacenp.clave AS clave_alm,
            c_almacenp.nombre AS Nombre_Almacen,
            g.des_gpoart AS grupo,
            '' as id_proveedor,
            '' AS proveedor,
            ar.cve_articulo AS articulo,
            IFNULL(ar.des_articulo, '--') AS nombre,
            '' AS lote,
            '' as caducidad,
            '' as caducidad_sap,
            0 AS Existencia_SAP,
            0 AS existencia
        FROM c_articulo ar
            LEFT JOIN c_gpoarticulo g ON g.cve_gpoart = ar.grupo
            LEFT JOIN t_artcompuesto art ON art.Cve_Articulo = ar.cve_articulo
            LEFT JOIN c_proveedores p ON p.ID_Proveedor = ar.ID_Proveedor
            LEFT JOIN c_almacenp ON c_almacenp.id = ar.cve_almac
        WHERE $sql_in_na
            $ands2
        GROUP BY ar.cve_articulo
        ) AS concentrado {$filtro_where_concentrado_na} {$filtro_diferencias} {$sqlLotes}
        GROUP BY concentrado.grupo, concentrado.id_proveedor, concentrado.articulo, concentrado.lote
        {$order_by_diferencias}";
    }


    $res = "";
    if (!($res = mysqli_query($conn, $sql))) 
    {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

//***************************************************************************************************

        $columnas = [
          'Clave',
          utf8_decode('Descripción'),
          'Lote',
          'Caducidad',
          'Caducidad SAP',
          'Existencia',
          'Existencia SAP',
          'Diferencia',
          'Proveedor',
          'Almacen'
        ];

        $filename = "Comparativo de Existencias" . ".xls";
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
/*
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
*/
            echo utf8_decode($row->articulo) . "\t";
            echo utf8_decode($row->nombre) . "\t";
            echo $row->lote . "\t";
            echo $row->caducidad . "\t";
            echo $row->caducidad_sap . "\t";
            echo $row->existencia . "\t";
            echo $row->Existencia_SAP . "\t";
            echo ($row->existencia-$row->Existencia_SAP) . "\t";
            echo utf8_decode($row->proveedor) . "\t";
            echo utf8_decode($row->Nombre_Almacen) . "\t";
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


    public function exportar_concentrado()
    {

//***************************************************************************************************
    //$almacen = $_GET['almacen'];
    //$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
/*
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
    ";*/

    $almacen = $_GET['almacen'];
////////////
    $check_sin_almacen = 0;
    if(isset($_GET['check_almacen']))
    {
        $check_sin_almacen = $_GET['check_almacen'];
    }
///////////
    $filtro_where_concentrado = "WHERE 1 ";
    if(isset($_GET['filtro_concentrado']))
    {
        if($_GET['filtro_concentrado'])
            $filtro_where_concentrado = $_GET['filtro_concentrado'];
    }
 
    $ands = ""; $ands2 = "";
    if (!empty($search)){
        $ands.=" and a.cve_articulo like '%".$search."%' ";
        $ands2.=" and ar.cve_articulo like '%".$search."%' ";
    }
    $articulo = $_GET["articulo"];
    $proveedor = $_GET["proveedor"];
    $grupo = $_GET["grupo"];

    $sqlArticulo1 = !empty($articulo) ? " AND a.cve_articulo = '{$articulo}' " : "";
    $sqlArticulo2 = !empty($articulo) ? " AND ar.cve_articulo = '{$articulo}' " : "";

    $sqlProveedor = !empty($proveedor) ? " AND p.ID_Proveedor = '{$proveedor}' " : "";

    $sqlGrupo = !empty($grupo) ? " AND g.id = '{$grupo}' " : "";

    $ands = $sqlArticulo1.$sqlProveedor.$sqlGrupo;
    $ands2 = $sqlArticulo2.$sqlProveedor.$sqlGrupo;
    //$sql1 = 'SELECT * FROM c_almacenp WHERE id = "'.$almacen.'"';
    //$result = getArraySQL($sql1);  
 
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $sql = "SET NAMES 'utf8mb4';";
    $query = mysqli_query($conn, $sql);   

/*
    $sql = "SELECT SUM(Existencia) AS cantidad, COUNT(DISTINCT cve_articulo) AS total FROM V_ExistenciaGralProduccion WHERE Existencia > 0;";

    $query = mysqli_query($conn, $sql);   
    if($query->num_rows > 0){
        $row = mysqli_fetch_row($query);
        $cantidad = $row[0];
        $count = $row[1];
    }
*/
    $filtro_clientes = "";
    if(isset($_GET['cve_proveedor']))
    {
      if($_GET['cve_proveedor'])
      {
          $proveedor = $_GET['cve_proveedor'];
          $filtro_clientes = "AND concentrado.id_proveedor = {$proveedor}";
      }
    }

    if(isset($_POST['cve_proveedor']))
    {
      if($_POST['cve_proveedor'])
      {
          $proveedor = $_POST['cve_proveedor'];
          $filtro_clientes = "AND concentrado.id_proveedor = {$proveedor}";
      }
    }

    $sql_in = " AND ar.cve_articulo NOT IN (SELECT  
            z.cve_articulo
        FROM td_aduana z
            LEFT JOIN th_aduana adz ON z.num_orden = adz.num_pedimento
        WHERE adz.Cve_Almac = '{$almacen}') AND ar.cve_articulo NOT IN (SELECT 
        cve_articulo FROM V_ExistenciaGralProduccion)";

    if($check_sin_almacen == 1)
    $sql_in = " AND ar.cve_articulo NOT IN (SELECT  
            z.cve_articulo
        FROM td_aduana z
            LEFT JOIN th_aduana adz ON z.num_orden = adz.num_pedimento
        ) AND ar.cve_articulo NOT IN (SELECT 
        cve_articulo FROM V_ExistenciaGralProduccion)";

    $sql = "";
    if($almacen != '')
    {
/*
    $sql = "
    SELECT *, SUM(concentrado.existencia_conc) AS existencia FROM (
    SELECT  DISTINCT
            #ad.num_orden AS folio,
            alm.nombre AS Nombre_Almacen,
            g.des_gpoart AS grupo,
            p.ID_Proveedor AS id_proveedor,
            p.Nombre AS proveedor,
            a.cve_articulo AS articulo,
            IFNULL(a.des_articulo, '--') AS nombre,
            IFNULL(a.cajas_palet, 0) AS cajasxpallets,
            IFNULL(a.num_multiplo, 0) AS piezasxcajas,
            0 AS Pallet,
            0 AS Caja, 
            0 AS Piezas,
            #IFNULL((SELECT SUM(td.cantidad) FROM td_aduana td, th_aduana th WHERE th.num_pedimento = td.num_orden AND th.Cve_Almac = '{$almacen}' AND td.cve_articulo = a.cve_articulo AND th.status = 'C'), 0) AS Prod_OC,
            #IFNULL((SELECT SUM(ve.Existencia) FROM V_ExistenciaGralProduccion ve WHERE ve.cve_articulo = a.cve_articulo AND ve.cve_almac = (SELECT id FROM c_almacenp WHERE clave = '{$almacen}')  AND ve.tipo = 'area'), 0) AS Prod_RTM, 
            #AND ve.cve_lote = ad.cve_lote
            #IFNULL((SELECT SUM(Cantidad) FROM t_recorrido_surtido WHERE Cve_articulo = a.cve_articulo), 0) AS Res_Pick,
            #IFNULL(IF(IFNULL((SELECT COUNT(vp.Cuarentena) FROM V_ExistenciaGralProduccion vp WHERE vp.cve_articulo = a.cve_articulo AND vp.Cuarentena = 1 AND vp.tipo = 'ubicacion' GROUP BY vp.cve_articulo), 0) = 0, 0, (SELECT SUM(vp.Cantidad) FROM t_movcuarentena vp WHERE vp.Cve_Articulo = a.cve_articulo GROUP BY vp.Cve_Articulo)), 0) AS Prod_QA,
            #IF(a.caduca = 'S', IFNULL((SELECT SUM(vp.Existencia) FROM V_ExistenciaGralProduccion vp, c_lotes lo, c_ubicacion cu WHERE cu.idy_ubica = vp.cve_ubicacion AND cu.AreaProduccion != 'S' AND vp.cve_articulo = a.cve_articulo AND a.cve_articulo = lo.cve_articulo AND vp.cve_lote = lo.LOTE AND lo.Caducidad != '0000-00-00' AND lo.Caducidad != '' AND lo.Caducidad < CURDATE() AND vp.tipo = 'ubicacion'), 0), 0) AS Obsoletos,
            #IFNULL((SELECT SUM(tds.Cantidad) FROM td_surtidopiezas tds, rel_uembarquepedido rep, th_pedido tp WHERE tds.fol_folio = rep.fol_folio AND rep.fol_folio = tp.Fol_folio AND tp.status = 'C' AND tds.cve_almac = (SELECT id FROM c_almacenp WHERE clave = '{$almacen}') AND tds.Cve_articulo = a.cve_articulo GROUP BY tds.Cve_articulo), 0) AS RTS,
            #IFNULL((SELECT SUM(vp.Existencia) FROM V_ExistenciaGralProduccion vp, c_ubicacion cu WHERE cu.idy_ubica = vp.cve_ubicacion AND cu.AreaProduccion = 'S' AND vp.cve_articulo = a.cve_articulo AND vp.tipo = 'ubicacion'), 0) AS Prod_kit, 

            #(SELECT IFNULL(SUM(e.Existencia), 0) FROM V_ExistenciaGralProduccion e 
            #WHERE e.cve_almac = (SELECT id FROM c_almacenp WHERE clave = '{$almacen}')  
            #AND e.tipo = 'ubicacion' AND e.Existencia > 0  AND e.cve_articulo = a.cve_articulo
            #) AS existencia

            #(SELECT IFNULL(SUM(e.Existencia), 0) FROM ts_existenciapiezas e 
            #WHERE e.cve_almac = (SELECT id FROM c_almacenp WHERE clave = '{$almacen}')  
            #AND e.Existencia > 0  AND e.cve_articulo = a.cve_articulo
            #) AS existencia
        (SELECT IFNULL(SUM(e.Existencia), 0) FROM V_ExistenciaGral e 
        WHERE e.cve_almac = (SELECT id FROM c_almacenp WHERE clave = '{$almacen}') 
        AND e.Existencia > 0 AND e.cve_articulo = a.cve_articulo AND e.tipo = 'ubicacion' AND e.Id_Proveedor = p.ID_Proveedor
        ) AS existencia_conc

        FROM c_articulo a
            LEFT JOIN c_gpoarticulo g ON g.cve_gpoart = a.grupo
            #LEFT JOIN th_aduana v  ON v.Cve_Almac = '{$almacen}'
            LEFT JOIN c_almacenp alm ON alm.clave = '{$almacen}'
            #LEFT JOIN td_aduana ad ON v.num_pedimento = ad.num_orden
            LEFT JOIN V_ExistenciaGral vg ON a.cve_articulo = vg.cve_articulo AND vg.tipo = 'ubicacion' 
            LEFT JOIN c_proveedores p ON p.ID_Proveedor = vg.ID_Proveedor
        WHERE a.cve_articulo IN (SELECT cve_articulo FROM V_ExistenciaGralProduccion) AND vg.Id_Proveedor = p.ID_Proveedor 
        $ands
        AND alm.clave = '{$almacen}' AND vg.Existencia > 0 AND vg.cve_almac = (SELECT id FROM c_almacenp WHERE clave = '{$almacen}')
        GROUP BY a.cve_articulo, p.ID_Proveedor

        UNION

        SELECT  
            #'' AS folio,
            c_almacenp.nombre AS Nombre_Almacen,
            g.des_gpoart AS grupo,
            '' as id_proveedor,
            '' AS proveedor,
            ar.cve_articulo AS articulo,
            IFNULL(ar.des_articulo, '--') AS nombre,
            IFNULL(ar.cajas_palet, 0) AS cajasxpallets,
            IFNULL(ar.num_multiplo, 0) AS piezasxcajas, 
            0 AS Pallet,
            0 AS Caja, 
            0 AS Piezas,
            #0 AS Prod_OC,
            #0 AS Prod_RTM,
            #0 AS Res_Pick,
            #0 AS Prod_QA,
            #IF(ar.caduca = 'S', IFNULL((SELECT SUM(vp.Existencia) FROM V_ExistenciaGralProduccion vp, c_lotes lo, c_ubicacion cu WHERE cu.idy_ubica = vp.cve_ubicacion AND cu.AreaProduccion != 'S' AND vp.cve_articulo = ar.cve_articulo AND ar.cve_articulo = lo.cve_articulo AND vp.cve_lote = lo.LOTE AND lo.Caducidad != '0000-00-00' AND lo.Caducidad != '' AND lo.Caducidad < CURDATE() AND vp.tipo = 'ubicacion'), 0), 0) AS Obsoletos,
            #IFNULL((SELECT SUM(tds.Cantidad) FROM td_surtidopiezas tds, rel_uembarquepedido rep, th_pedido tp WHERE tds.fol_folio = rep.fol_folio AND rep.fol_folio = tp.Fol_folio AND tp.status = 'C' AND tds.cve_almac = (SELECT id FROM c_almacenp WHERE clave = '{$almacen}') AND tds.Cve_articulo = ar.cve_articulo GROUP BY tds.Cve_articulo), 0) AS RTS,
            #art.Cantidad_Producida AS Prod_kit,
            0 AS existencia_conc
        FROM c_articulo ar
            LEFT JOIN c_gpoarticulo g ON g.cve_gpoart = ar.grupo
            LEFT JOIN t_artcompuesto art ON art.Cve_Articulo = ar.cve_articulo
            LEFT JOIN c_proveedores p ON p.ID_Proveedor = ar.ID_Proveedor
            LEFT JOIN c_almacenp ON c_almacenp.id = ar.cve_almac
        WHERE c_almacenp.clave = '{$almacen}' $sql_in 
            $ands2
        GROUP BY ar.cve_articulo
        ORDER BY Nombre_Almacen
        ) AS concentrado {$filtro_where_concentrado} {$filtro_clientes}
        GROUP BY concentrado.articulo
        ";

if($check_sin_almacen == 1)
    $sql = "
    SELECT *, SUM(concentrado.existencia_conc) AS existencia FROM (
    SELECT  DISTINCT
            #ad.num_orden AS folio,
            alm.nombre AS Nombre_Almacen,
            g.des_gpoart AS grupo,
            p.ID_Proveedor AS id_proveedor,
            p.Nombre AS proveedor,
            a.cve_articulo AS articulo,
            IFNULL(a.des_articulo, '--') AS nombre,
            IFNULL(a.cajas_palet, 0) AS cajasxpallets,
            IFNULL(a.num_multiplo, 0) AS piezasxcajas,
            0 AS Pallet,
            0 AS Caja, 
            0 AS Piezas,
            #IFNULL((SELECT SUM(td.cantidad) FROM td_aduana td, th_aduana th WHERE th.num_pedimento = td.num_orden AND th.Cve_Almac = '{$almacen}' AND td.cve_articulo = a.cve_articulo AND th.status = 'C'), 0) AS Prod_OC,
            #IFNULL((SELECT SUM(ve.Existencia) FROM V_ExistenciaGralProduccion ve WHERE ve.cve_articulo = a.cve_articulo AND ve.cve_almac = (SELECT id FROM c_almacenp WHERE clave = '{$almacen}')  AND ve.tipo = 'area'), 0) AS Prod_RTM, 
            #AND ve.cve_lote = ad.cve_lote
            #IFNULL((SELECT SUM(Cantidad) FROM t_recorrido_surtido WHERE Cve_articulo = a.cve_articulo), 0) AS Res_Pick,
            #IFNULL(IF(IFNULL((SELECT COUNT(vp.Cuarentena) FROM V_ExistenciaGralProduccion vp WHERE vp.cve_articulo = a.cve_articulo AND vp.Cuarentena = 1 AND vp.tipo = 'ubicacion' GROUP BY vp.cve_articulo), 0) = 0, 0, (SELECT SUM(vp.Cantidad) FROM t_movcuarentena vp WHERE vp.Cve_Articulo = a.cve_articulo GROUP BY vp.Cve_Articulo)), 0) AS Prod_QA,
            #IF(a.caduca = 'S', IFNULL((SELECT SUM(vp.Existencia) FROM V_ExistenciaGralProduccion vp, c_lotes lo, c_ubicacion cu WHERE cu.idy_ubica = vp.cve_ubicacion AND cu.AreaProduccion != 'S' AND vp.cve_articulo = a.cve_articulo AND a.cve_articulo = lo.cve_articulo AND vp.cve_lote = lo.LOTE AND lo.Caducidad != '0000-00-00' AND lo.Caducidad != '' AND lo.Caducidad < CURDATE() AND vp.tipo = 'ubicacion'), 0), 0) AS Obsoletos,
            #IFNULL((SELECT SUM(tds.Cantidad) FROM td_surtidopiezas tds, rel_uembarquepedido rep, th_pedido tp WHERE tds.fol_folio = rep.fol_folio AND rep.fol_folio = tp.Fol_folio AND tp.status = 'C' AND tds.cve_almac = (SELECT id FROM c_almacenp WHERE clave = '{$almacen}') AND tds.Cve_articulo = a.cve_articulo GROUP BY tds.Cve_articulo), 0) AS RTS,
            #IFNULL((SELECT SUM(vp.Existencia) FROM V_ExistenciaGralProduccion vp, c_ubicacion cu WHERE cu.idy_ubica = vp.cve_ubicacion AND cu.AreaProduccion = 'S' AND vp.cve_articulo = a.cve_articulo AND vp.tipo = 'ubicacion'), 0) AS Prod_kit, 

            #(SELECT IFNULL(SUM(e.Existencia), 0) FROM V_ExistenciaGralProduccion e 
            #WHERE e.cve_almac = (SELECT id FROM c_almacenp WHERE clave = '{$almacen}')  
            #AND e.tipo = 'ubicacion' AND e.Existencia > 0  AND e.cve_articulo = a.cve_articulo
            #) AS existencia

            #(SELECT IFNULL(SUM(e.Existencia), 0) FROM ts_existenciapiezas e 
            #WHERE e.cve_almac = (SELECT id FROM c_almacenp WHERE clave = '{$almacen}')  
            #AND e.Existencia > 0  AND e.cve_articulo = a.cve_articulo
            #) AS existencia
        (SELECT IFNULL(SUM(e.Existencia), 0) FROM V_ExistenciaGral e 
        WHERE e.Existencia > 0 AND e.cve_articulo = a.cve_articulo AND e.tipo = 'ubicacion' AND e.Id_Proveedor = p.ID_Proveedor
        ) AS existencia_conc

        FROM c_articulo a
            LEFT JOIN c_gpoarticulo g ON g.cve_gpoart = a.grupo
            #LEFT JOIN th_aduana v  ON v.Cve_Almac = '{$almacen}'
            #LEFT JOIN td_aduana ad ON v.num_pedimento = ad.num_orden
            LEFT JOIN V_ExistenciaGral vg ON a.cve_articulo = vg.cve_articulo AND vg.tipo = 'ubicacion' 
            LEFT JOIN c_almacenp alm ON alm.id = vg.cve_almac
            LEFT JOIN c_proveedores p ON p.ID_Proveedor = vg.ID_Proveedor
        WHERE a.cve_articulo IN (SELECT cve_articulo FROM V_ExistenciaGralProduccion) AND vg.Id_Proveedor = p.ID_Proveedor 
        $ands
        AND vg.Existencia > 0 AND alm.id = vg.cve_almac
        GROUP BY a.cve_articulo, p.ID_Proveedor

        UNION

        SELECT  
            #'' AS folio,
            c_almacenp.nombre AS Nombre_Almacen,
            g.des_gpoart AS grupo,
            '' as id_proveedor,
            '' AS proveedor,
            ar.cve_articulo AS articulo,
            IFNULL(ar.des_articulo, '--') AS nombre,
            IFNULL(ar.cajas_palet, 0) AS cajasxpallets,
            IFNULL(ar.num_multiplo, 0) AS piezasxcajas, 
            0 AS Pallet,
            0 AS Caja, 
            0 AS Piezas,
            #0 AS Prod_OC,
            #0 AS Prod_RTM,
            #0 AS Res_Pick,
            #0 AS Prod_QA,
            #IF(ar.caduca = 'S', IFNULL((SELECT SUM(vp.Existencia) FROM V_ExistenciaGralProduccion vp, c_lotes lo, c_ubicacion cu WHERE cu.idy_ubica = vp.cve_ubicacion AND cu.AreaProduccion != 'S' AND vp.cve_articulo = ar.cve_articulo AND ar.cve_articulo = lo.cve_articulo AND vp.cve_lote = lo.LOTE AND lo.Caducidad != '0000-00-00' AND lo.Caducidad != '' AND lo.Caducidad < CURDATE() AND vp.tipo = 'ubicacion'), 0), 0) AS Obsoletos,
            #IFNULL((SELECT SUM(tds.Cantidad) FROM td_surtidopiezas tds, rel_uembarquepedido rep, th_pedido tp WHERE tds.fol_folio = rep.fol_folio AND rep.fol_folio = tp.Fol_folio AND tp.status = 'C' AND tds.cve_almac = (SELECT id FROM c_almacenp WHERE clave = '{$almacen}') AND tds.Cve_articulo = ar.cve_articulo GROUP BY tds.Cve_articulo), 0) AS RTS,
            #art.Cantidad_Producida AS Prod_kit,
            0 AS existencia_conc
        FROM c_articulo ar
            LEFT JOIN c_gpoarticulo g ON g.cve_gpoart = ar.grupo
            LEFT JOIN t_artcompuesto art ON art.Cve_Articulo = ar.cve_articulo
            LEFT JOIN c_proveedores p ON p.ID_Proveedor = ar.ID_Proveedor
            LEFT JOIN Rel_Articulo_Almacen ra ON ra.Cve_Articulo = ar.cve_articulo
            LEFT JOIN c_almacenp ON c_almacenp.id = ra.cve_almac
        WHERE 1 $sql_in 
            $ands2
        GROUP BY ar.cve_articulo
        ORDER BY Nombre_Almacen
        ) AS concentrado {$filtro_where_concentrado} {$filtro_clientes}
        GROUP BY concentrado.articulo
        ";
*/
        $sqlAlmacen = " AND alm.id = (SELECT id FROM c_almacenp WHERE clave = '{$almacen}') ";
        if($check_sin_almacen == 1)
          $sqlAlmacen = "";

        $sql = "
        SELECT * FROM (
        SELECT 
            a.id AS id_articulo,
            alm.clave AS clave_alm,
            alm.nombre AS Nombre_Almacen,
            IFNULL(g.des_gpoart, '') AS grupo,
            p.ID_Proveedor AS id_proveedor,
            p.Nombre AS proveedor,
            a.cve_articulo AS cve_articulo,
            IFNULL(a.des_articulo, '--') AS articulo,
            IFNULL(a.cajas_palet, 0) AS cajasxpallets,
            IFNULL(a.num_multiplo, 0) AS piezasxcajas,
            0 AS Pallet,
            0 AS Caja, 
            0 AS Piezas,

        (SELECT IFNULL(SUM(e.Existencia), 0) FROM V_ExistenciaGralProduccion e 
        WHERE e.Existencia > 0 AND e.cve_articulo = a.cve_articulo AND e.tipo = 'ubicacion' AND e.cve_almac = alm.id
        ) AS existencia

        FROM c_articulo a
        LEFT JOIN c_gpoarticulo g ON g.cve_gpoart = a.grupo
        LEFT JOIN Rel_Articulo_Almacen ra ON ra.Cve_Articulo = a.cve_articulo
        LEFT JOIN c_almacenp alm ON alm.id = ra.Cve_Almac AND alm.Activo = 1
        LEFT JOIN V_ExistenciaGralProduccion v ON v.cve_articulo = a.cve_articulo AND v.cve_almac = ra.Cve_Almac AND v.tipo = 'ubicacion'
        LEFT JOIN c_proveedores p ON p.ID_Proveedor = v.Id_Proveedor
        WHERE alm.clave IS NOT NULL {$sqlAlmacen} {$ands} 
        GROUP BY clave_alm, cve_articulo
        ) AS concentrado {$filtro_where_concentrado} {$filtro_clientes} 
        ";

    }
    else
    {
        $sql = "
        SELECT  
            #ad.num_orden AS folio,
            alm.nombre AS Nombre_Almacen,
            g.des_gpoart AS grupo,
            p.ID_Proveedor AS id_proveedor,
            p.Nombre AS proveedor,
            a.cve_articulo AS articulo,
            IFNULL(a.des_articulo, '--') AS nombre,
            IFNULL(a.cajas_palet, 0) AS cajasxpallets,
            IFNULL(a.num_multiplo, 0) AS piezasxcajas, 
            0 AS Pallet,
            0 AS Caja, 
            0 AS Piezas,
            #IFNULL((SELECT SUM(td.cantidad) FROM td_aduana td, th_aduana th WHERE th.num_pedimento = td.num_orden AND  td.cve_articulo = a.cve_articulo AND th.status = 'C'), 0) AS Prod_OC,
            #IFNULL((SELECT SUM(ve.Existencia) FROM V_ExistenciaGralProduccion ve WHERE ve.cve_articulo = a.cve_articulo AND ve.cve_lote = ad.cve_lote AND ve.tipo = 'area'), 0) AS Prod_RTM,
            #IFNULL((SELECT SUM(Cantidad) FROM t_recorrido_surtido WHERE Cve_articulo = a.cve_articulo), 0) AS Res_Pick,
            #IFNULL((SELECT SUM(Cantidad) FROM t_movcuarentena WHERE cve_articulo = a.cve_articulo), 0) AS Prod_QA,
            #IFNULL((SELECT SUM(vp.Existencia) FROM V_ExistenciaGralProduccion vp, c_lotes lo, c_ubicacion cu WHERE cu.idy_ubica = vp.cve_ubicacion AND cu.AreaProduccion != 'S' AND vp.cve_articulo = a.cve_articulo AND a.cve_articulo = lo.cve_articulo AND vp.cve_lote = lo.LOTE AND lo.Caducidad != '0000-00-00' AND lo.Caducidad != '' AND lo.Caducidad < CURDATE() AND vp.tipo = 'ubicacion'), 0) AS Obsoletos,
            #IFNULL((SELECT SUM(tds.Cantidad) FROM td_surtidopiezas tds, rel_uembarquepedido rep, th_pedido tp WHERE tds.fol_folio = rep.fol_folio AND rep.fol_folio = tp.Fol_folio AND tp.status = 'C' AND tds.Cve_articulo = a.cve_articulo GROUP BY tds.Cve_articulo), 0) AS RTS,
            #IFNULL((SELECT SUM(vp.Existencia) FROM V_ExistenciaGralProduccion vp, c_ubicacion cu WHERE cu.idy_ubica = vp.cve_ubicacion AND cu.AreaProduccion = 'S' AND vp.cve_articulo = a.cve_articulo AND vp.tipo = 'ubicacion'), 0) AS Prod_kit, 
            (SELECT IFNULL(SUM(e.Existencia), 0) FROM V_ExistenciaGralProduccion e 
            WHERE e.tipo = 'ubicacion' AND e.Existencia > 0  AND e.cve_articulo = a.cve_articulo
            ) AS existencia
        FROM c_articulo a
        LEFT JOIN c_gpoarticulo g ON g.cve_gpoart = a.grupo
        LEFT JOIN th_aduana v  ON v.Cve_Almac != '100ABCDEFG_JK'
        LEFT JOIN c_almacenp alm ON alm.clave = v.Cve_Almac
            LEFT JOIN td_aduana ad ON v.num_pedimento = ad.num_orden
            LEFT JOIN c_proveedores p ON p.ID_Proveedor = v.ID_Proveedor
        WHERE a.cve_articulo != '' AND a.cve_articulo IN (SELECT cve_articulo FROM V_ExistenciaGralProduccion)
        $ands
         
        GROUP BY a.cve_articulo

        UNION

        SELECT  
            #'' AS folio,
            c_almacenp.nombre AS Nombre_Almacen,
            g.des_gpoart AS grupo,
            '' AS id_proveedor,
            '' AS proveedor,
            ar.cve_articulo AS articulo,
            IFNULL(ar.des_articulo, '--') AS nombre,
            IFNULL(ar.cajas_palet, 0) AS cajasxpallets,
            IFNULL(ar.num_multiplo, 0) AS piezasxcajas, 
            0 AS Pallet,
            0 AS Caja, 
            0 AS Piezas,
            #0 AS Prod_OC,
            #0 AS Prod_RTM,
            #0 AS Res_Pick,
            #0 AS Prod_QA,
            #IFNULL((SELECT SUM(vp.Existencia) FROM V_ExistenciaGralProduccion vp, c_lotes lo, c_ubicacion cu WHERE cu.idy_ubica = vp.cve_ubicacion AND cu.AreaProduccion != 'S' AND vp.cve_articulo = ar.cve_articulo AND ar.cve_articulo = lo.cve_articulo AND vp.cve_lote = lo.LOTE AND lo.Caducidad != '0000-00-00' AND lo.Caducidad != '' AND lo.Caducidad < CURDATE() AND vp.tipo = 'ubicacion'), 0) AS Obsoletos,
            #IFNULL((SELECT SUM(tds.Cantidad) FROM td_surtidopiezas tds, rel_uembarquepedido rep, th_pedido tp WHERE tds.fol_folio = rep.fol_folio AND rep.fol_folio = tp.Fol_folio AND tp.status = 'C' AND tds.Cve_articulo = ar.cve_articulo GROUP BY tds.Cve_articulo), 0) AS RTS,
            art.Cantidad_Producida AS Prod_kit,
            0 AS existencia
        FROM c_articulo ar
            LEFT JOIN c_gpoarticulo g ON g.cve_gpoart = ar.grupo
            LEFT JOIN t_artcompuesto art ON art.Cve_Articulo = ar.cve_articulo
            LEFT JOIN c_almacenp ON c_almacenp.id = ar.cve_almac
        WHERE ar.cve_articulo NOT IN (SELECT  
            z.cve_articulo
        FROM td_aduana z
            LEFT JOIN th_aduana adz ON z.num_orden = adz.num_pedimento
        ) AND ar.cve_articulo NOT IN (SELECT 
        cve_articulo FROM V_ExistenciaGralProduccion)
        $ands2
         
        GROUP BY ar.cve_articulo
        ORDER BY Nombre_Almacen        
        
    ";
    }


    $res = "";
    if (!($res = mysqli_query($conn, $sql))) 
    {
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    }

//***************************************************************************************************

        $columnas = [
          //'Proveedor',
          'Clave',
          'Descripcion',
          'Pallet',
          'Caja',
          'Piezas',
          'Existencia'
        ];

        if($check_sin_almacen == 1)
        $columnas = [
          //'Proveedor',
          'Clave',
          'Descripcion',
          'Pallet',
          'Caja',
          'Piezas',
          'Existencia',
          'Almacén'
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

            //echo $row->proveedor . "\t";
            echo $row->cve_articulo . "\t";
            echo utf8_encode($row->articulo) . "\t";
            echo $Pallet . "\t";
            echo $Cajas . "\t";
            echo $Piezas . "\t";
            echo $row->existencia . "\t";
            if($check_sin_almacen == 1)
                echo $row->Nombre_Almacen . "\t";
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

    public function Xexportar_existenciaubicaX()
    {

//***************************************************************************************************
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $utf8Sql = "SET NAMES 'utf8mb4';";
    $res_charset = mysqli_query($conn, $utf8Sql);

    //mysqli_set_charset($conn,"utf8mb3");

    $articulo = $_GET["articulo"];
    $contenedor = $_GET["contenedor"];
    $almacen = $_GET["almacen"];
    $zona = $_GET["zona"];
    $cve_cliente = $_GET["cve_cliente"];
    $cve_proveedor = $_GET["cve_proveedor"];
    $proveedor = $_GET["proveedor"];
    $bl = $_GET["bl"];
    $lp = $_GET["lp"];
    $grupo = $_GET["grupo"];
    $clasificacion = $_GET["clasificacion"];
    $art_obsoletos = $_GET["art_obsoletos"];
    $refWell = $_GET['refWell'];
    $pedimentoW = $_GET['pedimentoW'];
    $picking = $_GET['picking'];

    $zona_produccion = "";
    $num_produccion = 0;
    if(!empty($zona))
    {
        $sql_verificar_zona_produccion = "SELECT DISTINCT AreaProduccion FROM c_ubicacion WHERE cve_almac = '{$zona}'";
        //AND AreaProduccion = 'S'
        $query_zona_produccion = mysqli_query($conn, $sql_verificar_zona_produccion);
        $num_produccion = mysqli_num_rows($query_zona_produccion);
        //if($query_zona_produccion){
        $zona_produccion = mysqli_fetch_array($query_zona_produccion)['AreaProduccion'];
        //}
    }

    $sql = "SELECT COUNT(*) AS existe FROM t_configuraciongeneral WHERE cve_conf = 'mostrar_folios_excel_existencias' AND Valor = '1'";
    if (!($res = mysqli_query($conn, $sql)))
        echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
    $mostrar_folios_excel_existencias = mysqli_fetch_array($res)['existe'];

    $_page = 0;

      if (intval($page)>0) $_page = ($page-1)*$limit;//

    $sql_obsoletos = "";
    if($art_obsoletos == 1)
       $sql_obsoletos = " AND a.control_lotes = 'S' AND a.Caduca = 'S' AND l.Caducidad < CURDATE() AND COALESCE(IF(IFNULL(a.Caduca, 'N') = 'S' AND IFNULL(e.cve_lote,'') != '',IF(DATE_FORMAT(l.Caducidad,'%Y-%m-%d') = '0000-00-00','',DATE_FORMAT(l.Caducidad,'%d-%m-%Y')),'')) != '' ";

    if($art_obsoletos == 0)
       $sql_obsoletos = " AND a.control_lotes = 'S' AND a.Caduca = 'S' AND l.Caducidad >= CURDATE() AND COALESCE(IF(IFNULL(a.Caduca, 'N') = 'S' AND IFNULL(e.cve_lote,'') != '',IF(DATE_FORMAT(l.Caducidad,'%Y-%m-%d') = '0000-00-00','',DATE_FORMAT(l.Caducidad,'%d-%m-%Y')),'')) != '' ";


    $sql_instancia = "SELECT DISTINCT IFNULL(Valor, '') as instancia FROM t_configuraciongeneral WHERE cve_conf = 'instancia'";
    if (!($res_instancia = mysqli_query($conn, $sql_instancia)))
        echo "Falló la preparación instancia: (" . mysqli_error($conn) . ") ";
    $instancia = mysqli_fetch_array($res_instancia)['instancia'];

    $sqlCollation = "";
    if($instancia == 'foam')
    {
        $sqlCollation = " COLLATE utf8mb4_unicode_ci ";
    }

    $SQLrefWell = "";
    if($refWell && $instancia == 'welldex')
        $SQLrefWell = " AND ta.recurso LIKE '%$refWell%' ";

    $SQLpedimentoW = "";
    if($pedimentoW && $instancia == 'welldex')
        $SQLpedimentoW = " AND ta.Pedimento LIKE '%$pedimentoW%' ";


    $sqlPicking = ($picking != "") ? "AND IFNULL(u.picking, 'N') = '{$picking}'" : "";

    $sqlAlmacen = "";
    if($almacen)
       $sqlAlmacen = ' WHERE id = "'.$almacen.'" ';
/*
    $sql1 = 'SELECT * FROM c_almacenp $sqlAlmacen';
    $result = getArraySQL($sql1);
    $responce = array();
    $responce["bl"] = $result[0]["BL"];
*/
    $sqlZona = (!empty($zona) && $zona != "RTS" && $zona != "RTM") ? "AND e.cve_ubicacion IN (SELECT DISTINCT idy_ubica FROM c_ubicacion WHERE cve_almac = '{$zona}')" : "";

    if($zona_produccion == 'S')
    $sqlZona = (!empty($zona) && $zona != "RTS" && $zona != "RTM") ? "AND e.cve_ubicacion IN (SELECT DISTINCT idy_ubica FROM c_ubicacion WHERE cve_almac = '{$zona}')" : "";

    $zona_rts = "";
    $zona_rtm_tipo = "ubicacion";
    $zona_rtm_tipo2 = "";
    if($zona == "RTS")
    {
        $sqlAlmacen = "";
        if($almacen)
           $sqlAlmacen = " AND tds.cve_almac = (SELECT id FROM c_almacenp WHERE id = '".$almacen."') ";

        $zona_rts = " AND IFNULL((SELECT COUNT(*) FROM td_surtidopiezas tds, rel_uembarquepedido rep, th_pedido tp WHERE tds.fol_folio = rep.fol_folio AND rep.fol_folio = tp.Fol_folio AND tp.status = 'C' {$sqlAlmacen} AND tds.Cve_articulo = a.cve_articulo), 0) != 0";
    }
    else if($zona == "RTM")
    {
        $zona_rtm_tipo = "area";
        $zona_rtm_tipo2 = " AND x.tipo_ubicacion = '' ";
    }

    $sqlArticulo = !empty($articulo) ? "AND e.cve_articulo = '{$articulo}'" : "";
  
    $sqlContenedor = !empty($contenedor) ? "AND e.Cve_Contenedor = '{$contenedor}'" : "";

    $sqlCliente = !empty($cve_cliente) ? "INNER JOIN c_cliente c ON c.ID_Proveedor = p.ID_Proveedor AND e.ID_Proveedor = c.ID_Proveedor AND c.Cve_Clte = '{$cve_cliente}'" : "";

    $sqlProveedor = !empty($proveedor) ? "AND e.ID_Proveedor = '{$proveedor}'" : "";
    $sqlProveedor2 = !empty($proveedor) ? "AND x.id_proveedor = '{$proveedor}'" : "";
  
    $sqlLotes = !empty($lotes) ? "AND x.lote like '%{$lotes}%'" : "";

    $sqlbl = !empty($bl) ? "AND x.codigo like '%{$bl}%'" : "";
    $sqlLP = !empty($lp) ? "AND x.LP like '%{$lp}%'" : "";

    $sqlGrupo = !empty($grupo) ? "AND gr.id = '{$grupo}'" : "";
    $sqlClasif = !empty($clasificacion) ? "AND cl.cve_sgpoart = '{$clasificacion}'" : "";

    $sqlbl_search = !empty($bl) ? "AND u.CodigoCSD like '%{$bl}%'" : "";
    $sqlLP_search = !empty($lp) ? "AND ch.CveLP like '%{$lp}%'" : "";
    

    $sqlproveedor_tipo = !empty($cve_proveedor) ? "AND x.id_proveedor = {$cve_proveedor}" : "";
    $sqlproveedor_tipo2 = !empty($cve_proveedor) ? "AND e.ID_Proveedor = {$cve_proveedor}" : "";

    $sqlAlmacen = "";
    if($almacen)
       $sqlAlmacen = " e.cve_almac = '{$almacen}' AND ";

   $tabla_from = "V_ExistenciaGral";
   $tipo_prod  = " e.tipo = '{$zona_rtm_tipo}' AND ";
   $field_folio_ot = "''";
   $field_NCaja = "''";
   $SQL_FolioOT = "";
   if($zona_produccion == 'S' && $num_produccion < 2)
   {
        $tabla_from = "V_ExistenciaProduccion";
        $tipo_prod = "";

       $field_folio_ot = "IFNULL(op.Folio_Pro, '')";
       $field_NCaja = "IFNULL(cm.NCaja, '')";
       $SQL_FolioOT = "
            LEFT JOIN t_tarima tt ON tt.ntarima = ch.IDContenedor 
            LEFT JOIN t_ordenprod op ON op.Cve_Articulo = IFNULL(e.cve_articulo, tt.cve_articulo ) AND IFNULL(op.Cve_Lote,'') = IFNULL(tt.lote, e.cve_lote) AND op.Folio_Pro = IFNULL(tt.Fol_Folio, op.Folio_Pro) 
            LEFT JOIN th_cajamixta cm ON cm.fol_folio = tt.Fol_Folio AND cm.Cve_CajaMix = tt.Caja_ref 
        ";

   }
   else if($num_produccion == 2)
    {
       $tabla_from = "V_ExistenciaGralProduccion";
       $tipo_prod  = " e.tipo = '{$zona_rtm_tipo}' AND ";
    }

    $sqlAlmacen = "";
    if($almacen)
       $sqlAlmacen = " (e.cve_almac = '{$almacen}')  AND ";//OR zona.cve_almacp = '{$almacen}'


    $sql_folios = "";$sql = ""; $sql_foliox = "";
    if($mostrar_folios_excel_existencias) 
    {
        $sql_folios = " IFNULL((SELECT GROUP_CONCAT(DISTINCT Factura SEPARATOR ', ') FROM th_aduana WHERE num_pedimento IN (SELECT id_ocompra FROM th_entalmacen WHERE Fol_Folio IN (SELECT fol_folio FROM td_entalmacen WHERE IFNULL(cve_articulo, '') = IFNULL(e.cve_articulo, '') AND IFNULL(cve_lote, '') = IFNULL(e.cve_lote, '')))), '')  AS folio, ";
        $sql_foliox = ", x.folio ";
    }
    $sql = "
      SELECT x.cve_almacen, x.id_almacen, x.almacen, x.folio_OT, x.NCaja, x.zona, x.codigo, x.cve_ubicacion, x.zona_recepcion, x.QA, x.contenedor, x.LP, x.clave, x.descripcion, x.des_grupo, x.des_clasif, x.cajasxpallets, x.piezasxcajas, x.Pallet, x.Caja, x.Piezas, x.control_lotes, x.control_numero_series, x.lote, x.caducidad, x.nserie, x.peso, (x.cantidad) AS cantidad, (x.cantidad_kg) AS cantidad_kg, x.id_proveedor, (x.proveedor) AS proveedor, (x.empresa_proveedor) AS empresa_proveedor, x.RP,x.tipo_ubicacion, x.control_abc, x.clasif_abc, x.um, x.control_peso, x.referencia_well, x.pedimento_well, x.fecha_ingreso {$sql_foliox} FROM(
         SELECT DISTINCT 
            #IF(IFNULL(e.Cuarentena, '') = 0, '<input class=\"column-asignar\" type=\"checkbox\">', '') as acciones, 
           #IFNULL(CONCAT(IFNULL(oc.num_pedimento, ''), IF(IFNULL(oc.Factura, '') != '', ' | ', ''), IFNULL(oc.Factura, '')), '') AS oc,
            ap.clave AS cve_almacen,
            e.cve_almac AS id_almacen,
            ap.nombre as almacen,
            {$field_folio_ot} AS folio_OT,
            {$field_NCaja} AS NCaja,
            z.des_almac as zona,
            u.CodigoCSD as codigo,
            e.cve_ubicacion,
            zona.desc_ubicacion AS zona_recepcion,
            IF(IFNULL(e.Cuarentena, 0) = 1, 'Si','No') as QA,
            e.Cve_Contenedor as contenedor,
            IF(e.Cve_Contenedor != '', ch.CveLP, '') AS LP,
            a.cve_articulo as clave,
            a.des_articulo as descripcion,
            gr.des_gpoart as des_grupo,
            cl.cve_sgpoart as des_clasif,
            IFNULL(a.cajas_palet, 0) as cajasxpallets,
            IFNULL(a.num_multiplo, 0) as piezasxcajas, 
            0 as Pallet,
            0 as Caja, 
            0 as Piezas,
            ta.recurso as referencia_well,
            ta.Pedimento as pedimento_well,
            a.control_lotes as control_lotes,
            a.control_numero_series as control_numero_series,
            IFNULL(e.cve_lote,'') AS lote,
            COALESCE(IF(IFNULL(a.Caduca, 'N') = 'S' AND IFNULL(e.cve_lote,'') != '',IF(DATE_FORMAT(l.Caducidad,'%Y-%m-%d') = '0000-00-00','',DATE_FORMAT(l.Caducidad,'%d-%m-%Y')),'')) AS caducidad,
            COALESCE(IF(a.control_numero_series = 'S',e.cve_lote,'' ), ' ') AS nserie,
            a.peso,
            e.Existencia as cantidad,
            e.Existencia*a.peso*(IF(IFNULL(a.num_multiplo, 0) = 0, 1, a.num_multiplo)) AS cantidad_kg,
            IFNULL(trs.Cantidad, 0) AS RP,
            p.ID_proveedor AS id_proveedor,
            p.Nombre AS proveedor,
            IF(p.es_cliente = 1, p.Nombre, '') AS empresa_proveedor,
            #IFNULL(ent.num_orden, '') AS folio_oc,
            DATE_FORMAT(ent.fecha_fin, '%d-%m-%Y') AS fecha_ingreso,
                CASE 
                    WHEN u.Picking = 'S' THEN 'Si'
                    WHEN u.Picking = 'N' THEN 'No'
                END
            AS tipo_ubicacion,
            a.control_abc,
            z.clasif_abc,

            {$sql_folios}
            IFNULL(um.cve_umed, '') as um, 
            a.control_peso
            FROM
                {$tabla_from} e
            INNER JOIN c_articulo a ON a.cve_articulo = e.cve_articulo
            LEFT JOIN c_unimed um ON a.unidadMedida = um.id_umed
            LEFT JOIN c_gpoarticulo gr ON gr.cve_gpoart = a.grupo
            LEFT JOIN c_sgpoarticulo cl ON cl.cve_sgpoart = a.clasificacion
            LEFT JOIN c_ubicacion u ON u.idy_ubica = e.cve_ubicacion
            LEFT JOIN c_lotes l ON l.LOTE = e.cve_lote AND l.cve_articulo = e.cve_articulo
            LEFT JOIN c_almacen z ON z.cve_almac = u.cve_almac
            #LEFT JOIN c_almacen z ON z.cve_almac = (SELECT cve_almac FROM c_ubicacion WHERE CONCAT(idy_ubica, '') = CONCAT(e.cve_ubicacion, ''))
            LEFT JOIN c_charolas ch ON ch.clave_contenedor = e.Cve_Contenedor 
            LEFT JOIN tubicacionesretencion zona ON zona.cve_ubicacion = e.cve_ubicacion  {$sqlCollation} 
            LEFT JOIN c_almacenp ap ON ap.id = z.cve_almacenp OR ap.id = zona.cve_almacp 
            LEFT JOIN t_recorrido_surtido trs ON trs.Cve_articulo = e.cve_articulo AND trs.cve_lote = e.cve_lote AND trs.cve_almac = z.cve_almac AND e.cve_ubicacion = trs.idy_ubica 

             {$SQL_FolioOT} 

            LEFT JOIN rel_articulo_proveedor rap ON rap.Cve_Articulo = e.cve_articulo AND e.ID_Proveedor = rap.Id_Proveedor AND rap.Id_Proveedor != 0
            LEFT JOIN c_proveedores p ON p.ID_Proveedor = e.ID_Proveedor AND p.ID_Proveedor = IFNULL(rap.Id_Proveedor, e.ID_Proveedor)
             {$sqlCliente} 
            LEFT JOIN td_entalmacen ent ON ent.cve_articulo = e.cve_articulo AND IFNULL(ent.cve_lote, '') = e.cve_lote
            LEFT JOIN th_entalmacen th ON th.Cve_Proveedor = e.ID_Proveedor AND th.Fol_folio = ent.fol_folio #AND th.tipo = 'OC'
            LEFT JOIN th_aduana ta ON ta.num_pedimento = th.id_ocompra

                WHERE {$sqlAlmacen} {$tipo_prod} e.Existencia > 0  {$sqlArticulo} {$sqlContenedor} {$sqlZona}
                {$sqlProveedor} {$sqlGrupo}
                {$zona_rts} {$sql_obsoletos} {$SQLrefWell} {$SQLpedimentoW} {$sqlPicking}
            #GROUP BY id_proveedor, cve_almacen, cve_ubicacion, contenedor, clave, lote, nserie
            ORDER BY a.des_articulo, folio_OT, (NCaja+0) ASC
                )x
            #where x.lote != '--'
            WHERE 1 AND x.id_almacen = '{$almacen}'
            {$sqlbl} 
            {$sqlLP} 
            {$sqlLotes} 
            {$sqlproveedor_tipo}  
            {$sqlProveedor2} 
            #{$SQLOC} 
            #GROUP BY cve_almacen, cve_ubicacion, contenedor, clave, id_proveedor, lote, nserie
            GROUP BY cve_almacen, cve_ubicacion, contenedor, clave, lote, nserie, id_proveedor
            ";

    
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
            'Clasificacion',
            'Descripcion',
            'Lote | Serie',
            'Caducidad',
            'Unidad Medida',
            'Total',
            'RP',
            'Prod QA',
            'Obsoletos',
/*
            'Pallet',
            'Caja',
            'Piezas',
*/
            'Disponible',
            'Fecha Ingreso',
            //'Folio OC',
            'Grupo',
            'Proveedor'

        ];

        if($mostrar_folios_excel_existencias)
            $columnas = [
                'Codigo BL',
                'Pallet|Cont',
                'License Plate (LP)',
                'Clave',
                'Clasificacion',
                'Descripcion',
                'Lote | Serie',
                'Caducidad',
                'Unidad Medida',
                'Total',
                'RP',
                'Prod QA',
                'Obsoletos',
    /*
                'Pallet',
                'Caja',
                'Piezas',
    */
                'Disponible',
                'Fecha Ingreso',
                'Folio OC',
                'Grupo',
                'Proveedor'
            ];

        if($instancia == 'welldex')
        $columnas = [
            'Codigo BL',
            'Pallet|Cont',
            'License Plate (LP)',
            'Clave',
            'Clasificacion',
            'Descripcion',
            'Lote | Serie',
            'Caducidad',
            'Unidad Medida',
            'Total',
            'RP',
            'Prod QA',
            'Obsoletos',
/*
            'Pallet',
            'Caja',
            'Piezas',
*/
            'Disponible',
            'Fecha Ingreso',
            //'Folio OC',
            'Grupo',
            'Proveedor',
            'Referencia Well',
            'Pedimento Well'

        ];
/*
$header = array(
  'c1-text'=>'string',//text
  'c2-text'=>'@',//text
  'c3-integer'=>'integer',
  'c4-integer'=>'0',
  'c5-price'=>'price',
  'c6-price'=>'#,##0.00',//custom
  'c7-date'=>'date',
  'c8-date'=>'YYYY-MM-DD',
);
*/
/*
$header = array(
  'c1-text'=>'string',//text
  'c2-text'=>'string',//text
  'c3-text'=>'string',
  'c4-text'=>'string',
  'c5-text'=>'string',
  'c6-text'=>'string',//custom
  'c7-text'=>'string',
  'c8-date'=>'DD-MM-YYYY',
  'c9-text'=>'string',
  'c10-text'=>'string',
  'c11-text'=>'string',
  'c12-text'=>'string',
  'c13-text'=>'string',

);
*/
//$writer = new XLSXWriter();
//$writer->writeSheetHeader('Sheet1', $header);
/*
        $filename = "Reporte de Existencias" . ".xls";
        ob_clean();

        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        //header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        //header('Content-Length: ' . filesize($filename));
        //header('Content-Transfer-Encoding: binary');
        //header('Cache-Control: must-revalidate');
        //header('Pragma: public');

        foreach($columnas as $column) 
        {            
            echo $column . "\t" ;            
        }
        print("\r\n");
*/
//echo $sql . "\t";
//header("Pragma: public");
//header("Expires: 0");
$filename = "Existencias Ubicacion.xls";
header("Content-type: application/x-msdownload");
header("Content-Disposition: attachment; filename=$filename");
//header("Pragma: no-cache");
//header("Cache-Control: must-revalidate, post-check=0, pre-check=0");

?>
<table>
<tbody>
<tr>
<th>
<h2>Listado en tabla excel</h2>
</th>
</tr>
<?php
        while($row = mysqli_fetch_array($res))
        {
            //$writer->writeSheetRow('Sheet1', $row);
?>
<tr>
<td>1</td>
<td>2</td>
<td>3</td>
<td>4</td>
<td>5</td>
<td>6</td>
<td>7</td>
<td>8</td>
<td>9</td>
<td>10</td>
</tr>
<?php
        }
?>
</tbody>
</table>

<?php 
        //echo $sql. "\t";

            //$writer->writeToFile('Existencias.xlsx');

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
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $utf8Sql = "SET NAMES 'utf8mb4';";
    $res_charset = mysqli_query($conn, $utf8Sql);


    $articulo = $_GET["articulo"];
    $contenedor = $_GET["contenedor"];
    $almacen = $_GET["almacen"];
    $zona = $_GET["zona"];
    $cve_cliente = $_GET["cve_cliente"];
    $cve_proveedor = $_GET["cve_proveedor"];
    $proveedor = $_GET["proveedor"];
    $bl = $_GET["bl"];
    $lp = $_GET["lp"];
    $grupo = $_GET["grupo"];
    $clasificacion = $_GET["clasificacion"];
    $art_obsoletos = $_GET["art_obsoletos"];
    $refWell = $_GET['refWell'];
    $pedimentoW = $_GET['pedimentoW'];
    $picking = $_GET['picking'];
    $mostrar_folios_excel_existencias = $_GET['mostrar_folios_excel_existencias'];
    $existencia_cajas = $_GET['existencia_cajas'];
    $lote = $_GET['lote'];
    $factura = $_GET['factura'];
    $proyecto_existencias = $_GET['proyecto_existencias'];


    $sql_obsoletos = "";
    if($art_obsoletos == 1)
       $sql_obsoletos = " AND a.control_lotes = 'S' AND a.Caduca = 'S' AND l.Caducidad < CURDATE() AND COALESCE(IF(IFNULL(a.Caduca, 'N') = 'S' AND IFNULL(e.cve_lote,'') != '',IF(DATE_FORMAT(l.Caducidad,'%Y-%m-%d') = '0000-00-00','',DATE_FORMAT(l.Caducidad,'%d-%m-%Y')),'')) != '' ";

    if($art_obsoletos == 0)
       $sql_obsoletos = " AND a.control_lotes = 'S' AND a.Caduca = 'S' AND l.Caducidad >= CURDATE() AND COALESCE(IF(IFNULL(a.Caduca, 'N') = 'S' AND IFNULL(e.cve_lote,'') != '',IF(DATE_FORMAT(l.Caducidad,'%Y-%m-%d') = '0000-00-00','',DATE_FORMAT(l.Caducidad,'%d-%m-%Y')),'')) != '' ";


    $zona_produccion = "";
    $num_produccion = 0;
    if(!empty($zona))
    {
        $sql_verificar_zona_produccion = "SELECT DISTINCT AreaProduccion FROM c_ubicacion WHERE cve_almac = '{$zona}'";
        //AND AreaProduccion = 'S'
        $query_zona_produccion = mysqli_query($conn, $sql_verificar_zona_produccion);
        $num_produccion = mysqli_num_rows($query_zona_produccion);
        //if($query_zona_produccion){
        $zona_produccion = mysqli_fetch_array($query_zona_produccion)['AreaProduccion'];
        //}
    }


    $sql_existencia_cajas = "LEFT";
    if($existencia_cajas == 1)
        $sql_existencia_cajas = "INNER";

    $sqlPicking = ($picking != "") ? "AND IFNULL(u.picking, 'N') = '{$picking}'" : "";

    $sqlAlmacen = "";
    if($almacen)
       $sqlAlmacen = ' WHERE id = "'.$almacen.'" ';
/*
    $sql1 = 'SELECT * FROM c_almacenp $sqlAlmacen';
    $result = getArraySQL($sql1);
    $responce = array();
    $responce["bl"] = $result[0]["BL"];
*/
    $sql_folios = ""; $sql_foliox = ""; $group_mostrar_folios = "";$left_join_folios = ""; 
    $field_folios = " a.cve_articulo as clave, ";
    if($mostrar_folios_excel_existencias) 
    {
        //$sql_folios = " IFNULL((SELECT GROUP_CONCAT(DISTINCT Factura SEPARATOR ', ') FROM th_aduana WHERE num_pedimento IN (SELECT id_ocompra FROM th_entalmacen WHERE Fol_Folio IN (SELECT fol_folio FROM td_entalmacen WHERE IFNULL(cve_articulo, '') = IFNULL(e.cve_articulo, '') AND IFNULL(cve_lote, '') = IFNULL(e.cve_lote, '')))), '')  AS folio, ";

        $field_folios = " IF((tdt.fol_folio IS NOT NULL AND IFNULL(e.Cve_Contenedor, '') != '') OR (tdt.fol_folio IS NULL AND IFNULL(e.Cve_Contenedor, '') = ''), a.cve_articulo, e.cve_articulo) AS clave, ";
        $left_join_folios = " LEFT JOIN td_entalmacenxtarima tdt ON IFNULL(tdt.fol_folio, '') = IFNULL(th.Fol_folio, '') AND IFNULL(tdt.ClaveEtiqueta, '') = IFNULL(e.Cve_Contenedor, '') AND IFNULL(tdt.Cve_Articulo, '') = IFNULL(td.cve_articulo, '') AND IFNULL(ta.Cve_Almac, '') = IFNULL(ap.clave, '') AND IFNULL(tdt.Cve_Lote, '') = IFNULL(e.cve_lote, '') AND IFNULL(th.Fol_OEP, '') =  IFNULL(ta.Factura, '') "; 
        //$sql_folios = " IFNULL(ta.Factura, '')  AS folio, ";
        $sql_folios = " IFNULL(tr.factura_ent, '')  AS folio, ";
        //$sql_foliox = ", x.folio";
        $sql_foliox = " , IF(IFNULL(x.folio, '') = '', '', GROUP_CONCAT(DISTINCT NULLIF(x.folio, '') SEPARATOR ', ')) AS folio ";
        $group_mostrar_folios = ", folio";
    }
 
    $sql_proyecto = " IFNULL(tr.proyecto, '') as proyecto, ";
    $sql_proyectox = ", IF(IFNULL(x.proyecto, '') = '', '', GROUP_CONCAT(DISTINCT NULLIF(x.proyecto, '') SEPARATOR ', ')) AS proyecto";
    $group_mostrar_proyecto = ", proyecto";

    $sqlZona = (!empty($zona) && $zona != "RTS" && $zona != "RTM") ? "AND e.cve_ubicacion IN (SELECT DISTINCT idy_ubica FROM c_ubicacion WHERE cve_almac = '{$zona}')" : "";

    if($zona_produccion == 'S')
    $sqlZona = (!empty($zona) && $zona != "RTS" && $zona != "RTM") ? "AND e.cve_ubicacion IN (SELECT DISTINCT idy_ubica FROM c_ubicacion WHERE cve_almac = '{$zona}')" : "";

    $sqlFactura = !empty($factura) ? " AND IFNULL(tr.factura_ent, '' ) LIKE '%$factura%'" : "";
    $sqlProyecto = !empty($proyecto_existencias) ? " AND IFNULL(tr.proyecto, '' ) LIKE '%$proyecto_existencias%'" : "";

    $zona_rts = "";
    $zona_rtm_tipo = "ubicacion";
    $zona_rtm_tipo2 = "";
    if($zona == "RTS")
    {
        $sqlAlmacen = "";
        if($almacen)
           $sqlAlmacen = " AND tds.cve_almac = (SELECT id FROM c_almacenp WHERE id = '".$almacen."') ";

        $zona_rts = " AND IFNULL((SELECT COUNT(*) FROM td_surtidopiezas tds, rel_uembarquepedido rep, th_pedido tp WHERE tds.fol_folio = rep.fol_folio AND rep.fol_folio = tp.Fol_folio AND tp.status = 'C' {$sqlAlmacen} AND tds.Cve_articulo = a.cve_articulo), 0) != 0";
    }
    else if($zona == "RTM")
    {
        $zona_rtm_tipo = "area";
        $zona_rtm_tipo2 = " AND x.tipo_ubicacion = '' ";
    }


    $sqlArticulo = !empty($articulo) ? "AND e.cve_articulo = '{$articulo}'" : "";
  
    $sqlContenedor = !empty($contenedor) ? "AND e.Cve_Contenedor = '{$contenedor}'" : "";

    $sqlProveedor = !empty($proveedor) ? "AND e.ID_Proveedor = '{$proveedor}'" : "";
    $sqlCliente = !empty($cve_cliente) ? "INNER JOIN c_cliente c ON c.ID_Proveedor = p.ID_Proveedor AND e.ID_Proveedor = c.ID_Proveedor AND c.Cve_Clte = '{$cve_cliente}'" : "";
    $sqlProveedor2 = !empty($proveedor) ? "AND x.id_proveedor = '{$proveedor}'" : "";
  
    $sqlLotes = !empty($lotes) ? "AND x.lote like '%{$lotes}%'" : "";

    $sqlbl = !empty($bl) ? "AND x.codigo like '%{$bl}%'" : "";
    $sqlLP = !empty($lp) ? "AND x.LP like '%{$lp}%'" : "";

    $sqlGrupo = !empty($grupo) ? "AND gr.cve_gpoart = '{$grupo}'" : "";
    $sqlClasif = !empty($clasificacion) ? "AND cl.cve_sgpoart = '{$clasificacion}'" : "";


    $sqlbl_search = !empty($bl) ? "AND u.CodigoCSD like '%{$bl}%'" : "";
    $sqlLP_search = !empty($lp) ? "AND ch.CveLP like '%{$lp}%'" : "";
    

    $sqlproveedor_tipo = !empty($cve_proveedor) ? "AND x.id_proveedor = {$cve_proveedor}" : "";
    $sqlproveedor_tipo2 = !empty($cve_proveedor) ? "AND e.ID_Proveedor = {$cve_proveedor}" : "";

    $sqlAlmacen = "";
    if($almacen)
       $sqlAlmacen = " e.cve_almac = '{$almacen}' AND ";

    $sql_instancia = "SELECT DISTINCT IFNULL(Valor, '') as instancia FROM t_configuraciongeneral WHERE cve_conf = 'instancia'";
    if (!($res_instancia = mysqli_query($conn, $sql_instancia)))
        echo "Falló la preparación instancia: (" . mysqli_error($conn) . ") ";
    $instancia = mysqli_fetch_array($res_instancia)['instancia'];


    $SQLrefWell = "";
    if($refWell && $instancia == 'welldex')
        $SQLrefWell = " AND ta.recurso LIKE '%$refWell%' ";

    $SQLpedimentoW = "";
    if($pedimentoW && $instancia == 'welldex')
        $SQLpedimentoW = " AND ta.Pedimento LIKE '%$pedimentoW%' ";


    $sqlCollation = "";$sqlEliminaraduanaTemporalmente = "";
    if($instancia == 'foam')
    {
        $sqlCollation = " COLLATE utf8mb4_unicode_ci ";
        $sqlEliminaraduanaTemporalmente = " AND 0 ";
    }

    $field_bl = " u.CodigoCSD AS codigo, ";
    if($instancia == 'asl' || $instancia == 'dicoisa')// || $instancia == 'oslo'
        $field_bl = " REPLACE(u.CodigoCSD, '-', '_') AS codigo, ";


   $tabla_from = "V_ExistenciaGral";
   $tipo_prod  = " e.tipo = '{$zona_rtm_tipo}' AND ";
   $field_folio_ot = "''";
   $field_NCaja = "''";
   $SQL_FolioOT = "";
   if($zona_produccion == 'S' && $num_produccion < 2)
   {
        $tabla_from = "V_ExistenciaProduccion";
        $tipo_prod = "";

       $field_folio_ot = "IFNULL(op.Folio_Pro, '')";
       $field_NCaja = "IFNULL(cm.NCaja, '')";
       $SQL_FolioOT = "
            LEFT JOIN t_tarima tt ON tt.ntarima = ch.IDContenedor 
            LEFT JOIN t_ordenprod op ON op.Cve_Articulo = IFNULL(e.cve_articulo, tt.cve_articulo ) AND IFNULL(op.Cve_Lote,'') = IFNULL(tt.lote, e.cve_lote) AND op.Folio_Pro = IFNULL(tt.Fol_Folio, op.Folio_Pro) 
            LEFT JOIN th_cajamixta cm ON cm.fol_folio = tt.Fol_Folio AND cm.Cve_CajaMix = tt.Caja_ref 
        ";

   }
   else if($num_produccion == 2)
    {
       $tabla_from = "V_ExistenciaGralProduccion";
       $tipo_prod  = " e.tipo = '{$zona_rtm_tipo}' AND ";
    }

    $sqlAlmacen = "";
    if($almacen)
       $sqlAlmacen = " (e.cve_almac = '{$almacen}')  AND ";//OR zona.cve_almacp = '{$almacen}'

$sql = "SET NAMES utf8mb4;";
$sth = \db()->prepare( $sql );
$sth->execute();

    $sql_decimales = "SELECT IF(COUNT(*) = 0, 3, Valor) AS Valor FROM t_configuraciongeneral WHERE cve_conf = 'decimales_cantidad'";
    if (!($res_decimales = mysqli_query($conn, $sql_decimales)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $decimales_cantidad = mysqli_fetch_array($res_decimales)['Valor'];

    $sql_decimales = "SELECT IF(COUNT(*) = 0, 3, Valor) AS Valor FROM t_configuraciongeneral WHERE cve_conf = 'decimales_costo'";
    if (!($res_decimales = mysqli_query($conn, $sql_decimales)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $decimales_costo = mysqli_fetch_array($res_decimales)['Valor'];

    $sql = "
      SELECT x.cve_almacen, x.id_almacen, x.almacen, x.folio_OT, x.NCaja, x.fecha_ingreso, x.zona, x.codigo, x.RP, x.cve_ubicacion, x.zona_recepcion, x.QA, x.contenedor, x.LP, x.clave, x.clave_alterna, x.descripcion, x.des_grupo, x.des_clasif, x.cajasxpallets, x.piezasxcajas, x.Pallet, x.Caja, x.Piezas, x.control_lotes, x.control_numero_series, x.lote, x.caducidad, x.nserie, x.peso, (x.cantidad) AS cantidad, (x.cantidad_kg) AS cantidad_kg, x.id_proveedor, (x.proveedor) AS proveedor, (x.empresa_proveedor) AS empresa_proveedor, x.tipo_ubicacion, x.control_abc, x.clasif_abc, x.um, x.control_peso, x.codigo_barras_pieza, 
            x.codigo_barras_caja, x.codigo_barras_pallet, x.referencia_well, x.pedimento_well {$sql_foliox} {$sql_proyectox} FROM(
         SELECT DISTINCT 
            ap.clave AS cve_almacen,
            e.cve_almac AS id_almacen,
            ap.nombre as almacen,
            {$field_folio_ot} AS folio_OT,
            {$field_NCaja} AS NCaja,
            z.des_almac as zona,
            {$field_bl}
            e.cve_ubicacion,
            zona.desc_ubicacion AS zona_recepcion,
            IF(IFNULL(e.Cuarentena, 0) = 1, 'Si','No') as QA,
            e.Cve_Contenedor as contenedor,
            IF(e.Cve_Contenedor != '', ch.CveLP, '') AS LP,
            {$field_folios} 
            IFNULL(a.cve_alt, '') as clave_alterna,
            IFNULL(a.cve_codprov, '') as codigo_barras_pieza, 
            IFNULL(a.barras2, '') as codigo_barras_caja, 
            IFNULL(a.barras3, '') as codigo_barras_pallet, 
            IFNULL(a.des_articulo, '') as descripcion,
            IFNULL(trs.Cantidad, 0) AS RP,
            IFNULL(gr.des_gpoart, '') as des_grupo,
            IFNULL(cl.cve_sgpoart, '') as des_clasif,
            IFNULL(a.cajas_palet, 0) as cajasxpallets,
            IFNULL(a.num_multiplo, 0) as piezasxcajas, 
            0 as Pallet,
            0 as Caja, 
            0 as Piezas,
            ta.recurso as referencia_well,
            ta.Pedimento as pedimento_well,
            a.control_lotes as control_lotes,
            a.control_numero_series as control_numero_series,
            IFNULL(e.cve_lote,'') AS lote,
            COALESCE(IF(IFNULL(a.Caduca, 'N') = 'S' AND IFNULL(e.cve_lote,'') != '',IF(DATE_FORMAT(l.Caducidad,'%Y-%m-%d') = '0000-00-00','',DATE_FORMAT(l.Caducidad,'%d-%m-%Y')),'')) AS caducidad,
            COALESCE(IF(a.control_numero_series = 'S',e.cve_lote,'' ), ' ') AS nserie,
            a.peso,
            IF((a.control_peso = 'S' AND um.mav_cveunimed = 'H87') OR a.control_peso = 'S', TRUNCATE(e.Existencia, $decimales_cantidad), e.Existencia) as cantidad,
            TRUNCATE(e.Existencia*a.peso*(IF(IFNULL(a.num_multiplo, 0) = 0, 1, a.num_multiplo)), $decimales_cantidad) AS cantidad_kg,
            IFNULL(p.ID_proveedor, '') AS id_proveedor,
            IFNULL(p.Nombre, '') AS proveedor,
            IFNULL(DATE_FORMAT(td.fecha_fin, '%d-%m-%Y'), '') AS fecha_ingreso,
            IFNULL(poc.Nombre, '') AS empresa_proveedor,
                CASE 
                    WHEN u.Picking = 'S' THEN 'Si'
                    WHEN u.Picking = 'N' THEN 'No'
                END
            AS tipo_ubicacion,
            a.control_abc,
            z.clasif_abc,
            {$sql_folios}
            {$sql_proyecto}
            IFNULL(um.cve_umed, '') as um,
            a.control_peso
            FROM
                {$tabla_from} e
            INNER JOIN c_articulo a ON a.cve_articulo = e.cve_articulo
            LEFT JOIN c_unimed um ON a.unidadMedida = um.id_umed
            LEFT JOIN c_gpoarticulo gr ON gr.cve_gpoart = a.grupo
            LEFT JOIN c_sgpoarticulo cl ON cl.cve_sgpoart = a.clasificacion
            LEFT JOIN c_ubicacion u ON u.idy_ubica = e.cve_ubicacion
            LEFT JOIN c_lotes l ON l.LOTE = e.cve_lote AND l.cve_articulo = e.cve_articulo
            LEFT JOIN c_almacen z ON z.cve_almac = u.cve_almac
            #LEFT JOIN c_almacen z ON z.cve_almac = (SELECT cve_almac FROM c_ubicacion WHERE CONCAT(idy_ubica, '') = CONCAT(e.cve_ubicacion, ''))
            LEFT JOIN c_charolas ch ON IFNULL(ch.clave_contenedor, '') = IFNULL(e.Cve_Contenedor, '') AND IFNULL(ch.clave_contenedor, '') != ''
            LEFT JOIN tubicacionesretencion zona ON zona.cve_ubicacion = e.cve_ubicacion {$sqlCollation}
            LEFT JOIN c_almacenp ap ON ap.id = IFNULL(e.cve_almac, z.cve_almacenp) #OR ap.id = zona.cve_almacp
            LEFT JOIN t_recorrido_surtido trs ON trs.Cve_articulo = e.cve_articulo AND trs.cve_lote = e.cve_lote AND trs.cve_almac = z.cve_almac AND e.cve_ubicacion = trs.idy_ubica 
            {$sql_existencia_cajas} JOIN ts_existenciacajas ec ON ec.idy_ubica = e.cve_ubicacion AND e.cve_articulo = ec.cve_articulo AND IFNULL(ec.cve_lote, '') = IFNULL(e.cve_lote, '')  AND e.cve_almac = ec.cve_almac
            #AND IFNULL(ec.nTarima, '') = IFNULL(ch.IDContenedor, '')
             {$SQL_FolioOT} 
            LEFT JOIN rel_articulo_proveedor rap ON rap.Cve_Articulo = e.cve_articulo AND e.ID_Proveedor = rap.Id_Proveedor AND rap.Id_Proveedor != 0
            LEFT JOIN c_proveedores p ON p.ID_Proveedor = e.ID_Proveedor AND p.ID_Proveedor = IFNULL(rap.Id_Proveedor, e.ID_Proveedor)
            LEFT JOIN td_entalmacen td ON td.cve_articulo = e.cve_articulo AND td.cve_lote = e.cve_lote $sqlEliminaraduanaTemporalmente
            LEFT JOIN th_entalmacen th ON th.Cve_Proveedor = e.ID_Proveedor AND th.Fol_folio = td.fol_folio $sqlEliminaraduanaTemporalmente #AND th.tipo = 'OC'
            LEFT JOIN th_aduana ta ON ta.num_pedimento = th.id_ocompra $sqlEliminaraduanaTemporalmente
            LEFT JOIN c_proveedores poc ON poc.ID_Proveedor = ta.ID_Proveedor
            LEFT JOIN t_trazabilidad_existencias tr ON CONVERT(tr.cve_articulo, CHAR) = CONVERT(e.cve_articulo, CHAR) AND CONVERT(IFNULL(tr.cve_lote, ''), CHAR) = CONVERT(IFNULL(e.cve_lote, ''), CHAR) AND e.cve_ubicacion = tr.idy_ubica AND tr.cve_almac = e.cve_almac AND tr.idy_ubica IS NOT NULL AND tr.id_proveedor = e.Id_Proveedor AND IFNULL(tr.ntarima, '') = IFNULL(ch.IDContenedor, '')
            {$left_join_folios}
            {$sqlCliente}
            #LEFT JOIN td_entalmacenxtarima ent ON ent.cve_articulo = e.cve_articulo AND ent.cve_lote = e.cve_lote AND ent.ClaveEtiqueta = ch.CveLP AND ent.fol_folio IN (SELECT Fol_Folio FROM th_entalmacen WHERE Cve_Almac = ap.clave AND (tipo = 'RL' OR tipo = 'OC'))
                WHERE {$sqlAlmacen} {$tipo_prod} e.Existencia > 0  {$sqlArticulo} {$sqlContenedor} {$sqlZona} {$sqlFactura} {$sqlProyecto}
                {$sqlProveedor} {$sqlGrupo} {$sqlClasif} {$sql_obsoletos} {$SQLrefWell} {$SQLpedimentoW} {$sqlPicking}
                $zona_rts

            #GROUP BY id_proveedor
            #GROUP BY id_proveedor, cve_almacen, cve_ubicacion, contenedor, clave, lote, nserie

            ORDER BY a.des_articulo, folio_OT, (NCaja+0) ASC

                )x
            #where x.lote != '--'
            WHERE 1 AND x.id_almacen = '{$almacen}' #AND x.id_proveedor IS NOT NULL
            {$sqlbl} 
            {$sqlLP} 
            {$sqlLotes} 
            {$sqlproveedor_tipo} 
            {$sqlProveedor2}
            GROUP BY cve_almacen, cve_ubicacion, contenedor, clave, lote, nserie, id_proveedor #{$group_mostrar_folios} {$group_mostrar_proyecto}
            ";

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
            'Clave Alterna',
            'CB Pieza',
            'Clasificacion',
            'Descripcion',
            'Lote | Serie',
            'Caducidad',
            'Unidad Medida',
            'Total',
            'RP',
            'Prod QA',
            'Disponible',
            'Fecha Ingreso',
            'Proyecto',
            'Grupo',
            'Proveedor'
        ];

        if($mostrar_folios_excel_existencias)
            $columnas = [
                'Codigo BL', 
                'Pallet|Cont', 
                'License Plate (LP)', 
                'Clave', 
                'Clave Alterna', 
                'CB Pieza', 
                'Clasificacion', 
                'Descripcion', 
                'Lote | Serie', 
                'Caducidad', 
                'Unidad Medida', 
                'Total', 
                'RP', 
                'Prod QA', 
                'Disponible', 
                'Fecha Ingreso', 
                'Folio OC', 
                'Proyecto', 
                'Grupo', 
                'Proveedor'
            ];

        if($instancia == 'welldex')
        $columnas = [
            'Codigo BL',
            'Pallet|Cont',
            'License Plate (LP)',
            'Clave',
            'CB Pieza',
            'Clasificacion',
            'Descripcion',
            'Lote | Serie',
            'Caducidad',
            'Unidad Medida',
            'Total',
            'RP',
            'Prod QA',
            'Disponible',
            'Fecha Ingreso',
            'Folio OC',
            'Proyecto',
            'Grupo',
            'Proveedor',
            'Referencia Well',
            'Pedimento Well'
        ];

        $filename = "Reporte de Existencias" . ".xls";
        ob_clean();

        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Type: application/vnd.ms-excel");
        //header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        //header('Content-Length: ' . filesize($filename));
        //header('Content-Transfer-Encoding: binary');
        //header('Cache-Control: must-revalidate');
        //header('Pragma: public');

        foreach($columnas as $column) 
        {            
            echo $column . "\t" ;            
        }
        print("\r\n");

//echo $sql . "\t";

        while($row = mysqli_fetch_object($res))
        {
            echo utf8_decode(" ".($row->codigo)." ") . "\t";
            echo trim($row->contenedor) . "\t";
            echo trim($row->LP) . "\t";
            //echo ($row->contenedor != '')?($row->contenedor. "\t"):("\t");
            //echo ($row->LP != '')?($row->LP. "\t"):("\t");
            echo ($row->clave) . "\t";
            echo ($row->clave_alterna) . "\t";
            echo utf8_decode($row->codigo_barras_pieza) . "\t";
            echo utf8_decode($row->des_clasif) . "\t";
            echo utf8_decode($row->descripcion) . "\t";
            echo utf8_decode($row->lote) . "\t";
            echo $row->caducidad. "\t"; 
            echo $row->um . "\t";
            //echo "\t";
            if($row->QA == 'No') echo $row->cantidad. "\t" ; else echo "". "\t";
            if($row->RP != 0) echo $row->RP. "\t" ; else echo "". "\t";//echo $row->RP. "\t";
            //echo $row->Prod_QA. "\t";
            if($row->QA == 'Si') echo $row->cantidad. "\t" ; else echo "". "\t";
            //echo $row->Obsoletos. "\t";

            //echo $Pallet . "\t";
            //echo $Caja . "\t";
            //echo $Piezas . "\t";


            //echo ($row->cantidad-$row->RP-$row->Obsoletos) . "\t";//-$row->Prod_QA
            echo ($row->cantidad-$row->RP) . "\t";
            echo utf8_decode($row->fecha_ingreso) . "\t";
            if($mostrar_folios_excel_existencias) echo utf8_decode($row->folio) . "\t";
            echo utf8_decode($row->proyecto) . "\t";
            echo utf8_decode($row->des_grupo) . "\t";
            echo utf8_decode($row->proveedor) . "\t";
            if($instancia == 'welldex')
            {
                echo utf8_decode($row->referencia_well) . "\t";
                echo utf8_decode($row->pedimento_well) . "\t";
            }
            echo  "\r\n";
        }
        //echo $sql. "\t";

    mysqli_close($conn);

        exit;
    }


    public function exportar_kardex()
    {

//***************************************************************************************************
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    $kardex_consolidado = 0;
    if(isset($_GET['kardex_consolidado']))
      $kardex_consolidado = $_GET['kardex_consolidado'];

    $almacen= $_GET['almacen'];
    $movimiento = $_GET['movimiento'];
    $_criterio = $_GET['criterio'];
    $id_proveedor = $_GET['cve_proveedor'];

    //if($kardex_consolidado == 0)
    //{//if($kardex_consolidado == 0)
    $lote= $_GET['lote'];
    $cve_articulo= $_GET['cve_articulo'];
    $fecha_inicio= $_GET['fechaI'];
    $fecha_final= $_GET['fechaF'];

    $OCBusq = $_GET['OCBusq'];

    $SQLCriterio = "";
    $SQLCriterioCajas = "";

    if($_criterio)
    {
        $SQLCriterio = " AND (k.cve_articulo LIKE '%".$_criterio."%' OR k.cve_lote LIKE '%".$_criterio."%' OR a.des_articulo LIKE '%".$_criterio."%' OR uo.CodigoCSD LIKE '%".$_criterio."%' OR ud.CodigoCSD LIKE '%".$_criterio."%' OR ch.clave_contenedor LIKE '%".$_criterio."%' OR ch.CveLP LIKE '%".$_criterio."%' OR rd.desc_ubicacion LIKE '%".$_criterio."%' OR m.nombre LIKE '%".$_criterio."%' OR k.cve_usuario LIKE '%".$_criterio."%' OR rd.cve_ubicacion LIKE '%".$_criterio."%' OR k.origen LIKE '%".$_criterio."%' OR k.destino LIKE '%".$_criterio."%') ";
        $SQLCriterioCajas = " AND (k.cve_articulo LIKE '%".$_criterio."%' OR k.cve_lote LIKE '%".$_criterio."%' OR a.des_articulo LIKE '%".$_criterio."%' OR u_or.CodigoCSD LIKE '%".$_criterio."%' OR u_dest.CodigoCSD LIKE '%".$_criterio."%' OR lp.clave_contenedor LIKE '%".$_criterio."%' OR lp.CveLP LIKE '%".$_criterio."%' OR k.cve_usuario LIKE '%".$_criterio."%') ";
    }


    $SQL_Mov = "";
    if($movimiento)
    {
        $SQL_Mov = " AND k.id_TipoMovimiento IN ($movimiento) ";
        if($movimiento == 6 || $movimiento == 12)
            $SQL_Mov = " AND k.id_TipoMovimiento IN (6,12) ";
    }


    $SQLOC = "";
    if($OCBusq)
        $SQLOC = " AND (oc.num_pedimento LIKE '%$OCBusq%' OR oc.Factura LIKE '%$OCBusq%' OR ent_ocompra.num_pedimento LIKE '%$OCBusq%' OR ent_ocompra.Factura LIKE '%$OCBusq%') ";

    $SQLArticulo = "";
    if($cve_articulo)
    {
        $SQLArticulo = " AND k.cve_articulo = '".$cve_articulo."' ";
    }

    $SQLLote = "";
    if($lote)
    {
        $SQLLote = " AND k.cve_lote = '".$lote."' ";
    }
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    $SQLFecha = "";
    if($fecha_inicio && $fecha_final)
    {
        $SQLFecha = " AND k.fecha BETWEEN STR_TO_DATE('".$fecha_inicio."', '%d-%m-%Y') AND STR_TO_DATE('".$fecha_final."', '%d-%m-%Y')";
        if($fecha_inicio == $fecha_final)
        $SQLFecha = " AND DATE_FORMAT(k.fecha, '%Y-%m-%d') = STR_TO_DATE('".$fecha_inicio."', '%d-%m-%Y')";
    }
    else if(!$fecha_inicio && $fecha_final)
    {
        $SQLFecha = " AND k.fecha <= STR_TO_DATE('".$fecha_final."', '%d-%m-%Y')";
    }
    else if($fecha_inicio && !$fecha_final)
    {
        $SQLFecha = " AND k.fecha >= STR_TO_DATE('".$fecha_inicio."', '%d-%m-%Y')";
    }

    if(!$SQLArticulo && !$SQLLote && !$SQLFecha && !$_criterio && !$OCBusq) //&& !$id_proveedor
    {
        //$SQLFecha = " AND DATE_FORMAT(k.fecha, '%d-%m-%Y') = DATE_FORMAT((SELECT MAX(fecha) FROM t_cardex), '%d-%m-%Y') ";
        $SQLFecha = " AND k.fecha >= (SELECT DATE_FORMAT(DATE_ADD(CURDATE(), INTERVAL -7 DAY), '%d-%m-%Y') AS fecha_semana FROM DUAL) ";
    }

    $tipomovimiento = ""; $SQLMovimientos = "";
    if(isset($_GET['tipomovimiento']))
    {
        $tipomovimiento= $_GET['tipomovimiento'];
        $SQLMovimientos = " AND k.id_TipoMovimiento IN (2, 12) ";
    }
    
    if($tipomovimiento)
    {
        $SQLMovimientos = " AND k.id_TipoMovimiento IN ($tipomovimiento) ";
    }

    $sqlSesionEmpresa = "";

    if($id_proveedor)
    {
        //INNER JOIN c_proveedores p ON p.ID_Proveedor = c.ID_Proveedor AND IFNULL(c.ID_Proveedor, 0) != 0 AND p.ID_Proveedor IN (ent_orig.Cve_Proveedor, ent_dest.Cve_Proveedor, c.ID_Proveedor) AND p.ID_Proveedor = {$id_proveedor}
        $sqlSesionEmpresa = "
        LEFT JOIN c_cliente c ON c.Cve_Clte IN (th_orig.Cve_clte, th_dest.Cve_clte)
        INNER JOIN c_proveedores p ON p.ID_Proveedor = {$id_proveedor} OR (ent_ocompra.procedimiento = p.cve_proveedor OR ent_orig.Cve_Proveedor = {$id_proveedor})
        ";
        //p.ID_Proveedor IN (ent_orig.Cve_Proveedor, ent_dest.Cve_Proveedor, c.ID_Proveedor) AND
    }


    $fields_reporte_w = ""; $sql_ref_well = ""; $sql_pedimento_well = "";
    if(isset($_GET['reporte_w']))
    {
        $fields_reporte_w = "
        ###############################################################################################
        IFNULL(oc.Pedimento, '') AS Pedimento_well, IFNULL(oc.recurso, '') AS ref_well, 
        ###############################################################################################
        ";

        $refWell = $_GET['refWell'];
        if($refWell) $sql_ref_well = " AND oc.recurso LIKE '%$refWell%' "; 

        $pedimentoW = $_GET['pedimentoW'];
        if($pedimentoW) $sql_pedimento_well = " AND oc.Pedimento LIKE '%$pedimentoW%' "; 
    }

    $sql_instancia = "SELECT DISTINCT IFNULL(Valor, '') as instancia FROM t_configuraciongeneral WHERE cve_conf = 'instancia'";
    if (!($res_instancia = mysqli_query($conn, $sql_instancia)))
        echo "Falló la preparación instancia: (" . mysqli_error($conn) . ") ";
    $instancia = mysqli_fetch_array($res_instancia)['instancia'];

    $sql_cantidad = " IFNULL(IFNULL(k.ajuste, k.cantidad), 0) ";
    $gb_instancia = " GROUP BY k.id ";
    if($instancia == 'repremundo')
    {
        $sql_cantidad = " IF(k.id_TipoMovimiento = 8, SUM(IFNULL(k.ajuste, 0)), SUM(IFNULL(k.cantidad, 0)) ) ";
        $gb_instancia = " GROUP BY id_articulo, cve_lote, almacen, origen, destino, clave_contenedor, movimiento ";
    }

    $sql = "SELECT DISTINCT 
    {$fields_reporte_w} 
    #IF(ec.Id_Caja IS NOT NULL, CONCAT('(',(SELECT COUNT(*) FROM ts_existenciacajas exc WHERE exc.idy_ubica = k.destino AND k.cve_articulo = exc.cve_articulo AND IFNULL(exc.cve_lote, '') = IFNULL(k.cve_lote, '') AND k.cve_almac = exc.cve_almac AND k.id_TipoMovimiento IN (2, 12, 6)), ')'), '') AS cajas, 
    COUNT(DISTINCT cj.id) AS cajas,
    IFNULL(IFNULL(pr_oc.descripcion, pr_ent.descripcion), '') AS Protocolo,
    IFNULL(tp.Descripcion, '') AS Prioridad_Pedido,
    GROUP_CONCAT(DISTINCT IFNULL(IFNULL(CONCAT(IFNULL(ent_ocompra.num_pedimento, ''), IF(IFNULL(ent_ocompra.Factura, '') != '', ' | ', ''), IFNULL(ent_ocompra.Factura, '')), ''), CONCAT(IFNULL(oc.num_pedimento, ''), IF(IFNULL(oc.Factura, '') != '', ' | ', ''), IFNULL(oc.Factura, ''))) SEPARATOR ' ; ') AS oc,
    k.cve_articulo AS id_articulo, a.des_articulo AS des_articulo, k.cve_lote AS cve_lote, 
                IF(a.control_lotes = 'S' AND a.Caduca = 'S'  AND IFNULL(k.cve_lote, '') != '', IFNULL(IF(DATE_FORMAT(lote.Caducidad, '%Y-%m-%d') != DATE_FORMAT('0000-00-00', '%Y-%m-%d'),DATE_FORMAT(lote.Caducidad, '%d-%m-%Y'), DATE_FORMAT(lote.Caducidad, '%d-%m-%Y')),''), '') AS Caducidad, 
                DATE_FORMAT(k.fecha, '%d-%m-%Y | %H:%m:%i') AS fecha, 
                IF(k.id_TipoMovimiento NOT IN (20,1, 21, 22, 25, 26, 27, 8) AND LEFT(k.origen, 4) != 'Inv_' AND k.origen != 'Inventario Inicial',IFNULL(IFNULL(uo.CodigoCSD, ro.desc_ubicacion), IF(LEFT(k.origen, 3) = 'PT_' OR LEFT(k.origen, 2) = 'OT', k.origen, IF(k.id_TipoMovimiento IN (20,1, 21, 22, 25, 26, 27, 8), k.origen, 'RTM'))), IF(k.id_TipoMovimiento = 8, uo.CodigoCSD,k.origen)) AS origen, 
                IFNULL(ch.clave_contenedor,'') AS clave_contenedor, IFNULL(ch.CveLP, '') AS CveLP, IFNULL(uo.CodigoCSD, '') AS bl, 
                CONVERT({$sql_cantidad},FLOAT) as cantidad,
                #IFNULL(ud.CodigoCSD, rd.desc_ubicacion) AS destino, 
                IFNULL(ud.CodigoCSD, IFNULL(rd.desc_ubicacion, IF(k.id_TipoMovimiento = 8, CONCAT(k.destino, IF(IFNULL(th_dest.Pick_Num, '') = '', '', ' | '), IFNULL(th_dest.Pick_Num, '')),k.destino))) AS destino, 
                #k.cantidad, 
                m.nombre AS movimiento, k.cve_usuario, al.nombre AS almacen 
                FROM t_cardex k 
                LEFT JOIN c_articulo a ON a.cve_articulo = k.cve_articulo
                LEFT JOIN c_almacenp al ON al.id = k.Cve_Almac
                LEFT JOIN t_MovCharolas mch ON k.id = mch.id_kardex #OR (k.origen = mch.Origen AND k.destino = mch.Destino AND k.id_TipoMovimiento = mch.Id_TipoMovimiento AND k.cve_usuario = mch.Cve_Usuario)
                LEFT JOIN th_entalmacen ent_orig ON ent_orig.Fol_Folio = k.origen
                LEFT JOIN th_entalmacen ent_dest ON ent_dest.Fol_Folio = k.destino
                LEFT JOIN c_almacenp ent_alm_orig ON ent_alm_orig.clave = ent_orig.cve_almac
                LEFT JOIN c_almacenp ent_alm_dest ON ent_alm_dest.clave = ent_dest.cve_almac

                LEFT JOIN c_almacen aog ON aog.cve_almacenp = ent_alm_orig.id
                LEFT JOIN c_ubicacion uo ON uo.idy_ubica = k.origen and aog.cve_almac = uo.cve_almac
                LEFT JOIN tubicacionesretencion ro ON ro.cve_ubicacion = k.origen 
                LEFT JOIN c_ubicacion ud ON ud.idy_ubica = k.destino
                LEFT JOIN tubicacionesretencion rd ON rd.cve_ubicacion = k.destino 
                LEFT JOIN t_tipomovimiento m ON m.id_TipoMovimiento = k.id_TipoMovimiento
                LEFT JOIN c_lotes lote ON lote.cve_articulo = k.cve_articulo AND lote.Lote = k.cve_lote

                LEFT JOIN th_pedido th_orig ON th_orig.Fol_folio = k.origen
                LEFT JOIN th_pedido th_dest ON th_dest.Fol_folio = REPLACE(k.destino, '-1', '')

                LEFT JOIN td_pedidoxtarima pxt ON pxt.Fol_folio = th_dest.Fol_folio AND pxt.Cve_articulo = a.cve_articulo AND pxt.cve_lote = k.cve_lote
                LEFT JOIN c_charolas ch ON ch.IDContenedor = IFNULL(mch.ID_Contenedor, pxt.nTarima)

                LEFT JOIN c_almacenp alm_orig ON alm_orig.id = th_orig.cve_almac
                LEFT JOIN c_almacenp alm_dest ON alm_dest.id = th_dest.statusaurora


                LEFT JOIN th_aduana oc ON ent_orig.id_ocompra = oc.num_pedimento AND DATE_FORMAT(oc.fech_pedimento, '%Y-%m-%d') = DATE_FORMAT(k.fecha, '%Y-%m-%d')
                LEFT JOIN td_entalmacen ent_oc ON ent_oc.cve_articulo = k.cve_articulo AND ent_oc.cve_lote = k.cve_lote AND IFNULL(ent_oc.num_orden, 0) != 0
                LEFT JOIN th_aduana ent_ocompra ON ent_ocompra.num_pedimento = ent_oc.num_orden AND DATE_FORMAT(ent_ocompra.fech_pedimento, '%Y-%m-%d') = DATE_FORMAT(k.fecha, '%Y-%m-%d')

                LEFT JOIN t_protocolo pr_ent ON pr_ent.ID_Protocolo = IFNULL(ent_orig.ID_Protocolo, ent_dest.ID_Protocolo)
                LEFT JOIN t_protocolo pr_oc ON pr_oc.ID_Protocolo = oc.ID_Protocolo
                LEFT JOIN t_tiposprioridad tp ON tp.ID_Tipoprioridad = IFNULL(th_orig.ID_Tipoprioridad, th_dest.ID_Tipoprioridad)

                 {$sqlSesionEmpresa} 
                #LEFT JOIN ts_existenciacajas ec ON ec.idy_ubica = k.destino AND k.cve_articulo = ec.cve_articulo AND IFNULL(ec.cve_lote, '') = IFNULL(k.cve_lote, '')  AND k.cve_almac = ec.cve_almac AND k.id_TipoMovimiento IN (2, 12, 6)
                LEFT JOIN t_MovCharolas cj ON cj.id_kardex = k.id AND cj.EsCaja = 'S'
                WHERE k.Cve_Almac = {$almacen} {$SQLArticulo} {$SQLLote} {$SQLCriterio} {$SQLFecha} {$SQLMovimientos} {$SQLOC} {$SQL_Mov} {$sql_ref_well} {$sql_pedimento_well}

                #GROUP BY id_articulo, cve_lote, almacen, origen, destino, clave_contenedor, CveLP, movimiento
                #GROUP BY ext.ID_Contenedor
                #GROUP BY k.id
                #GROUP BY id_articulo, cve_lote, almacen, origen, destino, clave_contenedor, movimiento
                {$gb_instancia}
                ORDER BY DATE(k.fecha) DESC, k.id DESC
            ";

    if($kardex_consolidado == 1)
    {
        /*
        $sql = "SELECT kar.id_articulo, kar.des_articulo, 
                       GROUP_CONCAT(DISTINCT kar.movimiento SEPARATOR ', ') AS movimiento, kar.cve_usuario, 
                       SUM(kar.cajas) AS cajas, kar.almacen, 
                       '' AS oc, '' AS cve_lote, '' AS Caducidad, '' AS fecha, kar.origen, '' AS clave_contenedor, 
                       '' AS CveLP, '' AS bl, '' AS ajuste, '' AS stockinicial, kar.destino, '' AS cantidad
                FROM (".$sql.") AS kar
                GROUP BY kar.id_articulo";
        */
        $sql = "SELECT  #kar.al_id_or, kar.al_id_dest,
                        kar.fecha, kar.Almacen_Origen, kar.Almacen_Destino, kar.id_articulo, kar.des_articulo, 
                                       'Traslado' AS movimiento, kar.cve_usuario, 
                                       COUNT(DISTINCT kar.cajas) AS cajas, SUM(kar.num_unidades) AS num_unidades, 
                                       #kar.almacen, 
                                       #kar.ntarima, kar.ntarima2,
                                       #'' AS oc, '' AS cve_lote, '' AS Caducidad, kar.origen, 
                                       kar.clave_contenedor AS clave_contenedor, 
                                       kar.CveLP AS CveLP#, kar.caja
                                       #,kar.bl AS bl, '' AS ajuste, '' AS stockinicial, kar.destino, '' AS cantidad
                                FROM (

                                SELECT alp_or.id as al_id_or, alp_dest.id as al_id_dest, k.fecha, alp_or.clave as Almacen_Origen, alp_dest.clave as Almacen_Destino, a.cve_articulo as id_articulo, a.des_articulo as des_articulo, 
                                       'Traslado' AS movimiento, k.cve_usuario, 
                                       #mch_cj.ID_Contenedor AS cajas, 
                                       ec.Id_Caja AS cajas, k.cantidad as num_unidades,
                                       al_dest.des_almac as almacen, lp.IDContenedor as ntarima, mch.ID_Contenedor as ntarima2,
                                       '' AS oc, '' AS cve_lote, '' AS Caducidad, k.origen as origen, lp.Clave_Contenedor AS clave_contenedor,  #cj.Clave_Contenedor as caja,
                                       lp.CveLP AS CveLP, u_dest.CodigoCSD AS bl, '' AS ajuste, '' AS stockinicial, k.destino, '' AS cantidad
                                FROM ts_existenciacajas ec 
                                LEFT JOIN t_MovCharolas mch ON mch.ID_Contenedor = ec.Id_Caja AND IFNULL(mch.EsCaja, '') = 'S'
                                LEFT JOIN t_cardex k ON mch.id_kardex = k.id AND ec.cve_articulo = k.cve_articulo and IFNULL(ec.cve_lote, '') = IFNULL(k.cve_lote, '')
                                LEFT JOIN c_ubicacion u_or ON u_or.idy_ubica = mch.Origen
                                LEFT JOIN c_ubicacion u_dest ON u_dest.idy_ubica = mch.Destino
                                LEFT JOIN c_almacen al_or ON al_or.cve_almac = u_or.cve_almac
                                LEFT JOIN c_almacen al_dest ON al_dest.cve_almac = u_dest.cve_almac
                                LEFT JOIN c_almacenp alp_or ON alp_or.id = al_or.cve_almacenp
                                LEFT JOIN c_almacenp alp_dest ON alp_dest.id = al_dest.cve_almacenp
                                LEFT JOIN c_articulo a ON a.cve_articulo = k.cve_articulo
                                LEFT JOIN c_charolas cj ON cj.IDContenedor = ec.Id_Caja
                                #LEFT JOIN t_MovCharolas mch_cj ON mch.id_kardex = k.id AND mch_cj.EsCaja = 'S'
                                LEFT JOIN c_charolas lp ON lp.IDContenedor = ec.nTarima
                                WHERE k.id_TipoMovimiento IN (6, 12) AND mch.Id_TipoMovimiento IN (6, 12) AND ec.Id_Caja is not null AND alp_dest.id != alp_or.id
                                AND (alp_or.id= $almacen OR alp_dest.id = $almacen)
                                AND IFNULL(lp.CveLP, '') != ''
                                {$SQLArticulo} {$SQLLote} {$SQLCriterioCajas} {$SQLFecha}
                                #GROUP BY clave_contenedor 
                  ) AS kar
                where (kar.al_id_or = $almacen OR kar.al_id_dest = $almacen)  #kar.cve_usuario = 'wmsmaster'
                GROUP BY kar.clave_contenedor, kar.id_articulo
                ORDER BY kar.clave_contenedor
                ";

    }

    $res = "";
    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";
    }
        $columnas = [
            'Fecha', 
            'Clave', 
            'Articulo', 
            'Lote|Serie', 
            'Caducidad', 
            'Pallet|Contenedor', 
            'License Plate (LP)', 
            'Movimiento', 
            'Origen', 
            'Destino', 
            'BL', 
            'Cantidad', 
            'Usuario', 
            'OC | Factura',
            'Protocolo',
            'Prioridad Pedido'
        ];

        if(isset($_GET['reporte_w']))
        $columnas = [
                    'Fecha', 
                    'Clave', 
                    'Articulo', 
                    'Lote|Serie', 
                    'Caducidad', 
                    'Pallet|Contenedor', 
                    'License Plate (LP)', 
                    'Movimiento', 
                    'Origen', 
                    'Destino', 
                    'BL', 
                    'Cantidad', 
                    'Usuario', 
                    'OC | Factura',
                    'Protocolo',
                    'Prioridad Pedido', 
                    'Referencia Well',
                    'Pedimento Well'
                ];

        if($kardex_consolidado == 1)
            $columnas = [
                'Clave', 
                'Descripcion', 
                'Movimiento', 
                'Almacen Origen', 
                'Almacen Destino', 
                'Pallet/Contenedor', 
                'QTY UNITS', 
                'QTY CAJAS',
                'Usuario'
            ];


        $filename = "Reporte Kardex" . ".xls";
        if(isset($_GET['tipomovimiento']))
            $filename = "Reporte de Movimientos" . ".xls";
        ob_clean();

        header("Content-Disposition: attachment; filename=\"$filename\"");
        //header('Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Type: application/vnd.ms-excel");

        foreach($columnas as $column) 
        {            
            echo $column . "\t" ;            
        }
        print("\r\n");

        while($row = mysqli_fetch_object($res))
        {
            //extract($row);

        if($kardex_consolidado == 0)
        {
            echo $row->fecha . "\t";
            echo utf8_decode($row->id_articulo) . "\t";
            echo utf8_decode($row->des_articulo) . "\t";
            echo utf8_decode($row->cve_lote) . "\t";
            echo $row->Caducidad . "\t";
            echo utf8_decode($row->CveLP) . "\t";
            echo utf8_decode($row->clave_contenedor) . "\t";
            echo utf8_decode($row->movimiento) . "\t";
            echo utf8_decode(str_replace("-", "_", $row->origen)) . "\t";
            echo utf8_decode(str_replace("-", "_", $row->destino)) . "\t";
            echo str_replace("-", "_", $row->bl) . "\t";
            echo $row->cantidad . "\t";
            echo $row->cve_usuario . "\t";
            echo $row->oc . "\t";
            echo $row->Protocolo . "\t";
            echo $row->Prioridad_Pedido . "\t";
            if(isset($_GET['reporte_w']))
            {
                echo $row->ref_well . "\t";
                echo $row->Pedimento_well . "\t";
            }
        }
        else
        {
          echo $row->id_articulo. "\t";
          echo utf8_decode($row->des_articulo). "\t";
          echo utf8_decode($row->movimiento). "\t";
          echo $row->Almacen_Origen. "\t";
          echo $row->Almacen_Destino. "\t";
          echo utf8_decode($row->clave_contenedor). "\t";
          echo $row->num_unidades. "\t";
          echo $row->cajas. "\t";
          echo $row->cve_usuario. "\t";
        }


            echo  "\r\n";


        }
    mysqli_close($conn);
/*
    }//if($kardex_consolidado == 0)
    else
    {
        $columnas = [
            //'Fecha', 
            'Clave', 
            'Articulo', 
            //'Pallet|Contenedor', 
            //'License Plate (LP)', 
            'Movimiento', 
            'Almacen Origen', 
            'Origen', 
            'Almacen Destino', 
            'Destino', 
            //'Cantidad', 
            'Cajas',
            'Usuario'
        ];

        $filename = "Reporte Kardex" . ".xls";
        ob_clean();

        header("Content-Disposition: attachment; filename=\"$filename\"");
        //header('Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Type: application/vnd.ms-excel");

        foreach($columnas as $column) 
        {            
            echo $column . "\t" ;            
        }
        print("\r\n");

        $reporte = json_decode($_GET['reporte'], true);
        //$reporte = explode("==|==", $reporte);
        //var_dump($reporte);
        //exit;
        //$k = 0;
        foreach($reporte as $rep)
        {
            //extract($rep);
            echo utf8_decode($rep["fecha"]) . "\t";
            echo utf8_decode($rep["cve_articulo"]) . "\t";
            echo utf8_decode($rep["descripcion"]) . "\t";
            echo utf8_decode($rep["pallet"]) . "\t";
            echo utf8_decode($rep["lp"]) . "\t";
            echo utf8_decode($rep["movimiento"]) . "\t";
            echo utf8_decode($rep["alm_origen"]) . "\t";
            echo utf8_decode($rep["blorigen"]) . "\t";
            echo utf8_decode($rep["alm_destino"]) . "\t";
            echo utf8_decode($rep["bldestino"]) . "\t";
            echo utf8_decode($rep["cantidad"]) . "\t";
            echo utf8_decode($rep["cajas"]) . "\t";
            echo utf8_decode($rep["usuario"]) . "\t";
            echo  "\r\n";
        }
    }
*/
/*
        $this->response(200, [
            'id' =>  $id,
            'folios' => $folios,
            'Embarque' => 'Embarque OK'
        ]);
*/
        exit;
    }


    public function inventario_conteo()
    {

//***************************************************************************************************
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);


    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'utf8') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset2'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);

    $id_inventario = $_GET['id'];
    $status        = "";//$_GET['status'];
    $cia           = "";//$_GET['comp'];
    $usuario       = $_GET['usuario'];
    $conteo        = $_GET['conteo_usuario'];
    $fecha         = "";//$_GET['fecha_inv'];
    $ubicacion     = $_GET['ubicacion_inv'];
    $codigo_csd    = $_GET['ubicacion_text_inv'];
    $codigo_rack   = $_GET['ubicacion_rack'];
    $tipo          = $_GET['tipo'];
//https://grupoasl.assistpro-adl.com/inventario/conteos?id=651&conteo_usuario=1&fecha_inv=14-02-2024%2008:19:05&ubicacion_inv=&ubicacion_text_inv=Seleccione%20Ubicaci%C3%B3n&ubicacion_rack=&usuario=&tipo=F%C3%ADsico
      $sql_usuario = "";
      if($usuario) $sql_usuario = "AND inv.cve_usuario = '{$usuario}'";

      $sql_rack = "";
      if($codigo_rack) $sql_rack = "AND ub.cve_rack = '{$codigo_rack}'";

      $sql_ubicacion = "AND inv.idy_ubica = v.cve_ubicacion";
      if($ubicacion) $sql_ubicacion = "AND inv.idy_ubica = '{$ubicacion}'";

      $sql_ubicacion2 = "";
      if($ubicacion) $sql_ubicacion2 = "AND inv.idy_ubica = '{$ubicacion}'";

//*************************************************************************************************

    $usuarios = "";
    if($usuario)
    {
      $sql = "SELECT DISTINCT cve_usuario, nombre_completo FROM c_usuario WHERE cve_usuario = '$usuario'";
        if (!($res = mysqli_query($conn, $sql))){
            echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";
        }
        $rowusuario = mysqli_fetch_array($res);
        $usuarios = "(".$rowusuario['cve_usuario'].") - ".$rowusuario['nombre_completo'];
    
    }
    else 
    {
      $sql = "";
      if($tipo == 'Físico')
      $sql = "SELECT DISTINCT c.cve_usuario, c.nombre_completo FROM c_usuario c WHERE c.cve_usuario IN (
              SELECT cve_usuario FROM t_invpiezas WHERE ID_Inventario = {$id_inventario} AND NConteo = {$conteo}
              UNION
              SELECT cve_usuario FROM t_invtarima WHERE ID_Inventario = {$id_inventario} AND NConteo = {$conteo}
              )
              ";
        else
            $sql = "SELECT DISTINCT c.cve_usuario, c.nombre_completo FROM c_usuario c WHERE c.cve_usuario IN (
              SELECT cve_usuario FROM t_invpiezasciclico WHERE ID_PLAN = {$id_inventario} AND NConteo = {$conteo}
              UNION
              SELECT cve_usuario FROM t_invtarimaciclico WHERE ID_PLAN = {$id_inventario} AND NConteo = {$conteo}
              )
              ";
        if (!($res = mysqli_query($conn, $sql))){
            echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";
        }

        while ($rowusuario = mysqli_fetch_array($res)) {
            $usuarios .= "(".$rowusuario['cve_usuario'].") - ".$rowusuario['nombre_completo']." ";
        }
    }
//*************************************************************************************************

    $sql = "";

    if($tipo == 'Físico')
    {
          $sql = "SELECT DISTINCT 
            inv.idy_ubica,
            IFNULL(c_articulo.cve_articulo, '') AS clave,
            IFNULL(c_articulo.des_articulo, '') AS descripcion,
            IF(inv.cve_lote = '', '', inv.cve_lote) AS lote,
            #IF(c_articulo.Caduca = 'S', IFNULL(c_lotes.CADUCIDAD, '--'), '') AS caducidad,
            '' AS caducidad,
            IFNULL(DATE_FORMAT(inv.fecha, '%d-%m-%Y'), '') AS fecha,
            IFNULL((SELECT SUM(ext.ExistenciaTeorica) FROM t_invpiezas ext WHERE ext.ID_Inventario = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote), 0) AS stockTeorico,
            #IFNULL((SELECT SUM(Existencia) FROM V_ExistenciaGral WHERE tipo = 'ubicacion' AND cve_ubicacion = inv.idy_ubica AND cve_lote = inv.cve_lote AND cve_articulo = inv.cve_articulo), 0) AS stockTeorico,
            #IF(IF(COUNT(DISTINCT inv.NConteo) = (SELECT COUNT(DISTINCT Cantidad) FROM t_invpiezas iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica ), 0, 1) = 1, (SELECT SUM(Cantidad) FROM t_invpiezas iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica AND iv.NConteo = MAX(inv.NConteo)), 0) AS Inventariado,
            inv.Cantidad AS Inventariado,
            (SELECT u.CodigoCSD AS ubicacion FROM c_ubicacion u WHERE u.idy_ubica = inv.idy_ubica) AS ubicacion,
            inv.NConteo AS conteo, 
            '' AS LP,
            p.Nombre as Nombre_Empresa,
            CONCAT('(',u.cve_usuario, ') - ', u.nombre_completo) AS usuario
            FROM t_invpiezas inv
                LEFT JOIN c_articulo ON c_articulo.cve_articulo = inv.cve_articulo
                LEFT JOIN c_almacen AS c ON c.cve_almac=(SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = inv.idy_ubica) 
                LEFT JOIN c_lotes ON c_lotes.LOTE = inv.cve_lote
                LEFT JOIN c_usuario u ON inv.cve_usuario = u.cve_usuario
                LEFT JOIN c_ubicacion ub ON ub.idy_ubica = inv.idy_ubica
                LEFT JOIN c_proveedores p ON p.ID_Proveedor = inv.ID_Proveedor
            WHERE (inv.ID_Inventario = {$id_inventario} {$sql_ubicacion2} {$sql_usuario} AND inv.cve_articulo = c_articulo.cve_articulo AND inv.cve_lote = c_lotes.Lote AND inv.NConteo = {$conteo} AND CONCAT(inv.cve_articulo,inv.cve_lote) NOT IN (SELECT CONVERT(CONCAT(cve_articulo,cve_lote), CHAR) FROM t_invtarima WHERE ID_Inventario = {$id_inventario} AND NConteo = {$conteo})) {$sql_rack}
            AND inv.Cantidad >= 0 
            GROUP BY LP,clave,ubicacion,lote
  UNION

        SELECT DISTINCT 
            inv.idy_ubica,
            IFNULL(c_articulo.cve_articulo, '') AS clave,
            IFNULL(c_articulo.des_articulo, '') AS descripcion,
            IF(inv.cve_lote = '', '', inv.cve_lote) AS lote,
            #IF(c_articulo.Caduca = 'S', IFNULL(c_lotes.CADUCIDAD, '--'), '') AS caducidad,
            '' AS caducidad,
            IFNULL(DATE_FORMAT(inv.fecha, '%d-%m-%Y'), '') AS fecha,
            #IFNULL((SELECT SUM(ext.ExistenciaTeorica) FROM t_invpiezas ext WHERE ext.ID_Inventario = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote), 0) AS stockTeorico,
            inv.ExistenciaTeorica AS stockTeorico,
            #IFNULL((SELECT SUM(Existencia) FROM V_ExistenciaGral WHERE tipo = 'ubicacion' AND cve_ubicacion = inv.idy_ubica AND cve_lote = inv.cve_lote AND cve_articulo = inv.cve_articulo), 0) AS stockTeorico,
            #IF(IF(COUNT(DISTINCT inv.NConteo) = (SELECT COUNT(DISTINCT Cantidad) FROM t_invpiezas iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica ), 0, 1) = 1, (SELECT SUM(Cantidad) FROM t_invpiezas iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica AND iv.NConteo = MAX(inv.NConteo)), 0) AS Inventariado,
            inv.Cantidad AS Inventariado,
            #(SELECT u.CodigoCSD AS ubicacion FROM c_ubicacion u WHERE u.idy_ubica = inv.idy_ubica) AS ubicacion,
            ub.CodigoCSD AS ubicacion,
            inv.NConteo AS conteo, 
            '' AS LP,
            p.Nombre as Nombre_Empresa,
            CONCAT('(',u.cve_usuario, ') - ', u.nombre_completo) AS usuario
            FROM t_invpiezas inv
                LEFT JOIN c_articulo ON c_articulo.cve_articulo = inv.cve_articulo
                LEFT JOIN c_almacen AS c ON c.cve_almac=(SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = inv.idy_ubica) 
                LEFT JOIN c_lotes ON c_lotes.LOTE = inv.cve_lote
                LEFT JOIN c_usuario u ON inv.cve_usuario = u.cve_usuario
                LEFT JOIN c_ubicacion ub ON ub.idy_ubica = inv.idy_ubica
                LEFT JOIN c_proveedores p ON p.ID_Proveedor = inv.ID_Proveedor
            WHERE (inv.ID_Inventario = {$id_inventario} {$sql_ubicacion2} {$sql_usuario} 
            AND inv.NConteo = 0
            AND (CONCAT(inv.idy_ubica, inv.cve_articulo, inv.cve_lote) NOT IN (SELECT CONCAT(idy_ubica, cve_articulo, cve_lote) FROM t_invpiezas WHERE ID_Inventario = {$id_inventario} AND NConteo > 0))
            ) {$sql_rack}
            AND inv.Cantidad >= 0 
            GROUP BY LP,clave,idy_ubica,lote

        UNION

        SELECT DISTINCT 
            inv.idy_ubica,
            IFNULL(c_articulo.cve_articulo, '') AS clave,
            IFNULL(c_articulo.des_articulo, '') AS descripcion,
            IF(inv.cve_lote = '', '', inv.cve_lote) AS lote,
            #IF(c_articulo.Caduca = 'S', IFNULL(c_lotes.CADUCIDAD, '--'), '') AS caducidad,
            '' AS caducidad,
            IFNULL(DATE_FORMAT(inv.fecha, '%d-%m-%Y'), '') AS fecha,
            #(SELECT ext.Teorico FROM t_invtarima ext WHERE ext.ID_Inventario = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote) AS stockTeorico,
            IFNULL((SELECT SUM(ext.Teorico) AS steorico FROM t_invtarima ext WHERE ext.ID_Inventario = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote AND ext.ntarima = ch.IDContenedor), 0) AS stockTeorico,
            #IFNULL((SELECT SUM(Existencia) FROM V_ExistenciaGral WHERE tipo = 'ubicacion' AND cve_ubicacion = inv.idy_ubica AND cve_lote = inv.cve_lote AND cve_articulo = inv.cve_articulo AND Cve_Contenedor = ch.CveLP), 0) AS stockTeorico,
            #IF(IF(COUNT(DISTINCT inv.NConteo) = (SELECT COUNT(DISTINCT existencia) FROM t_invtarima iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica), 0, 1) = 1, (SELECT SUM(existencia) FROM t_invtarima iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica AND iv.ntarima = inv.ntarima AND iv.Cve_Lote = inv.Cve_Lote AND iv.NConteo = MAX(inv.NConteo)), 0) AS Inventariado,
            inv.existencia AS Inventariado,
            (SELECT u.CodigoCSD AS ubicacion FROM c_ubicacion u WHERE u.idy_ubica = inv.idy_ubica) AS ubicacion,
            inv.NConteo AS conteo, 
            IFNULL(ch.CveLP, '') AS LP,
            p.Nombre as Nombre_Empresa,
            CONCAT('(',u.cve_usuario, ') - ', u.nombre_completo) AS usuario
            FROM t_invtarima inv
                LEFT JOIN c_articulo ON c_articulo.cve_articulo = inv.cve_articulo
                LEFT JOIN c_almacen AS c ON c.cve_almac=(SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = inv.idy_ubica) 
                LEFT JOIN c_lotes ON c_lotes.LOTE = inv.cve_lote
                LEFT JOIN c_usuario u ON inv.cve_usuario = u.cve_usuario
                LEFT JOIN c_ubicacion ub ON ub.idy_ubica = inv.idy_ubica
                LEFT JOIN c_charolas ch ON ch.IDContenedor = inv.ntarima
                LEFT JOIN c_proveedores p ON p.ID_Proveedor = inv.ID_Proveedor
            WHERE (inv.ID_Inventario = {$id_inventario} {$sql_ubicacion2} {$sql_usuario} AND inv.cve_articulo = c_articulo.cve_articulo AND inv.cve_lote = c_lotes.Lote AND inv.NConteo = {$conteo} ) {$sql_rack}
            AND inv.existencia >= 0 
            GROUP BY LP,clave,ubicacion,lote

UNION

        SELECT DISTINCT 
            inv.idy_ubica,
            IFNULL(c_articulo.cve_articulo, '') AS clave,
            IFNULL(c_articulo.des_articulo, '') AS descripcion,
            IF(inv.cve_lote = '', '', inv.cve_lote) AS lote,
            #IF(c_articulo.Caduca = 'S', IFNULL(c_lotes.CADUCIDAD, '--'), '') AS caducidad,
            '' AS caducidad,
            IFNULL(DATE_FORMAT(inv.fecha, '%d-%m-%Y'), '') AS fecha,
            #(SELECT ext.Teorico FROM t_invtarima ext WHERE ext.ID_Inventario = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote) AS stockTeorico,
            IFNULL((SELECT SUM(ext.Teorico) AS steorico FROM t_invtarima ext WHERE ext.ID_Inventario = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote AND ext.ntarima = ch.IDContenedor), 0) AS stockTeorico,
            #IFNULL((SELECT SUM(Existencia) FROM V_ExistenciaGral WHERE tipo = 'ubicacion' AND cve_ubicacion = inv.idy_ubica AND cve_lote = inv.cve_lote AND cve_articulo = inv.cve_articulo AND Cve_Contenedor = ch.CveLP), 0) AS stockTeorico,
            #IF(IF(COUNT(DISTINCT inv.NConteo) = (SELECT COUNT(DISTINCT existencia) FROM t_invtarima iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica), 0, 1) = 1, (SELECT SUM(existencia) FROM t_invtarima iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica AND iv.ntarima = inv.ntarima AND iv.Cve_Lote = inv.Cve_Lote AND iv.NConteo = MAX(inv.NConteo)), 0) AS Inventariado,
            inv.existencia AS Inventariado,
            (SELECT u.CodigoCSD AS ubicacion FROM c_ubicacion u WHERE u.idy_ubica = inv.idy_ubica) AS ubicacion,
            inv.NConteo AS conteo, 
            IFNULL(ch.CveLP, '') AS LP,
            p.Nombre as Nombre_Empresa,
            CONCAT('(',u.cve_usuario, ') - ', u.nombre_completo) AS usuario
            FROM t_invtarima inv
                LEFT JOIN c_articulo ON c_articulo.cve_articulo = inv.cve_articulo
                LEFT JOIN c_almacen AS c ON c.cve_almac=(SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = inv.idy_ubica) 
                LEFT JOIN c_lotes ON c_lotes.LOTE = inv.cve_lote
                LEFT JOIN c_usuario u ON inv.cve_usuario = u.cve_usuario
                LEFT JOIN c_ubicacion ub ON ub.idy_ubica = inv.idy_ubica
                LEFT JOIN c_charolas ch ON ch.IDContenedor = inv.ntarima
                LEFT JOIN c_proveedores p ON p.ID_Proveedor = inv.ID_Proveedor
            WHERE (inv.ID_Inventario = {$id_inventario} {$sql_ubicacion2} {$sql_usuario} 
            AND inv.NConteo = 0
            AND (CONCAT(inv.idy_ubica, inv.cve_articulo, inv.cve_lote, inv.ntarima) NOT IN (SELECT CONCAT(idy_ubica, cve_articulo, cve_lote, ntarima) FROM t_invtarima WHERE ID_Inventario = {$id_inventario} AND NConteo > 0))
            #AND inv.cve_articulo = c_articulo.cve_articulo AND inv.cve_lote = c_lotes.Lote AND inv.NConteo = {$conteo} 
            ) 
            {$sql_rack}
            AND inv.existencia >= 0 
            GROUP BY LP,clave,ubicacion,lote
            ORDER BY descripcion
";
    }
    else
    {
      $sql = "SELECT DISTINCT 
            inv.idy_ubica,
            IFNULL(c_articulo.cve_articulo, '') AS clave,
            IFNULL(c_articulo.des_articulo, '') AS descripcion,
            IF(inv.cve_lote = '', '', inv.cve_lote) AS lote,
            #IF(c_articulo.Caduca = 'S', IFNULL(c_lotes.CADUCIDAD, '--'), '') AS caducidad,
            '' AS caducidad,
            IFNULL(DATE_FORMAT(inv.fecha, '%d-%m-%Y'), '') AS fecha,
            IFNULL((SELECT SUM(ext.ExistenciaTeorica) FROM t_invpiezasciclico ext WHERE ext.ID_PLAN = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote), 0) AS stockTeorico,
            #IFNULL((SELECT SUM(Existencia) FROM V_ExistenciaGral WHERE tipo = 'ubicacion' AND cve_ubicacion = inv.idy_ubica AND cve_lote = inv.cve_lote AND cve_articulo = inv.cve_articulo), 0) AS stockTeorico,
            #IF(IF(COUNT(DISTINCT inv.NConteo) = (SELECT COUNT(DISTINCT Cantidad) FROM t_invpiezas iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica ), 0, 1) = 1, (SELECT SUM(Cantidad) FROM t_invpiezas iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica AND iv.NConteo = MAX(inv.NConteo)), 0) AS Inventariado,
            inv.Cantidad AS Inventariado,
            (SELECT u.CodigoCSD AS ubicacion FROM c_ubicacion u WHERE u.idy_ubica = inv.idy_ubica) AS ubicacion,
            inv.NConteo AS conteo, 
            '' AS LP,
            p.Nombre as Nombre_Empresa,
            CONCAT('(',u.cve_usuario, ') - ', u.nombre_completo) AS usuario
            FROM t_invpiezasciclico inv
                LEFT JOIN c_articulo ON c_articulo.cve_articulo = inv.cve_articulo
                LEFT JOIN c_almacen AS c ON c.cve_almac=(SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = inv.idy_ubica) 
                LEFT JOIN c_lotes ON c_lotes.LOTE = inv.cve_lote
                LEFT JOIN c_usuario u ON inv.cve_usuario = u.cve_usuario
                LEFT JOIN c_ubicacion ub ON ub.idy_ubica = inv.idy_ubica
                LEFT JOIN c_proveedores p ON p.ID_Proveedor = inv.ID_Proveedor
            WHERE (inv.ID_PLAN = {$id_inventario} {$sql_ubicacion2} {$sql_usuario} AND inv.cve_articulo = c_articulo.cve_articulo AND inv.cve_lote = c_lotes.Lote AND inv.NConteo = {$conteo} AND CONCAT(inv.cve_articulo,inv.cve_lote) NOT IN (SELECT CONVERT(CONCAT(cve_articulo,cve_lote), CHAR) FROM t_invtarimaciclico WHERE ID_PLAN = {$id_inventario} AND NConteo = {$conteo})) {$sql_rack}
            AND inv.Cantidad >= 0 
            GROUP BY LP,clave,ubicacion,lote

  UNION

        SELECT DISTINCT 
            inv.idy_ubica,
            IFNULL(c_articulo.cve_articulo, '') AS clave,
            IFNULL(c_articulo.des_articulo, '') AS descripcion,
            IF(inv.cve_lote = '', '', inv.cve_lote) AS lote,
            '' AS caducidad,
            IFNULL(DATE_FORMAT(inv.fecha, '%d-%m-%Y'), '') AS fecha,
            IFNULL((SELECT SUM(ext.ExistenciaTeorica) FROM t_invpiezasciclico ext WHERE ext.ID_PLAN = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote), 0) AS stockTeorico,
            inv.Cantidad AS Inventariado,
            (SELECT u.CodigoCSD AS ubicacion FROM c_ubicacion u WHERE u.idy_ubica = inv.idy_ubica) AS ubicacion,
            inv.NConteo AS conteo, 
            '' AS LP,
            p.Nombre as Nombre_Empresa,
            CONCAT('(',u.cve_usuario, ') - ', u.nombre_completo) AS usuario
            FROM t_invpiezasciclico inv
                LEFT JOIN c_articulo ON c_articulo.cve_articulo = inv.cve_articulo
                LEFT JOIN c_almacen AS c ON c.cve_almac=(SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = inv.idy_ubica) 
                LEFT JOIN c_lotes ON c_lotes.LOTE = inv.cve_lote
                LEFT JOIN c_usuario u ON inv.cve_usuario = u.cve_usuario
                LEFT JOIN c_ubicacion ub ON ub.idy_ubica = inv.idy_ubica
                LEFT JOIN c_proveedores p ON p.ID_Proveedor = inv.ID_Proveedor
            WHERE (inv.ID_PLAN = {$id_inventario} {$sql_ubicacion2} {$sql_usuario} 
            AND inv.NConteo = 0
            AND (CONCAT(inv.idy_ubica, inv.cve_articulo, inv.cve_lote) NOT IN (SELECT CONCAT(idy_ubica, cve_articulo, cve_lote) FROM t_invpiezasciclico WHERE ID_PLAN = {$id_inventario} AND NConteo > 0))
            ) {$sql_rack}
            AND inv.Cantidad >= 0 
            GROUP BY LP,clave,idy_ubica,lote

        UNION

        SELECT DISTINCT 
            inv.idy_ubica,
            IFNULL(c_articulo.cve_articulo, '') AS clave,
            IFNULL(c_articulo.des_articulo, '') AS descripcion,
            IF(inv.cve_lote = '', '', inv.cve_lote) AS lote,
            '' AS caducidad,
            IFNULL(DATE_FORMAT(inv.fecha, '%d-%m-%Y'), '') AS fecha,
            IFNULL((SELECT SUM(ext.Teorico) AS steorico FROM t_invtarimaciclico ext WHERE ext.ID_PLAN = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote AND ext.ntarima = ch.IDContenedor), 0) AS stockTeorico,
            inv.existencia AS Inventariado,
            (SELECT u.CodigoCSD AS ubicacion FROM c_ubicacion u WHERE u.idy_ubica = inv.idy_ubica) AS ubicacion,
            inv.NConteo AS conteo, 
            IFNULL(ch.CveLP, '') AS LP,
            p.Nombre as Nombre_Empresa,
            CONCAT('(',u.cve_usuario, ') - ', u.nombre_completo) AS usuario
            FROM t_invtarimaciclico inv
                LEFT JOIN c_articulo ON c_articulo.cve_articulo = inv.cve_articulo
                LEFT JOIN c_almacen AS c ON c.cve_almac=(SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = inv.idy_ubica) 
                LEFT JOIN c_lotes ON c_lotes.LOTE = inv.cve_lote
                LEFT JOIN c_usuario u ON inv.cve_usuario = u.cve_usuario
                LEFT JOIN c_ubicacion ub ON ub.idy_ubica = inv.idy_ubica
                LEFT JOIN c_charolas ch ON ch.IDContenedor = inv.ntarima
                LEFT JOIN c_proveedores p ON p.ID_Proveedor = inv.ID_Proveedor
            WHERE (inv.ID_PLAN = {$id_inventario} {$sql_ubicacion2} {$sql_usuario} AND inv.cve_articulo = c_articulo.cve_articulo AND inv.cve_lote = c_lotes.Lote AND inv.NConteo = {$conteo} ) {$sql_rack}
            AND inv.existencia >= 0 
            GROUP BY LP,clave,ubicacion,lote

UNION

        SELECT DISTINCT 
            inv.idy_ubica,
            IFNULL(c_articulo.cve_articulo, '') AS clave,
            IFNULL(c_articulo.des_articulo, '') AS descripcion,
            IF(inv.cve_lote = '', '', inv.cve_lote) AS lote,
            '' AS caducidad,
            IFNULL(DATE_FORMAT(inv.fecha, '%d-%m-%Y'), '') AS fecha,
            IFNULL((SELECT SUM(ext.Teorico) AS steorico FROM t_invtarimaciclico ext WHERE ext.ID_PLAN = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote AND ext.ntarima = ch.IDContenedor), 0) AS stockTeorico,
            inv.existencia AS Inventariado,
            (SELECT u.CodigoCSD AS ubicacion FROM c_ubicacion u WHERE u.idy_ubica = inv.idy_ubica) AS ubicacion,
            inv.NConteo AS conteo, 
            IFNULL(ch.CveLP, '') AS LP,
            p.Nombre as Nombre_Empresa,
            CONCAT('(',u.cve_usuario, ') - ', u.nombre_completo) AS usuario
            FROM t_invtarimaciclico inv
                LEFT JOIN c_articulo ON c_articulo.cve_articulo = inv.cve_articulo
                LEFT JOIN c_almacen AS c ON c.cve_almac=(SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = inv.idy_ubica) 
                LEFT JOIN c_lotes ON c_lotes.LOTE = inv.cve_lote
                LEFT JOIN c_usuario u ON inv.cve_usuario = u.cve_usuario
                LEFT JOIN c_ubicacion ub ON ub.idy_ubica = inv.idy_ubica
                LEFT JOIN c_charolas ch ON ch.IDContenedor = inv.ntarima
                LEFT JOIN c_proveedores p ON p.ID_Proveedor = inv.ID_Proveedor
            WHERE (inv.ID_PLAN = {$id_inventario} {$sql_ubicacion2} {$sql_usuario} 
            AND inv.NConteo = 0
            AND (CONCAT(inv.idy_ubica, inv.cve_articulo, inv.cve_lote, inv.ntarima) NOT IN (SELECT CONCAT(idy_ubica, cve_articulo, cve_lote, ntarima) FROM t_invtarimaciclico WHERE ID_PLAN = {$id_inventario} AND NConteo > 0))
            ) 
            {$sql_rack}
            AND inv.existencia >= 0 
            GROUP BY LP,clave,ubicacion,lote
            ORDER BY descripcion
";
    }

    $res = "";
    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";
    }

        $columnas = [
            'LP', 
            'Ubicacion', 
            'Articulo', 
            'Descripcion', 
            'Lote', 
            'Teorico', 
            'Inventariado', 
            'Diferencia', 
            'Usuario'
        ];

        $filename = "Inventario por conteos" . ".xls";

        ob_clean();

        header("Content-Disposition: attachment; filename=\"$filename\"");
        //header('Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Type: application/vnd.ms-excel");

        foreach($columnas as $column) 
        {            
            echo $column . "\t" ;            
        }
        print("\r\n");

        while($row = mysqli_fetch_object($res))
        {
            //extract($row);
            echo $row->LP . "\t";
            echo str_replace("-", "_", $row->ubicacion) . "\t";
            echo utf8_decode($row->clave) . "\t";
            echo utf8_decode($row->descripcion) . "\t";
            echo utf8_decode($row->lote) . "\t";
            echo utf8_decode($row->stockTeorico) . "\t";
            echo utf8_decode($row->Inventariado) . "\t";
            echo ($row->Inventariado-$row->stockTeorico) . "\t";
            if($row->usuario) echo $row->usuario; else echo $row->usuarios;
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

    public function inventario_consolidado()
    {

//***************************************************************************************************
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

/*
    $sql_charset = "SELECT DISTINCT IFNULL(Valor, 'utf8') as charset FROM t_configuraciongeneral WHERE cve_conf = 'charset2'";
    if (!($res_charset = mysqli_query($conn, $sql_charset)))echo "Falló la preparación Charset: (" . mysqli_error($conn) . ") ";
    $charset = mysqli_fetch_array($res_charset)['charset'];
    mysqli_set_charset($conn , $charset);
*/
    $utf8Sql = "SET NAMES 'utf8mb4';";
    $res_charset = mysqli_query($conn, $utf8Sql);

      $id_inventario = $_GET['id'];
      $status        = "";//$_GET['status'];
      $cia           = "";//$_GET['comp'];
      $fecha         = "";//$_GET['fecha_inv'];
      $rack          = $_GET['rack'];
      $tipo          = $_GET['tipo'];
      $diferencia    = $_GET['diferencia_inv'];

      $sql_rack = "";
      if($rack)
      $sql_rack = "AND ub.cve_rack = '{$rack}'";

    $sql = "";
    if($tipo == 'Físico')
    {
$sql_inicial = "";
/*
    $sql = "SELECT IFNULL(Inv_Inicial, 0) as Inv_Inicial FROM th_inventario WHERE ID_Inventario = {$id_inventario}";
    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";
    }

    $tipo_inicial = 1;
    $rowinv = mysqli_fetch_array($res);
    $tipo_inicial = $rowinv['Inv_Inicial'];

    $sql_inicial = "";
    if($tipo_inicial == 0)
      $sql_inicial = " WHERE RIGHT(tinv.Nconteo, 1) != '1' AND tinv.TeoricoPiezas != 0 ";
*/
      $sql = "
SELECT CONVERT(tinv.zona, CHAR) AS zona, CONVERT(tinv.cve_ubicacion, CHAR) AS cve_ubicacion, tinv.ubicacion, CONVERT(tinv.clave, CHAR) AS clave, CONVERT(tinv.descripcion USING utf8) AS descripcion, CONVERT(tinv.lote, CHAR) AS lote, tinv.caducidad, 
  tinv.conteo, 
  GROUP_CONCAT(DISTINCT tinv.NConteo_Cantidad_reg SEPARATOR ',') AS NConteo_Cantidad_reg, 
  GROUP_CONCAT(DISTINCT tinv.Nconteo SEPARATOR ',') AS Nconteo, 
  tinv.stockTeorico, 
  GROUP_CONCAT(DISTINCT tinv.Cantidad_reg SEPARATOR ',') AS Cantidad_reg, 
  tinv.Cantidad, #tinv.Cerrar, 
  CONVERT(tinv.LP, CHAR) AS LP, CONVERT(tinv.Nombre_Empresa, CHAR) AS Nombre_Empresa, CONVERT(tinv.usuario, CHAR) AS usuario, CONVERT(tinv.unidad_medida, CHAR) AS unidad_medida, tinv.Max_Conteo AS Max_Conteo, tinv.Status 
FROM (

        SELECT DISTINCT 
                c.des_almac COLLATE utf8mb4_general_ci as zona,
                v.cve_ubicacion COLLATE utf8mb4_general_ci as cve_ubicacion, 
                (CASE 
                    WHEN CONVERT(v.tipo, CHAR) = CONVERT('area', CHAR) AND v.cve_ubicacion IS NOT NULL THEN 
                        (SELECT desc_ubicacion AS ubicacion FROM tubicacionesretencion WHERE CONVERT(cve_ubicacion, CHAR) = CONVERT(v.cve_ubicacion, CHAR)) 
                    WHEN CONVERT(v.tipo, CHAR) = CONVERT('ubicacion', CHAR) AND v.cve_ubicacion IS NOT NULL THEN 
                        (SELECT u.CodigoCSD AS ubicacion FROM c_ubicacion u WHERE CONVERT(u.idy_ubica, CHAR) = CONVERT(v.cve_ubicacion, CHAR))
                    ELSE '--'
                END) COLLATE utf8mb4_general_ci AS ubicacion,
                IFNULL(c_articulo.cve_articulo, '--') COLLATE utf8mb4_general_ci AS clave,
                IFNULL(c_articulo.des_articulo, '--') COLLATE utf8mb4_general_ci AS descripcion,
                IF(CONVERT(v.cve_lote, CHAR) = CONVERT('', CHAR), '', CONVERT(v.cve_lote, CHAR)) COLLATE utf8mb4_general_ci AS lote,
                IF(CONVERT(c_articulo.Caduca, CHAR) = CONVERT('S', CHAR), IFNULL(c_lotes.CADUCIDAD, '--'), '') COLLATE utf8mb4_general_ci AS caducidad,
                IFNULL(inv.NConteo, 0) COLLATE utf8mb4_general_ci AS conteo, 
                GROUP_CONCAT(DISTINCT CONCAT(inv.NConteo,'-',inv.Cantidad, '-', inv.cve_usuario) ORDER BY inv.NConteo SEPARATOR ',') COLLATE utf8mb4_general_ci AS NConteo_Cantidad_reg,
                GROUP_CONCAT(DISTINCT inv.NConteo SEPARATOR ',') COLLATE utf8mb4_general_ci AS Nconteo, 
                #IFNULL((SELECT SUM(ext.ExistenciaTeorica) FROM t_invpiezas ext WHERE ext.ID_Inventario = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = v.cve_articulo AND ext.idy_ubica = v.cve_ubicacion AND ext.cve_lote = v.cve_lote AND v.tipo = 'ubicacion'), 0) AS stockTeorico,
                IFNULL((SELECT SUM(Existencia) FROM V_ExistenciaGralProduccion WHERE CONVERT(tipo, CHAR) = CONVERT('ubicacion', CHAR) AND CONVERT(cve_ubicacion, CHAR) = CONVERT(v.cve_ubicacion, CHAR) AND CONVERT(cve_lote, CHAR) = CONVERT(v.cve_lote, CHAR) AND CONVERT(cve_articulo, CHAR) = CONVERT(v.cve_articulo, CHAR)), 0) COLLATE utf8mb4_general_ci AS stockTeorico,
                GROUP_CONCAT(DISTINCT inv.Cantidad ORDER BY inv.NConteo SEPARATOR ',') COLLATE utf8mb4_general_ci AS Cantidad_reg,
                (SELECT DISTINCT SUM(Cantidad) FROM t_invpiezas iv WHERE CONVERT(iv.ID_Inventario, CHAR) = {$id_inventario} AND CONVERT(iv.cve_articulo, CHAR) = CONVERT(v.cve_articulo, CHAR) AND CONVERT(iv.idy_ubica, CHAR) = CONVERT(v.cve_ubicacion, CHAR)  AND CONVERT(iv.NConteo, CHAR) = MAX(inv.NConteo) AND CONVERT(iv.cve_lote, CHAR) = CONVERT(v.cve_lote, CHAR)) COLLATE utf8mb4_general_ci AS Cantidad,
                #IF(COUNT(DISTINCT inv.NConteo) = (SELECT COUNT(DISTINCT Cantidad) FROM t_invpiezas iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = v.cve_articulo AND iv.idy_ubica = v.cve_ubicacion AND iv.cve_lote = v.cve_lote AND iv.NConteo > 0 ), 0, 1) AS Cerrar,
                '' AS LP,
                p.Nombre COLLATE utf8mb4_general_ci as Nombre_Empresa,
                inv.cve_usuario COLLATE utf8mb4_general_ci AS usuario,
                'Piezas' COLLATE utf8mb4_general_ci AS unidad_medida,
                MAX(cinv.NConteo) COLLATE utf8mb4_general_ci AS Max_Conteo,
                inv.ExistenciaTeorica COLLATE utf8mb4_general_ci AS TeoricoPiezas,
                (SELECT Status FROM th_inventario WHERE CONVERT(ID_inventario, CHAR) = {$id_inventario} LIMIT 1) COLLATE utf8mb4_general_ci AS Status
            FROM V_ExistenciaGralProduccion v
                LEFT JOIN c_articulo ON CONVERT(c_articulo.cve_articulo, CHAR) = CONVERT(v.cve_articulo, CHAR)
                #LEFT JOIN c_almacen AS c ON c.cve_almac=(SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = v.cve_ubicacion) 
                #LEFT JOIN c_lotes ON c_lotes.LOTE = v.cve_lote
                LEFT JOIN c_lotes ON CONVERT(c_lotes.LOTE, CHAR) = CONVERT(v.cve_lote, CHAR) AND CONVERT(c_lotes.cve_articulo, CHAR) = CONVERT(v.cve_articulo, CHAR)
                LEFT JOIN t_invpiezas inv ON CONVERT(inv.ID_Inventario, CHAR) = {$id_inventario} AND CONVERT(inv.cve_articulo, CHAR) = CONVERT(v.cve_articulo, CHAR) AND CONVERT(inv.idy_ubica, CHAR) = CONVERT(v.cve_ubicacion, CHAR) AND CONVERT(inv.cve_lote, CHAR) = CONVERT(v.cve_lote, CHAR) 
                LEFT JOIN t_conteoinventario cinv ON CONVERT(cinv.ID_Inventario, CHAR) = CONVERT(inv.ID_Inventario, CHAR) AND CONVERT(cinv.ID_Inventario, CHAR) = {$id_inventario}
                LEFT JOIN c_ubicacion ub ON CONVERT(ub.idy_ubica, CHAR) = CONVERT(inv.idy_ubica, CHAR)
                LEFT JOIN c_almacen AS c ON CONVERT(c.cve_almac, CHAR)=CONVERT(ub.cve_almac, CHAR)
                LEFT JOIN rel_articulo_proveedor rap ON CONVERT(rap.Cve_Articulo, CHAR) = CONVERT(inv.cve_articulo, CHAR)
                LEFT JOIN c_proveedores p ON CONVERT(p.ID_Proveedor, CHAR) = IFNULL(inv.ID_Proveedor, rap.Id_Proveedor)
            WHERE v.Existencia > 0 AND 
            CONVERT(v.tipo, CHAR)=CONVERT('ubicacion', CHAR) AND CONVERT(inv.idy_ubica, CHAR) = CONVERT(v.cve_ubicacion, CHAR) AND CONVERT(inv.cve_articulo, CHAR) = CONVERT(v.cve_articulo, CHAR) AND CONVERT(inv.cve_lote, CHAR) = CONVERT(v.cve_lote, CHAR) 
            #AND inv.NConteo > 0 
            {$sql_rack} 
            AND inv.Cantidad >= 0
            AND CONCAT(CONVERT(v.cve_articulo, CHAR),CONVERT(v.cve_lote, CHAR)) NOT IN (SELECT CONVERT(CONCAT(cve_articulo,cve_lote), CHAR) FROM t_invtarima WHERE CONVERT(ID_Inventario, CHAR) = {$id_inventario})
            GROUP BY LP,clave,cve_ubicacion,lote

        UNION

        SELECT DISTINCT 
                c.des_almac zona,
                inv.idy_ubica AS cve_ubicacion, 
                #(SELECT u.CodigoCSD AS ubicacion FROM c_ubicacion u WHERE u.idy_ubica = inv.idy_ubica) AS ubicacion,
                ub.CodigoCSD AS ubicacion,
                IFNULL(CONVERT(c_articulo.cve_articulo, CHAR), '--') AS clave,
                IFNULL(CONVERT(c_articulo.des_articulo, CHAR), '--') AS descripcion,
                IF(CONVERT(inv.cve_lote, CHAR) = CONVERT('', CHAR), '', CONVERT(inv.cve_lote, CHAR)) AS lote,
                IF(CONVERT(c_articulo.Caduca, CHAR) = CONVERT('S', CHAR), IFNULL(c_lotes.CADUCIDAD, '--'), '') AS caducidad,
                IFNULL(inv.NConteo, 0) AS conteo, 
                GROUP_CONCAT(DISTINCT CONCAT(inv.NConteo,'-',inv.Cantidad, '-', inv.cve_usuario) ORDER BY inv.NConteo SEPARATOR ',') AS NConteo_Cantidad_reg,
                GROUP_CONCAT(DISTINCT inv.NConteo SEPARATOR ',') AS Nconteo, 

                #IFNULL((SELECT SUM(ext.ExistenciaTeorica) FROM t_invpiezas ext WHERE ext.ID_Inventario = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote), 0) AS stockTeorico,
                inv.ExistenciaTeorica AS stockTeorico,

                ##### ESTO ES MOMENTANEO MIENTRAS SE CIERRAN LOS INVENTARIOS
                #IFNULL((SELECT SUM(ext.Teorico) AS steorico FROM t_invtarima ext WHERE ext.ID_Inventario = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote AND ext.ntarima = ch.IDContenedor), (SELECT SUM(ext.ExistenciaTeorica) FROM t_invpiezas ext WHERE ext.ID_Inventario = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote)) AS stockTeorico,
                #######################
                #(SELECT SUM(ext.ExistenciaTeorica) FROM t_invpiezas ext WHERE ext.ID_Inventario = 168 AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote) AS stockTeorico,
                #IFNULL((SELECT SUM(Existencia) FROM V_ExistenciaGral WHERE tipo = 'ubicacion' AND cve_ubicacion = inv.idy_ubica AND cve_lote = inv.cve_lote AND cve_articulo = inv.cve_articulo AND Cve_Contenedor = inv.ClaveEtiqueta), 0) AS stockTeorico,
                GROUP_CONCAT(DISTINCT inv.Cantidad ORDER BY inv.NConteo SEPARATOR ',') AS Cantidad_reg,
                #(SELECT DISTINCT SUM(Cantidad) FROM t_invpiezas iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica AND iv.NConteo = MAX(inv.NConteo)) AS Cantidad,
                inv.Cantidad,
                #IF(COUNT(DISTINCT inv.NConteo) = (SELECT COUNT(DISTINCT Cantidad) FROM t_invpiezas iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica AND iv.cve_lote = inv.cve_lote ), 0, 1) AS Cerrar,
                '' AS LP,
                p.Nombre as Nombre_Empresa,
                ##### ESTO ES MOMENTANEO MIENTRAS SE CIERRAN LOS INVENTARIOS
                #IFNULL(ch.CveLP, '') AS LP,
                inv.cve_usuario AS usuario,
                'Piezas' AS unidad_medida,
                MAX(cinv.NConteo) AS Max_Conteo,
                inv.ExistenciaTeorica AS TeoricoPiezas,
                (SELECT Status FROM th_inventario WHERE CONVERT(ID_inventario, CHAR) = {$id_inventario} LIMIT 1) AS Status
            FROM t_invpiezas inv
                LEFT JOIN c_articulo ON CONVERT(c_articulo.cve_articulo, CHAR) = CONVERT(inv.cve_articulo, CHAR)
                LEFT JOIN t_conteoinventario cinv ON CONVERT(cinv.ID_Inventario, CHAR) = CONVERT(inv.ID_Inventario, CHAR) AND CONVERT(cinv.ID_Inventario, CHAR) = {$id_inventario}
                #LEFT JOIN c_almacen AS c ON c.cve_almac=(SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = inv.idy_ubica) 
                #LEFT JOIN c_lotes ON c_lotes.LOTE = inv.cve_lote
                LEFT JOIN c_lotes ON CONVERT(c_lotes.LOTE, CHAR) = CONVERT(inv.cve_lote, CHAR) AND CONVERT(c_lotes.cve_articulo, CHAR) = CONVERT(inv.cve_articulo, CHAR)
                LEFT JOIN c_usuario u ON CONVERT(inv.cve_usuario, CHAR) = CONVERT(u.cve_usuario, CHAR)
                LEFT JOIN c_ubicacion ub ON CONVERT(ub.idy_ubica, CHAR) = CONVERT(inv.idy_ubica, CHAR)
                LEFT JOIN c_almacen AS c ON CONVERT(c.cve_almac, CHAR)=CONVERT(ub.cve_almac, CHAR)
                LEFT JOIN rel_articulo_proveedor rap ON CONVERT(rap.Cve_Articulo, CHAR) = CONVERT(inv.cve_articulo, CHAR)
                LEFT JOIN c_proveedores p ON CONVERT(p.ID_Proveedor, CHAR) = IFNULL(inv.ID_Proveedor, rap.Id_Proveedor)
                ##### ESTO ES MOMENTANEO MIENTRAS SE CIERRAN LOS INVENTARIOS
                #LEFT JOIN t_invtarima invt ON invt.ID_Inventario = inv.ID_Inventario AND invt.cve_articulo = c_articulo.cve_articulo AND invt.idy_ubica = ub.idy_ubica
                #LEFT JOIN c_charolas ch ON ch.IDContenedor = invt.ntarima
                #####
            WHERE (CONVERT(inv.ID_Inventario, CHAR) = {$id_inventario} AND CONVERT(inv.cve_articulo, CHAR) = CONVERT(c_articulo.cve_articulo, CHAR) 
            #AND CONCAT(inv.cve_articulo,inv.cve_lote) NOT IN (SELECT CONCAT(cve_articulo,cve_lote) FROM t_invpiezas WHERE ID_Inventario = {$id_inventario}             AND NConteo = 0)
            ) 
            #AND inv.NConteo > 0 
            {$sql_rack}
            AND CONVERT(inv.Cantidad, CHAR) >= 0
            GROUP BY LP,clave,cve_ubicacion,lote

UNION

        SELECT DISTINCT 
                c.des_almac zona,
                inv.idy_ubica AS cve_ubicacion, 
                #(SELECT u.CodigoCSD AS ubicacion FROM c_ubicacion u WHERE u.idy_ubica = inv.idy_ubica) AS ubicacion,
                ub.CodigoCSD AS ubicacion,
                IFNULL(CONVERT(c_articulo.cve_articulo, CHAR), '--') AS clave,
                IFNULL(CONVERT(c_articulo.des_articulo, CHAR), '--') AS descripcion,
                IF(CONVERT(inv.cve_lote, CHAR) = CONVERT('', CHAR), '', CONVERT(inv.cve_lote, CHAR)) AS lote,
                IF(CONVERT(c_articulo.Caduca, CHAR) = CONVERT('S', CHAR), IFNULL(c_lotes.CADUCIDAD, '--'), '') AS caducidad,
                IFNULL(inv.NConteo, 0) AS conteo, 
                GROUP_CONCAT(DISTINCT CONCAT(inv.NConteo,'-',inv.existencia, '-', inv.cve_usuario) ORDER BY inv.NConteo SEPARATOR ',') AS NConteo_Cantidad_reg,
                GROUP_CONCAT(DISTINCT inv.NConteo SEPARATOR ',') AS Nconteo, 
                #(SELECT ext.Teorico FROM t_invtarima ext WHERE ext.ID_Inventario = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote) AS stockTeorico,
                #IF(IFNULL(inv.existencia, 0) = 0, 0, IFNULL((SELECT SUM(ext.Teorico) AS steorico FROM t_invtarima ext WHERE CONVERT(ext.ID_Inventario, CHAR) = {$id_inventario} AND CONVERT(ext.NConteo, CHAR) = 0 AND CONVERT(ext.cve_articulo, CHAR) = CONVERT(inv.cve_articulo, CHAR) AND CONVERT(ext.idy_ubica, CHAR) = CONVERT(inv.idy_ubica, CHAR) AND CONVERT(ext.cve_lote, CHAR) = CONVERT(inv.cve_lote, CHAR) AND CONVERT(ext.ntarima, CHAR) = CONVERT(ch.IDContenedor, CHAR)), 0)) AS stockTeorico,
                inv.Teorico AS stockTeorico,
                #IFNULL((SELECT SUM(Existencia) FROM V_ExistenciaGral WHERE tipo = 'ubicacion' AND cve_ubicacion = inv.idy_ubica AND cve_lote = inv.cve_lote AND cve_articulo = inv.cve_articulo AND Cve_Contenedor = ch.clave_contenedor), 0) AS stockTeorico,
                GROUP_CONCAT(DISTINCT inv.existencia ORDER BY inv.NConteo SEPARATOR ',') AS Cantidad_reg,
                IF(IFNULL(inv.existencia, 0) = 0, 0, (SELECT DISTINCT SUM(existencia) FROM t_invtarima iv WHERE CONVERT(iv.ID_Inventario, CHAR) = {$id_inventario} AND CONVERT(iv.cve_articulo, CHAR) = CONVERT(inv.cve_articulo, CHAR) AND CONVERT(iv.idy_ubica, CHAR) = CONVERT(inv.idy_ubica, CHAR) AND CONVERT(iv.ntarima, CHAR) = CONVERT(inv.ntarima, CHAR) AND CONVERT(iv.Cve_Lote, CHAR) = CONVERT(inv.Cve_Lote, CHAR) AND CONVERT(iv.NConteo, CHAR) = MAX(inv.NConteo))) AS Cantidad,
                #IF(COUNT(DISTINCT inv.NConteo) = (SELECT COUNT(DISTINCT existencia) FROM t_invtarima iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica AND iv.cve_lote = inv.cve_lote ), 0, 1) AS Cerrar,
                IFNULL(ch.CveLP, '') AS LP,
                p.Nombre as Nombre_Empresa,
                inv.cve_usuario AS usuario,
                'Piezas' AS unidad_medida,
                MAX(cinv.NConteo) AS Max_Conteo,
                '1' AS TeoricoPiezas,
                (SELECT STATUS FROM th_inventario WHERE CONVERT(ID_inventario, CHAR) = {$id_inventario} LIMIT 1) AS STATUS
            FROM t_invtarima inv
                LEFT JOIN c_articulo ON CONVERT(c_articulo.cve_articulo, CHAR) = CONVERT(inv.cve_articulo, CHAR)
                LEFT JOIN t_conteoinventario cinv ON CONVERT(cinv.ID_Inventario, CHAR) = CONVERT(inv.ID_Inventario, CHAR) AND CONVERT(cinv.ID_Inventario, CHAR) = {$id_inventario}
                #LEFT JOIN c_almacen AS c ON c.cve_almac=(SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = inv.idy_ubica) 
                #LEFT JOIN c_lotes ON c_lotes.LOTE = inv.cve_lote
                LEFT JOIN c_lotes ON CONVERT(c_lotes.LOTE, CHAR) = CONVERT(inv.cve_lote, CHAR) AND CONVERT(c_lotes.cve_articulo, CHAR) = CONVERT(inv.cve_articulo, CHAR)
                LEFT JOIN c_usuario u ON CONVERT(inv.cve_usuario, CHAR) = CONVERT(u.cve_usuario, CHAR)
                LEFT JOIN c_ubicacion ub ON CONVERT(ub.idy_ubica, CHAR) = CONVERT(inv.idy_ubica, CHAR)
                LEFT JOIN c_almacen AS c ON CONVERT(c.cve_almac, CHAR)=CONVERT(ub.cve_almac, CHAR)
                LEFT JOIN c_charolas ch ON CONVERT(ch.IDContenedor, CHAR) = CONVERT(inv.ntarima, CHAR)
                LEFT JOIN rel_articulo_proveedor rap ON CONVERT(rap.Cve_Articulo, CHAR) = CONVERT(inv.cve_articulo, CHAR)
                LEFT JOIN c_proveedores p ON p.ID_Proveedor = IFNULL(inv.ID_Proveedor, rap.Id_Proveedor)
                LEFT JOIN t_invtarima inv_pr ON CONVERT(inv_pr.ID_Inventario, CHAR) = CONVERT(inv.ID_Inventario, CHAR)  AND CONVERT(inv_pr.NConteo, CHAR) = 0 AND CONVERT(inv_pr.cve_articulo, CHAR) = CONVERT(inv.cve_articulo, CHAR) AND CONVERT(inv_pr.Cve_Lote, CHAR) = CONVERT(inv.Cve_Lote, CHAR) AND CONVERT(inv_pr.ntarima, CHAR) = CONVERT(inv.ntarima, CHAR)  AND CONVERT(inv_pr.ID_Inventario, CHAR) = {$id_inventario}
            WHERE (CONVERT(inv.ID_Inventario, CHAR) = {$id_inventario} AND CONVERT(inv.cve_articulo, CHAR) = CONVERT(c_articulo.cve_articulo, CHAR))
            #AND inv.NConteo > 0 
            {$sql_rack}
            AND CONVERT(inv.existencia, CHAR) >= 0
            GROUP BY LP,clave,cve_ubicacion,lote

  UNION 

        SELECT DISTINCT 
                '' AS zona,
                inv.idy_ubica AS cve_ubicacion, 
                (SELECT u.CodigoCSD AS ubicacion FROM c_ubicacion u WHERE CONVERT(u.idy_ubica, CHAR) = CONVERT(inv.idy_ubica, CHAR)) AS ubicacion,
                '' AS clave,
                '' AS descripcion,
                IF(CONVERT(inv.cve_lote, CHAR) = CONVERT('', CHAR), '', CONVERT(inv.cve_lote, CHAR)) AS lote,
                '' AS caducidad,
                IFNULL(inv.NConteo, 0) AS conteo, 
                GROUP_CONCAT(DISTINCT CONCAT(inv.NConteo,'-',inv.Cantidad, '-', inv.cve_usuario) ORDER BY inv.NConteo SEPARATOR ',') AS NConteo_Cantidad_reg,
                GROUP_CONCAT(DISTINCT inv.NConteo SEPARATOR ',') AS Nconteo, 
                #IFNULL((SELECT SUM(ext.ExistenciaTeorica) FROM t_invpiezas ext WHERE ext.ID_Inventario = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote), 0) AS stockTeorico,
                inv.ExistenciaTeorica as stockTeorico,
                ##### ESTO ES MOMENTANEO MIENTRAS SE CIERRAN LOS INVENTARIOS
                #IFNULL((SELECT SUM(ext.Teorico) AS steorico FROM t_invtarima ext WHERE ext.ID_Inventario = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote AND ext.ntarima = ch.IDContenedor), (SELECT SUM(ext.ExistenciaTeorica) FROM t_invpiezas ext WHERE ext.ID_Inventario = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote)) AS stockTeorico,
                #######################
                #(SELECT SUM(ext.ExistenciaTeorica) FROM t_invpiezas ext WHERE ext.ID_Inventario = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote) AS stockTeorico,
                #IFNULL((SELECT SUM(Existencia) FROM V_ExistenciaGral WHERE tipo = 'ubicacion' AND cve_ubicacion = inv.idy_ubica AND cve_lote = inv.cve_lote AND cve_articulo = inv.cve_articulo AND Cve_Contenedor = inv.ClaveEtiqueta), 0) AS stockTeorico,
                GROUP_CONCAT(DISTINCT inv.Cantidad ORDER BY inv.NConteo SEPARATOR ',') AS Cantidad_reg,
                #(SELECT DISTINCT SUM(Cantidad) FROM t_invpiezas iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica AND iv.NConteo = MAX(inv.NConteo)) AS Cantidad,
                inv.Cantidad,
                #IF(COUNT(DISTINCT inv.NConteo) = (SELECT COUNT(DISTINCT Cantidad) FROM t_invpiezas iv WHERE iv.ID_Inventario = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica AND iv.cve_lote = inv.cve_lote ), 0, 1) AS Cerrar,
                '' AS LP,
                p.Nombre as Nombre_Empresa,
                ##### ESTO ES MOMENTANEO MIENTRAS SE CIERRAN LOS INVENTARIOS
                #IFNULL(ch.CveLP, '') AS LP,
                inv.cve_usuario AS usuario,
                'Piezas' AS unidad_medida,
                MAX(cinv.NConteo) AS Max_Conteo,
                inv.ExistenciaTeorica AS TeoricoPiezas,
                (SELECT Status FROM th_inventario WHERE CONVERT(ID_inventario, CHAR) = {$id_inventario} LIMIT 1) AS Status
            FROM t_invpiezas inv
            LEFT JOIN t_conteoinventario cinv ON CONVERT(cinv.ID_Inventario, CHAR) = CONVERT(inv.ID_Inventario, CHAR) AND CONVERT(cinv.ID_Inventario, CHAR) = {$id_inventario}
            LEFT JOIN rel_articulo_proveedor rap ON CONVERT(rap.Cve_Articulo, CHAR) = CONVERT(inv.cve_articulo, CHAR)
            LEFT JOIN c_proveedores p ON CONVERT(p.ID_Proveedor, CHAR) = IFNULL(inv.ID_Proveedor, rap.Id_Proveedor)
            WHERE (CONVERT(inv.ID_Inventario, CHAR) = {$id_inventario} AND CONVERT(inv.cve_articulo, CHAR) = CONVERT('', CHAR)
            #AND CONCAT(inv.cve_articulo,inv.cve_lote) NOT IN (SELECT CONCAT(cve_articulo,cve_lote) FROM t_invpiezas WHERE ID_Inventario = {$id_inventario}             AND NConteo = 0)
            ) 
            #AND inv.NConteo > 0 
            #{$sql_rack}
            AND CONVERT(inv.Cantidad, CHAR) >= 0
            GROUP BY LP,clave,ubicacion,lote

            ORDER BY ubicacion
            ) AS tinv
            {$sql_inicial}
            GROUP BY clave, cve_ubicacion, lote, LP
            ORDER BY descripcion
            ";
    }
    else
        $sql = "SELECT  tinv.zona, tinv.cve_ubicacion, tinv.ubicacion, tinv.clave, CONVERT(tinv.descripcion USING utf8) as descripcion, tinv.lote, tinv.caducidad, 
  tinv.conteo, 
  GROUP_CONCAT(DISTINCT tinv.NConteo_Cantidad_reg SEPARATOR ',') AS NConteo_Cantidad_reg, 
  GROUP_CONCAT(DISTINCT tinv.Nconteo SEPARATOR ',') AS Nconteo, 
  tinv.stockTeorico, 
  GROUP_CONCAT(DISTINCT tinv.Cantidad_reg SEPARATOR ',') AS Cantidad_reg, 
  tinv.Cantidad, #tinv.Cerrar, 
  tinv.LP, tinv.Nombre_Empresa, tinv.usuario, tinv.unidad_medida, tinv.Max_Conteo AS Max_Conteo, tinv.Status 
FROM (

        SELECT DISTINCT 
                c.des_almac zona,
                v.cve_ubicacion, 
                (CASE 
                    WHEN v.tipo = 'area' AND v.cve_ubicacion IS NOT NULL THEN 
                        (SELECT desc_ubicacion AS ubicacion FROM tubicacionesretencion WHERE cve_ubicacion = v.cve_ubicacion) 
                    WHEN v.tipo = 'ubicacion' AND v.cve_ubicacion IS NOT NULL THEN 
                        (SELECT u.CodigoCSD AS ubicacion FROM c_ubicacion u WHERE u.idy_ubica = v.cve_ubicacion)
                    ELSE '--'
                END) AS ubicacion,
                IFNULL(c_articulo.cve_articulo, '--') AS clave,
                IFNULL(c_articulo.des_articulo, '--') AS descripcion,
                IF(v.cve_lote = '', '', v.cve_lote) AS lote,
                IF(c_articulo.Caduca = 'S', IFNULL(c_lotes.CADUCIDAD, '--'), '') AS caducidad,
                IFNULL(inv.NConteo, 0) AS conteo, 
                GROUP_CONCAT(DISTINCT CONCAT(inv.NConteo,'-',inv.Cantidad, '-', inv.cve_usuario) ORDER BY inv.NConteo SEPARATOR ',') AS NConteo_Cantidad_reg,
                GROUP_CONCAT(DISTINCT inv.NConteo SEPARATOR ',') AS Nconteo, 
                #IFNULL((SELECT SUM(ext.ExistenciaTeorica) FROM t_invpiezas ext WHERE ext.ID_PLAN = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = v.cve_articulo AND ext.idy_ubica = v.cve_ubicacion AND ext.cve_lote = v.cve_lote AND v.tipo = 'ubicacion'), 0) AS stockTeorico,
                IFNULL((SELECT SUM(Existencia) FROM V_ExistenciaGralProduccion WHERE tipo = 'ubicacion' AND cve_ubicacion = v.cve_ubicacion AND cve_lote = v.cve_lote AND cve_articulo = v.cve_articulo), 0) AS stockTeorico,
                GROUP_CONCAT(DISTINCT inv.Cantidad ORDER BY inv.NConteo SEPARATOR ',') AS Cantidad_reg,
                (SELECT DISTINCT SUM(Cantidad) FROM t_invpiezasciclico iv WHERE iv.ID_PLAN = {$id_inventario} AND iv.cve_articulo = v.cve_articulo AND iv.idy_ubica = v.cve_ubicacion  AND iv.NConteo = MAX(inv.NConteo) AND iv.cve_lote = v.cve_lote) AS Cantidad,
                #IF(COUNT(DISTINCT inv.NConteo) = (SELECT COUNT(DISTINCT Cantidad) FROM t_invpiezasciclico iv WHERE iv.ID_PLAN = {$id_inventario} AND iv.cve_articulo = v.cve_articulo AND iv.idy_ubica = v.cve_ubicacion AND iv.cve_lote = v.cve_lote AND iv.NConteo > 0 ), 0, 1) AS Cerrar,
                '' AS LP,
                p.Nombre as Nombre_Empresa,
                inv.cve_usuario AS usuario,
                'Piezas' AS unidad_medida,
                MAX(cinv.NConteo) AS Max_Conteo,
                (SELECT Status FROM det_planifica_inventario WHERE ID_PLAN = {$id_inventario} LIMIT 1) AS Status
            FROM V_ExistenciaGralProduccion v
                LEFT JOIN c_articulo ON c_articulo.cve_articulo = v.cve_articulo
                #LEFT JOIN c_almacen AS c ON c.cve_almac=(SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = v.cve_ubicacion) 
                #LEFT JOIN c_lotes ON c_lotes.LOTE = v.cve_lote
                LEFT JOIN c_lotes ON c_lotes.LOTE = v.cve_lote AND c_lotes.cve_articulo = v.cve_articulo
                LEFT JOIN t_invpiezasciclico inv ON inv.ID_PLAN = {$id_inventario} AND inv.cve_articulo = v.cve_articulo AND inv.idy_ubica = v.cve_ubicacion AND inv.cve_lote = v.cve_lote 
                LEFT JOIN t_conteoinventariocicl cinv ON cinv.ID_PLAN = inv.ID_PLAN AND cinv.ID_PLAN = {$id_inventario}
                LEFT JOIN c_ubicacion ub ON ub.idy_ubica = inv.idy_ubica
                LEFT JOIN c_almacen AS c ON c.cve_almac=ub.cve_almac
                LEFT JOIN rel_articulo_proveedor rap ON rap.Cve_Articulo = inv.cve_articulo
                LEFT JOIN c_proveedores p ON p.ID_Proveedor = IFNULL(inv.ID_Proveedor, rap.Id_Proveedor)
            WHERE v.Existencia > 0 AND 
            v.tipo='ubicacion' AND inv.idy_ubica = v.cve_ubicacion AND inv.cve_articulo = v.cve_articulo AND inv.cve_lote = v.cve_lote 
            #AND inv.NConteo > 0 
            {$sql_rack} 
            AND inv.Cantidad >= 0
            AND CONCAT(v.cve_articulo,v.cve_lote) NOT IN (SELECT CONVERT(CONCAT(cve_articulo,cve_lote), CHAR) FROM t_invtarimaciclico WHERE ID_PLAN = {$id_inventario})
            GROUP BY LP,clave,cve_ubicacion,lote

        UNION

        SELECT DISTINCT 
                c.des_almac zona,
                inv.idy_ubica AS cve_ubicacion, 
                #(SELECT u.CodigoCSD AS ubicacion FROM c_ubicacion u WHERE u.idy_ubica = inv.idy_ubica) AS ubicacion,
                ub.CodigoCSD AS ubicacion,
                IFNULL(c_articulo.cve_articulo, '--') AS clave,
                IFNULL(c_articulo.des_articulo, '--') AS descripcion,
                IF(inv.cve_lote = '', '', inv.cve_lote) AS lote,
                IF(c_articulo.Caduca = 'S', IFNULL(c_lotes.CADUCIDAD, '--'), '') AS caducidad,
                IFNULL(inv.NConteo, 0) AS conteo, 
                GROUP_CONCAT(DISTINCT CONCAT(inv.NConteo,'-',inv.Cantidad, '-', inv.cve_usuario) ORDER BY inv.NConteo SEPARATOR ',') AS NConteo_Cantidad_reg,
                GROUP_CONCAT(DISTINCT inv.NConteo SEPARATOR ',') AS Nconteo, 
                IFNULL((SELECT SUM(ext.ExistenciaTeorica) FROM t_invpiezasciclico ext WHERE ext.ID_PLAN = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote), 0) AS stockTeorico,
                ##### ESTO ES MOMENTANEO MIENTRAS SE CIERRAN LOS INVENTARIOS
                #IFNULL((SELECT SUM(ext.Teorico) AS steorico FROM t_invtarimaciclico ext WHERE ext.ID_PLAN = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote AND ext.ntarima = ch.IDContenedor), (SELECT SUM(ext.ExistenciaTeorica) FROM t_invpiezasciclico ext WHERE ext.ID_PLAN = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote)) AS stockTeorico,
                #######################
                #(SELECT SUM(ext.ExistenciaTeorica) FROM t_invpiezasciclico ext WHERE ext.ID_PLAN = 168 AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote) AS stockTeorico,
                #IFNULL((SELECT SUM(Existencia) FROM V_ExistenciaGral WHERE tipo = 'ubicacion' AND cve_ubicacion = inv.idy_ubica AND cve_lote = inv.cve_lote AND cve_articulo = inv.cve_articulo AND Cve_Contenedor = inv.ClaveEtiqueta), 0) AS stockTeorico,
                GROUP_CONCAT(DISTINCT inv.Cantidad ORDER BY inv.NConteo SEPARATOR ',') AS Cantidad_reg,
                (SELECT DISTINCT SUM(Cantidad) FROM t_invpiezasciclico iv WHERE iv.ID_PLAN = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica AND iv.NConteo = MAX(inv.NConteo)) AS Cantidad,
                #IF(COUNT(DISTINCT inv.NConteo) = (SELECT COUNT(DISTINCT Cantidad) FROM t_invpiezasciclico iv WHERE iv.ID_PLAN = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica AND iv.cve_lote = inv.cve_lote ), 0, 1) AS Cerrar,
                '' AS LP,
                p.Nombre as Nombre_Empresa,
                ##### ESTO ES MOMENTANEO MIENTRAS SE CIERRAN LOS INVENTARIOS
                #IFNULL(ch.CveLP, '') AS LP,
                inv.cve_usuario AS usuario,
                'Piezas' AS unidad_medida,
                MAX(cinv.NConteo) AS Max_Conteo,
                (SELECT Status FROM det_planifica_inventario WHERE ID_PLAN = {$id_inventario} LIMIT 1) AS Status
            FROM t_invpiezasciclico inv
                LEFT JOIN c_articulo ON c_articulo.cve_articulo = inv.cve_articulo
                LEFT JOIN t_conteoinventariocicl cinv ON cinv.ID_PLAN = inv.ID_PLAN AND cinv.ID_PLAN = {$id_inventario}
                #LEFT JOIN c_almacen AS c ON c.cve_almac=(SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = inv.idy_ubica) 
                #LEFT JOIN c_lotes ON c_lotes.LOTE = inv.cve_lote
                LEFT JOIN c_lotes ON c_lotes.LOTE = inv.cve_lote AND c_lotes.cve_articulo = inv.cve_articulo
                LEFT JOIN c_usuario u ON inv.cve_usuario = u.cve_usuario
                LEFT JOIN c_ubicacion ub ON ub.idy_ubica = inv.idy_ubica
                LEFT JOIN c_almacen AS c ON c.cve_almac=ub.cve_almac
                LEFT JOIN rel_articulo_proveedor rap ON rap.Cve_Articulo = inv.cve_articulo
                LEFT JOIN c_proveedores p ON p.ID_Proveedor = IFNULL(inv.ID_Proveedor, rap.Id_Proveedor)
                ##### ESTO ES MOMENTANEO MIENTRAS SE CIERRAN LOS INVENTARIOS
                #LEFT JOIN t_invtarimaciclico invt ON invt.ID_Inventario = inv.ID_Inventario AND invt.cve_articulo = c_articulo.cve_articulo AND invt.idy_ubica = ub.idy_ubica
                #LEFT JOIN c_charolas ch ON ch.IDContenedor = invt.ntarima
                #####
            WHERE (inv.ID_PLAN = {$id_inventario} AND inv.cve_articulo = c_articulo.cve_articulo 
            #AND CONCAT(inv.cve_articulo,inv.cve_lote) NOT IN (SELECT CONCAT(cve_articulo,cve_lote) FROM t_invpiezasciclico WHERE ID_PLAN = {$id_inventario}             AND NConteo = 0)
            ) 
            #AND inv.NConteo > 0 
            AND inv.Cantidad >= 0
            {$sql_rack}
            GROUP BY LP,clave,cve_ubicacion,lote

        UNION

        SELECT DISTINCT 
                c.des_almac zona,
                inv.idy_ubica AS cve_ubicacion, 
                #(SELECT u.CodigoCSD AS ubicacion FROM c_ubicacion u WHERE u.idy_ubica = inv.idy_ubica) AS ubicacion,
                ub.CodigoCSD AS ubicacion,
                IFNULL(c_articulo.cve_articulo, '--') AS clave,
                IFNULL(c_articulo.des_articulo, '--') AS descripcion,
                IF(inv.cve_lote = '', '', inv.cve_lote) AS lote,
                IF(c_articulo.Caduca = 'S', IFNULL(c_lotes.CADUCIDAD, '--'), '') AS caducidad,
                IFNULL(inv.NConteo, 0) AS conteo, 
                GROUP_CONCAT(DISTINCT CONCAT(inv.NConteo,'-',inv.existencia, '-', inv.cve_usuario) ORDER BY inv.NConteo SEPARATOR ',') AS NConteo_Cantidad_reg,
                GROUP_CONCAT(DISTINCT inv.NConteo SEPARATOR ',') AS Nconteo, 
                #(SELECT ext.Teorico FROM t_invtarimaciclico ext WHERE ext.ID_PLAN = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote) AS stockTeorico,
                IFNULL((SELECT SUM(ext.Teorico) AS steorico FROM t_invtarimaciclico ext WHERE ext.ID_PLAN = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote AND ext.ntarima = ch.IDContenedor), 0) AS stockTeorico,
                #IFNULL((SELECT SUM(Existencia) FROM V_ExistenciaGral WHERE tipo = 'ubicacion' AND cve_ubicacion = inv.idy_ubica AND cve_lote = inv.cve_lote AND cve_articulo = inv.cve_articulo AND Cve_Contenedor = ch.clave_contenedor), 0) AS stockTeorico,
                GROUP_CONCAT(DISTINCT inv.existencia ORDER BY inv.NConteo SEPARATOR ',') AS Cantidad_reg,
                (SELECT DISTINCT SUM(existencia) FROM t_invtarimaciclico iv WHERE iv.ID_PLAN = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica AND iv.ntarima = inv.ntarima AND iv.Cve_Lote = inv.Cve_Lote AND iv.NConteo = MAX(inv.NConteo)) AS Cantidad,
                #IF(COUNT(DISTINCT inv.NConteo) = (SELECT COUNT(DISTINCT existencia) FROM t_invtarimaciclico iv WHERE iv.ID_PLAN = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica AND iv.cve_lote = inv.cve_lote ), 0, 1) AS Cerrar,
                IFNULL(ch.CveLP, '') AS LP,
                p.Nombre as Nombre_Empresa,
                inv.cve_usuario AS usuario,
                'Piezas' AS unidad_medida,
                MAX(cinv.NConteo) AS Max_Conteo,
                (SELECT Status FROM det_planifica_inventario WHERE ID_PLAN = {$id_inventario} LIMIT 1) AS Status
            FROM t_invtarimaciclico inv
                LEFT JOIN c_articulo ON c_articulo.cve_articulo = inv.cve_articulo
                LEFT JOIN t_conteoinventariocicl cinv ON cinv.ID_PLAN = inv.ID_PLAN AND cinv.ID_PLAN = {$id_inventario}
                #LEFT JOIN c_almacen AS c ON c.cve_almac=(SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = inv.idy_ubica) 
                #LEFT JOIN c_lotes ON c_lotes.LOTE = inv.cve_lote
                LEFT JOIN c_lotes ON c_lotes.LOTE = inv.cve_lote AND c_lotes.cve_articulo = inv.cve_articulo
                LEFT JOIN c_usuario u ON inv.cve_usuario = u.cve_usuario
                LEFT JOIN c_ubicacion ub ON ub.idy_ubica = inv.idy_ubica
                LEFT JOIN c_almacen AS c ON c.cve_almac=ub.cve_almac
                LEFT JOIN c_charolas ch ON ch.IDContenedor = inv.ntarima
                LEFT JOIN rel_articulo_proveedor rap ON rap.Cve_Articulo = inv.cve_articulo
                LEFT JOIN c_proveedores p ON p.ID_Proveedor = IFNULL(inv.ID_Proveedor, rap.Id_Proveedor)
                LEFT JOIN t_invtarimaciclico inv_pr ON inv_pr.ID_PLAN = inv.ID_PLAN  AND inv_pr.NConteo = 0 AND inv_pr.cve_articulo = inv.cve_articulo AND inv_pr.Cve_Lote = inv.Cve_Lote AND inv_pr.ntarima = inv.ntarima  AND inv_pr.ID_PLAN = {$id_inventario}
            WHERE (inv.ID_PLAN = {$id_inventario} AND inv.cve_articulo = c_articulo.cve_articulo)
            #AND inv.NConteo > 0 
            {$sql_rack}
            AND inv.existencia >= 0
            GROUP BY LP,clave,cve_ubicacion,lote

  UNION 

        SELECT DISTINCT 
                '' AS zona,
                inv.idy_ubica AS cve_ubicacion, 
                (SELECT u.CodigoCSD AS ubicacion FROM c_ubicacion u WHERE u.idy_ubica = inv.idy_ubica) AS ubicacion,
                '' AS clave,
                '' AS descripcion,
                IF(inv.cve_lote = '', '', inv.cve_lote) AS lote,
                '' AS caducidad,
                IFNULL(inv.NConteo, 0) AS conteo, 
                GROUP_CONCAT(DISTINCT CONCAT(inv.NConteo,'-',inv.Cantidad, '-', inv.cve_usuario) ORDER BY inv.NConteo SEPARATOR ',') AS NConteo_Cantidad_reg,
                GROUP_CONCAT(DISTINCT inv.NConteo SEPARATOR ',') AS Nconteo, 
                IFNULL((SELECT SUM(ext.ExistenciaTeorica) FROM t_invpiezasciclico ext WHERE ext.ID_PLAN = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote), 0) AS stockTeorico,
                ##### ESTO ES MOMENTANEO MIENTRAS SE CIERRAN LOS INVENTARIOS
                #IFNULL((SELECT SUM(ext.Teorico) AS steorico FROM t_invtarimaciclico ext WHERE ext.ID_PLAN = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote AND ext.ntarima = ch.IDContenedor), (SELECT SUM(ext.ExistenciaTeorica) FROM t_invpiezasciclico ext WHERE ext.ID_PLAN = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote)) AS stockTeorico,
                #######################
                #(SELECT SUM(ext.ExistenciaTeorica) FROM t_invpiezasciclico ext WHERE ext.ID_PLAN = {$id_inventario} AND ext.NConteo = 0 AND ext.cve_articulo = inv.cve_articulo AND ext.idy_ubica = inv.idy_ubica AND ext.cve_lote = inv.cve_lote) AS stockTeorico,
                #IFNULL((SELECT SUM(Existencia) FROM V_ExistenciaGral WHERE tipo = 'ubicacion' AND cve_ubicacion = inv.idy_ubica AND cve_lote = inv.cve_lote AND cve_articulo = inv.cve_articulo AND Cve_Contenedor = inv.ClaveEtiqueta), 0) AS stockTeorico,
                GROUP_CONCAT(DISTINCT inv.Cantidad ORDER BY inv.NConteo SEPARATOR ',') AS Cantidad_reg,
                (SELECT DISTINCT SUM(Cantidad) FROM t_invpiezasciclico iv WHERE iv.ID_PLAN = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica AND iv.NConteo = MAX(inv.NConteo)) AS Cantidad,
                #IF(COUNT(DISTINCT inv.NConteo) = (SELECT COUNT(DISTINCT Cantidad) FROM t_invpiezasciclico iv WHERE iv.ID_PLAN = {$id_inventario} AND iv.cve_articulo = inv.cve_articulo AND iv.idy_ubica = inv.idy_ubica AND iv.cve_lote = inv.cve_lote ), 0, 1) AS Cerrar,
                '' AS LP,
                p.Nombre as Nombre_Empresa,
                ##### ESTO ES MOMENTANEO MIENTRAS SE CIERRAN LOS INVENTARIOS
                #IFNULL(ch.CveLP, '') AS LP,
                inv.cve_usuario AS usuario,
                'Piezas' AS unidad_medida,
                MAX(cinv.NConteo) AS Max_Conteo,
                (SELECT Status FROM det_planifica_inventario WHERE ID_PLAN = {$id_inventario} LIMIT 1) AS Status
            FROM t_invpiezasciclico inv
            LEFT JOIN t_conteoinventariocicl cinv ON cinv.ID_PLAN = inv.ID_PLAN AND cinv.ID_PLAN = {$id_inventario}
            LEFT JOIN rel_articulo_proveedor rap ON rap.Cve_Articulo = inv.cve_articulo
            LEFT JOIN c_proveedores p ON p.ID_Proveedor = IFNULL(inv.ID_Proveedor, rap.Id_Proveedor)
            WHERE (inv.ID_PLAN = {$id_inventario} AND inv.cve_articulo = ''
            #AND CONCAT(inv.cve_articulo,inv.cve_lote) NOT IN (SELECT CONCAT(cve_articulo,cve_lote) FROM t_invpiezasciclico WHERE ID_PLAN = {$id_inventario}             AND NConteo = 0)
            ) 
            #AND inv.NConteo > 0 
            AND inv.Cantidad >= 0
            #{$sql_rack}
            GROUP BY LP,clave,ubicacion,lote

            ORDER BY ubicacion
            ) AS tinv
            GROUP BY clave, cve_ubicacion, lote, LP
            ORDER BY descripcion
            ";

    if (!($res = mysqli_query($conn, $sql))){
        echo "Falló la preparación(2): (" . mysqli_error($conn) . ") ";
    }

    $columnas = array();
    if($diferencia == 2 || $diferencia == 3)
    {
        $columnas = [
            'Ubicacion', 
            'Conteo 1', 
            'Conteo 2', 
            'Conteo 3', 
            'Conteo 4', 
            'Conteo 5', 
            'Valor Final', 
            'Ajuste'
        ];
    }
    else
    {
        $columnas = [
                'LP',
                'Articulo',
                'Descripcion',
                'Lote',
                'Ubicacion',
                'Teorico',
                'Conteo 1',
                'Usuario 1',
                'Conteo 2',
                'Usuario 2',
                'Conteo 3',
                'Usuario 3',
                'Conteo 4',
                'Usuario 4',
                'Conteo 5',
                'Usuario 5',
                'Valor Final',
                'Ajuste'
            ];
    }

        $filename = "Reporte Consolidado" . ".xls";

        ob_clean();

        header("Content-Disposition: attachment; filename=\"$filename\"");
        //header('Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Type: application/vnd.ms-excel");

        foreach($columnas as $column) 
        {            
            echo $column . "\t" ;            
        }
        print("\r\n");

        //echo $sql. "\t";
/*
        while($row = mysqli_fetch_object($res))
        {
            //extract($row);
            echo $row->LP . "\t";
            echo str_replace("-", "_", $row->ubicacion) . "\t";
            echo utf8_decode($row->clave) . "\t";
            echo utf8_decode($row->descripcion) . "\t";
            echo utf8_decode($row->lote) . "\t";
            echo utf8_decode($row->stockTeorico) . "\t";
            echo utf8_decode($row->Inventariado) . "\t";
            echo ($row->Inventariado-$row->stockTeorico) . "\t";
            if($row->usuario) echo $row->usuario; else echo $row->usuarios;
            echo  "\r\n";
        }
*/
///////////////////////////#########################################################################################
    while ($row = mysqli_fetch_object($res)) {

        //extract($row);
                      //if($diferencia == 1 && $Cerrar == 1) continue;

                      $cantidad_conteoN = explode(",", $row->Cantidad_reg);
                      $conteosN         = explode(",", $row->Nconteo);
                      $NConteo_Cantidad_reg = explode(",", $row->NConteo_Cantidad_reg);


                      $conteo = array("BB", "BB", "BB", "BB", "BB", "BB");//$conteo2 = 0;$conteo3 = 0;$conteo4 = 0;$conteo5 = 0;
                      $usuario = array();

                      $n_cantidades = count($cantidad_conteoN);
                      //$n_conteos    = count($conteosN);

                      $val_in_i = 1;
                      $n_conteos = $conteosN[count($conteosN)-1];
                      $n_conteos++;
                      //if($NConteo_Cantidad_reg[0] != '0-0') {$val_in_i = 1; $n_conteos++;}
                      if($NConteo_Cantidad_reg[0] == '0-0')
                      {
                      array_splice($NConteo_Cantidad_reg, 0, 1); 
                      array_splice($conteosN, 0, 1); 
                      }
                      for($j = 1; $j <= count($NConteo_Cantidad_reg); $j++)
                      {
                            $conteo_cantidad = explode("-", $NConteo_Cantidad_reg[$j-$val_in_i]);
                            $conteo[$conteo_cantidad[0]] = $conteo_cantidad[1];
                            $usuario[$conteo_cantidad[0]] = $conteo_cantidad[2];
                      }

                      $imprimir_diferencia = false;
                      if($diferencia == 1)
                      {
                            $valor = false;
                            $found = 0;
                            for($n = 1; $n < count($conteo); $n++)
                              if($conteo[$n] != "")
                                $valor = true;

                            if($valor)
                            {
                              $found = 0;

                              for($n = 1; $n < count($conteo); $n++)
                              {
                                  if($Cantidad == $conteo[$n] && $conteo[$n] != 0 && $Cantidad != 0)// || $row["stockTeorico"] == $conteo[$n]
                                     $found++;
                              }

                                if($found >= 2)
                                    $imprimir_diferencia = false;
                                else 
                                    $imprimir_diferencia = true;
                            }
                            else 
                            {
                                $imprimir_diferencia = true; 
                            }
                      }

                       //if($diferencia == 1 && ($row["Cantidad"]-$row["stockTeorico"]) == 0) continue;
                      if($diferencia == 1 && ($imprimir_diferencia == false || $row->clave == "")) continue;


                      //if($n_cantidades < $n_conteos)
                        // $conteo[$n_conteos-1] = $Cantidad;

            if($diferencia == 2 || $diferencia == 3)
            {
                if($diferencia == 3)
                {
                    $found = 0; 
                    $valor = false;
                    $ajuste_val = "";
                    for($n = 1; $n < count($conteo); $n++)
                      if($conteo[$n] != "")
                        $valor = true;

                    if($valor)
                    {
                      $found = 0;
                      for($n = 1; $n < count($conteo); $n++)
                      {
                          if($row->Cantidad == $conteo[$n])
                             $found++;
                      }
                        if($found >= 2)
                            $ajuste_val = $row->Cantidad;
                        else
                             $ajuste_val = "";
                    }

                    if($conteo[1] == 'BB' || $ajuste_val != "" || $found >= 2) continue;
                }?>
                <?php echo $row->ubicacion. "\t";?>
                <?php if($conteo[1]>=0) {if($conteo[1] == 'BB') echo ''. "\t"; else echo $conteo[1]. "\t";}?>
                <?php if($conteo[2]>=0) {if($conteo[2] == 'BB') echo ''. "\t"; else echo $conteo[2]. "\t";}?>
                <?php if($conteo[3]>=0) {if($conteo[3] == 'BB') echo ''. "\t"; else echo $conteo[3]. "\t";}?>
                <?php if($conteo[4]>=0) {if($conteo[4] == 'BB') echo ''. "\t"; else echo $conteo[4]. "\t";}?>
                <?php if($conteo[5]>=0) {if($conteo[5] == 'BB') echo ''. "\t"; else echo $conteo[5]. "\t";}?>
                        <?php
                            $found = 0; 
                            $valor = false;
                            for($n = 1; $n < count($conteo); $n++)
                              if($conteo[$n] != "")
                                $valor = true;

                            if($valor)
                            {
                              $found = 0;
                              for($n = 1; $n < count($conteo); $n++)
                              {
                                  if($row->Cantidad == $conteo[$n])
                                     $found++;
                              }
                                if($found >= 2)
                                    echo $row->Cantidad. "\t";
                                else
                                    echo "". "\t";
                            }
                        ?>
                        <?php if($found >= 2) echo ($row->Cantidad-$row->stockTeorico). "\t";
                        echo  "\r\n";?>
        <?php 
            }
            else
                
            {
                if($diferencia == 1 && ($conteo[1] == "BB" || ($row->Cantidad-$row->stockTeorico) == 0) || $row->ubicacion == '') continue;
                    ?>
                  <?php echo trim($row->LP). "\t";?>
                  <?php echo trim($row->clave). "\t";?>
                  <?php echo trim($row->descripcion). "\t";?>
                  <?php echo trim($row->lote). "\t";?>
                  <?php echo trim($row->ubicacion). "\t";?>
                  <?php echo trim($row->stockTeorico). "\t";?>
                  <?php if($conteo[1]>=0) {if($conteo[1] == 'BB') echo ''."\t"; else echo trim($conteo[1])."\t";}?>
                  <?php if($conteo[1]>=0) {if($conteo[1] == 'BB') echo ''."\t"; else echo trim($usuario[1])."\t";}?>
                  <?php if($conteo[2]>=0) {if($conteo[2] == 'BB') echo ''."\t"; else echo trim($conteo[2])."\t";}?>
                  <?php if($conteo[2]>=0) {if($conteo[2] == 'BB') echo ''."\t"; else echo trim($usuario[2])."\t";}?>
                  <?php if($conteo[3]>=0) {if($conteo[3] == 'BB') echo ''."\t"; else echo trim($conteo[3])."\t";}?>
                  <?php if($conteo[3]>=0) {if($conteo[3] == 'BB') echo ''."\t"; else echo trim($usuario[3])."\t";}?>
                  <?php if($conteo[4]>=0) {if($conteo[4] == 'BB') echo ''."\t"; else echo trim($conteo[4])."\t";}?>
                  <?php if($conteo[4]>=0) {if($conteo[4] == 'BB') echo ''."\t"; else echo trim($usuario[4])."\t";}?>
                  <?php if($conteo[5]>=0) {if($conteo[5] == 'BB') echo ''."\t"; else echo trim($conteo[5])."\t";}?>
                  <?php if($conteo[5]>=0) {if($conteo[5] == 'BB') echo ''."\t"; else echo trim($usuario[5])."\t";}?>
                    <?php
                            $found = 0; 
                            $valor = false;
                            for($n = 1; $n < count($conteo); $n++)
                              if($conteo[$n] != "")
                                $valor = true;

                            if($valor)
                            {
                              $found = 0;
                              for($n = 1; $n < count($conteo); $n++)
                              {
                                  if($row->Cantidad == $conteo[$n])
                                     $found++;
                              }
                                if($found >= 2)
                                    echo trim($row->Cantidad). "\t";
                                else
                                {
                                    if($diferencia == 1) $found = 20;
                                    echo "". "\t";
                                }
                            }
                        ?>
                    <?php if($found == 20){echo (0-trim($row->stockTeorico-$row->Cantidad));}else{if($found >= 2) echo (trim($row->Cantidad-$row->stockTeorico));}
                    echo  "\r\n";?>
        <?php 
            }

    }
                                   // echo $sql. "\t";

//////////////////////////*/#########################################################################################
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

const ALM_ORIGEN_TR      = 0;
const ALM_DESTINO_TR     = 1;
const BL_ORIGEN_TR       = 2;
const BL_DESTINO_TR      = 3;
const LP_TR_ORIG         = 4;
const LP_TR_DEST         = 5;
const CVE_ARTICULO_TR    = 6;
const CVE_LOTE_TR        = 7;
const CANTIDAD_TR        = 8;
const TRASLADAR_EN_CAJAS = 9;

function TrasladosMasivos()
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
        $ga = new \Ubicaciones\Ubicaciones();

        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);


        $linea = 1; $productos = 0; $cajas_sin_lP = array(); $msj_cajas = "";
        $lineas = $xlsx->rows();
        $lp_tipo_caja = array();

        $post = [];
        foreach ($xlsx->rows() as $row)
        {
            if($linea == 1) {
                $linea++;continue;
            }
            $cve_articulo = $this->pSQL($row[self::CVE_ARTICULO_TR]);
            $cve_lote = $this->pSQL($row[self::CVE_LOTE_TR]);

            $alm_origen  = $this->pSQL($row[self::ALM_ORIGEN_TR]);
            $alm_destino = $this->pSQL($row[self::ALM_DESTINO_TR]);
            $trasladar_en_cajas = $this->pSQL($row[self::TRASLADAR_EN_CAJAS]);

            $traslado_almacen = 0;
            if(($alm_origen != $alm_destino && $alm_origen != "" && $alm_destino != ""))// || ($alm_origen != '' && $alm_destino == '')
                $traslado_almacen = 1;

            if($alm_destino == '')
               $alm_destino = $alm_origen;

            $bl_origen  = $this->pSQL($row[self::BL_ORIGEN_TR]);
            $bl_destino = $this->pSQL($row[self::BL_DESTINO_TR]);

            $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
            mysqli_set_charset($conn, 'utf8');

            $sql_doc = "SELECT idy_ubica FROM c_ubicacion WHERE CodigoCSD = '$bl_origen'";
            if (!($res_doc = mysqli_query($conn, $sql_doc))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
            $row_doc = mysqli_fetch_array($res_doc);
            $idiorigen = $row_doc['idy_ubica'];

            $sql_doc = "SELECT idy_ubica FROM c_ubicacion WHERE CodigoCSD = '$bl_destino'";
            if (!($res_doc = mysqli_query($conn, $sql_doc))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
            $row_doc = mysqli_fetch_array($res_doc);
            $ididestino = $row_doc['idy_ubica'];

            $sql_doc = "SELECT id FROM c_almacenp WHERE clave = '$alm_origen'";
            if (!($res_doc = mysqli_query($conn, $sql_doc))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
            $row_doc = mysqli_fetch_array($res_doc);
            $almacen_origen = $row_doc['id'];

            $sql_doc = "SELECT id FROM c_almacenp WHERE clave = '$alm_destino'";
            if (!($res_doc = mysqli_query($conn, $sql_doc))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
            $row_doc = mysqli_fetch_array($res_doc);
            $almacen_destino = $row_doc['id'];

            $cantidad  = $this->pSQL($row[self::CANTIDAD_TR]);

            $LP_ORIG  = $this->pSQL($row[self::LP_TR_ORIG]);
            $LP_DEST  = $this->pSQL($row[self::LP_TR_DEST]);

            $sql_doc = "SELECT Existencia as existencia, ID_Proveedor FROM ts_existenciapiezas WHERE idy_ubica = '$idiorigen' AND cve_articulo = '$cve_articulo' AND cve_lote = '$cve_lote' AND IFNULL(Cuarentena, 0) = 0";
            $tipo = 'Artículo';
            $tipo_caja = "";
            if($ididestino == '' || $idiorigen == '' || $almacen_origen == '') continue;
            $ntarima = 0;
            if($LP_ORIG != '')
            {
                $sql_doc = "SELECT IDContenedor, tipo FROM c_charolas WHERE CveLP = '$LP_ORIG'";
                if (!($res_doc = mysqli_query($conn, $sql_doc))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
                $row_doc = mysqli_fetch_array($res_doc);
                $tipo = $row_doc['tipo'];
                $ntarima = $row_doc['IDContenedor'];

                $sql_doc = "SELECT existencia, ID_Proveedor FROM ts_existenciatarima WHERE idy_ubica = '$idiorigen' AND ntarima = (SELECT IDContenedor FROM c_charolas WHERE CveLP = '$LP_ORIG' AND IDContenedor in (SELECT ntarima from ts_existenciatarima where cve_almac = $almacen_origen) LIMIT 1) AND IFNULL(Cuarentena, 0) = 0 LIMIT 1";
            }

            if (!($res_doc2 = mysqli_query($conn, $sql_doc))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}


            if($tipo == 'Caja' && $LP_ORIG != '')
            {
                if($LP_DEST == '') $LP_DEST = $LP_ORIG;
                if($trasladar_en_cajas == 'S') $trasladar_en_cajas = '';//NO SE PUEDE REALIZAR PROCESO DE CONVERSIÓN DE CAJA A CAJA 
                $cantidad     = '';//----|
                $cve_articulo = '';//----> LA CANTIDAD, ARTICULO Y LOTE CUANDO ES POR CAJAS SIEMPRE SERÁ COMPLETA
                $cve_lote     = '';//----|
                $tipo_caja = $tipo;
            }

            if($trasladar_en_cajas == 'S' && ($LP_DEST == '' || $LP_ORIG == ''))
            {
                if($LP_ORIG != '')
                   $cajas_sin_lP[] = $LP_ORIG;
                else 
                   $msj_cajas = "Hay Pallets Destino que no poseen cajas";

                continue;
            }

            if(@mysqli_num_rows($res_doc2) || $tipo == 'Caja') 
            {

                    $row_doc = mysqli_fetch_array($res_doc2);
                    $cantidad_max = $row_doc['existencia'];
                    $id_proveedor = $row_doc['ID_Proveedor'];

                    $pallets_por_piezas = 'N';
                    if($tipo != 'Caja')
                    {
                        if($LP_ORIG != '' && $cve_articulo == '')
                        {
                            $sql_doc = "SELECT cve_articulo, existencia, ID_Proveedor FROM ts_existenciatarima WHERE ntarima = $ntarima LIMIT 1";
                            if (!($res_doc3 = mysqli_query($conn, $sql_doc))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
                            $row_doc = mysqli_fetch_array($res_doc3);
                            $cve_articulo = $row_doc['cve_articulo'];
                            $cantidad = $row_doc['existencia'];
                            $cantidad_max = $row_doc['existencia'];
                            $id_proveedor = $row_doc['ID_Proveedor'];
                        }
                        else if($LP_ORIG != '' && $cve_articulo != '')
                        {
                            $pallets_por_piezas = 'S';
                        }
                    }

                    if(($LP_DEST != '' && $trasladar_en_cajas == 'S') || $LP_DEST != '')
                    {

                            if($LP_ORIG != '' && $trasladar_en_cajas == 'S' && $cantidad == '')
                            {
                                $sql_doc = "SELECT cve_articulo, existencia, ID_Proveedor FROM ts_existenciatarima WHERE ntarima = $ntarima LIMIT 1";
                                if (!($res_doc = mysqli_query($conn, $sql_doc))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
                                $row_doc = mysqli_fetch_array($res_doc);
                                $cve_articulo = $row_doc['cve_articulo'];
                                $cantidad = $row_doc['existencia'];
                                $cantidad_max = $row_doc['existencia'];
                                $id_proveedor = $row_doc['ID_Proveedor'];
                            }

                            $sql = "SELECT COUNT(*) as existe, cve_almac, tipo FROM c_charolas WHERE clave_contenedor = '$LP_DEST' OR CveLP = '$LP_DEST'";
                            $rs = mysqli_query($conn, $sql);
                            $row_exist = mysqli_fetch_array($rs, MYSQLI_ASSOC);
                            $existe_dest = $row_exist['existe'];
                            $id_almacen = $row_exist['cve_almac'];
                            $tipo_lp = $row_exist['tipo'];
                            $label_lp = $LP_DEST;

                            //if($tipo_lp == 'Caja' && $LP_ORIG != $LP_DEST && $LP_DEST != '') {$lp_tipo_caja[] = $label_lp; continue;}
                            if($tipo_lp == 'Caja' && $LP_ORIG != $LP_DEST && $LP_DEST != '' || ($tipo_caja == 'Caja' && $tipo_lp != 'Caja' && $LP_DEST != '')) {$lp_tipo_caja[] = $label_lp; continue;}//TENGO ACTIVO ESTO MIENTRAS REALIZO EL PROCESO PARA UBICAR CAJAS EN LP

                            if($existe_dest == 0)
                            {
                                $sql = "SELECT #(SELECT MAX(ch.IDContenedor)+1 FROM c_charolas ch) AS id_contenedor, 
                                cve_almac, descripcion, tipo, alto, ancho, fondo, peso, pesomax, capavol FROM c_charolas WHERE TipoGen = 1 AND cve_almac = '$almacen_origen' AND tipo = 'Pallet' ORDER BY IDContenedor ASC LIMIT 1";

                                $rs = mysqli_query($conn, $sql);
                                $resul = mysqli_fetch_array($rs, MYSQLI_ASSOC);

                                //if(!$resul['id_contenedor']) break;

                                //$id_contenedor = $resul['id_contenedor'];
                                $descripcion   = $resul['descripcion'];
                                $tipo          = $resul['tipo'];
                                $alto          = $resul['alto'];
                                $ancho         = $resul['ancho'];
                                $fondo         = $resul['fondo'];
                                $peso          = $resul['peso'];
                                $pesomax       = $resul['pesomax'];
                                $capavol       = $resul['capavol'];
                                $id_almacen    = $resul['cve_almac'];

                                $label_lp = $LP_DEST;//"LP".str_pad($id_contenedor, 9, "0", STR_PAD_LEFT);

                                $sql = "INSERT IGNORE INTO c_charolas(cve_almac, clave_contenedor, descripcion, Permanente, tipo, alto, ancho, fondo, peso, pesomax, capavol, CveLP, TipoGen) 
                                        VALUES ({$almacen_origen}, '{$label_lp}', '{$descripcion}', 0, 'Pallet', {$alto}, {$ancho}, {$fondo}, {$peso}, {$pesomax}, {$capavol}, '{$label_lp}', 0)";
                                $rs = mysqli_query($conn, $sql);
                            }
                            if($trasladar_en_cajas == 'S') $trasladar_en_cajas = $label_lp;
                    }

        /*
                    $post = array(
                    'tipo' => 1,
                    'traslado_almacen' => $traslado_almacen,
                    'palletizar_traslado' => 0,
                    'trasladar_ubicacion' => 0,
                    'convertir_pzs' => 0,
                    'pallet_palletizar' => 1,
                    'almacen_origen' => $almacen_origen,
                    'almacen_destino' => $almacen_destino,
                    'zonarecepcioni_alm' => '',
                    'cantidad' => $cantidad,
                    'cantidad_max' => $cantidad_max,
                    'idiorigen' => $idiorigen,
                    'ididestino' => $ididestino,
                    'pallet_contenedor' => $LP_ORIG,
                    'tipo_pallet_contenedor_articulo' => $tipo,
                    'lp_val' => $LP_ORIG,
                    'pallet_val' => $LP_ORIG,
                    'fusionar' => 0,
                    'cve_articulo' => $cve_articulo,
                    'cve_lote' => $cve_lote,
                    'ID_Proveedor' => $id_proveedor,
                    'cantidadTotal' => $cantidad,
                    'cve_usuario' => $_SESSION['cve_usuario'],
                    'QA' => 'No'
                    );
        */
                    if($trasladar_en_cajas == 'N' || $trasladar_en_cajas == '') $trasladar_en_cajas = '';

                    //$trasladar_en_cajas = '';

                    $palletizar = 0;$lp_palletizar = 1;
                    if($LP_ORIG == '' && $LP_DEST != '') 
                    {
                        /*
                        $sql_doc = "SELECT IDContenedor, tipo FROM c_charolas WHERE CveLP = '$LP_DEST'";
                        if (!($res_doc = mysqli_query($conn, $sql_doc))) {echo "Falló la preparación: (" . mysqli_error($conn) . ") ";}
                        $row_doc = mysqli_fetch_array($res_doc);
                        $ntarima = $row_doc['IDContenedor'];
                        */
                        $palletizar = 1; 
                        //$lp_palletizar = $ntarima;
                        $lp_palletizar = $LP_DEST;
                    }

            }
            else
                continue;

            if($id_proveedor == '' && $tipo_caja == '') continue; 

            $_POST['tipo'] = 1;
            $_POST['traslado_almacen'] = $traslado_almacen;
            $_POST['palletizar_traslado'] = $palletizar;
            $_POST['trasladar_ubicacion'] = 0;
            $_POST['convertir_pzs'] = 0;
            $_POST['importacion'] = 1;
            $_POST['pallet_palletizar'] = $lp_palletizar;
            $_POST['almacen_origen'] = $almacen_origen;
            $_POST['almacen_destino'] = $almacen_destino;
            $_POST['zonarecepcioni_alm'] = '';
            $_POST['cantidad'] = $cantidad;
            $_POST['cantidad_max'] = $cantidad_max;
            $_POST['idiorigen'] = $idiorigen;
            $_POST['ididestino'] = $ididestino;
            $_POST['pallet_contenedor'] = $LP_ORIG;
            $_POST['tipo_pallet_contenedor_articulo'] = $tipo;
            $_POST['lp_val'] = $LP_ORIG;
            $_POST['pallet_val'] = $LP_ORIG;
            $_POST['lp_dest'] = $LP_DEST;
            $_POST['fusionar'] = 0;
            $_POST['cve_articulo'] = $cve_articulo;
            $_POST['cve_lote'] = $cve_lote;
            $_POST['ID_Proveedor'] = $id_proveedor;
            $_POST['cantidadTotal'] = $cantidad;
            $_POST['cve_usuario'] = $_SESSION['cve_usuario'];
            $_POST['QA'] = 'No';
            $_POST['pallets_por_piezas'] = $pallets_por_piezas;
            $_POST['fusionar_cajas'] = $trasladar_en_cajas;

            $data= $ga->moverTraslado($_POST);

            $linea++;
        }

        $success = 1;
        $msj = "Traslados Realizado con éxito";
        if(count($cajas_sin_lP) > 0 || $msj_cajas != '')
        {
            $success = 0;
            if($msj_cajas != '')
               $msj = $msj_cajas;
            else
            {
                $cjs_msj = "";
                $cjs = implode(", ", $cajas_sin_lP);
                $limite = 100;
                if (strlen($cjs) > $limite) {
                  $cjs_msj = substr($cjs, 0, $limite) . " ...";
                } else {
                  $cjs_msj = $cjs;
                }

                $msj = "Hay Cajas que no poseen Pallet Destino: $cjs_msj";
            }
        }
        $msj_lp = "";
        if(count($lp_tipo_caja) > 0)
        {
            $success = 0;
            $msj_lp = "";
            $lps = implode(", ", $lp_tipo_caja);
            $limite = 100;
            if (strlen($lps) > $limite) {
              $msj_lp = substr($lps, 0, $limite) . " ...";
            } else {
              $msj_lp = $lps;
            }

            $msj = "Los siguientes LP Destino están marcados como caja cuando deben ser Pallets: $msj_lp";
            if($tipo_caja != $tipo_lp)
                $msj = "Por los momentos solo puede ubicar cajas sueltas sin LP, El siguiente LP debe ser igual al LP origen para ubicar cajas sueltas: $msj_lp";
        }


        $this->response(200, [
            'statusText' => $msj,
            "success" => $success,
            "posts" => $_POST,
            //'mensaje_articulos_no_existentes' => $mensaje_articulos_no_existentes,
            //'mensaje_bl_no_existentes' => $mensaje_bl_no_existentes,
            "lineas"=>$lineas
        ]);

}


    public function xpaginatex()
    {
      $page    = $this->getInput('page', 1); // get the requested page
      $limit   = $this->getInput('rows', 10); // get how many rows we want to have into the grid
      $sidx    = $this->getInput('sidx'); // get index row - i.e. user click to sort
      $sord    = $this->getInput('sord'); // get the direction
      $status  = $this->getInput('status');

      $search  = $this->getInput('search');
      $codigo  = $this->getInput('codigo');
      $criterio  = $search;//$this->getInput('criterio');
      $almacen  = $this->getInput('almacen');
      //$filtro  = $this->getInput('filtro');
      $tipopedido  = $this->getInput('tipopedido');
      $ruta_pedido  = $this->getInput('ruta_pedido_list');
      $ciudad_pedido  = $this->getInput('ciudad_pedido_list');

      $fecha_inicio  = $this->getInput('fecha_inicio');
      $fecha_fin  = $this->getInput('fecha_fin');

      //$cross = "";
      //if(isset($this->getInput('cross')))
      $cross  = $this->getInput('cross');


      $start = $limit * $page - $limit; // do not put $limit*($page - 1) 
      $count = 0;

      $subpedidos_con_bl = ""; $ver_subpedido_A = "";
/*
      $sql2 = "
        SELECT o.fol_folio
        FROM th_pedido o
        LEFT JOIN cat_estados e ON e.ESTADO = o.status 
        LEFT JOIN c_cliente c On c.Cve_Clte = o.Cve_clte   
        LEFT JOIN td_pedido od ON od.Fol_folio = o.Fol_folio
        LEFT JOIN th_subpedido thsub ON o.Fol_folio = thsub.fol_folio 
        LEFT JOIN c_usuario u on u.cve_usuario = thsub.cve_usuario 
        LEFT JOIN V_ArticuloAlmacen a ON a.cve_articulo = od.Cve_articulo 
        LEFT JOIN t_recorrido_surtido trs on trs.fol_folio = od.Fol_folio and trs.Cve_articulo = od.Cve_articulo 
        LEFT JOIN td_surtidopiezas s ON s.fol_folio = od.Fol_folio AND s.Cve_articulo = od.Cve_articulo AND s.cve_almac = o.cve_almac 
        WHERE o.Activo = 1
      ";
*/
      
      $sql_tipo_pedido1 = " AND IFNULL(o.TipoPedido, 'P')  = '$tipopedido' "; 
      $sql_tipo_pedido2 = " AND IFNULL(th.TipoPedido, 'P') = '$tipopedido' ";
/*
      $sql_tipo_pedido1 = ""; $sql_tipo_pedido2 = "";
      if($tipopedido != "")
      {
          //if($tipopedido == '-') $tipopedido = 0; //hago este cambio aquí porque está tomando el 0 como vacío y no entra al if
          $sql_tipo_pedido1 = " AND IF(op.Folio_Pro = o.Fol_folio, 1, IF(LEFT(o.Fol_folio, 2) = 'TR', 2, IF(LEFT(o.Fol_folio, 2) = 'WS', 4, IF(LEFT(o.Fol_folio, 1) = 'S' AND IFNULL(o.cve_ubicacion,'') != '', 5, IF(IFNULL(o.ruta,'') != '' AND LEFT(o.Fol_folio, 1) != 'R' AND LEFT(o.Fol_folio, 1) != 'P', 3, IF(IFNULL(o.ruta,'') != '' AND LEFT(o.Fol_folio, 1) = 'R', 6, IF(IFNULL(o.ruta,'') != '' AND LEFT(o.Fol_folio, 1) = 'P', 7, 0))))))) = '{$tipopedido}' ";

          $sql_tipo_pedido2 = " AND IF(opx.Folio_Pro = th.Fol_folio, 1, IF(LEFT(thsp.Fol_folio, 2) = 'TR', 2, IF(LEFT(thsp.Fol_folio, 2) = 'WS', 4, IF(LEFT(th.Fol_folio, 1) = 'S' AND IFNULL(th.cve_ubicacion,'') != '', 5, IF(IFNULL(th.ruta,'') != '' AND LEFT(th.Fol_folio, 1) != 'R' AND LEFT(th.Fol_folio, 1) != 'P', 3, IF(IFNULL(th.ruta,'') != '' AND LEFT(th.Fol_folio, 1) = 'R', 6, IF(IFNULL(th.ruta,'') != '' AND LEFT(th.Fol_folio, 1) = 'P', 7, 0))))))) = '{$tipopedido}' ";

        
        //if($tipopedido == 4)
        //{
        //  $sql_tipo_pedido1 .= " AND o.tipo_venta = 'venta' ";
        //  $sql_tipo_pedido2 .= " AND th.tipo_venta = 'venta' ";
        //}
        //else if($tipo_pedido == 0)
        //{
        //  $sql_tipo_pedido1 .= " AND o.tipo_venta = 'preventa' ";
        //  $sql_tipo_pedido2 .= " AND th.tipo_venta = 'preventa' ";
        //}
        
      }
      else
      {
          $sql_tipo_pedido1 = " AND IFNULL(o.ruta, '') = '' AND IFNULL(o.cve_ubicacion, '') = '' ";  //AND o.Fol_folio LIKE 'S%'
          $sql_tipo_pedido2 = " AND IFNULL(th.ruta, '') = '' AND IFNULL(th.cve_ubicacion, '') = '' "; //AND th.Fol_folio LIKE 'S%'
      }
*/
      $sql_ruta = "";$sql_ruta2 = "";
      if($ruta_pedido != '')
      {
          $ruta_arr = explode(";;;;;", $ruta_pedido);
          $ruta1 = $ruta_arr[0];
          $ruta2 = "";
          if(count($ruta_arr) > 1)
            $ruta2 = $ruta_arr[1];
          if($ruta1 != "")
          {
              $sql_ruta  = " AND (o.ruta = '$ruta1' OR o.cve_ubicacion = '$ruta1') ";
              $sql_ruta2 = " AND (th.ruta = '$ruta1' OR th.cve_ubicacion = '$ruta1') ";
          }

          if($ruta2 != "")
          {
              $sql_ruta  = " AND (o.ruta IN ('$ruta1', '$ruta2') OR o.cve_ubicacion IN ('$ruta1', '$ruta2')) ";
              $sql_ruta2 = " AND (th.ruta IN ('$ruta1', '$ruta2') OR th.cve_ubicacion IN ('$ruta1', '$ruta2')) ";
          }
      }

      $sql_ciudad = "";$sql_ciudad2 = "";
      if($ciudad_pedido != '')
      {
          $ciudad_pedido = implode($ciudad_pedido, "','");
          $sql_ciudad = " AND o.Cve_Clte IN (SELECT DISTINCT Cve_Clte FROM c_cliente WHERE Colonia IN ('{$ciudad_pedido}')) ";
          $sql_ciudad2 = " AND th.Cve_Clte IN (SELECT DISTINCT Cve_Clte FROM c_cliente WHERE Colonia IN ('{$ciudad_pedido}')) ";
      }


      if($search != "") $status = ""; //cuando se realice una busqueda con texto escrito, se debe poder buscar por cualquier status

      $status_A = "";
      if(!empty($status) && $status != 'A')
        $status_A = " AND o.status = '{$status}' AND o.Fol_folio NOT IN (SELECT Fol_folio FROM th_subpedido)";

      if($status == 'S') 
      {
          $subpedidos_con_bl = "
       AND o.Fol_folio IN (SELECT fol_folio FROM t_recorrido_surtido WHERE fol_folio = o.Fol_folio) 
       AND (SELECT COUNT(*) FROM th_pedido WHERE fol_folio IN (SELECT fol_folio FROM th_subpedido) AND STATUS = 'S') = 0 ";
       //#SOLO CON STATUS = 'S'
       //SOLO CON STATUS = 'S', este = 'S' tambien es fijo no variable
          //$ver_subpedido_A = " AND (SELECT claverp FROM t_recorrido_surtido WHERE fol_folio = thsp.Fol_folio AND Cve_articulo = tdsp.Cve_articulo LIMIT 1) != ''";
      }

      $sql_search = "";$sql_union_search = "";
      if (!empty($search) ) //&& !empty($filtro)
      {
            $sql_search = " AND (o.Fol_folio like '%$search%' OR o.Pick_Num like '%$search%' OR p.Descripcion like '%$search%' OR u.nombre_completo like '%$search%' OR o.Fol_folio IN (SELECT Fol_folio FROM td_pedido WHERE Cve_articulo LIKE '%$search%')) ";

            $sql_union_search = " AND (thsp.Fol_folio LIKE '%$criterio%' OR th.Pick_Num LIKE '%$criterio%' OR p.Descripcion LIKE '%$criterio%'  OR c_usuario.nombre_completo LIKE '%$criterio%' OR thsp.Fol_folio IN (SELECT Fol_folio FROM td_pedido WHERE Cve_articulo LIKE '%$search%')) ";

            /*$sql_union_backorder .= "WHERE (b.Fol_Folio LIKE '%$criterio%' OR b.Pick_Num LIKE '%$criterio%' OR c.RazonSocial LIKE '%$criterio%')";*/

            //$sql2 .= "AND o.Fol_folio like '%$search%' OR o.Pick_Num like '%$search%' OR u.nombre_completo like '%$search%'";
      }

      $sql_disponible = " '1' AS disponible, ";
      $sql_disponible2 = " '1' AS disponible, ";
      
      //&& (strpos($_SERVER['HTTP_HOST'], 'wms.ql.') === false)
if((strpos($_SERVER['HTTP_HOST'], 'avavex') === false) && (strpos($_SERVER['HTTP_HOST'], 'rie') === false) && (strpos($_SERVER['HTTP_HOST'], 'dicoisa') === false))
{
    $sql_disponible = "    
    IF(IFNULL(o.Ship_Num, '') = '',
    
    (SELECT SUM(IF(COALESCE(
    IF(IFNULL(tsbx.cve_lote, '') = '', 
(SELECT SUM(Existencia) AS suma 
FROM VS_ExistenciaParaSurtido V_ExistenciaGral 
WHERE V_ExistenciaGral.cve_articulo = tsbx.Cve_Articulo AND V_ExistenciaGral.cve_almac = '{$almacen}' 
AND ((V_ExistenciaGral.cve_lote IN (SELECT Lote FROM c_lotes WHERE cve_articulo = V_ExistenciaGral.cve_articulo AND Caducidad > CURDATE())) OR (V_ExistenciaGral.cve_articulo = ax.cve_articulo AND IFNULL(ax.Caduca, 'N') = 'N'))
), 
(SELECT SUM(V_ExistenciaG.Existencia) AS suma 
FROM VS_ExistenciaParaSurtido V_ExistenciaG 
LEFT JOIN V_ExistenciaGral veg ON veg.cve_articulo = V_ExistenciaG.cve_articulo and IFNULL(veg.cve_lote, '') = IFNULL(V_ExistenciaG.cve_lote, '') 
WHERE V_ExistenciaG.cve_articulo = tsbx.Cve_Articulo AND IFNULL(tsbx.cve_lote, '') = IFNULL(V_ExistenciaG.cve_lote, '') AND IFNULL(veg.cve_lote, '') = IFNULL(V_ExistenciaG.cve_lote, '') AND V_ExistenciaG.cve_almac = '{$almacen}' AND veg.cve_articulo = tsbx.Cve_Articulo
AND ((IFNULL(V_ExistenciaG.cve_lote, '') IN (SELECT Lote FROM c_lotes WHERE cve_articulo = V_ExistenciaG.cve_articulo AND Caducidad > CURDATE())) OR (V_ExistenciaG.cve_articulo = ax.cve_articulo AND IFNULL(tsbx.cve_lote, '') = IFNULL(V_ExistenciaG.cve_lote, '') AND IFNULL(ax.Caduca, 'N') = 'N'))
))
, 0) >= tsbx.Num_cantidad, tsbx.Num_cantidad, COALESCE((SELECT SUM(Existencia) AS suma FROM VS_ExistenciaParaSurtido V_ExistenciaGral WHERE V_ExistenciaGral.cve_articulo = tsbx.Cve_Articulo AND V_ExistenciaGral.cve_almac = '{$almacen}' AND ((V_ExistenciaGral.cve_lote IN (SELECT Lote FROM c_lotes WHERE cve_articulo = V_ExistenciaGral.cve_articulo AND Caducidad > CURDATE())) OR (V_ExistenciaGral.cve_articulo = ax.cve_articulo AND IFNULL(ax.Caduca, 'N') = 'N'))), 0))
) AS existencia
FROM td_pedido tsbx
  LEFT JOIN c_articulo ax ON ax.cve_articulo = tsbx.Cve_Articulo
  LEFT JOIN th_pedido ON th_pedido.Fol_folio = tsbx.Fol_folio
  #LEFT JOIN c_cliente cx ON cx.Cve_Clte = th_pedido.Cve_clte
  LEFT JOIN c_lotes lox ON lox.cve_articulo = ax.cve_articulo AND lox.Lote = tsbx.cve_lote
  LEFT JOIN c_serie sex ON sex.cve_articulo = ax.cve_articulo AND sex.numero_serie = tsbx.cve_lote
WHERE tsbx.Fol_folio = o.Fol_folio
AND th_pedido.cve_almac = '{$almacen}')

, 

    (SELECT SUM(IF(COALESCE(
IF(IFNULL(tsbx.cve_lote, '') = '',
(SELECT SUM(Existencia) AS suma 
FROM V_ExistenciaGralProduccion V_ExistenciaGral 
WHERE V_ExistenciaGral.cve_articulo = tsbx.Cve_Articulo AND V_ExistenciaGral.cve_almac = '{$almacen}' 
AND ((V_ExistenciaGral.cve_lote IN (SELECT Lote FROM c_lotes WHERE cve_articulo = V_ExistenciaGral.cve_articulo AND Caducidad > CURDATE())) OR (V_ExistenciaGral.cve_articulo = ax.cve_articulo AND IFNULL(ax.Caduca, 'N') = 'N'))
)
,
(SELECT SUM(Existencia) AS suma 
FROM V_ExistenciaGralProduccion V_ExistenciaG 
WHERE V_ExistenciaG.cve_articulo = tsbx.Cve_Articulo AND V_ExistenciaG.cve_lote = tsbx.cve_lote AND V_ExistenciaG.cve_almac = '{$almacen}' 
AND ((V_ExistenciaG.cve_lote IN (SELECT Lote FROM c_lotes WHERE cve_articulo = V_ExistenciaG.cve_articulo AND Caducidad > CURDATE())) OR (V_ExistenciaG.cve_articulo = ax.cve_articulo AND tsbx.cve_lote = V_ExistenciaG.cve_lote AND IFNULL(ax.Caduca, 'N') = 'N'))
)
)
, 0) >= tsbx.Num_cantidad, tsbx.Num_cantidad, COALESCE((SELECT SUM(Existencia) AS suma FROM V_ExistenciaGralProduccion V_ExistenciaGral WHERE V_ExistenciaGral.cve_articulo = tsbx.Cve_Articulo AND V_ExistenciaGral.cve_almac = '{$almacen}' AND ((V_ExistenciaGral.cve_lote IN (SELECT Lote FROM c_lotes WHERE cve_articulo = V_ExistenciaGral.cve_articulo AND Caducidad > CURDATE())) OR (V_ExistenciaGral.cve_articulo = ax.cve_articulo AND IFNULL(ax.Caduca, 'N') = 'N'))), 0))) AS existencia
FROM td_pedido tsbx
  LEFT JOIN c_articulo ax ON ax.cve_articulo = tsbx.Cve_Articulo
  LEFT JOIN th_pedido ON th_pedido.Fol_folio = tsbx.Fol_folio
  #LEFT JOIN c_cliente cx ON cx.Cve_Clte = th_pedido.Cve_clte
  LEFT JOIN c_lotes lox ON lox.cve_articulo = ax.cve_articulo AND lox.Lote = tsbx.cve_lote
  LEFT JOIN c_serie sex ON sex.cve_articulo = ax.cve_articulo AND sex.numero_serie = tsbx.cve_lote
WHERE tsbx.Fol_folio = o.Fol_folio
AND th_pedido.cve_almac = '{$almacen}')

) AS disponible,

";
/*
$sql_disponible2 = "(SELECT SUM(IF(COALESCE((SELECT SUM(Existencia) AS suma 
FROM VS_ExistenciaParaSurtido V_ExistenciaGral 
WHERE V_ExistenciaGral.cve_articulo = tsbx.Cve_Articulo AND V_ExistenciaGral.cve_almac = '{$almacen}' 
AND ((V_ExistenciaGral.cve_lote IN (SELECT Lote FROM c_lotes WHERE cve_articulo = V_ExistenciaGral.cve_articulo AND Caducidad > CURDATE())) OR (V_ExistenciaGral.cve_articulo = ax.cve_articulo AND IFNULL(ax.Caduca, 'N') = 'N'))), 0) >= tsbx.Num_cantidad, tsbx.Num_cantidad, COALESCE((SELECT SUM(Existencia) AS suma FROM VS_ExistenciaParaSurtido V_ExistenciaGral WHERE V_ExistenciaGral.cve_articulo = tsbx.Cve_Articulo AND V_ExistenciaGral.cve_almac = '{$almacen}' AND ((V_ExistenciaGral.cve_lote IN (SELECT Lote FROM c_lotes WHERE cve_articulo = V_ExistenciaGral.cve_articulo AND Caducidad > CURDATE())) OR (V_ExistenciaGral.cve_articulo = ax.cve_articulo AND IFNULL(ax.Caduca, 'N') = 'N'))), 0))) AS existencia
FROM td_pedido tsbx
  LEFT JOIN c_articulo ax ON ax.cve_articulo = tsbx.Cve_Articulo
  LEFT JOIN th_pedido ON th_pedido.Fol_folio = tsbx.Fol_folio
  LEFT JOIN c_cliente cx ON cx.Cve_Clte = th_pedido.Cve_clte
  LEFT JOIN c_lotes lox ON lox.cve_articulo = ax.cve_articulo AND lox.Lote = tsbx.cve_lote
  LEFT JOIN c_serie sex ON sex.cve_articulo = ax.cve_articulo AND sex.numero_serie = tsbx.cve_lote
WHERE tsbx.Fol_folio = tdsp.fol_folio
AND th_pedido.cve_almac = '{$almacen}') AS disponible,";
*/
}

      $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

  $sql_instancia = "SELECT IFNULL(Valor, '') AS instancia FROM t_configuraciongeneral WHERE cve_conf = 'instancia' LIMIT 1";
  if (!($res_instancia = mysqli_query($conn, $sql_instancia)))
          echo "Falló la preparación res_ordenar: (" . mysqli_error($conn) . ") ";
  $instancia = mysqli_fetch_array($res_instancia)['instancia'];

    $sql_ordenar = "SELECT DISTINCT COUNT(*) as ordenar_pedidos_folio FROM t_configuraciongeneral WHERE cve_conf = 'ordenar_pedidos_folio' AND Valor = '1'";
    if (!($res_ordenar = mysqli_query($conn, $sql_ordenar)))
        echo "Falló la preparación res_ordenar: (" . mysqli_error($conn) . ") ";
    $ordenar_por_folio = mysqli_fetch_array($res_ordenar)['ordenar_pedidos_folio'];


  $sql_fecha1 = ""; $sql_fecha2 = "";

  if($search == '')
  {
      if (!empty($fecha_inicio) AND !empty($fecha_fin)) 
      {
        $fecha_inicio = date ("d-m-Y", strtotime($fecha_inicio));
        $fecha_fin = date ("d-m-Y", strtotime($fecha_fin));

        //$sql .= " AND DATE_FORMAT(o.Fec_Entrada, '%d-%m-%Y') BETWEEN '{$fecha_inicio}' AND '{$fecha_fin}' ";
        //$sql2 .= " AND DATE_FORMAT(o.Fec_Entrada, '%d-%m-%Y') BETWEEN '{$fecha_inicio}' AND '{$fecha_fin}' ";

        //$sql_fecha1 = " AND IF(IFNULL(o.Fec_Entrada, '0000-00-00 00:00:00') = '0000-00-00 00:00:00', o.Fec_Pedido, o.Fec_Entrada) BETWEEN STR_TO_DATE('$fecha_inicio', '%d-%m-%Y') AND STR_TO_DATE('$fecha_fin', '%d-%m-%Y') ";
        $sql_fecha1 = " AND IF(IFNULL(o.Fec_Pedido, '0000-00-00 00:00:00') = '0000-00-00 00:00:00', o.Fec_Entrada, o.Fec_Pedido) BETWEEN STR_TO_DATE('$fecha_inicio', '%d-%m-%Y') AND STR_TO_DATE('$fecha_fin', '%d-%m-%Y') ";
        $sql_fecha2 = " AND IF(IFNULL(th.Fec_Pedido, '0000-00-00 00:00:00') = '0000-00-00 00:00:00', th.Fec_Entrada, th.Fec_Pedido) BETWEEN STR_TO_DATE('$fecha_inicio', '%d-%m-%Y') AND STR_TO_DATE('$fecha_fin', '%d-%m-%Y') ";

        if($instancia == 'iberofarmacos')
        {
            $sql_fecha1 = " AND IF(IFNULL(o.Fec_Entrada, '0000-00-00 00:00:00') = '0000-00-00 00:00:00', o.Fec_Pedido, o.Fec_Entrada) BETWEEN STR_TO_DATE('$fecha_inicio', '%d-%m-%Y') AND STR_TO_DATE('$fecha_fin', '%d-%m-%Y') ";
            $sql_fecha2 = " AND IF(IFNULL(th.Fec_Entrada, '0000-00-00 00:00:00') = '0000-00-00 00:00:00', th.Fec_Pedido, th.Fec_Entrada) BETWEEN STR_TO_DATE('$fecha_inicio', '%d-%m-%Y') AND STR_TO_DATE('$fecha_fin', '%d-%m-%Y') ";
        }
      }
      elseif (!empty($fecha_inicio)) 
      {
        $fecha_inicio = date ("d-m-Y", strtotime($fecha_inicio));
        //$sql .= " AND DATE_FORMAT(o.Fec_Entrada, '%d-%m-%Y') >= '{$fecha_inicio}' ";
        //$sql2 .= " AND DATE_FORMAT(o.Fec_Entrada, '%d-%m-%Y') >= '{$fecha_inicio}' ";
        $sql_fecha1 .= " AND IF(IFNULL(o.Fec_Pedido, '0000-00-00 00:00:00') = '0000-00-00 00:00:00', o.Fec_Entrada, o.Fec_Pedido) >= STR_TO_DATE('$fecha_inicio', '%d-%m-%Y') ";
        $sql_fecha2 .= " AND IF(IFNULL(th.Fec_Pedido, '0000-00-00 00:00:00') = '0000-00-00 00:00:00', th.Fec_Entrada, th.Fec_Pedido) >= STR_TO_DATE('$fecha_inicio', '%d-%m-%Y') ";

        if($instancia == 'iberofarmacos')
        {
        $sql_fecha1 .= " AND IF(IFNULL(o.Fec_Entrada, '0000-00-00 00:00:00') = '0000-00-00 00:00:00', o.Fec_Pedido, o.Fec_Entrada) >= STR_TO_DATE('$fecha_inicio', '%d-%m-%Y') ";
        $sql_fecha2 .= " AND IF(IFNULL(th.Fec_Entrada, '0000-00-00 00:00:00') = '0000-00-00 00:00:00', th.Fec_Pedido, th.Fec_Entrada) >= STR_TO_DATE('$fecha_inicio', '%d-%m-%Y') ";
        }

      }
      elseif (!empty($fecha_fin)) 
      {
        $fecha_fin = date ("d-m-Y", strtotime($fecha_fin));
        //$sql .= " AND DATE_FORMAT(o.Fec_Entrada, '%d-%m-%Y') <= '{$fecha_fin}'";
        //$sql2 .= " AND DATE_FORMAT(o.Fec_Entrada, '%d-%m-%Y') <= '{$fecha_fin}'";
        $sql_fecha1 .= " AND IF(IFNULL(o.Fec_Entrada, '0000-00-00 00:00:00') = '0000-00-00 00:00:00', o.Fec_Pedido, o.Fec_Entrada) <= STR_TO_DATE('{$fecha_fin}', '%d-%m-%Y')";
        $sql_fecha2 .= " AND IF(IFNULL(th.Fec_Entrada, '0000-00-00 00:00:00') = '0000-00-00 00:00:00', th.Fec_Pedido, th.Fec_Entrada) <= STR_TO_DATE('{$fecha_fin}', '%d-%m-%Y')";
      }
  }

  $sql_welldex = ""; $sql_welldex_sub = "";
  if($instancia == 'welldex')
  {
     $sql_welldex = "
          IFNULL(o.Ref_Wel, '') as Ref_Wel,
          IFNULL(o.Ref_Imp, '') as Ref_Imp,
          IFNULL(o.Pedimento, '') as Pedimento,
          IFNULL(o.Factura_Vta, '') as Factura_Vta,
          IFNULL(o.Ped_Imp, '') as Ped_Imp,
    ";
     $sql_welldex_sub = "
          IFNULL(th.Ref_Wel, '') as Ref_Wel,
          IFNULL(th.Ref_Imp, '') as Ref_Imp,
          IFNULL(th.Pedimento, '') as Pedimento,
          IFNULL(th.Factura_Vta, '') as Factura_Vta,
          IFNULL(th.Ped_Imp, '') as Ped_Imp,
    ";
  }

  $sql_tipo_cross1 = "";$sql_tipo_cross2 = "";
  if(isset($cross) && $cross == true) 
  {
    $sql_tipo_cross1 = " AND LEFT(o.Fol_folio, 2) = 'XD' ";
    $sql_tipo_cross2 = " AND LEFT(th.Fol_folio, 2) = 'XD' ";
  }

      $sql = "
      SELECT * FROM (
        SELECT 
          IF(o.TipoDoc = 'tipo_lp', 0, (SELECT COUNT(*) FROM td_pedido WHERE Fol_folio = o.Fol_folio)) AS num_registros,
          o.id_pedido, 
          IF(IFNULL(o.TipoDoc, '') = 'tipo_lp', 'S', 'N') as EsTipoLP,
          IF(LEFT(IFNULL((SELECT Folio_BackO FROM th_backorder WHERE Fol_Folio = o.Fol_folio LIMIT 1), ''), 2) = 'BO', 4, IF(op.Folio_Pro = o.Fol_folio, 1, IF(LEFT(o.Fol_folio, 2) = 'TR', 2, IF(LEFT(o.Fol_folio, 2) = 'WS', 3, IF(LEFT(o.Fol_folio, 2) = 'RB', 5, 0))))) AS es_ot,
          o.Fol_folio AS orden, 
          0 AS sufijo, 
          #o.Cve_clte as cliente,
          IFNULL(IF(LEFT(o.Fol_folio, 2) = 'TR', (SELECT CONCAT('(',cal.clave, ') ', cal.nombre) FROM c_almacenp cal WHERE cal.id = o.statusaurora), o.Cve_clte), '--') AS cliente,
          IFNULL((SELECT Folio_BackO FROM th_backorder WHERE Fol_Folio = o.Fol_folio LIMIT 1), '') AS Folio_BackO,
          IFNULL(o.Pick_Num, '--') AS orden_cliente, 
          IFNULL(p.Descripcion, '--') AS prioridad, 
          CASE  
          WHEN LEFT(o.Fol_folio,2) = 'WS' THEN CONCAT(e.DESCRIPCION, ' Surtiendo WS') 
          WHEN LEFT(IFNULL((SELECT Folio_BackO FROM th_backorder WHERE Fol_Folio = o.Fol_folio LIMIT 1), ''), 2) = 'BO' THEN (SELECT DESCRIPCION FROM cat_estados WHERE ESTADO = 'A')
          ELSE IFNULL(e.DESCRIPCION, '--') 
          END AS status, 
          o.status AS status_pedido,
          IFNULL(o.statusaurora, '') as statusaurora, 
          IFNULL(op.Tipo, '') as TipoOT,
          IFNULL((SELECT DISTINCT Fol_PedidoCon FROM td_consolidado WHERE Fol_Folio = o.Fol_folio LIMIT 1), 1) AS TieneOla,
          IF(
          o.TipoPedido = 'W', (SELECT DISTINCT GROUP_CONCAT(DISTINCT Fol_Folio SEPARATOR ', ') FROM td_consolidado WHERE Fol_PedidoCon = o.Fol_folio), 
          IF(o.TipoPedido = 'W2', 
          (SELECT DISTINCT GROUP_CONCAT(DISTINCT tws1.Fol_PedidoCon, '<br>',(SELECT GROUP_CONCAT(DISTINCT tws2.Fol_Folio SEPARATOR ', ') FROM t_consolidado tws2 WHERE tws2.Fol_PedidoCon = tws1.Fol_PedidoCon) SEPARATOR '<br><br>') AS foliows FROM t_consolidado tws1 WHERE tws1.Fol_Consolidado = o.Fol_folio)
          , '')
          ) AS folio_ws,
          IF(o.TipoPedido = 'X', 1, 0) AS folio_xd,
          IF(o.TipoPedido = 'X' AND IFNULL(o.Ship_Num, '') = '', 0, 1) AS folio_xd_asignable,
          IFNULL(o.Ship_Num, '') as Ship_Num,

          #IFNULL(c.Colonia, '') as Colonia,
          '1' AS disponible,
          IFNULL(IFNULL(r.cve_ruta, o.cve_ubicacion), '') as ruta_pedido,

          IF(IFNULL(o.Fec_Entrada, '0000-00-00 00:00:00') = '0000-00-00 00:00:00', DATE_FORMAT(o.Fec_Pedido, '%d-%m-%Y'), DATE_FORMAT(o.Fec_Entrada, '%d-%m-%Y')) AS fecha_pedido, 
          IFNULL(DATE_FORMAT(o.Fec_Entrega, '%d-%m-%Y'), '--') AS Fec_Entrega, 
          #IF(IFNULL(o.Fec_Entrada, '0000-00-00 00:00:00') = '0000-00-00 00:00:00', DATE_FORMAT(o.Fec_Pedido, '%d-%m-%Y'), DATE_FORMAT(o.Fec_Entrada, '%d-%m-%Y')) AS Fec_Entrega_ord,
          o.id_pedido AS Fec_Entrega_ord,
          IFNULL(DATE_FORMAT(o.Fec_Entrega, '%d-%m-%Y'), '--') AS fecha_compromiso, 
          IFNULL(o.rango_hora, '--') AS rango_hora, 
          IFNULL(DATE_FORMAT(thsub.Hora_inicio, '%d-%m-%Y %H:%i:%s'), '--') AS fecha_ini, 
          IFNULL(DATE_FORMAT(thsub.Hora_Final, '%d-%m-%Y %H:%i:%s'), '--') AS fecha_fi, 
          IFNULL(o.BanEmpaque, 0) as bloqueado,
          IFNULL(o.Cve_Usuario, '') as usuario_pedido,
          IFNULL(o.Observaciones, '') AS observaciones,
          IFNULL(DATE_FORMAT(o.Fec_Aprobado, '%d-%m-%Y | %H:%i:%s'), '') AS fecha_aprobacion,
          IFNULL(o.Tot_Factura, '') AS total_factura,
          'N' AS tiene_foto,
          IFNULL((SELECT GROUP_CONCAT(DISTINCT IFNULL(Proyecto, '') SEPARATOR ', ') FROM td_pedido WHERE Fol_folio = o.Fol_folio AND IFNULL(Proyecto, '') != ''), '') AS proyectos,

          {$sql_welldex}

          IFNULL(u.nombre_completo, '--') AS asignado 
        FROM th_pedido o 
          LEFT JOIN t_tiposprioridad p ON p.ID_Tipoprioridad = o.ID_Tipoprioridad 
          #LEFT JOIN c_cliente c ON c.Cve_Clte = o.Cve_Clte
          LEFT JOIN cat_estados e ON e.ESTADO = o.status 
          LEFT JOIN th_subpedido thsub ON o.Fol_folio = thsub.fol_folio 
          LEFT JOIN c_usuario u on u.cve_usuario = thsub.cve_usuario 
          LEFT JOIN t_ordenprod op ON op.Folio_Pro = o.Fol_folio
          LEFT JOIN t_ruta r ON r.ID_Ruta = o.ruta
        WHERE o.Activo = 1 and o.id_pedido != '' 
        {$sql_tipo_pedido1} 
        {$sql_ruta} 
        {$sql_ciudad} 
        {$sql_search} {$sql_fecha1}
        $status_A 
        {$sql_tipo_cross1}
        $subpedidos_con_bl 
      ";

      if (!empty($almacen)) 
      {
        $sql_tr = "";$sql_tr2 = "";
        if($tipopedido == 'R' || $tipopedido == 'RI') 
        {
          $sql_tr = " OR o.statusaurora = '{$almacen}' ";
          $sql_tr2 = " OR th.statusaurora = '{$almacen}' ";
        }
        $sql .= " AND (o.cve_almac = '{$almacen}'  {$sql_tr}) ";
        //$sql2 .= " AND o.cve_almac = '{$almacen}' ";
      }


      $status_subpedidos = "";
      if (!empty($status)) 
      {
          //$status_subpedidos = " AND th_pedido.status = '{$status}'";
          $status_subpedidos = " AND thsp.status = '{$status}' ";
      }
/*
      $sql_union = "
      SELECT DISTINCT
              th_pedido.id_pedido,
                    td_pedido.fol_folio AS orden,
                    t_recorrido_surtido.Sufijo AS sufijo,
                    '' AS Folio_BackO,
                    IFNULL(th_pedido.Pick_Num, '--') AS orden_cliente, 
                    th_pedido.destinatario AS destinatario,
                    IFNULL(p.Descripcion, '--') AS prioridad, 
                    CASE LEFT(th_pedido.Fol_folio,2) 
                    WHEN 'WS' THEN CONCAT(e.DESCRIPCION, ' Surtiendo WS') 
                    ELSE IFNULL(e.DESCRIPCION, '--') 
                    END AS STATUS, 
                    th_pedido.status AS status_pedido,
                    IFNULL((SELECT DISTINCT Fol_PedidoCon FROM td_consolidado WHERE Fol_Folio = th_pedido.Fol_folio), 1) AS TieneOla,
                    IFNULL(c.RazonSocial, '--') AS cliente, 
                    IFNULL(c.CalleNumero, '--') AS direccion, 
                    IFNULL(c.CodigoPostal, '--') AS dane, 
                    IFNULL(c.Ciudad, '--') AS ciudad, 
                    IFNULL(c.Estado, '--') AS estado, 
                    '--' AS cantidad,
                    '0' AS volumen,
                    '0' AS peso,
                    IFNULL(DATE_FORMAT(th_pedido.Fec_Entrada, '%d-%m-%Y'), '--') AS fecha_pedido, 
                    th_pedido.Fec_Entrada AS Fec_Entrega,
                    IFNULL(DATE_FORMAT(th_pedido.Fec_Pedido, '%d-%m-%Y'), '--') AS fecha_compromiso,
                    '--' AS fecha_ini, 
                    '--' AS fecha_fi, 
                    '--' AS TiempoSurtido,
                    '0%' AS surtido,
                    '' as bloqueado,
                    IFNULL(c_usuario.nombre_completo, '--') AS asignado 
                FROM td_pedido
                    INNER JOIN t_recorrido_surtido ON t_recorrido_surtido.fol_folio = td_pedido.Fol_folio AND t_recorrido_surtido.Cve_articulo = td_pedido.Cve_articulo
                    LEFT JOIN c_lotes ON c_lotes.id = t_recorrido_surtido.cve_lote
                    LEFT JOIN c_usuario ON c_usuario.cve_usuario = t_recorrido_surtido.cve_usuario
                    LEFT JOIN c_articulo a ON a.cve_articulo = td_pedido.Cve_articulo
                    LEFT JOIN th_pedido ON th_pedido.Fol_folio = td_pedido.Fol_folio $status_subpedidos
                    LEFT JOIN cat_estados e ON e.ESTADO = th_pedido.status 
                    LEFT JOIN t_tiposprioridad p ON p.ID_Tipoprioridad = th_pedido.ID_Tipoprioridad 
                    LEFT JOIN c_cliente c ON c.Cve_Clte = th_pedido.Cve_clte
                    LEFT JOIN (
                        SELECT 
                            V_ExistenciaGral.cve_articulo,
                            c_ubicacion.CodigoCSD,
                            MIN(V_ExistenciaGral.cve_ubicacion) AS MIN 
                        FROM V_ExistenciaGral 
                            LEFT JOIN c_ubicacion ON V_ExistenciaGral.cve_ubicacion = c_ubicacion.idy_ubica 
                        GROUP BY cve_articulo
                    ) Z ON Z.cve_articulo = td_pedido.Cve_articulo
                  WHERE th_pedido.id_pedido != ''
      ";
*/
      $sql_union = "
      SELECT DISTINCT 
                    #thsp.Fol_folio AS id_pedido,
                    1 AS num_registros,
                    th.id_pedido AS id_pedido,
                    IF(IFNULL(th.TipoDoc, '') = 'tipo_lp', 'S', 'N') as EsTipoLP,
                    IF(thsp.Sufijo > 0 AND opx.Folio_Pro != th.Fol_folio, 4, IF(opx.Folio_Pro = th.Fol_folio, 1, IF(LEFT(thsp.Fol_folio, 2) = 'TR', 2, IF(LEFT(thsp.Fol_folio, 2) = 'WS', 3, IF(LEFT(thsp.Fol_folio, 2) = 'RB', 5, 0))))) AS es_ot,
                    thsp.fol_folio AS orden,
                    thsp.Sufijo AS sufijo,
                    IFNULL(IF(LEFT(thsp.Fol_folio, 2) = 'TR', CONCAT('(',cal.clave, ') ', cal.nombre), th.Cve_clte), '--') AS cliente, 
                    '' AS Folio_BackO,
                    IFNULL(th.Pick_Num, '--') AS orden_cliente, 
                    IFNULL(p.Descripcion, '--') AS prioridad, 
                    CASE LEFT(thsp.Fol_folio,2) 
                    WHEN 'WS' THEN CONCAT(e.DESCRIPCION, ' Surtiendo WS') 
                    ELSE IFNULL(e.DESCRIPCION, '--') 
                    END AS status, 
                    thsp.status AS status_pedido,
                    IFNULL(th.statusaurora, '') as statusaurora,
                    IFNULL(opx.Tipo, '') as TipoOT,
                    IFNULL((SELECT DISTINCT Fol_PedidoCon FROM td_consolidado WHERE Fol_Folio = thsp.Fol_folio LIMIT 1), 1) AS TieneOla,
                    IF(
                    th.TipoPedido = 'W', (SELECT DISTINCT GROUP_CONCAT(DISTINCT Fol_Folio SEPARATOR ', ') FROM td_consolidado WHERE Fol_PedidoCon = th.Fol_folio), 
                    IF(th.TipoPedido = 'W2', 
                    (SELECT DISTINCT GROUP_CONCAT(DISTINCT tws1.Fol_PedidoCon, '<br>',(SELECT GROUP_CONCAT(DISTINCT tws2.Fol_Folio) FROM t_consolidado tws2 WHERE tws2.Fol_PedidoCon = tws1.Fol_PedidoCon) SEPARATOR '<br><br>') AS foliows FROM t_consolidado tws1 WHERE tws1.Fol_Consolidado = th.Fol_folio)
                    , '')
                    ) AS folio_ws,
                    IF(th.TipoPedido = 'X', 1, 0) AS folio_xd,
                    IF(th.TipoPedido = 'X' AND IFNULL(th.Ship_Num, '') = '', 0, 1) AS folio_xd_asignable,
                    IFNULL(th.Ship_Num, '') as Ship_Num,
 
                    #IFNULL(c.Colonia, '') as Colonia,
                    '1' AS disponible,
                    IFNULL(IFNULL(r.cve_ruta, th.cve_ubicacion), '') as ruta_pedido,

                    #IFNULL(DATE_FORMAT(th.Fec_Entrada, '%d-%m-%Y'), '--') AS fecha_pedido, 
                    IF(IFNULL(th.Fec_Entrada, '0000-00-00 00:00:00') = '0000-00-00 00:00:00', DATE_FORMAT(th.Fec_Pedido, '%d-%m-%Y'), DATE_FORMAT(th.Fec_Entrada, '%d-%m-%Y')) AS fecha_pedido, 
                    thsp.Fec_Entrada AS Fec_Entrega,
                    thsp.Fec_Entrada AS Fec_Entrega_ord,
                    IFNULL(DATE_FORMAT(th.Fec_Pedido, '%d-%m-%Y'), '--') AS fecha_compromiso,
                    IFNULL(th.rango_hora, '--') AS rango_hora, 

                    IF(thsp.status != 'S', IFNULL(DATE_FORMAT(thsp.Hora_inicio, '%d-%m-%Y %H:%i:%s'), '--'), '--') AS fecha_ini, 
                    IF(thsp.status != 'S', IFNULL(DATE_FORMAT(thsp.Hora_Final, '%d-%m-%Y %H:%i:%s'), '--'), '--') AS fecha_fi, 
                    '0' as bloqueado,
                    IFNULL(thsp.Cve_Usuario, '') as usuario_pedido,
                    IFNULL(th.Observaciones, '') as observaciones,
                    IFNULL(DATE_FORMAT(th.Fec_Aprobado, '%d-%m-%Y | %H:%i:%s'), '') as fecha_aprobacion,
                    IFNULL(th.Tot_Factura, '') as total_factura,
                    IF(IFNULL(ef.th_embarque_folio, '') = '', 'N', 'S') AS tiene_foto,
                    IFNULL((SELECT GROUP_CONCAT(DISTINCT IFNULL(Proyecto, '') SEPARATOR ', ') FROM td_pedido WHERE Fol_folio = thsp.fol_folio AND IFNULL(Proyecto, '') != ''), '') AS proyectos,

                    {$sql_welldex_sub}

                    IFNULL(c_usuario.nombre_completo, '--') AS asignado 
                FROM th_subpedido thsp 
                    LEFT JOIN th_pedido th ON th.Fol_folio = thsp.fol_folio 
                    #LEFT JOIN c_cliente c ON c.Cve_Clte = th.Cve_Clte
                    LEFT JOIN t_ordenprod opx ON th.Fol_folio = opx.Folio_Pro
                    LEFT JOIN c_usuario ON c_usuario.cve_usuario = thsp.cve_usuario 
                    LEFT JOIN c_almacenp cal ON cal.id = th.statusaurora
                    LEFT JOIN cat_estados e ON e.ESTADO = thsp.status 
                    LEFT JOIN t_tiposprioridad p ON p.ID_Tipoprioridad = th.ID_Tipoprioridad  
                    LEFT JOIN t_ruta r ON r.ID_Ruta = th.ruta
                    LEFT JOIN td_ordenembarque tdo ON tdo.Fol_folio = th.Fol_folio
                    LEFT JOIN th_embarque_fotos ef ON CONVERT(ef.folio_pedido, CHAR) = CONVERT(tdo.Fol_folio, CHAR)
                  WHERE thsp.Fol_folio != '' 
                  {$sql_tipo_pedido2} {$sql_ruta2} {$sql_ciudad2} {$sql_fecha2}
                  {$sql_union_search}  {$status_subpedidos} 
                  {$sql_tipo_cross2}
                  AND (thsp.cve_almac = '{$almacen}'  {$sql_tr2})
                  GROUP BY thsp.Fol_folio,thsp.Sufijo 
                  ";
/*

                    LEFT JOIN ( 
                        SELECT 
                            V_ExistenciaGral.cve_articulo, 
                            c_ubicacion.CodigoCSD,
                            MIN(V_ExistenciaGral.cve_ubicacion) AS MIN 
                        FROM V_ExistenciaGral 
                            LEFT JOIN c_ubicacion ON V_ExistenciaGral.cve_ubicacion = c_ubicacion.idy_ubica 
                        GROUP BY cve_articulo 
                    ) Z ON Z.cve_articulo = tdsp.Cve_articulo 
*/
      $status_backorder = "";
      if (!empty($status)) 
      {
          $status_backorder = " AND b.Status = '{$status}' ";
      }

      $sql_union_backorder = "";
/*
      $sql_union_backorder = "
                SELECT DISTINCT
                    b.Folio_BackO AS id_pedido, 
                    b.Fol_Folio AS orden, 
                    0 AS sufijo, 
                    b.Folio_BackO AS Folio_BackO,
                    b.Pick_num AS orden_cliente, 
                    '' AS destinatario,
                    '--' AS prioridad, 
                    (SELECT DESCRIPCION FROM cat_estados WHERE ESTADO = b.Status) AS STATUS,
                    b.Status AS status_pedido,
                    IFNULL((SELECT DISTINCT Fol_PedidoCon FROM td_consolidado WHERE Fol_Folio = b.Fol_Folio), 1) AS TieneOla,
                    IFNULL(c.RazonSocial, '--') AS cliente, 
                    IFNULL(c.CalleNumero, '--') AS direccion, 
                    IFNULL(c.CodigoPostal, '--') AS dane, 
                    IFNULL(c.Ciudad, '--') AS ciudad, 
                    IFNULL(c.Estado, '--') AS estado, 
                    '--' AS cantidad_surtida,
                    '--' AS cantidad, 
                    '0' AS volumen,
                    '0' AS peso,
                    IFNULL(DATE_FORMAT(b.Fec_Entrega, '%d-%m-%Y'), '--') AS fecha_pedido, 
                    b.Fec_Entrega,
                    IFNULL(DATE_FORMAT(b.Fec_Pedido, '%d-%m-%Y'), '--') AS fecha_compromiso,
                    '--' AS fecha_ini, 
                    '--' AS fecha_fi, 
                    '--' AS TiempoSurtido,
                    '0%' AS surtido,
                    '--' AS asignado 
                FROM td_backorder bo
                    LEFT JOIN th_backorder b ON b.Folio_BackO = bo.Folio_BackO $status_backorder
                    LEFT JOIN V_ArticuloAlmacen a ON a.cve_articulo = bo.Cve_Articulo
                    LEFT JOIN c_cliente c ON c.Cve_Clte = b.Cve_Clte
                ";
*/
      if (!empty($status)) 
      {
        //$sql .= " AND o.status = '{$status}' ";
       // $sql_union .= " AND thsp.status = '{$status}' ";
        //$sql_union_backorder .= " AND b.status = '{$status}' ";
        //$sql .= " AND (o.status = '{$status}' AND o.Fol_folio NOT LIKE 'WS%') OR (o.status = 'O' AND IFNULL((SELECT DISTINCT Fol_PedidoCon FROM td_consolidado WHERE Fol_Folio = o.Fol_folio), 0) <> '0') ";
        //$sql2 .= " AND o.status = '{$status}' ";
      }
/*
      if (!empty($criterio) ) //&& !empty($filtro)
      {
            $sql .= " AND (o.Fol_folio like '%$criterio%' OR o.Pick_Num like '%$criterio%' OR p.Descripcion like '%$criterio%' OR c.RazonSocial like '%$criterio%' OR u.nombre_completo like '%$criterio%') ";

            //$sql_union .= " AND (tdsp.Fol_folio LIKE '%$criterio%' OR th.Pick_Num LIKE '%$criterio%' OR p.Descripcion LIKE '%$criterio%' OR c.RazonSocial LIKE '%$criterio%' OR c_usuario.nombre_completo LIKE '%$criterio%') ";

            //$sql_union_backorder .= "WHERE (b.Fol_Folio LIKE '%$criterio%' OR b.Pick_Num LIKE '%$criterio%' OR c.RazonSocial LIKE '%$criterio%')";

            $sql2 .= "AND o.Fol_folio like '%$criterio%' OR o.Pick_Num like '%$criterio%' OR c.RazonSocial like '%$criterio%' OR u.nombre_completo like '%$criterio%'";
      }
*/
      //$sql_union_backorder .= " ORDER BY Fec_Entrega DESC";
      //if($search != "")
      //  $sql_union_backorder .= " ORDER BY id_pedido DESC ";//$sql_union_backorder .= " ORDER BY id_pedido DESC ";
      //else 
        //$sql_union_backorder .= " ORDER BY Fec_Entrega_ord DESC ";
        //$sql_union_backorder .= " ORDER BY id_pedido DESC ";


        //if($ordenar_por_folio)
          $sql_union_backorder .= " ORDER BY orden ASC ";
        //else
        //  $sql_union_backorder .= " ORDER BY id_pedido DESC ";
        
      

      //$sql .= " AND o.status <> 'O' ";
      //$sql .= " GROUP BY o.Fol_folio ORDER BY o.id_pedido DESC";
      $sql .= " GROUP BY o.Fol_folio";
    //{$status}

      $status_search = 'A';
      if($status == 'K') $status_search = 'K'; 
      if($status == 'T') $status_search = 'T'; 
      $sql .= " ) AS res1 WHERE res1.status_pedido = '$status_search' 
      AND (SELECT COUNT(*) FROM td_pedido WHERE Fol_folio = res1.orden) != (SELECT COUNT(*) num FROM th_subpedido th, td_subpedido td WHERE th.fol_folio = res1.orden AND th.fol_folio = td.fol_folio AND th.status = 'S' 
      AND th.Sufijo = td.Sufijo) OR (res1.status_pedido = 'S' AND res1.sufijo = 0 AND res1.orden IN (SELECT Fol_folio FROM th_backorder)) 

      UNION ".$sql_union."  ".$sql_union_backorder;
      //#este = 'S' es fijo no variable
//      #OR (res1.status_pedido = '{$status}' AND res1.sufijo = 0 AND res1.orden IN (SELECT Fol_folio FROM th_backorder)) 
//     #AND (res1.orden NOT IN (SELECT fol_folio FROM th_subpedido)) 


      //$sql2 .= " GROUP BY o.Fol_folio  ORDER BY o.id_pedido DESC";
      //$sql2 .= " GROUP BY Fol_folio  ORDER BY Fec_Entrega DESC";

      //$sql_count = "Select count(*) as x from (".$sql2.") y";
/*
      if($tipopedido == 'W2')
        $sql = "SELECT 
o.id_pedido AS id_pedido, 
0 AS es_ot,
tc.Fol_Consolidado AS orden, 
0  AS sufijo, 
IFNULL(IF(o.TipoPedido = 'R' OR o.TipoPedido = 'RI', (SELECT CONCAT('(',cal.clave, ') ', cal.nombre) FROM c_almacenp cal WHERE cal.id = o.statusaurora), o.Cve_clte), '--') AS cliente,
'' AS Folio_BackO,
IFNULL(o.Pick_Num, '--') AS orden_cliente, 
IFNULL(p.Descripcion, '--') AS prioridad, 
CASE
WHEN LEFT(o.Fol_folio,2) = 'WS' THEN CONCAT(e.DESCRIPCION, ' Surtiendo WS') 
WHEN LEFT(IFNULL((SELECT Folio_BackO FROM th_backorder WHERE Fol_Folio = o.Fol_folio LIMIT 1), ''), 2) = 'BO' THEN (SELECT DESCRIPCION FROM cat_estados WHERE ESTADO = 'A')
ELSE IFNULL(e.DESCRIPCION, '--') 
END AS STATUS, 
'A' AS status_pedido,
o.statusaurora, 
'WS2' AS TipoOT,
IFNULL((SELECT DISTINCT Fol_PedidoCon FROM td_consolidado WHERE Fol_Folio = o.Fol_folio LIMIT 1), 1) AS TieneOla,
IF(LEFT(o.Fol_folio,2) = 'WS', (SELECT DISTINCT GROUP_CONCAT(DISTINCT Fol_PedidoCon SEPARATOR ', ') FROM t_consolidado WHERE Fol_Consolidado = tc.Fol_Consolidado), '') AS folio_ws,
IF(LEFT(o.Fol_folio,2) = 'XD', 1, 0) AS folio_xd,
IF(LEFT(o.Fol_folio,2) = 'XD' AND IFNULL(o.Ship_Num, '') = '', 0, 1) AS folio_xd_asignable,
IFNULL(o.Ship_Num, '') AS Ship_Num,
'1'AS disponible,
'' AS ruta_pedido,
IF(IFNULL(o.Fec_Entrada, '0000-00-00 00:00:00') = '0000-00-00 00:00:00', DATE_FORMAT(o.Fec_Pedido, '%d-%m-%Y'), DATE_FORMAT(o.Fec_Entrada, '%d-%m-%Y')) AS fecha_pedido, 
IFNULL(DATE_FORMAT(o.Fec_Entrega, '%d-%m-%Y'), '--') AS Fec_Entrega, 
o.id_pedido AS Fec_Entrega_ord,
IFNULL(DATE_FORMAT(o.Fec_Pedido, '%d-%m-%Y'), '--') AS fecha_compromiso, 
IFNULL(o.rango_hora, '--') AS rango_hora, 
'' AS fecha_ini, 
'' AS fecha_fi, 
IFNULL(o.BanEmpaque, 0) AS bloqueado,
'' AS asignado 
FROM t_consolidado tc
LEFT JOIN th_pedido o ON o.Fol_folio = tc.Fol_PedidoCon
LEFT JOIN t_tiposprioridad p ON p.ID_Tipoprioridad = o.ID_Tipoprioridad 
LEFT JOIN cat_estados e ON e.ESTADO = o.status 
WHERE o.cve_almac = '{$almacen}'
GROUP BY orden
";
*/
$utf8Sql = \db()->prepare("SET NAMES 'utf8mb4';");
$utf8Sql->execute();

      if (!($res_count = mysqli_query($conn, $sql))) {
          echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
      }

      $count = mysqli_num_rows($res_count);

      $sql .= " limit ".$start.", ".$limit;
      
      

      $data = Capsule::select(Capsule::raw($sql));
      
//       echo var_dump($data);
//       die();

      //$data_count = Capsule::select(Capsule::raw($sql_count));


//*********************************************************************************
        $sql3 = "SELECT COUNT(o.Fol_folio) num_pedidos
                FROM th_pedido o   
                WHERE o.Activo = 1 and o.status = '{$status}'";
        if (!($res3 = mysqli_query($conn, $sql3))) {
            echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
        }
        $row = mysqli_fetch_array($res3);
        $count_pedidos = $row['num_pedidos'];

        if($status == 'A')
        {
          $sql3 = "SELECT COUNT(o.fol_folio) num_pedidos_bo
                  FROM th_backorder o   
                  WHERE o.Status = 'A'";
          if (!($res3 = mysqli_query($conn, $sql3))) {
              echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
          }
          $row = mysqli_fetch_array($res3);
          $count_pedidos += $row['num_pedidos_bo'];
        }



        $sql4 = "SELECT COUNT(o.Fol_folio) num_pedidos
                FROM th_pedido o   
                WHERE o.Activo = 1 and o.status = 'S'";
        if (!($res4 = mysqli_query($conn, $sql4))) {
            echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
        }
        $row = mysqli_fetch_array($res4);
        $count_asignados = $row['num_pedidos'];


        if($status)
        $sql4 = "SELECT COUNT(o.fol_folio) num_subpedidos
                FROM th_subpedido o   
                WHERE o.status = '{$status}'";
        else
        $sql4 = "SELECT COUNT(o.fol_folio) num_subpedidos
                FROM th_subpedido o ";
        if (!($res4 = mysqli_query($conn, $sql4))) {
            echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
        }
        $row = mysqli_fetch_array($res4);
        $count_subpedidos = $row['num_subpedidos'];


        $sql5 = "SELECT COUNT(o.Fol_folio) num_pedidos
                FROM th_pedido o   
                WHERE o.Activo = 1 and o.status = 'C'";
        if (!($res5 = mysqli_query($conn, $sql5))) {
            echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
        }
        $row = mysqli_fetch_array($res5);
        $count_empaque = $row['num_pedidos'];
//*********************************************************************************

      $response = new \stdClass;
      $response->data = [];

      //$response->query["sql"] = $sql;
      //$response->query["sql_count"] = $sql_count;

      //$response->data["sql_count"] = $sql_count;

      //$count = count($data);
      //$count = $data_count[0]->x;

      foreach ($data as $row) 
      {
        //   $date1 = new DateTime("2015-02-14");
        //  $date2 = new DateTime("2015-02-16");
        // $diff = $date1->diff($date2);
        // will output 2 days
        //echo $diff->days . ' days ';
        //$date_tiempo = strtotime($row->fecha_fi)-strtotime($row->fecha_ini);
        /*$fecha1 = new DateTime($row->fecha_ini);//fecha inicial
        $fecha2 = new DateTime($row->fecha_fi);//fecha de cierre

        $intervalo = $fecha1->diff($fecha2);

        $diferencia = $intervalo->format('%Y %m %d %H %i %s');*/
        $cliente = $row->cliente; $folio_pedido = $row->orden;
        $sql_cliente = "SELECT DISTINCT IFNULL(RazonComercial, RazonSocial) AS RazonComercial FROM c_cliente WHERE Cve_Almacenp = (SELECT Cve_Almac FROM th_pedido where Fol_folio = '$folio_pedido' AND SUBSTRING_INDEX(Fol_folio, '_', -1) NOT IN (SELECT Folio FROM Recarga)) AND Cve_Clte = '$cliente'";
        if (!($res5 = mysqli_query($conn, $sql_cliente))) {
            echo "Falló la preparación: (" . mysqli_error($conn) . ") ";
        }
        $row5 = mysqli_fetch_array($res5);
        if(mysqli_num_rows($res5))
          $RazonComercial = $row5['RazonComercial'];
        else 
          $RazonComercial = $cliente;

      if($instancia == 'welldex')
      {
        $response->data[] = [
          'num_registros' => $row->num_registros,
          'id' => $row->id_pedido,
          'TieneOla' => $row->TieneOla,
          //'destinatario' => $row->destinatario,
          'orden' => $row->orden,
          'sufijo' => $row->sufijo,
          'Folio_BackO' => $row->Folio_BackO,
          'orden_cliente' => $row->orden_cliente,
          'prioridad' => $row->prioridad,
          'status' => $row->status,
          'entregado' => '',
          'status_pedido' => $row->status_pedido,
          'statusaurora' => $row->statusaurora,
          'TipoOT' => $row->TipoOT,
          'EsTipoLP' => $row->EsTipoLP,
          //'num_articulos' => $row->num_articulos,
          //'cantidad_surtida' => $row->cantidad_surtida,
          //'cantidad' => $row->cantidad, metavpad, OPERON ORIGINS, ATLANTIS METAVERSE
          //'articulo_ot' => $row->articulo_ot,
          'es_ot' => $row->es_ot,
          'folio_ws' => $row->folio_ws,
          'folio_xd' => $row->folio_xd,
          'folio_xd_asignable' => $row->folio_xd_asignable,
          'Ship_Num' => $row->Ship_Num,
          'disponible' => $row->disponible,
          'ruta_pedido' => $row->ruta_pedido,
          //'volumen' => $row->volumen,
          //'peso' => round($row->peso,3),
          'fecha_pedido' => $row->fecha_pedido,
          'fecha_compromiso' => $row->fecha_compromiso,
          'rango_hora' => $row->rango_hora,
          'fecha_ini' => $row->fecha_ini,
          'fecha_fi' => $row->fecha_fi,
          //'TiempoSurtido' => $row->TiempoSurtido,  
          //'surtido' => $row->surtido,

          //'cliente' =>  $row->Cve_clte, 
          //'cliente' =>  $row->cliente, 
          'cliente' => $RazonComercial,
          //'direccion' =>  $row->direccion, 
          //'dane' => $row->dane,
          //'ciudad' =>  $row->ciudad, 
          //'estado' =>  $row->estado, 

          //'ubicacion_reabasto' =>  $row->ubicacion_reabasto, 
          //'Colonia' =>  $row->Colonia, 
          'usuario_pedido' =>  $row->usuario_pedido ,
          'tiene_foto' => $row->tiene_foto,
          'asignado' =>  $row->asignado,
          'proyectos' =>  $row->proyectos,
          

          'observaciones' => $row->observaciones,
          'fecha_aprobacion' => $row->fecha_aprobacion,
          'total_factura' => $row->total_factura,

          'Ref_Wel' => $row->Ref_Wel,
          'Ref_Imp' => $row->Ref_Imp,
          'Pedimento' => $row->Pedimento,
          'Factura_Vta' => $row->Factura_Vta,
          'Ped_Imp' => $row->Ped_Imp

          //'sql' => $sql
        ];
      }
      else if($instancia == 'iberofarmacos')
      {
        $response->data[] = [
          'num_registros' => $row->num_registros,
          'id' => $row->id_pedido,
          'TieneOla' => $row->TieneOla,
          //'destinatario' => $row->destinatario,
          'orden' => $row->orden,
          'sufijo' => $row->sufijo,
          'Folio_BackO' => $row->Folio_BackO,
          'orden_cliente' => $row->orden_cliente,
          'prioridad' => $row->prioridad,
          'status' => $row->status,
          'entregado' => '',
          'status_pedido' => $row->status_pedido,
          'statusaurora' => $row->statusaurora,
          'TipoOT' => $row->TipoOT,
          'EsTipoLP' => $row->EsTipoLP,
          //'num_articulos' => $row->num_articulos,
          //'cantidad_surtida' => $row->cantidad_surtida,
          //'cantidad' => $row->cantidad, metavpad, OPERON ORIGINS, ATLANTIS METAVERSE
          //'articulo_ot' => $row->articulo_ot,
          'es_ot' => $row->es_ot,
          'folio_ws' => $row->folio_ws,
          'folio_xd' => $row->folio_xd,
          'folio_xd_asignable' => $row->folio_xd_asignable,
          'Ship_Num' => $row->Ship_Num,
          'disponible' => $row->disponible,
          'ruta_pedido' => $row->ruta_pedido,
          //'volumen' => $row->volumen,
          //'peso' => round($row->peso,3),
          'fecha_pedido' => $row->fecha_pedido,
          'fecha_compromiso' => $row->fecha_compromiso,
          'rango_hora' => $row->rango_hora,
          'fecha_ini' => $row->fecha_ini,
          'fecha_fi' => $row->fecha_fi,
          //'TiempoSurtido' => $row->TiempoSurtido,  
          //'surtido' => $row->surtido,

          //'cliente' =>  $row->Cve_clte, 
          //'cliente' =>  $row->cliente, 
          'cliente' => $RazonComercial,
          //'direccion' =>  $row->direccion, 
          //'dane' => $row->dane,
          //'ciudad' =>  $row->ciudad, 
          //'estado' =>  $row->estado, 
          'observaciones' => $row->observaciones,
          'fecha_aprobacion' => $row->fecha_aprobacion,
          'total_factura' => $row->total_factura,

          //'ubicacion_reabasto' =>  $row->ubicacion_reabasto, 
          //'Colonia' =>  $row->Colonia, 
          'usuario_pedido' =>  $row->usuario_pedido ,
          'tiene_foto' => $row->tiene_foto,
          'proyectos' =>  $row->proyectos,
          'asignado' =>  $row->asignado 
          
          //'sql' => $sql
        ];
      }
      else
      {
        $response->data[] = [
          'num_registros' => $row->num_registros,
          'id' => $row->id_pedido,
          'TieneOla' => $row->TieneOla,
          //'destinatario' => $row->destinatario,
          'orden' => $row->orden,
          'sufijo' => $row->sufijo,
          'Folio_BackO' => $row->Folio_BackO,
          'orden_cliente' => $row->orden_cliente,
          'prioridad' => $row->prioridad,
          'status' => $row->status,
          'entregado' => '',
          'status_pedido' => $row->status_pedido,
          'statusaurora' => $row->statusaurora,
          'TipoOT' => $row->TipoOT,
          'EsTipoLP' => $row->EsTipoLP,
          //'num_articulos' => $row->num_articulos,
          //'cantidad_surtida' => $row->cantidad_surtida,
          //'cantidad' => $row->cantidad, metavpad, OPERON ORIGINS, ATLANTIS METAVERSE
          //'articulo_ot' => $row->articulo_ot,
          'es_ot' => $row->es_ot,
          'folio_ws' => $row->folio_ws,
          'folio_xd' => $row->folio_xd,
          'folio_xd_asignable' => $row->folio_xd_asignable,
          'Ship_Num' => $row->Ship_Num,
          'disponible' => $row->disponible,
          'ruta_pedido' => $row->ruta_pedido,
          //'volumen' => $row->volumen,
          //'peso' => round($row->peso,3),
          'fecha_pedido' => $row->fecha_pedido,
          'fecha_compromiso' => $row->fecha_compromiso,
          'rango_hora' => $row->rango_hora,
          'fecha_ini' => $row->fecha_ini,
          'fecha_fi' => $row->fecha_fi,
          //'TiempoSurtido' => $row->TiempoSurtido,  
          //'surtido' => $row->surtido,

          //'cliente' =>  $row->Cve_clte, 
          //'cliente' =>  $row->cliente, 
          'cliente' => $RazonComercial,
          //'direccion' =>  $row->direccion, 
          //'dane' => $row->dane,
          //'ciudad' =>  $row->ciudad, 
          //'estado' =>  $row->estado, 
          'observaciones' => $row->observaciones,
          'fecha_aprobacion' => $row->fecha_aprobacion,
          'total_factura' => $row->total_factura,

          //'ubicacion_reabasto' =>  $row->ubicacion_reabasto, 
          //'Colonia' =>  $row->Colonia, 
          'usuario_pedido' =>  $row->usuario_pedido ,
          'tiene_foto' => $row->tiene_foto,
          'proyectos' =>  $row->proyectos,
          'asignado' =>  $row->asignado 
          
          //'sql' => $sql
        ];
      }
/*
        if ($row->peso !='')
        {
          $response->pesototal+= $row->peso; 
        }
        if ($row->volumen !='')
        {
          $response->volumentotal+= $row->volumen; 
        }
        if ($row->orden !='')
        {
          $response->totalpedidos++; 
        }
*/
      }
      //$response->data = array_slice($response->data, $start, $limit);
        $response->pesototal = $count_asignados; // Se cambió a Pedidos Asignados pero se usa la variable peso total ya que estaba ahí
        $response->totalpedidos = $count_pedidos;
        $response->totalsubpedidos = $count_subpedidos;
        $response->volumentotal = $count_empaque;

      if ($count >0) 
      {
        $total_pages = ceil($count/$limit);
      }
      else 
      {
        $total_pages = 0;
      }

      if ($page > $total_pages) 
      {
        $page = $total_pages;
      }

      $sql = str_replace(["\n", "\t", "  "], ['',' ', ''], $sql);
      $response->from = ($start == 0 ? 1 : $start) ;
      $response->to = ($start + $limit);
      $response->page = $page;
      $response->total_pages = $total_pages;
      $response->total = $count;
      $response->status = 200;
      $response->sql = $sql;
      $response->sql_search = $sql_search;

      ob_clean();
      header('Content-Type: application/json; charset=utf-8');
      //echo $sql;
      //echo " ------------- ";
      //echo $sql2;
      mysqli_close($conn);
      echo json_encode($response,JSON_PRETTY_PRINT);exit;   
    }



}
 