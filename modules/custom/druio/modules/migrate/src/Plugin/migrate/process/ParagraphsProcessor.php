<?php

namespace Drupal\druio_migrate\Plugin\migrate\process;

use Drupal\Core\Language\LanguageInterface;
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
    $paragraphs = [];
    if (is_array($value)) {
      $paragraph = $this->getParagraph($value['type'], $row, $destination_property, 0);
      foreach ($value['fields'] as $field_name => $field_value) {
        if ($paragraph->hasField($field_name)) {
          $paragraph->$field_name = $field_value;
        }
      }
      $paragraph->save();
      $paragraphs[] = [
        'target_id' => $paragraph->id(),
        'target_revision_id' => $paragraph->getRevisionId(),
      ];
    }
    else {
      throw new MigrateException('Paragraphs value must be an array.');
    }
    return $paragraphs;
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
  public function getParagraph($type, Row $row, $field_name, $delta) {
    // Trying to get existed paragraph.
    if ($row->getDestinationProperty('nid') && $node = Node::load($row->getDestinationProperty('nid'))) {
      if (isset($node->$field_name[$delta]) && $node->$field_name[$delta]->getEntity()) {
        $paragraph = $node->$field_name[$delta]->getEntity();
        return $paragraph;
      }
    }

    // If not find, create new one.
    $paragraph = Paragraph::create(['type' => $type]);
    return $paragraph;
  }

}
