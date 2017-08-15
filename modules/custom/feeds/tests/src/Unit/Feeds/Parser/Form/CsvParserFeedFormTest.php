<?php

namespace Drupal\Tests\feeds\Unit\Feeds\Parser\Form;

use Drupal\Core\Form\FormState;
use Drupal\Tests\feeds\Unit\FeedsUnitTestCase;
use Drupal\feeds\FeedInterface;
use Drupal\feeds\Feeds\Parser\Form\CsvParserFeedForm;
use Drupal\feeds\Plugin\Type\FeedsPluginInterface;

/**
 * @coversDefaultClass \Drupal\feeds\Feeds\Parser\Form\CsvParserFeedForm
 * @group feeds
 */
class CsvParserFeedFormTest extends FeedsUnitTestCase {

  public function test() {
    $plugin = $this->prophesize(FeedsPluginInterface::class);

    $feed = $this->prophesize(FeedInterface::class);
    $feed->getConfigurationFor($plugin->reveal())
      ->willReturn(['delimiter' => ',', 'no_headers' => FALSE]);

    $form_object = new CsvParserFeedForm();

    $form_object->setPlugin($plugin->reveal());
    $form_object->setStringTranslation($this->getStringTranslationStub());

    $form_state = new FormState();

    $form = $form_object->buildConfigurationForm([], $form_state, $feed->reveal());

    $form_object->validateConfigurationForm($form, $form_state, $feed->reveal());

    $form_object->submitConfigurationForm($form, $form_state, $feed->reveal());
  }

}

