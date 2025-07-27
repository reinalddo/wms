<?php

  namespace Usuarios;

  class Usuarios {

    const TABLE = 'c_usuario';
    const VENDEDOR = 't_vendedores';
    const DESTINATARIOS = 'c_destinatarios';
    const TABLE_Profiles = 't_perfilesusuarios';
    const TABLE_P_T = 't_usuariosperfil';

    var $identifier;

    public function __construct( $id_user = false, $key = false ) {

      if( $id_user ) {
        $this->id_user = (int) $id_user;
      }

      if($key) {

        $sql = sprintf('
          SELECT
            id_user
          FROM
            %s
          WHERE
            id_user = ?
        ',
          self::TABLE
        );

        $sth = \db()->prepare($sql);
        $sth->setFetchMode(\PDO::FETCH_CLASS, '\Usuarios\Usuarios');
        $sth->execute(array($key));

        $id_user = $sth->fetch();

        $this->id_user = $id_user->id_user;

      }

    }

  private function load() {

      $sql = sprintf('
    SELECT
      *
    FROM
      %s
    WHERE
      id_user = ?
  ',
          self::TABLE
      );

      $sth = \db()->prepare( $sql );
      $sth->setFetchMode( \PDO::FETCH_CLASS, '\Usuarios\Usuarios' );
      $sth->execute( array( $this->id_user ) );

      $this->data = $sth->fetch();

  }
  
  function exist($cve_usuario) {

      $sql = sprintf('
        SELECT
          *
        FROM
          %s
        WHERE
          cve_usuario = ?
      ',
        self::TABLE
      );

      $sth = \db()->prepare( $sql );
	  
      $sth->setFetchMode( \PDO::FETCH_CLASS, '\Almacen\Almacen' );
      $sth->execute( array( $cve_usuario ) );

      $this->data = $sth->fetch();
	  
	  if(!$this->data)
		  return false; 
				else 
					return true;
    }

  private function loadPerRoles() {
        $sql1 = "select ID_PERFIL, PER_NOMBRE, cve_cia from t_perfilesusuarios where ID_PERFIL not in(select ID_PERFIL from v_permisosusuario where cve_usuario='".$this->cve_usuario."');";
        $rs1 = mysqli_query(\db2(), $sql1) or die("Error description: " . mysqli_error(\db2()));
        $arr1 = array();
        while ($row1 = mysqli_fetch_array($rs1)) {
            $arr1[] = $row1;
        }
        $this->dataOrigen = $arr1;

            $sql2 = "select distinct ID_PERFIL,	DES_PERFIL as PER_NOMBRE,cve_cia from v_permisosusuario	where cve_usuario='".$this->cve_usuario."';";
            $rs2 = mysqli_query(\db2(), $sql2) or die("Error description: " . mysqli_error(\db2()));
            $arr2 = array();
            while ($row2 = mysqli_fetch_array($rs2)) {
                $arr2[] = $row2;
            }
            $this->dataDestino = $arr2;
  }

    function getAll($surtidores = 0) 
    {
      $sql_surtidores = "";
      if($surtidores == 1)
        $sql_surtidores = " AND cve_usuario != 'wmsmaster' AND es_cliente = 0";
        $sql = '
        SELECT
          *
        FROM
          ' . self::TABLE . '
		  where Activo=1 '.$sql_surtidores.'
      ORDER BY nombre_completo
      ';

        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\Usuarios\Usuarios' );
        @$sth->execute( array( id_user ) );

        return $sth->fetchAll();

    }

    function getAllVendedor($cve_almacen = "") {

      $SQL_Almacen = "";
      if($cve_almacen)
        $SQL_Almacen = " WHERE Id_Vendedor IN (SELECT VendedorId FROM Venta WHERE IdEmpresa = '$cve_almacen') ";

        $sql = '
        SELECT
          *
        FROM
          ' . self::VENDEDOR . $SQL_Almacen;

        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\Usuarios\Usuarios' );
        @$sth->execute( array( cve_Vendedor ) );

        return $sth->fetchAll();

    }
    function getAllDestinatarios() {

        $sql = '
        SELECT
          *
        FROM
          ' . self::DESTINATARIOS . '
      ';

        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\Usuarios\Usuarios' );
        $sth->execute( array( Cve_Dest ) );

        return $sth->fetchAll();

    }
	
    function __get( $key ) {

    switch($key) {
      case 'id_user':
          $this->load();
          return @$this->data->$key;
      default:
          return $this->key;
    }

    }

  function __getPerRoles( $key ) {

      switch($key) {
          case 'cve_usuario':
              $this->loadPerRoles();
              return @$this->data->$key;
          default:
              return $this->key;
      }

  }

    function saveUser( $_post ) {

        $usuario_web_apk = 'AW';

        if($_post['es_cliente'] == 'AW')
        {
           $_post['es_cliente'] = 0;
        }
        else if($_post['es_cliente'] == 'A')
        {
           $_post['es_cliente'] = 0;
           $usuario_web_apk = 'A';
        }
        else
          $usuario_web_apk = 'W';


        if($_post['es_cliente'] != 0 && $_post['es_cliente'] != 3) $_post['perfil'] = 1;
        if($_post['almacen_asignado'] == "0") $_post['almacen_asignado'] = "";
        if($_post['es_cliente'] == 0) {$_post['cliente_asignado'] = "";$_post['proveedor_asignado'] = "";}

          $sql = "INSERT IGNORE INTO " . self::TABLE . " (cve_usuario, cve_cia,nombre_completo,email,perfil, des_usuario, fec_ingreso, pwd_usuario, ban_usuario, status, Activo, timestamp, identifier, image_url, es_cliente, cve_almacen, cve_cliente, cve_proveedor, web_apk)";
          $sql .= "Values (";
          $sql .= "'".$_post['cve_usuario']."',";
          $sql .= "'".$_post['cve_cia']."',";
          $sql .= "'".$_post['nombre_completo']."',";
          $sql .= "'".$_post['email']."',";
          $sql .= "'".$_post['perfil']."',";
          $sql .= "'".$_post['des_usuario']."',";
          $sql .= "NOW(),";
          $sql .= "'".$_post['pwd_usuario']."',";
          $sql .= "'1','',";
          $sql .= "'1', '1480436303','',";
          $sql .= "'/img/imageperfil/".$_post['imagen']."', ".$_post['es_cliente'].", '".$_post['almacen_asignado']."', '".$_post['cliente_asignado']."', '".$_post['proveedor_asignado']."', '".$usuario_web_apk."')";

          $this->save = \db()->prepare($sql);
          $this->save->execute();

          $sql = "INSERT IGNORE INTO t_usuariosperfil (ID_PERFIL, cve_usuario, Activo)";
          $sql .= "VALUES (";
          $sql .= "'".$_post['perfil']."',";
          $sql .= "'".$_post['cve_usuario']."',";
          $sql .= "'1')";
          $this->save = \db()->prepare($sql);
          $this->save->execute();


          if($_post['es_cliente'] != 0)
          {
              $sql = 'SELECT id_user, cve_almacen FROM c_usuario WHERE cve_usuario = "'.$_post['cve_usuario'].'" ';

              $res_user = $this->getArraySQL($sql);

              $id_user     = $res_user[0]["id_user"];
              $cve_almacen = $res_user[0]["cve_almacen"];

              $sql = "INSERT IGNORE INTO t_usu_alm_pre (id_user, cve_almac)";
              $sql .= "VALUES (";
              $sql .= "'".$id_user."',";
              $sql .= "'".$_post['almacen_asignado']."')";

              $this->save = \db()->prepare($sql);
              $this->save->execute();

              if($_post['es_cliente'] == 3)
              {
                  $sql = "INSERT IGNORE INTO t_vendedores(Id_Vendedor, Nombre, Cve_Vendedor, Psswd_EDA) ";
                  $sql .= "VALUES (";
                  $sql .= "'".$id_user."',";
                  $sql .= "'".$_post['nombre_completo']."',";
                  $sql .= "'".$_post['cve_usuario']."',";
                  $sql .= "'".$_post['pwd_usuario']."')";

                  $this->save = \db()->prepare($sql);
                  $this->save->execute();
              }

          }
    }


  function addRolUser( $data ) {
      $arr = $_post['arrAdd'];
      foreach ($arr as $id) {
          $sql = "INSERT IGNORE into t_usuariosperfil(ID_PERFIL,cve_usuario) values ('".$id."','".$_post['cve_usuario']."');";
          $this->save = \db()->prepare($sql);
          $this->save->execute( array(
              $data['ID_PERFIL']
          ) );
      }
  }

  function BorrarRolUser( $data ) {
      $arr = $_post['arrDelete'];
      foreach ($arr as $id) {
          $sql = "Delete from t_usuariosperfil where ID_PERFIL='".$id."' and cve_usuario='".$_post['cve_usuario']."';";
          $this->delete = \db()->prepare($sql);
          $this->delete->execute( array(
              $data['ID_PERFIL']
          ) );
      }
  }


    function actualizarUser( $_post ) {

        $usuario_web_apk = 'AW';

        if($_post['es_cliente'] == 'AW')
        {
           $_post['es_cliente'] = 0;
        }
        else if($_post['es_cliente'] == 'A')
        {
           $_post['es_cliente'] = 0;
           $usuario_web_apk = 'A';
        }
        else
          $usuario_web_apk = 'W';

        if($_post['es_cliente'] != 0 && $_post['es_cliente'] != 3) $_post['perfil'] = 1;

          $sql = 'SELECT a.cve_usuario FROM t_usuariosperfil a, c_usuario b WHERE a.cve_usuario = b.cve_usuario and b.id_user = "'.$_post['Clave'].'" ';

          $validate = $this->getArraySQL($sql);

          if(!empty($validate) && is_array($validate)){
              $sql = "UPDATE IGNORE t_usuariosperfil SET cve_usuario = '".$_post['cve_usuario']."', ID_PERFIL = '".$_post['perfil']."' WHERE cve_usuario = '".$validate[0]["cve_usuario"]."'";
              $this->save = \db()->prepare($sql);
              $this->save->execute();
          }
          else{
            $sql = "INSERT IGNORE INTO t_usuariosperfil (ID_PERFIL, cve_usuario, Activo)";
              $sql .= "Values (";
              $sql .= "'".$_post['perfil']."',";
              $sql .= "'".$_post['cve_usuario']."',";
              $sql .= "'1')";
              $this->save = \db()->prepare($sql);
              $this->save->execute();
          }

          $sql = 'SELECT id_user, cve_almacen FROM c_usuario WHERE cve_usuario = "'.$_post['cve_usuario'].'" ';
          $res_user = $this->getArraySQL($sql);
          $id_user     = $res_user[0]["id_user"];
          $cve_almacen = $res_user[0]["cve_almacen"];

          if($_post['es_cliente'] == 0) 
          {
              $_post['almacen_asignado'] = "";
              $sql = "DELETE FROM t_usu_alm_pre WHERE id_user = '{$id_user}' AND cve_almac = '{$cve_almacen}'";
          }
          else
          {
              $sql = "UPDATE t_usu_alm_pre SET cve_almac = '".$_post['almacen_asignado']."' WHERE id_user = '".$id_user."'";
          }
          $this->save = \db()->prepare($sql);
          $this->save->execute();


          $sql = "
          UPDATE 
          " . self::TABLE . "
           SET 
            cve_usuario = '".$_post['cve_usuario']."',
            cve_cia = '".$_post['cve_cia']."', 
            nombre_completo = '".$_post['nombre_completo']."', 
            email = '".$_post['email']."', 
            perfil = '".$_post['perfil']."', 
            des_usuario = '".$_post['des_usuario']."', 
            pwd_usuario = '".$_post['pwd_usuario']."',
            es_cliente = '".$_post['es_cliente']."',
            cve_almacen = '".$_post['almacen_asignado']."',
            cve_cliente = '".$_post['cliente_asignado']."',
            cve_proveedor = '".$_post['proveedor_asignado']."',
            image_url   = '".$_post['imagen']."',
            web_apk    = '{$usuario_web_apk}'
            WHERE 
            id_user = '".$_post['Clave']."'";
          $this->save = \db()->prepare($sql);
          $this->save->execute();

          if($_post['es_cliente'] == 3)
          {
              $sql = 'SELECT COUNT(*) as existe FROM t_vendedores WHERE Cve_Vendedor = "'.$_post['cve_usuario'].'" ';
              $validate = $this->getArraySQL($sql);
              $existe = $validate[0]["existe"];

              if($existe)
              {
                $sql = "UPDATE t_vendedores SET Nombre = '".$_post['nombre_completo']."', 
                                                Psswd_EDA = '".$_post['pwd_usuario']."' 
                                            WHERE Id_Vendedor = ".$id_user."";
              }
              else
              {
                  $sql = "INSERT IGNORE INTO t_vendedores(Id_Vendedor, Nombre, Cve_Vendedor, Psswd_EDA) ";
                  $sql .= "VALUES (";
                  $sql .= "'".$id_user."',";
                  $sql .= "'".$_post['nombre_completo']."',";
                  $sql .= "'".$_post['cve_usuario']."',";
                  $sql .= "'".$_post['pwd_usuario']."')";
              }
                  $this->save = \db()->prepare($sql);
                  $this->save->execute();
          }

    }

    function borrarUser( $data ) {

      $sql = "UPDATE c_usuario SET Activo = 0 WHERE id_user='".$data['id_user']."';";
      $this->delete = \db()->prepare($sql);
      $this->delete->execute( array(
          $data['id_user']
      ) );
    }

      function recoveryUser( $data ) {

          $sql = "UPDATE c_usuario SET Activo = 1 WHERE  id_user='".$data['id_user']."';";
          $this->delete = \db()->prepare($sql);
          $this->delete->execute( array(
              $data['id_user']
          ) );
      }
      function tieneAlmacen() {
      $sql = sprintf('
        SELECT
          cr.*
        FROM
          trel_us_alm r, c_usuario cr
        WHERE
          cr.cve_usuario = r.cve_usuario 
          and cr.Activo = 1
          and cr.id_user = ?
      ',
        self::TABLE
      );

      $sth = \db()->prepare( $sql );
      $sth->setFetchMode( \PDO::FETCH_CLASS, '\Usuarios\Usuarios');
      $sth->execute( array( $this->id_user ) );

      $this->data = $sth->fetch();

    }

    function getArraySQL($sql){

        $conexion = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

        mysqli_set_charset($conexion, "utf8");

        if(!$result = mysqli_query($conexion, $sql)) 
            echo "Falló la preparación: (" . mysqli_error($conexion) . ") ";;

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
  }


