<?php

require_once(__DIR__ . '/../tournament-www.php');

class Test extends Page {
	public function main() {
    if (!A()->isAdmin()) {
      return R('/');
    }
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
      return T()->test([]);
    } else {
      $events = new Events();
      $events->create('Test Event', '0');
      $eventId = D()->value("SELECT MAX(id) FROM event");
      $players = D()->execute("SELECT player_id, name, url FROM player_event");
      $players = array_slice($players, 0, 30);
      foreach ($players AS $player) {
        $events->signUp($eventId, $player['player_id'], $player['name'], $player['url']);  
      }
      return R('/');
    }
  }
}

echo (new Test())->main();
