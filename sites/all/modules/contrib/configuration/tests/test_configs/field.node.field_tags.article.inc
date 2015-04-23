<?php
/**
 * @file
 * field.node.field_tags.article.inc
 */

$api = '2.0.0';

$data = array(
  'field_config' => array(
    'settings' => array(
      'allowed_values' => array(
        0 => array(
          'vocabulary' => 'tags',
          'parent' => 0,
        ),
      ),
    ),
    'entity_types' => array(),
    'translatable' => '0',
    'storage' => array(
      'type' => 'field_sql_storage',
      'settings' => array(),
      'module' => 'field_sql_storage',
      'active' => '1',
      'details' => array(
        'sql' => array(
          'FIELD_LOAD_CURRENT' => array(
            'field_data_field_tags' => array(
              'tid' => 'field_tags_tid',
            ),
          ),
          'FIELD_LOAD_REVISION' => array(
            'field_revision_field_tags' => array(
              'tid' => 'field_tags_tid',
            ),
          ),
        ),
      ),
    ),
    'foreign keys' => array(
      'tid' => array(
        'table' => 'taxonomy_term_data',
        'columns' => array(
          'tid' => 'tid',
        ),
      ),
    ),
    'indexes' => array(
      'tid' => array(
        0 => 'tid',
      ),
    ),
    'field_name' => 'field_tags',
    'type' => 'taxonomy_term_reference',
    'module' => 'taxonomy',
    'active' => '1',
    'locked' => '0',
    'cardinality' => '-1',
    'deleted' => '0',
    'columns' => array(
      'tid' => array(
        'type' => 'int',
        'unsigned' => TRUE,
        'not null' => FALSE,
      ),
    ),
  ),
  'field_instance' => array(
    'label' => 'Tags',
    'description' => 'Enter a comma-separated list of words to describe your content.',
    'widget' => array(
      'type' => 'taxonomy_autocomplete',
      'weight' => -4,
      'settings' => array(
        'size' => 60,
        'autocomplete_path' => 'taxonomy/autocomplete',
      ),
      'module' => 'taxonomy',
    ),
    'display' => array(
      'default' => array(
        'type' => 'taxonomy_term_reference_link',
        'weight' => 10,
        'label' => 'above',
        'settings' => array(),
        'module' => 'taxonomy',
      ),
      'teaser' => array(
        'type' => 'taxonomy_term_reference_link',
        'weight' => 10,
        'label' => 'above',
        'settings' => array(),
        'module' => 'taxonomy',
      ),
    ),
    'settings' => array(
      'user_register_form' => FALSE,
    ),
    'required' => FALSE,
    'field_name' => 'field_tags',
    'entity_type' => 'node',
    'bundle' => 'article',
    'deleted' => '0',
    'default_value' => NULL,
  ),
);

$dependencies = array(
  'content_type.article' => 'content_type.article',
  'vocabulary.tags' => 'vocabulary.tags',
);

$optional = array();

$modules = array(
  0 => 'field_sql_storage',
  1 => 'taxonomy',
);
