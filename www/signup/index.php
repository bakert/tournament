<?php

require_once(__DIR__ . '/../tournament-www.php');

class SignUp extends Page {
  function main() {
    $events = new Events();
    $person = (new Facebook())->person(S()->id());
    $events->signUp($_GET['event_id'], S()->id(), $person['name'], $person['link']);
    R('/');
  }
}

echo (new SignUp())->main();
