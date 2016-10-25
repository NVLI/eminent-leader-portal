<?php

namespace Drupal\blazy\Dejavu;

use Drupal\Core\Url;
use Drupal\blazy\Form\BlazyAdminFormatterBase;

/**
 * Provides re-usable admin functions, or form elements.
 */
class BlazyAdminExtended extends BlazyAdminFormatterBase {

  /**
   * Returns shared form elements across field formatter and Views.
   */
  public function openingForm(array &$form, $definition = []) {
    if (!isset($definition['namespace'])) {
      return;
    }

    $namespace = $definition['namespace'];

    if (isset($definition['vanilla'])) {
      $form['vanilla'] = [
        '#type'        => 'checkbox',
        '#title'       => t('Vanilla @namespace', ['@namespace' => $namespace]),
        '#description' => t('<strong>Check</strong>:<ul><li>To render individual item as is as without extra logic.</li><li>To disable 99% @module features, and most of the mentioned options here, such as layouts, et al.</li><li>When the @module features can not satisfy the need.</li><li>Things may be broken! You are on your own.</li></ul><strong>Uncheck</strong>:<ul><li>To get consistent markups and its advanced features -- relevant for the provided options as @module needs to know what to style/work with.</li></ul>', ['@module' => $namespace]),
        '#weight'      => -109,
        '#enforced'    => TRUE,
        '#access'      => isset($definition['vanilla']),
       '#wrapper_attributes' => ['class' => ['form-item--full', 'form-item--tooltip-bottom']],
      ];
    }

    if (isset($definition['optionsets'])) {
      $form['optionset'] = [
        '#type'        => 'select',
        '#title'       => t('Optionset'),
        '#options'     => isset($definition['optionsets']) ? $definition['optionsets'] : $this->getOptionsetOptions($namespace),
        '#enforced'    => TRUE,
        '#description' => t('Enable the optionset UI module to manage the optionsets.'),
        '#access'      => isset($definition['optionsets']),
        '#weight'      => -108,
      ];

      if ($this->blazyManager()->getModuleHandler()->moduleExists($namespace . '_ui')) {
        $form['optionset']['#description'] = t('Manage optionsets at <a href=":url" target="_blank">the optionset admin page</a>.', [':url' => Url::fromRoute('entity.' . $namespace . '.collection')->toString()]);
      }
    }

    parent::openingForm($form, $definition);
  }

  /**
   * Returns re-usable fieldable formatter form elements.
   */
  public function fieldableForm(array &$form, $definition = []) {
    if (isset($definition['images'])) {
      $form['image'] = [
        '#type'        => 'select',
        '#title'       => t('Main stage'),
        '#options'     => isset($definition['images']) ? $definition['images'] : [],
        '#description' => t('Main background/stage image field.'),
        '#access'      => isset($definition['images']),
        '#prefix'      => '<h3 class="form__title">' . t('Fields') . '</h3>',
      ];
    }

    if (isset($definition['thumbnails'])) {
      $form['thumbnail'] = array(
        '#type'        => 'select',
        '#title'       => t('Thumbnail image'),
        '#options'     => isset($definition['thumbnails']) ? $definition['thumbnails'] : [],
        '#description' => t("Leave empty to not use thumbnail pager."),
        '#access'      => isset($definition['thumbnails']),
      );
    }

    if (isset($definition['overlays'])) {
      $form['overlay'] = array(
        '#type'        => 'select',
        '#title'       => t('Overlay media'),
        '#options'     => isset($definition['overlays']) ? $definition['overlays'] : [],
        '#description' => t('Overlay is displayed over the main main.'),
        '#access'      => isset($definition['overlays']),
      );
    }

    if (isset($definition['titles'])) {
      $form['title'] = [
        '#type'        => 'select',
        '#title'       => t('Title'),
        '#options'     => isset($definition['titles']) ? $definition['titles'] : [],
        '#description' => t('If provided, it will bre wrapped with H2.'),
        '#access'      => isset($definition['titles']),
      ];
    }

    if (isset($definition['links'])) {
      $form['link'] = [
        '#type'        => 'select',
        '#title'       => t('Link'),
        '#options'     => isset($definition['links']) ? $definition['links'] : [],
        '#description' => t('Link to content: Read more, View Case Study, etc.'),
        '#access'      => isset($definition['links']),
      ];
    }

    if (isset($definition['classes'])) {
      $form['class'] = [
        '#type'        => 'select',
        '#title'       => t('Item class'),
        '#options'     => isset($definition['classes']) ? $definition['classes'] : [],
        '#description' => t('If provided, individual item will have this class, e.g.: to have different background with transparent images. Be sure its formatter is Key or Label. Accepted field types: list text, string (e.g.: node title), term/entity reference label.'),
        '#access'      => isset($definition['classes']),
        '#weight'      => 6,
      ];
    }

    if (isset($definition['id'])) {
      $form['id'] = [
        '#type'         => 'textfield',
        '#title'        => t('Slick ID'),
        '#size'         => 40,
        '#maxlength'    => 255,
        '#field_prefix' => '#',
        '#enforced'     => TRUE,
        '#description'  => t("Manually define the container ID. <em>This ID is used for the cache identifier, so be sure it is unique</em>. Leave empty to have a guaranteed unique ID managed by the module."),
        '#access'       => isset($definition['id']),
        '#weight'       => 94,
      ];
    }

    if (isset($form['caption'])) {
      $form['caption']['#description'] = t('Enable any of the following fields as captions. These fields are treated and wrapped as captions.');
    }

    if (!isset($definition['id'])) {
      if (isset($form['caption'])) {
        $form['caption']['#description'] .= ' ' . t('Be sure to make them visible at their relevant Manage display.');
      }
    }
    else {
      if (isset($form['overlay'])) {
        $form['overlay']['#description'] .= ' ' . t('Be sure to CHECK "Use field template" under its formatter if using Slick field formatter.');
      }
    }
  }

