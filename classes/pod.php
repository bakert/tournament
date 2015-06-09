<?php

class Pod {
  public function __construct($podId) {
    $this->podId = $podId;

    $sql = 'SELECT m.id AS match_id, '
      . 'pp.player_id, pm2.player_id AS opponent_id, '
      . 'pm1.wins, pm2.wins AS opponent_wins, '
      . 'r.id AS round_id, r.round_number '
      . 'FROM player_pod AS pp '
      . 'LEFT JOIN round AS r ON r.pod_id = pp.pod_id '
      . 'LEFT JOIN `match` AS m ON m.round_id = r.id '
      . 'LEFT JOIN player_match AS pm1 ON pm1.match_id = m.id '
      . 'LEFT JOIN player_match AS pm2 ON pm2.match_id = m.id AND pm2.player_id != pm1.player_id '
      . 'WHERE pp.pod_id = ' . Q($podId)
      . ' AND pp.player_id != 0 ' // Exclude the bye as antagonist from results.
      . 'ORDER BY m.round_id';
    $matches = D()->execute($sql);
    list($players, $rounds) = [[], []];
    $this->matches = $matches;
    foreach ($matches as $match) {
      $playerId = $match['player_id'];
      if (!isset($players[$playerId])) {
        $players[$playerId] = ['playerId' => $playerId, 'opponents' => [], 'points' => 0];
      }
      if ($match['match_id'] !== null) {
        $players[$playerId]['opponents'][] = $match['opponent_id'];
        $players[$playerId]['points'] += $this->points($match['wins'], $match['opponent_wins']);
      }
      $roundId = $match['round_id'];
      if ($roundId !== null) {
        if (!isset($rounds[$roundId])) {
          $rounds[$roundId] = [
            'roundId' => $roundId,
            'roundNumber' => $match['round_number'],
            'matches' => []
          ];
        }
        // Don't add a match twice.
        if (!isset($rounds[$roundId]['matches'][$match['opponent_id']])) {
          $rounds[$roundId]['matches'][$match['player_id']] = [
            'playerId' => $match['player_id'],
            'opponentId' => $match['opponent_id'],
            'wins' => $match['wins'],
            'opponentWins' => $match['opponentWins']
          ];
        }
      }
    }
    foreach ($rounds as &$round) {
      $round['matches'] = array_values($round['matches']);
    }
    $this->rounds = array_values($rounds);
    $this->players = array_values($players);
  }

  public function rounds() {
    return $this->rounds;
  }

  public function pair() {
    if (!$this->awaitingPairings()) {
      throw new IllegalStateException('Called pair when not awaitingPairings');
    }
    $this->t = new Transaction();
    $roundId = $this->createRound();
    $pairings = (new Pairings($this->players()))->pair();
    $this->store($roundId, $pairings);
    return $this->t->commit();
  }

  public function awaitingPairings() {
    $sql = 'SELECT COUNT(*) AS unfinished '
      . 'FROM player_match AS pm '
      . 'INNER JOIN `match` AS m ON m.id = pm.match_id '
      . 'INNER JOIN round AS r ON r.id = m.round_id '
      . 'WHERE r.pod_id = ' . Q($this->podId) . ' AND wins IS NULL';
    return (D()->value($sql) == 0);
  }

  private function players() {
    return $this->players;
  }

  private function createRound() {
    $roundNumber = $this->getRoundNumber() + 1;
    $sql = 'INSERT INTO round (pod_id, round_number) VALUES '
      . '(' . Q($this->podId) . ', ' . Q($roundNumber) . ')';
    $this->t->execute($sql);
    return $this->t->id();
  }

  private function store($roundId, $pairings) {
    $matchesSql = 'INSERT INTO player_match (match_id, player_id) VALUES ';
    foreach ($pairings as $pairing) {
      $sql = 'INSERT INTO `match` (round_id) VALUES (' . Q($roundId) . ')';
      $this->t->execute($sql);
      $matchId = $this->t->id();
      $matchesSql .= '(' . Q($matchId) . ', ' . Q($pairing[0]['playerId']) . '), ';
      $matchesSql .= '(' . Q($matchId) . ', ' . Q($pairing[1]['playerId']) . '), ';
    }
    $matchesSql = chop($matchesSql, ', ');
    return $this->t->execute($matchesSql);
  }

  private function getRoundNumber() {
    $sql = 'SELECT MAX(round_number) AS round_number '
      . 'FROM round '
      . 'WHERE pod_id = ' . Q($this->podId);
    return D()->value($sql, 0);
  }

  private function points($wins, $opponentWins) {
    if ($wins > $opponentWins) {
      return 3;
    } elseif ($opponentWins === $wins) {
      return 1;
    } else {
      return 0;
    }
  }
}
