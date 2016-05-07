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
    foreach ($args['players'] as $player) {
      if (!$player['dropped'] && $player['playerId'] === S()->id()) {
        $args['topDropUrl'] = U('/drop/', false, ['player_id' => $player['playerId']]);
      }
    }
    foreach ($args['pods'] as &$pod) {
      $podId = $pod['players'][0]['podId'];
      $pod['podId'] = $podId;
      if ($podId !== '-') {
        $pod['podUrl'] = U('/pod/', false, ['pod_id' => $podId]);
      }
    }
    if (A()->isAdmin()) {
      $args['isAdmin'] = true;
      foreach ($args['pods'] as &$pod) {
        if (isset($pod['awaitingPairings']) && $pod['awaitingPairings']) {
          $pod['pairUrl'] = U('/pair/', false, ['pod_id' => $podId]);
        }
        foreach ($pod['players'] as &$player) {
          if (!$player['dropped']) {
            $player['dropUrl'] = U('/drop/', false, ['player_id' => $player['playerId']]);
          }
        }
      }

      if (!$event->started() && count($args['players']) > 1) {
        $args['startUrl'] = U('/start/', false, ['event_id' => $eventId]);
      } elseif (!$event->started()) {
        $args['cancelUrl'] = U('/cancel/', false, ['event_id' => $eventId]);
      } else {
        if ($event->canBeUnstarted()) {
          $args['unstartUrl'] = U('/unstart/', false, ['event_id' => $eventId]);
        }
        $args['endUrl'] = U('/end/', false, ['event_id' => $eventId]);
      }
    }
    return T()->event($args);
  }
}

echo (new EventPage())->main();
