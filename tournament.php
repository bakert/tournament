<?php

date_default_timezone_set('UTC');

require_once(__DIR__ . '/vendor/autoload.php');
spl_autoload_register([new Autoloader(), 'load']);

class Autoloader {
  public function load($name) {
    foreach (['', 'exceptions/', 'www/'] as $dir) {
      $path = __DIR__ . '/classes/' . $dir . mb_strtolower($name) . '.php';
      if (file_exists($path)) {
        require_once($path);
      }
    }
  }
}

function C() {
  return new Config();
}

function D() {
  return new Db();
}

function L() {
  return new Log();
}

function Q($s) {
  if ($s === 'NULL') {
      return 'NULL';
  }
  return "'" . addslashes($s) . "'";
}
