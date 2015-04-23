<?php
/**
 * @file
 * permission.use_text_format_custom.inc
 */

$api = '2.0.0';

$data = array(
  'permission' => 'use text format custom',
  'roles' => array(
    0 => 'administrator',
  ),
);

$dependencies = array(
  'text_format.custom' => 'text_format.custom',
);

$optional = array();

$modules = array(
  0 => 'filter',
);
