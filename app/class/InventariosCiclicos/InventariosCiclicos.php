<?php

namespace InventariosCiclicos;

class InventariosCiclicos {

    const TABLE = 'th_pedido';
    const TABLE_D = 'td_pedido';
    var $identifier;

    public function __construct( $Fol_folio = false, $key = false ) {

        if( $Fol_folio ) {
            $this->Fol_folio = (int) $Fol_folio;
        }

        if($key) {

            $sql = sprintf('
          SELECT
            Fol_folio
          FROM
            %s
          WHERE
            Fol_folio = ?
        ',
                           self::TABLE
                          );

            $sth = \db()->prepare($sql);
            $sth->setFetchMode(\PDO::FETCH_CLASS, '\InventariosCiclicos\InventariosCiclicos');
            $sth->execute(array($key));

            $Fol_folio = $sth->fetch();

            $this->Fol_folio = $Fol_folio->Fol_folio;

        }

    }

    private function load() {

        $sql = sprintf('
        SELECT
          *
        FROM
          %s
        WHERE
          Fol_folio = ?
      ',
                       self::TABLE
                      );

        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\InventariosCiclicos\InventariosCiclicos' );
        $sth->execute( array( $this->Fol_folio ) );

        $this->data = $sth->fetch();

    }
    function loadDetalleCount($codigo){
        $sql="
            SELECT COUNT(cve_articulo) AS total FROM det_planifica_inventario where ID_PLAN = {$codigo}";

        $sth = \db()->prepare($sql);

        $sth->execute();

        return $sth->fetch()['total'];
    }




    function saveUser($id, $usuario){
        $sqlConteo = "SELECT MAX(NConteo) AS conteo FROM t_conteoinventariocicl WHERE ID_PLAN = {$id};";
        $sth = \db()->prepare($sqlConteo);
        $sth->execute();
        $conteo = intval($sth->fetch()['conteo']) + 1;
        $sql = "INSERT  INTO t_conteoinventariocicl (ID_PLAN, NConteo, cve_usuario, Status, Activo)
                        VALUES ({$id}, {$conteo}, '{$usuario}', 'A', 1);";
        $sth = \db()->prepare($sql);
        $result = $sth->execute();
        $this->saveExistencia($id, $usuario);
        return  $result;  

    }


    function saveExistencia($id = false, $usuario = false){
        $idSQL = $id === false  ? "(SELECT MAX(ID_PLAN) FROM det_planifica_inventario)" : $id;
        $usuario = !$usuario ? '' : $usuario;
        $sql = "SELECT cve_articulo, cve_ubicacion, cve_lote, Existencia as cantidad FROM V_ExistenciaGralProduccion WHERE Existencia > 0 AND cve_ubicacion IN (SELECT COALESCE(idy_ubica) FROM t_invpiezasciclico WHERE ID_PLAN = {$idSQL}) AND cve_articulo IN (SELECT COALESCE(cve_articulo) FROM t_invpiezasciclico WHERE ID_PLAN = {$idSQL})";
        $sth = \db()->prepare($sql);
        $sth->execute();
        $data = $sth->fetchAll();

        $sql = "";

        foreach ($data as $e){
            extract($e);
            $sql .= "INSERT INTO t_invpiezasciclico(ID_PLAN, NConteo, cve_articulo, cve_lote, idy_ubica, Cantidad, ExistenciaTeorica, cve_usuario, fecha, Activo) VALUES({$idSQL}, (SELECT MAX(NConteo) FROM t_conteoinventariocicl WHERE ID_PLAN = {$idSQL}), '$cve_articulo',  '$cve_lote','$cve_ubicacion', '0', '$cantidad', '$usuario', NOW(), 1); ";
        }

        $sth = \db()->prepare($sql);
        $sth->execute();        
    }


