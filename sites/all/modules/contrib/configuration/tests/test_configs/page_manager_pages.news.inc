<?php
/**
 * @file
 * page_manager_pages.news.inc
 */

$api = '2.0.0';

$data = $page = new stdClass();
$page->disabled = FALSE; /* Edit this to true to make a default page disabled initially */
$page->api_version = 1;
$page->name = 'news';
$page->task = 'page';
$page->admin_title = 'News';
$page->admin_description = '';
$page->path = 'news';
$page->access = array();
$page->menu = array(
  'type' => 'normal',
  'title' => 'News',
  'name' => 'main-menu',
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
  'page_manager_handlers.page_news_panel_context' => 'page_manager_handlers.page_news_panel_context',
);

$optional = array();

$modules = array(
  0 => 'ctools',
  1 => 'page_manager',
);
