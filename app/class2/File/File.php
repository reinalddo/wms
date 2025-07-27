<?php

  namespace File;

  class File {

    const TABLE = 'file';

    public function __construct( $id_file = false ) {

      if( $id_file ) {
        $this->id_file = (int) $id_file;
      }

    }

    function __get( $key ) {

      switch( $key ) {
        case 'id_file':
        case 'id_template':
        case 'file_original':
        case 'filename':
        case 'filesize':
        case 'hash':
        case 'timestamp':
          $this->load();
          return @$this->data->$key;
        default:
          return $this->key;
      }

    }

    private function load() {

      $sql = '
        SELECT
          *
        FROM
          ' . self::TABLE . '
        WHERE
          id_file = ?
      ';


      $sth = \db()->prepare( $sql );
      $sth->setFetchMode( \PDO::FETCH_CLASS, '\File\File' );
      $sth->execute( array( $this->id_file ) );

      $this->data = $sth->fetch();

    }

    function getList( $id_template = false ) {

      $sql = '
        SELECT
          *
        FROM
          ' . self::TABLE . '
      ';

      if( $id_template ) {
        $sql .= ' WHERE id_template = ' . $id_template;
      }

      $sth = \db()->prepare( $sql );
      $sth->setFetchMode( \PDO::FETCH_CLASS, '\File\File' );
      $sth->execute();

      return $sth->fetchAll();

    }

    function save( $data ) {

      $sql = sprintf('
        INSERT INTO
          ' . self::TABLE . '
        SET
          id_file = :id_file
        , id_template = :id_template
        , file_original = :file_original
        , filename = :filename
        , filesize = :filesize
        , hash = :hash
        , timestamp = :timestamp
      ');

      $this->save = \db()->prepare( $sql );

      $this->save->bindValue( ':id_file', $this->id_file, \PDO::PARAM_INT );
      $this->save->bindValue( ':id_template', $data['id_template'], \PDO::PARAM_INT );
      $this->save->bindValue( ':file_original', $data['file_original'], \PDO::PARAM_STR );
      $this->save->bindValue( ':filename', $data['filename'], \PDO::PARAM_STR );
      $this->save->bindValue( ':filesize', $data['filesize'], \PDO::PARAM_INT );
      $this->save->bindValue( ':hash', md5( microtime() ), \PDO::PARAM_INT );
      $this->save->bindValue( ':timestamp', time(), \PDO::PARAM_STR );

      $this->save->execute();

      return array( 'id_file' => \db()->lastInsertId() );

    }

  }
