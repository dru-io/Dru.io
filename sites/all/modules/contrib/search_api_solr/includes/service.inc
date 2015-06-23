<?php

/**
 * Search service class using Solr server.
 */
class SearchApiSolrService extends SearchApiAbstractService {

  /**
   * The date format that Solr uses, in PHP date() syntax.
   */
  const SOLR_DATE_FORMAT = 'Y-m-d\TH:i:s\Z';

  /**
   * The connection class used by this service.
   *
   * Must implement SearchApiSolrConnectionInterface.
   *
   * @var string
   */
  protected $connection_class = 'SearchApiSolrConnection';

  /**
   * A connection to the Solr server.
   *
   * @var SearchApiSolrConnectionInterface
   */
  protected $solr;

  /**
   * Static cache for getFieldNames().
   *
   * @var array
   */
  protected $fieldNames = array();

  /**
   * Metadata describing fields on the Solr/Lucene index.
   *
   * @see SearchApiSolrService::getFields().
   *
   * @var array
   */
  protected $fields;

  /**
   * Saves whether a commit operation was already scheduled for this server.
   *
   * @var bool
   */
  protected $commitScheduled = FALSE;

  /**
   * Request handler to use for this search query.
   *
   * @var string
   */
  protected $request_handler = NULL;

  /**
   * Overrides SearchApiAbstractService::configurationForm().
   */
  public function configurationForm(array $form, array &$form_state) {
    if ($this->options) {
      // Editing this server
      $form['server_description'] = array(
        '#type' => 'item',
        '#title' => t('Solr server URI'),
        '#description' => $this->getServerLink(),
      );
    }

    $options = $this->options + array(
      'scheme' => 'http',
      'host' => 'localhost',
      'port' => '8983',
      'path' => '/solr',
      'http_user' => '',
      'http_pass' => '',
      'excerpt' => FALSE,
      'retrieve_data' => FALSE,
      'highlight_data' => FALSE,
      'skip_schema_check' => FALSE,
      'solr_version' => '',
      'http_method' => 'AUTO',
      // Default to TRUE for new servers, but to FALSE for existing ones.
      'clean_ids' => $this->options ? FALSE : TRUE,
      'site_hash' => $this->options ? FALSE : TRUE,
      'autocorrect_spell' => TRUE,
      'autocorrect_suggest_words' => TRUE,
    );

    if (!$options['clean_ids']) {
      if (module_exists('advanced_help')) {
        $variables['@url'] = url('help/search_api_solr/README.txt');
      }
      else {
        $variables['@url'] = url(drupal_get_path('module', 'search_api_solr') . '/README.txt');
      }
      $description = t('Change Solr field names to be more compatible with advanced features. Doing this leads to re-indexing of all indexes on this server. See <a href="@url">README.txt</a> for details.', $variables);
      $form['clean_ids_form'] = array(
        '#type' => 'fieldset',
        '#title' => t('Clean field identifiers'),
        '#description' => $description,
        '#collapsible' => TRUE,
      );
      $form['clean_ids_form']['submit'] = array(
        '#type' => 'submit',
        '#value' => t('Switch to clean field identifiers'),
        '#submit' => array('_search_api_solr_switch_to_clean_ids'),
      );
    }
    $form['clean_ids'] = array(
      '#type' => 'value',
      '#value' => $options['clean_ids'],
    );

    if (!$options['site_hash']) {
      $description = t('If you want to index content from multiple sites on a single Solr server, you should enable the multi-site compatibility here. Note, however, that this will completely clear all search indexes (from this site) lying on this server. All content will have to be re-indexed.');
      $form['site_hash_form'] = array(
        '#type' => 'fieldset',
        '#title' => t('Multi-site compatibility'),
        '#description' => $description,
        '#collapsible' => TRUE,
      );
      $form['site_hash_form']['submit'] = array(
        '#type' => 'submit',
        '#value' => t('Turn on multi-site compatibility and clear all indexes'),
        '#submit' => array('_search_api_solr_switch_to_site_hash'),
      );
    }
    $form['site_hash'] = array(
      '#type' => 'value',
      '#value' => $options['site_hash'],
    );

    $form['scheme'] = array(
      '#type' => 'select',
      '#title' => t('HTTP protocol'),
      '#description' => t('The HTTP protocol to use for sending queries.'),
      '#default_value' => $options['scheme'],
      '#options' => array(
        'http' => 'http',
        'https' => 'https',
      ),
    );

    $form['host'] = array(
      '#type' => 'textfield',
      '#title' => t('Solr host'),
      '#description' => t('The host name or IP of your Solr server, e.g. <code>localhost</code> or <code>www.example.com</code>.'),
      '#default_value' => $options['host'],
      '#required' => TRUE,
    );
    $form['port'] = array(
      '#type' => 'textfield',
      '#title' => t('Solr port'),
      '#description' => t('The Jetty example server is at port 8983, while Tomcat uses 8080 by default.'),
      '#default_value' => $options['port'],
      '#required' => TRUE,
    );
    $form['path'] = array(
      '#type' => 'textfield',
      '#title' => t('Solr path'),
      '#description' => t('The path that identifies the Solr instance to use on the server. (For Solr 4.x servers, this should include the name of the core to use.)'),
      '#default_value' => $options['path'],
    );

    $form['http'] = array(
      '#type' => 'fieldset',
      '#title' => t('Basic HTTP authentication'),
      '#description' => t('If your Solr server is protected by basic HTTP authentication, enter the login data here.'),
      '#collapsible' => TRUE,
      '#collapsed' => empty($options['http_user']),
    );
    $form['http']['http_user'] = array(
      '#type' => 'textfield',
      '#title' => t('Username'),
      '#default_value' => $options['http_user'],
    );
    $form['http']['http_pass'] = array(
      '#type' => 'password',
      '#title' => t('Password'),
      '#description' => t('If this field is left blank and the HTTP username is filled out, the current password will not be changed.'),
    );

    $form['advanced'] = array(
      '#type' => 'fieldset',
      '#title' => t('Advanced'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
    );
    $form['advanced']['excerpt'] = array(
      '#type' => 'checkbox',
      '#title' => t('Return an excerpt for all results'),
      '#description' => t("If search keywords are given, use Solr's capabilities to create a highlighted search excerpt for each result. " .
          'Whether the excerpts will actually be displayed depends on the settings of the search, though.'),
      '#default_value' => $options['excerpt'],
    );
    $form['advanced']['retrieve_data'] = array(
      '#type' => 'checkbox',
      '#title' => t('Retrieve result data from Solr'),
      '#description' => t('When checked, result data will be retrieved directly from the Solr server. ' .
          'This might make item loads unnecessary. Only indexed fields can be retrieved. ' .
          'Note also that the returned field data might not always be correct, due to preprocessing and caching issues.'),
      '#default_value' => $options['retrieve_data'],
    );
    $form['advanced']['highlight_data'] = array(
      '#type' => 'checkbox',
      '#title' => t('Highlight retrieved data'),
      '#description' => t('When retrieving result data from the Solr server, try to highlight the search terms in the returned fulltext fields.'),
      '#default_value' => $options['highlight_data'],
    );
    $form['advanced']['skip_schema_check'] = array(
      '#type' => 'checkbox',
      '#title' => t('Skip schema verification'),
      '#description' => t('Skip the automatic check for schema-compatibillity. Use this override if you are seeing an error-message about an incompatible schema.xml configuration file, and you are sure the configuration is compatible.'),
      '#default_value' => $options['skip_schema_check'],
    );
    $form['advanced']['solr_version'] = array(
      '#type' => 'select',
      '#title' => t('Solr version override'),
      '#description' => t('Specify the Solr version manually in case it cannot be retrived automatically. The version can be found in the Solr admin interface under "Solr Specification Version" or "solr-spec"'),
      '#options' => array(
        '' => t('Determine automatically'),
        '1' => '1.4',
        '3' => '3.x',
        '4' => '4.x',
      ),
      '#default_value' => $options['solr_version'],
    );
    // Highlighting retrieved data only makes sense when we retrieve data.
    // (Actually, internally it doesn't really matter. However, from a user's
    // perspective, having to check both probably makes sense.)
    $form['advanced']['highlight_data']['#states']['invisible']
        [':input[name="options[form][advanced][retrieve_data]"]']['checked'] = FALSE;

    $form['advanced']['http_method'] = array(
      '#type' => 'select',
      '#title' => t('HTTP method'),
      '#description' => t('The HTTP method to use for sending queries. GET will often fail with larger queries, while POST should not be cached. AUTO will use GET when possible, and POST for queries that are too large.'),
      '#default_value' => $options['http_method'],
      '#options' => array(
        'AUTO' => t('AUTO'),
        'POST' => 'POST',
        'GET' => 'GET',
      ),
    );

    if (module_exists('search_api_autocomplete')) {
      $form['advanced']['autocomplete'] = array(
        '#type' => 'fieldset',
        '#title' => t('Autocomplete'),
        '#collapsible' => TRUE,
        '#collapsed' => TRUE,
      );
      $form['advanced']['autocomplete']['autocorrect_spell'] = array(
        '#type' => 'checkbox',
        '#title' => t('Use spellcheck for autocomplete suggestions'),
        '#description' => t('If activated, spellcheck suggestions ("Did you mean") will be included in the autocomplete suggestions. Since the used dictionary contains words from all indexes, this might lead to leaking of sensitive data, depending on your setup.'),
        '#default_value' => $options['autocorrect_spell'],
      );
      $form['advanced']['autocomplete']['autocorrect_suggest_words'] = array(
        '#type' => 'checkbox',
        '#title' => t('Suggest additional words'),
        '#description' => t('If activated and the user enters a complete word, Solr will suggest additional words the user wants to search, which are often found (not searched!) together. This has been known to lead to strange results in some configurations – if you see inappropriate additional-word suggestions, you might want to deactivate this option.'),
        '#default_value' => $options['autocorrect_suggest_words'],
      );
    }

    return $form;
  }

  /**
   * Overrides SearchApiAbstractService::configurationFormValidate().
   */
  public function configurationFormValidate(array $form, array &$values, array &$form_state) {
    if (isset($values['port']) && (!is_numeric($values['port']) || $values['port'] < 0 || $values['port'] > 65535)) {
      form_error($form['port'], t('The port has to be an integer between 0 and 65535.'));
    }
  }

  /**
   * Overrides SearchApiAbstractService::configurationFormSubmit().
   */
  public function configurationFormSubmit(array $form, array &$values, array &$form_state) {
    // Since the form is nested into another, we can't simply use #parents for
    // doing this array restructuring magic. (At least not without creating an
    // unnecessary dependency on internal implementation.)
    $values += $values['http'];
    $values += $values['advanced'];
    $values += !empty($values['autocomplete']) ? $values['autocomplete'] : array();
    unset($values['http'], $values['advanced'], $values['autocomplete']);

    // Highlighting retrieved data only makes sense when we retrieve data.
    $values['highlight_data'] &= $values['retrieve_data'];

    // For password fields, there is no default value, they're empty by default.
    // Therefore we ignore empty submissions if the user didn't change either.
    if ($values['http_pass'] === ''
        && isset($this->options['http_user'])
        && $values['http_user'] === $this->options['http_user']) {
      $values['http_pass'] = $this->options['http_pass'];
    }

    parent::configurationFormSubmit($form, $values, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function supportsFeature($feature) {
    // First, check the features we always support.
    $supported = drupal_map_assoc(array(
      'search_api_autocomplete',
      'search_api_facets',
      'search_api_facets_operator_or',
      'search_api_grouping',
      'search_api_mlt',
      'search_api_multi',
      'search_api_service_extra',
      'search_api_spellcheck',
      'search_api_data_type_location',
      'search_api_data_type_geohash',
    ));
    if (isset($supported[$feature])) {
      return TRUE;
    }

    // If it is a custom data type, maybe we support it automatically via
    // search_api_solr_hook_search_api_data_type_info().
    if (substr($feature, 0, 21) != 'search_api_data_type_') {
      return FALSE;
    }
    $type = substr($feature, 21);
    $type = search_api_get_data_type_info($type);
    // We only support it if the "prefix" key is set.
    return $type && !empty($type['prefix']);
  }

  /**
   * Overrides SearchApiAbstractService::viewSettings().
   *
   * Returns an empty string since information is instead added via
   * getExtraInformation().
   */
  public function viewSettings() {
    return '';
  }

  /**
   * {@inheritdoc}
   */
  public function getExtraInformation() {
    $info = array();

    $info[] = array(
      'label' => t('Solr server URI'),
      'info' => $this->getServerLink(),
    );

    if ($this->options['http_user']) {
      $vars = array(
        '@user' => $this->options['http_user'],
        '@pass' => str_repeat('*', strlen($this->options['http_pass'])),
      );
      $http = t('Username: @user; Password: @pass', $vars);
      $info[] = array(
        'label' => t('Basic HTTP authentication'),
        'info' => $http,
      );
    }

    if ($this->server->enabled) {
      // If the server is enabled, check whether Solr can be reached.
      $ping = $this->ping();
      if ($ping) {
        $msg = t('The Solr server could be reached (latency: @millisecs ms).', array('@millisecs' => $ping * 1000));
      }
      else {
        $msg = t('The Solr server could not be reached. Further data is therefore unavailable.');
      }
      $info[] = array(
        'label' => t('Connection'),
        'info' => $msg,
        'status' => $ping ? 'ok' : 'error',
      );

      if ($ping) {
        try {
          // If Solr can be reached, provide more information. This isn't done
          // often (only when an admin views the server details), so we clear the
          // cache to get the current data.
          $this->connect();
          $this->solr->clearCache();
          $data = $this->solr->getLuke();
          if (isset($data->index->numDocs)) {
            // Collect the stats
            $stats_summary = $this->solr->getStatsSummary();

            $pending_msg = $stats_summary['@pending_docs'] ? t('(@pending_docs sent but not yet processed)', $stats_summary) : '';
            $index_msg = $stats_summary['@index_size'] ? t('(@index_size on disk)', $stats_summary) : '';
            $indexed_message = t('@num items !pending !index_msg', array(
              '@num' => $data->index->numDocs,
              '!pending' => $pending_msg,
              '!index_msg' => $index_msg,
            ));
            $info[] = array(
              'label' => t('Indexed'),
              'info' => $indexed_message,
            );

            if (!empty($stats_summary['@deletes_total'])) {
              $info[] = array(
                'label' => t('Pending Deletions'),
                'info' => $stats_summary['@deletes_total'],
              );
            }

            $info[] = array(
              'label' => t('Delay'),
              'info' => t('@autocommit_time before updates are processed.', $stats_summary),
            );

            $status = 'ok';
            if (empty($this->options['skip_schema_check'])) {
              if (substr($stats_summary['@schema_version'], 0, 10) == 'search-api') {
                drupal_set_message(t('Your schema.xml version is too old. Please replace all configuration files with the ones packaged with this module and re-index you data.'), 'error');
                $status = 'error';
              }
              elseif (substr($stats_summary['@schema_version'], 0, 9) != 'drupal-4.') {
                $variables['@url'] = url(drupal_get_path('module', 'search_api_solr') . '/INSTALL.txt');
                $message = t('You are using an incompatible schema.xml configuration file. Please follow the instructions in the <a href="@url">INSTALL.txt</a> file for setting up Solr.', $variables);
                drupal_set_message($message, 'error');
                $status = 'error';
              }
            }
            $info[] = array(
              'label' => t('Schema'),
              'info' => $stats_summary['@schema_version'],
              'status' => $status,
            );

            if (!empty($stats_summary['@core_name'])) {
              $info[] = array(
                'label' => t('Solr Core Name'),
                'info' => $stats_summary['@core_name'],
              );
            }
          }
        }
        catch (SearchApiException $e) {
          $info[] = array(
            'label' => t('Additional information'),
            'info' => t('An error occurred while trying to retrieve additional information from the Solr server: @msg.', array('@msg' => $e->getMessage())),
            'status' => 'error',
          );
        }
      }
    }

    return $info;
  }

  /**
   * Returns a link to the Solr server, if the necessary options are set.
   */
  public function getServerLink() {
    if (!$this->options) {
      return '';
    }
    $host = $this->options['host'];
    if ($host == 'localhost' && !empty($_SERVER['SERVER_NAME'])) {
      $host = $_SERVER['SERVER_NAME'];
    }
    $url = $this->options['scheme'] . '://' . $host . ':' . $this->options['port'] . $this->options['path'];
    return l($url, $url);
  }

  /**
   * Create a connection to the Solr server as configured in $this->options.
   */
  protected function connect() {
    if (!$this->solr) {
      if (!class_exists($this->connection_class)) {
        throw new SearchApiException(t('Invalid class @class set as Solr connection class.', array('@class' => $this->connection_class)));
      }
      $options = $this->options + array('server' => $this->server->machine_name);
      $this->solr = new $this->connection_class($options);
      if (!($this->solr instanceof SearchApiSolrConnectionInterface)) {
        $this->solr = NULL;
        throw new SearchApiException(t('Invalid class @class set as Solr connection class.', array('@class' => $this->connection_class)));
      }
    }
  }

  /**
   * Overrides SearchApiAbstractService::addIndex().
   */
  public function addIndex(SearchApiIndex $index) {
    if (module_exists('search_api_multi') && module_exists('search_api_views')) {
      views_invalidate_cache();
    }
  }

  /**
   * Overrides SearchApiAbstractService::fieldsUpdated().
   */
  public function fieldsUpdated(SearchApiIndex $index) {
    if (module_exists('search_api_multi') && module_exists('search_api_views')) {
      views_invalidate_cache();
    }
    // Find out if anything changed enough to need re-indexing.
    $old_fields = isset($index->original->options['fields']) ? $index->original->options['fields'] : array();
    $new_fields = isset($index->options['fields']) ? $index->options['fields'] : array();
    if (!$old_fields && !$new_fields) {
      return FALSE;
    }
    if (array_diff_key($old_fields, $new_fields) || array_diff_key($new_fields, $old_fields)) {
      return TRUE;
    }
    $old_field_names = $this->getFieldNames($index->original, TRUE);
    $new_field_names = $this->getFieldNames($index, TRUE);
    return $old_field_names != $new_field_names;
  }

  /**
   * Overrides SearchApiAbstractService::removeIndex().
   */
  public function removeIndex($index) {
    if (module_exists('search_api_multi') && module_exists('search_api_views')) {
      views_invalidate_cache();
    }

    parent::removeIndex($index);
  }

  /**
   * Implements SearchApiServiceInterface::indexItems().
   */
  public function indexItems(SearchApiIndex $index, array $items) {
    $documents = array();
    $ret = array();
    $index_id = $this->getIndexId($index->machine_name);
    $fields = $this->getFieldNames($index);
    $languages = language_list();
    $base_urls = array();

    foreach ($items as $id => $item) {
      $doc = new SearchApiSolrDocument();
      $doc->setField('id', $this->createId($index_id, $id));
      $doc->setField('index_id', $index_id);
      $doc->setField('item_id', $id);

      // If multi-site compatibility is enabled, add the site hash and
      // language-specific base URL.
      if (!empty($this->options['site_hash'])) {
        $doc->setField('hash', search_api_solr_site_hash());
        $lang = $item['search_api_language']['value'];
        if (empty($base_urls[$lang])) {
          $url_options = array('absolute' => TRUE);
          if (isset($languages[$lang])) {
            $url_options['language'] = $languages[$lang];
          }
          $base_urls[$lang] = url(NULL, $url_options);
        }
        $doc->setField('site', $base_urls[$lang]);
      }

      // Now add all fields contained in the item, with dynamic fields. Also,
      // gather the contents of all text fields to also add them to "content".
      $text_content = '';
      foreach ($item as $key => $field) {
        // If the field is not known for the index, something weird has
        // happened. We refuse to index the items and hope that the others are
        // OK.
        if (!isset($fields[$key])) {
          $type = search_api_get_item_type_info($index->item_type);
          $vars = array(
            '@field' => $key,
            '@type' => $type ? $type['name'] : $index->item_type,
            '@id' => $id,
          );
          watchdog('search_api_solr', 'Error while indexing: Unknown field @field set for @type with ID @id.', $vars, WATCHDOG_WARNING);
          $doc = NULL;
          break;
        }
        $text_content[] = $this->addIndexField($doc, $fields[$key], $field['value'], $field['type']);
      }
      $doc->setField('content', implode("\n\n", array_filter($text_content)));

      if ($doc) {
        $documents[] = $doc;
        $ret[] = $id;
      }
    }

    // Let other modules alter documents before sending them to solr.
    drupal_alter('search_api_solr_documents', $documents, $index, $items);
    $this->alterSolrDocuments($documents, $index, $items);

    if (!$documents) {
      return array();
    }
    try {
      $this->connect();
      $this->solr->addDocuments($documents);
      if (!empty($index->options['index_directly'])) {
        $this->scheduleCommit();
      }
      return $ret;
    }
    catch (SearchApiException $e) {
      watchdog_exception('search_api_solr', $e, "%type while indexing: !message in %function (line %line of %file).");
    }
    return array();
  }

  /**
   * Creates an ID used as the unique identifier at the Solr server.
   *
   * This has to consist of both index and item ID. Optionally, the site hash is
   * also included.
   *
   * @see search_api_solr_site_hash()
   */
  protected function createId($index_id, $item_id) {
    $site_hash = !empty($this->options['site_hash']) ? search_api_solr_site_hash() . '-' : '';
    return "$site_hash$index_id-$item_id";
  }

  /**
   * Create a list of all indexed field names mapped to their Solr field names.
   *
   * The special fields "search_api_id" and "search_api_relevance" are also
   * included. Any Solr fields that exist on search results are mapped back to
   * to their local field names in the final result set.
   *
   * @see SearchApiSolrService::search()
   */
  public function getFieldNames(SearchApiIndex $index, $reset = FALSE) {
    if (!isset($this->fieldNames[$index->machine_name]) || $reset) {
      // This array maps "local property name" => "solr doc property name".
      $ret = array(
        'search_api_id' => 'item_id',
        'search_api_relevance' => 'score',
      );

      // Add the names of any fields configured on the index.
      $fields = (isset($index->options['fields']) ? $index->options['fields'] : array());
      foreach ($fields as $key => $field) {
        // Generate a field name; this corresponds with naming conventions in
        // our schema.xml
        $type = $field['type'];

        // Use the real type of the field if the server supports this type.
        if (isset($field['real_type'])) {
          $custom_type = search_api_extract_inner_type($field['real_type']);
          if ($this->supportsFeature('search_api_data_type_' . $custom_type)) {
            $type = $field['real_type'];
          }
        }

        $inner_type = search_api_extract_inner_type($type);
        $type_info = search_api_solr_get_data_type_info($inner_type);
        $pref = isset($type_info['prefix']) ? $type_info['prefix']: '';
        if (empty($type_info['always multiValued'])) {
          $pref .= ($type == $inner_type) ? 's' : 'm';
        }
        if (!empty($this->options['clean_ids'])) {
          $name = $pref . '_' . str_replace(':', '$', $key);
        }
        else {
          $name = $pref . '_' . $key;
        }

        $ret[$key] = $name;
      }

      // Let modules adjust the field mappings.
      drupal_alter('search_api_solr_field_mapping', $index, $ret);

      $this->fieldNames[$index->machine_name] = $ret;
    }

    return $this->fieldNames[$index->machine_name];
  }

  /**
   * Helper method for indexing.
   *
   * Adds $value with field name $key to the document $doc. The format of $value
   * is the same as specified in SearchApiServiceInterface::indexItems().
   */
  protected function addIndexField(SearchApiSolrDocument $doc, $key, $value, $type, $multi_valued = FALSE) {
    $text_content = '';
    // Don't index empty values (i.e., when field is missing).
    if (!isset($value)) {
      return $text_content;
    }
    if (search_api_is_list_type($type)) {
      $type = substr($type, 5, -1);
      foreach ($value as $v) {
        $text_content .= $this->addIndexField($doc, $key, $v, $type, TRUE) . "\n\n";
      }
      return trim($text_content);
    }
    switch ($type) {
      case 'tokens':
        foreach ($value as $v) {
          $text_content .= $v['value'] . ' ';
          $doc->addField($key, $v['value']);
        }
        return trim($text_content);
      case 'boolean':
        $value = $value ? 'true' : 'false';
        break;
      case 'date':
        $value = is_numeric($value) ? (int) $value : strtotime($value);
        if ($value === FALSE) {
          return $text_content;
        }
        $value = format_date($value, 'custom', self::SOLR_DATE_FORMAT, 'UTC');
        break;
      case 'integer':
        $value = (int) $value;
        break;
      case 'decimal':
        $value = (float) $value;
        break;
    }
    if ($multi_valued) {
      $doc->addField($key, $value);
    }
    else {
      $doc->setField($key, $value);
    }
    if (search_api_is_text_type($type)) {
      $text_content = $value;
    }
    return $text_content;
  }

  /**
   * Applies custom modifications to indexed Solr documents.
   *
   * This method allows subclasses to easily apply custom changes before the
   * documents are sent to Solr. The method is empty by default.
   *
   * @param array $documents
   *   An array of SearchApiSolrDocument objects ready to be indexed, generated
   *   from $items array.
   * @param SearchApiIndex $index
   *   The search index for which items are being indexed.
   * @param array $items
   *   An array of items being indexed.
   *
   * @see hook_search_api_solr_documents_alter()
   */
  protected function alterSolrDocuments(array &$documents, SearchApiIndex $index, array $items) {
  }

  /**
   * Implements SearchApiServiceInterface::deleteItems().
   *
   * This method has a custom, Solr-specific extension:
   *
   * If $ids is a string other than "all", it is treated as a Solr query. All
   * items matching that Solr query are then deleted. If $index is additionally
   * specified, then only those items also lying on that index will be deleted.
   *
   * It is up to the caller to ensure $ids is a valid query when the method is
   * called in this fashion.
   */
  public function deleteItems($ids = 'all', SearchApiIndex $index = NULL) {
    $this->connect();
    if (is_array($ids)) {
      $index_id = $this->getIndexId($index->machine_name);
      $solr_ids = array();
      foreach ($ids as $id) {
        $solr_ids[] = $this->createId($index_id, $id);
      }
      $this->solr->deleteByMultipleIds($solr_ids);
    }
    else {
      $query = array();
      if ($index) {
        $index_id = $this->getIndexId($index->machine_name);
        $index_id = call_user_func(array($this->connection_class, 'phrase'), $index_id);
        $query[] = "index_id:$index_id";
      }
      if (!empty($this->options['site_hash'])) {
        // We don't need to escape the site hash, as that consists only of
        // alphanumeric characters.
        $query[] = 'hash:' . search_api_solr_site_hash();
      }
      if ($ids != 'all') {
        $query[] = $query ? "($ids)" : $ids;
      }
      $this->solr->deleteByQuery($query ? implode(' AND ', $query) : '*:*');
    }
    $this->scheduleCommit();
  }

  /**
   * Implements SearchApiServiceInterface::search().
   */
  public function search(SearchApiQueryInterface $query) {
    $time_method_called = microtime(TRUE);
    // Reset request handler.
    $this->request_handler = NULL;
    // Get field information.
    $index = $query->getIndex();
    $index_id = $this->getIndexId($index->machine_name);
    $fields = $this->getFieldNames($index);
    // Get Solr connection.
    $this->connect();
    $version = $this->solr->getSolrVersion();

    // Extract keys.
    $keys = $query->getKeys();
    if (is_array($keys)) {
      $keys = $this->flattenKeys($keys);
    }

    // Set searched fields.
    $options = $query->getOptions();
    $search_fields = $query->getFields();
    // Get the index fields to be able to retrieve boosts.
    $index_fields = $index->getFields();
    $qf = array();
    foreach ($search_fields as $f) {
      $boost = '';
      $boost = isset($index_fields[$f]['boost']) ? '^' . $index_fields[$f]['boost'] : '';
      $qf[] = $fields[$f] . $boost;
    }

    // Extract filters.
    $filter = $query->getFilter();
    $fq = $this->createFilterQueries($filter, $fields, $index->options['fields']);
    $fq[] = 'index_id:' . call_user_func(array($this->connection_class, 'phrase'), $index_id);
    if (!empty($this->options['site_hash'])) {
      // We don't need to escape the site hash, as that consists only of
      // alphanumeric characters.
      $fq[] = 'hash:' . search_api_solr_site_hash();
    }

    // Extract sort.
    $sort = array();
    foreach ($query->getSort() as $field => $order) {
      $f = $fields[$field];
      if (substr($f, 0, 3) == 'ss_') {
        $f = 'sort_' . substr($f, 3);
      }
      $order = strtolower($order);
      $sort[$field] = "$f $order";
    }

    // Get facet fields.
    $facets = $query->getOption('search_api_facets', array());
    $facet_params = $this->getFacetParams($facets, $fields, $fq);

    // Handle highlighting.
    $highlight_params = $this->getHighlightParams($query);

    // Handle More Like This query.
    $mlt = $query->getOption('search_api_mlt');
    if ($mlt) {
      $mlt_params['qt'] = 'mlt';
      // The fields to look for similarities in.
      $mlt_fl = array();
      foreach($mlt['fields'] as $f) {
        // Solr 4 has a bug which results in numeric fields not being supported
        // in MLT queries.
        // Date fields don't seem to be supported at all.
        if ($fields[$f][0] === 'd' || ($version == 4 && in_array($fields[$f][0], array('i', 'f')))) {
          continue;
        }
        $mlt_fl[] = $fields[$f];
        // For non-text fields, set minimum word length to 0.
        if (isset($index->options['fields'][$f]['type']) && !search_api_is_text_type($index->options['fields'][$f]['type'])) {
          $mlt_params['f.' . $fields[$f] . '.mlt.minwl'] = 0;
        }
      }
      $mlt_params['mlt.fl'] = implode(',', $mlt_fl);
      $id = $this->createId($index_id, $mlt['id']);
      $id = call_user_func(array($this->connection_class, 'phrase'), $id);
      $keys = 'id:' . $id;
    }

    // Handle spatial filters.
    if ($spatials = $query->getOption('search_api_location')) {
      foreach ($spatials as $i => $spatial) {
        // Spatial options all need a field to do anything.
        if (!isset($spatial['field'])) {
          continue;
        }

        unset($radius);
        $field = $fields[$spatial['field']];
        $escaped_field = SearchApiSolrConnection::escapeFieldName($field);

        // If proper bbox coordinates were given use them to filter.
        if (isset($spatial['bbox'])) {
          if ($version >= 4) {
            $bbox = $spatial['bbox'];
            $fq[] = $escaped_field . ':[' . (float) $bbox['bottom'] . ',' . (float) $bbox['left'] . ' TO ' . (float) $bbox['top'] . ',' . (float) $bbox['right'] . ']';
          }
          else {
            $warnings[] = t('Filtering by a bounding box is not supported in Solr versions below 4.');
          }
        }

        // Everything other than a bounding box filter requires a point, so stop
        // here (for this option) if "lat" and "lon" aren't both set.
        if (!isset($spatial['lat']) || !isset($spatial['lon'])) {
          continue;
        }
        $point = ((float) $spatial['lat']) . ',' . ((float) $spatial['lon']);

        // Prepare the filter settings.
        if (isset($spatial['radius'])) {
          $radius = (float) $spatial['radius'];
        }
        $spatial_method = 'geofilt';
        if (isset($spatial['method']) && in_array($spatial['method'], array('geofilt', 'bbox'))) {
          $spatial_method = $spatial['method'];
        }

        // Change the fq facet ranges to the correct fq.
        foreach ($fq as $key => $value) {
          // If the fq consists only of a filter on this field, replace it with
          // a range.
          $preg_field = preg_quote($escaped_field, '/');
          if (preg_match('/^' . $preg_field . ':\["?(\*|\d+(?:\.\d+)?)"? TO "?(\*|\d+(?:\.\d+)?)"?\]$/', $value, $m)) {
            unset($fq[$key]);
            if ($m[1] && is_numeric($m[1])) {
              $min_radius = isset($min_radius) ? max($min_radius, $m[1]) : $m[1];
            }
            if (is_numeric($m[2])) {
              // Make the radius tighter accordingly.
              $radius = isset($radius) ? min($radius, $m[2]) : $m[2];
            }
          }
        }

        // If either a radius was given in the option, or a filter was
        // encountered, set a filter for the lowest value. If a lower boundary
        // was set (too), we can only set a filter for that if the field name
        // doesn't contains any colons.
        if (isset($min_radius) && strpos($field, ':') === FALSE) {
          $upper = isset($radius) ? " u=$radius" : '';
          $fq[] = "{!frange l=$min_radius$upper}geodist($field,$point)";
        }
        elseif (isset($radius)) {
          $fq[] = "{!$spatial_method pt=$point sfield=$field d=$radius}";
        }

        // Change sort on the field, if set (and not already changed).
        if (isset($sort[$spatial['field']]) && substr($sort[$spatial['field']], 0, strlen($field)) === $field) {
          if (strpos($field, ':') === FALSE) {
            $sort[$spatial['field']] = str_replace($field, "geodist($field,$point)", $sort[$spatial['field']]);
          }
          else {
            $link = l(t('edit server'), 'admin/config/search/search_api/server/' . $this->server->machine_name . '/edit');
            watchdog('search_api_solr', 'Location sort on field @field had to be ignored because unclean field identifiers are used.', array('@field' => $spatial['field']), WATCHDOG_WARNING, $link);
          }
        }

        // Add parameters to fetch distance, if requested.
        if (!empty($spatial['distance']) && $version >= 4) {
          if (strpos($field, ':') === FALSE) {
            // Add pseudofield with the distance to the result items.
            $location_fields[] = '_' . $field . '_distance_:geodist(' . $field . ',' . $point . ')';
          }
          else {
            $link = l(t('edit server'), 'admin/config/search/search_api/server/' . $this->server->machine_name . '/edit');
            watchdog('search_api_solr', "Location distance information can't be added because unclean field identifiers are used.", array(), WATCHDOG_WARNING, $link);
          }
        }


        // Change the facet parameters for spatial fields to return distance
        // facets.
        if (!empty($facets)) {
          if (!empty($facet_params['facet.field'])) {
            $facet_params['facet.field'] = array_diff($facet_params['facet.field'], array($field));
          }
          foreach ($facets as $delta => $facet) {
            if ($facet['field'] != $spatial['field']) {
              continue;
            }
            $steps = $facet['limit'] > 0 ? $facet['limit'] : 5;
            $step = (isset($radius) ? $radius : 100) / $steps;
            for ($k = $steps - 1; $k > 0; --$k) {
              $distance = $step * $k;
              $key = "spatial-$delta-$distance";
              $facet_params['facet.query'][] = "{!$spatial_method pt=$point sfield=$field d=$distance key=$key}";
            }
            foreach (array('limit', 'mincount', 'missing') as $setting) {
              unset($facet_params["f.$field.facet.$setting"]);
            }
          }
        }
      }
    }
    // Normal sorting on location fields isn't possible.
    foreach ($sort as $field => $sort_param) {
      if (substr($sort_param, 0, 3) === 'loc') {
        unset($sort[$field]);
      }
    }

    // Handle field collapsing / grouping.
    $grouping = $query->getOption('search_api_grouping');
    if (!empty($grouping['use_grouping'])) {
      $group_params['group'] = 'true';
      // We always want the number of groups returned so that we get pagers done
      // right.
      $group_params['group.ngroups'] = 'true';
      if (!empty($grouping['truncate'])) {
        $group_params['group.truncate'] = 'true';
      }
      if (!empty($grouping['group_facet'])) {
        $group_params['group.facet'] = 'true';
      }
      foreach ($grouping['fields'] as $collapse_field) {
        $type = $index_fields[$collapse_field]['type'];
        // Only single-valued fields are supported.
        if ($version < 4) {
          // For Solr 3.x, only string and boolean fields are supported.
          if (search_api_is_list_type($type) || !search_api_is_text_type($type, array('string', 'boolean', 'uri'))) {
            $warnings[] = t('Grouping is not supported for field @field. ' .
                'Only single-valued fields of type "String", "Boolean" or "URI" are supported.',
                array('@field' => $index_fields[$collapse_field]['name']));
            continue;
          }
        }
        else {
          if (search_api_is_list_type($type) || search_api_is_text_type($type)) {
            $warnings[] = t('Grouping is not supported for field @field. ' .
                'Only single-valued fields not indexed as "Fulltext" are supported.',
                array('@field' => $index_fields[$collapse_field]['name']));
            continue;
          }
        }
        $group_params['group.field'][] = $fields[$collapse_field];
      }
      if (empty($group_params['group.field'])) {
        unset($group_params);
      }
      else {
        if (!empty($grouping['group_sort'])) {
          foreach ($grouping['group_sort'] as $group_sort_field => $order) {
            if (isset($fields[$group_sort_field])) {
              $f = $fields[$group_sort_field];
              if (substr($f, 0, 3) == 'ss_') {
                $f = 'sort_' . substr($f, 3);
              }
              $order = strtolower($order);
              $group_params['group.sort'][] = $f . ' ' . $order;
            }
          }
          if (!empty($group_params['group.sort'])) {
            $group_params['group.sort'] = implode(', ', $group_params['group.sort']);
          }
        }
        if (!empty($grouping['group_limit']) && ($grouping['group_limit'] != 1)) {
          $group_params['group.limit'] = $grouping['group_limit'];
        }
      }
    }

    // Set defaults.
    if (!$keys) {
      $keys = NULL;
    }

    // Collect parameters.
    $params = array(
      'fl' => 'item_id,score',
      'qf' => $qf,
      'fq' => $fq,
    );
    if (isset($options['offset'])) {
      $params['start'] = $options['offset'];
    }
    $params['rows'] = isset($options['limit']) ? $options['limit'] : 1000000;
    if ($sort) {
      $params['sort'] = implode(', ', $sort);
    }
    if (!empty($facet_params['facet.field'])) {
      $params += $facet_params;
    }
    if (!empty($highlight_params)) {
      $params += $highlight_params;
    }
    if (!empty($options['search_api_spellcheck'])) {
      $params['spellcheck'] = 'true';
    }
    if (!empty($mlt_params['mlt.fl'])) {
      $params += $mlt_params;
    }
    if (!empty($group_params)) {
      $params += $group_params;
    }
    if (!empty($this->options['retrieve_data'])) {
      $params['fl'] = '*,score';
    }
    if (!empty($location_fields)) {
      $params['fl'] .= ',' . implode(',', $location_fields);
    }

    // Retrieve http method from server options.
    $http_method = !empty($this->options['http_method']) ? $this->options['http_method'] : 'AUTO';

    $call_args = array(
      'query'       => &$keys,
      'params'      => &$params,
      'http_method' => &$http_method,
    );
    if ($this->request_handler) {
      $this->setRequestHandler($this->request_handler, $call_args);
    }

    try {
      // Send search request.
      $time_processing_done = microtime(TRUE);
      drupal_alter('search_api_solr_query', $call_args, $query);
      $this->preQuery($call_args, $query);

      $response = $this->solr->search($keys, $params, $http_method);
      $time_query_done = microtime(TRUE);

      // Extract results.
      $results = $this->extractResults($query, $response);

      // Add warnings, if present.
      if (!empty($warnings)) {
        $results['warnings'] = isset($results['warnings']) ? array_merge($warnings, $results['warnings']) : $warnings;
      }

      // Extract facets.
      if ($facets = $this->extractFacets($query, $response)) {
        $results['search_api_facets'] = $facets;
      }

      drupal_alter('search_api_solr_search_results', $results, $query, $response);
      $this->postQuery($results, $query, $response);

      // Compute performance.
      $time_end = microtime(TRUE);
      $results['performance'] = array(
        'complete' => $time_end - $time_method_called,
        'preprocessing' => $time_processing_done - $time_method_called,
        'execution' => $time_query_done - $time_processing_done,
        'postprocessing' => $time_end - $time_query_done,
      );

      return $results;
    }
    catch (SearchApiException $e) {
      throw new SearchApiException(t('An error occurred while trying to search with Solr: @msg.', array('@msg' => $e->getMessage())));
    }
  }

  /**
   * Extract results from a Solr response.
   *
   * @param object $response
   *   A HTTP response object.
   *
   * @return array
   *   An array with two keys:
   *   - result count: The number of total results.
   *   - results: An array of search results, as specified by
   *     SearchApiQueryInterface::execute().
   */
  protected function extractResults(SearchApiQueryInterface $query, $response) {
    $index = $query->getIndex();
    $fields = $this->getFieldNames($index);
    $field_options = $index->options['fields'];
    $version = $this->solr->getSolrVersion();

    // Set up the results array.
    $results = array();
    $results['results'] = array();
    // Keep a copy of the response in the results so it's possible to extract
    // further useful information out of it, if necessary.
    $results['search_api_solr_response'] = $response;

    // In some rare cases (e.g., MLT query with nonexistent ID) the response
    // will be NULL.
    if (!isset($response->response) && !isset($response->grouped)) {
      $results['result count'] = 0;
      return $results;
    }

    // If field collapsing has been enabled for this query, we need to process
    // the results differently.
    $grouping = $query->getOption('search_api_grouping');
    if (!empty($grouping['use_grouping']) && !empty($response->grouped)) {
      $docs = array();
      $results['result count'] = 0;
      foreach ($grouping['fields'] as $field) {
        if (!empty($response->grouped->{$fields[$field]})) {
          $results['result count'] += $response->grouped->{$fields[$field]}->ngroups;
          foreach ($response->grouped->{$fields[$field]}->groups as $group) {
            foreach ($group->doclist->docs as $doc) {
              $docs[] = $doc;
            }
          }
        }
      }
    }
    else {
      $results['result count'] = $response->response->numFound;
      $docs = $response->response->docs;
    }
    $spatials = $query->getOption('search_api_location');

    // Add each search result to the results array.
    foreach ($docs as $doc) {
      // Blank result array.
      $result = array(
        'id' => NULL,
        'score' => NULL,
        'fields' => array(),
      );

      // Extract properties from the Solr document, translating from Solr to
      // Search API property names. This reverses the mapping in
      // SearchApiSolrService::getFieldNames().
      foreach ($fields as $search_api_property => $solr_property) {
        if (isset($doc->{$solr_property})) {
          $result['fields'][$search_api_property] = $doc->{$solr_property};
          // Date fields need some special treatment to become valid date values
          // (i.e., timestamps) again.
          if (isset($field_options[$search_api_property]['type'])
              && $field_options[$search_api_property]['type'] == 'date'
              && preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}Z$/', $result['fields'][$search_api_property])) {
            $result['fields'][$search_api_property] = strtotime($result['fields'][$search_api_property]);
          }
        }
      }

      // We can find the item id and score in the special 'search_api_*'
      // properties. Mappings are provided for these properties in
      // SearchApiSolrService::getFieldNames().
      $result['id'] = $result['fields']['search_api_id'];
      $result['score'] = $result['fields']['search_api_relevance'];

      // If location based search is enabled ensure the calculated distance is
      // set to the appropriate field. If the calculation wasn't possible add
      // the coordinates to allow calculation.
      if ($spatials) {
        foreach ($spatials as $spatial) {
          if (isset($spatial['field']) && !empty($spatial['distance'])) {
            if ($version >= 4) {
              $doc_field = '_' . $fields[$spatial['field']] . '_distance_';
              if (!empty($doc->{$doc_field})) {
                $results['search_api_location'][$spatial['field']][$result['id']]['distance'] = $doc->{$doc_field};
              }
            }
          }
        }
      }

      $index_id = $this->getIndexId($index->machine_name);
      $solr_id = $this->createId($index_id, $result['id']);
      $excerpt = $this->getExcerpt($response, $solr_id, $result['fields'], $fields);
      if ($excerpt) {
        $result['excerpt'] = $excerpt;
      }

      // Use the result's id as the array key. By default, 'id' is mapped to
      // 'item_id' in SearchApiSolrService::getFieldNames().
      if ($result['id']) {
        $results['results'][$result['id']] = $result;
      }
    }

    // Check for spellcheck suggestions.
    if (module_exists('search_api_spellcheck') && $query->getOption('search_api_spellcheck')) {
      $results['search_api_spellcheck'] = new SearchApiSpellcheckSolr($response);
    }

    return $results;
  }

  /**
   * Extract and format highlighting information for a specific item from a Solr response.
   *
   * Will also use highlighted fields to replace retrieved field data, if the
   * corresponding option is set.
   */
  protected function getExcerpt($response, $id, array &$fields, array $field_mapping) {
    if (!isset($response->highlighting->$id)) {
      return FALSE;
    }
    $output = '';

    if (!empty($this->options['excerpt']) && !empty($response->highlighting->$id->content)) {
      foreach ($response->highlighting->$id->content as $snippet) {
        $snippet = strip_tags($snippet);
        $snippet = preg_replace('/^.*>|<.*$/', '', $snippet);
        $snippet = $this->formatHighlighting($snippet);
        // The created fragments sometimes have leading or trailing punctuation.
        // We remove that here for all common cases, but take care not to remove
        // < or > (so HTML tags stay valid).
        $snippet = trim($snippet, "\00..\x2F:;=\x3F..\x40\x5B..\x60");
        $output .= $snippet . ' … ';
      }
    }
    if (!empty($this->options['highlight_data'])) {
      foreach ($field_mapping as $search_api_property => $solr_property) {
        if (substr($solr_property, 0, 3) == 'tm_' && !empty($response->highlighting->$id->$solr_property)) {
          // Contrary to above, we here want to preserve HTML, so we just
          // replace the [HIGHLIGHT] tags with the appropriate format.
          $fields[$search_api_property] = $this->formatHighlighting($response->highlighting->$id->$solr_property);
        }
      }
    }

    return $output;
  }

  /**
   * Changes highlighting tags from our custom, HTML-safe ones to HTML.
   *
   * @param string|array $snippet
   *   The snippet(s) to format.
   *
   * @return string|array
   *   The snippet(s), properly formatted as HTML.
   */
  protected function formatHighlighting($snippet) {
    return preg_replace('#\[(/?)HIGHLIGHT\]#', '<$1strong>', $snippet);
  }

  /**
   * Extract facets from a Solr response.
   *
   * @param object $response
   *   A response object from SolrPhpClient.
   *
   * @return array
   *   An array describing facets that apply to the current results.
   */
  protected function extractFacets(SearchApiQueryInterface $query, $response) {
    $facets = array();

    if (!isset($response->facet_counts)) {
      return $facets;
    }

    $index = $query->getIndex();
    $fields = $this->getFieldNames($index);

    $extract_facets = $query->getOption('search_api_facets', array());

    if (isset($response->facet_counts->facet_fields)) {
      $facet_fields = $response->facet_counts->facet_fields;

      foreach ($extract_facets as $delta => $info) {
        $field = $fields[$info['field']];
        if (!empty($facet_fields->$field)) {
          $min_count = $info['min_count'];
          $terms = $facet_fields->$field;
          if ($info['missing']) {
            // We have to correctly incorporate the "_empty_" term.
            // This will ensure that the term with the least results is dropped,
            // if the limit would be exceeded.
            if (isset($terms->_empty_)) {
              if ($terms->_empty_ < $min_count) {
                unset($terms->_empty_);
              }
              else {
                $terms = (array) $terms;
                arsort($terms);
                if ($info['limit'] > 0 && count($terms) > $info['limit']) {
                  array_pop($terms);
                }
              }
            }
          }
          elseif (isset($terms->_empty_)) {
            $terms = clone $terms;
            unset($terms->_empty_);
          }
          $type = isset($index->options['fields'][$info['field']]['type']) ? search_api_extract_inner_type($index->options['fields'][$info['field']]['type']) : 'string';
          foreach ($terms as $term => $count) {
            if ($count >= $min_count) {
              if ($term === '_empty_') {
                $term = '!';
              }
              elseif ($type == 'boolean') {
                if ($term == 'true') {
                  $term = '"1"';
                }
                elseif ($term == 'false') {
                  $term = '"0"';
                }
              }
              elseif ($type == 'date') {
                $term = $term ? '"' . strtotime($term) . '"' : NULL;
              }
              else {
                $term = "\"$term\"";
              }
              if ($term) {
                $facets[$delta][] = array(
                  'filter' => $term,
                  'count' => $count,
                );
              }
            }
          }
          if (empty($facets[$delta])) {
            unset($facets[$delta]);
          }
        }
      }

    }

    if (isset($response->facet_counts->facet_queries)) {
      if ($spatials = $query->getOption('search_api_location')) {
        $queries = array();
        foreach ($response->facet_counts->facet_queries as $key => $count) {
          if (!preg_match('/^spatial-(.*)-(\d+(?:\.\d+)?)$/', $key, $m)) {
            continue;
          }
          if (empty($extract_facets[$m[1]])) {
            continue;
          }
          $facet = $extract_facets[$m[1]];
          if ($count >= $facet['min_count']) {
            $facets[$m[1]][] = array(
              'filter' => "[* {$m[2]}]",
              'count' => $count,
            );
          }
        }
      }
    }

    return $facets;
  }

  /**
   * Flatten a keys array into a single search string.
   *
   * @param array $keys
   *   The keys array to flatten, formatted as specified by
   *   SearchApiQueryInterface::getKeys().
   *
   * @return string
   *   A Solr query string representing the same keys.
   */
  protected function flattenKeys(array $keys) {
    $k = array();
    $or = $keys['#conjunction'] == 'OR';
    $neg = !empty($keys['#negation']);
    foreach (element_children($keys) as $i) {
      $key = $keys[$i];
      if (!$key) {
        continue;
      }
      if (is_array($key)) {
        $subkeys = $this->flattenKeys($key);
        if ($subkeys) {
          $nested_expressions = TRUE;
          // If this is a negated OR expression, we can't just use nested keys
          // as-is, but have to put them into parantheses.
          if ($or && $neg) {
            $subkeys = "($subkeys)";
          }
          $k[] = $subkeys;
        }
      }
      else {
        $key = trim($key);
        $key = call_user_func(array($this->connection_class, 'phrase'), $key);
        $k[] = $key;
      }
    }
    if (!$k) {
      return '';
    }

    // Formatting the keys into a Solr query can be a bit complex. The following
    // code will produce filters that look like this:
    //
    // #conjunction | #negation | return value
    // ----------------------------------------------------------------
    // AND          | FALSE     | A B C
    // AND          | TRUE      | -(A AND B AND C)
    // OR           | FALSE     | ((A) OR (B) OR (C))
    // OR           | TRUE      | -A -B -C

    // If there was just a single, unnested key, we can ignore all this.
    if (count($k) == 1 && empty($nested_expressions)) {
      $k = reset($k);
      return $neg ? "*:* AND -$k" : $k;
    }

    if ($or) {
      if ($neg) {
        return '*:* AND -' . implode(' AND -', $k);
      }
      return '((' . implode(') OR (', $k) . '))';
    }
    $k = implode(' AND ', $k);
    return $neg ? "*:* AND -($k)" : $k;
  }

  /**
   * Transforms a query filter into a flat array of Solr filter queries, using
   * the field names in $fields.
   */
  protected function createFilterQueries(SearchApiQueryFilterInterface $filter, array $solr_fields, array $fields) {
    $or = $filter->getConjunction() == 'OR';
    $fq = array();
    $prefix = '';
    foreach ($filter->getFilters() as $f) {
      if (is_array($f)) {
        if (!isset($fields[$f[0]])) {
          throw new SearchApiException(t('Filter term on unknown or unindexed field @field.', array('@field' => $f[0])));
        }
        if ($f[1] !== '') {
          $fq[] = $this->createFilterQuery($solr_fields[$f[0]], $f[1], $f[2], $fields[$f[0]]);
        }
      }
      elseif ($f instanceof SearchApiQueryFilterInterface) {
        $q = $this->createFilterQueries($f, $solr_fields, $fields);
        if ($filter->getConjunction() != $f->getConjunction()) {
          // $or == TRUE means the nested filter has conjunction AND, and vice versa
          $sep = $or ? ' ' : ' OR ';
          $fq[] = count($q) == 1 ? reset($q) : '((' . implode(')' . $sep . '(', $q) . '))';
        }
        else {
          $fq = array_merge($fq, $q);
        }
      }
    }
    if (method_exists($filter, 'getTags')) {
      foreach ($filter->getTags() as $tag) {
        $prefix = "{!tag=$tag}";
        // We can only apply one tag per filter.
        break;
      }
    }
    if ($or && count($fq) > 1) {
      $fq = array('((' . implode(') OR (', $fq) . '))');
    }
    if ($prefix) {
      foreach ($fq as $i => $filter) {
        $fq[$i] = $prefix . $filter;
      }
    }
    return $fq;
  }

  /**
   * Create a single search query string according to the given field, value
   * and operator.
   */
  protected function createFilterQuery($field, $value, $operator, $field_info) {
    $field = call_user_func(array($this->connection_class, 'escapeFieldName'), $field);
    // Special handling for location fields.
    if (isset($field_info['real_type']) && $field_info['real_type'] == 'location') {
      // Empty / non-empty comparison has to take place in one of the subfields
      // of the location field type. These subfields are usually generated with
      // the index and the field type as name suffix.
      // @TODO Do we need to handle other operators / values too?
      if ($value === NULL) {
        $field .= '_0___tdouble';
      }
    }
    if ($value === NULL) {
      return ($operator == '=' ? '*:* AND -' : '') . "$field:[* TO *]";
    }
    $value = trim($value);
    $value = $this->formatFilterValue($value, search_api_extract_inner_type($field_info['type']));
    switch ($operator) {
      case '<>':
        return "*:* AND -($field:$value)";
      case '<':
        return "$field:{* TO $value}";
      case '<=':
        return "$field:[* TO $value]";
      case '>=':
        return "$field:[$value TO *]";
      case '>':
        return "$field:{{$value} TO *}";

      default:
        return "$field:$value";
    }
  }

  /**
   * Format a value for filtering on a field of a specific type.
   */
  protected function formatFilterValue($value, $type) {
    switch ($type) {
      case 'boolean':
        $value = $value ? 'true' : 'false';
        break;
      case 'date':
        $value = is_numeric($value) ? (int) $value : strtotime($value);
        if ($value === FALSE) {
          return 0;
        }
        $value = format_date($value, 'custom', self::SOLR_DATE_FORMAT, 'UTC');
        break;
      case 'text':
        return '(' . call_user_func(array($this->connection_class, 'escape'), $value) . ')';
    }
    return call_user_func(array($this->connection_class, 'phrase'), $value);
  }

  /**
   * Helper method for creating the facet field parameters.
   */
  protected function getFacetParams(array $facets, array $fields, array &$fq = array()) {
    if (!$facets) {
      return array();
    }
    $facet_params['facet'] = 'true';
    $facet_params['facet.sort'] = 'count';
    $facet_params['facet.limit'] = 10;
    $facet_params['facet.mincount'] = 1;
    $facet_params['facet.missing'] = 'false';
    foreach ($facets as $info) {
      if (empty($fields[$info['field']])) {
        continue;
      }
      // String fields have their own corresponding facet fields.
      $field = $fields[$info['field']];
      // Check for the "or" operator.
      if (isset($info['operator']) && $info['operator'] === 'or') {
        // Remember that filters for this field should be tagged.
        $tag = 'facet:' . $info['field'];
        // Add the facet field.
        $facet_params['facet.field'][] = "{!ex=$tag}$field";
      }
      else {
        // Add the facet field.
        $facet_params['facet.field'][] = $field;
      }
      // Set limit, unless it's the default.
      if ($info['limit'] != 10) {
        $facet_params["f.$field.facet.limit"] = $info['limit'] ? $info['limit'] : -1;
      }
      // Set mincount, unless it's the default.
      if ($info['min_count'] != 1) {
        $facet_params["f.$field.facet.mincount"] = $info['min_count'];
      }
      // Set missing, if specified.
      if ($info['missing']) {
        $facet_params["f.$field.facet.missing"] = 'true';
      }
    }

    return $facet_params;
  }

  /**
   * Helper method for creating the highlighting parameters.
   *
   * (The $query parameter currently isn't used and only here for the potential
   * sake of subclasses.)
   *
   * @param SearchApiQueryInterface|SearchApiMultiQueryInterface $query
   *   The query object, either for a normal Search API query or a multi-index
   *   query.
   *
   * @return array
   *   An array of parameters to be added to the Solr search request.
   */
  protected function getHighlightParams($query) {
    $highlight_params = array();

    if (!empty($this->options['excerpt']) || !empty($this->options['highlight_data'])) {
      $highlight_params['hl'] = 'true';
      $highlight_params['hl.fl'] = 'content';
      $highlight_params['hl.simple.pre'] = '[HIGHLIGHT]';
      $highlight_params['hl.simple.post'] = '[/HIGHLIGHT]';
      $highlight_params['hl.snippets'] = 3;
      $highlight_params['hl.fragsize'] = 70;
      $highlight_params['hl.mergeContiguous'] = 'true';
    }

    if (!empty($this->options['highlight_data'])) {
      $highlight_params['hl.fl'] = 'tm_*';
      $highlight_params['hl.snippets'] = 1;
      $highlight_params['hl.fragsize'] = 0;
      if (!empty($this->options['excerpt'])) {
        // If we also generate a "normal" excerpt, set the settings for the
        // "content" field (which we use to generate the excerpt) back to the
        // above values.
        $highlight_params['f.content.hl.snippets'] = 3;
        $highlight_params['f.content.hl.fragsize'] = 70;
        // It regrettably doesn't seem to be possible to set hl.fl to several
        // values, if one contains wild cards (i.e., "t_*,content" wouldn't
        // work).
        $highlight_params['hl.fl'] = '*';
      }
    }

    return $highlight_params;
  }

  /**
   * Sets the request handler.
   *
   * This should also make the needed adjustments to the request parameters.
   *
   * @param $handler
   *   Name of the handler to set.
   * @param array $call_args
   *   An associative array containing all three arguments to the
   *   SearchApiSolrConnectionInterface::search() call ("query", "params" and
   *   "method") as references.
   *
   * @return bool
   *   TRUE iff this method invocation handled the given handler. This allows
   *   subclasses to recognize whether the request handler was already set by
   *   this method.
   */
  protected function setRequestHandler($handler, array &$call_args) {
    if ($handler == 'pinkPony') {
      $call_args['params']['qt'] = $handler;
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Empty method called before sending a search query to Solr.
   *
   * This allows subclasses to apply custom changes before the query is sent to
   * Solr. Works exactly like hook_search_api_solr_query_alter().
   *
   * @param array $call_args
   *   An associative array containing all three arguments to the
   *   SearchApiSolrConnectionInterface::search() call ("query", "params" and
   *   "method") as references.
   * @param SearchApiQueryInterface $query
   *   The SearchApiQueryInterface object representing the executed search query.
   */
  protected function preQuery(array &$call_args, SearchApiQueryInterface $query) {
  }

  /**
   * Empty method to allow subclasses to apply custom changes before search results are returned.
   *
   * Works exactly like hook_search_api_solr_search_results_alter().
   *
   * @param array $results
   *   The results array that will be returned for the search.
   * @param SearchApiQueryInterface $query
   *   The SearchApiQueryInterface object representing the executed search query.
   * @param object $response
   *   The response object returned by Solr.
   */
  protected function postQuery(array &$results, SearchApiQueryInterface $query, $response) {
  }

  //
  // Autocompletion feature
  //

  /**
   * Implements SearchApiAutocompleteInterface::getAutocompleteSuggestions().
   */
  // Largely copied from the apachesolr_autocomplete module.
  public function getAutocompleteSuggestions(SearchApiQueryInterface $query, SearchApiAutocompleteSearch $search, $incomplete_key, $user_input) {
    $suggestions = array();
    // Reset request handler
    $this->request_handler = NULL;
    // Turn inputs to lower case, otherwise we get case sensivity problems.
    $incomp = drupal_strtolower($incomplete_key);

    $index = $query->getIndex();
    $fields = $this->getFieldNames($index);
    $complete = $query->getOriginalKeys();

    // Extract keys
    $keys = $query->getKeys();
    if (is_array($keys)) {
      $keys_array = array();
      while ($keys) {
        reset($keys);
        if (!element_child(key($keys))) {
          array_shift($keys);
          continue;
        }
        $key = array_shift($keys);
        if (is_array($key)) {
          $keys = array_merge($keys, $key);
        }
        else {
          $keys_array[$key] = $key;
        }
      }
      $keys = $this->flattenKeys($query->getKeys());
    }
    else {
      $keys_array = drupal_map_assoc(preg_split('/[-\s():{}\[\]\\\\"]+/', $keys, -1, PREG_SPLIT_NO_EMPTY));
    }
    if (!$keys) {
      $keys = NULL;
    }

    // Set searched fields
    $options = $query->getOptions();
    $search_fields = $query->getFields();
    $qf = array();
    foreach ($search_fields as $f) {
      $qf[] = $fields[$f];
    }

    // Extract filters
    $fq = $this->createFilterQueries($query->getFilter(), $fields, $index->options['fields']);
    $index_id = $this->getIndexId($index->machine_name);
    $fq[] = 'index_id:' . call_user_func(array($this->connection_class, 'phrase'), $index_id);
    if (!empty($this->options['site_hash'])) {
      // We don't need to escape the site hash, as that consists only of
      // alphanumeric characters.
      $fq[] = 'hash:' . search_api_solr_site_hash();
    }

    // Autocomplete magic
    $facet_fields = array();
    foreach ($search_fields as $f) {
      $facet_fields[] = $fields[$f];
    }

    $limit = $query->getOption('limit', 10);

    $params = array(
      'qf' => $qf,
      'fq' => $fq,
      'rows' => 0,
      'facet' => 'true',
      'facet.field' => $facet_fields,
      'facet.prefix' => $incomp,
      'facet.limit' => $limit * 5,
      'facet.mincount' => 1,
      'spellcheck' => (!isset($this->options['autocorrect_spell']) || $this->options['autocorrect_spell']) ? 'true' : 'false',
      'spellcheck.count' => 1,
    );
    // Retrieve http method from server options.
    $http_method = !empty($this->options['http_method']) ? $this->options['http_method'] : 'AUTO';

    $call_args = array(
      'query'       => &$keys,
      'params'      => &$params,
      'http_method' => &$http_method,
    );
    if ($this->request_handler) {
      $this->setRequestHandler($this->request_handler, $call_args);
    }
    $second_pass = !isset($this->options['autocorrect_suggest_words']) || $this->options['autocorrect_suggest_words'];
    for ($i = 0; $i < ($second_pass ? 2 : 1); ++$i) {
      try {
        // Send search request
        $this->connect();
        drupal_alter('search_api_solr_query', $call_args, $query);
        $this->preQuery($call_args, $query);
        $response = $this->solr->search($keys, $params, $http_method);

        if (!empty($response->spellcheck->suggestions)) {
          $replace = array();
          foreach ($response->spellcheck->suggestions as $word => $data) {
            $replace[$word] = $data->suggestion[0];
          }
          $corrected = str_ireplace(array_keys($replace), array_values($replace), $user_input);
          if ($corrected != $user_input) {
            array_unshift($suggestions, array(
              'prefix' => t('Did you mean') . ':',
              'user_input' => $corrected,
            ));
          }
        }

        $matches = array();
        if (isset($response->facet_counts->facet_fields)) {
          foreach ($response->facet_counts->facet_fields as $terms) {
            foreach ($terms as $term => $count) {
              if (isset($matches[$term])) {
                // If we just add the result counts, we can easily get over the
                // total number of results if terms appear in multiple fields.
                // Therefore, we just take the highest value from any field.
                $matches[$term] = max($matches[$term], $count);
              }
              else {
                $matches[$term] = $count;
              }
            }
          }

          if ($matches) {
            // Eliminate suggestions that are too short or already in the query.
            foreach ($matches as $term => $count) {
              if (strlen($term) < 3 || isset($keys_array[$term])) {
                unset($matches[$term]);
              }
            }

            // Don't suggest terms that are too frequent (by default in more
            // than 90% of results).
            $result_count = $response->response->numFound;
            $max_occurrences = $result_count * variable_get('search_api_solr_autocomplete_max_occurrences', 0.9);
            if (($max_occurrences >= 1 || $i > 0) && $max_occurrences < $result_count) {
              foreach ($matches as $match => $count) {
                if ($count > $max_occurrences) {
                  unset($matches[$match]);
                }
              }
            }

            // The $count in this array is actually a score. We want the
            // highest ones first.
            arsort($matches);

            // Shorten the array to the right ones.
            $additional_matches = array_slice($matches, $limit - count($suggestions), NULL, TRUE);
            $matches = array_slice($matches, 0, $limit, TRUE);

            // Build suggestions using returned facets
            $incomp_length = strlen($incomp);
            foreach ($matches as $term => $count) {
              if (drupal_strtolower(substr($term, 0, $incomp_length)) == $incomp) {
                $suggestions[] = array(
                  'suggestion_suffix' => substr($term, $incomp_length),
                  'term' => $term,
                  'results' => $count,
                );
              }
              else {
                $suggestions[] = array(
                  'suggestion_suffix' => ' ' . $term,
                  'term' => $term,
                  'results' => $count,
                );
              }
            }
          }
        }
      }
      catch (SearchApiException $e) {
        watchdog_exception('search_api_solr', $e, "%type during autocomplete Solr query: !message in %function (line %line of %file).", array(), WATCHDOG_WARNING);
      }

      if (count($suggestions) >= $limit) {
        break;
      }
      // Change parameters for second query.
      unset($params['facet.prefix']);
      $keys = trim ($keys . ' ' . $incomplete_key);
    }

    return $suggestions;
  }

  //
  // SearchApiMultiServiceInterface methods
  //

  /**
   * Implements SearchApiMultiServiceInterface::queryMultiple().
   */
  public function queryMultiple(array $options = array()) {
    return new SearchApiMultiQuery($this->server, $options);
  }

  /**
   * Implements SearchApiMultiServiceInterface::searchMultiple().
   */
  public function searchMultiple(SearchApiMultiQueryInterface $query) {
    $time_method_called = microtime(TRUE);
    // Get field information
    $solr_fields = array(
      'search_api_id' => 'item_id',
      'search_api_relevance' => 'score',
      'search_api_multi_index' => 'index_id',
    );
    $fields = array(
      'search_api_multi_index' => array(
        'type' => 'string',
      ),
    );
    foreach ($query->getIndexes() as $index) {
      if (empty($index->options['fields'])) {
        continue;
      }
      $prefix = $this->getIndexId($index->machine_name) . ':';
      foreach ($this->getFieldNames($index) as $field => $key) {
        if (!isset($solr_fields[$field])) {
          $solr_fields[$prefix . $field] = $key;
        }
      }
      foreach ($index->options['fields'] as $field => $info) {
        $fields[$prefix . $field] = $info;
      }
    }

    // Extract keys
    $keys = $query->getKeys();
    if (is_array($keys)) {
      $keys = $this->flattenKeys($keys);
    }

    // Set searched fields
    $search_fields = $query->getFields();
    $qf = array();
    foreach ($search_fields as $f) {
      $qf[] = $solr_fields[$f];
    }

    // Extract filters
    $filter = $query->getFilter();
    $fq = $this->createFilterQueries($filter, $solr_fields, $fields);

    // Restrict search to searched indexes.
    $index_filter = array();
    $indexes = array();
    foreach ($query->getIndexes() as $index) {
      $index_id = $this->getIndexId($index->machine_name);
      $indexes[$index_id] = $index;
      $index_filter[] = 'index_id:' . call_user_func(array($this->connection_class, 'phrase'), $index_id);
    }
    $fq[] = implode(' OR ', $index_filter);
    if (!empty($this->options['site_hash'])) {
      // We don't need to escape the site hash, as that consists only of
      // alphanumeric characters.
      $fq[] = 'hash:' . search_api_solr_site_hash();
    }

    // Extract sort
    $sort = array();
    foreach ($query->getSort() as $f => $order) {
      $f = $solr_fields[$f];
      if (substr($f, 0, 3) == 'ss_') {
        $f = 'sort_' . substr($f, 3);
      }
      $order = strtolower($order);
      $sort[] = "$f $order";
    }

    // Get facet fields
    $facets = $query->getOption('search_api_facets') ? $query->getOption('search_api_facets') : array();
    $facet_params = $this->getFacetParams($facets, $solr_fields, $fq);

    // Handle highlighting.
    $highlight_params = $this->getHighlightParams($query);

    // Set defaults
    if (!$keys) {
      $keys = NULL;
    }
    $options = $query->getOptions();

    // Collect parameters
    $params = array(
      'fl' => 'item_id,index_id,score',
      'qf' => $qf,
      'fq' => $fq,
    );
    if (isset($options['offset'])) {
      $params['start'] = $options['offset'];
    }
    if (isset($options['limit'])) {
      $params['rows'] = $options['limit'];
    }
    if ($sort) {
      $params['sort'] = implode(', ', $sort);
    }
    if (!empty($facet_params['facet.field'])) {
      $params += $facet_params;
    }
    if (!empty($highlight_params)) {
      $params += $highlight_params;
    }
    if (!empty($this->options['retrieve_data'])) {
      $params['fl'] = '*,score';
    }

    // Retrieve http method from server options.
    $http_method = !empty($this->options['http_method']) ? $this->options['http_method'] : 'AUTO';

    // Send search request
    $time_processing_done = microtime(TRUE);
    $this->connect();

    $call_args = array(
      'query'       => &$keys,
      'params'      => &$params,
      'http_method' => &$http_method,
    );
    drupal_alter('search_api_solr_multi_query', $call_args, $query);

    $response = $this->solr->search($keys, $params, $http_method);
    $time_query_done = microtime(TRUE);

    // Extract results
    $results = array();
    $results['result count'] = $response->response->numFound;
    $results['results'] = array();
    $tmp = array();
    foreach ($response->response->docs as $id => $doc) {
      if (isset($indexes[$doc->index_id])) {
        $index = $indexes[$doc->index_id];
      }
      else {
        $index = new SearchApiIndex(array('machine_name' => $doc->index_id));
      }
      $fields = $this->getFieldNames($index);
      $field_options = $index->options['fields'];
      $result = array(
        'id' => NULL,
        'index_id' => $doc->index_id,
        'score' => NULL,
        'fields' => array(),
      );
      $solr_id = $this->createId($doc->index_id, $doc->item_id);
      foreach ($fields as $search_api_property => $solr_property) {
        if (isset($doc->{$solr_property})) {
          $result['fields'][$search_api_property] = $doc->{$solr_property};
          // Date fields need some special treatment to become valid date values
          // (i.e., timestamps) again.
          if (isset($field_options[$search_api_property]['type'])
              && $field_options[$search_api_property]['type'] == 'date'
              && preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}Z$/', $result['fields'][$search_api_property])) {
            $result['fields'][$search_api_property] = strtotime($result['fields'][$search_api_property]);
          }
        }
      }

      $excerpt = $this->getExcerpt($response, $solr_id, $result['fields'], $fields);
      if ($excerpt) {
        $result['excerpt'] = $excerpt;
      }

      // We can find the item id and score in the special 'search_api_*'
      // properties. Mappings are provided for these properties in
      // SearchApiSolrService::getFieldNames().
      $result['id'] = $result['fields']['search_api_id'];
      $result['score'] = $result['fields']['search_api_relevance'];
      $results['results'][$id] = $result;
    }

    // Extract facets
    if (isset($response->facet_counts->facet_fields)) {
      $results['search_api_facets'] = array();
      $facet_fields = $response->facet_counts->facet_fields;
      foreach ($facets as $delta => $info) {
        $field = $solr_fields[$info['field']];
        if (!empty($facet_fields->$field)) {
          $min_count = $info['min_count'];
          $terms = $facet_fields->$field;
          if ($info['missing']) {
            // We have to correctly incorporate the "_empty_" term.
            // This will ensure that the term with the least results is dropped,
            // if the limit would be exceeded.
            if (isset($terms->_empty_)) {
              if ($terms->_empty_ < $min_count) {
                unset($terms->_empty_);
              }
              else {
                $terms = (array) $terms;
                arsort($terms);
                if ($info['limit'] > 0 && count($terms) > $info['limit']) {
                  array_pop($terms);
                }
              }
            }
          }
          elseif (isset($terms->_empty_)) {
            $terms = clone $terms;
            unset($terms->_empty_);
          }
          $type = isset($fields[$info['field']]['type']) ? search_api_extract_inner_type($fields[$info['field']]['type']) : 'string';
          foreach ($terms as $term => $count) {
            if ($count >= $min_count) {
              if ($term === '_empty_') {
                $term = '!';
              }
              elseif ($type == 'boolean') {
                if ($term == 'true') {
                  $term = '"1"';
                }
                elseif ($term == 'false') {
                  $term = '"0"';
                }
              }
              elseif ($type == 'date') {
                $term = $term ? '"' . strtotime($term) . '"' : NULL;
              }
              else {
                $term = "\"$term\"";
              }
              if ($term) {
                $results['search_api_facets'][$delta][] = array(
                  'filter' => $term,
                  'count' => $count,
                );
              }
            }
          }
          if (empty($results['search_api_facets'][$delta])) {
            unset($results['search_api_facets'][$delta]);
          }
        }
      }
    }

    drupal_alter('search_api_solr_multi_search_results', $results, $query, $response);

    // Compute performance
    $time_end = microtime(TRUE);
    $results['performance'] = array(
      'complete' => $time_end - $time_method_called,
      'preprocessing' => $time_processing_done - $time_method_called,
      'execution' => $time_query_done - $time_processing_done,
      'postprocessing' => $time_end - $time_query_done,
    );

    return $results;
  }

  //
  // Additional methods that might be used when knowing the service class.
  //

  /**
   * Ping the Solr server to tell whether it can be accessed.
   *
   * Uses the admin/ping request handler.
   */
  public function ping() {
    $this->connect();
    return $this->solr->ping();
  }

  /**
   * Sends a commit command to the Solr server.
   */
  public function commit() {
    try {
      $this->connect();
      return $this->solr->commit(FALSE);
    }
    catch (SearchApiException $e) {
      watchdog_exception('search_api_solr', $e,
          '%type while trying to commit on server @server: !message in %function (line %line of %file).',
          array('@server' => $this->server->machine_name), WATCHDOG_WARNING);
    }
  }

  /**
   * Schedules a commit operation for this server.
   *
   * The commit will be sent at the end of the current page request. Multiple
   * calls to this method will still only result in one commit operation.
   */
  public function scheduleCommit() {
    if (!$this->commitScheduled) {
      $this->commitScheduled = TRUE;
      drupal_register_shutdown_function(array($this, 'commit'));
    }
  }

  /**
   * Gets the Solr connection class used by this service.
   *
   * @return string
   *   The name of a class which implements SearchApiSolrConnectionInterface.
   */
  public function getConnectionClass() {
    return $this->connection_class;
  }

  /**
   * Sets the Solr connection class used by this service.
   *
   * @param string $class
   *   The name of a class which implements SearchApiSolrConnectionInterface.
   */
  public function setConnectionClass($class) {
    $this->connection_class = $class;
    $this->solr = NULL;
  }

  /**
   * Gets the currently used Solr connection object.
   *
   * @return SearchApiSolrConnectionInterface
   *   The solr connection object used by this server.
   */
  public function getSolrConnection() {
    $this->connect();
    return $this->solr;
  }

  /**
   * Get metadata about fields in the Solr/Lucene index.
   *
   * @param int $num_terms
   *   Number of 'top terms' to return.
   *
   * @return array
   *   An array of SearchApiSolrField objects.
   *
   * @see SearchApiSolrConnectionInterface::getFields()
   */
  public function getFields($num_terms = 0) {
    $this->connect();
    return $this->solr->getFields($num_terms);
  }

  /**
   * Retrieves a config file or file list from the Solr server.
   *
   * Uses the admin/file request handler.
   *
   * @param string|null $file
   *   (optional) The name of the file to retrieve. If the file is a directory,
   *   the directory contents are instead listed and returned. NULL represents
   *   the root config directory.
   *
   * @return object
   *  A HTTP response object containing either the file contents or a file list.
   */
  public function getFile($file = NULL) {
    $this->connect();

    $file_servlet_name = constant($this->connection_class . '::FILE_SERVLET');

    $params['contentType'] = 'text/xml;charset=utf-8';
    if ($file) {
      $params['file'] = $file;
    }
    return $this->solr->makeServletRequest($file_servlet_name, $params);
  }

  /**
   * Prefixes an index ID as configured.
   *
   * The resulting ID will be a concatenation of the following strings:
   * - If set, the "search_api_solr_index_prefix" variable.
   * - If set, the index-specific "search_api_solr_index_prefix_INDEX" variable.
   * - The index's machine name.
   *
   * @param string $machine_name
   *   The index's machine name.
   *
   * @return string
   *   The prefixed machine name.
   */
  protected function getIndexId($machine_name) {
    // Prepend per-index prefix.
    $id = variable_get('search_api_solr_index_prefix_' . $machine_name, '') . $machine_name;
    // Prepend environment prefix.
    $id = variable_get('search_api_solr_index_prefix', '') . $id;
    return $id;
  }

}
