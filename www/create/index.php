<?php

require_once(__DIR__ . '/../tournament-www.php');

class Create extends Page {
  public function main() {
    if (!A()->isAdmin()) {
      return R('/');
    }
    if (!isset($_POST['format']) || !isset($_POST['cost'])
        || trim($_POST['format']) === '' || trim($_POST['cost']) === '') {
      return R('/newevent/');
    }
    (new Events())->create($_POST['format'], $_POST['cost']);
    return R('/');
  }
}

echo (new Create())->main();
