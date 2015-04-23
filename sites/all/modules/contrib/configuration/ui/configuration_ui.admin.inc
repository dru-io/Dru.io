<?php

/**
 * @file
 * User interface functions for Configuration Management.
 */

use Drupal\configuration\Config\ConfigurationManagement;
use Drupal\configuration\Utils\ConfigIteratorSettings;

function configuration_ui_tracking_form($form, &$form_state) {
  $configurations = ConfigurationManagement::trackedConfigurations();

  $form_state['table_header'] = array(
    'names' => t('Configuration'),
    'status' => t('Status'),
    'operations' => t('Operations'),
  );

  configuration_ui_configuration_list('tracking', $configurations, $form, $form_state);

  if (!empty($form_state['component_exists'])) {
    $form['buttons'] = array('#theme' => 'configuration_ui_form_buttons', '#tree' => FALSE);
    // Do not allow writing to datastore if on remote server.
    if (variable_get('configuration_remote_server', 0) < 1) {
      $form['buttons']['submit'] = array(
        '#type' => 'submit',
        '#value' => t('Write Activestore to Datastore'),
        '#submit' => array('configuration_ui_export_to_datastore_form_submit'),
      );
    }
    else {
      $form['buttons']['export'] = array(
        '#type' => 'submit',
        '#value' => t('Export All Tracked Configurations'),
        '#submit' => array('configuration_ui_export_traked_to_tar_file_form_submit'),
      );
    }
    $form['buttons']['stop_tracking'] = array(
      '#type' => 'submit',
      '#value' => t('Stop Tracking'),
      '#submit' => array('configuration_ui_stop_tracking_form_submit'),
    );
    $form['buttons']['import'] = array(
      '#type' => 'submit',
      '#value' => t('Import Datastore to Activestore'),
      '#submit' => array('configuration_ui_activate_form_submit'),
    );
  }
  else {
    $form['no_configs'] = array(
      '#markup' => t('No Configurations were found. Please use the
      !export_link page to begin tracking new Configurations.',
          array('!export_link' => l(t('Not Tracking'), 'admin/config/system/configuration/notracking'))),
    );
  }
  return $form;
}

function configurations_ui_operations_helper($ui, $id) {
  switch ($ui) {
    case 'tracking':
      return l(t('Stop Tracking'), 'admin/config/system/configuration/stop_tracking/' . $id) . ' | ' . l(t('View info'), 'admin/config/system/configuration/view/' . $id);
      break;

    case 'no_tracking':
      return l(t('Start Tracking'), 'admin/config/system/configuration/start_tracking/' . $id) . ' | ' . l(t('View info'), 'admin/config/system/configuration/view/' . $id);

      break;

    case 'export':
      return l(t('View info'), 'admin/config/system/configuration/view/' . $id);
      break;
  }
}

/**
 * Menu Callback Form.
 */
function configuration_ui_notracking_form($form, &$form_state) {
  $configurations = ConfigurationManagement::nonTrackedConfigurations();

  $form_state['table_header'] = array(
    'names' => t('Configuration'),
    'operations' => t('Operations'),
  );
  configuration_ui_configuration_list('no_tracking', $configurations, $form, $form_state);

  $form['buttons'] = array('#theme' => 'configuration_ui_form_buttons', '#tree' => FALSE);
  $form['buttons']['start_tracking'] = array(
    '#type' => 'submit',
    '#value' => t('Start Tracking'),
    '#submit' => array('configuration_ui_start_tracking_form_submit'),
  );
  return $form;
}

function configuration_ui_export_form($form, &$form_state) {
  $configurations = ConfigurationManagement::allConfigurations();

  $form_state['table_header'] = array(
    'names' => t('Configuration'),
    'status' => t('Tracked'),
    'operations' => t('Operations'),
  );
  configuration_ui_configuration_list('export', $configurations, $form, $form_state);

  $form['buttons'] = array('#theme' => 'configuration_ui_form_buttons', '#tree' => FALSE);
  $form['buttons']['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Export to Tar File'),
    '#submit' => array('configuration_ui_export_to_tar_file_form_submit'),
  );
  return $form;
}
/**
 * Helper function for configuration lists.
 */
