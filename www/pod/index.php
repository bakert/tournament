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
    return T()->pod($args);
  }
}

echo (new PodPage())->main();
