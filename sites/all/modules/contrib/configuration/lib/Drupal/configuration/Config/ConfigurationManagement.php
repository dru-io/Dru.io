<?php

/**
 * @file
 * Definition of Drupal\configuration\Config\ConfigurationManagement.
 */

namespace Drupal\configuration\Config;

use \StdClass;
use Drupal\configuration\Config\Configuration;
use Drupal\configuration\Utils\ConfigIteratorSettings;
use Drupal\configuration\Storage\Storage;

class ConfigurationManagement {

  /**
   * Constant used to indicate the api version of Configuration Management.
   */
  const api = '2.0.0';

  /**
   * The stream to use while importing and exporting configurations.
   *
   * @var string
   */
  static protected $stream = 'config://';

  /**
   * Returns TRUE if the minimun
   *
   * @param $minimum
   *   The version of the api of the current configuration.
   *
   * @return  boolean
   *   Rerturns TRUE if the current version of Configuration Management
   *   installed can handle the configuration.
   */
  static public function validApiVersion($config_version) {
    return (version_compare($config_version, ConfigurationManagement::api) >= 0);
  }

  /**
   * Returns the current stream used to import and export configurations.
   * Default value is config://
   *
   * @return string
   */
  public static function getStream() {
    $temp_stream = static::$stream;
    // During the install process strema wrappers are to available so this is
    // a work around.
    if (!file_stream_wrapper_get_instance_by_uri($temp_stream)) {
      $temp_stream = variable_get('configuration_config_path', conf_path() . '/files/config');
    }
    return $temp_stream;
  }

  /**
   * Set the stream to use while importing and exporting configurations.
   *
   * @param string $stream
   */
  public static function setStream($stream) {
    static::$stream = $stream;
  }

  /**
   * Returns a Configuration Object of the type specified in the firt part of
   * the $configuration_id.
   *
   * @param  string $configuration_id
   *   A string that identified the configuration and its identifier. e.g
   *   content_type.article
   */
  static public function createConfigurationInstance($configuration_id) {
    list($component_name, $identifier) = explode('.', $configuration_id, 2);
    $handler = static::getConfigurationHandler($component_name);
    if (!empty($handler)) {
      return new $handler($identifier, $component_name);
    }
    else {
      throw new \Exception('There is no configuration handler for: ' . $configuration_id);
    }

  }

  /**
   * Returns a handler that manages the configurations for the given component.
   */
  public static function getConfigurationHandler($component = NULL, $skip_module_checking = FALSE) {
    static $handlers;
    static $map;
    if (!isset($map)) {
      $map = array();
    }
    if (!isset($handlers)) {
      $handlers = module_invoke_all('configuration_handlers');
    }
    foreach ($handlers as $handler) {
      if ($skip_module_checking || $handler::isActive()) {
        foreach($handler::supportedComponents() as $component_name) {
          $map[$component_name] = $handler;
        }
      }
    }
    if (empty($component)) {
      return $map;
    }
    else {
      if (!empty($map[$component])) {
        return $map[$component];
      }
    }
  }

  /**
   * Returns a list of modules required to import the configurations indicated
   * in $list.
   *
   * @param  array  $modules
   *   The list of modules that are listed in the tracked.inc file.
   */
  static public function discoverRequiredModules($modules) {

    $settings = new ConfigIteratorSettings(
      array(
        'info' => array(
          'modules' => array(),
          'modules_missing' => array(),
          'modules_to_install' => array(),
        )
      )
    );

    $stack = array();
    foreach ($modules as $module) {
      Configuration::getDependentModules($module, $stack);
    }

    $missing = array();
    $to_install = array();
    foreach ($stack as $module_name => $status) {
      if ($status == Configuration::moduleMissing) {
        $missing[] = $module_name;
      }
      elseif ($status == Configuration::moduleToInstall) {
        $to_install[] = $module_name;
      }
    }
    $settings->setInfo('modules_to_install', array_filter(array_unique($to_install)));
    $settings->setInfo('modules_missing', array_filter(array_unique($missing)));

    return $settings;
  }

