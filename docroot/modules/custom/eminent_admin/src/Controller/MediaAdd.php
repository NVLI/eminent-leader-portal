<?php

namespace Drupal\eminent_admin\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * MediaAdd Class. Contains the methods for playlist/timeline/quote creation.
 */
class MediaAdd extends ControllerBase {

  /**
   * Adds the media item to playlist/timeline depending on the parametrs.
   */
  public function addMedia($media_id, $group) {
    // Get the add media to playlist/timeline form.
    if ($group == 'playlist') {
      $form = \Drupal::formBuilder()->getForm('\Drupal\eminent_admin\Form\AddPlayListForm', $media_id);
    }
    elseif ($group == 'timeline') {
      $form = \Drupal::formBuilder()->getForm('\Drupal\eminent_admin\Form\AddTimeLineForm', $media_id);
    }
    return $form;
  }

  /**
   * Displays the form to create new playlist.
   */
  public function createPlaylist($media_id) {
    // Get the play list form.
    $form = \Drupal::formBuilder()->getForm('\Drupal\eminent_admin\Form\CreatePlaylistForm', $media_id);
    return $form;
  }

  /**
   * Displays the form to create new quote.
   */
  public function createQuote() {
    // Get the quote form.
    $form = \Drupal::formBuilder()->getForm('\Drupal\eminent_admin\Form\CreateQuoteForm');
    return $form;
  }

  /**
   * Displays the form to create new timeline.
   */
  public function createTimeline($media_id) {
    // Get the time line form.
    $form = \Drupal::formBuilder()->getForm('\Drupal\eminent_admin\Form\CreateTimelineForm', $media_id);
    return $form;
  }

}
