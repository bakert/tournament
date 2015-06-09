<?php

class Transaction {
  public function __construct() {
    $this->conn = start_transaction(
      C()->databasename(),
      C()->databasehost(),
      C()->databaseusername(),
      C()->databasepassword()
    );
  }

  public function execute($sql) {
    $result = transact_db($sql, $this->conn);
    if (C()->debug()) {
      echo "<hr>";
      echo $sql;
      var_dump($result);
      echo "<hr>";
    }
    if ($result === false) {
      $this->rollback();
      $msg = "Database failure with '$sql'. Transaction rolled back.";
      throw new DatabaseException($msg);
    }
    return $result;
  }

  public function id() {
    return $this->value('SELECT LAST_INSERT_ID()');
  }

  public function value($sql, $default = 'ERROR') {
    $rs = $this->execute($sql);
    if (!isset($rs[0][0])) {
      if ($default === 'ERROR') {
        $this->rollback();
        $msg = "Asked for value but none present with '$sql'. Transaction rolled back.";
        throw new DatabaseException($msg);
      } else {
        return $default;
      }
    }
    return $rs[0][0];
  }

  public function rollback() {
    return rollback($this->conn);
  }

  public function commit() {
    return commit($this->conn);
  }
}
