<?php

class Redirect {
  public function redirectTo($path) {
    header('Location: ' . U($path, true /* absolute */));
    die();
  }
}
