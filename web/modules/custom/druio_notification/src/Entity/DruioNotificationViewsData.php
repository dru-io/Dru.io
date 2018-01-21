<?php

namespace Drupal\druio_notification\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for Druio notification entities.
 */
class DruioNotificationViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    // Additional information for Views integration, such as table joins, can be
    // put here.

    return $data;
  }

}
