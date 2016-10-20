<?php

namespace Drupal\blazy;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Component\Serialization\Json;
use Drupal\Component\Utility\NestedArray;

/**
 * Implements a public facing blazy manager.
 *
 * A few modules re-use this: GridStack, Mason, Slick...
 */
class BlazyManager extends BlazyManagerBase {

  /**
   * Builds URLs for individual breakpoint, 0 is respected.
   */
  public function getUrlBreakpoints(array &$settings = []) {
    if (!empty($settings['breakpoints']) && !empty($settings['uri'])) {
      $srcset = [];
      foreach ($settings['breakpoints'] as $key => $breakpoint) {
        $image_style = empty($breakpoint['image_style']) ? '' : $breakpoint['image_style'];

        if (!empty($image_style)) {
          $image_styles[$key] = $this->entityLoad($image_style, 'image_style');
          $url = $image_styles[$key]->buildUrl($settings['uri']);
          $settings['breakpoints'][$key]['url'] = $url;

          if ($breakpoint['width'] !== '') {
            $width = is_numeric($breakpoint['width']) ? $breakpoint['width'] . 'w' : $breakpoint['width'];
            $srcset[] = $url . ' ' . $width;
          }
        }
      }

      if ($srcset) {
        $settings['srcset'] = implode(', ', $srcset);
      }
    }
  }

  /**
   * Cleans up empty breakpoints.
   */
  public function cleanUpBreakpoints(array &$settings = []) {
    if (!empty($settings['breakpoints'])) {
      foreach ($settings['breakpoints'] as $key => $breakpoint) {
        if (empty($breakpoint['width']) && empty($breakpoint['image_style'])) {
          unset($settings['breakpoints'][$key]);
        }
      }
    }

    $settings['breakpoints'] = empty($settings['breakpoints']) ? [] : array_filter($settings['breakpoints']);

    // If breakpoints provided, enforce Blazy lazyloading without further ado.
    if (!empty($settings['breakpoints'])) {
      $settings['blazy'] = TRUE;
    }
  }

  /**
   * Checks for Blazy formatter such as from within a Views style plugin.
   *
   * Ensures the settings traverse up to the container where Blazy is clueless.
   * The supported plugins can add [data-blazy] attribute into its container
   * containing $settings['blazy_data'] converted into [data-blazy] JSON.
   *
   * @see \Drupal\gridstack\Plugin\views\style\GridStackViews::render().
   * @see \Drupal\slick_views\Plugin\views\style\SlickViews::render().
   * @see template_preprocess_slick().
   * @see template_preprocess_gridstack().
   *
   * @todo unified way between View style plugin and field formatter.
   */
  public function isBlazy(array &$settings = [], $item = []) {
    // Retrives Blazy formatter related settings from within Views style plugin.
    $item_id = $settings['item_id'];
    if (isset($item['settings']) && isset($item[$item_id]['#build']['settings'])) {
      $blazy_settings = $item[$item_id]['#build']['settings'];

      if (isset($blazy_settings['blazy_data'])) {
        $settings['blazy_data'] = $blazy_settings['blazy_data'];
      }

      // Allows breakpoints overrides such as multi-styled images by GridStack.
      if (empty($settings['breakpoints']) && isset($blazy_settings['breakpoints'])) {
        $settings['breakpoints'] = $blazy_settings['breakpoints'];
      }

      foreach (['box_style', 'image_style', 'lazy', 'media_switch', 'ratio'] as $key) {
        $fallback = isset($settings[$key]) ? $settings[$key] : '';
        $settings[$key] = isset($blazy_settings[$key]) && empty($fallback) ? $blazy_settings[$key] : $fallback;
      }
    }

    // If not Blazy formatter, build the Blazy data as some plugins use Blazy.
    if (isset($item['item']) && !isset($settings['blazy_data'])) {
      $settings['blazy_data'] = $this->buildDataBlazy($settings, $item['item']);
      $settings['blazy_data']['_reset'] = TRUE;
    }
  }

