<?php

/**
 * @file
 * Provides Elastica client for Search API Elasticsearch.
 */

/**
 * Search API Elasticsearch Elastica service class.
 */
class SearchApiElasticsearchElastica extends SearchApiElasticsearchAbstractService {

  /**
   * Elasticsearch Connection.
   */
  protected $elasticaClient = NULL;

  /**
   * Overrides __construct().
   */
  public function __construct(SearchApiServer $server) {
    parent::__construct($server);

    if (search_api_elasticsearch_elastica_loaded()) {
      $config = array();
      $count_options = array();

      $options = $this->getOptions();

      if (isset($options) && !empty($options)) {
        $count_options = $options;
        if (isset($count_options['facet_limit'])) {
          unset($count_options['facet_limit']);
        }

        if (count($count_options) > 1) {
          foreach ($count_options as $id => $option) {
            $config[] = $option;
          }

          try {
            $this->elasticaClient = new Elastica\Client(array('servers' => $config));
          }
          catch (Exception $e) {
            watchdog('Elasticsearch', check_plain($e->getMessage()), array(), WATCHDOG_ERROR);
            drupal_set_message(check_plain($e->getMessage()), 'error');
            return FALSE;
          }
        }
        else {
          $config = reset($count_options);
          if (module_exists('search_api_facetapi')) {
            if (isset($options['facet_limit'])) {
              $config['facet_limit'] = $options['facet_limit'];
            }
          }
          try {
            $this->elasticaClient = new Elastica\Client($config);
          }
          catch (Exception $e) {
            watchdog('Elasticsearch', check_plain($e->getMessage()), array(), WATCHDOG_ERROR);
            drupal_set_message(check_plain($e->getMessage()), 'error');
            return FALSE;
          }
        }
        if (module_exists('psr3_watchdog')) {
          $logger = new Psr3Watchdog();
          $logger->setType('Elastica');
          $this->elasticaClient->setLogger($logger);
        }
        if (module_exists('monolog')) {
          $logger = new \Drupal\Monolog\Logger();
          $logger->setName('Elastica');
          $this->elasticaClient->setLogger($logger);
        }
      }
    }
    else {
      watchdog('Elasticsearch', 'Elastica Client does not exist', array(), WATCHDOG_ERROR);
      return FALSE;
    }
  }

  /**
   * Overrides supportsFeature().
   */
  public function supportsFeature($feature) {
    parent::supportsFeature($feature);
    $this->_supportedFeatures += drupal_map_assoc(array(
          'search_api_facets',
          'search_api_facets_operator_or',
          'search_api_autocomplete',
          'search_api_mlt',
          'search_api_data_type_location',
          // 'search_api_spellcheck',
        ));
    return isset($this->_supportedFeatures[$feature]);
  }

  /**
   * Overrides postCreate().
   */
  public function postCreate() {

  }

  /**
   * Overrides postUpdate().
   */
  public function postUpdate() {
    return FALSE;
  }

  /**
   * Overrides preDelete().
   */
  public function preDelete() {

  }

  /**
   * Overrides viewSettings().
   */
  public function viewSettings() {
    $output = array();

    try {
      $health = !empty($this->elasticaClient) ? $this->getClusterHealth() : NULL;
    }
    catch (Exception $e) {
      watchdog('Elasticsearch', check_plain($e->getMessage()), array(), WATCHDOG_ERROR);
      drupal_set_message(check_plain($e->getMessage()), 'error');
      drupal_set_message(t('No connection to the Elasticsearch server.'), 'error');
    }
    $output['status'] = array(
      '#type' => 'item',
      '#title' => t('Elasticsearch daemon status'),
      '#markup' => '<div class="elasticsearch-daemon-status"><em>' . (!empty($health['status']) ? 'running' : 'One or more nodes not running') . '</em></div>',
    );

    // Display settings.
    $form = $form_state = array();
    $option_form = $this->configurationForm($form, $form_state);
    $option_form['#title'] = t('Elasticsearch server settings');

    $element = $this->parseOptionFormElement($option_form, 'options');
    if (!empty($element)) {
      $settings = '';
      foreach ($element['option'] as $sub_element) {
        $settings .= $this->viewSettingElement($sub_element);
      }

      $output['settings'] = array(
        '#type' => 'fieldset',
        '#title' => $element['label'],
      );

      $output['settings'][] = array(
        '#type' => 'markup',
        '#markup' => '<div class="elasticsearch-server-settings">' . $settings . '</div>',
      );
    }

    return $output;
  }

