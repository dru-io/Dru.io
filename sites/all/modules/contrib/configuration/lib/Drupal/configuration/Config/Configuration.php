<?php

/**
 * @file
 * Definition of Drupal\configuration\Config\Configuration.
 */

namespace Drupal\configuration\Config;

use \StdClass;
use Drupal\configuration\Utils\ConfigIteratorSettings;

abstract class Configuration {


  /**
   * A bit flag used to let us know if a configuration is the same in both the
   * activestore and the datastore.
   */
  const inSync = 0x0000;

  /**
   * A bit flag used to let us know if a configuration was overridden as a result
   * of changing the activestore directly. (config changes via the UI)
  */
  const overriden = 0x0001;

  /**
   * A bit flag used to let us know if a configuration is not currently being
   * tracked.
   */
  const notTracked = 0x0200;

  /**
   * A bit flag used to let us know if a module for the configuration is not
   * available to install in the site.
   */
  const moduleMissing = 0x0100;

  /**
   * A bit flag used to let us know if a module for the configuration is disabled
   * but can be enabled.
   */
  const moduleToInstall = 0x0101;

  /**
   * A bit flag used to let us know if a module for the configuration is already
   * installed.
   */
  const moduleInstalled = 0x0102;

  /**
   * The identifier that identifies to the component, usually the machine name.
   */
  protected $identifier;

  /**
   * A hash that represent that sumarizes the configuration and can
   * be used to copare configurations.
   */
  protected $hash;

  /**
   * The data of this configuration.
   */
  protected $data;

  /**
   * An array of configuration objects required to use this configuration.
   */
  protected $dependencies = array();

  /**
   * An array of configuration objects that are parts of this configurations
   * but are not required to use this configuration.
   */
  protected $optional_configurations = array();

  /**
   * The required modules to load this configuration.
   */
  protected $required_modules = array();

  /**
   * An array of keys names to export. If the array is empty,
   * all the keys of the configuration will be exported.
   */
  protected $keys_to_export = array();

  /**
   * An object to save and load the data from a persistent medium.
   */
  protected $storage;

  /**
   * A boolean flag to indicate if the configuration object was already
   * populated from the ActiveStore, or from the DataStore.
   */
  protected $built;

  /**
   * A boolean flag to indicate if the configuration object couldn't be loaded
   * from it source.
   */
  protected $broken = FALSE;

  /**
   * The ConfigIteratorSettings instance used by iterate.
   */
  protected $context = NULL;

  /**
   * Constructor.
   * @param string $identifier
   *   The identifier of the configurations. Usually a machine name.
   * @param string $component
   *   The component of this configuration. i.e content_type, field, variable,
   *   etc.
   */
  public function __construct($identifier, $component = '') {
    $this->identifier = $identifier;
    $this->storage = static::getStorageInstance($component);
    $this->storage->setFileName($this->getFileName());
  }

  /**
   * Returns a class with its namespace to save data to the disk.
   */
  static protected function getStorageSystem($component) {
    $default = '\Drupal\configuration\Storage\StoragePhp';
    // Specify a default Storage system
    $return = variable_get('configuration_storage_system', $default);
    // Allow to configure the Storage System per configuration component
    $return = variable_get('configuration_storage_system_' . $component, $return);
    return $return;
  }

  /**
   * Returns a Storage Object ready to load or write configurations from the
   * disk.
   */
  static protected function getStorageInstance($component) {
    $storage = static::getStorageSystem($component);
    $return = new $storage();
    return $return;
  }

  /**
   * Returns all the identifiers available for this component.
   */
  public static function getAllIdentifiers($component) {
    return array();
  }

    /**
   * Cache wrapper for getAllIdentifiers().
   */
  public static function getAllIdentifiersCached($component) {
    static $identifiers;
    if (!isset($identifiers)) {
      $identifiers = array();
    }
    if (!isset($identifiers[$component])) {
      $identifiers[$component] = static::getAllIdentifiers($component);
    }
    return  $identifiers[$component];
  }

