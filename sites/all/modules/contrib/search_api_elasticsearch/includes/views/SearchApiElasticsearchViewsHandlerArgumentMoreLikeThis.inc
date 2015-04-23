<?php

/**
 * @file
 * Contains SearchApiElasticsearchViewsHandlerArgumentMoreLikeThis.
 */

class SearchApiElasticsearchViewsHandlerArgumentMoreLikeThis extends SearchApiViewsHandlerArgumentMoreLikeThis {

  public function option_definition() {
    $options = parent::option_definition();
    $options['min_term_freq'] = array('default' => 1);
    $options['min_doc_freq'] = array('default' => 1);
    return $options;
  }

  public function options_form(&$form, &$form_state) {
    parent::options_form($form, $form_state);

    // We only want this to work with Elasticsearch servers.
    $index_name = substr($form_state['view']->base_table, 17);
    $query = new EntityFieldQuery();
    $query->entityCondition('entity_type', 'search_api_index', '=');
    $query->propertyCondition('machine_name', $index_name, '=');
    $result = $query->execute();
    $result = reset(reset($result));
    $index = search_api_index_load($result->id);
    $server = $index->server();
    $service_info = search_api_get_service_info();
    $reflection = new ReflectionClass($service_info[$server->class]['class']);
    if ($reflection->isSubclassOf('SearchApiElasticsearchAbstractService')) {
      $form['min_doc_freq'] = array(
        '#type' => 'textfield',
        '#title' => t('Min doc frequency'),
        '#default_value' => $this->options['min_doc_freq'],
        '#element_validate' => array('element_validate_integer_positive'),
        '#description' => t('The frequency at which words will be ignored which do not occur in at least this many docs.'),
      );

      $form['min_term_freq'] = array(
        '#type' => 'textfield',
        '#title' => t('Min term frequency'),
        '#default_value' => $this->options['min_term_freq'],
        '#element_validate' => array('element_validate_integer_positive'),
        '#description' => t('The frequency below which terms will be ignored in the source doc.'),
      );
    }
  }

  public function query($group_by = FALSE) {
    parent::query($group_by);
    $mlt = $this->query->getOption('search_api_mlt');
    $mlt['min_doc_freq'] = $this->options['min_doc_freq'];
    $mlt['min_term_freq'] = $this->options['min_term_freq'];
    $this->query->getSearchApiQuery()->setOption('search_api_mlt', $mlt);
  }

}
