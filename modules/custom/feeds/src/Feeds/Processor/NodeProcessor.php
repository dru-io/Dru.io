<?php

namespace Drupal\feeds\Feeds\Processor;

/**
 * Defines a node processor.
 *
 * Creates nodes from feed items.
 *
 * @FeedsProcessor(
 *   id = "entity:node",
 *   title = @Translation("Node"),
 *   description = @Translation("Creates nodes from feed items."),
 *   entity_type = "node",
 *   arguments = {"@entity_type.manager", "@entity.query", "@entity_type.bundle.info"},
 *   form = {
 *     "configuration" = "Drupal\feeds\Feeds\Processor\Form\DefaultEntityProcessorForm",
 *     "option" = "Drupal\feeds\Feeds\Processor\Form\EntityProcessorOptionForm",
 *   },
 * )
 */
class NodeProcessor extends EntityProcessorBase {

  /**
   * {@inheritdoc}
   */
  public function entityLabel() {
    return $this->t('Node');
  }

  /**
   * {@inheritdoc}
   */
  public function entityLabelPlural() {
    return $this->t('Nodes');
  }

}
