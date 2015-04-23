<?php

/**
 * @file
 * Hooks for configuration module.
 */

/**
 * Alter the handlers to manage configurations.
 *
 * The hook is implemented to provide new handlers to manage configurations not
 * covered by configuration handler or to modify the main handler that handle
 * a certain configuration.
 *
 * @param array $handlers
 *   The array of already defined handlers.
 *
 * @see configuration_configuration_handlers().
 */
function hook_configuration_handlers_alter(&$handlers) {
  // Define a new configuration handler to manage 'Foo' configurations.
  // A FooConfiguration.php file must be placed into
  // yourmodule/lib/Drupal/yourmodule/Config
  $handlers['foo'] = '\Drupal\yourmodule\Config\FooConfiguration';

  // Modify an existen handler.
  $handlers['variable'] = '\Drupal\yourmodule\Config\CustomVariableConfiguration';
}

/**
 * Allow to execute opterations before import configurations to the Active Store.
 */
function hook_configuration_pre_import(ConfigIteratorSettings &$settings) {

}

/**
 * Allow to execute opterations the settings after configurations were imported
 * to the Active Store.
 */
function hook_configuration_post_import(ConfigIteratorSettings &$settings) {

}

/**
 * Alter the settings before export configurations to the Data Store.
 */
function hook_configuration_pre_export(ConfigIteratorSettings &$settings) {

}

/**
 * Alter the settings after export configurations to the Data Store.
 */
function hook_configuration_post_export(ConfigIteratorSettings &$settings) {

}

