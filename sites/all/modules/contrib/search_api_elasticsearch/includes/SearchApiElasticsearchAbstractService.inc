<?php

/**
 * @file
 * Provides a Elasticsearch-based service class for the Search API.
 */

/**
 * Elasticsearch service abstract class.
 */
abstract class SearchApiElasticsearchAbstractService extends SearchApiAbstractService {

  abstract protected function getClusterHealth();
  abstract protected function getClusterState();
  abstract protected function getTransportOptions();
  abstract protected function getSettings(SearchApiIndex $index);
  abstract protected function updateSettings(SearchApiIndex $index, $data);

  /**
   * {@inheritdoc}
   */
  public function supportsFeature($feature) {
    $this->_supportedFeatures = drupal_map_assoc(array('search_api_service_extra'));
  }

  /**
   * Overrides configurationForm().
   */
  public function configurationForm(array $form, array &$form_state) {
    $options = $this->options + array(
      'host' => '127.0.0.1',
      'port' => 9200,
      'path' => '',
      'url' => NULL,
      'transport' => 'Http',
      'persistent' => TRUE,
      'timeout' => 300,
      'log' => FALSE,
      'retryOnConflict' => 0,
    );

    // Daemon settings.
    $form['daemon_settings'] = array(
      '#type' => 'fieldset',
      '#title' => t('Elasticsearch client settings'),
      '#tree' => TRUE,
      '#prefix' => '<div id="elasticsearch-ajax-wrapper">',
      '#suffix' => '</div>',
    );

    $delta = 1;
    $i = 1;
    if (isset($form_state['values']['options']['form']) && !empty($form_state['values']['options']['form']) && !isset($form_state['values']['remove_delta'])) {
      unset($form_state['values']['options']['form']['add_more']);
      if (isset($form_state['values']['options']['form']['facet_limit'])) {
        unset($form_state['values']['options']['form']['facet_settings']);
        unset($form_state['values']['options']['form']['facet_limit']);
      }

      $delta = count($form_state['values']['options']['form']) + 1;
    }
    elseif (isset($form_state['values']['remove_delta'])) {
      unset($form_state['values']['options']['form']['add_more']);
      if (isset($form_state['values']['options']['form']['facet_limit'])) {
        unset($form_state['values']['options']['form']['facet_settings']);
        unset($form_state['values']['options']['form']['facet_limit']);
      }

      $delta = count($form_state['values']['options']['form']);
    }
    elseif (isset($this->options) && !empty($this->options)) {
      if (isset($this->options['facet_limit'])) {
        unset($this->options['facet_limit']);
      }
      $delta = count($this->options);
    }

    for ($c = 0; $c < $delta; $c++) {
      if (isset($form_state['values']['remove_delta']) && $c == $form_state['values']['remove_delta']) {
        unset($form_state['values']['options']['form'][$form_state['values']['remove_delta']]);
        continue;
      }
      else {
        // Daemon settings.
        $form['daemon_settings']['fieldset'][$c] = array(
          '#type' => 'fieldset',
          '#title' => t('Node :id', array(':id' => $i)),
          '#tree' => TRUE,
          '#collapsible' => TRUE,
          '#collapsed' => TRUE,
        );

        // Elasticsearch daemon host.
        $form['daemon_settings']['fieldset'][$c]['host'] = array(
          '#type' => 'textfield',
          '#title' => t('Host'),
          '#description' => t('Host to which the elasticsearch daemon will listen for this server. Default is %default.', array(
            '%default' => $options['host'],
          )),
          '#required' => TRUE,
          '#default_value' => isset($options[$c]['host']) ? $options[$c]['host'] : $options['host'],
          '#parents' => array('options', 'form', $c, 'host'),
        );

        // Elasticsearch daemon port.
        $form['daemon_settings']['fieldset'][$c]['port'] = array(
          '#type' => 'textfield',
          '#title' => t('Port'),
          '#description' => t('Port to which the elasticsearch daemon will listen for this server. Default is %default.', array(
            '%default' => $options['port'],
          )),
          '#required' => TRUE,
          '#default_value' => isset($options[$c]['port']) ? $options[$c]['port'] : $options['port'],
          '#parents' => array('options', 'form', $c, 'port'),
        );

        //Elasticsearch basic authentication.
        $form['daemon_settings']['fieldset'][$c]['headers'] = array(
          '#type' => 'fieldset',
          '#tree' => TRUE,
          '#title' => t('HTTP Basic Authentication'),
          '#description' => t('If your Elasticsearch server is protected by basic HTTP authentication, enter the login data here.'),
        );

        //Elasticsearch basic authentication username.
        $form['daemon_settings']['fieldset'][$c]['headers']['http_user'] = array(
          '#type' => 'textfield',
          '#title' => t('Username'),
          '#default_value' => (!empty($options[$c]['headers']) && !empty($options[$c]['headers']['http_user'])) ? $options[$c]['headers']['http_user'] : '',
          '#parents' => array('options', 'form', $c, 'headers', 'http_user'),
        );

        //Elasticsearch basic password.
        $form['daemon_settings']['fieldset'][$c]['headers']['http_pass'] = array(
          '#type' => 'password',
          '#title' => t('Password'),
          '#parents' => array('options', 'form', $c, 'headers', 'http_pass'),
        );

        // Elasticsearch daemon path.
        $form['daemon_settings']['fieldset'][$c]['path'] = array(
          '#type' => 'textfield',
          '#title' => t('Elasticsearch path prefix'),
          '#description' => t('Normally empty. Use when you have remapped the Elasticsearch server API path.'),
          '#required' => FALSE,
          '#default_value' => isset($options[$c]['path']) ? $options[$c]['path'] : $options['path'],
          '#parents' => array('options', 'form', $c, 'path'),
        );

        // Elasticsearch daemon URL.
        $form['daemon_settings']['fieldset'][$c]['url'] = array(
          '#type' => 'textfield',
          '#title' => t('Elasticsearch url'),
          '#description' => t('Normally empty. Use instead of host/port when you have remapped the Elasticsearch server API url.'),
          '#required' => FALSE,
          '#default_value' => isset($options[$c]['url']) ? $options[$c]['url'] : $options['url'],
          '#parents' => array('options', 'form', $c, 'url'),
        );

        // Elasticsearch daemon transport.
        $form['daemon_settings']['fieldset'][$c]['transport'] = array(
          '#type' => 'select',
          '#title' => t('Select transport'),
          '#description' => t('Transport to connect to this elasticsearch server.'),
          '#options' => $this->getTransportOptions(),
          '#default_value' => isset($options[$c]['transport']) ? $options[$c]['transport'] : $options['transport'],
          '#parents' => array('options', 'form', $c, 'transport'),
        );

        // Elasticsearch daemon persistent.
        $form['daemon_settings']['fieldset'][$c]['persistent'] = array(
          '#type' => 'checkbox',
          '#title' => t('Persistent connection'),
          '#description' => t('Use persistent connection when connecting to this node.'),
          '#default_value' => isset($options[$c]['persistent']) ? $options[$c]['persistent'] : $options['persistent'],
          '#parents' => array('options', 'form', $c, 'persistent'),
        );

        // Elasticsearch daemon timeout.
        $form['daemon_settings']['fieldset'][$c]['timeout'] = array(
          '#type' => 'textfield',
          '#title' => t('Timeout in ms'),
          '#description' => t('Timeout in ms for waiting this elastic server to respond'),
          '#default_value' => isset($options[$c]['timeout']) ? $options[$c]['timeout'] : $options['timeout'],
          '#parents' => array('options', 'form', $c, 'timeout'),
        );

        // Elasticsearch daemon log.
        $form['daemon_settings']['fieldset'][$c]['log'] = array(
          '#type' => 'checkbox',
          '#title' => t('Log'),
          '#description' => t('Log this elasticsearch server queries to the default log.'),
          '#default_value' => isset($options[$c]['log']) ? $options[$c]['log'] : $options['log'],
          '#parents' => array('options', 'form', $c, 'log'),
        );

        if (!class_exists('\Psr\Log\AbstractLogger')) {
          $form['daemon_settings']['fieldset'][$c]['log']['#disabled'] = TRUE;
          $form['daemon_settings']['fieldset'][$c]['log']['#description'] = t('Logging Elasticsearch queries requires the <a href="@psr3_url">PSR-3 logger</a> to be installed and available. It is recommended to install the <a href="@psr3_watchdog_url">PSR-3 Watchdog module</a> or <a href="@monolog_url">Monolog module</a>.', array('@psr_url' => url('https://packagist.org/packages/psr/log'), '@psr3_watchdog_url' => url('https://drupal.org/project/psr3_watchdog'), '@monolog_url' => url('https://www.drupal.org/project/monolog')));
        }

        // Elasticsearch daemon retryOnConflict.
        $form['daemon_settings']['fieldset'][$c]['retryOnConflict'] = array(
          '#type' => 'textfield',
          '#title' => t('retryOnConflict'),
          '#description' => t('Sets the number of retries of a version conflict occurs because the document was updated between getting it and updating it.'),
          '#default_value' => isset($options[$c]['retryOnConflict']) ? $options[$c]['retryOnConflict'] : $options['retryOnConflict'],
          '#parents' => array('options', 'form', $c, 'retryOnConflict'),
        );

        if ((!isset($form_state['values']['remove_delta']) && $delta > 1) || (isset($form_state['values']['remove_delta']) && $delta > 2)) {
          // Elasticsearch daemon retryOnConflict.
          $form['daemon_settings']['fieldset'][$c]['remove_node'] = array(
            '#type' => 'submit',
            '#value' => t('Remove node') . ' ' . $i,
            '#submit' => array('_search_api_elasticsearch_configuration_form_remove_custom'),
            '#ajax' => array(
              'callback' => '_search_api_elasticsearch_configuration_form_remove_ajax',
              'wrapper' => 'elasticsearch-ajax-wrapper',
              'method' => 'replace',
              'effect' => 'fade',
            ),
            '#remove_delta' => $c,
            '#parents' => array('options', 'form', $c, 'remove_node'),
          );
        }
      }
      $i++;
    }

    // Elasticsearch daemon retryOnConflict.
    $form['add_more'] = array(
      '#type' => 'submit',
      '#value' => t('+'),
      '#submit' => array('_search_api_elasticsearch_configuration_form_submit_custom'),
      '#ajax' => array(
        'callback' => '_search_api_elasticsearch_configuration_form_ajax',
        'wrapper' => 'elasticsearch-ajax-wrapper',
        'method' => 'replace',
        'effect' => 'fade',
      ),
    );

    if (module_exists('search_api_facetapi')) {
      // Facet settings.
      $form['facet_settings'] = array(
        '#type' => 'fieldset',
        '#title' => t('Elasticsearch facet settings'),
        '#tree' => FALSE,
      );

      // Elasticsearch facet limit.
      $default = 10;
      $form['facet_settings']['facet_limit'] = array(
        '#type' => 'textfield',
        '#title' => t('Facet limit'),
        '#description' => t("Maximum number of facet elements to be returned by the server if 'no limit' is selected as hard limit is the facet option. Default is %default.", array(
          '%default' => $default,
        )),
        '#required' => TRUE,
        '#default_value' => (isset($options['facet_limit'])) ? $options['facet_limit'] : $default,
        '#parents' => array('options', 'form', 'facet_limit'),
      );
    }

    return $form;
  }

