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

  if ($variables['page']['sidebar_right']) {
    $variables['classes_array'][] = 'sidebar-right';
  }
  else {
    $variables['classes_array'][] = 'no-sidebar';
  }

}
/**
 * Implements hook_preprocess_page().
 */
function druiot_preprocess_page(&$variables, $hook) {
  global $user;

  // Header links
  $variables['header_links'] = theme('druiot_header_links');

  // Search form.
  $variables['header_search_form'] = '<form action="/search" id="site-search">
          <input name="s" value="" maxlength="128" class="form-search" type="text" placeholder="Поиск по сообществу">
        </form>';

  // Header profile data.
  if (user_is_anonymous()) {
    $variables['header_profile'] = theme('druiot_auth_anon', array('user' => $user));
  }
  else {
    $variables['header_profile'] = theme('druiot_auth_user', array('user' => $user));
  }

  // Content classes.
  $content_classes_array = array();
  $is_sidebar = FALSE;

  // Check sidebar is available?
  $sidebar_right = render($variables['page']['sidebar_right']);
  $variables['sidebar_right'] = $sidebar_right;
  if ($sidebar_right) {
    $is_sidebar = TRUE;
  }

  // Finally check have sidebar or not.
  if ($is_sidebar) {
    $content_classes_array[] = 'gl col xl-15-24';
  }
  else {
    $content_classes_array[] = 'gl col xl-1-1';
  }

  // Write to variable.
  $variables['content_classes'] = drupal_attributes(
    array(
      'class' => $content_classes_array,
    )
  );
}

/**
 * Implements hook_preprocess_node().
 */
/*
function THEMENAME_preprocess_node(&$variables, $hook) {

}
*/

/**
 * Implements hook_preprocess_comment().
 */
/*
function THEMENAME_preprocess_comment(&$variables, $hook) {

}
*/

/**
 * Implements hook_preprocess_region().
 */
/*
function THEMENAME_preprocess_region(&$variables, $hook) {

}
*/

/**
 * Implements hook_preprocess_block().
 */
/*
function STARTERKIT_preprocess_block(&$variables, $hook) {

}
*/

/**
 * Implements hook_theme().
 * For more flexibility we create some theme definitions.
 */
function druiot_theme() {
  $theme = array();
  global $user;

  // Tracker icon in header.
  $theme['druiot_header_links'] = array(
    'variables' => array(
      'tracker_count' => dlcommunity_tracker_count($user->uid)
      ),
    'template' => 'templates/druiot-header-links',
  );

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