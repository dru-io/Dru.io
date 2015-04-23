<?php
/**
 * @file
 * Functions for integrating the HybridAuth module with Rules.
 */

/**
 * Implements hook_rules_action_info().
 * @ingroup rules
 */
function hybridauth_rules_action_info() {
  return array(
    'hybridauth_url_managed_file' => array(
      'label' => t('Save remote file to managed file'),
      'group' => t('HybridAuth'),
      'parameter' => array(
        'url' => array(
          'label' => t('URL'),
          'type' => 'uri',
          'description' => t('URL to the remote file.'),
        ),
        'directory' => array(
          'label' => t('File directory'),
          'type' => 'text',
          'description' => t('File directory to save remote file to.'),
        ),
      ),
      'provides' => array(
        'file' => array(
          'label' => 'Managed file entity',
          'type' => 'file',
        ),
      ),
    ),
    'hybridauth_dmy_to_date' => array(
      'label' => t('Create date from day/month/year'),
      'group' => t('HybridAuth'),
      'parameter' => array(
        'day' => array(
          'label' => t('Day'),
          'type' => 'text',
          // 'description' => t(''),
        ),
        'month' => array(
          'label' => t('Month'),
          'type' => 'text',
          // 'description' => t(''),
        ),
        'year' => array(
          'label' => t('Year'),
          'type' => 'text',
          // 'description' => t(''),
        ),
      ),
      'provides' => array(
        'date' => array(
          'label' => 'Date',
          'type' => 'date',
        ),
      ),
    ),
    'hybridauth_set_destination' => array(
      'label' => t('Set destination'),
      'group' => t('HybridAuth'),
      'parameter' => array(
        'destination' => array(
          'label' => t('Destination'),
          'type' => 'text',
          // 'description' => t(''),
        ),
      ),
    ),
    'hybridauth_session_destroy' => array(
      'label' => t('Destroy session'),
      'group' => t('HybridAuth'),
    ),
    'hybridauth_window_close' => array(
      'label' => t('Close HybridAuth window'),
      'group' => t('HybridAuth'),
    ),
  );
}

/**
 * Rules action.
 */
function hybridauth_url_managed_file($url, $directory) {
  if (valid_url($url, TRUE)) {
    $url_info = parse_url($url);
    // Prepare directory.
    $directory = file_default_scheme() . '://' . $directory . '/' . $url_info['host'] . dirname($url_info['path']);
    if (!file_prepare_directory($directory, FILE_CREATE_DIRECTORY | FILE_MODIFY_PERMISSIONS)) {
      watchdog('hybridauth', 'Error accessing directory %directory.', array('%directory' => $directory), WATCHDOG_ERROR);
      return array('file' => '');
    }
    // Make a request.
    $content = drupal_http_request($url);
    if (isset($content->error)) {
      watchdog('hybridauth', 'Error while executing drupal_http_request() to %url: %error.', array('%url' => $url, '%error' => $content->error), WATCHDOG_ERROR);
      return array('file' => '');
    }
    else {
      if ($file = file_save_data($content->data, $directory . '/' . basename($url_info['path']), FILE_EXISTS_REPLACE)) {
        $path_info = pathinfo($url);
        if (empty($path_info['extension'])) {
          $info = image_get_info($file->uri);
          $file = file_move($file, $directory . '/' . basename($url_info['path']) . '.' . $info['extension'], FILE_EXISTS_REPLACE);
        }
        return array('file' => $file);
      }
      else {
        watchdog('hybridauth', 'Failed to save file %file.', array('%file' => basename($url_info['path'])), WATCHDOG_ERROR);
      }
    }
  }
  else {
    watchdog('hybridauth', 'Not valid url %url', array('%url' => $url), WATCHDOG_ERROR);
  }
  return array('file' => '');
}

/**
 * Rules action.
 */
function hybridauth_dmy_to_date($day, $month, $year) {
  // My tests have shown there is no need for any timezone handling.
  // $timezone = date_default_timezone_get();
  // date_default_timezone_set('UTC');
  $timestamp = mktime(0, 0, 0, intval($month), intval($day), intval($year));
  // $timestamp = mktime(0, 0, 0, $month, $day, $year);
  // date_default_timezone_set($timezone);
  return array('date' => $timestamp);
}

/**
 * Rules action.
 */