  /**
   * Overrides configurationFormValidate().
   */
  public function configurationFormValidate(array $form, array &$values, array &$form_state) {
    unset($values['add_more']);
    $count_nodes = count($values);

    if (module_exists('search_api_facetapi')) {
      // Facet limit.
      if (filter_var($values['facet_limit'], FILTER_VALIDATE_INT, array('options' => array('min_range' => 0))) === FALSE) {
        form_set_error('options][form][facet_limit', t('You must enter a positive integer for the elasticsearch facet limit.'));
      }
    }

    $options = $this->getOptions();
    foreach ($values as $i => $setting) {
      if ($i != 'facet_limit') {
        // Daemon IP address.
        if (filter_var($values[$i]['host'], FILTER_VALIDATE_IP) === FALSE) {
          form_set_error('options][form]' . $i . '[host', t('You must enter a valid IP address for the elasticsearch daemon.'));
        }

        // Daemon Port.
        if (filter_var($values[$i]['port'], FILTER_VALIDATE_INT, array('options' => array('min_range' => 0, 'max_range' => 65535))) === FALSE) {
          form_set_error('options][form]' . $i . '[port', t('You must enter a valid Port (between 0 and 65535) for the elasticsearch daemon.'));
        }

        $values[$i]['path'] = $this->setPath($values[$i]['path']);
      }

      // Put http_user and http_password in correct form.
      if (!empty($setting['headers']['http_user'])) {
        // If username matches the old value and password is empty, then use the old Authentication.
        if (empty($setting['headers']['http_pass'])) {
          if ($setting['headers']['http_user'] == $options[$i]['headers']['http_user']) {
            $values[$i]['headers']['Authentication'] = $options[$i]['headers']['Authentication'];
          }
          // If username does not match and password is empty, then give a validation error.
          else {
            form_set_error('http_pass', t('If you are changing the username, you need to supply the password.'));
            return;
          }
        }
        else {
          $values[$i]['headers']['Authentication'] = 'Basic ' . base64_encode($setting['headers']['http_user'] . ':' . $setting['headers']['http_pass']);
        }
      }
      if (isset($values[$i]['headers']['http_pass'])) {
        unset($values[$i]['headers']['http_pass']);
      }
    }
  }

