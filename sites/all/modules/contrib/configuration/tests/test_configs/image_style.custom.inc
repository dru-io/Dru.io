<?php
/**
 * @file
 * image_style.custom.inc
 */

$api = '2.0.0';

$data = array(
  'name' => 'custom',
  'effects' => array(
    1 => array(
      'label' => 'Resize',
      'help' => 'Resizing will make images an exact set of dimensions. This may cause images to be stretched or shrunk disproportionately.',
      'effect callback' => 'image_resize_effect',
      'dimensions callback' => 'image_resize_dimensions',
      'form callback' => 'image_resize_form',
      'summary theme' => 'image_resize_summary',
      'module' => 'image',
      'name' => 'image_resize',
      'data' => array(
        'width' => '100',
        'height' => '100',
      ),
      'weight' => '1',
    ),
  ),
);

$dependencies = array();

$optional = array();

$modules = array(
  0 => 'image',
);
