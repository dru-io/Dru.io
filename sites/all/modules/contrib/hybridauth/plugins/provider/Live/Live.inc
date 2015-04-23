<?php
/**
 * @file
 * HybridAuth Windows Live specific settings.
 */

/**
 * Plugin declaration.
 */
$plugin = array(
  'configuration_form_callback' => 'hybridauth_provider_live_configuration_form_callback',
);

function hybridauth_provider_live_configuration_form_callback(&$form, $provider_id) {
  unset($form['vtabs']['application']['hybridauth_provider_' . $provider_id . '_keys_key']);
}
