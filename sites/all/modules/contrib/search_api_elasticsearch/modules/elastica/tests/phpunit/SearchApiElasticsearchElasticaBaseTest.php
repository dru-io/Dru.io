<?php

abstract class SearchApiElasticsearchElasticaBaseTest extends SearchApiElasticsearchBaseTest {

  /**
   * Provides transports to test against.
   */
  public function transportProvider() {
    $options = array(
      array('Http'),
      array('Https'),
      array('Memcache'),
      array('Null'),
    );

    if (class_exists('\GuzzleHttp\Client')) {
      $options[] = array('Guzzle');
    }

    if (class_exists('\Elasticsearch\RestClient')) {
      $options[] = array('Thrift');
    }

    return $options;
  }
}
