<?php

namespace Drupal\custom_solr_search;

use Solarium\Client;

/**
 * Class Server.
 *
 * @package Drupal\custom_solr_search
 */
class Server {

  /**
   * Constructor.
   */
  public function __construct() {

  }

  /**
   * Method to initiate and return solr client.
   *
   * @param string $server
   *   Server machine name.
   *
   * @return \Solarium\Client
   *   Solarium client object.
   */
  public function getSolrClient($server) {
    // Get solr server backend config.
    $backend_config = \Drupal::config('search_api.server.' . $server)->get('backend_config');
    // Initiate solr client.
    $solr = new Client();
    // Create and set solr client endpoint.
    $solr->createEndpoint($backend_config + ['key' => 'core'], TRUE);
    dpm($solr);
    return $solr;
  }


}
