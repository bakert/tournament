<?php

require_once(__DIR__ . '/../tournament-www.php');

class Unpair extends Page {
  public function main() {
    if (!A()->isAdmin() || $_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_GET['round_id'])) {
        return R('/');
    }
    $sql = 'DELETE FROM round WHERE id = ' . Q($_GET['round_id']);
    D()->execute($sql);
    return R('/pod/', ['pod_id' => $_GET['pod_id']]);
  }
}

echo (new Unpair())->main();
