<?php

namespace Articulos;

class Articulos {

    const TABLE = 'c_articulo';
    var $identifier;

    public function __construct( $cve_articulo = false, $key = false )
	{
        if( $cve_articulo )
		{
			$this->cve_articulo = (int) $cve_articulo;
        }
        if($key)
		{
			$sql = sprintf('SELECT cve_articulo FROM %s WHERE cve_articulo = ?',self::TABLE);
            $sth = \db()->prepare($sql);
            $sth->setFetchMode(\PDO::FETCH_CLASS, '\Articulos\Articulos');
            $sth->execute(array($key));
            $articulo = $sth->fetch();
            $this->cve_articulo = $articulo->cve_articulo;
        }
    }

    private function load() 
    {
		try 
		{
			$sql = sprintf('SELECT	c_articulo.cve_almac,
									c_articulo.cve_articulo,
									c_articulo.cve_codprov,
									c_articulo.des_articulo,
									c_articulo.peso,
									c_articulo.costo,
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
									ts_ubicxart.CapacidadMinima as stock_minimo,
									ts_ubicxart.CapacidadMaxima as stock_maximo,
									if(c_articulo.req_refrigeracion="S","1","0") as req_refrigeracion,
									if(c_articulo.mat_peligroso="S","1","0") as mat_peligroso,
									c_articulo.alto,
									c_articulo.fondo,
									c_articulo.ancho,
									c_articulo.tipo_caja
							FROM	%s LEFT JOIN ts_ubicxart on ts_ubicxart.cve_articulo=c_articulo.cve_articulo
							WHERE	c_articulo.id=?',self::TABLE);
			$sth = \db()->prepare( $sql );
			$sth->setFetchMode( \PDO::FETCH_CLASS, '\Articulos\Articulos' );
			$sth->execute( array( $this->cve_articulo ) );
			$this->data = $sth->fetch();
			$sql= "SELECT a.* FROM c_articulo_imagen a INNER JOIN c_articulo p on p.cve_articulo=a.cve_articulo WHERE p.id= '$this->cve_articulo'";
			$sth = \db()->prepare( $sql );
			$sth->setFetchMode( \PDO::FETCH_CLASS, '\Articulos\Articulos' );
			$sth->execute( array( $this->cve_articulo ) );
			@$this->data->fotos=$sth->fetchAll();
		} 
		catch(PDOException $e) 
		{
			echo 'ERROR: ' . $e->getMessage();
		}
	}

    private function loadVer() {
        try {
            $sql = 'Select	c_articulo.*,
							ts_ubicxart.CapacidadMinima as stock_minimo,
							ts_ubicxart.CapacidadMaxima as stock_maximo,
							c_gpoarticulo.des_gpoart as grupo,
							c_sgpoarticulo.des_sgpoart as clasificacion,
							c_ssgpoarticulo.des_ssgpoart as tipo
					FROM    c_articulo left join ts_ubicxart on c_articulo.cve_articulo=ts_ubicxart.cve_articulo
							left join c_gpoarticulo on c_articulo.grupo=c_gpoarticulo.cve_gpoart
							left join c_sgpoarticulo on c_articulo.clasificacion=c_sgpoarticulo.cve_sgpoart
							left join c_ssgpoarticulo on c_articulo.tipo=c_ssgpoarticulo.cve_ssgpoart
					Where	c_articulo.id=?';
            $sth = \db()->prepare( $sql );
            $sth->setFetchMode( \PDO::FETCH_CLASS, '\Articulos\Articulos' );
            $sth->execute( array( $this->cve_articulo ) );
            $this->data = $sth->fetch();
        } catch(PDOException $e) {
            echo 'ERROR: ' . $e->getMessage();
        }
    }

    function __get( $key ) 
    {
		switch($key) 
		{
			case 'cve_articulo':
			case 'id':
				$this->load();
			return @$this->data->$key;
			default:
			return $this->key;
		}
    }

    function __getVer( $key )
	{
		switch($key) {
			case 'cve_articulo':
				$this->loadVer();
				return @$this->data->$key;
			default:
				return $this->key;
		}
    }

    function saveFromAPI( $_post )
	{
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
            $sql = "INSERT	INTO ".self::TABLE."
					SET		cve_almac= (SELECT id FROM c_almacenp WHERE clave = '${cve_almac}'),
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
                            tipo_producto= '${tipo_producto}',
                            umas= '${umas}',
                            tipo_caja= '${tipo_caja}',
                            unidadMedida= '${unidadMedida}',
                            Compuesto= '${compuesto}';
					INSERT	INTO ts_ubicxart
					SET		cve_articulo= '${cve_articulo}',
                            CapacidadMinima= '${stock_minimo}',
                            CapacidadMaxima= '${stock_maximo}';";
            mysqli_multi_query(\db2(), $sql);
            return $_post;
        }
		catch(PDOException $e)
		{
            echo 'ERROR: ' . $e->getMessage();
			return FALSE;
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
		extract($_post);
		$des_articulo=utf8_decode($des_articulo);
		$sql="DELETE from c_articulo_imagen where cve_articulo='${cve_articulo}';
			INSERT INTO ".self::TABLE."
			SET		cve_almac= '${cve_almac}',
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
					Caduca= '${control_caducidad}',
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
					unidadMedida= '${unidadMedida}',
					tipo_caja= '${tipo_caja}',
					Compuesto= '${compuesto}';
			INSERT INTO ts_ubicxart
			SET		cve_articulo= '${cve_articulo}',
					CapacidadMinima= '${stock_minimo}',
					CapacidadMaxima= '${stock_maximo}';";
		foreach (${fotos} as $foto)
		{
			$sql.="INSERT INTO c_articulo_imagen SET cve_articulo= '${cve_articulo}', url= '$foto';";
		}
		mysqli_multi_query(\db2(), $sql);
    }

    function getAll()
	{
        try {
            $sql = 'SELECT * FROM ' . self::TABLE . ' where Activo=1';
            $sth = \db()->prepare( $sql );
            $sth->setFetchMode( \PDO::FETCH_CLASS, '\Articulos\Articulos' );
            $sth->execute();
            return $sth->fetchAll();
        }
		catch(PDOException $e)
		{
            echo 'ERROR: ' . $e->getMessage();
        }
    }
	
    function getAllForDashboard($almacen) 
    {
        try {
            $and = "";
            if(!empty($almacen)){
                $and = " WHERE c_almacenp.clave = {$almacen}";
            }
            $sql = "select *,concat('Articulos: ',total,' |',' Piezas: ',piezas) as texto 
					from(SELECT	sum(x.total) as total,
									sum(x.piezas) as piezas,
									sum(x.activo) as activo,
									sum(x.inactivo) as inactivo
						from(
							SELECT 	COUNT(distinct(a.cve_articulo)) AS total,
									if(a.Activo = 1, 1,0) AS activo,
									if(a.Activo = 0, 1,0) AS inactivo,
									truncate(SUM(V_ExistenciaGralProduccion.Existencia),4) as piezas
							FROM 	".self::TABLE." a inner join V_ExistenciaGralProduccion on V_ExistenciaGralProduccion.cve_articulo = a.cve_articulo
									inner join c_almacenp on c_almacenp.id=V_ExistenciaGralProduccion.cve_almac ".$and."
									and V_ExistenciaGralProduccion.Existencia > 0
									and V_ExistenciaGralProduccion.tipo = 'ubicacion'
							group by a.cve_articulo) x) y;";
            $sth = \db()->prepare( $sql );
            $sth->setFetchMode( \PDO::FETCH_CLASS, '\Articulos\Articulos' );
            $sth->execute();
            return $sth->fetch();
        }
		catch(PDOException $e) {echo 'ERROR: ' . $e->getMessage();}
    }

	function getArtCompuestos($almacen, $start, $length, $search)
	{
        $sql = 'SELECT	des_articulo AS articulo,
						barras2 AS barra_pieza,
						barras3 AS barra_caja,
						cve_articulo AS clave,
						num_multiplo,
						control_lotes,
						control_numero_series
                FROM	' . self::TABLE.' LEFT JOIN c_almacenp on c_articulo.cve_almac=c_almacenp.id
                WHERE 	c_articulo.Activo=1 AND Compuesto="S" AND c_almacenp.clave="'.$almacen.'"';
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
	
    function getArtCompuestosTotalCount($almacen, $search)
	{
        try
		{
            $sql = 'SELECT 	COUNT(cve_articulo) AS total
                    FROM	' . self::TABLE.' LEFT JOIN c_almacenp on c_articulo.cve_almac = c_almacenp.id
                    WHERE	c_articulo.Activo=1 AND Compuesto="S" AND c_almacenp.clave="'.$almacen.'"' ;
            if(!empty($search)){
                $sql .= " AND (c_articulo.des_articulo like '%$search%' OR c_articulo.barras2 like '%$search%' OR c_articulo.barras3 like '%$search%' OR c_articulo.cve_articulo like '%$search%') ";
            }
            $sth = \db()->prepare( $sql );
            $sth->execute();
            return $sth->fetchAll()[0]['total'];
        }
		catch(PDOException $e)
		{
            echo 'ERROR: ' . $e->getMessage();
        }
    }

	function getConLotes() 
	{
        try 
        {
			$sql = "SELECT *
					FROM	". self::TABLE."
					WHERE	Activo=1 AND control_lotes= 'S'";
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

    function borrarArticulo( $data )
	{
        $sql = 'UPDATE '.self::TABLE.' SET Activo=0
				WHERE	id = ?';
        $this->save = \db()->prepare($sql);
        $this->save->execute( array(
            $data['cve_articulo']));
    }

    function actualizarArticulos( $_post )
	{
        try
		{
            if ($_post["control_lotes"]=="true")
			{
                $_post["control_lotes"] = "S";
            }
			else
			{
                $_post["control_lotes"] = "N";
            }
            if ($_post["control_numero_series"]=="true")
			{
                $_post["control_numero_series"] = "S";
            }
			else
			{
                $_post["control_numero_series"] = "N";
            }
            if ($_post["control_peso"]=="true")
			{
                $_post["control_peso"] = "S";
            }
			else
			{
                $_post["control_peso"] = "N";
            }
            if ($_post["control_volumen"]=="true")
			{
                $_post["control_volumen"] = "S";
            }
			else
			{
                $_post["control_volumen"] = "N";
            }
            if ($_post["req_refrigeracion"]=="true")
			{
                $_post["req_refrigeracion"] = "S";
            }
			else
			{
                $_post["req_refrigeracion"] = "N";
            }
            if ($_post["mat_peligroso"]=="true") {
                $_post["mat_peligroso"] = "S";
            }
			else
			{
                $_post["mat_peligroso"] = "N";
            }
			if ($_post["compuesto"]=="true")
			{
                $_post["compuesto"] = "S";
            }
			else
			{
                $_post["compuesto"] = "N";
            }
            $sql = sprintf('update '.self::TABLE.'
							SET 	cve_almac=:cve_almac,
									cve_codprov=:cve_codprov,
									barras2=:barras2,
									barras3=:barras3,
									des_articulo=:des_articulo,
									peso=:peso,
									costo=:costo,
									num_multiplo=:num_multiplo,
									cajas_palet=:cajas_palet,
									control_lotes=:control_lotes,
									control_numero_series=:control_numero_series,
									control_peso=:control_peso,
									control_volumen=:control_volumen,
									req_refrigeracion=:req_refrigeracion,
									clasificacion=:clasificacion,
									tipo=:tipo,
									grupo=:grupo,
									mat_peligroso=:mat_peligroso,
									alto=:alto,
									ancho=:ancho,
									fondo=:fondo,
									tipo_producto=:tipo_producto,
									umas=:umas,
									tipo_caja=:tipo_caja,
									unidadMedida=:unidadMedida,
									Compuesto=:compuesto
							where	cve_articulo = :cve_articulo;');
            $this->save = \db()->prepare($sql);
            $this->save->bindValue( ':cve_almac', $_post['cve_almac'], \PDO::PARAM_STR );
            $this->save->bindValue( ':cve_articulo', $_post['cve_articulo'], \PDO::PARAM_STR );
            $this->save->bindValue( ':cve_codprov', $_post['cve_codprov'], \PDO::PARAM_STR );
            $this->save->bindValue( ':des_articulo', $_post['des_articulo'], \PDO::PARAM_STR );
            $this->save->bindValue( ':peso', $_post['peso'], \PDO::PARAM_STR );
            $this->save->bindValue( ':costo', $_post['costo'], \PDO::PARAM_STR );
            $this->save->bindValue( ':barras2', $_post['barras2'], \PDO::PARAM_STR );
            $this->save->bindValue( ':num_multiplo', $_post['num_multiplo'], \PDO::PARAM_STR );
            $this->save->bindValue( ':barras3', $_post['barras3'], \PDO::PARAM_STR );
            $this->save->bindValue( ':cajas_palet', $_post['cajas_palet'], \PDO::PARAM_STR );
            $this->save->bindValue( ':control_lotes', $_post['control_lotes'], \PDO::PARAM_STR );
            $this->save->bindValue( ':control_numero_series', $_post['control_numero_series'], \PDO::PARAM_STR );
            $this->save->bindValue( ':control_peso', $_post['control_peso'], \PDO::PARAM_STR );
            $this->save->bindValue( ':control_volumen', $_post['control_volumen'], \PDO::PARAM_STR );
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
            $this->save->bindValue( ':ancho', $_post['ancho'], \PDO::PARAM_STR );
            $this->save->bindValue( ':tipo_caja', $_post['tipo_caja'], \PDO::PARAM_STR );
			$this->save->bindValue( ':compuesto', $_post['compuesto'], \PDO::PARAM_STR );
            $this->save->execute();
            $sql = sprintf('update 	ts_ubicxart
							SET 	CapacidadMinima=:CapacidadMinima,
									CapacidadMaxima=:CapacidadMaxima
							where 	cve_articulo = :cve_articulo;');
            $this->save = \db()->prepare($sql);
            $this->save->bindValue( ':cve_articulo', $_post['cve_articulo'], \PDO::PARAM_STR );
            $this->save->bindValue( ':CapacidadMinima', $_post['stock_minimo'], \PDO::PARAM_STR );
            $this->save->bindValue( ':CapacidadMaxima', $_post['stock_maximo'], \PDO::PARAM_STR );
            $this->save->execute();
			$sql = sprintf('delete from c_articulo_imagen where cve_articulo=:cve_articulo;');
            $this->save = \db()->prepare($sql);
            $this->save->bindValue( ':cve_articulo', $_post['cve_articulo'], \PDO::PARAM_STR );
            $this->save->execute();
			foreach ($_post["fotos"] as $foto)
			{
				$sql = sprintf('insert c_articulo_imagen set cve_articulo = :cve_articulo,url=:foto');
				$this->save = \db()->prepare($sql);
				$this->save->bindValue( ':cve_articulo', $_post['cve_articulo'], \PDO::PARAM_STR );
				$this->save->bindValue( ':foto', $foto, \PDO::PARAM_STR );
				$this->save->execute();
			}
        }
		catch(PDOException $e)
		{
            return 'ERROR: ' . $e->getMessage();
        }
    }

    function existeCodigoArticulo($data)
	{
        $sql = "SELECT * FROM c_articulo WHERE cve_articulo='".$data["cve_articulo"]."';";
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
			$sql="SELECT count(*) as x FROM `c_articulo` WHERE ( (cve_codprov = '{$search}') || (barras2 = '{$search}') || (barras3 = '{$search}') || (cve_articulo = '{$search}') ) AND id <> '{$id[0]}'";
		}
		else
		{
			$sql="SELECT count(*) as x FROM `c_articulo` WHERE ( (cve_codprov = '{$search}') || (barras2 = '{$search}') || (barras3 = '{$search}') || (cve_articulo = '{$search}') )";
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

    function existeEnUbicacion($data)
	{
        $sql ='SELECT * FROM ts_ubicxart, c_articulo WHERE c_articulo.id="'.$data["cve_articulo"].'" and  c_articulo.cve_articulo=ts_ubicxart.cve_articulo and ts_ubicxart.Activo=1';
        $sth = \db()->prepare( $sql );
        $sth->execute( array( $clave ) );
        $this->data = $sth->fetch();
        if(!$this->data)
            return false;
        else
            return true;
    }

	function loadClasificaciones($clave)
	{
		$sql = 'Select 	c_sgpoarticulo.des_sgpoart as des,c_sgpoarticulo.cve_sgpoart as clave
				from	c_gpoarticulo left join c_sgpoarticulo on c_gpoarticulo.cve_gpoart = c_sgpoarticulo.cve_gpoart
				where	c_gpoarticulo.cve_gpoart like "'.$clave.'" and c_gpoarticulo.activo = 1 and c_sgpoarticulo.activo =1;';
		$sth = \db()->prepare( $sql );
		$sth->execute();
		return $sth->fetchAll();
    }

	function loadTipos($clave)
	{
		$sql = 'Select	c_ssgpoarticulo.des_ssgpoart as tipo,c_ssgpoarticulo.cve_ssgpoart as clave
				from	c_sgpoarticulo left join c_ssgpoarticulo on c_sgpoarticulo.cve_sgpoart = c_ssgpoarticulo.cve_sgpoart
				where	c_sgpoarticulo.cve_sgpoart like "'.$clave.'" and c_sgpoarticulo.activo = 1 and c_ssgpoarticulo.activo =1;';
        $sth = \db()->prepare( $sql );
		$sth->execute();
		return $sth->fetchAll();
    }

	function recoveryArticulo( $data )
	{
		$sql = "UPDATE " . self::TABLE . " SET Activo = 1 WHERE  id='".$data['id']."';";
		$this->delete = \db()->prepare($sql);
		$this->delete->execute( array(
			$data['ID_Proveedor']
		) );
    }

	function loadArt( $cve_articulo )
	{
		$sql = "Select * from " . self::TABLE . " where Activo = 1 and cve_articulo='".$cve_articulo."';";
		$sth = \db()->prepare( $sql );
		$sth->execute();
		return $sth->fetchAll();
    }
}
