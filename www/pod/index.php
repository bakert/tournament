<?php

require_once(__DIR__ . '/../tournament-www.php');

class PodPage extends Page {
  public function main() {
    if (!isset($_GET['pod_id'])) {
      return R('/');
    }
    return T()->pod(new Pod($_GET['pod_id']));
  }
}

echo (new PodPage())->main();