  /**
   * Builds breakpoints suitable for top-level [data-blazy] wrapper attributes.
   */
  public function buildDataBlazy(array &$settings = [], $item = NULL) {
    // Addresses the trouble with non-mobile-first approach.
    $settings['_dimensions_reset'] = TRUE;
    $this->getUrlDimensions($settings, $item);

    $json = $sources = [];
    if (!empty($settings['breakpoints'])) {
      $end = end($settings['breakpoints']);
      foreach ($settings['breakpoints'] as $key => $breakpoint) {
        if (empty($breakpoint['image_style'])) {
          continue;
        }

        $point = $breakpoint['width'];

        $image_styles[$point] = $this->entityLoad($breakpoint['image_style'], 'image_style');

        $dimensions[$point] = [
          'width'  => $settings['width'],
          'height' => $settings['height'],
        ];

        $image_styles[$point]->transformDimensions($dimensions[$point], $settings['uri']);

        $descriptor = $this->getDescriptors($point);
        $padding = round((($dimensions[$point]['height'] / $dimensions[$point]['width']) * 100), 2);
        $json['dimensions'][$descriptor] = $padding;

        // Helper for the BG option.
        if (empty($point)) {
          $point = $dimensions[$point]['width'];
        }

        if (!empty($settings['background'])) {
          $source          = [];
          $source['width'] = (int) $point;
          $source['src']   = 'data-src-' . $key;
          $sources[]       = $source;
        }

        // Only set CSS padding-bottom value for the last breakpoint.
        if (!empty($end['breakpoint']) && ($key == $end['breakpoint'] && $end['width'] == $point)) {
          $settings['padding_bottom'] = $padding;
        }
      }

      // Identify that Blazy can be activated only by breakpoints.
      $settings['blazy'] = TRUE;
    }

    if ($sources) {
      // As of Blazy v1.6.0 applied to BG only.
      $json['breakpoints'] = $sources;

      // @todo drop or fetch the last from breakpoints if available.
      if (!empty($settings['width'])) {
        $json['default'] = [$settings['width'], $settings['height']];
      }
    }

    // Clean up URIs since this is meant for the top-level.
    unset($settings['uri'], $settings['image_url']);
    return $json;
  }

  /**
   * Get the "w" (width) descriptor.
   */
  public function getDescriptors($point = '') {
    // Dynamic multi-serving aspect ratio with backward compatibility.
    if (is_numeric($point)) {
      $descriptor = $point;
    }
    else {
      // Cleanup w descriptor to fetch numerical width for JS aspect ratio.
      if (strpos($point, "w") !== FALSE) {
        $descriptor = str_replace('w', '', $point);
      }

      // If both w and x descriptors are provided.
      if (strpos($point, " ") !== FALSE) {
        // If the position is expected: 640w 2x.
        list($descriptor, $px) = array_pad(array_map('trim', explode(" ", $descriptor, 2)), 2, NULL);

        // If the position is reversed: 2x 640w.
        if (is_numeric($px) && strpos($descriptor, "x") !== FALSE) {
          $descriptor = $px;
        }
      }
    }
    return $descriptor;
  }

  /**
   * Defines image dimensions once as it costs, unless reset for breakpoints.
   */
  public function getUrlDimensions(array &$settings = [], $item = NULL, $modifier = NULL) {
    $settings['width']      = isset($settings['width'])  ? $settings['width']  : NULL;
    $settings['height']     = isset($settings['height']) ? $settings['height'] : NULL;
    $settings['cache_tags'] = empty($settings['cache_tags']) ? [] : $settings['cache_tags'];

    // This is not always available with a VEF textfield.
    if ($item && is_object($item)) {
      $settings['width']  = isset($item->width)  ? $item->width  : NULL;
      $settings['height'] = isset($item->height) ? $item->height : NULL;

      if (!isset($settings['uri'])) {
        $settings['uri'] = ($entity = $item->entity) && empty($item->uri) ? $entity->getFileUri() : $item->uri;
      }
    }

    if (!empty($settings['uri'])) {
      if (!isset($settings['image_url'])) {
        $settings['image_url'] = file_create_url($settings['uri']);
      }

      // No file API, no $item, with unmanaged VEF image without image_style.
      $modifier = empty($modifier) ? $settings['image_style'] : $modifier;
      if (empty($modifier) && empty($settings['width']) && !empty($settings['image_url'])) {
        list($settings['width'], $settings['height']) = getimagesize($settings['image_url']);
      }

      if (!empty($modifier)) {
        $style = $this->entityLoad($modifier, 'image_style');

        // Image URLs are for lazyloaded images.
        $settings['image_url']  = $style->buildUrl($settings['uri']);
        $settings['cache_tags'] = $style->getCacheTags();

        // Unless reset for multi-styled images, set dimensions once.
        if (empty($settings['_dimensions']) || isset($settings['_dimensions_reset'])) {
          $dimensions = [
            'width'  => $settings['width'],
            'height' => $settings['height'],
          ];
          $style->transformDimensions($dimensions, $settings['uri']);
          $settings['height']      = $dimensions['height'];
          $settings['width']       = $dimensions['width'];
          $settings['_dimensions'] = TRUE;
        }
      }
    }
  }

