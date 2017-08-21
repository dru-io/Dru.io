<?php

namespace Drupal\druio_migrate\Plugin\migrate\process;

use Drupal\migrate\MigrateException;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;

/**
 * @see \Drupal\migrate\Plugin\MigrateProcessInterface
 *
 * @MigrateProcessPlugin(
 *   id = "druio_paragraphs"
 * )
 */
class ParagraphsProcessor extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $result = [];
    if (is_array($value)) {
      // Maybe we need to control delta for paragraphs. If there is multiple
      // paragraphs to migrate, this will fail.
      $paragraph = $this->getParagraph($value['type'], $row, $destination_property);
      foreach ($value['fields'] as $field_name => $field_value) {
        if ($paragraph->hasField($field_name)) {
          $paragraph->$field_name = $field_value;
        }
      }
      $paragraph->save();
      $result = [
        'target_id' => $paragraph->id(),
        'target_revision_id' => $paragraph->getRevisionId(),
        'entity' => $paragraph,
      ];
    }
    else {
      throw new MigrateException('Paragraphs value must be an array.');
    }
    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function multiple() {
    return TRUE;
  }

  /**
   * Trying to get existed paragraph otherwise creates new.
   */
  public function getParagraph($type, Row $row, $field_name) {
    // Trying to get existed paragraph.
    if ($row->getDestinationProperty('nid') && $node = Node::load($row->getDestinationProperty('nid'))) {
      if (isset($node->$field_name[0]) && $node->$field_name[0]->getEntity()) {
        $paragraph = $node->$field_name[0]->getEntity();
        return $paragraph;
      }
    }

    // If not find, create new one.
    $paragraph = Paragraph::create(['type' => $type]);
    return $paragraph;
  }

}
