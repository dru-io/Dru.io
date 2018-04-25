<?php

/**
 * @file
 * Configuration forms and helper functions for VotingAPI module.
 */

/**
 * Administrative settings for VotingAPI.
 */
function votingapi_settings_form($form_state) {
  $period = array(0 => t('Immediately')) + drupal_map_assoc(array(
      300,
      900,
      1800,
      3600,
      10800,
      21600,
      32400,
      43200,
      86400,
      172800,
      345600,
      604800,
    ), 'format_interval') + array(-1 => t('Never'));

  $form['votingapi_anonymous_window'] = array(
    '#type' => 'select',
    '#title' => t('Anonymous vote rollover'),
    '#description' => t('The amount of time that must pass before two anonymous votes from the same computer are considered unique. Setting this to \'never\' will eliminate most double-voting, but will make it impossible for multiple anonymous on the same computer (like internet cafe customers) from casting votes.'),
    '#default_value' => variable_get('votingapi_anonymous_window', 86400),
    '#options' => $period,
  );

  $form['votingapi_user_window'] = array(
    '#type' => 'select',
    '#title' => t('Registered user vote rollover'),
    '#description' => t('The amount of time that must pass before two registered user votes from the same user ID are considered unique. Setting this to \'never\' will eliminate most double-voting for registered users.'),
    '#default_value' => variable_get('votingapi_user_window', -1),
    '#options' => $period,
  );

  $form['votingapi_calculation_schedule'] = array(
    '#type' => 'radios',
    '#title' => t('Vote tallying'),
    '#description' => t('On high-traffic sites, administrators can use this setting to postpone the calculation of vote results.'),
    '#default_value' => variable_get('votingapi_calculation_schedule', 'immediate'),
    '#options' => array(
      'immediate' => t('Tally results whenever a vote is cast'),
      'cron' => t('Tally results at cron-time'),
      'manual' => t('Do not tally results automatically: I am using a module that manages its own vote results.'),
    ),
  );

  return system_settings_form($form);
}

/**
 * Developer tool to generate dummy votes.
 */
function votingapi_generate_votes_form() {
  $form['node_types'] = array(
    '#type' => 'checkboxes',
    '#title' => t('Which node types should receive votes?'),
    '#options' => node_type_get_names(),
    '#default_value' => array_keys(node_type_get_names()),
  );

  $form['vote_type'] = array(
    '#type' => 'select',
    '#title' => t('What type of votes should be generated?'),
    '#options' => array(
      'percent' => t('Percentage (Fivestar style)'),
      'points' => t('Point-based (Digg style)'),
    ),
    '#default_value' => 'percent',
  );

  $form['kill_votes'] = array(
    '#type' => 'checkbox',
    '#title' => t('Delete existing votes before generating new ones.'),
    '#default_value' => FALSE,
  );
  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Do it!'),
  );
  return $form;
}

/**
 * Submit handler for votingapi_generate_votes_form.
 */
function votingapi_generate_votes_form_submit($form, &$form_state) {
  $options = array(
    'types' => $form_state['values']['node_types'],
    'kill' => $form_state['values']['kill_votes'],
  );
  require_once(drupal_get_path('module', 'votingapi') . '/votingapi.devel.inc');
  votingapi_generate_votes('node', $form_state['values']['vote_type'], $options);
}

/**
 * Rebuild the voting results and clear the voting cache.
 */
function votingapi_rebuild_form($form, $form_state) {
  $query = db_select('votingapi_vote')
    ->fields('votingapi_vote', array('tag'))
    ->distinct(TRUE);

  $results = $query
    ->execute()
    ->fetchAll(PDO::FETCH_ASSOC);

  $tags = array('All Tags');
  foreach ($results as $tag_type) {
    $tags[] = $tag_type['tag'];
  }

  $form['results'] = array(
    '#type' => 'fieldset',
    '#title' => t('Rebuild voting results'),
  );
  $form['results']['votingapi_rebuild_content_selected_tag'] = array(
    '#type' => 'select',
    '#title' => t('Content tags'),
    '#options' => $tags,
  );
  $form['results']['votingapi_rebuild_content_tags'] = array(
    '#type' => 'hidden',
    '#value' => $tags,
  );
  $form['results']['votingapi_recalculate_all'] = array(
    '#type' => 'submit',
    '#value' => t('Rebuild all Voting Results'),
  );
  $form['results']['help'] = array(
    '#prefix' => '<div class="description">',
    '#markup' => t('This action rebuilds all voting results, and may be a lengthy process.')
      . '<br>' . t("In regular site confirurations, this process does not re-determine <em>validity</em> of votes, so if you change 'rollover' settings on the General Settings page, this does not mean that e.g. Fivestar votes attached to comments will be re-evaluated."),
    '#suffix' => '</div>',
  );

  return $form;
}

