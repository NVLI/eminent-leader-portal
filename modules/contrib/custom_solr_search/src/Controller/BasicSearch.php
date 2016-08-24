<?php

/**
 * @file
 * Contains \Drupal\custom_solr_search\Controller\BasicSearch.
 */

namespace Drupal\custom_solr_search\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class BasicSearch.
 *
 * @package Drupal\custom_solr_search\Controller
 */
class BasicSearch extends ControllerBase {

  /**
   * Search.
   *
   * @return string
   *   Return Hello string.
   */
  public function search($server = NULL, $keyword = NULL) {
    $limit = isset($_GET['limit']) ? $_GET['limit'] : 10;
    $offset = isset($_GET['page']) ? ($_GET['page'] * $limit) : 0;

    // Search form.
    $render['form'] = $this->formBuilder()
      ->getForm('Drupal\custom_solr_search\Form\SearchForm', $server, $keyword);
    // Display result if keyword is defined.
    if (!empty($keyword)) {
      $url_components = custom_solr_search_get_url_components();
      $options = custom_solr_search_get_facet_filter_query_string($url_components['facet_query']);
      // Get search results from solr core.
      if ($server == 'all') {
        $results = \Drupal::service('custom_solr_search.search_all')
          ->seachAll($keyword, $options);
      }
      else {
        $results = \Drupal::service('custom_solr_search.search')
          ->basicSearch($keyword, $offset, $limit, $server, $options);
      }
      $total_docs = $results['total_docs'];
      $render['result']['#attached']['library'][] = 'core/drupal.dialog.ajax';
      // Format result to display as table.
      foreach ($results['docs'] as $result) {
        if (isset($result->title)) {
          $title = $result->title;
        }
        else {
          $title = $result->label;
        }
        $render['result'][] = array(
          '#theme' => 'custom_solr_search_result',
          '#url' => isset($result->url[0]) ? $result->url[0] : '',
          '#title' => isset($title) ? $title : '',
          '#author' => isset($result->author) ? implode(', ', $result->author) : '',
          '#publishDate' => isset($result->publishDate) ? implode(', ', $result->publishDate) : '',
          '#publisher' => isset($result->publisher) ? implode(', ', $result->publisher) : '',
          '#topic' => isset($result->topic) ? implode(', ', $result->topic) : '',
          '#docid' => isset($result->id) ? $result->id : '',
        );
      }
    }
    // Now that we have the total number of results, initialize the pager.
    pager_default_initialize($total_docs, $limit);

    // Finally, add the pager to the render array, and return.
    $render[] = ['#type' => 'pager'];
    return $render;

  }
}