  /**
   * Overrides configurationFormSubmit().
   */
  public function configurationFormSubmit(array $form, array &$values, array &$form_state) {
    $facet_limit = '';

    if (isset($values['facet_limit'])) {
      $facet_limit = $values['facet_limit'];
      unset($values['facet_limit']);
    }

    $values = array_values($values);
    $values['facet_limit'] = $facet_limit;
    $this->options = $values;
  }

  /**
   * Helper function. Parse an option form element.
   */
  protected function parseOptionFormElement($element, $key) {
    $children_keys = element_children($element);

    if (!empty($children_keys)) {
      $children = array();
      foreach ($children_keys as $child_key) {
        $child = $this->parseOptionFormElement($element[$child_key], $child_key);
        if (!empty($child)) {
          $children[] = $child;
        }
      }
      if (!empty($children)) {
        return array(
          'label' => isset($element['#title']) ? $element['#title'] : $key,
          'option' => $children,
        );
      }
    }
    elseif (isset($this->options[$key])) {
      return array(
        'label' => isset($element['#title']) ? $element['#title'] : $key,
        'option' => $key,
      );
    }

    return array();
  }

  /**
   * Helper function. Display a setting element.
   */
  protected function viewSettingElement($element) {
    $output = '';

    if (is_array($element['option'])) {
      $value = '';
      foreach ($element['option'] as $sub_element) {
        $value .= $this->viewSettingElement($sub_element);
      }
    }
    else {
      $value = $this->getOption($element['option']);
      $value = nl2br(check_plain(print_r($value, TRUE)));
    }
    $output .= '<dt><em>' . check_plain($element['label']) . '</em></dt>' . "\n";
    $output .= '<dd>' . $value . '</dd>' . "\n";

    return "<dl>\n{$output}</dl>";
  }

