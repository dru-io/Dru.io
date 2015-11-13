/**
 * @file
 * Example of JavaScript file for theme.
 * You can edit it or write on your own.
 */
(function ($, Drupal, window, document, undefined) {

  Drupal.behaviors.druio_theme = {
    attach: function (context, settings) {
      // Set target="_blank" for links in content section.
      var $article = $('#content > article', context);
      $('section.question__right a, section.post__content a', $article)
        .not('.category a, .projects a, ul.links a')
        .attr('target', '_blank');

      // @todo: use imagesloaded.js
      $('section.question__right img, ' +
        'section.post__content img,' +
        '.answer img,' +
        '.comments img', $article).one("load", function () {
        var $this = $(this);
        if ($this.width() != $this[0].naturalWidth) {
          if (0 == $(this).parent('a').length) {
            var $link_wrapper = $('<a class="lightcase" href="' + $this.attr('src') + '"></a>');
            $this.wrap($link_wrapper);
            Drupal.behaviors.Lightcase.attach($link_wrapper, settings);
          }
        }
      }).each(function () {
        if (this.complete) $(this).load();
      });

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
      function responsive_sidebar() {
        var width = $(window).width(),
        // This is our tablet media query sizes.
          minWidth = 0,
          maxWidth = 992 - 16,
          hasSidebar = $('body', context).hasClass('sidebar');

        // If we in media query breakpoint.
        if (width >= minWidth && width <= maxWidth && hasSidebar) {
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
      responsive_sidebar();

      /**
       * Mobile header.
       */
      var mobileNav = $('<div id="mobile-menu"></div>'),
        mobileNavButton = $('<a href="#" id="mobile-menu-button">Меню</a>');

      // Bind click for button.
      mobileNavButton.on('click', function(e) {
        $('body', context).toggleClass('menu-open');
      });

      function mobile_menu() {
        var maxWidth = 768,
          width = $(window).width();

        // If mobile size.
        if (width < maxWidth) {
          // Add our mobile nav.
          $('body', context).append(mobileNav);
          // Add mobile menu button.
          $('#header .pane', context).append(mobileNavButton);
          // Move header-auth.
          $('#mobile-menu', context).append($('#header .content .top [class^="header-auth"]', context));
          // Move navigation.
          $('#mobile-menu', context).append($('#navigation .menu', context));
        }
        else {
          // Move back navigation.
          $('#navigation', context).append($('#mobile-menu .menu', context));
          // Move back header auth links.
          $('#header .content .top', context).append($('#mobile-menu [class^="header-auth"]', context));
          // Remove mobile nav.
          $('#mobile-menu', context).remove();
          // Remove mobile nav button.
          $('#mobile-menu-button', context).remove();
        }
      };
      mobile_menu();

      // Attach event on widnow resize.
      $(window).on('resize', function (e) {
        responsive_sidebar();
        mobile_menu();
      });
    }
  };

})(jQuery, Drupal, this, this.document);
