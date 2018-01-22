<?php

namespace Drupal\druio_notification;

use Drupal\Core\Entity\EntityTypeManager;

/**
 * Class DruioNotificationHelperService.
 */
class DruioNotificationHelperService {

  /**
   * @var EntityTypeManager.
   */
  protected $entityTypeManager;

  /**
   * Constructs a new DruioNotificationHelperService object.
   */
  public function __construct(EntityTypeManager $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

}
