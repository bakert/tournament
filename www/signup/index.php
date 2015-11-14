<?php

require_once(__DIR__ . '/../tournament-www.php');

class SignUp extends Page {
  function main() {
    $person = (new Facebook())->person(S()->id());
    (new Events())->signUp($_GET['event_id'], S()->id(), $person['name'], $person['link']);
    return R('/');
  }
}

echo (new SignUp())->main();
