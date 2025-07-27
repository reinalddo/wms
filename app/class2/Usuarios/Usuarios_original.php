<?php

  namespace Usuarios;

  class Usuarios {

    const TABLE = 'c_usuario';
    const TABLE_Profiles = 't_perfilesusuarios';
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

    function getAll() {

        $sql = '
        SELECT
          *
        FROM
          ' . self::TABLE . '
      ';

        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\Usuarios\Usuarios' );
        $sth->execute( array( id_user ) );

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
        try {
          $sql = "INSERT INTO " . self::TABLE . " (cve_usuario, cve_cia, des_usuario, fec_ingreso, pwd_usuario, ban_usuario, status, Activo, timestamp, identifier)";
          $sql .= "Values (";
          $sql .= "'".$_post['cve_usuario']."',";
          $sql .= "'".$_post['cve_cia']."',";
          $sql .= "'".$_post['des_usuario']."',";
          $sql .= "now(),";
          $sql .= "'".$_post['pwd_usuario']."',";
          $sql .= "'1','',";
          $sql .= "'1', '1480436303','');";

          $rs = mysqli_query(\db2(), $sql) or die("Error description: " . mysqli_error(\db2()));

        } catch(Exception $e) {
          return 'ERROR: ' . $e->getMessage();
        }
    }


  function addRolUser( $_post ) {
      $arr = $_post['arrAdd'];
      foreach ($arr as $id) {
          $sql = "insert into t_usuariosperfil(ID_PERFIL,cve_usuario) values ('".$id."','".$_post['cve_usuario']."');";
          $this->save = \db()->prepare($sql);
          $this->save->execute( array(
              $data['ID_PERFIL']
          ) );
      }
  }

  function BorrarRolUser( $_post ) {
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
      try {
          $sql = "
          UPDATE 
          " . self::TABLE . "
           SET 
            cve_usuario = '".$_post['cve_usuario']."',
            cve_cia = '".$_post['cve_cia']."', 
            des_usuario = '".$_post['des_usuario']."', 
            pwd_usuario = '".$_post['pwd_usuario']."' 
            WHERE 
            id_user = '".$_post['Clave']."'";
          $rs = mysqli_query(\db2(), $sql) or die("Error description: " . mysqli_error(\db2()));

      } catch(Exception $e) {
          return 'ERROR: ' . $e->getMessage();
      }
    }

    function borrarUser( $data ) {

      $sql = "Delete From c_usuario where id_user='".$data['id_user']."';";
      $this->delete = \db()->prepare($sql);
      $this->delete->execute( array(
          $data['id_user']
      ) );
    }
  }
