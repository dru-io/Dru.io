<?php

/**
 * Form for batch add terms.
 */
function batch_add_terms_form($form, $form_state, $vocabulary) {
  $form['terms'] = array(
    '#type' => 'textarea',
    '#title' => t('Terms'),
    '#description' => t('Enter one term name per line.'),
    '#required' => TRUE,
    '#rows' => 15,
  );

  $form['check_duplicates'] = array(
    '#type' => 'checkbox',
    '#title' => t('Check for duplicates'),
    '#description' => t('Enable this option if you do not want to add existing terms.'),
  );

  $form['actions'] = array(
    '#type' => '#actions',
  );

  $form['actions']['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Add'),
  );

  return $form;
}

/**
 * Batch add terms.
 */
function batch_add_terms_form_submit($form, &$form_state) {
  $vocabulary = $form_state['build_info']['args'][0];
  $terms_names = trim($form_state['values']['terms']);
  $terms_names_array = explode("\n", $terms_names);
  $parent_terms = array();
  $weight = 0;

  foreach ($terms_names_array as $term_name) {
    $term_depth = 0;
    if (preg_match('#^(-+)(.+)#', $term_name, $matches)) {
      $term_depth = drupal_strlen($matches[1]);
      $term_name = $matches[2];
    }

    $term_name = trim($term_name);
    if ($form_state['values']['check_duplicates'] && taxonomy_get_term_by_name($term_name, $vocabulary->machine_name)) {
      continue;
    }

    $term = (object)array(
      'vid' => $vocabulary->vid,
      'name' => $term_name,
      'parent' => $term_depth ? $parent_terms[$term_depth - 1] : 0,
      'weight' => $weight++,
    );
    taxonomy_term_save($term);

    $parent_terms[$term_depth] = $term->tid;
  }

  drupal_set_message(t('Added @count new terms', array('@count' => count($terms_names_array))));
  $form_state['redirect'] = 'admin/structure/taxonomy/' . $vocabulary->machine_name;
}

/**
 * Override theme_taxonomy_overview_vocabularies().
 */
function batch_add_terms_taxonomy_overview_vocabularies($variables) {
  $form = $variables['form'];

  $rows = array();

  foreach (element_children($form) as $key) {
    if (isset($form[$key]['name'])) {
      $vocabulary = &$form[$key];

      $row = array();
      $row[] = drupal_render($vocabulary['name']);
      if (isset($vocabulary['weight'])) {
        $vocabulary['weight']['#attributes']['class'] = array('vocabulary-weight');
        $row[] = drupal_render($vocabulary['weight']);
      }
      $row[] = drupal_render($vocabulary['edit']);
      $row[] = drupal_render($vocabulary['list']);
      $row[] = drupal_render($vocabulary['add']);
      $row[] = drupal_render($vocabulary['batch_add']);
      $rows[] = array('data' => $row, 'class' => array('draggable'));
    }
  }

  $header = array(t('Vocabulary name'));
  if (isset($form['actions'])) {
    $header[] = t('Weight');
    drupal_add_tabledrag('taxonomy', 'order', 'sibling', 'vocabulary-weight');
  }
  $header[] = array('data' => t('Operations'), 'colspan' => '4');
  return theme('table', array('header' => $header, 'rows' => $rows, 'empty' => t('No vocabularies available. <a href="@link">Add vocabulary</a>.', array('@link' => url('admin/structure/taxonomy/add'))), 'attributes' => array('id' => 'taxonomy'))) . drupal_render_children($form);
}
