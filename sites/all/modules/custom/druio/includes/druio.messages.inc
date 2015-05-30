<?php

/**
 * @file
 * Create tracking message
 */

/**
 * @param $node
 */
function druio_node_insert($node) {
  if ($node->type == 'answer') {
    $question_node = node_load($node->field_answer_question_reference['und'][0]['target_id']);
    _druio_tracker_create($question_node->nid, $question_node->uid);
  }
}

/**
 * @param $comment
 * @throws \EntityMetadataWrapperException
 */
function druio_comment_insert($comment) {
  global $user;
  $node = entity_metadata_wrapper('node', $comment->nid);
  switch ($node->type->value()) {
    case 'question':
      $nid = $comment->nid;
      $question_node = node_load($nid);
      // prevent notify for own comments
      if ($user->uid != $question_node->uid) {
        _druio_tracker_create($nid, $question_node->uid);
      }
      break;
    case 'answer':
      $nid = $node->field_answer_question_reference->value()->nid;
      $uid = $node->field_answer_question_reference->value()->uid;
      // prevent notify for own comments
      if ($user->uid != $node->author->uid->value()) {
        _druio_tracker_create($nid, $uid);
        _druio_tracker_create($nid, $node->value()->uid);
      }
      break;
    case 'post':
      $nid = $comment->nid;
      $post_node = node_load($nid);
      // prevent notify for own comments
      if ($user->uid != $post_node->uid) {
        _druio_tracker_create($nid, $post_node->uid);
      }
      break;
  }
}

/**
 * Return tracking messages for user
 *
 * @param $uid - The user id for search
 * @return mixed - Status of viewed messages, default 0
 */
function druio_tracker_list($uid) {
  $query = db_select('eck_track_message', 'e')
    ->fields('e', array('id', 'nid', 'uid', 'linked_url'))
    ->condition('e.uid', $uid)
    ->execute()
    ->fetchAll();

    return $query;
}

/**
 * Return count of unread tracking messages
 *
 * @param null $uid - The user id for serach
 * @return mixed - Status of viewed messages, default 0
 */
function druio_tracker_count($uid = NULL) {
  global $user;
  if (!$uid) {
    $uid = $user->uid;
  }
  $query = db_select('eck_track_message', 'e')
    ->fields('e', array('uid'))
    ->condition('e.uid', $uid)
    ->countQuery()
    ->execute()
    ->fetchField();
  return $query;
}

/**
 * @param null $uid
 * @return string
 */
function _druio_messages_status($uid = NULL) {
  global $user;
  if (!$uid) {
    $uid = $user->uid;
  }
  $query = db_select('eck_track_message', 'e')
    ->fields('e', array('uid'))
    ->condition('e.uid', $uid)
    ->countQuery()
    ->execute()
    ->fetchField();

  if ($query) {
    return 'new';
  }
  else return '';
}

/**
 * Add information for use entity property in views relationship.
 *
 * @param $info
 * @return mixed
 */
function druio_entity_property_info_alter(&$info) {
  $info['track_message']['properties']['nid']['type'] = 'node';
  $info['track_message']['properties']['uid']['type'] = 'user';
  return $info;
}

/**
 * Implements hook_node_view
 * Delete viewed nodes from tracker
 *
 * @param $node
 * @param $view_mode
 * @param $langcode
 */
function druio_node_view($node, $view_mode, $langcode) {
  $valid_types = $node->type == 'question' || $node->type == 'post';
  if (user_is_logged_in() && $valid_types && $view_mode == 'full') {
    global $user;
    _druio_tracker_delete($node->nid, $user->uid);
  }
}

/**
 * Create entity
 *
 * @param $nid
 * @param $uid
 */
function _druio_tracker_create($nid, $uid) {
  $entity = entity_create('track_message', array('type' => 'track_message'));
  $wrapper = entity_metadata_wrapper('track_message', $entity);
  $wrapper->nid = $nid;
  $wrapper->uid = $uid;
  $wrapper->linked_url = drupal_get_path_alias('node/'.$nid);
  $wrapper->save();
}

/**
 * @param $nid
 * @param $uid
 */
function _druio_tracker_delete($nid, $uid) {
  $deleted = db_delete('eck_track_message')
    ->condition('nid', $nid)
    ->condition('uid', $uid)
    ->execute();
}
