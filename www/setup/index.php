<?php

require_once(__DIR__ . '/../tournament-www.php');

class Setup {
  public function main() {
    if (!S()->isSignedIn()) {
      return R('/');
    }

    $statements = [
      "USE " . C()->databasename(),

      "GRANT ALL ON " . C()->databasename() . ".* TO "
        . C()->databaseusername() . "@" . C()->databasehost()
        . " IDENTIFIED BY " . Q(C()->databasepassword()),

      "CREATE TABLE event ("
        . "id INT PRIMARY KEY AUTO_INCREMENT UNIQUE NOT NULL,"
        . "`date` DATETIME NOT NULL,"
        . "format NVARCHAR(256) NOT NULL,"
        . "cost INT NOT NULL"
        . ") Engine = InnoDB DEFAULT CHARSET=UTF8",

      "CREATE TABLE pod ("
        . "id INT PRIMARY KEY AUTO_INCREMENT UNIQUE NOT NULL,"
        . "event_id INT NOT NULL,"
        . "FOREIGN KEY (event_id) REFERENCES event (id)"
          . "ON UPDATE NO ACTION ON DELETE CASCADE"
        . ") Engine = InnoDB DEFAULT CHARSET=UTF8",

      "CREATE TABLE player_pod ("
        . "id INT PRIMARY KEY AUTO_INCREMENT UNIQUE NOT NULL,"
        . "pod_id INT NOT NULL,"
        . "player_id BIGINT NOT NULL,"
        . "FOREIGN KEY (pod_id) REFERENCES pod (id)"
          . "ON UPDATE NO ACTION ON DELETE CASCADE"
        . ") Engine = InnoDB DEFAULT CHARSET=UTF8",

      "CREATE TABLE player_event ("
        . "id INT PRIMARY KEY AUTO_INCREMENT UNIQUE NOT NULL,"
        . "event_id INT NOT NULL,"
        . "player_id BIGINT NOT NULL,"
        . "FOREIGN KEY (event_id) REFERENCES event (id)"
          . "ON UPDATE NO ACTION ON DELETE CASCADE"
        . ") Engine = InnoDB DEFAULT CHARSET=UTF8",

      "CREATE TABLE admin ("
        . "id INT PRIMARY KEY AUTO_INCREMENT UNIQUE NOT NULL,"
        . "player_id BIGINT NOT NULL UNIQUE"
        . ") Engine = InnoDB DEFAULT CHARSET=UTF8",

      "INSERT INTO admin (player_id) VALUES (" . Q(S()->id()) . ")"

    ];
    ob_end_flush();
    foreach ($statements as $statement) {
      D()->write($statement);
    }
    ob_start();
    return R('/admin/');
  }
}

echo (new Setup())->main();
