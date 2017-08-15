<?php

namespace Drupal\feeds\Component;

/**
 * Helper methods for dealing with XML documents.
 */
trait XmlParserTrait {

  /**
   * Matches the characters of an XML element.
   *
   * @var string
   */
  protected static $_elementRegex = '[:A-Z_a-z\\xC0-\\xD6\\xD8-\\xF6\\xF8-\\x{2FF}\\x{370}-\\x{37D}\\x{37F}-\\x{1FFF}\\x{200C}-\\x{200D}\\x{2070}-\\x{218F}\\x{2C00}-\\x{2FEF}\\x{3001}-\\x{D7FF}\\x{F900}-\\x{FDCF}\\x{FDF0}-\\x{FFFD}\\x{10000}-\\x{EFFFF}][:A-Z_a-z\\xC0-\\xD6\\xD8-\\xF6\\xF8-\\x{2FF}\\x{370}-\\x{37D}\\x{37F}-\\x{1FFF}\\x{200C}-\\x{200D}\\x{2070}-\\x{218F}\\x{2C00}-\\x{2FEF}\\x{3001}-\\x{D7FF}\\x{F900}-\\x{FDCF}\\x{FDF0}-\\x{FFFD}\\x{10000}-\\x{EFFFF}\\.\\-0-9\\xB7\\x{0300}-\\x{036F}\\x{203F}-\\x{2040}]*';

  /**
   * The previous value of libxml error reporting.
   *
   * @var bool
   */
  protected static $_useError;

  /**
   * The previous value of the entity loader.
   *
   * @var bool
   */
  protected static $_entityLoader;

  /**
   * The errors reported during parsing.
   *
   * @var array
   */
  protected static $_errors = [];

  /**
   * Returns a new DOMDocument.
   *
   * Implementers can override this to setup the document.
   *
   * @param string $source
   *   The XML string to parse.
   * @param int $options
   *   (optional) Bitwise OR of the libxml option constants. Defaults to 0.
   *
   * @return \DOMDocuemnt
   *   The new DOMDocument object.
   *
   * @throws \RuntimeException
   *   Thrown if the document fails to load.
   */
  protected static function getDomDocument($source, $options = 0) {
    static::startXmlErrorHandling();

    $document = new \DOMDocument('1.0', 'utf-8');
    $document->strictErrorChecking = FALSE;
    $document->resolveExternals = FALSE;
    // Libxml specific.
    $document->substituteEntities = FALSE;
    $document->recover = TRUE;

    $options = $options | LIBXML_NOENT | LIBXML_NONET | defined('LIBXML_COMPACT') ? LIBXML_COMPACT : 0;
    $options = $options | defined('LIBXML_PARSEHUGE') ? LIBXML_PARSEHUGE : 0;

    $document->loadXML($source, $options);
    static::stopXmlErrorHandling();

    return $document;
  }

  /**
   * Starts custom error handling.
   */
  protected static function startXmlErrorHandling() {
    static::$_useError = libxml_use_internal_errors(TRUE);
    static::$_entityLoader = libxml_disable_entity_loader(TRUE);
    libxml_clear_errors();
  }

  /**
   * Stops custom error handling.
   */
  protected static function stopXmlErrorHandling() {
    foreach (libxml_get_errors() as $error) {
      static::$_errors[$error->level][] = [
        'message' => trim($error->message),
        'line' => $error->line,
        'code' => $error->code,
      ];
    }
    libxml_clear_errors();
    libxml_use_internal_errors(static::$_useError);
    libxml_disable_entity_loader(static::$_entityLoader);
  }

  /**
   * Returns the errors reported during parsing.
   *
   * @return array
   *   An array of errors keyed by error level.
   *
   * @see libxml_get_errors()
   */
  protected static function getXmlErrors() {
    return static::$_errors;
  }

  /**
   * Strips the default namespaces from an XML string.
   *
   * @param string $xml
   *   The XML string.
   *
   * @return string
   *   The XML string with the default namespaces removed.
   */
  protected static function removeDefaultNamespaces($xml) {
    return preg_replace('/(<' . static::$_elementRegex . '[^>]*)\s+xmlns\s*=\s*("|\').*?(\2)([^>]*>)/u', '$1$4', $xml);
  }

}
