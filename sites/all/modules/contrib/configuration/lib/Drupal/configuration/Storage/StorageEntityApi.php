<?php

/**
 * @file
 * Definition of Drupal\configuration\Storage\StorageEntityApi.
 */

namespace Drupal\configuration\Storage;

use Drupal\configuration\Storage\StoragePhp;

class StorageEntityApi extends StoragePHP {

  // The class that storages the data of the configuration
  protected $entity_type;

  public function __construct($entity_type) {
    parent::__construct();
    $this->entity_type = $entity_type;
  }

  /**
   * Saves the configuration object into the DataStore.
   */
  public function getDataToSave() {

    $filename = $this->filename;

    $export = '$api = ' . $this->export($this->api_version) . ";\n\n";
    $export .= '$data = entity_import(\'' . $this->entity_type . "', '" . addcslashes(entity_export($this->entity_type, $this->data, '  '), '\\\'') . "');\n\n";
    $export .= '$dependencies = ' . $this->export($this->dependencies) . ";\n\n";
    $export .= '$optional = ' . $this->export($this->optional_configurations) . ";\n\n";
    $export .= '$modules = ' . $this->export($this->required_modules) . ";";

    $file_contents = "<?php\n/**\n * @file\n * {$filename}\n */\n\n" . $export . "\n";

    $this->hash = sha1($file_contents);

    return $file_contents;
  }

}
