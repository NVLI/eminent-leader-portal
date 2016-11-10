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
use Drupal\Component\Utility\Unicode;

/**
 * Add media item to Timeline form.
 */
class AddTimeLineForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'eminent_admin_add_time_line_form';
  }

  /**
   * Form to add media items to timeline.
   */
  public function buildForm(array $form, FormStateInterface $form_state, $media_id = NULL) {
    // Store the media id in fromstate for future use.
    $storage = array('media_id' => $media_id);
    $form_state->setStorage($storage);

    // Fetch all the timelines added to the system.
    $option = $this->eminentAdminGetTimelines($media_id);

    // Generate the create timeline url.
    $create_timeline_url = Url::fromRoute('eminent_admin.CreateTimeline', ['media_id' => $media_id]);
    // We will be displaying the link content in a popup.
    $create_timeline_url->setOptions([
      'attributes' => [
        'class' => ['use-ajax', 'button', 'button--small', 'a'],
        'data-dialog-type' => 'modal',
        'data-dialog-options' => '{"width": "70%"}',
      ],
    ]);
    $create_timeline_link = Link::fromTextAndUrl(t('Create Timeline'), $create_timeline_url)->toString();
    $help_text = t('Select the timeline from above list or @link', array('@link' => $create_timeline_link));
    $empty_text = t('Select Timeline');
    if (empty($option)) {
      $empty_text = t('No timelines to show');
    }
    $form['time_line'] = [
      '#title' => t('Time Line'),
      '#type' => 'select',
      '#description' => $help_text,
      '#options' => $option,
      '#required' => TRUE,
      '#empty_option' => $empty_text,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add to timeline'),
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

    $time_line = $form_state->getValue('time_line');
    $storage = $form_state->getStorage();
    $media_id = $storage['media_id'];
    $media_content = entity_load('media', $media_id);
    $bundle = $media_content->bundle();

    if ($bundle == "image") {
      $image = $media_content->field_media_image->target_id;
    }
    else {
      $image = $media_content->thumbnail->target_id;
    }

    $description = $media_content->get('field_dc_description')->value;
    // Create paragraph entity.
    $media_paragraph = Paragraph::create([
      'type' => 'time_line_story',
      'field_time_line_description' => [
        'value' => $description,
      ],
      'field_time_line_image->' => [
        ['target_id' => $image],
      ],
      'field_time_line_media_reference' => [
        ['target_id' => $media_id],
      ],
      'field_time_line_title' => [
        'value' => $media_content->get('name')->value,
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
    drupal_set_message(t('Successfully added media to timeline.'));
    $form_state->setRedirect('entity.media.canonical', ['media' => $media_id]);
  }

  /**
   * Callback for add to timeline form.
   */
  public function addToTimeline(array &$form, FormStateInterface $form_state) {
    $time_line = $form_state->getValue('time_line');
    $storage = $form_state->getStorage();
    $media_id = $storage['media_id'];
    $media_content = entity_load('media', $media_id);
    $bundle = $media_content->bundle();
    if ($bundle == "image") {
      $image = $media_content->field_media_image->target_id;
    }
    else {
      $image = $media_content->thumbnail->target_id;
    }
    $description = $media_content->get('field_dc_description')->value;
    // Create paragraph entity.
    $media_paragraph = Paragraph::create([
      'type' => 'time_line_story',
      'field_time_line_description' => [
        'value' => Unicode::truncate($description, 70),
      ],
      'field_time_line_image->' => [
        ['target_id' => $image],
      ],
      'field_time_line_media_reference' => [
        ['target_id' => $media_id],
      ],
      'field_time_line_title' => [
        'value' => $media_content->get('name')->value,
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

  /**
   * Gathers all the timelines items added to the system.
   *
   * @param int $media_id
   *   The media id. The timelines in which this media id is not added will be
   *   returned.
   */
  public function eminentAdminGetTimelines($media_id) {
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
        if (!empty($time_line_node_id)) {
          unset($options[$time_line_node_id]);
        }
      }
    }
    return $options;
  }

}
