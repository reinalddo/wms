<?php

namespace RutasSurtido;

$tools = new \Tools\Tools();

class RutasSurtido {
  
    private $tools = null;

    const TABLE = 'c_ubicacion';
    const HEADER = 'th_ruta_surtido';
    const CUERPO = 'td_ruta_surtido';
    var $identifier;

    public function __construct( $idy_ubica = false, $key = false ) 
    {
      
        $this->tools = new \Tools\Tools();

        if( $idy_ubica ) {
            $this->idy_ubica = (int) $idy_ubica;
        }

        if($key) {

            $sql = sprintf('
          SELECT
            idy_ubica
          FROM
            %s
          WHERE
            idy_ubica = ?
        ',
                self::TABLE
            );

            $sth = \db()->prepare($sql);
            $sth->setFetchMode(\PDO::FETCH_CLASS, '\RutasSurtido\RutasSurtido');
            $sth->execute(array($key));

            $RutasSurtido = $sth->fetch();

            $this->idy_ubica = $RutasSurtido->idy_ubica;

        }

    }
  
    function importar_rutas($data) 
    {
//       INSERT INTO `td_ruta_surtido`
//       (`idr`, 
//        `idy_ubica`, 
//        `orden_secuencia`, 
//        `Activo`) 

//        VALUES 
//        (1,
//         1...486,
//         1...486,
//         1)
      
      $sql = "SELECT idy_ubica FROM `c_ubicacion` 
              WHERE c_ubicacion.cve_pasillo = '{$data["pasillo"]}' 
              AND c_ubicacion.cve_almac = '{$data["cve_almacen"]}' 
              ORDER BY c_ubicacion.idy_ubica ASC;";
      
      $query_importar = $this->tools->dbQuery($sql);
      $valores = $query_importar->fetch();
      
      
      echo var_dump($valores);
      die();
      /*
      $sql = sprintf('
        INSERT INTO ' . self::CUERPO . '
        SET       
          idr = :idr,
          idy_ubica = :idy_ubica,
          orden_secuencia = :orden_secuencia,
          Activo= 1
      ');

       $this->guardarOrdenSec = \db()->prepare($sql);
       $this->guardarOrdenSec->bindValue( ':idy_ubica', $data['idy_ubica'], \PDO::PARAM_STR );
       $this->guardarOrdenSec->bindValue( ':orden_secuencia', $data['orden_secuencia'], \PDO::PARAM_STR );
       $this->guardarOrdenSec->bindValue( ':idr', $data['id_ruta'], \PDO::PARAM_STR );

       $this->guardarOrdenSec->execute();
       */
      return $valores;
    }

    function actualizarRutasSurtido( $data ) 
    {
      $sql = '
        UPDATE ' . self::TABLE . '
        SET
          orden_secuencia = ?
        WHERE idy_ubica = ?
      ';
      $this->save = \db()->prepare($sql);
      $this->save->execute(array($data['orden_secuencia'], $data['idy_ubica']));
    }

    function guardarRuta($data) 
    {
      //Crea una nueva ruta en th_ruta_surtido
      $sql = sprintf('
        INSERT INTO '. self::HEADER .'
        SET       
          nombre = '."'".$data['nombre']."'".',
          cve_almac = (SELECT cve_almac FROM `c_ubicacion` WHERE c_ubicacion.idy_ubica = '."'".$data['ubicacion']."'".' LIMIT 1),
          Activo= 1,
        status="P"
      ');
      //almacen: (SELECT DISTINCT cve_almac from c_ubicacion where orden_secuencia <> "" limit 1)
      $this->guardarRuta = \db()->prepare($sql);
      $this->guardarRuta->execute();

      //Hace un Update en c_ubicacion en el campo orden_secuencia a todos los valores en "" o 0 los cambia a Null
      $sqlUpdate = sprintf('
        UPDATE ' . self::TABLE . '
        SET orden_secuencia = null
        WHERE orden_secuencia <> "" or orden_secuencia = 0
      ');

      $sth = \db()->prepare( $sqlUpdate );
      $sth->setFetchMode( \PDO::FETCH_CLASS, '\RutasSurtido\RutasSurtido' );
      $sth->execute( array($data['orden_secuencia'])); 

      if(!$this->guardarRuta){return false;}
      else {return true;}
    }


    function guardarOrdenSec($data) 
    {
      $sql = sprintf('
        INSERT INTO ' . self::CUERPO . '
        SET
          idr = (SELECT MAX(idr) AS id FROM th_ruta_surtido),
          idy_ubica = :idy_ubica,
          orden_secuencia = :orden_secuencia,
          Activo= 1
      ');

       $this->guardarOrdenSec = \db()->prepare($sql);
       $this->guardarOrdenSec->bindValue( ':idy_ubica', $data['idy_ubica'], \PDO::PARAM_STR );
       $this->guardarOrdenSec->bindValue( ':orden_secuencia', $data['orden_secuencia'], \PDO::PARAM_STR );

       $this->guardarOrdenSec->execute();
    }
  
    function editarOrdenSec($data) 
    {
      $sql = sprintf('
        INSERT INTO ' . self::CUERPO . '
        SET       
          idr = :idr,
          idy_ubica = :idy_ubica,
          orden_secuencia = :orden_secuencia,
          Activo= 1
      ');

       $this->guardarOrdenSec = \db()->prepare($sql);
       $this->guardarOrdenSec->bindValue( ':idy_ubica', $data['idy_ubica'], \PDO::PARAM_STR );
       $this->guardarOrdenSec->bindValue( ':orden_secuencia', $data['orden_secuencia'], \PDO::PARAM_STR );
       $this->guardarOrdenSec->bindValue( ':idr', $data['id_ruta'], \PDO::PARAM_STR );

       $this->guardarOrdenSec->execute();
    }
  
    
}
