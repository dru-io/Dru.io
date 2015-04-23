<?php
/**
 * @file
 * image_style.large.inc
 */

$api = '2.0.0';

$data = array(
  'effects' => array(
    0 => array(
      'label' => 'Scale',
      'help' => 'Scaling will maintain the aspect-ratio of the original image. If only a single dimension is specified, the other dimension will be calculated.',
      'effect callback' => 'image_scale_effect',
      'dimensions callback' => 'image_scale_dimensions',
      'form callback' => 'image_scale_form',
      'summary theme' => 'image_scale_summary',
      'module' => 'image',
      'name' => 'image_scale',
      'data' => array(
        'width' => 480,
        'height' => 480,
        'upscale' => 0,
      ),
      'weight' => 0,
    ),
  ),
  'name' => 'large',
);

$dependencies = array();

$optional = array();

$modules = array(
  0 => 'image',
);
