<?php

class Session {
  public function __construct($accessToken = null) {
    if ($accessToken !== null) {
      $this->set('accessToken', $accessToken);
      $this->setCookie('accessToken', $accessToken);
    }
  }

  public function id() {
    if (!$this->get('id')) {
      $this->set('id', $this->fetchId());
    }
    return $this->get('id');
  }

  public function isSignedIn() {
    return $this->id() !== null;
  }

  public function signOut() {
    $this->set('accessToken', null);
    $this->setCookie('accessToken', null);
    $this->set('id', null);
  }

  public function accessToken() {
    return $this->get('accessToken');
  }

  private function get($key) {
    $actualKey = C()->sessionprefix() . $key;
    if (!isset($_SESSION[$actualKey])) {
      return null;
    }
    return $_SESSION[$actualKey];
  }

  private function set($key, $value) {
    $_SESSION[C()->sessionprefix() . $key] = $value;
  }

  private function getCookie($key) {
    $idx = C()->sessionprefix() . $key;
    return isset($_COOKIE[$idx]) ? $_COOKIE[$idx] : null;
  }

  private function setCookie($key, $value) {
    $sixty_days = time() + 60 * 60 * 24 * 60;
    setcookie(C()->sessionprefix() . $key, $value, $sixty_days, '/');
  }

  private function fetchId() {
    if ($this->get('accessToken') === null) {
      if ($this->getCookie('accessToken') !== null) {
        $this->set('accessToken', $this->getCookie('accessToken'));
      } else {
        return null;
      }
    }
    try {
      $me = F()->get('/me')->getGraphUser();
    } catch (Facebook\FacebookAuthorizationException $e) {
      return null;
    }
    return $me->getId();
  }
}