  /**
   * Overrides addIndex().
   */
  public function addIndex(SearchApiIndex $index) {
    $elastica_index = $this->getElasticaIndex($index);
    if (!empty($elastica_index)) {
      try {
        drupal_alter('search_api_elasticsearch_elastica_add_index', $index->options);
        $response = $elastica_index->create($index->options, TRUE);
      }
      catch (Exception $e) {
        watchdog('Elasticsearch', check_plain($e->getMessage()), array(), WATCHDOG_ERROR);
        drupal_set_message(check_plain($e->getMessage()), 'error');
        return FALSE;
      }

      // Update mapping.
      $this->fieldsUpdated($index);
    }
  }

  /**
   * Overrides fieldsUpdated().
   */
  public function fieldsUpdated(SearchApiIndex $index) {
    parent::fieldsUpdated($index);
    $elastica_index = $this->getElasticaIndex($index);
    if (!empty($elastica_index)) {
      $elastica_type = $elastica_index->getType($index->machine_name);

      // Create a new mapping.
      $mapping = new Elastica\Type\Mapping();
      $mapping->setType($elastica_type);
      $mapping->setParam('_all', array('enabled' => FALSE));
      try {
        try {
          // First we try a simple merge.
          $mapping->setProperties($this->fieldsUpdatedProperties);
          $mapping->send();
        }
        catch (Exception $e) {
          // If a merge fails, we must delete the type first.
          $elastica_type->delete();
          $elastica_type = $elastica_index->getType($index->machine_name);
          $mapping->setType($elastica_type);
          $mapping->send();
        }
      }
      catch (Exception $e) {
        watchdog('Elasticsearch', check_plain($e->getMessage()), array(), WATCHDOG_ERROR);
        drupal_set_message(check_plain($e->getMessage()), 'error');
        drupal_set_message(t('Fields are not re-indexed'), 'error');
        return FALSE;
      }
    }

    // Flag for re-indexing.
    return TRUE;
  }

  /**
   * Overrides removeIndex().
   */
  public function removeIndex($index) {
    $elastica_index = $this->getElasticaIndex($index);
    // PATCH - Only delete the index's data if the index isn't read-only.
    if (!empty($elastica_index) && $index->read_only != 1) {
      try {
        $response = $elastica_index->delete();
        return $response;
      }
      catch (Exception $e) {
        watchdog('Elasticsearch', check_plain($e->getMessage()), array(), WATCHDOG_ERROR);
        drupal_set_message(check_plain($e->getMessage()), 'error');
        return FALSE;
      }
    }
  }

  /**
   * Overrides indexItems().
   */
  public function indexItems(SearchApiIndex $index, array $items) {
    $elastica_type = $this->getElasticaType($index);

    if (empty($elastica_type) || empty($items)) {
      return array();
    }

    $documents = array();
    foreach ($items as $id => $fields) {
      $data = array('id' => $id);
      foreach ($fields as $field_id => $field_data) {
        if (isset($field_data['value']) && is_array($field_data['value'])) {
          $data[$field_id] = array();
          foreach ($field_data['value'] as $token) {
            if (is_array($token) && isset($token['value'])) {
              $data[$field_id][] = $token['value'];
            }
            else {
              $data[$field_id][] = $token;
            }
          }
        }
        else {
          $data[$field_id] = $field_data['value'];
        }
      }

      $documents[] = new Elastica\Document($id, $data);
    }
    try {
      $elastica_type->addDocuments($documents);
    }
    catch (Exception $e) {
      watchdog('Elasticsearch', check_plain($e->getMessage()), array(), WATCHDOG_ERROR);
      drupal_set_message(check_plain($e->getMessage()), 'error');
    }
    $elastica_type->getIndex()->refresh();

    return array_keys($items);
  }

