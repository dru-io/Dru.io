<?php
/**
 * @file
 * druio_events.ds.inc
 */

/**
 * Implements hook_ds_field_settings_info().
 */
function druio_events_ds_field_settings_info() {
  $export = array();

  $ds_fieldsetting = new stdClass();
  $ds_fieldsetting->api_version = 1;
  $ds_fieldsetting->id = 'comment|comment_node_event|default';
  $ds_fieldsetting->entity_type = 'comment';
  $ds_fieldsetting->bundle = 'comment_node_event';
  $ds_fieldsetting->view_mode = 'default';
  $ds_fieldsetting->settings = array(
    'author_created_comment' => array(
      'weight' => '1',
      'label' => 'hidden',
      'format' => 'default',
    ),
    'links' => array(
      'weight' => '3',
      'label' => 'hidden',
      'format' => 'default',
    ),
    'ds_user_picture' => array(
      'weight' => '0',
      'label' => 'hidden',
      'format' => 'ds_picture_thumbnail',
    ),
  );
  $export['comment|comment_node_event|default'] = $ds_fieldsetting;

  $ds_fieldsetting = new stdClass();
  $ds_fieldsetting->api_version = 1;
  $ds_fieldsetting->id = 'node|event|default';
  $ds_fieldsetting->entity_type = 'node';
  $ds_fieldsetting->bundle = 'event';
  $ds_fieldsetting->view_mode = 'default';
  $ds_fieldsetting->settings = array(
    'ds_flag_will_go2_event' => array(
      'weight' => '6',
      'label' => 'hidden',
      'format' => 'default',
      'formatter_settings' => array(
        'ft' => array(
          'func' => 'theme_field',
        ),
      ),
    ),
    'title' => array(
      'weight' => '3',
      'label' => 'hidden',
      'format' => 'default',
    ),
    'comments' => array(
      'weight' => '7',
      'label' => 'hidden',
      'format' => 'default',
    ),
    'body' => array(
      'formatter_settings' => array(
        'ft' => array(
          'func' => 'theme_field',
        ),
      ),
    ),
    'field_event_date' => array(
      'formatter_settings' => array(
        'ft' => array(
          'func' => 'theme_field',
        ),
      ),
    ),
  );
  $export['node|event|default'] = $ds_fieldsetting;

  return $export;
}

/**
 * Implements hook_ds_layout_settings_info().
 */
function druio_events_ds_layout_settings_info() {
  $export = array();

  $ds_layout = new stdClass();
  $ds_layout->api_version = 1;
  $ds_layout->id = 'comment|comment_node_event|default';
  $ds_layout->entity_type = 'comment';
  $ds_layout->bundle = 'comment_node_event';
  $ds_layout->view_mode = 'default';
  $ds_layout->layout = 'comment_light';
  $ds_layout->settings = array(
    'regions' => array(
      'left' => array(
        0 => 'ds_user_picture',
      ),
      'right' => array(
        1 => 'author_created_comment',
        2 => 'comment_body',
        3 => 'links',
      ),
    ),
    'fields' => array(
      'ds_user_picture' => 'left',
      'author_created_comment' => 'right',
      'comment_body' => 'right',
      'links' => 'right',
    ),
    'classes' => array(
      'layout_class' => array(
        '' => '',
      ),
      'left' => array(
        'comment__left' => 'comment__left',
      ),
      'right' => array(
        'comment__right' => 'comment__right',
      ),
    ),
    'wrappers' => array(
      'left' => 'section',
      'right' => 'section',
    ),
    'layout_wrapper' => 'article',
    'layout_attributes' => '',
    'layout_attributes_merge' => 1,
    'layout_link_attribute' => '',
    'layout_link_custom' => '',
    'hide_sidebars' => 0,
  );
  $export['comment|comment_node_event|default'] = $ds_layout;

  $ds_layout = new stdClass();
  $ds_layout->api_version = 1;
  $ds_layout->id = 'node|event|default';
  $ds_layout->entity_type = 'node';
  $ds_layout->bundle = 'event';
  $ds_layout->view_mode = 'default';
  $ds_layout->layout = 'event';
  $ds_layout->settings = array(
    'regions' => array(
      'left' => array(
        0 => 'field_event_poster',
      ),
      'right' => array(
        1 => 'title',
        2 => 'field_event_date',
        3 => 'body',
        4 => 'ds_flag_will_go2_event',
      ),
      'bottom' => array(
        5 => 'comments',
      ),
    ),
    'fields' => array(
      'field_event_poster' => 'left',
      'title' => 'right',
      'field_event_date' => 'right',
      'body' => 'right',
      'ds_flag_will_go2_event' => 'right',
      'comments' => 'bottom',
    ),
    'classes' => array(
      'layout_class' => array(
        '' => '',
      ),
    ),
    'wrappers' => array(
      'left' => 'div',
      'right' => 'div',
      'bottom' => 'div',
    ),
    'layout_wrapper' => 'div',
    'layout_attributes' => '',
    'layout_attributes_merge' => 1,
    'layout_link_attribute' => '',
    'layout_link_custom' => '',
    'hide_sidebars' => 0,
  );
  $export['node|event|default'] = $ds_layout;

  return $export;
}
