<?php

namespace Drupal\feeds\Component;

/**
 * Parses a generic OPML string into an array.
 *
 * @todo Move this to Github.
 */
class GenericOpmlParser {
  use XmlParserTrait;

  /**
   * The XPath query object.
   *
   * @var \DOMXpath
   */
  protected $xpath;

  /**
   * Whether to normalize the case of elements and attributes.
   *
   * @var bool
   */
  protected $normalizeCase;

  /**
   * Constructs a GenericOpmlParser object.
   *
   * @param string $xml
   *   The XML string.
   */
  public function __construct($xml) {
    $this->xpath = new \DOMXPath(static::getDomDocument($xml));
  }

  /**
   * Parses the OPML file.
   *
   * @param bool $normalize_case
   *   (optional) True to convert all attributes to lowercase. False, to leave
   *   them as is. Defaults to false.
   *
   * @return array
   *   A structured array.
   *
   * @todo Document the return value.
   */
  public function parse($normalize_case = FALSE) {
    $this->normalizeCase = $normalize_case;

    $return = ['head' => ['#title' => '']];
    // Title is a required field, let parsers assume its existence.

    foreach ($this->xpath->query('/opml/head/*') as $element) {
      if ($this->normalizeCase) {
        $return['head']['#' . strtolower($element->nodeName)] = $element->nodeValue;
      }
      else {
        $return['head']['#' . $element->nodeName] = $element->nodeValue;
      }
    }

    if (isset($return['head']['#expansionState'])) {
      $return['head']['#expansionState'] = array_filter(explode(',', $return['head']['#expansionState']));
    }

    $return['outlines'] = [];
    if ($content = $this->xpath->evaluate('/opml/body', $this->xpath->document)->item(0)) {
      $return['outlines'] = $this->getOutlines($content);
    }

    return $return;
  }

  /**
   * Returns the sub-outline structure.
   *
   * @param \DOMElement $context
   *   The context element to iterate on.
   *
   * @return array
   *   The nested outline array.
   */
  protected function getOutlines(\DOMElement $context) {
    $outlines = [];

    foreach ($this->xpath->query('outline', $context) as $element) {
      $outline = [];
      if ($element->hasAttributes()) {
        foreach ($element->attributes as $attribute) {
          if ($this->normalizeCase) {
            $outline['#' . strtolower($attribute->nodeName)] = $attribute->nodeValue;
          }
          else {
            $outline['#' . $attribute->nodeName] = $attribute->nodeValue;
          }
        }
      }
      // Recurse.
      if ($sub_outlines = $this->getOutlines($element)) {
        $outline['outlines'] = $sub_outlines;
      }

      $outlines[] = $outline;
    }

    return $outlines;
  }

}
