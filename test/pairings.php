<?php

require_once(__DIR__ . '/../tournament.php');

function generateScenario() {
  $num_players = mt_rand(6, 11);
  $players = [];
  for ($i = 0; $i < $num_players; $i++) {
    $players[] = [
      'dropped' => false,
      'playerId' => chr(65 + $i),
      'opponents' => [],
      'points' => 0
    ];
  }
  $afterRound1 = playRound($players);
  $afterRound2 = playRound($afterRound1);
  $afterRound3 = playRound($afterRound2, /* lastRound = */   true);
}

function playRound($players, $lastRound = false) {
  echo "=== ROUND ===\n";
  $pairings = (new Pairings($players))->pair();
  $nextRoundPlayers = [];
  foreach ($pairings as $pairing) {
    $pairing[0]['opponents'][] = $pairing[1]['playerId'];
    $pairing[1]['opponents'][] = $pairing[0]['playerId'];

    echo $pairing[0]['playerId'] . " (" . $pairing[0]['points'] . ") is playing " . $pairing[1]['playerId'] . " (" . $pairing[1]['points'] . ")\n";

    // Random result of match
    $result = mt_rand(0, 5);
    if ($result === 0 && $pairing[0]['playerId'] !== 0 && $pairing[1]['playerId'] !== 0) {
      $pairing[0]['points'] += 1;
      $pairing[1]['points'] += 1;
      echo $pairing[0]['playerId'] . " and " . $pairing[1]['playerId'] . " Draw\n";
    } elseif ($result <= 3 || $pairing[0]['playerId'] !== 0) {
      $pairing[0]['points'] += 3;
      echo $pairing[0]['playerId'] . " wins\n";
    } else {
      $pairing[1]['points'] += 3;
      echo $pairing[1]['playerId'] . " wins\n";
    }
    if ($lastRound) {
      // you can't drop after the last round.
      continue;
    }
    // Simulate drops
    if (mt_rand(0, 5) !== 0 && $pairing[0]['playerId'] !== 0) {
      $nextRoundPlayers[] = $pairing[0];
    } else if ($pairing[0]['playerId'] !== 0) {
      echo $pairing[0]['playerId'] . " drops\n";
    }
    if (mt_rand(0, 5) !== 0 && $pairing[1]['playerId'] !== 0) {
      $nextRoundPlayers[] = $pairing[1];
    } else if ($pairing[1]['playerId'] !== 0) {
      echo $pairing[1]['playerId'] . " drops\n";
    }
  }
  return $nextRoundPlayers;
}

generateScenario();
