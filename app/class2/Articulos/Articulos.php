<?php

namespace Articulos;

class Articulos {

    const TABLE = 'c_articulo';
    var $identifier;

    public function __construct( $cve_articulo = false, $key = false ) {

        if( $cve_articulo ) {
            $this->cve_articulo = (int) $cve_articulo;
        }

        if($key) {

            $sql = sprintf('
          SELECT
            cve_articulo
          FROM
            %s
          WHERE
            cve_articulo = ?
        ',
                self::TABLE
            );

            $sth = \db()->prepare($sql);
            $sth->setFetchMode(\PDO::FETCH_CLASS, '\Articulos\Articulos');
            $sth->execute(array($key));

            $articulo = $sth->fetch();

            $this->cve_articulo = $articulo->cve_articulo;

        }

    }

    private function load($almacen) 
    {
      try 
      {
        $sql = sprintf('
          SELECT DISTINCT
            ra.Cve_Almac AS cve_almac,
            c_articulo.cve_articulo,
            c_articulo.cve_codprov,
            c_articulo.des_articulo,
            c_articulo.des_detallada,
            c_articulo.peso,
            c_articulo.costo,
            c_articulo.mav_pctiva,
            c_articulo.IEPS,
            c_articulo.tipo_producto,
            c_articulo.umas,
            c_articulo.unidadMedida,
            c_articulo.barras2,
            c_articulo.num_multiplo,
            c_articulo.barras3,
            c_articulo.cajas_palet,
            c_articulo.tipo,
            c_articulo.grupo,
            c_articulo.clasificacion,
            if(c_articulo.control_lotes="S","1","0") as control_lotes,
            if(c_articulo.Caduca="S","1","0") as Caduca,
            if(c_articulo.Compuesto="S","1","0") as compuesto,
            if(c_articulo.control_numero_series="S","1","0") as control_numero_series,
            if(c_articulo.control_peso="S","1","0") as control_peso,
            if(c_articulo.control_volumen="S","1","0") as control_volumen,
            #ts_ubicxart.CapacidadMinima as stock_minimo,
            #ts_ubicxart.CapacidadMaxima as stock_maximo,
            ts_existenciaxart.num_stkmin as stock_minimo,
            ts_existenciaxart.num_stkmax as stock_maximo,
            if(c_articulo.req_refrigeracion="S","1","0") as req_refrigeracion,
            if(c_articulo.mat_peligroso="S","1","0") as mat_peligroso,
            if(c_articulo.Ban_Envase="S","1","0") as ban_envase,
            IFNULL(c_articulo.Tipo_Envase, "N") as Tipo_Envase,
            if(c_articulo.Usa_Envase="S","1","0") as usa_envase,
            IFNULL(c_articulo.control_abc, "N") as control_abc,
            c_articulo.alto,
            c_articulo.fondo,
            c_articulo.ancho,
            c_articulo.cve_tipcaja,
            c_articulo.tipo_caja,
            c_articulo.empq_cveumed,
            c_tipocaja.alto AS alto_caja,
            c_tipocaja.ancho AS ancho_caja,
            c_tipocaja.largo AS largo_caja,
            c_tipocaja.clave AS clave_caja,
            c_tipocaja.descripcion AS des_caja,
            c_tipocaja.id_tipocaja AS id_tipocaja,
            c_tipocaja.peso AS peso_caja,
            GROUP_CONCAT(IFNULL(p.Envase, "") SEPARATOR ";;;;;") AS envase_productos,
            GROUP_CONCAT(p.Cant_Base SEPARATOR ";;;;;") AS cantidad_base,
            GROUP_CONCAT(p.Cant_Eq SEPARATOR ";;;;;") AS cantidad_equivalente,
            GROUP_CONCAT(DISTINCT Rel_Art_Carac.Id_Carac ORDER BY Rel_Art_Carac.Id_Carac) AS Id_Carac
          FROM
            %s
          #LEFT JOIN ts_ubicxart on ts_ubicxart.cve_articulo = c_articulo.cve_articulo
          LEFT JOIN ts_existenciaxart on ts_existenciaxart.cve_articulo = c_articulo.cve_articulo
          LEFT JOIN c_tipocaja ON c_articulo.tipo_caja = c_tipocaja.id_tipocaja
          LEFT JOIN Rel_Art_Carac ON c_articulo.cve_articulo = Rel_Art_Carac.Cve_Articulo
          LEFT JOIN Rel_Articulo_Almacen ra ON ra.Cve_Articulo = c_articulo.cve_articulo
          LEFT JOIN ProductoEnvase p ON p.Producto = c_articulo.cve_articulo
          LEFT JOIN c_almacenp ON ra.Cve_Almac = c_almacenp.id
          WHERE c_articulo.id = ?
          AND ra.Cve_Almac = ?
        ',
        self::TABLE
        );

        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\Articulos\Articulos' );
        $sth->execute( array( $this->cve_articulo, $almacen) );
        //$sth->execute( array( '$clave_articulo', $almacen) );
        $this->data = $sth->fetch();

        $sql= "
          SELECT a.* 
          FROM c_articulo_imagen a 
          INNER JOIN c_articulo p on p.cve_articulo=a.cve_articulo 
          WHERE p.id= $this->cve_articulo
        ";
        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\Articulos\Articulos' );
        $sth->execute( array( $this->cve_articulo ) );
/*
"<br />
<b>Fatal error</b>:  Uncaught PDOException: SQLSTATE[42000]: Syntax error or access violation: 1064 You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near '' at line 4 in /home/aproadl/public_html/subdominios/wms.dev/app/class/Articulos/Articulos.php:128
Stack trace:
#0 /home/aproadl/public_html/subdominios/wms.dev/app/class/Articulos/Articulos.php(128): PDOStatement-&gt;execute(Array)
#1 /home/aproadl/public_html/subdominios/wms.dev/app/class/Articulos/Articulos.php(182): Articulos\Articulos-&gt;load(NULL)
#2 /home/aproadl/public_html/subdominios/wms.dev/app/class/Articulos/Articulos.php(183): Articulos\Articulos-&gt;__get('id')
#3 /home/aproadl/public_html/subdominios/wms.dev/api/articulos/update/index.php(229): Articulos\Articulos-&gt;__get('id')
#4 {main}
  thrown in <b>/home/aproadl/public_html/subdominios/wms.dev/app/class/Articulos/Articulos.php</b> on line <b>128</b><br />
"*/
        @$this->data->fotos=$sth->fetchAll();
      } 
      catch(PDOException $e) 
      {
        echo 'ERROR: ' . $e->getMessage();
      }
    }


