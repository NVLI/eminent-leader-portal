<?php

namespace Drupal\custom_solr_search;

use Solarium\QueryType\Select\Query\Query as SelectQuery;
use Drupal\custom_solr_search\FilterQuerySettings;

/**
 * Class Facet.
 *
 * @package Drupal\custom_solr_search
 */
class Facet {

  /**
   * Constructor.
   */
  public function __construct() {

  }

  public function filter($solr_core) {
    $facet_fields = custom_solr_search_get_facet_field_settings();
    // Get solarium client.
    $solr_client = \Drupal::service('custom_solr_search.server')->getSolrClient($solr_core);
    $url_components = custom_solr_search_get_url_components();
    
    // Get the resource type from URl.
    $filterID = \Drupal::request()->get('resource_type');
    if (!empty($filterID)) {
      $filterQuerySettings = \Drupal::service('custom_solr_search.filter_query_settings')->getFilterQueryString($filterID);
      $options[] = $filterQuerySettings['filter'];
      $new_query_options = array_merge($options,$url_components['facet_query']);
    }
    else {
      $new_query_options = $url_components['facet_query'];
    }
 
    $keyword = urldecode(end(explode('/', $url_components['path'])));
    // Initiate Solarium basic select query.
    $query = new SelectQuery();
    // Set search keyword.
    $query->setQuery($keyword);
    // Set facet filter queries.
    custom_solr_search_set_facet_filter($query, $new_query_options);
    
    // Set limit.
    $query->setRows(0);
    // get the facetset component
    $facetSet = $query->getFacetSet();

    // create a facet field instance and set options
    foreach ($facet_fields as $label => $field) {
      $facetSet->createFacetField($label)->setField($field);
    }

    // Create a request for query.
    $request = $solr_client->createRequest($query);
    // Execute request.
    $response = $solr_client->executeRequest($request);
    // Extract result from response.
    $result = $this->extractFacet($response);
    return $result;
  }



  protected function extractFacet($response) {
    // If response status code is 200, return response docs.
    if ($response->getStatusCode() == 200) {
      $facet_field_settings = custom_solr_search_get_facet_field_settings();
      // Get response raw body.
      $raw_body = $response->getBody();
      // Decode json string to array or object.
      $result = json_decode($raw_body);
      // Get facet fields result.
      $facet_fields = $result->facet_counts->facet_fields;
      $facets = [];
      $url = custom_solr_search_remove_url_pager($_SERVER['REQUEST_URI']);
      $append_query_string = (parse_url($url, PHP_URL_QUERY) != NULL) ? '&' : '?';
      foreach ($facet_fields as $field => $facet) {
        for ($i=0; $i< count($facet); $i+=2) {
          if ($facet[$i + 1]) {
            $facets[$field][] = array(
              'value' => $facet[$i],
              'count' => $facet[$i + 1],
              'url' => $url . $append_query_string . '_facet_' . str_replace('_facet', '', $facet_field_settings[$field]) . '=' . $facet[$i],
            );
          }
        }
      }
      // Return.
      return $facets;
    }
    else {
      // Throw exception.
    }

  }

}
