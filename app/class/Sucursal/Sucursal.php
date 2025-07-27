<?php

  namespace Sucursal;

  class Sucursal {

    const TABLE = 'c_almacen';
    const COMPANIA = 'c_sucursal';
    const TIPO = 'c_tipocia';

    var $identifier;
    var $clavecomp;

   

     function load($id) {

      $sql = sprintf('
        SELECT
          *
        FROM
          c_sucursal
        WHERE
          id = ?
      ',
        self::TABLE
      );

      $sth = \db()->prepare( $sql );
      $sth->setFetchMode( \PDO::FETCH_CLASS, '\Sucursal\Sucursal' );
      $sth->execute( array( $id ) );

      $this->data = $sth->fetch();

    }
	
		   function exist($clave_empresa) {

      $sql = sprintf('
        SELECT
          *
        FROM
          c_sucursal
        WHERE
          clave_sucursal = ?
      ',
        self::TABLE
      );

      $sth = \db()->prepare( $sql );
	  
      $sth->setFetchMode( \PDO::FETCH_CLASS, '\Sucursal\Sucursal' );
      $sth->execute( array( $clave_empresa ) );

      $this->data = $sth->fetch();
	  
	  if(!$this->data)
		  return false; 
				else 
					return true;
    }
	


      function getAll() {

        $sql = '
        SELECT
          *
        FROM
          c_sucursal
		  where Activo=1
      ';

        $sth = \db()->prepare( $sql );
      //  $sth->setFetchMode( \PDO::FETCH_CLASS, '\Sucursal\Sucursal' );
        $sth->execute();

        return $sth->fetchAll();

    }


      function borrarSucursal( $data ) {
          $sql = '
        UPDATE
         c_sucursal
        SET
          Activo = 0
        WHERE
          id = ?
      ';
          $this->save = \db()->prepare($sql);
          $this->save->execute( array(
              $data['id']
          ) );
      }



    function save( $data ) {

      $sql = sprintf('
        INSERT INTO
          ' . self::COMPANIA . '
        SET
          distrito = :distrito
        , cve_cia = :cve_cia
        , des_cia = :des_cia
        , des_rfc = :des_rfc
        , des_direcc = :des_direcc
        , des_cp = :des_cp
        , des_telef = :des_telef										
        , des_contacto = :des_contacto
        , des_email = :des_email
        , des_observ = :des_observ  
        , clave_sucursal = :clave_sucursal    
		    , imagen = :imagen    		
      ');

      $this->save = \db()->prepare($sql);

      //$this->save->bindValue( ':ID_Proveedor', $this->ID_Proveedor, \PDO::PARAM_STR );
      $this->save->bindValue( ':distrito', $data['distrito'], \PDO::PARAM_STR );
      $this->save->bindValue( ':cve_cia', $data['cve_cia'], \PDO::PARAM_STR );
      $this->save->bindValue( ':des_cia', $data['des_cia'], \PDO::PARAM_STR );
      $this->save->bindValue( ':des_rfc', $data['des_rfc'], \PDO::PARAM_STR );
      $this->save->bindValue( ':des_direcc', $data['des_direcc'], \PDO::PARAM_STR );
      $this->save->bindValue( ':des_cp', $data['des_cp'], \PDO::PARAM_STR );
      $this->save->bindValue( ':des_telef', $data['des_telef'], \PDO::PARAM_STR );
      $this->save->bindValue( ':des_contacto', $data['des_contacto'], \PDO::PARAM_STR );
      $this->save->bindValue( ':des_email', $data['des_email'], \PDO::PARAM_STR );
      $this->save->bindValue( ':des_observ', $data['des_observ'], \PDO::PARAM_STR );
	    $this->save->bindValue( ':imagen', $data['imagen'], \PDO::PARAM_STR );
      $this->save->bindValue( ':clave_sucursal', $data['clave_sucursal'], \PDO::PARAM_STR );

      $this->save->execute();

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

	function actualizarCompa( $data ) {
		//var_dump($data); exit;
      $sql = '
        UPDATE
          ' . self::COMPANIA . '
        SET
          clave_sucursal = ?
        , distrito = ?
        , cve_cia = ?
        , des_cia = ?
        , des_rfc = ?
        , des_direcc = ?
        , des_cp = ?
        , des_telef = ?
        , des_contacto = ?
        , des_email = ?												
        , des_observ = ?  
		, imagen = "'.$data["imagen"].'"  		
        WHERE
          id = ?
      ';
      $this->save = \db()->prepare($sql);
      $this->save->execute( array(
        $data['clave_sucursal']
      , $data['distrito']
      , $data['cve_cia']
      , $data['des_cia']
	  , $data['des_rfc']
	  , $data['des_direcc']
	  , $data['des_cp']
	  , $data['des_telef']
	  , $data['des_contacto']
	  , $data['des_email']
	  , $data['des_observ']
      , $data['id']
      ) );
    }


  function recovery( $data ) {

          $sql = "UPDATE c_sucursal SET Activo = 1 WHERE  id='".$data['id']."';";
          $this->delete = \db()->prepare($sql);
          $this->delete->execute( array(
              $data['cve_cia']
          ) );
  }
}

