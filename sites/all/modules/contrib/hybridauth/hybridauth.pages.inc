<?php
/**
 * @file
 * HybridAuth module pages.
 */

function hybridauth_endpoint() {
  // Make sure the session is started, HybridAuth library needs it.
  hybridauth_session_start();

  if ($lib_path = _hybridauth_library_path()) {
    try {
      require_once $lib_path . '/index.php';
    }
    catch(Exception $e) {
      watchdog_exception('hybridauth', $e);
    }
  }

  return MENU_ACCESS_DENIED;
}

/**
 * Initialize the user session before using the HybridAuth library.
 */
function hybridauth_session_start() {
  // Make sure that a user session exists.
  drupal_session_start();
  // Special handling for HTTPS with normal session IDs and secure session IDs.
  // Duplicated sessions are created, so we need to pull out the correct session
  // data.
  global $is_https;
  if ($is_https && variable_get('https', FALSE)) {
    // If there is an existing session with the same secure session ID we need
    // to replace the $_SESSION contents with that.
    $session = db_query('SELECT session FROM {sessions} WHERE sid = :sid AND ssid = :sid', array('sid' => session_id()))->fetchField();
    if ($session) {
      // Overwrite $_SESSION with the data.
      session_decode($session);
      // Remove the duplicate session from the database.
      db_delete('sessions')->condition('sid', session_id())->execute();
    }
  }
}

/**
 * Returns HybridAuth widget with list of providers icons.
 */
function hybridauth_providers($js, $icon_pack) {
  $build = array(
    '#type' => 'hybridauth_widget',
    '#title' => '',
    '#hybridauth_widget_type' => 'list',
    '#hybridauth_widget_icon_pack' => $icon_pack,
  );

  if ($js) {
    ctools_include('modal');
    ctools_modal_render(t('Log in using your account with'), $build);
  }
  else {
    $build['#title'] = t('Log in using your account with');
    return $build;
  }
}

function hybridauth_window_start($provider_id) {
  // If provider is OpenID, but we don't have the openid_identifier.
  if ($provider_id == 'OpenID' && !isset($_GET['openid_identifier'])) {
    $form = drupal_get_form('hybridauth_openid_form');
    return _hybridauth_window_render_form($form, $provider_id);
  }

  // Make sure the session is started, HybridAuth library needs it.
  hybridauth_session_start();

  // Try to get HybridAuth instance.
  if ($hybridauth = hybridauth_get_instance()) {
    return _hybridauth_window_auth($hybridauth, $provider_id);
  }
  else {
    drupal_set_message(t('There was an error processing your request.'), 'error');
    _hybridauth_window_close(FALSE);
  }
}

/**
 * Close the popup (if used) and redirect if there was no error.
 */
function _hybridauth_window_close($redirect = TRUE) {
  global $user;
  // Prevent devel module from spewing.
  $GLOBALS['devel_shutdown'] = FALSE;

  $destination = drupal_get_destination();
  $destination = token_replace($destination['destination'], array('user' => $user), array('clear' => TRUE));
  $destination_error = $_GET['destination_error'];

  $destination_options = drupal_parse_url($destination) + array('absolute' => TRUE);
  $destination_error_options = drupal_parse_url($destination_error) + array('absolute' => TRUE);

  $destination = url($destination_options['path'], $destination_options);
  $destination_error = url($destination_error_options['path'], $destination_error_options);

  drupal_add_js(array(
    'hybridauth'=> array(
      'redirect' => $redirect ? 1 : 0,
      'destination' => $destination,
      'destination_error' => $destination_error,
    )
  ), 'setting');
  drupal_add_js(drupal_get_path('module', 'hybridauth') . '/js/hybridauth.close.js');
  $page = element_info('page');
  // Don't show messages on this closing page, show them later.
  $page['#show_messages'] = FALSE;
  $page['#children'] = t('Closing...');
  print theme('html', array('page' => $page));
  drupal_exit();
}

