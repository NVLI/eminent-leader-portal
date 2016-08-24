<?php

/**
 * @file
 * Contains \Drupal\slick_quiz\Access\AddAccessCheck.
 */

namespace Drupal\slick_quiz\Access;

use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Checks access for displaying configuration translation page.
 */
class AddAccessCheck implements AccessInterface {

  /**
   * An access check.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   Run access checks for this account.
   */
  public function access(AccountInterface $account) {
    return AccessResult::allowedIf($account->hasPermission('add slick_quiz entity') && $this->isExistEntity());
  }

  /**
   * Method to find the entity exist or not for the current user.
   */
  public function isExistEntity() {

    // Get current user.
    $user = \Drupal::currentUser();
    $uid = $user->id();

    $query = \Drupal::entityQuery('slick_quiz')
            ->condition('user_id', $uid);
    $eids = $query->execute();

    if (empty($eids)) {
      return TRUE;
    }
    else {
      return FALSE;
    }
  }

}
