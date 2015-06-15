<?php

class Results {

  public function potentialResults($matchId, $reporterId = null) {
    $reverse = false;
    if ($reporterId) {
      $sql = 'SELECT player_id '
        . 'FROM player_match '
        . 'WHERE match_id = ' . Q($matchId)
        . ' ORDER BY player_id';
      $firstPlayerId = D()->value($sql);
      $reverse = $reporterId !== $firstPlayerId;
    }
    $potentialResults = [];
    foreach (['2-0', '2-1', '1-0', '1-1', '0-0', '0-1', '1-2', '0-2'] as $r) {
      list($wins, $opponentWins) = explode('-', $r);
      $reportQuerystring = [
        'match_id' => $matchId,
        'wins' => $reverse ? $opponentWins : $wins,
        'opponent_wins' => $reverse ? $wins : $opponentWins
      ];
      $potentialResults[] = [
        'display' => $wins . '-' . $opponentWins,
        'url' => U('/report/', false, $reportQuerystring)
      ];
    }
    return $potentialResults;
  }
}