<?php

  namespace Lists;

  class Subscribers {

    const TABLE = 'subscribers_lists';
    const TABLE_LISTS = 'lists';
    const TABLE_SUBSCRIBER = 'subscribers';

    function getList( $id_list = false, $id_subscriber = false, $approved = true ) {

      $sql = '
        SELECT
          s.*
        , l.title
        FROM
          ' . self::TABLE . ' AS s
        LEFT JOIN
          ' . self::TABLE_LISTS . ' AS l
        ON
          s.id_list = l.id_list
        LEFT JOIN
          ' . self::TABLE_SUBSCRIBER . ' AS su
        ON
          s.id_subscriber = su.id_subscriber
      ';

      if( $id_list ) {
        $sql .= ' WHERE s.id_list = ' . $id_list;
      }

      if( $id_subscriber ) {
        $sql .= ' WHERE s.id_subscriber = ' . $id_subscriber;
      }

      if( $approved ) {
        $sql .= ' AND su.approved = 1';
      }

      $sth = \db()->prepare( $sql );
      $sth->setFetchMode( \PDO::FETCH_CLASS, '\Lists\Subscribers' );
      $sth->execute();

      return $sth->fetchAll();

    }

    function save( $data ) {

      $sql = sprintf('
        INSERT INTO
          ' . self::TABLE . '
        SET
          id_associate = :id_associate
        , id_subscriber = :id_subscriber
        , id_list = :id_list
      ');

      $this->save = \db()->prepare($sql);

      $this->save->bindValue( ':id_associate', $this->id_associate, \PDO::PARAM_STR );
      $this->save->bindValue( ':id_subscriber', $data['id_subscriber'], \PDO::PARAM_STR );
      $this->save->bindValue( ':id_list', $data['id_list'], \PDO::PARAM_STR );

      $this->save->execute();

    }

  }
