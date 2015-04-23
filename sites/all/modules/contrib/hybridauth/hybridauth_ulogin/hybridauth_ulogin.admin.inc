<?php
/**
 * @file
 * Admin pages for the HybridAuth uLogin module.
 */

function hybridauth_ulogin_migrate_form($form, &$form_state) {
  $form = array();
  $form['message'] = array(
    '#markup' => format_plural(_hybridauth_ulogin_identities_count(), 'There is 1 uLogin identity to process.', 'There are @count uLogin identities to process.'),
  );
  $form['actions'] = array(
    '#type' => 'actions',
  );
  $form['actions']['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Process'),
  );
  return $form;
}

function hybridauth_ulogin_migrate_form_submit($form, &$form_state) {
  $batch = array(
    // 'title' => t('Processing'),
    // 'init_message' => t('Initializing.'),
    // 'progress_message' => t('Completed @current of @total.'),
    // 'error_message' => t('An error has occurred.'),
    'operations' => array(
      array('_hybridauth_ulogin_batch_operation', array()),
    ),
    'finished' => '_hybridauth_ulogin_finished_callback',
    'file' => drupal_get_path('module', 'hybridauth_ulogin') . '/hybridauth_ulogin.admin.inc',
  );
  batch_set($batch);
}

function _hybridauth_ulogin_batch_operation(&$context) {
  if (empty($context['sandbox'])) {
    $context['sandbox']['progress'] = 0;
    $context['sandbox']['max'] = _hybridauth_ulogin_identities_count();
  }

  $limit = 5;
  $identities = _hybridauth_ulogin_identities($context['sandbox']['progress'], $limit);

  foreach ($identities as $id => $identity) {
    _hybridauth_ulogin_identity_process($identity);
    $context['results'][] = $id;
    $context['sandbox']['progress']++;
  }
  $context['message'] = t('Processed uLogin identity !id', array('!id' => $id));
  if ($context['sandbox']['progress'] != $context['sandbox']['max']) {
    $context['finished'] = $context['sandbox']['progress'] / $context['sandbox']['max'];
  }
}

function _hybridauth_ulogin_finished_callback($success, $results, $operations) {
  if ($success) {
    drupal_set_message(format_plural(count($results), 'Processed 1 uLogin identity.', 'Processed @count uLogin identities.'));
  }
  else {
    drupal_set_message(t('An error has occurred.'), 'error');
  }
}

function _hybridauth_ulogin_identities_count() {
  $result = 0;
  if (db_table_exists('ulogin_identity')) {
    $result = db_select('ulogin_identity', 'ul_id')
      ->countQuery()
      ->execute()
      ->fetchField();
  }

  return $result;
}

function _hybridauth_ulogin_identities($start = 0, $limit = 5) {
  $result = db_select('ulogin_identity', 'ul_id')
    ->fields('ul_id')
    ->range($start, $limit)
    ->execute()
    ->fetchAllAssoc('id');
  return $result;
}

function _hybridauth_ulogin_identity_process($ulogin_identity) {
  // If no network to provider mapping - then skip this identity.
  if ($provider = _hybridauth_ulogin_provider_convert($ulogin_identity->network)) {
    $ulogin_data = unserialize($ulogin_identity->data);
    $hybridauth_data = $ulogin_data;
    $hybridauth_data['provider'] = $provider;
    $hybridauth_data['identifier'] = _hybridauth_provider_identifier($ulogin_identity->ulogin_uid);
    if (!($existing_identity = _hybridauth_identity_load($hybridauth_data)) || $existing_identity['uid'] == $ulogin_identity->uid) {
      _hybridauth_identity_save($hybridauth_data, $ulogin_identity->uid);
    }

    $account = user_load($ulogin_identity->uid);
    if (!empty($account->data['ulogin'])) {
      $edit['data']['hybridauth'] = $hybridauth_data;
      user_save($account, $edit);
    }
  }
}

function _hybridauth_ulogin_providers_map() {
  $providers_map = &drupal_static(__FUNCTION__, NULL);
  if (!isset($providers_map)) {
    // Map ulogin network key => hybridauth provider key.
    $providers_map = array(
      'vkontakte' => 'Vkontakte',
      'odnoklassniki' => 'Odnoklassniki',
      'mailru' => 'Mailru',
      'facebook' => 'Facebook',
      'twitter' => 'Twitter',
      'google' => 'Google',
      'yandex' => 'Yandex',
      'livejournal' => '',
      'openid' => 'OpenID',
      'lastfm' => 'LastFM',
      'linkedin' => 'LinkedIn',
      'liveid' => 'Live',
      'soundcloud' => '',
      'steam' => 'Steam',
      'flickr' => '',
      'vimeo' => '',
      'youtube' => '',
      'webmoney' => '',
    );
  }
  return $providers_map;
}

function _hybridauth_ulogin_provider_convert($ulogin_network_key) {
  $providers_map = _hybridauth_ulogin_providers_map();

  if (array_key_exists($ulogin_network_key, $providers_map)) {
    return $providers_map[$ulogin_network_key];
  }

  return FALSE;
}

function _hybridauth_ulogin_data_map() {
  $data_map = &drupal_static(__FUNCTION__, NULL);
  if (!isset($data_map)) {
    // Map ulogin data key => hybridauth data key.
    $data_map = array(
      // Provider, but we must convert; it is added in _hybridauth_ulogin_identity_process().
      'network' => '',
      'identity' => 'profileURL',
      'uid' => 'identifier',
      'first_name' => 'firstName',
      'last_name' => 'lastName',
      'email' => 'email',
      'nickname' => 'displayName',
      // Should convert to separate values for day, month and year.
      'bdate' => '',
      // Gender, but we should convert.
      'sex' => '',
      'photo' => 'photoURL',
      'photo_big' => 'photoURL',
      'country' => 'country',
      'city' => 'city',
      'phone' => 'phone',
      'manual' => 'manual',
      'profile' => 'profileURL',
      // If it is 1 then we should assign emailVerified.
      'verified_email' => '',
    );
  }
  return $data_map;
}

function _hybridauth_ulogin_data_convert($ulogin_data) {
  $data_map = _hybridauth_ulogin_data_map();
  $hybridauth_data = array();
  foreach ($ulogin_data as $key => $value) {
    if (array_key_exists($key, $data_map) && $data_map[$key]) {
      $hybridauth_data[$data_map[$key]] = $value;
    }

    if ($key == 'bdate') {
      list($day, $month, $year) = explode('.', $value);
      $hybridauth_data['birthDay'] = intval($day);
      $hybridauth_data['birthMonth'] = intval($month);
      $hybridauth_data['birthYear'] = intval($year);
    }
    if ($key == 'sex') {
      if ($value == 1) {
        $hybridauth_data['gender'] = 'female';
      }
      elseif ($value == 2) {
        $hybridauth_data['gender'] = 'male';
      }
    }
    if ($key == 'verified_email' && $value == 1) {
      $hybridauth_data['emailVerified'] = $ulogin_data['email'];
    }
  }

  // Adding empty keys - to follow HybridAuth standard.
  foreach (hybridauth_fields_list() as $key => $value) {
    if (!array_key_exists($key, $hybridauth_data)) {
      $hybridauth_data[$key] = '';
    }
  }

  return $hybridauth_data;
}