function configuration_ui_configuration_list($ui, $configurations, &$form, &$form_state) {
  $component_exists = FALSE;
  $handlers = configuration_configuration_handlers();

  $form['packages'] = array('#type' => 'vertical_tabs');
  $form['#attached']['css'] = array(
    drupal_get_path('module', 'configuration_ui') . '/theme/configuration.css'
  );
  $form['#attached']['js'] = array(
    drupal_get_path('module', 'configuration_ui') . '/js/configuration_ui.js'
  );

  $form_state['component_exists'] = FALSE;
  foreach ($configurations as $component => $list) {
    $handler = ConfigurationManagement::getConfigurationHandler($component);
    if (empty($handler) || count($list) < 1) {
      continue;
    }
    $form[$component] = array(
      '#type' => 'fieldset',
      '#group' => 'packages',
      '#title' => check_plain($handler::getComponentHumanName($component)),
      '#description' => '<span class= "component" rel="' . check_plain($component) . '">' .  t('Configurations for: @component', array('@component' => $handler::getComponentHumanName($component, TRUE))) . '</span>',
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
      '#tree' => TRUE,
      '#attached' => array(
        'js' => array(
          'vertical-tabs' => drupal_get_path('module', 'configuration_ui') . '/theme/vertical-tabs.js',
        ),
      ),
      '#attributes' => array('class' => array('configuration')),
    );

    $form[$component]['configurations'] = array(
      '#tree' => FALSE,
      '#theme' => 'configuration_table',
      'items' => array(
        '#tree' => TRUE,
      ),
      'name' => array(
        '#tree' => TRUE,
      ),
      'status' => array(
        '#tree' => TRUE,
      ),
      'operations' => array(
        '#tree' => TRUE,
      ),
    );

    $form[$component]['configurations']['#header'] = $form_state['table_header'];

    foreach ($list as $identifier => $info) {
      $form_state['component_exists'] = TRUE;
      $id = $component . '.' . $identifier;

      $form[$component]['configurations']['items'][$id] = array(
        '#type' => 'checkbox',
        '#title' => check_plain($info['name']),
        '#title_display' => 'invisible',
        '#return_value' => $id,
      );

      $form[$component]['configurations']['names'][$id] = array(
        '#markup' => check_plain($info['name']),
      );

      if ($ui == 'tracking') {
        $form[$component]['configurations']['status'][$id] = array(
          '#markup' => '<span class ="config-status" rel="' . $id . '">' . t('Processing...') . '</span>',
        );
      }
      elseif ($ui == 'export') {
        $form[$component]['configurations']['status'][$id] = array(
          '#markup' => $info['hash'] ? t('Tracked') : t('Not tracked'),
        );
      }

      $form[$component]['configurations']['operations'][$id] = array(
        '#markup' => configurations_ui_operations_helper($ui, $id),
      );
    }
    asort($form[$component]['configurations']['items']);
  }

  if (!empty($form_state['component_exists'])) {
    $form['dependencies_option'] = array(
      '#type' => 'fieldset',
      '#title' => t('Dependencies and Optionals'),
      '#description' => t('Choose if you want to process dependent and optional components.'),
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
    );
    $form['dependencies_option']['include_dependencies'] = array(
      '#type' => 'checkbox',
      '#title' => t('Process component dependencies.'),
      '#default_value' => TRUE,
    );
    $form['dependencies_option']['include_optionals'] = array(
      '#type' => 'checkbox',
      '#title' => t('Process component optionals.'),
      '#default_value' => TRUE,
    );
  }

}

/**
 * Menu Callback Form.
 */
