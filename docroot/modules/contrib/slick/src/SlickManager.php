<?php

namespace Drupal\slick;

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Cache\Cache;
use Drupal\slick\Entity\Slick;
use Drupal\blazy\BlazyManagerBase;
use Drupal\blazy\BlazyManagerInterface;

/**
 * Implements BlazyManagerInterface, SlickManagerInterface.
 */
class SlickManager extends BlazyManagerBase implements BlazyManagerInterface, SlickManagerInterface {

  /**
   * The supported $skins.
   *
   * @const $skins.
   */
  private static $skins = ['overlay', 'main', 'thumbnail', 'arrows', 'dots', 'widget'];

  /**
   * Returns the supported skins.
   */
  public static function getConstantSkins() {
    return self::$skins;
  }

  /**
   * Returns slick skins registered via hook_slick_skins_info(), or defaults.
   *
   * @see \Drupal\blazy\BlazyManagerBase::buildSkins().
   */
  public function getSkins() {
    $skins = &drupal_static(__METHOD__, NULL);
    if (!isset($skins)) {
      $skins = $this->buildSkins('slick', '\Drupal\slick\SlickSkin', ['skins', 'arrows', 'dots']);
    }
    return $skins;
  }

  /**
   * Returns available slick skins by group.
   */
  public function getSkinsByGroup($group = '', $option = FALSE) {
    $skins         = $groups = $ungroups = [];
    $nav_skins     = in_array($group, ['arrows', 'dots']);
    $defined_skins = $nav_skins ? $this->getSkins()[$group] : $this->getSkins()['skins'];

    foreach ($defined_skins as $skin => $properties) {
      $item = $option ? Html::escape($properties['name']) : $properties;
      if (!empty($group)) {
        if (isset($properties['group'])) {
          if ($properties['group'] != $group) {
            continue;
          }
          $groups[$skin] = $item;
        }
        elseif (!$nav_skins) {
          $ungroups[$skin] = $item;
        }
      }
      $skins[$skin] = $item;
    }

    return $group ? array_merge($ungroups, $groups) : $skins;
  }

  /**
   * {@inheritdoc}
   */
  public function attach($attach = []) {
    $attach += [
      'slick_css'  => $this->configLoad('slick_css', 'slick.settings'),
      'module_css' => $this->configLoad('module_css', 'slick.settings'),
    ];

    $attach['blazy_colorbox'] = FALSE;
    $load = parent::attach($attach);

    if (is_file('libraries/easing/jquery.easing.min.js')) {
      $load['library'][] = 'slick/slick.easing';
    }

    if (!empty($attach['lazy'])) {
      $load['library'][] = 'blazy/loading';
    }

    $load['library'][] = 'slick/slick.load';

    $components = ['colorbox', 'mousewheel'];
    foreach ($components as $component) {
      if (!empty($attach[$component])) {
        $load['library'][] = 'slick/slick.' . $component;
      }
    }

    if (!empty($attach['skin'])) {
      $this->attachSkin($load, $attach);
    }

    // Attach default JS settings to allow responsive displays have a lookup,
    // excluding wasted/trouble options, e.g.: PHP string vs JS object.
    $excludes = explode(' ', 'mobileFirst appendArrows appendDots asNavFor prevArrow nextArrow respondTo');
    $excludes = array_combine($excludes, $excludes);
    $load['drupalSettings']['slick'] = array_diff_key(Slick::defaultSettings(), $excludes);

    $this->moduleHandler->alter('slick_attach_load_info', $load, $attach);
    return $load;
  }

