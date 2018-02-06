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
      // Luxon doesn't suppoer relative time for now, so we handle it manually.
      // @see https://github.com/moment/luxon/blob/40bfc1396d06fd8f7996843dbc913db29428329d/docs/moment.md#major-functional-differences
      // @todo find out why it fails on second plural form. Issue #2934480.
      if (different.days > 0) {
        result = Drupal.formatPlural(different.days, '@count day', `@count days`);
      }
      else if (different.hours > 0) {
        result = Drupal.formatPlural(different.hours, '@count hour', `@count hours`);
      }
      else if (different.minutes > 0) {
        result = Drupal.formatPlural(different.hours, '@count minute', `@count minutes`);
      }
      else {
        // Seconds are decimals in Luxon. We parse only int value.
        result = Drupal.formatPlural(parseInt(different.seconds), '@count second', `@count seconds`);
      }

      $(element).html(result);
    },
  };

})(jQuery, Drupal, luxon.DateTime);

