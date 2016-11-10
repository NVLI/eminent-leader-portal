<?php

namespace Drupal\eminent_admin\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\Core\Url;
use Drupal\Core\Link;

/**
 * Play list form class.
 */
class AddPlayListForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'eminent_admin_add_play_list_form';
  }

  /**
   * Form to add media item to play list.
   */
  public function buildForm(array $form, FormStateInterface $form_state, $media_id = NULL) {
    // Store the media id in fromstate for future use.
    $storage = array('media_id' => $media_id);
    $form_state->setStorage($storage);

    // Fetch all the added playlists in the system.
    $option = $this->eminentAdminGetPlaylists($media_id);

    // Generate the add link.
    $create_playlist_url = Url::fromRoute('eminent_admin.CreatePlaylist', ['media_id' => $media_id]);
    // We will be displaying the link content in a popup.
    $create_playlist_url->setOptions([
      'attributes' => [
        'class' => ['use-ajax', 'button', 'button--small', 'a'],
        'data-dialog-type' => 'modal',
        'data-dialog-options' => '{"width": "70%"}',
      ],
    ]);
    $create_playlist_link = Link::fromTextAndUrl(t('Create Playlist'), $create_playlist_url)->toString();
    $help_text = t('Select the playlist from above list or @link', array('@link' => $create_playlist_link));
    $empty_text = t('Select Exhibition');
    if (empty($option)) {
      $empty_text = t('No exhibitions to show');
    }
    $form['play_list'] = [
      '#title' => t('Select Play List'),
      '#type' => 'select',
      '#required' => TRUE,
      '#empty_option' => $empty_text,
      '#description' => $help_text,
      '#options' => $option,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add to playlist'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $play_list = $form_state->getValue('play_list');
    $storage = $form_state->getStorage();
    $media_id = $storage['media_id'];
    $media_content = entity_load('media', $media_id);
    $bundle = $media_content->bundle();

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

    $playlist_node = Node::load($play_list);
    $playlist_node->field_play_list_story->appendItem($media_paragraph);
    $playlist_node->save();
    drupal_set_message(t('Successfully added media to playlist.'));
    $form_state->setRedirect('entity.media.canonical', ['media' => $media_id]);
  }

  /**
   * Callback for play list form.
   */
  public function addToPlaylist(array &$form, FormStateInterface $form_state) {
    $play_list = $form_state->getValue('play_list');
    $storage = $form_state->getStorage();
    $media_id = $storage['media_id'];
    $media_content = entity_load('media', $media_id);
    $bundle = $media_content->bundle();

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

    $playlist_node = Node::load($play_list);
    $playlist_node->field_play_list_story->appendItem($media_paragraph);
    $playlist_node->save();

    $title = t('Success');
    $response = new AjaxResponse();
    $message = t('Successfully Added media to the selected playlist');
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

  /**
   * Gathers all the playlist items added to the system.
   *
   * @param int $media_id
   *   The media id. The playlist in which this media id is not added will be
   *   returned.
   */
  public function eminentAdminGetPlaylists($media_id) {
    $options = array();
    // Database query for fetching timelines.
    $db = \Drupal::database();
    $query = $db->select('paragraph__field_play_list_media_reference');

    // Joins.
    $query->leftjoin('node__field_play_list_story', 'play_list_story',
      'play_list_story.field_play_list_story_target_id = paragraph__field_play_list_media_reference.entity_id');
    $query->leftjoin('node_field_data', 'node',
      'node.nid = play_list_story.entity_id');

    // Fields.
    $query->fields('node', array('title', 'nid'));
    $query->fields('paragraph__field_play_list_media_reference', array('field_play_list_media_reference_target_id'));

    $playlists = $query->execute();
    foreach ($playlists as $playlist) {
      if (!empty($playlist->nid)) {
        if ($playlist->field_play_list_media_reference_target_id == $media_id) {
          $playlist_node_id = $playlist->nid;
        }
        $options[$playlist->nid] = $playlist->title;
        if (!empty($playlist_node_id)) {
          unset($options[$playlist_node_id]);
        }
      }
    }
    return $options;
  }

}
