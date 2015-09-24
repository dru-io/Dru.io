/**
 * @file
 * Example of JavaScript file for theme.
 * You can edit it or write on your own.
 */
(function ($, Drupal, window, document, undefined) {

  Drupal.behaviors.druio_theme = {
    attach: function (context, settings) {
      // Attach highlightjs.
      if ($('pre code').length) {
        $('pre code').each(function (i, block) {
          hljs.highlightBlock(block);
        });
      }

      // Fix wrapper for Rate module.
      if ($('.rate-widget').length) {
        $('.rate-widget').each(function () {
          $(this).addClass($(this).attr('id'));
        });
      }

      if ($('#make-reply').length) {
        $('#make-reply').click(function () {
          $(this).hide();
          $('form.node-answer-form').fadeIn();
        });
      }

      /**
       * Sidebar for tablet size media queries.
       */
      // Button for toggle sidebar.
      var sidebarButton = $('<a href="#" id="sidebar-toggle">Сайдбар</a>');
      // Function which did all work.
      function tablet_sidebar() {
        var width = $(window).width(),
        // This is our tablet media query sizes.
          minWidth = 768,
          maxWidth = 992;

        // If we in media query breakpoint.
        if (width >= minWidth && width <= maxWidth) {
          $('#main', context).prepend(sidebarButton);
          // Attach click listener to our button.
          sidebarButton.on('click', function (e) {
            $('body', context).toggleClass('sidebar-open');
            // Set min-height for content equals of sidebar height.
            var contentHeight = $('#content', context).height(),
              sidebarHeight = $('#sidebar', context).height();
            if (contentHeight < sidebarHeight) {
              $('#content', context).css('min-height', sidebarHeight);
            }
            e.preventDefault();
          });
        }
        // If we outside this sizes.
        else {
          sidebarButton.remove();
        }
      }
      // First at all we call this function on page load.
      tablet_sidebar();
      // Attach event on widnow resize.
      $(window).on('resize', function (e) {
        tablet_sidebar();
      });
    }
  };

})(jQuery, Drupal, this, this.document);
