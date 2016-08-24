<?php

/**
 * @file
 * Contains \Drupal\slick_quiz\Access\DeleteAccessCheck.
 */

namespace Drupal\slick_quiz\Access;

use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Checks access for displaying configuration translation page.
 */
class DeleteAccessCheck implements AccessInterface {

  /**
   * An access check.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   Run access checks for this account.
   */
  public function access($slick_quiz, AccountInterface $account) {
    return AccessResult::allowedIf($account->hasPermission('delete own slick_quiz entity') && $this->isOwnEntity($slick_quiz));
  }

  /**
   * Method to find the entity's owner.
   */
  public function isOwnEntity($slick_quiz) {

    // Get current user.
    $user = \Drupal::currentUser();
    $current_uid = $user->id();

    if ($current_uid == 1) {
      return TRUE;
    }

    $query = \Drupal::entityQuery('slick_quiz')
            ->condition('id', $slick_quiz);
    $eids = $query->execute();
    $entities = entity_load_multiple('slick_quiz', $eids);

    foreach ($entities as $entity) {
      $uid_value = $entity->user_id->getValue();
      $uid = $uid_value['0']['target_id'];
    }

    if ($uid == $current_uid) {
      return TRUE;
    }
    else {
      return FALSE;
    }
  }

}
