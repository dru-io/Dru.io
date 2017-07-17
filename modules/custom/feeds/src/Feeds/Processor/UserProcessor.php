<?php

namespace Drupal\feeds\Feeds\Processor;

/**
 * Defines a user processor.
 *
 * Creates users from feed items.
 *
 * @FeedsProcessor(
 *   id = "entity:user",
 *   title = @Translation("User"),
 *   description = @Translation("Creates users from feed items."),
 *   entity_type = "user",
 *   arguments = {"@entity_type.manager", "@entity.query", "@entity_type.bundle.info"},
 *   form = {
 *     "configuration" = "Drupal\feeds\Feeds\Processor\Form\DefaultEntityProcessorForm",
 *     "option" = "Drupal\feeds\Feeds\Processor\Form\EntityProcessorOptionForm",
 *   },
 * )
 */
class UserProcessor extends EntityProcessorBase {

}