function configuration_ui_config_info($config_id) {
  static $diff_exists;

  if (!isset($diff_exists)) {
    $diff_exists = module_exists('diff');
  }

  $config = ConfigurationManagement::createConfigurationInstance($config_id);

  $config->loadFromActiveStore();
  $identifiers = $config->getAllIdentifiers($config->getComponent());

  $status = check_plain($config->getStatus());
  if ($diff_exists && $status != t('In Sync')) {
    $status = l(check_plain($status), 'admin/config/system/configuration/diff/' . arg(5), array('attributes' => array('title' => t('View differences'))));
  }
  if (arg(6) == 'status') {
    print $status;
    exit();
  }
  if (arg(6) == 'dependencies') {
    ConfigurationManagement::rawDepdendencyInfo(array($config_id), TRUE, FALSE);
    exit();
  }
  if (arg(6) == 'optionals') {
    ConfigurationManagement::rawDepdendencyInfo(array($config_id), FALSE, TRUE);
    exit();
  }
  if (arg(6) == 'dependencies_optionals') {
    ConfigurationManagement::rawDepdendencyInfo(array($config_id), TRUE, TRUE);
    exit();
  }

  $page = '';

  $page .= '<h2>' . t('Configuration for: @config', array('@config' => $identifiers[$config->getIdentifier()])) . '</h2>';
  $page .= '<p>' . t('Identifier: @hash', array('@hash' => $config->getIdentifier())) . '</p>';
  $page .= '<p>' . t('Hash: @hash', array('@hash' => $config->getHash())) . '</p>';

  $page .= '<p>' . t('Status: <strong>!status</strong>', array('!status' => $status)) . '</p>';

  $dependencies = array('items' => array_keys($config->getDependencies()));
  if (!empty($dependencies['items'])) {
    $page .= '<p>' . t('Dependencies:') . '</p>';
    $page .= theme('item_list', $dependencies);
  }
  else {
    $page .= '<p>' . t('No Dependencies') . '</p>';
  }

  $optionals = array('items' => array_keys($config->getOptionalConfigurations()));
  if (!empty($optionals['items'])) {
    $page .= '<p>' . t('Optional Configurations:') . '</p>';
    $page .= theme('item_list', $optionals);
  }
  else {
    $page .= '<p>' . t('No Optional Configurations') . '</p>';
  }

  return $page;
}

/**
 * Menu Callback: Return the status for each tracked configuration.
 */
function configuration_ui_config_status($component) {
  $components = ConfigurationManagement::getConfigurationHandler();
  if (empty($components[$component])) {
    print '';
    exit();
  }

  $tracked_in_file = ConfigurationManagement::readTrackingFile();

  $tracked = ConfigurationManagement::trackedConfigurations();
  $status = array();
  $needs_attention = 0;
  foreach ($tracked as $component_id => $info) {
    if ($component_id == $component) {
      foreach ($info as $identifier => $item) {
        $id = $component . '.' . $identifier;
        $config = ConfigurationManagement::createConfigurationInstance($id);
        $hash = $config->loadFromActiveStore()->getHash();
        if ($config->isBroken()) {
          $status[$id] = t('Removed from Active Store');
        }
        elseif (empty($tracked_in_file['tracked'][$id])) {
          $status[$id] = t('Active Store Only');
        }
        elseif ($hash != $item['hash']) {
          $status[$id] = t('Overriden');
        }
        else {
          $status[$id] = t('In Sync');
        }

        if ($status[$id] != t('In Sync')) {
          $needs_attention++;
        }

        unset($config);
      }
      break;
    }
  }

  $return = array(
    'status' => $status,
    'diff' => module_exists('diff'),
    'needs_attention' => $needs_attention,
  );

  print drupal_json_encode($return);
  exit();
}

/**
 * Menu Callback Form.
 */
function configuration_ui_config_diff($config_id) {
  $activestore = ConfigurationManagement::createConfigurationInstance($config_id);
  $datastore = ConfigurationManagement::createConfigurationInstance($config_id);

  drupal_add_css(drupal_get_path('module', 'configuration_ui') . '/theme/configuration.css');

  $output = '';

  module_load_include('inc', 'diff', 'diff.engine');
  $formatter = new DrupalDiffFormatter();

  $formatter->leading_context_lines = 2;
  $formatter->trailing_context_lines = 2;

  $rows[] = array();

  $diff = new Diff(explode("\n", $datastore->loadFromStorage()->raw()), explode("\n", $activestore->loadFromActiveStore()->raw()));
  $rows = array_merge($rows, $formatter->format($diff));

  $header = array(
    array('data' => t('Datastore'), 'colspan' => 2),
    array('data' => t('Activestore'), 'colspan' => 2),
  );
  $output .= theme('table', array('header' => $header, 'rows' => $rows, 'attributes' => array('class' => array('diff', 'configuration-diff'))));

  $output = array('page' => array('#markup' => "<div class='configuration-comparison'>{$output}</div>"));
  return $output;
}

/**
 * Submit handler for reverting configs.
 */
function configuration_ui_activate_form_submit($form, &$form_state) {
  $args = configuration_ui_get_form_components($form, $form_state);
  $revert_dependencies = $form_state['values']['include_dependencies'];
  $revert_optionals = $form_state['values']['include_optionals'];
  $result = ConfigurationManagement::importToActiveStore($args, $revert_dependencies, $revert_optionals);
  $imported_configurations = $result->getInfo('imported');
  if (!empty($imported_configurations)) {
    foreach ($imported_configurations as $configuration) {
      drupal_set_message(t('Reverted @configuration.', array('@configuration' => $configuration)));
    }
  }
  $non_imported_configurations =  $result->getInfo('fail');
  if (!empty($non_imported_configurations)) {
    foreach ($non_imported_configurations as $configuration) {
      drupal_set_message(t('@configuration could not be imported.', array('@configuration' => $configuration)), 'error');
    }
  }
}

