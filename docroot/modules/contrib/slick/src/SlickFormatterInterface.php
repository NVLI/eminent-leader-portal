<?php

namespace Drupal\slick;

/**
 * Defines re-usable services and functions for slick field plugins.
 */
interface SlickFormatterInterface {

  /**
   * Returns the slick field formatter and custom coded settings.
   *
   * @param array $build
   *   The array containing: settings, optionset.
   * @param array $items
   *   The items to prepare settings for.
   *
   * @return array
   *   The combined settings of a slick field formatter.
   */
  public function buildSettings(array &$build = [], $items);

}
