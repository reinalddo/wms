<?php

  namespace ZonaHoraria;

  class ZonaHoraria {

    const ZONAHORARIA = 'c_zonahoraria';

    var $identifier;

     public function __construct( $descripcion = false, $key = false ) {

      if( $descripcion ) {
        $this->descripcion = (int) $descripcion;
      }

      if($key) {

        $sql = sprintf('
          SELECT
            descripcion
          FROM
            %s
          WHERE
            descripcion = ?
        ',
          self::ZONAHORARIA
        );

        $sth = \db()->prepare($sql);
        $sth->setFetchMode(\PDO::FETCH_CLASS, '\ZonaHoraria\ZonaHoraria');
        $sth->execute(array($key));

        $zonahoraria = $sth->fetch();

        $this->descripcion = $zonahoraria->descripcion;

      }

    }
  
    function save( $data ) {
      $sql = sprintf('
        INSERT INTO
          ' . self::ZONAHORARIA . '
        SET
          descripcion = :descripcion
        , id_user = :id_user  		
      ');

      $this->save = \db()->prepare($sql);

      //$this->save->bindValue( ':ID_Proveedor', $this->ID_Proveedor, \PDO::PARAM_STR );
      $this->save->bindValue( ':descripcion', $data['descripcion'], \PDO::PARAM_STR );
      $this->save->bindValue( ':id_user', $_SESSION['id_user'], \PDO::PARAM_STR );

      $this->save->execute();
    
    if(!$this->data)
      return true; 
    else 
      return false;

    }

   function update( $data ) {
       $sql = sprintf("
          UPDATE 
          " . self::ZONAHORARIA . "
           SET 
            descripcion = :descripcion
            WHERE 
            id_user = :id_user");
         $this->save = \db()->prepare($sql);

          $this->save->bindValue( ':descripcion', $data['descripcion'], \PDO::PARAM_STR );
          $this->save->bindValue( ':id_user', $_SESSION['id_user'], \PDO::PARAM_STR );

      $this->save->execute();
    
      if(!$this->data)
        return true; 
      else 
        return false;
    }

    function getZonas() {

    /*  $array = [
        "(UTC-12:00) Linea internacional de cambio de fecha", 
        "(UTC-11:00) Hora universal coordinada-11",
        "(UTC-10:00) Hawai", 
        "(UTC-10:00) Islas Aleutianas",
        "(UTC-9:30) Islas Marquesas",
        "(UTC-9:00) Alaska",
        "(UTC-9:00) Hora universal coordinada-09",
        "(UTC-8:00) Baja California",
        "(UTC-8:00) Hora del Pacifico (EE.UU. y Canadá)",
        "(UTC-8:00) Hora universal coordinada-09",
        "(UTC-87:00) Arizona",

      ]; */
      $array = [];

      foreach(timezone_abbreviations_list() as $abbr => $timezone){
        foreach($timezone as $val){
                if(isset($val['timezone_id'])){
                      array_push($array, $val['timezone_id']);
                }
        }
    } 
        array_multisort($array);

          return array_values(array_unique($array));

      }

      function getZonaHoraria() {

      $sql = sprintf('select descripcion, id_user from c_zonahoraria order by id desc limit 1',
              self::ZONAHORARIA
          );
          $sth = \db()->prepare( $sql );
          $sth->setFetchMode( \PDO::FETCH_CLASS, '\ZonaHoraria\ZonaHoraria' );
          $sth->execute( array( $this->descripcion ) );

          $this->data = $sth->fetch();
      }

     function existe($id_user) {

      $sql = "
          SELECT
          *
        FROM
          c_zonahoraria
        WHERE
            id_user = '".$id_user."'";
         $sth = \db()->prepare( $sql );
          $sth->setFetchMode( \PDO::FETCH_CLASS, '\ZonaHoraria\ZonaHoraria' );
          $sth->execute( array( $this->id_user ) );

          $this->data = $sth->fetch();

    
      if($this->save)
         return false; 
      else 
         return true;

      }
}

