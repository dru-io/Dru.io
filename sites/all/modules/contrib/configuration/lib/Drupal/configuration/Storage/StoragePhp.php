<?php

/**
 * @file
 * Definition of Drupal\configuration\Storage\StoragePhp.
 */

namespace Drupal\configuration\Storage;

use Drupal\configuration\Storage\Storage;
use Drupal\configuration\Config\ConfigurationManagement;

class StoragePhp extends Storage {

  static public $file_extension = '.inc';

  /**
   * Adapted from CTools ctools_var_export().
   *
   * This is a replacement for var_export(), allowing us to more nicely
   * format exports. It will recurse down into arrays and will try to
   * properly export bools when it can, though PHP has a hard time with
   * this since they often end up as strings or ints.
   */
  public function export($var, $prefix = '') {
    if (is_array($var)) {
      if (empty($var)) {
        $output = 'array()';
      }
      else {
        $output = "array(\n";
        foreach ($var as $key => $value) {
          $output .= $prefix . "  " . $this->export($key) . " => " . $this->export($value, $prefix . '  ') . ",\n";
        }
        $output .= $prefix . ')';
      }
    }
    elseif (is_object($var) && get_class($var) === 'stdClass') {
      // var_export() will export stdClass objects using an undefined
      // magic method __set_state() leaving the export broken. This
      // workaround avoids this by casting the object as an array for
      // export and casting it back to an object when evaluated.
      $output = '(object) ' . $this->export((array) $var, $prefix);
    }
    elseif (is_bool($var)) {
      $output = $var ? 'TRUE' : 'FALSE';
    }
    else {
      $output = var_export($var, TRUE);
    }

    return $output;
  }

  public function import($file_content) {
    @eval($file_content);
    $this->data = isset($data) ? $data : NULL;
    $this->dependencies = isset($dependencies) ? $dependencies : array();
    $this->optional_configurations = isset($optional) ? $optional : array();
    $this->required_modules = isset($modules) ? $modules : array();
    $this->api_version = isset($api) ? $api : '0';
    return $this;
  }

  /**
   * Saves the configuration object into the DataStore.
   */
  public function getDataToSave() {
    $data_is_array = FALSE;
    $data_to_export = NULL;
    if (is_array($this->data)) {
      $data_is_array = TRUE;
      $data_to_export = array();
    }
    else {
      $data_to_export = new \StdClass();
    }

    if (!empty($this->keys_to_export)) {
      foreach ($this->keys_to_export as $key) {
        if ($data_is_array) {
          $data_to_export[$key] = $this->data[$key];
        }
        else {
          $data_to_export->$key = $this->data->$key;
        }
      }
    }
    else {
      $data_to_export = $this->data;
    }

    $export = '$api = ' . $this->export($this->api_version) . ";\n\n";
    $export .= '$data = ' . $this->export($data_to_export) . ";\n\n";
    $export .= '$dependencies = ' . $this->export($this->dependencies) . ";\n\n";
    $export .= '$optional = ' . $this->export($this->optional_configurations) . ";\n\n";
    $export .= '$modules = ' . $this->export($this->required_modules) . ";";

    $filename = $this->filename;
    $file_contents = "<?php\n/**\n * @file\n * {$filename}\n */\n\n" . $export . "\n";

    $this->hash = sha1($file_contents);

    return $file_contents;
  }

  public function save() {
    if ($this->checkFilePermissions($this->filename)) {
      file_put_contents(ConfigurationManagement::getStream() . '/' . $this->filename, $this->getDataToSave());
    }
    return $this;
  }

  public function delete() {
    if ($this->checkFilePermissions($this->filename)) {
      file_unmanaged_delete(ConfigurationManagement::getStream() . '/' . $this->filename);
    }
    return $this;
  }

  /**
   * Loads the configuration object from the DataStore.
   *
   * @param $file_content
   *   Optional. The content to load directly.
   * @param $source
   *   Optional. An optional path to load the configuration.
   */
  public function load($file_content = NULL, $source = NULL) {
    if (empty($this->loaded)) {
      $this->loaded = TRUE;
      if (empty($file_content)) {
        $dir = $source ? $source : ConfigurationManagement::getStream();
        if (!file_exists($dir . '/' . $this->filename)) {
          $this->data = NULL;
        }
        else {
          $file_content = drupal_substr(file_get_contents($dir . '/' . $this->filename), 6);
        }
      }
      if (!empty($file_content)) {
        $this->import($file_content);
      }
    }
    return $this;
  }
}
