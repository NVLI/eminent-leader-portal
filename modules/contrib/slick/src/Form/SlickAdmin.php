<?php

/**
 * @file
 * Contains \Drupal\slick\Form\SlickAdmin.
 */

namespace Drupal\slick\Form;

use Drupal\Core\Url;
use Drupal\Core\Render\Element;
use Drupal\Component\Utility\Html;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\blazy\Form\BlazyAdminInterface;
use Drupal\slick\SlickManagerInterface;

/**
 * Provides resusable admin functions, or form elements.
 */
class SlickAdmin implements SlickAdminInterface {

  /**
   * The blazy admin service.
   *
   * @var \Drupal\blazy\Form\BlazyAdminInterface.
   */
  protected $blazyAdmin;

  /**
   * The slick manager service.
   *
   * @var \Drupal\slick\SlickManagerInterface.
   */
  protected $manager;

  /**
   * Constructs a SlickAdmin object.
   *
   * @param \Drupal\blazy\Form\BlazyAdminInterface $blazy_admin
   *   The blazy admin service.
   * @param \Drupal\slick\SlickManagerInterface $manager
   *   The slick manager service.
   */
  public function __construct(BlazyAdminInterface $blazy_admin, SlickManagerInterface $manager) {
    $this->blazyAdmin = $blazy_admin;
    $this->manager = $manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static (
      $container->get('blazy.admin.extended'),
      $container->get('slick.manager')
    );
  }

  /**
   * Returns the blazy admin formatter.
   */
  public function blazyAdmin() {
    return $this->blazyAdmin;
  }

  /**
   * Returns the slick manager.
   */
  public function manager() {
    return $this->manager;
  }

  /**
   * Returns the main form elements.
   */
  public function buildSettingsForm(array &$form, $definition = []) {
    $definition += [
      'caches'     => TRUE,
      'namespace'  => 'slick',
      'optionsets' => $this->getOptionsetsByGroupOptions('main'),
      'skins'      => $this->getSkinsByGroupOptions('main'),
    ];

    $definition['layouts'] = isset($definition['layouts']) ? array_merge($this->getLayoutOptions(), $definition['layouts']) : $this->getLayoutOptions();

    $this->openingForm($form, $definition);

    if (isset($definition['image_style_form']) && !isset($form['image_style'])) {
      $this->imageStyleForm($form, $definition);
    }

    if (isset($definition['media_switch_form']) && !isset($form['media_switch'])) {
      $this->mediaSwitchForm($form, $definition);
    }

    if (isset($definition['grid_form']) && !isset($form['grid'])) {
      $this->gridForm($form, $definition);
    }

    if (isset($definition['fieldable_form']) && !isset($form['image'])) {
      $this->fieldableForm($form, $definition);
    }

    if (isset($definition['breakpoints'])) {
      $this->blazyAdmin->breakpointsForm($form, $definition);
    }

    $this->closingForm($form, $definition);
  }

