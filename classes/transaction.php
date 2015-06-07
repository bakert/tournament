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
    return transact_db($sql, $this->conn);
  }

  public function rollback() {
    return rollback($this->conn);
  }

  public function commit() {
    return commit($this->conn);
  }
}
