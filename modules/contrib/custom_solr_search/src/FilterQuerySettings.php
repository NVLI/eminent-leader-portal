<?php

/**
 * @file
 * Contains \Drupal\custom_solr_search\FilterQuerySettings.
 */

namespace Drupal\custom_solr_search;

use Drupal\Core\Database\Database;
/**
 * Class FilterQuerySettings.
 *
 * @package Drupal\custom_solr_search
 */
class FilterQuerySettings {
  /**
   * Constructor.
   */
  public function __construct() {

  }

  public function getFilterQuerySetings(){
    $connection = Database::getConnection();
    $query = $connection->select('config', 'c')
      ->fields('c', array('data'))
      ->condition('c.name', '%custom_solr_search.custom_solr_search_filter_query.%', 'LIKE');
    $results = $query->execute();
    $results = $results->fetchAll(\PDO::FETCH_OBJ);
    foreach ($results as $result) {
      $id = unserialize($result->data)['id'];
      $settings[$id] = unserialize($result->data);
    }
    return $settings;
  }

  /**
   * by passing the setting id, you will be getting the filter query string.
   * @param $filter_setting_id
   *   Machine name of the settings.
   * @return
   */
  public function getFilterQueryString($filter_setting_id){
    $connection = Database::getConnection();
    $query = $connection->select('config', 'c')
      ->fields('c', array('data'))
      ->condition('c.name', '%custom_solr_search.custom_solr_search_filter_query.%', 'LIKE');
    $results = $query->execute();
    $results = $results->fetchAll(\PDO::FETCH_OBJ);
    foreach ($results as $result) {
      $id = unserialize($result->data)['id'];
      $settings[$id] = unserialize($result->data);
    }
    return $settings[$filter_setting_id];
  }

  /**
   * @return mixed
   *   return an array that have all the setting ids.
   */
  public function getFilterQuerySetingids(){
    $connection = Database::getConnection();
    $query = $connection->select('config', 'c')
      ->fields('c', array('data'))
      ->condition('c.name', '%custom_solr_search.custom_solr_search_filter_query.%', 'LIKE');
    $results = $query->execute();
    $results = $results->fetchAll(\PDO::FETCH_OBJ);
    foreach ($results as $result) {
      $id = unserialize($result->data)['id'];
      $settings[$id] = $id;
    }
    return $settings;
  }
}
