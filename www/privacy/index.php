<?php

require_once(__DIR__ . '/../tournament-www.php');

class PrivacyPage extends Page {
    return T()->privacy();
  }
}

echo (new PodPage())->main();
