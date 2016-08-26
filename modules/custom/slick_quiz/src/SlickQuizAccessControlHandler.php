<?php

/**
 * @file
 * Contains \Drupal\slick_quiz\SlickQuizAccessControlHandler.
 */

namespace Drupal\slick_quiz;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;


/**
 * Access controller for the slick_quiz entity.
 *
 * @see \Drupal\comment\Entity\Comment.
 */
class SlickQuizAccessControlHandler extends EntityAccessControlHandler {

   /**
   * {@inheritdoc}
   *
   * Link the activities to the permissions. checkAccess is called with the
   * $operation as defined in the routing.yml file.
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermission($account, 'view slick_quiz entity');

      case 'edit':
        return AccessResult::allowedIfHasPermission($account, 'edit slick_quiz entity');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete slick_quiz entity');
    }
    return AccessResult::allowed();
  }

  /**
   * {@inheritdoc}
   *
   * Separate from the checkAccess because the entity does not yet exist, it
   * will be created during the 'add' process.
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add contact entity');
  }

}