function _hybridauth_window_auth($hybridauth, $provider_id) {
  $error_code = NULL;
  if (is_object($hybridauth)) {
    $params = array(
      'hauth_return_to' => url('hybridauth/window/' . $provider_id, array('absolute' => TRUE, 'query' => drupal_get_query_parameters())),
    );
    if (isset($_GET['openid_identifier'])) {
      $params['openid_identifier'] = $_GET['openid_identifier'];
    }
    try {
      $adapter = $hybridauth->authenticate($provider_id, $params);
      $profile = (array) ($adapter->getUserProfile());
    }
    catch(Exception $e) {
      watchdog_exception('hybridauth', $e);
      $error_code = $e->getCode();
    }
  }
  else {
    $error_code = $hybridauth;
  }
  
  if (!is_null($error_code)) {
    // Destroy the session started in hybridauth_window_start() if user is not
    // logged in.
    if (!user_is_logged_in()) {
      session_destroy();
    }
    switch ($error_code) {
      case 5:
        // Authentication failed. The user has canceled the authentication or
        // the provider refused the connection.
        break;

      case 0:
        // Unspecified error.
      case 1:
        // Hybridauth configuration error.
      case 2:
        // Provider not properly configured.
      case 3:
        // Unknown or disabled provider.
      case 4:
        // Missing provider application credentials (your application id, key
        // or secret).
      case 6:
        // User profile request failed.
      case 7:
        // User not connected to the provider.
      case 8:
        // Provider does not support this feature.
      default:
        // Report the error - this message is not shown to anonymous users as
        // we destroy the session - see below.
        drupal_set_message(t('There was an error processing your request.'), 'error');
    }

    _hybridauth_window_close(FALSE);
  }

  $profile['provider'] = $provider_id;
  // Invoke hook_hybridauth_profile_alter().
  drupal_alter('hybridauth_profile', $profile);
  // Process Drupal authentication.
  return _hybridauth_window_process_auth($profile);
}

/**
 * Handle the Drupal authentication.
 */
