<?php

namespace Drupal\custom_solr_search\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'FacetRemoveBlock' block.
 *
 * @Block(
 *  id = "facet_remove_block",
 *  admin_label = @Translation("Facet remove block"),
 * )
 */
class FacetRemoveBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $build['#cache']['max-age'] = 0;
    $facet_filters = custom_solr_search_get_url_components('facet_query');
    $facet_field_settings = custom_solr_search_get_facet_field_settings();
    $facets_applied = [];
    if (empty($facet_filters)) {
      return $build;
    }
    foreach ($facet_filters  as $filter) {
      list($category, $value) = explode(':', $filter, 2);
      $key = array_search($category, $facet_field_settings);
      if (!$key) {
        $key = array_search($category . '_facet', $facet_field_settings);
      }
      $url_param = '_facet_' . str_replace('_facet', '', $facet_field_settings[$key]) . '=' . $value;
      $url = $this->removeFacetFilter($url_param);
      $facets_applied[$filter] = array(
        'key' => $key,
        'value' => trim(urldecode($value), '"'),
        'url' => $url,
        );
    }
    $build['facet_search'] = array(
      '#theme' => 'custom_solr_search_facet_applied',
      '#facets' => isset($facets_applied) ? (array) $facets_applied : [],
    );

    return $build;
  }

  /**
   * Method to remove facet filter.
   *
   * @param string $param
   *   Facet filter param to be removed from url.
   *
   * @return mixed
   *   Resultant url.
   */
  protected function removeFacetFilter($param) {
    $replaced = 0;
    $param = str_replace('"', '', $param);
    $url = str_replace($param . '&', '', $_SERVER['REQUEST_URI'], $replaced);
    if ($replaced == 0) {
      $url = str_replace('&' . $param, '', $_SERVER['REQUEST_URI'], $replaced);
    }
    if ($replaced == 0) {
      $url = str_replace('?' . $param, '', $_SERVER['REQUEST_URI'], $replaced);
    }
    $url = custom_solr_search_remove_url_pager($url);
    return $url;
  }

}
