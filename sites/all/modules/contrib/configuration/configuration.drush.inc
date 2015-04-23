<?php

/**
 * @file
 * configuration.drush.inc
 * Let you perform configuration actions from the console.
 */

use Drupal\configuration\Config\ConfigurationManagement;

/**
 * Implements of hook_drush_help()
 */
function configuration_drush_help($section) {
  switch ($section) {
    case 'drush:cgi':
      return dt("Return the list of identifiers for a given component.");
    case 'drush:cgc':
      return dt("Return the list of configurations components that can be exported.");
    case 'drush:cgt':
      return dt("Return the list of configurations components that are tracked.");
    case 'drush:cgnt':
      return dt("Return the list of configurations components that are not tracked.");
    case 'drush:cexp':
      return dt("Export a configuration to the Data Store.");
    case 'drush:csyn':
      return dt("Synchronize configurations based on config://tracked.inc.");
    case 'drush:csta':
      return dt("Start tracking a configuration changes.");
    case 'drush:csto':
      return dt("Stop tracking a configuration changes.");
    case 'drush:clist':
      return dt("Return the list of components and identifiers.");
  }
}

/**
 * Implements of hook_drush_command().
 */
function configuration_drush_command() {
  $items = array();
  $items['config-get-identifiers'] = array(
    'callback' => '_configuration_get_identifiers',
    'description' => 'Return the list of identifiers for a given component.',
    'aliases' => array('cgi'),
    'examples' => array(
      'drush cgi content_type' => 'Return the list of all available content types to export',
    ),
    'arguments' => array(
      'component' => 'The name of the component to get the identifiers',
    ),
  );
  $items['config-get-components'] = array(
    'callback' => '_configuration_get_components',
    'description' => 'Return the list of configurations components that can be exported.',
    'aliases' => array('cgc'),
    'examples' => array(
      'drush cgc' => 'Return the list of all available configuration components that can be exported',
    ),
    'arguments' => array(),
  );
  $items['config-get-tracked'] = array(
    'callback' => '_configuration_get_tracked',
    'description' => 'Return the list of configurations components that are tracked.',
    'aliases' => array('cgt'),
    'examples' => array(
      'drush cgt' => 'Return the list of configurations components that are tracked.',
    ),
    'arguments' => array(),
  );
  $items['config-get-non-tracked'] = array(
    'callback' => '_configuration_get_not_tracked',
    'description' => 'Return the list of configurations components that are not tracked.',
    'aliases' => array('cgnt'),
    'examples' => array(
      'drush cgnt' => 'Return the list of configurations components that are not tracked.',
    ),
    'arguments' => array(),
  );
  $items['config-export'] = array(
    'callback' => '_configuration_export_to_datastore',
    'description' => 'Export a configuration to the datastore.',
    'aliases' => array('cexp'),
    'examples' => array(
      'drush cexp --all' => 'Export all tracked components',
      'drush cexp content_type.article' => 'Export the content type article and all its dependencies to the datastore',
      'drush cexp content_type.article --exclude-dependencies --exclude-optionals' => 'Export the only the content type article to the datastore  without include its dependencies and optional configurations.',
    ),
    'options' => array(
      'all' => 'Export all tracked components',
      'exclude-dependencies' => 'Export a configuration without export its dependencies.',
      'exclude-optionals' => 'Export a configuration without export its optional configurations.',
      'start-tracking' => 'Export a configuration and automatically start to tracking it.',
    ),
    'arguments' => array(),
  );
  $items['config-start-tracking'] = array(
    'callback' => '_configuration_start_tracking',
    'description' => 'Start tracking configuration changes.',
    'aliases' => array('csta'),
    'examples' => array(
      'drush csta --all' => 'Start tracking changes in all configurations',
      'drush csta content_type.article' => 'Start tracking changes in the content type article and in all its dependencies',
    ),
    'options' => array(
      'all' => 'Start tracking all components',
      'exclude-dependencies' => 'Export a configuration without export its dependencies.',
      'exclude-optionals' => 'Export a configuration without export its optional configurations.',
    ),
    'arguments' => array(),
  );
  $items['config-stop-tracking'] = array(
    'callback' => '_configuration_stop_tracking',
    'description' => 'Stop tracking configuration changes.',
    'aliases' => array('csto'),
    'examples' => array(
      'drush csto --all' => 'Stop tracking changes in all identifiers',
      'drush csto content_type.article' => 'Stop tracking changes in the content type article and in all its dependencies',
    ),
    'options' => array(
      'all' => 'Stop tracking all components',
      'exclude-dependencies' => 'Export a configuration without export its dependencies.',
      'exclude-optionals' => 'Export a configuration without export its optional configurations.',
    ),
    'arguments' => array(),
  );
  $items['config-list'] = array(
    'callback' => '_configuration_list',
    'description' => 'Return the list of components and identifiers',
    'aliases' => array('clist'),
    'examples' => array(
      'drush clist' => 'Return the list of components and identifiers',
    ),
  );
  $items['config-sync'] = array(
    'callback' => '_configuration_sync',
    'description' => 'Synchronize configurations',
    'aliases' => array('csyn'),
    'examples' => array(
      'drush config-sync' => 'Enable all the required modules and the configurations defined in tracked.inc',
      'drush config-sync --preserve-tracked' => 'Will keep tracking the previous configurations even if they are not listed in the tracked.inc',
      'drush csyn --source=%site/myconfig' => 'Synchronize configurations from drupal/sites/example.com/myconfig/tracked.inc',
      'drush csyn --source=myconfig' => 'Synchronize configurations from drupal/myconfig/tracked.inc',
      'drush csyn --source=/path/to/myconfig' => 'Synchronize configurations from /path/to/myconfig/tracked.inc',
    ),
    'options' => array(
      'source' => 'import all the configurations from a given directory.',
      'preserve-tracked' => 'By default, all configurations that are not listed on the tracked.inc file are automatically untracked. Using this options avoid remove the already being tracked configurations.',
    ),
  );
  return $items;
}