  /**
   * Returns the image based on the Responsive image mapping, or blazy.
   */
  public function getImage($build = []) {
    /* @var Drupal\image\Plugin\Field\FieldType\ImageItem $item */
    $item      = $build['item'];
    $settings  = &$build['settings'];
    $namespace = $settings['namespace'] = empty($settings['namespace']) ? 'blazy' : $settings['namespace'];
    $theme     = isset($settings['theme_hook_image']) ? $settings['theme_hook_image'] : 'blazy';

    if (empty($item)) {
      return [];
    }

    $image = [
      '#theme'       => $theme,
      '#item'        => [],
      '#delta'       => isset($settings['delta']) ? $settings['delta'] : 0,
      '#image_style' => $settings['image_style'],
      '#pre_render'  => [[$this, 'preRenderImage']],
    ];

    // Gets individual image URLs, and dimensions set once.
    $this->getUrlDimensions($settings, $item, $image['#image_style']);

    // Gets multi-serving image URLs if breakpoints are provided.
    if (!empty($settings['breakpoints'])) {
      $this->getUrlBreakpoints($settings);
    }

    $image['#build'] = $build;

    if (!isset($settings['_no_cache'])) {
      $file_tags = isset($settings['file_tags']) ? $settings['file_tags'] : [];
      $settings['cache_tags'] = empty($settings['cache_tags']) ? $file_tags : Cache::mergeTags($settings['cache_tags'], $file_tags);
      $image['#cache'] = ['tags' => $settings['cache_tags']];

      if (isset($settings['cache_keys'])) {
        $image['#cache']['keys'] = $settings['cache_keys'];
      }
    }

    if (isset($settings['theme_hook_image_wrapper'])) {
      $image['#theme_wrappers'][] = $settings['theme_hook_image_wrapper'];
    }

    $this->getModuleHandler()->alter($namespace . '_image', $image, $settings);

    return $image;
  }

  /**
   * Builds the Blazy image as a structured array ready for ::renderer().
   */
  public function preRenderImage($element) {
    $build = $element['#build'];
    $item  = $build['item'];
    unset($element['#build']);

    $settings = &$build['settings'];
    if (empty($item)) {
      return [];
    }

    // Extract field item attributes for the theme function, and unset them
    // from the $item so that the field template does not re-render them.
    $item_attributes = [];
    if (isset($item->_attributes)) {
      $item_attributes = $item->_attributes;
      unset($item->_attributes);
    }

    $element['#item'] = $item;

    // Responsive image integration.
    $settings['responsive_image_style_id'] = '';
    if (!empty($settings['resimage']) && !empty($settings['responsive_image_style'])) {
      $responsive_image_style = $this->entityLoad($settings['responsive_image_style'], 'responsive_image_style');
      $settings['responsive_image_style_id'] = $responsive_image_style->id() ?: '';
      $settings['lazy'] = '';
      if (!empty($settings['responsive_image_style_id'])) {
        if ($this->configLoad('responsive_image')) {
          $item_attributes['data-srcset'] = TRUE;
          $settings['lazy'] = 'responsive';
        }
        $element['#cache']['tags'] = $this->getResponsiveImageCacheTags($responsive_image_style);
      }
    }

    // With CSS background, IMG may be empty, so add thumbnail to the container.
    if (!empty($settings['thumbnail_style'])) {
      $settings['thumbnail_url'] = $this->entityLoad($settings['thumbnail_style'], 'image_style')->buildUrl($settings['uri']);
    }

    $element['#url']             = '';
    $element['#settings']        = $settings;
    $element['#captions']        = isset($build['captions']) ? ['inline' => $build['captions']] : [];
    $element['#item_attributes'] = $item_attributes;

    if (!empty($settings['media_switch']) && ($settings['media_switch'] == 'content' || strpos($settings['media_switch'], 'box') !== FALSE)) {
      $this->getMediaSwitch($element, $settings);
    }

    return $element;
  }

