<?php

/**
 * @file
 * Contains SearchApiElasticsearchViewsHandlerFilterFulltext.
 */

/**
 * Views filter handler class for handling fulltext fields.
 */

class SearchApiElasticsearchViewsHandlerFilterFulltext extends SearchApiViewsHandlerFilterFulltext {

  /**
   * Specify our options.
   */
  public function option_definition() {
    $options = parent::option_definition();

    $options['analyzer'] = array('default' => array());

    return $options;
  }

  /**
   * Extend the options form.
   */
  public function options_form(&$form, &$form_state) {
    parent::options_form($form, $form_state);

    $analyzer_options = array();

    // We only want this to work with Elasticsearch servers.
    $index_name = substr($form_state['view']->base_table, 17);
    $query = new EntityFieldQuery();
    $query->entityCondition('entity_type', 'search_api_index', '=');
    $query->propertyCondition('machine_name', $index_name, '=');
    $result = $query->execute();
    $result = reset($result['search_api_index']);
    $index = search_api_index_load($result->id);
    $server = $index->server();
    $service_info = search_api_get_service_info();
    $reflection = new ReflectionClass($service_info[$server->class]['class']);
    if ($reflection->isSubclassOf('SearchApiElasticsearchAbstractService')) {
      $elasticsearch_client = new $service_info[$server->class]['class']($server);
      $analysis_settings = $elasticsearch_client->getAnalysisSettings($index);
      foreach ($analysis_settings['analyzer'] as $name => $settings) {
        $analyzer_options[$name] = $name;
      }

      // Build our form.
      $form['analyzer'] = array(
        '#type' => 'select',
        '#title' => t('Analyzer'),
        '#description' => t('The analyzer to use for this search. If none are selected, the default from the index will be used.'),
        '#options' => $analyzer_options,
        '#default_value' => $this->options['analyzer'],
        '#empty_option' => t('Use index default'),
      );
    }
  }

  /**
   * Add these options to the query.
   */
  public function query() {
    parent::query();
    $this->query->setOption('analyzer', $this->options['analyzer']);
  }
}
