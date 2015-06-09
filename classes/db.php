<?php

class Db {
  public function execute($sql) {
    $result = db(
      $sql,
      C()->databasename(),
      C()->databasehost(),
      C()->databaseusername(),
      C()->databasepassword()
    );
    if (C()->debug()) {
      echo "<hr>";
      echo $sql;
      var_dump($result);
      echo "<hr>";
    }
    if ($result === false) {
      throw new DatabaseException("Database failure: '$sql'");
    }
    return $result;
  }

  public function value($sql, $default = 'ERROR') {
    $rs = $this->execute($sql);
    if (!isset($rs[0][0])) {
      if ($default === 'ERROR') {
        $msg = "Asked for value but none present with '$sql'";
        throw new DatabaseException($msg);
      } else {
        return $default;
      }
    }
    return $rs[0][0];
  }

  public function id() {
    return $this->value('SELECT LAST_INSERT_ID()');
  }
}
