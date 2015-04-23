<?php
/**
 * Copyright (c) 2007-2009, Conduit Internet Technologies, Inc.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 *  - Redistributions of source code must retain the above copyright notice,
 *    this list of conditions and the following disclaimer.
 *  - Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 *  - Neither the name of Conduit Internet Technologies, Inc. nor the names of
 *    its contributors may be used to endorse or promote products derived from
 *    this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @copyright Copyright 2007-2009 Conduit Internet Technologies, Inc. (http://conduit-it.com)
 * @license New BSD (http://solr-php-client.googlecode.com/svn/trunk/COPYING)
 * @version $Id: Document.php 15 2009-08-04 17:53:08Z donovan.jimenez $
 *
 * @package Apache
 * @subpackage Solr
 * @author Donovan Jimenez <djimenez@conduit-it.com>
 */

/**
 * Additional code Copyright (c) 2011 by Peter Wolanin, and
 * additional contributors.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or (at
 * your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
 * or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License
 * for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program as the file LICENSE.txt; if not, please see
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt.
 */

/**
 * Holds Key / Value pairs that represent a Solr Document along with any
 * associated boost values. Field values can be accessed by direct dereferencing
 * such as:
 *
 * @code
 *   $document->title = 'Something';
 *   echo $document->title;
 * @endcode
 *
 * Additionally, the field values can be iterated with foreach:
 *
 * @code
 *   foreach ($document as $fieldName => $fieldValue) {
 *     // ...
 *   }
 * @endcode
 */
class SearchApiSolrDocument implements IteratorAggregate {

  /**
   * Document boost value.
   *
   * @var float|false
   */
  protected $documentBoost = FALSE;

  /**
   * Document field values, indexed by name.
   *
   * @var array
   */
  protected $fields = array();

  /**
   * Document field boost values, indexed by name.
   *
   * @var array
   */
  protected $fieldBoosts = array();

  /**
   * Clears all boosts and fields from this document.
   */
  public function clear() {
    $this->documentBoost = FALSE;

    $this->fields = array();
    $this->fieldBoosts = array();
  }

  /**
   * Gets the current document boost.
   *
   * @return float|false
   *   The current document boost, or FALSE if none is set.
   */
  public function getBoost() {
    return $this->documentBoost;
  }

  /**
   * Sets the document boost factor.
   *
   * @param float|false $boost
   *   FALSE for default boost, or a positive number for setting a document
   *   boost.
   */
  public function setBoost($boost) {
    $boost = (float) $boost;

    if ($boost > 0.0) {
      $this->documentBoost = $boost;
    }
    else {
      $this->documentBoost = FALSE;
    }
  }

  /**
   * Adds a value to a multi-valued field
   *
   * NOTE: the solr XML format allows you to specify boosts PER value even
   * though the underlying Lucene implementation only allows a boost per field.
   * To remedy this, the final field boost value will be the product of all
   * specified boosts on field values - this is similar to SolrJ's
   * functionality.
   *
   * @code
   *   $doc = new ApacheSolrDocument();
   *   $doc->addField('foo', 'bar', 2.0);
   *   $doc->addField('foo', 'baz', 3.0);
   *   // Resultant field boost will be 6!
   *   echo $doc->getFieldBoost('foo');
   * @endcode
   *
   * @param string $key
   *   The name of the field.
   * @param $value
   *   The value to add for the field.
   * @param float|false $boost
   *   FALSE for default boost, or a positive number for setting a field boost.
   */
  public function addField($key, $value, $boost = FALSE) {
    if (!isset($this->fields[$key])) {
      // create holding array if this is the first value
      $this->fields[$key] = array();
    }
    else if (!is_array($this->fields[$key])) {
      // move existing value into array if it is not already an array
      $this->fields[$key] = array($this->fields[$key]);
    }

    if ($this->getFieldBoost($key) === FALSE) {
      // boost not already set, set it now
      $this->setFieldBoost($key, $boost);
    }
    else if ((float) $boost > 0.0) {
      // multiply passed boost with current field boost - similar to SolrJ implementation
      $this->fieldBoosts[$key] *= (float) $boost;
    }

    // add value to array
    $this->fields[$key][] = $value;
  }

  /**
   * Gets information about a field stored in Solr.
   *
   * @param string $key
   *   The name of the field.
   *
   * @return array|false
   *   An associative array of info if the field exists, FALSE otherwise.
   */
  public function getField($key) {
    if (isset($this->fields[$key])) {
      return array(
        'name' => $key,
        'value' => $this->fields[$key],
        'boost' => $this->getFieldBoost($key)
      );
    }

    return FALSE;
  }

  /**
   * Sets a field value.
   *
   * Multi-valued fields should be set as arrays or via the addField()
   * function which will automatically make sure the field is an array.
   *
   * @param string $key
   *   The name of the field.
   * @param string|array $value
   *   The value to set for the field.
   * @param float|false $boost
   *   FALSE for default boost, or a positive number for setting a field boost.
   */
  public function setField($key, $value, $boost = FALSE) {
    $this->fields[$key] = $value;
    $this->setFieldBoost($key, $boost);
  }

