<?php

/**
 * @file
 * Contains \Drupal\blazy\Form\BlazyAdminFormatterBase.
 */

namespace Drupal\blazy\Form;

use Drupal\Core\Url;
use Drupal\Core\Form\FormState;

/**
 * A base for field formatter admin to have re-usable methods in one place.
 */
abstract class BlazyAdminFormatterBase extends BlazyAdminBase {

  /**
   * Returns re-usable image formatter form elements.
   */
  public function imageStyleForm(array &$form, $definition = []) {
    $image_styles  = image_style_options(FALSE);
    $is_responsive = function_exists('responsive_image_get_image_dimensions');

    $form['image_style'] = [
      '#type'        => 'select',
      '#title'       => t('Image style'),
      '#options'     => $image_styles,
      '#description' => t('The content image style. Ignored if Breakpoints are provided, use smaller image style here instead. Otherwise this is the only image displayed.'),
      '#weight'      => -100,
    ];

    $form['responsive_image_style'] = [
      '#type'        => 'select',
      '#title'       => t('Responsive image'),
      '#options'     => $this->getResponsiveImageOptions(),
      '#description' => t('Responsive image style for the main stage image is more reasonable for large images. Not compatible with aspect ratio, yet. Leave empty to disable.'),
      '#access'      => $is_responsive && $this->getResponsiveImageOptions(),
      '#weight'      => -100,
    ];

    $form['thumbnail_style'] = [
      '#type'        => 'select',
      '#title'       => t('Thumbnail style'),
      '#options'     => $image_styles,
      '#description' => t('Usages: Photobox thumbnail, or custom work with thumbnails. Leave empty to not use thumbnails.'),
      '#access'      => isset($definition['thumbnail_styles']),
      '#weight'      => -100,
    ];

    $form['thumbnail_effect'] = [
      '#type'        => 'select',
      '#title'       => t('Thumbnail effect'),
      '#options'     => isset($definition['thumbnail_effects']) ? $definition['thumbnail_effects'] : [],
      '#access'      => isset($definition['thumbnail_effects']),
      '#weight'      => -100,
      // '#states'      => $this->getState(static::STATE_THUMBNAIL_STYLE_ENABLED, $definition),
    ];

    if ($is_responsive) {
      $url = Url::fromRoute('entity.responsive_image_style.collection')->toString();
      $form['responsive_image_style']['#description'] .= ' ' . t('<a href=":url" target="_blank">Manage responsive image styles</a>.', [':url' => $url]);
    }

    $form['background']['#states'] = $this->getState(static::STATE_RESPONSIVE_IMAGE_STYLE_DISABLED, $definition);
  }

