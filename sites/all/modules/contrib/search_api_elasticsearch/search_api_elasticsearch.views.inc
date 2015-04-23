<?php

/**
 * @file
 * Views hook implementations for the Search API Elasticsearch module.
 */

/**
 * Implements hook_views_data_alter().
 */
function search_api_elasticsearch_views_data_alter(&$data) {
  foreach (search_api_server_load_multiple(FALSE, array('class' => 'search_api_elasticsearch_elastica_service'), TRUE) as $server) {
    foreach (search_api_index_load_multiple(FALSE, array('server' => $server->machine_name), TRUE) as $index) {
      $key = 'search_api_index_' . $index->machine_name;
      $table = &$data[$key];
      $table['search_api_views_fulltext']['filter']['handler'] = 'SearchApiElasticsearchViewsHandlerFilterFulltext';
      $table['search_api_views_more_like_this']['argument']['handler'] = 'SearchApiElasticsearchViewsHandlerArgumentMoreLikeThis';
    }
  }
}
