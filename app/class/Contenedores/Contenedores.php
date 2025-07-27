<?php

  namespace Contenedores;
$tools = new \Tools\Tools();

  class Contenedores {

    const TABLE = 'c_charolas';
    var $identifier;

    public function __construct( $IDContenedor = false, $key = false ) {

      if( $IDContenedor ) {
        $this->IDContenedor = (int) $IDContenedor;
      }

      if($key) {

        $sql = sprintf('
          SELECT
            IDContenedor
          FROM
            %s
          WHERE
            IDContenedor = ?
        ',
          self::TABLE
        );

        $sth = \db()->prepare($sql);
        $sth->setFetchMode(\PDO::FETCH_CLASS, '\Contenedores\Contenedores');
        $sth->execute(array($key));

        $contenedor = $sth->fetch();

        $this->IDContenedor = $contenedor->IDContenedor;

      }

    }

    private function load() {

      $sql = sprintf('
        SELECT
          *
        FROM
          %s
        WHERE
          IDContenedor = ?
      ',
        self::TABLE
      );

      $sth = \db()->prepare( $sql );
      $sth->setFetchMode( \PDO::FETCH_CLASS, '\Contenedores\Contenedores' );
      $sth->execute( array( $this->IDContenedor ) );

      $this->data = $sth->fetch();

    }

    function getAll() {

        $sql = '
        SELECT
          *
        FROM
          ' . self::TABLE . '
      ';

        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\Contenedores\Contenedores' );
        $sth->execute( array( IDContenedor ) );

        return $sth->fetchAll();

    }
	
    function __get( $key ) {

      switch($key) {
        case 'IDContenedor':
          $this->load();
          return @$this->data->$key;
        case 'cve_almac':
        case 'charola':
        case 'tipo':
          $this->load();
          return @$this->data->$key;
        default:
          return $this->key;
      }

    }
	
	   function exist($clave_contenedor) {

      $sql = sprintf('
        SELECT
          *
        FROM
          c_charolas
		  WHERE
          clave_contenedor = ?
      ',
        self::TABLE
      );

      $sth = \db()->prepare( $sql );
	  
      $sth->setFetchMode( \PDO::FETCH_CLASS, '\Contenedores\Contenedores' );
      $sth->execute( array( $clave_contenedor ) );

      $this->data = $sth->fetch();
	  
	  if(!$this->data)
		  return false; 
				else 
					return true;
    }

    function save( $_post ) {

      //$tipoGen = 0;
      if( !$_post['cve_almac'] ) { throw new \ErrorException( 'cve_almac is required.' ); }
      if( !$_post['tipo'] ) { throw new \ErrorException( 'tipo is required.' ); }
	    //if( isset($_post['tipoGen'])) $tipoGen = 1;

//, Permanente = 0
      $sql = sprintf('
        INSERT INTO
          ' . self::TABLE . '
        SET
          cve_almac = :cve_almac
        , tipo = :tipo 
		, clave_contenedor= :clave_contenedor
		, descripcion= :descripcion
		, ancho = :ancho
		, alto= :alto
		, fondo= :alto
		, peso = :peso
		, pesomax = :pesomax
		, capavol = :capavol
    , TipoGen = :tipoGen
      ');

        $this->save = \db()->prepare($sql);

        //$this->save->bindValue( ':ID_Proveedor', $this->ID_Proveedor, \PDO::PARAM_STR );
       $this->save->bindValue( ':cve_almac', $_post['cve_almac'], \PDO::PARAM_STR );
       $this->save->bindValue( ':tipo', $_post['tipo'], \PDO::PARAM_STR );
       $this->save->bindValue( ':tipoGen', $_post['tipoGen'], \PDO::PARAM_STR );
	   $this->save->bindValue( ':clave_contenedor', $_post['clave_contenedor'], \PDO::PARAM_STR );
	   $this->save->bindValue( ':descripcion', $_post['descripcion'], \PDO::PARAM_STR );
	   $this->save->bindValue( ':ancho', $_post['ancho'], \PDO::PARAM_STR );
	   $this->save->bindValue( ':alto', $_post['alto'], \PDO::PARAM_STR );
	   $this->save->bindValue( ':fondo', $_post['fondo'], \PDO::PARAM_STR );
	   $this->save->bindValue( ':peso', $_post['peso'], \PDO::PARAM_STR );
	   $this->save->bindValue( ':pesomax', $_post['pesomax'], \PDO::PARAM_STR );
	   $this->save->bindValue( ':capavol', $_post['capavol'], \PDO::PARAM_STR );	   

        $this->save->execute();
    }

	function actualizarContenedor( $data ) {
//Permanente = ".$data['tipoGen'].",
	  $sql = "UPDATE " . self::TABLE . " 
		SET
			cve_almac = '".$data['cve_almac']."', 
			descripcion = '".$data['descripcion']."', 
			alto = '".$data['alto']."',
			ancho = '".$data['ancho']."',
      tipo = '".$data['tipo']."',        
      fondo = '".$data['fondo']."', 
			clave_contenedor = '".$data['clave_contenedor']."',
			peso = '".$data['peso']."',
			pesomax = '".$data['pesomax']."',	
      TipoGen = '".$data['tipoGen']."',
			capavol = '".$data['capavol']."'	
		WHERE clave_contenedor = '".$data['clave_contenedor']."'";
        $rs = mysqli_query(\db2(), $sql) or die(mysqli_error(\db2()));
    }

      function borrarContenedor( $data ) {
          $sql = '
        UPDATE
          ' . self::TABLE . '
        SET
          Activo = 0
        WHERE
          clave_contenedor = ?
      ';
          $this->save = \db()->prepare($sql);
          $this->save->execute( array(
              $data['clave_contenedor']
          ) );
      }
	  
    /*function password( $data ) {

      if( !$data['password'] ) { throw new \ErrorException( 'Unfortuantly you wont get far without a password.' ); }

      $sql = sprintf('
        UPDATE
          ' . self::TABLE . '
        SET
          password = :password
        WHERE
          id_user = ' . $this->id_user . '
      ');

      $this->save = \db()->prepare($sql);

      $password = password_hash( $data['password'], PASSWORD_BCRYPT, array( 'cost' => 11 ) );

      $this->save->bindValue( ':password', $password, \PDO::PARAM_STR );
      $this->save->execute();

    }*/

	  	function recovery( $data ) {

          $sql = "UPDATE " . self::TABLE . "
		  SET Activo = 1 WHERE  IDContenedor='".$data['IDContenedor']."';";
          $this->delete = \db()->prepare($sql);
          $this->delete->execute( array(
              $data['IDContenedor']
          ) );
    }


    /*function settings_design( $data ) {

      $sql = '
        UPDATE
          ' . self::TABLE . '
        SET
          Empresa = ?
        , VendId = ?
        , ID_Externo = ?
        WHERE
          ID_Proveedor = ?
      ';

      $this->save = \db()->prepare($sql);
      $this->save->execute( array(
        $data['Empresa']
      , $data['VendId']
      , $data['ID_Externo']
      ) );

    }*/

 public function inUse( $data ) {

      $sql = "SELECT clave FROM `c_almacenp` WHERE clave ='".$data['clave']."'";
      $sth = \db()->prepare($sql);
      $sth->execute();
      $data = $sth->fetch();
    
    if ($data['clave']) 
        return true;
    else
        return false;
  }  
    
    function loadcon( $data ) {
      $clave = 
      $sql = "
            SELECT
                V_EntradasContenedores.Clave_Contenedor,
                c_charolas.IDContenedor as IDContenedor,
                c_charolas.tipo as tipo,
                SUM(IFNULL(CAST(((c_articulo.alto / 1000) * (c_articulo.ancho / 1000) * (c_articulo.fondo / 1000) * V_EntradasContenedores.CantidadRecibida) AS DECIMAL(10,6)), 0)) as volumen_ocupado,
                SUM(IFNULL(CAST((c_articulo.peso * V_EntradasContenedores.CantidadRecibida) as DECIMAL(10,2)), 0)) as peso_total
            FROM V_EntradasContenedores
                LEFT JOIN c_articulo on c_articulo.cve_articulo = V_EntradasContenedores.Cve_articulo
                LEFT JOIN c_charolas on c_charolas.clave_contenedor = V_EntradasContenedores.Clave_Contenedor
            WHERE  V_EntradasContenedores.Clave_Contenedor ='{$data["clave_contenedor"]}';";
    //  echo var_dump($sql);
    //  die();
      $sth = \db()->prepare($sql);
      $sth->setFetchMode( \PDO::FETCH_CLASS, '\OrdenCompra\OrdenCompra' );
      $sth->execute();
      return $sth->fetchAll();
    
  }  

    function GenerarLP( $data ) {

      //******************************************************************************************
//******************************************************************************************
      $lps_generados = array();
      $conexion = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
          $almacen = $data["cve_almac"];
          $cantidad = $data["cantidad"];
          $prefijo = $data["prefijo"];
          $TipoGen = $data["pallet_generico"];
          $sql_history = "";
          $error_history = "";
          $id_history = "";
                  for($i = 0; $i < $cantidad; $i++)
                  {
                        //$sql ="SELECT AUTO_INCREMENT  AS id FROM information_schema.TABLES                               WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'c_charolas'";
                        $sql = "SELECT MAX(IDContenedor) as id FROM c_charolas";
                               $sql_history .= $sql." ----- ";
                        if(!$res_id = mysqli_query($conexion, $sql)) {echo "Falló la preparación: (".mysqli_error($conexion).")"; $error_history .= mysqli_error($conexion);}
                        $row_autoid = mysqli_fetch_assoc($res_id);
                        $nextid = $row_autoid['id'];
                        $id_history .= $nextid." ------- ";

                        $label_lp = $prefijo.str_pad($nextid, 8, "0", STR_PAD_LEFT).($i+1);
                        $lps_generados[] = $label_lp;

                           $clave_contenedor = $label_lp;
                        //else
                          //$clave_contenedor .= "-".$nextid;
                          $sqlGuardar = "INSERT IGNORE INTO c_charolas(cve_almac, clave_contenedor, descripcion, Permanente, tipo, Activo, alto, ancho, fondo, peso, pesomax, capavol, CveLP, TipoGen) VALUES('$almacen', '$label_lp', '$label_lp', 0, 'Pallet', '1', '0', '0', '0', '0', '0', '0', '$label_lp', $TipoGen)";

                          if($TipoGen == 1)
                            $sqlGuardar = "INSERT IGNORE INTO c_charolas(cve_almac, clave_contenedor, descripcion, Permanente, tipo, Activo, alto, ancho, fondo, peso, pesomax, capavol, TipoGen) VALUES('$almacen', '$label_lp', '$label_lp', 0, 'Pallet', '1', '0', '0', '0', '0', '0', '0', $TipoGen)";
                          $sql_history .= $sqlGuardar." ----- ";

                          if(!$res_id = mysqli_query($conexion, $sqlGuardar)) {echo "Falló la preparación: (".mysqli_error($conexion).")"; $error_history .= mysqli_error($conexion);}
                  }
//******************************************************************************************
//******************************************************************************************

      /*
      $sth = \db()->prepare($sql);
      $sth->setFetchMode( \PDO::FETCH_CLASS, '\OrdenCompra\OrdenCompra' );
      $sth->execute();
      return $sth->fetchAll();
      */

      return $lps_generados;
    
  }  

  }
