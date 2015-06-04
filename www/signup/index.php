<?php

require_once(__DIR__ . '/../tournament-www.php');

class SignUp extends Page {
  function main() {
    $events = new Events();
    $events->signUp($_GET['event_id'], S()->id());
    R('/');
  }
}

echo (new SignUp())->main();
