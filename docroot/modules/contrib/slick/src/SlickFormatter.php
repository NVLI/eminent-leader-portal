<?php

namespace Drupal\slick;

use Drupal\slick\Entity\Slick;
use Drupal\blazy\BlazyFormatterManager;

/**
 * Implements SlickFormatterInterface.
 */
class SlickFormatter extends BlazyFormatterManager implements SlickFormatterInterface {

  /**
   * {@inheritdoc}
   */
  public function buildSettings(array &$build = [], $items) {
    $settings = &$build['settings'];

    // Prepare integration with Blazy.
    $settings['item_id']          = 'slide';
    $settings['namespace']        = 'slick';
    $settings['theme_hook_image'] = isset($settings['theme_hook_image']) ? $settings['theme_hook_image'] : 'slick_image';

    parent::buildSettings($build, $items);

    $optionset_name     = $settings['optionset'] ?: 'default';
    $build['optionset'] = Slick::load($optionset_name);

    if (!isset($settings['nav'])) {
      $settings['nav'] = !empty($settings['optionset_thumbnail']) && isset($items[1]);
    }

    // Do not bother for SlickTextFormatter, or when vanilla is on.
    if (empty($settings['vanilla'])) {
      $resimage          = !empty($settings['responsive_image_style']);
      $lazy              = $build['optionset']->getSetting('lazyLoad');
      $lazy              = ($this->configLoad('responsive_image') && $resimage) ? 'blazy' : $lazy;
      $settings['blazy'] = $lazy == 'blazy' || !empty($settings['blazy']);
      $settings['lazy']  = $settings['blazy'] ? 'blazy' : $lazy;

      if (!$settings['blazy']) {
        $settings['lazy_class'] = $settings['lazy_attribute'] = 'lazy';
      }
    }
    else {
      // Nothings to work with Vanilla on, disable the asnavfor.
      $settings['nav'] = FALSE;
    }
  }

  /**
   * Gets the thumbnail image.
   */
  public function getThumbnail($settings = []) {
    $thumbnail = [];
    if (!empty($settings['uri'])) {
      $thumbnail = [
        '#theme'      => 'image_style',
        '#style_name' => $settings['thumbnail_style'],
        '#uri'        => $settings['uri'],
      ];

      foreach (['height', 'width', 'alt', 'title'] as $data) {
        $thumbnail["#$data"] = isset($settings[$data]) ? $settings[$data] : NULL;
      }
    }
    return $thumbnail;
  }

  /**
   * Overrides BlazyFormatterManager::getMediaSwitch().
   */
  public function getMediaSwitch(array &$element = [], $settings = []) {
    parent::getMediaSwitch($element, $settings);
    $switch = $settings['media_switch'];

    if (isset($element['#url_attributes'])) {
      $element['#url_attributes']['class'] = ['slick__' . $switch, 'litebox'];
    }
  }

}
