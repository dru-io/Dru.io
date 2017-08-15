<?php

namespace Drupal\feeds;

use Drupal\Core\Entity\ContentEntityInterface;

interface SubscriptionInterface extends ContentEntityInterface {

  public function subscribe();

  public function unsubscribe();

  public function getExpire();

  public function getHub();

  public function getSecret();

  public function getTopic();

  public function getToken();

  public function getState();

  public function setState($state);

  public function getLease();

  public function setLease($lease);

  public function checkSignature($sha1, $data);

}
