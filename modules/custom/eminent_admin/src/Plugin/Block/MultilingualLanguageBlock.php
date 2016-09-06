<?php

/**
 * @file
 * Contains \Drupal\multilingual\Plugin\Block\MultilingualLanguageBlock.
 */

namespace Drupal\multilingual\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\multilingual\Controller\MultilingualController;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Multilingual Language' Block.
 *
 * @Block(
 *   id = "multilingual_language",
 *   admin_label = @Translation("Multilingual Language"),
 *   category = @Translation("Multilingual"),
 * )
 */
class MultilingualLanguageBlock extends BlockBase implements ContainerFactoryPluginInterface {

  protected $multilingual;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    $this->multilingual = new MultilingualController();
    $this->multilingual->checkRedirect();
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
        $configuration, $plugin_id, $plugin_definition, $container->get('language_manager'), $container->get('path.matcher')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $language_block = $this->multilingual->getLanguageLinks();
    return [
      '#markup' => render($language_block),
      '#cache' => [
        'max-age' => 0,
      ],
    ];
  }

}
