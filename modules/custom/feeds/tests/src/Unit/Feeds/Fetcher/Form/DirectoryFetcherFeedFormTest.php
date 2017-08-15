<?php

namespace Drupal\Tests\feeds\Unit\Feeds\Fetcher\Form;

use Drupal\Core\Form\FormState;
use Drupal\Tests\feeds\Unit\FeedsUnitTestCase;
use Drupal\feeds\FeedInterface;
use Drupal\feeds\Feeds\Fetcher\Form\DirectoryFetcherFeedForm;
use Drupal\feeds\Plugin\Type\Fetcher\FetcherInterface;
use Prophecy\Argument;

/**
 * @coversDefaultClass \Drupal\feeds\Feeds\Fetcher\Form\DirectoryFetcherFeedForm
 * @group feeds
 */
class DirectoryFetcherFeedFormTest extends FeedsUnitTestCase {

  public function test() {
    file_put_contents('vfs://feeds/test.txt', 'data');

    $plugin = $this->prophesize(FetcherInterface::class);
    $plugin->getConfiguration('allowed_schemes')->willReturn(['vfs']);
    $plugin->getConfiguration('allowed_extensions')->willReturn('txt');

    $feed = $this->prophesize(FeedInterface::class);
    $feed->getSource()->willReturn('vfs://feeds/test.txt');
    $feed->setSource('vfs://feeds/test.txt')->shouldBeCalled();

    $form_object = new DirectoryFetcherFeedForm();
    $form_object->setStringTranslation($this->getStringTranslationStub());
    $form_object->setPlugin($plugin->reveal());

    $form_state = new FormState();

    $form = $form_object->buildConfigurationForm([], $form_state, $feed->reveal());

    $form_state->setValue('source', 'vfs://feeds/test.txt');

    $form_object->validateConfigurationForm($form, $form_state, $feed->reveal());

    $form_object->submitConfigurationForm($form, $form_state, $feed->reveal());
  }

}

