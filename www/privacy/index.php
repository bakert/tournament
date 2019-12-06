<?php

require_once(__DIR__ . '/../tournament-www.php');

class PrivacyPage extends Page {
  public function main() {
    return T()->privacy([]);
  }
}

echo (new PrivacyPage())->main();
