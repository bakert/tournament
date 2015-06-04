<?php

class Session {
  public function __construct($accessToken = null) {
    if ($accessToken !== null) {
      $this->set('accessToken', $accessToken);
    }
  }

  public function id() {
    if (!$this->get('id')) {
      $this->set('id', $this->fetchId());
    }
    return $this->get('id');
  }

  public function isSignedIn() {
    return $this->get('accessToken') !== null;
  }

  public function signOut() {
    $this->set('accessToken', null);
    $this->set('id', null);
  }

  private function get($key) {
    return $_SESSION[C()->sessionprefix() . $key];
  }

  private function set($key, $value) {
    $_SESSION[C()->sessionprefix() . $key] = $value;
  }

  private function fetchId() {
    $me = (new Facebook\FacebookRequest(
      new Facebook\FacebookSession($this->get('accessToken')), 'GET', '/me'
    ))->execute()->getGraphObject(Facebook\GraphUser::className());
    return $me->getId();
  }
}