  /**
   * Includes a record of each configuration tracked in the
   * configuration_tracked table and export the configurations to the DataStore.
   *
   * @param  array   $list
   *   The list of components that have to will be tracked.
   * @param  boolean $track_dependencies
   *   If TRUE, dependencies of each proccessed configuration will be tracked
   *   too.
   * @param  boolean $track_optionals
   *   If TRUE, optionals configurations of each proccessed configuration will
   *   be tracked too.
   * @return ConfigIteratorSettings
   *   An ConfigIteratorSettings object that contains the tracked
   *   configurations.
   */
  static public function startTracking($list = array(), $track_dependencies = TRUE, $track_optionals = TRUE) {
    return static::exportToDataStore($list, $track_dependencies, $track_optionals, TRUE);
  }

  /**
   * Removes a record of each configuration that is not tracked anymore and
   * deletes the configuration file in the DataStore.
   *
   * @param  array   $list
   *   The list of components that have to will be tracked.
   * @param  boolean $track_dependencies
   *   If TRUE, dependencies of each proccessed configuration will not be
   *   tracked anymore.
   * @param  boolean $track_optionals
   *   If TRUE, optionals configurations of each proccessed configuration will
   *   not be tracked anymore.
   * @return ConfigIteratorSettings
   *   An ConfigIteratorSettings object that contains configurations that are
   *   not tracked anymore.
   */
  static public function stopTracking($list = array(), $stop_track_dependencies = TRUE, $stop_track_optionals = TRUE) {
    $excluded = static::excludedConfigurations();
    $settings = new ConfigIteratorSettings(
      array(
        'build_callback' => 'build',
        'callback' => 'removeConfiguration',
        'process_dependencies' => $stop_track_dependencies,
        'process_optionals' => $stop_track_optionals,
        'settings' => array(
          'excluded' => $excluded,
        ),
        'info' => array(
          'untracked' => array(),
        )
      )
    );
    if (Storage::checkFilePermissions('tracked.inc')) {
      foreach ($list as $component) {
        if (in_array($component, $excluded)) {
          continue;
        }

        $config = static::createConfigurationInstance($component);

        // Make sure the object is built before start to iterate on its
        // dependencies.
        $config->setContext($settings);
        $config->build();
        $config->iterate($settings);
      }

      $tracked = static::trackedConfigurations();
      $args = array();
      foreach ($tracked as $component => $list) {
        foreach ($list as $identifier => $hash) {
          $id = $component . '.' . $identifier;
          $args[] = $id;
        }
      }

      static::exportToDataStore($args, TRUE, TRUE, TRUE);
    }

    return $settings;
  }

  /**
   * Loads the configuration from the DataStore into the ActiveStore.
   *
   * @param  array   $list
   *   The list of components that have to will be imported.
   * @param  boolean $import_dependencies
   *   If TRUE, dependencies of each proccessed configuration will be imported
   *   too.
   * @param  boolean $import_optionals
   *   If TRUE, optionals configurations of each proccessed configuration will
   *   be imported too.
   * @param  boolean $start_tracking
   *   If TRUE, after import the configuration, it will be also tracked.
   * @param $source
   *   Optional. An optional path to load configurations.
   * @return ConfigIteratorSettings
   *   An ConfigIteratorSettings object that contains the imported
   *   configurations.
   */
  static public function importToActiveStore($list = array(), $import_dependencies = TRUE, $import_optionals = TRUE, $start_tracking = FALSE, $source = NULL) {
    $excluded = static::excludedConfigurations();
    $settings = new ConfigIteratorSettings(
      array(
        'build_callback' => 'loadFromStorage',
        'callback' => 'import',
        'process_dependencies' => $import_dependencies,
        'process_optionals' => $import_optionals,
        'settings' => array(
          'start_tracking' => $start_tracking,
          'source' => $source,
          'excluded' => $excluded,
        ),
        'info' => array(
          'imported' => array(),
          'fail' => array(),
          'no_handler' => array(),
        )
      )
    );

    module_invoke_all('configuration_pre_import', $settings);

    $handlers = static::getConfigurationHandler();

    foreach ($list as $component) {
      if (in_array($component, $excluded)) {
        continue;
      }
      $part = explode('.', $component, 2);
      if (empty($handlers[$part[0]])) {
        $settings->addInfo('no_handler', $part[0]);
      }
      else {
        $config = static::createConfigurationInstance($component);

        // Make sure the object is built before start to iterate on its
        // dependencies.
        $config->setContext($settings);
        $config->loadFromStorage();
        $config->iterate($settings);
      }
    }

    drupal_flush_all_caches();
    if ($start_tracking) {
      static::exportToDataStore($list, $import_dependencies, $import_optionals, TRUE);
    }

    module_invoke_all('configuration_post_import', $settings);

    return $settings;
  }

