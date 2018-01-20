/**
 * @file
 * Author: Synpase-studio.
 */

(function ($) {
  $(document).ready(function () {
    // Document ready!
    console.log('script.js');
    mobile_nav();
  });
  function mobile_nav() {
    var dropdown_link = $('#block-menu-main .menu-dropdown-trigger');
    if (dropdown_link.length) {
      dropdown_link.on('click', function () {
        var child_menu = $(this).siblings('.menu'),
            parent_row = $(this).parent('.menu-item');
        child_menu.slideToggle('250').toggleClass('menu--is-open');
        parent_row.toggleClass('menu-item--is-open');
      });
    }
  }
  if (typeof(Drupal) == 'object') {
    Drupal.behaviors.adaptive = {
      attach: function (context) {
      }
    }
  }
})(this.jQuery);
