<?php
/**
 * @file
 * text_format.custom.inc
 */

$api = '2.0.0';

$data = (object) array(
  'format' => 'custom',
  'name' => 'Custom',
  'cache' => '1',
  'status' => '1',
  'weight' => '0',
  'filters' => array(
    'filter_html' => array(
      'weight' => '-10',
      'status' => '1',
      'settings' => array(
        'allowed_html' => '<a> <ul> <ol> <li> <dl> <dt> <dd>',
        'filter_html_help' => 1,
        'filter_html_nofollow' => 0,
      ),
    ),
    'filter_url' => array(
      'weight' => '0',
      'status' => '1',
      'settings' => array(
        'filter_url_length' => '100',
      ),
    ),
    'filter_htmlcorrector' => array(
      'weight' => '10',
      'status' => '1',
      'settings' => array(),
    ),
  ),
);

$dependencies = array();

$optional = array(
  'permission.use_text_format_custom' => 'permission.use_text_format_custom',
);

$modules = array(
  0 => 'filter',
);