  /**
   * Provides skins if required.
   */
  public function attachSkin(array &$load, $attach = []) {
    // If we do have a defined skin, load the optional Slick and module css.
    if ($attach['slick_css']) {
      $load['library'][] = 'slick/slick.css';
    }

    if ($attach['module_css']) {
      $load['library'][] = 'slick/slick.theme';
    }

    if (!empty($attach['thumbnail_effect'])) {
      $load['library'][] = 'slick/slick.thumbnail.' . $attach['thumbnail_effect'];
    }

    if (!empty($attach['down_arrow'])) {
      $load['library'][] = 'slick/slick.arrow.down';
    }

    foreach (self::getConstantSkins() as $group) {
      $skin = $group == 'main' ? $attach['skin'] : (isset($attach['skin_' . $group]) ? $attach['skin_' . $group] : '');
      if (!empty($skin)) {
        $skins = $this->getSkinsByGroup($group);
        $provider = isset($skins[$skin]['provider']) ? $skins[$skin]['provider'] : 'slick';
        $load['library'][] = 'slick/' . $provider . '.' . $group . '.' . $skin;
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function slick($build = []) {
    foreach (['items', 'options', 'optionset', 'settings'] as $key) {
      $build[$key] = isset($build[$key]) ? $build[$key] : [];
    }

    if (empty($build['items'])) {
      return [];
    }

    $slick = [
      '#theme'      => 'slick',
      '#items'      => [],
      '#build'      => $build,
      '#pre_render' => [static::class . '::preRenderSlick'],
    ];

    $settings = $build['settings'];

    if (isset($settings['cache'])) {
      $suffixes[]        = count($build['items']);
      $suffixes[]        = count(array_filter($settings));
      $suffixes[]        = $settings['cache'];
      $cache['contexts'] = ['languages'];
      $cache['max-age']  = $settings['cache'];
      $cache['keys']     = isset($settings['cache_metadata']['keys']) ? $settings['cache_metadata']['keys'] : [$settings['id']];
      $cache['keys'][]   = $settings['display'];
      $cache['tags']     = Cache::buildTags('slick:' . $settings['id'], $suffixes, '.');
      if (!empty($settings['cache_tags'])) {
        $cache['tags'] = array_merge($cache['tags'], $settings['cache_tags']);
      }

      $slick['#cache']   = $cache;
    }

    return $slick;
  }

  /**
   * Builds the Slick instance as a structured array ready for ::renderer().
   */
  public static function preRenderSlick($element) {
    $build = $element['#build'];
    unset($element['#build']);

    $settings = &$build['settings'];
    if (empty($build['items'])) {
      return [];
    }

    // Adds helper class if thumbnail on dots hover provided.
    $dots_class = [];
    if (!empty($settings['thumbnail_style']) && !empty($settings['thumbnail_effect'])) {
      $dots_class[] = 'slick-dots--thumbnail-' . $settings['thumbnail_effect'];
    }

    // Adds dots skin modifier class if provided.
    if (!empty($settings['skin_dots'])) {
      $dots_class[] = Html::cleanCssIdentifier('slick-dots--' . $settings['skin_dots']);
    }

    if ($dots_class) {
      $dots_class[] = $build['optionset']->getSetting('dotsClass');
      $js['dotsClass'] = implode(" ", $dots_class);
    }

    // Overrides common options to re-use an optionset.
    if ($settings['display'] == 'main') {
      if (!empty($settings['override'])) {
        foreach ($settings['overridables'] as $key => $override) {
          $js[$key] = empty($override) ? FALSE : TRUE;
        }
      }

      // Build the Slick grid if provided.
      if (!empty($settings['grid']) && !empty($settings['visible_items'])) {
        $build['items'] = self::buildGrid($build['items'], $settings);
      }
    }

    $build['options'] = isset($js) ? array_merge($build['options'], $js) : $build['options'];
    foreach (['items', 'options', 'optionset', 'settings'] as $key) {
      $element["#$key"] = $build[$key];
    }

    return $element;
  }

  /**
   * Returns items as a grid display.
   */
  public static function buildGrid($build = [], array &$settings) {
    $grids = [];

    // Display all items if unslick is enforced for plain grid to lightbox.
    if (!empty($settings['unslick'])) {
      $settings['display']      = 'main';
      $settings['current_item'] = 'grid';
      $settings['count']        = 2;

      $slide['slide'] = [
        '#theme'    => 'slick_grid',
        '#items'    => $build,
        '#delta'    => 0,
        '#settings' => $settings,
      ];
      $slide['settings'] = $settings;
      $grids[0] = $slide;
    }
    else {
      // Otherwise do chunks to have a grid carousel.
      $preserve_keys     = !empty($settings['preserve_keys']);
      $grid_items        = array_chunk($build, $settings['visible_items'], $preserve_keys);
      $settings['count'] = count($grid_items);

      foreach ($grid_items as $delta => $grid_item) {
        $slide = [];
        $slide['slide'] = [
          '#theme'    => 'slick_grid',
          '#items'    => $grid_item,
          '#delta'    => $delta,
          '#settings' => $settings,
        ];
        $slide['settings'] = $settings;
        $grids[] = $slide;
        unset($slide);
      }
    }
    return $grids;
  }

  /**
   * {@inheritdoc}
   */
  public function build($build = []) {
    foreach (['items', 'options', 'optionset', 'settings'] as $key) {
      $build[$key] = isset($build[$key]) ? $build[$key] : [];
    }

    return empty($build['items']) ? [] : [
      '#theme'      => 'slick_wrapper',
      '#items'      => [],
      '#build'      => $build,
      '#pre_render' => [[$this, 'preRenderSlickWrapper']],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function preRenderSlickWrapper($element) {
    $build = $element['#build'];
    unset($element['#build']);

    if (empty($build['items'])) {
      return [];
    }

    // One slick_theme() to serve multiple displays: main, overlay, thumbnail.
    $defaults = Slick::htmlSettings();
    $settings = $build['settings'] ? array_merge($defaults, $build['settings']) : $defaults;
    $id       = isset($settings['id']) ? $settings['id'] : '';
    $id       = self::getHtmlId('slick', $id);
    $thumb_id = $id . '-thumbnail';
    $options  = $build['options'];
    $switch   = isset($settings['media_switch']) ? $settings['media_switch'] : '';

    // Additional settings.
    $build['optionset']      = $build['optionset'] ?: Slick::load($settings['optionset']);
    $settings['id']          = $id;
    $settings['nav']         = isset($settings['nav']) ? $settings['nav'] : (!empty($settings['optionset_thumbnail']) && isset($build['items'][1]));
    $settings['navpos']      = !empty($settings['nav']) && !empty($settings['thumbnail_position']);
    $settings['vertical']    = $build['optionset']->getSetting('vertical');
    $mousewheel              = $build['optionset']->getSetting('mouseWheel');

    if ($settings['nav']) {
      $options['asNavFor']     = "#{$thumb_id}-slider";
      $optionset_thumbnail     = Slick::load($settings['optionset_thumbnail']);
      $mousewheel              = $optionset_thumbnail->getSetting('mouseWheel');
      $settings['vertical_tn'] = $optionset_thumbnail->getSetting('vertical');
    }

    // Attach libraries.
    if ($switch && $switch != 'content') {
      $settings[$switch] = $switch;
    }

    $settings['mousewheel'] = !empty($options['overridables']['mouseWheel']) || $mousewheel;
    $settings['down_arrow'] = $build['optionset']->getSetting('downArrow');

    $attachments            = $this->attach($settings);
    $build['options']       = $options;
    $build['settings']      = $settings;
    $element['#settings']   = $settings;
    $element['#attached']   = empty($build['attached']) ? $attachments : NestedArray::mergeDeep($build['attached'], $attachments);

    // Build the main Slick.
    $slick[0] = self::slick($build);

    // Build the thumbnail Slick.
    if (isset($build['thumb'])) {
      foreach (['items', 'options', 'settings'] as $key) {
        $build[$key] = isset($build['thumb'][$key]) ? $build['thumb'][$key] : [];
      }

      $settings                     = array_merge($settings, $build['settings']);
      $settings['optionset']        = $settings['optionset_thumbnail'];
      $settings['skin']             = isset($settings['skin_thumbnail']) ? $settings['skin_thumbnail'] : '';
      $settings['display']          = 'thumbnail';
      $build['optionset']           = $optionset_thumbnail;
      $build['settings']            = $settings;
      $build['options']['asNavFor'] = "#{$id}-slider";

      unset($build['thumb']);
      $slick[1] = self::slick($build);
    }

    if ($settings['navpos']) {
      $slick = array_reverse($slick);
    }

    // Collect the slick instances.
    $element['#items'] = $slick;
    return $element;
  }

}
