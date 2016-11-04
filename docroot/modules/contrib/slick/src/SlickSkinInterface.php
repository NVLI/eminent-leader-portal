<?php

namespace Drupal\slick;

/**
 * Provides an interface defining Slick skins.
 *
 * The hook_hook_info() is deprecated, and no resolution by 1/16/16:
 *   #2233261: Deprecate hook_hook_info()
 *     Postponed till D9
 */
interface SlickSkinInterface {

 /**
  * Returns the Slick skins.
  *
  * This can be used to register skins for the Slick. Skins will be
  * available when configuring the Optionset, Field formatter, or Views style,
  * or custom coded slicks.
  *
  * Slick skins get a unique CSS class to use for styling, e.g.:
  * If your skin name is "my_module_slick_carousel_rounded", the CSS class is:
  * slick--skin--my-module-slick-carousel-rounded
  *
  * A skin can specify some CSS and JS files to include when Slick is displayed,
  * except for a thumbnail skin which accepts CSS only.
  *
  * Each skin supports 5 keys:
  * - name: The human readable name of the skin.
  * - description: The description about the skin, for help and manage pages.
  * - css: An array of CSS files to attach.
  * - js: An array of JS files to attach, e.g.: image zoomer, reflection, etc.
  * - group: A string grouping the current skin: main, thumbnail.
  * - provider: A module name registering the skins.
  *
  * @return array
  *   The array of the main and thumbnail skins.
  */
  public function skins();

  /**
   * Returns the Slick dot skins.
   *
   * The provided dot skins will be available at sub-module UI form.
   * A skin dot named 'hop' will have a class 'slick-dots--hop' for the UL.
   *
   * The array is similar to the self::skins(), excluding group, JS.
   *
   * @return array
   *   The array of the dot skins.
   */
  // public function dots();

  /**
   * Returns the Slick arrow skins.
   *
   * The provided arrow skins will be available at sub-module UI form.
   * A skin arrow 'slit' will have a class 'slick__arrow--slit' for the NAV.
   *
   * The array is similar to the self::skins(), excluding group, JS.
   *
   * @return array
   *   The array of the arrow skins.
   */
  // public function arrows();

}
