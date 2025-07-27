<?php

  namespace Roles;

  class Roles {

    const TABLE = 't_roles';
    const TABLE_T = 't_perfilesusuarios';
    var $identifier;

    public function __construct( $ID_Ruta = false, $key = false ) {

      if( $ID_Ruta ) {
        $this->ID_Ruta = (int) $ID_Ruta;
      }

      if($key) {

        $sql = sprintf('
          SELECT
            id_role
          FROM
            %s
          WHERE
            id_role = ?
        ',
          self::TABLE
        );

        $sth = \db()->prepare($sql);
        $sth->setFetchMode(\PDO::FETCH_CLASS, '\Roles\Roles');
        $sth->execute(array($key));

        $roles = $sth->fetch();

        $this->id_role = $roles->id_role;

        }

    }

    private function load() {

        $sql = sprintf('
        SELECT
          *
        FROM
          %s
        WHERE
          id_role = ?
        ',
        self::TABLE
        );

        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\Roles\Roles' );
        $sth->execute( array( $this->id_role ) );

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
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\Roles\Roles' );
        @$sth->execute( array( Roles ) );

        return $sth->fetchAll();

    }
	
    function __get( $key ) {

        switch($key) {
            case 'id_role':
            $this->load();
            return @$this->data->$key;
            default:
            return $this->key;
        }

    }

    function __getPerRoles( $key ) {

        switch($key) {
            case 'id_role':
            $this->loadPerRoles();
            return @$this->data->$key;
            default:
            return $this->key;
        }

    }

      function __getPerUser( $key ) {

          switch($key) {
              case 'id_role':
                  $this->loadPerUser();
                  return @$this->data->$key;
              default:
                  return $this->key;
          }

      }

    private function loadPerUser() {
        $sql1 = "select * from c_usuario where cve_usuario not in( select cve_usuario from t_usuariosperfil where id_role='".$this->id_role."');";
        $rs1 = mysqli_query(\db2(), $sql1) or die("Error description: " . mysqli_error(\db2()));
        $arr1 = array();
        while ($row1 = mysqli_fetch_array($rs1)) {
          $arr1[] = $row1;
        }
        $this->dataOrigen = $arr1;

        $sql2 = "select u.*	from t_usuariosperfil up, c_usuario u where up.id_role='".$this->id_role."' and u.cve_usuario=up.cve_usuario;";
        $rs2 = mysqli_query(\db2(), $sql2) or die("Error description: " . mysqli_error(\db2()));
        $arr2 = array();
        while ($row2 = mysqli_fetch_array($rs2)) {
          $arr2[] = $row2;
        }
        $this->dataDestino = $arr2;
    }

    private function loadPerRoles() {
        $sql1 = "select * from s_permisos_modulo where ID_PERMISO not in(select ID_PERMISO from t_permisos_perfil where id_role='".$this->id_role."');";
        $rs1 = mysqli_query(\db2(), $sql1) or die("Error description: " . mysqli_error(\db2()));
        $arr1 = array();
        while ($row1 = mysqli_fetch_array($rs1)) {
          $arr1[] = $row1;
        }
        $this->dataOrigen = $arr1;

            $sql2 = "select * from v_permisosperfil where id_role='".$this->id_role."';";
            $rs2 = mysqli_query(\db2(), $sql2) or die("Error description: " . mysqli_error(\db2()));
            $arr2 = array();
            while ($row2 = mysqli_fetch_array($rs2)) {
              $arr2[] = $row2;
            }
            $this->dataDestino = $arr2;
    }

    function addRolUser( $data ) {
        try {
            $arr = $data['ID_PERMISO'];
            foreach ($arr as $id) {
                $sql = mysqli_query(\db2(), "CALL SPAD_AgregaPermisoPerfil(
                  '".$data['id_role']."'
                , '".$id."'       
                );") or die(mysqli_error(\db2()));
            }
        } catch(PDOException $e) {
            return 'ERROR: ' . $e->getMessage();
        }
    }

    function addPerUser( $_post ) {
      try {
//          $arr = $_post['ID_PERMISO'];
//          foreach ($arr as $id) {
              $sql = "insert into t_usuariosperfil(id_role,cve_usuario,Activo) values ('" . $_post['id_role'] . "','" . $_post['cve_usuario'] . "','1');";
              $rs = mysqli_query(\db2(), $sql) or die("Error description: " . mysqli_error(\db2()));
//          }
      } catch(Exception $e) {
          return 'ERROR: ' . $e->getMessage();
      }
    }

    function BorrarPerUser( $_post ) {
//      $arr = $_post['ID_PERMISO'];
//      foreach ($arr as $id) {
          $sql = "delete from t_usuariosperfil where id_role='".$_post['id_role']."' and cve_usuario='".$_post['cve_usuario']."';";
          $this->delete = \db()->prepare($sql);
          $this->delete->execute( array(
              $data['id_role']
          ) );
//      }
    }

    function BorrarRolUser( $_post ) {
        $arr = $_post['ID_PERMISO'];
        foreach ($arr as $id) {
            $sql = "delete from t_permisos_perfil where ID_PERMISO='".$id."' and id_role='".$_post['id_role']."';";
            $this->delete = \db()->prepare($sql);
            $this->delete->execute( array(
                $data['id_role']
            ) );
        }
    }

    function save( $_post ) {

          $sql = "INSERT INTO " . self::TABLE . " (rol, activo)";
          $sql .= "Values (";
          $sql .= "'".$_post['rol']."',";
          $sql .= "'1');";

          $rs = mysqli_query(\db2(), $sql) or die("Error description: " . mysqli_error(\db2()));

          $sql2 = "SELECT id_role FROM t_roles WHERE rol = '".$_post['rol']."'";

          $validate = $this->getArraySQL($sql2);

          if(!empty($validate) && is_array($validate)){
              $sql1 = "INSERT INTO t_perfilesusuarios (ID_PERFIL, PER_NOMBRE, cve_cia, activo)";
              $sql1 .= "Values (";
              $sql1 .= "'".$validate[0]["id_role"]."',";
              $sql1 .= "'".$_post['rol']."',";
              $sql1 .= "'1',";
              $sql1 .= "'1');";
              $rst = mysqli_query(\db2(), $sql1) or die("Error description: " . mysqli_error(\db2()));
          }
    }

    function actualizarRole( $data ) {

          $sql = 'SELECT ID_PERFIL FROM t_perfilesusuarios  WHERE ID_PERFIL = "'.$data['id_role'].'"';

          $validate = $this->getArraySQL($sql);

          if(!empty($validate) && is_array($validate)){
              $sql = "UPDATE t_perfilesusuarios SET PER_NOMBRE = '".$data['rol']."' WHERE ID_PERFIL = '".$data['id_role']."'";
              $this->save = \db()->prepare($sql);
              $this->save->execute();
          }
          else{
              $sql = "INSERT INTO t_perfilesusuarios (ID_PERFIL, PER_NOMBRE, cve_cia, Activo)";
              $sql .= "Values (";
              $sql .= "'".$data['id_role']."',";
              $sql .= "'".$data['rol']."',";
              $sql .= "'1',";
              $sql .= "'1')";
              $this->save = \db()->prepare($sql);
              $this->save->execute();
        }

        $sql = 'UPDATE ' . self::TABLE . ' SET rol = ? WHERE id_role = ? ';
        $this->update = \db()->prepare($sql);
        $this->update->execute( array(
          $data['rol']
        , $data['id_role']
        ) );
    }

    function borrarRole( $data ) {
        $sql = 'DELETE FROM ' . self::TABLE . ' WHERE id_role = ? ';
        $this->delete = \db()->prepare($sql);
        $this->delete->execute( array(
            $data['id_role']
        ) );

        $sql = 'DELETE FROM ' . self::TABLE_T . ' WHERE ID_PERFIL = ? ';
        $this->delete = \db()->prepare($sql);
        $this->delete->execute( array(
            $data['id_role']
        ) );
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
