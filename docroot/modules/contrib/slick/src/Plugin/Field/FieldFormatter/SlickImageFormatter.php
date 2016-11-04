<?php

namespace Drupal\slick\Plugin\Field\FieldFormatter;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\slick\SlickFormatterInterface;
use Drupal\slick\SlickManagerInterface;
use Drupal\slick\SlickDefault;
use Drupal\image\Plugin\Field\FieldFormatter\ImageFormatterBase;

/**
 * Plugin implementation of the 'slick image' formatter.
 *
 * @FieldFormatter(
 *   id = "slick_image",
 *   label = @Translation("Slick carousel"),
 *   description = @Translation("Display the images as a Slick carousel."),
 *   field_types = {"image"},
 *   quickedit = {"editor" = "disabled"}
 * )
 */
class SlickImageFormatter extends ImageFormatterBase implements ContainerFactoryPluginInterface {
  use SlickFormatterTrait;

  /**
   * The image style entity storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $imageStyleStorage;

  /**
   * Constructs a SlickMediaFormatter instance.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, EntityStorageInterface $image_style_storage, SlickFormatterInterface $formatter, SlickManagerInterface $manager) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
    $this->imageStyleStorage = $image_style_storage;
    $this->formatter         = $formatter;
    $this->manager           = $manager;
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
      $container->get('entity.manager')->getStorage('image_style'),
      $container->get('slick.formatter'),
      $container->get('slick.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return SlickDefault::imageSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $files = $this->getEntitiesToView($items, $langcode);

    // Early opt-out if the field is empty.
    if (empty($files)) {
      return [];
    }

    // Collects specific settings to this formatter.
    $settings = $this->getSettings();
    $build = ['settings' => $settings];

    $this->formatter->buildSettings($build, $items);

    // Build the elements.
    $this->buildElements($build, $files);

    return $this->manager()->build($build);
  }

  /**
   * Build the slick carousel elements.
   */
  public function buildElements(array &$build = [], $files) {
    $settings = $build['settings'];
    $item_id  = $settings['item_id'];

    foreach ($files as $delta => $file) {
      /* @var Drupal\image\Plugin\Field\FieldType\ImageItem $item */
      $item   = $file->_referringItem;
      $config = $settings;

      $config['delta']     = $delta;
      $config['file_tags'] = $file->getCacheTags();
      $config['type']      = 'image';
      $config['uri']       = ($entity = $item->entity) && empty($item->uri) ? $entity->getFileUri() : $item->uri;

      $element = ['item' => $item, 'settings' => $config];

      if (!empty($config['caption'])) {
        foreach ($config['caption'] as $caption) {
          $element['caption'][$caption] = empty($item->{$caption}) ? [] : ['#markup' => Xss::filterAdmin($item->{$caption})];
        }
      }

      // Image with responsive image, lazyLoad, and lightbox supports.
      $element[$item_id] = $this->formatter->getImage($element);
      $build['items'][$delta] = $element;
      unset($element);
    }

    if ($settings['nav']) {
      foreach ($files as $delta => $file) {
        $item   = $file->_referringItem;
        $config = $item->getValue();

        $config['uri'] = ($entity = $item->entity) && empty($item->uri) ? $entity->getFileUri() : $item->uri;

        foreach (['background', 'lazy', 'ratio', 'thumbnail_effect', 'thumbnail_style'] as $key) {
          $config[$key] = isset($settings[$key]) ? $settings[$key] : NULL;
        }

        $thumb = ['settings' => $config];

        // Thumbnail usages: asNavFor pagers, dot, arrows, photobox thumbnails.
        $thumb[$item_id] = empty($settings['thumbnail_style']) ? [] : $this->formatter->getThumbnail($config);

        $caption = $settings['thumbnail_caption'];
        $thumb['caption'] = empty($item->{$caption}) ? [] : ['#markup' => Xss::filterAdmin($item->{$caption})];

        $build['thumb']['items'][$delta] = $thumb;
        unset($thumb);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element    = [];
    $definition = $this->getScopedFormElements();

    $definition['_views'] = isset($form['field_api_classes']);

    $this->admin()->buildSettingsForm($element, $definition);
    return $element;
  }

  /**
   * Defines the scope for the form elements.
   */
  public function getScopedFormElements() {
    $captions    = ['title' => $this->t('Title'), 'alt' => $this->t('Alt')];
    $field       = $this->fieldDefinition;
    $entity_type = $field->getTargetEntityTypeId();
    $target_type = $this->getFieldSetting('target_type');

    return [
      'background'        => TRUE,
      'box_captions'      => TRUE,
      'breakpoints'       => SlickDefault::getConstantBreakpoints(),
      'current_view_mode' => $this->viewMode,
      'captions'          => $captions,
      'entity_type'       => $entity_type,
      'field_name'        => $field->getName(),
      'image_style_form'  => TRUE,
      'media_switch_form' => TRUE,
      'settings'          => $this->getSettings(),
      'target_type'       => $target_type,
      'thumb_captions'    => $captions,
      'thumb_positions'   => TRUE,
      'nav'               => TRUE,
    ];
  }

}
