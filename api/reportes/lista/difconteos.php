<?php 
include '../../../config.php'; 

$sql = 
  /*
  "
    SELECT th_inventario.ID_Inventario AS inventario,
                          DATE_FORMAT(th_inventario.Fecha, '%d-%m-%Y') AS fecha,

                          (CASE
                              WHEN t_ubicacioninventario.cve_ubicacion IS NOT NULL THEN 
                                (SELECT desc_ubicacion FROM tubicacionesretencion WHERE t_ubicacioninventario.cve_ubicacion = tubicacionesretencion.cve_ubicacion)
                              WHEN t_ubicacioninventario.idy_ubica IS NOT NULL THEN
                                (SELECT c_ubicacion.CodigoCSD FROM c_ubicacion WHERE c_ubicacion.idy_ubica = t_ubicacioninventario.idy_ubica)
                              ELSE '--'
                          END) as ubicacion,

                          #c_ubicacion.CodigoCSD AS ubicacion,
                          t_invpiezas.cve_articulo AS clave_articulo,
                          c_articulo.des_articulo AS descrt_invpiezascion_articulo,
                          IFNULL(c_lotes.LOTE, '--') AS lote,
                          IFNULL(c_lotes.CADUCIDAD, '--') AS caducidad,
                          '--' AS numero_serie,
                          t_invpiezas.ExistenciaTeorica AS stock_teorico,
                          t_invpiezas.Cantidad AS stock_fisico,
                          (t_invpiezas.Cantidad - t_invpiezas.ExistenciaTeorica) AS diferencia,
                          t_invpiezas.NConteo AS conteo,
                          c_usuario.nombre_completo AS usuario,
                          'Piezas' AS unidad_medida,
                          (SELECT (MAX(NConteo) - 1) FROM t_invpiezas WHERE ID_Inventario = th_inventario.ID_Inventario) AS total_conteo,
                          (SELECT MAX(NConteo) FROM t_invpiezas WHERE ID_Inventario = th_inventario.ID_Inventario) as conteoMax,
                          th_inventario.Status As status_inventario
                  FROM t_invpiezas 
                  LEFT JOIN th_inventario ON th_inventario.ID_Inventario = t_invpiezas.ID_Inventario
                  LEFT JOIN c_ubicacion ON c_ubicacion.idy_ubica = t_invpiezas.idy_ubica
                  LEFT JOIN c_articulo ON c_articulo.cve_articulo = t_invpiezas.cve_articulo
                  LEFT JOIN c_lotes ON c_lotes.LOTE = t_invpiezas.cve_lote AND c_lotes.cve_articulo = t_invpiezas.cve_articulo
                 
                  LEFT JOIN c_usuario ON c_usuario.cve_usuario = t_invpiezas.cve_usuario

                  LEFT JOIN t_ubicacioninventario ON t_ubicacioninventario.ID_Inventario = t_invpiezas.ID_Inventario

                  WHERE t_invpiezas.NConteo > 0
                  
                  AND t_invpiezas.Cantidad <> t_invpiezas.ExistenciaTeorica
                  AND t_invpiezas.NConteo = (SELECT MAX(NConteo) FROM t_invpiezas WHERE ID_Inventario = th_inventario.ID_Inventario)
                  ORDER BY th_inventario.ID_Inventario DESC
";
*/
  "SELECT * FROM(SELECT 
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
                
                    IFNULL((
                        SELECT 
                        SUM(ABS(ExistenciaTeorica - Cantidad))
                        FROM t_invpiezas
                        WHERE ID_Inventario = inv.ID_Inventario
                        AND t_invpiezas.NConteo = (Select max(t_invpiezas.NConteo) from t_invpiezas where t_invpiezas.ID_Inventario = inv.ID_Inventario)
                            GROUP BY ID_Inventario
                    
                    ), '--') AS diferencia,
                    
                  ROUND(
                    IFNULL((
                        SELECT 
                        ((SUM(Cantidad))*100) / SUM(ExistenciaTeorica) as Porcentaje
                        FROM t_invpiezas
                        WHERE ID_Inventario = inv.ID_Inventario
                        AND t_invpiezas.NConteo = (Select max(t_invpiezas.NConteo) from t_invpiezas where t_invpiezas.ID_Inventario = inv.ID_Inventario)
                            GROUP BY ID_Inventario
                    ), '--'), 2) AS porcentaje,
                    
                    
                    'Físico' AS tipo, 

                    IFNULL((SELECT 
                        nombre_completo 
                    FROM c_usuario 
                    WHERE id_user = 
                        (SELECT cve_supervisor FROM t_conteoinventario WHERE ID_Inventario = inv.ID_Inventario LIMIT 1)
                    ), '--') AS supervisor,

                    IFNULL((
                        SELECT 
                        IF(MAX(NConteo) < 1, 0, (MAX(NConteo))) conteo
                        FROM t_invpiezas
                        WHERE ID_Inventario = consecutivo
                            GROUP BY ID_Inventario
                        
                    )-1, '--') AS conteos_totales  
                FROM th_inventario inv
                    LEFT JOIN c_almacenp almac ON inv.cve_almacen = CONVERT(almac.clave USING UTF8)
                    LEFT JOIN c_almacen ON inv.cve_zona = c_almacen.cve_almac
                    LEFT JOIN V_Inventario v_inv ON v_inv.ID_Inventario = inv.ID_Inventario
              		LEFT JOIN t_invpiezas ON t_invpiezas.ID_Inventario = inv.ID_Inventario
                WHERE inv.Activo = 1  
              	AND inv.`Status` = 'T'
              	AND t_invpiezas.Cantidad <> t_invpiezas.ExistenciaTeorica
              	AND (
                        SELECT 
                        SUM(ABS(ExistenciaTeorica - Cantidad))
                        FROM t_invpiezas
                        WHERE ID_Inventario = inv.ID_Inventario
                        AND t_invpiezas.NConteo = (Select max(t_invpiezas.NConteo) from t_invpiezas where t_invpiezas.ID_Inventario = inv.ID_Inventario)
                            GROUP BY ID_Inventario
                    
                    ) > 0
                GROUP BY inv.ID_Inventario) W
              ORDER BY consecutivo DESC";
$res = getArraySQL($sql); 
echo json_encode($res); 

function getArraySQL($sql)
{
  $conexion = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
  mysqli_set_charset($conexion, "utf8");
  if(!$result = mysqli_query($conexion, $sql)) 
  {
    echo "Falló la preparación: (" . mysqli_error($conexion) . ") ";;
  }
  $rawdata = array();
  $i = 0;
  while($row = mysqli_fetch_assoc($result))
  {
    $rawdata[$i] = $row;
    $i++;
  }
  mysqli_close($conexion);
  return $rawdata;
}