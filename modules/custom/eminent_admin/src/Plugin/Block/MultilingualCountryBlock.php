<?php

/**
 * @file
 * Contains \Drupal\multilingual\Plugin\Block\MultilingualCountryBlock.
 */

namespace Drupal\multilingual\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\multilingual\Controller\MultilingualController;

/**
 * Provides a 'Multilingual Country' Block.
 *
 * @Block(
 *   id = "multilingual_country",
 *   admin_label = @Translation("Multilingual Country"),
 *   category = @Translation("Multilingual"),
 * )
 */
class MultilingualCountryBlock extends BlockBase {

  protected $multilingual;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    $this->multilingual = new MultilingualController();
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $country_block = $this->multilingual->getCountryLinks();
    return [
      '#markup' => render($country_block),
      '#cache' => [
        'max-age' => 0,
      ],
    ];
  }

}