  /**
   * Helper function. Get the Elasticsearch mapping for a field.
   */
  protected function getFieldMapping($field) {
    $field_type = (isset($field['real_type'])) ? $field['real_type'] : $field['type'];
    $type = search_api_extract_inner_type($field_type);

    switch ($type) {
      case 'text':
        return array(
          'type' => 'string',
          'boost' => $field['boost'],
        );

      case 'uri':
      case 'string':
      case 'token':
        return array(
          'type' => 'string',
          'index' => 'not_analyzed',
        );

      case 'integer':
      case 'duration':
        return array(
          'type' => 'integer',
        );

      case 'boolean':
        return array(
          'type' => 'boolean',
        );

      case 'decimal':
        return array(
          'type' => 'float',
        );

      case 'date':
        return array(
          'type' => 'date',
          'format' => 'date_time',
        );

      case 'location':
        return array(
          'type' => 'geo_point',
          'lat_lon' => TRUE,
        );

      default:
        return NULL;
    }
  }
  /**
   * Helper function. Return date gap from two dates or timestamps.
   *
   * @see facetapi_get_timestamp_gap()
   */
  protected static function getDateGap($min, $max, $timestamp = TRUE) {
    if ($timestamp !== TRUE) {
      $min = strtotime($min);
      $max = strtotime($max);
    }

    if (empty($min) || empty($max)) {
      return 'DAY';
    }

    $diff = $max - $min;

    switch (TRUE) {
      case ($diff > 86400 * 365):
        return 'NONE';

      case ($diff > 86400 * gmdate('t', $min)):
        return 'YEAR';

      case ($diff > 86400):
        return 'MONTH';

      default:
        return 'DAY';
    }
  }

