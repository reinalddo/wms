<?php

namespace Perfiles;

class Perfiles {

    const TABLE = 't_profiles';
    const TABLE2 = 't_permisos_perfil';
    var $identifier;

    public function __construct( $id_perfil = false, $key = false ) {

        if( $id_perfil ) {
            $this->id_perfil = (int) $id_perfil;
        }

        if($key) {

            $sql = sprintf('
          SELECT
            *
          FROM
            %s
          WHERE
            id_perfil = ?
        ',
                           self::TABLE
                          );

            $sth = \db()->prepare($sql);
            $sth->setFetchMode(\PDO::FETCH_CLASS, '\Perfiles\Perfiles');
            $sth->execute(array($key));

            $Perfiles = $sth->fetch();

            $this->id_perfil = $Perfiles->id_perfil;

        }

    }

    private function load() {

        $sql = sprintf('
        SELECT
          *
        FROM
          %s
        WHERE
          id_perfil = ?
        ',
                       self::TABLE
                      );

        $sth = \db()->prepare( $sql );
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\Perfiles\Perfiles' );
        $sth->execute( array( $this->id_perfil ) );

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
        $sth->setFetchMode( \PDO::FETCH_CLASS, '\Perfiles\Perfiles' );
        $sth->execute( array( Perfiles ) );

        return $sth->fetchAll();

    }

    function __get( $key ) {

        switch($key) {
            case 'id_perfil':
                $this->load();
                return @$this->data->$key;
            default:
                return $this->key;
        }

    }

    function __getPerPerfiles( $key ) {

        switch($key) {
            case 'id_perfil':
                $this->loadPerPerfiles();
                return @$this->data->$key;
            default:
                return $this->key;
        }

    }

    function __getPerUser( $key ) {

        switch($key) {
            case 'id_perfil':
                $this->loadPerUser();
                return @$this->data->$key;
            default:
                return $this->key;
        }

    }

    private function loadPerUser() {
        $sql1 = "select * from c_usuario where cve_usuario not in( select cve_usuario from t_usuariosperfil where id_perfil='".$this->id_perfil."');";
        $rs1 = mysqli_query(\db2(), $sql1) or die("Error description: " . mysqli_error(\db2()));
        $arr1 = array();
        while ($row1 = mysqli_fetch_array($rs1)) {
            $arr1[] = $row1;
        }
        $this->dataOrigen = $arr1;

        $sql2 = "select u.*	from t_usuariosperfil up, c_usuario u where up.id_perfil='".$this->id_perfil."' and u.cve_usuario=up.cve_usuario;";
        $rs2 = mysqli_query(\db2(), $sql2) or die("Error description: " . mysqli_error(\db2()));
        $arr2 = array();
      
        $responce->query($sql1);  
      
        while ($row2 = mysqli_fetch_array($rs2)) {
            $arr2[] = $row2;
        }
        $this->dataDestino = $arr2;
    }

    private function loadPerPerfiles() {
        $sql1 = "select * from s_permisos_modulo where ID_PERMISO not in(select ID_PERMISO from t_permisos_perfil where id_perfil='".$this->id_perfil."');";
        $rs1 = mysqli_query(\db2(), $sql1) or die("Error description: " . mysqli_error(\db2()));
        $arr1 = array();
        while ($row1 = mysqli_fetch_array($rs1)) {
            $arr1[] = $row1;
        }
        $this->dataOrigen = $arr1;

        $sql2 = "select * from v_permisosperfil where id_perfil='".$this->id_perfil."';";
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
                  '".$data['id_perfil']."'
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
            $sql = "insert into t_usuariosperfil(id_perfil,cve_usuario,Activo) values ('" . $_post['id_perfil'] . "','" . $_post['cve_usuario'] . "','1');";
            $rs = mysqli_query(\db2(), $sql) or die("Error description: " . mysqli_error(\db2()));
            //          }
        } catch(Exception $e) {
            return 'ERROR: ' . $e->getMessage();
        }
    }

    function BorrarPerUser( $_post ) {
        //      $arr = $_post['ID_PERMISO'];
        //      foreach ($arr as $id) {
        $sql = "delete from t_usuariosperfil where id_perfil='".$_post['id_perfil']."' and cve_usuario='".$_post['cve_usuario']."';";
        $this->delete = \db()->prepare($sql);
        $this->delete->execute( array(
            $data['id_perfil']
        ) );
        //      }
    }

    function BorrarRolUser( $_post ) {
        $arr = $_post['ID_PERMISO'];
        foreach ($arr as $id) {
            $sql = "delete from t_permisos_perfil where ID_PERMISO='".$id."' and id_perfil='".$_post['id_perfil']."';";
            $this->delete = \db()->prepare($sql);
            $this->delete->execute( array(
                $data['id_perfil']
            ) );
        }
    }

    function save( $_post ) {
        try {
            $rol = $_post['rol'];
            $sqlCount = mysqli_query(\db2(),"Delete from t_profiles where id_role='".$_post['rol']."';");
            $sqlCount2 = mysqli_query(\db2(),"Delete from t_premisos_perfil where ID_PERFIL='".$_post['rol']."';");
            unset($_post["select-all"]);
            unset($_post["hidden_rol"]);
            unset($_post["action"]);
            unset($_post["rol"]);
            if (!empty($_post)) {
                $i = 0;
                foreach ($_post as $v) {
                    if (is_array($v)) {
                        $id_menu = $this->KeyName($_post, $i);
                        $ex=explode('-',$id_menu);
                        $Activo = (isset($v[1]) && $v[1]=="on") ? "1" : "0";

                        if($ex[1]!='movil')
                        {
                            $sql = "INSERT INTO " . self::TABLE . " (id_menu,id_submenu, id_role, Activo)";
                            $sql .= "Values (";
                            $sql .= "'".$ex[0]."','".$ex[1]."',";
                            $sql .= "'".$rol."', '1');";

                            $rs = mysqli_query(\db2(), $sql) or die("Error description: " . mysqli_error(\db2()));
                            $i++;
                        }
                        else
                        {
                            $sql = "INSERT INTO " . self::TABLE2 . " (ID_PERMISO,ID_PERFIL, STATUS, Activo)";
                            $sql .= "Values (";
                            $sql .= "'".$ex[0]."',";
                            $sql .= "'".$rol."', '1', '1');";

                            $rs = mysqli_query(\db2(), $sql) or die("Error description: " . mysqli_error(\db2()));
                            $i++;

                        }


                    }
                }
            }
        } catch(Exception $e) {
            return 'ERROR: ' . $e->getMessage();
        }
    }

    function KeyName($myArray, $pos) {
        // $pos--;
        /* uncomment the above line if you */
        /* prefer position to start from 1 */

        if ( ($pos < 0) || ( $pos >= count($myArray) ) )
            return "NULL";  // set this any way you like

        reset($myArray);
        for($i = 0;$i < $pos; $i++) next($myArray);

        return key($myArray);
    }

    function actualizarRole( $data ) {
        $sql = 'UPDATE ' . self::TABLE . ' SET PER_NOMBRE = ? WHERE id_perfil = ? ';
        $this->update = \db()->prepare($sql);
        $this->update->execute( array(
            $data['PER_NOMBRE']
            , $data['id_perfil']
        ) );
    }

    function borrarRole( $data ) {
        $sql = 'DELETE FROM ' . self::TABLE . ' WHERE id_perfil = ? ';
        $this->delete = \db()->prepare($sql);
        $this->delete->execute( array(
            $data['id_perfil']
        ) );
    }

}
