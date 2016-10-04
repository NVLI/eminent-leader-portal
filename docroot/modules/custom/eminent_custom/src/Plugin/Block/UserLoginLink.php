<?php

/**
 * @file
 * Contains \Drupal\eminent_custom\Plugin\Block\UserLoginLink.
 */

namespace Drupal\eminent_custom\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Url;
use Drupal\Core\Link;
use \Drupal\user\Entity\User;

/**
 * Provides a 'User login links' block.
 *
 * @Block(
 *   id = "eminent_user_login_links",
 *   admin_label = @Translation("User login links"),
 *   category = @Translation("Blocks")
 * )
 */
class UserLoginLink extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $user = \Drupal::currentUser();
    $uid = \Drupal::currentUser()->id();
    if ($uid != 0) {
      $name = $user->getUsername();
      $text = t('Welcome @name', array('@name' => $name));
      // Get current user roles.
      $user_roles = \Drupal::currentUser()->getRoles();
      if ($uid == 1 || in_array("curator", $user_roles)) {
      $markup = '
        <a href="/user" class="dropdown-toggle user-profile-toggle" data-toggle="dropdown">
        <span class="user-image">
        <img src="/themes/eminent_sardar/images/man4.jpg" class="img-responsive"></span> ' . $text . '</a>
          <ul class="dropdown-menu">
            <li><a href="/quote/add">Add Quote</a></li>
            <li><a href="/node/add/time_line_collection">Add Timeline</a></li>
            <li><a href="/node/add/play_list">Add Playlist</a></li>
            <li><a href="/node/add/quiz">Add Quiz item</a></li>
          </ul>
       ';
      }
    }
    return array(
      '#type' => 'markup',
      '#markup' => $markup,
      '#cache' => [
        'max-age' => 0,
      ],
    );
  }

}
