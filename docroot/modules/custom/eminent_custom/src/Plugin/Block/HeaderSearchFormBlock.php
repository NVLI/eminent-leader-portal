<?php

/**
 * @file
 * Contains \Drupal\eminent_custom\Plugin\Block\RelatedMedia.
 */

namespace Drupal\eminent_custom\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormInterface;

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
    return $form;
  }

}
