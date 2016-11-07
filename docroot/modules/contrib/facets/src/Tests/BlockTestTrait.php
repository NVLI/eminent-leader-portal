<?php

namespace Drupal\facets\Tests;

/**
 * Shared test methods for facet blocks.
 */
trait BlockTestTrait {

  /**
   * The block entities used by this test.
   *
   * @var \Drupal\block\BlockInterface[]
   */
  protected $blocks;

  /**
   * Add a facet trough the UI.
   *
   * @param string $name
   *   The facet name.
   * @param string $id
   *   The facet id.
   * @param string $field
   *   The facet field.
   * @param string $display_id
   *   The display id.
   * @param string $source
   *   Facet source.
   */
  protected function createFacet($name, $id, $field = 'type', $display_id = 'page_1', $source = 'search_api_test_view') {
    $facet_add_page = 'admin/config/search/facets/add-facet';

    $this->drupalGet($facet_add_page);

    $facet_source = "views_page:{$source}__{$display_id}";
    $form_values = [
      'id' => $id,
      'name' => $name,
      'facet_source_id' => $facet_source,
      "facet_source_configs[views_page:{$source}__{$display_id}][field_identifier]" => $field,
    ];
    $this->drupalPostForm(NULL, ['facet_source_id' => $facet_source], $this->t('Configure facet source'));
    $this->drupalPostForm(NULL, $form_values, $this->t('Save'));

    $this->blocks[$id] = $this->createBlock($id);
  }

  /**
   * Creates a facet block by id.
   *
   * @param string $id
   *   The id of the block.
   *
   * @return \Drupal\block\Entity\Block
   *   The block entity.
   */
  protected function createBlock($id) {
    $block = [
      'region' => 'footer',
      'id' => str_replace('_', '-', $id),
    ];
    return $this->drupalPlaceBlock('facet_block:' . $id, $block);
  }

  /**
   * Deletes a facet block by id.
   *
   * @param string $id
   *   The id of the block.
   */
  protected function deleteBlock($id) {
    $this->drupalGet('admin/structure/block/manage/' . $this->blocks[$id]->id(), array('query' => array('destination' => 'admin')));
    $this->clickLink(t('Delete'));
    $this->drupalPostForm(NULL, array(), t('Delete'));
    $this->assertRaw(t('The block %name has been deleted.', array('%name' => $this->blocks[$id]->label())));
  }

}
