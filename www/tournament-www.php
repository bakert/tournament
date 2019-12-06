<?php

require_once(__DIR__ . '/../tournament.php');

session_start();

function A() {
  return Singletons::A();
}

function F() {
  return Singletons::F();
}

function R($path, $queryString = []) {
  return Singletons::R()->redirectTo($path, $queryString);
}

function S() {
  return Singletons::S();
}

function T() {
  return Singletons::T();
}

function U($path, $absolute = false, $querystring = []) {
  return Singletons::U()->u($path, $absolute, $querystring);
}

function init() {
  // Redirect to home if not signed in or signing in.
  $currentPath = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
  $exemptPaths = [U('/'), U('/signin/'), U('/privacy/')];
  if (!in_array($currentPath, $exemptPaths) && S()->id() === null) {
    R('/');
  }
}

init();