  /**
   * Gets the currently set field boost for a document field.
   *
   * @param string $key
   *   The name of the field.
   *
   * @return float|false
   *   The currently set field boost, or FALSE if none was set.
   */
  public function getFieldBoost($key) {
    return isset($this->fieldBoosts[$key]) ? $this->fieldBoosts[$key] : FALSE;
  }

  /**
   * Sets the field boost for a document field.
   *
   * @param string $key
   *   The name of the field.
   * @param float|false $boost
   *   FALSE for default boost, or a positive number for setting a field boost.
   */
  public function setFieldBoost($key, $boost) {
    $boost = (float) $boost;

    if ($boost > 0.0) {
      $this->fieldBoosts[$key] = $boost;
    }
    else {
      $this->fieldBoosts[$key] = FALSE;
    }
  }

  /**
   * Returns all current field boosts, indexed by field name.
   *
   * @return array
   *   An associative array in the format $field_name => $field_boost.
   */
  public function getFieldBoosts() {
    return $this->fieldBoosts;
  }

  /**
   * Gets the names of all fields in this document.
   *
   * @return array
   *   The names of all fields in this document.
   */
  public function getFieldNames() {
    return array_keys($this->fields);
  }

  /**
   * Gets the values of all fields in this document.
   *
   * @return array
   *   The values of all fields in this document.
   */
  public function getFieldValues() {
    return array_values($this->fields);
  }

  /**
   * Implements IteratorAggregate::getIterator().
   *
   * Implementing the IteratorAggregate interface allows the following usage:
   * @code
   *   foreach ($document as $key => $value) {
   *     // ...
   *   }
   * @endcode
   *
   * @return Traversable
   *   An iterator over this document's fields.
   */
  public function getIterator() {
    $arrayObject = new ArrayObject($this->fields);

    return $arrayObject->getIterator();
  }

  /**
   * Magic getter for field values.
   *
   * @param string $key
   *   The name of the field.
   *
   * @return string|array|null
   *   The value that was set for the field.
   */
  public function __get($key) {
    return $this->fields[$key];
  }

  /**
   * Magic setter for field values.
   *
   * Multi-valued fields should be set as arrays or via the addField() function
   * which will automatically make sure the field is an array.
   *
   * @param string $key
   *   The name of the field.
   * @param string|array $value
   *   The value to set for the field.
   */
  public function __set($key, $value) {
    $this->setField($key, $value);
  }

  /**
   * Magic isset for fields values.
   *
   * Do not call directly. Allows the following usage:
   * @code
   *   isset($document->some_field);
   * @endcode
   *
   * @param string $key
   *   The name of the field.
   *
   * @return bool
   *   Whether the given key is set in this document.
   */
  public function __isset($key) {
    return isset($this->fields[$key]);
  }

  /**
   * Magic unset for field values.
   *
   * Do not call directly. Allows the following usage:
   * @code
   *   unset($document->some_field);
   * @endcode
   *
   * @param string $key
   *   The name of the field.
   */
  public function __unset($key) {
    unset($this->fields[$key]);
    unset($this->fieldBoosts[$key]);
  }

  /**
   * Create an XML fragment from this document.
   *
   * This string can then be used inside a Solr add call.
   *
   * @return string
   *   An XML formatted string for this document.
   */
  public function toXml() {
    $xml = '<doc';

    if ($this->documentBoost !== FALSE) {
      $xml .= ' boost="' . $this->documentBoost . '"';
    }

    $xml .= '>';

    foreach ($this->fields as $key => $value) {
      $fieldBoost = $this->getFieldBoost($key);
      $key = htmlspecialchars($key, ENT_COMPAT, 'UTF-8');

      if (is_array($value)) {
        foreach ($value as $multivalue) {
          $xml .= '<field name="' . $key . '"';

          if ($fieldBoost !== FALSE) {
            $xml .= ' boost="' . $fieldBoost . '"';

            // Only set the boost for the first field in the set.
            $fieldBoost = FALSE;
          }

          $xml .= '>' . htmlspecialchars($multivalue, ENT_NOQUOTES, 'UTF-8') . '</field>';
        }
      }
      else {
        $xml .= '<field name="' . $key . '"';

        if ($fieldBoost !== FALSE) {
          $xml .= ' boost="' . $fieldBoost . '"';
        }

        $xml .= '>' . htmlspecialchars($value, ENT_NOQUOTES, 'UTF-8') . '</field>';
      }
    }

    $xml .= '</doc>';

    // Remove any control characters to avoid Solr XML parser exception.
    return self::stripCtrlChars($xml);
  }

  /**
   * Sanitizes XML for sending to Solr.
   *
   * Replaces control (non-printable) characters that are invalid to Solr's XML
   * parser with a space.
   *
   * @param string $string
   *   The string to sanitize.
   *
   * @return string
   *   A string safe for including in a Solr request.
   */
  public static function stripCtrlChars($string) {
    // See:  http://w3.org/International/questions/qa-forms-utf-8.html
    // Printable utf-8 does not include any of these chars below x7F
    return preg_replace('@[\x00-\x08\x0B\x0C\x0E-\x1F]@', ' ', $string);
  }
}