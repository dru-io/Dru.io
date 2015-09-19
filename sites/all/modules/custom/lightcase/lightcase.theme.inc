<?php

/**
 * @file
 * Lightcase theme functions.
 */

/**
 * Formats and displays a Lightcase image field.
 *
 * @param $variables array
 */
function theme_lightcase_image_formatter($variables) {
  $item = $variables['item'];
  $entity = $variables['entity'];
  $entity_type = $variables['entity_type'];
  $field = $variables['field'];
  $settings = $variables['display_settings'];

  $image = array(
    'path' => $item['uri'],
    'alt' => $item['alt'],
    'title' => $item['title'],
    'style_name' => $settings['lightcase_image_style_content'],
  );

  if (isset($item['width']) && isset($item['height'])) {
    $image['width'] = $item['width'];
    $image['height'] = $item['height'];
  }

  $caption = '';

  // Default entity keys.
  $entity_key_label = 'title';
  $entity_key_id = $entity_type == 'node' ? 'nid' : 'id';

  // Prepare entity info if Entity API is available
  if (module_exists('entity')) {
    $entity_info = entity_get_info($entity_type);
    $entity_key_label = isset($entity_info['entity keys']['label']) ? $entity_info['entity keys']['label'] : $entity_key_label;
    $entity_key_id = isset($entity_info['entity keys']['id']) ? $entity_info['entity keys']['id'] : $entity_key_id;
  }

  // Finally getting values of the content.
  $content_label = isset($entity->$entity_key_label)? $entity->$entity_key_label : '';
  $content_id = isset($entity->$entity_key_id)? $entity->$entity_key_id : 'id';

  switch ($settings['lightcase_caption']) {
    case 'auto':
      if (!empty($image['title'])) {
        $caption = $image['title'];
      }
      elseif (!empty($image['alt'])) {
        $caption = $image['alt'];
      }
      else {
        $caption = $content_label;
      }
      break;
    case 'title':
      $caption = $image['title'];
      break;
    case 'alt':
      $caption = $image['alt'];
      break;
    case 'content_title':
      $caption = $content_label;
      break;
    case 'custom':
      $caption = token_replace($settings['lightcase_caption_custom'], array($entity_type => $entity, 'file' => (object) $item), array('clear' => TRUE));
      $caption = htmlspecialchars_decode($caption, ENT_QUOTES);
      break;
  }

  switch ($settings['lightcase_gallery']) {
    case 'post':
      $gid = 'gallery-post-' . $content_id;
      break;
    case 'page':
      $gid = 'gallery-page';
      break;
    case 'field_post':
      $gid = 'gallery-post-' . $content_id . '-field-' . $field['id'];
      break;
    case 'field_page':
      $gid = 'gallery-page-field-' . $field['id'];
      break;
    case 'custom':
      $gid = $settings['lightcase_gallery_custom'];
      break;
    default:
      $gid = '';
      break;
  }

  $path = $settings['lightcase_image_style_lightcase'] ? image_style_url($settings['lightcase_image_style_lightcase'], $image['path']) : file_create_url($image['path']);

  return theme('lightcase_imagefield', array(
    'image' => $image,
    'path' => $path,
    'caption' => $caption,
    'group' => $gid,
  ));
}

/**
 * Returns HTML for a Lightcase image field.
 *
 * @param $variables array
 */
function theme_lightcase_imagefield($variables) {
  $image = $variables['image'];
  $path = $variables['path'];
  $caption = $variables['caption'];
  $group = $variables['group'];

  return l(theme(empty($image['style_name']) ? 'image' : 'image_style', $image), $path, array(
    'html' => TRUE,
    'attributes' => array(
      'title' => $caption,
      'class' => 'lightcase',
      'data-rel' => 'lightcase:' . $group,
    ),
  ));
}

/**
 * Preprocess function for lightcase-insert-image.tpl.php.
 */
function template_preprocess_lightcase_insert_image(&$variables) {
  $classes = array();
  $file = file_load($variables['item']['fid']);

  if (!empty($variables['widget']['settings']['insert_class'])) {
    $classes = explode(' ', $variables['widget']['settings']['insert_class']);
  }

  $classes[] = 'image-' . $variables['item']['style_name'];

  foreach ($classes as $i => $value) {
    $classes[$i] = drupal_html_class($value);
  }

  $variables['image_path'] = image_style_url($variables['item']['style_name'], $file->uri);
  $variables['link_path'] = file_create_url($file->uri);
  $variables['class'] = implode(' ', $classes);
  $variables['gid'] = 'gallery-all';
}
