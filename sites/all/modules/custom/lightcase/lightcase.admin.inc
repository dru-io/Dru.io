<?php

/**
 * @file
 * Administration page callbacks for the Lightcase module.
 */

function lightcase_admin_settings_form($form, &$form_state) {
  $library = libraries_detect('lightcase');
  $settings = _lightcase_array_replace_recursive(_lightcase_defaults(), variable_get('lightcase_settings'));

  if (!$library['installed']) {
    if ($library['error'] == 'not found' || $library['error'] == 'not detected') {
      drupal_set_message(t('!error Please make sure the library is !installedcorrectly.', array(
        '!error' => $library['error message'],
        '!installedcorrectly' => l(t('installed correctly'), 'admin/help/lightcase'),
      )), 'error');
    }
    else {
      drupal_set_message(t('A conflict has been detected between Libraries API and the Lightcase module. Please make sure both modules are !updated to their latest versions. Also, make sure your Lightcase plugin is !uptodate and is !installedcorrectly. If this error persists, please submit a support request to the !issuequeue.', array(
        '!error' => $library['error message'],
        '!uptodate' => l(t('up-to-date'), 'http://cornel.bopp-art.com/lightcase/'),
        '!installedcorrectly' => l(t('installed correctly'), 'admin/help/lightcase'),
        '!updated' => l(t('updated'), 'admin/reports/updates'),
        '!issuequeue' => l(t('issue queue'), 'http://drupal.org/project/issues/lightcase'),
      )), 'error');
    }

    return;
  }

  $jquery = drupal_get_library('system', 'jquery');

  if (version_compare($jquery['version'], '1.7', '<')) {
    drupal_set_message(t('Lightcase requires jQuery 1.7 or higher. Please !configure to use a higher jQuery version.', array('!configure' => l(t('configure jQuery Update'), 'admin/config/development/jquery_update'))), 'error');

    return;
  }

  $form['settings'] = array(
    '#type' => 'fieldset',
    '#title' => t('Settings'),
    '#description' => t('These settings affect how Lightcase is used in your Drupal installation.'),
    '#collapsible' => TRUE,
    '#collapsed' => FALSE,
    '#tree' => TRUE,
  );

  $form['settings']['selectors'] = array(
    '#type' => 'textarea',
    '#title' => t('Lightcase Selectors'),
    '#description' => t('Lightcase will automatically be applied to the HTML elements represented by these jQuery selectors. List one selector per line.<br /><strong>Examples</strong>: <em>a:has(img)</em>, <em>.my_gallery a</em>, etc.'),
    '#default_value' => $settings['settings']['selectors'],
  );

  $form['settings']['deactivated_pages'] = array(
    '#type' => 'textarea',
    '#title' => t('Deactivate Lightcase on specific pages'),
    '#description' => t('Enter one page per line as Drupal paths. The \'*\' character is a wildcard. Example paths are %blog for the blog page and %blog-wildcard for every personal blog. %front is the front page.', array('%blog' => 'blog', '%blog-wildcard' => 'blog/*', '%front' => '<front>')),
    '#default_value' => $settings['settings']['deactivated_pages'],
  );

  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Save configuration'),
  );

  return $form;
}

/**
 * Submit handler for the Lightcase Admin Settings Form.
 *
 * @see lightcase_admin_settings_form().
 */
function lightcase_admin_settings_form_submit(&$form, &$form_state) {
  $settings = array();

  foreach ($form_state['values'] as $key => &$value) {
    if (!in_array($key, array(
      'submit',
      'form_build_id',
      'form_token',
      'form_id',
      'op',
    ))
    ) {
      $settings[$key] = $value;
    }
  }

  array_walk_recursive($settings, '_lightcase_array_walk_convert_string');

  if ($settings['settings']['selectors']) {
    $selectors = explode("\r\n", trim($settings['settings']['selectors']));

    foreach ($selectors as &$selector) {
      $selector = trim($selector, ',');
    }

    $settings['settings']['selectors'] = implode("\r\n", $selectors);
  }

  drupal_set_message(t('The configuration options have been saved.'));
  variable_set('lightcase_settings', $settings);
}
