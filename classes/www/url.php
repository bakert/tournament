<?php

class Url {
  public function u($path = '/', $absolute = false, $querystring = []) {
    $url = '';
    if ($absolute) {
      $url = C()->protocol('http') . '://' . C()->hostname();
    }
    $url .= C()->basepath() . $path;
    if ($querystring) {
      $url .= '?' . http_build_query($querystring);
    }
    return $url;
  }
}
