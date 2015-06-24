<?php

require_once(__DIR__ . '/../tournament-www.php');

class SignOut extends Page {
  public function main() {
    S()->signOut();
    return R('/');
  }
}

echo (new SignOut())->main();
