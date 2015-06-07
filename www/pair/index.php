<?php

require_once(__DIR__ . '/../tournament-www.php');

class Pair extends Page {
  public function main() {
    if (!isset($_GET['pod_id']) || !A()->isAdmin()) {
      return R('/');
    }
    $pod = new Pod($_GET['pod_id']);
    if (!$pod->awaitingPairings()) {
      return R('/');
    }
    $pod->pair();
    return R('/');
  }
}

echo (new Pair())->main();
