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
    $cssUrl = U('/css/tournament.css');
    return $this->engine->render('header', ['cssUrl' => $cssUrl]);
  }

  private function renderFooter() {
    $signOutUrl = (S()->isSignedIn()) ? U('/signout/') : null;
    return $this->engine->render('footer', ['logoutUrl' => $signOutUrl]);
  }
}
