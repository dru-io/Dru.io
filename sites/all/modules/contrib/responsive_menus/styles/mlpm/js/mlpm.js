/**
 * @file
 * Integrate codrops' ResponsiveMultiLevelMenu library with Responsive Menus.
 */
(function ($) {
  Drupal.behaviors.mlpm = {
    attach: function (context, settings) {
      settings.responsive_menus = settings.responsive_menus || {};
      var $windowWidth = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;
      $.each(settings.responsive_menus, function(ind, iteration) {
        if (iteration.responsive_menus_style != 'mlpm') {
          return true;
        }
        if (!iteration.selectors.length) {
          return;
        }
        // Only apply if window size is correct.  Runs once on page load.
        var $media_size = iteration.media_size || 768;
        if ($windowWidth <= $media_size || $media_size == 0) {
          //change array of strings to array of jquery elements
          var $contToPush = [];
          for (i in iteration.push) {
            $contToPush.push($(iteration.push[i]));
          }
          // Call mlpm with our settings.
          $(iteration.selectors).once('responsive-menus-mlpm', function() {
            if (iteration.nav_block == 1) {
              $(this).wrapInner("<nav id=" + iteration.nav_block_name + "></nav>");
            }
            if (iteration.move_to) {
              $(this).detach().prependTo(iteration.move_to);
            }
            //set up a menu toggle button
            if (iteration.toggle_container) {
              $(iteration.toggle_container).prepend("<a id='mlpm-toggle' href='#'>" + iteration.toggle_text + "</a>");
              $('#mlpm-toggle').click({menu:this}, function(event) {
                if ($(event.data.menu).multilevelpushmenu('visiblemenus') == false) {
                  $(event.data.menu).multilevelpushmenu('expand');
                } else {
                  $(event.data.menu).multilevelpushmenu('collapse');
                }
              });
            }
            //make clicking on certain elements close the menu (for the off menu click effect)
            if (iteration.off_menu) {
              $(iteration.off_menu).click({menu: this}, function(event) {
                if ($(event.data.menu).multilevelpushmenu('visiblemenus') != false) {
                  $(event.data.menu).multilevelpushmenu('collapse');
                }
              });
            }

            //Define the multi level push menu
            $(this).multilevelpushmenu({
              container: $(this),
              menuID: iteration.nav_block_name,
              direction: iteration.direction,
              menuHeight: iteration.menu_height,
              mode: iteration.mode,
              collapsed: iteration.collapsed == 1,
              fullCollapse: iteration.full_collapse == 1,
              swipe: iteration.swipe,
              containersToPush: $contToPush,
              backText: iteration.back_text,
              backItemClass: iteration.back_class,
              backItemIcon: iteration.back_icon,
              groupIcon: iteration.group_icon,
              onItemClick: function() {
                $item = arguments[2];
                var itemHref = $item.find('a:first').attr('href');
                location.href = itemHref;
              },
              onExpandMenuEnd: function () {
                //Browse back through the layers of the menu during overlap
                $menu = arguments[0];
                $('.multilevelpushmenu_inactive').click({menu: $menu.container}, function (event) {
                  event.data.menu.multilevelpushmenu('collapse', $(this).attr('data-level'));
                });
              }
            });
          });
        }

      });

    }
  };
}(jQuery));