<?php
/**
 * @file
 * Variable module integration for the HybridAuth module.
 */

/**
 * Implements hook_variable_info().
 */
function hybridauth_variable_info($options = array()) {
  $variables['hybridauth_providers'] = array(
    'type' => 'array',
    'title' => t('HybridAuth - Providers list', array(), $options),
    'description' => t('List of providers and their status (enabled or not).', array(), $options),
    'default' => array(),
    'localize' => TRUE,
  );
  $variables['hybridauth_widget_title'] = array(
    'type' => 'string',
    'title' => t('HybridAuth widget - Title', array(), $options),
    'description' => t('Title of the HybridAuth login widget.', array(), $options),
    'default' => 'Or log in with...',
    'localize' => TRUE,
  );
  $variables['hybridauth_widget_link_text'] = array(
    'type' => 'string',
    'title' => t('HybridAuth widget - Link text', array(), $options),
    'description' => t('Text of the link of the HybridAuth login widget.', array(), $options),
    'default' => 'Social network account',
    'localize' => TRUE,
  );
  $variables['hybridauth_widget_link_title'] = array(
    'type' => 'string',
    'title' => t('HybridAuth widget - Link or icon title', array(), $options),
    'description' => t('Title of the link or icon of the HybridAuth login widget.', array(), $options),
    'default' => 'Social network account',
    'localize' => TRUE,
  );

  return $variables;
}
