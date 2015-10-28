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

  $status = drupal_get_http_header("status");
  if ($status == "403 Forbidden") {
    _druio_theme_preprocess_page_403($variables);
  }
  drupal_add_feed(url('rss/questions', array('absolute' => TRUE)), variable_get('site_name') . ': Вопросы');
  drupal_add_feed(url('rss/posts', array('absolute' => TRUE)), variable_get('site_name') . ': Публикации');
}

/**
 * Helper function for adding variables to page--403.tpl.php file.
 *
 * @param $variables
 */
function _druio_theme_preprocess_page_403(&$variables) {
  $variables['is_anon'] = user_is_anonymous();

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

  $status = drupal_get_http_header("status");
  if ($status == "404 Not Found") {
    $variables['classes_array'][] = drupal_html_class('page-404');
  }
  if ($status == "403 Forbidden") {
    $variables['classes_array'][] = drupal_html_class('page-403');
  }
}

/**
 * Imlements hook_preprocess_hook().
 *
 * @param $variables
 */
function druio_theme_preprocess_flag(&$variables) {
  global $user;
  $user_wrapper = entity_metadata_wrapper('user', $user);
  if ($variables['flag']->name == 'ready2work' && !user_is_anonymous()) {
    $field_user_contacts = $user_wrapper->field_user_contacts->value();
    if ($field_user_contacts) {
      $variables['has_contacts'] = TRUE;
    }
    else {
      $variables['has_contacts'] = FALSE;
    }

    $node = menu_get_object();
    $variables['is_active'] = FALSE;
    if (isset($node) && $node->type = 'order') {
      $node_wrapper = entity_metadata_wrapper('node', $node);
      $field_order_status_term = $node_wrapper->field_order_status_term->getIdentifier();
      if ($field_order_status_term == 3420) {
        $variables['is_active'] = TRUE;
      }
    }

    $variables['no_contacts_message'] = format_string(
      '<div class="no-contacts-warning">@text !link</div>',
      array(
        '@text' => 'Для того чтобы отозваться на заявку, вам необходимо указать личные контактные данные в своём профиле.',
        '!link' => l('Добавить контактную информацию.', '/user/' . $user->uid . '/edit', array(
          'fragment' => 'edit-field-user-contacts',
          'query' => array(
            'destination' => current_path(),
          )
        )),
      )
    );
  }
}
