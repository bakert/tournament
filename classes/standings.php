<?php

class Standings {
  public function __construct($podId) {
    $this->podId = $podId;
    //BAKERT what if there have been no completed matches/no pairings?
    $sql = 'SELECT SUM(pm1.wins) AS wins, SUM(pm2.wins) AS losses, '
      . 'SUM(CASE WHEN pm1.wins > pm2.wins '
          . 'THEN 3 ELSE ('
            . 'CASE WHEN pm1.wins = pm2.wins THEN 1 ELSE 0 END '
          . ') END) AS points, '
      . 'pe1.name AS name, pe1.url, pe1.player_id AS playerId '
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
      . 'GROUP BY pm1.player_id '
      . 'ORDER BY points DESC, SUM(pm1.wins - pm2.wins) DESC';
    $this->standings = D()->execute($sql);
  }

  public function getStandings() {
    return $this->standings;
  }
}