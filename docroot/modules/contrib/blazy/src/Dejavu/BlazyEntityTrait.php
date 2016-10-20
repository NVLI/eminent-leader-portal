<?php

namespace Drupal\blazy\Dejavu;

/**
 * A Trait common for supported entities.
 */
trait BlazyEntityTrait {

  /**
   * Returns the string value of the fields: link, or text.
   */
  public function getFieldString($entity, $field_name = '', $langcode) {
    $value = '';
    if ($field_name && isset($entity->{$field_name})) {
      $values = $entity->getTranslation($langcode)->get($field_name)->getValue();
      // If any text field.
      if (!empty($values[0]['value'])) {
        $value = strip_tags($values[0]['value']);
      }
      // If a VEF URL is using a link field.
      elseif (isset($values[0]['uri']) && !empty($values[0]['title'])) {
        $value = strip_tags($values[0]['uri']);
      }
    }
    return trim($value);
  }

  /**
   * Returns the formatted renderable array of the field.
   */
  public function getFieldRenderable($entity, $field_name = '', $view_mode = 'full') {
    $view = [];
    $has_field = $field_name && isset($entity->{$field_name});
    if ($has_field && !empty($entity->{$field_name}->view($view_mode)[0])) {
      $view = $entity->get($field_name)->view($view_mode);

      // Prevents quickedit to operate here as otherwise JS error.
      // @see 2314185, 2284917, 2160321.
      // @see quickedit_preprocess_field().
      $view['#view_mode'] = '_custom';
    }
    return $view;
  }

}
