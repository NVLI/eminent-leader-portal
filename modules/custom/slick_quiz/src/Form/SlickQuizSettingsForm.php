<?php
/**
 * @file
 * Contains Drupal\slick_quiz\Form\SlickQuizSettingsForm.
 */

namespace Drupal\slick_quiz\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class SlickQuizSettingsForm.
 *
 * @package Drupal\slick_quiz\Form
 *
 * @ingroup slick_quiz
 */
class SlickQuizSettingsForm extends FormBase {
  /**
   * Returns a unique string identifying the form.
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return 'slick_quiz_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Empty implementation of the abstract submit class.
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['slick_quiz_settings']['#markup'] = 'Settings form for SlickQuiz entity. Manage field settings here.';
    return $form;
  }

}