function _hybridauth_window_process_auth($data) {
  global $user;

  // User is already logged in, tries to add new identity.
  if (user_is_logged_in()) {
    // Identity is already registered.
    if ($identity = _hybridauth_identity_load($data)) {
      // Registered to this user.
      if ($user->uid == $identity['uid']) {
        drupal_set_message(t('You have already registered this identity.'));
        _hybridauth_window_close();
      }
      // Registered to another user.
      else {
        drupal_set_message(t('This identity is registered to another user.'), 'error');
        _hybridauth_window_close();
      }
    }
    // Identity is not registered - add it to the logged in user.
    else {
      _hybridauth_identity_save($data);
      drupal_set_message(t('New identity added.'));
      _hybridauth_invoke_hooks('hybridauth_identity_added', $user, $data);
      _hybridauth_window_close();
    }
  }

  if ($identity = _hybridauth_identity_load($data)) {
    // Check if user is blocked.
    if ($account = _hybridauth_user_is_blocked_by_uid($identity['uid'])) {
      drupal_set_message(t('The username %name has not been activated or is blocked.', array('%name' => $account->name)), 'error');
    }
    // Check for email verification timestamp.
    elseif (!_hybridauth_user_login_access_by_uid($identity['uid'])) {
      $data = unserialize($identity['data']);
      drupal_set_message(t('You need to verify your e-mail address - !email.', array('!email' => $data['email'])), 'error');
      drupal_set_message(t('A welcome message with further instructions has been sent to your e-mail address.'));
      _hybridauth_mail_notify('hybridauth_email_verification', user_load($identity['uid']));
    }
    else {
      $form_state['uid'] = $identity['uid'];
      user_login_submit(array(), $form_state);
      _hybridauth_invoke_hooks('hybridauth_user_login', $user, $data);
    }
  }
  // Handle duplicate email addresses.
  elseif (variable_get('hybridauth_duplicate_emails', 1) && !empty($data['email']) && $account = user_load_by_mail($data['email'])) {
    // Add identity to existing account, only if emailVerified.
    if (variable_get('hybridauth_duplicate_emails', 1) == 2 && $data['email'] == $data['emailVerified']) {
      _hybridauth_identity_save($data, $account->uid);
      drupal_set_message(t('New identity added.'));
      _hybridauth_invoke_hooks('hybridauth_identity_added', $account, $data);
      $form_state['uid'] = $account->uid;
      user_login_submit(array(), $form_state);
      _hybridauth_invoke_hooks('hybridauth_user_login', $user, $data);
    }
    // Block registration - if (variable_get('hybridauth_duplicate_emails', 1) == 1) or
    // (variable_get('hybridauth_duplicate_emails', 1) == 2 && $data['email'] != $data['emailVerified'])
    else {
      drupal_set_message(t('You are trying to login with email address of another user.'), 'error');
      if (!empty($account->data['hybridauth'])) {
        $providers = hybridauth_providers_list();
        drupal_set_message(t('If you are completely sure it is your email address, try to login through %provider.',
          array('%provider' => $providers[$account->data['hybridauth']['provider']])), 'status');
      }
      else {
        drupal_set_message(t('If you are completely sure it is your email address, try to login using your username and password on this site. If you don\'t remember your password - <a href="@password">request new password</a>.',
          array('@password' => url('user/password'))));
      }
    }
  }
  // Check if other modules want to block this registration.
  elseif ($message = _hybridauth_registration_block($data)) {
    // Destroy the session with the blocked authorized identity.
    session_destroy();
    if (is_string($message)) {
      drupal_set_message($message, 'error');
    }
  }
  // Create new user account.
  else {
    // Visitors can create accounts.
    if ((!variable_get('hybridauth_register', 0) && variable_get('user_register', USER_REGISTER_VISITORS_ADMINISTRATIVE_APPROVAL))
      || variable_get('hybridauth_register', 0) != 3) {
      _hybridauth_invoke_hooks('hybridauth_user_preinsert', $user, $data);

      // Check profile information for required fields.
      if ($additional_info = _hybridauth_check_additional_info($data)) {
        return $additional_info;
      }

      // As we have already checked for the additional info we can unset the plaintext $data['pass'] here.
      if (isset($data['pass'])) {
        $user_password = $data['pass'];
        unset($data['pass']);
      }

      // TODO: remove this global if possible.
      global $_hybridauth_data;
      $_hybridauth_data = $data;
      // Register this new user.
      $name = _hybridauth_make_username($data);
      $userinfo = array(
        'name' => $name,
        'pass' => empty($user_password) ? user_password() : $user_password,
        'init' => $name,
        'status' => 1,
        'access' => REQUEST_TIME,
        'mail' => $data['email'],
        'data' => array('hybridauth' => $data),
      );
      // Invoke hook_hybridauth_userinfo_alter().
      drupal_alter('hybridauth_userinfo', $userinfo, $data);

      $admin_approval_required = FALSE;
      // Admin approval is required.
      if ((!variable_get('hybridauth_register', 0) && variable_get('user_register', USER_REGISTER_VISITORS_ADMINISTRATIVE_APPROVAL) == USER_REGISTER_VISITORS_ADMINISTRATIVE_APPROVAL)
        || variable_get('hybridauth_register', 0) == 2) {
        $userinfo['status'] = 0;
        $admin_approval_required = TRUE;
      }
      $account = user_save(drupal_anonymous_user(), $userinfo);
      // Terminate if an error occurred during user_save().
      if (!$account) {
        drupal_set_message(t('Error saving user account.'), 'error');
        _hybridauth_window_close();
      }
      _hybridauth_invoke_hooks('hybridauth_user_insert', $account, $data);
      _hybridauth_identity_save($data, $account->uid);
      _hybridauth_invoke_hooks('hybridauth_identity_added', $account, $data);

      $user_save_trigger = FALSE;
      $user_email_verify_trigger = FALSE;
      $user_login_trigger = TRUE;
      // Save user picture.
      if (variable_get('user_pictures', 0) && variable_get('hybridauth_pictures', 1)) {
        $photo_url = $data['photoURL'];
        if (valid_url($photo_url)) {
          $photo = drupal_http_request($photo_url);
          if (isset($photo->error)) {
            watchdog('hybridauth', 'Error while executing drupal_http_request() to %url: %error.', array('%url' => $photo_url, '%error' => $photo->error), WATCHDOG_ERROR);
          }
          else {
            if ($file = file_save_data($photo->data)) {
              // To make user_save() to process the file and move it.
              $file->status = 0;
              $edit['picture'] = $file;
              $user_save_trigger = TRUE;
            }
            else {
              watchdog('hybridauth', 'Failed to save user image from url %url.', array('%url' => $photo_url), WATCHDOG_ERROR);
            }
          }
        }
      }
      // Admin approval is required.
      if ($admin_approval_required) {
        $user_login_trigger = FALSE;
        _user_mail_notify('register_pending_approval', $account);
        drupal_set_message(t('Thank you for applying for an account. Your account is currently pending approval by the site administrator.<br />In the meantime, a welcome message with further instructions has been sent to your e-mail address.'));
      }
      // Email verification is required.
      elseif (!empty($data['email']) && $data['email'] != $data['emailVerified']
        && ((!variable_get('hybridauth_email_verification', 0) && variable_get('user_email_verification', TRUE)) || variable_get('hybridauth_email_verification', 0) == 1)) {
        $user_login_trigger = FALSE;
        // Dries birthday timestamp, Nov 19, 1978 = 280281600 :).
        $edit['login'] = 280281600;
        $user_save_trigger = TRUE;
        $user_email_verify_trigger = TRUE;
      }

      if ($user_save_trigger) {
        // Hack to remove one notice from Legal module.
        if (module_exists('legal')) {
          $edit['legal_accept'] = NULL;
        }
        $account = user_save($account, $edit);
      }
      if ($user_email_verify_trigger) {
        _hybridauth_mail_notify('hybridauth_email_verification', $account);
        drupal_set_message(t('A welcome message with further instructions has been sent to your e-mail address.'));
      }

      // Log user in.
      if ($user_login_trigger) {
        $form_state['uid'] = $account->uid;
        user_login_submit(array(), $form_state);
        _hybridauth_invoke_hooks('hybridauth_user_login', $user, $data);
      }
    }
    // Visitors can't register accounts through HybridAuth.
    elseif (variable_get('hybridauth_register', 0) == 3) {
      drupal_set_message(t('New account registration is not allowed.'), 'error');
      _hybridauth_window_close(FALSE);
    }
    // Only admin can create accounts.
    else {
      drupal_set_message(t('Only site administrators can create new user accounts.'), 'error');
      _hybridauth_window_close(FALSE);
    }
  }

  _hybridauth_window_close();
}