  /**
   * Returns the list of components available in the DataStore.
   */
  public static function scanDataStore($component, $source = FALSE) {
    $list_of_components = array();

    if ($source) {
      $path = $source;
    }
    else {
      $path = drupal_realpath('config://');
    }
    $storage_system = static::getStorageSystem($component);
    $ext = $storage_system::$file_extension;
    $look_for = '/\A' . $component . '\..*' . $ext . '$/';

    $files = file_scan_directory($path, $look_for);

    foreach ($files as $file) {
      if (!in_array($file->name, $list_of_components)) {
        $storage = static::getStorageInstance($component);
        $storage
          ->setFileName($file->name)
          ->load();

        if ($storage->withData()) {
          $list_of_components[$file->name] = $file->name;
        }
      }
    }
    return $list_of_components;
  }

  /**
   * Load a configurations from the database.
   */
  public function loadFromActiveStore() {
    $this->build();
    $this->buildHash();
    return $this;
  }

  /**
   * Load the Configuration data from the disk.
   */
  public function loadFromStorage() {
    $source = NULL;
    if (isset($this->context)) {
      $source = $this->context->getSetting('source');
    }

    $this->storage->load(NULL, $source);

    // Check if thereis a context defined, then we are iterating.
    if (isset($this->context)) {
      // Check that this configuration is supported by Configuration Management
      if (!ConfigurationManagement::validApiVersion($this->storage->getApiVersion())) {
        $this->broken = TRUE;
        return $this;
      }
    }
    $this->setData($this->storage->getData());
    $this->setDependencies($this->storage->getDependencies());
    $this->setOptionalConfigurations($this->storage->getOptionalConfigurations());
    $this->setModules($this->storage->getModules());
    // This build the Hash;
    $this->storage->getDataToSave();
    $this->setHash($this->storage->getHash());

    $this->built = TRUE;
    return $this;
  }

  /**
   * Save a configuration object into the configuration_tracked table.
   */
  public function startTracking() {
    db_delete('configuration_tracked')
      ->condition('component', $this->getComponent())
      ->condition('identifier', $this->getIdentifier())
      ->execute();

    $fields = array(
      'component' => $this->getComponent(),
      'identifier' => $this->getIdentifier(),
      'hash' => $this->getHash(),
      'file' => $this->getFileName(),
    );
    db_insert('configuration_tracked')->fields($fields)->execute();
  }

  /**
   * Returns an array of keys names to export. If the array is empty,
   * all the keys of the configuration will be exported.
   */
  public function getKeysToExport() {
    return $this->keys_to_export;
  }

  /**
   * Set an array of keys names to export. If the array is empty,
   * all the keys of the configuration will be exported.
   */
  public function setKeysToExport($keys) {
    $this->keys_to_export = $keys;
    return $this;
  }

  /**
   * Internal function to discover what modules are required for the current
   * being proccessed configurations.
   *
   * @see iterate()
   */
  protected function discoverModules(ConfigIteratorSettings &$settings) {
    $modules = $settings->getInfo('modules');
    $modules = array_merge($modules, $this->getRequiredModules());
    $settings->setInfo('modules', $modules);
  }

  /**
   * Removes the configuration record from the configuration_tracked table for
   * the current configuration.
   */
  public function removeConfiguration(ConfigIteratorSettings &$settings) {
    $this->stopTracking($settings);
    $this->removeFromDataStore($settings);
  }

  /**
   * Removes the configuration record from the configuration_tracked table for
   * the current configuration.
   */
  public function stopTracking(ConfigIteratorSettings &$settings) {
    $deleted = db_delete('configuration_tracked')
      ->condition('component', $this->getComponent())
      ->condition('identifier', $this->getIdentifier())
      ->execute();

    if ($deleted > 0) {
      $settings->addInfo('untracked', $this->getUniqueId());
    }
  }

  /**
   * Removes the configuration file from the dataStore folder.
   */
  public function removeFromDataStore(ConfigIteratorSettings &$settings) {
    $this->storage->delete();
  }

