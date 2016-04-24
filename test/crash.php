<?php

require_once(__DIR__ . '/../tournament.php');

function crash() {
  $players = [];
  for ($i = 0; $i < 10; $i++) {
    $points = 0;
    if ($i <= 3) {
      $points = 3;
    } elseif ($i <= 5) {
      $points = 1;
    }
    $players[] = ['dropped' => false, 'playerId' => chr(65 + $i),
      'opponents' => [chr(65 + 9 - $i)], 'points' => $points];
  }
  (new Pairings($players))->pair();
}

crash();
