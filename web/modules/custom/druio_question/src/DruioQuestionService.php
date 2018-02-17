<?php

namespace Drupal\druio_question;

use Drupal\Core\Entity\EntityTypeManager;

/**
 * Class DruioQuestionService.
 */
class DruioQuestionService {

  /**
   * @var EntityTypeManager.
   */
  protected $entityTypeManager;

  /**
   * Constructs a new DruioQuestionService object.
   */
  public function __construct(EntityTypeManager $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * Returns how much question has answers.
   *
   * @param int $qid
   *   Question node ID for which answer count must be returned.
   *
   * @return int
   *   The answers count for question.
   */
  public function getQuestionAnswerCount($qid) {
    $query = $this->entityTypeManager->getStorage('comment')
      ->getQuery()
      ->condition('comment_type', 'question_answer')
      ->condition('entity_id', $qid);
    return $query->count()->execute();
  }

  /**
   * Return last answer creation time, otherwise question creation time.
   *
   * @param int $qid
   *   Question NID.
   *
   * @return int
   *   UNIX timestamp with time of last answer for needed question, if question
   *   has no answers, the question time will return.
   */
  public function getQuestionLastAnswerCreatedTime($qid) {
    $comment_storage = $this->entityTypeManager->getStorage('comment');
    $query = $comment_storage
      ->getQuery()
      ->condition('comment_type', 'question_answer')
      ->condition('entity_id', $qid)
      ->range(0, 1)
      ->sort('created', 'DESC');
    $result = $query->execute();
    if (!empty($result)) {
      $answer = $comment_storage->load(reset($result));
      return $answer->getCreatedTime();
    }
    else {
      $question = $this->entityTypeManager->getStorage('node')->load($qid);
      return $question->getCreatedTime();
    }
  }

}
