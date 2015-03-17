/**
 * @file
 * Example of JavaScript file for theme.
 * You can edit it or write on your own.
 */
(function ($, Drupal, window, document, undefined) {

    Drupal.behaviors.yourBehaviorName = {
        attach: function (context, settings) {
            // Attach highlightjs.
            $('pre code').each(function (i, block) {
                hljs.highlightBlock(block);
            });
        }
    };

})(jQuery, Drupal, this, this.document);
