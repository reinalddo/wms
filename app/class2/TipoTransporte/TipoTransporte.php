<?php

  namespace TipoTransporte;

  class TipoTransporte {

    const TABLE = 'tipo_transporte';
    var $identifier;

    public function __construct( $clave_ttransporte = false, $key = false ) {

      if( $clave_ttransporte ) {
        $this->clave_ttransporte = (int) $clave_ttransporte;
      }

      if($key) {

        $sql = sprintf('
          SELECT
            clave_ttransporte
          FROM
            %s
          WHERE
            clave_ttransporte = ?
        ',
          self::TABLE
        );

        $sth = \db()->prepare($sql);
        $sth->setFetchMode(\PDO::FETCH_CLASS, '\TipoTransporte\TipoTransporte');
        $sth->execute(array($key));

        $TipoTransporte = $sth->fetch();

        $this->clave_ttransporte = $TipoTransporte->clave_ttransporte;

      }

    }

    private function load() {

      $sql = sprintf('
        SELECT
         ' . self::TABLE . '.*
		 ,((alto/1000)*(ancho/1000)*(fondo/1000)) as capacidad_volumetrica
        FROM
          %s
        WHERE
          clave_ttransporte = ?
      ',
        self::TABLE
      );

      $sth = \db()->prepare( $sql );
      $sth->setFetchMode( \PDO::FETCH_CLASS, '\TipoTransporte\TipoTransporte' );
      $sth->execute( array( $this->clave_ttransporte ) );

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
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\TipoTransporte\TipoTransporte' );
        @$sth->execute( array( clave_ttransporte ) );

        return $sth->fetchAll();

    }
	
    function __get( $key ) {

      switch($key) {
        case 'id':
		case 'clave_ttransporte':
        case 'alto':
        case 'fondo':
        case 'ancho':
        case 'capacidad_carga':
        case 'desc_ttransporte':
        case 'imagen':
          $this->load();
          return @$this->data->$key;
        default:
          return $this->key;
      }

    }

      function save( $data ) {
          try {
			  
        $sql = sprintf('
        INSERT INTO
          ' . self::TABLE . '
        SET
		clave_ttransporte = :clave_ttransporte
       ,alto = :alto
       ,fondo = :fondo
       ,ancho = :ancho
       ,capacidad_carga = :capacidad_carga
       ,desc_ttransporte = :desc_ttransporte
       ,imagen = :imagen
      
      ');
	  
	        $this->save = \db()->prepare($sql);

      //$this->save->bindValue( ':ID_Proveedor', $this->ID_Proveedor, \PDO::PARAM_STR );
      $this->save->bindValue( ':clave_ttransporte', $data['clave_ttransporte'], \PDO::PARAM_STR );
      $this->save->bindValue( ':alto', $data['alto'], \PDO::PARAM_STR );
      $this->save->bindValue( ':fondo', $data['fondo'], \PDO::PARAM_STR );
      $this->save->bindValue( ':capacidad_carga', $data['capacidad_carga'], \PDO::PARAM_STR );
      $this->save->bindValue( ':ancho', $data['ancho'], \PDO::PARAM_STR );
      $this->save->bindValue( ':desc_ttransporte', $data['desc_ttransporte'], \PDO::PARAM_STR );
      $this->save->bindValue( ':imagen', $data['imagen'], \PDO::PARAM_STR );

      $this->save->execute();


	  
	  
	  
          } catch(PDOException $e) {
              return 'ERROR: ' . $e->getMessage();
          }

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

	function actualizarTipoTransporte( $data ) {
        $sql = sprintf('
        UPDATE
          ' . self::TABLE . '
        SET
        alto = :alto
       ,fondo = :fondo
       ,ancho = :ancho
       ,capacidad_carga = :capacidad_carga
       ,desc_ttransporte = :desc_ttransporte
       ,imagen = :imagen
	   where
	   clave_ttransporte = :clave_ttransporte
	   
      
      ');
	  
	        $this->save = \db()->prepare($sql);

      //$this->save->bindValue( ':ID_Proveedor', $this->ID_Proveedor, \PDO::PARAM_STR );
      $this->save->bindValue( ':clave_ttransporte', $data['clave_ttransporte'], \PDO::PARAM_STR );
      $this->save->bindValue( ':alto', $data['alto'], \PDO::PARAM_STR );
      $this->save->bindValue( ':fondo', $data['fondo'], \PDO::PARAM_STR );
      $this->save->bindValue( ':capacidad_carga', $data['capacidad_carga'], \PDO::PARAM_STR );
      $this->save->bindValue( ':ancho', $data['ancho'], \PDO::PARAM_STR );
      $this->save->bindValue( ':desc_ttransporte', $data['desc_ttransporte'], \PDO::PARAM_STR );
      $this->save->bindValue( ':imagen', $data['imagen'], \PDO::PARAM_STR );

      $this->save->execute();


	  

    }

      function borrarTipoTransporte( $data ) {
          $sql = '
        UPDATE
          ' . self::TABLE . '
        SET
          Activo = 0
        WHERE
          clave_ttransporte = ?
      ';
          $this->save = \db()->prepare($sql);
          $this->save->execute( array(
              $data['clave_ttransporte']
          ) );
      }
	  
	  function exist($clave_ttransporte) {

      $sql = sprintf('
        SELECT
          *
        FROM
			' . self::TABLE . '
        WHERE
          clave_ttransporte = ?
      ',
        self::TABLE
      );

      $sth = \db()->prepare( $sql );
	  
       $sth->setFetchMode( \PDO::FETCH_CLASS, '\TipoTransporte\TipoTransporte' );
      $sth->execute( array( $clave_ttransporte ) );

      $this->data = $sth->fetch();
	  
	  if(!$this->data)
		  return false; 
				else 
					return true;
    }

	function recoveryTipoTransporte( $data ) {

          $sql = "UPDATE " . self::TABLE . "
		  SET Activo = 1 WHERE  id='".$data['id']."';";
          $this->delete = \db()->prepare($sql);
          $this->delete->execute( array(
              $data['id']
          ) );
    }
public function inUse( $data ) {

      $sql = "SELECT tipo_transporte FROM `t_transporte` WHERE tipo_transporte='".$data['clave_ttransporte']."'";
      $sth = \db()->prepare($sql);
      $sth->execute();
      $data = $sth->fetch();
    
    if ($data['tipo_transporte']) 
        return true;
    else
        return false;
  }  

  }
