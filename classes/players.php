<?php

class Players {
  public function allPlayers() {
    $sql = 'SELECT DISTINCT player_id AS id, name, url '
      . 'FROM player_event '
      # in the case of a dupe id with differing name/url have the db pick one
      . 'GROUP BY player_id '
      . 'ORDER BY name';
    return D()->execute($sql);
  }
}