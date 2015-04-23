[![Build Status](https://travis-ci.org/VeggieMeat/search_api_elasticsearch.svg?branch=7.x-1.x)](https://travis-ci.org/VeggieMeat/search_api_elasticsearch)
[![Coverage Status](https://coveralls.io/repos/VeggieMeat/search_api_elasticsearch/badge.png?branch=7.x-1.x)](https://coveralls.io/r/VeggieMeat/search_api_elasticsearch?branch=7.x-1.x)

CONTENTS
--------

 * Introduction
 * Installation
 * Documentation
 * Automated Tests
 * Contributing

INTRODUCTION
------------

Current Maintainer: Brian Altenhofel <brian.altenhofel@vmdoh.com>

Elasticsearch is a powerful schema-less search engine.

Search API Elasticsearch provides a backend allowing the Search API module
to use an Elasticsearch server or cluster.

REQUIREMENTS
------------

 * Drupal 7
 * Search API module
 * Elasticsearch 1.3.0+

INSTALLATION
------------

DOCUMENTATION
-------------

@see https://www.drupal.org/node/2303957

AUTOMATED TESTS
---------------

After every commit, we run PHPUnit tests and Simpletests on Travis-CI. Current
build status can be seen at the top of this document.

Additionally, we also use Coveralls to ensure that we can be confident that code
is actually being tested.

CONTRIBUTING
------------

Feel free to submit patches in the Drupal.org issue queue or via Github pull
requests. If you can, please include test coverage for your contributions.
