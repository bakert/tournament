<?php

require_once(__DIR__ . '/../tournament-www.php');

class EventPage extends Page {
  public function main() {
    if (!isset($_GET['event_id'])) {
      return R('/');
    }
    $args = [];
    $event = new Event($_GET['event_id']);
    $args = (array)$event;
    if (A()->isAdmin()) {
      $args['isAdmin'] = true;
      if (!$event->started()) {
        $args['startUrl'] = U('/start/', false, ['event_id' => $_GET['event_id']]);
      } else {
        foreach ($args['pods'] as &$pod) {
          $podId = $pod['players'][0]['podId'];
          $pod['podId'] = $podId;
          $pod['podUrl'] = U('/pod/', false, ['pod_id' => $podId]);
          $pod['pairUrl'] = U('/pair/', false, ['pod_id' => $podId]);
        }
      }
    }
    foreach ($event->players() as $player) {
      if ($player['playerId'] === S()->id()) {
        $args['dropUrl'] = U('/drop/');
      }
    }
    return T()->event($args);
  }
}

echo (new EventPage())->main();
