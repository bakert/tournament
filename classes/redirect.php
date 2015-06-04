<?php

class Redirect {
  public function redirect($path) {
    header('Location: ' . U($path, true /* absolute */));
    die();
  }
}
