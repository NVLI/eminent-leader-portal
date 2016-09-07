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
	$path_args = arg();
    return array(
      '#type' => 'markup',
      '#markup' => '<a href ="/media/add/playlist/"' . $path_args[3] .'>Add to play list</a>',
    );
  }

}
