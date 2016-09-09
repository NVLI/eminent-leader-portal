<?php
/**
 * @file
 * Contains \Drupal\eminent_admin\Form\AddTimeLineForm.
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
use Drupal\paragraphs\Entity\Paragraph;

/**
 * Time line form class.
 */
class AddTimeLineForm extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'eminent_admin_add_time_line_form';
  }

  /**
   * form to add time line.
   */
  public function buildForm(array $form, FormStateInterface $form_state, $media_id = NULL) {
    $option = $this->eminent_admin_get_timelines($media_id);
    if (!empty($option)) {
      $form['time_line'] = [
        '#title' => t('Time Line'),
        '#type' => 'select',
        '#description' => 'Select the desired Time Line',
        '#options' => $option,
      ];

       $form['media_id'] = [
        '#type' => 'hidden',
        '#default_value' => $media_id,
      ];

      $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add to timeline'),
      '#ajax' => array(
         'callback' => '::add_to_timeline',
       ),
      ];
    }
    else {
      $form['media_id'] = [
        '#type' => 'markup',
        '#markup' => t('This media item is added to all the timelines in the system'),
      ];
    }
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
   * Callback for play list form.
   *
   * Add a media to play list programatically.
   */
  public function add_to_timeline(array &$form, \Drupal\Core\Form\FormStateInterface $form_state) {
    $time_line = $form_state->getValue('time_line');
    $media_id = $form_state->getValue('media_id');
    $media_content = entity_load('media', $media_id);
    // Create paragraph entity.
    $media_paragraph = Paragraph::create([
      'type' => 'time_line_story',
      'field_time_line_description' => [
          'value' => $media_content->get('field_dc_description')->value,
          'format' => 'wysiwyg',
        ],
        'field_time_line_image->' => [
          ['target_id' => $media_content->thumbnail->target_id],
        ],
        'field_time_line_media_reference' => [
          ['target_id' => $media_id],
        ],
        'field_time_line_title' => [
          'value' => $media_content->get('field_dc_title')->value,
        ],
        'field_time_line_date' => [
          'value' => $media_content->get('field_dc_date')->value,
        ],
    ]);
    $media_paragraph->save();
    $paragraph_id = $media_paragraph->id();

    // Attach the created paragraph entity to the timeline.
    $timeline_node = Node::load($time_line);
    $timeline_node->field_time_line_collection_story->appendItem($media_paragraph);
    $timeline_node->save();

    $title = t('Success');
    $response = new AjaxResponse();
    $message = t('Successfully Added media to the selected timeline');
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
      'eminent_admine.settings',
    ];
  }

  public function eminent_admin_get_timelines($media_id) {
    $options = array();

    // Database query for fetching timelines.
    $db = \Drupal::database();
    $query = $db->select('paragraph__field_time_line_media_reference');

    // Joins.
    $query->leftjoin('node__field_time_line_collection_story', 'time_line_story',
      'time_line_story.field_time_line_collection_story_target_id = paragraph__field_time_line_media_reference.entity_id');
    $query->leftjoin('node_field_data', 'node',
      'node.nid = time_line_story.entity_id');

    // Fields.
    $query->fields('node', array('title', 'nid'));
    $query->fields('paragraph__field_time_line_media_reference', array('field_time_line_media_reference_target_id'));

    $time_lines = $query->execute();
    foreach ($time_lines as $time_line) {
      if (!empty($time_line->nid)) {
        if ($time_line->field_time_line_media_reference_target_id == $media_id) {
          $time_line_node_id = $time_line->nid;
        }
        $options[$time_line->nid] = $time_line->title;
        unset($options[$time_line_node_id]);
      }
    }
    return $options;
  }
}
