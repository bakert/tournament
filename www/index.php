<?php

require_once('tournament-www.php');

class Index extends Page {

  public function main() {
    if (!S()->isSignedIn()) {
      return $this->signIn();
    }
    return $this->status();
  }

  private function signIn() {
    return T()->signin(['signInUrl' => A()->externalSignInUrl()]);
  }

  private function status() {
    if (A()->isAdmin()) {
      $args['isAdmin'] = true;
      $args['dropUrl'] = U('/drop/', false, ['player_id' => S()->id()]);
      $args['createEventUrl'] = U('/newevent/');
    }
    $args['events'] = (new Events())->currentEvents(S()->id());
    foreach ($args['events'] as &$event) {
      $event['eventUrl'] = U('/event/', false, ['event_id' => $event['id']]);
      $event['signUpUrl'] = U('/signup/', false, ['event_id' => $event['id']]);
      $event['startUrl'] = U('/start/', false, ['event_id' => $event['id']]);
      $event['cancelUrl'] = U('/cancel/', false, ['event_id' => $event['id']]);
    }
    return T()->status($args);
  }
}

echo (new Index())->main();
