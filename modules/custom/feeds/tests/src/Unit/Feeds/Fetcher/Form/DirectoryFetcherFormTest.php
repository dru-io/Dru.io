<?php

namespace Drupal\Tests\feeds\Unit\Feeds\Fetcher\Form;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\Form\FormState;
use Drupal\Tests\feeds\Unit\FeedsUnitTestCase;
use Drupal\feeds\FeedTypeInterface;
use Drupal\feeds\Feeds\Fetcher\DirectoryFetcher;
use Drupal\feeds\Feeds\Fetcher\Form\DirectoryFetcherForm;
use Drupal\feeds\Plugin\Type\FeedsPluginInterface;

/**
 * @coversDefaultClass \Drupal\feeds\Feeds\Fetcher\Form\DirectoryFetcherForm
 * @group feeds
 */
class DirectoryFetcherFormTest extends FeedsUnitTestCase {


  public function testConfigurationForm() {
    $container = new ContainerBuilder();
    $container->set('stream_wrapper_manager', $this->getMockStreamWrapperManager());

    $plugin = $this->prophesize(FeedsPluginInterface::class);

    $form_object = DirectoryFetcherForm::create($container);
    $form_object->setStringTranslation($this->getStringTranslationStub());
    $form_object->setPlugin($plugin->reveal());

    $form_state = new FormState();

    $form = $form_object->buildConfigurationForm([], $form_state);

    $form_state->setValue('allowed_extensions', ' txt  pdf ');

    $form_object->validateConfigurationForm($form, $form_state);

    $this->assertSame('txt pdf', $form_state->getValue('allowed_extensions'));
  }

}

