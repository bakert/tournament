<?php

require_once('tournament-www.php');

class Index extends Page {

  function main() {
    if (!S()->isSignedIn()) {
      return $this->signIn();
    }
    return $this->status();
  }

  function signIn() {
    return T()->signin(['signInUrl' => A()->externalSignInUrl()]);
  }

  function status() {
    $args['isAdmin'] = A()->isAdmin();
    $args['dropUrl'] = U('/drop/');
    $args['createEventUrl'] = U('/newevent/');
    $args['events'] = (new Events())->allEvents(S()->id());
    foreach ($args['events'] as &$event) {
      $event['signUpUrl'] = U('/signup/', false, ['event_id' => $event['id']]);
      $event['cancelUrl'] = U('/cancel/', false, ['event_id' => $event['id']]);
    }
    return T()->status($args);
  }
}

echo (new Index())->main();
