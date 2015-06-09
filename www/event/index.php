<?php

require_once(__DIR__ . '/../tournament-www.php');

class EventPage extends Page {
  public function main() {
    if (!isset($_GET['event_id'])) {
      return R('/');
    }
    $eventId = $_GET['event_id'];
    try {
      $event = new Event($eventId);
    } catch (IllegalStateException $e) {
      return R('/');
    }
    $args = (array)$event;
    if (A()->isAdmin()) {
      $args['isAdmin'] = true;
      if (!$event->started()) {
        $args['startUrl'] = U('/start/', false, ['event_id' => $eventId]);
      } else {
        foreach ($args['pods'] as &$pod) {
          $podId = $pod['players'][0]['podId'];
          $pod['podId'] = $podId;
          $pod['podUrl'] = U('/pod/', false, ['pod_id' => $podId]);
          if ($pod['awaitingPairings']) {
            $pod['pairUrl'] = U('/pair/', false, ['pod_id' => $podId]);
          }
          if (A()->isAdmin()) {
            foreach ($pod['players'] as &$player) {
              if (!$player['dropped']) {
                $player['dropUrl'] = U('/drop/', false, ['player_id' => $player['playerId']]);
              }
            }
          }
        }
      }
    }
    foreach ($event->players() as $player) {
      if (!$player['dropped'] && $player['playerId'] === S()->id()) {
        $args['dropUrl'] = U('/drop/', false, ['player_id' => $player['playerId']]);
      }
    }
    return T()->event($args);
  }
}

echo (new EventPage())->main();
