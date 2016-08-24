<?php

namespace Drupal\facets\Plugin\facets\processor;

use Drupal\Core\Config\ConfigManagerInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\facets\FacetInterface;
use Drupal\facets\FacetSource\SearchApiFacetSourceInterface;
use Drupal\facets\Processor\BuildProcessorInterface;
use Drupal\facets\Processor\ProcessorPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a processor that transforms the results to show the list item label.
 *
 * @FacetsProcessor(
 *   id = "list_item",
 *   label = @Translation("List item label"),
 *   description = @Translation("Fields that are a list (such as list (integer), list (text)) can use this processor to show the value instead of the key."),
 *   stages = {
 *     "build" = 5
 *   }
 * )
 */
class ListItemProcessor extends ProcessorPluginBase implements BuildProcessorInterface, ContainerFactoryPluginInterface {

  /**
   * The config manager.
   *
   * @var \Drupal\Core\Config\ConfigManagerInterface
   */
  private $configManager;

  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  private $entityFieldManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigManagerInterface $config_manager, EntityFieldManagerInterface $entity_field_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->configManager = $config_manager;

    $this->entityFieldManager = $entity_field_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.manager'),
      $container->get('entity_field.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build(FacetInterface $facet, array $results) {
    $field_identifier = $facet->getFieldIdentifier();
    $entity = 'node';

    // Support multiple entities when using Search API.
    if ($facet->getFacetSource() instanceof SearchApiFacetSourceInterface) {
      $index = $facet->getFacetSource()->getIndex();
      $field = $index->getField($field_identifier);

      $entity = str_replace('entity:', '', $field->getDatasourceId());
    }

    // If it's an entity base field, we find it in the field definitions.
    // We don't have access to the bundle via SearchApiFacetSourceInterface, so
    // we check the entity's base fields only.
    $base_fields = $this->entityFieldManager->getFieldDefinitions($entity, '');

    // This only works for configurable fields.
    $config_entity_name = sprintf('field.storage.%s.%s', $entity, $field_identifier);

    if (isset($base_fields[$field_identifier])) {
      $field = $base_fields[$field_identifier];
    }
    elseif ($this->configManager->loadConfigEntityByName($config_entity_name) !== NULL) {
      $field = $this->configManager->loadConfigEntityByName($config_entity_name);
    }

    if ($field) {
      $function = $field->getSetting('allowed_values_function');

      if (empty($function)) {
        $allowed_values = $field->getSetting('allowed_values');
      }
      else {
        $allowed_values = ${$function}($field);
      }

      if (is_array($allowed_values)) {
        /** @var \Drupal\facets\Result\ResultInterface $result */
        foreach ($results as &$result) {
          if (isset($allowed_values[$result->getRawValue()])) {
            $result->setDisplayValue($allowed_values[$result->getRawValue()]);
          }
        }
      }
    }

    return $results;
  }

}
