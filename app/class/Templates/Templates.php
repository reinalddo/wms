<?php

  namespace Templates;

  class Templates {

    const TABLE = 'templates';
    const TABLE_MAIL = 'mail';

    public function __construct( $id_template = false ) {

      if( $id_template ) {
        $this->id_template = (int) $id_template;
      }

    }

    private function load() {

      $sql = sprintf('
        SELECT
          *
        FROM
          %s
        WHERE
          id_template = ?
      ',
        self::TABLE
      );

      $sth = \db()->prepare( $sql );
      $sth->setFetchMode( \PDO::FETCH_CLASS, '\Templates\Templates' );
      $sth->execute( array( $this->id_template ) );

      $this->data = $sth->fetch();

    }

    function __get( $key ) {

      switch($key) {
        case 'id_template':
        case 'id_user':
        case 'type':
        case 'title':
        case 'content':
        case 'timestamp':
          $this->load();
          return @$this->data->$key;
        default:
          return $this->key;
      }

    }

    function getList( $id_user, $type = false ) {

      $sql = '
        SELECT
          t.*
        , ( SELECT COUNT( id_subscriber ) FROM ' . self::TABLE_MAIL . ' WHERE id_template = t.id_template ) AS sent
        , ( SELECT COUNT( id_subscriber ) FROM ' . self::TABLE_MAIL . ' WHERE id_template = t.id_template AND open = 1 ) AS opens
        FROM
          ' . self::TABLE . ' AS t
        WHERE
          t.id_user = ?
      ';

      if( $type ) {
        $sql .= ' AND t.type = "' . $type . '"';
      }

      $sql .= ' ORDER BY t.timestamp DESC';

      $sth = \db()->prepare( $sql );
      $sth->setFetchMode( \PDO::FETCH_CLASS, '\Templates\Templates' );
      $sth->execute( array( $id_user ) );

      return $sth->fetchAll();

    }

    function save( $data ) {

      $sql = sprintf('
        INSERT INTO
          ' . self::TABLE . '
        SET
          id_template = :id_template
        , id_user = :id_user
        , type = :type
        , title = :title
        , content = :content
        , lists = :lists
        , timestamp = :timestamp
        ON DUPLICATE KEY UPDATE
          title = :title
        , content = :content
      ');

      $this->save = \db()->prepare($sql);

      $this->save->bindValue( ':id_template', $this->id_template, \PDO::PARAM_STR );
      $this->save->bindValue( ':id_user', $data['id_user'], \PDO::PARAM_STR );
      $this->save->bindValue( ':type', $data['type'], \PDO::PARAM_STR );
      $this->save->bindValue( ':title', $data['title'], \PDO::PARAM_STR );
      $this->save->bindValue( ':content', $data['content'], \PDO::PARAM_STR );
      $this->save->bindValue( ':lists', json_encode( $data['lists'] ), \PDO::PARAM_STR );
      $this->save->bindValue( ':timestamp', time(), \PDO::PARAM_STR );

      $this->save->execute();

      return array( 'id_template' => \db()->lastInsertId() );

    }

  }
