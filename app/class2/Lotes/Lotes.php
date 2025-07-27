<?php

  namespace Lotes;

  class Lotes {

    const TABLE = 'c_lotes';
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
        $sth->setFetchMode(\PDO::FETCH_CLASS, '\Lotes\Lotes');
        $sth->execute(array($key));

        $lotes = $sth->fetch();

        $this->lotes = $lotes->lotes;

      }

    }

    function getAll($filtro = "") {

        $sql = '

        SELECT DISTINCT

          Lote

        FROM

          ' . self::TABLE . '
      WHERE Activo=1 '.$filtro.'';



        $sth = \db()->prepare( $sql );

        $sth->setFetchMode( \PDO::FETCH_CLASS, '\Lotes\Lotes' );

        $sth->execute();



        return $sth->fetchAll();



    }

    function getAllLS() {

        $sql = '

        SELECT DISTINCT 

          L.Lote as Lote

        FROM

          c_lotes L
      WHERE L.Activo=1

      UNION 

        SELECT DISTINCT 

          S.numero_serie as Lote

        FROM

          c_serie S
      ';



        $sth = \db()->prepare( $sql );

        $sth->setFetchMode( \PDO::FETCH_CLASS, '\Lotes\Lotes' );

        $sth->execute();



        return $sth->fetchAll();



    }

    private function load() {

      $sql = "
        SELECT
          *
        FROM
          ".self::TABLE."
        WHERE
          LOTE = ?
		  and Activo=1 ";
            

      $sth = \db()->prepare( $sql );
      $sth->setFetchMode( \PDO::FETCH_CLASS, '\Lotes\Lotes' );
      $sth->execute( array( $this->LOTE ) );

      $this->data = $sth->fetch();

    }

    public function load2($lote,$cve_articulo) {

      $sql = "
        SELECT
            c_articulo.cve_articulo,
            LOTE,
            Caducidad
        FROM
        ".self::TABLE."
        LEFT JOIN c_articulo on c_lotes.cve_articulo=c_articulo.cve_articulo
        WHERE
          LOTE =  '".$lote."'
          and c_articulo.id ='".$cve_articulo."'
          and c_lotes.Activo=1 ";

      $sth = \db()->prepare( $sql );
      $sth->execute( );

     return $sth->fetch();

    }
    
    function maneja_caducidad($data) 
    {
      $sql = "
        SELECT
          Caduca
        FROM
          c_articulo
        WHERE
          c_articulo.cve_articulo = '{$data["cve_articulo"]}'
      ";
      $sth = \db()->prepare( $sql );
      $sth->execute();
      $resultado = $sth->fetch();
      return $resultado["Caduca"];
    }

    function buscarLote($lote,$cve_articulo) 
    {
      $sql = "
        SELECT
          *
        FROM
          ".self::TABLE."
        WHERE
          c_lotes.LOTE =  '".$lote."' 
        AND c_lotes.cve_articulo ='".$cve_articulo."' 
        AND c_lotes.Activo=1 
      ";
      $sth = \db()->prepare( $sql );
      $sth->execute( );
      return $sth->fetch();
    }

    function __get( $key ) {

      switch($key) {
        case 'cve_articulo':
        case 'LOTE':
        case 'CADUCIDAD':
		    case 'id_lote':
          $this->load();
          return @$this->data->$key;
        default:
          return $this->key;
      }

    }

    function save($data) 
    {
      if( !$data['cve_articulo'] ) { throw new \ErrorException( 'cve_articulo is required.' ); }
      if( !$data['nuevo_lote'] ) { throw new \ErrorException( 'LOTE is required.' ); }
      //if( !$data['des_cve_lote'] ) { throw new \ErrorException( 'des_cve_lote is required.' ); }
      //if( !$data['CADUCIDAD'] ) { throw new \ErrorException( 'CADUCIDAD is required.' ); }
      
      if( $data['CADUCIDAD'] ) {$fecha_caducidad = date('Y-m-d', strtotime($data["CADUCIDAD"]));}
      else{$fecha_caducidad = '0000-00-00';}

/*
      $sql = "SELECT cve_articulo FROM `c_articulo` WHERE cve_articulo = '".$data['cve_articulo']."'";
      $sth = \db()->prepare($sql);
      $sth->execute();
      $data = $sth->fetch();
    
      if ($data['cve_articulo']) 
          return true;
      else
          return false;
*/

      $sql = sprintf('
        INSERT INTO
          ' . self::TABLE . '
        SET
          Lote = :nuevo_lote
        , Caducidad = :CADUCIDAD
        , cve_articulo = :cve_articulo
      ');

      $this->save = \db()->prepare($sql);

      //$this->save->bindValue( ':ID_Proveedor', $this->ID_Proveedor, \PDO::PARAM_STR );
      $this->save->bindValue( ':cve_articulo', $data['cve_articulo'], \PDO::PARAM_STR );
      $this->save->bindValue( ':nuevo_lote', $data['nuevo_lote'], \PDO::PARAM_STR );
      $this->save->bindValue( ':CADUCIDAD', $fecha_caducidad, \PDO::PARAM_STR );

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

    function actualizarLotes($data) 
    {
      $fecha_caducidad = date('Y-m-d', strtotime($data["CADUCIDAD"]));
      $sql = "
        UPDATE " . self::TABLE . " 
        SET
          CADUCIDAD = '{$fecha_caducidad}'
        WHERE LOTE = '{$data["id_lote"]}'; 
      ";
      $rs = mysqli_query(\db2(), $sql) or die(mysqli_error(\db2()));
    }

    function borrarLote( $data ) 
    {
      $sql = "
        UPDATE " . self::TABLE . "
        SET
          Activo = 0
        WHERE LOTE = '{$data['LOTE']}' 
        AND cve_articulo = '{$data['cve_articulo']}'
      ";
      $this->save = \db()->prepare($sql);
      $this->save->execute( array(
          $data['LOTE']
        )
      );
    }
	  
	private function loadClave() {

      $sql = sprintf('
        SELECT
          *
        FROM
          c_lotes
        WHERE
          LOTE = ?
      ');

      $sth = \db()->prepare( $sql );
      $sth->setFetchMode( \PDO::FETCH_CLASS, '\Lotes\Lotes' );
      $sth->execute( array( $this->LOTE ) );

      $this->data = $sth->fetch();

    }
	  
	function validaClave( $key ) {
      switch($key) {
        case 'LOTE':
          $this->loadClave();
          return @$this->data->$key;
        default:
          return $this->key;
      }

    }

	function getLotes($id_articulo){
		 $sql = sprintf('
        SELECT
          *
        FROM
          c_lotes
        WHERE
          cve_articulo= ?
      ');

      $sth = \db()->prepare( $sql );
      $sth->setFetchMode( \PDO::FETCH_CLASS, '\Lotes\Lotes' );
      $sth->execute( array( $id_articulo ) );

      $this->data = $sth->fetchAll();
		
		
	}

  function getLotesActivos($id_articulo){
     $sql = "
        SELECT
          *
        FROM
          c_lotes
        WHERE
          cve_articulo= '".$id_articulo."'
          and STR_TO_DATE(CADUCIDAD,'%d-%m-%Y')>=NOW()
      ";

      $sth = \db()->prepare( $sql );
      $sth->setFetchMode( \PDO::FETCH_CLASS, '\Lotes\Lotes' );
      $sth->execute();

      $this->data = $sth->fetchAll();
    
    
  }
	
	function recovery( $data ) {

          $sql = "UPDATE " 
          . self::TABLE . "
		      SET Activo = 1 WHERE 
          cve_articulo='".$data['cve_articulo']."' AND LOTE = '".$data['LOTE']."';";
          $this->delete = \db()->prepare($sql);
          $this->delete->execute( array(
              $data['cve_articulo']
          ) );
    }

 public function inUse( $data ) {


      $sql = "SELECT cve_articulo FROM `c_articulo` WHERE cve_articulo = '".$data['cve_articulo']."'";
      $sth = \db()->prepare($sql);
      $sth->execute();
      $data = $sth->fetch();
    
    if ($data['cve_articulo']) 
        return true;
    else
        return false;
  }  
	
  }
