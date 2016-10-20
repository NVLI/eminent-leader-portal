<?php

namespace Drupal\blazy;

use Drupal\Core\Template\Attribute;
use Drupal\Component\Utility\Unicode;
use Drupal\Component\Serialization\Json;
use Drupal\blazy\Dejavu\BlazyDefault;

/**
 * Defines preprocess and alter methods specific to blazy.
 */
class Blazy extends BlazyManager {

  /**
   * Defines constant placeholder Data URI image.
   */
  const PLACEHOLDER = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';

  /**
   * Prepares variables for blazy templates.
   */
  public static function buildAttributes(&$variables) {
    $element = $variables['element'];
    foreach (['attributes', 'captions', 'item', 'item_attributes', 'settings', 'url', 'url_attributes'] as $key) {
      $variables[$key] = isset($element["#$key"]) ? $element["#$key"] : [];
    }

    // Load the supported formatter variables for the possesive blazy wrapper.
    $item               = $variables['item'];
    $settings           = &$variables['settings'];
    $attributes         = &$variables['attributes'];
    $image_attributes   = &$variables['item_attributes'];

    // Modifies variables.
    foreach (['icon', 'lightbox', 'media_switch', 'player', 'scheme', 'type'] as $key) {
      $settings[$key] = isset($settings[$key]) ? $settings[$key] : '';
    }

    $settings['ratio']   = empty($settings['ratio']) ? '' : str_replace(':', '', $settings['ratio']);
    $settings['item_id'] = empty($settings['item_id']) ? 'blazy' : $settings['item_id'];

    if (empty($settings['icon']) && !empty($settings['lightbox'])) {
      $settings['icon'] = ['#markup' => '<span class="media__icon media__icon--litebox"></span>'];
    }

    // Supports non-blazy formatter, that is, responsive image theme.
    $image  = &$variables['image'];
    $iframe = [];

    // Media URL is stored in the settings.
    $media = !empty($settings['embed_url']) && !empty($settings['type']) && in_array($settings['type'], ['video', 'audio']);

    // The regular non-responsive, non-lazyloaded image URI where image_url may
    // contain image_style which is not expected by responsive_image.
    $image['#uri'] = empty($settings['image_url']) ? $settings['uri'] : $settings['image_url'];

    if (!empty($settings['thumbnail_url'])) {
      $attributes['data-thumb'] = $settings['thumbnail_url'];
    }

    $height = isset($item->height) ? $item->height : NULL;
    $width  = isset($item->width) ? $item->width : NULL;
    $height = isset($settings['height']) ? $settings['height'] : $height;
    $width  = isset($settings['width']) ? $settings['width'] : $width;

    // Check whether we have responsive image, or lazyloaded one.
    if (!empty($settings['responsive_image_style_id']) && !empty($settings['uri'])) {
      $image['#type'] = 'responsive_image';
      $image['#responsive_image_style_id'] = $settings['responsive_image_style_id'];
      $image['#uri'] = $settings['uri'];

      // Disable aspect ratio which is not yet supported due to complexity.
      $settings['ratio'] = FALSE;
    }
    else {
      // Supports non-lazyloaded image.
      $image['#theme'] = 'image';

      if (!empty($settings['lazy'])) {
        $image['#uri'] = static::PLACEHOLDER;

        // Attach data-attributes to the either DIV or IMG container.
        if (empty($settings['background']) || empty($settings['blazy'])) {
          self::buildBreakpointAttributes($image_attributes, $settings);
        }

        // Supports both Slick and Blazy CSS background lazyloading.
        if (!empty($settings['background'])) {
          self::buildBreakpointAttributes($attributes, $settings);
          $attributes['class'][] = 'media--background';

          // Blazy doesn't need IMG to lazyload CSS background. Slick does.
          if (!empty($settings['blazy'])) {
            $image = [];
          }
        }
      }

      // Aspect ratio to fix layout reflow with lazyloaded images responsively.
      if (!empty($height) && !empty($settings['ratio']) && in_array($settings['ratio'], ['enforced', 'fluid'])) {
        $padding_bottom = isset($settings['padding_bottom']) ? $settings['padding_bottom'] : round((($height / $width) * 100), 2);
        $attributes['style'] = 'padding-bottom: ' . $padding_bottom . '%';
      }
    }

    // Image is optional for Video, and CSS background images.
    if ($image) {
      $image_attributes['height'] = $height;
      $image_attributes['width']  = $width;
      $image_attributes['alt']    = isset($item->alt) ? $item->alt : NULL;

      // Do not output an empty 'title' attribute.
      if (isset($item->title) && (Unicode::strlen($item->title) != 0)) {
        $image_attributes['title'] = $item->title;
      }

      $image_attributes['class'][] = 'media__image media__element';
      $image['#attributes'] = $image_attributes;
    }

    // Prepares a media player.
    if ($media) {
      // image : If iframe switch disabled, fallback to iframe, remove image.
      // player: If no colorbox/photobox, it is an image to iframe switcher.
      // data- : Gets consistent with colorbox to share JS manipulation.
      $lazy                    = empty($settings['lazy_attribute']) ? 'src' : $settings['lazy_attribute'];
      $image                   = empty($settings['media_switch']) ? [] : $image;
      $settings['player']      = empty($settings['lightbox']) && $settings['media_switch'] != 'content';
      $iframe['data-media']    = Json::encode(['type' => $settings['type'], 'scheme' => $settings['scheme']]);
      $iframe['data-' . $lazy] = $settings['embed_url'];
      $iframe['class'][]       = empty($settings['lazy_class']) ? 'b-lazy' : $settings['lazy_class'];
      $iframe['src']           = empty($settings['iframe_lazy']) ? $settings['embed_url'] : 'about:blank';
    }

    if (!empty($settings['caption'])) {
      $variables['caption_attributes'] = new Attribute();
      $variables['caption_attributes']->addClass($settings['item_id'] . '__caption');
    }

    // URL can be entity or lightbox URL different from the content image URL.
    $variables['content_attributes'] = new Attribute($iframe);
    $variables['url_attributes']     = new Attribute($variables['url_attributes']);
  }

