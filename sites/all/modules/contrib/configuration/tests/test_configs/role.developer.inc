<?php
/**
 * @file
 * role.developer.inc
 */

$api = '2.0.0';

$data = (object) array(
  'name' => 'Developer',
  'weight' => '4',
  'machine_name' => 'developer',
);

$dependencies = array();

$optional = array();

$modules = array(
  0 => 'role_export',
);
