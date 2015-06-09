<?php

require_once(__DIR__ . '/../tournament.php');

session_start();

Facebook\FacebookSession::setDefaultApplication(
  C()->fbappid(),
  C()->fbappsecret()
);


function A() {
  return Singletons::A();
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