  /**
   * Returns the opening form elements.
   */
  public function openingForm(array &$form, $definition = []) {
    $path         = drupal_get_path('module', 'slick');
    $readme       = Url::fromUri('base:' . $path . '/README.txt')->toString();
    $readme_field = Url::fromUri('base:' . $path . '/src/Plugin/Field/README.txt')->toString();
    $arrows       = $this->getSkinsByGroupOptions('arrows');
    $dots         = $this->getSkinsByGroupOptions('dots');

    if (!isset($form['optionset'])) {
      $this->blazyAdmin->openingForm($form, $definition);

      $form['optionset']['#title'] = t('Optionset main');
    }

    $form['optionset_thumbnail'] = [
      '#type'        => 'select',
      '#title'       => t('Optionset thumbnail'),
      '#options'     => $this->getOptionsetsByGroupOptions('thumbnail'),
      '#description' => t('If provided, asNavFor aka thumbnail navigation applies. Leave empty to not use thumbnail navigation.'),
      '#access'      => isset($definition['nav']) || isset($definition['thumbnails']),
      '#weight'      => -108,
    ];

    $form['skin_thumbnail'] = [
      '#type'        => 'select',
      '#title'       => t('Skin thumbnail'),
      '#options'     => $this->getSkinsByGroupOptions('thumbnail'),
      '#description' => t('Thumbnail navigation skin. See main <a href="@url" target="_blank">README</a> for details on Skins. Leave empty to not use thumbnail navigation.', ['@url' => $readme]),
      '#access'      => isset($definition['nav']) || isset($definition['thumbnails']),
      '#weight'      => -106,
    ];

    $form['skin_arrows'] = [
      '#type'        => 'select',
      '#title'       => t('Skin arrows'),
      '#options'     => $arrows ?: [],
      '#enforced'    => TRUE,
      '#description' => t('Implement \Drupal\slick\SlickSkinInterface::arrows() to add your own arrows skins, in the same format as SlickSkinInterface::skins().'),
      '#access'      => count($arrows) > 0,
      '#weight'      => -105,
    ];

    $form['skin_dots'] = [
      '#type'        => 'select',
      '#title'       => t('Skin dots'),
      '#options'     => $dots ?: [],
      '#enforced'    => TRUE,
      '#description' => t('Implement \Drupal\slick\SlickSkinInterface::dots() to add your own dots skins, in the same format as SlickSkinInterface::skins().'),
      '#access'      => count($dots) > 0,
      '#weight'      => -105,
    ];

    $form['thumbnail_caption'] = [
      '#type'        => 'select',
      '#title'       => t('Thumbnail caption'),
      '#options'     => isset($definition['thumb_captions']) ? $definition['thumb_captions'] : [],
      '#description' => t('Thumbnail caption maybe just title/ plain text. If Thumbnail image style is not provided, the thumbnail pagers will be just text like regular tabs.'),
      '#access'      => isset($definition['thumb_captions']),
      '#states' => [
        'visible' => [
          'select[name*="[optionset_thumbnail]"]' => ['!value' => ''],
        ],
      ],
      '#weight'      => 2,
    ];

    $form['skin']['#title'] = t('Skin main');
    $form['skin']['#description'] = t('Skins allow various layouts with just CSS. Some options below depend on a skin. However a combination of skins and options may lead to unpredictable layouts, get yourself dirty. See <a href=":url" target="_blank">SKINS section at README.txt</a> for details on Skins. Leave empty to DIY. Or use hook_slick_skins_info() and implement \Drupal\slick\SlickSkinInterface to register ones.', [':url' => $readme]);

    $form['layout']['#description'] = t('Requires a skin. The builtin layouts affects the entire slides uniformly. Split half requires any skin Split. See <a href="@url" target="_blank">README</a> under "Slide layout" for more info. Leave empty to DIY.', ['@url' => $readme_field]);

    $weight = -99;
    foreach (Element::children($form) as $key) {
      if (!isset($form[$key]['#weight'])) {
        $form[$key]['#weight'] = ++$weight;
      }
    }
  }

  /**
   * Returns the image formatter form elements.
   */
  public function mediaSwitchForm(array &$form, $definition = []) {
    $this->blazyAdmin->mediaSwitchForm($form, $definition);

    $form['media_switch']['#description'] = t('Depends on the enabled supported modules, or has known integration with Slick.<ol><li>Link to content: for aggregated small slicks.</li><li>Image to iframe: audio/video is hidden below image until toggled, otherwise iframe is always displayed, and draggable fails. Aspect ratio applies.</li><li>Colorbox.</li><li>Photobox. Be sure to select "Thumbnail style" for the overlay thumbnails.</li><li>Intense: image to fullscreen intense image.</li></ol>');

    $form['ratio']['#description'] .= ' ' . t('Required if using media entity to switch between iframe and overlay image, otherwise DIY.');
  }

