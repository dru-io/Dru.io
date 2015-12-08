<?php
/**
 * @file
 * HybridAuth ESIA specific settings.
 */

/**
 * Plugin declaration.
 */
$plugin = array(
  'configuration_form_callback' => 'hybridauth_provider_esia_configuration_form_callback',
  'configuration_callback' => 'hybridauth_provider_esia_configuration_callback',
);

function hybridauth_provider_esia_configuration_form_callback(&$form, $provider_id) {
  $app_settings = &$form['vtabs']['application'];
  unset($app_settings['hybridauth_provider_' . $provider_id . '_keys_key']);
  unset($app_settings['hybridauth_provider_' . $provider_id . '_keys_secret']);

  $app_settings['hybridauth_provider_' . $provider_id . '_oauth2_server'] = array(
    '#type' => 'textfield',
    '#title' => t('OAuth2 Server'),
    '#default_value' => variable_get('hybridauth_provider_' . $provider_id . '_oauth2_server', 'https://server.example.org'),
    '#description' => t('Server to send authentication requests to, like the test one @link.', array('@link' => 'https://esia-portal1.test.gosuslugi.ru')),
    '#weight' => -10,
  );

  $app_settings['hybridauth_provider_' . $provider_id . '_certificate_path'] = array(
    '#type' => 'textfield',
    '#title' => t('Certificate'),
    '#default_value' => variable_get('hybridauth_provider_' . $provider_id . '_certificate_path', ''),
    '#description' => t('Server path to the certificate file.'),
  );
  $app_settings['hybridauth_provider_' . $provider_id . '_private_key_path'] = array(
    '#type' => 'textfield',
    '#title' => t('Private key'),
    '#default_value' => variable_get('hybridauth_provider_' . $provider_id . '_private_key_path', ''),
    '#description' => t('Server path to the private key file.'),
  );
  $app_settings['hybridauth_provider_' . $provider_id . '_private_key_pass'] = array(
    '#type' => 'textfield',
    '#title' => t('Private key password'),
    '#default_value' => variable_get('hybridauth_provider_' . $provider_id . '_private_key_pass', ''),
    '#description' => t('Password for the private key.'),
  );
  $app_settings['hybridauth_provider_' . $provider_id . '_scope'] = array(
    '#type' => 'textfield',
    '#title' => t('Scope'),
    '#default_value' => variable_get('hybridauth_provider_' . $provider_id . '_scope', 'openid http://esia.gosuslugi.ru/usr_inf'),
    '#description' => t(''),
  );
}

function hybridauth_provider_esia_configuration_callback(&$config, $provider_id) {
  $config['oauth2_server'] = variable_get('hybridauth_provider_' . $provider_id . '_oauth2_server', 'https://server.example.org');
  $config['certificate_path'] = variable_get('hybridauth_provider_' . $provider_id . '_certificate_path', '');
  $config['private_key_path'] = variable_get('hybridauth_provider_' . $provider_id . '_private_key_path', '');
  $config['private_key_pass'] = variable_get('hybridauth_provider_' . $provider_id . '_private_key_pass', '');
  $config['scope'] = variable_get('hybridauth_provider_' . $provider_id . '_scope', 'openid http://esia.gosuslugi.ru/usr_inf');
  $config['timestamp'] = format_date(REQUEST_TIME, 'custom', 'Y.m.d H:i:s O');
}
