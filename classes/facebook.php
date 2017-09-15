<?php

class Facebook {
  private $fb;

  public function __construct() {
    $this->fb = new Facebook\Facebook([
      'app_id' => C()->fbappid(),
      'app_secret' => C()->fbappsecret(),
      'default_access_token' => S()->accessToken(),
      'default_graph_version' => 'v2.10',
    ]);
  }

  public function __call($name, $arguments) {
    return call_user_func_array([$this->fb, $name], $arguments);
  }

  public function person($fbId) {
    $profile = $this->fb->get('/me')->getGraphUser();
    return ['name' => $profile->getName(), 'link' => $profile->getLink()];
  }
}
