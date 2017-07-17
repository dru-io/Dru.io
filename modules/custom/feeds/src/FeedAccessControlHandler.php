<?php

namespace Drupal\feeds;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines an access controller for the feeds_feed entity.
 *
 * @see \Drupal\feeds\Entity\Feed
 */
class FeedAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $feed, $operation, AccountInterface $account) {
    $has_perm = $account->hasPermission('administer feeds') || $account->hasPermission("$operation {$feed->bundle()} feeds");

    switch ($operation) {
      case 'view':
      case 'create':
      case 'update':
        return AccessResult::allowedIf($has_perm);

      case 'import':
      case 'clear':
        return AccessResult::allowedIf($has_perm && !$feed->isLocked());

      case 'unlock':
        return AccessResult::allowedIf($has_perm && $feed->isLocked());

      case 'delete':
        return AccessResult::allowedIf($has_perm && !$feed->isLocked() && !$feed->getItemCount() && !$feed->isNew());

      default:
        return AccessResult::neutral();
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    $has_perm = $account->hasPermission('administer feeds') || $account->hasPermission("create $entity_bundle feeds");
    return AccessResult::allowedIf($has_perm);
  }

}
