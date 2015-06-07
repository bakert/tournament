<?php

class Auth {
  public function isAdmin() {
    if (!isset($this->isAdmin)) {
      if (!S()->isSignedIn()) {
        return false;
      }
      $sql = 'SELECT id FROM admin WHERE player_id = ' . Q(S()->id());
      $rs = D()->execute($sql);
      $this->isAdmin = count($rs) > 0;
    }
    return $this->isAdmin;

  }

  public function signInUrl() {
    return U('/signin/', true /* absolute */);
  }

  public function externalSignInUrl() {
    $helper = new Facebook\FacebookRedirectLoginHelper($this->signInUrl());
    return $helper->getLoginUrl();
  }
}
