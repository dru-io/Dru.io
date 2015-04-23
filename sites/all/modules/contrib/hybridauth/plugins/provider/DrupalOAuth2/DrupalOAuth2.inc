<?php
/**
 * @file
 * HybridAuth DrupalOAuth2 specific settings.
 */

/**
 * Plugin declaration.
 */
$plugin = array(
  'css' => 'drupaloauth2.css',
  'configuration_form_callback' => 'hybridauth_provider_drupaloauth2_configuration_form_callback',
  'configuration_callback' => 'hybridauth_provider_drupaloauth2_configuration_callback',
);

function hybridauth_provider_drupaloauth2_configuration_form_callback(&$form, $provider_id) {
  $app_settings = &$form['vtabs']['application'];

  $app_settings['#description'] = t('<p>Enter the <strong>OAuth2 Server</strong>, <strong>Client ID</strong> and <strong>Client Secret</strong>.<br/>'
    . 'On the server you must set the <strong>Redirect URI</strong> of the client to:<br/>'
    . '<strong>%redirect_uri</strong></p>',
    array(
      '%redirect_uri' => url('hybridauth/endpoint', array('absolute' => TRUE, 'query' => array('hauth.done' => 'DrupalOAuth2'), 'language' => _hybridauth_language_default())),
    )
  );

  unset($app_settings['hybridauth_provider_' . $provider_id . '_keys_key']);
  unset($app_settings['hybridauth_provider_' . $provider_id . '_keys_id']['#description']);
  unset($app_settings['hybridauth_provider_' . $provider_id . '_keys_secret']['#description']);
  $app_settings['hybridauth_provider_' . $provider_id . '_keys_id']['#title'] = t('Client ID');
  $app_settings['hybridauth_provider_' . $provider_id . '_keys_secret']['#title'] = t('Client Secret');

  $app_settings['hybridauth_provider_' . $provider_id . '_oauth2_server'] = array(
    '#type' => 'textfield',
    '#title' => t('OAuth2 Server'),
    '#default_value' => variable_get('hybridauth_provider_' . $provider_id . '_oauth2_server', 'https://server.example.org'),
    '#weight' => -10,
  );

  $app_settings['testing'] = array(
    '#type' => 'fieldset',
    '#title' => t('Development settings'),
    '#collapsible' => TRUE,
    '#collapsed' => TRUE,
  );
  $app_settings['testing']['hybridauth_provider_DrupalOAuth2_skipssl'] = array(
    '#type' => 'checkbox',
    '#title' => t('Skip SSL'),
    '#description' => t('Skip checking the SSL certificates.'),
    '#default_value' => variable_get('hybridauth_provider_' . $provider_id . '_skipssl', FALSE),
  );
  $app_settings['testing']['hybridauth_provider_DrupalOAuth2_proxy'] = array(
    '#type' => 'textfield',
    '#title' => t('HTTP Proxy'),
    '#description' => t('HTTP proxy (like http://mitmproxy.org/) can be very useful for debug.'),
    '#default_value' => variable_get('hybridauth_provider_' . $provider_id . '_proxy', ''),
  );
}

function hybridauth_provider_drupaloauth2_configuration_callback(&$config, $provider_id) {
  $config['oauth2_server'] = variable_get('hybridauth_provider_' . $provider_id . '_oauth2_server', 'https://server.example.org');
  $config['skip_ssl'] = variable_get('hybridauth_provider_' . $provider_id . '_skipssl', FALSE);
  $config['http_proxy'] = variable_get('hybridauth_provider_' . $provider_id . '_proxy', '');
}