  /**
   * Overrides deleteItems().
   */
  public function deleteItems($ids = 'all', SearchApiIndex $index = NULL) {
    if (empty($index)) {
      foreach (search_api_index_load_multiple(FALSE, array('server' => $this->server->machine_name)) as $index) {
        $this->deleteItems('all', $index);
      }
    }
    elseif ($ids === 'all') {
      $elastica_type = $this->getElasticaType($index);
      if (!empty($elastica_type)) {
        $match_all = new Elastica\Query\MatchAll();
        $elastica_type->deleteByQuery($match_all);
      }
    }
    else {
      $elastica_type = $this->getElasticaType($index);
      if (!empty($elastica_type)) {
        $elastica_type->deleteIds($ids);
      }
    }
  }

  /**
   * Overrides search().
   */
  public function search(SearchApiQueryInterface $query) {

    // Results.
    $search_result = array('result count' => 0);

    // Get index.
    $index = $query->getIndex();

    // Get index type.
    $elastica_type = $this->getElasticaType($index);

    // Get query options.
    $query_options = $this->getSearchQueryOptions($query);

    // Check elasticsearch index.
    if (empty($elastica_type)) {
      return $search_result;
    }

    // Build Elastica query.
    $elastica_query = $this->buildSearchQuery($query);

    // Add facets.
    $this->addSearchFacets($elastica_query, $query);

    $response = SearchApiElasticsearchElasticaSearcher::search($elastica_type, $elastica_query, $query_options, $query);

    // Show Elasticsearch query string from Elastica
    // as json output when views debug output is enabled.
    if (function_exists('vpr') &&
        $elastica_param_query = $elastica_query->getParam('query')) {
      vpr(drupal_json_encode($elastica_param_query));
    }

    // Parse response.
    return $this->parseSearchResponse($response, $query);
  }

  /**
   * Recursively parse Search API filters.
   */
  protected function parseFilter(SearchApiQueryFilter $query_filter, $index_fields, $ignored_field_id = '') {
    if (empty($query_filter)) {
      return NULL;
    }
    else {
      $conjunction = $query_filter->getConjunction();

      $filters = array();

      try {
        foreach ($query_filter->getFilters() as $filter_info) {
          $filter = NULL;

          // Simple filter [field_id, value, operator].
          if (is_array($filter_info)) {
            $filter_assoc = $this->getAssociativeFilter($filter_info);
            $this->correctFilter($filter_assoc, $index_fields, $ignored_field_id);
            // Check field.
            $filter = $this->getFilter($filter_assoc);

            if (!empty($filter)) {
              $filters[] = $filter;
            }
          }
          // Nested filters.
          elseif ($filter_info instanceof SearchApiQueryFilter) {
            $nested_filters = $this->parseFilter($filter_info, $index_fields, $ignored_field_id);
            if (!empty($nested_filters)) {
              $filters = array_merge($filters, $nested_filters);
            }
          }
        }
        $filters = $this->setFiltersConjunction($filters, $conjunction);
      }
      catch (Exception $e) {
        watchdog('Elasticsearch', check_plain($e->getMessage()), array(), WATCHDOG_ERROR);
        drupal_set_message(check_plain($e->getMessage()), 'error');
      }

      return $filters;
    }
  }

