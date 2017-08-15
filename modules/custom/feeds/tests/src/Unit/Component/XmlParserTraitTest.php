<?php

namespace Drupal\Tests\feeds\Unit\Component;

use Drupal\Tests\feeds\Unit\FeedsUnitTestCase;

/**
 * @coversDefaultClass \Drupal\feeds\Component\XmlParserTrait
 * @group feeds
 */
class XmlParserTraitTest extends FeedsUnitTestCase {

  public function test() {
    $trait = $this->getMockForTrait('Drupal\feeds\Component\XmlParserTrait');

    $doc = $this->callProtectedMethod($trait, 'getDomDocument', [' <thing></thing> ']);
    $this->assertSame('DOMDocument', get_class($doc));

    $errors = $this->callProtectedMethod($trait, 'getXmlErrors');
    $this->assertSame([], $errors);
  }

  public function testErrors() {
    $trait = $this->getMockForTrait('Drupal\feeds\Component\XmlParserTrait');

    $doc = $this->callProtectedMethod($trait, 'getDomDocument', ['asdfasdf']);
    $this->assertSame('DOMDocument', get_class($doc));

    $errors = $this->callProtectedMethod($trait, 'getXmlErrors');
    $this->assertSame("Start tag expected, '<' not found", $errors[3][0]['message']);
  }

  /**
   * Strip some namespaces out of XML.
   *
   * @dataProvider namespaceProvider
   */
  public function testRemoveDefaultNamespaces($in, $out) {
    $trait = $this->getMockForTrait('Drupal\feeds\Component\XmlParserTrait');

    $result = $this->callProtectedMethod($trait, 'removeDefaultNamespaces', [$in]);
    $this->assertSame($out, $result);
  }

  /**
   * Checks that the input and output are equal.
   */
  public function namespaceProvider() {
    return [
      ['<feed xmlns="http://www.w3.org/2005/Atom">bleep blorp</feed>', '<feed>bleep blorp</feed>'],
      ['<подача xmlns="http://www.w3.org/2005/Atom">bleep blorp</подача>', '<подача>bleep blorp</подача>'],
      ['<по.дача xmlns="http://www.w3.org/2005/Atom">bleep blorp</по.дача>', '<по.дача>bleep blorp</по.дача>'],
      ['<element other attrs xmlns="http://www.w3.org/2005/Atom">bleep blorp</element>', '<element other attrs>bleep blorp</element>'],
      ['<cat xmlns="http://www.w3.org/2005/Atom" other attrs>bleep blorp</cat>', '<cat other attrs>bleep blorp</cat>'],
      ['<飼料 thing="stuff" xmlns="http://www.w3.org/2005/Atom">bleep blorp</飼料>', '<飼料 thing="stuff">bleep blorp</飼料>'],
      ['<飼-料 thing="stuff" xmlns="http://www.w3.org/2005/Atom">bleep blorp</飼-料>', '<飼-料 thing="stuff">bleep blorp</飼-料>'],
      ['<self xmlns="http://www.w3.org/2005/Atom" />', '<self />'],
      ['<self attr xmlns="http://www.w3.org/2005/Atom"/>', '<self attr/>'],
      ['<a xmlns="http://www.w3.org/2005/Atom"/>', '<a/>'],
      ['<a xmlns="http://www.w3.org/2005/Atom"></a>', '<a></a>'],
      ['<a href="http://google.com" xmlns="http://www.w3.org/2005/Atom"></a>', '<a href="http://google.com"></a>'],

      // Test invalid XML element names.
      ['<1name href="http://google.com" xmlns="http://www.w3.org/2005/Atom"></1name>', '<1name href="http://google.com" xmlns="http://www.w3.org/2005/Atom"></1name>'],

      // Test other namespaces.
      ['<name href="http://google.com" xmlns:h="http://www.w3.org/2005/Atom"></name>', '<name href="http://google.com" xmlns:h="http://www.w3.org/2005/Atom"></name>'],

      // Test multiple default namespaces.
      ['<name xmlns="http://www.w3.org/2005/Atom"></name><name xmlns="http://www.w3.org/2005/Atom"></name>', '<name></name><name></name>'],
    ];
  }

}