  /**
   * Load a configuration from the DataStore and save it into the ActiveStore.
   * This function is called from iterator().
   *
   * @see iterate()
   */
  public function import(ConfigIteratorSettings &$settings) {
    $this->loadFromStorage();
    if ($this->isBroken()) {
      $settings->addInfo('fail', $this->getUniqueId());
    }
    else {
      $this->saveToActiveStore($settings);

      if ($settings->getSetting('start_tracking')) {
        $this->startTracking();
      }
    }
  }

  /**
   * Save a configuration into the ActiveStore.
   *
   * Each configuration should implement their own version of saveToActiveStore.
   * I.e, content types should call to node_save_type(), variables should call
   * to variable_set(), etc.
   */
  abstract public function saveToActiveStore(ConfigIteratorSettings &$settings);

  public function export(ConfigIteratorSettings &$settings) {
    $this->build();

    $modules = array_keys($this->getRequiredModules());

    // Save the configuration into a file.
    $this->storage
            ->setApiVersion(ConfigurationManagement::api)
            ->setData($this->data)
            ->setKeysToExport($this->getKeysToExport())
            ->setDependencies(drupal_map_assoc(array_keys($this->getDependencies())))
            ->setOptionalConfigurations(drupal_map_assoc(array_keys($this->getOptionalConfigurations())))
            ->setModules($modules)
            ->save();

    if ($settings->getSetting('start_tracking')) {
      $this->buildHash();
      $settings->addInfo('hash', $this->getHash());
      $this->startTracking();
    }

    foreach ($modules as $module) {
      $settings->addInfo('modules', $module);
    }
    // Add the current config as an exported item
    $settings->addInfo('exported', $this->getUniqueId());
  }

  /**
   * Gets the structure of the configuration and save
   * it into the $data attribute.
   */
  abstract protected function prepareBuild();

  /**
   * Return TRUE if something went wrong with the load of the configuration.
   */
  public function isBroken() {
    return $this->broken;
  }

  /**
   * Build the configuration object based on the component name and
   * in the identifier.
   *
   * The build process implies get the structure of the configuration and save
   * it into the $data attribute. Also, this function should look for the
   * dependencies of this configuration if $include_dependencies is TRUE.
   */
  public function build($include_dependencies = TRUE) {
    $this->prepareBuild();
    $this->broken = $this->data === NULL;
    if ($this->broken) {
      drupal_set_message(t('Configuration %component is broken.', array('%component' => $this->getUniqueId())), 'error');
    }
    if ($include_dependencies) {
      $this->findDependencies();
    }
    if (empty($this->broken)) {
      $this->findRequiredModules();
    }
    $this->built = TRUE;
    return $this;
  }

  /**
   * Create a unique hash for this configuration based on the data,
   * dependencies, optional configurations and modules required to use this
   * configuration. Use getHash() after call this function.
   */
  public function buildHash() {
    if ($this->broken) {
      $this->setHash('Broken Configuration');
      return $this;
    }
    $this->storage
        ->setApiVersion(ConfigurationManagement::api)
        ->setData($this->data)
        ->setKeysToExport($this->getKeysToExport())
        ->setDependencies(drupal_map_assoc(array_keys($this->getDependencies())))
        ->setOptionalConfigurations(drupal_map_assoc(array_keys($this->getOptionalConfigurations())))
        ->setModules(array_keys($this->getRequiredModules()));
    $this->storage->getDataToSave();
    $this->setHash($this->storage->getHash());
    return $this;
  }

  /**
   * Return the current status of the configuration.
   *
   * @param  boolean $human_name
   *   If TRUE a human readable name will be return for the status of the
   *   configuration. If FALSE a numeric code will be returned.
   * @return string|integer
   *   The status of the configuration. (ActiveStore only, In Sync, Overriden).
   */
  public function getStatus($human_name = TRUE) {
    if ($this->broken) {
      return $human_name ? t('Removed from ActiveStore') : 0;
    }
    $tracking_file = ConfigurationManagement::readTrackingFile();
    $tracked = array();
    if (isset($tracking_file['tracked'])) {
      $tracked = $tracking_file['tracked'];
    }

    if (isset($tracked[$this->getUniqueId()])) {
      $file_hash = $tracked[$this->getUniqueId()];
    }
    if (!isset($file_hash)) {
      return $human_name ? t('ActiveStore only') : Configuration::notTracked;
    }
    else {
      if ($this->getHash() == $file_hash) {
        return $human_name ? t('In Sync') : Configuration::inSync;
      }
      else {
        return $human_name ? t('Overriden') : Configuration::overriden;
      }
    }
  }

