<?php

class Redirect {
  public function redirectTo($path, $queryString = []) {
    header('Location: ' . U($path, true /* absolute */, $queryString));
    die();
  }
}