  /**
   * Returns re-usable grid elements across field formatter and Views.
   */
  public function gridForm(array &$form, $definition = []) {
    $range = range(1, 12);
    $grid_options = array_combine($range, $range);

    $header = t('Group individual items as block grid?<small>Only works if the total items &gt; <strong>Visible items</strong>.</small>');
    $form['grid_header'] = [
      '#type'   => 'item',
      '#markup' => '<h3 class="form__title">' . $header . '</h3>',
    ];

    $form['grid'] = [
      '#type'        => 'select',
      '#title'       => t('Grid large'),
      '#options'     => $grid_options,
      '#description' => t('The amount of block grid columns for large monitors 64.063em - 90em. <br /><strong>Requires</strong>:<ol><li>Visible items,</li><li>Skin Grid for starter,</li><li>A reasonable amount of contents.</li></ol>Leave empty to DIY, or to not build grids.'),
      '#enforced'    => TRUE,
    ];

    $form['grid_medium'] = [
      '#type'        => 'select',
      '#title'       => t('Grid medium'),
      '#options'     => $grid_options,
      '#description' => t('The amount of block grid columns for medium devices 40.063em - 64em.'),
    ];

    $form['grid_small'] = [
      '#type'        => 'select',
      '#title'       => t('Grid small'),
      '#options'     => $grid_options,
      '#description' => t('The amount of block grid columns for small devices 0 - 40em.'),
    ];

    $form['visible_items'] = [
      '#type'        => 'select',
      '#title'       => t('Visible items'),
      '#options'     => array_combine(range(1, 32), range(1, 32)),
      '#description' => t('How many items per display at a time. Required if Grid provided. Grid will not work if Views rows count &lt; <strong>Visible items</strong>.'),
    ];

    $form['preserve_keys'] = [
      '#type'        => 'checkbox',
      '#title'       => t('Preserve keys'),
      '#description' => t('If checked, keys will be preserved. Default is FALSE which will reindex the grid chunk numerically.'),
    ];

    $grids = [
      'grid_header',
      'grid_medium',
      'grid_small',
      'visible_items',
      'preserve_keys',
    ];

    foreach ($grids as $key) {
      $form[$key]['#enforced'] = TRUE;
      $form[$key]['#states'] = [
        'visible' => [
          'select[name$="[grid]"]' => ['!value' => ''],
        ],
      ];
    }
  }

  /**
   * Returns shared ending form elements across field formatter and Views.
   */
  public function closingForm(array &$form, $definition = []) {
    if (isset($definition['caches']) && $definition['caches']) {
      $form['cache'] = [
        '#type'        => 'select',
        '#title'       => t('Cache'),
        '#options'     => $this->getCacheOptions(),
        '#weight'      => 98,
        '#enforced'    => TRUE,
        '#description' => t('Ditch all the logic to cached bare HTML. <ol><li><strong>Permanent</strong>: cached contents will persist (be displayed) till the next cron runs.</li><li><strong>Any number</strong>: expired by the selected expiration time, and fresh contents are fetched till the next cache rebuilt.</li></ol>A working cron job is required to clear stale cache. At any rate, cached contents will be refreshed regardless of the expiration time after the cron hits. <br />Leave it empty to disable caching.<br /><strong>Warning!</strong> Be sure no useless/ sensitive data such as Edit links as they are rendered as is regardless permissions. No permissions are changed, just ugly. Only enable it when all is done, otherwise cached options will be displayed while changing them.'),
        '#access'      => isset($definition['caches']),
      ];
    }

    parent::closingForm($form, $definition);
  }

}
