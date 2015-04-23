/*global Drupal: false, jQuery: false */
/*jslint devel: true, browser: true, maxerr: 50, indent: 2 */
(function ($) {
  "use strict";

  Drupal.behaviors.hybridauth_onclick = {};
  Drupal.behaviors.hybridauth_onclick.attach = function(context, settings) {
    $('.hybridauth-widget-provider', context).each(function() {
      // $(this).attr('href', $(this).attr('data-hybridauth-url'));
      this.href = $(this).attr('data-hybridauth-url');
    });
    $('.hybridauth-onclick-current:not(.hybridauth-onclick-processed)', context).addClass('hybridauth-onclick-processed').bind('click', function() {
      $(this).parents('.hybridauth-widget').after('<div>' + Drupal.t('Contacting @title...', {'@title': this.title}) + '</div>');
    });
    $('.hybridauth-onclick-popup:not(.hybridauth-onclick-processed)', context).addClass('hybridauth-onclick-processed').bind('click', function() {
      var width = $(this).attr('data-hybridauth-width'), height = $(this).attr('data-hybridauth-height');
      var popup_window = window.open(
        this.href,
        'hybridauth',
        'location=no,menubar=no,resizable=yes,scrollbars=yes,status=no,titlebar=yes,toolbar=no,channelmode=yes,fullscreen=yes,width=' + width + ',height=' + height
      );
      popup_window.focus();
      return false;
    });

    // Last used provider feature.
    var last_provider = $.cookie('hybridauth_last_provider');
    if (last_provider != null) {
      $('[data-hybridauth-provider="' + last_provider + '"]', context).addClass('hybridauth-last-provider');
    }
    $('.hybridauth-widget-provider:not(.hybridauth-provider-processed)', context).addClass('hybridauth-provider-processed').bind('click', function() {
      $.cookie('hybridauth_last_provider', $(this).attr('data-hybridauth-provider'), {expires: 30, path: '/'});
    });
  };

})(jQuery);
