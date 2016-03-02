<?php

require_once(__DIR__ . '/../tournament.php');

function generateScenario() {
  $players = [];
  for ($i = 0; $i < 10; $i++) {
    $players[] = [
      'dropped' => false,
      'playerId' => chr(65 + $i),
      'opponents' => [],
      'points' => 0
    ];
  }
  $afterRound1 = playRound($players);
  print_r($afterRound1);
  $afterRound2 = playRound($afterRound1);
}

function playRound($players, $lastRound = false) {
  echo "=== ROUND ===\n";
  $pairings = (new Pairings($players))->pair();
  $nextRoundPLayers = [];
  foreach ($pairings as $idx => $pairing) {
    $pairing[0]['opponents'][] = $pairing[1]['playerId'];
    $pairing[1]['opponents'][] = $pairing[0]['playerId'];

    echo $pairing[0]['playerId'] . " (" . $pairing[0]['points'] . ") is playing " . $pairing[1]['playerId'] . " (" . $pairing[1]['points'] . ")\n";

    if ($idx < 4 && $pairing[0]['playerId'] !== 0) {
      $pairing[0]['points'] += 3;
      echo $pairing[0]['playerId'] . " wins\n";
    } elseif ($idx == 4 && $pairing[0]['playerId'] !== 0 && $pairing[1]['playerId'] !== 0) {
      $pairing[0]['points'] += 1;
      $pairing[1]['points'] += 1;
      echo $pairing[0]['playerId'] . " and " . $pairing[1]['playerId'] . " Draw\n";
    }
    if ($pairing[0]['playerId'] !== 0)
      $nextRoundPlayers[] = $pairing[0];
    if ($pairing[1]['playerId'] !== 0)
      $nextRoundPlayers[] = $pairing[1];
  }
  return $nextRoundPlayers;
}

generateScenario();