    private function loadVer() {
        try {
            $sql = 'SELECT
							Select c_articulo.*,
							ts_ubicxart.CapacidadMinima as stock_minimo,
							ts_ubicxart.CapacidadMaxima as stock_maximo,
							c_gpoarticulo.des_gpoart as grupo,
							c_sgpoarticulo.des_sgpoart as clasificacion,
							c_ssgpoarticulo.des_ssgpoart as tipo
							FROM
                            c_articulo
							left join ts_ubicxart
							on c_articulo.cve_articulo = ts_ubicxart.cve_articulo
							left join c_gpoarticulo
							on c_articulo.grupo = c_gpoarticulo.cve_gpoart
							left join c_sgpoarticulo
							on c_articulo.clasificacion = c_sgpoarticulo.cve_sgpoart
							left join c_ssgpoarticulo
							on c_articulo.tipo = c_ssgpoarticulo.cve_ssgpoart
                            Where c_articulo.id = ?
                  ';

            $sth = \db()->prepare( $sql );
            $sth->setFetchMode( \PDO::FETCH_CLASS, '\Articulos\Articulos' );
            $sth->execute( array( $this->cve_articulo ) );

            $this->data = $sth->fetch();
        } catch(PDOException $e) {
            echo 'ERROR: ' . $e->getMessage();
        }
    }

    function __get( $key) 
    {
      $arr = explode("::::::::::", $key);
      $key = $arr[0];
      $almacen = $arr[1];

      switch($key) 
      {
        case 'cve_articulo':
        $this->load($almacen);
        case 'id':
          $this->load($almacen);
        return @$this->data->$key;
        default:
        return $this->key;
      }
    }

    function __getVer( $key ) {

        switch($key) {
            case 'cve_articulo':
                $this->loadVer();
                return @$this->data->$key;
            default:
                return $this->key;
        }



    }

    function saveFromAPI( $_post ) {
        try {

            if ($_post["control_lotes"] === "true" or $_post["control_lotes"] === 1) {
                $_post["control_lotes"] = "S";
            } else {
                $_post["control_lotes"] = "N";
            }
            $_post["q"] = $_post["control_numero_series"];
            if ($_post["control_numero_series"] === "true" or $_post["control_numero_series"] === 1) {
                $_post["control_numero_series"] = "S";
            } else {
                $_post["control_numero_series"] = "N";
            }

            if ($_post["control_peso"] === "true" or $_post["control_peso"] === 1) {
                $_post["control_peso"] = "S";
            } else {
                $_post["control_peso"] = "N";
            }

            if ($_post["control_volumen"] === "true" or $_post["control_volumen"] === 1) {
                $_post["control_volumen"] = "S";
            } else {
                $_post["control_volumen"] = "N";
            }

            if ($_post["req_refrigeracion"] === "true" or $_post["req_refrigeracion"] === 1) {
                $_post["req_refrigeracion"] = "S";
            } else {
                $_post["req_refrigeracion"] = "N";
            }

            if ($_post["mat_peligroso"] === "true" or $_post["mat_peligroso"] === 1) {
                $_post["mat_peligroso"] = "S";
            } else {
                $_post["mat_peligroso"] = "N";
            }

            if ($_post["compuesto"] === "true" or $_post["compuesto"] === 1) {
                $_post["compuesto"] = "S";
            } else {
                $_post["compuesto"] = "N";
            }

            extract($_post);

            $sql = "INSERT INTO ".self::TABLE."
                        SET
                            cve_almac= (SELECT id FROM c_almacenp WHERE clave = '${cve_almac}'),
                            cve_articulo= '${cve_articulo}',
                            cve_codprov= '${cve_codprov}',
                            des_articulo= '${des_articulo}',
                            des_detallada= '${des_detallada}',
                            peso= '${peso}',
                            costo= '${costo}',
                            barras2= '${barras2}',
                            num_multiplo= '${num_multiplo}',
                            barras3= '${barras3}',
                            cajas_palet= '${cajas_palet}',
                            control_lotes= '${control_lotes}',
                            control_numero_series= '${control_numero_series}',
                            control_peso= '${control_peso}',
                            control_volumen= '${control_volumen}',
                            req_refrigeracion= '${req_refrigeracion}',
                            clasificacion= '${clasificacion}',
                            tipo= '${tipo}',
                            grupo= '${grupo}',
                            mat_peligroso= '${mat_peligroso}',
                            alto= '${alto}',
                            ancho= '${ancho}',
                            fondo= '${fondo}',
                            tipo_producto= '${tipo_producto}',
                            umas= '${umas}',
                            tipo_caja= '${tipo_caja}',
                            unidadMedida= '${unidadMedida}',
                            Compuesto= '${compuesto}';

                        INSERT INTO ts_ubicxart
                        SET
                            cve_articulo= '${cve_articulo}',
                            CapacidadMinima= '${stock_minimo}',
                            CapacidadMaxima= '${stock_maximo}';";

            mysqli_multi_query(\db2(), $sql);

            return $_post;
        } catch(PDOException $e) {
            echo 'ERROR: ' . $e->getMessage();return FALSE;
            
        }
        return FALSE;
    }

