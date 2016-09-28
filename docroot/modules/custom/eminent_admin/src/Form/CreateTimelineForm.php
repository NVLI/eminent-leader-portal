<?php
/**
 * @file
 * Contains \Drupal\eminent_admin\Form\CreateTimelineForm.
 */

namespace Drupal\eminent_admin\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use \Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;

/**
 * Play list form class.
 */
class CreateTimelineForm extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'eminent_admin_create_timeline_form';
  }

  /**
   * Form to create timeline.
   */
  public function buildForm(array $form, FormStateInterface $form_state, $media_id = NULL) {
    // Store the media id in fromstate for future use.
    $storage = array('media_id' => $media_id);
    $form_state->setStorage($storage);

    $form['title'] = [
      '#title' => t('Title'),
      '#type' => 'textfield',
      '#required' => TRUE,
      '#description' => t('Title for the timeline'),
    ];
    $form['description'] = [
      '#title' => t('Description'),
      '#type' => 'textfield',
      '#required' => TRUE,
      '#description' => t('Description for the timeline'),
    ];
    $form['featured'] = [
      '#title' => t('Display this item in Home page'),
      '#type' => 'checkbox',
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Create and Add'),
      '#ajax' => array(
        'callback' => '::createTimeline',
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
   * Callback for Create timeline form.
   */
  public function createTimeline(array &$form, \Drupal\Core\Form\FormStateInterface $form_state) {
    $title = $form_state->getValue('title');
    $description = $form_state->getValue('description');
    $featured = $form_state->getValue('featured');
    $storage = $form_state->getStorage();
    $media_id = $storage['media_id'];

    // Load the media item.
    $media_content = entity_load('media', $media_id);
    $image = $media_content->thumbnail->target_id;

    // Create node object with attached file.
    $node = Node::create([
      'type' => 'time_line_collection',
      'body' => [
        'value' => $description,
      ],
      'title'  => $title,
      'field_time_line_collection_front' => $featured,
      'field_time_line_collection_image' => [
        'target_id' => $image,
      ],
    ]);

    // Create paragraph entity.
    $media_paragraph = Paragraph::create([
      'type' => 'time_line_story',
      'field_time_line_description' => [
        'value' => $media_content->get('field_dc_description')->value,
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
    $node->field_time_line_collection_story->appendItem($media_paragraph);
    $node->save();
    // Display success message to the user.
    $title = t('Success');
    $response = new AjaxResponse();
    $message = t('Successfully created the timeline');
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
