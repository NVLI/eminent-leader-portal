<?php

namespace Drupal\blazy\Plugin\views\field;

use Drupal\views\ResultRow;

/**
 * Defines a custom field that renders a preview of a file.
 *
 * @ViewsField("blazy_file")
 */
class BlazyViewsFieldFile extends BlazyViewsFieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    /** @var \Drupal\file\Entity\File $file */
    $entity   = $values->_entity;
    $build    = [];
    $settings = $this->options;

    $settings['blazy'] = TRUE;
    $settings['lazy']  = 'blazy';
    $settings['ratio'] = 'fluid';
    list($type,)       = explode('/', $entity->getMimeType(), 2);
    $uri               = $entity->getFileUri();

    // Check if this file is an image.
    $image_factory = \Drupal::service('image.factory');
    if ($type == 'image' && ($image = $image_factory->get($uri)) && $image->isValid()) {
      // Faking an ImageItem object.
      $item             = new \stdClass();
      $item->target_id  = $entity->id();
      $item->width      = $image->getWidth();
      $item->height     = $image->getHeight();
      $item->alt        = '';
      $item->title      = $entity->getFilename();
      $image_data       = (array) $item;
      $item->entity     = $entity;
      $settings['type'] = 'image';

      // Add settings.
      $settings = array_merge($settings, $image_data);

      // Build Blazy.
      $data = ['item' => $item, 'settings' => $settings];
      // $build = $this->blazyManager->getImage($data);
      $build = $this->getImage($data);
    }
    else {
      $build = $this->blazyManager->getEntityView($entity, $settings);

      if (!$build) {
        $build = ['#markup' => $entity->getFilename()];
      }
    }

    return $build;
  }

  /**
   * Defines the scope for the form elements.
   */
  public function getScopedFormElements() {
    return ['target_type' => 'file'] + parent::getScopedFormElements();
  }

}
