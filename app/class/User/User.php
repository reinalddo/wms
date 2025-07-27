<?php

  namespace User;

  class User {

    const TABLE = 'c_usuario';
    var $identifier;

    public function __construct( $id_user = false, $key = false ) {

      if( $id_user ) {
        $this->id_user = (int) $id_user;
      }

      if($key) {

        $sql = sprintf('
          SELECT
            id_user
          FROM
            %s
          WHERE
            identifier = ?
        ',
          self::TABLE
        );

        $sth = \db()->prepare($sql);
        $sth->setFetchMode(\PDO::FETCH_CLASS, '\User\User');
        $sth->execute(array($key));

        $user = $sth->fetch();

        $this->id_user = $user->id_user;

      }

    }

    private function load() {

      $sql = sprintf('
        SELECT
          *
        FROM
          %s
        WHERE
          id_user = ?
      ',
        self::TABLE
      );

      $sth = \db()->prepare( $sql );
      $sth->setFetchMode( \PDO::FETCH_CLASS, '\User\User' );
      $sth->execute( array( $this->id_user ) );

      $this->data = $sth->fetch();

    }

    function __get( $key ) {

      switch($key) {
        case 'id_user':
        case 'name':
        case 'email':
        case 'password':
        case 'identifier':
        case 'emails_allowed':
        case 'due_date':
        case 'settings_company':
        case 'settings_description':
        case 'settings_press_contact':
        case 'settings_logo':
        case 'settings_primary_color':
        case 'settings_secondary_color':
        case 'timestamp':
        case 'subdomain':
          $this->load();
          return @$this->data->$key;
        default:
          return $this->key;
      }

    }

    function save( $data ) {

      if( !$data['name'] ) { throw new \ErrorException( 'Full name is required.' ); }
      if( !$data['email'] ) { throw new \ErrorException( 'Cant really register without an email address.' ); }
      if( !$data['password'] AND !$data['save'] ) { throw new \ErrorException( 'Unfortuantly you wont get far without a password.' ); }

      $sql = sprintf('
        INSERT INTO
          ' . self::TABLE . '
        SET
          id_user = :id_user
        , name = :name
        , email = :email
        , password = :password
        , country = :country
        , identifier = :identifier
        , emails_allowed = :emails_allowed
        , timestamp = :timestamp
        , subdomain = :subdomain
        ON DUPLICATE KEY UPDATE
          email = :email
        , name = :name
        , country = :country
      ');

      $this->save = \db()->prepare($sql);

      $password = password_hash( $data['password'], PASSWORD_BCRYPT, array( 'cost' => 11 ) );
      $identifier = bin2hex(openssl_random_pseudo_bytes(10));

      $this->identifier = $identifier;

      $this->save->bindValue( ':id_user', $this->id_user, \PDO::PARAM_STR );
      $this->save->bindValue( ':name', $data['name'], \PDO::PARAM_STR );
      $this->save->bindValue( ':email', $data['email'], \PDO::PARAM_STR );
      $this->save->bindValue( ':password', $password, \PDO::PARAM_STR );
      $this->save->bindValue( ':country', $data['country'], \PDO::PARAM_STR );
      $this->save->bindValue( ':identifier', $identifier, \PDO::PARAM_STR );
      $this->save->bindValue( ':emails_allowed', $data['emails_allowed'], \PDO::PARAM_STR );
      $this->save->bindValue( ':timestamp', time(), \PDO::PARAM_STR );
      $this->save->bindValue( ':subdomain', $data['subdomain'], \PDO::PARAM_STR );

      $this->save->execute();

    }

    function password( $data ) {

      if( !$data['password'] ) { throw new \ErrorException( 'Unfortuantly you wont get far without a password.' ); }

      $sql = sprintf('
        UPDATE
          ' . self::TABLE . '
        SET
          password = :password
        WHERE
          id_user = ' . $this->id_user . '
      ');

      $this->save = \db()->prepare($sql);

      $password = password_hash( $data['password'], PASSWORD_BCRYPT, array( 'cost' => 11 ) );

      $this->save->bindValue( ':password', $password, \PDO::PARAM_STR );
      $this->save->execute();

    }

    function settings_data( $data ) {

      $sql = '
        UPDATE
          ' . self::TABLE . '
        SET
          settings_company = ?
        , settings_description = ?
        , settings_press_contact = ?
        , identifier = ?
        WHERE
          id_user = ?
      ';

      $this->save = \db()->prepare($sql);
      $this->save->execute( array(
        $data['settings_company']
      , $data['settings_description']
      , $data['settings_press_contact']
      , $data['identifier']
      , $data['id_user']
      ) );

    }

    function settings_design( $data ) {

      $sql = '
        UPDATE
          ' . self::TABLE . '
        SET
          settings_logo = ?
        , settings_primary_color = ?
        , settings_secondary_color = ?
        WHERE
          id_user = ?
      ';

      $this->save = \db()->prepare($sql);
      $this->save->execute( array(
        $data['settings_logo']
      , $data['settings_primary_color']
      , $data['settings_secondary_color']
      , $data['id_user']
      ) );

    }

  }