  /**
   * Export the configuration from the ActiveStore to the DataStore.
   *
   * @param  array   $list
   *   The list of components that have to will be exported.
   * @param  boolean $import_dependencies
   *   If TRUE, dependencies of each proccessed configuration will be exported
   *   too.
   * @param  boolean $import_optionals
   *   If TRUE, optionals configurations of each proccessed configuration will
   *   be exported too.
   * @param  boolean $star_tracking
   *   If TRUE, after export the configuration, it will be also tracked.
   * @return ConfigIteratorSettings
   *   An ConfigIteratorSettings object that contains the exported
   *   configurations.
   */
  static public function exportToDataStore($list = array(), $export_dependencies = TRUE, $export_optionals = TRUE, $start_tracking = FALSE) {
    $excluded = static::excludedConfigurations();
    $settings = new ConfigIteratorSettings(
      array(
        'build_callback' => 'build',
        'callback' => 'export',
        'process_dependencies' => $export_dependencies,
        'process_optionals' => $export_optionals,
        'settings' => array(
          'start_tracking' => $start_tracking,
          'excluded' => $excluded,
        ),
        'info' => array(
          'modules' => array(),
          'exported' => array(),
          'hash' => array(),
        )
      )
    );

    module_invoke_all('configuration_pre_export', $settings);

    foreach ($list as $component) {
      if (in_array($component, $excluded)) {
        continue;
      }
      $config = static::createConfigurationInstance($component);

      // Make sure the object is built before start to iterate on its
      // dependencies.
      $config->setContext($settings);
      $config->build();
      $config->iterate($settings);
    }

    // Even if we are exporting only a few configurations, all tracked
    // configurations should be considered while creating the list of required
    // modules.
    if ($start_tracking) {
      $modules = array();
      foreach (array_keys(static::trackedConfigurations(FALSE)) as $config_id) {
        $config = static::createConfigurationInstance($config_id);
        $config->build();
        $modules = array_merge($modules, array_keys($config->getRequiredModules()));
      }
      array_merge($modules, $settings->getInfo('modules'));

      static::updateTrackingFile($modules);
    }

    module_invoke_all('configuration_post_export', $settings);

    return $settings;
  }

  /**
   * Returns a list of configurations that are currently being tracked.
   *
   * @param boolean $tree
   *   A boolean flag to indicate if the tracked configuration have to be
   *   organized in a tree structure.
   *
   * @return array
   *   If $tree is TRUE the returned array is structured in the this way:
   *
   *   @code
   *     array(
   *       'content_type' => array(
   *         'article' => array(
   *           'hash' => 'c08223610b3eb55161d4539c704e40989dcf3e72',
   *           'name' => 'Article',
   *         ),
   *         'page' => array(
   *           'hash' => '5161d4539c704e40989dcf3e72c08223610b3eb5',
   *           'name' => 'Page',
   *         ),
   *       ),
   *       'variable' => array(
   *         'site_name' => array(
   *           'hash' => '539c704e40989dcf35161d4e72c08223610b3eb5',
   *           'name' => 'site_name',
   *         ),
   *       )
   *     );
   *
   *     If $tree is FALSE the returned array is structured in the this way:
   *
   *     array(
   *       'content_type.article' => array(
   *         'hash' => 'c08223610b3eb55161d4539c704e40989dcf3e72',
   *         'name' => 'Article',
   *       ),
   *       'content_type.page' => array(
   *         'hash' => '5161d4539c704e40989dcf3e72c08223610b3eb5',
   *         'name' => 'Page',
   *       ),
   *       'variable.site_name' => array(
   *         'hash' =>'539c704e40989dcf35161d4e72c08223610b3eb5',
   *         'name' => 'site_name'
   *       )
   *     );
   *   @endcode
   */
  static public function trackedConfigurations($tree = TRUE) {
    $excluded = static::excludedConfigurations();
    $tracked = db_select('configuration_tracked', 'ct')
                  ->fields('ct', array('component', 'identifier', 'hash'))
                  ->execute()
                  ->fetchAll();

    $return = array();
    // Prepare the array to return
    $handlers = static::getConfigurationHandler();

    if ($tree) {
      foreach ($handlers as $component => $handler) {
        $return[$component] = array();
      }
    }

    foreach ($tracked as $object) {
      $id = $object->component . '.' . $object->identifier;
      if (in_array($id, $excluded)) {
        continue;
      }

      // Only return tracked Configurations for supported components.
      if (isset($handlers[$object->component])) {

        $all_identifiers = $handlers[$object->component]::getAllIdentifiersCached($object->component);

        if (empty($all_identifiers[$object->identifier])) {
          $name = $object->identifier;
        }
        else {
          $name = $all_identifiers[$object->identifier];
        }

        if ($tree) {
          $return[$object->component][$object->identifier] = array(
            'hash' => $object->hash,
            'name' => $name,
          );
        }
        else {
          $return[$object->component . '.' . $object->identifier] = array(
            'hash' => $object->hash,
            'name' => $name,
          );
        }
      }
    }
    return $return;
  }

