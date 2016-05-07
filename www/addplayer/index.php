<?php

require_once(__DIR__ . '/../tournament-www.php');

class AddPlayer extends Page {
  public function main() {
    if (!A()->isAdmin()) {
      return R('/');
    }
    if (isset($_POST['pod_info'])) {
      $this->addPlayerToPod($_POST['player_info'], $_POST['pod_info']);
      return R('/');
      // Add to player_event for the event
    } else {
      $players = [];
      foreach ((new Players())->allPlayers() as $player) {
        $players[] = [
          'info' => serialize([$player['id'], $player['name'], $player['url']]),
          'display' => $player['name']
        ];
      }
      $pods = [];
      foreach ($this->pods() AS $pod) {
        $pods[] = [
          'info' => serialize([$pod['event_id'], $pod['pod_id']]),
          'display' => $pod['display'],
        ];
      }
      return T()->addplayer([
        'players' => $players,
        'pods' => $pods,
      ]);
    }
  }

  private function addPlayerToPod($playerInfo, $podInfo) {
    list($playerId, $name, $url) = unserialize($playerInfo);
    list($eventId, $podId) = unserialize($podInfo);
    (new Events())->signUp($eventId, $playerId, $name, $url);
    $sql = 'SELECT MAX(seat) + 1 AS seat '
      . 'FROM player_pod '
      . 'WHERE pod_id = ' . Q($podId);
    $seat = D()->value($sql);
    $sql = 'INSERT INTO player_pod (pod_id, player_id, seat) VALUES ('
      . Q($podId) . ', ' . Q($playerId) . ', ' . Q($seat) . ')';
    return D()->execute($sql);
  }

  private function pods() {
    $events = (new Events())->currentEvents();
    $pods = [];
    foreach ($events as $event) {
      $sql = 'SELECT id '
        . 'FROM pod '
        . 'WHERE event_id = ' . Q($event['id'])
        . ' ORDER BY id';
      $rs = D()->execute($sql);
      foreach ($rs as $pod) {
        $pods[] = [
          'event_id' => $event['id'],
          'pod_id' => $pod['id'],
          'display' => $event['format'] . ' – Pod ' . $pod['id']
        ];
      }
    }
    return $pods;
  }
}

echo (new AddPlayer())->main();