  /**
   * Returns an unique identifier for this configuration. Usually something like
   * 'content_type.article' where content_type is the component of the
   * configuration and 'article' is the identifier of the configuration for the
   * given component.
   *
   * @return string
   */
  public function getUniqueId() {
    return $this->getComponent() . '.' . $this->getIdentifier();
  }

  /**
   * Returns the component that this configuration represent.
   */
  public function getComponent() {
  }

  /**
   * Returns the all the components that this handler can handle.
   */
  static public function supportedComponents() {
    return array();
  }

  /**
   * Returns the human name of the given component.
   */
  static public function getComponentHumanName($component, $plural = FALSE) {
    return t('UNDEFINED: ') . $component;
  }

  /**
   * Determine if the handler can be used. Usually this function should check
   * that modules required to handle the configuration are installed.
   *
   *  @return boolean
   *    TRUE if the handler is active and can be used. FALSE otherwise.
   */
  public static function isActive() {
    return TRUE;
  }

  /**
   * Returns the identifier of the configuration object.
   */
  public function getIdentifier() {
    return $this->identifier;
  }

  /**
   * Set the component identifier of this configuration
   */
  public function setIdentifier($value) {
    $this->identifier = $value;
    return $this;
  }

  /**
   * Returns the hash of the configuration object.
   */
  public function getHash() {
    return $this->hash;
  }

  /**
   * Set the hash for this configuration.
   */
  public function setHash($value) {
    $this->hash = $value;
    return $this;
  }

  /**
   * Return the data for this configuration.
   */
  public function getData() {
    return $this->data;
  }

  /**
   * Set the data for this configuration.
   */
  public function setData($value) {
    $this->data = $value;
    return $this;
  }

  /**
   * Returns the name of the required_modules that provide this configuration.
   */
  public function getModules() {
    return $this->required_modules;
  }

  /**
   * Set the name of the required_modules that provide this configuration.
   */
  public function setModules($list) {
    $this->required_modules = $list;
    return $this;
  }

  /**
   * Add the required modules to load this configuration.
   */
  public function findRequiredModules() {
    // Configurations classes should use this method to add the required
    // modules to load the configuration.
  }

  /**
   * Add a new dependency for this configuration.
   */
  public function addToModules($module) {
    if (!in_array($module, $this->required_modules)) {
      $this->required_modules[] = $module;
    }
    return $this;
  }

  /**
   * Return TRUE if this is the configuration for an entity.
   */
  public function configForEntity() {
    return FALSE;
  }

  /**
   * Add a new child configuration for this configuration.
   */
  public function addToOptionalConfigurations(Configuration $config) {
    if (!isset($this->optional_configurations)) {
      $this->optional_configurations = array();
    }
    $this->optional_configurations[$config->getUniqueId()] = $config;
    return $this;
  }

  /**
   * Returns the list of optional_configurations of this configuration
   */
  public function getOptionalConfigurations() {
    return $this->optional_configurations;
  }

  /**
   * Returns the list of optional_configurations of this configuration
   */
  public function setOptionalConfigurations($optional_configurations) {
    $this->optional_configurations = $optional_configurations;
    return $this;
  }

  /**
   * Add a new dependency for this configuration.
   */
  public function addToDependencies(Configuration $config) {
    if (!isset($this->dependencies)) {
      $this->dependencies = array();
    }
    $this->dependencies[$config->getUniqueId()] = $config;
    return $this;
  }

  /**
   * Returns the list of dependencies of this configuration
   */
  public function getDependencies() {
    return $this->dependencies;
  }

