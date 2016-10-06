<?php
/**
 * @file
 * Contains \Drupal\eminent_admin\Form\RemoveMediaFromPlaylist.
 */

namespace Drupal\eminent_admin\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Ajax\CloseModalDialogCommand;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\Component\Utility\Unicode;

/**
 * Remove media item from playlist.
 */
class RemoveMediaFromPlaylist extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'eminent_admin_remove_media_from_playlist';
  }

  /**
   * Form to add media items to timeline.
   */
  public function buildForm(array $form, FormStateInterface $form_state, $playlist_id = NULL, $media_id = NULL) {
    // Store the media id in fromstate for future use.
    $storage['media_id'] = $media_id;
    $storage['playlist_id'] = $playlist_id;
    $form_state->setStorage($storage);

    // Load the media item.
    $media_content = entity_load('media', $media_id);
    $media_name = $media_content->name;
    // Load the playlist.
    $playlist_content = entity_load('node', $playlist_id);
    $playlist_title = $playlist_content->getTitle();
    $confirm_text = t('Do you really want to remove this item from playlist @playlist?', array('@playlist' => $playlist_title));
    $form['time_line'] = [
      '#type' => 'markup',
      '#markup' => '<h2>' . $confirm_text .'</h2>',
    ];
    $form['continue'] = [
      '#type' => 'submit',
      '#value' => $this->t('Continue'),
      '#ajax' => array(
        'callback' => '::removeMedia',
      ),
    ];
    $form['cancel'] = [
      '#type' => 'submit',
      '#value' => $this->t('Cancel'),
      '#ajax' => array(
        'callback' => '::cancelAction',
      ),
    ];
    return $form;
  }
  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, \Drupal\Core\Form\FormStateInterface $form_state) {

  }

  /**
   * Callback for remove media.
   */
  public function removeMedia(array &$form, \Drupal\Core\Form\FormStateInterface $form_state) {
    $storage = $form_state->getStorage();
    $media_id = $storage['media_id'];
    $playlist_id = $storage['playlist_id'];

    // Load the playlist.
    $playlist_content = entity_load('node', $playlist_id);
    $playlist_media = $playlist_content->field_resource->getValue();
    // Loop through the media items and remove the selected media item.
    foreach ($playlist_media as $key => $media) {
      if ($media['target_id'] == $media_id) {
        unset($playlist_media[$key]);
      }
    }
    $playlist_content->field_resource->setValue($playlist_media);
    $playlist_content->save();
    // Display success message to the user.
    $title = t('Success');
    $response = new AjaxResponse();
    $message = t('Successfully Removed the media');
    $content = '<div class="remove-media-message">' . $message . '</div>';
    $options = array(
      'dialogClass' => 'popup-dialog-class',
      'width' => '300',
      'height' => '300',
    );
    $response->addCommand(new RedirectCommand('/media/'.$media_id));
    return $response;
  }

  /**
   * Callback for remove media.
   */
  public function cancelAction(array &$form, \Drupal\Core\Form\FormStateInterface $form_state) {
    $command = new CloseModalDialogCommand();
    $response = new AjaxResponse();
    $response->addCommand($command);
    return $response;
  }
  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'eminent_admine.settings',
    ];
  }


}
