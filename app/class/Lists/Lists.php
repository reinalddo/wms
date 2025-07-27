<?php

  namespace Lists;

  class Lists {

    const TABLE = 'lists';

    public function __construct( $id_list = false ) {

      if( $id_list ) {
        $this->id_list = (int) $id_list;
      }

    }

    private function load() {

      $sql = sprintf('
        SELECT
          *
        FROM
          %s
        WHERE
          id_list = ?
      ',
        self::TABLE
      );

      $sth = \db()->prepare( $sql );
      $sth->setFetchMode( \PDO::FETCH_CLASS, '\Lists\Lists' );
      $sth->execute( array( $this->id_list ) );

      $this->data = $sth->fetch();

    }

    function __get( $key ) {

      switch($key) {
        case 'id_list':
        case 'id_user':
        case 'title':
        case 'timestamp':
          $this->load();
          return @$this->data->$key;
        default:
          return $this->key;
      }

    }

    function getList( $id_user ) {

      $sql = '
        SELECT
          *
        FROM
          ' . self::TABLE . '
        WHERE
          id_user = ?
      ';

      $sth = \db()->prepare( $sql );
      $sth->setFetchMode( \PDO::FETCH_CLASS, '\Lists\Lists' );
      $sth->execute( array( $id_user ) );

      return $sth->fetchAll();

    }

    function save( $data ) {

      $sql = sprintf('
        INSERT INTO
          ' . self::TABLE . '
        SET
          id_list = :id_list
        , id_user = :id_user
        , title = :title
        , timestamp = :timestamp
      ');

      $this->save = \db()->prepare($sql);

      $this->save->bindValue( ':id_list', $this->id_list, \PDO::PARAM_STR );
      $this->save->bindValue( ':id_user', $data['id_user'], \PDO::PARAM_STR );
      $this->save->bindValue( ':title', $data['title'], \PDO::PARAM_STR );
      $this->save->bindValue( ':timestamp', time(), \PDO::PARAM_STR );

      $this->save->execute();

    }

  }
