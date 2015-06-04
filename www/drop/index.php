<?php

require_once(__DIR__ . '/../tournament-www.php');

class Drop extends Page {
  public function main() {
    (new Events())->drop(S()->id());
    return R('/');
  }
}

echo (new Drop())->main();