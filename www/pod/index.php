<?php

require_once(__DIR__ . '/../tournament-www.php');

class PodPage extends Page {
  public function main() {
    if (!isset($_GET['pod_id'])) {
      return R('/');
    }
    $podId = $_GET['pod_id'];
    $pod = new Pod($podId);
    $args = (array)$pod;
    if (A()->isAdmin() && $pod->awaitingPairings()) {
      $args['pairUrl'] = U('/pair/', false, ['pod_id' => $podId]);
    }
    foreach ($args['rounds'] as &$round) {
      foreach ($round['matches'] as &$match) {
        $needsReport = $match['wins'] === null;
        $canReport = A()->isAdmin()
            || $match['playerId'] === S()->id()
            || $match['opponentId'] === S()->id();
        if ($needsReport && $canReport) {
          $queryString = ['match_id' => $match['matchId']];
          $match['reportUrl'] = U('/report/', false, $queryString);
        }
      }
    }
    return T()->pod($args);
  }
}

echo (new PodPage())->main();
