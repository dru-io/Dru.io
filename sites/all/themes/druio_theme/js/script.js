/**
 * @file
 * Example of JavaScript file for theme.
 * You can edit it or write on your own.
 */
(function ($, Drupal, window, document, undefined) {

  Drupal.behaviors.druio_theme = {
    attach: function (context, settings) {
      // Fix wrapper for Rate module.
      $('.rate-widget').each(function () {
        $(this).addClass($(this).attr('id'));
      });

      $('#make-reply').click(function() {
        $(this).hide();
        $('form.node-answer-form').fadeIn();
      });
    }
  };

})(jQuery, Drupal, this, this.document);
