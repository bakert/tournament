<?php

class Event {

  public function __construct($eventId) {
    $this->eventId = $eventId;

    $sql = 'SELECT format, cost, started '
      . 'FROM event '
      . 'WHERE id = ' . Q($eventId);
    $event = current(D()->execute($sql));

    if (!$event) {
      throw new IllegalStateException("No event with id '$eventId'");
    }

    $this->format = $event['format'];
    $this->cost = $event['cost'];
    $this->started = $event['started'];

    if ($this->started) {
      $sql = 'SELECT pe.player_id, pe.name, pe.url, pp.pod_id, pp.seat '
        . 'FROM player_event AS pe '
        . 'INNER JOIN pod AS p ON p.event_id = pe.event_id '
        . 'INNER JOIN player_pod AS pp ON pp.pod_id = p.id '
          . 'AND pp.player_id = pe.player_id '
        . 'WHERE pe.event_id = ' . Q($eventId)
        . ' ORDER BY p.id, pp.seat';
    } else {
      $sql = "SELECT player_id, '-' AS pod_id, '-' AS seat, name, url "
        . 'FROM player_event '
        . 'WHERE event_id = ' . Q($eventId);
    }
    $players = D()->execute($sql);

    $this->players = [];
    $this->pods = [];
    $pod = [];
    $podId = null;
    foreach ($players as $player) {
      if ($player['pod_id'] !== $podId) {
        if ($pod) {
          $this->pods[] = ['players' => $pod];
        }
        $pod = [];
        $podId = $player['pod_id'];
      }
      $player = [
        'playerId' => $player['player_id'],
        'podId' => $player['pod_id'],
        'seat' => $player['seat'],
        'name' => $player['name'],
        'url' => $player['url']
      ];
      $pod[] = $player;
      $this->players[] = $player;
    }
    if ($pod) {
      $this->pods[] = ['players' => $pod];
    }
  }

  public function format() {
    return $this->format;
  }

  public function cost() {
    return $this->cost;
  }

  public function started() {
    return $this->started;
  }

  public function players() {
    return $this->players;
  }

  public function pods() {
    return $this->pods;
  }
}
