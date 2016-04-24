<?php

# Pairings can take a long time.
ini_set('max_execution_time', 0);
date_default_timezone_set('UTC');

require_once(__DIR__ . '/vendor/autoload.php');
spl_autoload_register([new Autoloader(), 'load']);

require_once(__DIR__ . '/shared/db.php');
require_once(__DIR__ . '/shared/masort.php');

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
  return Singletons::C();
}

function D() {
  return Singletons::D();
}

function L() {
  return Singletons::L();
}

function Q($s) {
  if ($s === 'NULL') {
      return 'NULL';
  }
  return "'" . addslashes($s) . "'";
}
