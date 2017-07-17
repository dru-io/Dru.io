<?php

namespace Drupal\Tests\feeds\Unit;

use Drupal\feeds\TargetDefinition;
use Drupal\Tests\feeds\Unit\FeedsUnitTestCase;

/**
 * @coversDefaultClass \Drupal\feeds\TargetDefinition
 * @group feeds
 */
class TargetDefinitionTest extends FeedsUnitTestCase {

  public function test() {
    $target_definition = TargetDefinition::create()
      ->setPluginId('test_plugin')
      ->setLabel('Test label')
      ->setDescription('Test description')
      ->addProperty('test_property', 'Test property', 'Test property description')
      ->markPropertyUnique('test_property');

    $this->assertSame($target_definition->getPluginId(), 'test_plugin');

    $this->assertSame('Test label', $target_definition->getLabel());
    $this->assertSame('Test description', $target_definition->getDescription());

    $this->assertSame($target_definition->getProperties(), ['test_property']);
    $this->assertSame($target_definition->getPropertyLabel('test_property'), 'Test property');
    $this->assertSame($target_definition->getPropertyDescription('test_property'), 'Test property description');
    $this->assertSame($target_definition->getProperties(), ['test_property']);

    $this->assertTrue($target_definition->hasProperty('test_property'));
    $this->assertTrue($target_definition->isUnique('test_property'));
  }

}