  /**
   * Helper function. Return server options.
   */
  protected function getOptions() {
    return $this->options;
  }

  /**
   * Helper function. Return a server option.
   */
  protected function getOption($option, $default = NULL) {
    $options = $this->getOptions();
    return isset($options[$option]) ? $options[$option] : $default;
  }

  /**
   * Helper function. Return index fields.
   */
  protected function getIndexFields(SearchApiQueryInterface $query) {
    $index = $query->getIndex();
    $index_fields = $index->getFields();
    return $index_fields;
  }

  /**
   * Helper function that return Sort for query in search.
   */
  protected function getSortSearchQuery(SearchApiQueryInterface $query) {

    $index_fields = $this->getIndexFields($query);
    $sort = array();
    foreach ($query->getSort() as $field_id => $direction) {
      $direction = drupal_strtolower($direction);

      if ($field_id === 'search_api_relevance') {
        $sort['_score'] = $direction;
      }
      elseif (isset($index_fields[$field_id])) {
        $sort[$field_id] = $direction;
      }
      else {
        throw new Exception(t('Incorrect sorting!.'));
      }
    }
    return $sort;
  }

  /**
   * Helper function return Facet filter.
   */
  protected function getFacetSearchFilter(SearchApiQueryInterface $query, $facet_info) {
    $index_fields = $this->getIndexFields($query);
    $facet_search_filter = '';

    if (isset($facet_info['operator']) && drupal_strtolower($facet_info['operator']) == 'or') {
      $facet_search_filter = $this->parseFilter($query->getFilter(), $index_fields, $facet_info['field']);
      if (!empty($facet_search_filter)) {
        $facet_search_filter = $facet_search_filter[0];
      }
    }
    // Normal facet, we just use the main query filters.
    else {
      $facet_search_filter = $this->parseFilter($query->getFilter(), $index_fields);
      if (!empty($facet_search_filter)) {
        $facet_search_filter = $facet_search_filter[0];
      }
    }

    return $facet_search_filter;
  }

  /**
   * Helper function that return facet limits.
   */
  protected function getFacetLimit(array $facet_info) {
    // If no limit (-1) is selected, use the server facet limit option.
    $facet_limit = !empty($facet_info['limit']) ? $facet_info['limit'] : -1;
    if ($facet_limit < 0) {
      $facet_limit = $this->getOption('facet_limit', 10);
    }
    return $facet_limit;
  }

  /**
   * Helper function which add params to date facets.
   */
  protected function getDateFacetInterval($facet_id) {
    // Active search corresponding to this index.
    $searcher = key(facetapi_get_active_searchers());

    // Get the FacetApiAdpater for this searcher.
    $adapter = isset($searcher) ? facetapi_adapter_load($searcher) : NULL;

    // Get the date granularity.
    $date_gap = $this->getDateGranularity($adapter, $facet_id);

    switch ($date_gap) {
      // Already a selected YEAR, we want the months.
      case 'YEAR':
        $date_interval = 'month';
        break;

      // Already a selected MONTH, we want the days.
      case 'MONTH':
        $date_interval = 'day';
        break;

      // Already a selected DAY, we want the hours and so on.
      case 'DAY':
        $date_interval = 'hour';
        break;

      // By default we return result counts by year.
      default:
        $date_interval = 'year';
    }

    return $date_interval;
  }

  /**
   * Helper function to return date gap.
   */
  protected function getDateGranularity($adapter, $facet_id) {
    // Date gaps.
    $gap_weight = array('YEAR' => 2, 'MONTH' => 1, 'DAY' => 0);
    $gaps = array();
    $date_gap = 'YEAR';

    // Get the date granularity.
    if (isset($adapter)) {
      // Get the current date gap from the active date filters.
      $active_items = $adapter->getActiveItems(array('name' => $facet_id));
      if (!empty($active_items)) {
        foreach ($active_items as $active_item) {
          $value = $active_item['value'];
          if (strpos($value, ' TO ') > 0) {
            list($date_min, $date_max) = explode(' TO ', str_replace(array('[', ']'), '', $value), 2);
            $gap = self::getDateGap($date_min, $date_max, FALSE);
            if (isset($gap_weight[$gap])) {
              $gaps[] = $gap_weight[$gap];
            }
          }
        }
        if (!empty($gaps)) {
          // Minimum gap.
          $date_gap = array_search(min($gaps), $gap_weight);
        }
      }
    }

    return $date_gap;
  }

