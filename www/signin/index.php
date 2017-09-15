<?php

require_once(__DIR__ . '/../tournament-www.php');

class SignIn extends Page {
  public function main() {
    try {
      $helper = F()->getRedirectLoginHelper();
      $accessToken = $helper->getAccessToken();
      (new Session($accessToken));
    } catch (Facebook\FacebookAuthorizationException $e) {
      // This seems to be ok but log a warning to see if it happens.
      L()->warning($e);
    }
    R('/');
  }
}

echo (new SignIn())->main();