  /**
   * Get filter by associative array.
   */
  protected function getFilter(array $filter_assoc) {
    // Handles "empty", "not empty" operators.
    if (!isset($filter_assoc['filter_value'])) {
      switch ($filter_assoc['filter_operator']) {
        case '<>':
          $filter = new Elastica\Filter\Exists($filter_assoc['field_id']);
          break;

        case '=':
          $filter = new Elastica\Filter\BoolNot(new Elastica\Filter\Exists($filter_assoc['field_id']));
          break;

        default:
          throw new Exception(t('Value is empty for :field_id! Incorrect filter criteria is using for searching!', array(':field_id' => $filter_assoc['field_id'])));
      }
    }
    // Normal filters.
    else {
      switch ($filter_assoc['filter_operator']) {
        case '=':
          $filter = new Elastica\Filter\Term(array($filter_assoc['field_id'] => $filter_assoc['filter_value']));
          break;

        case '<>':
          $filter = new Elastica\Filter\BoolNot(new Elastica\Filter\Term(array($filter_assoc['field_id'] => $filter_assoc['filter_value'])));
          break;

        case '>':
          $filter = new Elastica\Filter\Range($filter_assoc['field_id'], array(
                'from' => $filter_assoc['filter_value'],
                'to' => NULL,
                'include_lower' => FALSE,
                'include_upper' => FALSE,
              ));
          break;

        case '>=':
          $filter = new Elastica\Filter\Range($filter_assoc['field_id'], array(
                'from' => $filter_assoc['filter_value'],
                'to' => NULL,
                'include_lower' => TRUE,
                'include_upper' => FALSE,
              ));
          break;

        case '<':
          $filter = new Elastica\Filter\Range($filter_assoc['field_id'], array(
                'from' => NULL,
                'to' => $filter_assoc['filter_value'],
                'include_lower' => FALSE,
                'include_upper' => FALSE,
              ));
          break;

        case '<=':
          $filter = new Elastica\Filter\Range($filter_assoc['field_id'], array(
                'from' => NULL,
                'to' => $filter_assoc['filter_value'],
                'include_lower' => FALSE,
                'include_upper' => TRUE,
              ));
          break;

        default:
          throw new Exception(t('Undefined operator :field_operator for :field_id field! Incorrect filter criteria is using for searching!',
                  array(':field_operator' => $filter_assoc['filter_operator'], ':field_id' => $filter_assoc['field_id'])));
      }
    }

    return $filter;
  }

  /**
   * Helper function that return associative array  of filters info.
   */
  public function getAssociativeFilter(array $filter_info) {

    $filter_operator = str_replace('!=', '<>', $filter_info[2]);
    return array(
      'field_id' => $filter_info[0],
      'filter_value' => $filter_info[1],
      'filter_operator' => $filter_operator,
    );
  }

  /**
   * Helper function thaht set filters conjunction.
   */
  protected function setFiltersConjunction(&$filters, $conjunction) {

    if (count($filters) > 1) {
      if ($conjunction === 'OR') {
        $filter = new Elastica\Filter\BoolOr();
        $filter->setFilters($filters);
        $filters = array($filter);
      }
      elseif ($conjunction === 'AND') {
        $filter = new Elastica\Filter\BoolAnd();
        $filter->setFilters($filters);
        $filters = array($filter);
      }
      else {
        throw new Exception(t('Undefined conjunction :conjunction! Available values are :avail_conjunction! Incorrect filter criteria is using for searching!',
                array(':conjunction!' => $conjunction, ':avail_conjunction' => $conjunction)));
      }
    }
    return $filters;
  }

  /**
   * Helper function that check if filter is set correct.
   */
  protected function correctFilter($filter_assoc, $index_fields, $ignored_field_id = '') {
    if (!array_key_exists('field_id', $filter_assoc) || !array_key_exists('filter_value', $filter_assoc)
        || !isset($filter_assoc['filter_operator'])) {
      throw new Exception(t('Incorrect filter criteria is using for searching!'));
    }

    $field_id = $filter_assoc['field_id'];
    if (!isset($index_fields[$field_id])) {
      throw new Exception(t(':field_id Undefined field ! Incorrect filter criteria is using for searching!', array(':field_id' => $field_id)));
    }

    // Check operator.
    if (empty($filter_assoc['filter_operator'])) {
      throw new Exception(t('Empty filter operator for :field_id field! Incorrect filter criteria is using for searching!', array(':field_id' => $field_id)));
    }

    // If field should be ignored, we skip.
    if ($field_id === $ignored_field_id) {
      return TRUE;
    }

    return TRUE;
  }

