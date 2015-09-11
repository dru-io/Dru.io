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
  $variables['page']['header_profile'] = array(
    '#theme' => $user->uid ? 'druiot_auth_user' : 'druiot_auth_anon',
    '#user' => $user,
  );
  // Tracker.
  $variables['druiot_tracker']['count'] = druio_tracker_count($user->uid);
  $variables['druiot_tracker']['status'] = _druio_messages_status($user->uid);
}

/**
 * Implements hook_theme().
 */
function druio_theme_theme() {
  global $user;
  return array(
    // Header links in top line of header.
    'druio_theme_header_links' => array(
      'variables' => array(
        'user' => $user,
        'links' => NULL,
      ),
      'template' => 'templates/theme/header-links',
    ),
  );
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

