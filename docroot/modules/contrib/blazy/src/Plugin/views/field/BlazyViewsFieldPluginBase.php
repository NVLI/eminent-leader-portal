<?php

namespace Drupal\blazy\Plugin\views\field;

use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Drupal\blazy\BlazyManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a base views field plugin to render a preview of supported fields.
 */
abstract class BlazyViewsFieldPluginBase extends FieldPluginBase {

  /**
   * The blazy service manager.
   *
   * @var \Drupal\blazy\BlazyManagerInterface
   */
  protected $blazyManager;

  /**
   * Constructs a BlazyViewsFieldPluginBase object.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityDisplayRepositoryInterface $entity_display_repository, BlazyManagerInterface $blazy_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityDisplayRepository = $entity_display_repository;
    $this->blazyManager = $blazy_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition, $container->get('entity_display.repository'), $container->get('blazy.manager'));
  }

  /**
   * Returns the blazy manager.
   */
  public function blazyManager() {
    return $this->blazyManager;
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();

    $defaults = $this->getDefaultValues();
    $definitions = $this->getScopedFormElements();

    foreach ($defaults as $key => $default) {
      if (isset($definitions[$key])) {
        $options[$key] = array('default' => $default);
      }
    }
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    $is_colorbox  = function_exists('colorbox_theme');
    $is_photobox  = function_exists('photobox_theme');
    $image_styles = image_style_options(TRUE);
    $photobox     = \Drupal::root() . '/libraries/photobox/photobox/jquery.photobox.js';
    $definitions  = $this->getScopedFormElements();

    if (is_file($photobox)) {
      $is_photobox = TRUE;
    }

    if (isset($definitions['image_style'])) {
      $form['image_style'] = [
        '#type'          => 'select',
        '#title'         => $this->t('Image style'),
        '#options'       => $image_styles,
        '#default_value' => $this->options['image_style'],
        '#description'   => $this->t('This will use an image style where applicable.'),
      ];
    }

    if (isset($definitions['view_mode']) && !empty($definitions['target_type'])) {
      $vide_modes = $this->entityDisplayRepository->getViewModeOptions($definitions['target_type']);
      $form['view_mode'] = [
        '#type'          => 'select',
        '#options'       => empty($vide_modes) ? [] : $vide_modes,
        '#title'         => $this->t('View mode'),
        '#default_value' => $this->options['view_mode'],
        '#description'   => $this->t('Will attempt to fetch data from a view mode if applicable. Be sure the selected "View mode" is enabled, and the relevant fields are not hidden.'),
      ];
    }

    if (isset($definitions['media_switch'])) {
      $form['media_switch'] = [
        '#type'         => 'select',
        '#title'        => $this->t('Media switcher'),
        '#options'      => [
          'content' => $this->t('Image linked to content'),
        ],
        '#empty_option' => '- None -',
        '#description'  => $this->t('May depend on the enabled supported modules: colorbox, photobox. Be sure to add Thumbnail style if using Photobox.'),
      ];

      if ($is_colorbox || $is_photobox || isset($definition['lightbox'])) {
        if ($is_colorbox) {
          $form['media_switch']['#options']['colorbox'] = $this->t('Image to colorbox');
        }

        if ($is_photobox) {
          $form['media_switch']['#options']['photobox'] = $this->t('Image to photobox');
        }
      }

      if (isset($definitions['media'])) {
        $form['media_switch']['#options']['media'] = $this->t('Image to iframe');
      }
    }

    parent::buildOptionsForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    // Do nothing -- to override the parent query.
  }

  /**
   * Returns Blazy without bells and whistles.
   */
  public function getImage($build = []) {
    /* @var Drupal\image\Plugin\Field\FieldType\ImageItem $item */
    $item     = $build['item'];
    $settings = $build['settings'];

    if (empty($item)) {
      return [];
    }

    $this->blazyManager->getUrlDimensions($settings, $item, $settings['image_style']);

    // Build Blazy.
    return [
      '#theme'       => 'blazy',
      '#item'        => $item,
      '#settings'    => $settings,
      '#attached'    => $this->blazyManager->attach($settings),
    ];
  }

  /**
   * Defines the default values.
   */
  public function getDefaultValues() {
    return [
      'image_style'  => '',
      'media_switch' => '',
      'view_mode'    => '',
    ];
  }

  /**
   * Defines the scope for the form elements.
   */
  public function getScopedFormElements() {
    return [
      'image_style' => TRUE,
      'view_mode'   => TRUE,
    ];
  }

}
