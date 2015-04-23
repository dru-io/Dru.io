<?php
/**
 * @file
 * content_type.old_version.inc
 */

$api = '1.0.0';

$data = (object) array(
  'type' => 'old_version',
  'name' => 'Test',
  'description' => 'Content type description.',
  'has_title' => '1',
  'title_label' => 'Title',
  'base' => 'node_content',
  'help' => '',
);

$dependencies = array();

$optional = array(
  'permission.create_test_content' => 'permission.create_test_content',
  'permission.edit_own_test_content' => 'permission.edit_own_test_content',
  'permission.edit_any_test_content' => 'permission.edit_any_test_content',
  'permission.delete_own_test_content' => 'permission.delete_own_test_content',
  'permission.delete_any_test_content' => 'permission.delete_any_test_content',
);

$modules = array(
  0 => 'node',
);