  /**
   * Helper function. Get an Elastica index.
   */
  public function getElasticaIndex(SearchApiIndex $index) {
    if (!empty($index) && !empty($this->elasticaClient)) {
      $index_name = $this->getIndexName($index);
      try {
        $elastica_index = $this->elasticaClient->getIndex($index_name);
        return $elastica_index;
      }
      catch (Exception $e) {
        watchdog('Elasticsearch', check_plain($e->getMessage()), array(), WATCHDOG_ERROR);
        drupal_set_message(check_plain($e->getMessage()), 'error');
      }
    }
    return NULL;
  }

  /**
   * Helper function. Get the corresponding Elastica type.
   */
  public function getElasticaType(SearchApiIndex $index) {
    $elastica_index = $this->getElasticaIndex($index);
    return !empty($elastica_index) ? $elastica_index->getType($index->machine_name) : NULL;
  }

  /**
   * Helper function build search query().
   */
  protected function buildSearchQuery(SearchApiQueryInterface $query) {
    // Query options.
    $query_options = $this->getSearchQueryOptions($query);

    // Main query.
    $elastica_query = new Elastica\Query();
    $elastica_query->setFrom($query_options['query_offset']);
    $elastica_query->setSize($query_options['query_limit']);

    // Search API Location support.
    if (!empty($query_options['spatials'])) {
      foreach ($query_options['spatials'] as $i => $spatial) {
        if (empty($spatial['field']) || empty($spatial['lat']) || empty($spatial['lon'])) {
          continue;
        }

        $field = $spatial['field'];
        $point = array(
          'lat' => (float) $spatial['lat'],
          'lon' => (float) $spatial['lon'],
        );

        $radius = isset($spatial['radius']) ? (float) $spatial['radius'] : NULL;

        $query_options['query_search_filter'] = new Elastica\Filter\GeoDistance($field, $point, $radius);
      }
    }

    // Build the query.
    if (!empty($query_options['query_search_string']) && !empty($query_options['query_search_filter'])) {
      $elastica_query->setQuery(new Elastica\Query\Filtered($query_options['query_search_string'], $query_options['query_search_filter']));
    }
    elseif (!empty($query_options['query_search_string'])) {
      $elastica_query->setQuery($query_options['query_search_string']);
    }
    elseif (!empty($query_options['query_search_filter'])) {
      $elastica_query->setFilter($query_options['query_search_filter']);
    }

    // Sort.
    if (!empty($query_options['sort'])) {
      $elastica_query->setSort($query_options['sort']);
    }

    return $elastica_query;
  }

  /**
   * Helper function build facets in search.
   */
  protected function addSearchFacets(Elastica\Query $elastica_query, SearchApiQueryInterface $query) {

    // SEARCH API FACETS.
    $facets = $query->getOption('search_api_facets');
    $index_fields = $this->getIndexFields($query);
    if (!empty($facets)) {
      // Loop trough facets.
      foreach ($facets as $facet_id => $facet_info) {
        $facet = NULL;
        $field_id = $facet_info['field'];
        // Skip if not recognized as a known field.
        if (!isset($index_fields[$field_id])) {
          continue;
        }

        $facet_missing = $facet_info['missing'];

        $field_type = search_api_extract_inner_type($index_fields[$field_id]['type']);

        // TODO: handle different types (GeoDistance and so on).
        if ($field_type === 'date') {
          $facet = $this->createDateFieldFacet($facet_id);
        }
        else {
          $facet = new Elastica\Facet\Terms($facet_id);
          // We may want missing facets.
          $facet->setAllTerms($facet_missing);
        }

        // Add the facet.
        if (!empty($facet)) {
          // Add facet options.
          $facet = $this->addFacetOptions($facet, $query, $facet_info, $elastica_query);
          $elastica_query->addFacet($facet);
        }
      }
    }
  }

