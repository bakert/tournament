<?php

class Events {
  public function signUp($eventId, $playerId, $name, $url) {
    $this->drop($playerId);
    $sql = 'INSERT INTO player_event (event_id, player_id, name, url, dropped) VALUES '
      . '(' . Q($eventId) . ', ' . Q($playerId) . ', '
      . Q($name) . ', ' . Q($url) . ', FALSE)';
    return D()->execute($sql);
  }

  public function drop($playerId) {
    $t = new Transaction();
    $sql = 'DELETE pe '
      . 'FROM player_event AS pe '
      . 'INNER JOIN event AS e ON pe.event_id = e.id '
      . 'WHERE NOT started AND player_id = ' . Q($playerId);
    $t->execute($sql);
    $sql = 'UPDATE player_event SET dropped = TRUE WHERE player_id = ' . Q($playerId);
    $t->execute($sql);
    return $t->commit();
  }

  public function create($format, $cost) {
    $sql = 'INSERT INTO event (started, format, cost) VALUES '
      . '(FALSE, ' . Q($format) . ', ' . Q($cost) . ')';
    return D()->execute($sql);
  }

  public function cancel($eventId) {
    $sql = 'DELETE FROM event WHERE id = ' . Q($eventId);
    return D()->execute($sql);
  }

  public function players($eventId) {
    $sql = 'SELECT player_id FROM player_event WHERE event_id = ' . Q($eventId);
    return D()->execute($sql);
  }

  public function currentEvents($playerId) {
    if (!$this->events) {
      $sql = 'SELECT e.id, format, cost, started, '
        . 'COUNT(DISTINCT player_id) AS numPlayers, ';
      if ($playerId !== null) {
        $sql .= 'SUM(CASE WHEN player_id = ' . Q($playerId)
          . ' THEN 1 ELSE 0 END)';
      } else {
        $sql .= 'FALSE';
      }
      $sql .= ' AS signedUp '
        . 'FROM event AS e '
        . 'LEFT JOIN player_event AS pe ON e.id = pe.event_id '
        . 'GROUP BY e.id';
      $this->events = D()->execute($sql);
    }
    return $this->events;
  }
}
