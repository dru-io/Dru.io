<?php

class SearchApiElasticsearchElasticaFacetTest extends SearchApiElasticsearchElasticaBaseTest {

  public function setUp() {
    $this->_server = $this->createServer('elastica_test_facet', 'search_api_elasticsearch_elastica_service', array(array('host' => '127.0.0.1', 'port' => '9200')));
    $this->_client = new SearchApiElasticsearchElastica($this->_server);
    $this->_index = $this->createIndex('elastica_test_facet_index', 'node', 'elastica_test_facet');
    $this->_index->options['fields'] = array(
      'nid' => array(
        'type' => 'integer',
      ),
      'title' => array(
        'type' => 'text',
      ),
    );
    $this->_items = array(
      '1' => array(
        'nid' => array(
          'value' => 1,
        ),
        'title' => array(
          'value' => 'batman',
        ),
      ),
      '2' => array(
        'nid' => array(
          'value' => 2,
        ),
        'title' => array(
          'value' => 'bruce wayne',
        ),
      ),
      '3' => array(
        'nid' => array(
          'value' => 3,
        ),
        'title' => array(
          'value' => 'batman bruce wayne',
        ),
      ),
    );
    $this->_client->indexItems($this->_index, $this->_items);
    $this->_client->getElasticaIndex($this->_index)->refresh();
    $this->_query = new SearchApiQuery($this->_index);
    $facets = array(
      'title' => array(
        'field' => 'title',
        'limit' => 50,
        'operator' => 'and',
        'min_count' => 1,
        'missing' => 0,
      ),
    );
    $this->_query->setOption('search_api_facets', $facets);
    $this->_query->condition('title', 'batman');
  }

  /**
   * testFacets
   *
   * @access public
   * @dataProvider transportProvider
   * @return void
   */
  public function testFacets($transport) {
    $this->_client->setTransport($transport);
    $result_set = $this->_client->search($this->_query);
    $this->assertEquals(2, $result_set['result count']);
  }

}