  /**
   * Helper function that add options and return facet.
   */
  protected function addFacetOptions(&$facet, SearchApiQueryInterface $query, $facet_info, Elastica\Query $elastica_query) {

    $facet_limit = $this->getFacetLimit($facet_info);
    $facet_search_filter = $this->getFacetSearchFilter($query, $facet_info);
    // Set the field.
    $facet->setField($facet_info['field']);

    // OR facet. We remove filters affecting the assiociated field.
    // TODO: distinguish between normal filters and facet filters.
    // See http://drupal.org/node/1390598.

    // Filter the facet.
    if (!empty($facet_search_filter)) {
      $facet->setFilter($facet_search_filter);
    }

    // Limit the number of returned entries.
    if ($facet_limit > 0 && method_exists($facet, 'setSize')) {
      $facet->setSize($facet_limit);
    }

    return $facet;
  }

  /**
   * Helper function create Facet for date field type.
   */
  protected function createDateFieldFacet($facet_id) {

    $date_interval = $this->getDateFacetInterval($facet_id);

    $facet = new Elastica\Facet\DateHistogram($facet_id);

    $facet->setInterval($date_interval);

    // Maybe get php timezone?
    $facet->setTimezone('UTC');

    // Use factor 1000 as we store dates as seconds from epoch
    // not milliseconds.
    $facet->setParam('factor', 1000);

    return $facet;
  }

  /**
   * Helper function which parse facets in search().
   */
  public function parseSearchResponse($response, SearchApiQueryInterface $query) {

    $search_result = array('results' => array());

    $search_result['result count'] = $response->getTotalHits();

    // Parse results.
    foreach ($response->getResults() as $result) {
      $id = $result->getId();

      $search_result['results'][$id] = array(
        'id' => $id,
        'score' => $result->getScore(),
        'fields' => $result->getSource(),
      );
    }

    // Parse facets.
    $search_result['search_api_facets'] = $this->parseSearchFacets($response, $query);
    if (module_exists('search_api_spellcheck')) {
      $search_result['search_api_spellcheck'] = new SearchApiElasticsearchElasticaSpellcheck($response);
    }

    return $search_result;
  }

  /**
   * Update settings.
   */
  public function updateSettings(SearchApiIndex $index, $data) {
    try {
      $elastica_index = $this->getElasticaIndex($index);
      $elastica_index->close();
      $elastica_index->setSettings($data);
      $elastica_index->open();
    }
    catch (Exception $e) {
      watchdog('Elasticsearch', check_plain($e->getMessage()), array(), WATCHDOG_ERROR);
      drupal_set_message(check_plain($e->getMessage()), 'error');
      return FALSE;
    }
  }

  /**
   * Get settings.
   */
  public function getSettings(SearchApiIndex $index) {
    try {
      $elastica_index = $this->getElasticaIndex($index);

      if ($elastica_index) {
        $settings = $elastica_index->getSettings()->get();
        if (!empty($settings)) {
          $settings = $this->filterSettings($settings);

          return $settings;
        }
      }
    }
    catch (Exception $e) {
      watchdog('Elasticsearch', check_plain($e->getMessage()), array(), WATCHDOG_ERROR);
      drupal_set_message(check_plain($e->getMessage()), 'error');
    }

    return FALSE;
  }

  /**
   * Get settings.
   */
  public function filterSettings($settings) {
    $new_settings = array();

    foreach ($settings as $setting => $value) {
      $filtered_setting = str_replace('index.', '', $setting);
      $new_settings[$filtered_setting] = $value;
    }

    foreach ($new_settings as $new_setting => $new_settings_value) {
      if (in_array($new_setting, array('number_of_shards', 'number_of_replicas'))) {
        unset($new_settings[$new_setting]);
      }
    }

    return $new_settings;
  }