  /**
   * Returns the list of dependencies of this configuration
   */
  public function setDependencies($dependencies) {
    $this->dependencies = $dependencies;
    return $this;
  }

  /**
   * Ask to each configuration handler to add its dependencies
   * to the current configuration that is being exported.
   */
  public function findDependencies() {
    $handlers = ConfigurationManagement::getConfigurationHandler();

    foreach ($handlers as $component => $handler) {
      $handler::alterDependencies($this);
    }
  }

  /**
   * Configurations should implement this function to add configuration
   * objects (by using addToDepedencies).
   *
   * @param $config
   *   The object that requires all the dependencies.
   */
  public static function alterDependencies(Configuration $config) {
    // Override
  }

  /**
   * Returns TRUE if all the dependencies of this configurations are met.
   * Returns FALSE if a module or a dependency is required by this configuration
   * is not enabled.
   */
  public function checkDependencies() {
    foreach ($this->required_modules as $module) {
      if (!module_exists($module)) {
        return FALSE;
      }
    }
    foreach ($this->getDependencies() as $dependency) {
      // First, look for the dependency in the tracked table.
      $exists = db_select('configuration_tracked', 'ct')
                        ->fields('ct', array('identifier'))
                        ->condition('component', $this->getComponent())
                        ->condition('identifier', $this->getIdentifier())
                        ->fetchField();

      if (!$exists) {
        // If not exists in the database, look into the config:// directory.
        if (!$this->configFileExists()) {
          return FALSE;
        }
      }
    }
  }

  /**
   * Returns TRUE if the file that represents this configuration exists in the
   * datastore.
   *
   * @return boolean
   */
  public function configFileExists() {
    $storage_system = static::getStorageSystem($this->getComponent());
    return $storage_system::configFileExists($this->getFileName());
  }

  /**
   * Returns the filename that contains the content of the current
   * configuration.
   *
   * @return string
   */
  public function getFileName() {
    $storage_system = static::getStorageSystem($this->getComponent());
    return drupal_strtolower(preg_replace("/[^A-Za-z0-9 \.]/", '_', $this->getUniqueId())) . $storage_system::getFileExtension();
  }

  /**
   * Returns a list of modules that are required to run this configuration.
   *
   * @return
   *   A keyed array by module name that idicates the status of each module.
   */
  public function getRequiredModules() {
    $stack = array();
    foreach ($this->getModules() as $module) {
      static::getDependentModules($module, $stack);
    }
    return $stack;
  }

  /**
   * Determine the status of the given module and of its dependencies.
   */
  static public function getDependentModules($module, &$stack) {
    $available_modules = static::getAvailableModules();
    if (!isset($available_modules[$module])) {
      $stack[$module] = Configuration::moduleMissing;
      return;
    }
    else {
      if (empty($available_modules[$module]->status)) {
        $stack[$module] = Configuration::moduleToInstall;
        foreach ($available_modules[$module]->requires as $required_module) {
          if (empty($stack[$required_module['name']])) {
            static::getDependentModules($required_module['name'], $stack);
          }
        }
      }
      else {
        $stack[$module] = Configuration::moduleInstalled;
      }
    }
  }

  /**
   * Helper for retrieving info from system table.
   */
  static protected function getAvailableModules($reset = FALSE) {
    static $modules;

    if (!isset($modules)) {
      // @todo use cache for this function

      $files = system_rebuild_module_data();
      $modules = array();
      foreach ($files as $id => $file) {
        if ($file->type == 'module' && empty($file->info['hidden'])) {
          $modules[$id] = $file;
        }
      }
    }

    return $modules;
  }

  /**
   * Print the configuration as plain text formatted to use in a tar file.
   *
   * @param  ConfigIteratorSettings $settings
   * @see iterate()
   */
  public function raw() {
    // Save the configuration into a file.
    $file_content = $this->storage
                      ->setApiVersion(ConfigurationManagement::api)
                      ->setData($this->data)
                      ->setKeysToExport($this->getKeysToExport())
                      ->setDependencies(drupal_map_assoc(array_keys($this->getDependencies())))
                      ->setOptionalConfigurations(drupal_map_assoc(array_keys($this->getOptionalConfigurations())))
                      ->setModules(array_keys($this->getRequiredModules()))
                      ->getDataToSave();
    return $file_content;
  }

