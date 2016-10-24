<?php
/**
 * @file
 * Contains \Drupal\curtain_raiser\Form\InaugurationForm.
 */

namespace Drupal\curtain_raiser\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Url;
use Drupal\Core\Link;

/**
 * Play list form class.
 */
class InaugurationForm extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'curtain_raiser_inauguration_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
  $form['inaugurate_password'] = array(
    '#type' => 'password',
    '#title' => t('Password:'),
    '#required' => TRUE,
  );
  $form['actions']['#type'] = 'actions';
  $form['actions']['submit'] = array(
    '#type' => 'submit',
    '#value' => $this->t('Inaugurate'),
    '#button_type' => 'primary',
  );
  return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Validate submitted form data.
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, \Drupal\Core\Form\FormStateInterface $form_state) {

  }



}
