<?php

class SearchApiElasticsearchElasticaSpellcheck extends SearchApiSpellcheck {

  function __construct($response) {
    $suggestions = $response->getSuggests();
    foreach ($suggestions as $suggestion_group) {
      foreach ($suggestion_group as $suggestion_set) {
        $original = $suggestion_set['text'];
        foreach ($suggestion_set['options'] as $suggestion) {
          $this->addSuggestion(new SearchApiSpellcheckSuggestion($original, $suggestion['text']));
        }
      }
    }
  }
}
