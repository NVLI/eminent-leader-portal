<?php

/**
 * @file
 * Contains \Drupal\custom_solr_search\Tests\BasicSearch.
 */

namespace Drupal\custom_solr_search\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Provides automated tests for the custom_solr_search module.
 */
class BasicSearchTest extends WebTestBase {
  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => "custom_solr_search BasicSearch's controller functionality",
      'description' => 'Test Unit for module custom_solr_search and controller BasicSearch.',
      'group' => 'Other',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
  }

  /**
   * Tests custom_solr_search functionality.
   */
  public function testBasicSearch() {
    // Check that the basic functions of module custom_solr_search.
    $this->assertEquals(TRUE, TRUE, 'Test Unit Generated via App Console.');
  }

}
