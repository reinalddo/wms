<?php
    namespace TipoPrioridad;

    class TipoPrioridad {

        const TABLE = 't_tiposprioridad';
        var $identifier;

        public function __construct( $ID_Tipoprioridad = false, $key = false ) 
        {
            if( $ID_Tipoprioridad ) 
            {
                $this->ID_Tipoprioridad = (int) $ID_Tipoprioridad;
            }

            if($key) 
            {
                $sql = sprintf(' SELECT ID_Tipoprioridad FROM %s WHERE ID_Tipoprioridad = ? ', self::TABLE );

                $sth = \db()->prepare($sql);
                $sth->setFetchMode(\PDO::FETCH_CLASS, '\TipoPrioridad\TipoPrioridad');
                $sth->execute(array($key));

                $prioridad = $sth->fetch();

                $this->ID_Tipoprioridad = $prioridad->ID_Tipoprioridad;
            }
        }

        private function load() 
        {
            $sql = sprintf(' SELECT * FROM %s WHERE ID_Tipoprioridad = ? ', self::TABLE );

            $sth = \db()->prepare( $sql );
            $sth->setFetchMode( \PDO::FETCH_CLASS, '\TipoPrioridad\TipoPrioridad' );
            $sth->execute( array( $this->ID_Tipoprioridad ) );

            $this->data = $sth->fetch();
        }

        function getAll() 
        {
            $sql = ' SELECT * FROM ' . self::TABLE . ' where Activo=1 ';

            $sth = \db()->prepare( $sql );
            $sth->setFetchMode( \PDO::FETCH_CLASS, '\TipoPrioridad\TipoPrioridad' );
            @$sth->execute( array( ID_Tipoprioridad ) );

            return $sth->fetchAll();

        }
	
        private function loadClave() 
        {
            $sql = sprintf(' SELECT * FROM %s WHERE Clave = ? ', self::TABLE );

            $sth = \db()->prepare( $sql );
            $sth->setFetchMode( \PDO::FETCH_CLASS, '\TipoPrioridad\TipoPrioridad' );
            $sth->execute( array( $this->Clave ) );

            $this->data = $sth->fetch();
        }

        function __get( $key ) 
        {
            switch($key) 
            {
                case 'ID_Tipoprioridad':
                case 'Clave':
                case 'Descripcion':
                case 'Prioridad':
                    $this->load();
                    return @$this->data->$key;
                default:
                  return $this->key;
            }
        }
	
        function validaClave( $key ) 
        {
            switch($key) {
                case 'Clave':
                    $this->loadClave();
                    return @$this->data->$key;
                default:
                    return $this->key;
            }
        }

        function save( $_post ) 
        {
            $sql = sprintf(' 
                INSERT INTO ' . self::TABLE . ' 
                    SET	Descripcion = :Descripcion,
                    Clave = :Clave,
                    Prioridad = :Prioridad,
                    Status = :Status        
            ');

            $this->save = \db()->prepare($sql);
            $this->save->bindValue( ':Descripcion', $_post['Descripcion'], \PDO::PARAM_STR );
            $this->save->bindValue( ':Clave', $_post['Descripcion'], \PDO::PARAM_STR );
            $this->save->bindValue( ':Prioridad', $_post['ID_Tipoprioridad'], \PDO::PARAM_STR );
            $this->save->bindValue( ':Status', 'A', \PDO::PARAM_STR );
            $this->save->execute();
        }

        /*function password( $data ) 
        {
          if( !$data['password'] ) { throw new \ErrorException( 'Unfortuantly you wont get far without a password.' ); }

          $sql = sprintf('UPDATE' . self::TABLE . 'SETpassword = :passwordWHEREid_user = ' . $this->id_user . '');

          $this->save = \db()->prepare($sql);
          $password = password_hash( $data['password'], PASSWORD_BCRYPT, array( 'cost' => 11 ) );
          $this->save->bindValue( ':password', $password, \PDO::PARAM_STR );
          $this->save->execute();
        }*/

        function actualizarPrioridad( $data ) 
        {
          $sql = "UPDATE " . self::TABLE . " 
              SET
                Descripcion = '".$data['Descripcion']."', 
                Prioridad = '".$data['Prioridad']."'
              WHERE ID_Tipoprioridad = '".$data['ID_Tipoprioridad']."'";
           $rs = mysqli_query(\db2(), $sql) or die(mysqli_error(\db2()));
        }

        function borrarTipoPrioridad( $data ) 
        {
            $sql = ' UPDATE ' . self::TABLE . ' SET Activo = 0 WHERE ID_Tipoprioridad = ? ';
            $this->save = \db()->prepare($sql);
            $this->save->execute( array(
                $data['ID_Tipoprioridad']
            ));
        }
	  
        function exist($prioridad) 
        {
            $sql = sprintf(' SELECT * FROM ' . self::TABLE . ' WHERE Prioridad = ? ');
            $sth = \db()->prepare( $sql );
            $sth->setFetchMode( \PDO::FETCH_CLASS, '\TipoPrioridad\TipoPrioridad' );
            $sth->execute( array( $prioridad ) );

            $this->data = $sth->fetch();
            if(!$this->data)
                return false; 
            else 
                return true;
        }


        function recovery( $data ) 
        {
            $sql = "UPDATE t_tiposprioridad SET Activo = 1 WHERE  ID_Tipoprioridad='".$data['ID_Tipoprioridad']."';";
            $this->delete = \db()->prepare($sql);
            $this->delete->execute( array(
                $data['ID_Tipoprioridad']
            ));
        }

        public function inUse( $data ) 
        {
          $sql = "SELECT ID_Tipoprioridad FROM `th_pedido` WHERE ID_Tipoprioridad ='".$data['ID_Tipoprioridad']."'";
          $sth = \db()->prepare($sql);
          $sth->execute();
          $data = $sth->fetch();

          if ($data['ID_Tipoprioridad']) 
              return true;
          else
              return false;
        }
  }