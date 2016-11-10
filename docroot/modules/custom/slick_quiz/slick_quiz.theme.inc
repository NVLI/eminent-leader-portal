<?php

/**
 * @file
 * Theme for Slick Quiz views.
 */

use Drupal\field_collection\Entity\FieldCollectionItem;

/**
 * Preprocess for the slick quiz view.
 */
function template_preprocess_views_view_slick_quiz(&$variables) {
  $slick_quiz = array();
  $correct = TRUE;
  // View options set by user.
  $options = $variables['view']->style_plugin->options;

  // Fetch the quiz data from the view settings.
  $slick_quiz['info']['name'] = $options['general_settings']['quiz_title'];
  $slick_quiz['info']['main'] = $options['general_settings']['quiz_description'];
  $slick_quiz['info']['results'] = $options['general_settings']['quiz_results'];
  $slick_quiz['info']['level1'] = $options['general_settings']['quiz_level_1'];
  $slick_quiz['info']['level2'] = $options['general_settings']['quiz_level_2'];
  $slick_quiz['info']['level3'] = $options['general_settings']['quiz_level_3'];
  $slick_quiz['info']['level4'] = $options['general_settings']['quiz_level_4'];
  $slick_quiz['info']['level5'] = $options['general_settings']['quiz_level_5'];

  // Get the machine names of the quiz fields.
  $quiz_field_question = $options['field_settings']['quiz_question'];
  $quiz_field_options = $options['field_settings']['quiz_field'];
  $quiz_field_option = $options['field_settings']['quiz_field_option'];
  $quiz_field_correct_option_feedback = $options['field_settings']['quiz_field_correct_option_feedback'];
  $quiz_field_wrong_option_feedback = $options['field_settings']['quiz_field_wrong_option_feedback'];
  $quiz_field_option_correct = $options['field_settings']['quiz_field_option_correct'];

  // Build a two-dimension array with quiz options.
  $quiz_questions = array();
  // Loop through the view results to generate slick quiz questions array.
  foreach ($variables['view']->result as $id => $result) {
    $entity = $result->_entity;
    $quiz_options = $entity->get($quiz_field_options)->getValue();
    $title = $entity->get($quiz_field_question)->getValue();
    $slick_quiz['questions'][$id]["q"] = $title['0']['value'];

    foreach ($quiz_options as $option_item) {
      $correct = TRUE;
      $option = FieldCollectionItem::load($option_item['value']);
      $option_val = $option->get($quiz_field_option)->getValue();
      $option_correct = $option->get($quiz_field_option_correct)->getValue();
      if ($option_correct['0']['value'] == 0) {
        $correct = FALSE;
      }
      $slick_quiz['questions'][$id]["a"][] = array(
        'option' => $option_val['0']['value'],
        'correct' => $correct,
      );
      $correct_feedback = $entity->get($quiz_field_correct_option_feedback)->getValue();
      $wrong_feedback = $entity->get($quiz_field_wrong_option_feedback)->getValue();
      $slick_quiz['questions'][$id]["correct"] = $correct_feedback['0']['value'];
      $slick_quiz['questions'][$id]["incorrect"] = $wrong_feedback['0']['value'];
    }
    $slick_quiz_config = json_encode($slick_quiz);

    // Attach the js.
    $variables['#attached']['library'][] = 'slick_quiz/DrupalSlickQuiz';
    $variables['#attached']['library'][] = 'slick_quiz/SlickQuiz';

    // Pass the configuration settings to jquery.
    $variables['#attached']['drupalSettings']['DrupalSlickQuiz']['slick_conf'] = $slick_quiz_config;
  }
}
