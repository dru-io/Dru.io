<?php
/**
 * @file
 * page_manager_pages.configuration_test_page.inc
 */

$api = '2.0.0';

$data = $page = new stdClass();
$page->disabled = FALSE; /* Edit this to true to make a default page disabled initially */
$page->api_version = 1;
$page->name = 'configuration_test_page';
$page->task = 'page';
$page->admin_title = 'Configuration Test Page';
$page->admin_description = 'Configuration Test Page Description.';
$page->path = 'config-test-page';
$page->access = array(
  'logic' => 'and',
);
$page->menu = array(
  'type' => 'none',
  'title' => '',
  'name' => 'navigation',
  'weight' => '0',
  'parent' => array(
    'type' => 'none',
    'title' => '',
    'name' => 'navigation',
    'weight' => '0',
  ),
);
$page->arguments = array();
$page->conf = array(
  'admin_paths' => FALSE,
);


$dependencies = array(
  'page_manager_handlers.page_configuration_test_page_panel_context' => 'page_manager_handlers.page_configuration_test_page_panel_context',
);

$optional = array();

$modules = array(
  0 => 'ctools',
  1 => 'page_manager',
);