/**
 * List of all components and its identifiers.
 */
function _get_component_and_identifier() {
  //get all of the components
  $handlers = ConfigurationManagement::getConfigurationHandler();
  foreach ($handlers as $component => $handler) {
    //get all identifiers from a component
    $identifiers = $handler::getAllIdentifiers($component);
    foreach (array_keys($identifiers) as $identifier) {
      $rows[] = array(
        'handler' => $handler::getComponentHumanName($component),
        'machine_name' => $component,
        'identifier' => $identifier,
      );
    }
  }
  return $rows;
}

/**
 * List of all components and its identifier in the following format
 * component.identifier.
 */
function _components_indentifier_formater() {
  $formated = array();
  $components = _get_component_and_identifier();
  foreach ($components as $component) {
    if (!empty($component['identifier'])) {
      $formated[] =  $component['machine_name'] . "." . $component['identifier'];
    }
  }
  return $formated;
}

/*
 * Configuration list drush command.
 */
function _configuration_list() {
  $header = array();
  $header[] = array(
    'Component::Name',
    'Component::Machine Name',
    'Identifier',
  );
  $content = _get_component_and_identifier();
  $table = array_merge($header, $content);
  drush_print_table($table, TRUE);
}

/*
 * List of all components that are tracked.
 */
function _configuration_get_tracked() {
  $header = array();
  $header[] = array(
    'Component::Machine Name',
    'Identifier',
    'Status',
    'Hash',
  );
  //get tracked file
  $tracked = ConfigurationManagement::trackedConfigurations();
  //format the result
  foreach ($tracked as $component => $list) {
    foreach ($list as $identifier => $hash) {
      //check status
      $configuration = ConfigurationManagement::createConfigurationInstance($component);
      $content[] = array(
        'component' => $component,
        'identifier' => $identifier,
        'status' => $configuration->getStatus(),
        'hash' => $hash,
      );
    }
  }
  if (empty($content)) {
    return drush_print('No tracked components');
  }
  $table = array_merge($header, $content);
  drush_print_table($table, TRUE);
}

