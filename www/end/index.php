<?php

require_once(__DIR__ . '/../tournament-www.php');

class EndPage extends Page {
  public function main() {
    if (!isset($_GET['event_id'])) {
      return R('/');
    }
    $event = new Event($_GET['event_id']);
    $event->end();
    return R('/');
  }
}

echo (new EndPage())->main();
