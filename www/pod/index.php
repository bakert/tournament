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
    $args['minsLeft'] = $pod->minsLeft();
    # Gross way to get Mustache to display a falsy value.
    if ($args['minsLeft'] === 0) {
      $args['minsLeft'] = "0 ";
    }
    if (A()->isAdmin() && $pod->awaitingPairings()) {
      $args['pairUrl'] = U('/pair/', false, ['pod_id' => $podId]);
    }
    $results = new Results();
    foreach ($args['rounds'] as &$round) {
      foreach ($round['matches'] as &$match) {
        $needsReport = $match['wins'] === null;
        $canReport = A()->isAdmin()
            || $match['playerId'] === S()->id()
            || $match['opponentId'] === S()->id();
        if ($needsReport && $canReport) {
          $match['potentialResults'] = $results->potentialResults($match['matchId']);
        }
      }
    }
    $args['standings'] = (new Standings($podId))->getStandings();
    return T()->pod($args);
  }
}

echo (new PodPage())->main();
