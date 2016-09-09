<?php
/**
 * @file
 * Contains \Drupal\eminent_admin\Form\AddPlayListForm.
 */

namespace Drupal\eminent_admin\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\node\Entity\Node;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Ajax\CssCommand;

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
   * form to add play list.
   */
  public function buildForm(array $form, FormStateInterface $form_state, $media_id = NULL) {
    $option = $this->eminent_admin_get_playlists($media_id);
    if (!empty($option)) {
      $form['play_list'] = [
        '#title' => t('Select Play List'),
        '#type' => 'select',
        '#required' => TRUE,
        '#description' => 'Select the desired Play list',
        '#options' => $option,
      ];

      $form['media_id'] = [
        '#type' => 'hidden',
        '#default_value' => $media_id,
      ];

      $form['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Add to playlist'),
        '#ajax' => array(
          'callback' => '::add_to_playlist',
        ),
      ];
    }
    else {
      $form['media_id'] = [
        '#type' => 'markup',
        '#markup' => t('This item is added to all the playlists in the system'),
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, \Drupal\Core\Form\FormStateInterface $form_state) {
    $play_list = $form_state->getValue('play_list');
    $media_id = $form_state->getValue('media_id');
    $node = Node::load($play_list);
    $node->field_resource->appendItem($media_id);
    $node->save();
  }

  /**
   * Callback for play list form.
   *
   * Add a media to play list programatically.
   */
  public function add_to_playlist(array &$form, \Drupal\Core\Form\FormStateInterface $form_state) {
    $play_list = $form_state->getValue('play_list');
    $media_id = $form_state->getValue('media_id');
    $node = Node::load($play_list);
    $node->field_resource->appendItem($media_id);
    $node->save();
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

  public function eminent_admin_get_playlists($media_id) {
    // Database query for fetching timelines.
    $db = \Drupal::database();
    $query = $db->select('node__field_resource');

    $query->leftjoin('node_field_data', 'node',
      'node.nid = node__field_resource.entity_id');

    // Fields.
    $query->fields('node', array('title', 'nid'));
    $query->fields('node__field_resource', array('field_resource_target_id'));

    $playlists = $query->execute();
    foreach ($playlists as $playlist) {
      if (!empty($playlist->nid)) {
        if ($playlist->field_resource_target_id == $media_id) {
          $playlist_node_id = $playlist->nid;
        }
        $options[$playlist->nid] = $playlist->title;
        unset($options[$playlist_node_id]);
      }
    }
    return $options;
  }
}