  /**
   * Returns the image formatter form elements.
   */
  public function imageStyleForm(array &$form, $definition = []) {
    $definition['thumbnail_styles'] = TRUE;
    $definition['ratios'] = TRUE;

    $definition['thumbnail_effects'] = [
      'hover' => t('Hoverable'),
      'grid'  => t('Static grid'),
    ];

    if (!isset($form['image_style'])) {
      $this->blazyAdmin->imageStyleForm($form, $definition);
    }

    $form['image_style']['#description'] = t('The main image style. Ignored if Breakpoints are provided, use smaller image style here instead. Otherwise this is the only image displayed. If Slick media module installed, this determines iframe sizes to have various iframe dimensions with just a single file entity view mode, relevant for a mix of image and multimedia to get a consistent display.');

    $form['thumbnail_style']['#description'] = t('Usages: <ol><li>If <em>Optionset thumbnail</em> provided, it is for asNavFor thumbnail navigation.</li><li>If <em>Dots with thumbnail</em> selected, displayed when hovering over dots.</li><li>Photobox thumbnail.</li><li>Custom work to build arrows with thumbnails via the provided data-thumb attributes.</li></ol>Leave empty to not use thumbnails.');

    $form['thumbnail_effect']['#description'] = t('Dependent on a Skin, Dots and Thumbnail style options. <ol><li><strong>Hoverable</strong>: Dots pager are kept, and thumbnail will be hidden and only visible on dot mouseover, default to min-width 120px.</li><li><strong>Static grid</strong>: Dots are hidden, and thumbnails are displayed as a static grid acting like dots pager.</li></ol>Alternative to asNavFor aka separate thumbnails as slider.');

    $form['background']['#description'] .= '' . t(' This opens up the goodness of CSS, such as background cover, fixed attachment, etc. Blazy lazyload applies. Works best with a  single visible slide, skins full width/screen. <br /><strong>Important!</strong> Requires a consistent Aspect ratio, otherwise collapsed containers. Unless a min-height is added manually to <strong>.media</strong> selector.');
  }

  /**
   * Returns re-usable fieldable formatter form elements.
   */
  public function fieldableForm(array &$form, $definition = []) {
    $this->blazyAdmin->fieldableForm($form, $definition);

    $form['thumbnail']['#description'] = t("Only needed if <em>Optionset thumbnail</em> is provided. Maybe the same field as the main image, only different instance and image style. Leave empty to not use thumbnail pager.");

    $form['overlay']['#title'] = t('Overlay media/slicks');
    $form['overlay']['#description'] = t('For audio/video, be sure the display is not image. For nested slicks, use the Slick carousel formatter for this field. Zebra layout is reasonable for overlay and captions.');
  }

  /**
   * Returns re-usable grid elements across Slick field formatter and Views.
   */
  public function gridForm(array &$form, $definition = []) {
    if (!isset($form['grid'])) {
      $this->blazyAdmin->gridForm($form, $definition);
    }

    $header = t('Group individual slide as block grid?<small>An older alternative to core <strong>Rows</strong> option. Only works if the total items &gt; <strong>Visible slides</strong>. <br />block grid != slidesToShow option, yet both can work in tandem.<br />block grid = Rows option, yet the first is module feature, the later core.</small>');

    $form['grid_header']['#markup'] = '<h3 class="form__title">' . $header . '</h3>';

    $form['grid']['#description'] = t('The amount of block grid columns for large monitors 64.063em - 90em. <br /><strong>Requires</strong>:<ol><li>Visible items,</li><li>Skin Grid for starter,</li><li>A reasonable amount of contents,</li><li>Optionset with Rows and slidesPerRow = 1.</li></ol>This is module feature, older than core Rows, and offers more flexibility. Leave empty to DIY, or to not build grids.');
  }

