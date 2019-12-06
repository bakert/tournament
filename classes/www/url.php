<?php

class Url {
  public function u($path = '/', $absolute = false, $querystring = []) {
    $url = '';
    if ($absolute) {
      $url = C()->protocol('https') . '://' . C()->hostname();
      if (C()->port('80') !== '80') {
        $url .= ':' . C()->port();
      }
    }
    $url .= C()->basepath() . $path;
    if ($querystring) {
      $url .= '?' . http_build_query($querystring);
    }
    return $url;
  }
}
