<?php

class Match {
  public function __construct($matchId) {
    $this->matchId = $matchId;
    $sql = 'SELECT pm.player_id, pm.wins, r.pod_id '
      . 'FROM player_match AS pm '
      . 'INNER JOIN `match` AS m ON m.id = pm.match_id '
      . 'INNER JOIN round AS r ON r.id = m.round_id '
      . 'WHERE pm.match_id = ' . Q($matchId)
      . ' ORDER BY pm.player_id';
    $rs = D()->execute($sql);
    if (count($rs) !== 2) {
      throw new IllegalStateException("Illegal match with id '$matchId'");
    }
    $this->podId = $rs[0]['pod_id'];
    $this->playerId = $rs[0]['player_id'];
    $this->opponentId = $rs[1]['player_id'];
    $this->wins = $rs[0]['wins'];
    $this->opponentWins = $rs[1]['wins'];
  }

  public function awaitingResult() {
    return $this->wins === null && $this->opponentWins === null;
  }

  public function isPlaying($playerId) {
    return $this->playerId === $playerId || $this->opponentId === $playerId;
  }

  public function report($wins, $opponentWins) {
    if (!$this->awaitingResult()) {
      $msg = 'Tried to report when not awaiting result.';
      throw new IllegalStateException($msg);
    }
    $t = new Transaction();
    $entries = [$this->playerId => $wins, $this->opponentId => $opponentWins];
    foreach ($entries as $playerId => $playerWins) {
      $sql = 'UPDATE player_match SET wins = ' . Q($playerWins)
        . ' WHERE match_id = ' . Q($this->matchId)
          . ' AND player_id = ' . Q($playerId);
      $t->execute($sql);
    }
    $t->commit();
  }

  public function podId() {
    return $this->podId;
  }
}