/**
 * Submit handler for stop tracking configs.
 */
function configuration_ui_stop_tracking_form_submit($form, &$form_state) {
  $args = configuration_ui_get_form_components($form, $form_state);
  $stop_tracking_dependencies = $form_state['values']['include_dependencies'];
  $stop_tracking_optionals = $form_state['values']['include_optionals'];
  $result = ConfigurationManagement::stopTracking($args, $stop_tracking_dependencies, $stop_tracking_optionals);

  $configurations = $result->getInfo('untracked');
  if (!empty($configurations)) {
    foreach ($configurations as $configuration) {
      drupal_set_message(t('Untracked @configuration', array('@configuration' => $configuration)));
    }
  }
}

/**
 * Confirm form for stop tracking configs.
 */
function configuration_ui_stop_tracking_single_confirm($form, &$form_state, $component) {
  $form['component_id'] = array('#type' => 'value', '#value' => $component);
  return confirm_form($form,
      t('Are you sure you want to stop tracking %component?', array('%component' => $component)),
      'admin/config/system/configuration',
      t('You can start tracking it again later.'),
      t('Stop Tracking'),
      t('Cancel')
  );
}

/**
 * Submit handler for stop tracking a single config.
 */
function configuration_ui_stop_tracking_single_confirm_submit($form, &$form_state) {
  if ($form_state['values']['confirm']) {
    $args = array($form_state['values']['component_id']);
    $stop_tracking_dependencies = FALSE;
    $stop_tracking_optionals = FALSE;
    $result = ConfigurationManagement::stopTracking($args, $stop_tracking_dependencies, $stop_tracking_optionals);
  }
  $form_state['redirect'] = 'admin/config/system/configuration';
}

/**
 * Confirm form for start tracking configs.
 */
function configuration_ui_start_tracking_single_confirm($form, &$form_state, $component) {
  $form['component_id'] = array('#type' => 'value', '#value' => $component);
  return confirm_form($form,
      t('Are you sure you want to start tracking %component?', array('%component' => $component)),
      'admin/config/system/configuration/notracking',
      t('You can stop tracking it later.'),
      t('Start Tracking'),
      t('Cancel')
  );
}

/**
 * Submit handler for start tracking a single config.
 */
function configuration_ui_start_tracking_single_confirm_submit($form, &$form_state) {
  if ($form_state['values']['confirm']) {
    $args = array($form_state['values']['component_id']);
    $start_tracking_dependencies = FALSE;
    $start_tracking_optionals = FALSE;
    $result = ConfigurationManagement::startTracking($args, $start_tracking_dependencies, $start_tracking_optionals);
  }
  $form_state['redirect'] = 'admin/config/system/configuration/notracking';
}

/**
 * Submit handler for export to datastore.
 */
function configuration_ui_export_to_datastore_form_submit($form, &$form_state) {
  $args = configuration_ui_get_form_components($form, $form_state);
  $export_dependencies = $form_state['values']['include_dependencies'];
  $export_optionals = $form_state['values']['include_optionals'];
  $result = ConfigurationManagement::exportToDataStore($args, $export_dependencies, $export_optionals, TRUE);
  $configurations = $result->getInfo('exported');
  if (!empty($configurations)) {
    foreach ($configurations as $configuration) {
      drupal_set_message(t('Exported @configuration.', array('@configuration' => $configuration)));
    }
  }
}

/**
 * Submit handler for start tracking configs.
 */
function configuration_ui_start_tracking_form_submit($form, &$form_state) {
  $args = configuration_ui_get_form_components($form, $form_state);
  $start_tracking_dependencies = $form_state['values']['include_dependencies'];
  $start_tracking_optionals = $form_state['values']['include_optionals'];
  $result = ConfigurationManagement::startTracking($args, $start_tracking_dependencies, $start_tracking_optionals);
  $configurations = $result->getInfo('exported');
  if (!empty($configurations)) {
    foreach ($configurations as $configuration) {
      drupal_set_message(t('Tracking @configuration.', array('@configuration' => $configuration)));
    }
  }
}

