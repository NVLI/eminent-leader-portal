<?php

namespace Drupal\slick_quiz\Plugin\views\style;

use Drupal\core\form\FormStateInterface;
use Drupal\views\Plugin\views\style\StylePluginBase;

/**
 * Style plugin to render slick quiz.
 *
 * @ingroup views_style_plugins
 *
 * @ViewsStyle(
 *   id = "slick_quiz",
 *   title = @Translation("Slick Quiz"),
 *   help = @Translation("Render a quiz items."),
 *   theme = "views_view_slick_quiz",
 *   display_types = { "normal" }
 * )
 */
class SlickQuiz extends StylePluginBase {

  /**
   * Set default options.
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['path'] = array('default' => 'slick_quiz');
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    $form['general_settings'] = array(
      '#type' => 'details',
      '#title' => t('General Settings'),
      '#open' => FALSE,
    );

    $form['general_settings']['quiz_title'] = array(
      '#type' => 'textfield',
      '#title' => t('The title for the quiz'),
      '#default_value' => (isset($this->options['general_settings']['quiz_title'])) ? $this->options['general_settings']['quiz_title'] : '',
      '#description' => t('The title for the quiz.'),
    );
    $form['general_settings']['quiz_description'] = array(
      '#type' => 'textfield',
      '#title' => t('Quiz Description'),
      '#default_value' => (isset($this->options['general_settings']['quiz_description'])) ? $this->options['general_settings']['quiz_description'] : '',
      '#description' => t('The description for the quiz.'),
    );
    $form['general_settings']['quiz_results'] = array(
      '#type' => 'textfield',
      '#title' => t('Quiz Results Description'),
      '#default_value' => (isset($this->options['general_settings']['quiz_results'])) ? $this->options['general_settings']['quiz_results'] : '',
      '#description' => t('The description for the quiz.'),
    );
    $form['general_settings']['quiz_level_1'] = array(
      '#type' => 'textfield',
      '#title' => t('Level 1 Feedback'),
      '#default_value' => (isset($this->options['general_settings']['quiz_level_1'])) ? $this->options['general_settings']['quiz_level_1'] : '',
      '#description' => t('The level 1 feedback.'),
    );
    $form['general_settings']['quiz_level_2'] = array(
      '#type' => 'textfield',
      '#title' => t('Level 2 Feedback'),
      '#default_value' => (isset($this->options['general_settings']['quiz_level_2'])) ? $this->options['general_settings']['quiz_level_2'] : '',
      '#description' => t('The level 2 feedback.'),
    );
    $form['general_settings']['quiz_level_3'] = array(
      '#type' => 'textfield',
      '#title' => t('Level 3 Feedback'),
      '#default_value' => (isset($this->options['general_settings']['quiz_level_3'])) ? $this->options['general_settings']['quiz_level_3'] : '',
      '#description' => t('The level 3 feedback.'),
    );
    $form['general_settings']['quiz_level_4'] = array(
      '#type' => 'textfield',
      '#title' => t('Level 4 Feedback'),
      '#default_value' => (isset($this->options['general_settings']['quiz_level_4'])) ? $this->options['general_settings']['quiz_level_4'] : '',
      '#description' => t('The level 4 feedback.'),
    );
    $form['general_settings']['quiz_level_5'] = array(
      '#type' => 'textfield',
      '#title' => t('Level 5 Feedback'),
      '#default_value' => (isset($this->options['general_settings']['quiz_level_5'])) ? $this->options['general_settings']['quiz_level_5'] : '',
      '#description' => t('The level 5 feedback.'),
    );

    $form['field_settings'] = array(
      '#type' => 'details',
      '#title' => t('Field Mappings'),
      '#open' => FALSE,
    );

    // The machine name of quiz question field.
    $form['field_settings']['quiz_question'] = array(
      '#type' => 'textfield',
      '#title' => t('Machine name of quiz question field'),
      '#default_value' => (isset($this->options['field_settings']['quiz_question'])) ? $this->options['field_settings']['quiz_question'] : '',
      '#description' => t('The machine name of quiz question field.'),
    );
    // The machine name of quiz field collection.
    $form['field_settings']['quiz_field'] = array(
      '#type' => 'textfield',
      '#title' => t('Machine name of quiz field collection'),
      '#default_value' => (isset($this->options['field_settings']['quiz_field'])) ? $this->options['field_settings']['quiz_field'] : '',
      '#description' => t('The machine name of quiz field collection.'),
    );
    // The machine name of quiz field collection option.
    $form['field_settings']['quiz_field_option'] = array(
      '#type' => 'textfield',
      '#title' => t('Machine name of quiz field collection option'),
      '#default_value' => (isset($this->options['field_settings']['quiz_field_option'])) ? $this->options['field_settings']['quiz_field_option'] : '',
      '#description' => t('The machine name of quiz field collection option.'),
    );
    // The machine name of quiz field collection option.
    $form['field_settings']['quiz_field_correct_option_feedback'] = array(
      '#type' => 'textfield',
      '#title' => t('Machine name of quiz field correct option feedback'),
      '#default_value' => (isset($this->options['field_settings']['quiz_field_correct_option_feedback'])) ? $this->options['field_settings']['quiz_field_correct_option_feedback'] : '',
      '#description' => t('The machine name of quiz field collection correct option feedback.'),
    );
    // The machine name of quiz field collection option.
    $form['field_settings']['quiz_field_wrong_option_feedback'] = array(
      '#type' => 'textfield',
      '#title' => t('Machine name of quiz field wrong option feedback'),
      '#default_value' => (isset($this->options['field_settings']['quiz_field_wrong_option_feedback'])) ? $this->options['field_settings']['quiz_field_wrong_option_feedback'] : '',
      '#description' => t('The machine name of quiz field collection wrong option feedback.'),
    );
    // The machine name of quiz field collection option.
    $form['field_settings']['quiz_field_option_correct'] = array(
      '#type' => 'textfield',
      '#title' => t('Machine name of quiz field collection option correct or wrong'),
      '#default_value' => (isset($this->options['field_settings']['quiz_field_option_correct'])) ? $this->options['field_settings']['quiz_field_option_correct'] : '',
      '#description' => t('The machine name of quiz field collection option correct.'),
    );
    // Extra CSS classes.
    $form['classes'] = array(
      '#type' => 'textfield',
      '#title' => t('CSS classes'),
      '#default_value' => (isset($this->options['classes'])) ? $this->options['classes'] : 'view-slick-quiz',
      '#description' => t('CSS classes for further customization of this Slick Quiz view.'),
    );
  }

}
