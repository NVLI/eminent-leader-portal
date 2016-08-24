<?php

namespace Drupal\custom_solr_search\Plugin\Block;

use Drupal\Component\Utility\Html;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\custom_solr_search\SolrServerDetails;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\custom_solr_search\Search;
use Drupal\custom_solr_search\SearchSolrAll;
use Drupal\custom_solr_search\FilterQuerySettings;
use Drupal\Core\Url;
use Drupal\Core\Path;
use Drupal\Core\Link;

/**
 * Provides a 'Result' Block
 *
 * @Block(
 *   id = "custom_solr_search_result_block",
 *   admin_label = @Translation("Custom SOLR Search Result block"),
 * )
 */
class CustomSolrSearchResultBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * \Drupal\custom_solr_search\Search definition.
   *
   * @var \Drupal\custom_solr_search\Search
   */
  protected $search;

  /**
   * \Drupal\custom_solr_search\SolrServerDetails definition.
   *
   * @var \Drupal\custom_solr_search\SolrServerDetails
   */
  protected $serverDetails;

  /**
   * \Drupal\custom_solr_search\Search definition.
   *
   * @var \Drupal\custom_solr_search\SearchSolrAll
   */
  protected $searchall;

  /**
   * Construct.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param string $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\custom_solr_search\Search $search
   *   Custom Solr search service.
   * @param \Drupal\custom_solr_search\SolrServerDetails $serverDetails
   *   Custom Solr server details service.
   * @param \Drupal\custom_solr_search\SearchSolrAll $searchall
   *   Custom Solr search service for all core.
   */
  public function __construct(
  array $configuration, $plugin_id, $plugin_definition, FilterQuerySettings $filtertQueryIds, Search $search, SolrServerDetails $serverDetails, SearchSolrAll $searchall
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->filtertQueryIds = $filtertQueryIds;
    $this->search = $search;
    $this->serverDetails = $serverDetails;
    $this->searchall = $searchall;
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    // Get the Filter Query Details.
    $filters = $this->filtertQueryIds->getFilterQuerySetingids();

    // Get the configurations.
    $config = $this->getConfiguration();
    $form['custom_block_filters'] = [
      '#type' => 'select',
      '#title' => $this->t('Select the Filter query Settings'),
      '#options' => $filters,
      '#default_value' => isset($config['custom_block_filters']) ? $config['custom_block_filters'] : '',
    ];
    $form['custom_solr_search_keyword_argument'] = [
      '#type' => 'number',
      '#min' => 0,
      '#title' => $this->t('Solr Search Keyword Argument'),
      '#default_value' => isset($config['custom_solr_search_keyword_argument']) ? $config['custom_solr_search_keyword_argument'] : 0,
      '#description' => $this->t('Add the argument of search keyword to fetch the results.e.g. example.com/result/keyword if keyword is a argument then enter 2.'),
    ];
    $form['custom_solr_search_offset'] = [
      '#type' => 'number',
      '#min' => 0,
      '#title' => $this->t('Solr Search Result Offset'),
      '#default_value' => isset($config['custom_solr_search_offset']) ? $config['custom_solr_search_offset'] : 0,
      '#description' => $this->t('Add the offset to show the search result in block.'),
    ];
    $form['custom_solr_search_limit'] = [
      '#type' => 'number',
      '#min' => 0,
      '#title' => $this->t('Solr Search Result Limit'),
      '#default_value' => isset($config['custom_solr_search_limit']) ? $config['custom_solr_search_limit'] : 5,
      '#description' => $this->t('Add the Limit to show the search result in block.'),
    ];
    $form['custom_solr_search_result_view_more'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Show View More Link on Solr Search Result Block '),
      '#default_value' => $config['custom_solr_search_result_view_more'],
      '#description' => $this->t('Add the internal relative urls, prefix the path with internal://for external links, use absolute path.'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->setConfigurationValue('custom_block_filters', $form_state->getValue('custom_block_filters'));
    $this->setConfigurationValue('custom_solr_search_keyword_argument', $form_state->getValue('custom_solr_search_keyword_argument'));
    $this->setConfigurationValue('custom_solr_search_limit', $form_state->getValue('custom_solr_search_limit'));
    $this->setConfigurationValue('custom_solr_search_offset', $form_state->getValue('custom_solr_search_offset'));
    $this->setConfigurationValue('custom_solr_search_result_view_more', $form_state->getValue('custom_solr_search_result_view_more'));
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $path = \Drupal::request()->getPathInfo();
    $args = explode('/', $path);

    $config = $this->getConfiguration();
    $filterId = $config['custom_block_filters'];
    $filterQuerySettings = $this->filtertQueryIds->getFilterQueryString($filterId);
    // Get the keyword argument.
    $argument_keyword = $config['custom_solr_search_keyword_argument'];
    $keyword = $args[$argument_keyword];
    $limit = $config['custom_solr_search_limit'];
    $offset = $config['custom_solr_search_offset'];
    $view_more = $config['custom_solr_search_result_view_more'];
    // Facet search integration.
    $url_components = custom_solr_search_get_url_components();
    $facet_options = custom_solr_search_get_facet_filter_query_string($url_components['facet_query']);
    // Check the block configuration and search the results.
    // If selected the core.
    $keyword = urldecode($keyword);

    if ($filterQuerySettings['server'] == 'all') {
      $solr_options = $filterQuerySettings['filter'];
      if (!empty($facet_options)) {
        $options = $solr_options . 'AND ( ' . $facet_options . ')';
      }
      else {
        $options = $solr_options;
      }
      $results = $this->searchall->seachAll($keyword, $offset, $limit, $options);
    }
    else {
      $server = $filterQuerySettings['server'];
      $solr_options = $filterQuerySettings['filter'];
      if (!empty($facet_options)) {
        $options = $solr_options . 'AND ( ' . $facet_options . ')';
      }
      else {
        $options = $solr_options;
      }
      $results = $this->search->basicSearch($keyword, $offset, $limit, $server, $options);
    }

    // Format result to display as unformatted list.
    if (!empty($results)) {
      foreach ($results['docs'] as $result) {
        if (isset($result->title)) {
          $title = $result->title;
        }
        else {
          $title = $result->label;
        }
        if ($result->recordtype == 'newspaper') {
          $publishdate = \Drupal::service('date.formatter')->format($result->publishDate[0], 'custom', 'd-m-Y');
        }
        else {
          $publishdate = isset($result->publishDate) ? implode(', ', $result->publishDate) : '';
        }
        
        $render['result'][] = array(
          '#theme' => 'custom_solr_search_result',
          '#url' => isset($result->url[0]) ? $result->url[0] : '',
          '#title' => isset($title) ? $title : '',
          '#author' => isset($result->author) ? implode(', ', $result->author) : '',
          '#publishDate' => isset($publishdate) ? $publishdate : '',
          '#publisher' => isset($result->publisher) ? implode(', ', $result->publisher) : '',
          '#topic' => isset($result->topic) ? implode(', ', $result->topic) : '',
          '#docid' => isset($result->id) ? $result->id : '',
        );
      }
    }
    $query_parameter = \Drupal::request()->getQueryString();
    $facet_params = !empty($query_parameter) ? $query_parameter : '';

    if (!empty($view_more) && !empty($results['docs'])) {
      if (!empty($keyword)) {
        $facet = isset($facet_params) ? "?$facet_params" : '';
        $url = Url::fromUri($view_more . $keyword . $facet);
      }
      else {
        $url = Url::fromUri($view_more);
      }
      $link = Link::fromTextAndUrl(t('View More'), $url)->toString();
    }

    $markup['search_results'] = array(
      '#theme' => 'item_list',
      '#items' => $render['result'],
      '#cache' => array(
        'max-age' => 0,
      ),
      '#empty' => t('No search results found!'),
      '#suffix' => !empty($view_more) ? $link : '',
    );

    return $markup;
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
        $container->get('custom_solr_search.filter_query_settings'),
        $container->get('custom_solr_search.search'),
        $container->get('custom_solr_search.solr_servers'),
        $container->get('custom_solr_search.search_all')
    );
  }

}
