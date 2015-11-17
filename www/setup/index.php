<?php

require_once(__DIR__ . '/../tournament-www.php');

class Setup {
  public function main() {
    if (!S()->isSignedIn()) {
      return R('/');
    }

    if (!C()->databasename() || !C()->databaseusername()
        || !C()->databasehost() || !C()->databasepassword()) {
      return '<p>Add databasename, datausername, databasehose and '
        . 'databasepassword values to config.json and reload this page.<p>';
    }

    $this->useSuccessful = false;
    register_shutdown_function([$this, 'databaseUnavailable']);
    D()->execute("USE " . C()->databasename());
    $this->useSuccessful = true;

    $statements = [

      "CREATE TABLE event ("
        . "id INT PRIMARY KEY AUTO_INCREMENT UNIQUE NOT NULL,"
        . "started BOOLEAN NOT NULL,"
        . "finished BOOLEAN NOT NULL,"
        . "format NVARCHAR(256) NOT NULL,"
        . "cost INT NOT NULL"
        . ") Engine = InnoDB DEFAULT CHARSET=UTF8",

      "CREATE TABLE pod ("
        . "id INT PRIMARY KEY AUTO_INCREMENT UNIQUE NOT NULL,"
        . "event_id INT NOT NULL,"
        . "FOREIGN KEY (event_id) REFERENCES event (id) "
          . "ON UPDATE NO ACTION ON DELETE CASCADE"
        . ") Engine = InnoDB DEFAULT CHARSET=UTF8",

      "CREATE TABLE player_pod ("
        . "id INT PRIMARY KEY AUTO_INCREMENT UNIQUE NOT NULL,"
        . "pod_id INT NOT NULL,"
        . "player_id BIGINT NOT NULL,"
        . "seat INT NOT NULL,"
        . "CONSTRAINT UNIQUE (pod_id, player_id),"
        . "CONSTRAINT UNIQUE (pod_id, seat),"
        . "FOREIGN KEY (pod_id) REFERENCES pod (id) "
          . "ON UPDATE NO ACTION ON DELETE CASCADE"
        . ") Engine = InnoDB DEFAULT CHARSET=UTF8",

      "CREATE TABLE player_event ("
        . "id INT PRIMARY KEY AUTO_INCREMENT UNIQUE NOT NULL,"
        . "event_id INT NOT NULL,"
        . "player_id BIGINT NOT NULL,"
        . "name NVARCHAR(1024) NOT NULL,"
        . "url NVARCHAR(1024) NOT NULL,"
        . "dropped BOOLEAN NOT NULL,"
        . "CONSTRAINT UNIQUE (event_id, player_id),"
        . "FOREIGN KEY (event_id) REFERENCES event (id) "
          . "ON UPDATE NO ACTION ON DELETE CASCADE"
        . ") Engine = InnoDB DEFAULT CHARSET=UTF8",

      "CREATE TABLE round ("
        . "id INT PRIMARY KEY AUTO_INCREMENT UNIQUE NOT NULL,"
        . "pod_id INT NOT NULL,"
        . "round_number INT NOT NULL,"
        . "start_time DATETIME NOT NULL, "
        . "CONSTRAINT UNIQUE (pod_id, round_number),"
        . "FOREIGN KEY (pod_id) REFERENCES pod (id) "
          . "ON UPDATE NO ACTION ON DELETE CASCADE"
        . ") Engine = InnoDB DEFAULT CHARSET=UTF8",

      "CREATE TABLE `match` ("
        . "id INT PRIMARY KEY AUTO_INCREMENT UNIQUE NOT NULL,"
        . "round_id INT NOT NULL,"
        . "FOREIGN KEY (round_id) REFERENCES round (id) "
          . "ON UPDATE NO ACTION ON DELETE CASCADE"
        . ") Engine = InnoDB DEFAULT CHARSET=UTF8",

      "CREATE TABLE player_match ("
        . "id INT PRIMARY KEY AUTO_INCREMENT UNIQUE NOT NULL,"
        . "player_id BIGINT NOT NULL,"
        . "match_id INT NOT NULL,"
        . "wins INT,"
        . "CONSTRAINT UNIQUE (player_id, match_id),"
        . "FOREIGN KEY (match_id) REFERENCES `match` (id) "
          . "ON UPDATE NO ACTION ON DELETE CASCADE"
        . ") Engine = InnoDB DEFAULT CHARSET=UTF8",

      "CREATE TABLE admin ("
        . "id INT PRIMARY KEY AUTO_INCREMENT UNIQUE NOT NULL,"
        . "player_id BIGINT NOT NULL UNIQUE"
        . ") Engine = InnoDB DEFAULT CHARSET=UTF8",

      "INSERT INTO admin (player_id) VALUES (" . Q(S()->id()) . ")"

    ];
    foreach ($statements as $statement) {
      try {
        echo "$statement<p>";
        var_dump(D()->execute($statement));
      } catch (DatabaseException $e) {
        if (mb_substr($statement, 0, 5) !== 'GRANT') {
          throw $e;
        }
      }
      echo "<hr>";
    }
  }

  public function databaseUnavailable() {
    if (!$this->useSuccessful) {
      echo '<p>Issue the following commands in MySQL, then reload this '
        . 'page:</p>'
        . '<pre>'
        . 'CREATE DATABASE tournament;'
        . 'GRANT ALL ON ' . C()->databasename() . '' . ".* TO "
        . C()->databaseusername() . "@" . C()->databasehost()
        . " IDENTIFIED BY " . Q(C()->databasepassword()) . ';'
        . '</pre>';
      }
  }
}

echo (new Setup())->main();
