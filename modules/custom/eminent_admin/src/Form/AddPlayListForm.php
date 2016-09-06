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
    $option = $this->eminent_admin_get_content_type('play_list');
    $form['play_list'] = [
      '#title' => t('Play List'),
      '#type' => 'select',
      '#description' => 'Select the desired Play list',
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
    $play_list = $form_state->getValue('play_list');
    $media_id = $form_state->getValue('media_id');
    $node = Node::load($play_list);
    $node->field_resource->appendItem($media_id);
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
