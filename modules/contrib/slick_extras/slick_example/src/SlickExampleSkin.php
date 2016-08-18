<?php

/**
 * @file
 * Contains \Drupal\slick_example\SlickExampleSkin.
 */

namespace Drupal\slick_example;

use Drupal\slick\SlickSkinInterface;

/**
 * Implements SlickSkinInterface as registered via hook_slick_skins_info().
 */
class SlickExampleSkin implements SlickSkinInterface {

  /**
   * {@inheritdoc}
   */
  public function skins() {
    $path  = base_path() . drupal_get_path('module', 'slick_example');
    $skins = [
      'x_testimonial' => [
        'name' => t('X: Testimonial'),
        'description' => t('Testimonial with thumbnail and description with slidesToShow 2.'),
        'provider' => 'slick_example',
        'css' => [
          'theme' => [
            $path . '/css/slick.theme--x-testimonial.css' => [],
          ],
        ],
      ],
    ];

    return $skins;
  }

}
