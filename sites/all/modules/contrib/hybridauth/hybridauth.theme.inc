<?php
/**
 * @file
 * Theme functions for the HybridAuth module.
 */

/**
 * Template preprocess function for hybridauth_widget.
 */
function template_preprocess_hybridauth_widget(&$vars, $hook) {
  $element = $vars['element'];

  $query = $element['#hybridauth_query'];
  $element['#hybridauth_destination'] = trim($element['#hybridauth_destination']);
  // Process destination; HTTP_REFERER is needed for widget in modals
  // to return to the current page.
  if ($element['#hybridauth_destination'] == '[HTTP_REFERER]' && isset($_SERVER['HTTP_REFERER'])) {
    $query += array(
      'destination' => $_SERVER['HTTP_REFERER'],
      'destination_error' => $_SERVER['HTTP_REFERER'],
    );
  }
  elseif ($element['#hybridauth_destination']) {
    $destination = drupal_get_destination();
    $query += array(
      'destination' => $element['#hybridauth_destination'],
      'destination_error' => $destination['destination'],
    );
  }
  else {
    $destination = drupal_get_destination();
    $query += array(
      'destination' => $destination['destination'],
      'destination_error' => $destination['destination'],
    );
  }

  $providers = array();
  if ($element['#hybridauth_widget_type'] == 'list') {
    $class = array('hybridauth-widget-provider');
    $rel = array('nofollow');

    foreach (hybridauth_get_enabled_providers() as $provider_id => $provider_name) {
      $window_type = variable_get('hybridauth_provider_' . $provider_id . '_window_type', 'current');
      $window_width = variable_get('hybridauth_provider_' . $provider_id . '_window_width', 800);
      $window_height = variable_get('hybridauth_provider_' . $provider_id . '_window_height', 500);
      $query_mod = $query;
      $class_mod = $class;
      $rel_mod = $rel;

      switch ($window_type) {
        // Add Colorbox-specific things.
        case 'colorbox':
          $class_mod[] = 'colorbox-load';
          $query_mod += array(
            'width' => $window_width,
            'height' => $window_height,
            'iframe' => 'true',
          );
          break;
        // Add Shadowbox specific settings.
        case 'shadowbox':
          $rel_mod = array('shadowbox;width=' . $window_width . ';height=' . $window_height, 'nofollow');
          break;
        // Add fancyBox-specific settings.
        case 'fancybox':
          $class_mod[] = 'fancybox';
          $class_mod[] = 'fancybox.iframe';
          $class_mod[] = '{width:' . $window_width . ',height:' . $window_height . '}';
          break;
        // Add Lightbox2-specific settings.
        case 'lightbox2':
          $rel_mod = array('lightframe[|width:' . $window_width . 'px; height:' . $window_height . 'px;]', 'nofollow');
          break;
      }

      // Determine onclick behavior.
      $onclick = '';
      if ($element['#hybridauth_onclick'] === FALSE) {}
      elseif (!empty($element['#hybridauth_onclick'])) {
        $onclick = $element['#hybridauth_onclick'];
      }
      elseif ($window_type == 'current') {
        $class_mod[] = 'hybridauth-onclick-current';
      }
      elseif ($window_type == 'popup') {
        $class_mod[] = 'hybridauth-onclick-popup';
      }

      $text = theme('hybridauth_provider_icon', array(
        'icon_pack' => $element['#hybridauth_widget_icon_pack'],
        'provider_id' => $provider_id,
        'provider_name' => $provider_name,
      ));
      $path = 'hybridauth/window/' . $provider_id;
      $url = url($path, array('query' => $query_mod));
      if ($element['#hybridauth_widget_hide_links']) {
        $path = 'user';
      }
      $options = array(
        'html' => TRUE,
        'query' => $query_mod,
        'attributes' => array(
          'title' => $provider_name,
          'class' => $class_mod,
          'rel' => $rel_mod,
          'data-hybridauth-provider' => $provider_id,
          'data-hybridauth-url' => $url,
          // jQuery Mobile compatibility - so it doesn't use AJAX.
          'data-ajax' => 'false',
          // Add authentication window width and height.
          'data-hybridauth-width' => $window_width,
          'data-hybridauth-height' => $window_height,
        ) + ($onclick ? array('onclick' => $onclick) : array()),
      );
      $providers[] = l($text, $path, $options);
    }
  }
  else {
    $provider_id = 'none';
    $class = array();
    if ($element['#hybridauth_widget_use_overlay']) {
      $class = array('ctools-use-modal', 'ctools-modal-hybridauthmodal');
      ctools_include('modal');
      ctools_modal_add_js();
      $settings = array(
        'hybridauthmodal' => array(
          'modalSize' => array(
            'type' => 'scale',
            'width' => '400px',
            'height' => '200px',
            'addWidth' => 0,
            'addHeight' => 0,
            'contentRight' => 25,
            'contentBottom' => 45,
          ),
          'modalTheme' => 'HybridAuthModalDialog',
          // 'throbberTheme' => 'HybridAuthModalThrobber',
          'modalOptions' => array(
            'opacity' => 0.55,
            'background' => '#000',
          ),
          'animation' => 'fadeIn',
          'animationSpeed' => 'slow',
          'closeText' => t('Close'),
          'closeImage' => '',
          // 'loadingText' => t('Loading...'),
          /* 'throbber' => theme('image', array(
            'path' => ctools_image_path('throbber.gif'),
            'title' => t('Loading...'),
            'alt' => t('Loading'),
          )), */
        ),
      );
      drupal_add_js($settings, 'setting');
    }

    $providers[] = l(
      ($element['#hybridauth_widget_type'] == 'link') ?
        $element['#hybridauth_widget_link_text'] :
        theme('hybridauth_provider_icon', array('icon_pack' => $element['#hybridauth_widget_icon_pack'], 'provider_id' => $provider_id, 'provider_name' => $element['#hybridauth_widget_link_title'])),
      'hybridauth/providers/nojs/' . $element['#hybridauth_widget_icon_pack'],
      array(
        'html' => TRUE,
        'query' => $query,
        'attributes' => array(
          'title' => $element['#hybridauth_widget_link_title'],
          'class' => $class,
          'rel' => array('nofollow'),
        ),
      )
    );
  }

  _hybridauth_add_icon_pack_files($element['#hybridauth_widget_icon_pack']);

  $vars['providers'] = $providers;
}

