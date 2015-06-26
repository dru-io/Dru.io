/**
 * @file
 * Example of JavaScript file for theme.
 *
 * You can edit it or write on your own.
 */

(function ($, Drupal, window, document, undefined) {

  Drupal.behaviors.yourBehaviorName = {
    attach: function (context, settings) {
      // Attach highlightjs.
      $('pre code').each(function (i, block) {
        hljs.highlightBlock(block);
      });

      // Fix wrapper for Rate module.
      $('.rate-widget').each(function () {
        $(this).addClass($(this).attr('id'));
      });

      // Activate Select2
      $('select').select2({
        theme: 'druio'
      });

      $('#make-reply').click(function() {
         $(this).hide();
         $('form.node-answer-form').fadeIn();
      });

    }
  };

})(jQuery, Drupal, this, this.document);
