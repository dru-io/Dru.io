<?php

namespace Drupal\Tests\feeds\Unit\Component;

use Drupal\feeds\Component\HttpHelpers;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\feeds\Component\HttpHelpers
 * @group feeds
 */
class HttpHelpersTest extends UnitTestCase {

  /**
   * @dataProvider httpResponses
   */
  public function testFindLinkHeader($headers, $rel, $expected) {
    $this->assertSame($expected, HttpHelpers::findLinkHeader($headers, $rel));
  }

  public function testFindLinkXml() {
    $xml = '<feed xmlns="http://www.w3.org/2005/Atom">
            <title>Blah blah blah</title>
            <link href="http://example.com/feed" rel="self" type="application/atom+xml"/>
            <link href="http://example.com" rel="alternate" type="text/html"/>
            <link rel="hub" href="http://example.com/hub" />
            <updated>2015-02-03T13:08:02+01:00</updated>
            <id>http://blog.superfeedr.com/</id></feed>';

    $this->assertSame('http://example.com/hub', HttpHelpers::findRelationFromXml($xml, 'hub'));

    $xml = '<?xml version="1.0"?>
            <rss xmlns:atom="http://www.w3.org/2005/Atom">
            <channel>
            <atom:link rel="hub" href="http://example.com/hub"/>
            </channel></rss>';
    $this->assertSame('http://example.com/hub', HttpHelpers::findRelationFromXml($xml, 'hub'));

    $this->assertSame(FALSE, HttpHelpers::findRelationFromXml('', 'hub'));
    $this->assertSame(FALSE, HttpHelpers::findRelationFromXml(' ', 'hub'));
  }

  public function httpResponses() {
    $headers1 = [
      'Link' => '<http://example.com/TheBook/chapter2>;
         rel="previous";
         title="previous chapter"',
    ];

    $headers2 = [
      'Link' => ['<http://example.com/TheBook/chapter2>; rel="previous";
         title="previous chapter"'],
    ];

    $headers3 = [
      'LINK' => '</>; rel="http://example.net/foo"',
    ];

    $headers4 = [
      'link' => '<http://example.com>; rel="hub self"',
    ];

    $headers5 = [
      'link' => '</TheBook/chapter2>;
         rel="previous"; title*=UTF-8\'de\'letztes%20Kapitel,
         </TheBook/chapter4>;
         rel="next"; title*=UTF-8\'de\'n%c3%a4chstes%20Kapitel',
    ];

    $headers6 = [
      'link' => '<http://example.com>; rel=hub',
    ];

    $headers7 = [
      'link' => '<http://example.com>; rel= hub ',
    ];

    return [
      [$headers1, 'previous', 'http://example.com/TheBook/chapter2'],
      [$headers2, 'previous', 'http://example.com/TheBook/chapter2'],
      [$headers3, 'http://example.net/foo', '/'],
      [$headers4, 'hub', 'http://example.com'],
      [$headers4, 'self', 'http://example.com'],
      [$headers5, 'next', '/TheBook/chapter4'],
      [$headers6, 'hub', 'http://example.com'],
      [$headers7, 'hub', 'http://example.com'],
      [$headers7, 'self', FALSE],
      [[], 'hub', FALSE],
      [['link' => ''], 'hub', FALSE],
    ];
  }

}