  /**
   * Print the configuration as plain text formatted to use in a tar file.
   *
   * @param  ConfigIteratorSettings $settings
   * @see iterate()
   */
  protected function printRaw(ConfigIteratorSettings &$settings) {
    $this->build();

    $this->buildHash();
    $settings->addInfo('hash', $this->getHash());

    $file_name = $this->storage->getFileName() ;
    $settings->addInfo('exported', $this->getUniqueId());
    foreach ($this->getRequiredModules() as $module => $status) {
      $settings->addInfo('modules', $module);
    }
    $settings->addInfo('exported_files', $file_name);

    if ($settings->getSetting('format') == 'tar') {
      $file_content = $this->raw();
      print ConfigurationManagement::createTarContent($settings->getSetting('tar_folder') . "/{$file_name}", $file_content);
    }
    else {
      $print = $settings->getSetting('print');
      if (is_array($print)) {
        if (!empty($print['dependencies'])) {
          foreach (array_keys($this->getDependencies()) as $line) {
            print '  "' . $line . '": "' . $line . "\",\n";
          }
        }
        if (!empty($print['optionals'])) {
          foreach (array_keys($this->getOptionalConfigurations()) as $line) {
            print '  "' . $line . '": "' . $line . "\",\n";
          }
        }
      }
    }
  }

  /**
   * Set the context where a function is executed.
   *
   * This function is called before call to the function callback in the iterate
   * function.
   *
   * @param  ConfigIteratorSettings $settings
   * @see iterate()
   */
  public function setContext(ConfigIteratorSettings &$settings) {
    $this->context = $settings;
  }

  /**
   * This function will exectute a callback function over all the configurations
   * objects that it process.
   *
   * @param  ConfigIteratorSettings $settings
   *   A ConfigIteratorSettings instance that specifies, which is the callback
   *   to execute. If dependencies and optional configurations should be
   *   processed too, and storage the cache of already processed configurations.
   *
   * @see importToActiveStore()
   * @see exportToDataStore()
   * @see revertActiveStore()
   * @see discoverRequiredModules()
   */
  public function iterate(ConfigIteratorSettings &$settings) {
    $callback = $settings->getCallback();
    $build_callback = $settings->getBuildCallback();

    if ($settings->alreadyProcessed($this) || $settings->excluded($this)) {
      return;
    }

    // First proccess requires the dependencies that have to be processed before
    // load the current configuration.
    if ($settings->processDependencies()) {
      foreach ($this->getDependencies() as $dependency => $config_dependency) {

        // In some callbacks, the dependencies storages the full config object
        // other simply use a plain string. If the object is available, use
        // that version.
        if (is_object($config_dependency)) {
          $config = $config_dependency;
        }
        else {
          $config = $settings->getFromCache($dependency);
          if (!$config) {
            $config = ConfigurationManagement::createConfigurationInstance($dependency);
          }
        }
        $config->setContext($settings);
        $config->{$build_callback}();
        $config->iterate($settings);
      }
    }

    if ($settings->alreadyProcessed($this)) {
      return;
    }

    // Now, after proccess the dependencies, proccess the current configuration.
    $this->setContext($settings);
    $this->{$callback}($settings);
    $settings->addToCache($this);

    // After proccess the dependencies and the current configuration, proccess
    // the optionals.
    if ($settings->processOptionals()) {
      foreach ($this->getOptionalConfigurations() as $optional => $optional_config) {
        $config = $settings->getFromCache($optional);

        // In some callbacks, the optionals storages the full config object
        // other simply use a plain string. If the object is available, use
        // that version.
        if (is_object($optional_config)) {
          $config = $optional_config;
        }
        else {
          if (!$config) {
            $config = ConfigurationManagement::createConfigurationInstance($optional);
          }
        }
        $config->setContext($settings);
        $config->{$build_callback}();
        $config->iterate($settings);
      }
    }
  }
}