function hybridauth_set_destination($destination) {
  drupal_static_reset('drupal_get_destination');
  $_GET['destination'] = $destination;
}

/**
 * Rules action.
 */
function hybridauth_session_destroy() {
  session_destroy();
}

/**
 * Rules action.
 */
function hybridauth_window_close() {
  _hybridauth_window_close();
}

/**
 * Implements hook_rules_data_info().
 * @ingroup rules
 */
function hybridauth_rules_data_info() {
  $types = array();

  $types['hybridauth'] = array(
    'label' => 'HybridAuth identity data',
    'wrap' => TRUE,
    'property info' => _hybridauth_rules_data_properties(),
  );

  return $types;
}

/**
 * Rules data properties for HybridAuth data type.
 */
function _hybridauth_rules_data_properties() {
  $properties = array();

  if (function_exists('hybridauth_token_info')) {
    $token_info = hybridauth_token_info();
  }
  else {
    include_once drupal_get_path('module', 'hybridauth') . '/hybridauth.tokens.inc';
    $token_info = hybridauth_token_info();
  }
  foreach ($token_info['tokens']['hybridauth'] as $key => $value) {
    $properties[$key] = array(
      'label' => $value['name'],
      'type' => 'text',
      'description' => $value['description'],
    );
  }

  return $properties;
}

/**
 * Implements hook_rules_event_info().
 * @ingroup rules
 */
function hybridauth_rules_event_info() {
  $events = array();

  $events['hybridauth_identity_added'] = array(
    'label' => t('User added HybridAuth identity'),
    'group' => t('HybridAuth'),
    'variables' => array(
      'hybridauth_user' => array(
        'label' => t('User adding the identity'),
        'type' => 'user',
      ),
      'hybridauth' => array(
        'label' => t('HybridAuth identity data'),
        'type' => 'hybridauth',
      ),
    ),
    'module' => 'hybridauth',
  );
  $events['hybridauth_identity_deleted'] = array(
    'label' => t('User deleted HybridAuth identity'),
    'group' => t('HybridAuth'),
    'variables' => array(
      'hybridauth_user' => array(
        'label' => t('User deleting the identity'),
        'type' => 'user',
      ),
      'hybridauth' => array(
        'label' => t('HybridAuth identity data'),
        'type' => 'hybridauth',
      ),
    ),
    'module' => 'hybridauth',
  );
  $events['hybridauth_user_preinsert'] = array(
    'label' => t('User is about to be created through HybridAuth'),
    'group' => t('HybridAuth'),
    'variables' => array(
      'hybridauth_user' => array(
        'label' => t('registering user'),
        'type' => 'user',
      ),
      'hybridauth' => array(
        'label' => t('HybridAuth identity data'),
        'type' => 'hybridauth',
      ),
    ),
    'module' => 'hybridauth',
  );
  $events['hybridauth_user_insert'] = array(
    'label' => t('User registered through HybridAuth'),
    'group' => t('HybridAuth'),
    'variables' => array(
      'hybridauth_user' => array(
        'label' => t('registered user'),
        'type' => 'user',
      ),
      'hybridauth' => array(
        'label' => t('HybridAuth identity data'),
        'type' => 'hybridauth',
      ),
    ),
    'module' => 'hybridauth',
  );
  $events['hybridauth_user_login'] = array(
    'label' => t('User logged in through HybridAuth'),
    'group' => t('HybridAuth'),
    'variables' => array(
      'hybridauth_user' => array(
        'label' => t('logged in user'),
        'type' => 'user',
      ),
      'hybridauth' => array(
        'label' => t('HybridAuth identity data'),
        'type' => 'hybridauth',
      ),
    ),
    'module' => 'hybridauth',
  );

  return $events;
}

/**
 * Implements hook_rules_condition_info().
 * @ingroup rules
 */
function hybridauth_rules_condition_info() {
  $conditions = array();

  $conditions['hybridauth_user_created_hybridauth'] = array(
    'label' => t('User created by HybridAuth'),
    'group' => t('HybridAuth'),
    'parameter' => array(
      'account' => array('label' => t('User'), 'type' => 'user'),
    ),
  );

  return $conditions;
}

/**
 * Rules condition.
 */
function hybridauth_user_created_hybridauth($account) {
  global $_hybridauth_data;
  return (!empty($_hybridauth_data) || !empty($account->data['hybridauth']));
}
