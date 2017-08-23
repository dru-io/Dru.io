<?php

namespace Drupal\druio_migrate\Plugin\migrate\source;

use Drupal\migrate\Plugin\migrate\source\SqlBase;
use Drupal\migrate\Row;

/**
 * @MigrateSource(
 *   id = "druio_file"
 * )
 */
class File extends SqlBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    return $this->select('file_managed', 'fm')
      ->fields('fm', [
        'fid',
        'uid',
        'filename',
        'uri',
        'filemime',
        'status',
        'timestamp',
      ]);
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    $fields = [
      'fid' => $this->t('File ID'),
      'uid' => $this->t('The users.uid of the user who is associated with the file'),
      'filename' => $this->t('Name of the file with no path components. This may differ from the basename of the URI if the file is renamed to avoid overwriting an existing file'),
      'uri' => $this->t('The URI to access the file (either local or remote)'),
      'filemime' => $this->t('The file’s MIME type'),
      'status' => $this->t('A field indicating the status of the file. Two status are defined in core: temporary (0) and permanent (1)'),
      'timestamp' => $this->t('UNIX timestamp for when the file was added'),
    ];

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    return [
      'fid' => [
        'type' => 'integer',
        'alias' => 'fm',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    // Before the actual migration for faster and easier testing all files uri's
    // will replaced by single image.
    $row->setSourceProperty('uri', 'sample.jpg');
    return parent::prepareRow($row);
  }

}
