<?php

namespace Drupal\feeds\Plugin\Field\FieldFormatter;

use Drupal\Component\Utility\Html;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldFormatter\UriLinkFormatter;
use Drupal\Core\Url;

/**
 * Plugin implementation of the 'feeds_uri_link' formatter.
 *
 * @FieldFormatter(
 *   id = "feeds_uri_link",
 *   label = @Translation("Link to URI, or string"),
 *   field_types = {
 *     "uri",
 *   }
 * )
 */
class FeedsUriLinkFormatter extends UriLinkFormatter {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {
      if ($item->isEmpty()) {
        continue;
      }

      $scheme = parse_url($item->value, PHP_URL_SCHEME);
      if ($scheme === 'http' || $scheme === 'https') {
        $elements[$delta] = [
          '#type' => 'link',
          '#url' => Url::fromUri($item->value),
          '#title' => $item->value,
        ];
      }
      else {
        $elements[$delta] = [
          '#markup' => Html::escape($item->value),
        ];
      }
    }

    return $elements;
  }

}
