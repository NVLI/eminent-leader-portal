<?php

/**
 * @file
 * Hooks and preprocess functions for the Slick module.
 */

use Drupal\Core\Template\Attribute;
use Drupal\Component\Serialization\Json;
use Drupal\slick\Entity\Slick;

/**
 * Prepares variables for slick.html.twig templates.
 */
function template_preprocess_slick(&$variables) {
  $element   = $variables['element'];
  $defaults  = Slick::htmlSettings();
  $settings  = isset($element['#settings']) ? array_merge($defaults, $element['#settings']) : $defaults;
  $customs   = isset($element['#options']) ? $element['#options'] : [];
  $optionset = isset($element['#optionset']) ? $element['#optionset'] : Slick::load($settings['optionset']);
  $js        = $customs ? array_merge($optionset->getSettings(), $customs) : $optionset->getSettings();

  if (isset($variables['settings'])) {
    $settings = array_merge($settings, $variables['settings']);
  }

  // Prepare attributes.
  $attributes = &$variables['attributes'];
  $content_attributes = new Attribute();
  $display = $variables['display'] = $settings['display'];

  $id = empty($settings['id']) ? Slick::getHtmlId('slick') : $settings['id'];
  $attributes['id'] = $display == 'thumbnail' ? $id . '-thumbnail' : $id;

  // Sniffs for Views to allow block__no_wrapper, views__no_wrapper, etc.
  if ($settings['view_name'] && $settings['current_view_mode']) {
    $settings['attributes']['class'][] = str_replace('_', '-', 'slick--view--' . $settings['view_name']);
    $settings['attributes']['class'][] = str_replace('_', '-', 'slick--view--' . $settings['view_name'] . '--' . $settings['current_view_mode']);
  }

  // @see SlickManager::buildGrid(), and this should make sense.
  $settings['count']   = isset($settings['count']) ? $settings['count'] : count($element['#items']);
  $settings['unslick'] = $settings['unslick'] || $settings['count'] == 1;

  foreach (['asNavFor', 'nextArrow', 'prevArrow', 'downArrowTarget', 'downArrowOffset', 'slidesToShow'] as $key) {
    $settings[$key] = isset($js[$key]) ? $js[$key] : FALSE;
  }

  $settings['lazy'] = isset($settings['lazy']) ? $settings['lazy'] : $js['lazyLoad'];

  // Blazy can still lazyload an unslick.
  if ($display != 'thumbnail' && ($settings['lazy'] == 'blazy' || !empty($settings['blazy']))) {
    $js['lazyLoad'] = 'blazy';
    $blazy = empty($settings['blazy_data']) ? [] : $settings['blazy_data'];
    $blazy['ratio'] = $settings['ratio'];
    if (!empty($settings['width']) && empty($blazy['ratio'])) {
      $blazy['default'] = [$settings['width'], $settings['height']];
    }
    $attributes['data-blazy'] = Json::encode($blazy);
  }

  // Prevents broken slick when only one item given, or an enforced unslick.
  if (!$settings['unslick']) {
    $content_attributes->setAttribute('id', $attributes['id'] . '-slider');

    $settings['arrow']['attributes'] = new Attribute();
    if ($display == 'main' && $js['downArrow'] && $js['downArrowTarget']) {
      $settings['downArrow']['attributes'] = new Attribute();
    }

    // focusOnSelect won't work with empty slide value, so add proper selector.
    if (empty($js['slide']) && $js['focusOnSelect']) {
      $js['slide'] = ($js['rows'] == 1 && $js['slidesPerRow'] == 1) ? '.slick__slide' : $js['slide'];
    }

    if ($js['rtl']) {
      $attributes['dir'] = 'rtl';
    }

    // Add the configuration as JSON object into the slick container.
    if ($json = $optionset->removeDefaultValues($js)) {
      $content_attributes->setAttribute('data-slick', Json::encode($json));
    }
  }

  // Pass settings and attributes to twig.
  $variables['settings'] = $settings;
  $variables['content_attributes'] = $content_attributes;

  // Process individual item.
  $variables['items'] = [];
  foreach ($element['#items'] as $delta => $item) {
    $settings['current_item'] = $display;
    $settings = isset($item['settings']) ? array_merge($settings, $item['settings']) : $settings;
    $item_attributes = isset($item['attributes']) ? $item['attributes'] : [];
    unset($item['settings'], $item['attributes']);

    $theme = $settings['vanilla'] ? 'vanilla' : ($display == 'thumbnail' ? 'thumbnail' : 'slide');

    $slide = [
      '#theme'      => 'slick_' . $theme,
      '#item'       => $item,
      '#delta'      => $delta,
      '#settings'   => $settings,
      '#attributes' => $item_attributes,
    ];
    $variables['items'][$delta] = $slide;
    unset($slide);
  }
}