function _hybridauth_check_additional_info($data) {
  $show_form = FALSE;

  if (empty($data['username']) && variable_get('hybridauth_registration_username_change', 0)) {
    $show_form = TRUE;
  }
  if (empty($data['pass']) && variable_get('hybridauth_registration_password', 0)) {
    $show_form = TRUE;
  }
  $required_fields = array_filter(variable_get('hybridauth_required_fields', array('email' => 'email')));
  foreach ($required_fields as $key => $value) {
    if (empty($data[$key]) && !($data[$key] === 0)) {
      $show_form = TRUE;
      break;
    }
  }
  // Allow other modules to show pre-registration form.
  // Invoke hook_hybridauth_registration_form().
  foreach (module_invoke_all('hybridauth_registration_form', $data) as $value) {
    if ($value) {
      $show_form = TRUE;
    }
  }

  if ($show_form) {
    $form = drupal_get_form('hybridauth_additional_info_form', $data);
    return _hybridauth_window_render_form($form, $data['provider']);
  }
}

function _hybridauth_window_render_form($form, $provider_id) {
  $window_type = variable_get('hybridauth_provider_' . $provider_id . '_window_type', 'current');

  if ($window_type == 'current') {
    return $form;
  }
  else { // 'popup' or modal ('colorbox', 'shadowbox', 'fancybox', 'lightbox2')
    $page = element_info('page');
    $page['#children'] = theme('status_messages') . drupal_render($form);
    print theme('html', array('page' => $page));
  }

  drupal_exit();
}

