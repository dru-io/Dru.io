<?php

namespace Drupal\Tests\feeds\Unit\Feeds\Target;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\feeds\Feeds\Target\DateRange;
use Drupal\Tests\feeds\Unit\FeedsUnitTestCase;

/**
 * @coversDefaultClass \Drupal\feeds\Feeds\Target\DateRange
 * @group feeds
 */
class DateRangeTest extends FeedsUnitTestCase {

  /**
   * The mocked feed.
   *
   * @var \Drupal\feeds\FeedTypeInterface
   */
  protected $feedType;

  /**
   * The target definition.
   *
   * @var \Drupal\feeds\FieldTargetDefinition
   */
  protected $targetDefinition;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $container = new ContainerBuilder();
    $language_manager = $this->getMock('Drupal\Core\Language\LanguageManagerInterface');
    $language = $this->getMock('Drupal\Core\Language\LanguageInterface');
    $language->expects($this->any())
      ->method('getId')
      ->will($this->returnValue('en'));
    $language_manager->expects($this->any())
      ->method('getCurrentLanguage')
      ->will($this->returnValue($language));
    $container->set('language_manager', $language_manager);

    \Drupal::setContainer($container);

    $this->feedType = $this->getMock('Drupal\feeds\FeedTypeInterface');
    $method = $this->getMethod('Drupal\feeds\Feeds\Target\DateRange', 'prepareTarget')->getClosure();
    $this->targetDefinition = $method($this->getMockFieldDefinition(['datetime_type' => 'date']));
  }

  /**
   * Basic test.
   */
  public function test() {
    $configuration = [
      'feed_type' => $this->feedType,
      'target_definition' => $this->targetDefinition,
    ];
    $target = new DateRange($configuration, 'daterange', []);
    $method = $this->getProtectedClosure($target, 'prepareValue');

    $values = [
      'value' => 1411606273,
      'end_value' => 1489582776,
    ];
    $method(0, $values);
    $this->assertSame(date(DATETIME_DATE_STORAGE_FORMAT, 1411606273), $values['value']);
    $this->assertSame(date(DATETIME_DATE_STORAGE_FORMAT, 1489582776), $values['end_value']);
  }

}
