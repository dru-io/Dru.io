<?php

namespace Drupal\feeds\Entity;

use Drupal\Component\Utility\Crypt;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\feeds\SubscriptionInterface;

/**
 * Defines the subscription entity class.
 *
 * @ContentEntityType(
 *   id = "feeds_subscription",
 *   label = @Translation("Subscription"),
 *   module = "feeds",
 *   base_table = "feeds_subscription",
 *   entity_keys = {"id" = "fid"}
 * )
 */
class Subscription extends ContentEntityBase implements SubscriptionInterface {

  /**
   * {@inheritdoc}
   */
  public function subscribe() {
    $this->set('state', 'subscribing');
    $this->save();
  }

  /**
   * {@inheritdoc}
   */
  public function unsubscribe() {
    $this->validateState();

    switch ($this->getState()) {
      case 'subscribed':
      case 'subscribing':
        $this->set('state', 'unsubscribing');
        break;
    }

    $this->delete();
  }

  /**
   * {@inheritdoc}
   */
  public function getHub() {
    return $this->get('hub')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getSecret() {
    return $this->get('secret')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getTopic() {
    return $this->get('topic')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getToken() {
    return $this->get('token')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getState() {
    return $this->get('state')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setState($state) {
    $this->set('state', $state);
  }

  /**
   * {@inheritdoc}
   */
  public function getLease() {
    return (int) $this->get('lease')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setLease($lease) {
    $lease = (int) trim($lease);
    $this->set('lease', $lease);
    $this->set('expires', $lease + REQUEST_TIME);
  }

  /**
   * {@inheritdoc}
   */
  public function getExpire() {
    return (int) $this->get('expires')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function checkSignature($sha1, $data) {
    return $sha1 === hash_hmac('sha1', $data, $this->getSecret());
  }

  /**
   * Validates the state of the subscription.
   *
   * @throws \LogicException
   *   Thrown if the state of the subscription is invalid.
   */
  protected function validateState() {
    $this->ensureSecret();
    $this->ensureToken();

    if ($this->validate()->count()) {
      throw new \LogicException('The subscription is invalid.');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage_controller, $update = TRUE) {
    parent::preSave($storage_controller, $update);

    $this->ensureSecret();
    $this->ensureToken();
    $this->validateState();
  }

  /**
   * Ensures that this subscription has a valid secret.
   */
  protected function ensureSecret() {
    if (!$this->getSecret()) {
      $this->set('secret', substr(Crypt::randomBytesBase64(32), 0, 32));
    }
  }

  /**
   * Ensures that this subscription has a valid token.
   */
  protected function ensureToken() {
    if (!$this->getToken()) {
      $this->set('token', substr(Crypt::randomBytesBase64(20), 0, 20));
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = [];

    $fields['fid'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Feed ID'))
      ->setDescription(t('The feed ID.'))
      ->setReadOnly(TRUE)
      ->setSetting('unsigned', TRUE);

    $fields['topic'] = BaseFieldDefinition::create('uri')
      ->setLabel(t('Topic'))
      ->setDescription(t('The fully-qualified URL of the feed.'))
      ->setReadOnly(TRUE)
      ->setRequired(TRUE);

    $fields['hub'] = BaseFieldDefinition::create('uri')
      ->setLabel(t('Hub'))
      ->setDescription(t('The fully-qualified URL of the PuSH hub.'))
      ->setReadOnly(TRUE)
      ->setRequired(TRUE);

    $fields['lease'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Lease time'))
      ->setDescription(t('The time, in seconds of the lease.'));

    $fields['expires'] = BaseFieldDefinition::create('timestamp')
      ->setLabel(t('Expires'))
      ->setDescription(t('The UNIX timestamp when the subscription expires.'));

    $fields['secret'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Secret'))
      ->setDescription(t('The secret used to verify a request.'))
      ->setReadOnly(TRUE)
      ->setRequired(TRUE)
      ->setSetting('is_ascii', TRUE)
      ->setSetting('max_length', 32);

    $fields['token'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Token'))
      ->setDescription(t('The token used as part of the URL.'))
      ->setReadOnly(TRUE)
      ->setRequired(TRUE)
      ->setSetting('is_ascii', TRUE)
      ->setSetting('max_length', 20);

    $fields['state'] = BaseFieldDefinition::create('string')
      ->setLabel(t('State'))
      ->setDescription(t('The state of the subscription.'))
      ->setRequired(TRUE)
      ->setSetting('is_ascii', TRUE)
      ->setSetting('max_length', 32)
      ->setDefaultValue('unsubscribed');

    return $fields;
  }

}