  /**
   * Provides re-usable breakpoint data-attributes.
   *
   * $settings['breakpoints'] must contain: xs, sm, md, lg breakpoints with
   * the expected keys: width, image_style, url.
   *
   * @see self::buildAttributes()
   * @see BlazyManager::buildDataBlazy()
   * @see BlazyManager::getUrlBreakpoints()
   */
  public static function buildBreakpointAttributes(array &$attributes = [], $settings = []) {
    $lazy_attribute = empty($settings['lazy_attribute']) ? 'src' : $settings['lazy_attribute'];
    $lazy_class = empty($settings['lazy_class']) ? 'b-lazy' : $settings['lazy_class'];

    // Defines attributes, builtin, or supported lazyload such as Slick.
    // Required for multi-serving images as of Blazy v1.6.0.
    $attributes['class'][] = $lazy_class;
    $attributes['data-' . $lazy_attribute] = empty($settings['image_url']) ? '' : $settings['image_url'];

    if (!empty($settings['breakpoints'])) {
      if (!empty($settings['background'])) {
        foreach ($settings['breakpoints'] as $key => $breakpoint) {
          if (!empty($breakpoint['url'])) {
            $attributes['data-src-' . $key] = $breakpoint['url'];
          }
        }
      }
      elseif (!empty($settings['srcset'])) {
        $attributes['srcset'] = '';
        $attributes['data-srcset'] = $settings['srcset'];
        $attributes['sizes'] = '100w';

        if (!empty($settings['sizes'])) {
          $attributes['sizes'] = trim($settings['sizes']);
          unset($attributes['width']);
          unset($attributes['height']);
        }
      }
    }
  }

  /**
   * Implements hook_config_schema_info_alter().
   */
  public static function configSchemaInfoAlter(array &$definitions, $formatter = 'blazy_base', $settings = []) {
    if (isset($definitions[$formatter])) {
      $mappings = &$definitions[$formatter]['mapping'];
      $settings = $settings ?: BlazyDefault::extendedSettings();
      foreach ($settings as $key => $value) {
        $mappings[$key]['type'] = $key == 'breakpoints' ? 'mapping' : (is_array($value) ? 'sequence' : gettype($value));

        if (!is_array($value)) {
          $mappings[$key]['label'] = Unicode::ucfirst(str_replace('_' , ' ' , $key));
        }
      }

      if (isset($mappings['breakpoints'])) {
        foreach (BlazyDefault::getConstantBreakpoints() as $breakpoint) {
          $mappings['breakpoints']['mapping'][$breakpoint]['type'] = 'mapping';
          foreach (['breakpoint', 'width', 'image_style'] as $item) {
            $mappings['breakpoints']['mapping'][$breakpoint]['mapping'][$item]['type']  = 'string';
            $mappings['breakpoints']['mapping'][$breakpoint]['mapping'][$item]['label'] = Unicode::ucfirst(str_replace('_' , ' ' , $item));
          }
        }
      }

      if (isset($mappings['overridables'])) {
        $mappings['overridables']['label'] = 'Overridable options';
        $mappings['overridables']['sequence'][0]['type'] = 'string';
        $mappings['overridables']['sequence'][0]['label'] = 'Overridable';
      }
    }
  }

  /**
   * Return blazy global config.
   */
  public static function getConfig($setting_name = '', $settings = 'blazy.settings') {
    $config = \Drupal::service('config.factory')->get($settings);
    return empty($setting_name) ? $config->get() : $config->get($setting_name);
  }

  /**
   * Returns the HTML ID of a single instance.
   */
  public static function getHtmlId($string = 'blazy', $id = '') {
    return parent::getHtmlId($string, $id);
  }

}