/**
 * Prepares variables for slick-wrapper.html.twig templates.
 */
function template_preprocess_slick_wrapper(&$variables) {
  foreach (['attributes', 'items', 'settings'] as $key) {
    $variables[$key] = isset($variables['element']["#$key"]) ? $variables['element']["#$key"] : [];
  }
}

/**
 * Prepares common variables for slick item templates.
 */
function _slick_preprocess_slick_item(&$variables) {
  foreach (['attributes', 'delta', 'item', 'settings'] as $key) {
    $default = $key == 'delta' ? NULL : [];
    $variables[$key] = isset($variables['element']["#$key"]) ? $variables['element']["#$key"] : $default;
  }
}

/**
 * Prepares variables for slick-vanilla.html.twig templates.
 */
function template_preprocess_slick_vanilla(&$variables) {
  _slick_preprocess_slick_item($variables);
}

/**
 * Prepares variables for slick-thumbnail.html.twig templates.
 */
function template_preprocess_slick_thumbnail(&$variables) {
  _slick_preprocess_slick_item($variables);
}

/**
 * Prepares variables for slick-slide.html.twig templates.
 */
function template_preprocess_slick_slide(&$variables) {
  _slick_preprocess_slick_item($variables);

  // All slide types -- main, thumbnail, grid, overlay -- may have captions.
  foreach (['alt', 'data', 'link', 'overlay', 'title'] as $key) {
    $variables['item']['caption'][$key] = isset($variables['item']['caption'][$key]) ? $variables['item']['caption'][$key] : [];
  }

  $item     = &$variables['item'];
  $settings = &$variables['settings'];

  // split: Split image from captions if we do have captions, and main image.
  // fullwidth: If full skins, add wrappers to hold caption and overlay.
  // detroy: Remove .slide__content if it is an enforced unslick grid.
  // wrapper: Don't add divities for a single item to have clean markups.
  $item['slide']         = isset($item['slide']) ? $item['slide'] : [];
  $item['caption']       = array_filter($item['caption']);
  $settings['split']     = !empty($item) && !empty($settings['caption']);
  $settings['data']      = !empty($item['caption']['alt']) || !empty($item['caption']['title']) || !empty($item['caption']['data']);
  $settings['fullwidth'] = !empty($settings['skin']) && strpos($settings['skin'], 'full') !== FALSE;
  $settings['detroy']    = $settings['current_item'] == 'main' && !empty($settings['grid']) && !empty($settings['unslick']);
  $settings['wrapper']   = $settings['count'] > 1 && $settings['current_item'] != 'grid';
}

/**
 * Prepares variables for slick-grid.html.twig templates.
 */
function template_preprocess_slick_grid(&$variables) {
  $variables['settings'] = $variables['element']['#settings'];

  $variables['items'] = [];
  foreach ($variables['element']['#items'] as $delta => $item) {
    $variables['settings']['current_item'] = 'grid';
    $variables['settings'] = isset($item['settings']) ? array_merge($variables['settings'], $item['settings']) : $variables['settings'];
    unset($item['settings']);

    $slide['slide'] = [
      '#theme'    => 'slick_slide',
      '#item'     => $item,
      '#delta'    => $delta,
      '#settings' => $variables['settings'],
    ];

    $slide['attributes'] = new Attribute();
    $variables['items'][$delta] = $slide;
    unset($slide);
  }
}

/**
 * Prepares variables for slick-image.html.twig template.
 */
function template_preprocess_slick_image(&$variables) {
  template_preprocess_blazy($variables);
}
