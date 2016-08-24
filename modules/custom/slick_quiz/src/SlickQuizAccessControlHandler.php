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

}

/**
 * {@inheritdoc}
 *
 * Separate from the checkAccess because the entity does not yet exist, it
 * will be created during the 'add' process.
 */
protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
  return AccessResult::allowedIfHasPermission($account, 'add slick_quiz entity');
}

}