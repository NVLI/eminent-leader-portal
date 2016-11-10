<?php

namespace Drupal\eminent_admin\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Ajax\CloseModalDialogCommand;

/**
 * Remove media item from Timeline.
 */
class RemoveMediaFromTimeline extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'eminent_admin_remove_media_from_timeline';
  }

  /**
   * Form to add media items to timeline.
   */
  public function buildForm(array $form, FormStateInterface $form_state, $timeline_id = NULL, $media_id = NULL) {
    // Store the media id in fromstate for future use.
    $storage['media_id'] = $media_id;
    $storage['timeline_id'] = $timeline_id;
    $form_state->setStorage($storage);

    // Load the timeline.
    $timeline_content = entity_load('node', $timeline_id);
    $timeline_title = $timeline_content->getTitle();
    $confirm_text = t('Do you really want to remove this item from timeline @playlist?', array('@playlist' => $timeline_title));
    $form['time_line'] = [
      '#type' => 'markup',
      '#markup' => '<h2>' . $confirm_text . '</h2>',
    ];
    $form['continue'] = [
      '#type' => 'submit',
      '#value' => $this->t('Continue'),
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
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $storage = $form_state->getStorage();
    $media_id = $storage['media_id'];
    $timeline_id = $storage['timeline_id'];

    // Load the playlist.
    $timeline_content = entity_load('node', $timeline_id);
    $timeline_paragraph = $timeline_content->field_time_line_collection_story->getValue();
    foreach ($timeline_paragraph as $key => $paragraph) {
      $paragraph_id = $paragraph['target_id'];
      $paragraph_entity = entity_load('paragraph', $paragraph_id);
      $timeline_media_id = $paragraph_entity->get('field_time_line_media_reference')->target_id;
      if ($timeline_media_id == $media_id) {
        unset($timeline_paragraph[$key]);
      }
    }
    $timeline_content->field_time_line_collection_story->setValue($timeline_paragraph);
    $timeline_content->save();
    $form_state->setRedirect('entity.media.canonical', ['media' => $media_id]);
  }

  /**
   * Callback for remove media.
   */
  public function removeMedia(array &$form, FormStateInterface $form_state) {
    $storage = $form_state->getStorage();
    $media_id = $storage['media_id'];
    $timeline_id = $storage['timeline_id'];

    // Load the playlist.
    $timeline_content = entity_load('node', $timeline_id);
    $timeline_paragraph = $timeline_content->field_time_line_collection_story->getValue();
    foreach ($timeline_paragraph as $key => $paragraph) {
      $paragraph_id = $paragraph['target_id'];
      $paragraph_entity = entity_load('paragraph', $paragraph_id);
      $timeline_media_id = $paragraph_entity->get('field_time_line_media_reference')->target_id;
      if ($timeline_media_id == $media_id) {
        unset($timeline_paragraph[$key]);
      }
    }
    $timeline_content->field_time_line_collection_story->setValue($timeline_paragraph);
    $timeline_content->save();
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
    $response->addCommand(new OpenModalDialogCommand($title, $content, $options));
    return $response;
  }

  /**
   * Callback for remove media.
   */
  public function cancelAction(array &$form, FormStateInterface $form_state) {
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