function hybridauth_additional_info_form($form, &$form_state, $data) {
  $form = array();
  $form['data'] = array(
    '#type' => 'value',
    '#value' => $data,
  );
  $form['fset'] = array(
    '#type' => 'fieldset',
    '#title' => t('Required information'),
    '#description' => t('Please fill in additional information to complete your registration.'),
  );

  if (variable_get('hybridauth_registration_username_change', 0)) {
    $form['fset']['username'] = array(
      '#type' => 'textfield',
      '#title' => t('Username'),
      '#maxlength' => USERNAME_MAX_LENGTH,
      '#required' => TRUE,
      '#attributes' => array('class' => array('username')),
      '#default_value' => _hybridauth_make_username($data),
      '#description' => t('Choose your username.') . ' '
        . t('Spaces are allowed; punctuation is not allowed except for periods, hyphens, apostrophes, and underscores.'),
    );
    if (module_exists('username_check')) {
      _username_check_load_resources('auto');
      $form['fset']['username']['#field_suffix'] = '<span id="username-check-informer">&nbsp;</span>';
      $form['fset']['username']['#suffix'] = '<div id="username-check-message"></div>';
    }
    elseif (module_exists('friendly_register')) {
      friendly_register_load_resources();
      $form['fset']['username']['#attributes']['class'][] = 'friendly-register-name';
    }
  }
  if (variable_get('hybridauth_registration_password', 0)) {
    $form['fset']['pass'] = array(
      '#type' => 'password_confirm',
      //'#title' => t('Password'),
      '#required' => TRUE,
    );
  }

  $hybridauth_fields = hybridauth_fields_list();
  $required_fields = array_filter(variable_get('hybridauth_required_fields', array('email' => 'email')));
  foreach ($required_fields as $key => $value) {
    if (empty($data[$key]) && !($data[$key] === 0)) {
      $form['fset'][$key] = array(
        '#type' => 'textfield',
        '#title' => $hybridauth_fields[$key],
        '#required' => TRUE,
      );
      if ($key == 'email') {
        $form['fset'][$key]['#maxlength'] = EMAIL_MAX_LENGTH;
        $form['fset'][$key]['#description'] = t('A valid e-mail address. All e-mails from the system will be sent to this address. The e-mail address is not made public and will only be used if you wish to receive a new password or wish to receive certain news or notifications by e-mail.');
      }
      if ($key == 'gender') {
        $form['fset'][$key]['#type'] = 'radios';
        $form['fset'][$key]['#options'] = array(
          'male' => t('Male'),
          'female' => t('Female'),
        );
      }
    }
  }

  $form['fset']['actions'] = array('#type' => 'actions');
  $form['fset']['actions']['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Submit'),
  );

  return $form;
}

function hybridauth_additional_info_form_validate($form, &$form_state) {
  // Validate username.
  if (isset($form_state['values']['username'])) {
    if ($error = user_validate_name($form_state['values']['username'])) {
      form_set_error('username', $error);
    }
    elseif (user_load_by_name($form_state['values']['username'])) {
      form_set_error('username', t('The name %name is already taken.', array('%name' => $form_state['values']['username'])));
    }
  }

  if (isset($form_state['values']['email'])) {
    // Trim whitespace from mail, to prevent confusing 'e-mail not valid'
    // warnings often caused by cutting and pasting.
    $mail = trim($form_state['values']['email']);
    form_set_value($form['fset']['email'], $mail, $form_state);

    // Validate the e-mail address.
    if ($error = user_validate_mail($form_state['values']['email'])) {
      form_set_error('email', $error);
    }
  }
}

function hybridauth_additional_info_form_submit($form, &$form_state) {
  $data = $form_state['values']['data'];
  $required_fields = array_filter(variable_get('hybridauth_required_fields', array('email' => 'email')));

  foreach ($required_fields as $key => $value) {
    if (empty($data[$key]) && !($data[$key] === 0)) {
      $data[$key] = $form_state['values'][$key];
      if (!isset($data['manual']) || is_array($data['manual'])) {
        $data['manual'][] = $key;
      }
    }
  }

  if (isset($form_state['values']['username'])) {
    $data['username'] = $form_state['values']['username'];
    if (!isset($data['manual']) || is_array($data['manual'])) {
      $data['manual'][] = 'username';
    }
  }
  if (isset($form_state['values']['pass'])) {
    $data['pass'] = $form_state['values']['pass'];
    if (!isset($data['manual']) || is_array($data['manual'])) {
      $data['manual'][] = 'pass';
    }
  }
  $data['manual'] = implode(',', $data['manual']);

  _hybridauth_window_process_auth($data);
}

