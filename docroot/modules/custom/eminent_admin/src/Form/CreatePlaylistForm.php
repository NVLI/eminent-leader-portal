<?php
/**
 * @file
 * Contains \Drupal\eminent_admin\Form\CreatePlaylistForm.
 */

namespace Drupal\eminent_admin\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use \Drupal\node\Entity\Node;

/**
 * Create playlist form.
 */
class CreatePlaylistForm extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'eminent_admin_create_play_list_form';
  }

  /**
   * Form to create play list.
   */
  public function buildForm(array $form, FormStateInterface $form_state, $media_id = NULL) {
    // Store the media id in fromstate for future use.
    $storage = array('media_id' => $media_id);
    $form_state->setStorage($storage);
    $form['title'] = [
      '#title' => t('title'),
      '#type' => 'textfield',
      '#required' => TRUE,
      '#description' => t('Title for the playlist'),
    ];
    $form['description'] = [
      '#title' => t('Description'),
      '#type' => 'textfield',
      '#required' => TRUE,
      '#description' => t('Description for the playlist'),
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Create and Add'),
      '#ajax' => array(
        'callback' => '::createPlaylist',
      ),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, \Drupal\Core\Form\FormStateInterface $form_state) {
  }

  /**
   * Callback for create play list form.
   */
  public function createPlaylist(array &$form, \Drupal\Core\Form\FormStateInterface $form_state) {
    $title = $form_state->getValue('title');
    $description = $form_state->getValue('description');
    $storage = $form_state->getStorage();
    $media_id = $storage['media_id'];

    // Load the media item.
    $media_content = entity_load('media', $media_id);
    $image = $media_content->thumbnail->target_id;

    // Create node object with attached file.
    $node = Node::create([
      'type' => 'play_list',
      'field_description' => $description,
      'title'  => $title,
      'field_playlist_image' => [
        'target_id' => $image,
      ],
    ]);
    $node->save();
    // Add the media item to the created node.
    $node->field_resource->appendItem($media_id);
    $node->save();

    // Display success message to user.
    $title = t('Success');
    $response = new AjaxResponse();
    $message = t('Successfully created the playlist');
    $content = '<div class="add-media-message">' . $message . '</div>';
    $options = array(
      'dialogClass' => 'popup-dialog-class',
      'width' => '300',
      'height' => '300',
    );
    $response->addCommand(new OpenModalDialogCommand($title, $content, $options));
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'eminent_admin.settings',
    ];
  }

}