  /**
   * Helper function that parse facets.
   */
  protected function parseSearchFacets($response, SearchApiQueryInterface $query) {

    $result = array();
    $index_fields = $this->getIndexFields($query);
    $facets = $query->getOption('search_api_facets');

    if (!empty($facets) && $response->hasFacets()) {
      foreach ($response->getFacets() as $facet_id => $facet_data) {
        if (isset($facets[$facet_id])) {
          $facet_info = $facets[$facet_id];
          $facet_min_count = $facet_info['min_count'];

          $field_id = $facet_info['field'];
          $field_type = search_api_extract_inner_type($index_fields[$field_id]['type']);

          // TODO: handle different types (GeoDistance and so on).
          if ($field_type === 'date') {
            foreach ($facet_data['entries'] as $entry) {
              if ($entry['count'] >= $facet_min_count) {
                // Divide time by 1000 as we want seconds from epoch
                // not milliseconds.
                $result[$facet_id][] = array(
                  'count' => $entry['count'],
                  'filter' => '"' . ($entry['time'] / 1000) . '"',
                );
              }
            }
          }
          else {
            foreach ($facet_data['terms'] as $term) {
              if ($term['count'] >= $facet_min_count) {
                $result[$facet_id][] = array(
                  'count' => $term['count'],
                  'filter' => '"' . $term['term'] . '"',
                );
              }
            }
          }
        }
      }
    }

    return $result;
  }

  /**
   * Helper function. Return the path in the correct format.
   */
  protected function setPath($path) {
    if (isset($path) && !empty($path)) {
      $trimmed_path = trim($path, '/');
      $path = $trimmed_path . '/';
    }

    return $path;
  }

  /**
   * Helper function. Escape a field or index name.
   *
   * Force names to be strictly alphanumeric-plus-underscore.
   */
  protected static function escapeName($name) {
    return preg_replace('/[^A-Za-z0-9_]+/', '', $name);
  }

  /**
   * Helper function. Get Autocomplete suggestions.
   *
   * @param SearchApiQueryInterface $query
   * @param SearchApiAutocompleteSearch $search
   * @param string $incomplete_key
   * @param string $user_input
   */
  public function getAutocompleteSuggestions(SearchApiQueryInterface $query, SearchApiAutocompleteSearch $search, $incomplete_key, $user_input) {
    $suggestions = array();
    // Turn inputs to lower case, otherwise we get case sensivity problems.
    $incomp = drupal_strtolower($incomplete_key);

    $index = $query->getIndex();
    $index_fields = $this->getIndexFields($query);

    $complete = $query->getOriginalKeys();
    $query->keys($user_input);

    try {
      $response = $this->search($query);
    }
    catch (Exception $e) {
      watchdog('Elasticsearch', check_plain($e->getMessage()), array(), WATCHDOG_ERROR);
      return array();
    }

    $matches = array();
    if (isset($response['results'])) {
      $items = $index->loadItems(array_keys($response['results']));
      foreach ($items as $id => $item) {
        $node_title = $index->datasource()->getItemLabel($item);
        $matches[$node_title] = $node_title;
      }

      if ($matches) {
        // Eliminate suggestions that are too short or already in the query.
        foreach ($matches as $name => $node_title) {
          if (drupal_strlen($name) < 3 || isset($keys_array[$name])) {
            unset($matches[$name]);
          }
        }

        // The $count in this array is actually a score. We want the
        // highest ones first.
        arsort($matches);

        // Shorten the array to the right ones.
        $additional_matches = array_slice($matches, $limit - count($suggestions), NULL, TRUE);
        $matches = array_slice($matches, 0, $limit, TRUE);

        foreach ($matches as $node => $name) {
          $suggestions[] = $name;
        }
      }
      $keys = trim($keys . ' ' . $incomplete_key);
      return $suggestions;
    }
  }

  /**
   * Helper function: Recursively parse Search API filters.
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
            // TODO: handle error. - here is unnecessary cause in if we thow exceptions and this is still in try{}  .
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
   * Helper function that return associative array  of filters info.
   */
  protected function getAssociativeFilter(array $filter_info) {

    $filter_operator = str_replace('!=', '<>', $filter_info[2]);
    return array(
      'field_id' => $filter_info[0],
      'filter_value' => $filter_info[1],
      'filter_operator' => $filter_operator,
    );
  }

