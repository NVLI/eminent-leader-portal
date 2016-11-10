<?php

namespace Drupal\eminent_admin\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Url;
use Drupal\Core\Link;

/**
 * Provides a 'Add to playlist' block.
 *
 * @Block(
 *   id = "add_to_playlist",
 *   admin_label = @Translation("Add to playlist"),
 *   category = @Translation("Blocks")
 * )
 */
class MediaAddtoPlaylist extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Fetch the media id.
    $media_id = \Drupal::routeMatch()->getRawParameter('media');
    // Generate the add link.
    $add_playlist_url = Url::fromRoute('eminent_admin.addPlaylistTimeline', ['media_id' => $media_id, 'group' => 'playlist']);
    // We will be displaying the link content in a popup.
    $add_playlist_url->setOptions([
      'attributes' => [
        'class' => ['use-ajax', 'button', 'button--small'],
        'data-dialog-type' => 'modal',
        'data-dialog-options' => '{"width": "30%"}',
      ],
    ]);
    return array(
      '#type' => 'markup',
      '#markup' => Link::fromTextAndUrl(t('Add to playlist'), $add_playlist_url)->toString(),
      '#attached' => ['library' => ['core/drupal.dialog.ajax']],
      '#cache' => [
        'max-age' => 0,
      ],
    );
  }

}
