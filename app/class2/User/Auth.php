<?php

  namespace User;

  class Auth extends \User\User {

    function authByEmail( $email ) {

      if( !$email['email'] ) { throw new \ErrorException( 'Email is required to authenticate' ); }

      $sql = sprintf('
        SELECT
          id_user
        , pwd_usuario
        FROM
          %s
        WHERE
          cve_usuario = ?
      ',
        self::TABLE
      );

      $sth = \db()->prepare( $sql );
      $sth->setFetchMode( \PDO::FETCH_CLASS, '\User\User' );
      $ex = $sth->execute( array( $email['email'] ) );
      $user = $sth->fetch();

      if( $user ) {

        if( $email['password'] == $user->password ) {

          // Password is correct
          return array( 'id_user' => $user->id_user );

        } else {

          // Password is incorrect
          throw new \ErrorException( 'Incorrect password provided' );

        }

      } else {

        // Email does NOT exist
        throw new \ErrorException( 'There is no such email in our system' );

      }

    }

  }
