<?php
/**
 * @file
 * HybridAuth Twitter specific settings.
 */

/**
 * Plugin declaration.
 */
$plugin = array(
  'configuration_form_callback' => 'hybridauth_provider_twitter_configuration_form_callback',
);

function hybridauth_provider_twitter_configuration_form_callback(&$form, $provider_id) {
  unset($form['vtabs']['application']['hybridauth_provider_' . $provider_id . '_keys_id']);

  $form['vtabs']['application']['#description'] = t('<p>Enter your consumer key and private key. You can get these by creating a new application at <a href="!apps_uri">!apps_uri</a>.</p>'
    . '<p>You must set <strong>Callback URL</strong> to <strong>%callback_uri</strong>.</p>',
    array(
      '!apps_uri' => 'https://dev.twitter.com/apps',
      '%callback_uri' => url('hybridauth/endpoint', array('absolute' => TRUE, 'query' => array('hauth.done' => 'Twitter'), 'language' => _hybridauth_language_default())),
    )
  );
}
