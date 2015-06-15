<?php

class Template {
  public function __construct() {
    $loader = new Mustache_Loader_FilesystemLoader(__DIR__ . '/../../views');
    $this->engine = new Mustache_Engine(['loader' => $loader]);
  }

  public function __call($name, $arguments) {
    return $this->render($name, $arguments[0] ?: []);
  }

  private function render($template, $vars = []) {
    return
      $this->renderHeader()
      . $this->engine->render($template, $vars)
      . $this->renderFooter();
  }

  private function renderHeader() {
    $args = [
      'homeUrl' => U('/'),
      'cssUrl' => U('/css/tournament.css')
    ];

    $args = array_merge($args, $this->viewerStatus(S()->id()));
    if ($args['matchId']) {
      $args['potentialResults'] = (new Results())->potentialResults($args['matchId'], S()->id());
    }

    return $this->engine->render('header', $args);
  }

  private function renderFooter() {
    $args = [
      'jsUrl' => U('/js/tournament.js')
    ];
    if (S()->isSignedIn()) {
      $args['signOutUrl'] = U('/signout/');
    }
    return $this->engine->render('footer', $args);
  }

  private function viewerStatus($playerId) {
    $status = [];
    if ($playerId === null) {
      return $status;
    }
    $status['playerId'] = $playerId;
    $sql = 'SELECT pe.event_id, pe.dropped, e.format '
      . 'FROM player_event AS pe '
      . 'INNER JOIN event AS e ON e.id = pe.event_id '
      . 'WHERE pe.player_id = ' . Q($playerId) . ' AND NOT e.finished';
    $rs = D()->execute($sql);
    if (!$rs) {
      return $status;
    }
    $status['eventId'] = $rs[0]['event_id'];
    $status['format'] = $rs[0]['format'];
    $status['dropped'] = $rs[0]['dropped'];
    $status['eventUrl'] = U('/event/', false, ['event_id' => $status['eventId']]);
    $status['dropUrl'] = U('/drop/', false, ['player_id' => $playerId]);

    if ($status['eventId'] === null || $status['dropped']) {
      return $status;
    }
    $sql = 'SELECT pp.pod_id '
      . 'FROM player_pod AS pp '
      . 'INNER JOIN pod AS p ON p.id = pp.pod_id '
      . 'WHERE p.event_id = ' . Q($status['eventId'])
        . ' AND pp.player_id = ' . Q($playerId);
    $status['podId'] = D()->value($sql, null);
    $status['podUrl'] = U('/pod/', false, ['pod_id' => $status['podId']]);
    if ($status['podId'] === null) {
      return $status;
    }
    $sql = 'SELECT r.id AS round_id, r.round_number '
      . 'FROM round AS r '
      . 'WHERE pod_id = ' . Q($status['podId'])
      . 'ORDER BY r.round_number DESC '
      . 'LIMIT 1';
    $rs = D()->execute($sql);
    if (!$rs) {
      return $status;
    }
    $status['roundNumber'] = $rs[0]['round_number'];
    $sql = 'SELECT pm.match_id '
      . 'FROM player_match AS pm '
      . 'INNER JOIN `match` AS m ON pm.match_id = m.id '
      . 'WHERE pm.player_id = ' . Q($playerId)
        . 'AND m.round_id = ' . Q($rs[0]['round_id'])
        . 'AND pm.wins IS NULL';
    $status['matchId'] = D()->value($sql, null);
    if ($status['matchId'] === null) {
      return $status;
    }
    $sql = 'SELECT pe.player_id, pe.name, pe.url '
      . 'FROM player_match AS pm '
      . 'INNER JOIN player_event AS pe ON pe.player_id = pm.player_id '
      . 'WHERE pm.match_id = ' . Q($status['matchId'])
        . 'AND pe.player_id <> ' . Q($playerId);
    $rs = D()->execute($sql);
    $status['opponent'] = [
      'playerId' => $rs[0]['player_id'],
      'name' => $rs[0]['name'],
      'url' => $rs[0]['url']
    ];
    return $status;
  }
}