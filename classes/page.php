<?php

class Page {
  public function __construct() {
    ob_start();
  }

  public function __destruct() {
    ob_end_flush();
  }
}
