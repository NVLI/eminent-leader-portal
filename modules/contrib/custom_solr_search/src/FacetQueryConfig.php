<?php

/**
 * @file
 * Contains \Drupal\custom_solr_search\FacetQueryConfig.
 */

namespace Drupal\custom_solr_search;
use Drupal\Core\Database\Database;


/**
 * Class FacetQueryConfig.
 *
 * @package Drupal\custom_solr_search
 */
class FacetQueryConfig {
  /**
   * Constructor.
   */
  public function __construct() {

  }

  /**
   *
   */
  public function getFacetQuery($facetId = NULL){
    $connection = Database::getConnection();
    $query = $connection->select('config', 'c')
      ->fields('c', array('data'))
      ->condition('c.name', '%custom_solr_search.facet_fields.%', 'LIKE');
    $results = $query->execute();
    $results = $results->fetchAll(\PDO::FETCH_OBJ);
    foreach ($results as $result) {
      $id = unserialize($result->data)['id'];
      $settings[$id] = unserialize($result->data);
    }
    if ($facetId) {
      $facet_config = $settings[$facetId];
      $facet_config['fields'] = $this->parseFacetFields($facet_config['fields']);
    }
    else {
      $facet_config = $settings;
      foreach ($facet_config as &$config) {
        $config['fields'] = $this->parseFacetFields($config['fields']);
      }
    }
    return $facet_config;
  }

  protected function parseFacetFields($fields) {
    $facet_fields = [];
    if ($fields) {
      $fields = explode(',', $fields);

      foreach ($fields as $field_string) {
        $field = explode(':', $field_string, 2);
        $facet_fields[$field[0]] = $field[1];
      }
    }

    return $facet_fields;
  }
}