  /**
   * Returns re-usable media switch form elements.
   */
  public function mediaSwitchForm(array &$form, $definition = []) {
    $is_colorbox   = function_exists('colorbox_theme');
    $is_photobox   = function_exists('photobox_theme');
    $is_responsive = function_exists('responsive_image_get_image_dimensions');
    $image_styles  = image_style_options(FALSE);
    $photobox      = \Drupal::root() . '/libraries/photobox/photobox/jquery.photobox.js';

    if (is_file($photobox)) {
      $is_photobox = TRUE;
    }

    $form['media_switch'] = [
      '#type'        => 'select',
      '#title'       => t('Media switcher'),
      '#options'     => [
        'content' => t('Image linked to content'),
      ],
      '#description' => t('May depend on the enabled supported modules: colorbox, photobox. Be sure to add Thumbnail style if using Photobox.'),
      '#prefix'      => '<h3 class="form__title">' . t('Media switcher') . '</h3>',
      '#weight'      => -99,
      '#access'      => isset($definition['media_switch_form']),
    ];

    // http://en.wikipedia.org/wiki/List_of_common_resolutions
    $ratio = ['1:1', '3:2', '4:3', '8:5', '16:9', 'fluid', 'enforced'];
    $form['ratio'] = [
      '#type'        => 'select',
      '#title'       => t('Aspect ratio'),
      '#options'     => array_combine($ratio, $ratio),
      '#description' => t('Aspect ratio to get consistently responsive images and iframes. And to fix layout reflow and excessive height issues. <a href="@dimensions" target="_blank">Image styles and video dimensions</a> must <a href="@follow" target="_blank">follow the aspect ratio</a>. If not, images will be unexpectedly distorted. Choose <strong>fluid</strong> if unsure. Choose <strong>enforced</strong> if you can stick to one aspect ratio and want multi-serving, or Responsive images. <a href="@link" target="_blank">Learn more</a>, or leave empty if you care not for aspect ratio, or prefer to DIY. <br /><strong>Note!</strong> Only compatible with Blazy multi-serving images, but not with Responsive image, unless they stick to one aspect ratio with an <strong>enforced</strong> ratio.', [
        '@dimensions' => '//size43.com/jqueryVideoTool.html',
        '@follow'     => '//en.wikipedia.org/wiki/Aspect_ratio_%28image%29',
        '@link'       => '//www.smashingmagazine.com/2014/02/27/making-embedded-content-work-in-responsive-design/',
      ]),
      '#access'       => isset($definition['media_switch_form']),
      '#weight'       => -96,
      '#states'       => $this->getState(static::STATE_RESPONSIVE_IMAGE_STYLE_DISABLED, $definition),
    ];

    $form['iframe_lazy'] = [
      '#type'        => 'checkbox',
      '#title'       => t('Lazy iframe'),
      '#description' => t('Check to make the video/audio iframes truly lazyloaded, and speed up loading time. Depends on JS enabled at client side. <a href=":more" target="_blank">Read more</a> to <a href=":url" target="_blank">decide</a>.', [':more' => '//goo.gl/FQLFQ6', ':url' => '//goo.gl/f78pMl']),
      '#access'      => isset($definition['multimedia']),
      '#weight'      => -96,
      '#states'      => $this->getState(static::STATE_IFRAME_ENABLED, $definition),
    ];

    $form['view_mode'] = [
      '#type'        => 'select',
      '#options'     => isset($definition['target_type']) ? $this->getViewModeOptions($definition['target_type']) : [],
      '#title'       => t('View mode'),
      '#description' => t('Required to grab the fields. Be sure the selected "View mode" is enabled, and the enabled fields here are not hidden there. Manage view modes on the <a href=":view_modes">View modes page</a>.', [':view_modes' => Url::fromRoute('entity.entity_view_mode.collection')->toString()]),
      '#access'      => isset($definition['fieldable_form']) && isset($definition['target_type']),
      '#weight'      => -96,
    ];

    // Optional lightbox integration.
    if ($is_colorbox || $is_photobox || isset($definition['lightbox'])) {
      if ($is_colorbox) {
        $form['media_switch']['#options']['colorbox'] = t('Image to colorbox');
      }

      if ($is_photobox) {
        $form['media_switch']['#options']['photobox'] = t('Image to photobox');
      }

      // Re-use the same image style for both lightboxes.
      $form['box_style'] = [
        '#type'    => 'select',
        '#title'   => t('Lightbox image style'),
        '#options' => $image_styles,
        '#weight'  => -99,
      ];

      if (!isset($definition['lightbox'])) {
        $form['box_style']['#states'] = $this->getState(static::STATE_LIGHTBOX_ENABLED, $definition);
      }

      $form['dimension'] = [
        '#type'        => 'textfield',
        '#title'       => t('Lightbox media dimension'),
        '#description' => t('Use WIDTHxHEIGHT, e.g.: 640x360. This allows video dimensions for the lightbox to be different from the lightbox image style.'),
        '#access'      => isset($definition['multimedia']),
        '#weight'      => -99,
        '#states'      => $this->getState(static::STATE_LIGHTBOX_ENABLED, $definition),
      ];
    }
  }

