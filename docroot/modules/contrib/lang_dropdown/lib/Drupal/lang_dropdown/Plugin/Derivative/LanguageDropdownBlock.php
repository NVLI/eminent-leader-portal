<?php

/**
 * @file
 * Contains \Drupal\lang_dropdown\Plugin\Derivative\LanguageDropdownBlock.
 */

namespace Drupal\lang_dropdown\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DerivativeBase;

/**
 * Provides language dropdown switcher block plugin definitions for all languages.
 */
class LanguageDropdownBlock extends DerivativeBase {
  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions(array $base_plugin_definition) {
    include_once DRUPAL_ROOT . '/core/includes/language.inc';
    $info = language_types_info();
    $configurable_types = language_types_get_configurable();
    foreach ($configurable_types as $type) {
      $this->derivatives[$type] = $base_plugin_definition;
      $this->derivatives[$type]['admin_label'] = t('Language dropdown switcher (!type)', array('!type' => $info[$type]['name']));
      $this->derivatives[$type]['cache'] = DRUPAL_NO_CACHE;
    }
    // If there is just one configurable type then change the title of the
    // block.
    if (count($configurable_types) == 1) {
      $this->derivatives[reset($configurable_types)]['admin_label'] = t('Language dropdown switcher');
    }
    return parent::getDerivativeDefinitions($base_plugin_definition);
  }
}
