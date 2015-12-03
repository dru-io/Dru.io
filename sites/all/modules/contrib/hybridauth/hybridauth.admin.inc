<?php
/**
 * @file
 * Administrative pages forms and functions for the HybridAuth module.
 */

/**
 * Form constructor for the hybridauth admin settings form.
 *
 * @see hybridauth_admin_settings_validate()
 *
 * @ingroup forms
 */
function hybridauth_admin_settings($form, &$form_state) {
  if (variable_get('hybridauth_register', 0) == 0 && variable_get('user_register', USER_REGISTER_VISITORS_ADMINISTRATIVE_APPROVAL) == USER_REGISTER_ADMINISTRATORS_ONLY) {
    drupal_set_message(t('Currently only administrators can create new user accounts. Either change the "Who can register accounts?" option under "Account settings" tab or at the core <a href="!link">Account settings</a>.',
      array('!link' => url('admin/config/people/accounts'))), 'warning');
  }

  $form['vtabs'] = array(
    '#type' => 'vertical_tabs',
    '#attached' => array(
      'js' => array(drupal_get_path('module', 'hybridauth') . '/js/hybridauth.admin.js'),
    ),
  );

  $form['vtabs']['fset_providers'] = array(
    '#type' => 'fieldset',
    '#title' => t('Authentication providers'),
  );
  $header = array(
    'name' => t('Name'),
    'icon' => t('Icon'),
    'available' => t('Available'),
    'settings' => t('Settings'),
  );
  $options = array();
  $weight = -50;
  // Clear the providers cache here to get any new ones.
  $providers = hybridauth_providers_list(TRUE);
  $enabled_providers = array_filter(variable_get('hybridauth_providers', array()));
  $available_providers = hybridauth_providers_files();
  $form['vtabs']['fset_providers']['hybridauth_providers'] = array();
  foreach (array_keys($enabled_providers + $providers) as $provider_id) {
    $available = array_key_exists($provider_id, $available_providers);
    $options[$provider_id] = array(
      'name' => $providers[$provider_id],
      'icon' => theme('hybridauth_provider_icon', array('provider_id' => $provider_id, 'icon_pack' => 'hybridauth_16')),
      'available' => $available ? t('Yes') : t('No'),
      'settings' => l(t('Settings'), 'admin/config/people/hybridauth/provider/' . $provider_id, array('query' => drupal_get_destination())),
      '#attributes' => array('class' => array('draggable')),
    );
    $form['vtabs']['fset_providers']['hybridauth_providers']['hybridauth_provider_' . $provider_id . '_weight'] = array(
      '#tree' => FALSE,
      '#type' => 'weight',
      '#delta' => 50,
      '#default_value' => $weight++,
      '#attributes' => array('class' => array('hybridauth-providers-weight')),
    );
  }
  $form['vtabs']['fset_providers']['hybridauth_providers'] += array(
    '#type' => 'tableselect',
    '#title' => t('Providers'),
    '#header' => $header,
    '#options' => $options,
    '#default_value' => $enabled_providers,
    '#pre_render' => array('hybridauth_admin_providers_pre_render'),
  );

  $form['vtabs']['fset_fields'] = array(
    '#type' => 'fieldset',
    '#title' => t('Required information'),
  );
  $form['vtabs']['fset_fields']['hybridauth_required_fields'] = array(
    '#type' => 'checkboxes',
    '#title' => t('Required information'),
    '#options' => array(
      'email' => t('Email address'),
      'firstName' => t('First name'),
      'lastName' => t('Last name'),
      'gender' => t('Gender'),
    ),
    '#description' => t("If authentication provider doesn't return it, visitor will need to fill additional form before registration."),
    '#default_value' => variable_get('hybridauth_required_fields', array('email' => 'email')),
  );

  $form['vtabs']['fset_widget'] = array(
    '#type' => 'fieldset',
    '#title' => t('Widget settings'),
  );
  $form['vtabs']['fset_widget']['hybridauth_widget_title'] = array(
    '#type' => 'textfield',
    '#title' => t('Widget title'),
    '#default_value' => variable_get('hybridauth_widget_title', 'Or log in with...'),
  );
  $options = array(
    'list' => t('Enabled providers icons'),
    'button' => t('Single icon leading to a list of enabled providers'),
    'link' => t('Link leading to a list of enabled providers'),
  );
  $form['vtabs']['fset_widget']['hybridauth_widget_type'] = array(
    '#type' => 'radios',
    '#title' => t('Widget type'),
    '#options' => $options,
    '#default_value' => variable_get('hybridauth_widget_type', 'list'),
  );
  $form['vtabs']['fset_widget']['hybridauth_widget_use_overlay'] = array(
    '#type' => 'checkbox',
    '#title' => t('Display list of enabled providers using overlay'),
    '#default_value' => variable_get('hybridauth_widget_use_overlay', 1),
    '#states' => array(
      'invisible' => array(
        ':input[name="hybridauth_widget_type"]' => array('value' => 'list'),
      ),
    ),
  );
  $form['vtabs']['fset_widget']['hybridauth_widget_link_text'] = array(
    '#type' => 'textfield',
    '#title' => t('Link text'),
    '#default_value' => variable_get('hybridauth_widget_link_text', 'Social network account'),
    '#states' => array(
      'visible' => array(
        ':input[name="hybridauth_widget_type"]' => array('value' => 'link'),
      ),
    ),
  );
  $form['vtabs']['fset_widget']['hybridauth_widget_link_title'] = array(
    '#type' => 'textfield',
    '#title' => t('Link or icon title'),
    '#default_value' => variable_get('hybridauth_widget_link_title', 'Social network account'),
    '#states' => array(
      'invisible' => array(
        ':input[name="hybridauth_widget_type"]' => array('value' => 'list'),
      ),
    ),
  );
  $options = array();
  foreach (hybridauth_get_icon_packs() as $name => $icon_pack) {
    $options[$name] = $icon_pack['title'];
  }
  $form['vtabs']['fset_widget']['hybridauth_widget_icon_pack'] = array(
    '#type' => 'select',
    '#title' => t('Icon package'),
    '#options' => $options,
    '#default_value' => variable_get('hybridauth_widget_icon_pack', 'hybridauth_32'),
  );
  $form['vtabs']['fset_widget']['hybridauth_widget_weight'] = array(
    '#type' => 'weight',
    '#title' => t('Widget weight'),
    '#delta' => 200,
    '#description' => t('Determines the order of the elements on the form - heavier elements get positioned later.'),
    '#default_value' => variable_get('hybridauth_widget_weight', 100),
  );
  $form['vtabs']['fset_widget']['hybridauth_widget_hide_links'] = array(
    '#type' => 'checkbox',
    '#title' => t('Hide links'),
    '#description' => t('Remove href property from authentication provider links.'),
    '#default_value' => variable_get('hybridauth_widget_hide_links', 0),
  );


  $form['vtabs']['fset_account'] = array(
    '#type' => 'fieldset',
    '#title' => t('Account settings'),
  );
  $core_settings = array(
    USER_REGISTER_ADMINISTRATORS_ONLY => t('Administrators only'),
    USER_REGISTER_VISITORS => t('Visitors'),
    USER_REGISTER_VISITORS_ADMINISTRATIVE_APPROVAL => t('Visitors, but administrator approval is required'),
  );
  $form['vtabs']['fset_account']['hybridauth_register'] = array(
    '#type' => 'radios',
    '#title' => t('Who can register accounts?'),
    '#options' => array(
      0 => t('Follow core') . ': ' . $core_settings[variable_get('user_register', USER_REGISTER_VISITORS_ADMINISTRATIVE_APPROVAL)],
      1 => t('Visitors'),
      2 => t('Visitors, but administrator approval is required'),
      3 => t('Nobody, only login for existing accounts is possible'),
    ),
    '#description' => t('Select who can register accounts through HybridAuth.') . ' '
      . t('The core settings are available at <a href="!link">Account settings</a>.',
        array('!link' => url('admin/config/people/accounts'))),
    '#default_value' => variable_get('hybridauth_register', 0),
  );
  $core_settings = array(
    0 => t("Don't require e-mail verification"),
    1 => t('Require e-mail verification'),
  );
  $form['vtabs']['fset_account']['hybridauth_email_verification'] = array(
    '#type' => 'radios',
    '#title' => t('E-mail verification'),
    '#options' => array(
      0 => t('Follow core') . ': ' . $core_settings[variable_get('user_email_verification', TRUE)],
      1 => t('Require e-mail verification'),
      2 => t("Don't require e-mail verification"),
    ),
    '#description' => t("Select how to handle not verified e-mail addresses (authentication provider gives non-verified e-mail address or doesn't provide one at all).") . ' '
      . t('The core settings are available at <a href="!link">Account settings</a>.',
        array('!link' => url('admin/config/people/accounts'))),
    '#default_value' => variable_get('hybridauth_email_verification', 0),
  );
  // E-mail address verification template.
  $form['vtabs']['fset_account']['fset_email_verification_template'] = array(
    '#type' => 'fieldset',
    '#title' => t('E-mail verification template'),
    '#collapsible' => TRUE,
    '#collapsed' => TRUE,
    '#states' => array(
      'invisible' => array(
        ':input[name="hybridauth_email_verification"]' => array('value' => '2'),
      ),
    ),
  );
  $form['vtabs']['fset_account']['fset_email_verification_template']['hybridauth_email_verification_subject'] = array(
    '#type' => 'textfield',
    '#title' => t('Subject'),
    '#default_value' => _hybridauth_mail_text('hybridauth_email_verification_subject', NULL, array(), FALSE),
    '#maxlength' => 180,
  );
  $form['vtabs']['fset_account']['fset_email_verification_template']['hybridauth_email_verification_body'] = array(
    '#type' => 'textarea',
    '#title' => t('Body'),
    '#default_value' => _hybridauth_mail_text('hybridauth_email_verification_body', NULL, array(), FALSE),
    '#rows' => 12,
  );
  if (module_exists('token')) {
    $form['vtabs']['fset_account']['fset_email_verification_template']['hybridauth_token_tree'] = array(
      '#theme' => 'token_tree',
      '#token_types' => array('user'),
      '#global_types' => TRUE,
      '#dialog' => TRUE,
    );
  }
  $form['vtabs']['fset_account']['hybridauth_pictures'] = array(
    '#type' => 'checkbox',
    '#title' => t('Save HybridAuth provided picture as user picture'),
    '#description' => t('Save pictures provided by HybridAuth as user pictures. Check the "Enable user pictures" option at <a href="!link">Account settings</a> to make this option available.',
      array('!link' => url('admin/config/people/accounts'))),
    '#default_value' => variable_get('hybridauth_pictures', 1),
    '#disabled' => !variable_get('user_pictures', 0),
  );
  $form['vtabs']['fset_account']['hybridauth_username'] = array(
    '#type' => 'textfield',
    '#title' => t('Username pattern'),
    '#default_value' => variable_get('hybridauth_username', '[user:hybridauth:firstName] [user:hybridauth:lastName]'),
    '#description' => t('Create username for new users using this pattern; counter will be added in case of username conflict.') . ' '
      . t('You should use only HybridAuth tokens here as the user is not created yet.') . ' '
      . t('Install !link module to get list of all available tokens.',
        array('!link' => l(t('Token'), 'http://drupal.org/project/token', array('attributes' => array('target' => '_blank'))))),
    '#required' => TRUE,
  );
  $form['vtabs']['fset_account']['hybridauth_display_name'] = array(
    '#type' => 'textfield',
    '#title' => t('Display name pattern'),
    '#default_value' => variable_get('hybridauth_display_name', '[user:hybridauth:firstName] [user:hybridauth:lastName]'),
    '#description' => t('Leave empty to not alter display name. You can use any user tokens here.') . ' '
      . t('Install !link module to get list of all available tokens.',
        array('!link' => l(t('Token'), 'http://drupal.org/project/token', array('attributes' => array('target' => '_blank'))))),
  );
  if (module_exists('token')) {
    $form['vtabs']['fset_account']['fset_token'] = array(
      '#type' => 'fieldset',
      '#title' => t('Tokens'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
    );
    $form['vtabs']['fset_account']['fset_token']['hybridauth_token_tree'] = array(
      '#theme' => 'token_tree',
      '#token_types' => array('user'),
      '#global_types' => FALSE,
      '#dialog' => TRUE,
    );
  }
  $form['vtabs']['fset_account']['hybridauth_registration_username_change'] = array(
    '#type' => 'checkbox',
    '#title' => t('Allow username change when registering'),
    '#description' => t('Allow users to change their username when registering through HybridAuth.'),
    '#default_value' => variable_get('hybridauth_registration_username_change', 0),
  );
  $form['vtabs']['fset_account']['hybridauth_registration_password'] = array(
    '#type' => 'checkbox',
    '#title' => t('Ask user for a password when registering'),
    '#description' => t('Ask users to set password for account when registering through HybridAuth.'),
    '#default_value' => variable_get('hybridauth_registration_password', 0),
  );
  $form['vtabs']['fset_account']['hybridauth_override_realname'] = array(
    '#type' => 'checkbox',
    '#title' => t('Override Real name'),
    '#description' => t('Override <a href="!link1">Real name settings</a> using the above display name pattern for users created by HybridAuth. This option is available only if !link2 module is installed.',
      array('!link1' => url('admin/config/people/realname'),
        '!link2' => l(t('Real name'), 'http://drupal.org/project/realname', array('attributes' => array('target' => '_blank'))))),
    '#default_value' => variable_get('hybridauth_override_realname', 0),
    '#disabled' => !module_exists('realname'),
  );
  $form['vtabs']['fset_account']['hybridauth_disable_username_change'] = array(
    '#type' => 'checkbox',
    '#title' => t('Disable username change'),
    '#description' => t('Remove username field from user account edit form for users created by HybridAuth.'
      . ' If this is unchecked then users should also have "Change own username" permission to actually be able to change the username.'),
    '#default_value' => variable_get('hybridauth_disable_username_change', 1),
  );
  $form['vtabs']['fset_account']['hybridauth_remove_password_fields'] = array(
    '#type' => 'checkbox',
    '#title' => t('Remove password fields'),
    '#description' => t('Remove password fields from user account edit form for users created by HybridAuth.'),
    '#default_value' => variable_get('hybridauth_remove_password_fields', 1),
  );

  $form['vtabs']['fset_forms'] = array(
    '#type' => 'fieldset',
    '#title' => t('Drupal forms'),
  );
  $form['vtabs']['fset_forms']['hybridauth_forms'] = array(
    '#type' => 'checkboxes',
    '#title' => t('Drupal forms'),
    '#options' => hybridauth_forms_list(),
    '#description' => t('Add HybridAuth widget to these forms.'),
    '#default_value' => variable_get('hybridauth_forms', array('user_login', 'user_login_block')),
  );

  $form['vtabs']['fset_other'] = array(
    '#type' => 'fieldset',
    '#title' => t('Other settings'),
  );
  $destination_description = t('Drupal path to redirect to, like "node/1". Leave empty to return to the same page (set to [HTTP_REFERER] for widget in modal dialogs loaded by AJAX).') . '<br/>'
    . t('You can use any user or global tokens here.') . ' '
    . t('Install !link module to get list of all available tokens.',
      array('!link' => l(t('Token'), 'http://drupal.org/project/token', array('attributes' => array('target' => '_blank')))));
  $form['vtabs']['fset_other']['hybridauth_destination'] = array(
    '#type' => 'textfield',
    '#title' => t('Redirect after login'),
    '#default_value' => variable_get('hybridauth_destination', ''),
    '#description' => $destination_description,
  );
  $form['vtabs']['fset_other']['hybridauth_destination_error'] = array(
    '#type' => 'textfield',
    '#title' => t('Redirect after error on login'),
    '#default_value' => variable_get('hybridauth_destination_error', ''),
    '#description' => $destination_description,
  );
  if (module_exists('token')) {
    $form['vtabs']['fset_other']['fset_token'] = array(
      '#type' => 'fieldset',
      '#title' => t('Tokens'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
    );
    $form['vtabs']['fset_other']['fset_token']['hybridauth_token_tree'] = array(
      '#theme' => 'token_tree',
      '#token_types' => array('user'),
      '#global_types' => TRUE,
      '#dialog' => TRUE,
    );
  }
  $options = array(
    0 => t('Allow duplicate email addresses, create new user account and login'),
    1 => t("Don't allow duplicate email addresses, block registration and advise to login using the existing account"),
    2 => t("Don't allow duplicate email addresses, add new identity to the existing account and login"),
  );
  $form['vtabs']['fset_other']['hybridauth_duplicate_emails'] = array(
    '#type' => 'radios',
    '#title' => t('Duplicate emails'),
    '#options' => $options,
    '#default_value' => variable_get('hybridauth_duplicate_emails', 1),
    '#description' => t('Select how to handle duplicate email addresses. '
      . 'This situation occurs when the same user is trying to authenticate using different authentication providers, but with the same email address.'),
  );
  $form['vtabs']['fset_other']['hybridauth_proxy'] = array(
    '#type' => 'textfield',
    '#title' => t('Proxy'),
    '#default_value' => variable_get('hybridauth_proxy', NULL),
    '#description' => t('The HTTP proxy to tunnel requests through. For example <strong>1.2.3.4:8080</strong>.'),
  );
  $form['vtabs']['fset_other']['hybridauth_debug'] = array(
    '#type' => 'checkbox',
    '#title' => t('Debug mode'),
    '#description' => t('When in debug mode, extra error information will be logged/displayed. This should be disabled when not in development.'),
    '#default_value' => variable_get('hybridauth_debug', 0),
  );

  return system_settings_form($form);
}

/**
 * Pre-render callback for the providers tableselect.
 */
function hybridauth_admin_providers_pre_render($element) {
  // Add weight column.
  $element['#header']['weight'] = t('Weight');
  foreach (array_keys($element['#options']) as $provider_id) {
    $key = 'hybridauth_provider_' . $provider_id . '_weight';
    $element['#options'][$provider_id]['weight'] = array(
      'data' => drupal_render($element[$key]),
    );
    unset($element[$key]);
  }

  // Assign id to the table.
  $table_id = 'hybridauth-providers';
  $element['#attributes'] = array('id' => $table_id);
  drupal_add_tabledrag($table_id, 'order', 'sibling', 'hybridauth-providers-weight');
  return $element;
}

/**
 * Form validation handler for the hybridauth admin settings form.
 */
function hybridauth_admin_settings_validate($form, &$form_state) {
  $values = &$form_state['values'];
  $providers = hybridauth_providers_list();

  // Remove weights so they are not saved as variables.
  foreach (array_keys($providers) as $provider_id) {
    $providers_weights[$provider_id] = $values['hybridauth_provider_' . $provider_id . '_weight'];
    unset($values['hybridauth_provider_' . $provider_id . '_weight']);
  }
  asort($providers_weights);

  $providers_values = $values['hybridauth_providers'];
  unset($values['hybridauth_providers']);

  foreach (array_keys($providers_weights) as $provider_id) {
    $values['hybridauth_providers'][$provider_id] = $providers_values[$provider_id];
  }
}

/**
 * Form constructor for the hybridauth provider admin settings form.
 *
 * @see hybridauth_admin_provider_settings_validate()
 *
 * @ingroup forms
 */
function hybridauth_admin_provider_settings($form, &$form_state, $provider_id = NULL) {
  $form['vtabs'] = array(
    '#type' => 'vertical_tabs',
  );

  $form['vtabs']['application'] = array(
    '#type' => 'fieldset',
    '#title' => t('Application settings'),
    '#description' => t('Enter the application ID, consumer key, and/or secret key as required.'),
  );
  $form['vtabs']['application']['hybridauth_provider_' . $provider_id . '_keys_id'] = array(
    '#type' => 'textfield',
    '#title' => t('Application ID'),
    '#description' => t('The application ID.'),
    '#default_value' => variable_get('hybridauth_provider_' . $provider_id . '_keys_id', ''),
  );
  $form['vtabs']['application']['hybridauth_provider_' . $provider_id . '_keys_key'] = array(
    '#type' => 'textfield',
    '#title' => t('Application consumer key'),
    '#description' => t('The Application consumer key.'),
    '#default_value' => variable_get('hybridauth_provider_' . $provider_id . '_keys_key', ''),
  );
  $form['vtabs']['application']['hybridauth_provider_' . $provider_id . '_keys_secret'] = array(
    '#type' => 'textfield',
    '#title' => t('Application consumer secret'),
    '#description' => t('The application consumer secret key.'),
    '#default_value' => variable_get('hybridauth_provider_' . $provider_id . '_keys_secret', ''),
  );

  $form['vtabs']['window_settings'] = array(
    '#type' => 'fieldset',
    '#title' => t('Authentication window settings'),
  );
  $options = array(
    'current' => t('Current window'),
    'popup' => t('New popup window'),
  );
  $modal_description = FALSE;
  if (module_exists('colorbox')) {
    $options['colorbox'] = t('Colorbox');
    $modal_description = TRUE;
  }
  if (module_exists('shadowbox')) {
    $options['shadowbox'] = t('Shadowbox');
    $modal_description = TRUE;
  }
  if (module_exists('fancybox')) {
    $options['fancybox'] = t('fancyBox');
    $modal_description = TRUE;
  }
  if (module_exists('lightbox2')) {
    $options['lightbox2'] = t('Lightbox2');
    $modal_description = TRUE;
  }
  $form['vtabs']['window_settings']['hybridauth_provider_' . $provider_id . '_window_type'] = array(
    '#type' => 'radios',
    '#title' => t('Authentication window type'),
    '#options' => $options,
    '#default_value' => variable_get('hybridauth_provider_' . $provider_id . '_window_type', 'current'),
    '#description' => $modal_description ? t("Be careful with modal windows - some authentication providers (Twitter, LinkedIn) won't work with them.") : '',
  );
  $base = array(
    '#type' => 'textfield',
    '#element_validate' => array('element_validate_integer_positive'),
    '#size' => 4,
    '#maxlength' => 4,
    '#states' => array(
      'invisible' => array(
        ':input[name="hybridauth_provider_' . $provider_id . '_window_type"]' => array('value' => 'current'),
      ),
    ),
  );
  $form['vtabs']['window_settings']['hybridauth_provider_' . $provider_id . '_window_width'] = array(
    '#title' => t('Width'),
    '#description' => t('Authentication window width (pixels).'),
    '#default_value' => variable_get('hybridauth_provider_' . $provider_id . '_window_width', 800),
  ) + $base;
  $form['vtabs']['window_settings']['hybridauth_provider_' . $provider_id . '_window_height'] = array(
    '#title' => t('Height'),
    '#description' => t('Authentication window height (pixels).'),
    '#default_value' => variable_get('hybridauth_provider_' . $provider_id . '_window_height', 500),
  ) + $base;

  if ($provider = hybridauth_get_provider($provider_id)) {
    if ($function = ctools_plugin_get_function($provider, 'configuration_form_callback')) {
      $function($form, $provider_id);
    }
  }

  return system_settings_form($form);
}

/**
 * Form validation handler for the hybridauth provider admin settings form.
 */
function hybridauth_admin_provider_settings_validate($form, &$form_state) {
  foreach ($form_state['values'] as $variable_name => $value) {
    if (empty($value) && strpos($variable_name, 'hybridauth_provider_') === 0) {
      unset($form_state['values'][$variable_name]);
      variable_del($variable_name);
    }
  }
}

/**
 * Renders report on HybridAuth identities.
 */
function hybridauth_report() {
  $providers = hybridauth_providers_list();

  $header = array(t('Authentication provider'), t('Users count'));
  $rows = array();
  $query = db_select('hybridauth_identity', 'ha_id');
  $query->addField('ha_id', 'provider', 'provider');
  $query->addExpression('COUNT(provider_identifier)', 'count');
  $query->groupBy('provider');
  $results = $query
    ->execute()
    ->fetchAllAssoc('provider', PDO::FETCH_ASSOC);
  foreach ($results as $result) {
    $rows[] = array(
      $providers[$result['provider']],
      $result['count'],
    );
  }

  $form = array();
  $form['report'] = array(
    '#theme' => 'table',
    '#header' => $header,
    '#rows' => $rows,
    '#empty' => t('There are no HybridAuth identities yet.'),
  );
  return $form;
}
