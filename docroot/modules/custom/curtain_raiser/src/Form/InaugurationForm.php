<?php

namespace Drupal\curtain_raiser\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

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

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['inaugurate_password'] = array(
      '#type' => 'password',
      '#required' => TRUE,
      '#prefix' => '<div id = "inagruate-pw>',
      '#suffix' => '</div>',
    );
    $form['actions']['submit'] = array(
      '#type' => 'image_button',
      '#value' => $this->t('Inaugurate'),
      '#src' => '/modules/custom/curtain_raiser/images/button.png',
    );
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Validate submitted form data.
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

  }

}
