<?php

namespace Drupal\feeds\Zend\Extension\Georss;

use Zend\Feed\Reader\Extension\AbstractEntry;

/**
 * Parses GeoRss data.
 */
class Entry extends AbstractEntry {

  /**
   * Returns the entry point.
   *
   * @param int $index
   *   The index of the point.
   *
   * @return string|null
   *   A geo point.
   */
  public function getGeoPoint($index = 0) {
    $points = $this->getGeoPoints();
    return isset($points[$index]) ? $points[$index] : NULL;
  }

  /**
   * Get the entry points.
   *
   * @return array
   */
  public function getGeoPoints() {
    if (!isset($this->data['georss'])) {
      $this->populateData();
    }
    return $this->data['georss'];
  }

  /**
   * Populates the georss data.
   */
  protected function populateData() {
    $this->data['georss'] = [];
    $list = $this->xpath->evaluate($this->getXpathPrefix() . '//georss:point');

    foreach ($list as $point) {
      // Normalize whitespace.
      $parts = explode(' ', preg_replace('/\s+/', ' ', trim($point->nodeValue)));
      if (count($parts) === 2) {
        $this->data['georss'][] = [
          'lat' => is_numeric($parts[0]) ? (float) $parts[0] : NULL,
          'lon' => is_numeric($parts[1]) ? (float) $parts[1] : NULL,
        ];
      }
    }
  }

  /**
   * Registers GeoRSS namespaces.
   */
  protected function registerNamespaces() {
    $this->getXpath()->registerNamespace('georss', 'http://www.georss.org/georss');
  }

}
