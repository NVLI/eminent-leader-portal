<?php

namespace Drupal\blazy\Dejavu;

/**
 * Defines shared plugin default settings for field formatter and Views style.
 */
class BlazyDefault {

  /**
   * The supported $breakpoints.
   *
   * @const $breakpoints.
   */
  private static $breakpoints = ['xs', 'sm', 'md', 'lg', 'xl'];

  /**
   * Returns Blazy specific breakpoints.
   */
  public static function getConstantBreakpoints() {
    return self::$breakpoints;
  }

  /**
   * Returns basic plugin settings.
   */
  public static function baseSettings() {
    return [
      'cache'             => 0,
      'current_view_mode' => '',
      'item_id'           => '',
      'optionset'         => 'default',
      'skin'              => '',
    ];
  }

  /**
   * Returns image-related field formatter and Views settings.
   */
  public static function imageSettings() {
    return [
      'background'             => FALSE,
      'box_caption'            => '',
      'box_caption_custom'     => '',
      'box_style'              => '',
      'breakpoints'            => [],
      'caption'                => [],
      'icon'                   => FALSE,
      'image_style'            => '',
      'layout'                 => '',
      'media_switch'           => '',
      'ratio'                  => '',
      'responsive_image_style' => '',
      'sizes'                  => '',
      'thumbnail_style'        => '',
    ] + self::baseSettings();
  }

  /**
   * Returns fieldable entity formatter and Views settings.
   */
  public static function extendedSettings() {
    return [
      'class'       => '',
      'dimension'   => '',
      'id'          => '',
      'iframe_lazy' => FALSE,
      'image'       => '',
      'link'        => '',
      'overlay'     => '',
      'title'       => '',
      'view_mode'   => '',
      'vanilla'     => FALSE,
    ] + self::imageSettings();
  }

}
