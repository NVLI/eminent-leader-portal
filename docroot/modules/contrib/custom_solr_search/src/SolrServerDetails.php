<?php

/**
 * @file
 * Contains \Drupal\custom_solr_search\SolrServerDetails.
 */

namespace Drupal\custom_solr_search;

use Drupal\Core\Database\Database;


/**
 * Class SolrServerDetails.
 *
 * @package Drupal\custom_solr_search
 */
class SolrServerDetails {
  /**
   * Constructor.
   */
  public function __construct() {

  }

  /**
   * Get the saved server config entities.
   */
   public function getServers(){
     $connection = Database::getConnection();
     $query = $connection->select('config', 'c')
       ->fields('c', array('data'))
       ->condition('c.name', '%search_api.server.%', 'LIKE');
     $results = $query->execute();
     $results = $results->fetchAll(\PDO::FETCH_OBJ);
     $cores = [];
     foreach ($results as $result) {
       $server = unserialize($result->data);
       $cores[$server['id']] = $server['name'];
     }
     return $cores;
   }
}
