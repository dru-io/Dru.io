<?php

namespace Drupal\Tests\feeds\Unit\Feeds\Fetcher\Form;

use Drupal\Component\Uuid\UuidInterface;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormState;
use Drupal\Tests\feeds\Unit\FeedsUnitTestCase;
use Drupal\feeds\FeedInterface;
use Drupal\feeds\Feeds\Fetcher\Form\UploadFetcherFeedForm;
use Drupal\feeds\Plugin\Type\FeedsPluginInterface;
use Drupal\file\FileInterface;
use Drupal\file\FileStorageInterface;
use Drupal\file\FileUsage\FileUsageInterface;
use Prophecy\Argument;

/**
 * @coversDefaultClass \Drupal\feeds\Feeds\Fetcher\Form\UploadFetcherFeedForm
 * @group feeds
 */
class UploadFetcherFeedFormTest extends FeedsUnitTestCase {

  public function test() {
    $file = $this->prophesize(FileInterface::class);

    $file_storage = $this->prophesize(FileStorageInterface::class);
    $file_storage->load(1)->willReturn($file->reveal());

    $entity_manager = $this->prophesize(EntityTypeManagerInterface::class);
    $entity_manager->getStorage('file')->willReturn($file_storage->reveal());

    $file_usage = $this->prophesize(FileUsageInterface::class);

    $uuid = $this->prophesize(UuidInterface::class);

    $container = new ContainerBuilder();
    $container->set('entity_type.manager', $entity_manager->reveal());
    $container->set('file.usage', $file_usage->reveal());
    $container->set('uuid', $uuid->reveal());

    $plugin = $this->prophesize(FeedsPluginInterface::class);
    $plugin->getConfiguration('allowed_extensions')->willReturn('foo');
    $plugin->getConfiguration('directory')->willReturn('foodir');

    $form_object = UploadFetcherFeedForm::create($container);
    $form_object->setStringTranslation($this->getStringTranslationStub());
    $form_object->setPlugin($plugin->reveal());

    $form_state = new FormState();

    $feed = $this->prophesize(FeedInterface::class);
    $feed->getConfigurationFor($plugin->reveal())
      ->willReturn(['fid' => 1, 'usage_id' => 'foo']);
    $feed->setConfigurationFor($plugin->reveal(), ['fid' => 1, 'usage_id' => 'foo'])
      ->willReturn(NULL);

    $form = $form_object->buildConfigurationForm([], $form_state, $feed->reveal());

    $form_object->validateConfigurationForm($form, $form_state, $feed->reveal());

    $form_state->setValue('source', [1]);

    $form_object->submitConfigurationForm($form, $form_state, $feed->reveal());
  }

}

