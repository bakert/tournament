<?php

require_once(__DIR__ . '/../tournament-www.php');

class SignIn extends Page {
  public function main() {
    try {
      $helper = new Facebook\FacebookRedirectLoginHelper(A()->signInUrl());
      $session = $helper->getSessionFromRedirect();
      if ($session) {
        (new Session($session->getAccessToken()));
      }
    } catch (Facebook\FacebookAuthorizationException $e) {
      // This seems to be ok but log a warning to see if it happens.
      L()->warning($e);
    }
    R('/');
  }
}

echo (new SignIn())->main();
