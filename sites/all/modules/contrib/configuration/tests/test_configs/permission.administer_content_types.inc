<?php
/**
 * @file
 * permission.administer_content_types.inc
 */

$api = '2.0.0';

$data = array(
  'permission' => 'administer content types',
  'roles' => array(
    0 => 'administrator',
    1 => 'developer',
  ),
);

$dependencies = array();

$optional = array();

$modules = array(
  0 => 'node',
);
