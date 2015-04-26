<?php
/**
 * @file
 * druio_events.features.inc
 */

/**
 * Implements hook_ctools_plugin_api().
 */
function druio_events_ctools_plugin_api($module = NULL, $api = NULL) {
  if ($module == "captcha" && $api == "captcha") {
    return array("version" => "1");
  }
  if ($module == "ds" && $api == "ds") {
    return array("version" => "1");
  }
}

/**
 * Implements hook_views_api().
 */
function druio_events_views_api($module = NULL, $api = NULL) {
  return array("api" => "3.0");
}

/**
 * Implements hook_flag_default_flags().
 */
function druio_events_flag_default_flags() {
  $flags = array();
  // Exported flag: "Я пойду".
  $flags['will_go2_event'] = array(
    'entity_type' => 'node',
    'title' => 'Я пойду',
    'global' => 0,
    'types' => array(
      0 => 'event',
    ),
    'flag_short' => 'Я пойду',
    'flag_long' => 'Сообщите организаторам о вашем приходе, это поможет лучше организовать мероприятие',
    'flag_message' => 'Спасибо! Ждем вам на мероприятии.',
    'unflag_short' => 'Не пойду',
    'unflag_long' => '',
    'unflag_message' => '',
    'unflag_denied_text' => '',
    'link_type' => 'toggle',
    'weight' => 0,
    'show_in_links' => array(
      'full' => 'full',
      'teaser' => 'teaser',
      'rss' => 0,
      'token' => 0,
      'frontpage' => 0,
      'revision' => 0,
    ),
    'show_as_field' => 1,
    'show_on_form' => 0,
    'access_author' => '',
    'show_contextual_link' => 0,
    'i18n' => 0,
    'api_version' => 3,
    'module' => 'druio_events',
    'locked' => array(
      0 => 'name',
    ),
  );
  return $flags;

}

/**
 * Implements hook_image_default_styles().
 */
function druio_events_image_default_styles() {
  $styles = array();

  // Exported image style: event_poster.
  $styles['event_poster'] = array(
    'label' => 'event_poster',
    'effects' => array(
      4 => array(
        'name' => 'image_scale',
        'data' => array(
          'width' => 275,
          'height' => 400,
          'upscale' => 1,
        ),
        'weight' => 2,
      ),
    ),
  );

  return $styles;
}

/**
 * Implements hook_node_info().
 */
function druio_events_node_info() {
  $items = array(
    'event' => array(
      'name' => t('Мероприятие'),
      'base' => 'node_content',
      'description' => '',
      'has_title' => '1',
      'title_label' => t('Заголовок'),
      'help' => '',
    ),
  );
  drupal_alter('node_info', $items);
  return $items;
}
