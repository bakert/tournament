<?php

class Pod {
  public function __construct($podId) {
    $this->podId = $podId;
    $sql = 'SELECT COUNT(*) FROM round WHERE pod_id = ' . Q($podId);
    if (D()->value($sql) > 0) {
      $this->subsequentRound();
    } else {
      $this->firstRound();
    }
  }

  private function firstRound() {
    $sql = 'SELECT pp.player_id, pe.dropped '
      . 'FROM player_pod AS pp '
      . 'INNER JOIN pod AS p ON p.id = pp.pod_id '
      . 'INNER JOIN player_event AS pe ON pp.player_id = pe.player_id '
        . 'AND pe.event_id = p.event_id '
      . 'WHERE pod_id = ' . Q($this->podId);
    $rs = D()->execute($sql);
    $players = [];
    foreach ($rs as $player) {
      $players[] = [
        'playerId' => $player['player_id'],
        'opponents' => [],
        'points' => 0,
        'dropped' => $player['dropped']
      ];
    }
    $this->rounds = [];
    $this->players = $players;
  }

  private function subsequentRound() {
    $sql = 'SELECT m.id AS match_id, '
      . 'pe1.name, pe1.url, '
      . 'pe2.name AS opponent_name, pe2.url AS opponent_url, '
      . 'pe1.dropped, pe2.dropped AS opponent_dropped, '
      . 'pm1.player_id, pm2.player_id AS opponent_id, '
      . 'pm1.wins, pm2.wins AS opponent_wins, r.id AS round_id, '
      . 'UNIX_TIMESTAMP(r.start_time) AS start_time, r.round_number '
      . 'FROM `match` AS m '
      . 'INNER JOIN round AS r ON r.id = m.round_id '
      . 'INNER JOIN pod AS p ON p.id = r.pod_id '
      . 'INNER JOIN player_match AS pm1 ON pm1.match_id = m.id '
      . 'INNER JOIN player_match AS pm2 ON pm2.match_id = m.id '
        . 'AND pm2.player_id != pm1.player_id '
      . 'INNER JOIN player_event AS pe1 ON pe1.player_id = pm1.player_id AND pe1.event_id = p.event_id '
      // LEFT because BYE does not have an entry.
      . 'LEFT JOIN player_event AS pe2 ON pe2.player_id = pm2.player_id AND pe2.event_id = p.event_id '
      . 'WHERE r.pod_id = ' . Q($this->podId) . ' AND pm1.player_id != 0 '
      . 'ORDER BY m.round_id DESC, pm1.match_id, pm1.player_id';
    $matches = D()->execute($sql);
    list($players, $rounds) = [[], []];
    foreach ($matches as $match) {
      $playerId = $match['player_id'];
      if (!isset($players[$playerId])) {
        $players[$playerId] = [
          'playerId' => $playerId,
          'opponents' => [],
          'points' => 0,
          'name' => $match['name'] ?: 'BYE',
          'url' => $match['url'],
          'dropped' => $match['dropped']
        ];
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
            'matches' => [],
            'startTime' => $match['start_time']
          ];
        }
        // Don't add a match twice even though they appear twice is resultset.
        if (!isset($rounds[$roundId]['matches'][$match['match_id']])) {
          $rounds[$roundId]['matches'][$match['match_id']] = [
            'matchId' => $match['match_id'],
            'playerId' => $match['player_id'],
            'opponentId' => $match['opponent_id'],
            'wins' => $match['wins'],
            'opponentWins' => $match['opponent_wins'],
            'name' => $match['name'] ?: 'BYE',
            'url' => $match['url'],
            'dropped' => $match['dropped'],
            'opponent' => [
              'playerId' => $match['opponent_id'],
              'name' => $match['opponent_name'] ?: 'BYE',
              'url' => $match['opponent_url'],
              'dropped' => $match['opponent_dropped']
            ]
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

  public function canBeUnpaired() {
    $hasResults = false;
    foreach ($this->latestRound()['matches'] as $match) {
      if ($match['wins'] !== null || $match['opponentWins'] !== null) {
        $hasResults = true;
      }
    }
    return !$this->awaitingPairings() && !$hasResults;
  }

  public function startTime() {
    if (!$this->rounds) {
      return null;
    }
    return reset($this->rounds)['startTime'];
  }

  public function endTime() {
    $startTime = $this->startTime();
    if (!$startTime) {
      return null;
    }
    return $startTime + (C()->minsinround() * 60);
  }

  public function minsLeft() {
    $startTime = $this->startTime();
    if (!$startTime) {
      return null;
    }
    return round(($startTime - time()) / 60) + C()->minsinround();
  }

  public function latestRound() {
    return reset($this->rounds);
  }

  public function players() {
    return $this->players;
  }

  private function createRound() {
    $roundNumber = $this->getRoundNumber() + 1;
    $sql = 'INSERT INTO round (pod_id, start_time, round_number) VALUES '
      . '(' . Q($this->podId) . ', NOW(), ' . Q($roundNumber) . ')';
    $this->t->execute($sql);
    return $this->t->id();
  }

  private function store($roundId, $pairings) {
    $matchesSql = 'INSERT INTO player_match (match_id, player_id, wins) VALUES ';
    foreach ($pairings as $pairing) {
      $sql = 'INSERT INTO `match` (round_id) VALUES (' . Q($roundId) . ')';
      $this->t->execute($sql);
      $matchId = $this->t->id();
      if ($pairing[1]['playerId'] === 0) {
        $wins = 2;
        $opponentWins = 0;
      } else {
        $wins = 'NULL';
        $opponentWins = 'NULL';
      }
      $matchesSql .= '(' . Q($matchId) . ', ' . Q($pairing[0]['playerId']) . ', ' . Q($wins) . '), ';
      $matchesSql .= '(' . Q($matchId) . ', ' . Q($pairing[1]['playerId']) . ', ' . Q($opponentWins) . '), ';
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
