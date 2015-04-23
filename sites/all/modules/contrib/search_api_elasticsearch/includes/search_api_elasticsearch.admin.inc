<?php

/**
 * @file
 * Admin page callbacks for the Search API Elasticsearch module.
 */

/**
 * Form constructor for the Elasticsearch analysis configuration form.
 *
 * @param SearchApiIndex $index
 *   The index for which analysis options should be configured.
 *
 * @ingroup forms
 */
function search_api_elasticsearch_analysis_form($form, &$form_state, SearchApiIndex $index) {
  $server = $index->server();
  $service_info = search_api_get_service_info();
  // We only want to work with Elasticsearch servers.
  $reflection = new ReflectionClass($service_info[$server->class]['class']);
  if ($reflection->isSubclassOf('SearchApiElasticsearchAbstractService')) {
    $elasticsearch_client = new $service_info[$server->class]['class']($server);
    $analysis_settings = $elasticsearch_client->getAnalysisSettings($index);

    // Build our form.
    $form['analyzer'] = array(
      '#title' => t('Analyzers'),
      '#type' => 'fieldset',
      '#description' => t('Select the analyzers to use on this index. If none are selected, the default will be used. See <a href="@elasticsearch_analyzers_url">Elasticsearch Analyzers</a>.', array('@elasticsearch_analyzers_url' => url('http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/analysis-analyzers.html'))),
      '#collapsible' => FALSE,
      '#tree' => TRUE,
    );

    $form['analyzer']['standard'] = array(
      '#title' => t('Standard'),
      '#type' => 'fieldset',
      '#description' => t('An analyzer built using the Standard Tokenizer with the Standard Token Filter, Lower Case Token Filter, and Stop Token Filter.'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
      '#tree' => TRUE,
    );

    $form['analyzer']['standard']['enabled'] = array(
      '#title' => t('Enabled'),
      '#type' => 'checkbox',
      '#default_value' => isset($analysis_settings['analyzer']['standard']) ? 1 : 0,
    );

    $form['analyzer']['standard']['stopwords'] = array(
      '#title' => t('Stopwords'),
      '#type' => 'textarea',
      '#description' => t('A list of stopwords to initialize the stop filter with. Defaults to an empty stopword list.'),
      '#default_value' => !empty($analysis_settings['analyzer']['standard']['stopwords']) ? $analysis_settings['analyzer']['standard']['stopwords'] : '',
    );

    $form['analyzer']['standard']['max_token_length'] = array(
      '#title' => t('Maximum Token Length'),
      '#type' => 'textfield',
      '#description' => t('The maximum token length. If a token is seen that exceeds this length then it is discarded. Defaults to 255.'),
      '#default_value' => !empty($analysis_settings['analyzer']['standard']['max_token_length']) ? $analysis_settings['analyzer']['standard']['max_token_length'] : '',
    );

    $form['analyzer']['simple'] = array(
      '#title' => t('Simple'),
      '#type' => 'fieldset',
      '#description' => t('An analyzer built using a Lower Case Tokenizer.'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
      '#tree' => TRUE,
    );

    $form['analyzer']['simple']['enabled'] = array(
      '#title' => t('Enabled'),
      '#type' => 'checkbox',
      '#default_value' => isset($analysis_settings['analyzer']['simple']) ? 1 : 0,
    );

    $form['analyzer']['whitespace'] = array(
      '#title' => t('Whitespace'),
      '#type' => 'fieldset',
      '#description' => t('An analyzer built using a Whitespace Tokenizer.'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
      '#tree' => TRUE,
    );

    $form['analyzer']['whitespace']['enabled'] = array(
      '#title' => t('Enabled'),
      '#type' => 'checkbox',
      '#default_value' => isset($analysis_settings['analyzer']['whitespace']) ? 1 : 0,
    );

    $form['analyzer']['stop'] = array(
      '#title' => t('Stop'),
      '#type' => 'fieldset',
      '#description' => t('An analyzer built using a Lower Case Tokenizer, with Stop Token Filter.'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
      '#tree' => TRUE,
    );

    $form['analyzer']['stop']['enabled'] = array(
      '#title' => t('Enabled'),
      '#type' => 'checkbox',
      '#default_value' => isset($analysis_settings['analyzer']['stop']) ? 1 : 0,
    );

    $form['analyzer']['stop']['stopwords'] = array(
      '#title' => t('Stopwords'),
      '#type' => 'textarea',
      '#description' => t('A list of stopwords to initialize the stop filter with. Defaults to the English stopwords.'),
      '#default_value' => !empty($analysis_settings['analyzer']['stop']['stopwords']) ? $analysis_settings['analyzer']['stop']['stopwords'] : '',
    );

    $form['analyzer']['keyword'] = array(
      '#title' => t('Keyword'),
      '#type' => 'fieldset',
      '#description' => t('An analyzer that "tokenizes" an entire stream as a single token. This is useful for data like zip codes, ids and so on.'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
      '#tree' => TRUE,
    );

    $form['analyzer']['keyword']['enabled'] = array(
      '#title' => t('Enabled'),
      '#type' => 'checkbox',
      '#default_value' => isset($analysis_settings['analyzer']['keyword']) ? 1 : 0,
    );

    $form['analyzer']['pattern'] = array(
      '#title' => t('Pattern'),
      '#type' => 'fieldset',
      '#description' => t('An analyzer that can flexibly separate text into terms via a regular expression.'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
      '#tree' => TRUE,
    );

    $form['analyzer']['pattern']['enabled'] = array(
      '#title' => t('Enabled'),
      '#type' => 'checkbox',
      '#default_value' => isset($analysis_settings['analyzer']['pattern']) ? 1 : 0,
    );

    $form['analyzer']['pattern']['lowercase'] = array(
      '#title' => t('Lowercase'),
      '#type' => 'checkbox',
      '#default_value' => !empty($analysis_settings['analyzer']['pattern']['lowercase']) ? 1 : 0,
    );

    $form['analyzer']['pattern']['pattern'] = array(
      '#title' => t('Pattern'),
      '#type' => 'textarea',
      '#description' => t('The regular expression pattern. The regular expression should match the token separators, not the tokens themselves. Defaults to \W+.'),
      '#default_value' => !empty($analysis_settings['analyzer']['pattern']['pattern']) ? $analysis_settings['analyzer']['pattern']['pattern'] : '',
    );

    $form['analyzer']['pattern']['flags'] = array(
      '#title' => t('Flags'),
      '#type' => 'textfield',
      '#description' => t('The regular expression flags. Flags should be pipe-separated, eg "CASE_INSENSITIVE|COMMENTS". Check Java Pattern API for more details about flags options.'),
      '#default_value' => !empty($analysis_settings['analyzer']['pattern']['flags']) ? $analysis_settings['analyzer']['pattern']['flags'] : '',
    );

    $form['analyzer']['pattern']['stopwords'] = array(
      '#title' => t('Stopwords'),
      '#type' => 'textarea',
      '#description' => t('A list of stopwords to initialize the stop filter with. Defaults to an empty stopwords list.'),
      '#default_value' => !empty($analysis_settings['analyzer']['pattern']['stopwords']) ? $analysis_settings['analyzer']['pattern']['stopwords'] : '',
    );

    // @TODO Determine proper way to implement language analyzers.
    /*$form['analyzer']['language'] = array(
      '#title' =>t('Language'),
      '#type' => 'fieldset',
      '#description' => t('An analyzer that can flexibly separate text into terms via a regular expression.'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
      '#tree' => TRUE,
    );*/

    $form['analyzer']['snowball'] = array(
      '#title' => t('Snowball'),
      '#type' => 'fieldset',
      '#description' => t('An analyzer that uses the standard tokenizer, with standard filter, lowercase filter, stop filter, and snowball filter.'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
      '#tree' => TRUE,
    );

    $form['analyzer']['snowball']['enabled'] = array(
      '#title' => t('Enabled'),
      '#type' => 'checkbox',
      '#default_value' => isset($analysis_settings['analyzer']['snowball']) ? 1 : 0,
    );

    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Submit'),
      '#validate' => array('search_api_elasticsearch_analysis_form_validate'),
      '#submit' => array('search_api_elasticsearch_analysis_form_submit'),
    );

    // We need to pass certain things to the submit handler.
    $form['#SearchApiIndex'] = $index;
    $form['#SearchApiElasticsearchClient'] = $elasticsearch_client;

    return $form;
  }
}

/**
 * Form validation for the Elasticsearch analysis configuration form.
 *
 * @ingroup forms
 */
function search_api_elasticsearch_analysis_form_validate($form, &$form_state) {
  // @TODO Handle validation for settings.
}

/**
 * Form submit handler for the Elasticsearch analysis configuration form.
 *
 * @ingroup forms
 */
function search_api_elasticsearch_analysis_form_submit($form, &$form_state) {
  $data = array();
  foreach ($form_state['values']['analyzer'] as $analyzer => $settings) {
    if ($settings['enabled'] == TRUE) {
      unset($settings['enabled']);
      $settings['type'] = $analyzer;
      $data['analysis']['analyzer'][$analyzer] = $settings;
    }
  }
  $form['#SearchApiElasticsearchClient']->updateSettings($form['#SearchApiIndex'], $data);
}
