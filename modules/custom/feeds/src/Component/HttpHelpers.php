<?php

namespace Drupal\feeds\Component;

use Drupal\feeds\Component\XmlParserTrait;

/**
 * Various helpers for dealing with HTTP data.
 *
 * @todo Move this some place else and split it up.
 */
class HttpHelpers {

  use XmlParserTrait;

  /**
   * Finds a relation type in a header array.
   *
   * @param array $headers
   *   The header array.
   * @param string $relation
   *   The type of relation to find.
   *
   * @return string|false The link, or false.
   */
  public static function findLinkHeader(array $headers, $relation) {
    $headers = array_change_key_case($headers);

    if (!isset($headers['link'])) {
      return FALSE;
    }

    foreach ((array) $headers['link'] as $link) {
      if ($link = static::parseLinkRelation($link, $relation)) {
        return $link;
      }
    }

    return FALSE;
  }

  /**
   * Finds a hub link from a Link header.
   *
   * @param string $link_header
   *   The full link header string.
   * @param string $relation
   *   The relationship to find.
   *
   * @return string
   *   The link, or an empty string if one wasn't found.
   */
  public static function parseLinkRelation($link_header, $relation) {
    if (!preg_match_all('/<([^>]*)>\s*;.*?rel\s*=(.+?)(?:;|$)/is', trim($link_header), $matches)) {
      return '';
    }

    foreach ($matches[2] as $delta => $match) {
      $match = trim($match);

      // Strip quotes if present.
      $len = strlen($match);
      if ($match[0] === '"' && $match[$len - 1] === '"') {
        $match = substr($match, 1, $len - 2);
      }

      // Normalize whitespace.
      preg_replace('/\s+/s', ' ', trim($match));

      if (in_array($relation, explode(' ', $match), TRUE)) {
        return $matches[1][$delta];
      }
    }

    return '';
  }

  /**
   * Finds a link relation in XML.
   *
   * @param string $xml
   *   The XML.
   * @param string $relation
   *   The relation to find.
   *
   * @return string|false The relation, or false.
   */
  public static function findRelationFromXml($xml, $relation) {
    // Check if $xml has length.
    if (!isset($xml[0])) {
      return FALSE;
    }

    $document = static::getDomDocument($xml);

    $xpath = new \DOMXPath($document);

    $list = $xpath->query('//*[local-name() = "link" and @rel = "' . $relation . '"]/@href');

    if ($list->length === 0) {
      return FALSE;
    }

    return $list->item(0)->value;
  }

}