/**
 * Template preprocess function for hybridauth_provider_icon.
 */
function template_preprocess_hybridauth_provider_icon(&$vars, $hook) {
  if (!isset($vars['provider_name'])) {
    $vars['provider_name'] = hybridauth_get_provider_name($vars['provider_id']);
  }
  $icon_pack_classes = array(
    'hybridauth-icon',
    drupal_html_class($vars['provider_id']),
    drupal_html_class('hybridauth-icon-' . $vars['icon_pack']),
    drupal_html_class('hybridauth-' . $vars['provider_id']),
    drupal_html_class('hybridauth-' . $vars['provider_id'] . '-' . $vars['icon_pack']),
  );

  // Icon pack modifications.
  _hybridauth_add_icon_pack_files($vars['icon_pack']);
  if ($function = ctools_plugin_load_function('hybridauth', 'icon_pack', $vars['icon_pack'], 'icon_classes_callback')) {
    $function($icon_pack_classes, $vars['provider_id']);
  }
  // Provider modifications.
  if ($provider = hybridauth_get_provider($vars['provider_id'])) {
    if (array_key_exists('css', $provider)) {
      drupal_add_css($provider['path'] . '/' . $provider['css']);
    }
    if ($function = ctools_plugin_get_function($provider, 'icon_classes_callback')) {
      $function($icon_pack_classes);
    }
  }

  $vars['icon_pack_classes'] = implode(' ', $icon_pack_classes);
}
