<?php

  namespace Mail;

  class Mail {

    const TABLE = 'mail';
    const TABLE_TEMPLATE = 'templates';
    const TABLE_SUBSCRIBERS = 'subscribers';

    public function __construct( $id_mail = false, $key = false ) {

      if( $id_mail ) {
        $this->id_mail = (int) $id_mail;
      }

      if($key) {

        $sql = sprintf('
          SELECT
            id_mail
          FROM
            %s
          WHERE
            hash = ?
        ',
          self::TABLE
        );

        $sth = \db()->prepare($sql);
        $sth->setFetchMode(\PDO::FETCH_CLASS, '\Mail\Mail');
        $sth->execute(array($key));

        $user = $sth->fetch();

        $this->id_mail = $user->id_mail;

      }

    }

    private function load() {

      $sql = sprintf('
        SELECT
          *
        FROM
          %s
        WHERE
          id_mail = ?
      ',
        self::TABLE
      );

      $sth = \db()->prepare( $sql );
      $sth->setFetchMode( \PDO::FETCH_CLASS, '\Mail\Mail' );
      $sth->execute( array( $this->id_mail ) );

      $this->data = $sth->fetch();

    }

    function __get( $key ) {

      switch($key) {
        case 'id_mail':
        case 'hash':
        case 'id_template':
        case 'id_subscriber':
        case 'id_user':
        case 'invite':
        case 'response':
        case 'open':
        case 'is_sent':
        case 'timestamp':
          $this->load();
          return @$this->data->$key;
        default:
          return $this->key;
      }

    }

    function getList( $id_template ) {

      $sql = '
        SELECT
            m.*
          , s.first_name
          , s.last_name
          , s.email
        FROM
          ' . self::TABLE . ' AS m
        LEFT JOIN
          ' . self::TABLE_SUBSCRIBERS . ' AS s
        ON
          m.id_subscriber = s.id_subscriber
        WHERE
          m.id_template = ?
      ';

      $sth = \db()->prepare( $sql );
      $sth->setFetchMode( \PDO::FETCH_CLASS, '\Mail\Mail' );
      $sth->execute( array( $id_template ) );

      return $sth->fetchAll();

    }

    function getNotSent() {

      $sql = '
        SELECT
          t.type
        , t.title
        , t.content
        , s.first_name
        , s.last_name
        , s.email
        , m.invite
        , m.id_mail
        , m.hash
        FROM
          ' . self::TABLE . ' AS m
        LEFT JOIN
          ' . self::TABLE_TEMPLATE . ' AS t
        ON
          m.id_template = t.id_template
        LEFT JOIN
          ' . self::TABLE_SUBSCRIBERS . ' AS s
        ON
          m.id_subscriber = s.id_subscriber
        WHERE
          m.is_sent = 0
        OR
          m.is_sent IS NULL
      ';

      $sth = \db()->prepare( $sql );
      $sth->setFetchMode( \PDO::FETCH_CLASS, '\Mail\Mail' );
      $sth->execute();

      return $sth->fetchAll();

    }

    function sentThisMonth( $id_user ) {

      $sql = '
        SELECT
          COUNT( id_subscriber ) AS total
        FROM
          ' . self::TABLE . '
        WHERE
          id_user = ?
        AND
          MONTH( FROM_UNIXTIME( timestamp ) ) = MONTH( NOW() )
      ';

      $sth = \db()->prepare( $sql );
      $sth->setFetchMode( \PDO::FETCH_CLASS, '\Mail\Mail' );
      $sth->execute( array( $id_user ) );
      $rr = $sth->fetch();

      return $rr->total;

    }

    function getTotalsSubscriber( $id_subscriber ) {

      $sql = '
        SELECT
          t.type
        , t.title
        , t.content
        , s.id_subscriber
        , s.first_name
        , s.last_name
        , s.email
        , s.media
        , ( SELECT COUNT( id_subscriber ) FROM ' . self::TABLE . ' WHERE id_subscriber = m.id_subscriber AND open = 1 ) AS opens
        , ( SELECT COUNT( id_subscriber ) FROM ' . self::TABLE . ' WHERE id_subscriber = m.id_subscriber ) AS sent
        FROM
          ' . self::TABLE . ' AS m
        LEFT JOIN
          ' . self::TABLE_TEMPLATE . ' AS t
        ON
          m.id_template = t.id_template
        LEFT JOIN
          ' . self::TABLE_SUBSCRIBERS . ' AS s
        ON
          m.id_subscriber = s.id_subscriber
        WHERE
          s.id_subscriber = ?
        GROUP BY
          m.id_subscriber
      ';

      $sth = \db()->prepare( $sql );
      $sth->setFetchMode( \PDO::FETCH_CLASS, '\Mail\Mail' );
      $sth->execute(array($id_subscriber));

      return $sth->fetchAll();

    }

    function getTotals() {

      $sql = '
        SELECT
          t.type
        , t.title
        , t.content
        , s.id_subscriber
        , s.first_name
        , s.last_name
        , s.email
        , s.media
        , ( SELECT COUNT( id_subscriber ) FROM ' . self::TABLE . ' WHERE id_subscriber = m.id_subscriber ) AS sent
        FROM
          ' . self::TABLE . ' AS m
        LEFT JOIN
          ' . self::TABLE_TEMPLATE . ' AS t
        ON
          m.id_template = t.id_template
        LEFT JOIN
          ' . self::TABLE_SUBSCRIBERS . ' AS s
        ON
          m.id_subscriber = s.id_subscriber
        WHERE
          m.id_user = ' . ID_USER . '
        GROUP BY
          m.id_subscriber
      ';

      $sth = \db()->prepare( $sql );
      $sth->setFetchMode( \PDO::FETCH_CLASS, '\Mail\Mail' );
      $sth->execute();

      return $sth->fetchAll();

    }

    function getPressTotalSent() {

      $sql = '
        SELECT
          count( m.id_mail ) AS sent
        FROM
          ' . self::TABLE . ' AS m
        LEFT JOIN
          ' . self::TABLE_TEMPLATE . ' AS t
        ON
          m.id_template = t.id_template
        WHERE
          t.type = "PRESS"

      ';

      $sth = \db()->prepare( $sql );
      $sth->setFetchMode( \PDO::FETCH_CLASS, '\Mail\Mail' );
      $sth->execute();

      return $sth->fetchAll();

    }

    function getPressTotalOpen() {

      $sql = '
        SELECT
          count( m.id_mail ) AS opens
        FROM
          ' . self::TABLE . ' AS m
        LEFT JOIN
          ' . self::TABLE_TEMPLATE . ' AS t
        ON
          m.id_template = t.id_template
        WHERE
          t.type = "PRESS"
        AND
          m.open = 1
      ';

      $sth = \db()->prepare( $sql );
      $sth->setFetchMode( \PDO::FETCH_CLASS, '\Mail\Mail' );
      $sth->execute();

      return $sth->fetchAll();

    }

    function topFive() {

      $sql = '
        SELECT
          s.id_subscriber
        , s.first_name
        , s.last_name
        , s.email
        , s.media
        , ( SELECT COUNT( id_subscriber ) FROM ' . self::TABLE . ' WHERE id_subscriber = m.id_subscriber AND open = 1 ) AS opens
        , ( SELECT COUNT( id_subscriber ) FROM ' . self::TABLE . ' WHERE id_subscriber = m.id_subscriber ) AS sent
        FROM
          ' . self::TABLE . ' AS m
        LEFT JOIN
          ' . self::TABLE_TEMPLATE . ' AS t
        ON
          m.id_template = t.id_template
        LEFT JOIN
          ' . self::TABLE_SUBSCRIBERS . ' AS s
        ON
          m.id_subscriber = s.id_subscriber
        WHERE
          s.id_user = ' . ID_USER . '
        GROUP BY
          m.id_subscriber
      ';

      $sth = \db()->prepare( $sql );
      $sth->setFetchMode( \PDO::FETCH_CLASS, '\Mail\Mail' );
      $sth->execute();

      return $sth->fetchAll();

    }

    function save( $data ) {

      $sql = sprintf('
        INSERT INTO
          ' . self::TABLE . '
        SET
          id_mail = :id_mail
        , hash = :hash
        , id_template = :id_template
        , id_subscriber = :id_subscriber
        , id_user = :id_user
        , invite = :invite
        , response = :response
        , is_sent = :is_sent
        , timestamp = :timestamp
      ');

      $this->save = \db()->prepare($sql);

      $this->save->bindValue( ':id_mail', $this->id_mail, \PDO::PARAM_INT );
      $this->save->bindValue( ':hash', md5( $this->id_user . microtime() ), \PDO::PARAM_INT );
      $this->save->bindValue( ':id_template', $data['id_template'], \PDO::PARAM_INT );
      $this->save->bindValue( ':id_subscriber', $data['id_subscriber'], \PDO::PARAM_INT );
      $this->save->bindValue( ':id_user', $data['id_user'], \PDO::PARAM_INT );
      $this->save->bindValue( ':invite', $data['invite'], \PDO::PARAM_INT );
      $this->save->bindValue( ':response', $data['response'], \PDO::PARAM_INT );
      $this->save->bindValue( ':is_sent', ( $data['is_sent'] ) ? $data['is_sent'] : 0, \PDO::PARAM_INT );
      $this->save->bindValue( ':timestamp', time(), \PDO::PARAM_STR );

      $this->save->execute();

    }

    function setSent( $id_mail ) {

      $sql = sprintf('
        UPDATE
          ' . self::TABLE . '
        SET
          is_sent = 1
        WHERE
          id_mail = ?
      ');

      $this->save = \db()->prepare($sql);

      $this->save->execute( array( $id_mail ) );

    }

    function setOpen( $id_mail ) {

      $sql = sprintf('
        UPDATE
          ' . self::TABLE . '
        SET
          open = 1
        WHERE
          id_mail = ?
      ');

      $this->save = \db()->prepare($sql);

      $this->save->execute( array( $id_mail ) );

    }

    function setResponse( $id_mail, $response ) {

      $sql = sprintf('
        UPDATE
          ' . self::TABLE . '
        SET
          response = ?
        WHERE
          id_mail = ?
      ');

      $this->save = \db()->prepare($sql);

      $this->save->execute( array( $response, $id_mail ) );

    }

  }
