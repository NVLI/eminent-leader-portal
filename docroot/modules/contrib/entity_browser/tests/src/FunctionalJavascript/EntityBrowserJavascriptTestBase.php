<?php

namespace Drupal\Tests\entity_browser\FunctionalJavascript;

use Drupal\Component\Utility\SafeMarkup;
use Drupal\Core\Entity\Sql\SqlEntityStorageInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\file\Entity\File;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\FunctionalJavascriptTests\JavascriptTestBase;
use Drupal\Tests\Component\Utility\SafeMarkupTest;

/**
 * Base class for Entity browser Javascript functional tests.
 *
 * @package Drupal\Tests\entity_browser\FunctionalJavascript
 */
abstract class EntityBrowserJavascriptTestBase extends JavascriptTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'entity_browser_test',
    'ctools',
    'views',
    'block',
    'node',
    'file',
    'image',
    'field_ui',
    'views_ui',
    'system',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->drupalCreateContentType(['type' => 'article', 'name' => 'Article']);

    FieldStorageConfig::create([
      'field_name' => 'field_reference',
      'type' => 'entity_reference',
      'entity_type' => 'node',
      'cardinality' => FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED,
      'settings' => [
        'target_type' => 'file',
      ],
    ])->save();

    FieldConfig::create([
      'field_name' => 'field_reference',
      'entity_type' => 'node',
      'bundle' => 'article',
      'label' => 'Reference',
      'settings' => [],
    ])->save();

    /** @var \Drupal\Core\Entity\Display\EntityFormDisplayInterface $form_display */
    $form_display = $this->container->get('entity_type.manager')
      ->getStorage('entity_form_display')
      ->load('node.article.default');

    $form_display->setComponent('field_reference', [
      'type' => 'entity_browser_entity_reference',
      'settings' => [
        'entity_browser' => 'test_entity_browser_file',
        'field_widget_display' => 'label',
        'open' => TRUE,
      ],
    ])->save();

    $account = $this->drupalCreateUser([
      'access test_entity_browser_file entity browser pages',
      'create article content',
      'access content',
    ]);
    $this->drupalLogin($account);
  }

  /**
   * Return an entity browser if it exists or creates a new one.
   *
   * @param string $browser_name
   *   The entity browser name.
   * @param string $display_id
   *   The display plugin id.
   * @param string $widget_selector_id
   *   The widget selector id.
   * @param string $selection_display_id
   *   The selection display id.
   * @param array $display_configuration
   *   The display plugin configuration.
   * @param array $widget_selector_configuration
   *   The widget selector configuration.
   * @param array $selection_display_configuration
   *   The selection display configuration.
   *
   * @return \Drupal\entity_browser\EntityBrowserInterface
   *   Returns an Entity Browser.
   */
  protected function getEntityBrowser($browser_name, $display_id, $widget_selector_id, $selection_display_id, $display_configuration = [], $widget_selector_configuration = [], $selection_display_configuration = []) {
    /** @var \Drupal\Core\Entity\EntityStorageInterface $storage */
    $storage = $this->container->get('entity_type.manager')
      ->getStorage('entity_browser');

    /** @var \Drupal\entity_browser\EntityBrowserInterface $browser */
    $browser = $storage->load($browser_name) ?: $storage->create(['name' => $browser_name]);

    $browser->setDisplay($display_id);
    if ($display_configuration) {
      $browser->getDisplay()->setConfiguration($display_configuration);
    }

    $browser->setWidgetSelector($widget_selector_id);
    if ($widget_selector_configuration) {
      $browser->getSelectionDisplay()
        ->setConfiguration($widget_selector_configuration);
    }

    $browser->setSelectionDisplay($selection_display_id);
    if ($selection_display_configuration) {
      $browser->getSelectionDisplay()
        ->setConfiguration($selection_display_configuration);
    }
    $browser->save();

    // Clear caches after new browser is saved to remove old cached states.
    drupal_flush_all_caches();

    return $browser;
  }

  /**
   * Creates an image.
   *
   * @param string $name
   *   The name of the image.
   *
   * @return \Drupal\file\FileInterface
   *   Returns an image.
   */
  protected function createFile($name) {
    file_put_contents('public://' . $name . '.jpg', $this->randomMachineName());

    $image = File::create([
      'filename' => $name . '.jpg',
      'uri' => 'public://' . $name . '.jpg',
    ]);
    $image->save();

    return $image;
  }

  /**
   * Waits for jQuery to become ready and animations to complete.
   */
  protected function waitForAjaxToFinish() {
    $this->assertSession()->assertWaitOnAjaxRequest();
  }

  /**
   * Waits and asserts that a given element is visible.
   *
   * @param string $selector
   *   The CSS selector.
   * @param int $timeout
   *   (Optional) Timeout in milliseconds, defaults to 1000.
   * @param string $message
   *   (Optional) Message to pass to assertJsCondition().
   */
  protected function waitUntilVisible($selector, $timeout = 1000, $message = '') {
    $condition = "jQuery('" . $selector . ":visible').length > 0";
    $this->assertJsCondition($condition, $timeout, $message);
  }

  /**
   * Debugger method to save additional HTML output.
   *
   * The base class will only save browser output when accessing page using
   * ::drupalGet and providing a printer class to PHPUnit. This method
   * is intended for developers to help debug browser test failures and capture
   * more verbose output.
   */
  protected function saveHtmlOutput() {
    $out = $this->getSession()->getPage()->getContent();
    // Ensure that any changes to variables in the other thread are picked up.
    $this->refreshVariables();
    if ($this->htmlOutputEnabled) {
      $html_output = '<hr />Ending URL: ' . $this->getSession()->getCurrentUrl();
      $html_output .= '<hr />' . $out;
      $html_output .= $this->getHtmlOutputHeaders();
      $this->htmlOutput($html_output);
    }
  }


}
