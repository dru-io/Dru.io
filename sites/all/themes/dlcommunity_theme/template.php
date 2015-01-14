<?php
/**
 * @file
 * Here you can use drupal hooks.
 *
 * Write hook on your own or just uncomment predefined common hooks.
 * Don't forget to replace THEMENAME.
 */

/**
 * Implements hook_preprocess_html().
 */
/*
function THEMENAME_preprocess_html(&$variables, $hook) {

}
*/

/**
 * Implements hook_preprocess_page().
 */
/*
function THEMENAME_preprocess_page(&$variables, $hook) {

}
*/

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
 * Implements hook_preprocess_page().
 */
function dlcommunity_theme_preprocess_page(&$variables, $hook) {
  $classes_array = array();
  $breadcrumb_classes = array();
  $is_sidebar = FALSE;

  // Check sidebar is available?
  $sidebar_first = render($variables['page']['sidebar_first']);
  $variables['sidebar_first'] = $sidebar_first;
  if ($sidebar_first) {
    $is_sidebar = TRUE;
  }

  // Check node checkbox.
  if (isset($variables['node'])) {
    $is_fullpage = isset($variables['node']->field_fullpage['und'][0]['value']) ? $variables['node']->field_fullpage['und'][0]['value'] : false;
  }
  else {
    $is_fullpage = FALSE;
  }

  if ($is_fullpage) {
    $is_sidebar = FALSE;
    $variables['sidebar_first'] = FALSE;
  }

  // Finally check need sidebar or not.
  if ($is_sidebar) {
    $classes_array[] = 'gl-p-1';
    $classes_array[] = 'gl-s-1 gl-s-md-14-24 gl-s-lg-17-24 gl-s-xl-18-24';
  }
  else {
    $classes_array[] = 'gl-s-1';
    $breadcrumb_classes[] = 'gl-p-1';
    /*if (!$is_fullpage) {
      $classes_array[] = 'p-box';
    }*/
  }

  $variables['content_classes'] = drupal_attributes(
    array(
      'class' => $classes_array,
    )
  );

  $variables['breadcrumb_classes'] = drupal_attributes(
    array(
      'class' => $breadcrumb_classes,
    )
  );
}

/**
 * Return buttons for login or profile.
 */
function dlcommunity_theme_get_auth_buttons() {
  if (user_is_anonymous()) {
    return '<a href="/user/" id="user-register-button">Войти</a>';
  }
  else {
    return '<a href="/user" id="user-profile-button">Профиль</a>';
  }
}

/**
 * Implements hook_preprocess_region().
 */
function dlcommunity_theme_preprocess_region(&$vars) {
  if ($vars['region'] == 'footer') {
    $count = count(element_children($vars['elements']));
    $vars['footer_block_count'] = $count;
  }
}


/**
 * Plural function for Russian words.
 */
function dlcommunity_theme_prural($number, $endingArray) {
  $number = $number % 100;
  if ($number >= 11 && $number <= 19) {
    $ending = $endingArray[2];
  }
  else {
    $i = $number % 10;
    switch ($i) {
      case (0):
        $ending = $endingArray[2];
        break;
      case (1):
        $ending = $endingArray[0];
        break;
      case (2):
      case (3):
      case (4):
        $ending = $endingArray[1];
        break;
      default:
        $ending = $endingArray[2];
    }
  }
  return $ending;
}

/**
 * Используем hook_theme().
 *
 * Темизации и шаблоны.
 */
function dlcommunity_theme_theme() {
  return array(
    'question_node_form' => array(
      'arguments' => array(
        'form' => NULL
      ),
      'template' => 'templates/questions/question-node-form',
      'render element' => 'form'
    ),
    // user-edit-form.tpl.php
    /*'user_profile_form' => array(
      'arguments' => array(
        'form' => NULL
      ),
      'template' => 'templates/user/user-edit-form',
      'render element' => 'form'
    ),*/
  );
}

function dlcommunity_theme_html_head_alter(&$head_elements) {
  foreach ($head_elements as $key => $element) {
    //dpm($element);
  }
}

/**
 * Implements hook_block_view_alter().
 */
function dlcommunity_theme_block_view_alter(&$data, $block) {
  // Alter views block for search api to theme.
  // This is exposed filter (block) from search page view.
  /*if ($block->module == 'views' && $block->delta == '-exp-searches-page') {
    // Add grid classes, to make form 100%;
    $data['content']['#markup'] = str_replace('views-widget-filter-search_api_views_fulltext', 'views-widget-filter-search_api_views_fulltext grid-4-6 left', $data['content']['#markup']);
    $data['content']['#markup'] = str_replace('views-submit-button', 'views-submit-button grid-1-6 left', $data['content']['#markup']);
    $data['content']['#markup'] = str_replace('views-reset-button', 'views-reset-button grid-1-6 left', $data['content']['#markup']);
  }*/
}