  /**
   * Returns the closing ending form elements.
   */
  public function closingForm(array &$form, $definition = []) {
    $form['override'] = [
      '#title'       => t('Override main optionset'),
      '#type'        => 'checkbox',
      '#description' => t('If checked, the following options will override the main optionset. Useful to re-use one optionset for several different displays.'),
      '#weight'      => 113,
      '#enforced'    => TRUE,
    ];

    $form['overridables'] = [
      '#type'          => 'checkboxes',
      '#title'         => t('Overridable options'),
      '#description'   => t("Override the main optionset to re-use one. Anything dictated here will override the current main optionset. Unchecked means FALSE"),
      '#options'       => $this->getOverridableOptions(),
      '#weight'        => 114,
      '#enforced'      => TRUE,
      '#states' => [
        'visible' => [
          ':input[name$="[override]"]' => ['checked' => TRUE],
        ],
      ],
    ];

    if (!isset($form['cache'])) {
      $this->blazyAdmin->closingForm($form, $definition);
    }
  }

  /**
   * Returns overridable options to re-use one optionset.
   */
  public function getOverridableOptions() {
    $options = [
      'arrows'        => t('Arrows'),
      'autoplay'      => t('Autoplay'),
      'dots'          => t('Dots'),
      'draggable'     => t('Draggable'),
      'infinite'      => t('Infinite'),
      'mouseWheel'    => t('Mousewheel'),
      'randomize'     => t('Randomize'),
      'variableWidth' => t('variableWidth'),
    ];

    $this->manager->getModuleHandler()->alter('slick_overridable_options_info', $options);
    return $options;
  }

  /**
   * Returns default layout options for the core Image, or Views.
   */
  public function getLayoutOptions() {
    $layouts = &drupal_static(__METHOD__, NULL);

    if (!isset($layouts)) {
      $layouts = [
        'bottom'      => t('Caption bottom'),
        'top'         => t('Caption top'),
        'right'       => t('Caption right'),
        'left'        => t('Caption left'),
        'center'      => t('Caption center'),
        'center-top'  => t('Caption center top'),
        'below'       => t('Caption below the slide'),
        'stage-right' => t('Caption left, stage right'),
        'stage-left'  => t('Caption right, stage left'),
        'split-right' => t('Caption left, stage right, split half'),
        'split-left'  => t('Caption right, stage left, split half'),
        'stage-zebra' => t('Stage zebra'),
        'split-zebra' => t('Split half zebra'),
      ];
    }
    return $layouts;
  }

  /**
   * Returns available slick optionsets by group.
   */
  public function getOptionsetsByGroupOptions($group = '') {
    $optionsets = $groups = $ungroups = [];
    $slicks = $this->manager->entityLoadMultiple('slick');
    foreach ($slicks as $slick) {
      $name = Html::escape($slick->label());
      $id = $slick->id();
      $current_group = $slick->getGroup();
      if (!empty($group)) {
        if ($current_group) {
          if ($current_group != $group) {
            continue;
          }
          $groups[$id] = $name;
        }
        else {
          $ungroups[$id] = $name;
        }
      }
      $optionsets[$id] = $name;
    }

    return $group ? array_merge($ungroups, $groups) : $optionsets;
  }

  /**
   * Returns available slick skins for select options.
   */
  public function getSkinsByGroupOptions($group = '') {
    return $this->manager->getSkinsByGroup($group, TRUE);
  }

  /**
   * Return the field formatter settings summary.
   */
  public function settingsSummary($plugin) {
    return $this->blazyAdmin->settingsSummary($plugin);
  }

  /**
   * Returns available fields for select options.
   */
  public function getFieldOptions($target_bundles = [], $allowed_field_types = [], $entity_type_id = 'media') {
    return $this->blazyAdmin->getFieldOptions($target_bundles, $allowed_field_types, $entity_type_id);
  }

  /**
   * Returns re-usable logic, styling and assets across fields and Views.
   */
  public function finalizeForm(array &$form, $definition = []) {
    return $this->blazyAdmin->finalizeForm($form, $definition);
  }

}
