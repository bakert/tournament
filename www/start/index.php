<?php

require_once(__DIR__ . '/../tournament-www.php');

class Start extends Page {
  public function main() {
    if (!isset($_GET['event_id']) || !A()->isAdmin()) {
      return R('/');
    }
    $eventId = $_GET['event_id'];
    try {
      $event = new Event($eventId);
    } catch (IllegalStateException $e) {
      return R('/');
    }
    if ($event->started()) {
      return R('/');
    }
    (new Pods())->createPods($eventId);
    R('/');
  }
}

echo (new Start())->main();