  /**
   * Helper function that check if filter is set correct.
   */
  protected function correctFilter($filter_assoc, $index_fields, $ignored_field_id = '') {
    if (!isset($filter_assoc['field_id']) || !isset($filter_assoc['filter_value'])
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
   * Return a full text search query.
   *
   * TODO: better handling of parse modes.
   */
  protected function flattenKeys($keys, $parse_mode = '', $full_text_fields = array()) {
    $conjunction = isset($keys['#conjunction']) ? $keys['#conjunction'] : 'AND';
    $negation = !empty($keys['#negation']);
    $values = array();

    foreach (element_children($keys) as $key) {
      $value = $keys[$key];

      if (empty($value)) {
        continue;
      }

      if (is_array($value)) {
        $values[] = $this->flattenKeys($value);
      }
      elseif (is_string($value)) {
        // If parse mode is not "direct": quote the keyword.
        if ($parse_mode !== 'direct') {
          $value = '"' . $value . '"';
        }

        $values[] = $value;
      }
    }
    if (!empty($values)) {
      return ($negation === TRUE ? 'NOT ' : '') . '(' . implode(" {$conjunction} ", $values) . ')';
    }
    else {
      return '';
    }
  }

  /**
   * Helper function. Returns the elasticsearch name of an index.
   */
  protected function getIndexName(SearchApiIndex $index) {
    global $databases;

    $site_database = $databases['default']['default']['database'];

    $index_machine_name = is_string($index) ? $index : $index->machine_name;

    return self::escapeName('elasticsearch_index_' . $site_database . '_' . $index_machine_name);
  }

  /**
   * Overrides fieldsUpdated().
   *
   * We only do the grunt work of building the array of properties. This allows
   * submodules who just need an array of properties to simply call this as a
   * parent method to have the array built for them, thus implementing the DRY
   * principle. The submodule is responsible for the return value.
   *
   * @param SearchApiIndex $index
   *   The Search API index.
   */
  public function fieldsUpdated(SearchApiIndex $index) {
    $this->fieldsUpdatedProperties = array(
      'id' => array('type' => 'integer', 'include_in_all' => FALSE),
    );
    foreach ($index->getFields() as $field_id => $field_data) {
      $this->fieldsUpdatedProperties[$field_id] = $this->getFieldMapping($field_data);
    }

    // Allow other modules to alter properties.
    drupal_alter('search_api_elasticsearch_fields_updated', $index, $this->fieldsUpdatedProperties);
  }

  /**
   * Get analyzers for an Elasticsearch index.
   *
   * @param SearchApiIndex $index
   *   A Search API index object.
   *
   * @return array | bool
   *   An array of available analyzers. FALSE if none.
   */
  public function getAnalysisSettings(SearchApiIndex $index) {
    $settings = $this->getSettings($index);
    return isset($settings['analysis']) ? $settings['analysis'] : FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getExtraInformation() {
    $info = array();

    $cluster_health = $this->getClusterHealth();
    if (!empty($cluster_health)) {
      $info[] = array(
        'label' => t('Cluster Name'),
        'info' => $cluster_health['cluster_name'],
      );
      $info[] = array(
        'label' => t('Cluster Status'),
        'info' => $cluster_health['status'],
      );
      $info[] = array(
        'label' => t('Number of Nodes'),
        'info' => $cluster_health['number_of_nodes'],
      );
      $info[] = array(
        'label' => t('Number of Data Nodes'),
        'info' => $cluster_health['number_of_data_nodes'],
      );
      $info[] = array(
        'label' => t('Active Primary Shards'),
        'info' => $cluster_health['active_primary_shards'],
      );
      $info[] = array(
        'label' => t('Active Shards'),
        'info' => $cluster_health['active_shards'],
      );
      $info[] = array(
        'label' => t('Relocating Shards'),
        'info' => $cluster_health['relocating_shards'],
      );
      $info[] = array(
        'label' => t('Initializing Shards'),
        'info' => $cluster_health['initializing_shards'],
      );
      $info[] = array(
        'label' => t('Unassigned Shards'),
        'info' => $cluster_health['unassigned_shards'],
      );
    }

    return $info;
  }
}
