<?php

/**
 * @file
 * Contains \Drupal\slick_quiz\SlickQuizViewsData.
 */

namespace Drupal\slick_quiz;

use Drupal\views\EntityViewsData;

/**
 * Add entity fields to the views.
 */
class SlickQuizViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();
    // Customize views data definitions...
    return $data;
  }

}