/**
 * Menu Callback Form.
 */
function configuration_ui_import_form($form, &$form_state) {
  $form['header'] = array(
    '#markup' => t('Importing configurations will write your configurations directly to the activestore. DO NOT USE THIS ON A PRODUCTION SITE!'),
    '#prefix' => '<div>',
    '#suffix' => '</div>',
  );

  $form['import_configurations'] = array(
    '#type' => 'file',
    '#title' => t('Import configurations'),
    '#description' => t('Upload the file you exported from another site.'),
    '#size' => 40,
  );

  $form['import'] = array(
    '#type' => 'submit',
    '#value' => t('Import'),
  );

  return $form;
}

/**
 * Submit handler for export to a tar file.
 */
function configuration_ui_export_to_tar_file_form_submit($form, &$form_state) {
  $args = configuration_ui_get_form_components($form, $form_state);
  $export_dependencies = $form_state['values']['include_dependencies'];
  $export_optionals = $form_state['values']['include_optionals'];
  ConfigurationManagement::exportAsTar($args, $export_dependencies, $export_optionals);
}

/**
 * Submit handler for export tracked configurations to a tar file.
 */
function configuration_ui_export_traked_to_tar_file_form_submit($form, &$form_state) {
  $args = array();
  //get tracked file
  $tracked = ConfigurationManagement::trackedConfigurations();
  //format the result
  foreach ($tracked as $component => $list) {
    foreach ($list as $identifier => $hash) {
      $args[] = $component . '.' . $identifier;
    }
  }
  $export_dependencies = $form_state['values']['include_dependencies'];
  $export_optionals = $form_state['values']['include_optionals'];
  ConfigurationManagement::exportAsTar($args, $export_dependencies, $export_optionals);
}

/**
 * Submit handler for importing configs.
 */
function configuration_ui_import_form_submit($form, &$form_state) {

  $validators = array('file_validate_extensions' => array('tar'));
  if ($file = file_save_upload('import_configurations', $validators)) {
    $settings = ConfigurationManagement::importToActiveStoreFromTar($file->uri);

    file_delete($file);
    $imported = $settings->getInfo('imported');
    if (!empty($imported)) {
      drupal_set_message(t('The following configurations have been migrated into the ActiveStore:'));
      $imported = $optionals = array('items' => $imported);
      drupal_set_message(filter_xss(theme('item_list', $imported), array('ul', 'li')));
    }
  }
}

/**
 * Menu Callback Form.
 */
function configuration_ui_sync_form($form, &$form_state) {

  $form['configuration_ops'] = array(
    '#type' => 'fieldset',
    '#title' => t('Configuration Operations'),
    '#collapsible' => TRUE,
    '#collapsed' => FALSE,
    '#description' => '<p>' . t('Updates the tracked configurations using the configurations defined in tracked.inc file. You should click on this button every time you update the DataStore and want to see the changes materialized in your site.') . '<p>',
  );
  $form['configuration_ops']['sync_configurations'] = array(
    '#type' => 'submit',
    '#value' => t('Synchronize configurations'),
    '#submit' => array('configuration_sync_configurations_submit'),
  );
  $form['configuration_ops']['preserve_tracked'] = array(
    '#type' => 'checkbox',
    '#title' => t('Preserve tracked configurations.'),
    '#description' => '<p>' . t('If checked, all the configurations current being tracked will still be tracked even if they are not in listed in the tracked.inc file of your DataStore. If not checked, those configurations that are not listed in the configuration.inc file and are currently being tracked will be automatically untracked.') . '<p>',
    '#default_value' => TRUE,
  );

  return $form;
}

/**
 * Menu Callback Form.
 */