    private function loadChangeStatus() {

        $sql = sprintf('
        SELECT
          status
        FROM
          %s
        WHERE
          ID_Pedido = ?
      ',
                       self::TABLE
                      );

        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\InventariosCiclicos\InventariosCiclicos' );
        $sth->execute( array( $this->ID_Pedido ) );

        $this->data = $sth->fetch();

    }

    function getStatus() {

        $sql = '
        SELECT
          *
        FROM
          cat_estados 
      ';

        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\InventariosCiclicos\InventariosCiclicos' );
        $sth->execute( array( ESTADO ) );

        return $sth->fetchAll();

    }

    function getAll() {

        $sql = '
        SELECT
          *
        FROM
          ' . self::TABLE . '
      ';

        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\InventariosCiclicos\InventariosCiclicos' );
        $sth->execute( array( Fol_folio ) );

        return $sth->fetchAll();

    }

    function loadDetalleCi($codigo, $start, $limit) {
        $sql="SELECT
                        c.des_almac zona,(CASE WHEN v.tipo = 'area' AND v.cve_ubicacion IS NOT NULL THEN (SELECT desc_ubicacion AS ubicacion FROM tubicacionesretencion WHERE cve_ubicacion = v.cve_ubicacion) WHEN v.tipo = 'ubicacion' AND v.cve_ubicacion IS NOT NULL THEN (SELECT u.CodigoCSD AS ubicacion FROM c_ubicacion u WHERE u.idy_ubica = v.cve_ubicacion)ELSE '--'END) AS ubicacion,
                        ifnull(c_articulo.cve_articulo, '--') as clave,
                        ifnull(c_articulo.des_articulo, '--') as descripcion,
                        if(v.cve_lote = '', '--', v.cve_lote) as lote,
                        ifnull(c_lotes.CADUCIDAD, '--') as caducidad,
                        ifnull(c_serie.numero_serie, '--') as serie,
                        (SELECT MAX(NConteo) FROM t_conteoinventariocicl WHERE ID_PLAN ={$codigo}) as conteo,
                        ifnull(v.Existencia, 0) as stockTeorico,
                        (SELECT Cantidad FROM t_invpiezasciclico WHERE ID_PLAN = {$codigo} AND NConteo = (SELECT conteo) AND cve_articulo = v.cve_articulo AND idy_ubica = v.cve_ubicacion and cve_lote = i.cve_lote GROUP BY cve_articulo) as stockFisico,
                        (SELECT stockFisico) - (SELECT stockTeorico) as diferencia,
                        (SELECT nombre_completo FROM c_usuario WHERE cve_usuario = (SELECT cve_usuario FROM t_conteoinventariocicl WHERE ID_PLAN = {$codigo} ORDER BY id DESC LIMIT 1)) as usuario,
                        'Piezas' as unidad_medida,
                        CONCAT(v.tipo, '|', v.cve_ubicacion, '|', v.cve_articulo, '|', {$codigo}, '|', (SELECT conteo), '|', (SELECT cve_usuario FROM t_conteoinventariocicl WHERE NConteo = (SELECT conteo) AND ID_PLAN = {$codigo})) AS id
                from V_ExistenciaGralProduccion v
                left join c_articulo on c_articulo.cve_articulo = v.cve_articulo
            /*    left join c_lotes on c_lotes.LOTE = v.cve_lote */
                left join c_serie on c_serie.clave_articulo = c_articulo.cve_articulo
                INNER join V_ExistenciaFisicaCiclica i on i.cve_articulo = v.cve_articulo AND i.idy_ubica=v.cve_ubicacion and i.ID_PLAN = {$codigo}
                LEFT JOIN c_almacen AS c ON c.cve_almac=(SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = v.cve_ubicacion) 
                LEFT JOIN c_lotes on c_lotes.LOTE = i.cve_lote
                where v.Existencia > 0
                AND v.tipo='ubicacion'
                /*group by v.cve_articulo,v.cve_ubicacion,v.cve_lote*/
                LIMIT {$start},{$limit};
            ";




        $sth = \db()->prepare($sql);
        $sth->execute();
        return $sth->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    
    function loadDetalleCid($codigo, $start, $limit) {

        $sql = "SELECT
                    c.des_almac zona,(CASE WHEN v.tipo = 'area' AND v.cve_ubicacion IS NOT NULL THEN (SELECT desc_ubicacion AS ubicacion FROM tubicacionesretencion WHERE cve_ubicacion = v.cve_ubicacion) WHEN v.tipo = 'ubicacion' AND v.cve_ubicacion IS NOT NULL THEN (SELECT u.CodigoCSD AS ubicacion FROM c_ubicacion u WHERE u.idy_ubica = v.cve_ubicacion)ELSE '--'END) AS ubicacion,
                    IFNULL(c_articulo.cve_articulo, '--') AS clave,
                    IFNULL(c_articulo.des_articulo, '--') AS descripcion,
                    IF(v.cve_lote = '', '--', v.cve_lote) AS lote,
                    IFNULL(c_lotes.CADUCIDAD, '--') AS caducidad,
                    #IFNULL(c_serie.numero_serie, '--') as serie,
                    (SELECT MAX(NConteo) FROM t_conteoinventariocicl WHERE ID_PLAN = {$codigo}) AS conteo,
                    IFNULL(v.Existencia, 0) AS stockTeorico,
                    (SELECT Cantidad FROM t_invpiezasciclico WHERE ID_PLAN = {$codigo} AND NConteo = (SELECT conteo) AND cve_articulo = v.cve_articulo AND idy_ubica = v.cve_ubicacion AND cve_lote = v.cve_lote GROUP BY cve_articulo) AS stockFisico,
                    (SELECT stockFisico) - (SELECT stockTeorico) AS diferencia,
                    (SELECT nombre_completo FROM c_usuario WHERE cve_usuario = (SELECT DISTINCT cve_usuario FROM t_conteoinventariocicl WHERE ID_PLAN = {$codigo} ORDER BY id DESC LIMIT 1)) AS usuario,
                    'Piezas' AS unidad_medida,
                    CONCAT(v.tipo, '|', v.cve_ubicacion, '|', v.cve_articulo, '|', 1, '|', (SELECT conteo), '|', (SELECT DISTINCT cve_usuario FROM t_conteoinventariocicl WHERE NConteo = (SELECT conteo) AND ID_PLAN = {$codigo})) AS id
            FROM V_ExistenciaGralProduccion v
                LEFT JOIN c_articulo ON c_articulo.cve_articulo = v.cve_articulo
                -- LEFT JOIN c_lotes on c_lotes.LOTE = v.cve_lote
                #LEFT JOIN c_serie on c_serie.cve_articulo = c_articulo.cve_articulo
                #inner JOIN V_ExistenciaFisicaCiclica i on i.cve_articulo = v.cve_articulo AND i.idy_ubica=v.cve_ubicacion and i.ID_PLAN = 1
                INNER JOIN t_invpiezasciclico ic ON ic.cve_articulo = v.cve_articulo AND ic.ID_PLAN = {$codigo}
                LEFT JOIN c_almacen AS c ON c.cve_almac=(SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = v.cve_ubicacion) 
                LEFT JOIN c_lotes ON c_lotes.LOTE = v.cve_lote
            WHERE v.Existencia > 0 AND v.tipo='ubicacion' 
            GROUP BY v.cve_articulo,v.cve_ubicacion,v.cve_lote";

        $sth = \db()->prepare($sql);
        $sth->execute();
        return $sth->fetchAll(\PDO::FETCH_ASSOC);
    }

    function __get( $key ) {

        switch($key) {
            case 'Fol_folio':
                $this->load();
                return @$this->data->$key;
            default:
                return $this->key;
        }

    }

    function __getChangeStatus( $key ) {

        switch($key) {
            case 'ID_Pedido':
                $this->loadChangeStatus();
                return @$this->data->$key;
            default:
                return $this->key;
        }

    }

    function __getDetalle( $key ) {

        switch($key) {
            case 'Fol_folio':
                $this->loadDetalle();
                return @$this->dataDetalle->$key;
            default:
                return $this->key;
        }

    }
    function existe($_post){
        $arr = array();

        //$articulo = $item['articulo'];

        foreach ($_post["articulos"] as $item) {
            $articulo = $item['articulo'];
            $sql = "SELECT 
                        a.cve_articulo,
                        a.des_articulo 
                    FROM cab_planifica_inventario ca, det_planifica_inventario p, c_articulo a 
                    WHERE 
                        p.`status` = 'A' AND 
                        ca.cve_articulo = '{$articulo}' AND 
                        p.cve_articulo = a.cve_articulo AND 
                        p.cve_articulo = '{$articulo}'";
            //    $sql = "SELECT a.cve_articulo, a.des_articulo from cab_planifica_inventario ca, det_planifica_inventario p, c_articulo a where ca.cve_articulo = '".$item['articulo']."' and p.ID_PLAN = ca.ID_PLAN AND p.cve_articulo = a.cve_articulo";

            $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
            mysqli_set_charset($conn, 'utf8');
            $rs = mysqli_query($conn, $sql) or die("Error description: " . mysqli_error($conn));
            while ($row = mysqli_fetch_array($rs)) {
                $arr[] = $row;
            }
        }

        //  echo $rs;
        $this->dataDetalle = $arr;

        return count($arr) > 0 ? $arr : FALSE;
        //  echo $this->dataDetalle;
    }

    function save( $_post )
    {
        $sqlID = "SELECT MAX(ID_PLAN) as plan FROM det_planifica_inventario";
        $rs = mysqli_query(\db2(), $sqlID) or die("Error description: " . mysqli_error(\db2()));
        $cuan = mysqli_num_rows($rs);

        $almacen = $_post['almacen'];
        $sql = "SELECT id FROM c_almacenp WHERE clave = '{$almacen}'";
        $res = mysqli_query(\db2(), $sql) or die("Error description: " . mysqli_error(\db2()));
        $almacen_id = mysqli_fetch_array($res);
        $almacen_id = $almacen_id['id'];

        if($cuan>0){
            while ($row = mysqli_fetch_array($rs)) {
                $valor=$row['plan'];
            }
            $conteo = intval($valor) + 1;
        }
        else {
            $conteo=1; 
        }

        try {
            if (!empty($_post["arrInv"])) {
                switch ($_post["MODE"]) {
                    case "Un Solo Día":
                        foreach ($_post["arrInv"] as $item) {
                            $fecha = date("Y-m-d" ,strtotime($item["fecha"]));
                            $sql = "Call SPAD_GuardaPlanInventarioDia(".$conteo.", '".$fecha."', '".$item["articulo"]."', ".$almacen_id.");";
                            $rs = mysqli_query(\db2(), $sql) or die("Error description: " . mysqli_error(\db2()));
                        }
                        break;
                    case "Diario":
                              foreach ($_post["arrInv"] as $item) {
                            $item["FechaIni"] = str_replace("/", "-", $item["FechaIni"]);
                            $item["FechaFin"] = str_replace("/", "-", $item["FechaFin"]);
                            $FechaIni = date("Y-m-d" ,strtotime($item["FechaIni"]));
                            $FechaFin = date("Y-m-d" ,strtotime($item["FechaFin"]));
                            $sql = "Call SPAD_GuardaPlanInventarioDiario(
                                    '0', 
                                    '".$FechaIni."', 
                                    '".$FechaFin."', 
                                    '".$item["articulo"]."', 
                                    '".$item["laborales"]."', 
                                    '".$item["NDias"]."');";
                            $rs = mysqli_query(\db2(), $sql) or die("Error description: " . mysqli_error(\db2()));
                        } 
                        break;
                    case "Semanal":
                        foreach ($_post["arrInv"] as $item) {
                            $item["fechaInicial"] = str_replace("/", "-", $item["fechaInicial"]);
                            $item["fechaFinal"] = str_replace("/", "-", $item["fechaFinal"]);
                            $fechaInicial = date("Y-m-d" ,strtotime($item["fechaInicial"]));
                            $fechaFinal = date("Y-m-d" ,strtotime($item["fechaFinal"]));

                            $lunes = ($item["lunes"]=="true") ? "S" : "N";
                            $martes = ($item["martes"]=="true") ? "S" : "N";
                            $miercoles = ($item["miercoles"]=="true") ? "S" : "N";
                            $jueves = ($item["jueves"]=="true") ? "S" : "N";
                            $viernes = ($item["viernes"]=="true") ? "S" : "N";
                            $sabado = ($item["sabado"]=="true") ? "S" : "N";
                            $domingo = ($item["domingo"]=="true") ? "S" : "N";

                            $sql = "Call SPAD_GuardaPlanInventarioSemanal(
                                    '0',
                                    '".$fechaInicial."', 
                                    '".$fechaFinal."', 
                                    '".$item["articulo"]."', 
                                    '".$item["intervalo"]."', 
                                    '".$lunes."', 
                                    '".$martes."', 
                                    '".$miercoles."', 
                                    '".$jueves."', 
                                    '".$viernes."',
                                    '".$sabado."',
                                    '".$domingo."');";
                            $rs = mysqli_query(\db2(), $sql) or die("Error description: " . mysqli_error(\db2()).$sql);
                        }
                        break;
                    case "Mensual":
                        foreach ($_post["arrInv"] as $item) {
                            $item["fechaInicial"] = str_replace("/", "-", $item["fechaInicial"]);
                            $item["fechaFinal"] = str_replace("/", "-", $item["fechaFinal"]);
                            $fechaInicial = date("Y-m-d" ,strtotime($item["fechaInicial"]));
                            $fechaFinal = date("Y-m-d" ,strtotime($item["fechaFinal"]));
                            $sql = "Call SPAD_GuardaPlanInventarioMensual(
                                    '0',
                                    '".$fechaInicial."', 
                                    '".$fechaFinal."', 
                                    '".$item["articulo"]."', 
                                    '".$item["intervalo"]."', 
                                    '".$item["escalar"]."', 
                                    '".$item["dia"]."');";
                            $rs = mysqli_query(\db2(), $sql) or die("Error description: " . mysqli_error(\db2()));
                        }
                        break;
                }
            }
        } catch(Exception $e) {
            return 'ERROR: ' . $e->getMessage();
        }
    }


    
    function saveConteo($conteo)
    {		
        $conteo = intval($conteo);
        $sql = "INSERT INTO t_conteoinventariocicl (
                ID_PLAN, NConteo, Status
            ) VALUES (
                (SELECT MAX(ID_PLAN) from det_planifica_inventario),
                {$conteo},
                'A'
            );";
        $this->save = \db()->prepare($sql);
        $this->save->execute(); 
    }



    function borrarPedido( $data ) {
        $sql = '
        UPDATE
          ' . self::TABLE . '
        SET
          Activo = 0
        WHERE
          Fol_folio = ?
      ';
        $this->save = \db()->prepare($sql);
        $this->save->execute( array(
            $data['Fol_folio']
        ) );
    }

    function actualizarStatus( $_post ) {
        try {
            $sql = "UPDATE " . self::TABLE . " SET status='".$_post['status']."' WHERE ID_Pedido='".$_post['ID_Pedido']."'";
            $rs = mysqli_query(\db2(), $sql) or die("Error description: " . mysqli_error(\db2()));

        } catch(Exception $e) {
            return 'ERROR: ' . $e->getMessage();
        }
    }

    function actualizarPedido( $_post ) {
        try {
            $sql = "Delete From " . self::TABLE . " WHERE Fol_folio = '".$_post['Fol_folio']."'";
            $rs = mysqli_query(\db2(), $sql) or die("Error description: " . mysqli_error(\db2()));

            $sql = "INSERT INTO " . self::TABLE . " (Fol_folio, Cve_clte, cve_Vendedor, Fec_Entrada, Pick_Num, Observaciones, cve_almac, Activo)";
            $sql .= "Values (";
            $sql .= "'".$_post['Fol_folio']."',";
            $sql .= "'".$_post['Cve_clte']."',";
            $sql .= "'".$_post['cve_Vendedor']."',";
            $sql .= "now(),";
            $sql .= "'".$_post['Pick_Num']."',";
            $sql .= "'".$_post['Observaciones']."',";
            $sql .= "'".$_post['cve_almac']."', '1');";

            $rs = mysqli_query(\db2(), $sql) or die("Error description: " . mysqli_error(\db2()));

            if (!empty($_post["arrDetalle"])) {
                $sql = "Delete From td_pedido WHERE Fol_folio = '".$_post['Fol_folio']."'";
                $rs = mysqli_query(\db2(), $sql) or die("Error description: " . mysqli_error(\db2()));

                foreach ($_post["arrDetalle"] as $item) {
                    $sql = "INSERT INTO td_pedido (Cve_articulo, Num_cantidad, Fol_folio, Activo) Values ";
                    $sql .= "('".$item['codigo']."', '".$item['CantPiezas']."', '".$_post['Fol_folio']."', '1');";
                    $rs = mysqli_query(\db2(), $sql) or die("Error description: " . mysqli_error(\db2()));
                }
            }
        } catch(Exception $e) {
            return 'ERROR: ' . $e->getMessage();
        }
    }

    public static function printReport($folio){
        $sqlHeader =    "SELECT DISTINCT cab.ID_PLAN AS consecutivo, 
                                ap.nombre AS almacen, 
                                (select u.cve_usuario from c_usuario u, t_conteoinventariocicl cic where cic.cve_usuario = u.cve_usuario AND cic.ID_PLAN=cab.ID_PLAN order by cic.NConteo desc limit 1) as usuario, 
                                DATE_FORMAT(cab.FECHA_INI, '%d-%m-%Y %H:%i:%s') as fecha_inicio, 
                                DATE_FORMAT(cab.FECHA_FIN, '%d-%m-%Y %H:%i:%s') as fecha_final, 
                                d.status 
                        FROM det_planifica_inventario d,c_articulo a, c_almacenp ap, cab_planifica_inventario cab 
                        WHERE d.cve_articulo=a.cve_articulo and a.cve_almac = ap.id and cab.ID_PLAN = d.ID_PLAN AND d.ID_PLAN = '{$folio}';";
        $queryHeader = \db()->prepare($sqlHeader);
        $queryHeader->execute();
        $dataHeader = [];
        $contentHeader = "";
        if($queryHeader){
            $dataHeader = $queryHeader->fetch(\PDO::FETCH_ASSOC);
        }

        $sqlBody = "SELECT
                            c.des_almac zona,(CASE WHEN v.tipo = 'area' AND v.cve_ubicacion IS NOT NULL THEN (SELECT desc_ubicacion AS ubicacion FROM tubicacionesretencion WHERE cve_ubicacion = v.cve_ubicacion) WHEN v.tipo = 'ubicacion' AND v.cve_ubicacion IS NOT NULL THEN (SELECT u.CodigoCSD AS ubicacion FROM c_ubicacion u WHERE u.idy_ubica = v.cve_ubicacion)ELSE '--'END) AS ubicacion,
                            ifnull(c_articulo.cve_articulo, '--') as clave,
                            ifnull(c_articulo.des_articulo, '--') as descripcion,
                            if(v.cve_lote = '', '--', v.cve_lote) as lote,
                            ifnull(c_lotes.CADUCIDAD, '--') as caducidad,
                            ifnull(c_serie.numero_serie, '--') as serie,
                            (SELECT MAX(NConteo) FROM t_conteoinventariocicl WHERE ID_PLAN = {$folio}) as conteo,
                            ifnull(v.Existencia, 0) as stockTeorico,
                            (SELECT Cantidad FROM t_invpiezasciclico WHERE ID_PLAN = {$folio} AND NConteo = (SELECT conteo) AND cve_articulo = v.cve_articulo AND idy_ubica = v.cve_ubicacion) as stockFisico,
                            (SELECT stockFisico) - (SELECT stockTeorico) as diferencia,
                            (SELECT nombre_completo FROM c_usuario WHERE cve_usuario = (SELECT cve_usuario FROM t_conteoinventariocicl WHERE ID_PLAN = {$folio} ORDER BY id DESC LIMIT 1)) as usuario,
                            'Piezas' as unidad_medida,
                            CONCAT(v.tipo, '|', v.cve_ubicacion, '|', v.cve_articulo, '|', {$folio}, '|', (SELECT conteo), '|', (SELECT cve_usuario FROM t_conteoinventariocicl WHERE NConteo = (SELECT conteo) AND ID_PLAN = {$folio})) AS id
                    from V_ExistenciaGralProduccion v
                    left join c_articulo on c_articulo.cve_articulo = v.cve_articulo
                /*    left join c_lotes on c_lotes.LOTE = v.cve_lote */
                    left join c_serie on c_serie.clave_articulo = c_articulo.cve_articulo
                    INNER join V_ExistenciaFisicaCiclica i on i.cve_articulo = v.cve_articulo AND i.idy_ubica=v.cve_ubicacion and i.ID_PLAN = {$folio}
                    LEFT JOIN c_almacen AS c ON c.cve_almacenp=v.cve_almac
                    LEFT JOIN c_lotes on c_lotes.LOTE = i.cve_lote
                    where v.Existencia > 0
                    AND v.tipo='ubicacion'
                    group by v.cve_articulo,v.cve_ubicacion;";
        $queryBody = \db()->prepare($sqlBody);
        $queryBody->execute();
        $dataBody = [];
        $contentBody = "";
        if($queryBody){
            $contentBody = $queryBody->fetchAll(\PDO::FETCH_ASSOC);
        }

        if(!empty($queryHeader)){
            $fecha_inicio = date("d-m-Y h:i:s A", strtotime($dataHeader['fecha_inicio']));
            $fecha_final = date("d-m-Y h:i:s A", strtotime($dataHeader['fecha_final']));
            ob_start();
            ?>
                <h3 align="center">Datos del Inventario #<?php echo $folio ?></h3>
                <table style="width: 100%; border-collapse: collapse; font-size: 12px;" border="1">
                    <thead>
                        <tr>
                            <th style="padding: 5px; background-color: #f5f5f5; width: 14.2%">Consecutivo</th>
                            <th style="padding: 5px; background-color: #f5f5f5; width: 14.2%">Almacén</th>
                            <th style="padding: 5px; background-color: #f5f5f5; width: 14.2%">Usuario</th>
                            <th style="padding: 5px; background-color: #f5f5f5; width: 14.2%">Fecha Inicio</th>
                            <th style="padding: 5px; background-color: #f5f5f5; width: 14.2%">Fecha Fin</th>
                            <th style="padding: 5px; background-color: #f5f5f5; width: 14.2%">Diferencia</th>
                            <th style="padding: 5px; background-color: #f5f5f5; width: 14.2%">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="padding: 5px"><?php echo $dataHeader['consecutivo'] ?></td>
                            <td style="padding: 5px"><?php echo $dataHeader['almacen'] ?></td>
                            <td style="padding: 5px"><?php echo $dataHeader['usuario'] ?></td>
                            <td style="padding: 5px"><?php echo $fecha_inicio ?></td>
                            <td style="padding: 5px"><?php echo $fecha_final ?></td>
                            <td style="padding: 5px"><?php echo $dataHeader['diferencia'] ?></td>
                            <td style="padding: 5px"><?php echo $dataHeader['status'] ?></td>
                        </tr>
                    </tbody>
                </table>
            <?php 
            $contentHeader = ob_get_clean();
            ob_flush();
        }

        if(!empty($queryBody)){
            ob_start();
            ?>
                <h3 align="center">Detalles del Conteo</h3>  
                <table style="width: 100%; border-collapse: collapse; font-size: 10px" border="1">
                    <thead>
                        <tr>
                            <th style="padding: 5px; background-color: #f5f5f5;  width: 8.3%">Ubicación</th>
                            <th style="padding: 5px; background-color: #f5f5f5;  width: 8.3%">Clave</th>
                            <th style="padding: 5px; background-color: #f5f5f5;  width: 8.3%">Descripción</th>
                            <th style="padding: 5px; background-color: #f5f5f5;  width: 8.3%">Lote</th>
                            <th style="padding: 5px; background-color: #f5f5f5;  width: 8.3%">Caducidad</th>
                            <th style="padding: 5px; background-color: #f5f5f5;  width: 8.3%">Serie</th>
                            <th style="padding: 5px; background-color: #f5f5f5;  width: 8.3%">Stock Teórico</th>
                            <th style="padding: 5px; background-color: #f5f5f5;  width: 8.3%">Stock Físico</th>
                            <th style="padding: 5px; background-color: #f5f5f5;  width: 8.3%">Diferencia</th>
                            <th style="padding: 5px; background-color: #f5f5f5;  width: 8.3%">Conteo</th>
                            <th style="padding: 5px; background-color: #f5f5f5;  width: 8.3%">Usuario</th>
                            <th style="padding: 5px; background-color: #f5f5f5;  width: 8.3%">Unidad</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($contentBody AS $index => $body): ?>
                            <?php 
                                $color = $index === (count($contentBody) - 1) && $dataHeader['status'] === 'Cerrado' ? 'background-color: yellow;' : '';
                             ?>
                            <tr>
                                <td style="padding: 5px; <?php echo $color ?>"><?php echo $body['ubicacion'] ?></td>
                                <td style="padding: 5px; <?php echo $color ?>"><?php echo $body['clave'] ?></td>
                                <td style="padding: 5px; <?php echo $color ?>"><?php echo $body['descripcion'] ?></td>
                                <td style="padding: 5px; <?php echo $color ?>"><?php echo $body['lote'] ?></td>
                                <td style="padding: 5px; <?php echo $color ?>"><?php echo $body['caducidad'] ?></td>
                                <td style="padding: 5px; <?php echo $color ?>"><?php echo $body['serie'] ?></td>
                                <td style="padding: 5px; <?php echo $color ?>"><?php echo $body['stockTeorico'] ?></td>
                                <td style="padding: 5px; <?php echo $color ?>"><?php echo $body['stockFisico'] ?></td>
                                <td style="padding: 5px; <?php echo $color ?>"><?php echo $body['diferencia'] ?></td>
                                <td style="padding: 5px; <?php echo $color ?>"><?php echo $body['conteo'] ?></td>
                                <td style="padding: 5px; <?php echo $color ?>"><?php echo $body['usuario'] ?></td>
                                <td style="padding: 5px; <?php echo $color ?>"><?php echo $body['unidad_medida'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php
            $contentBody = ob_get_clean();
            ob_flush();
        }

        $content = $contentHeader . $contentBody;

        $pdf = new \ReportePDF\PDF($_SESSION['cve_cia'], "Inventario Cíclico #{$folio}", "L");
        $pdf->setContent($content);
        $pdf->stream();

    }
}
