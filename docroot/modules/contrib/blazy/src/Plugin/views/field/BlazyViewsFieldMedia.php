<?php

namespace Drupal\blazy\Plugin\views\field;

use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Drupal\views\ResultRow;
use Drupal\video_embed_field\ProviderManagerInterface;
use Drupal\blazy\BlazyManagerInterface;
use Drupal\blazy\Dejavu\BlazyEntityTrait;
use Drupal\blazy\Dejavu\BlazyVideoTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a custom field that renders a preview of a media.
 *
 * @ViewsField("blazy_media")
 */
class BlazyViewsFieldMedia extends BlazyViewsFieldPluginBase {

  use BlazyEntityTrait;
  use BlazyVideoTrait;

  /**
   * The video provider manager.
   *
   * @var \Drupal\video_embed_field\ProviderManagerInterface
   */
  protected $providerManager;

  /**
   * Constructs a SlickViewsFieldPluginBase object.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityDisplayRepositoryInterface $entity_display_repository, BlazyManagerInterface $blazy_manager, ProviderManagerInterface $provider_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_display_repository, $blazy_manager);
    $this->providerManager = $provider_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition, $container->get('entity_display.repository'), $container->get('blazy.manager'), $container->get('video_embed_field.provider_manager'));
  }

  /**
   * Returns the video_embed_field.provider_manager manager.
   */
  public function providerManager() {
    return $this->providerManager;
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    /** @var \Drupal\media_entity\Entity\Media $entity */
    $entity   = $values->_entity;
    $fields   = $entity->getFields();
    $build    = [];
    $settings = $this->options;
    $item     = NULL;
    $bundle   = $entity->bundle();
    $langcode = $entity->language()->getId();

    $source_field[$bundle]    = $entity->getType()->getConfiguration()['source_field'];
    $settings['blazy']        = TRUE;
    $settings['lazy']         = 'blazy';
    $settings['ratio']        = 'fluid';
    $settings['source_field'] = $source_field[$bundle];
    $settings['media_url']    = $entity->url();
    $settings['media_id']     = $entity->id();
    $settings['delta']        = 0;

    $field_name = empty($settings['source_field']) ? '' : $settings['source_field'];
    if (!empty($field_name)) {
      $external_url = $this->getFieldString($entity, $field_name, $langcode);
      $video = $this->providerManager;

      /** @var \Drupal\video_embed_field\ProviderManagerInterface $video */
      if ($external_url && $video->loadProviderFromInput($external_url)) {
        $settings['media_switch'] = empty($settings['media_switch']) ? 'media' : $settings['media_switch'];
        $settings['iframe_lazy']  = TRUE;
        $settings['dimension']    = '640x360';

        $this->buildVideo($settings, $external_url, $video);
      }
    }

    // Main image can be separate image item from video thumbnail for highres.
    // Fallback to default thumbnail if any, which has no file API.
    // If Media entity via slick_media has defined source_field.
    if (isset($fields['thumbnail']) && !empty($field_name)) {
      $item = $fields['thumbnail']->get(0);
      $settings['file_tags'] = ['file:' . $item->target_id];
    }

    if ($item) {
      // Build Blazy.
      $settings['thumbnail_style'] = 'thumbnail';
      $data = ['item' => $item, 'settings' => $settings];
      $build = $this->blazyManager->getImage($data);
      $build['#attached'] = $this->blazyManager->attach($settings);
      // $build = $this->getImage($data);
    }
    else {
      $build = $this->blazyManager->getEntityView($entity, $settings);

      if (!$build) {
        $build = ['#markup' => $entity->label()];
      }
    }

    return $build;
  }

  /**
   * Defines the scope for the form elements.
   */
  public function getScopedFormElements() {
    return [
      'media'        => TRUE,
      'media_switch' => TRUE,
      'target_type'  => 'media',
    ] + parent::getScopedFormElements();
  }

}
