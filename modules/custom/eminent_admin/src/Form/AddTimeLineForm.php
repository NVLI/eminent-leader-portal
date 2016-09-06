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
    $option = $this->eminent_admin_get_content_type('time_line_collection');
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
    '#value' => $this->t('Add'),
    ];
    
    return $form;
  }

  /**
   * Callback for play list form.
   *
   * Add a media to play list programatically.
   */
  public function submitForm(array &$form, \Drupal\Core\Form\FormStateInterface $form_state) {
    $time_line = $form_state->getValue('time_line');
    $media_id = $form_state->getValue('media_id');
    $media_content = entity_load('media', $media_id);
    $paragraph = Paragraph::create([
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
    $paragraph->save();
    $node = Node::load($time_line);
    $node->field_time_line_collection_story->appendItem($paragraph->id());
    $node->save();
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'eminent_admine.settings',
    ];
  }
  
  public function eminent_admin_get_content_type($content_type) {
    $options['select'] = t('--- SELECT ---');
    $query = \Drupal::entityQuery('node')
      ->condition('type', $content_type);
    $result = $query->execute();
    if (!empty($result)) {
      $nodes = node_load_multiple($result);
      foreach ($nodes as $node) {
       $options[$node->id()] = $node->getTitle();
      }
    }
    return $options;
  }
}
