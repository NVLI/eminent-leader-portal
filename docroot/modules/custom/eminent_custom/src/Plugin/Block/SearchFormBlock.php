<?php

/**
 * @file
 * Contains \Drupal\eminent_custom\Plugin\Block\RelatedMedia.
 */

namespace Drupal\eminent_custom\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormInterface;

/**
 * Provides a 'Search Form' block.
 *
 * @Block(
 *   id = "eminent_search",
 *   admin_label = @Translation("Search Form"),
 *   category = @Translation("Blocks")
 * )
 */
class SearchFormBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $form = \Drupal::formBuilder()->getForm('Drupal\eminent_custom\Form\SearchForm');
    return [
      $form,
       '#cache' => [
         'max-age' => 0,
       ],
    ];
  }

}
