<?php

class Facebook {
  public function person($fbId) {
    $profile = (new Facebook\FacebookRequest(
      new Facebook\FacebookSession(S()->accessToken()), 'GET', '/me'
    ))->execute()->getGraphObject(Facebook\GraphUser::className());
    return ['name' => $profile->getName(), 'link' => $profile->getLink()];
  }
}
