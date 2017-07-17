<?php

namespace Drupal\import\Feeds\Processor;

use Drupal\feeds\Feeds\Processor\EntityProcessorBase;

/**
 * Defines a user processor.
 *
 * Creates users from feed items.
 *
 * @FeedsProcessor(
 *   id = "entity:comment",
 *   title = @Translation("DruComment"),
 *   description = @Translation("Product."),
 *   entity_type = "comment",
 *   arguments = {"@entity.manager", "@entity.query", "@entity_type.bundle.info"},
 *   form = {
 *     "configuration" = "Drupal\feeds\Feeds\Processor\Form\DefaultEntityProcessorForm",
 *     "option" = "Drupal\feeds\Feeds\Processor\Form\EntityProcessorOptionForm",
 *   },
 * )
 */
class CommentProcessor extends EntityProcessorBase {

}
