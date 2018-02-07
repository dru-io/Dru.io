/**
 * @file
 * Create relative time behaviors.
 *
 * Used for theme hook. @see druio_theme_created_relative
 */

(function ($, Drupal, DateTime) {

  'use strict';

  Drupal.behaviors.druioThemeCreatedRelative = {
    attach: function (context, settings) {
      let elements = $(context).find('.druio-theme-created-relative').once('created-relative');

      if (elements.length) {
        $.each(elements, (key, element) => {
          let timestamp = element.getAttribute('data-timestamp');
          let timeCurrent = DateTime.local();
          let timeCreated = DateTime.fromMillis(timestamp * 1000);
          let different = timeCurrent.diff(timeCreated, 'days');
          if (different.days < 7) {
            // Initial call.
            this.updateResult(element, timestamp);
            // Set update.
            setInterval(() => { this.updateResult(element, timestamp) }, 1000);
          }
        });
      }
    },

    /**
     * Update difference result.
     */
    updateResult: function(element, timestamp) {
      let timeCurrent = DateTime.local();
      let timeCreated = DateTime.fromMillis(timestamp * 1000);
      let different = timeCurrent.diff(timeCreated, ['days', 'hours', 'minutes', 'seconds']);
      let result = '';
      let translations = {
        'days': Drupal.formatPlural(different.days, '1 day', '@count days', {}, { context: 'Relative time' }),
        'hours': Drupal.formatPlural(different.hours, '1 hour', '@count hours', {}, { context: 'Relative time' }),
        'minutes': Drupal.formatPlural(different.minutes, '1 minute', '@count minutes', {}, { context: 'Relative time' }),
        // Seconds are decimals in Luxon. We parse only int value.
        'seconds': Drupal.formatPlural(parseInt(different.seconds), '1 second', '@count seconds', {}, { context: 'Relative time' }),
      };

      // Luxon doesn't support relative time for now, so we handle it manually.
      // @see https://github.com/moment/luxon/blob/40bfc1396d06fd8f7996843dbc913db29428329d/docs/moment.md#major-functional-differences
      if (different.days > 0) {
        result = this.prepareTranslation(different.hours, translations.days, translations.hours);
      }
      else if (different.hours > 0) {
        result = this.prepareTranslation(different.minutes, translations.hours, translations.minutes);
      }
      else if (different.minutes > 0) {
        result = this.prepareTranslation(parseInt(different.seconds), translations.minutes, translations.seconds);
      }
      else {
        result = this.prepareTranslation(0, translations.seconds);
      }

      $(element).html(result);
    },

    /**
     * Prepare translated string.
     */
    prepareTranslation: function(secondValue, firstString, secondString = '') {
      if (secondValue === 0) {
        return Drupal.t('@value ago', {
          '@value': firstString,
        }, { context: 'Relative time' });
      }
      else {
        return Drupal.t('@first and @second ago', {
          '@first': firstString,
          '@second': secondString,
        }, { context: 'Relative time' });
      }

    }
  };

})(jQuery, Drupal, luxon.DateTime);

