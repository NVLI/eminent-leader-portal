<?php

namespace Drupal\eminent_custom\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'Header Search' block.
 *
 * @Block(
 *   id = "eminent_header_search",
 *   admin_label = @Translation("Header Search"),
 *   category = @Translation("Blocks")
 * )
 */
class HeaderSearchFormBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $form = \Drupal::formBuilder()->getForm('Drupal\eminent_custom\Form\HeaderSearchForm');
    return [
      $form,
      '#cache' => [
        'max-age' => 0,
      ],
    ];
  }

}