function hybridauth_openid_form($form, &$form_state) {
  $form = array();

  $form['openid_identifier'] = array(
    '#type' => 'textfield',
    '#title' => t('OpenID Identity'),
    '#description' => t('Type your OpenID identity you want to use.'),
  );

  $form['actions'] = array('#type' => 'actions');
  $form['actions']['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Submit'),
  );

  return $form;
}

function hybridauth_openid_form_submit($form, &$form_state) {
  $query = drupal_get_query_parameters() + drupal_get_destination();
  // Unset destination in global $_GET so that drupal_goto() doesn't use it.
  unset($_GET['destination']);
  $query['openid_identifier'] = $form_state['values']['openid_identifier'];

  drupal_goto('hybridauth/window/OpenID', array('query' => $query));
}

/**
 * Menu callback; manage HybridAuth identities for the specified user.
 */
function hybridauth_user_identity($form, &$form_state, $account) {
  global $user;
  drupal_set_title(format_username($account));

  $identities = _hybridauth_identity_load_by_uid($account->uid);
  $providers = hybridauth_providers_list();

  $header = array(t('Authentication provider'), t('Identity'), t('Delete'));
  $rows = array();
  $data_array = array();
  foreach ($identities as $identity) {
    $data = unserialize($identity['data']);
    $data_array[] = $data;
    $rows[] = array(
      $providers[$data['provider']],
      l($data['profileURL'], $data['profileURL'], array('attributes' => array('target' => '_blank'), 'external' => TRUE)),
      l(t('Delete'), 'user/' . $account->uid . '/hybridauth/delete/' . $identity['id']),
    );
  }

  $form = array();

  $form['identity'] = array(
    '#theme' => 'table',
    '#header' => $header,
    '#rows' => $rows,
    '#sticky' => FALSE,
    '#empty' => t("You don't have any identities yet."),
  );

  // Add more identities.
  if ($account->uid == $user->uid && user_access('use hybridauth')) {
    $form['hybridauth_widget'] = array(
      '#type' => 'hybridauth_widget',
      '#title' => t('Add more identities'),
      '#weight' => 10,
      '#hybridauth_widget_type' => 'list',
      '#hybridauth_destination' => '',
    );
  }

  if (variable_get('hybridauth_debug', 0)) {
    $connected_providers = hybridauth_get_connected_providers();
    $form['connected_providers'] = array(
      '#markup' => t('Currently connected to (session data):') . ' ' . implode(', ', $connected_providers),
      '#weight' => 15,
    );
  }

  // Tokens browser for admins.
  if (user_access('administer site configuration') || user_access('administer users')) {
    $form['vtabs'] = array(
      '#type' => 'vertical_tabs',
      '#weight' => 20,
    );

    $header = array(t('Token'), t('Value'));
    // User tokens.
    if (!empty($account->data['hybridauth'])) {
      $form['vtabs']['fset_user_tokens'] = array(
        '#type' => 'fieldset',
        '#title' => t('User tokens'),
      );

      $rows = array();
      foreach ($account->data['hybridauth'] as $key => $value) {
        $rows[] = array('[user:hybridauth:' . $key . ']', $value);
      }
      $form['vtabs']['fset_user_tokens']['tokens'] = array(
        '#theme' => 'table',
        '#header' => $header,
        '#rows' => $rows,
        '#sticky' => FALSE,
      );
    }

    // Data from auth providers.
    foreach ($data_array as $data) {
      $form['vtabs']['fset_' . $data['provider'] . '_' . $data['identifier']] = array(
        '#type' => 'fieldset',
        '#title' => $providers[$data['provider']],
      );

      $rows = array();
      foreach ($data as $key => $value) {
        $rows[] = array($key, $value);
      }
      $form['vtabs']['fset_' . $data['provider'] . '_' . $data['identifier']]['tokens'] = array(
        '#theme' => 'table',
        '#header' => $header,
        '#rows' => $rows,
        '#sticky' => FALSE,
      );
    }
  }

  return $form;
}