    function save( $_post ) 
    {
      if($_post["control_lotes"]=="true") {$_post["control_lotes"] = "S";} 
      else {$_post["control_lotes"] = "N";}
      if($_post["control_caducidad"]=="true") {$_post["control_caducidad"] = "S";} 
      else {$_post["control_caducidad"] = "N";}
      if($_post["control_numero_series"]=="true") {$_post["control_numero_series"] = "S";} 
      else {$_post["control_numero_series"] = "N";}
      if($_post["control_peso"]=="true") {$_post["control_peso"] = "S";} 
      else {$_post["control_peso"] = "N";}
      if($_post["control_volumen"]=="true") {$_post["control_volumen"] = "S";} 
      else {$_post["control_volumen"] = "N";}
      if ($_post["req_refrigeracion"]=="true") {$_post["req_refrigeracion"] = "S";} 
      else {$_post["req_refrigeracion"] = "N";}
      if ($_post["mat_peligroso"]=="true") {$_post["mat_peligroso"] = "S";} 
      else {$_post["mat_peligroso"] = "N";}
      if ($_post["compuesto"]=="true") {$_post["compuesto"] = "S";} 
      else {$_post["compuesto"] = "N";}

      $tipo_envase = 'N';
      $tipo_ABC = '';
      if($_post["envase"]=="true") 
      {
          $_post["envase"] = "S";
          if($_post["tipo_envase_plastico"]=="true") {$tipo_envase = "P";} 
          if($_post["tipo_envase_cristal"]=="true") {$tipo_envase = "C";} 
          if($_post["tipo_envase_garrafon"]=="true") {$tipo_envase = "G";} 
      } 
      else {$_post["envase"] = "N";}

      if($_post["control_abc"]=="true") 
      {
          if($_post["control_tipo_A"]=="true") {$tipo_ABC = "A";} 
          if($_post["control_tipo_B"]=="true") {$tipo_ABC = "B";} 
          if($_post["control_tipo_C"]=="true") {$tipo_ABC = "C";} 
      } 
      else {$_post["control_abc"] = "";}

      $envase1 = "";
      $envase2 = "";
      $cantidad_base1 = "";
      $cantidad_base2 = "";
      $cantidad_equivalente1 = "";
      $cantidad_equivalente2 = "";
      if($_post["usa_envase"]=="true") 
      {
        $_post["usa_envase"] = "S";

        if($_post["envase1"]!="")  
        {
            $envase1               = $_post["envase1"];
            $cantidad_base1        = $_post["cantidad_base1"];
            $cantidad_equivalente1 = $_post["cantidad_equivalente1"];
        }

        if($_post["envase2"]!="")  
        {
            $envase2               = $_post["envase2"];
            $cantidad_base2        = $_post["cantidad_base2"];
            $cantidad_equivalente2 = $_post["cantidad_equivalente2"];
        }

      } 
      else 
      {
        $_post["usa_envase"] = "N";
      }

      if($_post["control_abc"]=="true") 
      {
          if($_post["control_tipo_A"]=="true") {$tipo_ABC = "A";} 
          if($_post["control_tipo_B"]=="true") {$tipo_ABC = "B";} 
          if($_post["control_tipo_C"]=="true") {$tipo_ABC = "C";} 
      } 
      else {$_post["control_abc"] = "";}


      extract($_post);
      $des_articulo=utf8_decode($des_articulo);
      $des_detallada=utf8_decode($des_detallada);

      if(isset($_post["peso_caja"]))
      {
          $descripcion_caja = $_post["descripcion_caja"];
          $peso_caja = $_post["peso_caja"];
          $altotc = $_post["altotc"];
          $anchotc = $_post["anchotc"];
          $fondotc = $_post["fondotc"];
          $tipo_caja = "";
          $cve_tipcaja = 'CO'.'${cve_articulo}';

          $sql_c_tipo_caja = "
            INSERT IGNORE INTO c_tipocaja SET 
              clave = 'CO${cve_articulo}',
              descripcion = '${descripcion_caja}',
              alto = '${altotc}',
              ancho = '${anchotc}',
              largo = '${fondotc}',
              peso = '${peso_caja}'";
          $sth = \db()->prepare($sql_c_tipo_caja);
          $sth->execute();

          $sql_existenciaxart = "
            INSERT IGNORE INTO ts_existenciaxart SET 
              cve_almac = '${cve_almac}',
              cve_articulo = '${cve_articulo}',
              num_stkmin = '${stock_minimo}',
              num_stkmax = '${stock_maximo}'";
          $sth = \db()->prepare($sql_existenciaxart);
          $sth->execute();

          $sql_tipocaja = "SELECT MAX(id_tipocaja) max_id FROM c_tipocaja";
          $sth = \db()->prepare($sql_tipocaja);
          $sth->execute();
          $id = $sth->fetch();
          $tipo_caja = $id[0];
      }

      if($caracteristicas_art[0] != "")
      {
        $sql_caracteristicas = "DELETE FROM Rel_Art_Carac WHERE Cve_Articulo = '${cve_articulo}'";
        $sth = \db()->prepare($sql_caracteristicas);
        $sth->execute();

        foreach ($_post['caracteristicas_art'] as $selectedOption)
        {
          $sql_caracteristicas = "INSERT INTO Rel_Art_Carac(Cve_Articulo, Id_Carac) VALUES('${cve_articulo}', ${selectedOption})";
          $sth = \db()->prepare($sql_caracteristicas);
          $sth->execute();
        }
      }

      if($_post["cve_proveedor"]!="") 
      {
          $id_proveedor = $_post["cve_proveedor"];
          $sql_proveedor = "INSERT IGNORE INTO rel_articulo_proveedor(Cve_Articulo, Id_Proveedor) VALUES('${cve_articulo}', $id_proveedor)";
          $sth = \db()->prepare($sql_proveedor);
          $sth->execute();
      }


      $v_ieps = $IEPS;
      $sql = "
        DELETE from c_articulo_imagen where cve_articulo='${cve_articulo}';
        
        INSERT INTO ".self::TABLE."
        SET
          cve_almac= '${cve_almac}',
          cve_articulo= '${cve_articulo}',
          cve_codprov= '${cve_codprov}',
          des_articulo= '${des_articulo}',
          des_detallada= '${des_detallada}',
          peso= '${peso}',
          costo= '${costo}',
          mav_pctiva= '${iva}',
          IEPS= '${v_ieps}',
          barras2= '${barras2}',
          num_multiplo= '${num_multiplo}',
          barras3= '${barras3}',
          cajas_palet= '${cajas_palet}',
          control_lotes= '${control_lotes}',
          Caduca= '${control_caducidad}',
          control_numero_series= '${control_numero_series}',
          control_peso= '${control_peso}',
          control_volumen= '${control_volumen}',
          Ban_Envase= '${envase}',
          Tipo_Envase= '${tipo_envase}',
          Usa_Envase= '${usa_envase}',
          control_abc= '${tipo_ABC}',
          req_refrigeracion= '${req_refrigeracion}',
          clasificacion= '${clasificacion}',
          tipo= '${tipo}',
          grupo= '${grupo}',
          mat_peligroso= '${mat_peligroso}',
          alto= '${alto}',
          ancho= '${ancho}',
          fondo= '${fondo}',
          tipo_producto= '${tipo_producto}',
          umas= '${umas}',
          unidadMedida= '${unidadMedida}',
          empq_cveumed= '${unidadMedida_empaque}',
          tipo_caja= '$tipo_caja',
          cve_tipcaja= '0',
          ID_Proveedor = '0',
          Compuesto= '${compuesto}';
      ";
      /*
        INSERT INTO ts_ubicxart
        SET
          cve_articulo= '${cve_articulo}',
          CapacidadMinima= '${stock_minimo}',
          CapacidadMaxima= '${stock_maximo}';
      */
        $sql_articulo  = $sql;

      foreach (${@fotos} as $foto)
      {
        if($foto)
        {
          $sql.="INSERT INTO c_articulo_imagen SET cve_articulo= '${cve_articulo}', url= '$foto';";
        }
      }
      //mysqli_multi_query(\db2(), $sql);

      $sth = \db()->prepare($sql);
      $sth->execute();




        if($envase1 != "")
        {
          $sql_envase = "INSERT IGNORE INTO ProductoEnvase(Producto, Envase, Cant_Base, Cant_Eq, Status, IdEmpresa) VALUES('$cve_articulo', '$envase1', '$cantidad_base1', '$cantidad_equivalente1', 'A', (SELECT clave FROM c_almacenp WHERE id = '{$cve_almac}'))";
          $sth = \db()->prepare($sql_envase);
          $sth->execute();
        }

        if($envase2 != "")
        {
          $sql_envase = "INSERT IGNORE INTO ProductoEnvase(Producto, Envase, Cant_Base, Cant_Eq, Status, IdEmpresa) VALUES('$cve_articulo', '$envase2', '$cantidad_base2', '$cantidad_equivalente2', 'A', (SELECT clave FROM c_almacenp WHERE id = '{$cve_almac}'))";
          $sth = \db()->prepare($sql_envase);
          $sth->execute();
        }


        return $sql_articulo;

    }

