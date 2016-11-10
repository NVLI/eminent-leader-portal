<?php

namespace Drupal\eminent_admin\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;

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
    // Fetch the categories for playlist.
    $vid = 'subject_classification';
    $categories = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($vid);
    $options = array();
    foreach ($categories as $category) {
      $options[$category->tid] = $category->name;
    }

    // Store the media id in fromstate for future use.
    $storage = array('media_id' => $media_id);
    $form_state->setStorage($storage);
    $form['title'] = [
      '#title' => t('Title'),
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
    $form['category'] = [
      '#title' => t('Category'),
      '#type' => 'select',
      '#description' => t('Category for the playlist'),
      '#options' => $options,
      '#empty_option' => t('Select Category'),
    ];
    $form['featured'] = [
      '#title' => t('Display this item in Home page'),
      '#type' => 'checkbox',
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Create and Add'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $title = $form_state->getValue('title');
    $description = $form_state->getValue('description');
    $featured = $form_state->getValue('featured');
    $category = $form_state->getValue('category');
    $storage = $form_state->getStorage();
    $media_id = $storage['media_id'];

    // Load the media item.
    $media_content = entity_load('media', $media_id);
    $bundle = $media_content->bundle();
    if ($bundle == "image") {
      $image = $media_content->field_media_image->target_id;
    }
    else {
      $image = $media_content->thumbnail->target_id;
    }

    // Create node object with attached file.
    if (empty($category)) {
      $node = Node::create([
        'type' => 'play_list',
        'field_description' => $description,
        'field_playlist_featured' => $featured,
        'title'  => $title,
        'field_playlist_image' => [
          'target_id' => $image,
        ],
      ]);
    }
    else {
      $node = Node::create([
        'type' => 'play_list',
        'field_description' => $description,
        'field_playlist_featured' => $featured,
        'field_category' => $category,
        'title'  => $title,
        'field_playlist_image' => [
          'target_id' => $image,
        ],
      ]);
    }

    $node->save();

    $description = $media_content->get('field_dc_description')->value;

    $media_paragraph = Paragraph::create([
      'type' => 'play_list_story',
      'field_play_list_description' => [
        'value' => $description,
      ],
      'field_play_list_media_reference' => [
        ['target_id' => $media_id],
      ],
      'field_play_list_title' => [
        'value' => $media_content->get('name')->value,
      ],
    ]);

    $media_paragraph->save();
    $paragraph_id = $media_paragraph->id();
    // Add the media item to the created node.
    $node->field_play_list_story->appendItem($media_paragraph);
    $node->save();
    drupal_set_message(t('Successfully created playlist and added the media item.'));
    $form_state->setRedirect('entity.media.canonical', ['media' => $media_id]);
  }

  /**
   * Callback for create play list form.
   */
  public function createPlaylist(array &$form, FormStateInterface $form_state) {
    $title = $form_state->getValue('title');
    $description = $form_state->getValue('description');
    $featured = $form_state->getValue('featured');
    $category = $form_state->getValue('category');
    $storage = $form_state->getStorage();
    $media_id = $storage['media_id'];

    // Load the media item.
    $media_content = entity_load('media', $media_id);
    $bundle = $media_content->bundle();
    if ($bundle == "image") {
      $image = $media_content->field_media_image->target_id;
    }
    else {
      $image = $media_content->thumbnail->target_id;
    }

    // Create node object with attached file.
    $node = Node::create([
      'type' => 'play_list',
      'field_description' => $description,
      'field_playlist_featured' => $featured,
      'field_category' => $category,
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
