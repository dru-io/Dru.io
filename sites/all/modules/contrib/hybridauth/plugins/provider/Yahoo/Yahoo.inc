<?php
/**
 * @file
 * HybridAuth Yahoo specific settings.
 */

/**
 * Plugin declaration.
 */
$plugin = array(
  'configuration_form_callback' => 'hybridauth_provider_yahoo_configuration_form_callback',
);

function hybridauth_provider_yahoo_configuration_form_callback(&$form, $provider_id) {
  unset($form['vtabs']['application']['hybridauth_provider_' . $provider_id . '_keys_id']);
}
