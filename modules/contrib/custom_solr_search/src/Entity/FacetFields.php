<?php

/**
 * @file
 * Contains \Drupal\custom_solr_search\Entity\FacetFields.
 */

namespace Drupal\custom_solr_search\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\custom_solr_search\FacetFieldsInterface;

/**
 * Defines the Facet fields entity.
 *
 * @ConfigEntityType(
 *   id = "facet_fields",
 *   label = @Translation("Facet fields"),
 *   handlers = {
 *     "list_builder" = "Drupal\custom_solr_search\FacetFieldsListBuilder",
 *     "form" = {
 *       "add" = "Drupal\custom_solr_search\Form\FacetFieldsForm",
 *       "edit" = "Drupal\custom_solr_search\Form\FacetFieldsForm",
 *       "delete" = "Drupal\custom_solr_search\Form\FacetFieldsDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\custom_solr_search\FacetFieldsHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "facet_fields",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "fields" = "fields",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/facet_fields/{facet_fields}",
 *     "add-form" = "/admin/structure/facet_fields/add",
 *     "edit-form" = "/admin/structure/facet_fields/{facet_fields}/edit",
 *     "delete-form" = "/admin/structure/facet_fields/{facet_fields}/delete",
 *     "collection" = "/admin/structure/facet_fields"
 *   }
 * )
 */
class FacetFields extends ConfigEntityBase implements FacetFieldsInterface {
  /**
   * The Facet fields ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Facet fields label.
   *
   * @var string
   */
  protected $label;

  /**
   * The Facet fields.
   *
   * @var string
   */
  protected $fields;

}
