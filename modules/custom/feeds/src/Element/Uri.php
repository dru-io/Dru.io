<?php

namespace Drupal\feeds\Element;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\Url;

/**
 * Provides a form element for input of a URI.
 *
 * @FormElement("feeds_uri")
 */
class Uri extends Url {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $info = parent::getInfo();
    $info['#allowed_schemes'] = [];
    return $info;
  }

  /**
   * Form element validation handler for #type 'feeds_uri'.
   */
  public static function validateUrl(&$element, FormStateInterface $form_state, &$complete_form) {
    $value = file_stream_wrapper_uri_normalize(trim($element['#value']));
    $form_state->setValueForElement($element, $value);

    if (!$value) {
      return;
    }

    $parsed = parse_url($value);
    $valid = $parsed && !empty($parsed['scheme']) && !empty($parsed['host']);

    if (!$valid) {
      $form_state->setError($element, t('The URI %url is not valid.', ['%url' => $value]));
      return;
    }

    if ($element['#allowed_schemes'] && !in_array(static::getScheme($value), $element['#allowed_schemes'], TRUE)) {
      $args = [
        '%scheme' => static::getScheme($value),
        '@schemes' => implode(', ', $element['#allowed_schemes']),
      ];
      $form_state->setError($element, t("The scheme %scheme is invalid. Available schemes: @schemes.", $args));
    }
  }

/**
 * Returns the scheme of a URI (e.g. a stream).
 *
 * @param string $uri
 *   A stream, referenced as "scheme://target".
 *
 * @return string
 *   A string containing the name of the scheme, or FALSE if none. For example,
 *   the URI "public://example.txt" would return "public".
 */
  protected static function getScheme($uri) {
    $position = strpos($uri, '://');
    return $position ? substr($uri, 0, $position) : FALSE;
  }

}