    function save_Rel_Art_Almacen($data)
    {
        extract($data);

      $sql_grupo = "SELECT id FROM c_gpoarticulo WHERE cve_gpoart = '${grupo}' AND id_almacen = '${cve_almac}'";
      $sth = \db()->prepare($sql_grupo);
      $sth->execute();
      $id = $sth->fetch();
      $id_grupo = $id[0];

      $sql_clasificacion = "SELECT id FROM c_sgpoarticulo WHERE cve_sgpoart = '${clasificacion}' AND id_almacen = '${cve_almac}'";
      $sth = \db()->prepare($sql_clasificacion);
      $sth->execute();
      $id = $sth->fetch();
      $id_clasificacion = $id[0];

      $sql_tipo_art = "SELECT id FROM c_ssgpoarticulo WHERE cve_ssgpoart = '${tipo}' AND id_almacen = '${cve_almac}'";
      $sth = \db()->prepare($sql_tipo_art);
      $sth->execute();
      $id = $sth->fetch();
      $id_tipo_art = $id[0];

      $sql =  "INSERT IGNORE INTO Rel_Articulo_Almacen(Cve_Almac, Cve_Articulo, Grupo_ID, Clasificacion_ID, Tipo_Art_ID) VALUES ('${cve_almac}', '${cve_articulo}', '$id_grupo', '$id_clasificacion', '$id_tipo_art') ON DUPLICATE KEY UPDATE Grupo_ID = '$id_grupo', Clasificacion_ID = '$id_clasificacion' , Tipo_Art_ID = '$id_tipo_art'";

      $sth = \db()->prepare($sql);
      $sth->execute();

        if($replicar_articulos=="true") 
        {
          $sql_replicar = "INSERT IGNORE INTO Rel_Articulo_Almacen(Cve_Almac, Cve_Articulo, Grupo_ID, Clasificacion_ID, Tipo_Art_ID) (SELECT a.id, '$cve_articulo', r.Grupo_ID, r.Clasificacion_ID, r.Tipo_Art_ID FROM Rel_Articulo_Almacen r LEFT JOIN c_almacenp a ON a.id != $cve_almac WHERE r.Cve_Articulo = '$cve_articulo' AND r.Cve_Almac = $cve_almac)";
          $sth = \db()->prepare($sql_replicar);
          $sth->execute();
        }

    }

    function getAll() {
        try {

            $sql = '
                SELECT
                *
                FROM
                ' . self::TABLE.'
				where Activo=1';

            $sth = \db()->prepare( $sql );
            $sth->setFetchMode( \PDO::FETCH_CLASS, '\Articulos\Articulos' );
            $sth->execute();

            return $sth->fetchAll();
        } catch(PDOException $e) {
            echo 'ERROR: ' . $e->getMessage();
        }

    }

