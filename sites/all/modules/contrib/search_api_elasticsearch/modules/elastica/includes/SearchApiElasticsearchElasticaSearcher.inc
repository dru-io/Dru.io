<?php

/**
 * @file
 * Contains searcher for Elastica client.
 */

class SearchApiElasticsearchElasticaSearcher {

  static public function search($type, $query, $options, SearchApiQueryInterface $search_api_query) {
    if (!empty($options['mlt'])) {
      SearchApiElasticsearchElasticaMLT::build($type, $options, $query);
    }

    drupal_alter('search_api_elasticsearch_elastica_query', $query, $search_api_query);

    return $type->search($query);
  }

}
