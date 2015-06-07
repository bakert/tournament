<?php

require_once(__DIR__ . '/../tournament-www.php');

class Start extends Page {
  public function main() {
    if (!isset($_GET['event_id']) || !A()->isAdmin()) {
      return R('/');
    }
    $event = new Event($_GET['event_id']);
    if ($event->started()) {
      return R('/');
    }
    (new Pods())->createPods($_GET['event_id']);
    R('/');
  }
}

echo (new Start())->main();