/**
 * List of all components that are not tracked.
 */
function _configuration_get_not_tracked() {
  $header = array();
  $header[] = array(
    'Component::Machine Name',
    'Identifier',
  );
  $not_tracked = ConfigurationManagement::nonTrackedConfigurations();

  if (empty($not_tracked)) {
    return drush_print('All components have been tracked');
  }
  foreach ($not_tracked as $component => $list) {
    foreach ($list as $identifier) {
      $content[] = array(
        'component' => $component,
        'identifier' => $identifier['name'],
      );
    }
  }
  $table = array_merge($header, $content);
  drush_print_table($table, TRUE);
}

/**
 * List of all components that are not tracked.
 */
function _configuration_get_identifiers() {
  if ($args = func_get_args()) {
    if (empty($args)) {
      return drush_set_error('', 'No components supplied.');
    }
  }
  $component = array_shift($args);
  $handlers = ConfigurationManagement::getConfigurationHandler();
  if (empty($handlers[$component])) {
    return drush_set_error('', dt('The component @component is not valid or the module to export the configuration is not installed.', array('@component' => $component)));
  }
  $handler = $handlers[$component];
  $identifiers = $handler::getAllIdentifiers($component);
  $rows = array();
  foreach ($identifiers as $identifier) {
    $rows[]['identifier'] = $identifier;
  }
  drush_print_table($rows);
}

function _configuration_get_components() {
  $handlers = ConfigurationManagement::getConfigurationHandler();
  $rows = array();
  $rows[] = array(
    'Name',
    'Machine Name'
  );
  foreach ($handlers as $component => $handler) {
    $rows[] = array(
      'handler' => $handler::getComponentHumanName($component),
      'machine_name' => $component,
    );
  }
  drush_print_table($rows, TRUE);
}

function _configuration_export_to_datastore() {
  $export_all = drush_get_option('all');
  if (!empty($export_all)) {
    $args = _components_indentifier_formater();
  }
  else {
    $args = func_get_args();
  }

  if (empty($args)) {
    return drush_set_error('', dt('No configurations were selected to export'));
  }
  $handlers = ConfigurationManagement::getConfigurationHandler();
  foreach ($args as $arg) {
    list($component, $identifier) = explode('.', $arg, 2);
    if (empty($handlers[$component])) {
      return drush_set_error('', dt('The component @component is not valid or the module to export the configuration is not installed.', array('@component' => $component)));
    }
    if (empty($identifier)) {
      return drush_set_error('', dt('No identifier supplied for @arg.', array('@arg' => $arg)));
    }
  }

  $rows = array();
  $export_dependencies = !drush_get_option('exclude-dependencies');
  $export_optionals = !drush_get_option('exclude-optionals');
  $start_tracking = drush_get_option('start-tracking');

  $result = ConfigurationManagement::exportToDataStore($args, $export_dependencies, $export_optionals, $start_tracking);
  $hashes = $result->getInfo('hash');
  foreach ($result->getInfo('exported') as $id => $exported) {
    $rows[] = array(
      'exported' => $exported,
      'hash' => $hashes[$id],
    );
  }

  drush_log(dt('The configurations for the following items have been created/updated.'), 'ok');
  drush_print_table($rows);
}

function _configuration_start_tracking() {
  $start_tracking_all = drush_get_option('all');
  if (!empty($start_tracking_all)) {
    $args = _components_indentifier_formater();
  }
  else {
    $args = func_get_args();
  }

  if (empty($args)) {
    return drush_set_error('', dt('No configurations were selected to track'));
  }

  $handlers = ConfigurationManagement::getConfigurationHandler();
  foreach ($args as $arg) {

    list($component, $identifier) = explode('.', $arg, 2);

    if (empty($handlers[$component])) {
      return drush_set_error('', dt('The component @component is not valid or the module to track the configuration is not installed.', array('@component' => $component)));
    }

    if (empty($identifier)) {
      return drush_set_error('', dt('No identifier supplied for @arg.', array('@arg' => $arg)));
    }
  }
  $rows = array();
  $start_tracking_dependencies = !drush_get_option('exclude-dependencies');
  $start_tracking_optionals = !drush_get_option('exclude-optionals');
  $result = ConfigurationManagement::startTracking($args, $start_tracking_dependencies, $start_tracking_optionals);
  foreach ($result->getInfo('exported') as $tracking) {
    $rows[]['exported'] = $tracking;
  }

  drush_log(dt('The configurations changes for for the following items are now being tracked.'), 'ok');
  drush_print_table($rows);
}

