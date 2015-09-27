/**
 * @file
 * Integration file for Lightcase module.
 */

(function($) {
  Drupal.behaviors.Lightcase = {
    attach: function(context, settings) {
      var selectors = ['.lightcase'];

      if (typeof settings.lightcase === 'undefined') {
        settings.lightcase = {};
      }

      if (typeof settings.lightcase.selectors !== 'undefined') {
        selectors = selectors.concat(settings.lightcase.selectors);
      }

      $(selectors.join(',')).lightcase(settings.lightcase.options);
    }
  };
})(jQuery);
