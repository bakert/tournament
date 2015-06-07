<?php

class Pod {
  public function __construct($podId) {
    $this->podId = $podId;

    $sql = 'SELECT m.id AS match_id, '
      . 'pp.player_id, pm2.player_id AS opponent_id, '
      . 'pm1.wins, pm2.wins AS opponent_wins '
      . 'FROM player_pod AS pp '
      . 'LEFT JOIN round AS r ON r.id = pp.pod_id '
      . 'LEFT JOIN `match` AS m ON m.round_id = r.id '
      . 'LEFT JOIN player_match AS pm1 ON pm1.match_id = m.id '
      . 'LEFT JOIN player_match AS pm2 ON pm2.match_id = m.id AND pm2.player_id != pm1.player_id '
      . 'WHERE pp.pod_id = ' . Q($podId)
      . ' ORDER BY m.round_id';
    $matches = D()->execute($sql);
    $players = [];
    foreach ($matches as $match) {
      $playerId = $match['player_id'];
      if (!isset($players[$playerId])) {
        $players[$playerId] = ['playerId' => $playerId, 'opponents' => [], 'points' => 0];
      }
      if ($match['match_id'] !== null) {
        $players[$playerId]['opponents'][] = $match['opponent_id'];
        $players[$playerId]['points'] += $this->points($match['wins'], $match['opponent_wins']);
      }
    }
    $this->players = array_values($players);
  }

  public function pair() {
    if (!$this->awaitingPairings()) {
      throw new IllegalStateException('Called pair when not awaitingPairings');
    }
    try {
      $this->t = new Transaction();
      $roundId = $this->createRound();
      $pairings = (new Pairings($this->players()))->pair();
      $this->store($roundId, $pairings);
      return $this->t->commit();
    } catch (DatabaseException $e) {
      $this->t->rollback();
      throw $e;
    }
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
    $sql = 'SELECT LAST_INSERT_ID() AS round_id';
    return D()->value($sql);
  }

  private function store($roundId, $pairings) {
    $matchesSql = 'INSERT INTO player_match (match_id, player_id) VALUES ';
    foreach ($pairings as $pairing) {
      $sql = 'INSERT INTO `match` (round_id) VALUES (' . Q($roundId) . ')';
      D()->execute($sql);
      $sql = 'SELECT LAST_INSERT_ID() AS match_id';
      $matchId = D()->value($sql);
      $matchesSql .= '(' . Q($matchId) . ', ' . $pairing[0]['playerId'] . '), ';
      $matchesSql .= '(' . Q($matchId) . ', ' . $pairing[1]['playerId'] . '), ';
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
