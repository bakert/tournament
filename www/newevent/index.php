<?php

require_once(__DIR__ . '/../tournament-www.php');

class NewEvent extends Page {
  public function main() {
    if (!A()->isAdmin()) {
      return R('/');
    }
    return T()->newevent([
      'formAction' => U('/create/')
    ]);
  }
}

echo (new NewEvent())->main();