function configuration_ui_settings_form($form, &$form_state) {

  $form['general_settings'] = array(
    '#type' => 'fieldset',
    '#title' => t('General Settings'),
    '#collapsible' => TRUE,
    '#collapsed' => FALSE,
  );

  $form['general_settings']['configuration_config_path'] = array(
    '#type' => 'textfield',
    '#title' => t('Configuration directory configs are stored in.'),
    '#description' => t('Configuration directory to store config files. This defaults to inside the files directory for easy write access. Since most users ignore the files directory in their VCS, you may want to set this outside of your files directory so that configs are included in version control.'),
    '#size' => 40,
    '#maxlength' => 255,
    '#default_value' => variable_get('configuration_config_path', conf_path() . '/files/config'),
  );

  $form['general_settings']['configuration_remote_server'] = array(
    '#type' => 'checkbox',
    '#title' => t('This is a remote server'),
    '#description' => t('If this is a remote server and you do not have access to the filesystem of this server, the ability to write files to datastore should not be allowed.'),
    '#default_value' => variable_get('configuration_remote_server', -1),
  );

  $form['configuration_exclude'] = array(
    '#type' => 'fieldset',
    '#title' => t('Exclude Configurations'),
    '#collapsible' => TRUE,
    '#collapsed' => FALSE,
    '#description' => t('There are some configurations, specially variables that are closely related to the current site. Like <em>cron_key</em> or <em>drupal_private_key</em>. You can use this list to exclude those configurations from being handler from Configuration Management module.'),
  );

  $form['configuration_exclude']['configuration_exclude_configurations'] = array(
    '#type' => 'textarea',
    '#title' => t('Configurations to exclude.'),
    '#description' => t('One configuration to exclude per line, no spaces allowed. Use the format: component.identifier (i.e: variable.cron_key).'),
    '#size' => 6,
    '#default_value' => variable_get('configuration_exclude_configurations', ''),
  );

  $form['#validate'][] = 'configuration_ui_validate_settings';
  return system_settings_form($form);
}

function configuration_ui_validate_settings($form, &$form_state) {
  $form_element = $form['general_settings']['configuration_config_path'];

  if (empty($form_state['values']['configuration_remote_server'])) {
    system_check_directory($form_element);
  }
  else {
    $directory = $form_state['values']['configuration_config_path'];
    if (!is_dir($directory)) {
      // If the directory does not exists and cannot be created.
      form_set_error($form_element['#parents'][0], t('The directory %directory does not exist.', array('%directory' => $directory)));
      watchdog('file system', 'The directory %directory does not exist.', array('%directory' => $directory), WATCHDOG_ERROR);
    }
  }
}

/**
 * Get a list of components to be processed.
 */
function configuration_ui_get_form_components($form, &$form_state) {
  $component_list = array();
  foreach (array_filter($form_state['values']['items']) as $component) {
    $component_list[] = $component;
  }
  return $component_list;
}


function configuration_sync_configurations_submit($form, &$form_state) {
  $tracking_file = ConfigurationManagement::readTrackingFile();
  if (empty($tracking_file)) {
    $path = drupal_realpath(ConfigurationManagement::getStream());
    drupal_set_message(t('The Tracking file is not present. The synchronization can not be performed.'), 'error');
    return FALSE;
  }
  $to_track = array_keys($tracking_file['tracked']);
  $modules_results = ConfigurationManagement::discoverRequiredModules($tracking_file['modules']);

  $missing_modules = $modules_results->getInfo('missing_modules');

  if (!empty($missing_modules)) {
    drupal_set_message(t('Configurations cannot be synchronized because the following modules are not available to install: %modules', array('%modules' => implode(', ', $missing_modules))), 'error');
    $error = TRUE;
  }
  else {
    $modules_to_install = $modules_results->getInfo('modules_to_install');
    drupal_set_message(t('The following will be enabled: %modules', array('%modules' => implode(', ', $modules_to_install))));
    if (!empty($modules_to_install)) {
      module_enable($modules_to_install, TRUE);
      drupal_set_message(t('The following modules have been enabled: %modules', array('%modules' => implode(', ', $modules_to_install))));
    }

  }

  // If we have to stop stracking some configurations, figure out which
  // configurations have to be untracked.
  if (empty($form_state['values']['preserve_tracked'])) {

    $being_tracked = array_keys(ConfigurationManagement::trackedConfigurations(FALSE));

    $to_untrack = array_diff($being_tracked, $to_track);

    $un_track_results = ConfigurationManagement::stopTracking($to_untrack, TRUE, TRUE);

    if (count($un_track_results->getInfo('untracked'))) {
      foreach ($un_track_results->getInfo('untracked') as $untracked_config) {
        drupal_set_message(t('Untracked %config', array('%config' => $untracked_config)));
      }
    }
  }

  $results = ConfigurationManagement::importToActiveStore($to_track, FALSE, FALSE, TRUE);
  foreach ($results->getInfo('imported') as $imported) {
    drupal_set_message(t('Imported %config', array('%config' => $imported)));
  }
  foreach ($results->getInfo('no_handler') as $failed) {
    drupal_set_message(t('%config could be imported because there is no module that can handle that configuration.', array('%config' => $failed)));
  }
}
