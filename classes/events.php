<?php

class Events {
  public function signUp($eventId, $playerId) {
    $this->drop($playerId);
    $sql = 'INSERT INTO player_event (event_id, player_id) VALUES '
      . '(' . Q($eventId) . ', ' . Q($playerId) . ')';
    return D()->write($sql);
  }

  public function drop($playerId) {
    $sql = 'DELETE pe '
      . 'FROM player_event AS pe '
      . 'INNER JOIN event AS e ON pe.event_id = e.id '
      . 'WHERE date > NOW() - INTERVAL 1 HOUR '
      . 'AND player_id = ' . Q($playerId);
    return D()->write($sql);
  }

  public function create($date, $format, $cost) {
    $sql = 'INSERT INTO event (date, format, cost) VALUES '
      . '(' . Q($date) . ', ' . Q($format) . ', ' . Q($cost) . ')';
    return D()->write($sql);
  }

  public function cancel($eventId) {
    $sql = 'DELETE FROM event WHERE id = ' . Q($eventId);
    return D()->write($sql);
  }

  public function allEvents($playerId) {
    if (!$this->events) {
      $sql = 'SELECT e.id, date, format, cost, '
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
        . 'WHERE date > NOW() - INTERVAL 1 HOUR '
        . 'GROUP BY e.id';
      $this->events = D()->read($sql);
    }
    return $this->events;
  }
}