  /**
   * Returns a list of configurations that are not currently being tracked.
   *
   * @return array
   */
  static public function nonTrackedConfigurations() {
    $excluded = static::excludedConfigurations();
    $handlers = static::getConfigurationHandler();

    $tracked = static::trackedConfigurations();
    $non_tracked = array();

    foreach (array_keys($handlers) as $component) {
      $handler = static::getConfigurationHandler($component);
      $identifiers = $handler::getAllIdentifiersCached($component);
      foreach ($identifiers as $identifier => $identifier_human_name) {
        if (empty($tracked[$component]) || empty($tracked[$component][$identifier])) {
          $id = $component . '.' . $identifier;
          if (in_array($id, $excluded)) {
            continue;
          }
          $non_tracked[$component][$identifier] = array(
            'id' => $id,
            'name' => $identifier_human_name,
          );
        }
      }
    }
    return $non_tracked;
  }

  /**
   * Return a list of configurations that will not be proccessed by
   * configuration management.
   */
  static public function excludedConfigurations() {
    $list = variable_get('configuration_exclude_configurations', '');
    $list = explode("\n", $list);
    $list = array_map('trim', $list);
    $list = array_filter($list, 'strlen');

    return $list;
  }

  /**
   * Returns a list of configurations available in the site without distinction
   * of tracked and not tracked.
   *
   * @return array
   */
  static public function allConfigurations() {
    $excluded = static::excludedConfigurations();

    $handlers = static::getConfigurationHandler();

    $tracked = static::trackedConfigurations();
    $all = array();

    foreach ($handlers as $component => $handler) {
      $identifiers = $handler::getAllIdentifiersCached($component);
      foreach ($identifiers as $identifier => $identifier_human_name) {
        $id = $component . '.' . $identifier;

        if (in_array($id, $excluded)) {
          continue;
        }

        if (!empty($tracked[$component][$identifier])) {
          // Set the hash for the tracked configurations
          $all[$component][$identifier] = array(
            'hash' => $tracked[$component][$identifier],
            'name' => $identifiers[$identifier],
          );
        }
        else {
          // Set FALSE for the non tracked configurations
          $all[$component][$identifier] = array(
            'hash' => FALSE,
            'name' => $identifiers[$identifier],
          );
        }
      }
    }
    return $all;
  }

  /**
   * This function save into config://tracked.inc file the configurations that
   * are currently tracked.
   */
  static public function updateTrackingFile($modules = array()) {
    $tracked = static::trackedConfigurations();

    $file = array();
    foreach ($tracked as $component => $list) {
      foreach ($list as $identifier => $info) {
        $file[$component . '.' . $identifier] = $info['hash'];
      }
    }
    $file_content = "<?php\n\n";
    $file_content .= "// This file contains the current being tracked configurations.\n\n";
    $file_content .= '$tracked = ' . var_export($file, TRUE) . ";\n";
    $file_content .= "\n\n// The following modules are required to run the configurations of this file.\n\n";
    $file_content .= "\$modules = array(\n";
    foreach (array_unique($modules) as $module) {
      $file_content .= "  '$module',\n";
    }
    $file_content .= ");\n";
    if (Storage::checkFilePermissions('tracked.inc')) {
      file_put_contents(static::getStream() . '/tracked.inc', $file_content);
    }
  }