function _configuration_stop_tracking() {
  $stop_tracking_all = drush_get_option('all');
  if (!empty($stop_tracking_all)) {
    $args = _components_indentifier_formater();
  }
  else {
    $args = func_get_args();
  }
  if (empty($args)) {
    return drush_set_error('', dt('No configurations were selected to export'));
  }

  $handlers = ConfigurationManagement::getConfigurationHandler();
  foreach ($args as $arg) {

    list($component, $identifier) = explode('.', $arg, 2);

    if (empty($handlers[$component])) {
      return drush_set_error('', dt('The component @component is not valid or the module to export the configuration is not installed.', array('@component' => $component)));
    }

    if (empty($identifier)) {
      return drush_set_error('', dt('No identifier supplied for @arg.', array('@arg' => $arg)));
    }
  }
  $rows = array();
  $stop_tracking_dependencies = !drush_get_option('exclude-dependencies');
  $stop_tracking_optionals = !drush_get_option('exclude-optionals');
  $result = ConfigurationManagement::stopTracking($args, $stop_tracking_dependencies, $stop_tracking_optionals);
  $configurations = $result->getInfo('untracked');
  if (!empty($configurations)) {
    foreach ($configurations as $untracked) {
      $rows[]['untracked'] = $untracked;
    }
    drush_log(dt('The configurations changes for for the following items are not being tracked anymore.'), 'ok');
    drush_print_table($rows);
  }
}

function _configuration_sync() {
  $tracking_file = ConfigurationManagement::readTrackingFile();
  $to_track = array_keys($tracking_file['tracked']);
  $modules_results = ConfigurationManagement::discoverRequiredModules($tracking_file['modules']);

  $missing_modules = $modules_results->getInfo('missing_modules');

  if (!empty($missing_modules)) {
    drush_log(dt('Configurations cannot be synchronized because the following modules are not available to install: %modules', array('%modules' => implode(', ', $missing_modules))), 'error');
    $error = TRUE;
  }
  else {
    $modules_to_install = $modules_results->getInfo('modules_to_install');
    drush_log(dt('The following will be enabled: %modules', array('%modules' => implode(', ', $modules_to_install))));
    if (!empty($modules_to_install)) {
      module_enable($modules_to_install, TRUE);
      drush_log(dt('The following modules have been enabled: %modules', array('%modules' => implode(', ', $modules_to_install))));
    }

  }

  // If we have to stop stracking some configurations, figure out which
  // configurations have to be untracked.
  if (drush_get_option('preserve-tracked')) {

    $being_tracked = array_keys(ConfigurationManagement::trackedConfigurations(FALSE));

    $to_untrack = array_diff($being_tracked, $to_track);

    $un_track_results = ConfigurationManagement::stopTracking($to_untrack, TRUE, TRUE);

    if (count($un_track_results->getInfo('untracked'))) {
      foreach ($un_track_results->getInfo('untracked') as $untracked_config) {
        drush_log(dt('Untracked %config', array('%config' => $untracked_config)));
      }
    }
  }

  $results = ConfigurationManagement::importToActiveStore($to_track, FALSE, FALSE, TRUE);
  foreach ($results->getInfo('imported') as $imported) {
    drush_log(dt('Imported !config', array('!config' => $imported)), 'ok');
  }
  foreach ($results->getInfo('no_handler') as $failed) {
    drush_log(dt('!config could be imported because there is no module that can handle that configuration.', array('!config' => $failed)), 'error');
  }
}
