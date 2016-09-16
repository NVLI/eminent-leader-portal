<?php

/**
 * @file
 * Contains \Drupal\blazy\Form\BlazyAdminFormatter.
 */

namespace Drupal\blazy\Form;

use Drupal\Core\Url;

/**
 * Provides admin form specific to Blazy admin formatter.
 */
class BlazyAdminFormatter extends BlazyAdminFormatterBase {

  /**
   * Defines re-usable form elements.
   */
  public function buildSettingsForm(array &$form, $definition = []) {
    $settings = $definition['settings'];

    $this->openingForm($form, $definition);
    $this->imageStyleForm($form, $definition);
    $this->mediaSwitchForm($form, $definition);

    if ($this->blazyManager()->getModuleHandler()->moduleExists('blazy_ui')) {
      $form['responsive_image_style']['#description'] .= ' ' . t('<a href=":url" target="_blank">Enable lazyloading Responsive image</a>.', [':url' => Url::fromRoute('blazy.settings')->toString()]);
    }

    $form['responsive_image_style']['#description'] = t('Only expects multi-serving images with srcset attribute. Not compatible with below breakpoints, and aspect ratio, yet. However it can still lazyload by checking <strong>Responsive image</strong> option via Blazy UI. Leave empty to disable.');

    if (isset($definition['breakpoints'])) {
      $this->breakpointsForm($form, $definition);
    }

    $this->closingForm($form, $definition);
  }

}