  /**
   * Gets the media switch options: colorbox, photobox, content.
   */
  public function getMediaSwitch(array &$element = [], $settings = []) {
    $item   = $element['#item'];
    $type   = isset($settings['type']) ? $settings['type'] : 'image';
    $uri    = $settings['uri'];
    $switch = $settings['media_switch'];

    // Provide relevant URL if it is a lightbox.
    if (strpos($switch, 'box') !== FALSE) {
      $json = ['type' => $type];
      $url_attributes = [];

      // If it is a video/audio, otherwise image to image.
      if (!empty($settings['embed_url'])) {
        $url = $settings['embed_url'];
        $json['scheme'] = $settings['scheme'];
        // Force autoplay for media URL on lightboxes, saving another click.
        if ($json['scheme'] == 'soundcloud') {
          if (strpos($url, 'auto_play') === FALSE || strpos($url, 'auto_play=false') !== FALSE) {
            $url = strpos($url, '?') === FALSE ? $url . '?auto_play=true' : $url . '&amp;auto_play=true';
          }
        }
        elseif (strpos($url, 'autoplay') === FALSE || strpos($url, 'autoplay=0') !== FALSE) {
          $url = strpos($url, '?') === FALSE ? $url . '?autoplay=1' : $url . '&amp;autoplay=1';
        }
      }
      else {
        $url = empty($settings['box_style']) ? file_create_url($uri) : $this->entityLoad($settings['box_style'], 'image_style')->buildUrl($uri);
      }

      $classes = ['blazy__' . $switch, 'litebox'];
      if ($switch == 'colorbox' && $settings['count'] > 1) {
        $json['rel'] = $settings['id'];
      }
      elseif ($switch == 'photobox' && !empty($settings['embed_url'])) {
        $url_attributes['rel'] = 'video';
      }

      // Provides lightbox media dimension if so configured.
      if ($type != 'image') {
        if (!empty($settings['dimension'])) {
          list($settings['box_width'], $settings['box_height']) = array_pad(array_map('trim', explode("x", $settings['dimension'], 2)), 2, NULL);
        }
        $json['width']  = empty($settings['box_width'])  ? $settings['width']  : $settings['box_width'];
        $json['height'] = empty($settings['box_height']) ? $settings['height'] : $settings['box_height'];
      }

      $url_attributes['class'] = $classes;
      $url_attributes['data-media'] = Json::encode($json);
      $url_attributes['data-' . $switch] = TRUE;

      $element['#url'] = $url;
      $element['#url_attributes'] = $url_attributes;
      $element['#settings']['lightbox'] = $switch;

      if (!empty($settings['box_caption'])) {
        $element['#captions']['lightbox'] = self::buildCaptions($item, $settings);
      }
    }
    elseif ($switch == 'content' && !empty($settings['absolute_path'])) {
      $element['#url'] = $settings['absolute_path'];
    }
  }

  /**
   * Build lightbox captions.
   */
  public static function buildCaptions($item, $settings = []) {
    $title   = empty($item->title) ? '' : $item->title;
    $alt     = empty($item->alt)   ? '' : $item->alt;
    $delta   = $settings['delta'];
    $caption = '';

    switch ($settings['box_caption']) {
      case 'auto':
        $caption = $alt ?: $title;
        break;

      case 'alt':
        $caption = $alt;
        break;

      case 'title':
        $caption = $title;
        break;

      case 'alt_title':
      case 'title_alt':
        $alt     = $alt ? '<p>' . $alt . '</p>' : '';
        $title   = $title ? '<h2>' . $title . '</h2>' : '';
        $caption = $settings['box_caption'] == 'alt_title' ? $alt . $title : $title . $alt;
        break;

      case 'entity_title':
        $caption = ($entity = $item->getEntity()) ? $entity->label() : '';
        break;

      case 'custom':
        $token = \Drupal::token();
        $caption = '';
        if ($entity = $item->getEntity()) {
          $entity_type = $entity->getEntityTypeId();

          $options = ['clear' => TRUE];
          $caption = $token->replace($settings['box_caption_custom'], [$entity_type => $entity, 'file' => $item], $options);

          // Checks for multi-value text fields, and maps its delta to image.
          if (strpos($caption, ", <p>") !== FALSE) {
            $caption = str_replace(", <p>", '| <p>', $caption);
            $captions = explode("|", $caption);
            $caption = isset($captions[$delta]) ? $captions[$delta] : '';
          }
        }
        break;

      default:
        $caption = '';
    }

    return empty($caption) ? [] : ['#markup' => $caption];
  }

  /**
   * Returns the entity view, if available.
   */
  public function getEntityView($entity = NULL, $settings = []) {
    if ($entity && $entity instanceof EntityInterface) {
      $entity_type_id = $entity->getEntityTypeId();
      $view_hook      = $entity_type_id . '_view';

      // If module implements own {entity_type}_view.
      if (function_exists($view_hook)) {
        return $view_hook($entity);
      }
      // If entity has view_builder handler.
      elseif ($this->getEntityTypeManager()->hasHandler($entity_type_id, 'view_builder')) {
        $view_mode = empty($settings['view_mode']) ? 'default' : $settings['view_mode'];
        return $this->getEntityTypeManager()->getViewBuilder($entity_type_id)->view($entity, $view_mode, $entity->language()->getId());
      }
    }

    return FALSE;
  }

  /**
   * Returns the Responsive image cache tags.
   */
  public function getResponsiveImageCacheTags($responsive_image_style = NULL) {
    $cache_tags = [];
    $image_styles_to_load = [];
    if ($responsive_image_style) {
      $cache_tags = Cache::mergeTags($cache_tags, $responsive_image_style->getCacheTags());
      $image_styles_to_load = $responsive_image_style->getImageStyleIds();
    }

    $image_styles = $this->entityLoadMultiple('image_style', $image_styles_to_load);
    foreach ($image_styles as $image_style) {
      $cache_tags = Cache::mergeTags($cache_tags, $image_style->getCacheTags());
    }
    return $cache_tags;
  }

}
