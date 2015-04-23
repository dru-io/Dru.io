<?php

/**
 * @file
 * Contains base test class for Search API Elasticsearch.
 */

abstract class SearchApiElasticsearchBaseTest extends \PHPUnit_Framework_TestCase {

  /**
   * Create Search API server.
   */
  protected function createServer($name = 'test', $class, $options = array()) {
    return $this->_server = entity_create('search_api_server', array(
      'name' => $name,
      'machine_name' => $name,
      'class' => $class,
      'options' => $options,
      'enabled' => 1,
      'status' => 1,
    ));
  }

  /**
   * Create Search API index.
   */
  protected function createIndex($name = 'test', $type, $server, $options = array()) {
    return $this->_index = entity_create('search_api_index', array(
      'name' => $name,
      'machine_name' => $name,
      'enabled' => 1,
      'item_type' => $type,
      'server' => $server,
      'options' => $options,
    ));
  }

  /**
   * Provides different transports to test.
   */
  abstract public function transportProvider();

}
