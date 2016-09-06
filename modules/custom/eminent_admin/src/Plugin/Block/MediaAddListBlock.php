<?php

/**
 * @file
 * Contains \Drupal\eminent_admin\Plugin\Block\MediaAddListBlock.
 */

namespace Drupal\eminent_admin\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a link block.
 */
class MediaAddListBlock extends BlockBase {

 
  /**
   * {@inheritdoc}
   */
  public function build() {

    return array(
      '#type' => 'markup',
      '#markup' => 'Add to Play List.',
    );
  }

}
