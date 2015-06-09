<?php

class Pairings {
  public function __construct($players) {
    if (!$players) {
      throw new IllegalStateException('Asked to get pairings with no players.');
    }
    $this->players = $players;
  }

  public function pair() {
    shuffle($this->players);
    $permutation = $this->getPairingOrdering($this->players);
    $pairings = [];
    for ($i = 0; $i < count($permutation); $i += 2) {
      if (isset($permutation[$i + 1])) {
        $secondPlayer = $permutation[$i + 1];
      } else {
        $secondPlayer = ['playerId' => 0, 'points' => 0];
      }
      $pairings[] = [$permutation[$i], $secondPlayer];
    }
    return $pairings;
  }

  private function getPairingOrdering() {
    // Perf improvement. 10mins => 25s by ordering in sensible order to start.
    masort($this->players, 'points_d', false);
    $bestPossible = $this->bestPossible();
    $size = count($this->players) - 1;
    $perm = range(0, $size);
    $j = 0;
    $best = null;
    $bestScore = PHP_INT_MAX;
    do {
      $thisPerm = [];
      foreach ($perm as $i) {
        $thisPerm[] = $this->players[$i];
      }
      $thisScore = $this->scorePairings($thisPerm, $bestScore);
      // Optimization. Short circuit if we've found a theoretically optimal set.
      if ($thisScore <= $bestPossible) {
        return $thisPerm;
      }
      if ($thisScore < $bestScore) {
        $best = $thisPerm;
        $bestScore = $thisScore;
      }
    } while ($perm = $this->nextPermutation($perm, $size) and ++$j);
    return $best;
  }

  private function nextPermutation($p, $size) {
    if ($size === 0) {
      return false;
    }
    // slide down the array looking for where we're smaller than the next guy
    for ($i = $size - 1; $p[$i] >= $p[$i+1]; --$i) {}

    // if this doesn't occur, we've finished our permutations
    // the array is reversed: (1, 2, 3, 4) => (4, 3, 2, 1)
    if ($i == -1) {
      return false;
    }

    // slide down the array looking for a bigger number than what we found before
    for ($j = $size; $p[$j] <= $p[$i]; --$j) {}

    // swap them
    $tmp = $p[$i];
    $p[$i] = $p[$j];
    $p[$j] = $tmp;

    // now reverse the elements in between by swapping the ends
    for (++$i, $j = $size; $i < $j; ++$i, --$j) {
         $tmp = $p[$i]; $p[$i] = $p[$j]; $p[$j] = $tmp;
    }
    return $p;
  }

  private function scorePairings($players, $bestScore) {
    $score = 0;
    for ($i = 0; $i < count($players); $i += 2) {
      $score += $this->score($players[$i], $players[$i + 1]);
      // Perf optimization - can save 20s.
      if ($score >= $bestScore) {
        return PHP_INT_MAX; //abort, this pairing is definitely bad
      }
    }
    return $score;
  }

  private function havePlayed($p1, $p2) {
    foreach ($p1['opponents'] as $opponentId) {
      if ($p2['playerId'] === $opponentId) {
        return true;
      }
      if ($p2 === null && $opponentId === 0) {
        return true;
      }
    }
    return false;
  }

  private function score($p1, $p2) {
    if ($this->havePlayed($p1, $p2)) {
      return 999999;
    }
    return $this->naiveScore($p1, $p2);
  }

  private function naiveScore($p1, $p2) {
    return abs($p1['points'] - $p2['points']) * max($p1['points'], $p2['points']);
  }

  // Expects $this->players to be sorted by points desc.
  private function bestPossible() {
    $bestPossible = 0;
    for ($i = 0; $i < count($this->players); $i += 2) {
      if (isset($this->players[$i + 1])) {
        $secondPlayer = $this->players[$i + 1];
      } else {
        $secondPlayer = ['points' => 0];
      }
      $naiveScore = $this->naiveScore($this->players[$i], $secondPlayer);
      $bestPossible += $naiveScore;
    }
    return $bestPossible;
  }
}
