<?php
/**
 * @file
 * vocabulary.tags.inc
 */

$api = '2.0.0';

$data = (object) array(
  'name' => 'Tags',
  'machine_name' => 'tags',
  'description' => 'Use tags to group articles on similar topics into categories.',
  'hierarchy' => '0',
  'module' => 'taxonomy',
  'weight' => '0',
  'rdf_mapping' => array(
    'rdftype' => array(
      0 => 'skos:ConceptScheme',
    ),
    'name' => array(
      'predicates' => array(
        0 => 'dc:title',
      ),
    ),
    'description' => array(
      'predicates' => array(
        0 => 'rdfs:comment',
      ),
    ),
  ),
);

$dependencies = array();

$optional = array();

$modules = array(
  0 => 'taxonomy',
);
