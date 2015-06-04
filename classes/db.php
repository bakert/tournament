<?php

require_once(__DIR__ . '/../shared/db.php');

class Db {

  public function read($sql) {
    $rs = $this->execute($sql);
    if ($rs === false) {
        L()->error("Problem in Db with $sql");
        return null;
    }
    return $rs;
  }

  public function write($sql) {
    return $this->execute($sql);
  }

  private function execute($sql) {
    return db(
      $sql,
      C()->databasename(),
      C()->databasehost(),
      C()->databaseusername(),
      C()->databasepassword()
    );
  }
}
