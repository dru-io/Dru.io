<?php

/**
 * @file
 * Definition of Drupal\configuration\Storage\StorageCtools.
 */

namespace Drupal\configuration\Storage;

use Drupal\configuration\Storage\StoragePhp;

class StorageCtools extends StoragePHP {

  // The class that storages the data of the configuration
  protected $table;

  public function __construct($table) {
    parent::__construct();
    $this->table = $table;
  }

  /**
   * Saves the configuration object into the DataStore.
   */
  public function getDataToSave() {
    $filename = $this->filename;
    ctools_include('export');
    $export = '$api = ' . $this->export($this->api_version) . ";\n\n";
    $export .= '$data = ' . ctools_export_crud_export($this->table, $this->data) . "\n\n";
    $export .= '$dependencies = ' . $this->export($this->dependencies) . ";\n\n";
    $export .= '$optional = ' . $this->export($this->optional_configurations) . ";\n\n";
    $export .= '$modules = ' . $this->export($this->required_modules) . ";";

    $file_contents = "<?php\n/**\n * @file\n * {$filename}\n */\n\n" . $export . "\n";

    $this->hash = sha1($file_contents);

    return $file_contents;
  }

}
