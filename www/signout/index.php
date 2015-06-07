<?php

require_once(__DIR__ . '/../tournament-www.php');

class SignOut extends Page {
  public function main() {
    (new Session())->signOut();
    (new Redirect())->redirectTo('/');
  }
}

echo (new SignOut())->main();