  /**
   * Returns a list of files that are listed in the config://tracked.inc file.
   */
  static public function readTrackingFile() {
    if (file_exists(static::getStream() . '/tracked.inc')) {
      $file_content = drupal_substr(file_get_contents(static::getStream() . '/tracked.inc'), 6);
      @eval($file_content);
      return array(
        'tracked' => $tracked,
        'modules' => $modules,
      );
    }
    return array();
  }

  /**
   * Import configurations from a Tar file.
   *
   * @param  StdClass $file
   *   A file object.
   * @param  boolean $start_tracking
   *   If TRUE, all the configurations provided in the Tar file will be imported
   *   and automatically tracked.
   *
   * @return ConfigIteratorSettings
   *   An ConfigIteratorSettings object that contains the imported
   *   configurations.
   */
  static public function importToActiveStoreFromTar($uri, $start_tracking = FALSE) {
    $path = 'temporary://';

    $archive = archiver_get_archiver($uri);
    $files = $archive->listContents();
    foreach ($files as $filename) {
      if (is_file($path . $filename)) {
        file_unmanaged_delete($path . $filename);
      }
    }

    $config_temp_path = 'temporary://' . 'config-tmp-' . time();
    $archive->extract(drupal_realpath($config_temp_path));

    $file_content = drupal_substr(file_get_contents($config_temp_path . '/configuration/configurations.inc'), 6);

    @eval($file_content);

    $source = $config_temp_path . '/configuration/';

    $modules_results = ConfigurationManagement::discoverRequiredModules($modules);

    $missing_modules = $modules_results->getInfo('modules_missing');

    $error = FALSE;
    if (!empty($missing_modules)) {
      drupal_set_message(t('Configurations cannot be synchronized because the following modules are not available to install: %modules', array('%modules' => implode(', ', $missing_modules))), 'error');
      return $modules_results;
    }
    else {
      $modules_to_install = $modules_results->getInfo('modules_to_install');
      if (!empty($modules_to_install)) {
        module_enable($modules_to_install, TRUE);
        drupal_set_message(t('The following modules have been enabled: %modules', array('%modules' => implode(', ', $modules_to_install))));
        drupal_flush_all_caches();
      }
    }

    $settings = static::importToActiveStore($configurations, FALSE, FALSE, $start_tracking, $source);

    static::deteleTempConfigDir($config_temp_path);

    return $settings;
  }


  /**
   * Download the entire configuration packaged up into tar file
   */
  public static function exportAsTar($list = array(), $export_dependencies = TRUE, $export_optionals = TRUE) {
    $excluded = static::excludedConfigurations();
    $settings = new ConfigIteratorSettings(
      array(
        'build_callback' => 'build',
        'callback' => 'printRaw',
        'process_dependencies' => $export_dependencies,
        'process_optionals' => $export_optionals,
        'info' => array(
          'exported' => array(),
          'exported_files' => array(),
          'hash' => array(),
          'modules' => array(),
          'excluded' => $excluded,
        ),
        'settings' => array(
          'format' => 'tar',
          'tar_folder' => 'configuration',
        )
      )
    );

    module_invoke_all('configuration_pre_export', $settings);

    $filename = 'configuration.' . time() . '.tar';

    // Clear out output buffer to remove any garbage from tar output.
    if (ob_get_level()) {
      ob_end_clean();
    }

    drupal_add_http_header('Content-type', 'application/x-tar');
    drupal_add_http_header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    drupal_send_headers();

    foreach ($list as $component) {
      if (in_array($component, $excluded)) {
        continue;
      }
      $config = static::createConfigurationInstance($component);

      // Make sure the object is built before start to iterate on its
      // dependencies.
      $config->setContext($settings);
      $config->build();
      $config->iterate($settings);
    }

    $exported = $settings->getInfo('exported');

    module_invoke_all('configuration_post_export', $settings);

    $file_content = "<?php\n\n";
    $file_content .= "// This file contains the list of configurations contained in this package.\n\n";
    $file_content .= "\$configurations = array(\n";
    foreach ($exported as $config) {
      $file_content .= "  '$config',\n";
    }
    $file_content .= ");\n\n";
    $file_content .= "\$modules = array(\n";
    foreach (array_unique($settings->getInfo('modules')) as $module) {
      $file_content .= "  '$module',\n";
    }
    $file_content .= ");\n";

    print static::createTarContent($settings->getSetting('tar_folder') . "/configurations.inc", $file_content);

    print pack("a1024", "");
    exit;
  }

