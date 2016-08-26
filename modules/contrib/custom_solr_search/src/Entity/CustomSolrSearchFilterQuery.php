<?php

/**
 * @file
 * Contains \Drupal\custom_solr_search\Entity\CustomSolrSearchFilterQuery.
 */

namespace Drupal\custom_solr_search\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\custom_solr_search\CustomSolrSearchFilterQueryInterface;

/**
 * Defines the Custom solr search filter query entity.
 *
 * @ConfigEntityType(
 *   id = "custom_solr_search_filter_query",
 *   label = @Translation("Custom solr search filter query"),
 *   handlers = {
 *     "list_builder" = "Drupal\custom_solr_search\CustomSolrSearchFilterQueryListBuilder",
 *     "form" = {
 *       "add" = "Drupal\custom_solr_search\Form\CustomSolrSearchFilterQueryForm",
 *       "edit" = "Drupal\custom_solr_search\Form\CustomSolrSearchFilterQueryForm",
 *       "delete" = "Drupal\custom_solr_search\Form\CustomSolrSearchFilterQueryDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\custom_solr_search\CustomSolrSearchFilterQueryHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "custom_solr_search_filter_query",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "server" = "server",
 *     "filter" = "filter",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/config/custom_solr_search_filter_query/{custom_solr_search_filter_query}",
 *     "add-form" = "/admin/config/custom_solr_search_filter_query/add",
 *     "edit-form" = "/admin/config/custom_solr_search_filter_query/{custom_solr_search_filter_query}/edit",
 *     "delete-form" = "/admin/config/custom_solr_search_filter_query/{custom_solr_search_filter_query}/delete",
 *     "collection" = "/admin/config/custom_solr_search_filter_query"
 *   }
 * )
 */
class CustomSolrSearchFilterQuery extends ConfigEntityBase implements CustomSolrSearchFilterQueryInterface {
  /**
   * The Custom solr search filter query ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Custom solr search filter query label.
   *
   * @var string
   */
  protected $label;

  /**
   * The Custom solr search filter query server.
   *
   * @var string
   */
  protected $server;

  /**
   * The Custom solr search filter query filter.
   *
   * @var filter
   */
  protected $filter;

}
