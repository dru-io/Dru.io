<?php
/**
 * @file
 * HybridAuth Disqus specific settings.
 */

/**
 * Plugin declaration.
 */
$plugin = array(
  'configuration_form_callback' => 'hybridauth_provider_disqus_configuration_form_callback',
);

function hybridauth_provider_disqus_configuration_form_callback(&$form, $provider_id) {
  unset($form['vtabs']['application']['hybridauth_provider_' . $provider_id . '_keys_key']);
  $form['vtabs']['application']['hybridauth_provider_' . $provider_id . '_keys_id']['#title'] = t('API Key (Public Key)');
  $form['vtabs']['application']['hybridauth_provider_' . $provider_id . '_keys_secret']['#title'] = t('API Secret (Secret Key)');

  $form['vtabs']['application']['#description'] = t('<p>Enter your application key and secret. You can get these by creating a new application at <a href="!apps_uri">!apps_uri</a>.</p>'
    . '<p>You must set <strong>CallBack URL</strong> to <strong>%redirect_uri</strong>.</p>',
    array(
      '!apps_uri' => 'https://disqus.com/api/applications/',
      '%redirect_uri' => url('hybridauth/endpoint', array('absolute' => TRUE, 'query' => array('hauth.done' => 'Discus'), 'language' => _hybridauth_language_default())),
    )
  );
}