/**
 * Submit handler for votingapi_rebuild_form.
 *
 * Recalculates voting results for all content, using the batch API.
 */
function votingapi_rebuild_form_submit($form, &$form_state) {
  $tags = $form_state['values']['votingapi_rebuild_content_tags'];
  $selected_tag_index = $form_state['values']['votingapi_rebuild_content_selected_tag'];

  // Change the list of tags to remove accordingly.
  if ($selected_tag_index > 0) {
    $tags = array($tags[$selected_tag_index]);
  }
  else {
    unset($tags[0]);
  }

  $operations = array();
  foreach ($tags as $tag) {
    $operations[] = array('_votingapi_results_rebuild', array($tag));
  }
  $batch = array(
    'title' => t('Recalculating votes'),
    'operations' => $operations,
    'init_message' => 'Preparing to recalculate',
    'finished' => '_votingapi_results_rebuild_finished',
    'file' => drupal_get_path('module', 'votingapi') . '/votingapi.admin.inc',
  );
  // Set the recalculation batch so it can be processed.
  batch_set($batch);

  // Redirect the user to the votingapi rebuild page.
  $form_state['redirect'] = 'admin/config/search/votingapi/rebuild';
}

/**
 * Batch API callback for the vote rebuilding operation.
 */
function _votingapi_results_rebuild($tag, &$context) {
  if (empty($context['sandbox']['max_entity_id'])) {

    // Set up the sandbox to work within.
    $context['sandbox']['current_entity_id'] = 0;
    $context['sandbox']['current_entity_types'] = array();

    // Get the lowest/highest entity_id for this context, for later comparisons.
    $res = db_query('SELECT MIN(entity_id) as min_id, MAX(entity_id) as max_id FROM {votingapi_vote} WHERE tag = :tag', array(':tag' => $tag))->fetchAssoc();
    $context['sandbox']['max_entity_id'] = $res['max_id'];
    $context['sandbox']['min_entity_id'] = $res['min_id'] - 1;
    $context['sandbox']['max_entity_id_type'] = db_query('SELECT max(entity_type) FROM {votingapi_vote} WHERE entity_id = :id AND tag = :tag',
      array(
        ':id' => $context['sandbox']['max_entity_id'],
        ':tag' => $tag
      ))->fetchField();

    $context['results']['count'] = 0;
  }

  // Grab all entity_ids from the votingapi_vote table.
  // Theoretically, there can be an endless loop if there are $limit entities
  // (of different types) with the same entity_id... We'll take that chance.
  $limit = 100;
//  $result = db_query_range('SELECT DISTINCT entity_type, entity_id FROM {votingapi_vote} WHERE tag = :tag AND entity_id >= :cid ORDER BY entity_id  ASC',
  $result = db_query_range('SELECT DISTINCT entity_type, entity_id FROM {votingapi_vote}
      WHERE tag = :tag AND (entity_id > :current_entity_id OR (entity_id = :current_entity_id AND entity_type > :current_entity_type))
      ORDER BY entity_id, entity_type', 0, $limit, array(
    ':tag' => $tag,
    ':current_entity_id' => $context['sandbox']['current_entity_id'],
    ':current_entity_type' => $context['sandbox']['current_entity_type']
  ));

  foreach ($result as $row) {
    // Force vote recalculation for this entity.
    votingapi_recalculate_results($row->entity_type, $row->entity_id, TRUE);
    $context['results']['count']++;
    $context['sandbox']['current_entity_id'] = $row->entity_id;
    $context['sandbox']['current_entity_type'] = $row->entity_type;
  }

  // Check if the process has not finished yet.
  if ($context['sandbox']['current_entity_id'] != $context['sandbox']['max_entity_id']
    || $context['sandbox']['current_entity_type'] != $context['sandbox']['max_entity_id_type']
  ) {
    // Make sure 'finished' < 1. Using entity IDs here may cause the progress
    // bar to behave eratically, but on the other hand, we make sure we actually
    // process all items instead of the number of items we had at the start.
    // (If we compared the items processed against the number of items at the
    // start of the process, we would not process the highest item if an extra
    // (older) entity got a vote, during processing.)
    // Using min_entity_id as 'base' should help smoothness at the start a bit.
    $context['finished'] =
      ($context['sandbox']['current_entity_id'] - $context['sandbox']['min_entity_id'])
      / ($context['sandbox']['max_entity_id'] + 1 - $context['sandbox']['min_entity_id']);
  }
}

/**
 * Batch API callback for finished recalculation batch operations.
 */
function _votingapi_results_rebuild_finished($success, $results, $operations) {
  if ($success) {
    $message = format_plural($results['count'], 'One content item was recalculated.', '@count content items were recalculated');
  }
  else {
    $message = t('The voting results rebuild process encountered an error.');
  }
  drupal_set_message($message);
}