    function getArticulosVenta($cve_almacen) {
        try {

            $sql = "SELECT DISTINCT * FROM (
                        SELECT DISTINCT dv.Articulo AS cve_articulo, a.des_articulo 
                        FROM DetalleVet dv
                        LEFT JOIN c_articulo a ON a.cve_articulo = dv.Articulo
                        WHERE dv.IdEmpresa = '$cve_almacen'

                        UNION

                        SELECT DISTINCT dp.Articulo AS cve_articulo, a.des_articulo 
                        FROM V_Detalle_Pedido dp
                        LEFT JOIN c_articulo a ON a.cve_articulo = dp.Articulo
                        WHERE dp.IdEmpresa = '$cve_almacen'
                    ) AS art";

            $sth = \db()->prepare( $sql );
            $sth->setFetchMode( \PDO::FETCH_CLASS, '\Articulos\Articulos' );
            $sth->execute();

            return $sth->fetchAll();
        } catch(PDOException $e) {
            echo 'ERROR: ' . $e->getMessage();
        }

    }

    function getArticulosVentaObseq($cve_almacen) {
        try {

            $sql = "SELECT DISTINCT pr.SKU AS cve_articulo, a.des_articulo 
                    FROM PRegalado pr
                    LEFT JOIN c_articulo a ON a.cve_articulo = pr.SKU
                    WHERE pr.IdEmpresa = '$cve_almacen'";

            $sth = \db()->prepare( $sql );
            $sth->setFetchMode( \PDO::FETCH_CLASS, '\Articulos\Articulos' );
            $sth->execute();

            return $sth->fetchAll();
        } catch(PDOException $e) {
            echo 'ERROR: ' . $e->getMessage();
        }

    }

    function getAllKardex($id_almacen) {
        try {

            $sql = "
                SELECT DISTINCT 
                a.cve_articulo, a.des_articulo
                FROM
                t_cardex k
                LEFT JOIN c_articulo a ON a.cve_articulo = k.cve_articulo
                WHERE k.Cve_Almac={$id_almacen}";

            $sth = \db()->prepare( $sql );
            $sth->setFetchMode( \PDO::FETCH_CLASS, '\Articulos\Articulos' );
            $sth->execute();

            return $sth->fetchAll();
        } catch(PDOException $e) {
            echo 'ERROR: ' . $e->getMessage();
        }

    }

    function getAllForDashboard($almacen, $cve_proveedor) 
    {
        try {
            $and_almacen = "";
            if(!empty($almacen)){
                //$and = " WHERE c_almacenp.clave = '{$almacen}'";
                $and_almacen = " e.cve_almac = (SELECT id FROM c_almacenp WHERE clave = '$almacen') AND ";
            }

            $left_joins_columna = "(SELECT COUNT(*) FROM c_articulo WHERE c_articulo.Activo = 1) AS activo,
                           (SELECT COUNT(*) FROM c_articulo WHERE c_articulo.Activo = 0) AS inactivo,";
            $and = "";
            if(!empty($cve_proveedor)){

                $left_joins_columna = "(SELECT COUNT(*) FROM c_articulo INNER JOIN rel_articulo_proveedor ON rel_articulo_proveedor.Cve_Articulo = c_articulo.cve_articulo WHERE c_articulo.Activo = 1 AND rel_articulo_proveedor.Id_Proveedor = $cve_proveedor) AS activo,
                        (SELECT COUNT(*) FROM c_articulo INNER JOIN rel_articulo_proveedor ON rel_articulo_proveedor.Cve_Articulo = c_articulo.cve_articulo WHERE c_articulo.Activo = 0 AND rel_articulo_proveedor.Id_Proveedor = $cve_proveedor) AS inactivo,";
                $and = " AND e.ID_Proveedor = $cve_proveedor ";
                //$and = " 
                //LEFT JOIN rel_articulo_proveedor r ON r.Cve_Articulo = a.cve_articulo
                //WHERE c_almacenp.clave = '{$almacen}'
                //AND V_ExistenciaGralProduccion.Id_Proveedor = '$cve_proveedor'";
            }
            /*
            $sql = "
                select *, 
                    concat('Articulos: ',total,' |',' Piezas: ',piezas) as texto 
                from (SELECT
                          sum(x.total) as total,
                          sum(x.piezas) as piezas,
                          sum(x.activo) as activo,
                          sum(x.inactivo) as inactivo
                      from
                      (
                          SELECT 
                              COUNT(distinct(a.cve_articulo)) AS total,
                              if(a.Activo = 1, 1,0) AS activo,
                              if(a.Activo = 0, 1,0) AS inactivo,
                              truncate(SUM(V_ExistenciaGralProduccion.Existencia),4) as piezas
                          FROM ".self::TABLE." a
                              inner join V_ExistenciaGralProduccion on V_ExistenciaGralProduccion.cve_articulo = a.cve_articulo
                              inner join c_almacenp on c_almacenp.id = V_ExistenciaGralProduccion.cve_almac
                          ".$and."
                              and V_ExistenciaGralProduccion.Existencia > 0
                              and V_ExistenciaGralProduccion.tipo = 'ubicacion'
                          group by a.cve_articulo
                      ) x)
                  y;
            ";
            */
            $sql = "
                    SELECT DISTINCT 
                        COUNT(DISTINCT e.cve_articulo) AS total,
                        SUM(e.Existencia) AS piezas,
                        $left_joins_columna
                        CONCAT('Articulos: ', COUNT(DISTINCT e.cve_articulo),' | Piezas: ', SUM(e.Existencia)) AS texto
                        FROM
                            V_ExistenciaGral e
                        LEFT JOIN c_articulo a ON a.cve_articulo = e.cve_articulo
                        LEFT JOIN c_ubicacion u ON u.idy_ubica = e.cve_ubicacion
                        LEFT JOIN c_lotes l ON l.LOTE = e.cve_lote AND l.cve_articulo = e.cve_articulo
                        LEFT JOIN c_almacen z ON z.cve_almac = (SELECT cve_almac FROM c_ubicacion WHERE idy_ubica = e.cve_ubicacion)
                        LEFT JOIN c_almacenp ap ON ap.id = z.cve_almacenp
                        LEFT JOIN c_charolas ch ON ch.clave_contenedor = e.Cve_Contenedor
                    WHERE $and_almacen e.tipo = 'ubicacion' AND e.Existencia > 0 
                    $and ";

            
            $sth = \db()->prepare( $sql );
            $sth->setFetchMode( \PDO::FETCH_CLASS, '\Articulos\Articulos' );
            $sth->execute();
            return $sth->fetch();
        } catch(PDOException $e) {echo 'ERROR: ' . $e->getMessage();}

    }

	function getArtCompuestos($almacen, $start, $length, $search) {
        $sql = '
                SELECT
                    des_articulo AS articulo,
                    barras2 AS barra_pieza,
                    barras3 AS barra_caja,
                    cve_articulo AS clave,
                    num_multiplo,
                    
                    control_lotes,
                    control_numero_series
                FROM ' . self::TABLE.'
                LEFT JOIN c_almacenp on c_articulo.cve_almac = c_almacenp.id
                WHERE c_articulo.Activo = 1
                AND Compuesto="S"
                AND c_almacenp.clave="'.$almacen.'"' ;

        if(!empty($search)){
            $sql .= " AND (des_articulo like '%$search%' OR barras2 like '%$search%' OR barras3 like '%$search%' OR cve_articulo like '%$search%') ";
        }

        if($length > 0){
            $sql .= " LIMIT $start, $length";
        }

        $query = mysqli_query(\db2(), $sql);
        $data = [];
        while($row = mysqli_fetch_array($query, MYSQLI_ASSOC)){
            $row['print'] = "<button class='btn btn-success' onclick='selectOption(\"".$row['clave']."\", \"".$row['articulo']."\", \"".$row['num_multiplo']."\", \"".$row['barra_pieza']."\", \"".$row['barra_caja']."\", \"".$row['control_lotes']."\", \"".$row['control_numero_series']."\")'><i class='fa fa-print'></i></button>";
            $data[] = $row;
        }
        return $data;

    }
    function getArtCompuestosTotalCount($almacen, $search) {
        try {
            $sql = '
                    SELECT
                        COUNT(cve_articulo) AS total
                    FROM ' . self::TABLE.'
                    LEFT JOIN c_almacenp on c_articulo.cve_almac = c_almacenp.id
                    WHERE c_articulo.Activo = 1
                    AND Compuesto="S"
                    AND c_almacenp.clave="'.$almacen.'"' ;
            if(!empty($search)){
                $sql .= " AND (c_articulo.des_articulo like '%$search%' OR c_articulo.barras2 like '%$search%' OR c_articulo.barras3 like '%$search%' OR c_articulo.cve_articulo like '%$search%') ";
            }

            $sth = \db()->prepare( $sql );
            $sth->execute();
            return $sth->fetchAll()[0]['total'];
        } catch(PDOException $e) {
            echo 'ERROR: ' . $e->getMessage();
        }

    }


	    function getConLotes() 
      {
        try 
        {
          $sql = "
            SELECT
              *
            FROM " . self::TABLE."
            WHERE Activo = 1 
            AND control_lotes= 'S'
          ";
          $sth = \db()->prepare($sql);
          $sth->setFetchMode( \PDO::FETCH_CLASS, '\Articulos\Articulos' );
          $sth->execute();
          return $sth->fetchAll();
        }
        catch(PDOException $e) 
        {
          echo 'ERROR: ' . $e->getMessage();
        }
      }

    function borrarArticulo( $data ) {
        $sql = '
        UPDATE
          ' . self::TABLE . '
        SET
          Activo = 0
        WHERE
          id = ?
      ';
        $this->save = \db()->prepare($sql);
        $this->save->execute( array(
            $data['cve_articulo']
        ) );
    }

    function actualizarArticulos( $_post ) {
        try {
            //$data["cve_ssgpo"] = 1;
            if ($_post["control_lotes"]=="true") {
                $_post["control_lotes"] = "S";
            } else {
                $_post["control_lotes"] = "N";
            }

            if ($_post["control_numero_series"]=="true") {
                $_post["control_numero_series"] = "S";
            } else {
                $_post["control_numero_series"] = "N";
            }

            if ($_post["control_caducidad"]=="true") {
                $_post["control_caducidad"] = "S";
            } else {
                $_post["control_caducidad"] = "N";
            }
            if ($_post["control_peso"]=="true") {
                $_post["control_peso"] = "S";
            } else {
                $_post["control_peso"] = "N";
            }
            if ($_post["control_volumen"]=="true") {
                $_post["control_volumen"] = "S";
            } else {
                $_post["control_volumen"] = "N";
            }
            if ($_post["req_refrigeracion"]=="true") {
                $_post["req_refrigeracion"] = "S";
            } else {
                $_post["req_refrigeracion"] = "N";
            }
            if ($_post["mat_peligroso"]=="true") {
                $_post["mat_peligroso"] = "S";
            } else {
                $_post["mat_peligroso"] = "N";
            }

			if ($_post["compuesto"]=="true") {
                $_post["compuesto"] = "S";
            } else {
                $_post["compuesto"] = "N";
            }

          $tipo_envase = 'N';
          if($_post["envase"]=="true") 
          {
              $_post["envase"] = "S";
              if($_post["tipo_envase_plastico"]=="true") {$tipo_envase = "P";} 
              if($_post["tipo_envase_cristal"]=="true") {$tipo_envase = "C";} 
              if($_post["tipo_envase_garrafon"]=="true") {$tipo_envase = "G";} 
          } 
          else {$_post["envase"] = "N";}

          if($_post["usa_envase"]=="true") {$_post["usa_envase"] = "S";} 
          else {$_post["usa_envase"] = "N";}

          $tipo_ABC = '';
          if($_post["control_abc"]=="true") 
          {
              if($_post["control_tipo_A"]=="true") {$tipo_ABC = "A";} 
              if($_post["control_tipo_B"]=="true") {$tipo_ABC = "B";} 
              if($_post["control_tipo_C"]=="true") {$tipo_ABC = "C";} 
          } 


            $sql = sprintf('SET FOREIGN_KEY_CHECKS=0; 
				UPDATE
				  ' . self::TABLE . '
				SET
					cve_almac= :cve_almac,
					cve_codprov= :cve_codprov,
                    barras2= :barras2,
                    barras3= :barras3,
                    des_articulo= :des_articulo,
                    des_detallada= :des_detallada,
					peso= :peso,
          costo= :costo,
          mav_pctiva= :iva,
          IEPS= :IEPS,
					num_multiplo= :num_multiplo,
					cajas_palet= :cajas_palet,
					control_lotes= :control_lotes,
                    Caduca = :control_caducidad,
					control_numero_series= :control_numero_series,
					control_peso= :control_peso,
                    control_volumen= :control_volumen,
                    Ban_Envase= :envase,
                    Tipo_Envase= :tipo_envase,
                    Usa_Envase= :usa_envase,
                    control_abc= :tipo_abc,
					req_refrigeracion= :req_refrigeracion,
					clasificacion= :clasificacion,
					tipo= :tipo,
					grupo= :grupo,
					mat_peligroso= :mat_peligroso,
					 alto= :alto,
					ancho= :ancho,
					fondo= :fondo,
          tipo_producto= :tipo_producto,
          umas= :umas,
          cve_tipcaja= :cve_tipcaja,
					tipo_caja= :tipo_caja,
          unidadMedida= :unidadMedida,
          empq_cveumed= :unidadMedida_empaque,
					Compuesto= :compuesto

					where
                        cve_articulo = :cve_articulo;');


            $this->save = \db()->prepare($sql);


            $this->save->bindValue( ':cve_almac', $_post['cve_almac'], \PDO::PARAM_STR );
            $this->save->bindValue( ':cve_articulo', $_post['cve_articulo'], \PDO::PARAM_STR );
            $this->save->bindValue( ':cve_codprov', $_post['cve_codprov'], \PDO::PARAM_STR );
            $this->save->bindValue( ':des_articulo', $_post['des_articulo'], \PDO::PARAM_STR );
            $this->save->bindValue( ':des_detallada', $_post['des_detallada'], \PDO::PARAM_STR );
            $this->save->bindValue( ':peso', $_post['peso'], \PDO::PARAM_STR );
            $this->save->bindValue( ':costo', $_post['costo'], \PDO::PARAM_STR );
            $this->save->bindValue( ':iva', $_post['iva'], \PDO::PARAM_STR );
            $this->save->bindValue( ':IEPS', $_post['IEPS'], \PDO::PARAM_STR );
            $this->save->bindValue( ':barras2', $_post['barras2'], \PDO::PARAM_STR );
            $this->save->bindValue( ':num_multiplo', $_post['num_multiplo'], \PDO::PARAM_STR );
            $this->save->bindValue( ':barras3', $_post['barras3'], \PDO::PARAM_STR );
            $this->save->bindValue( ':cajas_palet', $_post['cajas_palet'], \PDO::PARAM_STR );
            $this->save->bindValue( ':control_lotes', $_post['control_lotes'], \PDO::PARAM_STR );
            $this->save->bindValue( ':control_caducidad', $_post['control_caducidad'], \PDO::PARAM_STR );
            $this->save->bindValue( ':control_numero_series', $_post['control_numero_series'], \PDO::PARAM_STR );
            $this->save->bindValue( ':control_peso', $_post['control_peso'], \PDO::PARAM_STR );
            $this->save->bindValue( ':control_volumen', $_post['control_volumen'], \PDO::PARAM_STR );
            $this->save->bindValue( ':envase', $_post['envase'], \PDO::PARAM_STR );
            $this->save->bindValue( ':tipo_abc', $tipo_ABC, \PDO::PARAM_STR );
            $this->save->bindValue( ':tipo_envase', $tipo_envase, \PDO::PARAM_STR );
            $this->save->bindValue( ':usa_envase', $_post['usa_envase'], \PDO::PARAM_STR );
            $this->save->bindValue( ':req_refrigeracion', $_post['req_refrigeracion'], \PDO::PARAM_STR );
            $this->save->bindValue( ':mat_peligroso', $_post['mat_peligroso'], \PDO::PARAM_STR );
            $this->save->bindValue( ':tipo', $_post['tipo'], \PDO::PARAM_STR );
            $this->save->bindValue( ':grupo', $_post['grupo'], \PDO::PARAM_STR );
            $this->save->bindValue( ':clasificacion', $_post['clasificacion'], \PDO::PARAM_STR );
            $this->save->bindValue( ':alto', $_post['alto'], \PDO::PARAM_STR );
            $this->save->bindValue( ':fondo', $_post['fondo'], \PDO::PARAM_STR );
            $this->save->bindValue( ':tipo_producto', $_post['tipo_producto'], \PDO::PARAM_STR );
            $this->save->bindValue( ':umas', $_post['umas'], \PDO::PARAM_STR );
            $this->save->bindValue( ':unidadMedida', $_post['unidadMedida'], \PDO::PARAM_STR );
            $this->save->bindValue( ':unidadMedida_empaque', $_post['unidadMedida_empaque'], \PDO::PARAM_STR );
            $this->save->bindValue( ':ancho', $_post['ancho'], \PDO::PARAM_STR );
            $this->save->bindValue( ':cve_tipcaja', $_post['cve_tipcaja'], \PDO::PARAM_STR );
            $this->save->bindValue( ':tipo_caja', $_post['tipo_caja'], \PDO::PARAM_STR );
			      $this->save->bindValue( ':compuesto', $_post['compuesto'], \PDO::PARAM_STR );

            $this->save->execute();

            $sql = sprintf('
				update ts_existenciaxart
				SET
					num_stkmin= :CapacidadMinima,
					num_stkmax= :CapacidadMaxima
					where
            cve_articulo = :cve_articulo;');

            $this->save = \db()->prepare($sql);

            $this->save->bindValue( ':cve_articulo', $_post['cve_articulo'], \PDO::PARAM_STR );
            $this->save->bindValue( ':CapacidadMinima', $_post['stock_minimo'], \PDO::PARAM_STR );
            $this->save->bindValue( ':CapacidadMaxima', $_post['stock_maximo'], \PDO::PARAM_STR );


            $this->save->execute();

if($_post["fotos"][0] != 'noimage.jpg')
{
			 $sql = sprintf('
				delete from c_articulo_imagen
				where
                        cve_articulo = :cve_articulo;');

            $this->save = \db()->prepare($sql);

            $this->save->bindValue( ':cve_articulo', $_post['cve_articulo'], \PDO::PARAM_STR );


            $this->save->execute();
}
            $cve_articulo = $_post['cve_articulo'];


            if($_post['caracteristicas_art'][0])
            {
                $sql_caracteristicas = "DELETE FROM Rel_Art_Carac WHERE Cve_Articulo = '${cve_articulo}'";
                $sth = \db()->prepare($sql_caracteristicas);
                $sth->execute();
                foreach ($_post['caracteristicas_art'] as $selectedOption)
                {
                    $sql = sprintf('
                          INSERT INTO Rel_Art_Carac
                          SET Cve_Articulo = :cve_articulo,
                              Id_Carac = :caracteristicas_art');

                    $this->save = \db()->prepare($sql);
                    $this->save->bindValue( ':caracteristicas_art', $selectedOption, \PDO::PARAM_STR );
                    $this->save->bindValue( ':cve_articulo', $_post['cve_articulo'], \PDO::PARAM_STR );
                    $this->save->execute();
                }
            }
if($_post["fotos"][0] != 'noimage.jpg')
{
      foreach ($_post["fotos"] as $foto)
      {
              $sql = sprintf('
                insert  c_articulo_imagen
                set
                        cve_articulo = :cve_articulo,
                        url = :foto');

            $this->save = \db()->prepare($sql);

            $this->save->bindValue( ':cve_articulo', $_post['cve_articulo'], \PDO::PARAM_STR );
            $this->save->bindValue( ':foto', $foto, \PDO::PARAM_STR );


            $this->save->execute();
        /*
        $command = escapeshellcmd('img/articulo/permisos.py');
        shell_exec($command);
        */
        //chmod("../../../img/articulo/".$foto, 755);
        //chgrp("../../../img/articulo/",'proftpd');
        //chown("../../../img/articulo/".$foto, "nobody");
        
        
            }
}

            $cve_articulo = $_post['cve_articulo'];
            $descripcion_caja = $_post['descripcion_caja'];
            $altotc = $_post['altotc'];
            $anchotc = $_post['anchotc'];
            $fondotc = $_post['fondotc'];
            $peso_caja = $_post['peso_caja'];

          $sql_c_tipo_caja = "
            INSERT INTO c_tipocaja(clave, descripcion, alto, ancho, largo, peso) VALUES ('CO${cve_articulo}', '${descripcion_caja}', '${altotc}', '${anchotc}', '${fondotc}', '${peso_caja}') 
                ON DUPLICATE KEY UPDATE descripcion = '${descripcion_caja}', alto = '${altotc}', ancho = '${anchotc}', largo = '${fondotc}', peso = '${peso_caja}'";
          $sth = \db()->prepare($sql_c_tipo_caja);
          $sth->execute();

          $sql_c_tipo_caja = "
            UPDATE c_articulo SET tipo_caja = (SELECT DISTINCT id_tipocaja FROM c_tipocaja WHERE clave = 'CO${cve_articulo}') WHERE cve_articulo = '${cve_articulo}'";
          $sth = \db()->prepare($sql_c_tipo_caja);
          $sth->execute();

/*
            $sql = sprintf('
              update c_tipocaja
              SET
                  alto  = :altotc,
                  ancho = :anchotc,
                  largo = :fondotc,
                  descripcion = :descripcion_caja,
                  peso = :peso_caja
                where
                  id_tipocaja = :tipo_caja;');

            $this->save = \db()->prepare($sql);

            $this->save->bindValue( ':altotc', $_post['altotc'], \PDO::PARAM_STR );
            $this->save->bindValue( ':anchotc', $_post['anchotc'], \PDO::PARAM_STR );
            $this->save->bindValue( ':fondotc', $_post['fondotc'], \PDO::PARAM_STR );
            $this->save->bindValue( ':descripcion_caja', $_post['descripcion_caja'], \PDO::PARAM_STR );
            $this->save->bindValue( ':peso_caja', $_post['peso_caja'], \PDO::PARAM_STR );
            $this->save->bindValue( ':tipo_caja', $_post['tipo_caja'], \PDO::PARAM_STR );

            $this->save->execute();
*/

        $cve_almac = $_post['cve_almac'];
        $cve_articulo = $_post['cve_articulo'];
        $grupo = $_post['grupo'];
        $clasificacion = $_post['clasificacion'];
        $tipo = $_post['tipo'];

      $sql_grupo = "SELECT id FROM c_gpoarticulo WHERE cve_gpoart = '{$grupo}' AND id_almacen = '{$cve_almac}'";
      $sth = \db()->prepare($sql_grupo);
      $sth->execute();
      $id = $sth->fetch();
      $id_grupo = $id[0];

      $sql_clasificacion = "SELECT id FROM c_sgpoarticulo WHERE cve_sgpoart = '{$clasificacion}' AND id_almacen = '{$cve_almac}'";
      $sth = \db()->prepare($sql_clasificacion);
      $sth->execute();
      $id = $sth->fetch();
      $id_clasificacion = $id[0];

      $sql_tipo_art = "SELECT id FROM c_ssgpoarticulo WHERE cve_ssgpoart = '{$tipo}' AND id_almacen = '{$cve_almac}'";
      $sth = \db()->prepare($sql_tipo_art);
      $sth->execute();
      $id = $sth->fetch();
      $id_tipo_art = $id[0];


            $sql = sprintf('
              UPDATE Rel_Articulo_Almacen
              SET
                  Grupo_ID = :grupo,
                  Clasificacion_ID = :clasificacion,
                  Tipo_Art_ID = :tipo
                where
                  Cve_Articulo = :cve_articulo AND Cve_Almac = :cve_almac;');

            $this->save = \db()->prepare($sql);

            $this->save->bindValue( ':cve_almac', $cve_almac, \PDO::PARAM_STR );
            $this->save->bindValue( ':cve_articulo', $cve_articulo, \PDO::PARAM_STR );
            $this->save->bindValue( ':grupo', $id_grupo, \PDO::PARAM_STR );
            $this->save->bindValue( ':clasificacion', $id_clasificacion, \PDO::PARAM_STR );
            $this->save->bindValue( ':tipo', $id_tipo_art, \PDO::PARAM_STR );

            $this->save->execute();

      $envase1 = "";
      $envase2 = "";
      $cantidad_base1 = "";
      $cantidad_base2 = "";
      $cantidad_equivalente1 = "";
      $cantidad_equivalente2 = "";

        if($_post["envase1"]!="")  
        {
            $envase1               = $_post["envase1"];
            $cantidad_base1        = $_post["cantidad_base1"];
            $cantidad_equivalente1 = $_post["cantidad_equivalente1"];
        }

        if($_post["envase2"]!="")  
        {
            $envase2               = $_post["envase2"];
            $cantidad_base2        = $_post["cantidad_base2"];
            $cantidad_equivalente2 = $_post["cantidad_equivalente2"];
        }

          $sql_envase = "SELECT Id FROM ProductoEnvase WHERE Producto = '{$cve_articulo}' AND IdEmpresa = (SELECT clave FROM c_almacenp WHERE id = '{$cve_almac}') AND Envase = '$envase1'";
          $sth = \db()->prepare($sql_envase);
          $sth->execute();
          $id = $sth->fetch();
          $existe_envase = $id[0];

        if($existe_envase != "" && $envase1 == '')
        {
          $sql_envase = "DELETE FROM ProductoEnvase WHERE Id = '$existe_envase')";
          $sth = \db()->prepare($sql_envase);
          $sth->execute();
        }
        else if($existe_envase != "" && $envase1 != '')
        {
          $sql_envase = "UPDATE ProductoEnvase SET Envase = '$envase1', Cant_Base = '$cantidad_base1', Cant_Eq = '$cantidad_equivalente1' WHERE Id = '$existe_envase'";
          $sth = \db()->prepare($sql_envase);
          $sth->execute();
        }
        else if($existe_envase == "" && $envase1 != '')
        {
          $sql_envase = "INSERT INTO ProductoEnvase(Producto, Envase, Cant_Base, Cant_Eq, Status, IdEmpresa) VALUES('$cve_articulo', '$envase1', '$cantidad_base1', '$cantidad_equivalente1', 'A', (SELECT clave FROM c_almacenp WHERE id = '{$cve_almac}'))";
          $sth = \db()->prepare($sql_envase);
          $sth->execute();
        }

          $sql_envase = "SELECT Id FROM ProductoEnvase WHERE Producto = '{$cve_articulo}' AND IdEmpresa = (SELECT clave FROM c_almacenp WHERE id = '{$cve_almac}') AND Envase = '$envase2'";
          $sth = \db()->prepare($sql_envase);
          $sth->execute();
          $id = $sth->fetch();
          $existe_envase = $id[0];

        if($existe_envase != "" && $envase2 == '')
        {
          $sql_envase = "DELETE FROM ProductoEnvase WHERE Id = '$existe_envase')";
          $sth = \db()->prepare($sql_envase);
          $sth->execute();
        }
        else if($existe_envase != "" && $envase2 != '')
        {
          $sql_envase = "UPDATE ProductoEnvase SET Envase = '$envase2', Cant_Base = '$cantidad_base2', Cant_Eq = '$cantidad_equivalente2' WHERE Id = '$existe_envase'";
          $sth = \db()->prepare($sql_envase);
          $sth->execute();
        }
        else if($existe_envase == "" && $envase2 != '')
        {
          $sql_envase = "INSERT INTO ProductoEnvase(Producto, Envase, Cant_Base, Cant_Eq, Status, IdEmpresa) VALUES('$cve_articulo', '$envase2', '$cantidad_base2', '$cantidad_equivalente2', 'A', (SELECT clave FROM c_almacenp WHERE id = '{$cve_almac}'))";
          $sth = \db()->prepare($sql_envase);
          $sth->execute();
        }
          if($_post["cve_proveedor"]!="") 
          {
              $id_proveedor = $_post["cve_proveedor"];
              $sql_proveedor = "INSERT IGNORE INTO rel_articulo_proveedor(Cve_Articulo, Id_Proveedor) VALUES('{$cve_articulo}', $id_proveedor)";
              $sth = \db()->prepare($sql_proveedor);
              $sth->execute();
          }


        } catch(PDOException $e) {
            return 'ERROR: ' . $e->getMessage();
        }
    }

    function existeCodigoArticulo($data) {
        $sql = "SELECT
                  *
                FROM
                  c_articulo
                WHERE
                  cve_articulo = '".$data["cve_articulo"]."';";

        $rs = mysqli_query(\db2(), $sql) or die("Error description: " . mysqli_error(\db2()));

        $this->data = mysqli_fetch_object($rs);

        if(!$this->data)
            return false;
        else
            return true;
    }

    function exist($data) 
    {
      $clave_art = $data['clave_producto'];
      $search = $data["search"];
      
      $sql="SELECT id FROM `c_articulo` WHERE cve_articulo = '{$clave_art}'";
      $sth = \db()->prepare($sql);
      $sth->execute();
      $id = $sth->fetch();
      if($id[0] != "")
      {
        $sql="SELECT count(*) as x FROM `c_articulo` WHERE ( (cve_codprov = '{$search}') OR (barras2 = '{$search}') OR (barras3 = '{$search}') OR (cve_articulo = '{$search}') ) AND id <> '{$id[0]}'";
      }
      else
      {
        $sql="SELECT count(*) as x FROM `c_articulo` WHERE ( (cve_codprov = '{$search}' AND cve_codprov != '') OR (barras2 = '{$search}' AND barras2 != '') OR (barras3 = '{$search}' AND barras3 != '') OR (cve_articulo = '{$search}' AND cve_articulo != '') )";
      }
      $sth = \db()->prepare($sql);
      $sth->execute();
      $resultado = $sth->fetch();
      if($resultado["x"] >= 1)
      {
        return false;
      }
      return true;
    }

    function existe_en_otro_almacen($data) 
    {
        $clave_art = $data['clave_producto'];
        $id_almacen = $data["id_almacen"];

        $sql = "SELECT * FROM Rel_Articulo_Almacen WHERE Cve_Articulo = '{$clave_art}' AND Cve_Almac != '{$id_almacen}'";

        $sth = \db()->prepare( $sql );
        //$sth->setFetchMode( \PDO::FETCH_CLASS, '\Clientes\Clientes' );
        $sth->execute( array( $clave_art, $id_almacen ) );
        $this->data = $sth->fetch();

        $sql = "SELECT * FROM Rel_Articulo_Almacen WHERE Cve_Articulo = '{$clave_art}' AND Cve_Almac = '{$id_almacen}'";
      
        $sth2 = \db()->prepare( $sql );
        //$sth2->setFetchMode( \PDO::FETCH_CLASS, '\Clientes\Clientes' );
        $sth2->execute( array( $clave_art, $id_almacen ) );
        $this->data2 = $sth2->fetch();

        //if(!$this->data)
        if($sth->rowCount() && !$sth2->rowCount())
        {
          return true; 
        }
        else 
        {
          return false;
        }
    }

    function existeEnUbicacion($data) {
        $sql ='
        SELECT
          *
        FROM
          ts_ubicxart, c_articulo
        WHERE
		c_articulo.id="'.$data["cve_articulo"].'"
          and  c_articulo.cve_articulo=ts_ubicxart.cve_articulo
		  and ts_ubicxart.Activo=1';

        //echo $sql; exit;
        $sth = \db()->prepare( $sql );
        $sth->execute( array( $clave ) );

        $this->data = $sth->fetch();

        if(!$this->data)
            return false;
        else
            return true;
    }

	function loadClasificaciones($clave) {

       $sql = '
	   Select
		c_sgpoarticulo.des_sgpoart as des,
		c_sgpoarticulo.cve_sgpoart as clave
		from c_gpoarticulo
		left join c_sgpoarticulo
		on c_gpoarticulo.cve_gpoart = c_sgpoarticulo.cve_gpoart
		where c_gpoarticulo.cve_gpoart like "'.$clave.'"
		and c_gpoarticulo.activo = 1 and c_sgpoarticulo.activo =1;
		';

        $sth = \db()->prepare( $sql );
		$sth->execute();
		return $sth->fetchAll();

    }

		function loadTipos($clave) {

       $sql = '
	   Select
			c_ssgpoarticulo.des_ssgpoart as tipo,
			c_ssgpoarticulo.cve_ssgpoart as clave
			from c_sgpoarticulo
			left join c_ssgpoarticulo
			on c_sgpoarticulo.cve_sgpoart = c_ssgpoarticulo.cve_sgpoart
			where c_sgpoarticulo.cve_sgpoart like "'.$clave.'"
			and c_sgpoarticulo.activo = 1 and c_ssgpoarticulo.activo =1;
		';

        $sth = \db()->prepare( $sql );
		$sth->execute();
		return $sth->fetchAll();

    }

	function recoveryArticulo( $data ) {

          $sql = "UPDATE " . self::TABLE . "
		  SET Activo = 1 WHERE  id='".$data['id']."';";
          $this->delete = \db()->prepare($sql);
          $this->delete->execute( array(
              $data['ID_Proveedor']
          ) );
    }

	function loadArt( $cve_articulo ) {

          $sql = "Select * from " . self::TABLE . "
		  where Activo = 1 and cve_articulo='".$cve_articulo."';";
          $sth = \db()->prepare( $sql );
		$sth->execute();
		return $sth->fetchAll();
    }
}
