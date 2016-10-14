<?php

/**
 * @file
 * Contains \Drupal\eminent_custom\Plugin\Block\RelatedMedia.
 */

namespace Drupal\eminent_custom\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Url;
use Drupal\Core\Link;
use \Drupal\user\Entity\User;
use Drupal\Component\Utility\Unicode;
use Drupal\file\Entity\File;
use Drupal\image\Entity\ImageStyle;
use Drupal\user\Entity;
use Drupal\Core\Render\Markup;

/**
 * Provides a 'Related Media' block.
 *
 * @Block(
 *   id = "eminent_related_media",
 *   admin_label = @Translation("Eminent Related Media"),
 *   category = @Translation("Blocks")
 * )
 */
class RelatedMedia extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Get the current user.
    $user = \Drupal::currentUser();
    $media_items = $output = array();
    $media_image_url = $addtotimelinelink = $addtoplaylistlink = NULL;
    // Load the current media item.
    $media = \Drupal::request()->attributes->get('media');
    if (!empty($media)) {
      $current_media_id = $media->id();
      // Fetch the category tid.
      $subject_classification_tid = $media->field_subject_classification->target_id;
      // Fetch the media items under the subject classification tid.
      $media_ids = \Drupal::entityQuery('media')
       ->condition('field_subject_classification', $subject_classification_tid)
       ->condition('status', '1')
       ->range(0, 8)
       ->execute();
      $media_items = entity_load_multiple('media', $media_ids);
      foreach ($media_items as $media_item) {
        $media_id = $media_item->id();
        // Check whether the user has permission to create Time Line Collection.
        if ($user->hasPermission('create time_line_collection content')) {

          // Generate the add link.
          $add_playlist_url = Url::fromRoute('eminent_admin.addPlaylistTimeline', ['media_id' => $media_id, 'group' => 'timeline']);

          // We will be displaying the link content in a popup.
          $add_playlist_url->setOptions([
            'attributes' => [
              'class' => ['use-ajax', 'button', 'button--small', 'btn', 'btn-default'],
              'data-dialog-type' => 'modal',
              'data-dialog-options' => '{"width": "30%"}',
              'data-toggle' => "tooltip",
              'title' => "Add To Timeline",
            ],
          ]);

          $addtotimelinelink =  array(
            '#type' => 'markup',
            '#markup' => Link::fromTextAndUrl(Markup::create('<i class="fa fa-flag" aria-hidden="true"></i>'), $add_playlist_url)->toString(),
            '#attached' => ['library' => ['core/drupal.dialog.ajax']],
            '#cache' => [
              'max-age' => 0,
            ],
          );
        }
        // Check whether the user has permission to create Time Line Collection.
        if ($user->hasPermission('create play_list content')) {

          // Generate the add link.
          $add_playlist_url = Url::fromRoute('eminent_admin.addPlaylistTimeline', ['media_id' => $media_id, 'group' => 'playlist']);

          // We will be displaying the link content in a popup.
          $add_playlist_url->setOptions([
            'attributes' => [
              'class' => ['use-ajax', 'button', 'button--small', 'btn', 'btn-default'],
              'data-dialog-type' => 'modal',
              'data-dialog-options' => '{"width": "30%"}',
              'data-toggle' => "tooltip",
              'title' => "Add To Playlist",
            ],
          ]);

          $addtoplaylistlink =  array(
            '#type' => 'markup',
            '#markup' => Link::fromTextAndUrl(Markup::create('<i class="fa fa-list" aria-hidden="true"></i>'), $add_playlist_url)->toString(),
            '#attached' => ['library' => ['core/drupal.dialog.ajax']],
            '#cache' => [
              'max-age' => 0,
            ],
          );
        }
        if ($media_id != $current_media_id) {
          // Get the images to display in the block.
          if ($media_item->bundle() == "image") {
            $image = $media_content->field_media_image->target_id;
            $file = File::load($image);
            if(!empty($file)) {
              $media_image_url = ImageStyle::load('exhibition_grid')->buildUrl($file->getFileUri());
            }
          }
          elseif ($media_item->bundle() == "audio") {
            $media_image_url = '/themes/eminent_sardar/images/audio.png';
          }
          elseif ($media_item->bundle() == "document") {
            $media_image_url = '/themes/eminent_sardar/images/pdf.png';
          }
          elseif ($media_item->bundle() == "video") {
            $media_image_url = '/themes/eminent_sardar/images/video.png';
          }
          $media_teaser_title = $media_item->get('field_dc_title')->value;
          $title = $truncated_title = $media_teaser_title;
          if (strlen($media_teaser_title) > 30) {
            $truncated_title = Unicode::truncate($media_teaser_title, 30) . '...';
          }
          $output[$media_id] = array(
            'image' => $media_image_url,
            'title' => $title,
            'truncated_title' => $truncated_title,
            'media_id' => $media_id,
            'addtoplaylist' => $addtoplaylistlink,
            'addtotimeline' => $addtotimelinelink,
          );
        }
      }
    }
    return array(
      '#theme' => 'related_media',
      '#media_items' => $output,
    );
  }

}
