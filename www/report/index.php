<?php

require_once(__DIR__ . '/../tournament-www.php');

class Report extends Page {
  public function main() {
    foreach (['match_id', 'wins', 'opponent_wins'] as $key) {
      if (!isset($_GET[$key])) {
        return R('/');
      }
    }
    $match = new Match($_GET['match_id']);
    if (!$match->awaitingResult()) {
      return R('/pod/', ['pod_id' => $match->podId()]);
    }
    if (!$match->isPlaying(S()->id()) && !A()->isAdmin()) {
      return R('/pod/', ['pod_id' => $match->podId()]);
    }
    $match->report($_GET['wins'], $_GET['opponent_wins']);
    $pod = new Pod($match->podId());
    if ($pod->awaitingPairings() && count($pod->rounds) <= 2) {
      $pod->pair();
    }
    return R('/pod/', ['pod_id' => $match->podId()]);
  }
}

echo (new Report())->main();
