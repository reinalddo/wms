<?php

namespace TipoDeRecursos;

$tools = new \Tools\Tools();
//ToDo: Corregir todas las consultas para ver el detalle, editar, borrar y consultar. 
class TipoDeRecursos {

    const TABLE = 'c_recursos';
    var $identifier;

    public function __construct( $cve_articulo = false, $key = false ) {
      
        $this->tools = new \Tools\Tools();

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

    private function load() {
        try {
            $sql = 'SELECT * FROM c_recursos Where c_recursos.id = '.$this->id;
          
            //echo var_dump($sql);
            //die();
          
            $sth = \db()->prepare( $sql );
            //$sth->setFetchMode( \PDO::FETCH_CLASS, '\Presupuestos\Presupuestos' );
            $sth->execute( array( $this->id ) );
            $sth->execute();
            $this->data = $sth->fetch();
        } catch(PDOException $e) {
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

    function __get( $key ) {

        switch($key) {
            case 'id':
                $this->load();
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
                            tipo_caja= '${tipo_caja}',
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

    function save($_post) {
        try {
          extract($_post);
          $sql = "INSERT INTO ".self::TABLE."
                  SET
                  nombreDeRecurso= '${nombreDeRecurso}'";
        
          $sth = \db()->prepare( $sql );
          $sth->execute();
          
        }     
        catch(PDOException $e)  
        {
        echo 'ERROR: ' . $e->getMessage();
        }
    }


    function getAll() {
        try {

            $sql = '
                SELECT
                *
                FROM
                ' . self::TABLE.'';

            $sth = \db()->prepare( $sql );
            $sth->setFetchMode( \PDO::FETCH_CLASS, '\Articulos\Articulos' );
            $sth->execute();

            return $sth->fetchAll();
        } catch(PDOException $e) {
            echo 'ERROR: ' . $e->getMessage();
        }

    }
    function getAllForDashboard($almacen) {
        try {

            $sql = 'SELECT COUNT(a.id) AS total,
                    SUM(CASE WHEN a.Activo = 1 then 1 else 0 end) AS activo,
                    SUM(CASE WHEN a.Activo = 0 then 1 else 0 end) AS inactivo
                    FROM '.self::TABLE.' a
                    INNER JOIN c_almacenp ap on a.cve_almac=ap.id';

            if(!empty($almacen)){
                $sql .= " WHERE ap.clave = {$almacen}";
            }
            $sth = \db()->prepare( $sql );
            $sth->setFetchMode( \PDO::FETCH_CLASS, '\Articulos\Articulos' );
            $sth->execute();
            return $sth->fetch();
        } catch(PDOException $e) {
            echo 'ERROR: ' . $e->getMessage();
        }

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


	    function getConLotes() {
        try {

            $sql = '
                SELECT
                *
                FROM
                ' . self::TABLE.'
				where Activo=1 and control_lotes="S"';

            $sth = \db()->prepare( $sql );
            $sth->setFetchMode( \PDO::FETCH_CLASS, '\Articulos\Articulos' );
            $sth->execute();

            return $sth->fetchAll();
        } catch(PDOException $e) {
            echo 'ERROR: ' . $e->getMessage();
        }

    }

    function borrarPresupuesto( $data ) {
        $sql = "DELETE 
                FROM ". self::TABLE ." 
                WHERE id = '".$data["id"]."';";
        $sth = \db()->prepare($sql);
        $sth->execute();
      //return $sth->fetchAll();
    }

    function actualizarRecursos($_post) 
    {
      try
      {

        $to_update_c_recursos = array(
          "nombreDeRecurso"     => $_post['nombreDeRecurso'],
          
        );
        
        $where_update = array(
          "id"    => $_post['id']
          
        );
        
        $insert_orden = $this->tools->dbUpdate(self::TABLE,$to_update_c_recursos,$where_update);
      } 
      catch(Exception $e) 
      {
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

    function exist($data) {

		if ($data["barras3"]=="") return false;
		if ($data["barras2"]=="") return false;
	    $sql = "SELECT
                  *
                FROM
                  c_articulo
                WHERE
                  barras3 = '".$data["barras3"]."'
                  OR barras2 = '".$data["barras2"]."'
                  OR cve_articulo = '".$data["cve_articulo"]."'
                  OR cve_codprov = '".$data["cve_codprov"]."';";

        $rs = mysqli_query(\db2(), $sql) or die("Error description: " . mysqli_error(\db2()));

        $this->data = mysqli_fetch_object($rs);

        if(!$this->data)
            return false;
        else
            return true;
    }

    function existeEnUbicacion($data) {
        $sql ='SELECT * FROM
              c_recursos
              WHERE
              c_recursos.id="'.$data["id"].'"';

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
