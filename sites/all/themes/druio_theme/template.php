<?php
/**
 * @file
 * Main hooks for base theme.
 */

/**
 * Implements hook_preprocess_page().
 */
function druio_theme_preprocess_page(&$variables) {
  global $user;

  // Search form.
  $variables['header_search_form'] = format_string(
    '<form action="@action" class="@form_class">
  <input name="s" value="" maxlength="128" class="@input_class" type="text"
         placeholder="@placeholder">
</form>',
    array(
      '@action' => '/search',
      '@form_class' => 'search-form',
      '@input_class' => 'search-input',
      '@placeholder' => 'Поиск по сообществу',
    )
  );

  // Simple menu for header top line. Such as tracker and GitHub.
  $header_links = array(
    array(
      'title' => 'Трекер',
      'href' => '/tracker',
      'classes' => array(
        'tracker',
        _druio_messages_status(),
      ),
      'attributes' => array(
        'data-new-count' => druio_tracker_count(),
      )
    ),
    array(
      'title' => 'GitHub',
      'href' => 'https://github.com/Niklan/Dru.io',
      'classes' => array(
        'github',
      ),
      'attributes' => array(
        'target' => '_blank'
      ),
    )
  );
  $variables['header_links'] = theme('druio_theme_header_links', array('links' => $header_links));

  // Header profile data.
  $header_auth_template = $user->uid ? 'druio_theme_auth_user' : 'druio_theme_auth_anon';
  $variables['header_auth'] = theme($header_auth_template, array('user' => $user));
}

/**
 * Implements hook_theme().
 */
function druio_theme_theme() {
  global $user;

  // Header links in top line of header.
  $theme['druio_theme_header_links'] = array(
    'variables' => array(
      'user' => $user,
      'links' => NULL,
    ),
    'template' => 'templates/theme/header-links',
  );
  // Profile info for anonymous users.
  $theme['druio_theme_auth_anon'] = array(
    'variables' => array('user' => NULL),
    'template' => 'templates/theme/header-auth-anon',
  );
  // Profile in for user.
  $theme['druio_theme_auth_user'] = array(
    'variables' => array('user' => NULL),
    'template' => 'templates/theme/header-auth-user',
  );
  // Frontpage content.
  $theme['druio_theme_frontpage'] = array(
    'variables' => array('user' => NULL),
    'template' => 'templates/theme/frontpage',
  );

  return $theme;
}

/**
 * Implements hook_preprocess_HOOK():druio_theme_header_links.
 * @param $variables
 */
function druio_theme_preprocess_druio_theme_header_links(&$variables) {
  foreach ($variables['links'] as $key => $link) {
    $variables['links'][$key]['classes'] = 'link ' . implode(' ', $link['classes']);
    $variables['links'][$key]['attributes'] = drupal_attributes($link['attributes']);
  }
}

/**
 * Implements hook_preprocess_HOOK():druio_theme_auth_user.
 * @param $variables
 */
function druio_theme_preprocess_druio_theme_auth_user(&$variables) {
  $user = $variables['user'];
  $variables['picture'] = druio_get_user_picture($user->uid);
  $variables['username'] = $user->name;
}


/**
 * Implements template_preprocess_html().
 */
function druio_theme_preprocess_html(&$variables) {
  // Redefine body classes.
  $variables['classes_array'] = array();
  $element_children = element_children($variables['page']['sidebar']);
  if (!empty($element_children)) {
    $variables['classes_array'][] = 'sidebar';
  }
  else {
    $variables['classes_array'][] = 'no-sidebars';
  }

  if (drupal_is_front_page()) {
    $variables['classes_array'][] = 'frontpage';
  }
  if (user_is_logged_in()) {
    $variables['classes_array'][] = 'registered';
  }
  else {
    $variables['classes_array'][] = 'anonymous';
  }
}