<?php

require_once(__DIR__ . '/../tournament-www.php');

class Cancel extends Page {
  public function main() {
    if (!A()->isAdmin()) {
      return R('/');
    }
    (new Events())->cancel($_GET['event_id']);
    return R('/');
  }
}

echo (new Cancel())->main();
