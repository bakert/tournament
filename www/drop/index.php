<?php

require_once(__DIR__ . '/../tournament-www.php');

class Drop extends Page {
  public function main() {
    //BAKERT use the supplied playerId but only if you are that person or you are an admin
    (new Events())->drop(S()->id());
    return R('/');
  }
}

echo (new Drop())->main();