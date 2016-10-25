<?php

namespace Drupal\blazy\Dejavu;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\Plugin\Field\FieldFormatter\EntityReferenceFormatterBase;

/**
 * Base class for blazy entity reference formatters.
 */
abstract class BlazyEntityReferenceBase extends EntityReferenceFormatterBase {
  use BlazyEntityTrait;

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return BlazyDefault::extendedSettings();
  }

  /**
   * Returns media contents.
   */
  public function buildElements(array &$build = [], $entities, $langcode) {
    $settings  = &$build['settings'];
    $view_mode = $settings['view_mode'] ?: 'full';

    foreach ($entities as $delta => $entity) {
      // Protect ourselves from recursive rendering.
      static $depth = 0;
      $depth++;
      if ($depth > 20) {
        $this->loggerFactory->get('entity')->error('Recursive rendering detected when rendering entity @entity_type @entity_id. Aborting rendering.', array('@entity_type' => $entity->getEntityTypeId(), '@entity_id' => $entity->id()));
        return $build;
      }

      $settings['delta'] = $delta;
      if ($entity->id()) {
        if (!empty($settings['vanilla'])) {
          $build['items'][$delta] = $this->manager()->getEntityTypeManager()->getViewBuilder($entity->getEntityTypeId())->view($entity, $view_mode, $langcode);
        }
        else {
          $this->buildElement($build, $entity, $langcode);
        }

        // Add the entity to cache dependencies so to clear when it is updated.
        $this->manager()->getRenderer()->addCacheableDependency($build['items'][$delta], $entity);
      }
      else {
        $this->referencedEntities = NULL;
        // This is an "auto_create" item.
        $build[$delta] = array('#markup' => $entity->label());
      }

      $depth = 0;
    }

    // Supports Blazy formatter multi-breakpoint images if available.
    $this->formatter->isBlazy($settings, $build['items'][0]);

    return $build;
  }

  /**
   * Returns slide contents.
   */
  public function buildElement(array &$build = [], $entity, $langcode) {
    $settings    = &$build['settings'];
    $delta       = $settings['delta'];
    $item_id     = $settings['item_id'];
    $view_mode   = $settings['view_mode'] ?: 'full';
    $image       = [];

    // Built early before stage to allow custom highres video thumbnail.
    $this->buildMedia($settings, $entity, $langcode);

    // Build the main stage.
    $item = $this->buildStage($settings, $entity, $langcode);

    // Build the element settings.
    $element['settings'] = $settings;

    if (!empty($item)) {
      $element['item'] = $item;
      $image = $this->formatter->getImage($element);
    }

    // Optional image with responsive image, lazyLoad, and lightbox supports.
    $element[$item_id] = $image;

    // Captions if so configured.
    $this->getCaption($element, $entity, $langcode);

    // Layouts can be builtin, or field, if so configured.
    if ($layout = $settings['layout']) {
      if (strpos($layout, 'field_') !== FALSE) {
        $settings['layout'] = $this->getFieldString($entity, $layout, $langcode);
      }
      $element['settings']['layout'] = $settings['layout'];
    }

    // Classes, if so configured.
    $element['settings']['class'] = $this->getFieldString($entity, $settings['class'], $langcode);

    // Build the main item.
    $build['items'][$delta] = $element;

    // Build the thumbnail item.
    if (!empty($settings['nav'])) {
      // Thumbnail usages: asNavFor pagers, dot, arrows, photobox thumbnails.
      $element[$item_id]  = empty($settings['thumbnail_style']) ? [] : $this->formatter->getThumbnail($element['settings']);
      $element['caption'] = $this->getFieldRenderable($entity, $settings['thumbnail_caption'], $view_mode);

      $build['thumb']['items'][$delta] = $element;
    }
  }

  /**
   * Builds slide captions with possible multi-value fields.
   */
  public function getCaption(array &$element = [], $entity, $langcode) {
    $settings  = $element['settings'];
    $view_mode = $settings['view_mode'];

    // Title can be plain text, or link field.
    $field_title = $settings['title'];
    $has_title = !empty($field_title) && isset($entity->{$field_title});
    if ($has_title && $title = $entity->getTranslation($langcode)->get($field_title)->getValue()) {
      if (!empty($title[0]['value']) && !isset($title[0]['uri'])) {
        // Prevents HTML-filter-enabled text from having bad markups (h2 > p),
        // except for a few reasonable tags acceptable within H2 tag.
        $element['caption']['title']['#markup'] = strip_tags($title[0]['value'], '<a><strong><em><span><small>');
      }
      elseif (isset($title[0]['uri']) && !empty($title[0]['title'])) {
        $element['caption']['title'] = $this->getFieldRenderable($entity, $field_title, $view_mode)[0];
      }
    }

    // Other caption fields, if so configured.
    if (!empty($settings['caption'])) {
      $caption_items = [];
      foreach ($settings['caption'] as $i => $field_caption) {
        if (!isset($entity->{$field_caption})) {
          continue;
        }
        $caption_items[$i] = $this->getFieldRenderable($entity, $field_caption, $view_mode);
      }
      if ($caption_items) {
        $element['caption']['data'] = $caption_items;
      }
    }

    // Link, if so configured.
    $field_link = isset($settings['link']) ? $settings['link'] : '';
    if ($field_link && isset($entity->{$field_link})) {
      $links = $this->getFieldRenderable($entity, $field_link, $view_mode);

      // Only simplify markups for known formatters registered by link.module.
      if ($links && isset($links['#formatter']) && in_array($links['#formatter'], ['link'])) {
        $links = [];
        foreach ($entity->{$field_link} as $i => $link) {
          $links[$i] = $link->view($view_mode);
        }
      }
      $element['caption']['link'] = $links;
    }

    if (!empty($settings['overlay'])) {
      $element['caption']['overlay'] = $this->getOverlay($settings, $entity, $langcode);
    }
  }

  /**
   * Builds overlay placed within the caption.
   */
  public function getOverlay($settings = [], $entity, $langcode) {
    return $entity->get($settings['overlay'])->view($settings['view_mode']);
  }

  /**
   * Collects media definitions.
   */
  public function buildMedia(array &$settings = [], $entity, $langcode) {
    // Paragraphs return $type as a string bundle, Media entity object.
    $type   = $entity->getType();
    $bundle = $entity->bundle();

    // @todo get 'type' independent from bundle names: image, video, audio.
    $settings['type']           = in_array($bundle, ['image', 'video', 'audio']) ? $bundle : 'image';
    $settings['bundle']         = $bundle;
    $settings['target_bundles'] = $this->getFieldSetting('handler_settings')['target_bundles'];
    $settings['plugin_id']      = is_string($type) ? $this->getPluginId() : $type->getPluginId();
  }

  /**
   * Build the main background/stage, image or video.
   */
  public function buildStage(array &$settings = [], $entity, $langcode) {
    $fields = $this->getPluginId() == 'slick_media' ? $entity->getFields() : [];
    $item   = NULL;
    $stage  = '';

    // Main image can be separate image item from video thumbnail for highres.
    // Fallback to default thumbnail if any, which has no file API.
    // If Media entity via slick_media has defined source_field.
    if (isset($fields['thumbnail']) && !empty($settings['source_field'])) {
      $stage = $settings['source_field'];
      $item = $fields['thumbnail']->get(0);
      $settings['file_tags'] = ['file:' . $item->target_id];
    }

    $stage = empty($settings['image']) ? $stage : $settings['image'];

    // Fetches the highres image if provided and available.
    // With a mix of image and video, image is not always there.
    if ($stage && isset($entity->{$stage})) {
      /** @var \Drupal\file\Plugin\Field\FieldType\FileFieldItemList $file */
      $file = $entity->get($stage);
      $value = $file->getValue();

      // Do not proceed if it is a Media entity video.
      if (isset($value[0]) && $value[0]) {
        // If image, even if multi-value, we can only have one stage per slide.
        if (isset($value[0]['target_id']) && !empty($value[0]['target_id'])) {
          if (method_exists($file, 'referencedEntities') && isset($file->referencedEntities()[0])) {
            /** @var \Drupal\image\Plugin\Field\FieldType\ImageItem $item */
            $item = $file->get(0);

            // Collects cache tags to be added for each item in the field.
            $settings['file_tags'] = $file->referencedEntities()[0]->getCacheTags();
            $settings['uri'] = $file->referencedEntities()[0]->getFileUri();
          }
        }
        // If a VEF with a text, or link field.
        elseif (isset($value[0]['value']) || isset($value[0]['uri'])) {
          $external_url = $this->getFieldString($entity, $stage, $langcode);
          $provider_manager = $this->providerManager;

          /** @var \Drupal\video_embed_field\ProviderManagerInterface $provider */
          if ($external_url && $provider_manager->loadProviderFromInput($external_url)) {
            $this->buildVideo($settings, $external_url, $provider_manager);
            $item = $value;
          }
        }
      }
    }

    return $item;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element    = [];
    $definition = $this->getScopedFormElements();

    $definition['_views'] = isset($form['field_api_classes']);

    $this->admin()->buildSettingsForm($element, $definition);

    $layout_description = $element['layout']['#description'];
    $element['layout']['#description'] = $this->t('Create a dedicated List (text - max number 1) field related to the caption placement to have unique layout per slide with the following supported keys: top, right, bottom, left, center, center-top, etc. Be sure its formatter is Key.') . ' ' . $layout_description;

    $element['media_switch']['#options']['media'] = $this->t('Image to iframe');
    $element['media_switch']['#description'] .= ' ' . $this->t('Be sure the enabled fields here are not hidden/disabled at its view mode.');

    $element['caption']['#description'] = $this->t('Check fields to be treated as captions, even if not caption texts.');

    if (isset($element['image'])) {
      $element['image']['#description'] .= ' ' . $this->t('For video, this allows separate highres image, be sure the same field used for Image to have a mix of videos and images. Leave empty to fallback to the video provider thumbnails. The renderer is managed by <strong>@namespace</strong> formatter. <strong>Supported fields</strong>: Image, Video Embed Field.', ['@namespace' => $this->getPluginId()]);
    }

    if (isset($element['overlay'])) {
      $element['overlay']['#description'] .= ' ' . $this->t('The renderer is managed by the child formatter. <strong>Supported fields</strong>: Image, Video Embed Field, Media Entity.');
    }

    return $element;
  }

  /**
   * Defines the scope for the form elements.
   */
  public function getScopedFormElements() {
    $admin       = $this->admin();
    $field       = $this->fieldDefinition;
    $entity_type = $field->getTargetEntityTypeId();
    $target_type = $this->getFieldSetting('target_type');
    $views_ui    = $this->getFieldSetting('handler') == 'default';
    $bundles     = $views_ui ? [] : $this->getFieldSetting('handler_settings')['target_bundles'];
    $strings     = $admin->getFieldOptions($bundles, ['text', 'string', 'list_string'], $target_type);
    $texts       = $admin->getFieldOptions($bundles, ['text', 'text_long', 'string', 'string_long', 'link'], $target_type);

    return [
      'background'        => TRUE,
      'box_captions'      => TRUE,
      'breakpoints'       => BlazyDefault::getConstantBreakpoints(),
      'captions'          => $admin->getFieldOptions($bundles, [], $target_type),
      'classes'           => $strings,
      'current_view_mode' => $this->viewMode,
      'entity_type'       => $entity_type,
      'fieldable_form'    => TRUE,
      'field_name'        => $field->getName(),
      'images'            => $admin->getFieldOptions($bundles, ['image'], $target_type),
      'image_style_form'  => TRUE,
      'layouts'           => $strings,
      'links'             => $admin->getFieldOptions($bundles, ['text', 'string', 'link'], $target_type),
      'media_switch_form' => TRUE,
      'multimedia'        => TRUE,
      'settings'          => $this->getSettings(),
      'target_bundles'    => $bundles,
      'target_type'       => $target_type,
      'thumb_captions'    => $texts,
      'thumb_positions'   => TRUE,
      'nav'               => TRUE,
      'titles'            => $texts,
      'vanilla'           => TRUE,
    ];
  }

}
