<?php

namespace Drupal\feeds\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Access check for feeds link add list routes.
 */
class FeedAddAccessCheck implements AccessInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a FeedAddAccessCheck object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function access(AccountInterface $account) {
    // @todo Perhaps read config directly rather than load all feed types.
    $access_control_handler = $this->entityTypeManager->getAccessControlHandler('feeds_feed');

    foreach ($this->entityTypeManager->getStorage('feeds_feed_type')->loadByProperties(['status' => TRUE]) as $feed_type) {
      $access = $access_control_handler->createAccess($feed_type->id(), $account, [], TRUE);
      if ($access->isAllowed()) {
        return $access;
      }
    }

    return AccessResult::neutral();
  }

}
