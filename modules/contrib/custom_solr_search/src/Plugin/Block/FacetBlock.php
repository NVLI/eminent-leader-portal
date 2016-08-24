<?php

namespace Drupal\custom_solr_search\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\custom_solr_search\SolrServerDetails;

/**
 * Provides a 'FacetBlock' block.
 *
 * @Block(
 *  id = "facet_block",
 *  admin_label = @Translation("Facet search"),
 * )
 */
class FacetBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * \Drupal\custom_solr_search\SolrServerDetails definition.
   *
   * @var \Drupal\custom_solr_search\SolrServerDetails
   */
  protected $serverDetails;

  /**
   * Construct.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param string $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\custom_solr_search\SolrServerDetails $serverDetails
   *   Custom Solr server details service.
   */
  public function __construct(
  array $configuration, $plugin_id, $plugin_definition, SolrServerDetails $serverDetails
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->serverDetails = $serverDetails;
  }

  /**
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   *
   * @return array
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
        $configuration,
        $plugin_id,
        $plugin_definition,
        $container->get('custom_solr_search.solr_servers')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);
    // Get the Core Details.
    $servers = array('all' => 'ALL');
    $servers += $this->serverDetails->getServers();
    // Get the configurations.
    $config = $this->getConfiguration();
    $form['servers'] = [
      '#type' => 'select',
      '#title' => $this->t('Select Server'),
      '#options' => $servers,
      '#default_value' => isset($config['servers']) ? $config['servers'] : 'all',
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->setConfigurationValue('servers', $form_state->getValue('servers'));
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $build['#cache']['max-age'] = 0;
    $config = $this->getConfiguration();
    $solr_core = $config['servers'];

    if ($solr_core != 'all') {
      $facet_fields = \Drupal::service('custom_solr_search.facet')->filter($solr_core);
      $build['facet_search'] = array(
        '#theme' => 'custom_solr_search_facet',
        '#facets' => isset($facet_fields) ? (array) $facet_fields : [],
      );
    }
    else {
      $servers = $this->serverDetails->getServers();
      $facet_fields = [];
      foreach ($servers as $server_machine => $server_display) {
        $result = \Drupal::service('custom_solr_search.facet')->filter($server_machine);
        $facet_fields = array_merge($facet_fields, $result);
      }
      $build['facet_search'] = array(
        '#theme' => 'custom_solr_search_facet',
        '#facets' => isset($facet_fields) ? (array) $facet_fields : [],
      );
    }
    return $build;
  }

}