  /**
   * Download the entire configuration packaged up into tar file
   */
  public static function rawDepdendencyInfo($list = array(), $include_dependencies = TRUE, $include_optionals = TRUE) {
    $excluded = static::excludedConfigurations();
    $settings = new ConfigIteratorSettings(
      array(
        'build_callback' => 'build',
        'callback' => 'printRaw',
        'process_dependencies' => $include_dependencies,
        'process_optionals' => $include_optionals,
        'info' => array(
          'exported' => array(),
          'exported_files' => array(),
          'hash' => array(),
        ),
        'settings' => array(
          'print' => array(
            'optionals' => $include_optionals,
            'dependencies' => $include_dependencies,
          ),
          'excluded' => $excluded,
        )
      )
    );

    // Clear out output buffer to remove any garbage from tar output.
    if (ob_get_level()) {
      ob_end_clean();
    }

    drupal_add_http_header('Content-type', 'application/json');
    print "{\n";

    foreach ($list as $component) {
      if (in_array($component, $excluded)) {
        continue;
      }
      $config = static::createConfigurationInstance($component);

      // Make sure the object is built before start to iterate on its
      // dependencies.
      $config->setContext($settings);
      $config->build();
      $config->iterate($settings);
    }
    print '"null": "null"';
    print "}\n";

    exit;
  }

  static protected function deteleTempConfigDir($dir, $force = FALSE) {
    // Allow to delete symlinks even if the target doesn't exist.
    if (!is_link($dir) && !file_exists($dir)) {
      return TRUE;
    }
    if (!is_dir($dir)) {
      if ($force) {
        // Force deletion of items with readonly flag.
        @chmod($dir, 0777);
      }
      return unlink($dir);
    }
    foreach (scandir($dir) as $item) {
      if ($item == '.' || $item == '..') {
        continue;
      }
      if ($force) {
        @chmod($dir, 0777);
      }
      if (!static::deteleTempConfigDir($dir . '/' . $item, $force)) {
        return FALSE;
      }
    }
    if ($force) {
      // Force deletion of items with readonly flag.
      @chmod($dir, 0777);
    }
    return rmdir($dir);
  }

  /**
   * Tar creation function. Written by dmitrig01.
   *
   * @param $name
   *   Filename of the file to be tarred.
   * @param $contents
   *   String contents of the file.
   *
   * @return
   *   A string of the tar file contents.
   */
  public static function createTarContent($name, $contents) {
    $tar = '';
    $binary_data_first = pack("a100a8a8a8a12A12",
      $name,
      '100644 ', // File permissions
      '   765 ', // UID,
      '   765 ', // GID,
      sprintf("%11s ", decoct(drupal_strlen($contents))), // Filesize,
      sprintf("%11s", decoct(REQUEST_TIME)) // Creation time
    );
    $binary_data_last = pack("a1a100a6a2a32a32a8a8a155a12", '', '', '', '', '', '', '', '', '', '');

    $checksum = 0;
    for ($i = 0; $i < 148; $i++) {
      $checksum += ord(drupal_substr($binary_data_first, $i, 1));
    }
    for ($i = 148; $i < 156; $i++) {
      $checksum += ord(' ');
    }
    for ($i = 156, $j = 0; $i < 512; $i++, $j++) {
      $checksum += ord(drupal_substr($binary_data_last, $j, 1));
    }

    $tar .= $binary_data_first;
    $tar .= pack("a8", sprintf("%6s ", decoct($checksum)));
    $tar .= $binary_data_last;

    $buffer = str_split($contents, 512);
    foreach ($buffer as $item) {
      $tar .= pack("a512", $item);
    }
    return $tar;
  }
}
