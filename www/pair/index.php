<?php

require_once(__DIR__ . '/../tournament-www.php');

class Pair extends Page {
  public function main() {
    if (!isset($_GET['pod_id']) || !A()->isAdmin()) {
      return R('/');
    }
    $podId = $_GET['pod_id'];
    $pod = new Pod($podId);
    if (!$pod->awaitingPairings()) {
      return R('/pod/', ['pod_id' => $podId]);
    }
    $pod->pair();
    return R('/pod/', ['pod_id' => $podId]);
  }
}

echo (new Pair())->main();
