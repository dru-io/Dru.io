<?php
/**
 * @file
 * Here you can use drupal hooks.
 *
 * Write hook on your own or just uncomment predefined common hooks.
 * Don't forget to replace THEMENAME.
 */

/**
 * Implements template_preprocess_html().
 */
function druiot_preprocess_html(&$variables) {
  // Redefine body classes.
  $variables['classes_array'] = array();

  $element_children = element_children($variables['page']['sidebar_right']);
  if (!empty($element_children)) {
    $variables['classes_array'][] = 'sidebar-right';

    if (drupal_is_front_page()) {
      $variables['classes_array'][] = 'frontpage';
    }
  }
  else {
    $variables['classes_array'][] = 'no-sidebar';
  }

  if(user_is_logged_in()) {
    $variables['classes_array'][] = 'registered';
  }
  else {
    $variables['classes_array'][] = 'anonymous';
  }

}
/**
 * Implements hook_preprocess_page().
 */
function druiot_preprocess_page(&$variables) {
  global $user;

  // Header links
  //  $variables['header_links'] = theme('druiot_header_links');

  // Search form.
  $variables['page']['header_search_form'] = array(
    '#markup' => '<form action="/search" id="site-search">
                    <input name="s" value="" maxlength="128" class="form-search" type="text" placeholder="Поиск по сообществу">
                  </form>'
  );

  // Header profile data.
  $variables['page']['header_profile'] = array(
    '#theme' => $user->uid ? 'druiot_auth_user' : 'druiot_auth_anon',
    '#user' => $user,
  );

  //tracker
  $variables['druiot_tracker']['count'] = druio_tracker_count($user->uid);
  $variables['druiot_tracker']['status'] = _druio_messages_status($user->uid);
}

/**
 * Implements hook_theme().
 * For more flexibility we create some theme definitions.
 */
function druiot_theme() {
  $theme = array();
  global $user;

  // Profile info for anonymous users.
  $theme['druiot_auth_anon'] = array(
    'variables' => array('user' => NULL),
    'template' => 'templates/druiot-auth-anon',
  );

  // Profile in for user.
  $theme['druiot_auth_user'] = array(
    'variables' => array('user' => NULL),
    'template' => 'templates/druiot-auth-user',
  );

  // Frontpage content
  $theme['druiot_frontpage_content'] = array(
    'variables' => array('user' => $user),
    'template' => 'templates/druiot-frontpage-content',
  );

  return $theme;
}

/**
 * Implements hook_node_view_alter().
 */
function druiot_node_view_alter(&$build) {
  // Hide 'New comments', 'Comment count' and 'Read more' in links for answer.
  if (
    $build['#entity_type'] == 'node' &&
    $build['#bundle'] == 'answer' &&
    $build['#view_mode'] == 'teaser'
  ) {
    unset($build['links']['node']['#links']['node-readmore']);
    unset($build['links']['comment']['#links']['comment-comments']);
    unset($build['links']['comment']['#links']['comment-new-comments']);
  }
}
