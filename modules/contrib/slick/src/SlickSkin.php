<?php

/**
 * @file
 * Contains \Drupal\slick\SlickSkin.
 */

namespace Drupal\slick;

/**
 * Implements SlickSkinInterface.
 */
class SlickSkin implements SlickSkinInterface {

  /**
   * {@inheritdoc}
   */
  public function skins() {
    $skins = [
      'default' => [
        'name' => t('Default'),
        'css' => [
          'theme' => [
            'css/theme/slick.theme--default.css' => [],
          ],
        ],
      ],
      'asnavfor' => [
        'name' => t('Thumbnail: asNavFor'),
        'css' => [
          'theme' => [
            'css/theme/slick.theme--asnavfor.css' => [],
          ],
        ],
        'description' => t('Affected thumbnail navigation only.'),
      ],
      'classic' => [
        'name' => t('Classic'),
        'description' => t('Adds dark background color over white caption, only good for slider (single slide visible), not carousel (multiple slides visible), where small captions are placed over images.'),
        'css' => [
          'theme' => [
            'css/theme/slick.theme--classic.css' => [],
          ],
        ],
      ],
      'fullscreen' => [
        'name' => t('Full screen'),
        'description' => t('Adds full screen display, works best with 1 slidesToShow.'),
        'css' => [
          'theme' => [
            'css/theme/slick.theme--full.css' => [],
            'css/theme/slick.theme--fullscreen.css' => [],
          ],
        ],
      ],
      'fullwidth' => [
        'name' => t('Full width'),
        'description' => t('Adds .slide__constrained wrapper to hold caption overlay within the max-container.'),
        'css' => [
          'theme' => [
            'css/theme/slick.theme--full.css' => [],
            'css/theme/slick.theme--fullwidth.css' => [],
          ],
        ],
      ],
      'grid' => [
        'name' => t('Grid Foundation'),
        'description' => t('Use slidesToShow > 1 to have more grid combination, only if you have considerable amount of grids, otherwise 1.'),
        'css' => [
          'theme' => [
            'css/theme/slick.theme--grid.css' => [],
          ],
        ],
      ],
      'split' => [
        'name' => t('Split'),
        'description' => t('Puts image and caption side by side, related to slide layout options.'),
        'css' => [
          'theme' => [
            'css/theme/slick.theme--split.css' => [],
          ],
        ],
      ],
    ];

    foreach ($skins as $key => $skin) {
      $skins[$key]['group'] = $key == 'asnavfor' ? 'thumbnail' : 'main';
      $skins[$key]['provider'] = 'slick';
    }

    return $skins;
  }

}
