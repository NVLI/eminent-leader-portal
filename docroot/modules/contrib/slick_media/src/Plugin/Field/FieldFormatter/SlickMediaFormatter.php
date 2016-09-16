<?php

/**
 * @file
 * Contains \Drupal\slick_media\Plugin\Field\FieldFormatter\SlickMediaFormatter.
 */

namespace Drupal\slick_media\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\slick\SlickDefault;
use Drupal\slick\SlickFormatterInterface;
use Drupal\slick\SlickManagerInterface;
use Drupal\slick\Plugin\Field\FieldFormatter\SlickEntityReferenceFormatterBase;
use Drupal\video_embed_field\ProviderManagerInterface;
use Drupal\blazy\Dejavu\BlazyVideoTrait;

/**
 * Plugin implementation of the 'slick media entity' formatter.
 *
 * @FieldFormatter(
 *   id = "slick_media",
 *   label = @Translation("Slick media"),
 *   description = @Translation("Display the referenced entities as a Slick carousel."),
 *   field_types = {
 *     "entity_reference"
 *   },
 *   quickedit = {
 *     "editor" = "disabled"
 *   }
 * )
 */
class SlickMediaFormatter extends SlickEntityReferenceFormatterBase {
  use BlazyVideoTrait;

  /**
   * The video provider manager.
   *
   * @var \Drupal\video_embed_field\ProviderManagerInterface
   */
  protected $providerManager;

  /**
   * Constructs a SlickMediaFormatter instance.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, LoggerChannelFactoryInterface $logger_factory, EntityStorageInterface $image_style_storage, SlickFormatterInterface $formatter, SlickManagerInterface $manager, ProviderManagerInterface $provider_manager) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings, $logger_factory, $image_style_storage, $formatter, $manager);
    $this->providerManager = $provider_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('logger.factory'),
      $container->get('entity.manager')->getStorage('image_style'),
      $container->get('slick.formatter'),
      $container->get('slick.manager'),
      $container->get('video_embed_field.provider_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return ['color_field' => ''] + SlickDefault::extendedSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $entities = $this->getEntitiesToView($items, $langcode);

    // Early opt-out if the field is empty.
    if (empty($entities)) {
      return [];
    }

    // Collects specific settings to this formatter.
    $settings = $this->getSettings();

    // Overrides slick_image to use slick_media template.
    $settings['theme_hook_image'] = 'blazy';
    $build = ['settings' => $settings];

    $this->formatter->buildSettings($build, $items);

    // Build the elements.
    $this->buildElements($build, $entities, $langcode);

    return $this->manager()->build($build);
  }

  /**
   * {@inheritdoc}
   */
  public function buildMedia(array &$settings = [], $entity, $langcode) {
    parent::buildMedia($settings, $entity, $langcode);

    $source_field[$entity->bundle()] = $entity->getType()->getConfiguration()['source_field'];

    $settings['source_field'] = $source_field[$entity->bundle()];
    if (!empty($settings['source_field'])) {
      $media_url = $this->getFieldString($entity, $settings['source_field'], $langcode);

      /** @var \Drupal\video_embed_field\ProviderManagerInterface $provider */
      if ($media_url && $provider = $this->providerManager->loadProviderFromInput($media_url)) {
        $this->buildVideo($settings, $media_url);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    $storage = $field_definition->getFieldStorageDefinition();
    return $storage->isMultiple() && $storage->getSetting('target_type') === 'media';
  }

}
