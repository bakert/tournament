<?php

require_once(__DIR__ . '/../tournament-www.php');

class SignIn extends Page {
  public function main() {
    try {
      $helper = new Facebook\FacebookRedirectLoginHelper(A()->signInUrl());
      $session = $helper->getSessionFromRedirect();
      if ($session) {
        $accessToken = $session->getAccessToken();
        $url = 'https://graph.facebook.com/oauth/access_token?'
          . 'grant_type=fb_exchange_token'
          . '&client_id=' . rawurlencode(C()->fbappid())
          . '&client_secret=' . rawurlencode(C()->fbappsecret())
          . '&fb_exchange_token=' . rawurlencode($accessToken);
        $s = $this->getSslPage($url);
        parse_str($s, $values);
        if (isset($values['access_token'])) {
          $accessToken = $values['access_token'];
        }
        (new Session($accessToken));
      }
    } catch (Facebook\FacebookAuthorizationException $e) {
      // This seems to be ok but log a warning to see if it happens.
      L()->warning($e);
    }
    R('/');
  }

  function getSslPage($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
  }
}


echo (new SignIn())->main();
