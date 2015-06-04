<?php

use Monolog\Logger;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\ErrorLogHandler;

class Log {
  public function __construct() {
    $this->log = new Logger('tournament');
    $this->log->pushHandler(new ErrorLogHandler());
  }

  public function debug($s) {
    $this->log->debug($s);
  }

  public function info($s) {
    $this->log->addInfo($s);
  }

  public function notice($s) {
    $this->log->addNotice($s);
  }

  public function warning($s) {
    $this->log->addWarning($s);
  }

  public function error($s) {
    $this->log->addError($s);
  }

  public function critical($s) {
    $this->log->addCritical($s);
  }

  public function alert($s) {
    $this->log->addAlert($s);
  }

  public function emergency($s) {
    $this->log->addEmergency($s);
  }
}
