<?php

/**
 * @file
 * Contains handler for More Like This queries.
 */

class SearchApiElasticsearchElasticaMLT {

  static public function build($type, $options, $query) {
    $mlt_query = new Elastica\Query\MoreLikeThis();
    $mlt_query->setFields(array_values($options['mlt']['fields']));
    $mlt_query->setParam('ids', array($options['mlt']['id']));
    $mlt_query->setMinDocFrequency($options['mlt']['min_doc_freq']);
    $mlt_query->setMinTermFrequency($options['mlt']['min_term_freq']);
    $query->setQuery($mlt_query);

    if (!empty($options['query_search_filter'])) {
      $query->setQuery(new Elastica\Query\Filtered($mlt_query, $options['query_search_filter']));
    }
  }

}
