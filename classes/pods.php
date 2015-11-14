<?php

class Pods {

  public function createPods($eventId) {
    $this->t = new Transaction();
    $sql = 'UPDATE event SET started = TRUE WHERE id = ' . Q($eventId);
    $this->t->execute($sql);
    $players = (new Events())->players($eventId);
    shuffle($players);
    $pods = [];
    $podNums = $this->determinePods(count($players));
    foreach ($podNums as $podCount) {
      $podId = $this->createPod($eventId);
      $pod = [];
      for ($seat = 1; $seat <= $podCount; $seat++) {
        $player = array_pop($players);
        $player['seat'] = $seat;
        $pod[] = $player;
      }
      $sql = 'INSERT INTO player_pod (pod_id, player_id, seat) VALUES ';
      foreach ($pod as $player) {
        $sql .= ' (' . Q($podId) . ', ' . Q($player['player_id']) . ', '
          . Q($player['seat']) . '), ';
      }
      $sql = chop($sql, ', ');
      $this->t->execute($sql);
    }
    $this->t->commit();
  }

  private function createPod($eventId) {
    $sql = 'INSERT INTO pod (event_id) VALUES (' . Q($eventId) . ')';
    $this->t->execute($sql);
    return $this->t->id();
  }

  private function determinePods($n) {
    if ($n <= 11) {
      return [$n];
    }
    $surplus = $n % 8;
    if ($surplus === 0) {
      return $this->eights($n / 8);
    } elseif ($surplus === 1) {
      return array_merge([9], $this->eights(floor($n / 8) - 1));
    } elseif ($surplus === 2) {
      return array_merge([10], $this->eights(floor($n / 8) - 1));
    } elseif ($surplus === 3) {
      return array_merge([10, 9], $this->eights(floor($n / 8) - 2));
    } elseif ($surplus === 4) {
      return array_merge($this->eights(floor($n / 8) - 2), [6, 6]);
    } elseif ($surplus === 5) {
      return array_merge($this->eights(floor($n / 8) - 1), [7, 6]);
    } elseif ($surplus === 6) {
      return array_merge($this->eights(floor($n / 8)), [6]);
    } elseif ($surplus === 7) {
      return array_merge($this->eights(floor($n / 8)), [7]);
    }
  }

  private function eights($n) {
    $a = [];
    for ($i = 0; $i < $n; $i++) {
      $a[] = 8;
    }
    return $a;
  }
}
