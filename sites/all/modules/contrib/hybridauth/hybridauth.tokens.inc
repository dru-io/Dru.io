<?php
/**
 * @file
 * Builds placeholder replacement tokens for HybridAuth-related data.
 */

/**
 * Implements hook_token_info().
 */
function hybridauth_token_info() {
  $types = array();
  $types['hybridauth'] = array(
    'name' => t('HybridAuth data'),
    'description' => t('Information about the user from HybridAuth library.'),
    'needs-data' => 'user',
  );
  /*$types['hybridauth2'] = array(
    'name' => t('HybridAuth data'),
    'description' => t('Information about the user from HybridAuth library.'),
    'needs-data' => 'user',
  );*/

  $tokens = array();
  $tokens['user']['hybridauth'] = array(
    'name' => t('HybridAuth information'),
    'description' => t('Information from HybridAuth library.'),
    'type' => 'hybridauth',
  );

  foreach (hybridauth_fields_list() as $key => $value) {
    $tokens['hybridauth'][$key] = array(
      'name' => $value,
      'description' => $value . ' ' . t('provided by HybridAuth library.'),
    );
    /*$tokens['hybridauth2'][$key] = array(
      'name' => $value,
      'description' => $value . ' ' . t('provided by HybridAuth library.'),
    );*/
  }
  /*foreach (hybridauth_providers_list() as $key => $value) {
    $tokens['hybridauth'][$key] = array(
      'name' => $value,
      'description' => $value . t(' information through HybridAuth library.'),
      'type' => 'hybridauth2',
    );
  }*/

  return array(
    'types' => $types,
    'tokens' => $tokens,
  );
}

/**
 * Implements hook_tokens().
 */
function hybridauth_tokens($type, $tokens, array $data = array(), array $options = array()) {
  $sanitize = !empty($options['sanitize']);
  $replacements = array();

  if ($type == 'user' && !empty($data['user'])) {
    if (!empty($data['user']->data['hybridauth'])) {
      $hybridauth_tokens = token_find_with_prefix($tokens, 'hybridauth');
      $replacements += token_generate('hybridauth', $hybridauth_tokens, array('hybridauth' => $data['user']->data['hybridauth']), $options);
    }
  }

  if ($type == 'hybridauth' && !empty($data['hybridauth'])) {
    // Select tokens provided by HybridAuth only to avoid breaking tokens
    // provided by other modules.
    $tokens = array_intersect_key($tokens, hybridauth_fields_list());

    foreach ($tokens as $name => $original) {
      if (isset($data['hybridauth'][$name])) {
        $replacements[$original] = $sanitize ? check_plain($data['hybridauth'][$name]) : $data['hybridauth'][$name];
      }
      else {
        $replacements[$original] = '';
      }
    }
  }

  return $replacements;
}
