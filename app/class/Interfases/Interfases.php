<?php

  namespace Interfases;

  class Interfases {

    const TABLE = 't_log_ws';
    const TABLE2 = 't_log_sap';
    var $identifier;

    public function __construct( $clave = false, $key = false ) {

      if( $clave ) {
        $this->id = (int) $clave;
      }
/*
      if($key) {

        $sql = sprintf('
          SELECT
            cve_umed
          FROM
            %s
          WHERE
            cve_umed = ?
        ',
          self::TABLE
        );

        $sth = \db()->prepare($sql);
        $sth->setFetchMode(\PDO::FETCH_CLASS, '\UnidadesMedida\UnidadesMedida');
        $sth->execute(array($key));

        $unidadesmedida = $sth->fetch();

        $this->cve_umed = $unidadesmedida->cve_umed;

      }
*/
    }

    private function load() {

      $sql = sprintf('
        SELECT
          *
        FROM
          %s

        WHERE Id = ?
      ',
        self::TABLE
      );

      $sth = \db()->prepare( $sql );
      $sth->setFetchMode( \PDO::FETCH_CLASS, '\Proyectos\Proyectos' );
      $sth->execute( array( $this->clave ) );

      $this->data = $sth->fetch();

    }

    function getAll() {

        $sql = '
        SELECT
          *
        FROM
          ' . self::TABLE . '
		#where Activo=1
      ';

        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\Proyectos\Proyectos' );
        $sth->execute( array( $cve_umed ) );

        return $sth->fetchAll();

    }	

    function getAllProyectos($id_almacen) {

        $sql = "
        SELECT
          *
        FROM
          " . self::TABLE . " WHERE id_almacen = $id_almacen #AND IFNULL(Cve_Proyecto, '') NOT IN (SELECT IFNULL(Proyecto, '') FROM th_entalmacen)
    #where Activo=1
      ";

        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\Proyectos\Proyectos' );
        $sth->execute( array( $cve_umed ) );

        return $sth->fetchAll();

    } 

    function getAllProyectosOcupados($id_almacen) {

        $sql = "
        SELECT
          *
        FROM
          " . self::TABLE . " WHERE id_almacen = $id_almacen AND IFNULL(Cve_Proyecto, '') IN (SELECT IFNULL(Proyecto, '') FROM th_entalmacen) 
          #AND IFNULL(Cve_Proyecto, '') NOT IN (SELECT IFNULL(Proyecto, '') FROM td_pedido)
    #where Activo=1
      ";

        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\Proyectos\Proyectos' );
        $sth->execute( array( $cve_umed ) );

        return $sth->fetchAll();

    } 

    function getAllProyectosExistencias($id_almacen) {

        $sql = "
        SELECT
          *
        FROM
          " . self::TABLE . " WHERE id_almacen = $id_almacen AND IFNULL(Cve_Proyecto, '') IN (SELECT proyecto FROM t_trazabilidad_existencias WHERE IFNULL(proyecto, '') != '' AND cve_almac = $id_almacen AND idy_ubica IS NOT NULL)  
          #AND IFNULL(Cve_Proyecto, '') NOT IN (SELECT IFNULL(Proyecto, '') FROM td_pedido)
    #where Activo=1
      ";

        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\Proyectos\Proyectos' );
        $sth->execute( array( $cve_umed ) );

        return $sth->fetchAll();

    } 

    function __get( $key ) {

      switch($key) {
          case 'clave':
          $this->load();
          return @$this->data->$key;
        default:
          return $this->key;
      }

    }

    function save( $_post ) {

      //if( !$_post['des_umed'] ) { throw new \ErrorException( 'des_umed is required.' ); }

      $sql = sprintf('
        INSERT INTO
          ' . self::TABLE . '
        SET       
          Cve_Proyecto = :Clave,
          Des_Proyecto = :descripcion,
          id_almacen = :id_almacen
      ');

      $this->save = \db()->prepare($sql);

      $this->save->bindValue( ':Clave', $_post['Clave'], \PDO::PARAM_STR );
      $this->save->bindValue( ':descripcion', $_post['descripcion'], \PDO::PARAM_STR );
      $this->save->bindValue( ':id_almacen', $_post['id_almacen'], \PDO::PARAM_STR );

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

      function borrarProyecto( $data ) {
          $sql = '
        DELETE FROM 
          ' . self::TABLE . '
        WHERE
          Id = ?
      ';
          $this->save = \db()->prepare($sql);
          $this->save->execute( array(
              $data['clave']
          ) );
      }
	  
	  	function recovery( $data ) {

             $sql = '
        UPDATE
          ' . self::TABLE . '
        SET
          Activo = 1
        WHERE
          cve_umed = ?
      ';
          $this->save = \db()->prepare($sql);
          $this->save->execute( array(
              $data['cve_umed']
          ) );
    }

	function EnviarCadena( $data ) {

      //*******************************************************************************
      //                          EJECUTAR EN INFINITY
      //*******************************************************************************
      $query = mysqli_query(\db2(), "SELECT COUNT(*) as existe FROM t_configuraciongeneral WHERE cve_conf = 'ejecutar_infinity' AND Valor = '1'");
      $ejecutar_infinity = mysqli_fetch_assoc($query)['existe'];

      if($ejecutar_infinity)
      {
         $id = $data['id'];
         $query = mysqli_query(\db2(), "SELECT * FROM t_log_ws WHERE Id = $id");
         $json = mysqli_fetch_assoc($query)['Mensaje'];

        $query = mysqli_query(\db2(), "SELECT Url, Servicio, User, Pswd, IFNULL(Puerto, '8080') as Puerto, Empresa, to_base64(CONCAT(USER,':',Pswd)) Codificado, CONCAT(Url,':',Puerto,'/',Servicio) AS url_curl FROM c_datos_ws WHERE Servicio = 'wms_trasp' AND Activo = 1");
        $row_infinity = mysqli_fetch_assoc($query);
        $Url_inf = $row_infinity['Url'];
        $url_curl = $row_infinity['url_curl'];
        $Servicio_inf = $row_infinity['Servicio'];
        $Puerto = $row_infinity['Puerto'];
        $User_inf = $row_infinity['User'];
        $Pswd_inf = $row_infinity['Pswd'];
        $Empresa_inf = $row_infinity['Empresa'];
        $Codificado = $row_infinity['Codificado'];

          $curl = curl_init();
          //$url_curl = "$Url_inf.':'.$Puerto.'/'.$Servicio_inf";

          curl_setopt_array($curl, array(
            // Esta URL es para salidas (wms_sal), para entradas cambia al final por 'wms_entr' y para traslados cambia por 'wms_trasp'
            //CURLOPT_URL => '{$Url_inf:$Puerto/$Servicio_inf}',
            CURLOPT_URL => "$url_curl",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>
            // Aquí cambia la cadena JSON
            $json,
            CURLOPT_HTTPHEADER => array(
              'Content-Type: application/json',
              'Authorization: Basic '.$Codificado.''
            ),
          ));

          $response = curl_exec($curl);

          curl_close($curl);      

          $query = mysqli_query(\db2(), "UPDATE t_log_ws SET Respuesta = '$response' WHERE Id = $id");
      }

  }
	
    function RegistrarCadena( $json, $Servicio_inf, $Codificado, $url_curl, $proceso, $dispositivo, $pdo = "" ) {

      //*******************************************************************************
      //                          EJECUTAR EN INFINITY
      //*******************************************************************************
      //$query = mysqli_query(\db2(), "SELECT COUNT(*) as existe FROM t_configuraciongeneral WHERE cve_conf = 'ejecutar_infinity' AND Valor = '1'");
      //$ejecutar_infinity = mysqli_fetch_assoc($query)['existe'];

      //if($ejecutar_infinity)
      //{
         //$id = $data['id'];
         //$query = mysqli_query(\db2(), "SELECT * FROM t_log_ws WHERE Id = $id");
         //$json = mysqli_fetch_assoc($query)['Mensaje'];
      if($pdo == "") $pdo = \db();

        //$query = mysqli_query(\db2(), "SELECT Url, Servicio, User, Pswd, IFNULL(Puerto, '8080') as Puerto, Empresa, to_base64(CONCAT(USER,':',Pswd)) Codificado, CONCAT(Url,':',Puerto,'/',Servicio) AS url_curl, NOW() AS hora_movimiento FROM c_datos_ws WHERE Servicio = 'wms_trasp' AND Activo = 1");
        $query = $pdo->prepare("SELECT Url, Servicio, User, Pswd, IFNULL(Puerto, '8080') as Puerto, Empresa, to_base64(CONCAT(USER,':',Pswd)) Codificado, CONCAT(Url,':',Puerto,'/',Servicio) AS url_curl, NOW() AS hora_movimiento FROM c_datos_ws WHERE Servicio = 'wms_trasp' AND Activo = 1");$query->execute();
        //$row_infinity = mysqli_fetch_assoc($query);
        $row_infinity = $query->fetch();$query->closeCursor();

        $Url_inf = $row_infinity['Url'];
        $url_curl = $row_infinity['url_curl'];
        $Servicio_inf = $row_infinity['Servicio'];
        $Puerto = $row_infinity['Puerto'];
        $User_inf = $row_infinity['User'];
        $Pswd_inf = $row_infinity['Pswd'];
        $Empresa_inf = $row_infinity['Empresa'];
        $hora_movimiento = $row_infinity['hora_movimiento'];
        $Codificado = $row_infinity['Codificado'];

          $curl = curl_init();
          //$url_curl = "$Url_inf.':'.$Puerto.'/'.$Servicio_inf";

          curl_setopt_array($curl, array(
            // Esta URL es para salidas (wms_sal), para entradas cambia al final por 'wms_entr' y para traslados cambia por 'wms_trasp'
            //CURLOPT_URL => '{$Url_inf:$Puerto/$Servicio_inf}',
            CURLOPT_URL => "$url_curl",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>
            // Aquí cambia la cadena JSON
            $json,
            CURLOPT_HTTPHEADER => array(
              'Content-Type: application/json',
              'Authorization: Basic '.$Codificado.''
            ),
          ));

          $response = curl_exec($curl);

          curl_close($curl);      

          //$query = mysqli_query(\db2(), "UPDATE t_log_ws SET Respuesta = '$response' WHERE Id = $id");

          $sql = "INSERT INTO t_log_ws(Fecha, Referencia, Mensaje, Respuesta, Proceso, Dispositivo) VALUES (NOW(), '$Servicio_inf', '$json', '$response', '$proceso', '$dispositivo')";
          //$query = mysqli_query($conn, $sql);
            //$sql = "INSERT INTO t_log_ws(Fecha, Referencia, Mensaje, Respuesta, Proceso, Dispositivo) VALUES (NOW(), :Servicio_inf, :json, :response, 'Transformacion', 'WEB')";
            $query = $pdo->prepare($sql);
            //$query->execute(array('Servicio_inf' => $Servicio_inf, 'json' => $json, 'response' => $response));
            $query->execute();$query->closeCursor();
      //}

  }


    function actualizarProyectos( $data ) {
      $sql = '
        UPDATE
          ' . self::TABLE . '
        SET
          Cve_Proyecto = ?
          ,Des_Proyecto = ?
        WHERE
          id = ?
      ';
      $this->save = \db()->prepare($sql);
      $this->save->execute( array(
        $data['Clave']
    , $data['descripcion']
    , $data['id']
      ) );
    }

		   function exist($cve_umed) {

      $sql = sprintf('
        SELECT
          *
        FROM
			 ' . self::TABLE . '
        WHERE
          Cve_Proyecto = ?
      ',
        self::TABLE
      );

      $sth = \db()->prepare( $sql );

      $sth->execute( array( $cve_umed ) );

      $this->data = $sth->fetch();
	  
	  if(!$this->data)
		  return false; 
				else 
					return true;
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


      $sql = "SELECT cve_umed FROM `c_articulo` WHERE cve_umed = '".$data['cve_umed']."'";
      $sth = \db()->prepare($sql);
      $sth->execute();
      $data = $sth->fetch();
    
    if ($data['cve_umed']) 
        return true;
    else
        return false;
  }

  }
