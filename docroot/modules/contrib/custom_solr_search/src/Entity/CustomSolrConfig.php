<?php

namespace Drupal\custom_solr_search\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the Custom solr config entity.
 *
 * @ConfigEntityType(
 *   id = "custom_solr_config",
 *   label = @Translation("Custom solr config"),
 *   handlers = {
 *     "list_builder" = "Drupal\custom_solr_search\CustomSolrConfigListBuilder",
 *     "form" = {
 *       "add" = "Drupal\custom_solr_search\Form\CustomSolrConfigForm",
 *       "edit" = "Drupal\custom_solr_search\Form\CustomSolrConfigForm",
 *       "delete" = "Drupal\custom_solr_search\Form\CustomSolrConfigDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\custom_solr_search\CustomSolrConfigHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "custom_solr_config",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/config/custom/solr/servers/custom_solr_config/{custom_solr_config}",
 *     "add-form" = "/admin/config/custom/solr/servers/custom_solr_config/add",
 *     "edit-form" = "/admin/config/custom/solr/servers/custom_solr_config/{custom_solr_config}/edit",
 *     "delete-form" = "/admin/config/custom/solr/servers/custom_solr_config/{custom_solr_config}/delete",
 *     "collection" = "/admin/config/custom/solr/servers/custom_solr_config"
 *   }
 * )
 */
class CustomSolrConfig extends ConfigEntityBase implements CustomSolrConfigInterface {

  /**
   * The Custom solr config ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Custom solr config label.
   *
   * @var string
   */
  protected $label;

}
