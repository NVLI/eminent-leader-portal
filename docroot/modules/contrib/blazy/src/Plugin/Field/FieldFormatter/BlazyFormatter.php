<?php

namespace Drupal\blazy\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Template\Attribute;
use Drupal\Component\Utility\Xss;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\image\Plugin\Field\FieldFormatter\ImageFormatterBase;
use Drupal\blazy\BlazyFormatterManager;
use Drupal\blazy\Dejavu\BlazyDefault;

/**
 * Plugin for the Blazy image formatter.
 *
 * @FieldFormatter(
 *   id = "blazy",
 *   label = @Translation("Blazy"),
 *   field_types = {"image"}
 * )
 */
class BlazyFormatter extends ImageFormatterBase implements ContainerFactoryPluginInterface {

  /**
   * The blazy manager service.
   *
   * @var \Drupal\blazy\BlazyFormatterManager
   */
  protected $blazyFormatterManager;

  /**
   * Constructs a BlazyFormatter object.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, BlazyFormatterManager $blazy_formatter_manager) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
    $this->blazyFormatterManager = $blazy_formatter_manager;
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
      $container->get('blazy.formatter.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return BlazyDefault::imageSettings();
  }

  /**
   * Returns the blazy admin service.
   */
  public function admin() {
    return \Drupal::service('blazy.admin.formatter');
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $build = [];
    $files = $this->getEntitiesToView($items, $langcode);

    // Early opt-out if the field is empty.
    if (empty($files)) {
      return $build;
    }

    // Collects specific settings to this formatter.
    $formatter             = $this->blazyFormatterManager;
    $settings              = $this->getSettings();
    $settings['namespace'] = $settings['item_id'] = $settings['lazy'] = 'blazy';
    $settings['blazy']     = TRUE;

    // Build the settings.
    $build = ['settings' => $settings];
    $formatter->buildSettings($build, $items);

    // Build the elements.
    $this->buildElements($build, $files);

    $build['#blazy']    = $build['settings'];
    $build['#attached'] = $formatter->attach($build['settings']);
    unset($build['settings']);

    return $build;
  }

  /**
   * Build the Blazy elements.
   */
  public function buildElements(array &$build = [], $files) {
    $settings = &$build['settings'];

    foreach ($files as $delta => $file) {
      /* @var Drupal\image\Plugin\Field\FieldType\ImageItem $item */
      $box  = [];
      $item = $file->_referringItem;

      $settings['delta']     = $delta;
      $settings['file_tags'] = $file->getCacheTags();
      $settings['type']      = 'image';

      $box['item']     = $item;
      $box['settings'] = $settings;

      // Build caption if so configured.
      if (!empty($settings['caption'])) {
        foreach ($settings['caption'] as $caption) {
          $box['captions'][$caption]['content'] = empty($item->{$caption}) ? [] : ['#markup' => Xss::filterAdmin($item->{$caption})];
          $box['captions'][$caption]['tag'] = $caption == 'title' ? 'h2' : 'div';
          if (!isset($box['captions'][$caption]['attributes'])) {
            $class = $caption == 'alt' ? 'description' : $caption;
            $box['captions'][$caption]['attributes'] = new Attribute();
            $box['captions'][$caption]['attributes']->addClass($settings['item_id'] . '__' . $class);
          }
        }
      }

      // Image with responsive image, lazyLoad, and lightbox supports.
      $build[$delta] = $this->blazyFormatterManager->getImage($box);
      unset($box);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element    = [];
    $definition = $this->getScopedFormElements();

    $this->admin()->buildSettingsForm($element, $definition);

    return $element;
  }

  /**
   * Defines the scope for the form elements.
   */
  public function getScopedFormElements() {
    $field       = $this->fieldDefinition;
    $entity_type = $field->getTargetEntityTypeId();

    return [
      'background'        => TRUE,
      'box_captions'      => TRUE,
      'breakpoints'       => BlazyDefault::getConstantBreakpoints(),
      'captions'          => ['title' => $this->t('Title'), 'alt' => $this->t('Alt')],
      'current_view_mode' => $this->viewMode,
      'entity_type'       => $entity_type,
      'image_style_form'  => TRUE,
      'media_switch_form' => TRUE,
      'namespace'         => 'blazy',
      'settings'          => $this->getSettings(),
      'thumbnail_styles'  => TRUE,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    return $this->admin()->settingsSummary($this);
  }

}
