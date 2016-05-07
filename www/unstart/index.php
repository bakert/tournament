<?php

require_once(__DIR__ . '/../tournament-www.php');

class Unstart extends Page {
  public function main() {
    if (!A()->isAdmin() || $_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_GET['event_id'])) {
      return R('/');
    }
    $t = new Transaction();
    $sql = 'DELETE FROM pod WHERE event_id = ' . Q($_GET['event_id']);
    $t->execute($sql);
    $sql = 'UPDATE event SET started = FALSE WHERE id = ' . Q($_GET['event_id']);
    $t->execute($sql);
    $t->commit();
    return R('/event/', ['event_id' => $_GET['event_id']]);
  }  
}

echo (new Unstart())->main();