  /**
   * Return the field formatter settings summary.
   */
  public function settingsSummary($plugin) {
    $form         = [];
    $form_state   = new FormState();
    $settings     = $plugin->getSettings();
    $elements     = $plugin->settingsForm($form, $form_state);
    $definition   = $this->typedConfig->getDefinition('field.formatter.settings.' . $plugin->getPluginId());
    $image_styles = image_style_options(TRUE);
    $breakpoints  = isset($settings['breakpoints']) ? array_filter($settings['breakpoints']) : [];

    unset($image_styles['']);

    foreach ($settings as $key => $setting) {
      $access  = isset($elements[$key]['#access'])  ? $elements[$key]['#access']  : TRUE;
      $title   = isset($elements[$key]['#title'])   ? $elements[$key]['#title']   : '';
      $options = isset($elements[$key]['#options']) ? $elements[$key]['#options'] : [];
      $vanilla = !empty($settings['vanilla']) && !isset($elements[$key]['#enforced']);

      if ($key == 'breakpoints') {
        $widths = [];
        if ($breakpoints) {
          foreach ($breakpoints as $id => $breakpoint) {
            if (!empty($breakpoint['width'])) {
              $widths[] = $breakpoint['width'];
            }
          }
        }

        $title   = t('Breakpoints');
        $setting = $widths ? implode(', ', $widths) : t('None');
      }
      else {
        if (is_array($setting) || empty($title) || $vanilla || !$access) {
          continue;
        }

        if (isset($definition['mapping']) && isset($definition['mapping'][$key])) {
          if ($definition['mapping'][$key]['type'] == 'boolean') {
            if (empty($setting)) {
              continue;
            }
            $setting = t('Yes');
          }
          elseif ($definition['mapping'][$key]['type'] == 'string' && empty($setting)) {
            continue;
          }
        }

        if ($key == 'cache') {
          $setting = $this->getCacheOptions()[$setting];
        }

        // Value is based on select options.
        if (isset($options[$settings[$key]])) {
          $setting = is_object($options[$settings[$key]]) ? $options[$settings[$key]]->render() : $options[$settings[$key]];
        }
      }

      if (isset($settings[$key])) {
        $summary[] = t('@title: <strong>@setting</strong>', [
          '@title'   => $title,
          '@setting' => $setting,
        ]);
      }
    }
    return $summary;
  }

  /**
   * Returns available fields for select options.
   */
  public function getFieldOptions($target_bundles = [], $allowed_field_types = [], $entity_type_id = 'media') {
    $options = [];
    $storage = $this->blazyManager()->getEntityTypeManager()->getStorage('field_config');

    // Fix for Views UI not recognizing Media bundles, unlike Formatters.
    $bundle_service = \Drupal::service('entity_type.bundle.info');
    if (empty($target_bundles)) {
      $target_bundles = $bundle_service->getBundleInfo($entity_type_id);
    }

    foreach ($target_bundles as $bundle => $label) {
      if ($fields = $storage->loadByProperties(['entity_type' => $entity_type_id, 'bundle' => $bundle])) {
        foreach ((array) $fields as $field_name => $field) {
          if (empty($allowed_field_types)) {
            $options[$field->getName()] = $field->getLabel();
          }
          elseif (in_array($field->getType(), $allowed_field_types)) {
            $options[$field->getName()] = $field->getLabel();
          }
        }
      }
    }

    return $options;
  }

  /**
   * Returns Responsive image for select options.
   */
  public function getResponsiveImageOptions() {
    $options = [];
    if ($this->blazyManager()->getModuleHandler()->moduleExists('responsive_image')) {
      $image_styles = $this->blazyManager()->entityLoadMultiple('responsive_image_style');
      if (!empty($image_styles)) {
        foreach ($image_styles as $name => $image_style) {
          if ($image_style->hasImageStyleMappings()) {
            $options[$name] = strip_tags($image_style->label());
          }
        }
      }
    }
    return $options;
  }

}
