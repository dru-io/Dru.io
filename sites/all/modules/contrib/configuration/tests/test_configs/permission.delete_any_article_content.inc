<?php
/**
 * @file
 * permission.delete_any_article_content.inc
 */

$api = '2.0.0';

$data = array(
  'permission' => 'delete any article content',
  'roles' => array(
    0 => 'administrator',
  ),
);

$dependencies = array();

$optional = array();

$modules = array(
  0 => 'node',
);
