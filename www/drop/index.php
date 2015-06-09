<?php

require_once(__DIR__ . '/../tournament-www.php');

class Drop extends Page {
  public function main() {
    if (!isset($_GET['player_id'])) {
      return R('/');
    }
    $player_id = $_GET['player_id'];
    if (S()->id() === $playerId || A()->isAdmin()) {
      (new Events())->drop($playerId);
    }
    return R('/');
  }
}

echo (new Drop())->main();