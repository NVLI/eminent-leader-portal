<?php

namespace Drupal\blazy;

/**
 * Provides common field formatter-related methods: Blazy, Slick.
 */
class BlazyFormatterManager extends BlazyManager {

  /**
   * {@inheritdoc}
   */
  public function buildSettings(array &$build = [], $items) {
    $settings = &$build['settings'];

    // Sniffs for Views to allow block__no_wrapper, views_no_wrapper, etc.
    if (function_exists('views_get_current_view') && $view = views_get_current_view()) {
      $settings['view_name'] = $view->storage->id();
      $settings['current_view_mode'] = $view->current_display;
    }

    $count          = $items->count();
    $field          = $items->getFieldDefinition();
    $entity         = $items->getEntity();
    $entity_type_id = $entity->getEntityTypeId();
    $entity_id      = $entity->id();
    $bundle         = $entity->bundle();
    $field_name     = $field->getName();
    $field_type     = $field->getType();
    $field_clean    = str_replace("field_", '', $field_name);
    $target_type    = $field->getFieldStorageDefinition()->getSetting('target_type');
    $view_mode      = empty($settings['current_view_mode']) ? '_custom' : $settings['current_view_mode'];
    $namespace      = empty($settings['namespace']) ? 'blazy' : $settings['namespace'];
    $id             = isset($settings['id']) ? $settings['id'] : '';
    $id             = self::getHtmlId("{$namespace}-{$entity_type_id}-{$entity_id}-{$field_clean}-{$view_mode}", $id);
    $switch         = empty($settings['media_switch']) ? '' : empty($settings['media_switch']);
    $internal_path  = $absolute_path = NULL;

    // Deals with UndefinedLinkTemplateException such as paragraphs type.
    // @see #2596385, or fetch the host entity.
    if (!$entity->isNew() && method_exists($entity, 'hasLinkTemplate')) {
      if ($entity->hasLinkTemplate('canonical')) {
        $url = $entity->toUrl();
        $internal_path = $url->getInternalPath();
        $absolute_path = $url->setAbsolute()->toString();
      }
    }

    $settings += [
      'absolute_path'  => $absolute_path,
      'bundle'         => $bundle,
      'count'          => $count,
      'entity_id'      => $entity_id,
      'entity_type_id' => $entity_type_id,
      'field_type'     => $field_type,
      'field_name'     => $field_name,
      'internal_path'  => $internal_path,
      'lightbox'       => $switch && strpos($switch, 'box') !== FALSE,
      'target_type'    => $target_type,
      'cache_metadata' => ['keys' => [$id, $count]],
    ];

    $this->cleanUpBreakpoints($settings);

    $settings['id']         = $id;
    $settings['caption']    = empty($settings['caption']) ? [] : array_filter($settings['caption']);
    $settings['resimage']   = function_exists('responsive_image_get_image_dimensions');
    $settings['background'] = empty($settings['responsive_image_style']) && !empty($settings['background']);

    // @todo simplify these doors.
    $resimage = $this->configLoad('responsive_image') && !empty($settings['responsive_image_style']);
    $blazy = isset($settings['theme_hook_image']) && $settings['theme_hook_image'] == 'blazy';
    $settings['blazy'] = $blazy || !empty($settings['blazy']) || !empty($settings['breakpoints']) || $resimage;

    if (!isset($settings['blazy_data'])) {
      $settings['blazy_data'] = $field_type == 'image' ? $this->buildDataBlazy($settings, $items[0]) : [];
    }

    // Aspect ratio isn't working with Responsive image, yet.
    // However allows custom work to get going with an enforced.
    $ratio = FALSE;
    if (!empty($settings['ratio'])) {
      $ratio = empty($settings['responsive_image_style']);
      if ($settings['ratio'] == 'enforced' || $settings['background']) {
        $ratio = TRUE;
      }
    }
    $settings['ratio'] = $ratio ? $settings['ratio'] : FALSE;

    unset($entity, $field);
  }

}
