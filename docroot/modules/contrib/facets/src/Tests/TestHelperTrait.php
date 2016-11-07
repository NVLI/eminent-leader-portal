<?php

namespace Drupal\facets\Tests;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Url;

/**
 * Adds helpers for test methods.
 */
trait TestHelperTrait {

  /**
   * {@inheritdoc}
   */
  protected function assertFacetLabel($label, $index = 0, $message = '', $group = 'Other') {
    $label = (string) $label;
    $label = strip_tags($label);
    $matches = [];

    if (preg_match('/(.*)\s\((\d+)\)/', $label, $matches)) {
      $links = $this->xpath('//a//span[normalize-space(text())=:label]/following-sibling::span[normalize-space(text())=:count]', [':label' => $matches[1], ':count' => '(' . $matches[2] . ')']);
    }
    else {
      $links = $this->xpath('//a//span[normalize-space(text())=:label]', [':label' => $label]);
    }
    $message = ($message ? $message : strtr('Link with label %label found.', ['%label' => $label]));
    return $this->assert(isset($links[$index]), $message, $group);
  }

  /**
   * Check if a facet is active by providing a label for it.
   *
   * We'll check by activeness by seeing that there's a span with (-) in the
   * same link as the label.
   *
   * @param string $label
   *   The label of a facet that should be active.
   *
   * @return bool
   *   Returns true when the facet is found and is active.
   */
  protected function checkFacetIsActive($label) {
    $label = (string) $label;
    $label = strip_tags($label);
    $links = $this->xpath('//a/span[normalize-space(text())="(-)"]/following-sibling::span[normalize-space(text())=:label]', array(':label' => $label));
    return $this->assert(isset($links[0]));
  }

  /**
   * Asserts that a facet block does not appear.
   */
  protected function assertNoFacetBlocksAppear() {
    foreach ($this->blocks as $block) {
      $this->assertNoBlockAppears($block);
    }
  }

  /**
   * Asserts that a facet block appears.
   */
  protected function assertFacetBlocksAppear() {
    foreach ($this->blocks as $block) {
      $this->assertBlockAppears($block);
    }
  }

  /**
   * Asserts that a string is found before another string in the source.
   *
   * This uses the simpletest's getRawContent method to search in the source of
   * the page for the position of 2 strings and that the first argument is
   * before the second argument's position.
   *
   * @param string $x
   *   A string.
   * @param string $y
   *   Another string.
   */
  protected function assertStringPosition($x, $y) {
    $this->assertText($x);
    $this->assertText($y);

    $x_position = strpos($this->getTextContent(), $x);
    $y_position = strpos($this->getTextContent(), $y);

    $message = new FormattableMarkup('Assert that %x is before %y in the source', ['%x' => $x, '%y' => $y]);
    $this->assertTrue($x_position < $y_position, $message);
  }

  /**
   * Checks that the url after clicking a facet is as expected.
   *
   * @param \Drupal\Core\Url $url
   *   The expected url we end on.
   */
  protected function checkClickedFacetUrl(Url $url) {
    $this->drupalGet('search-api-test-fulltext');
    $this->assertResponse(200);
    $this->assertFacetLabel('item');
    $this->assertFacetLabel('article');

    $this->clickLink('item');

    $this->assertResponse(200);
    $this->checkFacetIsActive('item');
    $this->assertFacetLabel('article');
    $this->assertUrl($url);
  }

}
