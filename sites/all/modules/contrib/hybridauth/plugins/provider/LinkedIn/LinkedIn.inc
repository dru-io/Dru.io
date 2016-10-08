<?php
/**
 * @file
 * HybridAuth LinkedIn specific settings.
 */

/**
 * Plugin declaration.
 */
$plugin = array(
  'configuration_form_callback' => 'hybridauth_provider_linkedin_configuration_form_callback',
);

function hybridauth_provider_linkedin_configuration_form_callback(&$form, $provider_id) {
  $app_settings = &$form['vtabs']['application'];
  $app_settings['#description'] = t('Enter the Client ID and Client Secret.');
  $app_settings['hybridauth_provider_' . $provider_id . '_keys_key']['#title'] = t('Client ID');
  unset($app_settings['hybridauth_provider_' . $provider_id . '_keys_key']['#description']);
  $app_settings['hybridauth_provider_' . $provider_id . '_keys_secret']['#title'] = t('Client Secret');
  unset($app_settings['hybridauth_provider_' . $provider_id . '_keys_secret']['#description']);
  unset($app_settings['hybridauth_provider_' . $provider_id . '_keys_id']);
}