function hybridauth_user_identity_delete($form, &$form_state, $account, $id) {
  $del_identity = _hybridauth_identity_load_by_id($id);
  if (!$del_identity || $account->uid != $del_identity['uid']) {
    drupal_set_message(t('You are trying to delete non-existing identity.'), 'error');
    drupal_access_denied();
    // See https://drupal.org/node/2020351 and 
    // https://api.drupal.org/api/drupal/includes%21common.inc/function/drupal_access_denied/7
    drupal_exit();
  }
  $del_identity_data = unserialize($del_identity['data']);
  $username = format_username($account);
  $question = t('Are you sure you want to detach the HybridAuth identity !identity from %user?',
    array(
      '!identity' => l($del_identity_data['profileURL'], $del_identity_data['profileURL'], array('attributes' => array('target' => '_blank'), 'external' => TRUE)),
      '%user' => $username));

  $form = array();
  $form['#user'] = $account;
  $form['#del_identity'] = $del_identity;
  $form['#del_identity_data'] = $del_identity_data;
  $form['question'] = array(
    '#markup' => $question,
    '#prefix' => '<div>',
    '#suffix' => '</div>',
  );

  if (!empty($account->data['hybridauth']) && $account->data['hybridauth']['provider'] == $del_identity_data['provider'] &&
    $account->data['hybridauth']['identifier'] == $del_identity_data['identifier']) {
    $identities = _hybridauth_identity_load_by_uid($account->uid);
    $providers = hybridauth_providers_list();
    $options = array();
    foreach ($identities as $key => $identity) {
      $data = unserialize($identity['data']);
      if ($key != $id) {
        $options[$key] = $providers[$identity['provider']] . ' - ' . l($data['profileURL'], $data['profileURL'], array('attributes' => array('target' => '_blank'), 'external' => TRUE));
      }
    }

    if (!empty($options)) {
      $form['explanation'] = array(
        '#markup' => t('This identity was used to create your account. Please choose another identity to replace it.'),
        '#prefix' => '<div>',
        '#suffix' => '</div>',
      );
      $form['identity_choice'] = array(
        '#type' => 'radios',
        // '#title' => t('Identities'),
        '#options' => $options,
        '#default_value' => count($options) == 1 ? $key : NULL,
        // '#required' => TRUE, //required has bugs with radios http://drupal.org/node/811542.
      );
    }
    else {
      $form['explanation'] = array(
        '#markup' => t('This identity was used to create your account. To delete it you should first add another identity to your account.'),
        '#prefix' => '<div>',
        '#suffix' => '</div>',
      );
      // Add more identities.
      if (user_access('use hybridauth')) {
        $form['hybridauth_widget'] = array(
          '#type' => 'hybridauth_widget',
          '#title' => t('Add more identities'),
          '#weight' => 10,
          '#hybridauth_widget_type' => 'list',
          '#hybridauth_destination' => '',
        );
      }
      return $form;
    }
  }

  $form = confirm_form($form, '', 'user/' . $account->uid . '/hybridauth');
  drupal_set_title($username);

  return $form;
}

function hybridauth_user_identity_delete_validate($form, &$form_state) {
  if (!empty($form['identity_choice']) && empty($form_state['values']['identity_choice'])) {
    form_set_error('identity_choice', t('Please choose identity for replacement.'));
  }
}

function hybridauth_user_identity_delete_submit($form, &$form_state) {
  $account = $form['#user'];
  $del_identity = $form['#del_identity'];
  $del_identity_data = $form['#del_identity_data'];
  if (!empty($form_state['values']['identity_choice'])) {
    // Change hybridauth data used for tokens.
    $identity = _hybridauth_identity_load_by_id($form_state['values']['identity_choice']);
    $data = unserialize($identity['data']);
    $edit['data']['hybridauth'] = $data;
    // Change name.
    // $name = _hybridauth_make_username($data);
    // $edit['name'] = $name;
    $account = user_save($account, $edit);
  }

  $deleted = _hybridauth_identity_delete_by_id($del_identity['id']);
  if ($deleted) {
    drupal_set_message(t('Identity deleted.'));
    _hybridauth_invoke_hooks('hybridauth_identity_deleted', $account, $del_identity_data);
  }

  if ($hybridauth = hybridauth_get_instance()) {
    if (is_object($hybridauth)) {
      $adapter = $hybridauth->getAdapter($del_identity['provider']);
      $adapter->logout();
    }
  }

  $form_state['redirect'] = 'user/' . $account->uid . '/hybridauth';
}
