<?php

namespace Drupal\eminent_custom\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\file\Entity\File;
use Drupal\image\Entity\ImageStyle;
use Drupal\user\Entity\User;

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
    // Fetch the current user id.
    $uid = \Drupal::currentUser()->id();
    $user = User::load(\Drupal::currentUser()->id());
    $markup = NULL;

    // Display the block only for autherised users.
    if ($uid != 0) {
      $name = $user->getUsername();
      $text = t('Welcome @name', array('@name' => $name));
      // Get current user roles.
      $user_roles = \Drupal::currentUser()->getRoles();

      if ($uid == 1 || in_array("curator", $user_roles)) {
        $image_fid = $user->get('user_picture')->target_id;
        $file = File::load($image_fid);

        if (!empty($file)) {
          $media_image_url = ImageStyle::load('exhibition_grid')->buildUrl($file->getFileUri());
        }

        if (empty($media_image_url)) {
          $media_image_url = "/themes/eminent_sardar/images/user-image.png";
        }

        $markup = '
          <a href="/user" class="dropdown-toggle user-profile-toggle" data-toggle="dropdown">
          <span class="user-image">
          <img src="' . $media_image_url . '" class="img-responsive"></span> ' . $text . '</a>
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