  /**
   * Helper function return associative array with query options.
   */
  protected function getSearchQueryOptions(SearchApiQueryInterface $query) {

    // Query options.
    $query_options = $query->getOptions();

    // Index fields.
    $index_fields = $this->getIndexFields($query);

    // Range.
    $query_offset = empty($query_options['offset']) ? 0 : $query_options['offset'];
    $query_limit = empty($query_options['limit']) ? 10 : $query_options['limit'];

    // Query string.
    $query_search_string = NULL;

    // Query filter.
    $query_search_filter = NULL;

    // Query analyzer.
    $analyzer = isset($query_options['analyzer']) ? $query_options['analyzer'] : NULL;

    // Full text search.
    $keys = $query->getKeys();
    if (!empty($keys)) {
      if (is_string($keys)) {
        $keys = array($keys);
      }

      // Full text fields in which to perform the search.
      $query_full_text_fields = $query->getFields();

      // Query string.
      $search_string = $this->flattenKeys($keys, $query_options['parse mode']);

      if (!empty($search_string)) {
        $query_search_string = new Elastica\Query\QueryString($search_string);
        $query_search_string->setFields(array_values($query_full_text_fields));
        if ($analyzer) {
          $query_search_string->setParam('analyzer', $analyzer);
        }
      }
    }

    // Sort.
    try {
      $sort = $this->getSortSearchQuery($query);
    }
    catch (Exception $e) {
      watchdog('Elasticsearch', check_plain($e->getMessage()), array(), WATCHDOG_ERROR);
      drupal_set_message($e->getMessage(), 'error');
    }

    // Filters.
    $parsed_query_filters = $this->parseFilter($query->getFilter(), $index_fields);
    if (!empty($parsed_query_filters)) {
      $query_search_filter = $parsed_query_filters[0];
    }

    // More Like This.
    $mlt = array();
    if (isset($query_options['search_api_mlt'])) {
      $mlt = $query_options['search_api_mlt'];
    }

    // Spatials.
    $spatials = array();
    if (isset($query_options['search_api_location'])) {
      $spatials = $query_options['search_api_location'];
    }

    return array(
      'query_offset' => $query_offset,
      'query_limit' => $query_limit,
      'query_search_string' => $query_search_string,
      'query_search_filter' => $query_search_filter,
      'sort' => $sort,
      'mlt' => $mlt,
      'analyzer' => $analyzer,
      'spatials' => $spatials,
    );
  }

  /**
   * Transport options supported by this library.
   *
   * @return array
   *   An array of available transport options.
   *   Key: Transport name used by library.
   *   Value: Display name.
   */
  protected function getTransportOptions() {
    $options = array(
      'Http' => 'HTTP',
      'Https' => 'HTTPS',
      'Memcache' => 'Memcache',
      'Null' => 'Null',
    );

    if (class_exists('\GuzzleHttp\Client')) {
      $options['Guzzle'] = 'Guzzle';
    }

    if (class_exists('\Elasticsearch\RestClient')) {
      $options['Thrift'] = 'Thrift';
    }

    return $options;
  }

  /**
   * Get Cluster health.
   */
  protected function getClusterHealth() {
    return $this->elasticaClient->getCluster()->getHealth()->getData();
  }

  /**
   * Get Cluster state.
   */
  protected function getClusterState() {
    return $this->elasticaClient->getCluster()->getState();
  }

  /**
   * Return Elastica Client.
   */
  public function getElasticaClient() {
    return $this->elasticaClient;
  }

  /**
   * buildSpellcheckQuery
   *
   * @param SearchApiQueryInterface $query
   * @access protected
   * @return void
   */
  protected function buildSpellcheckQuery(SearchApiQueryInterface $query) {
    $suggest = new \Elastica\Suggest();
    $phrase = new \Elastica\Suggest\Phrase('suggest1', 'text');
    $phrase->setText($this->flattenKeys($this->getKeys()));
    $phrase->setHighlight("<suggest>", "</suggest>")->setStupidBackoffSmoothing();
    $phrase->addCandidateGenerator(new \Elastica\Suggest\CandidateGenerator\DirectGenerator("text"));
    $suggest->addSuggestion($phrase);

    return $suggest;
  }

  public function setTransport($transport) {
    $this->elasticaClient->setConfigValue('transport', $transport);
  }
}
